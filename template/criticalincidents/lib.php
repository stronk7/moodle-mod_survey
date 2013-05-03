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
 * @package    surveytemplate
 * @subpackage criticalincidents
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/lib.php');
require_once($CFG->dirroot.'/mod/survey/template/lib.php');

/*
 * surveytemplate_criticalincidents_add
 * @param
 * @return
 */
function surveytemplate_criticalincidents_add() {
    global $DB, $CFG;

    $externalname = 'criticalincidents'; // it must match the name of the parent folder of this file
    $timenow = time();
    $sortindex = 0;

    $content_sid = 0;
    // ////////////// SURVEY_ITEM
    $si_fields = array('surveyid'      , 'type'            , 'plugin'          , 'externalname' ,
                       'content_sid'   , 'content'         , 'contentformat'   , 'customnumber' ,
                       'extrarow'      , 'softinfo'        , 'hidehardinfo'    , 'required'     , 'fieldname',
                       'indent'        , 'basicform'       , 'advancedsearch'  , 'hide'         ,
                       'sortindex'     , 'basicformpage'   , 'advancedformpage', 'parentid'     ,
                       'parentcontent' , 'parentvalue'     , 'timecreated'     , 'timemodified');

    $labelintro_sid = 0;
    // ////////////// SURVEY_ITEM_LABEL
    $label_fields = array('surveyid', 'itemid', 'labelintro_sid', 'labelintro');

    // ////////////// SURVEY_ITEM_TEXTAREA
    $textarea_fields = array('surveyid', 'itemid'  , 'useeditor',
                             'arearows', 'areacols', 'minlength', 'maxlength');

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // // CRITICAL INCIDENTS
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // CRITICALINCIDENTS - 1 label
    // ////////////////////////////////////////////////////////////////////////////////////////////

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    $content_sid++;
    $values = array(0, SURVEY_TYPEFORMAT, 'label', $externalname,
                    $content_sid, null, FORMAT_HTML, null,
                    0, '', 0, null, null,
                    0, SURVEY_FILLANDSEARCH, SURVEY_ADVFILLANDSEARCH, 0,
                    $sortindex, 1, 1, null,
                    null, null, $timenow, null);
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

    // survey_label
    /*------------------------------------------------*/
    // $labelintro_sid++; // it never changes from 0
    $values = array(0, $itemid, $labelintro_sid, null);
    $DB->insert_record('survey_label', array_combine($label_fields, $values), false);

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // CRITICALINCIDENTS - 5 textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////
    for ($i = 1; $i <= 5; $i++) {

        $sortindex++; // <--- new item is going to be added

        // survey_item
        /*------------------------------------------------*/
        $content_sid++;
        $values = array(0, SURVEY_TYPEFIELD, 'textarea', $externalname,
                        $content_sid, null, FORMAT_HTML, null,
                        0, '', 0, SURVEY_REQUIREDITEM, null,
                        0, SURVEY_FILLANDSEARCH, SURVEY_ADVFILLANDSEARCH, 0,
                        $sortindex, 1, 1, null,
                        null, null, $timenow, null);
        $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

        // survey_textarea
        /*------------------------------------------------*/
        $values = array(0, $itemid, 0, 12, 60, '', '');
        $DB->insert_record('survey_textarea', array_combine($textarea_fields, $values), false);
    }
}
