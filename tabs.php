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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// do not prevent direct user input
// prevention is done in each working page according to actions

$hassubmissions = survey_has_submissions($survey->id);

$canpreview = survey_user_can_preview($cm);
$cansubmit = survey_user_can_submit($cm);
$cansearch = survey_user_can_search($cm);
$canexportdata = survey_user_can_export_data($cm);
$canaccessreports = survey_user_can_access_reports($cm);
$canmanageitems = survey_user_can_manage_items($cm);

$canmanageusertemplates = survey_user_can_manage_user_templates($cm);
$cancreateusertemplates = survey_user_can_create_user_templates($cm);
$canimportusertemplates = survey_user_can_import_user_templates($cm);
$canapplyusertemplates = survey_user_can_apply_user_templates($cm);

$cancreatemastertemplate = survey_user_can_create_master_templates($cm);
$canapplymastertemplate = survey_user_can_apply_master_templates($cm);

$whereparams = array('surveyid' => $survey->id);
$countparents = $DB->count_records_select('survey_item', 'surveyid = :surveyid AND parentid <> 0', $whereparams);

$inactive = null;
$activetwo = null;

// ==> single tab definition
$row = array();

/*
 * **********************************************
 * TABS
 * **********************************************
 */
$paramurl = array('id' => $cm->id);

// -----------------------------------------------------------------------------
// TAB SURVEY
// -----------------------------------------------------------------------------
if ($canmanageitems) {
    $elementurl = new moodle_url('/mod/survey/view_manage.php', $paramurl);
} else {
    $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
}
$row[] = new tabobject(SURVEY_TAB1NAME, $elementurl->out(), SURVEY_TAB1NAME);

// -----------------------------------------------------------------------------
// TAB ITEMS
// -----------------------------------------------------------------------------
if ($canmanageitems) {
    $itemcount = $DB->count_records('survey_item', array('surveyid' => $survey->id));
    if ($itemcount) {
        $elementurl = new moodle_url('/mod/survey/items_manage.php', $paramurl);
    } else {
        $elementurl = new moodle_url('/mod/survey/items_add.php', $paramurl);
    }
    $row[] = new tabobject(SURVEY_TAB2NAME, $elementurl->out(), SURVEY_TAB2NAME);
}

// -----------------------------------------------------------------------------
// TAB USER TEMPLATES
// -----------------------------------------------------------------------------
if ($canmanageusertemplates) {
    $elementurl = new moodle_url('/mod/survey/utemplates_create.php', $paramurl);
    $row[] = new tabobject(SURVEY_TAB3NAME, $elementurl->out(), SURVEY_TAB3NAME);
}

// -----------------------------------------------------------------------------
// TAB MASTER TEMPLATES
// -----------------------------------------------------------------------------
if ($cancreatemastertemplate || (!$hassubmissions && $canapplymastertemplate)) {
    $elementurl = new moodle_url('/mod/survey/mtemplates_create.php', $paramurl);
    $row[] = new tabobject(SURVEY_TAB4NAME, $elementurl->out(), SURVEY_TAB4NAME);
}

// -----------------------------------------------------------------------------
// ==> tab row definition
// -----------------------------------------------------------------------------
$tabs = array();
$tabs[] = $row; // Array of tabs. Closes the tab row element definition
                // next tabs element is going to define the pages row


// echo '$currentpage = '.$currentpage.'<br />';
$pageid = 'idpage'.$currentpage;
// $pageid is here because I leave open the door to override it during next switch

/*
 * **********************************************
 * PAGES
 * **********************************************
 */
