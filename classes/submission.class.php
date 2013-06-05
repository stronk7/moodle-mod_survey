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
     * $canreadallsubmissions
     */
    public $canreadallsubmissions = false;

    /*
     * $caneditallsubmissions
     */
    public $caneditallsubmissions = false;

    /*
     * $candeleteallsubmissions
     */
    public $candeleteallsubmissions = false;

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
            case SURVEY_EDITSURVEY:
            case SURVEY_READONLYSURVEY:
                break;
            case SURVEY_DELETESURVEY:
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
                    $message = get_string('askdeletemysurveynevermodified', 'survey', $a);
                } else {
                    $message = get_string('askdeletemysurvey', 'survey', $a);
                }
            }

            $optionbase = array('id' => $cm->id, 'act' => SURVEY_DELETESURVEY);

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
            $message = get_string('askdeleteallsurveys', 'survey');

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

        $paramurl = array('id' => $cm->id);
        if ($this->searchfields_get) {
            $paramurl['searchquery'] = $this->searchfields_get;
        }
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

        $table->initialbars(true);

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

        $status = array(SURVEY_STATUSINPROGRESS => get_string('statusinprogress', 'survey'),
                        SURVEY_STATUSCLOSED => get_string('statusclosed', 'survey'));
        $deletetitle = get_string('delete');
        $neverstring = get_string('never');
        $restrictedaccess = get_string('restrictedaccess', 'survey');

        $paramurl = array();
        $paramurl['id'] = $cm->id;
        $basepath = new moodle_url('view.php', $paramurl);

        list($where, $params) = $table->get_sql_where();

        $params['surveyid'] = $this->survey->id;

        if ($this->searchfields_get) {
            if ($submissionidlist = $this->get_filtered_id_list()) {
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
            // TODO: get only the list of submissions of the owners I am allowed to see
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

            if ($this->candeleteallsubmissions) {
                $paramurl = array();
                $paramurl['s'] = $this->survey->id;
                $paramurl['act'] = SURVEY_DELETEALLRESPONSES;
                $url = new moodle_url('/mod/survey/view_manage.php', $paramurl);
                $caption = get_string('deleteallsubmissions', 'survey');
                echo $OUTPUT->single_button($url, $caption, 'get');
            }

            $mygroups = survey_get_my_groups($cm);
            foreach ($submissions as $submission) {
                if (!$this->canreadallsubmissions && !survey_i_can_read($this->survey, $mygroups, $submission->userid)) {
                    continue;
                }

                $tablerow = array();

                // icon
                $tablerow[] = $OUTPUT->user_picture($submission, array('courseid'=>$COURSE->id));

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
                if (survey_i_can_edit($this->survey, $mygroups, $submission->userid) || $this->caneditallsubmissions) {     // "edit" or "edit as new"
                    if ($submission->status == SURVEY_STATUSCLOSED) {
                        if ($this->survey->history) {
                            $paramurl['act'] = SURVEY_DUPLICATESURVEY;
                            $icontitle = get_string('duplicate');
                            $iconpath = 't/copy';
                        } else {
                            $paramurl['act'] = SURVEY_EDITSURVEY;
                            $icontitle = get_string('edit');
                            $iconpath = 't/edit';
                        }
                    } else {
                        // alwats allow the user to finalize his/her submission
                        $paramurl['act'] = SURVEY_EDITSURVEY;
                        $icontitle = get_string('edit');
                        $iconpath = 't/edit';
                    }
                    $basepath = new moodle_url('view.php', $paramurl);
                    $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url($iconpath).'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
                } else {                                                                                                   // read only
                    $paramurl['act'] = SURVEY_READONLYSURVEY;
                    $basepath = new moodle_url('view.php', $paramurl);
                    $icontitle = $restrictedaccess;
                    $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('t/preview').'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
                }

                if (survey_i_can_delete($this->survey, $mygroups, $submission->userid) || $this->candeleteallsubmissions) { // delete
                    $paramurl['act'] = SURVEY_DELETESURVEY;
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

    /*
     * survey_find_submissions
     * @param
     * @return
     */
    public function get_filtered_id_list() {
        global $DB;

        $search_restrictions = unserialize($this->searchfields_get);
        // echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
        // echo '$search_restrictions (prima):';
        // var_dump($search_restrictions);

        foreach ($search_restrictions as $itemid => $valuesarray) {
            // I am interested only to non empty fields BUT different from SURVEY_NOANSWERVALUE
            if ($valuesarray == SURVEY_NOANSWERVALUE) {
                unset($search_restrictions[$itemid]);
            }
        }
        // echo '$search_restrictions (dopo):';
        // var_dump($search_restrictions);
        // die;

        // the search process is tricky
        // the procedure is:
        // step 1:
        //     get the set of submissions matching the first condition
        // step 2:
        //     check the found set for all the other conditions
        //     if at least one condition does not match, delete the submission id from the starting set
        //     Whatever will not be deleted, is the submission matching ALL submitted requests

        // if the search form is empty (has no conditions) return all the submissions
        if (!$search_restrictions) {
            return;
        }

        $keys = array_keys($search_restrictions);
        $firstitemid = $keys[0];
        $firstcontent = $search_restrictions[$firstitemid];

        unset($search_restrictions[$firstitemid]); // drop the first element of $search_restrictions

        // should work but does not: MDL-27629
        // $submissionidlist = $DB->get_records('survey_userdata', array('itemid' => $firstitemid, $DB->sql_compare_text('content') => $firstcontent), 'submissionid');

        $where = 'itemid = :itemid AND '.$DB->sql_compare_text('content').' = :content';
        $params = array('itemid' => $firstitemid, 'content' => (string)$firstcontent);
        if (!$submissionidlist = $DB->get_records_select('survey_userdata', $where, $params, 'submissionid', 'submissionid')) {
            // nessuna submission soddisfa le richieste
            return array();
        } else {
            $submissionidlist = array_keys($submissionidlist); // list of submission id matching the first constraint

        }

        if (!$search_restrictions) {
            // if no more constaints are available, the process is finished
            return $submissionidlist;
        }

        foreach ($search_restrictions as $itemid => $valuesarray) {
            $where = 'submissionid IN ('.implode(',', $submissionidlist).')
                          AND itemid = :itemid
                          AND content = :valuesarray';
            $params = array('itemid' => $itemid, 'content' => (string)$valuesarray);
            if ($submissionidlist = $DB->get_records_select('survey_userdata', $where, $params, 'submissionid', 'submissionid')) {
                $submissionidlist = array_keys($submissionidlist);
            } else {
                // not any submission meets all the constraints
                return array();
            }
        }
        return $submissionidlist;
    }
}