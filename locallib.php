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

require_once($CFG->dirroot.'/mod/survey/lib.php');

/*
 * survey_get_item
 * @param $itemid, $type, $plugin
 * @return
 */
function survey_get_item($itemid=0, $type='', $plugin='') {
    global $CFG, $DB;

    if (empty($itemid)) {
        if (empty($type) || empty($plugin)) {
            debugging('Can not get an item without its type, plugin and ID');
        }
    }

    if (empty($type) && empty($plugin)) { // I am asking for a template only
        $itemseed = $DB->get_record('survey_item', array('id' => $itemid), 'type, plugin', MUST_EXIST);
        $type = $itemseed->type;
        $plugin = $itemseed->plugin;
    }

    require_once($CFG->dirroot.'/mod/survey/'.$type.'/'.$plugin.'/plugin.class.php');
    $classname = 'survey'.$type.'_'.$plugin;
    $item = new $classname($itemid);

    return $item;
}

/*
 * survey_non_empty_only
 * @param $arrayelement
 * @return
 */
function survey_non_empty_only($arrayelement) {
    return strlen(trim($arrayelement)); // returns 0 if the array element is empty
}

/*
 * survey_textarea_to_array
 * @param $textareacontent
 * @return
 */
function survey_textarea_to_array($textareacontent) {

    $textareacontent = trim($textareacontent);
    $textareacontent = str_replace("\r", '', $textareacontent);

    $rows = explode("\n", $textareacontent);

    $arraytextarea = array_filter($rows, 'survey_non_empty_only');

    return $arraytextarea;
}

/*
 * survey_clean_textarea_fields
 * @param $record, $fieldlist
 * @return
 */
function survey_clean_textarea_fields($record, $fieldlist) {
    foreach ($fieldlist as $field) {
        // do not forget some item may be undefined causing:
        // Notice: Undefined property: stdClass::$defaultvalue
        // as, for instance, disabled $defaultvalue field when $delaultoption == invitation
        if (isset($record->{$field})) {
            $temparray = survey_textarea_to_array($record->{$field});
            $record->{$field} = implode("\n", $temparray);
        }
    }
}

/*
 * survey_add_tree_node
 * @param $confirm, $cm, $itemid, $type
 * @return
 */
function survey_add_tree_node(&$tohidelist, &$sortindextohidelist) {
    global $DB;

    $i = count($tohidelist);
    $itemid = $tohidelist[$i-1];
    if ($childitems = $DB->get_records('survey_item', array('parentid' => $itemid, 'hide' => 0), 'sortindex', 'id, sortindex')) { // potrebbero non esistere
        foreach ($childitems as $childitem) {
            $tohidelist[] = (int)$childitem->id;
            $sortindextohidelist[] = $childitem->sortindex;
            survey_add_tree_node($tohidelist, $sortindextohidelist);
        }
    }
}

/*
 * survey_move_regular_items
 * @param $itemid, $in
 * @return
 */
function survey_move_regular_items($itemid, $newbasicform) {
    global $DB;

    // build tohidelist
    // here I must select the whole tree down
    $tohidelist = array($itemid);
    $sortindextohidelist = array();
    survey_add_regular_item_node($tohidelist, $sortindextohidelist, $newbasicform);
    array_shift($tohidelist); // $itemid has already been saved

    $itemstoprocess = count($tohidelist);

    foreach ($tohidelist as $tohideitemid) {
        $DB->set_field('survey_item', 'basicform', $newbasicform, array('id' => $tohideitemid));
    }

    return $itemstoprocess; // did you do something?
}

/*
 * survey_add_regular_item_node
 * @param $tohidelist, $sortindextohidelist, $in
 * @return
 */
function survey_add_regular_item_node(&$tohidelist, &$sortindextohidelist, $newbasicform) {
    global $DB;

    $i = count($tohidelist);
    $itemid = $tohidelist[$i-1];
    $comparison = ($newbasicform == SURVEY_NOTPRESENT) ? '<>' : '=';
    $where = 'parentid = :parentid AND basicform '.$comparison.' :basicform';
    $params = array('parentid' => $itemid, 'basicform' => SURVEY_NOTPRESENT);
    if ($childitems = $DB->get_records_select('survey_item', $where, $params, 'sortindex', 'id, sortindex')) { // potrebbero non esistere
        foreach ($childitems as $childitem) {
            $tohidelist[] = (int)$childitem->id;
            $sortindextohidelist[] = $childitem->sortindex;
            survey_add_regular_item_node($tohidelist, $sortindextohidelist, $newbasicform);
        }
    }
}

