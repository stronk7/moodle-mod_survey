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
require_once($CFG->dirroot.'/mod/survey/classes/itemlist.class.php');

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

add_to_log($course->id, 'survey', 'view', "elements.php?id=$cm->id", $survey->name, $cm->id);

// params never passed but needed by called class
$type = null;
$plugin = null;
$itemid = 0;
$itemtomove = 0;
$userfeedback = SURVEY_NOFEEDBACK;
$action = SURVEY_NOACTION;
$view = SURVEY_SERVESURVEY;
$lastitembefore = 0;
$confirm = SURVEY_UNCONFIRMED;
$nextindent = 0;
$parentid = 0;
$saveasnew = null;

$context = context_module::instance($cm->id);
require_capability('mod/survey:additems', $context);

// -----------------------------
// calculations
// -----------------------------
$itemlistman = new mod_survey_itemlist($cm, $context, $survey, $type, $plugin, $itemid, $action, $view, $itemtomove,
                                           $lastitembefore, $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/survey/items_validate.php', array('id' => $cm->id));
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();

$moduletab = SURVEY_TABITEMS; // needed by tabs.php
$modulepage = SURVEY_ITEMS_VALIDATE; // needed by tabs.php
require_once($CFG->dirroot.'/mod/survey/tabs.php');

$itemlistman->validate_relations();

// Finish the page
echo $OUTPUT->footer();
