<?php

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/lib/formslib.php');

class survey_presetimportform extends moodleform {

    function definition() {
        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;

        //----------------------------------------
        // presetimport::importfile
        //----------------------------------------
        $fieldname = 'importfile';
        $preset_options = survey_get_preset_options();
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'survey'), null, $preset_options);

        //----------------------------------------
        // presetimport::overwrite
        //----------------------------------------
        $fieldname = 'overwrite';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        //----------------------------------------
        // presetimport::sharinglevel
        //----------------------------------------
        $fieldname = 'sharinglevel';
        $options = array();

        $options = survey_get_sharinglevel_options($cmid, $survey);

        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');
        $mform->setDefault($fieldname, CONTEXT_SYSTEM);

        $this->add_action_buttons(false, get_string('builplugin', 'survey'));
    }

    function validation($data, $files) {

        $errors = parent::validation($data, $files);

        // TODO: checks for uniqueness of pluginname

        return $errors;
    }
}
