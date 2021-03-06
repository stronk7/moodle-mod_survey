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
require_once($CFG->dirroot.'/mod/survey/forms/remoteuser/remoteuser_form.php');

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

$cover = optional_param('cvp', 1, PARAM_INT); // by default user asks for the cover page
$formpage = optional_param('formpage', 0, PARAM_INT); // form page number
$view = optional_param('view', SURVEY_SERVESURVEY, PARAM_INT);
$submissionid = optional_param('submissionid', 0, PARAM_INT);

// -----------------------------
// calculations
// -----------------------------
$userpageman = new mod_survey_userformmanager($cm, $survey, $submissionid, $formpage, $view);
if (empty($cover)) {
    $userpageman->prevent_direct_user_input();
}
$userpageman->survey_add_custom_css();

// redirect if no items were created and you are supposed to create them
if ($userpageman->canaccessadvanceditems) {
    if (!$userpageman->count_input_items()) {
        if (($formpage == 0) || ($formpage == 1)) {
            $paramurl = array('id' => $cm->id);
            $returnurl = new moodle_url('items_manage.php', $paramurl);
            redirect($returnurl);
        }
    }
}

$pageallowesubmission = ($userpageman->modulepage != SURVEY_SUBMISSION_READONLY);
$pageallowesubmission = $pageallowesubmission && ($userpageman->modulepage != SURVEY_ITEMS_PREVIEW);

// -----------------------------
// define $user_form return url
$paramurl = array('id' => $cm->id, 'cvp' => 0, 'view' => $view);
$formurl = new moodle_url('view.php', $paramurl);
// end of: define $user_form return url
// -----------------------------

// -----------------------------
// prepare params for the form
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->survey = $survey;
$formparams->submissionid = $submissionid;
$formparams->firstpageright = $userpageman->firstpageright;
$formparams->maxassignedpage = $userpageman->maxassignedpage;
$formparams->canaccessadvanceditems = $userpageman->canaccessadvanceditems; // Help selecting the fields to show
$formparams->formpage = $userpageman->formpage;
$formparams->modulepage = $userpageman->modulepage; // this is the page to get corresponding fields
$formparams->cansubmit = $userpageman->cansubmit;
// end of: prepare params for the form
// -----------------------------

// if ($view == SURVEY_READONLYRESPONSE) $editable = false, else $editable = true
$userpageform = new survey_submissionform($formurl, $formparams, 'post', '', array('id' => 'remoteuserentry'), ($view != SURVEY_READONLYRESPONSE));

// -----------------------------
// manage form submission
if ($userpageform->is_cancelled()) {
    $redirecturl = new moodle_url('view_manage.php', $paramurl);
    redirect($redirecturl, get_string('usercanceled', 'survey'));
}

if ($userpageman->formdata = $userpageform->get_data()) {
    // SAVE unless the "previous" button has been pressed
    //             and "pause"    button has been pressed
    $prevbutton = (isset($userpageman->formdata->prevbutton) && ($userpageman->formdata->prevbutton));
    $pausebutton = (isset($userpageman->formdata->pausebutton) && ($userpageman->formdata->pausebutton));

    if (!$prevbutton && !$pausebutton) {
        if ($userpageman->modulepage != SURVEY_ITEMS_PREVIEW) {
            $userpageman->save_user_data();
            $userpageman->notifyroles();
        }
    }

    // if "pause" button has been pressed, redirect
    if ($pausebutton) {
        $redirecturl = new moodle_url('view_manage.php', $paramurl);
        redirect($redirecturl); // -> go somewhere
    }

    $paramurl['submissionid'] = $userpageman->submissionid;

    if ($prevbutton) {
        $userpageman->next_not_empty_page(false, $userpageman->formpage, $userpageman->modulepage);
        $paramurl['formpage'] = $userpageman->firstpageleft;
        redirect(new moodle_url('view.php', $paramurl)); // -> go to the first non empty previous page of the form
    }

    $nextbutton = (isset($userpageman->formdata->nextbutton) && ($userpageman->formdata->nextbutton));
    if ($nextbutton) {
        $userpageman->next_not_empty_page(true, $userpageman->formpage, $userpageman->modulepage);
        // ok, I am leaving page $userpageman->formpage
        // to go to page $userpageman->firstpageright
        // I need to delete all the answer that were (maybe) written during a previous walk along the survey.
        // Data of each item in a page between ($userpageman->formpage+1) and ($userpageman->formpage-1) included, must be deleted
        $userpageman->drop_jumping_saved_data();

        $paramurl['formpage'] = $userpageman->firstpageright;
        redirect(new moodle_url('view.php', $paramurl)); // -> go to the first non empty next page of the form
    }
}
// end of: manage form submission
// -----------------------------

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/survey/view.php', array('id' => $cm->id));
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// make bold the navigation menu/link that refers to me
$url = new moodle_url('/mod/survey/view.php', array('s' => $survey->id));
navigation_node::override_active_url($url);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();

$moduletab = $userpageman->moduletab; // needed by tabs.php
$modulepage = $userpageman->modulepage; // needed by tabs.php
require_once($CFG->dirroot.'/mod/survey/tabs.php');

if ($cover) {
    $userpageman->display_cover();
    die;
}

// -----------------------------
// if survey is without items, alert and stop
if (!$userpageman->canaccessadvanceditems) {
    if (!$userpageman->count_input_items()) {
        $userpageman->noitem_stopexecution();
    }
}
// end of: if survey is without items, alert and stop
// -----------------------------

// -----------------------------
// is the user allowed to submit one more survey?
if ($pageallowesubmission) {
    if (!$userpageman->submissions_allowed()) {
        $userpageman->submissions_exceeded_stopexecution();
    }
    // } else {
    // I am editing an "in progress" submission
    // you are always allowed to carry on with your "in progress" submission
}
// end of: is the user allowed to submit one more survey?
// -----------------------------

// -----------------------------
// manage the thanks page
if ($pageallowesubmission) {
    $userpageman->manage_thanks_page();
}
// end of: manage the thanks page
// -----------------------------

// -----------------------------
// display an alert to explain why buttons are missing
$userpageman->message_preview_mode();
// end of: display an alert to explain why buttons are missing
// -----------------------------

// -----------------------------
// display orientation text: page xx of yy
$userpageman->display_page_x_of_y();
// end of: display orientation text: page xx of yy
// -----------------------------

// -----------------------------
// calculate prefill for fields and prepare standard editors and filemanager
// if sumission already exists
$prefill = $userpageman->get_prefill_data();

// populate the hidden field of the form
$prefill['formpage'] = $userpageman->formpage;

$userpageform->set_data($prefill);
$userpageform->display();
// end of: calculate prefill for fields and prepare standard editors and filemanager
// -----------------------------

// -----------------------------
// display an alert to explain why buttons are missing
if ($userpageman->modulepage == SURVEY_ITEMS_PREVIEW) {
    $userpageman->message_preview_mode();
}
// end of: display an alert to explain why buttons are missing
// -----------------------------

// Finish the page
echo $OUTPUT->footer();
