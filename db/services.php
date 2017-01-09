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

$functions = array(
    'create_user' => array(
        'classname'   => 'local_nrc_provisioning_external',
        'methodname'  => 'create_user',
        'classpath'   => 'local/nrc_provisioning/externallib.php',
        'description' => 'Create user assigned to NRC organisation',
        'type'        => 'write',
        'capabilities'=> 'local/nrc_provisioning:createuser'
    )
);

$services = array(
    'NRC OKTA User Provisioning' => array(
        'functions' => array(
            'create_user',
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'NRC_OKTA',
        'downloadfiles' => 1
    )
);
