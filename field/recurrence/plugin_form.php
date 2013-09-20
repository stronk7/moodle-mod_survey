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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/survey/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/survey/field/recurrence/lib.php');

class survey_pluginform extends mod_survey_itembaseform {

    public function definition() {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        // -------------------------------------------------------------------------------
        // start with common section of the form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // -------------------------------------------------------------------------------
        $startyear = $this->_customdata->survey->startyear;
        $stopyear = $this->_customdata->survey->stopyear;

        // ----------------------------------------
        // newitem::defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $days = array_combine(range(1, 31), range(1, 31));
        $months = array();
        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // january, february, march...
        }
        $years = array_combine(range($startyear, $stopyear), range($startyear, $stopyear));

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_recurrence'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('currentrecurrencedefault', 'surveyfield_recurrence'), SURVEY_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('likelast', 'survey'), SURVEY_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_recurrence'), ' ', false);
        $mform->setDefault($fieldname, SURVEY_INVITATIONDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_recurrence');

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, ' ', false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);

        // ----------------------------------------
        // newitem::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_recurrence'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_recurrence');

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::lowerbound
        // ----------------------------------------
        $fieldname = 'lowerbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_recurrence'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_recurrence');
        $mform->setDefault($fieldname.'_day', '1');
        $mform->setDefault($fieldname.'_month', '1');

        // ----------------------------------------
        // newitem::upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_recurrence'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_recurrence');
        $mform->setDefault($fieldname.'_day', '31');
        $mform->setDefault($fieldname.'_month', '12');

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        $errors = parent::validation($data, $files);

        $lowerbound = $item->item_recurrence_to_unix_time($data['lowerbound_month'], $data['lowerbound_day']);
        $upperbound = $item->item_recurrence_to_unix_time($data['upperbound_month'], $data['upperbound_day']);
        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('lowerequaltoupper', 'surveyfield_recurrence');
        }
        if ($lowerbound > $upperbound) {
            $errors['lowerbound_group'] = get_string('lowergreaterthanupper', 'surveyfield_integer');
        }

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_recurrence_to_unix_time($data['defaultvalue_month'], $data['defaultvalue_day']);

            if (!$item->item_check_monthday($data['defaultvalue_day'], $data['defaultvalue_month'])) {
                $errors['defaultvalue_group'] = get_string('notvaliddefault', 'surveyfield_recurrence');
            }
            if (!$item->item_check_monthday($data['lowerbound_day'], $data['lowerbound_month'])) {
                $errors['lowerbound_group'] = get_string('notvalidlowerbound', 'surveyfield_recurrence');
            }
            if (!$item->item_check_monthday($data['upperbound_day'], $data['upperbound_month'])) {
                $errors['upperbound_group'] = get_string('notvalidupperbound', 'surveyfield_recurrence');
            }

            // internal range
            if (($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound)) {
                $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyfield_recurrence');
            }
        }

        // if (default == noanswer && the field is mandatory) => error
        if ( ($data['defaultoption'] == SURVEY_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'survey');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'survey', $a);
        }
        return $errors;
    }
}