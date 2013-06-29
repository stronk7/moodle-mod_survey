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
 * Keeps track of upgrades to the surveyitem numeric
 *
 * @package    surveyitem
 * @subpackage numeric
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_numeric_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013062401) {
        // Changing precision of field lowerbound on table survey_numeric to ().
        $table = new xmldb_table('survey_numeric');
        $field = new xmldb_field('lowerbound', XMLDB_TYPE_FLOAT, null, null, null, null, null, 'signed');

        // Launch change of precision for field lowerbound.
        $dbman->change_field_precision($table, $field);


        // Changing precision of field upperbound on table survey_numeric to ().
        $table = new xmldb_table('survey_numeric');
        $field = new xmldb_field('upperbound', XMLDB_TYPE_FLOAT, null, null, null, null, null, 'lowerbound');

        // Launch change of precision for field upperbound.
        $dbman->change_field_precision($table, $field);

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013062401, 'surveyfield_numeric', 'survey');
    }

    if ($oldversion < 2013062501) {
        // Changing precision of field defaultvalue on table survey_numeric to ().
        $table = new xmldb_table('survey_numeric');
        $field = new xmldb_field('defaultvalue', XMLDB_TYPE_FLOAT, null, null, null, null, null, 'defaultvalue_sid');

        // Launch change of precision for field lowerbound.
        $dbman->change_field_precision($table, $field);

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013062501, 'surveyfield_numeric', 'survey');
    }

    return true;
}
