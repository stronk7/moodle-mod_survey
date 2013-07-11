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
 * has_extrapermission
 * advancedpermissions
 * @param $survey, $mygroups, $ownerid
 * @return whether I am allowed to see the survey submitted by the user belonging to $ownergroup
 */
function has_extrapermission($extrapermission, $survey, $mygroups, $ownerid) {
    global $USER, $COURSE;

    if (!in_array($extrapermission, array('read', 'edit', 'delete'))) {
        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $extrapermission = '.$extrapermission);
    }
    switch ($survey->{$extrapermission.'access'}) {
        case SURVEY_NONE:
            return false;
            break;
        case SURVEY_OWNER:
            return ($USER->id == $ownerid);
            break;
        case SURVEY_GROUP:
            $return = false;
            // $ownergroupid is the ID of the group of the owner of the submitted survey record
            $ownergroup = groups_get_user_groups($COURSE->id, $ownerid);
            foreach ($ownergroup[0] as $ownergroupid) { // [0] is for all groupings combined
                if (in_array($ownergroupid, $mygroups)) {
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
 * survey_get_my_groups
 * @param $cm
 * @return
 */
function survey_get_my_groups($cm) {
    if (groups_get_activity_groupmode($cm) == SEPARATEGROUPS) {   // Separate groups are being used
        $mygroupslist = groups_get_user_groups($cm->course); // this is 0 whether no groups are set
        return $mygroupslist[0]; // [0] is for all groupings combined
    } else {
        return array();
    }
}

/*
 * survey_get_sid_field_content
 * @param $record
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
 * survey_need_group_filtering
 * this function answer the question: do I Need to filter group in my next task?
 * @param
 * @return
 */
function survey_need_group_filtering($cm, $context) {
    // do I need to filter groups?
    $groupmode = groups_get_activity_groupmode($cm);
    $mygroups = survey_get_my_groups($cm);

    $filtergroups = true;
    $filtergroups = $filtergroups && ($groupmode == SEPARATEGROUPS);
    $filtergroups = $filtergroups && (count($mygroups));
    $filtergroups = $filtergroups && (!has_capability('moodle/site:accessallgroups', $context));

    return $filtergroups;
}