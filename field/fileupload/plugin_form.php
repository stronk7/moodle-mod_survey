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
require_once($CFG->dirroot.'/mod/survey/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/survey/field/fileupload/lib.php');

class survey_pluginform extends mod_survey_itembaseform {

    public function definition() {
        // -------------------------------------------------------------------------------
        // $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // I start with the common "section" form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // newitem::maxfiles
        // ----------------------------------------
        $fieldname = 'maxfiles';
        $options = array_combine(range(1, 5), range(1, 5));
        $options[EDITOR_UNLIMITED_FILES] = get_string('unlimited', 'survey');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_fileupload'), $options);
        $mform->setDefault($fieldname, '1048576');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_fileupload');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::maxbytes
        // ----------------------------------------
        $fieldname = 'maxbytes';
        $options = get_max_upload_sizes();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_fileupload'), $options);
        $mform->setDefault($fieldname, '1048576');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_fileupload');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::filetypes
        // ----------------------------------------
        $fieldname = 'filetypes';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_fileupload'));
        $mform->setDefault($fieldname, '*');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_fileupload');
        $mform->setType($fieldname, PARAM_TEXT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        $filetypes = array_map('trim', explode(',', $data['filetypes']));
        foreach ($filetypes as $filetype) {
            if (!$filetype) {
                $errors['filetypes'] = get_string('extensionisempty', 'surveyfield_fileupload');
                break;
            }
            if ($filetype[0] != '.') {
                $errors['filetypes'] = get_string('extensionmissingdot', 'surveyfield_fileupload');
                break;
            }
            $testtype = str_replace('.', '', $filetype);
            if (strlen($testtype) != strlen($filetype) - 1) {
                $errors['filetypes'] = get_string('extensiononlyonedot', 'surveyfield_fileupload');
                break;
            }
        }
        return $errors;
    }
}