switch ($currenttab) {
    case SURVEY_TABSUBMISSIONS:
        $tabname = get_string('tabsubmissionsname', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        $row = array();

        if (!empty($canpreview)) {
            $localparamurl = array('id' => $cm->id, 'act' => SURVEY_PREVIEWSURVEY);
            $elementurl = new moodle_url('/mod/survey/view.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage1', 'survey'); // preview
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
        }

        if (!empty($cansubmit)) {
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage2', 'survey'); // new
            $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
        }

        $elementurl = new moodle_url('/mod/survey/view_manage.php', $paramurl);
        $strlabel = get_string('tabsubmissionspage3', 'survey'); // manage data
        $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);

        if ($currentpage == SURVEY_SUBMISSION_EDIT) { // edit form
            $localparamurl = array('id' => $cm->id, 'act' => SURVEY_EDITRESPONSE);
            $elementurl = new moodle_url('/mod/survey/view.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage4', 'survey'); // edit
            $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
        }

        if ($currentpage == SURVEY_SUBMISSION_READONLY) { // read only form
            $localparamurl = array('id' => $cm->id, 'act' => SURVEY_READONLYRESPONSE);
            $elementurl = new moodle_url('/mod/survey/view.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage5', 'survey'); // read only
            $row[] = new tabobject('idpage5', $elementurl->out(), $strlabel);
        }

        if (!empty($cansearch)) {
            $elementurl = new moodle_url('/mod/survey/view_search.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage6', 'survey'); // search data
            $row[] = new tabobject('idpage6', $elementurl->out(), $strlabel);
        }

        if ($currentpage == SURVEY_SUBMISSION_REPORT) {
            if (!empty($canaccessreports)) {
                $elementurl = new moodle_url('/mod/survey/view_report.php', $paramurl);
                $strlabel = get_string('tabsubmissionspage7', 'survey'); // report
                $row[] = new tabobject('idpage7', $elementurl->out(), $strlabel);
            }
        }

        if (!empty($canexportdata)) {
            $elementurl = new moodle_url('/mod/survey/view_export.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage8', 'survey'); // export data
            $row[] = new tabobject('idpage8', $elementurl->out(), $strlabel);
        }

        $tabs[] = $row;

        break;
    case SURVEY_TABITEMS:
        $tabname = get_string('tabitemname', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        if ($canmanageitems) {
            $row = array();
            $elementurl = new moodle_url('/mod/survey/items_manage.php', $paramurl);
            $strlabel = get_string('tabitemspage1', 'survey'); // manage
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);

            if (!$hassubmissions) {
                $elementurl = new moodle_url('/mod/survey/items_add.php', $paramurl);
                $strlabel = get_string('tabitemspage2', 'survey'); // add
                $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
            }

            if ($currentpage == SURVEY_ITEMS_SETUP) {
                $elementurl = new moodle_url('/mod/survey/items_setup.php', $paramurl);
                $strlabel = get_string('tabitemspage3', 'survey'); // setup
                $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
            }

            if ($countparents) {
                $elementurl = new moodle_url('/mod/survey/items_validate.php', $paramurl);
                $strlabel = get_string('tabitemspage4', 'survey'); // verify parent child relations
                $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
            }
        }

        $tabs[] = $row;

        break;
    case SURVEY_TABUTEMPLATES:
        $tabname = get_string('tabutemplatename', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        if ($canmanageusertemplates) {
            $row = array();
            if (!$hassubmissions) { // if submissions were done, do not change the list of fields
                $elementurl = new moodle_url('/mod/survey/utemplates_manage.php', $paramurl);
                $strlabel = get_string('tabutemplatepage1', 'survey'); // manage
                $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
            }

            if ($cancreateusertemplates) {
                $elementurl = new moodle_url('/mod/survey/utemplates_create.php', $paramurl);
                $strlabel = get_string('tabutemplatepage2', 'survey'); // create
                $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
            }

            if ($canimportusertemplates) {
                $elementurl = new moodle_url('/mod/survey/utemplates_import.php', $paramurl);
                $strlabel = get_string('tabutemplatepage3', 'survey'); // import
                $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
            }

            if (!$hassubmissions && $canapplyusertemplates) { // if submissions were done, do not change the list of fields
                $elementurl = new moodle_url('/mod/survey/utemplates_apply.php', $paramurl);
                $strlabel = get_string('tabutemplatepage4', 'survey'); // apply
                $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
            }
        }

        $tabs[] = $row;

        break;
    case SURVEY_TABMTEMPLATES:
        $tabname = get_string('tabmtemplatename', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        if ($cancreatemastertemplate || (!$hassubmissions && $canapplymastertemplate)) {
            if ($cancreatemastertemplate) {
                $row = array();
                $elementurl = new moodle_url('/mod/survey/mtemplates_create.php', $paramurl);
                $strlabel = get_string('tabmtemplatepage1', 'survey'); // create
                $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
            }

            if (!$hassubmissions && $canapplymastertemplate) { // if submissions were done, do not change the list of fields
                $elementurl = new moodle_url('/mod/survey/mtemplates_apply.php', $paramurl);
                $strlabel = get_string('tabmtemplatepage2', 'survey'); // apply
                $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
            }
        }
        $tabs[] = $row;

        //$pageid = $tabname;

        break;
    default:
        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $currenttab = '.$currenttab);
}

// echo '$tabs:';
// var_dump($tabs);
//
// echo '$pageid:';
// var_dump($pageid);
//
// echo '$inactive:';
// var_dump($inactive);
//
// echo '$activetwo:';
// var_dump($activetwo);

print_tabs($tabs, $pageid, $inactive, $activetwo);