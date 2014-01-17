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
class mod_survey_userformmanager {
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
     * $firstpageright
     */
    public $firstpageright = 0;

    /*
     * $firstpageleft
     */
    public $firstpageleft = 0;

    /*
     * $view
     */
    public $view = SURVEY_SERVESURVEY;

    /*
     * $moduletab: The tab of the module where the page will be shown
     */
    public $moduletab = '';

    /*
     * $modulepage: this is the page of the module. Nothing to share with $formpage
     */
    public $modulepage = '';

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
    // public $canmanageitems = false;

    /*
     * $cansubmit
     */
    public $cansubmit = false;

    /*
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /*
     * Class constructor
     */
    public function __construct($cm, $survey, $submissionid, $formpage, $view) {
        global $DB;

        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);
        $this->survey = $survey;
        $this->submissionid = $submissionid;
        $this->view = $view;
        $this->set_page_from_view();

        // $this->canmanageitems = has_capability('mod/survey:manageitems', $this->context, null, true);
        $this->canaccessadvanceditems = has_capability('mod/survey:accessadvanceditems', $this->context, null, true);
        $this->cansubmit = has_capability('mod/survey:submit', $this->context, null, true);
        $this->canmanageallsubmissions = has_capability('mod/survey:manageallsubmissions', $this->context, null, true);

        // assign pages to items
        if (!$this->maxassignedpage = $DB->get_field('survey_item', 'MAX(formpage)', array('surveyid' => $survey->id))) {
            $this->assign_pages();
        }

        // calculare $this->firstpageright
        if ($this->canaccessadvanceditems) {
            $this->firstpageright = 1;
        } else {
            $this->next_not_empty_page(true, 0, $view); // this calculates $this->firstformpage
        }

