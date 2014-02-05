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
 * Keeps track of upgrades to the surveyitem pagebreak
 *
 * @package    surveyformat
 * @subpackage pagebreak
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyformat_pagebreak_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013103101) {

        // Define table survey_age to be renamed to survey_pagebreak.
        $table = new xmldb_table('survey_pagebreak');

        // Launch rename table for survey_age.
        $dbman->rename_table($table, 'surveyformat_pagebreak');

        // Survey savepoint reached.
        upgrade_plugin_savepoint(true, 2013103101, 'surveyformat', 'pagebreak');
    }

    if ($oldversion < 2014020504) {
        require_once($CFG->dirroot.'/mod/survey/format/pagebreak/lib.php');

        $sql = 'UPDATE {surveyformat_pagebreak} SET content = ?';
        $params = array(SURVEYFORMAT_PAGEBREAK_CONTENT);
        $DB->execute($sql, $params);
    }

    return true;
}
