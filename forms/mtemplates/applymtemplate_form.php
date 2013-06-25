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
        // applymtemplate::mastertemplate
        // ----------------------------------------
        $fieldname = 'mastertemplate';
        if (count($mtemplates)) {
            $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $mtemplates);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        }

        // ----------------------------------------
        // applymtemplate::otheritems
        // ----------------------------------------
        $fieldname = 'actionoverother';
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('ignoreitems', 'survey'), SURVEY_IGNOREITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('hideitems', 'survey'), SURVEY_HIDEITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('deleteitems', 'survey'), SURVEY_DELETEITEMS);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'survey'), '<br />', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'survey');
        $mform->setDefault($fieldname, SURVEY_IGNOREITEMS);

        // -------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(true, get_string('continue'));
    }
}