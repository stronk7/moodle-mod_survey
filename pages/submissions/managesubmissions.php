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


defined('MOODLE_INTERNAL') OR die();

require_once($CFG->libdir.'/tablelib.php');
$context = context_module::instance($cm->id);

switch ($action) {
    case SURVEY_NOACTION:
    case SURVEY_EDITSURVEY:
    case SURVEY_VIEWSURVEY:
        break;
    case SURVEY_DELETESURVEY:
        survey_manage_submission_deletion($cm, $confirm, $submissionid);
        break;
    case SURVEY_DELETEALLSURVEYS:
        survey_manage_all_surveys_deletion($confirm, $survey->id);
        break;
    default:
        echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
        echo 'I have $action = '.$action.'<br />';
        echo 'and the right "case" is missing<br />';
}

$canreadallsubmissions = survey_user_can_read_all_submissions($cm);
$caneditallsubmissions = survey_user_can_edit_all_submissions($cm);
$candeleteallsubmissions = survey_user_can_delete_all_submissions($cm);

$table = new flexible_table('submissionslist');

$paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_MANAGE);
if ($searchfields_get) { // declared in beforepage.php - it is a string
    $paramurl['searchquery'] = $searchfields_get;
}
$table->define_baseurl(new moodle_url('view.php', $paramurl));

$tablecolumns = array();
$tablecolumns[] = 'picture';
$tablecolumns[] = 'fullname';
$tablecolumns[] = 'status';
$tablecolumns[] = 'timecreated';
if (!$survey->history) {
    $tablecolumns[] = 'timemodified';
}
$tablecolumns[] = 'actions';
$table->define_columns($tablecolumns);

$tableheaders = array();
$tableheaders[] = '';
$tableheaders[] = get_string('fullname');
$tableheaders[] = get_string('status');
$tableheaders[] = get_string('timecreated', 'survey');
if (!$survey->history) {
    $tableheaders[] = get_string('timemodified', 'survey');
}
$tableheaders[] = get_string('actions');
$table->define_headers($tableheaders);

//$table->collapsible(true);
$table->sortable(true, 'sortindex', 'ASC');//sorted by sortindex by default
$table->no_sorting('actions');

//$table->column_style('actions', 'width', '60px');
//$table->column_style('actions', 'align', 'center');
$table->column_class('picture', 'picture');
$table->column_class('fullname', 'fullname');
$table->column_class('status', 'status');
$table->column_class('timecreated', 'timecreated');
if (!$survey->history) {
    $table->column_class('timemodified', 'timemodified');
}
$table->column_class('actions', 'actions');

$table->initialbars(true);

// ometti la casella se duplica la precedente
$table->column_suppress('picture');
$table->column_suppress('fullname');

// general properties for the whole table
$table->set_attribute('cellpadding', 5);
$table->set_attribute('id', 'submissions');
$table->set_attribute('class', 'generaltable');
$table->set_attribute('align', 'center');
//$table->set_attribute('width', '90%');
$table->setup();

/******************************************************************************/
if ($survey->readaccess == SURVEY_NONE) {
    $message = get_string('noreadaccess', 'survey');
    echo $OUTPUT->box($message, 'notice centerpara');
    echo $OUTPUT->footer();
    die;
}

$status = array(SURVEY_STATUSINPROGRESS => get_string('statusinprogress', 'survey'),
                SURVEY_STATUSCLOSED => get_string('statusclosed', 'survey'));
$firsticontitle = array();
$firsticontitle[0] = get_string('edit');
$firsticontitle[1] = get_string('add');
$firsticonicon = array();
$firsticonicon[0] = 't/edit';
$firsticonicon[1] = 't/copy';
$deletetitle = get_string('delete');
$neverstring = get_string('never');
$restrictedaccess = get_string('restrictedaccess', 'survey');

$paramurl = array();
$paramurl['id'] = $cm->id;
$paramurl['tab'] = SURVEY_TABSUBMISSIONS;
$basepath = new moodle_url('view.php', $paramurl);

list($where, $params) = $table->get_sql_where();

$params['surveyid'] = $survey->id;

