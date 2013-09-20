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
 * @package    surveyitem
 * @subpackage autofill
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/locallib.php');

define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT', 15);
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT01', 'submissionid');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT02', 'submissiontime');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT03', 'submissiondate');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT04', 'submissiondateandtime');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT05', 'userid');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT06', 'userfirstname');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT07', 'userlastname');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT08', 'userfullname');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT09', 'usergroupid');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT10', 'usergroupname');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT11', 'surveyid');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT12', 'surveyname');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT13', 'courseid');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT14', 'coursename');
define('SURVEYFIELD_AUTOFILL_CONTENTELEMENT15', 'label');

/*
 * survey_autofill_get_elements
 * @param
 * @return
 */
function survey_autofill_get_elements($surveyid) {
    global $COURSE;

    $cm = get_coursemodule_from_instance('survey', $surveyid, $COURSE->id, false, MUST_EXIST);
    $usegroups = groups_get_activity_groupmode($cm);

    $options = array();
    $options[''] = array(get_string('choosedots'));

    // submission date and time
    $begin = 1;
    $end = $begin + 3; // 3 == ('number of cycles' - 1)
    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyfield_autofill');
    }
    $menuitemlabel = get_string('submission', 'surveyfield_autofill');
    $options[$menuitemlabel] = $subelements;

    // user
    $begin = $end + 1;
    $menuelements = 3; // 3 == ('number of cycles' - 1)
    if ($usegroups) {
        $menuelements += 2; // 'group ID' and 'group name'
    }
    $end = $begin + $menuelements;
    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyfield_autofill');
    }
    $menuitemlabel = get_string('user');
    $options[$menuitemlabel] = $subelements;

    // survey
    $begin = $end + 1;
    if (!$usegroups) { // jump last two menu items
        $begin += 2;
    }
    $end = $begin + 1; // 1 == ('number of cycles' - 1)

    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyfield_autofill');
    }
    $menuitemlabel = get_string('modulename', 'survey');
    $options[$menuitemlabel] = $subelements;

    // course
    $begin = $end + 1;
    $end = $begin + 1; // 1 == ('number of cycles' - 1)
    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyfield_autofill');
    }
    $menuitemlabel = get_string('course');
    $options[$menuitemlabel] = $subelements;

    // submission info

    // custom info
    $begin = $end + 1;
    $end = $begin; // 0 == ('number of cycles' - 1)
    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyfield_autofill');
    }
    $menuitemlabel = get_string('custominfo', 'surveyfield_autofill');
    $options[$menuitemlabel] = $subelements;

    return $options;
}