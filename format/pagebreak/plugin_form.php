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


/**
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
require_once($CFG->dirroot.'/mod/survey/format/pagebreak/lib.php');

class survey_pluginform extends surveyitem_baseform {

    function definition() {
        //-------------------------------------------------------------------------------
        // acquisisco i valori per pre-definire i campi della form
        $item = $this->_customdata->item;

        //-------------------------------------------------------------------------------
        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // finisco con la "sezione" comune della form
        parent::definition();

        //-------------------------------------------------------------------------------
        // buttons
        if (!empty($item->itemid)) {
            $fieldname = 'buttons';
            $elementgroup=array();
            $elementgroup[] = $mform->createElement('submit', 'save', get_string('savechanges'));
            $elementgroup[] = $mform->createElement('submit', 'saveasnew', get_string('saveasnew', 'survey'));
            $elementgroup[] = $mform->createElement('cancel');
            $mform->addGroup($elementgroup, $fieldname.'_group', '', ' ', false);
            $mform->closeHeaderBefore($fieldname.'_group');
        } else {
            $this->add_action_buttons(true, get_string('add'));
        }

        //-------------------------------------------------------------------------------
        // sono alla fine della form
        // qui pre-definisco i valori dei campi che ho passato alla form
        // tramite
        // $this->set_data($item); // commented on September 17, 2012
    }
}