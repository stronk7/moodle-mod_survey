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

class survey_applymtemplateform extends moodleform {

    public function definition() {

        $mform = $this->_form;
        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;
        $inline = $this->_customdata->inline;

        if ($mtemplatepluginlist = get_plugin_list('surveytemplate')) {
            $mtemplates = array();

            foreach ($mtemplatepluginlist as $mtemplatename => $mtemplatepath) {
                if (!get_config('surveytemplate_'.$mtemplatename, 'disabled')) {
                    $mtemplates[$mtemplatename] = get_string('pluginname', 'surveytemplate_'.$mtemplatename);
                }
            }
            asort($mtemplates);
        }

        // ----------------------------------------
        // applymtemplate::mastertemplate
        // ----------------------------------------
        $fieldname = 'mastertemplate';
        if (count($mtemplates)) {
            if ($inline) {
                $elementgroup = array();
                $elementgroup[] = $mform->createElement('select', $fieldname, get_string($fieldname, 'survey'), $mtemplates);
                $elementgroup[] = $mform->createElement('submit', $fieldname.'_button', get_string('create'));
                $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'survey'), array(' '), false);
                $mform->addHelpButton($fieldname.'_group', $fieldname, 'survey');
            } else {
                $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $mtemplates);
                $mform->addHelpButton($fieldname, $fieldname, 'survey');
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');

                // ----------------------------------------
                // buttons
                $this->add_action_buttons(true, get_string('continue'));
            }
        } else {
            $mform->addElement('static', 'nomtemplates', get_string('mastertemplate', 'survey'), get_string('nomtemplates_message', 'survey'));
            $mform->addHelpButton('nomtemplates', 'nomtemplates', 'survey');
        }

    }

    public function validation($data, $files) {
        global $USER, $CFG;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;
        $mtemplateman = $this->_customdata->mtemplateman;
        $inline = $this->_customdata->inline;

        $errors = parent::validation($data, $files);

        $templatename = $data['mastertemplate'];
        $templatepath = $CFG->dirroot.'/mod/survey/template/'.$templatename.'/template.xml';
        $xml = file_get_contents($templatepath);
        // $xml = @new SimpleXMLElement($templatecontent);
        if (!$mtemplateman->validate_xml($xml)) {
            $errors['mastertemplate'] = get_string('invalidtemplate', 'survey', $templatename);
            return $errors;
        }

        return $errors;
    }
}