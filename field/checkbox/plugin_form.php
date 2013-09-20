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
require_once($CFG->dirroot.'/mod/survey/field/checkbox/lib.php');

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
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::labelother
        // ----------------------------------------
        $fieldname = 'labelother';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), array('maxlength' => '64', 'size' => '50'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::adjustment
        // ----------------------------------------
        $fieldname = 'adjustment';
        $options = array(SURVEY_HORIZONTAL => get_string('horizontal', 'surveyfield_checkbox'), SURVEY_VERTICAL => get_string('vertical', 'surveyfield_checkbox'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), $options);
        $mform->setDefault($fieldname, SURVEY_VERTICAL);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = array(SURVEYFIELD_CHECKBOX_RETURNVALUES => get_string('returnvalues', 'surveyfield_checkbox'),
                         SURVEYFIELD_CHECKBOX_RETURNLABELS => get_string('returnlabels', 'surveyfield_checkbox'),
                         SURVEYFIELD_CHECKBOX_RETURNPOSITION => get_string('returnposition', 'surveyfield_checkbox'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), $options);
        $mform->setDefault($fieldname, SURVEYFIELD_CHECKBOX_RETURNVALUES);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        // clean inputs
        $cleanoptions = survey_textarea_to_array($data['options']);
        $cleandefaultvalue = survey_textarea_to_array($data['defaultvalue']);
        $cleanlabelother = trim($data['labelother']);

        // build $value and $label arrays starting from $cleanoptions and $cleanlabelother
        $values = array();
        $labels = array();

        foreach ($cleanoptions as $option) {
            if (strpos($option, SURVEY_VALUELABELSEPARATOR) === false) {
                $values[] = trim($option);
                $labels[] = trim($option);
            } else {
                $pair = explode(SURVEY_VALUELABELSEPARATOR, $option);
                $values[] = $pair[0];
                $labels[] = $pair[1];
            }
        }
        if (!empty($cleanlabelother)) {
            if (strpos($cleanlabelother, SURVEY_OTHERSEPARATOR) === false) {
                $values[] = $cleanlabelother;
                $labels[] = $cleanlabelother;
            } else {
                $pair = explode(SURVEY_OTHERSEPARATOR, $cleanlabelother);
                $values[] = $pair[1];
                $labels[] = $pair[0];
            }
        }

        // -----------------------------
        // first check
        // each item of default has to be among options OR has to be == to otherlabel value
        // this also verify (helped by the second check) that the number of default is not gretr than the number of options
        // -----------------------------
        if (!empty($data['defaultvalue'])) {
            foreach ($cleandefaultvalue as $default) {
                if (!in_array($default, $labels)) {
                    $errors['defaultvalue'] = get_string('defaultvalue_err', 'surveyfield_checkbox', $default);
                    break;
                }
            }
        }

        // -----------------------------
        // second check
        // each single option item has to be unique
        // each single default item has to be unique
        // -----------------------------
        $arrayunique = array_unique($cleanoptions);
        if (count($cleanoptions) != count($arrayunique)) {
            $errors['options'] = get_string('optionsduplicated_err', 'surveyfield_checkbox', $default);
        }
        $arrayunique = array_unique($cleandefaultvalue);
        if (count($cleandefaultvalue) != count($arrayunique)) {
            $errors['defaultvalue'] = get_string('defaultvalue_err', 'surveyfield_checkbox', $default);
        }

        // -----------------------------
        // third check
        // SURVEY_DBMULTIVALUESEPARATOR can not be contained into values
        // -----------------------------
        foreach ($values as $value) {
            if (strpos($value, SURVEY_DBMULTIVALUESEPARATOR) !== false) {
                if (!empty($cleanlabelother) && ($value == end($values))) { // if $value is the last
                    $errors['labelother'] = get_string('optionswithseparator_err', 'surveyfield_checkbox', SURVEY_DBMULTIVALUESEPARATOR);
                } else {
                    $errors['options'] = get_string('optionswithseparator_err', 'surveyfield_checkbox', SURVEY_DBMULTIVALUESEPARATOR);
                }
                break;
            }
        }

        // print_object($errors);
        // die;
        return $errors;
    }
}