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
 * Keeps track of upgrades to the surveyitem fieldset
 *
 * @package    surveyitem
 * @subpackage fieldset
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyformat_fieldset_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013042901) {
        // Changing precision of field fslabel on table survey_fieldset to 128.
        $table = new xmldb_table('survey_fieldset');
        $field = new xmldb_field('fslabel', XMLDB_TYPE_CHAR, '128', null, null, null, null, 'fslabel_sid');

        // Launch change of precision for field fslabel.
        $dbman->change_field_precision($table, $field);


        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013042901, 'surveyformat', 'fieldset');
    }

    if ($oldversion < 2013072301) {

        // Rename field fslabel_sid on table survey_fieldset to label_sid.
        $table = new xmldb_table('survey_fieldset');
        $field = new xmldb_field('fslabel_sid', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'itemid');

        // Launch rename field fslabel_sid.
        $dbman->rename_field($table, $field, 'label_sid');


        // Rename field fslabel on table survey_fieldset to label.
        $table = new xmldb_table('survey_fieldset');
        $field = new xmldb_field('fslabel', XMLDB_TYPE_CHAR, '128', null, null, null, null, 'fslabel_sid');

        // Launch rename field fslabel.
        $dbman->rename_field($table, $field, 'label');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013072301, 'surveyformat', 'fieldset');
    }

    if ($oldversion < 2013073001) {

        // Define field content_sid to be dropped from survey_fieldset.
        $table = new xmldb_table('survey_fieldset');
        $field = new xmldb_field('label_sid');

        // Conditionally launch drop field content_sid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013073001, 'surveyfield', 'fieldset');
    }

    if ($oldversion < 2013081901) {

        // Define field content to be added to survey_fieldset.
        $table = new xmldb_table('survey_fieldset');
        $field = new xmldb_field('content', XMLDB_TYPE_TEXT, null, null, null, null, null, 'itemid');

        // Conditionally launch add field content.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013081901, 'surveyfield', 'fieldset');
    }

    return true;
}

