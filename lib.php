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
 * Creates/updates user and assigns to NRC organisation.
 *
 * @param int $employeeid Employee Id
 * @param string $firstname Firstname
 * @param string $lastname Lastname
 * @param string $email Email
 * @return string Response message
 */
function create_nrc_user($employeeid, $firstname, $lastname, $email) {
    global $DB, $CFG;

    $nrcorganisationid = $DB->get_field('org', 'id', array('shortname' => LOCAL_USERPROVISIONING_NRC_ORG_SHORTNAME));

    $transaction = $DB->start_delegated_transaction();

    $responsemsg = get_string('response:customfieldrequiresconfig', 'local_nrc_provisioning');

    // Ensure that custom user profile feed exists.
    if ($employeeidfield = $DB->get_field('user_info_field', 'id',
        array('shortname' => get_string('employeeiduifshortname', 'local_nrc_provisioning')))) {

        $sql = "SELECT uid.userid, u.firstname, u.lastname, u.email, u.username, pa.id AS posassignid
                  FROM {user_info_data} uid
                  JOIN {pos_assignment} pa ON pa.userid = uid.userid
                  JOIN {user} u ON u.id = uid.userid
                 WHERE uid.fieldid = :employeeidfield
                   AND ". $DB->sql_compare_text('uid.data') . " = :employeeid
                   AND pa.organisationid = :nrcorganisationid";
        $params = array(
            'employeeidfield' => $employeeidfield,
            'employeeid' => $employeeid,
            'nrcorganisationid' => $nrcorganisationid
        );

        if ($record = $DB->get_record_sql($sql, $params)) {

            $user = core_user::get_user($record->userid);

            // Existing user identified.
            if ($record->firstname != $firstname || $record->lastname != $lastname
                || $record->email != $email || $record->username != $email) {

                // New details - update user.
                $user = new stdClass();
                $user->id = $record->userid;
                $user->firstname = $firstname;
                $user->lastname = $lastname;
                $user->username = $email;
                $user->email = $email;
                $DB->update_record('user', $user);
                $responsemsg = get_string('response:useridentifiedandupdated', 'local_nrc_provisioning', fullname($user));
            } else {

                // Details unchanged.
                $responsemsg = get_string('response:useridentifiednotupdated', 'local_nrc_provisioning', fullname($user));
            }

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
            $userid = $authplugin->user_signup($user, false);

            // Set the Employee Id value for the user.
            $uid = new stdClass();
            $uid->userid = $userid;
            $uid->fieldid = $employeeidfield;
            $uid->data = $employeeid;
            $uid->dataformat = FORMAT_MOODLE;
            $DB->insert_record('user_info_data', $uid);

            // Assign user to NRC organisation.
            $now = time();
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
            $posassignment->organisationid = $nrcorganisationid;
            $posassignment->userid = $userid;
            $posassignment->appraiserid = '';
            $posassignment->positionid = '';
            $posassignment->reportstoid = '';
            $posassignment->type = 1;
            $posassignment->managerid = '';
            $posassignment->managerpath = '';
            $DB->insert_record('pos_assignment', $posassignment);

            $langstrings = new stdClass();
            $langstrings->fullname = fullname($user);
            $langstrings->employeeid = $employeeidfield;
            $responsemsg = get_string('response:usercreated', 'local_nrc_provisioning', $langstrings);
        }
    }

    $transaction->allow_commit();

    return $responsemsg;
}
