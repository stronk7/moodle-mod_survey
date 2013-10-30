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
 * Defines the version of survey autofill subplugin
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    surveyreport
 * @subpackage colles
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/survey/report/colles/lib.php');
require_once($CFG->dirroot.'/mod/survey/report/colles/report.class.php');

$id = optional_param('id', 0, PARAM_INT);
$s = optional_param('s', 0, PARAM_INT);
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

$type = optional_param('type', 'summary', PARAM_ALPHA);  // type of graph
$group = optional_param('group', 0, PARAM_INT);  // Group ID
$area = optional_param('area', false, PARAM_INT);  // Student ID
$qid = optional_param('qid', 0, PARAM_INT);  // 0..3 the question in the area

$paramurl = array('type' => $type, 's' => $survey->id);
if (!empty($group)) {
    $paramurl['group'] = $group;
}
if ($area) {
    $paramurl['area'] = $area;
}
if (!empty($qid)) {
    $paramurl['qid'] = $qid;
}
$url = new moodle_url('/mod/survey/report/colles/view.php', $paramurl);
$PAGE->set_url($url);

// make bold the navigation menu/link that refers to me
$url = new moodle_url('/mod/survey/report/colles/view.php', array('type' => $type, 's' => $survey->id));
navigation_node::override_active_url($url);

$context = context_module::instance($cm->id);

require_course_login($course, true, $cm);
if ($type == 'summary') {
    require_capability('mod/survey:accessownreports', $context);
} else {
    require_capability('mod/survey:accessreports', $context);
}

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/survey/view.php', array('id' => $cm->id));
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();

$currenttab = SURVEY_TABSUBMISSIONS; // needed by tabs.php
$currentpage = SURVEY_SUBMISSION_REPORT; // needed by tabs.php
require_once($CFG->dirroot.'/mod/survey/tabs.php');

$hassubmissions = survey_count_submissions($survey->id);
$reportman = new report_colles($cm, $survey);
$reportman->setup($hassubmissions, $group, $area, $qid);
$reportman->check_submissions();
switch ($type) {
    case 'summary':
        $reportman->output_summarydata();
        break;
    case 'scales':
        $reportman->output_scalesdata();
        break;
    case 'questions':
        $reportman->output_questionsdata($area);
        break;
    case 'question':
        break;
    case 'students':
        break;
    case 'student':
        break;
}

// Finish the page
echo $OUTPUT->footer();
