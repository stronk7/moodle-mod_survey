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
 * Strings for component 'field_datetime', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    survey
 * @subpackage datetime
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['parentcontentinvaliddate_err'] = 'Provided data is not a regular date';
$string['parentcontentinvalidtime_err'] = 'Provided data is not a regular time';
$string['parentcontentdateoutofrange_err'] = 'Provided date anf time is out of the range requested to the choosen item';
$string['pluginname'] = 'Date and time';
$string['userfriendlypluginname'] = 'Date and time [dd/mm/yyyy;hh:mm]';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['defaultvalue_help'] = 'This is the date and time the remote user will find answered by default. The default for this type of question is mandatory. If "Current date and time" is choosed as default, boundaries are not supposed to apply.';
$string['defaultvalue'] = 'Default';
$string['lowerbound_help'] = 'The lower date and time the user will be allowed to enter';
$string['lowerbound'] = 'Lower bound';
$string['upperbound_help'] = 'The upper date and time the user will be allowed to enter';
$string['upperbound'] = 'Upper bound';
$string['outofrangedefault'] = 'Default does not fall within the specified range';
$string['restriction_lowerupper'] = 'Answer is supposed to fit between {$a->lowerbound} and {$a->upperbound}';
$string['restriction_lower'] = 'Answer is supposed to be greater than {$a}';
$string['restriction_upper'] = 'Answer is supposed to be lower than {$a}';
$string['uerr_lowerthanminimum'] = 'Provided value is lower than minimum allowed';
$string['uerr_greaterthanmaximum'] = 'Provided value is greater than maximum allowed';
$string['customdefault'] = 'Custom';
$string['downloadformat'] = 'Download format';
$string['downloadformat_help'] = 'Choose the format of the answer as it appear once user attempts are downloaded';
$string['lowerequaltoupper'] = 'Lower and upper bounds need to be different';
$string['strftime01'] = '%A, %d %B %Y, %H:%M';
$string['strftime02'] = '%A, %d %B %Y, %I:%M %p';
$string['strftime03'] = '%a, %d %b %Y, %H:%M';
$string['strftime04'] = '%a, %d %b %Y, %I:%M %p';
$string['strftime05'] = '%d %B %Y, %H:%M';
$string['strftime06'] = '%d %B %Y, %I:%M %p';
$string['strftime07'] = '%d %b, %H:%M';
$string['strftime08'] = '%d %b, %I:%M %p';
$string['strftime09'] = '%d/%m/%y, %H:%M';
$string['strftime10'] = '%d/%m/%y, %I:%M %p';
$string['strftime11'] = '%d/%m/%Y, %H:%M';
$string['strftime12'] = '%d/%m/%Y, %I:%M %p';

$string['currentdatetimedefault'] = 'Current date and time';
$string['invitationday'] = 'Choose a day';
$string['invitationmonth'] = 'Choose a month';
$string['invitationyear'] = 'Choose a year';
$string['invitationhour'] = 'Choose an hour';
$string['invitationminute'] = 'Choose a minute';
$string['uerr_datetimenotset'] = 'Please define a date and time or select the "{$a}" checkbox';
$string['uerr_datetimenotsetrequired'] = 'Please define a date and time';
