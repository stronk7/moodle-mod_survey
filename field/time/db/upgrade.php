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
 * Keeps track of upgrades to the surveyitem time
 *
 * @package    surveyitem
 * @subpackage time
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_time_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013052302) {
        // Define field downloadformat to be added to survey_time.
        $table = new xmldb_table('survey_time');
        $field = new xmldb_field('downloadformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'defaultvalue');

        // Conditionally launch add field downloadformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013052302, 'surveyfield', 'time');
    }

    if ($oldversion < 2013060402) {
        // Define field step to be dropped from survey_time.
        $table = new xmldb_table('survey_time');
        $field = new xmldb_field('downloadformat');

        // Conditionally launch drop field step.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field step to be added to survey_time.
        $table = new xmldb_table('survey_time');
        $field = new xmldb_field('step', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'itemid');

        // Conditionally launch add field step.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013060402, 'surveyfield', 'time');
    }

    if ($oldversion < 2013061701) {
        // Define field downloadformat to be added to survey_time.
        $table = new xmldb_table('survey_time');
        $field = new xmldb_field('downloadformat', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'defaultvalue');

        // Conditionally launch add field downloadformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013061701, 'surveyfield', 'time');
    }

    if ($oldversion < 2013061702) {
        // Define field rangetype to be dropped from survey_time.
        $table = new xmldb_table('survey_time');
        $field = new xmldb_field('rangetype');

        // Conditionally launch drop field rangetype.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013061702, 'surveyfield', 'time');
    }

    return true;
}
