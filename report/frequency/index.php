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
 * @subpackage frequency
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/report/frequency/report.class.php');
require_once($CFG->dirroot.'/mod/survey/report/frequency/item_form.php');
require_once($CFG->dirroot.'/mod/survey/report/frequency/lib.php');
require_once($CFG->libdir.'/tablelib.php');

$context = context_module::instance($cm->id);

require_course_login($course, true, $cm);
require_capability('mod/survey:accessreports', $context);

// ////////////////////////////////////////////////////////////
// calculations
// ////////////////////////////////////////////////////////////
$report_manager = new report_frequency($cm, $survey);

// ////////////////////////////
// define $mform return url
$paramurl = array('id' => $cm->id, 'rname' => 'frequency');
$formurl = new moodle_url('view_report.php', $paramurl);
// end of: define $mform return url
// ////////////////////////////

// ////////////////////////////
// prepare params for the form
$formparams = new stdClass();
$formparams->survey = $survey;
$formparams->answercount = $hassubmissions;
$mform = new survey_chooseitemform($formurl, $formparams);
// end of: prepare params for the form
// ////////////////////////////

$fromform = $mform->get_data(); // get_data is needed to execute $mform->validation($data, $files);
$mform->display();

// ////////////////////////////
// manage form submission
if ($fromform) {
    $report_manager->fetch_information($fromform->itemid, $hassubmissions);

    $paramurl = array();
    $paramurl['id'] = $cm->id;
    $paramurl['group'] = 0;
    $paramurl['itemid'] = $fromform->itemid;
    $paramurl['submissionscount'] = $hassubmissions;
    $url = new moodle_url('/mod/survey/report/frequency/graph.php', $paramurl);
    $a = $url->out();

    $report_manager->output_information($url->out());
}
// end of: manage form submission
// ////////////////////////////
