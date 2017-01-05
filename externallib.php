<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    totara
 * @subpackage local_userprovisioning
 * @copyright  Catalyst IT Europe Ltd 2017
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class local_userprovisioning_external extends external_api {

    /**
     * Returns description of create_org_user method parameters
     *
     * @return external_function_parameters
     */
    public static function create_org_user_parameters() {
        return new external_function_parameters(
            array(
                'employeeid' => new external_value(PARAM_TEXT),
                'firstname' => new external_value(PARAM_TEXT),
                'lastname' => new external_value(PARAM_TEXT),
                'email' => new external_value(PARAM_TEXT),
                'org' => new external_value(PARAM_TEXT)
            )
        );
    }

    /**
     * Returns description of create_org_users method parameters
     *
     * @return external_function_parameters
     */
    public static function create_org_users_parameters() {
        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'employeeid' => new external_value(PARAM_TEXT),
                            'firstname' => new external_value(PARAM_TEXT),
                            'lastname' => new external_value(PARAM_TEXT),
                            'email' => new external_value(PARAM_TEXT)
                        )
                    )
                ),
                'org' => new external_value(PARAM_TEXT)
            )
        );
    }

    /**
     * Returns created user fullname and ID
     *
     * @return array
     */
    public static function create_org_user($employeeid, $firstname, $lastname, $email, $org) {
        global $USER, $DB, $CFG;

        $userparams = self::validate_parameters(self::create_org_user_parameters(), array(
            'employeeid' => $employeeid, 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'org' => $org
        ));

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('local/userprovisioning:createorgusers', $context)) {
            throw new moodle_exception('error:cannotcreateorgusers', 'local_userprovisioning');
        }

        list($name, $userid) = self::create_user($userparams, $orgid);

        return array('name' => $name, 'userid' => $userid);
    }

    /**
     * Returns array of created users fullnames and IDs
     *
     * @return array
     */
    public static function create_org_users($users, $org) {
        global $USER, $DB, $CFG;

        $params = self::validate_parameters(self::create_org_users_parameters(), array(
            'users' => $users, 'org' => $org
        ));

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('local/userprovisioning:createorgusers', $context)) {
            throw new moodle_exception('error:cannotcreateorgusers', 'local_userprovisioning');
        }

        if ($orgid = $DB->get_field('org', 'id', array('shortname' => $params['org']))) {

            $newusers = array();
            foreach ($params['users'] as $userparams) {
                list($name, $userid) = self::create_user($userparams, $orgid);
                $newusers[] = array(
                    'name' => $name,
                    'userid' => $userid
                );
            }
            return $newusers;
        } else {

            throw new invalid_parameter_exception('Invalid organisation');
        }
    }

    /**
     * Returns description of create_org_user method result value
     *
     * @return external_single_structure
     */
    public static function create_org_user_generation_returns() {
        return new external_single_structure(
            array(
                'name' => new external_value(PARAM_TEXT, 'User full name'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
            )
        );
    }

    /**
     * Returns description of create_org_users method result value
     *
     * @return external_multiple_structure
     */
    public static function create_org_users_generation_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'User full name'),
                    'userid' => new external_value(PARAM_INT, 'User ID'),
                )
            )
        );
    }

    /**
     * Creates user and assigns to organisation.
     *
     * @param array $userparams User parameters
     * @param int $orgid Organisation ID
     * @return array
     */
    public function create_user($userparams, $orgid) {

        // Check if user exists by matching against employeeid custom user profile field.
        if ($userid = $DB->get_field('user_info_data', 'userid',
            array('fieldid' => $employeeidfield, 'data' => $userparams['employeeid']))) {

            // User exists - update user.
            $user = new stdClass();
            $user->id = $userid;
            $user->firstname = $userparams['firstname'];
            $user->lastname = $userparams['lastname'];
            $user->email = $userparams['email'];
            $DB->update_record('user', $user);

        } else {

            // Create user.
            $user = new stdClass();
            $user->firstname = $userparams['firstname'];
            $user->lastname = $userparams['lastname'];
            $user->email = $userparams['email'];
            $user->username = $userparams['email'];
            $user->password = 'Password123+'; // TODO: Stage 2 of development will change auth type to SAML.
            $user->lang = 'en';
            $user->alternatename = '';
            $user->confirmed = 0;
            $user->firstaccess = 0;
            $user->timecreated = time();
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->secret = random_string(15);
            $user->auth = $CFG->registerauth;

            $authplugin = get_auth_plugin($CFG->registerauth);
            $userid = $authplugin->user_signup($user);
        }

        // Insert/update user organisation.
        $now = time();
        if ($posassignid = $DB->get_field('pos_assignment', 'id',
            array('organisationid' => $orgid, 'userid' => $userid))) {

            // Exists - update organisation assignment.
            $now = time();
            $posassignment = new stdClass();
            $posassignment->id = $posassignid;
            $posassignment->timemodified = $now;
            $posassignment->usermodified = 0;
            $posassignment->organisationid = $orgid;
            $DB->update_record('pos_assignment', $posassignment);

        } else {

            // Does not exist - create organisation assignment.
            $posassignment = new stdClass();
            $posassignment->fullname = '';
            $posassignment->shortname = '';
            $posassignment->idnumber = '';
            $posassignment->description = '';
            $posassignment->timevalidfrom = '';
            $posassignment->timevalidto = '';
            $posassignment->timecreated = $now;
            $posassignment->timemodified = $now;
            $posassignment->usermodified = 0;
            $posassignment->organisationid = $orgid;
            $posassignment->userid = $userid;
            $posassignment->appraiserid = '';
            $posassignment->positionid = '';
            $posassignment->reportstoid = '';
            $posassignment->type = 1;
            $posassignment->managerid = '';
            $posassignment->managerpath = '';
            $DB->insert_record('pos_assignment', $posassignment);
        }

        return array(fullname($user), $userid);
    }
}
