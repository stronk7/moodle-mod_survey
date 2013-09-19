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
require_once($CFG->dirroot.'/mod/survey/field/select/lib.php');

class survey_pluginform extends mod_survey_itembaseform {

    public function definition() {
        // -------------------------------------------------------------------------------
        // $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // I start with the common "section" form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // newitem::options
        // ----------------------------------------
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_select'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_select');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::labelother
        // ----------------------------------------
        $fieldname = 'labelother';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_select'), array('maxlength' => '64', 'size' => '50'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_select');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_select'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_select'), ' ', false);
        $mform->setDefault($fieldname, SURVEY_INVITATIONDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_select');

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $mform->addElement('text', $fieldname, null);
        $mform->setType($fieldname, PARAM_RAW);
        $mform->disabledIf($fieldname, 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);

        // ----------------------------------------
        // newitem::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = array(SURVEYFIELD_SELECT_RETURNVALUES => get_string('returnvalues', 'surveyfield_select'),
                         SURVEYFIELD_SELECT_RETURNLABELS => get_string('returnlabels', 'surveyfield_select'),
                         SURVEYFIELD_SELECT_RETURNPOSITION => get_string('returnposition', 'surveyfield_select'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_radiobutton'), $options);
        $mform->setDefault($fieldname, SURVEYFIELD_CHECKBOX_RETURNVALUES);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_radiobutton');
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        // clean inputs
        $clean_options = survey_textarea_to_array($data['options']);
        $clean_labelother = trim($data['labelother']);
        $clean_defaultvalue = isset($data['defaultvalue']) ? trim($data['defaultvalue']) : '';

        // build $value and $label arrays starting from $clean_options and $clean_labelother
        $values = array();
        $labels = array();

        foreach ($clean_options as $option) {
            if (strpos($option, SURVEY_VALUELABELSEPARATOR) === false) {
                $values[] = trim($option);
                $labels[] = trim($option);
            } else {
                $pair = explode(SURVEY_VALUELABELSEPARATOR, $option);
                $values[] = $pair[0];
                $labels[] = $pair[1];
            }
        }
        if (!empty($clean_labelother)) {
            if (strpos($clean_labelother, SURVEY_OTHERSEPARATOR) === false) {
                $values[] = $clean_labelother;
                $labels[] = $clean_labelother;
            } else {
                $pair = explode(SURVEY_OTHERSEPARATOR, $clean_labelother);
                $values[] = $pair[1];
                $labels[] = $pair[0];
            }
        }

        // if (default == noanswer but the item is mandatory) then => error
        if ( ($data['defaultoption'] == SURVEY_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'survey');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'survey', $a);
        }

        if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
            if (empty($data['defaultvalue'])) {
                // -----------------------------
                // first check
                // user asks for SURVEY_CUSTOMDEFAULT but doesn't provide it
                // -----------------------------
                $a = get_string('standarddefault', 'surveyfield_select');
                $errors['defaultvalue_group'] = get_string('default_missing', 'surveyfield_select', $a);
            } else {
                // -----------------------------
                // second check
                // each item of default has to be among options item OR has to be == to otherlabel value
                // -----------------------------
                if (!in_array($clean_defaultvalue, $labels)) {
                    $errors['defaultvalue_group'] = get_string('defaultvalue_err', 'surveyfield_select', $clean_defaultvalue);
                }

                // -----------------------------
                // third check
                // each single option item has to be unique
                // -----------------------------
                $array_unique = array_unique($clean_options);
                if (count($clean_options) != count($array_unique)) {
                    $errors['options'] = get_string('options_err', 'survey', $default);
                }
            }
        }

        return $errors;
    }
}