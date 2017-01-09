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

/**
 * Custom code to be run on installing the plugin.
 *
 * @return bool
 */
function xmldb_local_nrc_provisioning_install() {
    global $DB;

    // If necessary, create 'Employee ID' custom user profile field.
    if (!$DB->record_exists('user_info_field', array('shortname' => 'employeeid'))) {

        $categoryid = $DB->get_field('user_info_category', 'id', array('sortorder' => 1));
        $sortorder = $DB->count_records('user_info_field', array('categoryid' => $categoryid)) + 1;

        $uif = new stdClass();
        $uif->shortname = get_string('employeeiduifshortname', 'local_nrc_provisioning');
        $uif->name = get_string('employeeid', 'local_nrc_provisioning');
        $uif->datatype = 'text';
        $uif->description = '';
        $uif->descriptionformat = FORMAT_HTML;
        $uif->categoryid = $categoryid;
        $uif->sortorder = $sortorder;
        $uif->required = 0;
        $uif->locked = 0;
        $uif->visible = 2;
        $uif->forceunique = 0;
        $uif->signup = 0;
        $uif->defaultdata = '';
        $uif->defaultdataformat = FORMAT_MOODLE;
        $uif->param1 = 30;
        $uif->param2 = 30;
        $uif->param3 = 0;
        $uif->param4 = '';
        $uif->param5 = '';

        $DB->insert_record('user_info_field', $uif);
    }

    return true;
}
