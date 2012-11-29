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

// if I am here, this means that:
//    1. the "survey_additemform" has been submitted using "Next" or "Previous" buttons OR it is the first access
//    2. the $fromform->formpage has been recalculated in beforepage.php nell'ambito della gestione di << o >>
// Now I really need to calculate prefill for fields and prepare standard editors and filemanager
$submission = $DB->get_record('survey_submissions', array('id' => $submissionid));

$prefill = array();
$prefill = survey_set_prefill($survey, $canaccessadvancedform, $formpage, $submissionid, true);
$prefill['submissionid'] = $submissionid;
$prefill['formpage'] = $formpage; // go to populate the hidden field of the form

$mform->set_data($prefill);
$mform->display();