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
require_once($CFG->dirroot.'/mod/survey/field/shortdate/lib.php');

class survey_pluginform extends surveyitem_baseform {

    function definition() {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // comincio con la "sezione" comune della form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;
        $hassubmissions = $this->_customdata->hassubmissions;

        // -------------------------------------------------------------------------------
        $startyear = $this->_customdata->survey->startyear;
        $stopyear = $this->_customdata->survey->stopyear;

        // -------------------------------------------------------------------------------
        $format = get_string('strftimemonthyear', 'langconfig');

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $months = array();
        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B"); // january, february, march...
        }
        $years = array_combine(range($startyear, $stopyear), range($startyear, $stopyear));

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_shortdate'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('currentshortdatedefault', 'surveyfield_shortdate'), SURVEY_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('likelast', 'survey'), SURVEY_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $separator = array(' ', ' ', ' ', ' ', '<br />', ' ');
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_shortdate'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_shortdate');
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);

        $mform->setDefault('defaultoption', SURVEY_TIMENOWDEFAULT);
        if ($item->defaultoption == SURVEY_CUSTOMDEFAULT) {
            $justadefault = $item->item_split_unix_time($item->lowerbound);
            $mform->setDefault($fieldname.'_month', $justadefault['mon']);
            $mform->setDefault($fieldname.'_year', $justadefault['year']);
        }

        // ----------------------------------------
        // newitem::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = array();
        $options[SURVEYFIELD_SHORTDATE_USERFORMAT] = get_string('formatuser', 'surveyfield_shortdate');
        $options[SURVEYFIELD_SHORTDATE_MYFORMAT] = get_string('formatmy', 'surveyfield_shortdate');
        $options[SURVEYFIELD_SHORTDATE_YMFORMAT] = get_string('formatym', 'surveyfield_shortdate');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_shortdate'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_shortdate');

        if (!$hassubmissions) {
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
            $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
            $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
            $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_shortdate'), ' ', false);
            $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_shortdate');
            $mform->setDefault($fieldname.'_month', 1);
            $mform->setDefault($fieldname.'_year', $startyear);

            // ----------------------------------------
            // newitem::upperbound
            // ----------------------------------------
            $fieldname = 'upperbound';
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
            $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
            $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_shortdate'), ' ', false);
            $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_shortdate');
            $mform->setDefault($fieldname.'_month', 12);
            $mform->setDefault($fieldname.'_year', $stopyear);
        }

        $this->add_item_buttons();
    }

    function validation($data, $files) {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_shortdate_to_unix_time($data['defaultvalue_month'], $data['defaultvalue_year']);
            $lowerbound = $item->item_shortdate_to_unix_time($data['lowerbound_month'], $data['lowerbound_year']);
            $upperbound = $item->item_shortdate_to_unix_time($data['upperbound_month'], $data['upperbound_year']);

            if ( ($lowerbound > $upperbound) ) {
                $errors['upperbound_group'] = get_string('ierr_invertupperlowerbounds', 'surveyfield_shortdate');
            } else if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                $errors['defaultvalue_group'] = get_string('ierr_outofrangedefault', 'surveyfield_shortdate');
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