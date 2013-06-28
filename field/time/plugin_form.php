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

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/survey/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/survey/field/time/lib.php');

class survey_pluginform extends surveyitem_baseform {

    public function definition() {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // I start with the common "section" form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        // -------------------------------------------------------------------------------
        $hoptions = array();
        for ($i = 0; $i <= 23; $i++) {
            $hoptions[$i] = sprintf("%02d", $i);
        }
        $moptions = array();
        for ($i = 0; $i <= 59; $i++) {
            $moptions[$i] = sprintf("%02d", $i);
        }

        // ----------------------------------------
        // newitem::step
        // ----------------------------------------
        $fieldname = 'step';
        $options = array();
        $options[1] = get_string('oneminute', 'surveyfield_time');
        $options[5] = get_string('fiveminutes', 'surveyfield_time');
        $options[10] = get_string('tenminutes', 'surveyfield_time');
        $options[15] = get_string('fifteenminutes', 'surveyfield_time');
        $options[20] = get_string('twentyminutes', 'surveyfield_time');
        $options[30] = get_string('thirtyminutes', 'surveyfield_time');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_time'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_time');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_time'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('currenttimedefault', 'surveyfield_time'), SURVEY_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('likelast', 'survey'), SURVEY_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hoptions);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $moptions);
        $separator = array(' ', ' ', ' ', ' ', '<br />', ' ');
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_time'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_time');
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);

        $mform->setDefault('defaultoption', SURVEY_TIMENOWDEFAULT);
        if ($item->defaultoption == SURVEY_CUSTOMDEFAULT) {
            $justadefault = $item->item_split_unix_time($item->lowerbound);
            $mform->setDefault($fieldname.'_hour', $justadefault['hours']);
            $mform->setDefault($fieldname.'_minute', $justadefault['minutes']);
        }

        // ----------------------------------------
        // newitem::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_time'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_time');

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
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hoptions);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $moptions);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_time'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_time');
        $mform->setDefault($fieldname.'_hour', 0);
        $mform->setDefault($fieldname.'_minute', 0);

        // ----------------------------------------
        // newitem::upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hoptions);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $moptions);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_time'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_time');
        $mform->setDefault($fieldname.'_hour', 23);
        $mform->setDefault($fieldname.'_minute', 59);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        $errors = parent::validation($data, $files);

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_time_to_unix_time($data['defaultvalue_hour'], $data['defaultvalue_minute']);
            $lowerbound = $item->item_time_to_unix_time($data['lowerbound_hour'], $data['lowerbound_minute']);
            $upperbound = $item->item_time_to_unix_time($data['upperbound_hour'], $data['upperbound_minute']);

            if ($lowerbound == $upperbound) {
                $errors['lowerbound_group'] = get_string('lowerequaltoupper', 'surveyfield_time');
            }

            if ($lowerbound < $upperbound) {
                // internal range
                if ($defaultvalue < $lowerbound) {
                    $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyfield_time');
                }
                if ($defaultvalue > $upperbound) {
                    $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyfield_time');
                }
            }

            if ($lowerbound > $upperbound) {
                // external range
                if ($defaultvalue > $lowerbound) {
                    $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyfield_time');
                }
                if ($defaultvalue < $upperbound) {
                    $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyfield_time');
                }
            }
        }

        // if (default == noanswer) but item is required => error
        if ( ($data['defaultoption'] == SURVEY_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'survey');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'survey', $a);
        }

        return $errors;
    }
}