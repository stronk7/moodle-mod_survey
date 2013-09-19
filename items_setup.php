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

$type = optional_param('type', null, PARAM_TEXT);
$plugin = optional_param('plugin', null, PARAM_TEXT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$action = optional_param('act', SURVEY_NOACTION, PARAM_INT);

// params never passed but needed by called class
$itemtomove = 0;
$userfeedback = SURVEY_NOFEEDBACK;
$lastitembefore = 0;
$confirm = SURVEY_UNCONFIRMED;
$nextindent = 0;
$parentid = 0;
$saveasnew = null;

$context = context_module::instance($cm->id);
require_capability('mod/survey:additems', $context);

// ////////////////////////////////////////////////////////////
// calculations
// ////////////////////////////////////////////////////////////
$itemlist_manager = new mod_survey_itemlist($cm, $context, $survey, $type, $plugin, $itemid, $action, $itemtomove,
                                           $lastitembefore, $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);
$itemlist_manager->prevent_direct_user_input();

require_once($CFG->dirroot.'/mod/survey/'.$itemlist_manager->type.'/'.$itemlist_manager->plugin.'/plugin.class.php');
require_once($CFG->dirroot.'/mod/survey/'.$itemlist_manager->type.'/'.$itemlist_manager->plugin.'/plugin_form.php');

// ////////////////////////////
// get item
$itemclass = 'survey'.$itemlist_manager->type.'_'.$itemlist_manager->plugin;
$item = new $itemclass($itemlist_manager->itemid);
$item->item_set_editor($cm->id, $item);
// end of: get item
// ////////////////////////////

// ////////////////////////////
// define $itemform return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('items_setup.php', $paramurl);
// end of: define $itemform return url
// ////////////////////////////

// ////////////////////////////
// prepare params for the form
$formparams = new stdClass();
$formparams->survey = $survey;                               // needed to setup date boundaries in date fields
$formparams->item = $item;                                   // needed in many situations
$formparams->hassubmissions = $itemlist_manager->hassubmissions; // are editing features restricted?
$itemform = new survey_pluginform($formurl, $formparams);
// end of: prepare params for the form
// ////////////////////////////

// ////////////////////////////
// manage form submission
if ($itemform->is_cancelled()) {
    $returnurl = new moodle_url('items_manage.php', $paramurl);
    redirect($returnurl);
}

if ($fromform = $itemform->get_data()) {
    // was this item forced to be new?
    if (!empty($fromform->saveasnew)) {
        $fromform->itemid = 0;
    }

    $item->item_save($fromform);

    $paramurl = array('id' => $cm->id, 'ufd' => $item->userfeedback);
    $returnurl = new moodle_url('items_manage.php', $paramurl);
    redirect($returnurl);
}
// end of: manage form submission
// ////////////////////////////

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
$currentpage = SURVEY_ITEMS_SETUP; // needed by tabs.php
include_once($CFG->dirroot.'/mod/survey/tabs.php');

if ($itemlist_manager->hassubmissions) {
    echo $OUTPUT->notification(get_string('hassubmissions_alert', 'survey'));
}
$itemlist_manager->item_welcome();
$itemform->set_data($item);
$itemform->display();

// Finish the page
echo $OUTPUT->footer();
