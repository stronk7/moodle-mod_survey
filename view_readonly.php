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
require_once($CFG->dirroot.'/mod/survey/classes/userpage.class.php');
require_once($CFG->dirroot.'/mod/survey/forms/submissions/userpage_form.php');

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

$currenttab = SURVEY_TABSUBMISSIONS; // needed by tabs.php
$currentpage = SURVEY_SUBMISSION_READONLY; // needed by tabs.php

$formpage = optional_param('formpage' , 1, PARAM_INT); // form page number
$submissionid = optional_param('submissionid', 0, PARAM_INT);
// whether it comes from the form or from the redirect in GET, $submissionid is fetched here
// if the form (once submitted) send $submissionid == 0, the value will be overwritten later in if ($userpage_manager->formdata = $dataentry_form->get_data()) {

survey_add_custom_css($survey->id, $cm->id);

// ////////////////////////////////////////////////////////////
// calculations
// ////////////////////////////////////////////////////////////
$userpage_manager = new mod_survey_userpagemanager($survey);
$userpage_manager->formpage = $formpage;
$userpage_manager->submissionid = $submissionid;
$userpage_manager->canaccessadvancedform = survey_user_can_access_advanced_form($cm);
$userpage_manager->canmanageitems = survey_user_can_manage_items($cm);

// ////////////////////////////
// assign items to pages in the basicform and in the advancedform
$userpage_manager->assign_pages();
// this is the method used to assign $userpage_manager->lastformpage
// end of: assign items to pages in the basicform and in the advancedform
// ////////////////////////////

// ////////////////////////////
// define $user_form return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('view.php', $paramurl);
// end of: define $user_form return url
// ////////////////////////////

// ////////////////////////////
// prepare params for the form
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->survey = $survey;
$formparams->submissionid = $submissionid;
$formparams->lastformpage = $userpage_manager->lastformpage;
$formparams->canaccessadvancedform = $userpage_manager->canaccessadvancedform; // Help selecting the fields to show
$formparams->formpage = $formpage;
$formparams->currentpage = $currentpage;
if ($currentpage == SURVEY_SUBMISSION_READONLY) {
    $dataentry_form = new survey_submissionform($formurl, $formparams, 'post', '', null, false);
} else {
    $dataentry_form = new survey_submissionform($formurl, $formparams);
}
// end of: prepare params for the form
// ////////////////////////////

// ////////////////////////////
// manage form submission
if ($dataentry_form->is_cancelled()) {
    $redirecturl = new moodle_url('view_manage.php', $paramurl);
    redirect($redirecturl, get_string('usercanceled', 'survey'));
}

if ($userpage_manager->formdata = $dataentry_form->get_data()) {
    // if "pause" button has been pressed, redirect
    $pausebutton = (isset($userpage_manager->formdata->pausebutton) && ($userpage_manager->formdata->pausebutton));
    if ($pausebutton) {
        $redirecturl = new moodle_url('view_manage.php', $paramurl);
        redirect($redirecturl); // -> go somewhere
    }

    $paramurl['submissionid'] = $userpage_manager->submissionid;

    $prevbutton = (isset($userpage_manager->formdata->prevbutton) && ($userpage_manager->formdata->prevbutton));
    if ($prevbutton) {
        // $userpage_manager->formdata->formpage in the worst case becomes equal to 1
        $paramurl['formpage'] = $userpage_manager->next_not_empty_page(false);
        redirect(new moodle_url('view.php', $paramurl)); // -> go to the first non empty previous page of the form
    }

    $nextbutton = (isset($userpage_manager->formdata->nextbutton) && ($userpage_manager->formdata->nextbutton));
    if ($nextbutton) {
        // $userpage_manager->formdata->formpage in the worst case could become $lastformpage such as 0
        $paramurl['formpage'] = $userpage_manager->next_not_empty_page(true, $userpage_manager->lastformpage);
        redirect(new moodle_url('view.php', $paramurl)); // -> go to the first non empty next page of the form
    }
}
// end of: manage form submission
// ////////////////////////////

// ////////////////////////////////////////////////////////////
// Output starts here
// ////////////////////////////////////////////////////////////
$PAGE->set_url('/mod/survey/view.php', array('id' => $cm->id));
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();
include_once($CFG->dirroot.'/mod/survey/tabs.php');

// ////////////////////////////
// calculate prefill for fields and prepare standard editors and filemanager
$prefill = array();

// if I am editing an already existing submission
$prefill['submissionid'] = $submissionid;
$prefill = $userpage_manager->get_prefill_data(false);
$prefill['formpage'] = empty($formpage) ? $userpage_manager->lastformpage : $formpage;

$dataentry_form->set_data($prefill);
$dataentry_form->display();
// end of: calculate prefill for fields and prepare standard editors and filemanager
// ////////////////////////////

// Finish the page
echo $OUTPUT->footer();
