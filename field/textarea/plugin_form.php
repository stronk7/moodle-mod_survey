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
require_once($CFG->dirroot.'/mod/survey/field/textarea/lib.php');

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
        // newitem::useeditor
        // ----------------------------------------
        $fieldname = 'useeditor';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyfield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_textarea');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::arearows
        // ----------------------------------------
        $fieldname = 'arearows';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_textarea'));
        $mform->setDefault($fieldname, SURVEYFIELD_TEXTAREA_DEFAULTROWS);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::areacols
        // ----------------------------------------
        $fieldname = 'areacols';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, SURVEYFIELD_TEXTAREA_DEFAULTCOLS);

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::minlength
        // ----------------------------------------
        $fieldname = 'minlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, 0);

        // ----------------------------------------
        // newitem::maxlength
        // ----------------------------------------
        $fieldname = 'maxlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_textarea');
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        if (isset($data['maxlength'])) {
            if ($data['maxlength'] <= $data['minlength']) {
                $errors['maxlength'] = get_string('maxlengthlowerthanminlength', 'surveyfield_textarea');
            }
        }

        return $errors;
    }
}