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

class survey_chooseitemform extends moodleform {

    function definition() {
        global $DB;

        $mform = $this->_form;

        $survey = $this->_customdata->survey;

        // only fields
        // no matter for the page
        // elenco dei campi che l'utente vuole vedere nel file esportato
        $itemssql = 'SELECT si.id, si.fieldname, si.plugin, si.content
                        FROM {survey_item} si
                        WHERE si.surveyid = :surveyid
                            AND si.type = "'.SURVEY_TYPEFIELD.'"
                            AND si.basicform <> '.SURVEY_NOTPRESENT.'
                            AND si.hide = 0
                        ORDER BY si.sortindex';
        // $sqlparams = array('surveyid' => $survey->id, 'type' => SURVEY_TYPEFIELD, 'basicform' => SURVEY_NOTPRESENT, 'hide' => 0);
        $sqlparams = array('surveyid' => $survey->id);

        // I need get_records_sql instead of get_records because of '<> SURVEY_NOTPRESENT'
        $records = $DB->get_recordset_sql($itemssql, $sqlparams);

        // build options array
        $maxlength = 150;
        $options = array(get_string('choosedots'));
        foreach ($records as $record) {
            $thiscontent = survey_get_sid_field_content($record);
            $content = get_string('pluginname', 'surveyfield_'.$record->plugin).': '.strip_tags($thiscontent);
            if (strlen($content) > $maxlength) {
                $content = substr($content, 0, $maxlength);
            }
            $options[$record->id] = $content;
        }

        $fieldname = 'itemid';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyreport_frequency'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyreport_frequency');

        // -------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('continue'));

    }

    function validation($data, $files) {
        // "noanswer" default option is not allowed when the item is mandatory
        $errors = array();

        if (!$data['itemid']) {
            $errors['itemid'] = get_string('pleasechooseavalue', 'surveyreport_frequency');
        }

        return $errors;
    }
}