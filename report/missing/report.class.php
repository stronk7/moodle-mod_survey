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
 * Defines the version of survey autofill subplugin
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    surveyreport
 * @subpackage count
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class report_missing {

    /*
     * cm
     */
    public $cm = null;

    /*
     * coursecontext
     */
    public $coursecontext = 0;

    /*
     * survey
     */
    public $survey = null;

    /*
     * outputtable
     */
    public $outputtable = null;

    /*
     * Class constructor
     */
    public function __construct($cm, $survey) {
        global $COURSE;

        $this->cm = $cm;
        $this->coursecontext = context_course::instance($COURSE->id);
        $this->survey = $survey;
        $this->outputtable = new flexible_table('missingattempts');
        $this->setup_outputtable();
    }

    /*
     * setup_outputtable
     */
    public function setup_outputtable() {
        $paramurl = array('id' => $this->cm->id, 'rname' => 'missing');
        $this->outputtable->define_baseurl(new moodle_url('view_report.php', $paramurl));

        $tablecolumns = array();
        $tablecolumns[] = 'picture';
        $tablecolumns[] = 'fullname';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = '';
        $tableheaders[] = get_string('fullname');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(true, 'lastname', 'ASC'); // sorted by lastname by default

        $this->outputtable->column_class('picture', 'picture');
        $this->outputtable->column_class('fullname', 'fullname');

        // $this->outputtable->initialbars(true);

        // hide the same info whether in two consecutive rows
        $this->outputtable->column_suppress('picture');
        $this->outputtable->column_suppress('fullname');

        // general properties for the whole table
        $this->outputtable->summary = get_string('submissionslist', 'survey');
        $this->outputtable->set_attribute('cellpadding', '5');
        $this->outputtable->set_attribute('id', 'userattempts');
        $this->outputtable->set_attribute('class', 'generaltable');
        // $this->outputtable->set_attribute('width', '90%');
        $this->outputtable->setup();
    }

    /*
     * fetch_information
     */
    public function fetch_information() {
        global $CFG, $DB, $COURSE, $OUTPUT;

        $roles = get_roles_used_in_context($this->coursecontext);
        // if (isset($survey->guestisallowed)) {
        //     $guestrole = get_guest_role();
        //     $roles[$guestrole->id] = $guestrole;
        // }
        $role = array_keys($roles);
        $sql = 'SELECT DISTINCT '.user_picture::fields('u').', s.attempts
                FROM {user} u
                JOIN (SELECT *
                        FROM {role_assignments}
                        WHERE contextid = '.$this->coursecontext->id.'
                          AND roleid IN ('.implode(',', $role).')) ra ON u.id = ra.userid
                LEFT JOIN (SELECT *, count(s.id) as attempts
                             FROM {survey_submission} s
                             WHERE s.surveyid = :surveyid
                             GROUP BY s.userid) s ON s.userid = u.id
		        WHERE ISNULL(s.id)';
        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort();
        } else {
            $sql .= ' ORDER BY s.lastname';
        }
        $whereparams = array('surveyid' => $this->survey->id);
        $usersubmissions = $DB->get_recordset_sql($sql, $whereparams);

        foreach ($usersubmissions as $usersubmission) {
            $tablerow = array();

            // picture
            $tablerow[] = $OUTPUT->user_picture($usersubmission, array('courseid' => $COURSE->id));

            // user fullname
            $tablerow[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$usersubmission->id.'&amp;course='.$COURSE->id.'">'.fullname($usersubmission).'</a>';

            // add row to the table
            $this->outputtable->add_data($tablerow);
        }

        $usersubmissions->close();
    }

    /*
     * output_information
     */
    public function output_information() {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('pluginname', 'surveyreport_missing'));
        $this->outputtable->print_html();
    }
}