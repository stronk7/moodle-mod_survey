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
require_once($CFG->dirroot.'/mod/survey/classes/submission.class.php');

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
$currentpage = SURVEY_SUBMISSION_MANAGE; // needed by tabs.php

$submissionid = optional_param('submissionid', 0, PARAM_INT);
<<<<<<< HEAD
$action = optional_param('act', SURVEY_NOACTION, PARAM_INT);
$confirm = optional_param('cnf' , 0, PARAM_INT); // confirm submission deletion

switch ($action) {
    case SURVEY_NOACTION:
        break;
    case SURVEY_DUPLICATERESPONSE:
        survey_prevent_direct_user_input($survey, $cm, $submissionid, SURVEY_DUPLICATERESPONSE);
        break;
    case SURVEY_DELETEALLRESPONSES:
        require_capability('mod/survey:deleteallsubmissions', $context);
        break;
    default:
        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $action = '.$action);
}

=======
$confirm = optional_param('cnf' , 0, PARAM_INT); // confirm submission deletion

>>>>>>> 5be0a9a1b0149babfc062c50aa455db64239ab8c
// ////////////////////////////////////////////////////////////
// calculations
// ////////////////////////////////////////////////////////////
$submission_manager = new mod_survey_submissionmanager($survey);
$submission_manager->submissionid = $submissionid;
$submission_manager->confirm = $confirm;
$submission_manager->canaccessadvancedform = survey_user_can_access_advanced_form($cm);
<<<<<<< HEAD
$submission_manager->canmanageallsubmissions = survey_user_can_manage_all_submissions($cm);
$submission_manager->action = $action;
=======
$submission_manager->canreadallsubmissions = survey_user_can_read_all_submissions($cm);
$submission_manager->caneditallsubmissions = survey_user_can_edit_all_submissions($cm);
$submission_manager->candeleteallsubmissions = survey_user_can_delete_all_submissions($cm);
$submission_manager->action = optional_param('act', SURVEY_NOACTION, PARAM_INT);
>>>>>>> 5be0a9a1b0149babfc062c50aa455db64239ab8c
$submission_manager->searchfields_get = optional_param('searchquery', '', PARAM_RAW);

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

$submission_manager->manage_actions();

$submission_manager->manage_submissions();

// Finish the page
echo $OUTPUT->footer();
