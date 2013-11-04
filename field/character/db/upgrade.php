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
 * Keeps track of upgrades to the surveyitem character
 *
 * @package    surveyfield
 * @subpackage character
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_character_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013090701) {

        $DB->set_field('survey_character', 'maxlength', null, array('maxlength' => 0));

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013090701, 'surveyfield', 'character');
    }

    if ($oldversion < 2013100601) {

        // Rename field extrarow on table survey_character to position.
        $table = new xmldb_table('survey_character');
        $field = new xmldb_field('extrarow', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'customnumber');

        // Launch rename field extrarow.
        $dbman->rename_field($table, $field, 'position');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013100601, 'surveyfield', 'character');
    }

    if ($oldversion < 2013103101) {

        // Define table survey_age to be renamed to survey_character.
        $table = new xmldb_table('survey_character');

        // Launch rename table for survey_age.
        $dbman->rename_table($table, 'surveyfield_character');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013103101, 'surveyfield', 'character');
    }

    return true;
}
