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

class survey_itemtypeform extends moodleform {

    function definition() {

        $mform = $this->_form;

        // ----------------------------------------
        // newitem::plugin
        // ----------------------------------------
        $fieldname = 'plugin';
        // TAKE CARE! Here the plugin holds type and plugin both
        $field_plugins = survey_get_plugin_list(SURVEY_TYPEFIELD, true);
        $format_plugins = survey_get_plugin_list(SURVEY_TYPEFORMAT, true);

        foreach ($field_plugins as $k => $v) {
            $field_plugins[$k] = get_string('userfriendlypluginname', 'surveyfield_'.$v);
        }
        asort($field_plugins);

        foreach ($format_plugins as $k => $v) {
            $format_plugins[$k] = get_string('userfriendlypluginname', 'surveyformat_'.$v);
        }
        asort($format_plugins);

        $pluginlist = array(get_string('typefield' , 'survey') => $field_plugins,
                            get_string('typeformat', 'survey') => $format_plugins);

        $mform->addElement('selectgroups', $fieldname, get_string($fieldname, 'survey'), $pluginlist);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // -------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('continue'));
    }
}