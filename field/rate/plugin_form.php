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
require_once($CFG->dirroot.'/mod/survey/field/rate/lib.php');

class survey_pluginform extends surveyitem_baseform {

    function definition() {
        // -------------------------------------------------------------------------------
        // acquisisco i valori per pre-definire i campi della form
        $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // comincio con la "sezione" comune della form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // newitem::style
        // ----------------------------------------
        $fieldname = 'style';
        $options = array(SURVEYFIELD_RATE_USERADIO => get_string('useradio', 'surveyfield_rate'),
                         SURVEYFIELD_RATE_USESELECT => get_string('usemenu', 'surveyfield_rate')
                   );
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_rate'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, 0);

        // ----------------------------------------
        // newitem::options
        // ----------------------------------------
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_rate'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->addRule($fieldname, get_string($fieldname.'_err', 'surveyfield_rate'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::rates
        // ----------------------------------------
        $fieldname = 'rates';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_rate'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->addRule($fieldname, get_string($fieldname.'_err', 'surveyfield_rate'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_rate'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $elementgroup[] = $mform->createElement('textarea', $fieldname, '', array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_rate'), array(' ', ' ', '<br />'), false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_rate');
        $mform->setDefault('defaultoption', SURVEY_INVITATIONDEFAULT);
        $mform->setType($fieldname, PARAM_RAW);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);
        if (is_null($item->defaultvalue)) {
            $mform->setDefault($fieldname, $item->item_generate_standard_default());
        }

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::forcedifferentrates
        // ----------------------------------------
        $fieldname = 'forcedifferentrates';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyfield_rate'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->setType($fieldname, PARAM_TEXT);
        $mform->setDefault($fieldname, 0);

        $this->add_item_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // clean inputs
        $clean_options = survey_textarea_to_array($data['options']);
        $clean_rates = survey_textarea_to_array($data['rates']);
        $clean_defaultvalue = isset($data['defaultvalue']) ? survey_textarea_to_array($data['defaultvalue']) : '';

        // $clean_defaultvalue = isset($data['defaultvalue']) ? survey_textarea_to_array($data[$afield]) : '';
        // ora ho
        // clean_options
        // clean_rates
        // clean_defaultvalue

        // SE è richiesto un default
        if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
            // il numero dei default deve essere pari al numero delle opzioni
            if (count($clean_defaultvalue) != count($clean_options)) {
                $errors['defaultvalue_group'] = get_string('defaults_wrongdefaultsnumber', 'surveyfield_rate');
            }

            foreach ($clean_rates as $rate) {
                if (strpos($rate, SURVEY_VALUELABELSEPARATOR) === false) {
                    $values[] = $rate;
                } else {
                    $pair = explode(SURVEY_VALUELABELSEPARATOR, $rate);
                    $values[] = $pair[0];
                }
            }

            // values in the default field must all be hold among rates ($values)
            foreach ($clean_defaultvalue as $default) {
                if (!in_array($default, $values)) {
                    $errors['defaultvalue_group'] = get_string('default_notamongrates', 'surveyfield_rate', $default);
                    break;
                }
            }
        }

        // if (default == noanswer) but item is required => error
        if ( ($data['defaultoption'] == SURVEY_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'survey');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'survey', $a);
        }

        // SE è richiesto forcedifferentrates
        // il numero dei numero delle opzioni DEVE essere >= al numero degli elementi da valutare
        if (isset($data['forcedifferentrates'])) {
            // if I claim for different rates, I must provide a sufficient number of rates
            if (count($clean_options) > count($clean_rates)) {
                $errors['rates'] = get_string('notenoughrares', 'surveyfield_rate');
            }

            if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
                // if I claim for different rates, I have to respect the constraint in the default
                if (count($clean_defaultvalue) > count(array_unique($clean_defaultvalue))) {
                    $errors['defaultvalue_group'] = get_string('deafultsnotunique', 'surveyfield_rate');
                }
            }
        }

        return $errors;
    }
}