/*
 * survey_i_can_read
 * @param $survey, $mygroup, $ownerid
 * @return whether I am allowed to see the survey submitted by the user belonging to $ownergroup
 */
function survey_i_can_read($survey, $mygroup, $ownerid) {
    global $USER, $COURSE;

    switch ($survey->readaccess) {
        case SURVEY_NONE:
            return false;
            break;
        case SURVEY_OWNER:
            return ($USER->id == $ownerid);
            break;
        case SURVEY_GROUP:
            $return = false;
            // $ownergroupid is the group ID of the owner of the submitted survey record
            $ownergroup = groups_get_user_groups($COURSE->id, $ownerid);
            foreach ($ownergroup[0] as $ownergroupid) { // [0] is for all groupings combined
                if (in_array($ownergroupid, $mygroup)) {
                    $return = true;
                    break;
                }
            }
            return $return;
            break;
        case SURVEY_ALL:
            return true;
            break;
        default:
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $survey->readaccess = '.$survey->readaccess);
    }
}

/*
 * survey_i_can_edit
 * @param $survey, $mygroup, $ownerid
 * @return whether I am allowed to edit the survey submitted by the user belonging to $ownergroup
 */
function survey_i_can_edit($survey, $mygroup, $ownerid) {
    global $USER, $COURSE;

    switch ($survey->editaccess) {
        case SURVEY_NONE:
            return false;
            break;
        case SURVEY_OWNER:
            return ($USER->id == $ownerid);
            break;
        case SURVEY_GROUP:
            $return = false;
            // $ownergroupid the group ID of the owner of the submitted survey record
            $ownergroup = groups_get_user_groups($COURSE->id, $ownerid);
            foreach ($ownergroup[0] as $ownergroupid) { // [0] is for all groupings combined
                if (in_array($ownergroupid, $mygroup)) {
                    $return = true;
                    break;
                }
            }
            return $return;
            break;
        case SURVEY_ALL:
            return true;
            break;
        default:
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $survey->editaccess = '.$survey->editaccess);
    }
}

/*
 * survey_i_can_delete
 * @param $survey, $mygroup, $ownerid
 * @return whether I am allowed to delete the survey submitted by the user belonging to $ownergroup
 */
function survey_i_can_delete($survey, $mygroup, $ownerid) {
    global $USER, $COURSE;

    switch ($survey->deleteaccess) {
        case SURVEY_NONE:
            return false;
            break;
        case SURVEY_OWNER:
            return ($USER->id == $ownerid);
            break;
        case SURVEY_GROUP:
            $return = false;
            // $ownergroupid the group ID of the owner of the submitted survey record
            $ownergroup = groups_get_user_groups($COURSE->id, $ownerid);
            foreach ($ownergroup[0] as $ownergroupid) { // [0] is for all groupings combined
                if (in_array($ownergroupid, $mygroup)) {
                    $return = true;
                    break;
                }
            }
            return $return;
            break;
        case SURVEY_ALL:
            return true;
            break;
        default:
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $survey->deleteaccess = '.$survey->deleteaccess);
    }
}


/*
 * survey_get_my_groups
 * @param $cm
 * @return
 */
function survey_get_my_groups($cm) {
    global $USER, $COURSE;

    if (groups_get_activity_groupmode($cm, $COURSE) == SEPARATEGROUPS) {   // Separate groups are being used
        $mygroupslist = groups_get_user_groups($COURSE->id, $USER->id); // this is 0 whether no groups are set
        $mygroups = array();
        foreach ($mygroupslist[0] as $mygroupid) { // [0] is for all groupings combined
            $mygroups[] = $mygroupid;
        }
    } else {
        $mygroups = array();
    }

    return $mygroups;
}

/*
 * survey_find_submissions
 * @param $findparams
 * @return
 */
