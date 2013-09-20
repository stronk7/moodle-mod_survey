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
     * $canaccessadvanceditems
     */
    public $canaccessadvanceditems = false;

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
     * $canmanagesubmissions
     */
    public $canmanagesubmissions = false;

    /*
     * $canmanageallsubmissions
     */
    public $canmanageallsubmissions = false;

    /*
     * $searchfieldsget
     */
    public $searchfieldsget = '';

    /*
     * $userfeedback
     */
    public $userfeedback = '';

    /*
     * Class constructor
     */
    public function __construct($cm, $survey, $submissionid, $action, $confirm, $searchfieldsget) {
        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);
        $this->survey = $survey;
        $this->submissionid = $submissionid;
        $this->action = $action;
        $this->confirm = $confirm;
        $this->searchfields_get = $searchfieldsget;
        $this->canaccessadvanceditems = has_capability('mod/survey:accessadvanceditems', $this->context, null, true);
        $this->canmanagesubmissions = has_capability('mod/survey:managesubmissions', $this->context, null, true);
        $this->canmanageallsubmissions = has_capability('mod/survey:manageallsubmissions', $this->context, null, true);
    }

    /*
     * manage_actions
     *
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
     *
     * @param
     * @return
     */
    public function manage_submission_deletion() {
        global $USER, $DB, $OUTPUT;

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

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_DELETERESPONSE);

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
                    echo $OUTPUT->notification(get_string('responsedeleted', 'survey'), 'notifyproblem');
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
     *
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
     * get_manage_sql
     *
     * @param
     * @return
     */
    public function get_manage_sql($table) {
        global $USER;

        $sql = 'SELECT s.*, s.id as submissionid, '.user_picture::fields('u').'
                FROM {survey_submissions} s ';

        list($where, $whereparams) = $table->get_sql_where();

        // write $userdatatransposed whether necessary
        if ($this->searchfields_get) {
            // this will be re-send to URL for next page reload, whether requested with a sort, for instance
            $paramurl['searchquery'] = $this->searchfields_get;

            $searchrestrictions = unserialize($this->searchfields_get);

            // written following http://buysql.com/mysql/14-how-to-automate-pivot-tables.html
            $userdatatransposed = 'SELECT submissionid, ';
            $sqlrow = array();
            foreach ($searchrestrictions as $itemid => $searchrestriction) {
                $sqlrow[] = 'MAX(IF(itemid = \''.$itemid.'\', content, NULL)) AS \'c_'.$itemid.'\'';
            }
            $userdatatransposed .= implode(', ', $sqlrow);
            $userdatatransposed .= ' FROM {survey_userdata}';
            $userdatatransposed .= ' GROUP BY submissionid';

            $sql .= '    JOIN ('.$userdatatransposed.') udt ON udt.submissionid = s.id '; // udt == user data transposed
        }

        // if $survey->readaccess == SURVEY_GROUP I am sure that course or instance is divided by group too
        if (!$this->canmanageallsubmissions) {
            if ($this->survey->readaccess == SURVEY_GROUP) {
                // apply the group filter ONLY IF "Advanced permissions"
                $sql .= '    JOIN {groups_members} gm ON gm.userid = s.userid ';
            }
        }

        $sql .= '    JOIN {user} u ON (s.userid = u.id)
                WHERE s.surveyid = :surveyid';
        $whereparams['surveyid'] = $this->survey->id;

        if (!$this->canmanageallsubmissions) {
            if ($this->survey->readaccess == SURVEY_OWNER) {
                $sql .= ' AND s.userid = :userid';
                $whereparams['userid'] = $USER->id;
            }
        }

        // specific restrictions over {survey_userdata}
        if ($this->searchfields_get) {
            foreach ($searchrestrictions as $itemid => $searchrestriction) {
                $sql .= ' AND udt.c_'.$itemid.' = :c_'.$itemid;
                $whereparams['c_'.$itemid] = $searchrestriction;
            }
        }

        $mygroups = survey_get_my_groups($this->cm);
        if (!$this->canmanageallsubmissions) {
            if ($this->survey->readaccess == SURVEY_GROUP) { // if $survey->readaccess == SURVEY_GROUP then course or instance is divided by group
                if (count($mygroups)) {
                    $grouprow = array();
                    $sql .= ' AND (';
                    foreach ($mygroups as $mygroup) {
                        $grouprow[] = '(gm.groupid = '.$mygroup.')';
                    }
                    $sql .= implode(' OR ', $grouprow);
                    $sql .= ') ';
                }
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

        // echo '$sql = '.$sql.'<br />';
        return array($sql, $whereparams, $mygroups);
    }

    /*
     * manage_submissions
     *
     * @param
     * @return
     */
    public function manage_submissions() {
        global $OUTPUT, $CFG, $DB, $COURSE, $USER;

        require_once($CFG->libdir.'/tablelib.php');

        $table = new flexible_table('submissionslist');
        $table->initialbars(true);

        $paramurl = array('id' => $this->cm->id);
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

        $status = array(SURVEY_STATUSINPROGRESS => get_string('statusinprogress', 'survey'),
                        SURVEY_STATUSCLOSED => get_string('statusclosed', 'survey'));
        $downloadpdftitle = get_string('downloadpdf', 'survey');
        $deletetitle = get_string('delete');
        $neverstring = get_string('never');
        $edittitle = get_string('edit');
        $restrictedaccess = get_string('restrictedaccess', 'survey');
        $duplicatetitle = get_string('duplicate');

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        $basepath = new moodle_url('view.php', $paramurl);

        list($sql, $whereparams, $mygroups) = $this->get_manage_sql($table);

        $submissions = $DB->get_recordset_sql($sql, $whereparams, $table->get_sql_sort());

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
                if (!$this->canmanageallsubmissions && !has_extrapermission('read', $this->survey, $mygroups, $submission->userid)) {
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
                if ($this->canmanageallsubmissions || has_extrapermission('edit', $this->survey, $mygroups, $submission->userid)) { // "edit" or "edit as new"
                    $paramurl['act'] = SURVEY_EDITRESPONSE;
                    if ($submission->status == SURVEY_STATUSCLOSED) {
                        if ($this->survey->history) {
                            $icontitle = $duplicatetitle;
                            $iconpath = 't/copy';
                        } else {
                            $icontitle = $edittitle;
                            $iconpath = 't/edit';
                        }
                    } else {
                        // always allow the user to finalize his/her submission
                        $icontitle = $edittitle;
                        $iconpath = 't/edit';
                    }
                    $basepath = new moodle_url('view.php', $paramurl);
                    $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url($iconpath).'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
                } else { // read only
                    // I don't have canmanageallsubmissions || has_extrapermission
                    // but if I meet an "in progress" submission of mine...
                    if (($submission->status == SURVEY_STATUSINPROGRESS) && ($submission->userid == $USER->id)) {
                        $paramurl['act'] = SURVEY_EDITRESPONSE;
                        $icontitle = $edittitle;
                        $basepath = new moodle_url('view.php', $paramurl);
                        $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url('t/edit').'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
                    } else { // submission is closed
                        $paramurl['act'] = SURVEY_READONLYRESPONSE;
                        $icontitle = $restrictedaccess;
                        $basepath = new moodle_url('view.php', $paramurl);
                        $icons = '<a class="editing_update" title="'.$icontitle.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url('readonly', 'survey').'" class="iconsmall" alt="'.$icontitle.'" title="'.$icontitle.'" /></a>';
                    }
                }

                if ($this->canmanageallsubmissions || has_extrapermission('delete', $this->survey, $mygroups, $submission->userid)) { // delete
                    $paramurl['act'] = SURVEY_DELETERESPONSE;
                    $basepath = new moodle_url('view_manage.php', $paramurl);
                    $icons .= '&nbsp;<a class="editing_update" title="'.$deletetitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletetitle.'" title="'.$deletetitle.'" /></a>';
                }

                // if I am here I am sure I can see this submission
                $paramurl['act'] = SURVEY_RESPONSETOPDF;
                $basepath = new moodle_url('view_manage.php', $paramurl);
                $icons .= '&nbsp;<a class="editing_update" title="'.$downloadpdftitle.'" href="'.$basepath.'">';
                $icons .= '<img src="'.$OUTPUT->pix_url('i/export').'" class="iconsmall" alt="'.$downloadpdftitle.'" title="'.$downloadpdftitle.'" /></a>';

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
     * prevent_direct_user_input
     *
     * @param
     * @return
     */
    public function prevent_direct_user_input() {
        global $DB;

        if ($this->canmanageallsubmissions) {
            return true;
        }
        if (!$this->canmanagesubmissions) {
            print_error('incorrectaccessdetected', 'survey');
        }
        if ($this->action == SURVEY_NOACTION) {
            return true;
        }
        if (!$ownerid = $DB->get_field('survey_submissions', 'userid', array('id' => $this->submissionid), IGNORE_MISSING)) {
            print_error('incorrectaccessdetected', 'survey');
        }

        $allowed = true;
        $mygroups = survey_get_my_groups($this->cm);
        switch ($this->action) {
            case SURVEY_EDITRESPONSE:
                $allowed = has_extrapermission('edit', $this->survey, $mygroups, $ownerid);
                break;
            case SURVEY_READONLYRESPONSE:
                $allowed = has_extrapermission('read', $this->survey, $mygroups, $ownerid);
                break;
            case SURVEY_DELETERESPONSE:
                $allowed = has_extrapermission('delete', $this->survey, $mygroups, $ownerid);
                break;
            case SURVEY_RESPONSETOPDF:
                $allowed = has_capability('mod/survey:submissiontopdf', $this->context);
                break;
            case SURVEY_DELETEALLRESPONSES:
                $allowed = has_capability('mod/survey:deleteallsubmissions', $this->context);
                break;
            default:
                $allowed = false;
        }
        if (!$allowed) {
            print_error('incorrectaccessdetected', 'survey');
        }
    }

    /*
     * submission_to_pdf
     *
     * @param
     * @return
     */
    public function submission_to_pdf() {
        global $CFG, $DB;

        if ($this->action != SURVEY_RESPONSETOPDF) {
            return;
        }

        require_once($CFG->libdir.'/tcpdf/tcpdf.php');
        require_once($CFG->libdir.'/tcpdf/config/tcpdf_config.php');

        $emptyanswer = get_string('notanswereditem', 'survey');

        $submission = $DB->get_record('survey_submissions', array('id' => $this->submissionid));
        $user = $DB->get_record('user', array('id' => $submission->userid));
        $userdatarecord = $DB->get_records('survey_userdata', array('submissionid' => $this->submissionid), '', 'itemid, id, content');

        $accessedadvancedform = has_capability('mod/survey:accessadvanceditems', $this->context, $user->id, true);
        // $canaccessadvanceditems, $searchform = false; $type = false; $formpage = false;
        list($sql, $whereparams) = survey_fetch_items_seeds($this->survey->id, $accessedadvancedform, false);

        // I am not allowed to get ONLY answers from survey_userdata
        // because I also need to gather info about fieldset and label
        // $sql = 'SELECT *, s.id as submissionid, ud.id as userdataid, ud.itemid as id
        //         FROM {survey_submissions} s
        //             JOIN {survey_userdata} ud ON ud.submissionid = s.id
        //         WHERE s.id = :submissionid';
        $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('moodle-mod_survey');
        $pdf->SetTitle('User response');
        $pdf->SetSubject('Single response in PDF');

        // set default header data
        $textheader = get_string('responseauthor', 'survey');
        $textheader .= fullname($user);
        $textheader .= "\n";
        $textheader .= get_string('responsetimecreated', 'survey');
        $textheader .= userdate($submission->timecreated);
        if ($submission->timemodified) {
            $textheader .= get_string('responsetimemodified', 'survey');
            $textheader .= userdate($submission->timemodified);
        }

        $pdf->SetHeaderData('', 0, $this->survey->name, $textheader, array(0, 64, 255), array(0, 64, 128));
        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetDrawColorArray(array(0, 64, 128));
        // set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        $pdf->AddPage();

        $col1nunit = 1;
        $col2nunit = 6;
        $col3nunit = 3;
        $firstcolwidth = $pdf->getPageWidth();
        $firstcolwidth -= PDF_MARGIN_LEFT;
        $firstcolwidth -= PDF_MARGIN_RIGHT;
        $unitsum = $col1nunit + $col2nunit + $col3nunit;

        $firstcolwidth = number_format($col1nunit*100/$unitsum, 2);
        $secondcolwidth = number_format($col2nunit*100/$unitsum, 2);
        $thirdcolwidth = number_format($col3nunit*100/$unitsum, 2);
        $lasttwocolumns = $secondcolwidth + $thirdcolwidth;

        // 0: to the right (or left for RTL language)
        // 1: to the beginning of the next line
        // 2: below

        $htmllabeltemplate = '<table style="width:100%;"><tr><td style="width:'.$firstcolwidth.'%;text-align:left;">@@col1@@</td>';
        $htmllabeltemplate .= '<td style="width:'.$lasttwocolumns.'%;text-align:left;">@@col2@@</td></tr></table>';

        $htmlstandardtemplate = '<table style="width:100%;"><tr><td style="width:'.$firstcolwidth.'%;text-align:left;">@@col1@@</td>';
        $htmlstandardtemplate .= '<td style="width:'.$secondcolwidth.'%;text-align:left;">@@col2@@</td>';
        $htmlstandardtemplate .= '<td style="width:'.$thirdcolwidth.'%;text-align:left;">@@col3@@</td></tr></table>';

        $border = array('T' => array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(179, 219, 181)));
        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);
            // ($itemseed->plugin == 'pagebreak') is not selected by survey_fetch_items_seeds
            if (($itemseed->plugin == 'fieldset') || ($itemseed->plugin == 'fieldsetend')) {
                continue;
            }
            if ($itemseed->plugin == 'label') {
                // first column
                $html = $htmllabeltemplate;
                $content = ($item->get_customnumber()) ? $item->get_customnumber().':' : '';
                $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                $html = str_replace('@@col1@@', $content, $html);

                // second column: colspan 2
                $content = trim(strip_tags($item->get_content()), " \t\n\r");
                // why does $content here is already html encoded so that I do not have to apply htmlspecialchars?
                // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                $html = str_replace('@@col2@@', $content, $html);
                $pdf->writeHTMLCell(0, 0, '', '', $html, $border, 1, 0, true, '', true); // this is like span 2
                continue;
            }

            // first column
            $html = $htmlstandardtemplate;
            $content = ($item->get_customnumber()) ? $item->get_customnumber().':' : '';
            $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
            $html = str_replace('@@col1@@', $content, $html);

            // second column
            $content = trim(strip_tags($item->get_content()), " \t\n\r");
            // why does $content here is already html encoded so that I do not have to apply htmlspecialchars?
            // because it comes from an editor?
            // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
            $html = str_replace('@@col2@@', $content, $html);

            // third column
            if (isset($userdatarecord[$item->get_itemid()])) {
                $content = $item->userform_db_to_export($userdatarecord[$item->get_itemid()], SURVEY_FIRENDLYFORMAT);
                if ($item->get_plugin() != 'textarea') { // content does not come from an html editor
                    $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                } else {
                    if (!$item->get_useeditor()) { // content does not come from an html editor
                        $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                    }
                }
            } else {
                // $content = $emptyanswer;
                $content = '';
            }
            $html = str_replace('@@col3@@', $content, $html);
            $pdf->writeHTMLCell(0, 0, '', '', $html, $border, 1, 0, true, '', true);
        }

        $filename = $this->survey->name.'_'.$this->submissionid.'.pdf';
        $pdf->Output($filename, 'D');
        die;
    }
}
