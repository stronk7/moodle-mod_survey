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
require_once($CFG->dirroot.'/mod/survey/classes/mtemplate.class.php');
require_once($CFG->dirroot.'/mod/survey/forms/mtemplates/applymtemplate_form.php');

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

add_to_log($course->id, 'survey', 'view', "mtemplates.php?id=$cm->id", $survey->name, $cm->id);

$context = context_module::instance($cm->id);
require_capability('mod/survey:applymastertemplate', $context);

// -----------------------------
// calculations
// -----------------------------
$mtemplateman = new mod_survey_mastertemplate($survey);

// -----------------------------
// define $applymtemplate return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('mtemplates_apply.php', $paramurl);
// end of: define $applymtemplate return url
// -----------------------------

// -----------------------------
// prepare params for the form
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->survey = $survey;
$formparams->mtemplateman = $mtemplateman;
$formparams->inline = false;

$applymtemplate = new survey_applymtemplateform($formurl, $formparams);
// end of: prepare params for the form
// -----------------------------

// -----------------------------
// manage form submission
if ($applymtemplate->is_cancelled()) {
    $returnurl = new moodle_url('utemplates_add.php', $paramurl);
    redirect($returnurl);
}

if ($mtemplateman->formdata = $applymtemplate->get_data()) {
    $mtemplateman->apply_template(SURVEY_MASTERTEMPLATE);

    $redirecturl = new moodle_url('view.php', array('id' => $cm->id, 'act' => SURVEY_PREVIEWSURVEY));
    redirect($redirecturl);
}
// end of: manage form submission
// -----------------------------

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/survey/mtemplates.php', $paramurl);
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();

$currenttab = SURVEY_TABMTEMPLATES; // needed by tabs.php
$currentpage = SURVEY_MTEMPLATES_APPLY; // needed by tabs.php
require_once($CFG->dirroot.'/mod/survey/tabs.php');

$mtemplateman->friendly_halt();

if (survey_count_submissions($survey->id, SURVEY_STATUSALL)) {
    echo $OUTPUT->notification(get_string('hassubmissions_alert', 'survey'));
}

$message = get_string('applymtemplateinfo', 'survey');
echo $OUTPUT->box($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

$applymtemplate->display();

// Finish the page
echo $OUTPUT->footer();
