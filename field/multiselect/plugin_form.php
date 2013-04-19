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
require_once($CFG->dirroot.'/mod/survey/field/multiselect/lib.php');

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
        // newitem::options_sid
        // ----------------------------------------
        $fieldname = 'options_sid';
        $mform->addElement('hidden', $fieldname, '');
        $mform->setType($fieldname, PARAM_RAW);

        // ----------------------------------------
        // newitem::options
        // ----------------------------------------
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_multiselect'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_multiselect');
        $mform->addRule($fieldname, get_string($fieldname.'_err', 'surveyfield_multiselect'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_multiselect'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_multiselect');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::shownrows
        // ----------------------------------------
        $fieldname = 'shownrows';
        $options = array_combine(range(3, 12), range(3, 12));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_multiselect'), $options);
        $mform->setDefault($fieldname, 4);
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}