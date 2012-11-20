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

require_once($CFG->dirroot.'/mod/survey/locallib.php');
require_once($CFG->libdir.'/tablelib.php');

if ($action == SURVEY_DELETEPRESET) {
    survey_delete_preset($cm, $confirm, $fileid);
}

// /////////////////////////////////////////////////
// $paramurl_base definition
$paramurl_base = array();
$paramurl_base['id'] = $cm->id;
$paramurl_base['tab'] = SURVEY_TABPRESETS;
$paramurl_base['pag'] = SURVEY_PRESETS_MANAGE;
// end of $paramurl_base definition
// /////////////////////////////////////////////////

$table = new flexible_table('presetlist');

$paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABPRESETS, 'pag' => SURVEY_PRESETS_MANAGE);
$table->define_baseurl(new moodle_url('view.php', $paramurl));

$tablecolumns = array();
$tablecolumns[] = 'presetname';
$tablecolumns[] = 'sharinglevel';
$tablecolumns[] = 'timecreated';
$tablecolumns[] = 'actions';
$table->define_columns($tablecolumns);

$tableheaders = array();
$tableheaders[] = get_string('presetname', 'survey');
$tableheaders[] = get_string('sharinglevel', 'survey');
$tableheaders[] = get_string('timecreated', 'survey');
$tableheaders[] = get_string('actions');
$table->define_headers($tableheaders);

// $table->collapsible(true);
$table->sortable(true, 'presetname'); // sorted by sortindex by default
$table->no_sorting('actions');

$table->column_class('presetname', 'presetname');
$table->column_class('sharinglevel', 'sharinglevel');
$table->column_class('timecreated', 'timecreated');
$table->column_class('actions', 'actions');

// definisco delle proprietà generali per tutta la tabella
// $table->set_attribute('cellpadding', '5');
$table->set_attribute('id', 'managepresets');
$table->set_attribute('class', 'generaltable');
// $table->set_attribute('width', '90%');
$table->setup();

$applytitle = get_string('applypreset', 'survey');
$deletetitle = get_string('delete');
$exporttitle = get_string('presetexport', 'survey');

$options = survey_get_sharinglevel_options($cm->id, $survey);

// echo '$options:';
// var_dump($options);

$presets = new stdClass();
foreach ($options as $sharinglevel => $v) {
    $parts = explode('_', $sharinglevel);
    $contextlevel = $parts[0];

    $contextid = survey_get_contextid_from_sharinglevel($sharinglevel);
    $contextstring = survey_get_contextstring_from_sharinglevel($contextlevel);
    $presets->{$contextstring} = survey_get_available_presets($contextid);
}

// echo '$presets:';
// var_dump($presets);

foreach ($presets as $contextstring => $contextfiles) {
    foreach ($contextfiles as $xmlfile) {
        // echo '$xmlfile:';
        // var_dump($xmlfile);
        $tablerow = array();
        $tablerow[] = $xmlfile->get_filename();
        $tablerow[] = get_string($contextstring, 'survey');
        $tablerow[] = userdate($xmlfile->get_timecreated());

        $paramurl_base['fid'] = $xmlfile->get_id();

        $icons = '';
        // *************************************** SURVEY_DELETEPRESET
        if ($xmlfile->get_userid() == $USER->id) { // only the owner can delete his/her preset
            $paramurl = $paramurl_base + array('act' => SURVEY_DELETEPRESET);
            $basepath = new moodle_url('view.php', $paramurl);

            $icons .= '<a class="editing_update" title="'.$deletetitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletetitle.'" title="'.$deletetitle.'" /></a>&nbsp;';
        }

        // *************************************** SURVEY_EXPORTPRESET
        $paramurl = $paramurl_base + array('act' => SURVEY_EXPORTPRESET);
        $basepath = new moodle_url('view.php', $paramurl);

        $icons .= '<a class="editing_update" title="'.$exporttitle.'" href="'.$basepath.'">';
        $icons .= '<img src="'.$OUTPUT->pix_url('download', 'survey').'" class="iconsmall" alt="'.$exporttitle.'" title="'.$exporttitle.'" /></a>';

        $tablerow[] = $icons;

        $table->add_data($tablerow);
    }
}
$table->set_attribute('align', 'center');
$table->summary = get_string('presetlist', 'survey');
$table->print_html();
