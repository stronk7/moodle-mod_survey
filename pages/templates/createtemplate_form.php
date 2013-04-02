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

class survey_templatebuildform extends moodleform {

    function definition() {
        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;

        // ----------------------------------------
        // templatebuild::surveyid
        // ----------------------------------------
        $fieldname = 'surveyid';
        $mform->addElement('hidden', $fieldname, 0);
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // templatebuild::templatename
        // ----------------------------------------
        $fieldname = 'templatename';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_FILE); // templatename is going to be a file name

        // ----------------------------------------
        // templatebuild::overwrite
        // ----------------------------------------
        $fieldname = 'overwrite';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // ----------------------------------------
        // templatebuild::sharinglevel
        // ----------------------------------------
        $fieldname = 'sharinglevel';
        $options = array();

        $options = survey_get_sharinglevel_options($cmid, $survey);

        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');
        $mform->setDefault($fieldname, CONTEXT_SYSTEM);

        // -------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('continue'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!isset($data['overwrite'])) {
            // get all template files
            $contextid = survey_get_contextid_from_sharinglevel($data['sharinglevel']);
            $componentfiles = survey_get_available_templates($contextid);

            foreach ($componentfiles as $xmlfile) {
                $comparename = str_replace(' ', '_', $data['templatename']).'.xml';
                if ($comparename == $xmlfile->get_filename()) {
                    if (isset($data['overwrite'])) {
                        $xmlfile->delete();
                    } else {
                        $errors['templatename'] = get_string('enteruniquename', 'survey', $data['templatename']);
                    }
                    break;
                }
            }
        }

        return $errors;
    }
}