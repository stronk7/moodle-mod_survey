<?php

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/lib/formslib.php');

class survey_exportplugin extends moodleform {

    function definition() {

        $mform = $this->_form;

        //----------------------------------------
        // pluginbuild::surveyid
        //----------------------------------------
        $fieldname = 'surveyid';
        $mform->addElement('hidden', $fieldname, 0);

        //----------------------------------------
        // pluginbuild::pluginname
        //----------------------------------------
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
