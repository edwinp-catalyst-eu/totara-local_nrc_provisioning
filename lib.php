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
 * @subpackage local_nrc_provisioning
 * @copyright  Catalyst IT Europe Ltd 2017
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Creates user and assigns to NRC organisation.
 *
 *
 * @return array
 */
function create_nrc_user($employeeid, $firstname, $lastname, $email) {
    global $DB;

    // Match user by custom user profile field 'employeeid'.
    if ($employeeidfield = $DB->get_field('user_info_field', 'id',
        array('shortname' => get_string('employeeiduifshortname', 'local_nrc_provisioning')))) {

        $sql = "SELECT userid
                  FROM {user_info_data}
                 WHERE fieldid = :employeeidfield
                   AND ". $DB->sql_compare_text('data') . " = :employeeid";
        $params = array(
            'employeeidfield' => $employeeidfield,
            'employeeid' => $employeeid
        );

        if ($userid = $DB->get_field_sql($sql, $params)) {

            // User exists - update user.
            $user = new stdClass();
            $user->id = $userid;
            $user->firstname = $firstname;
            $user->lastname = $lastname;
            $user->email = $email;
            $DB->update_record('user', $user);

            $user = core_user::get_user($userid);

        } else {

            // Create user.
            $user = new stdClass();
            $user->firstname = $firstname;
            $user->firstnamephonetic = '';
            $user->middlename = '';
            $user->lastname = $lastname;
            $user->lastnamephonetic = '';
            $user->alternatename = '';
            $user->email = $email;
            $user->username = $email;
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
        $orgid = $DB->get_field('org', 'id', array('shortname' => LOCAL_USERPROVISIONING_NRC_ORG_SHORTNAME));

        if ($posassignid = $DB->get_field('pos_assignment', 'id', array('userid' => $userid))) {

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
