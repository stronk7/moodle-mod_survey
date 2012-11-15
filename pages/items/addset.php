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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') OR die();

require_once('addset_form.php');

$a = new stdClass();
$a->modulepresets = get_string('modulepresets', 'survey');
$a->userpresets = get_string('userpresets', 'survey');

$a->pluginstab = get_string('tabpluginsname', 'survey');
$a->presetstab = get_string('tabpresetsname', 'survey');
$a->presetexport = get_string('tabpresetspage2', 'survey');
$a->none = get_string('notanyset', 'survey');
$a->delete = get_string('delete', 'survey');
$a->actionoverother = get_string('actionoverother', 'survey');
$a->itemset = get_string('itemset', 'survey');

$message = get_string('additemsetinfo', 'survey', $a);
echo $OUTPUT->box($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

$mform->display();
