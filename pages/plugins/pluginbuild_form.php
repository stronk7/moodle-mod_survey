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


/**
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

class survey_exportplugin extends moodleform {

    function definition() {

        $mform = $this->_form;

        // ----------------------------------------
        // pluginbuild::surveyid
        // ----------------------------------------
        $fieldname = 'surveyid';
        $mform->addElement('hidden', $fieldname, 0);

        // ----------------------------------------
        // pluginbuild::pluginname
        // ----------------------------------------
        $fieldname = 'pluginname';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_FILE); // this word is going to be a file name

        $this->add_action_buttons(false, get_string('builplugin', 'survey'));
    }

    function validation($data, $files) {

        $errors = parent::validation($data, $files);

        // TODO: checks for uniqueness of pluginname


        return $errors;
    }



}
