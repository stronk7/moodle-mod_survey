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


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->dirroot.'/mod/survey/locallib.php');

// ////////////////////////////
// if survey is without items, alert and stop
$whereparams = array('surveyid' => $survey->id);
$whereclause = 'surveyid = :surveyid AND hide = 0';
if (!$canaccessadvancedform) {
    $whereclause .= ' AND basicform <> '.SURVEY_NOTPRESENT;
}

if (!$DB->count_records_select('survey_item', $whereclause, $whereparams)) {
    if ($canaccessadvancedform) {
        echo $OUTPUT->notification(get_string('noadvanceditemsfound', 'survey'), 'generaltable generalbox boxaligncenter boxwidthnormal');
    } else {
        echo $OUTPUT->notification(get_string('nouseritemsfound', 'survey'), 'generaltable generalbox boxaligncenter boxwidthnormal');
    }
    $continueurl = new moodle_url('/mod/survey/view.php', array('s' => $survey->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_MANAGE));
    echo $OUTPUT->continue_button($continueurl);
    echo $OUTPUT->footer();
    die;
}
// end of: if survey is without items, alert and stop
// ////////////////////////////

// ////////////////////////////
// is the user allowed to submit one more survey?
// do not trigger $survey->maxentries if you are submitting an already displayed form (&& !$fromform)
if ($currentpage == SURVEY_SUBMISSION_NEW) {
    if ($survey->maxentries && !$fromform) {
        $alreadysubmitted = $DB->count_records('survey_submissions', array('surveyid' => $survey->id, 'userid' => $USER->id));
        if ($alreadysubmitted >= $survey->maxentries) { // > should never be verified
            $params = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_MANAGE);
            $redirecturl = new moodle_url('view.php', $params);

            echo $OUTPUT->box_start();
            echo get_string('nomorerecordsallowed', 'survey', $survey->maxentries);
            echo $OUTPUT->single_button($redirecturl, get_string('continue'));
            echo $OUTPUT->box_end();
            echo $OUTPUT->footer();
            die;
        }
    }
}
// end of: is the user allowed to submit one more survey?
// ////////////////////////////

// ////////////////////////////
// manage the thanks page
// for the thanks page, you MUST be here because you need $PAGE before
if ($currentpage == SURVEY_SUBMISSION_NEW) {
    $savebutton = (isset($fromform->savebutton) && ($fromform->savebutton));
    if ($savebutton) {
        survey_show_thanks_page($survey, $cm);
        echo $OUTPUT->footer();
        die;
    }
}
// end of: manage the thanks page
// ////////////////////////////

// ////////////////////////////
// silly orientation text for the user
// $lastformpage has been defined in beforepage.php
if ($lastformpage > 1) {
    // if $formpage == 0 no more pages with items are available
    $a = new stdclass();
    $a->formpage = ($formpage == 0) ? $lastformpage : $formpage;
    $a->lastformpage = $lastformpage;
    echo $OUTPUT->heading(get_string('pagexofy', 'survey', $a));
}
// end of: silly orientation text for the user
// ////////////////////////////

// if I am here, this means that:
//    1. the "survey_additemform" has been submitted using "Next" or "Previous" buttons OR it is the first access
//    2. the $fromform->formpage has been recalculated in beforepage.php nell'ambito della gestione di << o >>
// Now I really need to calculate prefill for fields and prepare standard editors and filemanager
$prefill = array();

// if sumission already exists
if (!empty($submissionid)) {
    // $submission = $DB->get_record('survey_submissions', array('id' => $submissionid));

    $prefill = survey_set_prefill($survey, $canaccessadvancedform, $formpage, $submissionid, false);

    $prefill['submissionid'] = $submissionid;

    // $mygroups = survey_get_my_groups($cm);
    // $icanedit = survey_i_can_edit($survey, $mygroups, $submission->userid);
    // $icanedit = ($icanedit || survey_user_can_edit_all_submissions($cm));
    // $icanedit = ($icanedit && ($action != SURVEY_VIEWSURVEY));
}
$prefill['formpage'] = empty($formpage) ? $lastformpage : $formpage; // go to populate the hidden field of the form

$mform->set_data($prefill);
$mform->display();