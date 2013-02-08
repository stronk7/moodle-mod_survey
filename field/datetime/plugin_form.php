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
require_once($CFG->dirroot.'/mod/survey/itembase_form.php');
require_once($CFG->dirroot.'/mod/survey/field/datetime/lib.php');

class survey_pluginform extends surveyitem_baseform {

    function definition() {
        // -------------------------------------------------------------------------------
        // acquisisco i valori per pre-definire i campi della form
        $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // comincio con la "sezione" comune della form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // -------------------------------------------------------------------------------
        $startyear = $this->_customdata->survey->startyear;
        $stopyear = $this->_customdata->survey->stopyear;

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $days = array_combine(range(1, 31), range(1, 31));
        // $months = array_combine(range(0, 11), range(0, 11));
        $months = array();
        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B"); // january, february, march...
        }
        $years = array_combine(range($startyear, $stopyear), range($startyear, $stopyear));
        $hours = array_combine(range(0, 23), range(0, 23));
        $minutes = array_combine(range(0, 59), range(0, 59));

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_datetime'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('currentdatetimedefault', 'surveyfield_datetime'), SURVEY_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('likelast', 'survey'), SURVEY_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $minutes);
        $separator = array(' ', ' ', ' ', ' ', '<br />', ' ', ' ', ' ');
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_datetime'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_datetime');
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);

        $mform->setDefault('defaultoption', SURVEY_TIMENOWDEFAULT);
        if ($item->defaultoption == SURVEY_CUSTOMDEFAULT) {
            $justadefault = $item->item_split_unix_time($item->lowerbound);
            $mform->setDefault($fieldname.'_day', $justadefault['mday']);
            $mform->setDefault($fieldname.'_month', $justadefault['mon']);
            $mform->setDefault($fieldname.'_year', $justadefault['year']);
            $mform->setDefault($fieldname.'_hour', $justadefault['hours']);
            $mform->setDefault($fieldname.'_minute', $justadefault['minutes']);
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
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $minutes);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_datetime'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_datetime');
        $mform->setDefault($fieldname.'_year', $startyear);
        $mform->setDefault($fieldname.'_month', '1');
        $mform->setDefault($fieldname.'_day', '1');
        $mform->setDefault($fieldname.'_hour', '0');
        $mform->setDefault($fieldname.'_minute', '0');

        // ----------------------------------------
        // newitem::upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $minutes);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_datetime'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_datetime');
        $mform->setDefault($fieldname.'_year', $stopyear);
        $mform->setDefault($fieldname.'_month', '12');
        $mform->setDefault($fieldname.'_day', '31');
        $mform->setDefault($fieldname.'_hour', '23');
        $mform->setDefault($fieldname.'_minute', '59');

        $this->add_item_buttons();
    }

    function validation($data, $files) {
        // acquisisco i valori per pre-definire i campi della form
        $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_datetime_to_unix_time($data['defaultvalue_year'], $data['defaultvalue_month'], $data['defaultvalue_day'], $data['defaultvalue_hour'], $data['defaultvalue_minute']);
            $lowerbound = $item->item_datetime_to_unix_time($data['lowerbound_year'], $data['lowerbound_month'], $data['lowerbound_day'], $data['defaultvalue_hour'], $data['defaultvalue_minute']);
            $upperbound = $item->item_datetime_to_unix_time($data['upperbound_year'], $data['upperbound_month'], $data['upperbound_day'], $data['defaultvalue_hour'], $data['defaultvalue_minute']);
            if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyfield_datetime');
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