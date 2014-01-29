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
 * This file keeps track of upgrades to the survey module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
 * xmldb_survey_upgrade
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_survey_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013090501) {

        // Rename field forceediting on table survey to riskyeditdeadline.
        $table = new xmldb_table('survey');
        $field = new xmldb_field('forceediting', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'thankshtmlformat');

        // Launch rename field forceediting.
        $dbman->rename_field($table, $field, 'riskyeditdeadline');

        // Changing precision of field riskyeditdeadline on table survey to (10).
        $table = new xmldb_table('survey');
        $field = new xmldb_field('riskyeditdeadline', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'thankshtmlformat');

        // Launch change of precision for field riskyeditdeadline.
        $dbman->change_field_precision($table, $field);

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013090501, 'survey');
    }

    if ($oldversion < 2013093001) {

        // Define field contentformat to be added to survey_userdata.
        $table = new xmldb_table('survey_userdata');
        $field = new xmldb_field('contentformat', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'content');

        // Conditionally launch add field contentformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013093001, 'survey');
    }

    if ($oldversion < 2013100101) {

        // Define table survey_submissions to be renamed to survey_submission.
        $table = new xmldb_table('survey_submissions');

        // Launch rename table for survey_submissions.
        $dbman->rename_table($table, 'survey_submission');

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013100101, 'survey');
    }

    if ($oldversion < 2013110601) {

        // Define field readaccess to be dropped from survey.
        $table = new xmldb_table('survey');
        $field = new xmldb_field('readaccess');

        // Conditionally launch drop field readaccess.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field editaccess to be dropped from survey.
        $table = new xmldb_table('survey');
        $field = new xmldb_field('editaccess');

        // Conditionally launch drop field editaccess.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field deleteaccess to be dropped from survey.
        $table = new xmldb_table('survey');
        $field = new xmldb_field('deleteaccess');

        // Conditionally launch drop field deleteaccess.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013110601, 'survey');
    }

    if ($oldversion < 2013121101) {

        // Define field parentvalue to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('parentvalue');

        // Conditionally launch drop field parentcontent.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013121101, 'survey');
    }

    if ($oldversion < 2013121801) {

        // Rename field parentcontent on table survey_item to parentvalue.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('parentcontent', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'parentid');

        // Launch rename field parentcontent.
        $dbman->rename_field($table, $field, 'parentvalue');

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013121801, 'survey');
    }

    if ($oldversion < 2014012901) {

        // Rename field hide on table survey_item to hidden.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('hide', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'plugin');

        // Launch rename field hide.
        $dbman->rename_field($table, $field, 'hidden');

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2014012901, 'survey');
    }

    return true;
}