        if ($formpage == 0) { // you are viewing the survey for the first time
            $this->formpage = $this->firstpageright;
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
    public function next_not_empty_page($forward, $startingpage, $modulepage) {
        // depending on user provided answer, in the previous or next page there may be no items to display
        // get the first page WITH items
        //
        // this method writes:
        // if $forward == true:
        //     the page number of the first non empty page (according to user answers) in $this->firstpageright
        //     returns $nextpage or SURVEY_RIGHT_OVERFLOW if no more empty pages are found on the right
        // if $forward == false:
        //     the page number of the bigger non empty page lower than $startingpage (according to user answers) in $this->firstpageleft
        //     returns $nextpage or SURVEY_LEFT_OVERFLOW if no more empty pages are found on the left

        if ($modulepage == SURVEY_ITEMS_PREVIEW) { // I do not care relation, I am in "preview mode"
            if ($forward) {
                $this->firstpageright = ++$startingpage;
            } else {
                $this->firstpageleft = --$startingpage;
            }
            return;
        }

        $condition1 = ($startingpage == SURVEY_RIGHT_OVERFLOW) && ($forward);
        $condition2 = ($startingpage == SURVEY_LEFT_OVERFLOW) && (!$forward);
        if ($condition1 || $condition2) {
            print_error('Wrong direction required in next_not_empty_page whether $startingpage == SURVEY_RIGHT_OVERFLOW');
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
            $this->firstpageright = ($nextpage == $overflowpage) ? SURVEY_RIGHT_OVERFLOW : $nextpage;
        } else {
            $this->firstpageleft = ($nextpage == $overflowpage) ? SURVEY_LEFT_OVERFLOW : $nextpage;
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

        // $canaccessadvanceditems, $searchform=false, $type=SURVEY_TYPEFIELD, $formpage=$formpage
        list($sql, $whereparams) = survey_fetch_items_seeds($this->survey->id, $this->canaccessadvanceditems, false, false, $formpage);
        $itemseeds = $DB->get_records_sql($sql, $whereparams);

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
            $parentitem = new $itemclass($itemseed->parentid, false);

            if ($parentitem->userform_child_item_allowed_static($this->submissionid, $itemseed)) {
                // if (userform_child_item_allowed_static($this->submissionid, $itemseed)) {
                return true;
            }
        }

        // if you're not able to get out in the two previous occasions ... declares defeat
        return false;
    }

    /*
     * set_page_from_view
     *
     * @param
     * @return
     */
    public function set_page_from_view() {
        switch ($this->view) {
            case SURVEY_NOACTION:
                $this->moduletab = SURVEY_TABSUBMISSIONS; // needed by tabs.php
                $this->modulepage = SURVEY_SUBMISSION_ATTEMPT; // needed by tabs.php
                break;
            case SURVEY_PREVIEWSURVEY:
                $this->moduletab = SURVEY_TABITEMS; // needed by tabs.php
                $this->modulepage = SURVEY_ITEMS_PREVIEW; // needed by tabs.php
                break;
            case SURVEY_EDITRESPONSE:
                $this->moduletab = SURVEY_TABSUBMISSIONS; // needed by tabs.php
                $this->modulepage = SURVEY_SUBMISSION_EDIT; // needed by tabs.php
                break;
            case SURVEY_READONLYRESPONSE:
                $this->moduletab = SURVEY_TABSUBMISSIONS; // needed by tabs.php
                $this->modulepage = SURVEY_SUBMISSION_READONLY; // needed by tabs.php
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->view = '.$this->view);
        }
    }

    /*
     * survey_add_custom_css
     *
     * @param
     * @return
     */
    public function survey_add_custom_css() {
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
                    $parentitemid = $item->parentid;
                    if (!empty($parentitemid)) {
                        $parentpage = $DB->get_field('survey_item', 'formpage', array('id' => $item->parentid), MUST_EXIST);
                        if ($parentpage == $pagenumber) {
                            $pagenumber++;
                        }
                    }
                }
                // echo 'Assigning pages: $DB->set_field(\'survey_item\', \'formpage\', '.$pagenumber.', array(\'id\' => '.$item->id.'));<br />';
                $DB->set_field('survey_item', 'formpage', $pagenumber, array('id' => $item->id));
            }
            $items->close();
            $this->maxassignedpage = $pagenumber;
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
     *            [contentperelement] => Array (
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
     *            [contentperelement] => Array (
     *                [noanswer] => 1
     *            )
     *        )
     *        [150] => stdClass Object (
     *            [surveyid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => character
     *            [itemid] => 150
     *            [contentperelement] => Array (
     *                [mainelement] => horse
     *            )
     *        )
     *        [151] => stdClass Object (
     *            [surveyid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => fileupload
     *            [itemid] => 151
     *            [contentperelement] => Array (
     *                [filemanager] => 667420320
     *            )
     *        )
     *        [185] => stdClass Object (
     *            [surveyid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => checkbox
     *            [itemid] => 185
     *            [contentperelement] => Array (
     *                [0] => 1
     *                [1] => 0
     *                [2] => 1
     *                [3] => 0
     *                [noanswer] => 0
     *            )
     *        )
     *        [186] => stdClass Object (
     *            [surveyid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => checkbox
     *            [itemid] => 186
     *            [contentperelement] => Array (
     *                [0] => 1
     *                [1] => 1
     *                [2] => 0
     *                [3] => 0
     *                [other] => 1
     *                [text] => Apple juice
     *                [noanswer] => 1
     *            )
     *        )
     * 2. once $infoperitem is onboard...
     *    I update or I create the corresponding record
     *    asking to the parent class to manage its own data
     *    passing it $iteminfo->contentperelement
     */
    public function save_user_data() {
        global $DB;

        // at each submission I need to save one 'survey_submission' and some 'survey_userdata'

        // -----------------------------
        // let's start by saving one record in survey_submission
        // in this method I also assign $this->submissionid and $this->status
        $this->save_survey_submission();
        // end of: let's start by saving one record in survey_submission
        // -----------------------------

        // save now all the answers provided by the user
        $regexp = '~('.SURVEY_ITEMPREFIX.'|'.SURVEY_PLACEHOLDERPREFIX.')_('.SURVEY_TYPEFIELD.'|'.SURVEY_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';

        $infoperitem = array();
        foreach ($this->formdata as $itemname => $content) {
            if (!preg_match($regexp, $itemname, $matches)) {
                // button or something not relevant
                switch ($itemname) {
                    case 's': // <-- s is the survey id
                        $surveyid = $content;
                        break;
                    default:
                        // this is the black hole where is thrown each useless info like:
                        // - formpage
                        // - nextbutton
                        // and some more
                }
                continue; // to next foreach
            }

            // var_dump($matches);
            // $matches = array{
            //   0 => string 'survey_field_radiobutton_1452' (length=27)
            //   1 => string 'survey' (length=6)
            //   2 => string 'field' (length=5)
            //   3 => string 'radiobutton' (length=11)
            //   4 => string '1452' (length=4)
            // }
            // $matches = array{
            //   0 => string 'survey_field_radiobutton_1452_check' (length=33)
            //   1 => string 'survey' (length=6)
            //   2 => string 'field' (length=5)
            //   3 => string 'radiobutton' (length=11)
            //   4 => string '1452' (length=4)
            //   5 => string 'check' (length=5)
            // }
            // $matches = array{}
            //   0 => string 'survey_field_checkbox_1452_73' (length=30)
            //   1 => string 'survey' (length=6)
            //   2 => string 'field' (length=5)
            //   3 => string 'checkbox' (length=8)
            //   4 => string '1452' (length=4)
            //   5 => string '73' (length=2)
            // $matches = array{}
            //   0 => string 'placeholder_field_multiselect_199_placeholder' (length=45)
            //   1 => string 'placeholder' (length=11)
            //   2 => string 'field' (length=5)
            //   3 => string 'multiselect' (length=11)
            //   4 => string '199' (length=3)
            //   5 => string 'placeholder' (length=11)

            $itemid = $matches[4]; // itemid of the mform element (or of the group of mform elements referring to the same item)
            if (!isset($infoperitem[$itemid])) {
                $infoperitem[$itemid] = new stdClass();
                $infoperitem[$itemid]->surveyid = $surveyid;
                $infoperitem[$itemid]->submissionid = $this->submissionid;
                $infoperitem[$itemid]->type = $matches[2];
                $infoperitem[$itemid]->plugin = $matches[3];
                $infoperitem[$itemid]->itemid = $itemid;
            }
            if (!isset($matches[5])) {
                $infoperitem[$itemid]->contentperelement['mainelement'] = $content;
            } else {
                $infoperitem[$itemid]->contentperelement[$matches[5]] = $content;
            }
        }

        // if (isset($infoperitem)) {
        //     echo '$infoperitem = <br />';
        //     print_object($infoperitem);
        // } else {
        //     echo 'Nothing has been found<br />';
        // }
        // die;

        // once $infoperitem is onboard...
        //    I update/create the corresponding record
        //    asking to each item class to manage its informations

        foreach ($infoperitem as $iteminfo) {
            if (!$userdatarec = $DB->get_record('survey_userdata', array('submissionid' => $iteminfo->submissionid, 'itemid' => $iteminfo->itemid))) {
                // Quickly make one new!
                $userdatarec = new stdClass();
                $userdatarec->surveyid = $iteminfo->surveyid;
                $userdatarec->submissionid = $iteminfo->submissionid;
                $userdatarec->itemid = $iteminfo->itemid;
                $userdatarec->content = '__my_dummy_content@@';
                $userdatarec->contentformat = null;

                $id = $DB->insert_record('survey_userdata', $userdatarec);
                $userdatarec = $DB->get_record('survey_userdata', array('id' => $id));
            }
            $userdatarec->timecreated = time();

            $item = survey_get_item($iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

            // in this method I update $userdatarec->content
            // I do not really save to database
            $item->userform_save_preprocessing($iteminfo->contentperelement, $userdatarec, false);

            if ($userdatarec->content != '__my_dummy_content@@') {
                $DB->update_record('survey_userdata', $userdatarec);
            } else {
                print_error('Wrong $userdatarec! \'__my_dummy_content@@\' has not been replaced.');
            }
        }
    }

    /*
     * save_survey_submission
     *
     * @param
     * @return survey_submission record
     */
    public function save_survey_submission() {
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

        $submissions = new stdClass();
        if (empty($this->formdata->submissionid)) {
            // add a new record to survey_submission
            $submissions->surveyid = $this->survey->id;
            $submissions->userid = $USER->id;
            $submissions->timecreated = $timenow;

            // submit buttons are 3 and only 3
            if ($nextbutton) {
                $submissions->status = SURVEY_STATUSINPROGRESS;
            }
            if ($savebutton || $saveasnewbutton) {
                $submissions->status = SURVEY_STATUSCLOSED;
            }

            $submissions->id = $DB->insert_record('survey_submission', $submissions);

        } else {
            // survey_submission already exists
            // but I asked to save
            if ($savebutton) {
                $submissions->id = $this->formdata->submissionid;
                $submissions->status = SURVEY_STATUSCLOSED;
                $submissions->timemodified = $timenow;
                $DB->update_record('survey_submission', $submissions);
            } else {
                // I have $this->formdata->submissionid
                // case: "save" was requested, I am not here
                // case: "save as" was requested, I am not here
                // case: "next" was requested, so status = SURVEY_STATUSINPROGRESS
                $status = $DB->get_field('survey_submission', 'status', array('id' => $this->formdata->submissionid), MUST_EXIST);
                $submissions->id = $this->formdata->submissionid;
                $submissions->status = $status;
            }
        }

        $this->submissionid = $submissions->id;
        $this->status = $submissions->status;
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

        $mygroups = groups_get_my_groups();
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

        $whereparams = array('surveyid' => $this->survey->id, 'formpage' => $this->formpage);
        $whereclause = 'surveyid = :surveyid AND hide = 0 AND formpage = :formpage';
        if (!$this->canaccessadvanceditems) {
            $whereclause .= ' AND advanced = 0';
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

        $message = get_string('noitemsfound', 'survey');
        echo $OUTPUT->notification($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

        $continueurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
        echo $OUTPUT->continue_button($continueurl);
        echo $OUTPUT->footer();
        die();
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

        $whereparams = array('surveyid' => $this->survey->id, 'userid' => $USER->id);
        if ($status != SURVEY_STATUSALL) {
            $statuslist = array(SURVEY_STATUSCLOSED, SURVEY_STATUSINPROGRESS);
            if (!in_array($status, $statuslist)) {
                print_error('invalid $status passed to user_closed_submissions in '.__LINE__.' of file '.__FILE__);
            }
            $whereparams['status'] = $status;
        }

        return $DB->count_records('survey_submission', $whereparams);
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

        $whereparams = array('id' => $this->cm->id);
        $continueurl = new moodle_url('view_manage.php', $whereparams);

        echo $OUTPUT->continue_button($continueurl);
        echo $OUTPUT->footer();
        die();
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
            die();
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
        $alreadysubmitted = empty($this->survey->maxentries) ? 0 : $DB->count_records('survey_submission', array('surveyid' => $this->survey->id, 'userid' => $USER->id));
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
            $a = new stdClass();
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
        list($sql, $whereparams) = survey_fetch_items_seeds($this->survey->id, $this->canaccessadvanceditems, false, SURVEY_TYPEFIELD, $this->formpage);
        if ($itemseeds = $DB->get_recordset_sql($sql, $whereparams)) {
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
                $expectedvalue = $parentitem->userform_child_item_allowed_dynamic($childitem->parentvalue, $dirtydata);
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
        global $DB, $USER, $COURSE;

        if ($this->canmanageallsubmissions) {
            return;
        }
        if (!empty($this->submissionid)) {
            if (!$submission = $DB->get_record('survey_submission', array('id' => $this->submissionid), '*', IGNORE_MISSING)) {
                print_error('incorrectaccessdetected', 'survey');
            }
        }

        if ($courseisgrouped = groups_get_all_groups($COURSE->id)) {
            $mygroupmates = survey_groupmates();
        }

        switch ($this->view) {
            case SURVEY_SERVESURVEY:
                $allowed = has_capability('mod/survey:submit', $this->context);
                break;
            case SURVEY_PREVIEWSURVEY:
                $allowed = has_capability('mod/survey:preview', $this->context);
                break;
            case SURVEY_EDITRESPONSE:
                if ($USER->id == $submission->userid) {
                    $allowed = has_capability('mod/survey:editownsubmissions', $this->context);
                } else {
                    $allowed = false;
                }
                if (!$allowed) {
                    if ($courseisgrouped) {
                        if (in_array($submission->userid, $mygroupmates)) {
                            $allowed = has_capability('mod/survey:editgroupmatessubmissions', $this->context);
                        } else {
                            $allowed = has_capability('mod/survey:editothergroupsubmissions', $this->context);
                        }
                    } else {
                        $allowed = has_capability('mod/survey:editotherssubmissions', $this->context);
                    }
                }
                if (!$allowed) {
                    $allowed = has_capability('mod/survey:manageallsubmissions', $this->context);
                }
                break;
            case SURVEY_READONLYRESPONSE:
                $allowed = ($USER->id == $submission->userid);
                if (!$allowed) {
                    if ($courseisgrouped) {
                        if (in_array($submission->userid, $mygroupmates)) {
                            $allowed = has_capability('mod/survey:seegroupmatessubmissions', $this->context);
                        } else {
                            $allowed = has_capability('mod/survey:seeothergroupsubmissions', $this->context);
                        }
                    } else {
                        $allowed = has_capability('mod/survey:seeotherssubmissions', $this->context);
                    }
                }
                if (!$allowed) {
                    $allowed = has_capability('mod/survey:manageallsubmissions', $this->context);
                }
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

        $submissions = $DB->get_record('survey_submission', array('id' => $this->submissionid));
        $submissions->timecreated = time();
        $submissions->status = SURVEY_STATUSINPROGRESS;
        unset($submissions->timemodified);
        $submissionid = $DB->insert_record('survey_submission', $submissions);

        $surveyuserdata = $DB->get_recordset('survey_userdata', array('submissionid' => $this->submissionid));
        foreach ($surveyuserdata as $userdatum) {
            unset($userdatum->id);
            $userdatum->submissionid = $submissionid;
            $DB->insert_record('survey_userdata', $userdatum);
        }
        $surveyuserdata->close();
        $this->submissionid = $submissionid;
    }

    /*
     * display_cover
     *
     * @param $allpages
     * @return
     */
    public function display_cover() {
        global $OUTPUT, $CFG, $COURSE;

        $canaccessreports = has_capability('mod/survey:accessreports', $this->context, null, true);;
        $canaccessownreports = has_capability('mod/survey:accessownreports', $this->context, null, true);
        $canmanageusertemplates = has_capability('mod/survey:manageusertemplates', $this->context, null, true);
        $cansaveusertemplate = has_capability('mod/survey:saveusertemplates', context_course::instance($COURSE->id), null, true);
        $canimportusertemplates = has_capability('mod/survey:importusertemplates', $this->context, null, true);
        $canapplyusertemplates = has_capability('mod/survey:applyusertemplates', $this->context, null, true);
        $cansavemastertemplates = has_capability('mod/survey:savemastertemplates', $this->context, null, true);
        $canapplymastertemplates = has_capability('mod/survey:applymastertemplates', $this->context, null, true);
        $riskyediting = ($this->survey->riskyeditdeadline > time());
        $hassubmissions = survey_count_submissions($this->survey->id);

        $messages = array();
        $timenow = time();

        // is the button to add one more survey going to be displayed?
        $displaybutton = true;
        $displaybutton = $displaybutton && $this->cansubmit;
        if ($this->survey->timeopen) {
            $displaybutton = $displaybutton && ($this->survey->timeopen < $timenow);
        }
        if ($this->survey->timeclose) {
            $displaybutton = $displaybutton && ($this->survey->timeclose > $timenow);
        }
        $displaybutton = $displaybutton && (($this->survey->maxentries == 0) || ($next < $this->survey->maxentries));
        // End of: is the button to add one more survey going to be displayed?

        echo $OUTPUT->heading(get_string('coverpage_welcome', 'survey', $this->survey->name));
        if ($this->survey->intro) {
            $intro = file_rewrite_pluginfile_urls($this->survey->intro, 'pluginfile.php', $this->context->id, 'mod_survey', 'intro', null);
            echo $OUTPUT->box($intro, 'generalbox description', 'intro');
        }

        // general info
        if ($this->survey->timeopen) { // opening time:
            $key = ($this->survey->timeopen > $timenow) ? 'willopen' : 'opened';
            $messages[] = get_string($key, 'survey').': '.userdate($this->survey->timeopen);
        }

        if ($this->survey->timeclose) { // closing time:
            $key = ($this->survey->timeclose > $timenow) ? 'willclose' : 'closed';
            $messages[] = get_string($key, 'survey').': '.userdate($this->survey->timeclose);
        }

        if ($this->cansubmit) {
            // maxentries:
            $maxentries = ($this->survey->maxentries) ? $this->survey->maxentries : get_string('unlimited', 'survey');
            $messages[] = get_string('maxentries', 'survey').': '.$maxentries;

            // your closed attempt number:
            $countclosed = $this->user_closed_submissions(SURVEY_STATUSCLOSED);
            $next = $countclosed;
            $messages[] = get_string('closedsubmissions', 'survey', $countclosed);

            // your in progress attempt number:
            $inprogress = $this->user_closed_submissions(SURVEY_STATUSINPROGRESS);
            $next += $inprogress;
            $messages[] = get_string('inprogresssubmissions', 'survey', $inprogress);

            $next++;
            if ($displaybutton) {
                $messages[] = get_string('yournextattempt', 'survey', $next);
            }
        }

        $this->display_messages($messages, get_string('attemptinfo', 'survey'));
        $messages = array();
        // end of: general info

        if ($displaybutton) {
            $url = new moodle_url('/mod/survey/view.php', array('id' => $this->cm->id, 'cvp' => 0));
            echo $OUTPUT->single_button($url, get_string('addonemore', 'survey'), 'get');
        } else {
            if (!$this->cansubmit) {
                $message = get_string('canneversubmit', 'survey');
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->survey->timeopen) && ($this->survey->timeopen >= $timenow)) {
                $message = get_string('cannotsubmittooearly', 'survey', userdate($this->survey->timeopen));
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->survey->timeclose) && ($this->survey->timeclose <= $timenow)) {
                $message = get_string('cannotsubmittoolate', 'survey', userdate($this->survey->timeclose));
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->survey->maxentries > 0) && ($next >= $this->survey->maxentries)) {
                $message = get_string('nomoresubmissionsallowed', 'survey', $this->survey->maxentries);
                echo $OUTPUT->container($message, 'centerpara');
            }
        }
        // end of: the button to add one more survey

        // report
        $surveyreportlist = get_plugin_list('surveyreport');
        $paramurlbase = array('id' => $this->cm->id);
        foreach ($surveyreportlist as $pluginname => $pluginpath) {
            require_once($CFG->dirroot.'/mod/survey/report/'.$pluginname.'/report.class.php');
            $classname = 'report_'.$pluginname;
            $restricttemplates = $classname::restrict_templates();
            if ((!$restricttemplates) || in_array($this->survey->template, $restricttemplates)) {
                if ($canaccessreports || ($classname::has_student_report() && $canaccessownreports)) {
                    if ($childreports = $classname::get_childreports($canaccessreports)) {
                        foreach ($childreports as $childname => $childparams) {
                            $childparams['s'] = $PAGE->cm->instance;
                            $url = new moodle_url('/mod/survey/report/'.$pluginname.'/view.php', $childparams);
                            $a = new stdClass();
                            $a->href = $url->out();
                            $a->reportname = get_string('pluginname', 'surveyreport_'.$pluginname).': '.$childname;
                            $messages[] = get_string('runreport', 'survey', $a);
                        }
                    } else {
                        $url = new moodle_url('/mod/survey/report/'.$pluginname.'/view.php', $paramurlbase);
                        $a = new stdClass();
                        $a->href = $url->out();
                        $a->reportname = get_string('pluginname', 'surveyreport_'.$pluginname);
                        $messages[] = get_string('runreport', 'survey', $a);
                    }
                }
            }
        }

        $this->display_messages($messages, get_string('reportsection', 'survey'));
        $messages = array();
        // end of: report

        // user templates
        if ($canmanageusertemplates) {
            $url = new moodle_url('/mod/survey/utemplates_manage.php', $paramurlbase);
            $messages[] = get_string('manageusertemplates', 'survey', $url->out());
        }

        if ($cansaveusertemplate) {
            $url = new moodle_url('/mod/survey/utemplates_create.php', $paramurlbase);
            $messages[] = get_string('saveusertemplates', 'survey', $url->out());
        }

        if ($canimportusertemplates) {
            $url = new moodle_url('/mod/survey/utemplates_import.php', $paramurlbase);
            $messages[] = get_string('importusertemplates', 'survey', $url->out());
        }

        if ($canapplyusertemplates && (!$hassubmissions || $riskyediting)) {
            $url = new moodle_url('/mod/survey/utemplates_apply.php', $paramurlbase);
            $messages[] = get_string('applyusertemplates', 'survey', $url->out());
        }

        $this->display_messages($messages, get_string('utemplatessection', 'survey'));
        $messages = array();
        // end of: user templates

        // master templates
        if ($cansavemastertemplates) {
            $url = new moodle_url('/mod/survey/mtemplates_create.php', $paramurlbase);
            $messages[] = get_string('savemastertemplates', 'survey', $url->out());
        }

        if ($canapplymastertemplates) {
            $url = new moodle_url('/mod/survey/mtemplates_apply.php', $paramurlbase);
            $messages[] = get_string('applymastertemplates', 'survey', $url->out());
        }

        $this->display_messages($messages, get_string('mtemplatessection', 'survey'));
        $messages = array();
        // end of: master templates

        echo $OUTPUT->footer();
    }


    /*
     * display_messages
     *
     * @return
     */
    public function display_messages($messages, $strlegend) {
        global $OUTPUT;

        if (count($messages)) {
            // echo $OUTPUT->box_start('box generalbox description', 'intro');
            echo html_writer::start_tag('fieldset', array('class' => 'infofieldsset'));
            echo html_writer::start_tag('legend', array('class' => 'infolegend'));
            echo $strlegend;
            echo html_writer::end_tag('legend');
            foreach ($messages as $message) {
                echo $OUTPUT->container($message, 'mdl-left');
            }
            echo html_writer::end_tag('fieldset');
            // echo $OUTPUT->box_end();
        }
    }
}
