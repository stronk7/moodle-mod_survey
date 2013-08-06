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
 * Keeps track of upgrades to the surveyitem select
 *
 * @package    surveyitem
 * @subpackage select
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_select_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013062701) {
        // Define field downloadformat to be added to survey_date.
        $table = new xmldb_table('survey_select');
        $field = new xmldb_field('downloadformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'defaultvalue');

        // Conditionally launch add field downloadformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // set default
        $DB->set_field('survey_select', 'downloadformat', 0);

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013062701, 'surveyfield', 'select');
    }

    if ($oldversion < 2013071801) {

        // Changing precision of field defaultvalue on table survey_select to (32).
        $table = new xmldb_table('survey_select');
        $field = new xmldb_field('defaultvalue', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'defaultvalue_sid');

        // Launch change of precision for field defaultvalue.
        $dbman->change_field_precision($table, $field);

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013071801, 'surveyfield', 'select');
    }

    if ($oldversion < 2013073001) {

        // Define field content_sid to be dropped from survey_select.
        $table = new xmldb_table('survey_select');
        $field = new xmldb_field('options_sid');

        // Conditionally launch drop field content_sid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field content_sid to be dropped from survey_select.
        $table = new xmldb_table('survey_select');
        $field = new xmldb_field('labelother_sid');

        // Conditionally launch drop field content_sid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field content_sid to be dropped from survey_select.
        $table = new xmldb_table('survey_select');
        $field = new xmldb_field('defaultvalue_sid');

        // Conditionally launch drop field content_sid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013073001, 'surveyfield', 'select');
    }

    if ($oldversion < 2013080301) {

        // Define field downloadformat to be added to survey_select.
        $table = new xmldb_table('survey_select');
        $field = new xmldb_field('downloadformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'defaultvalue');

        // Conditionally launch add field downloadformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013080301, 'surveyfield', 'select');
    }

    return true;
}
