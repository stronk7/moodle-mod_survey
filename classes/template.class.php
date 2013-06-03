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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
 * The base class representing a field
 */
class mod_survey_template {
    /*
     * $survey: the record of this survey
     */
    public $survey = null;

    /*
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /*
     * get_table_structure
     * @param $tablename, $dropid=true
     * @return
     */
    public function get_table_structure($tablename, $dropid=true) {
        global $DB;

        $dbman = $DB->get_manager();
        if ($dbman->table_exists($tablename)) {
            $dbstructure = array();

            if ($dbfields = $DB->get_columns($tablename)) {
                foreach ($dbfields as $dbfield) {
                    $dbstructure[] = $dbfield->name;
                }
            }

            if ($dropid) {
                array_shift($dbstructure); // drop the first item: ID
            }
            return $dbstructure;
        } else {
            return false;
        }
    }
}