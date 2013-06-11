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
class mod_survey_submissionmanager {
    /*
     * $survey: the record of this survey
     */
    public $survey = null;

    /*
     * $submissionid: the ID of the current submission
     */
    public $submissionid = 0;

    /*
     * $canaccessadvancedform
     */
    public $canaccessadvancedform = false;

    /*
     * $canmanageitems
     */
    public $canmanageitems = false;

    /*
     * $action
     */
    public $action = SURVEY_NOACTION;

    /*
     * $confirm
     */
    public $confirm = false;

    /*
     * $canmanageallsubmissions
     */
    public $canmanageallsubmissions = false;

    /*
     * $searchfields_get
     */
    public $searchfields_get = '';

    /*
     * $userfeedback
     */
    public $userfeedback = '';


    /*
     * Class constructor
     */
    public function __construct($survey) {
        $this->survey = $survey;
    }

    /*
     * manage_actions
     * @param
     * @return
     */
    public function manage_actions() {
        switch ($this->action) {
            case SURVEY_NOACTION:
            case SURVEY_EDITRESPONSE:
            case SURVEY_READONLYRESPONSE:
                break;
            case SURVEY_DELETERESPONSE:
                $this->manage_submission_deletion();
                break;
            case SURVEY_DELETEALLRESPONSES:
                $this->manage_all_submission_deletion();
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $action = '.$this->action);
        }
    }

    /*
     * manage_submission_deletion
     * @param
     * @return
     */
    public function manage_submission_deletion() {
        global $USER, $DB, $OUTPUT, $PAGE;

        $cm = $PAGE->cm;

        if ($this->confirm == SURVEY_UNCONFIRMED) {
            // ask for confirmation
            $submission = $DB->get_record('survey_submissions', array('id' => $this->submissionid));

            $a = new stdClass();
            $a->timecreated = userdate($submission->timecreated);
            $a->timemodified = userdate($submission->timemodified);
            if ($submission->userid != $USER->id) {
                $a->fullname = fullname($DB->get_record('user', array('id' => $submission->userid), 'firstname, lastname', MUST_EXIST));
                if ($a->timemodified == 0) {
                    $message = get_string('askdeleteonesurveynevermodified', 'survey', $a);
                } else {
                    $message = get_string('askdeleteonesurvey', 'survey', $a);
                }
            } else {
                if ($a->timemodified == 0) {
                    $message = get_string('askdeletemysubmissionsnevermodified', 'survey', $a);
                } else {
                    $message = get_string('askdeletemysubmissions', 'survey', $a);
                }
            }

            $optionbase = array('id' => $cm->id, 'act' => SURVEY_DELETERESPONSE);

            $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRMED_YES, 'submissionid' => $this->submissionid);
            $urlyes = new moodle_url('view_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('confirmsurveydeletion', 'survey'));

            $optionsno = $optionbase + array('cnf' => SURVEY_CONFIRMED_NO);
            $urlno = new moodle_url('view_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die;
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    $DB->delete_records('survey_userdata', array('submissionid' => $this->submissionid));
                    $DB->delete_records('survey_submissions', array('id' => $this->submissionid));
                    echo $OUTPUT->notification(get_string('surveydeleted', 'survey'), 'notifyproblem');
                    break;
                case SURVEY_CONFIRMED_NO:
                    $message = get_string('usercanceled', 'survey');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $confirm = '.$this->confirm);
            }
        }
    }

    /*
     * manage_all_submission_deletion
     * @param
     * @return
     */
    public function manage_all_submission_deletion() {
        global $DB, $OUTPUT;

        if ($this->confirm == SURVEY_UNCONFIRMED) {
            // ask for confirmation
            $message = get_string('askdeleteallsubmissions', 'survey');

            $optionbase = array('s' => $this->survey->id, 'surveyid' => $this->survey->id, 'act' => SURVEY_DELETEALLRESPONSES);

            $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRMED_YES);
            $urlyes = new moodle_url('view_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('confirmallsurveysdeletion', 'survey'));

            $optionsno = $optionbase + array('cnf' => SURVEY_CONFIRMED_NO);
            $urlno = new moodle_url('view_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die;
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    $sql = 'SELECT s.id
                                FROM {survey_submissions} s
                                WHERE s.surveyid = :surveyid';
                    $idlist = $DB->get_records_sql($sql, array('surveyid' => $this->survey->id));

                    foreach ($idlist as $submissionid) {
                        $DB->delete_records('survey_userdata', array('submissionid' => $submissionid->id));
                    }

                    $DB->delete_records('survey_submissions', array('surveyid' => $this->survey->id));
                    echo $OUTPUT->notification(get_string('allsurveysdeleted', 'survey'), 'notifyproblem');
                    break;
                case SURVEY_CONFIRMED_NO:
                    $message = get_string('usercanceled', 'survey');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $confirm = '.$this->confirm);
            }
        }
    }

