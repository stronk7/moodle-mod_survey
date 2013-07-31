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

class survey_applyutemplateform extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;
        $utemplate_manager = $this->_customdata->utemplate_manager;

        $options = $utemplate_manager->get_sharinglevel_options();

        $templates = new stdClass();
        $templatesfiles = array();
        foreach ($options as $sharinglevel => $v) {
            $parts = explode('_', $sharinglevel);
            $contextlevel = $parts[0];
            $contextid = $utemplate_manager->get_contextid_from_sharinglevel($sharinglevel);
            $contextstring = $utemplate_manager->get_contextstring_from_sharinglevel($contextlevel);
            $templates->{$contextstring} = $utemplate_manager->get_available_templates($contextid);
        }

        foreach ($templates as $contextstring => $contextfiles) {
            $contextlabel = get_string($contextstring, 'survey');
            foreach ($contextfiles as $xmlfile) {
                $itemsetname = $xmlfile->get_filename();
                $templatesfiles[$xmlfile->get_id()] = '('.$contextlabel.') '.$itemsetname;
            }
        }
        asort($templatesfiles);

        // ----------------------------------------
        // applyutemplate::usertemplate
        // ----------------------------------------
        $fieldname = 'usertemplate';
        $templatesfiles = array(get_string('notanyset', 'survey')) + $templatesfiles;
        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $templatesfiles);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');

        // ----------------------------------------
        // applyutemplate::otheritems
        // ----------------------------------------
        $fieldname = 'actionoverother';
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('ignoreitems', 'survey'), SURVEY_IGNOREITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('hideitems', 'survey'), SURVEY_HIDEITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('deleteallitems', 'survey'), SURVEY_DELETEALLITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('deletevisibleitems', 'survey'), SURVEY_DELETEVISIBLEITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('deletehiddenitems', 'survey'), SURVEY_DELETEHIDDENITEMS);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'survey'), '<br />', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'survey');
        $mform->setDefault($fieldname, SURVEY_IGNOREITEMS);

        // -------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(true, get_string('continue'));
    }
}