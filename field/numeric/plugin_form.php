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

class survey_pluginform extends mod_survey_itembaseform {

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
        $options = array_combine(range(0, 8), range(0, 8));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_numeric'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_numeric');

        // ----------------------------------------
        // newitem::lowerbound
        // ----------------------------------------
        $fieldname = 'lowerbound';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_numeric');
        $mform->setType($fieldname, PARAM_RAW);

        // ----------------------------------------
        // newitem::upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_numeric');
        $mform->setType($fieldname, PARAM_RAW);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        $errors = parent::validation($data, $files);

        $pattern = '~^\s*([0-9]+)'.$item->decimalseparator.'?([0-9]*)\s*$~';

        $draftnumber = $data['lowerbound'];
        // constrain default between boundaries
        if (strlen($draftnumber)) {
            if (!preg_match($pattern, $draftnumber, $matches)) {
                $errors['lowerbound'] = get_string('lowerbound_notanumber', 'surveyfield_numeric');
                return $errors;
            } else {
                // $lowerbound = $matches[1].'.'.$matches[2];
                $lowerbound = unformat_float($draftnumber, true);
            }
        }

        $draftnumber = $data['upperbound'];
        // constrain default between boundaries
        if (strlen($draftnumber)) {
            if (!preg_match($pattern, $draftnumber, $matches)) {
                $errors['upperbound'] = get_string('upperbound_notanumber', 'surveyfield_numeric');
                return $errors;
            } else {
                // $upperbound = $matches[1].'.'.$matches[2];
                $upperbound = unformat_float($draftnumber, true);
            }
        }

        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('lowerequaltoupper', 'surveyfield_numeric');
        }

        // $default = unformat_float($data['defaultvalue'], true);
        // if (!is_numeric($default)) {

        $draftnumber = $data['defaultvalue'];
        // constrain default between boundaries
        if (strlen($draftnumber)) {
            if (!preg_match($pattern, $draftnumber, $matches)) {
                $errors['defaultvalue'] = get_string('default_notanumber', 'surveyfield_numeric');
            } else {
                // $defaultvalue = $matches[1].'.'.$matches[2];
                $defaultvalue = unformat_float($draftnumber, true);
                // if it is < 0 but has been defined as unsigned, shouts
                if ((!isset($data['signed'])) && ($defaultvalue < 0)) {
                    $errors['defaultvalue'] = get_string('defaultsignnotallowed', 'surveyfield_numeric');
                }

                $is_integer = (bool)(strval(intval($defaultvalue)) == strval($defaultvalue));
                // if it has decimal but has been defined as integer, shouts
                if ( ($data['decimals'] == 0) && (!$is_integer) ) {
                    $errors['defaultvalue'] = get_string('default_notinteger', 'surveyfield_numeric');
                }

                if (($lowerbound) && ($upperbound)) {
                    if ($lowerbound < $upperbound) {
                        // internal range
                        if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                            $errors['defaultvalue'] = get_string('outofrangedefault', 'surveyfield_numeric');
                        }
                    }

                    if ($lowerbound > $upperbound) {
                        // external range
                        if (($defaultvalue > $upperbound) && ($defaultvalue < $lowerbound)) {
                            $a = get_string('upperbound', 'surveyfield_numeric');
                            $errors['defaultvalue'] = get_string('outofexternalrangedefault', 'surveyfield_numeric', $a);
                        }
                    }
                } else {
                    if ($lowerbound) {
                        // if defaultvalue is < $this->lowerbound, shouts
                        if ($defaultvalue < $lowerbound) {
                            $errors['defaultvalue'] = get_string('default_outofrange', 'surveyfield_numeric');
                        }
                    }

                    if ($upperbound) {
                        // if defaultvalue is > $this->upperbound, shouts
                        if ($defaultvalue > $upperbound) {
                            $errors['defaultvalue'] = get_string('default_outofrange', 'surveyfield_numeric');
                        }
                    }
                }
            }
        }

        return $errors;
    }
}