function survey_find_submissions($findparams) {
    global $DB;

    // echo '$findparams (prima):';
    // var_dump($findparams);

    foreach ($findparams as $itemid => $valuesarray) {
        // I am interested only to non empty fields BUT different from SURVEY_NOANSWERVALUE
        if ($valuesarray == SURVEY_NOANSWERVALUE) {
            unset($findparams[$itemid]);
        } else {
            $findparams[$itemid] = str_replace(SURVEY_URLMULTIVALUESEPARATOR, SURVEY_DBMULTIVALUESEPARATOR, $valuesarray);
        }
    }
    // echo '$findparams (dopo):';
    // var_dump($findparams);
    // die;

    // the search process is tricky
    // the procedure is:
    // step 1:
    //     get the set of submissions matching the first condition
    // step 2:
    //     check the found set for all the other conditions
    //     if at least one condition does not match, delete the submission id from the starting set
    //     Whatever will not be deleted, is the submission matching ALL submitted requests

    // if the search form is empty (has no conditions) return all the submissions
    if (!$findparams) {
        return;
    }

    $keys = array_keys($findparams);
    $firstitemid = $keys[0];
    $firstcontent = $findparams[$firstitemid];

    unset($findparams[$firstitemid]); // drop the first element of $findparams

    // should work but does not: MDL-27629
    // $submissionidlist = $DB->get_records('survey_userdata', array('itemid' => $firstitemid, $DB->sql_compare_text('content') => $firstcontent), 'submissionid');

    $where = 'itemid = :itemid AND '.$DB->sql_compare_text('content').' = :content';
    $params = array('itemid' => $firstitemid, 'content' => (string)$firstcontent);
    if (!$submissionidlist = $DB->get_records_select('survey_userdata', $where, $params, 'submissionid', 'submissionid')) {
        // nessuna submission soddisfa le richieste
        return array();
    } else {
        $submissionidlist = array_keys($submissionidlist); // list of submission id matching the first constraint

    }

    if (!$findparams) {
        // if no more constaints are available, the process is finished
        return $submissionidlist;
    }

    foreach ($findparams as $itemid => $valuesarray) {
        $where = 'submissionid IN ('.implode(',', $submissionidlist).')
                      AND itemid = :itemid
                      AND content = :valuesarray';
        $params = array('itemid' => $itemid, 'content' => (string)$valuesarray);
        if ($submissionidlist = $DB->get_records_select('survey_userdata', $where, $params, 'submissionid', 'submissionid')) {
            $submissionidlist = array_keys($submissionidlist);
        } else {
            // not any submission meets all the constraints
            return array();
        }
    }
    return $submissionidlist;
}

/*
 * survey_add_custom_css
 * @param $surveyid, $cmid
 * @return
 */
function survey_add_custom_css($surveyid, $cmid) {
    global $PAGE;

    $filearea = SURVEY_STYLEFILEAREA;
    $context = context_module::instance($cmid);

    $fs = get_file_storage();
    if ($files = $fs->get_area_files($context->id, 'mod_survey', $filearea, 0, 'sortorder', false)) {
        $PAGE->requires->css('/mod/survey/userstyle.php?id='.$surveyid.'&amp;cmid='.$cmid); // not overridable via themes!
    }
}

/*
 * survey_get_sid_field_content
 * @param $record, $fieldname='content'
 * @return
 */
function survey_get_sid_field_content($record) {
    // this function is the equivalent of the method item_builtin_string_load_support in itembase.class.php
    if (empty($record->externalname)) {
        return $record->content;
    } else {
        // get the string 'content_sid'
        // from surveytemplate_{$this->externalname}.php file
        $stringindex = 'content'.sprintf('%02d', $record->content_sid);
        $return = get_string($stringindex, 'surveytemplate_'.$record->externalname);

        return $return;
    }
}

/*
 * survey_get_downloadformats
 * @param
 * @return
 */
function survey_get_unixtimedownloadformats() {
    $option = array();
    $timenow = time();

    $option[''] = get_string('unixtime', 'survey');
    $option['strftimedate'] = userdate($timenow, get_string('strftimedate', 'core_langconfig'));
    $option['strftimedatefullshort'] = userdate($timenow, get_string('strftimedatefullshort', 'core_langconfig'));
    $option['strftimedateshort'] = userdate($timenow, get_string('strftimedateshort', 'core_langconfig'));
    $option['strftimedatetime'] = userdate($timenow, get_string('strftimedatetime', 'core_langconfig'));
    $option['strftimedatetimeshort'] = userdate($timenow, get_string('strftimedatetimeshort', 'core_langconfig'));
    $option['strftimedaydate'] = userdate($timenow, get_string('strftimedaydate', 'core_langconfig'));
    $option['strftimedaydatetime'] = userdate($timenow, get_string('strftimedaydatetime', 'core_langconfig'));
    $option['strftimedayshort'] = userdate($timenow, get_string('strftimedayshort', 'core_langconfig'));
    $option['strftimedaytime'] = userdate($timenow, get_string('strftimedaytime', 'core_langconfig'));
    $option['strftimemonthyear'] = userdate($timenow, get_string('strftimemonthyear', 'core_langconfig'));
    $option['strftimerecent'] = userdate($timenow, get_string('strftimerecent', 'core_langconfig'));
    $option['strftimerecentfull'] = userdate($timenow, get_string('strftimerecentfull', 'core_langconfig'));
    $option['strftimetime'] = userdate($timenow, get_string('strftimetime', 'core_langconfig'));

    return $option;
}
