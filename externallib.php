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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/nrc_provisioning/lib.php');;

define('LOCAL_USERPROVISIONING_NRC_ORG_SHORTNAME', 'nrc');

class local_nrc_provisioning_external extends external_api {

    /**
     * Returns description of provision_nrc_user method parameters
     *
     * @return external_function_parameters
     */
    public static function create_user_parameters() {
        return new external_function_parameters(
            array(
                'employeeid' => new external_value(PARAM_TEXT),
                'firstname' => new external_value(PARAM_TEXT),
                'lastname' => new external_value(PARAM_TEXT),
                'email' => new external_value(PARAM_TEXT)
            )
        );
    }

    /**
     * Returns created user fullname and ID
     *
     * @return array
     */
    public static function create_user($employeeid, $firstname, $lastname, $email) {
        global $USER, $DB, $CFG;

        $userparams = self::validate_parameters(self::create_user_parameters(), array(
            'employeeid' => $employeeid, 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email
        ));

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('local/nrc_provisioning:createuser', $context)) {
            throw new moodle_exception('error:cannotcreatenrcusers', 'local_nrc_provisioning');
        }

        list($name, $userid) = create_nrc_user($employeeid, $firstname, $lastname, $email);

        return array('name' => $name, 'userid' => $userid);
    }

    /**
     * Returns description of provision_nrc_user method result value
     *
     * @return external_single_structure
     */
    public static function create_user_returns() {
        return new external_single_structure(
            array(
                'name' => new external_value(PARAM_TEXT, 'User full name'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
            )
        );
    }
}
