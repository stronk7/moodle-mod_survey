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
 * Strings for component 'field_age', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    survey
 * @subpackage age
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['and'] = 'and';
$string['customdefault'] = 'Custom';
$string['defaultvalue_help'] = 'This is the age the remote user will find answered by default. The default for this type of question is mandatory.';
$string['defaultvalue'] = 'Default';
$string['invitationmonth'] = 'Choose a month';
$string['invitationyear'] = 'Choose a year';
$string['lowerbound_help'] = 'The lowest age the user is allowed to enter';
$string['lowerbound'] = 'Lower bound';
$string['lowerequaltoupper'] = 'Lower and upper bounds need to be different';
$string['maximumage_desc'] = 'The maximun age this software will allow to ever enter';
$string['maximumage'] = 'Maximum age';
$string['months'] = 'months';
$string['outofrangedefault'] = 'Default does not fall within the specified range';
$string['outofexternalrangedefault'] = 'Default does not fall within the specified range (see "{$a}" help)';
$string['parentcontent_greaterthanmaximum'] = '{$a} is greater than the maximum age so this condition can never be verified.';
$string['parentcontent_lowerthanminimum'] = '{$a} is lower than the minimum age so this condition can never be verified.';
$string['pluginname'] = 'Age';
$string['restriction_lower'] = 'Answer is supposed to be greater than {$a}';
$string['restriction_lowerupper'] = 'Answer is supposed to fit between {$a->lowerbound} and {$a->upperbound}';
$string['restriction_upper'] = 'Answer is supposed to be lower-equal than {$a}';
$string['restriction_upperlower'] = 'Answer is supposed to be lower-equal than {$a->lowerbound} or greater-equal than {$a->upperbound}';
$string['uerr_agenotset'] = 'Please define a age or select the "{$a}" checkbox';
$string['uerr_agenotsetrequired'] = 'Please define a age';
$string['uerr_greaterthanmaximum'] = 'Provided value is greater than maximum allowed';
$string['uerr_lowerthanminimum'] = 'Provided value is lower than minimum allowed';
$string['uerr_outofexternalrange'] = 'Provided value is supposed to be lower-equal than {$a->lowerbound} or greater-equal than {$a->upperbound}';
$string['uerr_outofinternalrange'] = 'Provided value does not fall within the specified range';
$string['upperbound_help'] = 'The biggest age the user is allowed to enter.<br /><br />Upper and lower bound define a range.<br />If "lower bound" is lower than "upper bound" the user is forced to enter a value falling into the range.<br />If "lower bound" is greater than "upper bound" the user input is forced out from the range. i.e. the user input is supposed to be lower-equal than the lower bound OR grater-equal than the upper bound.';
$string['upperbound'] = 'Upper bound';
$string['userfriendlypluginname'] = 'Age [yy/mm]';
