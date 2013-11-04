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
 * @package    surveyfield
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

    if ($oldversion < 2013100201) {

        // Changing precision of field defaultvalue on table survey_numeric to (20, 10).
        $table = new xmldb_table('survey_numeric');
        $field = new xmldb_field('defaultvalue', XMLDB_TYPE_FLOAT, '20, 10', null, null, null, null, 'indent');

        // Launch change of precision for field defaultvalue.
        $dbman->change_field_precision($table, $field);


        // Changing precision of field defaultvalue on table survey_integer to (20, 10).
        $table = new xmldb_table('survey_numeric');
        $field = new xmldb_field('lowerbound', XMLDB_TYPE_FLOAT, '20, 10', null, null, null, null, 'signed');

        // Launch change of precision for field defaultvalue.
        $dbman->change_field_precision($table, $field);


        // Changing precision of field defaultvalue on table survey_integer to (20, 10).
        $table = new xmldb_table('survey_numeric');
        $field = new xmldb_field('upperbound', XMLDB_TYPE_FLOAT, '20, 10', null, null, null, null, 'lowerbound');

        // Launch change of precision for field defaultvalue.
        $dbman->change_field_precision($table, $field);

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013100201, 'surveyfield', 'numeric');
    }

    if ($oldversion < 2013100601) {

        // Rename field extrarow on table survey_numeric to position.
        $table = new xmldb_table('survey_numeric');
        $field = new xmldb_field('extrarow', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'customnumber');

        // Launch rename field extrarow.
        $dbman->rename_field($table, $field, 'position');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013100601, 'surveyfield', 'numeric');
    }

    if ($oldversion < 2013103101) {

        // Define table survey_age to be renamed to survey_numeric.
        $table = new xmldb_table('survey_numeric');

        // Launch rename table for survey_age.
        $dbman->rename_table($table, 'surveyfield_numeric');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013103101, 'surveyfield', 'numeric');
    }

    return true;
}
