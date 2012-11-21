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
 * @package    surveytemplate
 * @subpackage sample_tree
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/lib.php');
require_once($CFG->dirroot.'/mod/survey/template/lib.php');
require_once($CFG->dirroot.'/mod/survey/field/age/lib.php');
require_once($CFG->dirroot.'/mod/survey/field/boolean/lib.php');
require_once($CFG->dirroot.'/mod/survey/field/character/lib.php');


/**
 * surveytemplate_sample_tree_add
 * @param
 * @return
 */
function surveytemplate_sample_tree_add() {
    global $DB, $CFG;

    $externalname = 'sample_tree'; // it must match the name of the parent folder of this file
    $timenow = time();
    $sortindex = 0;

    $content_sid = 0;
    $content_sid = 0;
    // ////////////// SURVEY_ITEM
    $si_fields = array('surveyid','type','plugin','externalname','content_sid','content','contentformat','customnumber','extrarow','softinfo','required','fieldname','indent','basicform','advancedsearch','hide','sortindex','basicformpage','advancedformpage','parentid','parentcontent','parentvalue','timecreated','timemodified');

    // ////////////// SURVEY_AGE
    $age_fields = array('surveyid','itemid','defaultoption','defaultvalue','lowerbound','upperbound');

    // ////////////// SURVEY_BOOLEAN
    $boolean_fields = array('surveyid','itemid','defaultoption','defaultvalue','style');

    $defaultvalue_sid = 0;
    // ////////////// SURVEY_CHARACTER
    $character_fields = array('surveyid','itemid','defaultvalue_sid','defaultvalue','pattern','minlength','maxlength');

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // // SAMPLE_TREE
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    $content_sid++;
    $values = array(0,SURVEY_FIELD,'boolean','sample_tree',
                    $content_sid,null,FORMAT_HTML,'',
                    0,'',SURVEY_OPTIONALITEM,'',
                    0,SURVEY_FILLONLY,SURVEY_ADVFILLONLY,0,
                    $sortindex,'','',0,
                    '',null,1352990362,'');
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

        // survey_boolean
        /*------------------------------------------------*/
        $values = array(0,$itemid,SURVEY_INVITATIONDEFAULT,'-1',
                        SURVEYFIELD_BOOLEAN_USESELECT);
        $itemid = $DB->insert_record('survey_boolean', array_combine($boolean_fields, $values));
    //---------- end of this item

//----------------------------------------------------------------------------//

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    $content_sid++;
    $values = array(0,SURVEY_FIELD,'age','sample_tree',
                    $content_sid,null,FORMAT_HTML,'',
                    0,'',SURVEY_OPTIONALITEM,'',
                    0,SURVEY_FILLONLY,SURVEY_ADVFILLONLY,0,
                    $sortindex,'','',1,
                    1,1,1352990362,'');
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

        // survey_age
        /*------------------------------------------------*/
        $values = array(0,$itemid,SURVEY_INVITATIONDEFAULT,'-1',
                        '-2635200','3339835200');
        $itemid = $DB->insert_record('survey_age', array_combine($age_fields, $values));
    //---------- end of this item

//----------------------------------------------------------------------------//

    $sortindex++; // <--- new item is going to be added

    // survey_item
    /*------------------------------------------------*/
    $content_sid++;
    $values = array(0,SURVEY_FIELD,'character','sample_tree',
                    $content_sid,null,FORMAT_HTML,'',
                    0,'',SURVEY_OPTIONALITEM,'',
                    0,SURVEY_FILLONLY,SURVEY_ADVFILLONLY,0,
                    $sortindex,'','',1,
                    0,0,1352990362,'');
    $itemid = $DB->insert_record('survey_item', array_combine($si_fields, $values));

        // survey_character
        /*------------------------------------------------*/
        $defaultvalue_sid++;
        $values = array(0,$itemid,$defaultvalue_sid,null,
                        null,0,'255');
        $itemid = $DB->insert_record('survey_character', array_combine($character_fields, $values));
    //---------- end of this item

//----------------------------------------------------------------------------//
}
