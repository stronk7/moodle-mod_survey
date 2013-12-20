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

class survey_searchform extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;

        // $canaccessadvanceditems, $searchform=true, $type=false, $formpage=false
        list($sql, $whereparams) = survey_fetch_items_seeds($survey->id, $canaccessadvanceditems, true);
        $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

        $context = context_module::instance($cmid);

        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

            // position
            $position = $item->get_position();
            $elementnumber = $item->get_customnumber() ? $item->get_customnumber().':' : '';
            if ($position == SURVEY_POSITIONTOP) {
                // workaround suggested by Marina Glancy in MDL-42946
                $content = html_writer::tag('span', $item->get_content(), array('class' => 'indent-'.$item->get_indent()));

                $mform->addElement('static', $item->get_itemname().'_extrarow', $elementnumber, $content);
            }
            if ($position == SURVEY_POSITIONTOPLEFT) {
                $questioncontent = $item->get_content();
                if ($elementnumber) {
                    // I want to change "4.2:<p>Do you live in NY?</p>" to "<p>4.2: Do you live in NY?</p>"
                    if (preg_match('/^<p>(.*)$/', $questioncontent, $match)) {
                        // print_object($match);
                        $questioncontent = '<p>'.$elementnumber.' '.$match[1];
                    }
                }
                $content = '';
                $content .= html_writer::start_tag('fieldset', array('class' => 'hidden'));
                $content .= html_writer::start_tag('div');
                $content .= html_writer::start_tag('div', array('class' => 'fitem'));
                $content .= html_writer::start_tag('div', array('class' => 'fstatic fullwidth'));
                // $content .= html_writer::start_tag('div', array('class' => 'indent-'.$this->indent));
                $content .= $questioncontent;
                // $content .= html_writer::end_tag('div');
                $content .= html_writer::end_tag('div');
                $content .= html_writer::end_tag('div');
                $content .= html_writer::end_tag('div');
                $content .= html_writer::end_tag('fieldset');
                $mform->addElement('html', $content);
            }

            // element
            $item->userform_mform_element($mform, true);

            // note
            if ($fullinfo = $item->userform_get_full_info(true)) {
                // workaround suggested by Marina Glancy in MDL-42946
                $content = html_writer::tag('span', $fullinfo, array('class' => 'indent-'.$item->get_indent()));

                $mform->addElement('static', $item->get_itemname().'_info', get_string('note', 'survey'), $content);
            }
        }
        $itemseeds->close();

        // buttons
        // $this->add_action_buttons(true, get_string('search'));
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('search'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('findall', 'survey'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    // function validation($data, $files) {
    // }
}