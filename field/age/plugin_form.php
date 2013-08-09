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
require_once($CFG->dirroot.'/mod/survey/field/age/lib.php');

class survey_pluginform extends mod_survey_itembaseform {

    public function definition() {
        $maximumage = get_config('surveyfield_age', 'maximumage');

        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        // -------------------------------------------------------------------------------
        // I start with the common "section" form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // -------------------------------------------------------------------------------
        $format = get_string('strftimemonthyear', 'langconfig');

        $years = array_combine(range(0, $maximumage), range(0, $maximumage));
        $months = array_combine(range(0, 11), range(0, 11));

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_age'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $separator = array(' ', ' ', '<br />', ' ');
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_age'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_age');
        $mform->setDefault('defaultoption', SURVEY_INVITATIONDEFAULT);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);
        if (is_null($item->defaultvalue) || ($item->defaultvalue == SURVEY_INVITATIONDBVALUE)) {
            $mform->setDefault($fieldname.'_year', $item->lowerbound_year);
            $mform->setDefault($fieldname.'_month', $item->lowerbound_month);
        }

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::lowerbound
        // ----------------------------------------
        $fieldname = 'lowerbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_age'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_age');
        $mform->setDefault($fieldname.'_year', '0');
        $mform->setDefault($fieldname.'_month', '0');
        //$mform->disabledIf($fieldname.'_group', $fieldname.'_select', 'neq', constant($constantname));

        // ----------------------------------------
        // newitem::upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_age'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_age');
        $mform->setDefault($fieldname.'_year', $maximumage);
        $mform->setDefault($fieldname.'_month', '11');

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        $errors = parent::validation($data, $files);

        // "noanswer" default option is not allowed when the item is mandator
        if ( ($data['defaultoption'] == SURVEY_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'survey');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'survey', $a);
        }

        $lowerbound = $item->item_age_to_unix_time($data['lowerbound_year'], $data['lowerbound_month']);
        $upperbound = $item->item_age_to_unix_time($data['upperbound_year'], $data['upperbound_month']);
        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('lowerequaltoupper', 'surveyfield_age');
        }

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_age_to_unix_time($data['defaultvalue_year'], $data['defaultvalue_month']);

            if ($lowerbound < $upperbound) {
                // internal range
                if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                    $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyfield_age');
                }
            }

            if ($lowerbound > $upperbound) {
                // external range
                if (($defaultvalue > $upperbound) && ($defaultvalue < $lowerbound)) {
                    $a = get_string('upperbound', 'surveyfield_age');
                    $errors['defaultvalue_group'] = get_string('outofexternalrangedefault', 'surveyfield_age', $a);
                }
            }
        }

        return $errors;
    }
}