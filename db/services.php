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

/**
 * User provisioning plugin external functions and service definitions.
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_userprovisioning_create_org_user' => array(
        'classname'   => 'local_userprovisioning_external',
        'methodname'  => 'create_org_user',
        'classpath'   => 'local/userprovisioning/externallib.php',
        'description' => 'Create user and assign to organisation',
        'type'        => 'write',
        'capabilities'=> 'local/userprovisioning:createorgusers'
    ),
    'local_userprovisioning_create_org_users' => array(
        'classname'   => 'local_userprovisioning_external',
        'methodname'  => 'create_org_users',
        'classpath'   => 'local/userprovisioning/externallib.php',
        'description' => 'Create multiple users and assign to organisation',
        'type'        => 'write',
        'capabilities'=> 'local/userprovisioning:createorgusers'
    )
);

$services = array(
    'Create organisation user' => array(
        'functions' => array('local_userprovisioning_create_org_user'),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => LOCAL_USERPROVISIONING_CREATE_ORG_USER,
        'downloadfiles' => 1
    ),
    'Create organisation users' => array(
        'functions' => array('local_userprovisioning_create_org_users'),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => LOCAL_USERPROVISIONING_CREATE_ORG_USERS,
        'downloadfiles' => 1
    )
);
