<?php

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/lib/formslib.php');

class survey_presetimportform extends moodleform {

    function definition() {
        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;

        // ----------------------------------------
        // presetimport::importfile
        // ----------------------------------------
        $fieldname = 'importfile';
        $preset_options = survey_get_preset_options();
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'survey'), null, $preset_options);

        // ----------------------------------------
        // presetimport::overwrite
        // ----------------------------------------
        $fieldname = 'overwrite';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // ----------------------------------------
        // presetimport::sharinglevel
        // ----------------------------------------
        $fieldname = 'sharinglevel';
        $options = array();

        $options = survey_get_sharinglevel_options($cmid, $survey);

        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');
        $mform->setDefault($fieldname, CONTEXT_SYSTEM);

        // -------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('presetimport', 'survey'));
    }

    function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $draftitemid = file_get_submitted_draft_itemid('importfile_filemanager');
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, '', false);

        $uploadedfiles = array();
        foreach ($draftfiles as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $uploadedfiles[] = $file->get_filename();
        }

        // get all preset files in the specified context
        $contextid = survey_get_contextid_from_sharinglevel($data['sharinglevel']);
        $componentfiles = survey_get_available_presets($contextid, 'mod_survey');

        // TODO: there is a bug. Uploading a second file, the first in the same area get deleted. I can not understand the reason.
        foreach ($componentfiles as $xmlfile) {
            $filename = $xmlfile->get_filename();
            if (in_array($filename, $uploadedfiles)) {
                if (isset($data['overwrite'])) {
                    $xmlfile->delete();
                } else {
                    $errors['importfile_filemanager'] = get_string('enteruniquename', 'survey', $filename);
                    break;
                }
            }
        }

        return $errors;
    }
}
