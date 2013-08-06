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

        if ($mtemplatepluginlist = get_plugin_list('surveytemplate')) {
            $mtemplates = array();

            foreach ($mtemplatepluginlist as $mtemplatename => $mtemplatepath) {
                $mtemplates[$mtemplatename] = get_string('pluginname', 'surveytemplate_'.$mtemplatename);
            }
            asort($mtemplates);
        }

        // ----------------------------------------
        // applymtemplate::otheritems
        // ----------------------------------------
        $fieldname = 'actionoverother';
        $mform->addElement('hidden', $fieldname, SURVEY_DELETEALLITEMS);
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // applymtemplate::mastertemplate
        // ----------------------------------------
        $fieldname = 'mastertemplate';
        if (count($mtemplates)) {
            $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $mtemplates);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        }

        // -------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(true, get_string('continue'));
    }

    public function validation($data, $files) {
        global $USER;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;
        $utemplate_manager = $this->_customdata->utemplate_manager;

        $errors = parent::validation($data, $files);

        $templatename = $this->mtemplatename;
        $template_path = $CFG->dirroot.'/mod/survey/template/'.$templatename.'/template.xml';
        $templatecontent = file_get_contents($template_path);
        $xml = @new SimpleXMLElement($templatecontent);
        if (!$utemplate_manager->xml_check($xml)) {
            $errors['mastertemplate'] = get_string('invalidtemplate', 'survey', $templatename);
            return $errors;
        }

        return $errors;
    }
}