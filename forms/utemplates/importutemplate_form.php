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

class survey_importutemplateform extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;
        $utemplateman = $this->_customdata->utemplateman;
        $filemanageroptions = $this->_customdata->filemanager_options;

        // ----------------------------------------
        // templateimport::importfile
        // ----------------------------------------
        $fieldname = 'importfile';
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'survey'), null, $filemanageroptions);

        // ----------------------------------------
        // templateimport::overwrite
        // ----------------------------------------
        $fieldname = 'overwrite';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // ----------------------------------------
        // templateimport::sharinglevel
        // ----------------------------------------
        $fieldname = 'sharinglevel';
        $options = array();

        $options = $utemplateman->get_sharinglevel_options($cmid, $survey);

        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');
        $mform->setDefault($fieldname, CONTEXT_SYSTEM);

        // ----------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('templateimport', 'survey'));
    }

    public function validation($data, $files) {
        global $USER;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;
        $utemplateman = $this->_customdata->utemplateman;

        $errors = parent::validation($data, $files);

        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $draftitemid = file_get_submitted_draft_itemid('importfile_filemanager');
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, '', false);

        if (!count($draftfiles)) {
            $errors['importfile_filemanager'] = get_string('missingfile', 'survey');
        }

        $importedfiles = array();
        foreach ($draftfiles as $file) {
            $xmlfilename = $file->get_filename();
            $importedfiles[] = $xmlfilename;
            try {
                $xmlfileid = $file->get_id();
                $xml = $utemplateman->get_utemplate_content($xmlfileid);
                // $xml = @new SimpleXMLElement($templatecontent);
                if (!$utemplateman->validate_xml($xml)) {
                    $errors['importfile_filemanager'] = get_string('invalidtemplate', 'survey', $xmlfilename);
                    return $errors;
                }
            } catch (Exception $e) {
                $errors['importfile_filemanager'] = get_string('invalidtemplate', 'survey', $xmlfilename);
                return $errors;
            }
        }

        // $debug = true; if you want to always stop to see where the xml template is buggy
        $debug = false;
        if ($debug) {
            $errors['importfile_filemanager'] = 'All is fine here!';
            return $errors;
        }

        // get all template files in the specified context
        $contextid = $utemplateman->get_contextid_from_sharinglevel($data['sharinglevel']);
        $componentfiles = $utemplateman->get_available_templates($contextid);

        foreach ($componentfiles as $xmlfile) {
            $filename = $xmlfile->get_filename();
            if (in_array($filename, $importedfiles)) {
                if (isset($data['overwrite'])) {
                    $xmlfile->delete();
                } else {
                    $a = new stdClass();
                    $a->filename = $filename;
                    $a->overwrite = get_string('overwrite', 'survey');
                    $errors['importfile_filemanager'] = get_string('enteruniquename', 'survey', $a);
                    break;
                }
            }
        }

        return $errors;
    }
}
