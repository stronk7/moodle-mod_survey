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
 * Keeps track of upgrades to the surveyitem shortdate
 *
 * @package    surveyitem
 * @subpackage shortdate
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_shortdate_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013052302) {

        // Define field downloadformat to be added to survey_shortdate.
        $table = new xmldb_table('survey_shortdate');
        $field = new xmldb_field('downloadformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'defaultvalue');

        // Conditionally launch add field downloadformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013052302, 'surveyfield_shortdate', 'survey');
    }

    if ($oldversion < 2013052401) {

        // Changing type of field downloadformat on table survey_shortdate to char.
        $table = new xmldb_table('survey_shortdate');
        $field = new xmldb_field('downloadformat', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'defaultvalue');

        // Launch change of type for field downloadformat.
        $dbman->change_field_type($table, $field);

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013052401, 'surveyfield_shortdate', 'survey');
    }

    return true;
}
