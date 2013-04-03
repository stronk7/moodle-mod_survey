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

require_once($CFG->dirroot.'/lib/pear/HTML/QuickForm/element.php');

class survey_nonempty_rule extends HTML_QuickForm_Rule {
    /*
     * this server side validation is called ONLY WHRETHER the item is mandatory
     * The reason why I use the server side validation follows:
     * Let's say I have two items: item01 and item02
     * item01 is the parent of item02
     * item02 is mandatory
     * Let's suppose that item02 is in the same page of item01
     * AND
     * the condition enabling item02 DOES NOT MATCH.
     * At submit time item02 MUST NOT be checked EVEN IF IT IS MANDATORY
     */
    function validate($value, $mform = null) {

        $submission = $mform->getSubmitValues();

        // Now, check
        if (array_key_exists('prevbutton', $submission)) {
            // Omit the validation if previous button was pressed
            $valid = true;
        } else {
            // Perform the server side validation
            // I need to verify that:
            // --> text fields are not empty
            // --> select is not set to SURVEY_INVITATIONVALUE
            // --> radio buttons is not to SURVEY_INVITATIONVALUE
            $valid = isset($value) && ($value !== '');
            $valid = $valid && ($value != SURVEY_INVITATIONVALUE);
        }
        // echo '$valid = '.$valid.'<br />';
        return $valid;
    }
}