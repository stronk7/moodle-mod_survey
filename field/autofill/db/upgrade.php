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
 * Keeps track of upgrades to the surveyitem autofill
 *
 * @package    surveyfield
 * @subpackage autofill
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_autofill_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013092301) {

        // Rename field element01 on table survey_autofill to element01.
        $table = new xmldb_table('survey_autofill');
        $field = new xmldb_field('element_1', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'hiddenfield');

        // Launch rename field element01.
        $dbman->rename_field($table, $field, 'element01');

        // Rename field element01 on table survey_autofill to element02.
        $table = new xmldb_table('survey_autofill');
        $field = new xmldb_field('element_2', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'hiddenfield');

        // Launch rename field element01.
        $dbman->rename_field($table, $field, 'element02');

        // Rename field element01 on table survey_autofill to element03.
        $table = new xmldb_table('survey_autofill');
        $field = new xmldb_field('element_3', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'hiddenfield');

        // Launch rename field element01.
        $dbman->rename_field($table, $field, 'element03');

        // Rename field element01 on table survey_autofill to element04.
        $table = new xmldb_table('survey_autofill');
        $field = new xmldb_field('element_4', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'hiddenfield');

        // Launch rename field element01.
        $dbman->rename_field($table, $field, 'element04');

        // Rename field element01 on table survey_autofill to element05.
        $table = new xmldb_table('survey_autofill');
        $field = new xmldb_field('element_5', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'hiddenfield');

        // Launch rename field element01.
        $dbman->rename_field($table, $field, 'element05');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013092301, 'surveyfield', 'autofill');
    }

    if ($oldversion < 2013100601) {

        // Rename field extrarow on table survey_autofill to position.
        $table = new xmldb_table('survey_autofill');
        $field = new xmldb_field('extrarow', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'customnumber');

        // Launch rename field extrarow.
        $dbman->rename_field($table, $field, 'position');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013100601, 'surveyfield', 'autofill');
    }

    if ($oldversion < 2013103101) {

        // Define table survey_age to be renamed to survey_autofill.
        $table = new xmldb_table('survey_autofill');

        // Launch rename table for survey_age.
        $dbman->rename_table($table, 'surveyfield_autofill');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013103101, 'surveyfield', 'autofill');
    }

    return true;
}

