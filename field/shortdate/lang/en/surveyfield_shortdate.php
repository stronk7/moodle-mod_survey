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
 * @subpackage shortdate
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$string['restriction_lowerupper'] = 'Answer is supposed to fit between {$a->lowerbound} and {$a->upperbound}';
$string['restriction_lower'] = 'Answer is supposed to be greater than {$a}';
$string['restriction_upper'] = 'Answer is supposed to be lower than {$a}';
$string['uerr_lowerthanminimum'] = 'Provided value is lower than minimum required';
$string['uerr_greaterthanmaximum'] = 'Provided value is greater than maximum required';
$string['customdefault'] = 'Custom';
$string['downloadformat'] = 'Download format';
$string['downloadformat_help'] = 'Choose the format of the answer as it appear once user attempts are downloaded';
$string['strftime1'] = '%B %Y';
$string['strftime2'] = '%B \'%y';
$string['strftime3'] = '%b %Y';
$string['strftime4'] = '%b \'%y';
$string['strftime5'] = '%m/%y';

$string['currentshortdatedefault'] = 'Current short date';
$string['invitationmonth'] = 'Choose a month';
$string['invitationyear'] = 'Choose a year';
$string['uerr_shortdatenotset'] = 'Please choose a month or select "{$a}" checkbox';
$string['uerr_shortdatenotsetrequired'] = 'Please choose a year';
