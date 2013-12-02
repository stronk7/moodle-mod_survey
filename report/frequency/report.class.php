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

require_once($CFG->dirroot.'/mod/survey/classes/reportbase.class.php');

class report_frequency extends mod_survey_reportbase {
    /*
     * outputtable
     */
    public $outputtable = null;

    /*
     * setup
     */
    function setup($hassubmissions) {
        $this->hassubmissions = $hassubmissions;
        $this->outputtable = new flexible_table('submissionslist');
        $this->setup_outputtable();
    }

    /*
     * setup_outputtable
     */
    public function setup_outputtable() {
        $paramurl = array('id' => $this->cm->id, 'rname' => 'frequency');
        $this->outputtable->define_baseurl(new moodle_url('view_report.php', $paramurl));

        $tablecolumns = array();
        $tablecolumns[] = 'answer';
        $tablecolumns[] = 'absolute';
        $tablecolumns[] = 'percentage';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('content', 'surveyreport_frequency');
        $tableheaders[] = get_string('absolute', 'surveyreport_frequency');
        $tableheaders[] = get_string('percentage', 'surveyreport_frequency');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(true, 'content', 'ASC'); // sorted by content by default
        $this->outputtable->no_sorting('percentage');

        $this->outputtable->column_class('content', 'content');
        $this->outputtable->column_class('absolute', 'absolute');
        $this->outputtable->column_class('percentage', 'percentage');

        // $this->outputtable->initialbars(true);

        // hide the same info whether in two consecutive rows
        $this->outputtable->column_suppress('picture');
        $this->outputtable->column_suppress('fullname');

        // general properties for the whole table
        $this->outputtable->summary = get_string('submissionslist', 'survey');
        $this->outputtable->set_attribute('cellpadding', '5');
        $this->outputtable->set_attribute('id', 'submissions');
        $this->outputtable->set_attribute('class', 'generaltable');
        $this->outputtable->set_attribute('align', 'center');
        // $this->outputtable->set_attribute('width', '90%');
        $this->outputtable->setup();
    }

    /*
     * stop_if_textareas_only
     */
    public function stop_if_textareas_only() {
        global $CFG, $DB, $OUTPUT;

        $where = 'surveyid = :surveyid AND type = :type AND advanced = :advanced AND hide = :hide AND plugin <> :plugin';

        $params = array();
        $params['surveyid'] = $this->survey->id;
        $params['type'] = SURVEY_TYPEFIELD;
        $params['advanced'] = 0;
        $params['hide'] = 0;
        $params['plugin'] = 'textarea';

        $countfields = $DB->count_records_select('survey_item', $where, $params);

        if (!$countfields) {
            $a = get_string('userfriendlypluginname', 'surveyfield_textarea');
            echo $OUTPUT->box(get_string('textareasarenotallowed', 'surveyreport_frequency', $a));
            $url = $CFG->wwwroot.'/mod/survey/view.php?s='.$this->survey->id;
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();

            die();
        }
    }

    /*
     * fetch_data
     *
     * @param int $itemid
     * @param int $submissionscount
     */
    public function fetch_data($itemid, $submissionscount) {
        global $DB;

        list($where, $whereparams) = $this->outputtable->get_sql_where();

        $sql = 'SELECT *, count(ud.id) as absolute
                FROM {survey_userdata} ud
                WHERE ud.itemid = :itemid
                GROUP BY ud.content';

        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort();
        } else {
            $sql .= ' ORDER BY ud.content';
        }

        $whereparams['itemid'] = $itemid;

        $answers = $DB->get_recordset_sql($sql, $whereparams, $this->outputtable->get_sql_sort());

        $dummyitem = survey_get_item($itemid);

        $decimalseparator = get_string('decsep', 'langconfig');
        $counted = 0;
        foreach ($answers as $answer) {
            $tablerow = array();

            // answer
            $itemvalue = new stdClass();
            $itemvalue->id = $answer->id;
            $itemvalue->content = $answer->content;
            $tablerow[] = $dummyitem->userform_db_to_export($itemvalue);

            // absolute
            $tablerow[] = $answer->absolute;
            $counted += $answer->absolute;

            // percentage
            $tablerow[] = number_format(100*$answer->absolute/$submissionscount, 2, $decimalseparator, ' ').'%';

            // add row to the table
            $this->outputtable->add_data($tablerow);
        }

        // each item may be unanswered because it was not allowed by its ancestors
        // if ($counted < $submissionscount) {
        //     $tablerow = array();
        //
        //     // answer
        //     $tablerow[] = get_string('answernotpresent', 'surveyreport_frequency');
        //
        //     // absolute
        //     $tablerow[] = ($submissionscount - $counted);
        //
        //     // percentage
        //     $tablerow[] = number_format(100*($submissionscount - $counted)/$submissionscount, 2, $decimalseparator, ' ').'%';
        //
        //     // add row to the table
        //     $this->outputtable->add_data($tablerow);
        // }

        $answers->close();
    }

    /*
     * output_data
     *
     * @param string $url
     */
    public function output_data($url) {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('pluginname', 'surveyreport_count'));
        $this->outputtable->print_html();
        if ($this->outputtable->started_output) {
            $this->print_graph($url);
        }
    }

    /*
     * @param string $url
     */
    public function print_graph($graphurl) {
        global $CFG;

        if (empty($CFG->gdversion)) {
            echo '('.get_string('gdneed').')';
        } else {
            $imgparams = array();
            $imgparams['class'] = 'resultgraph';
            $imgparams['height'] = SURVEY_GHEIGHT;
            $imgparams['width'] = SURVEY_GWIDTH;
            $imgparams['src'] = $graphurl;
            $imgparams['alt'] = get_string('pluginname', 'surveyreport_frequency');

            $content = html_writer::start_tag('div', array('class' => 'centerpara'));
            $content .= html_writer::empty_tag('img', $imgparams);
            $content .= html_writer::end_tag('div');
            echo $content;
        }
    }
}