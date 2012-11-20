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

/**
 * @package    surveyitem
 * @subpackage attls
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

// $settings->add(new admin_setting_heading('surveyfield_numeric_settings', get_string('header_left', 'surveyfield_numeric'),
//     get_string('header_right', 'surveyfield_numeric')));

$settings->add(new admin_setting_configtext('surveyfield_shortage/maximumshortage',
    get_string('maximumshortage', 'surveyfield_shortage'),
    get_string('maximumshortage_desc', 'surveyfield_shortage'), 105, PARAM_INT));
