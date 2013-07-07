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
class mod_survey_userpagemanager {
    /*
     * $cm
     */
    public $cm = null;

    /*
     * $context
     */
    public $context = null;

    /*
     * $survey: the record of this survey
     */
    public $survey = null;

    /*
     * $submissionid: the ID of the saved surbey_submission
     */
    public $submissionid = 0;

    /*
     * $formpage: the form page as recalculated according to the first non empty page
     * do not confuse this properties with $this->formdata->formpage
     */
    public $formpage = null;

    /*
     * $maxassignedpage
     */
    public $maxassignedpage = 0;

    /*
     * $firstpage_right
     */
    public $firstpage_right = 0;

    /*
     * $firstpage_left
     */
    public $firstpage_left = 0;

    /*
     * $action
     */
    public $action = SURVEY_NOACTION;

    /*
     * $currentpage: this is the page of the module. Nothing to share with $formpage
     */
    public $currentpage = '';

    /*
     * $canmanageallsubmissions
     */
    public $canmanageallsubmissions = false;

    /*
     * $canaccessadvanceditems
     */
    public $canaccessadvanceditems = false;

    /*
     * $canmanageitems
     */
    public $canmanageitems = false;

    /*
     * $cansubmit
     */
    public $cansubmit = false;

    /********************** this will be provided later
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;



    /*
     * Class constructor
     */
    public function __construct($cm, $survey, $submissionid, $formpage, $action) {
        global $DB;

        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);
        $this->survey = $survey;
        $this->submissionid = $submissionid;
        $this->action = $action;
        $this->set_page_from_action();

        $this->canmanageitems = has_capability('mod/survey:manageitems', $this->context, null, true);
        $this->canaccessadvanceditems = has_capability('mod/survey:accessadvanceditems', $this->context, null, true);

        $this->cansubmit = has_capability('mod/survey:submit', $this->context, null, true);

        $this->canmanageallsubmissions = has_capability('mod/survey:manageallsubmissions', $this->context, null, true);


        // assign pages to items
        $this->maxassignedpage = $DB->get_field('survey_item', 'MAX(formpage)', array('surveyid' => $survey->id));
        $this->assign_pages();

        // calculare $this->firstpage_right
        if ($this->canaccessadvanceditems) {
            $this->firstpage_right = 1;
        } else {
            $this->next_not_empty_page(true, 0); // this calculates $this->firstformpage
        }

