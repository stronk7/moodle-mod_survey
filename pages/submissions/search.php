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


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');

/* is the user allowed to add a new survey? */
/* are you continuing a partially entered survey? */
/* are you editing an already closed survey? */

if (!isset($mform)) {
    echo $OUTPUT->notification(get_string('emptysearchform', 'survey'), 'generaltable generalbox boxaligncenter boxwidthnormal');

    $continueurl = new moodle_url('/mod/survey/view.php', array('s' => $survey->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_MANAGE));
    echo $OUTPUT->continue_button($continueurl);
} else {
    $mform->display();
}
