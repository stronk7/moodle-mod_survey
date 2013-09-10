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
require_once($CFG->dirroot.'/mod/survey/field/rate/lib.php');

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
        // newitem::style
        // ----------------------------------------
        $fieldname = 'style';
        $options = array(SURVEYFIELD_RATE_USERADIO => get_string('useradio', 'surveyfield_rate'),
                         SURVEYFIELD_RATE_USESELECT => get_string('usemenu', 'surveyfield_rate')
                   );
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_rate'), $options);
        $mform->setDefault($fieldname, '0');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::options
        // ----------------------------------------
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_rate'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::rates
        // ----------------------------------------
        $fieldname = 'rates';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_rate'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyfield_rate'), SURVEY_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'survey'), SURVEY_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'survey'), SURVEY_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyfield_rate'), ' ', false);
        $mform->setDefault($fieldname, SURVEY_INVITATIONDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyfield_rate');

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('textarea', $fieldname, '', array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->setType($fieldname, PARAM_RAW);
        $mform->disabledIf($fieldname, 'defaultoption', 'neq', SURVEY_CUSTOMDEFAULT);

        // ----------------------------------------
        // newitem::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = array(SURVEYFIELD_RATE_RETURNVALUES => get_string('returnvalues', 'surveyfield_rate'),
                         SURVEYFIELD_RATE_RETURNLABELS => get_string('returnlabels', 'surveyfield_rate'),
                         SURVEYFIELD_RATE_RETURNPOSITION => get_string('returnposition', 'surveyfield_rate'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_rate'), $options);
        $mform->setDefault($fieldname, SURVEYFIELD_RATE_RETURNVALUES);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->setType($fieldname, PARAM_INT);

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::differentrates
        // ----------------------------------------
        $fieldname = 'differentrates';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyfield_rate'));
        $mform->setDefault($fieldname, '0');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_rate');
        $mform->setType($fieldname, PARAM_TEXT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // -------------------------------------------------------------------------------
        // $item = $this->_customdata->item;

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

        // if a default is required
        if ($data['defaultoption'] == SURVEY_CUSTOMDEFAULT) {
            // il numero dei default deve essere pari al numero delle opzioni
            if (count($clean_defaultvalue) != count($clean_options)) {
                $errors['defaultvalue_group'] = get_string('defaults_wrongdefaultsnumber', 'surveyfield_rate');
            }

            $values = array();
            $labels = array();
            foreach ($clean_rates as $rate) {
                if (strpos($rate, SURVEY_VALUELABELSEPARATOR) === false) {
                    $values[] = $rate;
                    $labels[] = $rate;
                } else {
                    $pair = explode(SURVEY_VALUELABELSEPARATOR, $rate);
                    $values[] = $pair[0];
                    $labels[] = $pair[1];
                }
            }

            // values in the default field must all be hold among rates ($labels)
            foreach ($clean_defaultvalue as $default) {
                if (!in_array($default, $labels)) {
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

        // if differentrates was requested
        // count($clean_rates) HAS TO be >= count($clean_rates)
        if (isset($data['differentrates'])) {
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