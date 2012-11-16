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

class survey_addsetform extends moodleform {

    function definition() {

        $mform = $this->_form;
        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;

        if ($surveypluginlist = get_plugin_list('surveytemplate')) {
            $surveyplugins = array();

            foreach ($surveypluginlist as $surveyname => $surveypath) {
                $surveyplugins['SURVEY_'.$surveyname] = get_string('pluginname', 'surveytemplate_'.$surveyname);
            }
            asort($surveyplugins);
        }

        $options = survey_get_sharinglevel_options($cmid, $survey);

        $presets = new stdClass();
        $presetsfiles = array();
        foreach($options as $sharinglevel => $v) {
            $parts = explode('_', $sharinglevel);
            $contextlevel = $parts[0];

            $contextid = survey_get_contextid_from_sharinglevel($sharinglevel);
            $contextstring = survey_get_contextstring_from_sharinglevel($contextlevel);
            $presets->{$contextstring} = survey_get_available_presets($contextid, 'mod_survey');
        }

        foreach($presets as $contextstring => $contextfiles) {
            $contextlabel = get_string($contextstring, 'survey');
            foreach($contextfiles as $xmlfile) {
                $itemsetname = $xmlfile->get_filename();
                $presetsfiles['PRESET_'.$xmlfile->get_id()] = '('.$contextlabel.') '.$itemsetname;
            }
        }
        asort($presetsfiles);

        // ----------------------------------------
        // addset::itemset
        // ----------------------------------------
        $fieldname = 'itemset';
        if (count($surveyplugins)) {
            if (count($presetsfiles)) {
                $itemsetlist = array('' => array(get_string('notanyset', 'survey')),
                                     get_string('modulepresets', 'survey') => $surveyplugins,
                                     get_string('userpresets', 'survey') => $presetsfiles);
                $mform->addElement('selectgroups', $fieldname, get_string($fieldname, 'survey'), $itemsetlist);
            } else {
                $surveyplugins = array_merge(array(get_string('notanyset', 'survey')), $surveyplugins);
                $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $surveyplugins);
            }
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        } else {
            if (count($presetsfiles)) {
                $presetsfiles = array_merge(array(get_string('notanyset', 'survey')), $presetsfiles);
                $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $presetsfiles);
            }
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        }

        // ----------------------------------------
        // addset::otheritems
        // ----------------------------------------
        $fieldname = 'actionoverother';
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('ignoreitems', 'survey'), SURVEY_IGNOREITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('draftitems', 'survey'), SURVEY_DRAFTITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('deleteitems', 'survey'), SURVEY_DELETEITEMS);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'survey'), '<br />', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'survey');
        $mform->setDefault($fieldname, SURVEY_IGNOREITEMS);

        // -------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('continue'));
    }
}