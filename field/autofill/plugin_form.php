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
require_once($CFG->dirroot.'/mod/survey/field/autofill/lib.php');

class survey_pluginform extends mod_survey_itembaseform {

    public function definition() {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // -------------------------------------------------------------------------------
        // I close with the common section of the form
        parent::definition();

        // ----------------------------------------
        // newitem::contentelement$i
        // ----------------------------------------
        $options = survey_autofill_get_elements($survey->id);
        for ($i = 1; $i < 6; $i++) {
            $fieldname = 'element_'.$i;

            $elementgroup = array();
            $elementgroup[] = $mform->createElement('selectgroups', $fieldname.'_select', '', $options);
            $elementgroup[] = $mform->createElement('text', $fieldname.'_text', '');
            $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_autofill'), ' ', false);
            $constantname = 'SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT;

            $mform->disabledIf($fieldname.'_text', $fieldname.'_select', 'neq', constant($constantname));

            $mform->addHelpButton($fieldname.'_group', 'contentelement_group', 'surveyfield_autofill');
            $mform->setType($fieldname.'_text', PARAM_TEXT);
        }

        // ----------------------------------------
        // newitem::hiddenfield
        // ----------------------------------------
        $fieldname = 'hiddenfield';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyfield_autofill'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_autofill');
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        // $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        $errors = parent::validation($data, $files);

        $uniontext = '';
        for ($i = 1; $i < 6; $i++) {
            $fieldname = 'element_'.$i;
            $constantname = 'SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
            if ( ($data[$fieldname.'_select'] == constant($constantname)) && (!$data[$fieldname.'_text']) ) {
                $errors[$fieldname.'_group'] = get_string('contenttext_err', 'surveyfield_autofill');
            } else {
                if ($data[$fieldname.'_select'] == constant($constantname)) {
                    $uniontext .= $data[$fieldname.'_text'];
                } else {
                    if (!empty($data[$fieldname.'_select'])) {
                        $uniontext .= $data[$fieldname.'_select'];
                    }
                }
            }
        }

        if (!$errors && !$uniontext) {
            $fieldname = 'element_1';
            $errors[$fieldname.'_group'] = get_string('contentselect_err', 'surveyfield_autofill');
        }

        return $errors;
    }
}