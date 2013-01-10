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
 * Strings for component 'field_numeric', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    survey
 * @subpackage item_numeric
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['parentformat'] = '[12345.67 or 12345,67]';
$string['parentformatdecimal'] = '[12345{$a}67]';
$string['pluginname'] = 'Numeric';
$string['userfriendlypluginname'] = 'Numeric';
$string['defaultvalue_help'] = 'This is the value the remote user will find answered by default';
$string['defaultvalue'] = 'Default';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['digits_help'] = 'The number of digits of the number';
$string['digits'] = 'Digits';
$string['signed_help'] = 'Is the expected number supposed to be signed?';
$string['signed'] = 'Signed value';
$string['decimalautofix'] = 'exceeding or missing decimals will be dropped out or filled with zeroes';
$string['decimals_help'] = 'The number of decimals places of the request number';
$string['decimals'] = 'Decimal positions';
$string['declaredecimalseparator'] = 'decimal separator is supposed to be \'{$a}\'';
$string['lowerbound_help'] = 'The minimum allowed value';
$string['lowerbound'] = 'Minimum value';
$string['upperbound_help'] = 'The maximum allowed value';
$string['upperbound'] = 'Maximum value';
$string['number'] = 'Number ';
$string['hassign'] = 'can be less than zero';
$string['hasminvalue'] = 'has to be greater than {$a}';
$string['hasmaxvalue'] = 'has to be lower than {$a}';
$string['hasdecimals'] = 'has {$a} decimal positions required';
$string['decimalseparator'] = 'Decimal separator';
$string['decimalseparator_desc'] = 'Define here what the remote user is supposed to use to separate decimals in numeric items';
$string['uerr_notanumber'] = 'Entered value is not a number';
$string['uerr_negative'] = 'Entered value uses a not allowed sign';
$string['uerr_lowerthanminimum'] = 'Entered value is lower than the minimum required';
$string['uerr_greaterthanmaximum'] = 'Entered value is greater than the maximum required';
$string['uerr_notinteger'] = 'Entered value is not an integer';
$string['uerr_wrongseparator'] = 'The used decimal separator is wrong. It is supposed to be "{$a}"';
$string['isinteger'] = 'is supposed to be an integer';
$string['parentcontent_isnotanumber'] = 'Parent content is not a number';
$string['defaultsignnotunallowed'] = 'Default uses a not allowed sign';
$string['allowed'] = 'allowed';
$string['default_outofrange'] = 'Default does not fall within the specified range';
$string['default_notanumber'] = 'Default is not a number';
$string['default_notinteger'] = 'Default is not an integer';
