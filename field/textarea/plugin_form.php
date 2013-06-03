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

class survey_pluginform extends surveyitem_baseform {

    function definition() {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // comincio con la "sezione" comune della form
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
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_textarea');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, 12);

        // ----------------------------------------
        // newitem::areacols
        // ----------------------------------------
        $fieldname = 'areacols';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_textarea');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, 60);

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
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, 0);

        // ----------------------------------------
        // newitem::maxlength
        // ----------------------------------------
        $fieldname = 'maxlength';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('text', $fieldname, '');
        $elementgroup[] = $mform->createElement('checkbox', $fieldname.'_check', '', get_string('free', 'survey'));
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_textarea'), ' ', false);
        $mform->disabledIf($fieldname.'_group', $fieldname.'_check', 'checked');
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_textarea');
        $mform->setType($fieldname, PARAM_INT);
        // $mform->setDefault($fieldname.'_check', 1);

        $this->add_item_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}