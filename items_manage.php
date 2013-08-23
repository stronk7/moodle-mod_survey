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
require_once($CFG->dirroot.'/mod/survey/forms/items/selectitem_form.php');

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

$type = optional_param('type', null, PARAM_TEXT);
$plugin = optional_param('plugin', null, PARAM_TEXT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$action = optional_param('act', SURVEY_NOACTION, PARAM_INT);
$itemtomove = optional_param('itm', 0, PARAM_INT);
$lastitembefore = optional_param('lib', 0, PARAM_INT);
$confirm = optional_param('cnf', SURVEY_UNCONFIRMED, PARAM_INT);
$nextindent = optional_param('ind', 0, PARAM_INT);
$parentid = optional_param('pid', 0, PARAM_INT);
$userfeedback = optional_param('ufd', SURVEY_NOFEEDBACK, PARAM_INT);
$saveasnew = optional_param('saveasnew', null, PARAM_TEXT);

$context = context_module::instance($cm->id);
require_capability('mod/survey:manageitems', $context);

// ////////////////////////////////////////////////////////////
// calculations
// ////////////////////////////////////////////////////////////
$itemlist_manager = new mod_survey_itemlist($cm, $context, $survey, $type, $plugin, $itemid, $action, $itemtomove,
                                            $lastitembefore, $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);
// I need to execute this method before the page load because it modifies TAB elements
$itemlist_manager->drop_multilang();

// ////////////////////////////////////////////////////////////
// output starts here
// ////////////////////////////////////////////////////////////
$PAGE->set_url('/mod/survey/view.php', array('id' => $cm->id));
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();

$currenttab = SURVEY_TABITEMS; // needed by tabs.php
$currentpage = SURVEY_ITEMS_MANAGE; // needed by tabs.php
include_once($CFG->dirroot.'/mod/survey/tabs.php');

$itemlist_manager->manage_actions();

$itemlist_manager->display_user_feedback();

if ($itemlist_manager->hassubmissions) {
    echo $OUTPUT->notification(get_string('hassubmissions_alert', 'survey'));
}

// add item form
if (!$itemlist_manager->survey->template) {
    $forcemodifications = get_config('survey', 'forcemodifications');

    if (!$hassubmissions || $forcemodifications) {
        if (has_capability('mod/survey:additems', $context)) {
            $paramurl = array('id' => $cm->id);
            $formurl = new moodle_url('items_setup.php', $paramurl);

            $itemtype = new survey_itemtypeform($formurl);
            $itemtype->display();
        }
    }
}

$itemlist_manager->manage_items();

// Finish the page
echo $OUTPUT->footer();
