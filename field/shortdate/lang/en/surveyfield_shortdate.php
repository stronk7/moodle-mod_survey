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
 * Strings for component 'field_shortdate', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    survey
 * @subpackage item_shortdate
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');

$string['parentformat'] = SURVEYFIELD_SHORTDATE_FORMAT;
$string['err_exceedingmonth'] = 'Specified month can not be greater than 12';
$string['parentcontentdateoutofrange_err'] = 'Provided short date is out of the range requested to the choosen item';
$string['pluginname'] = 'Short date';
$string['userfriendlypluginname'] = 'Date (short) [mm/yyyy]';
$string['defaultvalue_help'] = 'This is the short date the remote user will find answered by default. The default for this type of question is mandatory. If "Current short date" is choosed as default, boundaries are not supposed to apply.';
$string['defaultvalue'] = 'Default';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['lowerbound_help'] = 'The lower date the user will be allowed to enter';
$string['lowerbound'] = 'Lower bound';
$string['upperbound_help'] = 'The upper date the user will be allowed to enter';
$string['upperbound'] = 'Upper bound';
$string['ierr_outofrangedefault'] = 'Default does not fall within the specified range';
$string['ierr_invertupperlowerbounds'] = 'Upper bound must be greater than lower bound';
$string['and'] = ' and ';
$string['restriction_lowerupper'] = 'Date is supposed to fit between {$a}';
$string['restriction_lower'] = 'Date is supposed to be greater than {$a}';
$string['restriction_upper'] = 'Date is supposed to be lower than {$a}';
$string['uerr_lowerthanminimum'] = 'Provided short date is too small';
$string['uerr_greaterthanmaximum'] = 'Provided short date is too high';
$string['customdefault'] = 'Custom';

$string['currentshortdatedefault'] = 'Current short date';
$string['invitationmonth'] = 'Choose a month';
$string['invitationyear'] = 'Choose a year';
$string['uerr_monthnotset'] = 'Please choose a month';
$string['uerr_yearnotset'] = 'Please choose a year';
