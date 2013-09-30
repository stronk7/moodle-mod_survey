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

    public function definition() {
        global $DB;

        $mform = $this->_form;

        $survey = $this->_customdata->survey;

        // only fields
        // no matter for the page
        // elenco dei campi che l'utente vuole vedere nel file esportato

        $where = array();
        $where['surveyid'] = $survey->id;
        $where['type'] = SURVEY_TYPEFIELD;
        $where['advanced'] = 0;
        $where['hide'] = 0;
        $itemseeds = $DB->get_recordset('survey_item', $where, 'sortindex');

        // build options array
        $options = array(get_string('choosedots'));
        foreach ($itemseeds as $itemseed) {
            if ($itemseed->plugin == 'textarea') {
                continue;
            }
            $thiscontent = $DB->get_field('survey_'.$itemseed->plugin, 'content', array('itemid' => $itemseed->id));
            if (!empty($survey->template)) {
                $thiscontent = get_string($thiscontent, 'surveytemplate_'.$survey->template);
            }

            $content = get_string('pluginname', 'surveyfield_'.$itemseed->plugin).': '.strip_tags($thiscontent);
            $content = survey_fixlength($content, 60);
            $options[$itemseed->id] = $content;
        }

        $fieldname = 'itemid';
        $mform->addElement('select', $fieldname, get_string('variable', 'survey'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyreport_frequency');

        // ----------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('continue'));
    }

    public function validation($data, $files) {
        // "noanswer" default option is not allowed when the item is mandatory
        $errors = array();

        if (!$data['itemid']) {
            $errors['itemid'] = get_string('pleasechooseavalue', 'surveyreport_frequency');
        }

        return $errors;
    }
}