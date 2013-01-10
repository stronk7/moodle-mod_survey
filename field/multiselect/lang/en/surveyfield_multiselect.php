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
 * Strings for component 'field_multiselect', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    survey
 * @subpackage item_multiselect
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/lib.php');

$string['parentformat'] = '[label<br />one more label<br />last label]';
$string['pluginname'] = 'Multiple selection';
$string['userfriendlypluginname'] = 'Multiple selection';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['defaultvalue_help'] = 'This is the value the remote user will find answered by default';
$string['defaultvalue'] = 'Default';
$string['defaultvalue'] = 'Default';
$string['options_err'] = 'Options need your attection';
$string['options_help'] = 'The list of the options for this item. You are allowed to write them as: value'.SURVEY_VALUELABELSEPARATOR.'label in order to define value and label both. The label will be displayed in the element list, the value will be stored in the survey field. If you only specify one word per line (without separator), value and label will both be valued to that word.';
$string['options'] = 'Options';
$string['option'] = 'Option';
