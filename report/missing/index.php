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
 * Defines the version of survey autofill subplugin
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    surveyreport
 * @subpackage frequency
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

echo $OUTPUT->heading(get_string('pluginname', 'surveyreport_missing'));

require_once($CFG->libdir.'/tablelib.php');

$table = new flexible_table('userattempts');

$paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_REPORT, 'rname' => $reportname);
$table->define_baseurl(new moodle_url('view.php', $paramurl));

$tablecolumns = array();
$tablecolumns[] = 'picture';
$tablecolumns[] = 'fullname';
$table->define_columns($tablecolumns);

$tableheaders = array();
$tableheaders[] = '';
$tableheaders[] = get_string('fullname');
$table->define_headers($tableheaders);

$table->sortable(true, 'lastname', 'ASC'); // sorted by sortindex by default

$table->column_class('picture', 'picture');
$table->column_class('fullname', 'fullname');

// $table->initialbars(true);

// hide the same info whether in two consecutive rows
$table->column_suppress('picture');
$table->column_suppress('fullname');

// general properties for the whole table
// $table->set_attribute('cellpadding', '5');
$table->set_attribute('id', 'userattempts');
$table->set_attribute('class', 'generaltable');
// $table->set_attribute('width', '90%');
$table->setup();

$context = context_course::instance($COURSE->id);
$roles = get_roles_used_in_context($context);
// if (isset($survey->guestisallowed)) {
//     $guestrole = get_guest_role();
//     $roles[$guestrole->id] = $guestrole;
// }
$role = array_keys($roles);
$sql = 'SELECT DISTINCT u.id, u.picture, u.imagealt, u.firstname, u.lastname, u.email, s.attempts
        FROM m20_user u
		JOIN (SELECT *
                FROM m20_role_assignments
                WHERE contextid = '.$context->id.'
                  AND roleid IN ('.implode($role).')) ra ON u.id = ra.userid
		LEFT JOIN (SELECT *, count(s.id) as attempts
			         FROM m20_survey_submissions s
			         WHERE s.surveyid = :surveyid
			         GROUP BY s.userid) s ON s.userid = u.id
		WHERE ISNULL(s.id)';
if ($table->get_sql_sort()) {
    $sql .= ' ORDER BY '.$table->get_sql_sort();
} else {
    $sql .= ' ORDER BY s.timecreated';
}

$sqlparams = array('surveyid' => $survey->id);
$usersubmissions = $DB->get_recordset_sql($sql, $sqlparams);

foreach ($usersubmissions as $usersubmission) {
    $tablerow = array();

    // picture
    $tablerow[] = $OUTPUT->user_picture($usersubmission, array('courseid'=>$COURSE->id));

    // user fullname
    $tablerow[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$usersubmission->id.'&amp;course='.$COURSE->id.'">'.fullname($usersubmission).'</a>';

    // add row to the table
    $table->add_data($tablerow);
}

$usersubmissions->close();

$table->summary = get_string('submissionslist', 'survey');
$table->print_html();

