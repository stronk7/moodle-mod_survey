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

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/survey/itembase_form.php');
require_once($CFG->dirroot.'/mod/survey/field/character/lib.php');

class survey_pluginform extends surveyitem_baseform {

    function definition() {
        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // start with the common section of the form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_character'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_character');
        $mform->setDefault($fieldname, '');
        $mform->setType($fieldname, PARAM_RAW);

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::pattern
        // ----------------------------------------
        $fieldname = 'pattern';
        $options = array();
        $options[SURVEYFIELD_CHARACTER_EMAILPATTERN] = get_string('mail', 'surveyfield_character');
        $options[SURVEYFIELD_CHARACTER_URLPATTERN] = get_string('url', 'surveyfield_character');
        $options[SURVEYFIELD_CHARACTER_CUSTOMPATTERN] = get_string('custompattern', 'surveyfield_character');
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname, '', $options);
        $elementgroup[] = $mform->createElement('text', $fieldname.'_text', '');
        $elementgroup[] = $mform->createElement('checkbox', $fieldname.'_check', '', get_string('free', 'survey'));
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_character'), ' ', false);
        $mform->disabledIf($fieldname.'_text', $fieldname, 'neq', SURVEYFIELD_CHARACTER_CUSTOMPATTERN);
        $mform->disabledIf($fieldname.'_group', $fieldname.'_check', 'checked');
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_character');
        $mform->setType($fieldname, PARAM_RAW);
        $mform->setType($fieldname.'_text', PARAM_ALPHANUMEXT);
        $mform->setDefault($fieldname.'_check', 'checked');

        // ----------------------------------------
        // newitem::minlength
        // ----------------------------------------
        $fieldname = 'minlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_character'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_character');
        $mform->disabledIf($fieldname, 'pattern_check', 'notchecked');
        $mform->setDefault($fieldname, 0);
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::maxlength
        // ----------------------------------------
        $fieldname = 'maxlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_character'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_character');
        $mform->disabledIf($fieldname, 'pattern_check', 'notchecked');
        $mform->setDefault($fieldname, 255);
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Minimum characters <= Maximum characters
        if ($data['minlength'] > $data['maxlength']) {
            $errors['minlength'] = get_string('ierr_mingtmax', 'surveyfield_character');
            $errors['maxlength'] = get_string('ierr_maxltmin', 'surveyfield_character');
        }

        // Minimum characters > 0
        if ($data['minlength'] < 0) {
            $errors['minlength'] = get_string('ierr_minexceeds', 'surveyfield_character');
        }

        // Maximum characters < 256
        if ($data['maxlength'] > 255) {
            $errors['maxlength'] = get_string('ierr_maxexceeds', 'surveyfield_character');
        }

        if (!empty($data['defaultvalue'])) {
            // Maximum characters > length of default
            $defaultvalue_length = strlen($data['defaultvalue']);
            if ($defaultvalue_length > $data['maxlength']) {
                $errors['defaultvalue'] = get_string('ierr_toolongdefault', 'surveyfield_character');
            }

            // Minimum characters < length of default
            if ($defaultvalue_length < $data['minlength']) {
                $errors['defaultvalue'] = get_string('ierr_tooshortdefault', 'surveyfield_character');
            }

            // default has to match the text pattern
            if (!isset($data['pattern_check'])) {
                switch ($data['pattern']) {
                    case SURVEYFIELD_CHARACTER_EMAILPATTERN:
                        if (!validate_email($data['defaultvalue'])) {
                            $errors['defaultvalue'] = get_string('ierr_defaultisnotemail', 'surveyfield_character');
                        }
                        break;
                    case SURVEYFIELD_CHARACTER_URLPATTERN:
                        if (!survey_character_is_valid_url($data['defaultvalue'])) {
                            $errors['defaultvalue'] = get_string('ierr_defaultisnoturl', 'surveyfield_character');
                        }
                        break;
                    case SURVEYFIELD_CHARACTER_CUSTOMPATTERN:
                        $patternlength = strlen($data['pattern_text']);
                        if ($defaultvalue_length != $patternlength) {
                            $errors['defaultvalue'] = get_string('ierr_defaultbadlength', 'surveyfield_character', $patternlength);
                        } else if (!survey_character_text_match_pattern($data['defaultvalue'], $data['pattern_text'])) {
                            $errors['defaultvalue'] = get_string('ierr_nopatternmatch', 'surveyfield_character');
                        }
                        break;
                    default:
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $data[\'pattern\'] = '.$data['pattern']);
                }
            }
        }

        // if pattern == SURVEYFIELD_CHARACTER_CUSTOMPATTERN, its length has to fall between minlength and maxlength
        if ( (!isset($data['pattern_check'])) && ($data['pattern'] == SURVEYFIELD_CHARACTER_CUSTOMPATTERN) ) {
            $patternlength = strlen($data['pattern_text']);
            // pattern can not be empty
            if (!$patternlength) {
                $errors['pattern_group'] = get_string('ierr_patternisempty', 'surveyfield_character');
            }
            // pattern can be done only from A, a, * and 0
            if (preg_match_all('~[^Aa\*0]~', $data['pattern_text'], $matches)) {
                $denied = array_unique($matches[0]);
                $a = '"'.implode('", "', $denied).'"';
                $errors['pattern_group'] = get_string('ierr_extracharfound', 'surveyfield_character', $a);
            }
        }

        return $errors;
    }
}