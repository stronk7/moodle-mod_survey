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
require_once($CFG->dirroot.'/mod/survey/field/numeric/lib.php');

class survey_pluginform extends surveyitem_baseform {

    public function definition() {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        // -------------------------------------------------------------------------------
        // I start with the common "section" form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_numeric');
        $mform->setType($fieldname, PARAM_TEXT); // maybe I use ',' as decimal separator so it is not a INT and not a FLOAT

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::signed
        // ----------------------------------------
        $fieldname = 'signed';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyfield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_numeric');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::decimals
        // ----------------------------------------
        $fieldname = 'decimals';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_numeric');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::lowerbound
        // ----------------------------------------
        $fieldname = 'lowerbound';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_numeric');
        $mform->setType($fieldname, PARAM_ALPHANUM);

        // ----------------------------------------
        // newitem::upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_numeric');
        $mform->setType($fieldname, PARAM_ALPHANUM);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        $errors = parent::validation($data, $files);

        // constrain default between boundaries
        if ($data['defaultvalue']) {
            if (!$thenumber = unformat_float($data['defaultvalue'])) {
                $errors['defaultvalue'] = get_string('default_notanumber', 'surveyfield_numeric');
            } else {
                // if it is < 0 but has been defined as unsigned, shouts
                if ((!$data['signed']) && ($thenumber < 0)) {
                    $errors['defaultvalue'] = get_string('defaultsignnotunallowed', 'surveyfield_numeric');
                }

                // if it is < $this->lowerbound, shouts
                if (!empty($data['lowerbound']) && ($thenumber < $data['lowerbound'])) {
                    $errors['defaultvalue'] = get_string('default_outofrange', 'surveyfield_numeric');
                }

                // if it is > $this->upperbound, shouts
                if (!empty($data['upperbound']) && ($thenumber > $data['upperbound'])) {
                    $errors['defaultvalue'] = get_string('default_outofrange', 'surveyfield_numeric');
                }

                $is_integer = (bool)(strval(intval($thenumber)) == strval($thenumber));
                // if it has decimal but has been defined as integer, shouts
                if ( ($data['decimals'] == 0) && (!$is_integer) ) {
                    $errors['defaultvalue'] = get_string('default_notinteger', 'surveyfield_numeric');
                }
            }
        }
        return $errors;
    }
}