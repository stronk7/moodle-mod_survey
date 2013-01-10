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
 * @subpackage attls
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/lib.php');
require_once($CFG->dirroot.'/mod/survey/template/lib.php');

/*
 * surveytemplate_attls_add
 * @param
 * @return
 */
function surveytemplate_attls_add() {
    global $DB, $CFG;

    $externalname = 'attls'; // it must match the name of the parent folder of this file
    $timenow = time();
    $sortindex = 0;

    $content_sid = 0;
    // ////////////// SURVEY_ITEM
    $si_fields = array('surveyid'      , 'type'            , 'plugin'          , 'externalname' ,
                       'content_sid'   , 'content'         , 'contentformat'   , 'customnumber' ,
                       'extrarow'      , 'softinfo'        , 'required'        , 'fieldname'    ,
                       'indent'        , 'basicform'       , 'advancedsearch'  , 'hide'         ,
                       'sortindex'     , 'basicformpage'   , 'advancedformpage', 'parentid'     ,
                       'parentcontent' , 'parentvalue'     , 'timecreated'     , 'timemodified');

    $radio_options_sid = 1;
    $radio_labelother_sid = 0;
    $radio_defaultvalue_sid = 0;
    // ////////////// SURVEY_ITEM_RADOIBUTTON
    $radiobutton_fields = array('surveyid'        , 'itemid'      , 'options_sid' , 'options',
                                'labelother_sid'  , 'labelother'  , 'defaultoption',
                                'defaultvalue_sid', 'defaultvalue', 'adjustment');

    $fslabel_sid = 0;
    // ////////////// SURVEY_ITEM_FIELDSET
    $fieldset_fields = array('surveyid', 'itemid', 'fslabel_sid', 'fslabel');

    $labelintro_sid = 0;
    // ////////////// SURVEY_ITEM_LABEL
    $label_fields = array('surveyid', 'itemid', 'labelintro_sid', 'labelintro');

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // // ATTLS 20
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ATTLS 20 - item #1/22 - label
    // ////////////////////////////////////////////////////////////////////////////////////////////

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    $content_sid++;
    $values = array(0, SURVEY_FORMAT, 'label', $externalname,
                    $content_sid, null, FORMAT_HTML, null,
                    0, '', null, '',
                    0, SURVEY_FILLANDSEARCH, SURVEY_ADVFILLANDSEARCH, 0,
                    $sortindex, 1, 1, 0,
                    '', '', $timenow, null);
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

    // survey_label
    /*------------------------------------------------*/
    // $labelintro_sid++; // it never changes from 0
    $values = array(0, $itemid, $labelintro_sid, null);
    $DB->insert_record('survey_label', array_combine($label_fields, $values), false);

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ATTLS 20 - item #2/22 - fieldset
    // ////////////////////////////////////////////////////////////////////////////////////////////

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    // $content_sid++; content_sid is not supposed to grow for fieldset
    $values = array(0, SURVEY_FORMAT, 'fieldset', $externalname,
                    null, null, FORMAT_HTML, null,
                    0, '', null, null,
                    0, SURVEY_FILLANDSEARCH, SURVEY_ADVFILLANDSEARCH, 0,
                    $sortindex, 1, 1, null,
                    null, null, $timenow, null);
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

    // survey_fieldset
    /*------------------------------------------------*/
    $fslabel_sid++;
    $values = array(0, $itemid, $fslabel_sid, null);
    $DB->insert_record('survey_fieldset', array_combine($fieldset_fields, $values), false);

    $itemoffset = 2;
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ATTLS 20 - 20 radiobutton items
    // ////////////////////////////////////////////////////////////////////////////////////////////
    for ($i = 1; $i <= 20; $i++) {

        $sortindex++; // <--- new item is going to be added

        // survey_item
        /*------------------------------------------------*/
        $content_sid++;
        $values = array(0, SURVEY_FIELD, 'radiobutton', $externalname,
                        $content_sid, null, FORMAT_HTML, null,
                        0, '', SURVEY_REQUIREDITEM, null,
                        0, SURVEY_FILLANDSEARCH, SURVEY_ADVFILLANDSEARCH, 0,
                        $sortindex, 1, 1, null,
                        null, null, $timenow, null);
        $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

        // survey_radiobutton
        /*------------------------------------------------*/
        // $radio_options_sid++;      // it never changes from 1
        // $radio_labelother_sid++;   // it never changes from 0
        // $radio_defaultvalue_sid++; // it never changes from 0
        $values = array(0, $itemid, $radio_options_sid, null,
                        $radio_labelother_sid, null, SURVEY_INVITATIONDEFAULT,
                        $radio_defaultvalue_sid, null, SURVEY_HORIZONTAL);
        $DB->insert_record('survey_radiobutton', array_combine($radiobutton_fields, $values), false);
    }
}