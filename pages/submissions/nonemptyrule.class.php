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
    function validate($value, $mform = null) {

        $submission = $mform->getSubmitValues();

        // Now, check
        if (array_key_exists('prevbutton', $submission)) {
            // echo 'I omit the validation<br />';
            $valid = true;
        } else {
            // echo 'I perform the validation<br />';
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