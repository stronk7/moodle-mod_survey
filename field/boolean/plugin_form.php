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
require_once($CFG->dirroot.'/mod/survey/field/boolean/lib.php');

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

        // ----------------------------------------
        // newitem::style
        // ----------------------------------------
        $fieldname = 'style';
        $options = array(SURVEYFIELD_BOOLEAN_USERADIOH => get_string('useradioh', 'surveyfield_boolean'),
                         SURVEYFIELD_BOOLEAN_USERADIOV => get_string('useradiov', 'surveyfield_boolean'),
                         SURVEYFIELD_BOOLEAN_USESELECT => get_string('usemenu', 'surveyfield_boolean')
                   );
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_boolean'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_boolean');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, SURVEYFIELD_BOOLEAN_USERADIOH);

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        // I am not allowed to use '', '1', '2' because the database field defaultvalue is a number so '' == '0'
        // so I will correct this input at save time in item_save($record)
        $options = array('1' => get_string('yes'), '0' => get_string('no'));
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_boolean'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $elementgroup[] = $mform->createElement('select', $fieldname, '', $options);
        $separator = array(' ', ' ', '<br />');
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_boolean'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_boolean');
        $mform->setDefault('defaultoption', SURVEY_INVITATIONDEFAULT);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);
        if (is_null($item->{$fieldname}) || ($item->{$fieldname} == SURVEY_INVITATIONDEFAULT)) {
            $mform->setDefault($fieldname, '1');
        }

        $this->add_item_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // "noanswer" default option is not allowed when the item is mandatory

        if ( ($data['defaultoption'] == SURVEY_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'survey');
            $errors['defaultvalue'] = get_string('notalloweddefault', 'survey', $a);
        }

        return $errors;
    }
}