if ($searchfields_get) {
    // there is a restriction to records to show
    $longformfield = array();
    // $fieldarray = explode('&amp;', $searchfields_get);
    $fieldarray = explode(SURVEY_URLPARAMSEPARATOR, $searchfields_get);
    foreach ($fieldarray as $valuefield) {
        $element = explode(SURVEY_URLVALUESEPARATOR, $valuefield);
        $index = $element[1];
        $longformfield[$index] = $element[0];
    }
// echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
// echo '$longformfield:';
// var_dump($longformfield);
    if ($submissionidlist = survey_find_submissions($longformfield)) {
        $sql = 'SELECT s.*, s.id as submissionid,
                       u.firstname, u.lastname, u.id, u.picture, u.imagealt, u.email
                FROM {survey_submissions} s
                    JOIN {user} u ON s.userid = u.id
                WHERE s.id IN ('.implode(',', $submissionidlist).')';
    } else {
        // No matching record has been found. I need to return an empty recordset
        $sql = 'SELECT s.*, s.id as submissionid,
                       u.firstname, u.lastname, u.id, u.picture, u.imagealt, u.email
                FROM {survey_submissions} s
                    JOIN {user} u ON s.userid = u.id
                WHERE s.surveyid = -1';
    }
} else {
    $sql = 'SELECT s.*, s.id as submissionid,
                   u.firstname, u.lastname, u.id, u.picture, u.imagealt, u.email
            FROM {survey_submissions} s
                JOIN {user} u ON s.userid = u.id
            WHERE s.surveyid = :surveyid';
}

if ($where) {
    $sql .= ' AND '.$where;
}

if ($table->get_sql_sort()) {
    $sql .= ' ORDER BY '.$table->get_sql_sort();
} else {
    $sql .= ' ORDER BY s.timecreated';
}

$submissions = $DB->get_recordset_sql($sql, $params, $table->get_sql_sort());

if ($submissions->valid()) {

    if ($candeleteallsubmissions) {
        $paramurl = array();
        $paramurl['s'] = $survey->id;
        $paramurl['tab'] = SURVEY_TABSUBMISSIONS;
        $paramurl['pag'] = SURVEY_SUBMISSION_MANAGE;
        $paramurl['act'] = SURVEY_DELETEALLSURVEYS;
        $url = new moodle_url('/mod/survey/view.php', $paramurl);
        $caption = get_string('deleteallsubmissions', 'survey');
        echo $OUTPUT->single_button($url, $caption, 'get');
    }

    $mygroups = survey_get_my_groups($cm);
    foreach ($submissions as $submission) {

        if (!$canreadallsubmissions && !survey_i_can_read($survey, $mygroups, $submission->userid)) continue;

        $tablerow = array();

        // icon
        $tablerow[] = $OUTPUT->user_picture($submission, array('courseid'=>$COURSE->id));

        // user fullname
        $tablerow[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$submission->userid.'&amp;course='.$COURSE->id.'">'.fullname($submission).'</a>';

        // survey status
        $tablerow[] = $status[$submission->status];

        // creation time
        $tablerow[] = userdate($submission->timecreated);

        if (!$survey->history) {
            // modification time
            if ($submission->timemodified) {
                $tablerow[] = userdate($submission->timemodified);
            } else {
                $tablerow[] = $neverstring;
            }
        }

        // actions
        $paramurl['submissionid'] = $submission->submissionid;
        if (survey_i_can_edit($survey, $mygroups, $submission->userid) || $caneditallsubmissions) {     // "edit" or "edit as new"
            $paramurl['pag'] = SURVEY_SUBMISSION_EDIT;
            $paramurl['act'] = SURVEY_EDITSURVEY;
            $icontype = ($submission->status == SURVEY_STATUSCLOSED) ? $survey->history : 0;
            $basepath = new moodle_url('view.php', $paramurl);
            $icontitle = $firsticontitle[$icontype];
            $iconpath = $firsticonicon[$icontype];
            $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url($iconpath).'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
        } else {                                                                                              // view only
            $paramurl['pag'] = SURVEY_SUBMISSION_READONLY;
            //$paramurl['act'] = SURVEY_VIEWSURVEY;
            $basepath = new moodle_url('view.php', $paramurl);
            $icontitle = $restrictedaccess;
            $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('t/preview').'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
        }

        if (survey_i_can_delete($survey, $mygroups, $submission->userid) || $candeleteallsubmissions) { // delete
            $paramurl['pag'] = SURVEY_SUBMISSION_MANAGE;
            $paramurl['act'] = SURVEY_DELETESURVEY;
            $basepath = new moodle_url('view.php', $paramurl);
            $icons .= '&nbsp;<a class="editing_update" title="'.$deletetitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletetitle.'" title="'.$deletetitle.'" /></a>';
        }

        $tablerow[] = $icons;

        // add row to the tabl
        $table->add_data($tablerow);
    }
}
$submissions->close();

$table->summary = get_string('submissionslist', 'survey');
$table->print_html();
