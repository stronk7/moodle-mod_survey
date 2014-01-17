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
$view = optional_param('view', SURVEY_SERVESURVEY, PARAM_INT);
$itemtomove = optional_param('itm', 0, PARAM_INT);
$lastitembefore = optional_param('lib', 0, PARAM_INT);
$confirm = optional_param('cnf', SURVEY_UNCONFIRMED, PARAM_INT);
$nextindent = optional_param('ind', 0, PARAM_INT);
$parentid = optional_param('pid', 0, PARAM_INT);
$userfeedback = optional_param('ufd', SURVEY_NOFEEDBACK, PARAM_INT);
$saveasnew = optional_param('saveasnew', null, PARAM_TEXT);

if ($action != SURVEY_NOACTION) {
    require_sesskey();
}
$context = context_module::instance($cm->id);
require_capability('mod/survey:manageitems', $context);

// -----------------------------
// calculations
// -----------------------------

// -----------------------------
// the form showing the drop down menu with the list of mater templates
$itemcount = $DB->count_records('survey_item', array('surveyid' => $survey->id));
if (!$itemcount) {
    require_once($CFG->dirroot.'/mod/survey/classes/mtemplate.class.php');
    require_once($CFG->dirroot.'/mod/survey/forms/mtemplates/applymtemplate_form.php');

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
    $formparams->inline = true;

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

        $redirecturl = new moodle_url('view.php', array('id' => $cm->id, 'view' => SURVEY_PREVIEWSURVEY));
        redirect($redirecturl);
    }
    // end of: manage form submission
    // -----------------------------
}
// end of: the form showing the drop down menu with the list of mater templates
// -----------------------------

// -----------------------------
// the form showing the drop down menu with the list of items
$itemlistman = new mod_survey_itemlist($cm, $context, $survey, $type, $plugin, $itemid, $action, $view, $itemtomove,
                                            $lastitembefore, $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);
// I need to execute this method before the page load because it modifies TAB elements
$itemlistman->drop_multilang();

// -----------------------------
// output starts here
// -----------------------------
$url = new moodle_url('/mod/survey/items_manage.php', array('s' => $survey->id));
$PAGE->set_url($url);
$PAGE->set_title($survey->name);
$PAGE->set_heading($course->shortname);

// make bold the navigation menu/link that refers to me
navigation_node::override_active_url($url);

// other things you may want to set - remove if not needed
// $PAGE->set_cacheable(false);
// $PAGE->set_focuscontrol('some-html-id');

echo $OUTPUT->header();

$moduletab = SURVEY_TABITEMS; // needed by tabs.php
$modulepage = SURVEY_ITEMS_MANAGE; // needed by tabs.php
require_once($CFG->dirroot.'/mod/survey/tabs.php');

$itemlistman->manage_actions();

$itemlistman->display_user_feedback();

if ($itemlistman->hassubmissions) {
    echo $OUTPUT->notification(get_string('hassubmissions_alert', 'survey'));
}

// add Master templates selection form
if (!$itemcount) {
    $message = get_string('beginfromscratch', 'survey');
    echo $OUTPUT->box($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

    $applymtemplate->display();
}

// add item form
if (!$itemlistman->survey->template) {
    $riskyediting = ($survey->riskyeditdeadline > time());

    if (!$hassubmissions || $riskyediting) {
        if (has_capability('mod/survey:additems', $context)) {
            $paramurl = array('id' => $cm->id);
            $formurl = new moodle_url('items_setup.php', $paramurl);

            $itemtype = new survey_itemtypeform($formurl);
            $itemtype->display();
        }
    }
}

$itemlistman->manage_items();

// Finish the page
echo $OUTPUT->footer();
