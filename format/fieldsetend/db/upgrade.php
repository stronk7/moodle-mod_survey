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
 * Keeps track of upgrades to the surveyitem fieldsetend
 *
 * @package    surveyitem
 * @subpackage fieldsetend
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyformat_fieldsetend_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013081902) {

        // Define table survey_fieldsetend to be created.
        $table = new xmldb_table('survey_fieldsetend');

        // Adding fields to table survey_fieldsetend.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('surveyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('content', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table survey_fieldsetend.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table survey_fieldsetend.
        $table->add_index('surveyid', XMLDB_INDEX_NOTUNIQUE, array('surveyid'));
        $table->add_index('itemid', XMLDB_INDEX_UNIQUE, array('itemid'));

        // Conditionally launch create table for survey_fieldsetend.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013081902, 'surveyfield', 'fieldsetend');
    }

    return true;
}

