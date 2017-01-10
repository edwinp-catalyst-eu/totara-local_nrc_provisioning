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

$string['pluginname'] = 'NRC User Provisioning';

$string['employeeid'] = 'Employee ID';
$string['employeeiduifshortname'] = 'employeeid';

// Error strings.
$string['error:cannotcreatenrcusers'] = 'Not permitted to create NRC users';

// Capability strings.
$string['nrc_provisioning:createuser'] = 'Create NRC user via provisioning web service';

// Response strings.
$string['response:customfieldrequiresconfig'] = 'Custom user profile field Employee Id requires correct configuration.';
$string['response:usercreated'] = 'User \'{$a->fullname}\' with Employee Id \'{$a->employeeid}\' created and assigned to NRC organisation.';
$string['response:useridentifiedandupdated'] = 'User \'{$a}\' identified by Employee Id and NRC organisation. Details updated with new values.';
$string['response:useridentifiednotupdated'] = 'User \'{$a}\' identified by Employee Id and NRC organisation. No details require updating.';
