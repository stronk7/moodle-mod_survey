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
 * Keeps track of upgrades to the surveyitem datetime
 *
 * @package    surveyitem
 * @subpackage datetime
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_datetime_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013091701) {

        // Define field step to be added to survey_datetime.
        $table = new xmldb_table('survey_datetime');
        $field = new xmldb_field('step', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'indent');

        // Conditionally launch add field step.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013091701, 'surveyfield', 'datetime');
    }

    if ($oldversion < 2013100601) {

        // Rename field extrarow on table survey_age to position.
        $table = new xmldb_table('survey_datetime');
        $field = new xmldb_field('extrarow', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'customnumber');

        // Launch rename field extrarow.
        $dbman->rename_field($table, $field, 'position');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013100601, 'surveyfield', 'datetime');
    }

    return true;
}