        if ($formpage == 0) { // you are viewing the survey for the first time
            $this->formpage = $this->firstpage_right;
        } else {
            $this->formpage = $formpage;
        }
    }

    /*
     * next_not_empty_page
     *
     * @param $forward
     * @param $startingpage
     * @return
     */
    public function next_not_empty_page($forward, $startingpage) {
        // depending on user provided answer, in the previous or next page there may be no questions to display
        // get the first page WITH questions
        // this method write the page number of the first non empty page (according to user answers) in $this->firstpage_right
        // or the page number of the last non empty page (according to user answers) in $this->firstpage_left
        // returns $nextpage or 0 if no more empty pages are found in the specified direction

        $condition1 = ($startingpage == SURVEY_RIGHT_OVERFLOW) && ($forward);
        $condition2 = ($startingpage == SURVEY_LEFT_OVERFLOW) && (!$forward);
        if ($condition1 || $condition2) {
            throw new moodle_exception('Wrong direction required in next_not_empty_page whether $startingpage == SURVEY_RIGHT_OVERFLOW');
        }

        if ($startingpage == SURVEY_RIGHT_OVERFLOW) {
            $startingpage = $this->maxassignedpage + 1;
        }
        if ($startingpage == SURVEY_LEFT_OVERFLOW) {
            $startingpage = 0;
        }

        if ($forward) {
            $nextpage = ++$startingpage;
            $overflowpage = $this->maxassignedpage + 1; // maxpage = $maxformpage, but I have to add      1 because of ($i != $overflowpage)
        } else {
            $nextpage = --$startingpage;
            $overflowpage = 0;                          // minpage = 1,            but I have to subtract 1 because of ($i != $overflowpage)
        }

        do {
            if ($this->page_has_items($nextpage)) {
                break;
            }
            $nextpage = ($forward) ? ++$nextpage : --$nextpage;
        } while ($nextpage != $overflowpage);

        if ($forward) {
            $this->firstpage_right = ($nextpage == $overflowpage) ? SURVEY_RIGHT_OVERFLOW : $nextpage;
        } else {
            $this->firstpage_left = ($nextpage == $overflowpage) ? SURVEY_LEFT_OVERFLOW : $nextpage;
        }
    }

    /*
     * page_has_items
     *
     * @param $formpage
     * @return
     */
    public function page_has_items($formpage) {
        global $CFG, $DB;

        //$canaccessadvanceditems, $searchform=false, $type=SURVEY_TYPEFIELD, $formpage=$formpage
        list($sql, $params) = survey_fetch_items_seeds($this->survey->id, $this->canaccessadvanceditems, false, SURVEY_TYPEFIELD, $formpage);
        $itemseeds = $DB->get_records_sql($sql, $params);

        // start looking ONLY at empty($item->parentid) because it doesn't involve extra queries
        foreach ($itemseeds as $itemseed) {
            if (empty($itemseed->parentid)) {
                // if at least one item has an empty parentid, I finished
                return true;
            }
        }

        foreach ($itemseeds as $itemseed) {
            // make sure that the visibility condition is verified
            if ($itemseed->type == SURVEY_TYPEFORMAT) {
                continue;
            }

            $parentplugin = $DB->get_field('survey_item', 'plugin', array('id' => $itemseed->parentid));
            require_once($CFG->dirroot.'/mod/survey/field/'.$parentplugin.'/plugin.class.php');

            $itemclass = 'surveyfield_'.$parentplugin;
            $parentitem = new $itemclass($itemseed->parentid);

            if ($parentitem->userform_child_item_allowed_static($this->submissionid, $itemseed)) {
            //if (userform_child_item_allowed_static($this->submissionid, $itemseed)) {
                return true;
            }
        }

        // if you're not able to get out in the two previous occasions ... declares defeat
        return false;
    }

    /*
     * set_page_from_action
     *
     * @param
     * @return
     */
    function set_page_from_action() {
        switch ($this->action) {
            case SURVEY_NOACTION:
                $this->currentpage = SURVEY_SUBMISSION_NEW; // needed by tabs.php
                break;
            case SURVEY_PREVIEWSURVEY:
                $this->currentpage = SURVEY_SUBMISSION_PREVIEW; // needed by tabs.php
                break;
            case SURVEY_EDITRESPONSE:
                $this->currentpage = SURVEY_SUBMISSION_EDIT; // needed by tabs.php
                break;
            case SURVEY_READONLYRESPONSE:
                $this->currentpage = SURVEY_SUBMISSION_READONLY; // needed by tabs.php
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $action = '.$action);
        }
    }

    /*
     * survey_add_custom_css
     *
     * @param
     * @return
     */
    function survey_add_custom_css() {
        global $PAGE;

        $filearea = SURVEY_STYLEFILEAREA;

        $fs = get_file_storage();
        if ($files = $fs->get_area_files($this->context->id, 'mod_survey', $filearea, 0, 'sortorder', false)) {
            $PAGE->requires->css('/mod/survey/userstyle.php?id='.$this->survey->id.'&amp;cmid='.$this->cm->id); // not overridable via themes!
        }
    }

    /*
     * assign_pages
     *
     * @param
     * @return
     */
    public function assign_pages() {
        global $DB;

        if ($this->maxassignedpage) {
            return;
        }

        $where = array();
        $where['surveyid'] = $this->survey->id;
        $where['hide'] = 0;

        $lastwaspagebreak = true; // whether 2 page breaks in line, the second one is ignored
        $pagenumber = 1;
        $items = $DB->get_recordset('survey_item', $where, 'sortindex', 'id, type, plugin, parentid, formpage, sortindex');
        if ($items) {
            foreach ($items as $item) {
                if ($item->plugin == 'pagebreak') { // it is a page break
                    if (!$lastwaspagebreak) {
                        $pagenumber++;
                    }
                    $lastwaspagebreak = true;
                    continue;
                } else {
                    $lastwaspagebreak = false;
                }
                if ($this->survey->newpageforchild) {
                    $item_parentid = $item->parentid;
                    if (!empty($item_parentid)) {
                        $parentpage = $DB->get_field('survey_item', 'formpage', array('id' => $item->parentid), MUST_EXIST);
                        if ($parentpage == $pagenumber) {
                            $pagenumber++;
                        }
                    }
                }
// echo 'assegno pagine: $DB->set_field(\'survey_item\', \'formpage\', '.$pagenumber.', array(\'id\' => '.$item->id.'));<br />';
                $DB->set_field('survey_item', 'formpage', $pagenumber, array('id' => $item->id));
            }
            $items->close();
        }
    }

    /*
     * there are items spreading out their value over more than one single field
     * so you may have more than one $this->formdata element referring to the same item
     * Es.:
     *   $fieldname = survey_datetime_1452_day
     *   $fieldname = survey_datetime_1452_year
     *   $fieldname = survey_datetime_1452_month
     *   $fieldname = survey_datetime_1452_hour
     *   $fieldname = survey_datetime_1452_minute
     *
     *   $fieldname = survey_select_1452_select
     *
     *   $fieldname = survey_age_1452_check
     *
     *   $fieldname = survey_rate_1452_group
     *   $fieldname = survey_rate_1452_1
     *   $fieldname = survey_rate_1452_2
     *   $fieldname = survey_rate_1452_3
     *
     *   $fieldname = survey_radio_1452_noanswer
     *   $fieldname = survey_radio_1452_text
     *
     * This method performs the following task:
     * 1. groups informations (eventually distributed over more mform elements)
     *    by itemid in the array $infoperitem
     *
     *    i.e.:
     *    $infoperitem = Array (
     *        [148] => stdClass Object (
     *            [surveyid] => 1
     *            [submissionid] => 60
     *            [type] => field
     *            [plugin] => age
     *            [itemid] => 148
     *            [extra] => Array (
     *                [year] => 5
     *                [month] => 9
     *            )
     *        )
     *        [149] => stdClass Object (
     *            [surveyid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => boolean
     *            [itemid] => 149
     *            [extra] => Array (
     *                [noanswer] => 1
     *            )
     *        )
     *        [150] => stdClass Object (
     *            [surveyid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => character
     *            [itemid] => 150
     *            [extra] => Array (
     *                [mainelement] => horse
     *            )
     *        )
     *        [151] => stdClass Object (
     *            [surveyid] => 1
     *            [submissionid] => 60
     *            [type] => field
     *            [plugin] => fileupload
     *            [itemid] => 151
     *            [extra] => Array (
     *                [filemanager] => 667420320
     *            )
     *        )
     * 2. once $infoperitem is onboard...
     *    I update or I create the corresponding record
     *    asking to the parent class to manage its own data
     *    passing it $iteminfo->extra
     */
    public function save_user_data() {
        global $DB;

        // ////////////////////////////
        // begin by saving survey_submissions first
        $this->save_survey_submissions();
        // in this method I also assign $this->submissionid and $this->status
        // end of: begin by saving survey_submissions first
        // ////////////////////////////

        // save now all the answers provided by the user
        $regexp = '~'.SURVEY_ITEMPREFIX.'_('.SURVEY_TYPEFIELD.'|'.SURVEY_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';

        $infoperitem = array();
        foreach ($this->formdata as $itemname => $content) {
            // var_dump($matches);
            // $matches = array{
            //   0 => string 'survey_field_radiobutton_1452' (length=27)
            //   1 => string 'field' (length=5)
            //   2 => string 'radiobutton' (length=11)
            //   3 => string '1452' (length=4)
            // }
            // $matches = array{
            //   0 => string 'survey_field_radiobutton_1452_check' (length=33)
            //   1 => string 'field' (length=5)
            //   2 => string 'radiobutton' (length=11)
            //   3 => string '1452' (length=4)
            //   4 => string 'check' (length=5)
            // }
            // $matches = array{}
            //   0 => string 'survey_field_checkbox_1452_73' (length=30)
            //   1 => string 'field' (length=5)
            //   2 => string 'checkbox' (length=8)
            //   3 => string '1452' (length=4)
            //   4 => string '73' (length=2)
            if (!preg_match($regexp, $itemname, $matches)) { // HERE I ONLY ALLOW ITEMS WITH NAME STARTING WITH SURVEY_ITEMPREFIX
                                                             // ITEMS STARTING WITH SURVEY_NEGLECTPREFIX ARE DISCARDED HERE
                // button or something not relevant
                switch ($itemname) {
                    case 's': // <-- s is the survey id
                        $surveyid = $content;
                        break;
                    default:
                        // this is the black hole where is thrown each useless info like:
                        // - formpage
                        // - nextbutton
                        // - placeholders
                        // and some more
                }
                continue; // to next foreach
            }

            $itemid = $matches[3]; // itemid of the mform element (o of the group of mform elements referring to the same item)
            if (!isset($infoperitem[$itemid])) {
                $infoperitem[$itemid] = new stdClass();
                $infoperitem[$itemid]->surveyid = $surveyid;
                $infoperitem[$itemid]->submissionid = $this->submissionid;
                $infoperitem[$itemid]->type = $matches[1];
                $infoperitem[$itemid]->plugin = $matches[2];
                $infoperitem[$itemid]->itemid = $itemid;
                if (!isset($matches[4])) {
                    $infoperitem[$itemid]->extra['mainelement'] = $content;
                } else {
                    $infoperitem[$itemid]->extra[$matches[4]] = $content;
                }
            } else {
                $infoperitem[$itemid]->extra[$matches[4]] = $content;
            }
        }

        // if (isset($infoperitem)) {
        //     echo '$infoperitem = <br />';
        //     print_object($infoperitem);
        // } else {
        //     echo 'Nothing has been found<br />';
        // }

        // once $infoperitem is onboard...
        //    I update/create the corresponding record
        //    asking to parent class to manage its informations
        //    I Pass to the parent class the $iteminfo->extra

        foreach ($infoperitem as $iteminfo) {
            if (!$userdata_record = $DB->get_record('survey_userdata', array('submissionid' => $iteminfo->submissionid, 'itemid' => $iteminfo->itemid))) {
                // Quickly make one new!
                $userdata_record = new stdClass();
                $userdata_record->surveyid = $iteminfo->surveyid;
                $userdata_record->submissionid = $iteminfo->submissionid;
                $userdata_record->itemid = $iteminfo->itemid;
                $userdata_record->content = 'dummy_content';

                $id = $DB->insert_record('survey_userdata', $userdata_record);
                $userdata_record = $DB->get_record('survey_userdata', array('id' => $id));
            }
            $userdata_record->timecreated = time();

            $item = survey_get_item($iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

            // in this method I update $userdata_record->content
            // I do not really save to database
            $item->userform_save_preprocessing($iteminfo->extra, $userdata_record);

            if ($userdata_record->content != 'dummy_content') {
                $DB->update_record('survey_userdata', $userdata_record);
            } else {
                throw new moodle_exception('Wrong $userdata_record! \'dummy_content\' has not been replaced.');
            }
        }
    }

    /*
     * save_survey_submissions
     *
     * @param
     * @return survey_submissions record
     */
    public function save_survey_submissions() {
        global $USER, $DB;

        if (!$this->survey->newpageforchild) {
            $this->drop_unexpected_values();
        }

        $timenow = time();
        $savebutton = (isset($this->formdata->savebutton) && ($this->formdata->savebutton));
        $saveasnewbutton = (isset($this->formdata->saveasnewbutton) && ($this->formdata->saveasnewbutton));
        $nextbutton = (isset($this->formdata->nextbutton) && ($this->formdata->nextbutton));
        if ($saveasnewbutton) {
            $this->formdata->submissionid = 0;
        }

        $survey_submissions = new stdClass();
        if (empty($this->formdata->submissionid)) {
            // add a new record to survey_submissions
            $survey_submissions->surveyid = $this->survey->id;
            $survey_submissions->userid = $USER->id;
            $survey_submissions->timecreated = $timenow;

            // submit buttons are 3 and only 3
            if ($nextbutton) {
                $survey_submissions->status = SURVEY_STATUSINPROGRESS;
            }
            if ($savebutton || $saveasnewbutton) {
                $survey_submissions->status = SURVEY_STATUSCLOSED;
            }

            $survey_submissions->id = $DB->insert_record('survey_submissions', $survey_submissions);

        } else {
            // survey_submissions already exists
            // but I asked to save
            if ($savebutton) {
                $survey_submissions->id = $this->formdata->submissionid;
                $survey_submissions->status = SURVEY_STATUSCLOSED;
                $survey_submissions->timemodified = $timenow;
                $DB->update_record('survey_submissions', $survey_submissions);
            } else {
                // I have $this->formdata->submissionid
                // case: "save" was requested, I am not here
                // case: "save as" was requested, I am not here
                // case: "next" was requested, so status = SURVEY_STATUSINPROGRESS
                $status = $DB->get_field('survey_submissions', 'status', array('id' => $this->formdata->submissionid), MUST_EXIST);
                $survey_submissions->id = $this->formdata->submissionid;
                $survey_submissions->status = $status;
            }
        }

        $this->submissionid = $survey_submissions->id;
        $this->status = $survey_submissions->status;
    }

    /*
     * notifyroles
     *
     * @param
     * @return
     */
    public function notifyroles() {
        global $CFG, $DB, $COURSE;

        require_once($CFG->dirroot.'/group/lib.php');

        if ($this->status != SURVEY_STATUSCLOSED) {
            return;
        }
        if (empty($this->survey->notifyrole) && empty($this->survey->notifymore)) {
            return;
        }

        // course context used locally to get groups
        $context = context_course::instance($COURSE->id);

        $mygroups = survey_get_my_groups($this->cm);
        if (count($mygroups)) {
            if ($this->survey->notifyrole) {
                $roles = explode(',', $this->survey->notifyrole);
                $receivers = array();
                foreach ($mygroups as $mygroup) {
                    $groupmemberroles = groups_get_members_by_role($mygroup, $COURSE->id, 'u.firstname, u.lastname, u.email');

                    foreach ($roles as $role) {
                        if (isset($groupmemberroles[$role])) {
                            $roledata = $groupmemberroles[$role];

                            foreach ($roledata->users as $member) {
                                $singleuser = new stdClass();
                                $singleuser->id = $member->id;
                                $singleuser->firstname = $member->firstname;
                                $singleuser->lastname = $member->lastname;
                                $singleuser->email = $member->email;
                                $receivers[] = $singleuser;
                            }
                        }
                    }
                }
            } else {
                // notification was not requested
                $receivers = array();
            }
        } else {
            if ($this->survey->notifyrole) {
                // get_enrolled_users($courseid, $options = array()) <-- role is missing
                // get_users_from_role_on_context($role, $context);  <-- this is ok but I need to call it once per $role, below I make the query once all together
                $sql = 'SELECT DISTINCT ra.userid, u.firstname, u.lastname, u.email
                        FROM (SELECT *
                              FROM {role_assignments}
                              WHERE contextid = '.$context->id.'
                                  AND roleid IN ('.$this->survey->notifyrole.')) ra
                        JOIN {user} u ON u.id = ra.userid';
                $receivers = $DB->get_records_sql($sql);
            } else {
                // notification was not requested
                $receivers = array();
            }
        }

        if (!empty($this->survey->notifymore)) {
            $morereceivers = survey_textarea_to_array($this->survey->notifymore);
            foreach ($morereceivers as $extraemail) {
                $singleuser = new stdClass();
                $singleuser->id = null;
                $singleuser->firstname = '';
                $singleuser->lastname = '';
                $singleuser->email = $extraemail;
                $receivers[] = $singleuser;
            }
        }

        $mailheader = '<head></head>
    <body id="email"><div>';
        $mailfooter = '</div></body>';

        $from = new object;
        $from->firstname = $COURSE->shortname;
        $from->lastname = $this->survey->name;
        $from->email = $CFG->noreplyaddress;
        $from->maildisplay = 1;
        $from->mailformat = 1;

        $htmlbody = $mailheader;
        $htmlbody .= get_string('newsubmissionbody', 'survey', $this->survey->name);
        $htmlbody .= $mailfooter;

        $body = strip_tags($htmlbody);

        $subject = get_string('newsubmissionsubject', 'survey');

        $recipient = new object;
        $recipient->maildisplay = 1;
        $recipient->mailformat = 1;

        foreach ($receivers as $receiver) {
            $recipient->firstname = $receiver->firstname;
            $recipient->lastname = $receiver->lastname;
            $recipient->email = $receiver->email;

            email_to_user($recipient, $from, $subject, $body, $htmlbody);
        }
    }

    /*
     * count_input_items
     *
     * @param
     * @return
     */
    public function count_input_items() {
        global $DB;

        // if no items are available, stop the intervention here
        $whereparams = array('surveyid' => $this->survey->id);
        $whereclause = 'surveyid = :surveyid AND hide = 0';
        if (!$this->canaccessadvanceditems) {
            $whereclause .= ' AND limitedaccess = 0';
        }
        return $DB->count_records_select('survey_item', $whereclause, $whereparams);
    }

    /*
     * noitem_stopexecution
     *
     * @param
     * @return
     */
    public function noitem_stopexecution() {
        global $COURSE, $OUTPUT;

        $message = ($this->canaccessadvanceditems) ? get_string('noadvanceditemsfound', 'survey') : get_string('nobasicitemsfound', 'survey');
        echo $OUTPUT->notification($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

        if ($this->canmanageitems) {
            $continueurl = new moodle_url('/mod/survey/items_add.php', array('s' => $this->survey->id));
        } else {
            $continueurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
        }
        echo $OUTPUT->continue_button($continueurl);
        echo $OUTPUT->footer();
        die;
    }

    /*
     * submissions_allowed
     *
     * @param
     * @return
     */
    public function submissions_allowed() {
        // if $this->formdata is available, this means that the form was already displayed and submitted
        // so it is not the time to say the user is not allowed to submit one more survey
        if ($this->formdata) {
            return true;
        }
        if (!$this->survey->maxentries) {
            return true;
        }

        return ($this->user_closed_submissions(SURVEY_STATUSALL) < $this->survey->maxentries);
    }

    /*
     * user_closed_submissions
     *
     * @param
     * @return
     */
    public function user_closed_submissions($status=SURVEY_STATUSALL) {
        global $USER, $DB;

        $params = array('surveyid' => $this->survey->id, 'userid' => $USER->id);
        if ($status != SURVEY_STATUSALL) {
            $statuslist = array(SURVEY_STATUSCLOSED, SURVEY_STATUSINPROGRESS);
            if (!in_array($status, $statuslist)) {
                throw new moodle_exception('invalid $status passed to user_closed_submissions in '.__LINE__.' of file '.__FILE__);
            }
            $params['status'] = $status;
        }

        return $DB->count_records('survey_submissions', $params);
    }

    /*
     * submissions_exceeded_stopexecution
     *
     * @param
     * @return
     */
    public function submissions_exceeded_stopexecution() {
        global $OUTPUT;

        $message = get_string('nomorerecordsallowed', 'survey', $this->survey->maxentries);
        echo $OUTPUT->notification($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

        $params = array('id' => $this->cm->id);
        $continueurl = new moodle_url('view_manage.php', $params);

        echo $OUTPUT->continue_button($continueurl);
        echo $OUTPUT->footer();
        die;
    }

    /*
     * manage_thanks_page
     *
     * @param
     * @return
     */
    public function manage_thanks_page() {
        global $OUTPUT;

        $savebutton = (isset($this->formdata->savebutton) && ($this->formdata->savebutton));
        $saveasnewbutton = (isset($this->formdata->saveasnewbutton) && ($this->formdata->saveasnewbutton));
        if ($savebutton || $saveasnewbutton) {
            $this->show_thanks_page();
            echo $OUTPUT->footer();
            die;
        }
    }

    /*
     * survey_show_thanks_page
     *
     * @param
     * @return
     */
    public function show_thanks_page() {
        global $DB, $OUTPUT, $USER;

        if (!empty($this->survey->thankshtml)) {
            $message = file_rewrite_pluginfile_urls($this->survey->thankshtml, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_THANKSHTMLFILEAREA, $this->survey->id);
        } else {
            $message = get_string('defaultthanksmessage', 'survey');
        }

        $paramurl = array('id' => $this->cm->id);
        // just to save a query
        $alreadysubmitted = empty($this->survey->maxentries) ? 0 : $DB->count_records('survey_submissions', array('surveyid' => $this->survey->id, 'userid' => $USER->id));
        if (($alreadysubmitted < $this->survey->maxentries) || empty($this->survey->maxentries)) { // if the user is allowed to submit one more survey
            $buttonurl = new moodle_url('view.php', $paramurl);
            $onemore = new single_button($buttonurl, get_string('onemorerecord', 'survey'));

            $buttonurl = new moodle_url('view_manage.php', $paramurl);
            $gotolist = new single_button($buttonurl, get_string('gotolist', 'survey'));

            echo $OUTPUT->confirm($message, $onemore, $gotolist);
        } else {
            echo $OUTPUT->box($message, 'notice centerpara');
            $buttonurl = new moodle_url('view_manage.php', $paramurl);
            echo $OUTPUT->single_button($buttonurl, get_string('gotolist', 'survey'));
        }
    }

    /*
     * message_preview_mode
     *
     * @param
     * @return
     */
    public function message_preview_mode() {
        global $OUTPUT;

        $previewmodestring = get_string('previewmode', 'survey');
        echo $OUTPUT->heading($previewmodestring, 2);
    }

    /*
     * display_page_x_of_y
     *
     * @param
     * @return
     */
    public function display_page_x_of_y() {
        global $OUTPUT;

        if ($this->maxassignedpage > 1) {
            // if $formpage == 0 no more pages with items are available
            $a = new stdclass();
            $a->formpage = $this->formpage;
            if ($this->formpage == SURVEY_LEFT_OVERFLOW) {
                $a->formpage = 1;
            }
            if ($this->formpage == SURVEY_RIGHT_OVERFLOW) {
                $a->formpage = $this->maxassignedpage;
            }

            $a->maxassignedpage = $this->maxassignedpage;
            echo $OUTPUT->heading(get_string('pagexofy', 'survey', $a));
        }
    }

    /*
     * get_prefill_data
     *
     * @param
     * @return
     */
    public function get_prefill_data() {
        global $DB;

        $prefill = array();
        // $canaccessadvanceditems, $searchform=false, $type=SURVEY_TYPEFIELD, $formpage=$this->formpage
        list($sql, $params) = survey_fetch_items_seeds($this->survey->id, $this->canaccessadvanceditems, false, SURVEY_TYPEFIELD, $this->formpage);
        if ($itemseeds = $DB->get_recordset_sql($sql, $params)) {
            foreach ($itemseeds as $itemseed) {
                $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

                $olduserdata = $DB->get_record('survey_userdata', array('submissionid' => $this->submissionid, 'itemid' => $item->get_itemid()));
                $singleprefill = $item->userform_set_prefill($olduserdata);
                $prefill = array_merge($prefill, $singleprefill);
            }
            $itemseeds->close();
        }

        $prefill['submissionid'] = $this->submissionid;

        return $prefill;
    }

    /*
     * drop_unexpected_values
     *
     * @param
     * @return
     */
    public function drop_unexpected_values() {
        // BEGIN: delete all the bloody values that were NOT supposed to be returned: MDL-34815
        $dirtydata = (array)$this->formdata;
        $indexes = array_keys($dirtydata);

        $disposelist = array();
        $olditemid = 0;
        $regexp = '~'.SURVEY_ITEMPREFIX.'_('.SURVEY_TYPEFIELD.'|'.SURVEY_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
        foreach ($indexes as $itemname) {
            if (!preg_match($regexp, $itemname, $matches)) { // if it starts with SURVEY_ITEMPREFIX_
                continue;
            }
            $type = $matches[1]; // item type
            $plugin = $matches[2]; // item plugin
            $itemid = $matches[3]; // item id

            if ($itemid == $olditemid) {
                continue;
            }

            // let's start
            $olditemid = $itemid;

            $childitem = survey_get_item($itemid, $type, $plugin);

            if (empty($childitem->parentid)) {
                continue;
            }

            // if my parent is already in $disposelist, I have to go to $disposelist FOR SURE
            if (in_array($childitem->parentid, $disposelist)) {
                $disposelist[] = $childitem->itemid;
                continue;
            }

            // call parentitem
            $parentitem = survey_get_item($childitem->parentid);

            $parentinsamepage = false;
            foreach ($indexes as $itemname) {
                if (strpos($itemname, $parentitem->itemid)) {
                    $parentinsamepage = true;
                    break;
                }
            }

            if ($parentinsamepage) { // if parent is in this same page
                // tell parentitem what child needs in order to be displayed and compare it with what was answered to parentitem ($dirtydata)
                $expectedvalue = $parentitem->userform_child_item_allowed_dynamic($childitem->parentcontent, $dirtydata);
                // parentitem, knowing itself, compare what is needed and provide an answer

                if (!$expectedvalue) {
                    $disposelist[] = $childitem->itemid;
                }
            }
        } // check next item
        // END: delete all the bloody values that were supposed to NOT be returned: MDL-34815

        // if not expected items are here...
        if (count($disposelist)) {
            $regexp = '~'.SURVEY_ITEMPREFIX.'_('.SURVEY_TYPEFIELD.'|'.SURVEY_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
            foreach ($indexes as $itemname) {
                if (preg_match($regexp, $itemname, $matches)) {
                    // $type = $matches[1]; // item type
                    // $plugin = $matches[2]; // item plugin
                    $itemid = $matches[3]; // item id
                    // $option = $matches[4]; // _text or _noanswer or...
                    if (in_array($itemid, $disposelist)) {
                        unset($this->formdata->$itemname);
                    }
                }
            }
        }
    }

    /*
     * prevent_direct_user_input
     *
     * @param
     * @return
     */
    public function prevent_direct_user_input() {
        global $DB, $USER;

        if ($this->canmanageallsubmissions) {
            return true;
        }
        $submission = $DB->get_record('survey_submissions', array('id' => $this->submissionid), '*', IGNORE_MISSING);

        $allowed = true;
        $mygroups = survey_get_my_groups($this->cm);
        switch ($this->action) {
            case SURVEY_NOACTION:
                $allowed = has_capability('mod/survey:view', $this->context);
                break;
            case SURVEY_PREVIEWSURVEY:
                $condition1 = ($submission);
                $condition2 = has_capability('mod/survey:preview', $this->context);
                $allowed = $condition1 && $condition2;
                break;
            case SURVEY_EDITRESPONSE:
                $condition1 = ($submission);
                $condition2 = has_extrapermission('edit', $this->survey, $mygroups, $submission->userid);
                $condition3 = ($submission->userid == $USER->id) && ($submission->status == SURVEY_STATUSINPROGRESS);
                $allowed = $condition1 && ($condition2 || $condition3);
                break;
            case SURVEY_READONLYRESPONSE:
                $condition1 = ($submission);
                $condition2 = has_extrapermission('read', $this->survey, $mygroups, $submission->userid);
                $allowed = $condition1 && $condition2;
                break;
            default:
                $allowed = false;
        }
        if (!$allowed) {
            print_error('incorrectaccessdetected', 'survey');
        }
    }

    /*
     * duplicate_submission
     *
     * @param $allpages
     * @return
     */
    public function duplicate_submission() {
        global $DB;

        $survey_submissions = $DB->get_record('survey_submissions', array('id' => $this->submissionid));
        $survey_submissions->timecreated = time();
        $survey_submissions->status = SURVEY_STATUSINPROGRESS;
        unset($survey_submissions->timemodified);
        $submissionid = $DB->insert_record('survey_submissions', $survey_submissions);

        $survey_userdata = $DB->get_recordset('survey_userdata', array('submissionid' => $this->submissionid));
        foreach ($survey_userdata as $survey_userdatum) {
            unset($survey_userdatum->id);
            $survey_userdatum->submissionid = $submissionid;
            $DB->insert_record('survey_userdata', $survey_userdatum);
        }
        $survey_userdata->close();
        $this->submissionid = $submissionid;
    }
}