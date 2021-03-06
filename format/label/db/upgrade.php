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
 * Keeps track of upgrades to the surveyitem label
 *
 * @package    surveyformat
 * @subpackage label
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyformat_label_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013091201) {

        // Define field label to be dropped from survey_label.
        $table = new xmldb_table('survey_label');
        $field = new xmldb_field('leftlabel');

        // Conditionally launch drop field label.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field label to be added to survey_label.
        $table = new xmldb_table('survey_label');
        $field = new xmldb_field('leftlabel', XMLDB_TYPE_TEXT, null, null, null, null, null, 'indent');

        // Conditionally launch add field label.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field labelformat to be added to survey_label.
        $table = new xmldb_table('survey_label');
        $field = new xmldb_field('leftlabelformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'leftlabel');

        // Conditionally launch add field labelformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013091201, 'surveyformat', 'label');
    }

    if ($oldversion < 2013091801) {

        // Define field label to be dropped from survey_label.
        $table = new xmldb_table('survey_label');
        $field = new xmldb_field('leftlabelformat');

        // Conditionally launch drop field label.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013091801, 'surveyformat', 'label');
    }

    if ($oldversion < 2013091802) {

        // Define field fullwidth to be added to survey_label.
        $table = new xmldb_table('survey_label');
        $field = new xmldb_field('fullwidth', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'indent');

        // Conditionally launch add field fullwidth.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013091802, 'surveyformat', 'label');
    }

    if ($oldversion < 2013103101) {

        // Define table survey_age to be renamed to survey_label.
        $table = new xmldb_table('survey_label');

        // Launch rename table for survey_age.
        $dbman->rename_table($table, 'surveyformat_label');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013103101, 'surveyformat', 'label');
    }

    return true;
}

