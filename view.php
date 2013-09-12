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
 * Prints a particular instance of survey
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/survey/locallib.php');
require_once($CFG->dirroot.'/mod/survey/classes/view_userform.class.php');
require_once($CFG->dirroot.'/mod/survey/forms/remoteuser/userpage_form.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$s = optional_param('s', 0, PARAM_INT);  // survey instance ID

if (!empty($id)) {
    $cm = get_coursemodule_from_id('survey', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $survey = $DB->get_record('survey', array('id' => $cm->instance), '*', MUST_EXIST);
} else if (!empty($s)) {
    $survey = $DB->get_record('survey', array('id' => $s), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $survey->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('survey', $survey->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_course_login($course, true, $cm);

add_to_log($course->id, 'survey', 'view', "view.php?id=$cm->id", $survey->name, $cm->id);

$formpage = optional_param('formpage' , 0, PARAM_INT); // form page number
$action = optional_param('act', SURVEY_NOACTION, PARAM_INT);
$submissionid = optional_param('submissionid', 0, PARAM_INT);

// ////////////////////////////////////////////////////////////
// calculations
// ////////////////////////////////////////////////////////////
$userpage_manager = new mod_survey_userformmanager($cm, $survey, $submissionid, $formpage, $action);
$userpage_manager->prevent_direct_user_input();
$userpage_manager->survey_add_custom_css();

// redirect if no items were created and you are supposed to create them
if ($userpage_manager->canaccessadvanceditems) {
    if (!$userpage_manager->count_input_items()) {
        $paramurl = array('id' => $cm->id);
        $returnurl = new moodle_url('items_manage.php', $paramurl);
        redirect($returnurl);
    }
}

$hassubmitbutton = ($userpage_manager->currentpage != SURVEY_SUBMISSION_READONLY);
$hassubmitbutton = $hassubmitbutton && ($userpage_manager->currentpage != SURVEY_ITEMS_PREVIEW);

// ////////////////////////////
// define $user_form return url
$paramurl = array('id' => $cm->id, 'act' => $action);
$formurl = new moodle_url('view.php', $paramurl);
// end of: define $user_form return url
// ////////////////////////////

// ////////////////////////////
// prepare params for the form
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->survey = $survey;
$formparams->submissionid = $submissionid;
$formparams->firstpage_right = $userpage_manager->firstpage_right;
$formparams->maxassignedpage = $userpage_manager->maxassignedpage;
$formparams->canaccessadvanceditems = $userpage_manager->canaccessadvanceditems; // Help selecting the fields to show
$formparams->formpage = $userpage_manager->formpage;
$formparams->tabpage = $userpage_manager->currentpage; // this is the page to get corresponding fields
$formparams->cansubmit = $userpage_manager->cansubmit;
// end of: prepare params for the form
// ////////////////////////////

if ($action == SURVEY_READONLYRESPONSE) {
    $userpage_form = new survey_submissionform($formurl, $formparams, 'post', '', array('id' => 'remoteuserentry'), false);
} else {
    $userpage_form = new survey_submissionform($formurl, $formparams, 'post', '', array('id' => 'remoteuserentry'));
}

// ////////////////////////////
// manage form submission
if ($userpage_form->is_cancelled()) {
    $redirecturl = new moodle_url('view_manage.php', $paramurl);
    redirect($redirecturl, get_string('usercanceled', 'survey'));
}

if ($userpage_manager->formdata = $userpage_form->get_data()) {
    // SAVE unless the "previous" button has been pressed
    //             and "pause"    button has been pressed
    $prevbutton = (isset($userpage_manager->formdata->prevbutton) && ($userpage_manager->formdata->prevbutton));
    $pausebutton = (isset($userpage_manager->formdata->pausebutton) && ($userpage_manager->formdata->pausebutton));

    if (!$prevbutton && !$pausebutton) {
        $userpage_manager->save_user_data();
        $userpage_manager->notifyroles();
    }

    // if "pause" button has been pressed, redirect
    if ($pausebutton) {
        $redirecturl = new moodle_url('view_manage.php', $paramurl);
        redirect($redirecturl); // -> go somewhere
    }

    $paramurl['submissionid'] = $userpage_manager->submissionid;

    if ($prevbutton) {
        // $userpage_manager->formdata->formpage in the worst case becomes equal to 1 such as left $overflow (-1)
        $userpage_manager->next_not_empty_page(false, $userpage_manager->formpage);
        $paramurl['formpage'] = $userpage_manager->firstpage_left;
        redirect(new moodle_url('view.php', $paramurl)); // -> go to the first non empty previous page of the form
    }

    $nextbutton = (isset($userpage_manager->formdata->nextbutton) && ($userpage_manager->formdata->nextbutton));
    if ($nextbutton) {
        // $userpage_manager->formdata->formpage in the worst case could become $firstpage_left such as right $overflow (-2)
        $userpage_manager->next_not_empty_page(true, $userpage_manager->formpage);
        $paramurl['formpage'] = $userpage_manager->firstpage_right;
        redirect(new moodle_url('view.php', $paramurl)); // -> go to the first non empty next page of the form
    }
}
// end of: manage form submission
// ////////////////////////////

// ////////////////////////////////////////////////////////////
// output starts here
// ////////////////////////////////////////////////////////////
$PAGE->set_url('/mod/survey/view.php', array('id' => $cm->id));
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();

$currenttab = $userpage_manager->currenttab; // needed by tabs.php
$currentpage = $userpage_manager->currentpage; // needed by tabs.php
include_once($CFG->dirroot.'/mod/survey/tabs.php');

// ////////////////////////////
// if survey is without items, alert and stop
if (!$userpage_manager->canaccessadvanceditems) {
    if (!$userpage_manager->count_input_items()) {
        $userpage_manager->noitem_stopexecution();
    }
}
// end of: if survey is without items, alert and stop
// ////////////////////////////

// ////////////////////////////
// is the user allowed to submit one more survey?
if ($hassubmitbutton) {
    if (!$userpage_manager->submissionid) { // I am going to create one more new submission
        if (!$userpage_manager->submissions_allowed()) {
            $userpage_manager->submissions_exceeded_stopexecution();
        }
    } else { // I am editing an "in progress" submission
        // you are always allowed to carry on with your "in progress" submission
    }
}
// end of: is the user allowed to submit one more survey?
// ////////////////////////////

// ////////////////////////////
// manage the thanks page
if ($hassubmitbutton) {
    $userpage_manager->manage_thanks_page();
}
// end of: manage the thanks page
// ////////////////////////////

// ////////////////////////////
// display an alert to explain why buttons are missing
if ($userpage_manager->currentpage == SURVEY_ITEMS_PREVIEW) {
    $userpage_manager->message_preview_mode();
}
// end of: display an alert to explain why buttons are missing
// ////////////////////////////

// ////////////////////////////
// display orientation text: page xx of yy
$userpage_manager->display_page_x_of_y();
// end of: display orientation text: page xx of yy
// ////////////////////////////

// ////////////////////////////
// calculate prefill for fields and prepare standard editors and filemanager
// if sumission already exists
if ($hassubmitbutton) {
    if (!empty($submissionid)) {
        $prefill = $userpage_manager->get_prefill_data();
    }
}
// go to populate the hidden field of the form
$prefill['formpage'] = $userpage_manager->formpage;

$userpage_form->set_data($prefill);
$userpage_form->display();
// end of: calculate prefill for fields and prepare standard editors and filemanager
// ////////////////////////////

// ////////////////////////////
// display an alert to explain why buttons are missing
if ($userpage_manager->currentpage == SURVEY_ITEMS_PREVIEW) {
    $userpage_manager->message_preview_mode();
}
// end of: display an alert to explain why buttons are missing
// ////////////////////////////

// Finish the page
echo $OUTPUT->footer();
