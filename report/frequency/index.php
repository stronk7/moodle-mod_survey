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
 * @package    surveyitem
 * @subpackage autofill
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/report/frequency/item_form.php');
require_once($CFG->dirroot.'/mod/survey/report/frequency/lib.php');
require_once($CFG->libdir.'/tablelib.php');

echo $OUTPUT->heading(get_string('pluginname', 'surveyreport_frequency'));

$formparams = new stdClass();
$formparams->survey = $survey;
$formparams->answercount = $hassubmissions;

$paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_REPORT, 'rname' => 'frequency');
$formurl = new moodle_url('view.php', $paramurl);
$mform = new survey_chooseitemform($formurl, $formparams);

$fromform = $mform->get_data(); // get_data is needed to execute $mform->validation($data, $files);
$mform->display();

if ($fromform) {
    surveyreport_displaydistribution($cm, $survey, $fromform->itemid, $hassubmissions);

    $debug = false;
    if ($debug) {
        include_once($CFG->dirroot.'/mod/survey/report/frequency/graph.php');
    } else {

        $url = 'id='.$cm->id.'&amp;group=0&amp;itemid='.$fromform->itemid;
        survey_print_graph($url);
    }

}
