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
        upgrade_plugin_savepoint(true, 2013042901, 'surveyformat_fieldset', 'survey');
    }

    return true;
}

