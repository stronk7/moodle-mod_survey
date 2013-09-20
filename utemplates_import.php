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
require_once($CFG->dirroot.'/mod/survey/classes/utemplate.class.php');
require_once($CFG->dirroot.'/mod/survey/forms/utemplates/importutemplate_form.php');

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

add_to_log($course->id, 'survey', 'view', "utemplates.php?id=$cm->id", $survey->name, $cm->id);

$utemplateid = optional_param('fid', 0, PARAM_INT);

// params never passed but needed by called class
$action = SURVEY_NOACTION;
$confirm = SURVEY_UNCONFIRMED;

$context = context_module::instance($cm->id);
require_capability('mod/survey:uploadusertemplates', $context);

// -----------------------------
// calculations
// -----------------------------
$utemplateman = new mod_survey_usertemplate($cm, $survey, $context, $utemplateid, $action, $confirm);
$utemplateman->prevent_direct_user_input();

// -----------------------------
// define $importutemplate return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('utemplates_import.php', $paramurl);
// end of: define $importutemplate return url
// -----------------------------

// -----------------------------
// prepare params for the form
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->survey = $survey;
$formparams->utemplateman = $utemplateman;
$formparams->filemanager_options = $utemplateman->get_filemanager_options();
$importutemplate = new survey_importutemplateform($formurl, $formparams);
// end of: prepare params for the form
// -----------------------------

// -----------------------------
// manage form submission

if ($utemplateman->formdata = $importutemplate->get_data()) {
    $utemplateman->upload_utemplate();

    $paramurl = array();
    $paramurl['s'] = $survey->id;
    $redirecturl = new moodle_url('utemplates_manage.php', $paramurl);
    redirect($redirecturl);
}
// end of: manage form submission
// -----------------------------

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/survey/utemplates.php', array('id' => $cm->id));
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();

$currenttab = SURVEY_TABUTEMPLATES; // needed by tabs.php
$currentpage = SURVEY_UTEMPLATES_IMPORT; // needed by tabs.php
require_once($CFG->dirroot.'/mod/survey/tabs.php');

$importutemplate->display();

// Finish the page
echo $OUTPUT->footer();
