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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class survey_exportform extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // ----------------------------------------
        // submissionexport::status
        // ----------------------------------------
        $fieldname = 'status';
        if ($DB->get_records('survey_submission', array('status' => SURVEY_STATUSINPROGRESS))) {
            $options = array(SURVEY_STATUSCLOSED => get_string('statusclosed', 'survey'),
                             SURVEY_STATUSINPROGRESS => get_string('statusinprogress', 'survey'),
                             SURVEY_STATUSALL => get_string('statusboth', 'survey'));
            $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
        } else {
            $mform->addElement('hidden', $fieldname, SURVEY_STATUSCLOSED);
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // submissionexport::includehidden
        // ----------------------------------------
        $fieldname = 'includehidden';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // submissionexport::advanced
        // ----------------------------------------
        $fieldname = 'advanced';
        if ($this->_customdata->canaccessadvanceditems) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        } else {
            $mform->addElement('hidden', $fieldname, 0);
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // submissionexport::downloadtype
        // ----------------------------------------
        $fieldname = 'downloadtype';
        $pluginlist = array(SURVEY_DOWNLOADCSV => get_string('downloadtocsv', 'survey'),
                            SURVEY_DOWNLOADTSV => get_string('downloadtotsv', 'survey'),
                            SURVEY_DOWNLOADXLS => get_string('downloadtoxls', 'survey'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $pluginlist);

        $this->add_action_buttons(false, get_string('continue'));
    }
}