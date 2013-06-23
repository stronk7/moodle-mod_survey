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
require_once($CFG->dirroot.'/mod/survey/classes/item.class.php');

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

$plugin = optional_param('plugin', null, PARAM_TEXT);
$type = optional_param('type', null, PARAM_TEXT);
$plugin = optional_param('plugin', null, PARAM_TEXT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$action = optional_param('act', SURVEY_NOACTION, PARAM_INT);
$itemtomove = optional_param('itm', 0, PARAM_INT);
$lastitembefore = optional_param('lib', 0, PARAM_INT);
$confirm = optional_param('cnf', 0, PARAM_INT);
$nextindent = optional_param('ind', 0, PARAM_INT);
$parentid = optional_param('pit', 0, PARAM_INT);
$userfeedback = optional_param('ufd', SURVEY_NOFEEDBACK, PARAM_INT);
$saveasnew = optional_param('saveasnew', null, PARAM_TEXT);

$context = context_module::instance($cm->id);
require_capability('mod/survey:additems', $context);

// ////////////////////////////////////////////////////////////
// manager definition
// ////////////////////////////////////////////////////////////
$item_manager = new mod_survey_itemelement($cm, $context, $survey, $type, $plugin, $itemid, $action, $itemtomove,
                                           $lastitembefore, $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);

// ////////////////////////////////////////////////////////////
// calculations
// ////////////////////////////////////////////////////////////
require_once($CFG->dirroot.'/mod/survey/'.$item_manager->type.'/'.$item_manager->plugin.'/plugin.class.php');
require_once($CFG->dirroot.'/mod/survey/'.$item_manager->type.'/'.$item_manager->plugin.'/plugin_form.php');

// ////////////////////////////
// get item
$itemclass = 'survey'.$item_manager->type.'_'.$item_manager->plugin;
$item = new $itemclass($item_manager->itemid);
if (method_exists($item, 'item_set_editor')) {
    $item->item_set_editor($cm->id, $item);
}
// end of: get item
// ////////////////////////////

// ////////////////////////////
// define $item_form return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('items_setup.php', $paramurl);
// end of: define $item_form return url
// ////////////////////////////

// ////////////////////////////
// prepare params for the form
$formparams = new stdClass();
$formparams->survey = $survey;                               // needed to setup date boundaries in date fields
$formparams->item = $item;                                   // needed in many situations
$formparams->hassubmissions = $item_manager->hassubmissions; // are editing features restricted?
$item_form = new survey_pluginform($formurl, $formparams);
// end of: prepare params for the form
// ////////////////////////////

// ////////////////////////////
// manage form submission
if ($item_form->is_cancelled()) {
    $returnurl = new moodle_url('items_manage.php', $paramurl);
    redirect($returnurl);
}

if ($fromform = $item_form->get_data()) {
    // was this item forced to be new?
    if (!empty($saveasnew)) {
        $fromform->itemid = 0;
    }

    $item->item_save($fromform);

    $paramurl = array('id' => $cm->id, 'ufd' => $userfeedback);
    $returnurl = new moodle_url('items_manage.php', $paramurl);
    redirect($returnurl);
}
// end of: manage form submission
// ////////////////////////////

// ////////////////////////////////////////////////////////////
// Output starts here
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

if ($item_manager->hassubmissions) {
    echo $OUTPUT->notification(get_string('hassubmissions_alert', 'survey'));
}
$item_form->set_data($item);
$item_form->display();

// Finish the page
echo $OUTPUT->footer();
