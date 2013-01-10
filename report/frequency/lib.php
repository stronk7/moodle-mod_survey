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
 * Internal library of functions for module survey
 *
 * All the survey specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('SURVEY_GHEIGHT', 500);
define('SURVEY_GWIDTH' , 600);

/*
 * survey_user_can_do_anything
 * @param
 * @return
 */
function surveyreport_displaydistribution($cm, $urvey, $itemid, $submissionscount) {
    global $DB, $OUTPUT;

    $table = new flexible_table('submissionslist');

    $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_REPORT, 'rname' => 'frequency');
    $table->define_baseurl(new moodle_url('view.php', $paramurl));

    $tablecolumns = array();
    $tablecolumns[] = 'answer';
    $tablecolumns[] = 'absolute';
    $tablecolumns[] = 'percentage';
    $table->define_columns($tablecolumns);

    $tableheaders = array();
    $tableheaders[] = get_string('content', 'surveyreport_frequency');
    $tableheaders[] = get_string('absolute', 'surveyreport_frequency');
    $tableheaders[] = get_string('percentage', 'surveyreport_frequency');
    $table->define_headers($tableheaders);

    // $table->collapsible(true);
    $table->sortable(true, 'content', 'ASC'); // sorted by sortindex by default
    $table->no_sorting('percentage');

    // $table->column_style('actions', 'width', '60px');
    // $table->column_style('actions', 'align', 'center');
    $table->column_class('content', 'picture');
    $table->column_class('absolute', 'fullname');
    $table->column_class('percentage', 'actions');

    // general properties for the whole table
    $table->set_attribute('cellpadding', 5);
    $table->set_attribute('id', 'submissions');
    $table->set_attribute('class', 'generaltable');
    $table->set_attribute('align', 'center');
    // $table->set_attribute('width', '90%');
    $table->setup();

    /*****************************************************************************/
    $dummyitem = survey_get_item($itemid);

    $paramurl = array();
    $paramurl['id'] = $cm->id;
    $paramurl['tab'] = SURVEY_TABSUBMISSIONS;
    $basepath = new moodle_url('view.php', $paramurl);

    list($where, $params) = $table->get_sql_where();

    $sql = 'SELECT *, count(ud.id) as absolute
            FROM {survey_userdata} ud
            WHERE ud.itemid = :itemid
            GROUP BY ud.content';

    if ($table->get_sql_sort()) {
        $sql .= ' ORDER BY '.$table->get_sql_sort();
    } else {
        $sql .= ' ORDER BY ud.content';
    }

    $params['itemid'] = $itemid;

    $answers = $DB->get_recordset_sql($sql, $params, $table->get_sql_sort());

    $decimalseparator = get_string('decsep', 'langconfig');
    foreach ($answers as $answer) {
        $tablerow = array();

        // answer
        $itemvalue = new StdClass();
        $itemvalue->id = $answer->id;
        $itemvalue->content = $answer->content;
        $tablerow[] = $dummyitem->userform_db_to_export($itemvalue);

        // absolute
        $tablerow[] = $answer->absolute;

        // percentage

        $tablerow[] = number_format(100*$answer->absolute/$submissionscount, 2, $decimalseparator, ' ').'%';

        // add row to the table
        $table->add_data($tablerow);
    }
    $answers->close();

    $table->summary = get_string('submissionslist', 'survey');
    $table->print_html();
}


//     echo $OUTPUT->heading('And now the distribution of the answers to the selected item');
//
//     $t = new html_table();survey_get_sid_field_content
//
//     $cell = new html_table_cell();
//     $cell->text = ($record);
//     $cell->colspan = 2;
//
//     $row1 = new html_table_row();
//     $row1->cells[] = $cell1;
//
//     /************************************/
//
//     $cell1 = new html_table_cell();
//     $cell1->text = 'Hermione Granger';
//
//     $cell2 = new html_table_cell();
//     $cell2->text = '100 %';
//
//     $row2 = new html_table_row();
//     $row2->cells = array($cell2, $cell3);
//
//     $t->data = array($row1, $row2);
//
//     echo html_writer::table($t);

/**
 * @global object
 * @global int
 * @global int
 * @param string $url
 */
function survey_print_graph($url) {
    global $CFG;

    if (empty($CFG->gdversion)) {
        echo "(".get_string("gdneed").")";
    } else {
        echo '<div class="reportsummary">'.
            '<img class="resultgraph" height="'.SURVEY_GHEIGHT.
            '" width="'.SURVEY_GWIDTH.
            '" src="'.$CFG->wwwroot.'/mod/survey/report/frequency/graph.php?'.$url.
            '" alt="'.get_string('pluginname', 'surveyreport_frequency').'" />'.
            '</div>';
    }
}