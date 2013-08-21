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
 * Keeps track of upgrades to the surveyitem integer
 *
 * @package    surveyitem
 * @subpackage integer
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_integer_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013081901) {

        // Define field content to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('content', XMLDB_TYPE_TEXT, null, null, null, null, null, 'itemid');

        // Conditionally launch add field content.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field contentformat to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('contentformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'content');

        // Conditionally launch add field contentformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field customnumber to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('customnumber', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'contentformat');

        // Conditionally launch add field customnumber.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field extrarow to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('extrarow', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'customnumber');

        // Conditionally launch add field extrarow.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field extranote to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('extranote', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'extrarow');

        // Conditionally launch add field extranote.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field required to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('required', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'extranote');

        // Conditionally launch add field required.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field hideinstructions to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('hideinstructions', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'required');

        // Conditionally launch add field hideinstructions.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field variable to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('variable', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'hideinstructions');

        // Conditionally launch add field variable.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field indent to be added to survey_integer.
        $table = new xmldb_table('survey_integer');
        $field = new xmldb_field('indent', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'variable');

        // Conditionally launch add field indent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013081901, 'surveyfield', 'integer');
    }

    return true;
}
