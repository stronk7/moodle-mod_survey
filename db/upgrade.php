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


    if ($oldversion < 2013060903) {
        // Rename field hidehardinfo on table survey_item to hideinstructions.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('hidehardinfo', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'required');

        // Launch rename field hidehardinfo.
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'hideinstructions');
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013060903, 'survey');
    }

    if ($oldversion < 2013062701) {
        // Define field insearchform to be added to survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('insearchform', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'hide');

        // Conditionally launch add field insearchform.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $DB->set_field('survey_item', 'insearchform', 0, array('basicform' => 1));
        $DB->set_field('survey_item', 'insearchform', 1, array('basicform' => 2));


        // Define field limitedaccess to be added to survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('limitedaccess', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'insearchform');

        // Conditionally launch add field limitedaccess.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $DB->set_field('survey_item', 'limitedaccess', 0);

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013062701, 'survey');
    }

    if ($oldversion < 2013062702) {
        // Define field basicform to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('basicform');

        // Conditionally launch drop field basicform.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field advancedsearch to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('advancedsearch');

        // Conditionally launch drop field advancedsearch.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Rename field basicformpage on table survey_item to formpage.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('basicformpage', XMLDB_TYPE_INTEGER, '7', null, XMLDB_NOTNULL, null, '0', 'sortindex');

        // Launch rename field basicformpage.
        $dbman->rename_field($table, $field, 'formpage');


        // Define field advancedformpage to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('advancedformpage');

        // Conditionally launch drop field advancedsearch.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013062702, 'survey');
    }

    return true;
}
