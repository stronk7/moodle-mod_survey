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
 * Keeps track of upgrades to the surveyitem fileupload
 *
 * @package    surveyfield
 * @subpackage fileupload
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyfield_fileupload_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013100601) {

        // Rename field extrarow on table survey_fileupload to position.
        $table = new xmldb_table('survey_fileupload');
        $field = new xmldb_field('extrarow', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'customnumber');

        // Launch rename field extrarow.
        $dbman->rename_field($table, $field, 'position');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013100601, 'surveyfield', 'fileupload');
    }

    if ($oldversion < 2013103101) {

        // Define table survey_age to be renamed to survey_fileupload.
        $table = new xmldb_table('survey_fileupload');

        // Launch rename table for survey_age.
        $dbman->rename_table($table, 'surveyfield_fileupload');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013103101, 'surveyfield', 'fileupload');
    }

    if ($oldversion < 2013121201) {

        // Rename field filetypes on table surveyfield_fileupload to allowedtypes.
        $table = new xmldb_table('surveyfield_fileupload');
        $field = new xmldb_field('filetypes', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'maxbytes');

        // Launch rename field filetypes.
        $dbman->rename_field($table, $field, 'allowedtypes');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013121201, 'surveyfield', 'fileupload');
    }

    return true;
}
