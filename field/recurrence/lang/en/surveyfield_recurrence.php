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
 * Strings for component 'field_recurrence', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    survey
 * @subpackage recurrence
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['currentrecurrencedefault'] = 'Current recurrence';
$string['customdefault'] = 'Custom';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['defaultvalue_help'] = 'This is the recurrence the remote user will find answered by default. The default for this type of question is mandatory. If "Current recurrence" is choosed as default, boundaries are not supposed to apply.';
$string['defaultvalue'] = 'Default';
$string['downloadformat_help'] = 'Choose the format of the answer as it appear once user attempts are downloaded';
$string['downloadformat'] = 'Download format';
$string['invitationday'] = 'Choose a day';
$string['invitationmonth'] = 'Choose a month';
$string['invitationyear'] = 'Choose a year';
$string['lowerbound_help'] = 'The lowest recurrence the user is allowed to enter';
$string['lowerbound'] = 'Lower bound';
$string['lowerequaltoupper'] = 'Lower and upper bounds must be different';
$string['notvaliddefault'] = 'Incorrect value entered';
$string['notvalidlowerbound'] = 'Incorrect value entered';
$string['notvalidupperbound'] = 'Incorrect value entered';
$string['outofrangedefault'] = 'Default does not fall within the specified range';
$string['outofexternalrangedefault'] = 'Default does not fall within the specified range (see "{$a}" help)';
$string['parentcontentinvalidrecurrence_err'] = 'Provided recurrence is not a regular recurrence';
$string['parentcontentrecurrenceoutofrange_err'] = 'Provided recurrence is out of the range requested to the choosen item';
$string['pluginname'] = 'Recurrence';
$string['restriction_lowerupper'] = 'Answer is supposed to fit between {$a->lowerbound} and {$a->upperbound}';
$string['restriction_upperlower'] = 'Answer is supposed to be lower-equal than {$a->lowerbound} or greater-equal than {$a->upperbound}';
$string['strftime1'] = '%d %B';
$string['strftime2'] = '%d %b';
$string['strftime3'] = '%d/%m';
$string['uerr_outofexternalrange'] = 'Provided value is supposed to be lower-equal than {$a->lowerbound} or greater-equal than {$a->upperbound}';
$string['uerr_outofinternalrange'] = 'Provided value does not fall within the specified range';
$string['uerr_recurrencenotset'] = 'Please define a recurrence or select the "{$a}" checkbox';
$string['uerr_recurrencenotsetrequired'] = 'Please define a recurrence';
$string['upperbound_help'] = 'The biggest recurrence the user is allowed to enter.<br /><br />Upper and lower bound define a range.<br />If "lower bound" is lower than "upper bound" the user is forced to enter a value falling into the range.<br />If "lower bound" is greater than "upper bound" the user input is forced out from the range. i.e. the user input is supposed to be lower-equal than the lower bound OR grater-equal than the upper bound.';
$string['upperbound'] = 'Upper bound';
$string['userfriendlypluginname'] = 'Recurrence [dd/mm]';