    /*
     * manage_submissions
     * @param
     * @return
     */
    public function manage_submissions() {
        global $PAGE, $USER, $OUTPUT, $CFG, $DB, $COURSE;

        require_once($CFG->libdir.'/tablelib.php');

        $cm = $PAGE->cm;

        $context = context_module::instance($cm->id);

        $table = new flexible_table('submissionslist');
        $table->initialbars(true);

        $paramurl = array('id' => $cm->id);
        $table->define_baseurl(new moodle_url('view_manage.php', $paramurl));

        $tablecolumns = array();
        $tablecolumns[] = 'picture';
        $tablecolumns[] = 'fullname';
        $tablecolumns[] = 'status';
        $tablecolumns[] = 'timecreated';
        if (!$this->survey->history) {
            $tablecolumns[] = 'timemodified';
        }
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = '';
        $tableheaders[] = get_string('fullname');
        $tableheaders[] = get_string('status');
        $tableheaders[] = get_string('timecreated', 'survey');
        if (!$this->survey->history) {
            $tableheaders[] = get_string('timemodified', 'survey');
        }
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        // $table->collapsible(true);
        $table->sortable(true, 'sortindex', 'ASC'); // sorted by sortindex by default
        $table->no_sorting('actions');

        // $table->column_style('actions', 'width', '60px');
        // $table->column_style('actions', 'align', 'center');
        $table->column_class('picture', 'picture');
        $table->column_class('fullname', 'fullname');
        $table->column_class('status', 'status');
        $table->column_class('timecreated', 'timecreated');
        if (!$this->survey->history) {
            $table->column_class('timemodified', 'timemodified');
        }
        $table->column_class('actions', 'actions');

        // hide the same info whether in two consecutive rows
        $table->column_suppress('picture');
        $table->column_suppress('fullname');

        // general properties for the whole table
        $table->set_attribute('cellpadding', 5);
        $table->set_attribute('id', 'submissions');
        $table->set_attribute('class', 'generaltable');
        $table->set_attribute('align', 'center');
        // $table->set_attribute('width', '90%');
        $table->setup();

        /*****************************************************************************/
        if ($this->survey->readaccess == SURVEY_NONE) {
            $message = get_string('noreadaccess', 'survey');
            echo $OUTPUT->box($message, 'notice centerpara');
            echo $OUTPUT->footer();
            die;
        }

        $mygroups = survey_get_my_groups($cm);

        $status = array(SURVEY_STATUSINPROGRESS => get_string('statusinprogress', 'survey'),
                        SURVEY_STATUSCLOSED => get_string('statusclosed', 'survey'));
        $deletetitle = get_string('delete');
        $neverstring = get_string('never');
        $restrictedaccess = get_string('restrictedaccess', 'survey');

        $paramurl = array();
        $paramurl['id'] = $cm->id;
        $basepath = new moodle_url('view.php', $paramurl);

        list($where, $params) = $table->get_sql_where();

        $userfields = user_picture::fields('u');

        $sql = 'SELECT s.*, s.id as submissionid, '.$userfields.'
                FROM {survey_submissions} s ';

        // write $userdata_transposed whether necessary
        if ($this->searchfields_get) {
            // this will be re-send to URL for next page reload, whether requested with a sort, for instance
            $paramurl['searchquery'] = $this->searchfields_get;

            $search_restrictions = unserialize($this->searchfields_get);
// $search_restrictions:
//   1053 => string '1' (length=1)
//   1054 => string '6.32' (length=4)
//   1055 => string '90' (length=2)
//   1065 => string 'Wine' (length=4)

            $userdata_transposed = 'SELECT submissionid, '."\n";
            $sqlrow = array();
            foreach ($search_restrictions as $itemid => $search_restriction) {
                $sqlrow[] = 'MAX(IF(itemid = \''.$itemid.'\', content, NULL)) AS \'c_'.$itemid.'\'';
            }
            $userdata_transposed .= implode(', ', $sqlrow)."\n";
            $userdata_transposed .= 'FROM {survey_userdata} '."\n";
            $userdata_transposed .= 'GROUP BY submissionid';

            $sql .= '    JOIN ('.$userdata_transposed.') userdata ON userdata.submissionid = s.id ';
        }
        $sql .= '    JOIN {user} u ON (s.userid = u.id)
                WHERE s.surveyid = :surveyid';
        $params['surveyid'] = $this->survey->id;

        // specific restrictions over {survey_userdata}
        if ($this->searchfields_get) {
            foreach ($search_restrictions as $itemid => $search_restriction) {
                $sql .= ' AND userdata.c_'.$itemid.' = :c_'.$itemid;
                $params['c_'.$itemid] = $search_restriction;
            }
        }

        // specific restrictions coming from $table->get_sql_where()
        if ($where) {
            $sql .= ' AND '.$where;
        }

        // sort coming from $table->get_sql_sort()
        if ($table->get_sql_sort()) {
            $sql .= ' ORDER BY '.$table->get_sql_sort();
        } else {
            $sql .= ' ORDER BY s.timecreated';
        }

        $submissions = $DB->get_recordset_sql($sql, $params, $table->get_sql_sort());

        if ($submissions->valid()) {
            if ($this->canmanageallsubmissions) {
                $paramurl = array();
                $paramurl['s'] = $this->survey->id;
                $paramurl['act'] = SURVEY_DELETEALLRESPONSES;
                $url = new moodle_url('/mod/survey/view_manage.php', $paramurl);
                $caption = get_string('deleteallsubmissions', 'survey');
                echo $OUTPUT->single_button($url, $caption, 'get');
            }

            foreach ($submissions as $submission) {
                if (!$this->canmanageallsubmissions && !survey_i_can_read($this->survey, $mygroups, $submission->userid)) {
                    continue;
                }

                $tablerow = array();

                // icon
                $tablerow[] = $OUTPUT->user_picture($submission, array('courseid' => $COURSE->id));

                // user fullname
                $tablerow[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$submission->userid.'&amp;course='.$COURSE->id.'">'.fullname($submission).'</a>';

                // survey status
                $tablerow[] = $status[$submission->status];

                // creation time
                $tablerow[] = userdate($submission->timecreated);

                if (!$this->survey->history) {
                    // modification time
                    if ($submission->timemodified) {
                        $tablerow[] = userdate($submission->timemodified);
                    } else {
                        $tablerow[] = $neverstring;
                    }
                }

                // actions
                $paramurl['submissionid'] = $submission->submissionid;
                if (survey_i_can_edit($this->survey, $mygroups, $submission->userid) || $this->canmanageallsubmissions) {     // "edit" or "edit as new"
                    if ($submission->status == SURVEY_STATUSCLOSED) {
                        if ($this->survey->history) {
                            $paramurl['act'] = SURVEY_DUPLICATERESPONSE;
                            $icontitle = get_string('duplicate');
                            $iconpath = 't/copy';
                        } else {
                            $paramurl['act'] = SURVEY_EDITRESPONSE;
                            $icontitle = get_string('edit');
                            $iconpath = 't/edit';
                        }
                    } else {
                        // alwats allow the user to finalize his/her submission
                        $paramurl['act'] = SURVEY_EDITRESPONSE;
                        $icontitle = get_string('edit');
                        $iconpath = 't/edit';
                    }
                    $basepath = new moodle_url('view.php', $paramurl);
                    $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url($iconpath).'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
                } else {                                                                                                   // read only
                    $paramurl['act'] = SURVEY_READONLYRESPONSE;
                    $basepath = new moodle_url('view.php', $paramurl);
                    $icontitle = $restrictedaccess;
                    $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('t/preview').'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
                }

                if (survey_i_can_delete($this->survey, $mygroups, $submission->userid) || $this->canmanageallsubmissions) { // delete
                    $paramurl['act'] = SURVEY_DELETERESPONSE;
                    $basepath = new moodle_url('view_manage.php', $paramurl);
                    $icons .= '&nbsp;<a class="editing_update" title="'.$deletetitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletetitle.'" title="'.$deletetitle.'" /></a>';
                }

                $tablerow[] = $icons;

                // add row to the table
                $table->add_data($tablerow);
            }
        }
        $submissions->close();

        $table->summary = get_string('submissionslist', 'survey');
        $table->print_html();
    }
}