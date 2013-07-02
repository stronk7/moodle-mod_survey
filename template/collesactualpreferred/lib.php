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
 * @subpackage collesactualpreferred
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/lib.php');
require_once($CFG->dirroot.'/mod/survey/template/lib.php');

/*
 * surveytemplate_collesactualpreferred_add
 * @param
 * @return
 */
function surveytemplate_collesactualpreferred_add() {
    global $DB;

    $externalname = 'collesactualpreferred'; // it must match the name of the parent folder of this file
    $timenow = time();
    $sortindex = 0;

    $content_sid = 0;
    // ////////////// SURVEY_ITEM
    $si_fields = array('surveyid'      , 'type'         , 'plugin'          , 'externalname',
                       'content_sid'   , 'content'      , 'contentformat'   , 'customnumber',
                       'extrarow'      , 'extranote'    , 'hideinstructions', 'required'    ,
                       'variable'      , 'indent'       , 'hide'            , 'insearchform',
                       'limitedaccess' , 'sortindex'    , 'formpage'        , 'parentid'    ,
                       'parentcontent' , 'parentvalue'  , 'timecreated'     , 'timemodified');

    $radio_options_sid = 1;
    $radio_labelother_sid = 0;
    $radio_defaultvalue_sid = 0;
    // ////////////// SURVEY_ITEM_RADOIBUTTON
    $radiobutton_fields = array('surveyid'        , 'itemid'      , 'options_sid'  , 'options',
                                'labelother_sid'  , 'labelother'  , 'defaultoption',
                                'defaultvalue_sid', 'defaultvalue', 'adjustment');

    $select_options_sid = 2;
    $select_labelother_sid = 0;
    $select_defaultvalue_sid = 0;
    // ////////////// SURVEY_ITEM_SELECT
    $select_fields = array('surveyid'        , 'itemid'    , 'options_sid'  , 'options',
                           'labelother_sid'  , 'labelother', 'defaultoption',
                           'defaultvalue_sid', 'defaultvalue');

    // ////////////// SURVEY_ITEM_TEXTAREA
    $textarea_fields = array('surveyid', 'itemid'  , 'useeditor',
                             'arearows', 'areacols', 'minlength', 'maxlength');

    $fslabel_sid = 0;
    // ////////////// SURVEY_ITEM_FIELDSET
    $fieldset_fields = array('surveyid', 'itemid', 'fslabel_sid', 'fslabel');

    $labelintro_sid = 0;
    // ////////////// SURVEY_ITEM_LABEL
    $label_fields = array('surveyid', 'itemid', 'labelintro_sid', 'labelintro');

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // // COLLESACTUALPREFERRED
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // COLLESACTUALPREFERRED - item #1 - label
    // ////////////////////////////////////////////////////////////////////////////////////////////

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    $content_sid++;
    $values = array(0, SURVEY_TYPEFORMAT, 'label', $externalname,
                    $content_sid, null, FORMAT_HTML, null,
                    0, '', 0, null,
                    '', 0, 0, 1,
                    0, $sortindex, 1, null,
                    '', '', $timenow, null);
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

    // survey_label
    /*------------------------------------------------*/
    // $labelintro_sid++; // it never changes from 0
    $values = array(0, $itemid, $labelintro_sid, null);
    $DB->insert_record('survey_label', array_combine($label_fields, $values), false);

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // COLLESACTUALPREFERRED - 6 * ( 1 fieldset with 1 label and 4 radiobuttons )
    // ////////////////////////////////////////////////////////////////////////////////////////////
    for ($i = 1; $i <= 6; $i++) {

        // ////////////////////////////////////////////////////////////////////////////////////////////
        // COLLESACTUALPREFERRED - item #2, #8, #14, #20, #26, #32 - fieldset
        // ////////////////////////////////////////////////////////////////////////////////////////////

        $sortindex++; // <--- new item is going to be added

        // survey_item
        /*------------------------------------------------*/
        // $content_sid++; content_sid is not supposed to grow for fieldset
        $values = array(0, SURVEY_TYPEFORMAT, 'fieldset', $externalname,
                    null, null, FORMAT_HTML, null,
                    0, '', 0, null,
                    '', 0, 0, 1,
                    0, $sortindex, 1, null,
                    null, null, $timenow, null);
        $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

        // survey_fieldset
        /*------------------------------------------------*/
        $fslabel_sid++;
        $values = array(0, $itemid, $fslabel_sid, null);
        $DB->insert_record('survey_fieldset', array_combine($fieldset_fields, $values), false);

        // ////////////////////////////////////////////////////////////////////////////////////////////
        // COLLESACTUALPREFERRED - item #3, #9, #15, #21, #27, #33 - label
        // ////////////////////////////////////////////////////////////////////////////////////////////

        $sortindex++; // <--- new item is going to be added

        // survey_item
        /*------------------------------------------------*/
        // $content_sid++; do not increase here because content of label does not change during the survey
        //                 and I gat the string for label always from content00
        $values = array(0, SURVEY_TYPEFORMAT, 'label', $externalname,
                        0, null, FORMAT_HTML, '',
                        0, '', 0, null,
                        '', 0, 0, 1,
                        0, $sortindex, 1, null,
                        '', '', $timenow, null);
        $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

        // survey_label
        /*------------------------------------------------*/
        // $labelintro_sid++; // it never changes from 0
        $values = array(0, $itemid, $labelintro_sid, null);
        $DB->insert_record('survey_label', array_combine($label_fields, $values), false);

        // ////////////////////////////////////////////////////////////////////////////////////////////
        // COLLESACTUALPREFERRED - item # 4,  5,  6,  7,  8,  9, 10, 11 - 8 radiobuttons
        //                              #14, 15, 16, 17, 18, 19, 20, 21 - 8 radiobuttons
        //                              #24, 25, 26, 27, 28, 29, 30, 31 - 8 radiobuttons
        //                              #34, 35, 36, 37, 38, 39, 40, 41 - 8 radiobuttons
        // ////////////////////////////////////////////////////////////////////////////////////////////
        for ($j = 1; $j <= 4; $j++) {
            for ($k = 1; $k <= 2; $k++) {
                $sortindex++; // <--- new item is going to be added

                // survey_item
                /*------------------------------------------------*/
                $content_sid++;
                $values = array(0, SURVEY_TYPEFIELD, 'radiobutton', $externalname,
                                $content_sid, null, FORMAT_HTML, null,
                                0, '', 0, SURVEY_REQUIREDITEM,
                                '', 0, 0, 1,
                                0, $sortindex, 1, null,
                                '', '', $timenow, null);
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
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // COLLESACTUALPREFERRED - item #38 - 1 select
    // ////////////////////////////////////////////////////////////////////////////////////////////

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    $content_sid++;
    $values = array(0, SURVEY_TYPEFIELD, 'select', $externalname,
                    $content_sid, null, FORMAT_HTML, null,
                    0, '', 0, SURVEY_REQUIREDITEM,
                    '', 0, 0, 1,
                    0, $sortindex, 1, null,
                    '', '', $timenow, null);
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

    // survey_select
    /*------------------------------------------------*/
    // $select_options_sid++;      // it never changes from 2
    // $select_labelother_sid++;   // it never changes from 0
    // $select_defaultvalue_sid++; // it never changes from 0
    $values = array(0, $itemid, $select_options_sid, null,
                    $select_labelother_sid, null, SURVEY_INVITATIONDEFAULT,
                    $select_defaultvalue_sid, null);
    $DB->insert_record('survey_select', array_combine($select_fields, $values), false);

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // COLLESACTUALPREFERRED - item #39 - last textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    $content_sid++;
    $values = array(0, SURVEY_TYPEFIELD, 'textarea', $externalname,
                    $content_sid, null, FORMAT_HTML, null,
                    0, '', 0, SURVEY_OPTIONALITEM,
                    '', 0, 0, 1,
                    0, $sortindex, 1, null,
                    '', '', $timenow, null);
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

    // survey_textarea
    /*------------------------------------------------*/
    $values = array(0, $itemid, 0, 12, 60, '', '');
    $DB->insert_record('survey_textarea', array_combine($textarea_fields, $values), false);
}