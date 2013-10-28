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
 * Internal library of functions for module survey
 *
 * All the survey specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('SURVEY_GHEIGHT', 500);
define('SURVEY_GWIDTH' , 800);

function fetch_scalesdata($surveyid) {
    global $DB;

    $iarea = new stdClass();
    // names of areas of investigation
    for ($i = 1; $i < 7; $i++) {
        $iarea->surveyname[] = get_string('fieldset_content_0'.$i, 'surveytemplate_'.$iarea->surveyname);
    }
    // end of: names of areas of investigation

    // useless now
    // $iarea->name = $DB->get_field('survey', 'template', array('id' => $surveyid));

    // group question id per area of investigation
    $sql = 'SELECT si.id, si.sortindex, si.plugin
            FROM {survey_item} si
            WHERE si.surveyid = :surveyid
                AND si.plugin = :plugin
            ORDER BY si.sortindex';

    $whereparams = array('surveyid' => $surveyid, 'plugin' => 'radiobutton');
    $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

    $countradio = ($iarea->surveyname == 'collesactualpreferred') ? 8 : 4;
    $idlist = array();
    $i = 0;
    foreach ($itemseeds as $itemseed) {
        $idlist[] = $itemseed->id;
        if (count($idlist) == $countradio) {
            $iarea->itemidlist[] = $idlist;
            $i++;
            $idlist = array();
        }
    }
    $itemseeds->close();
    // end of: group question id per area of investigation

    // options (label for possible answers)
    $itemid = $iarea->itemidlist[0][0]; // one of the itemid of the survey (the first)
    $item = survey_get_item($itemid, SURVEY_TYPEFIELD, 'radiobutton');
    $iarea->options = $item->item_get_labels_array('options');
    // end of: options (label for possible answers)

    // calculate the mean and the standard deviation of answers
    $m = array();
    $i = 0;
    foreach ($iarea->itemidlist as $areaidlist) {
        $sql = 'SELECT count(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                FROM {survey_userdata} ud
                WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
        $aggregate = $DB->get_record_sql($sql);
        $m = $aggregate->sumofanswers/$aggregate->countofanswers;
        $iarea->mean[] = $m;
        $i++;

        $sql = 'SELECT ud.content
                FROM {survey_userdata} ud
                WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
        $answers = $DB->get_recordset_sql($sql);
        $bigsum = 0;
        foreach ($answers as $answer) {
            $xi = (double)$answer->content;
            $bigsum += ($xi - $m) * ($xi - $m);
        }
        $answers->close();

        $bigsum /= $aggregate->countofanswers;
        $iarea->stddeviation[] = sqrt($bigsum);
    }
    // end of: calculate the mean and the standard deviation of answers

    return $iarea;
}

function fetch_summarydata($surveyid) {
    global $DB;

    $iarea = new stdClass();
    $iarea->surveyname = $DB->get_field('survey', 'template', array('id' => $surveyid));

    // names of areas of investigation
    for ($i = 1; $i < 7; $i++) {
        $iarea->name[] = get_string('fieldset_content_0'.$i, 'surveytemplate_'.$iarea->surveyname);
    }
    // end of: names of areas of investigation

    // group question id per area of investigation
    $sql = 'SELECT si.id, si.sortindex, si.plugin
            FROM {survey_item} si
            WHERE si.surveyid = :surveyid
                AND si.plugin = :plugin
            ORDER BY si.sortindex';

    $whereparams = array('surveyid' => $surveyid, 'plugin' => 'radiobutton');
    $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

    $countradio = ($iarea->surveyname == 'collesactualpreferred') ? 8 : 4;
    $idlist = array();
    $i = 0;
    foreach ($itemseeds as $itemseed) {
        $idlist[] = $itemseed->id;
        if (count($idlist) == $countradio) {
            $iarea->itemidlist[] = $idlist;
            $i++;
            $idlist = array();
        }
    }
    $itemseeds->close();
    // end of: group question id per area of investigation

    // options (label for possible answers)
    $itemid = $iarea->itemidlist[0][0]; // one of the itemid of the survey (the first)
    $item = survey_get_item($itemid, SURVEY_TYPEFIELD, 'radiobutton');
    $iarea->options = $item->item_get_labels_array('options');
    // end of: options (label for possible answers)

    // calculate the mean and the standard deviation of answers
    $m = array();
    $i = 0;
    foreach ($iarea->itemidlist as $areaidlist) {
        $sql = 'SELECT count(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                FROM {survey_userdata} ud
                WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
        $aggregate = $DB->get_record_sql($sql);
        $m = $aggregate->sumofanswers/$aggregate->countofanswers;
        $iarea->mean[] = $m;
        $i++;

        $sql = 'SELECT ud.content
                FROM {survey_userdata} ud
                WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
        $answers = $DB->get_recordset_sql($sql);
        $bigsum = 0;
        foreach ($answers as $answer) {
            $xi = (double)$answer->content;
            $bigsum += ($xi - $m) * ($xi - $m);
        }
        $answers->close();

        $bigsum /= $aggregate->countofanswers;
        $iarea->stddeviation[] = sqrt($bigsum);
    }
    // end of: calculate the mean and the standard deviation of answers

    return $iarea;
}