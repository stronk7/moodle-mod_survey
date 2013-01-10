<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    surveytemplate
 * @subpackage attls
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

// $settings->add(new admin_setting_heading('surveytemplate_attls_settings', get_string('header_left', 'surveytemplate_attls'),
//     get_string('header_right', 'surveytemplate_attls')));

$settings->add(new admin_setting_configcheckbox('surveytemplate_attls/extrarow',
    get_string('extrarow', 'surveytemplate_attls'),
    get_string('extrarow_desc', 'surveytemplate_attls'), 1));