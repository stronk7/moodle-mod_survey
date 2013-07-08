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
$context = context_module::instance($cm->id);

$canview = has_capability('mod/survey:view', $context, null, true);
$canpreview = has_capability('mod/survey:preview', $context, null, true);
$cansubmit = has_capability('mod/survey:submit', $context, null, true);
$cansearch = has_capability('mod/survey:searchsubmissions', $context, null, true);
$canexportdata = has_capability('mod/survey:exportdata', $context, null, true);
$canaccessreports = has_capability('mod/survey:accessreports', $context, null, true);
$canmanageitems = has_capability('mod/survey:manageitems', $context, null, true);

$canmanagesubmissions = has_capability('mod/survey:managesubmissions', $context, null, true);

$canmanageusertemplates = has_capability('mod/survey:manageusertemplates', $context, null, true);
$cancreateusertemplates = has_capability('mod/survey:createusertemplates', $context, null, true);
$canimportusertemplates = has_capability('mod/survey:importusertemplates', $context, null, true);
$canapplyusertemplates = has_capability('mod/survey:applyusertemplates', $context, null, true);

$cancreatemastertemplate = has_capability('mod/survey:createmastertemplate', $context, null, true);
$canapplymastertemplate = has_capability('mod/survey:applymastertemplate', $context, null, true);

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
if ($cansubmit) {
    $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
} else {
    $elementurl = new moodle_url('/mod/survey/view_manage.php', $paramurl);
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
if ($cancreatemastertemplate || ((!$hassubmissions || $CFG->survey_forcemodifications) && $canapplymastertemplate)) {
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

        if ($canpreview) { // preview
            $localparamurl = array('id' => $cm->id, 'act' => SURVEY_PREVIEWSURVEY);
            $elementurl = new moodle_url('/mod/survey/view.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage1', 'survey');
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
        }

        if ($canview) { // attempt
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage2', 'survey');
            $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
        }

        // manage data
        if ($canmanagesubmissions) {
            $elementurl = new moodle_url('/mod/survey/view_manage.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage3', 'survey');
            $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
        }

        if ($currentpage == SURVEY_SUBMISSION_EDIT) { // edit
            $localparamurl = array('id' => $cm->id, 'act' => SURVEY_EDITRESPONSE);
            $elementurl = new moodle_url('/mod/survey/view.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage4', 'survey'); // edit
            $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
        }

        if ($currentpage == SURVEY_SUBMISSION_READONLY) { // read only
            $localparamurl = array('id' => $cm->id, 'act' => SURVEY_READONLYRESPONSE);
            $elementurl = new moodle_url('/mod/survey/view.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage5', 'survey'); // read only
            $row[] = new tabobject('idpage5', $elementurl->out(), $strlabel);
        }

        if ($cansearch) { // search
            $elementurl = new moodle_url('/mod/survey/view_search.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage6', 'survey');
            $row[] = new tabobject('idpage6', $elementurl->out(), $strlabel);
        }

        if ($currentpage == SURVEY_SUBMISSION_REPORT) { // report
            if (!empty($canaccessreports)) {
                $elementurl = new moodle_url('/mod/survey/view_report.php', $paramurl);
                $strlabel = get_string('tabsubmissionspage7', 'survey');
                $row[] = new tabobject('idpage7', $elementurl->out(), $strlabel);
            }
        }

        if ($canexportdata) { // export
            $elementurl = new moodle_url('/mod/survey/view_export.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage8', 'survey');
            $row[] = new tabobject('idpage8', $elementurl->out(), $strlabel);
        }

        $tabs[] = $row;

        break;
    case SURVEY_TABITEMS:
        $tabname = get_string('tabitemname', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        if ($canmanageitems) { // manage
            $row = array();
            $elementurl = new moodle_url('/mod/survey/items_manage.php', $paramurl);
            $strlabel = get_string('tabitemspage1', 'survey');
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);

            if (!$hassubmissions || $CFG->survey_forcemodifications) { // add
                $elementurl = new moodle_url('/mod/survey/items_add.php', $paramurl);
                $strlabel = get_string('tabitemspage2', 'survey');
                $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
            }

            if ($currentpage == SURVEY_ITEMS_SETUP) { // setup
                $elementurl = new moodle_url('/mod/survey/items_setup.php', $paramurl);
                $strlabel = get_string('tabitemspage3', 'survey');
                $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
            }

            if ($countparents) { // verify parent child relations
                $elementurl = new moodle_url('/mod/survey/items_validate.php', $paramurl);
                $strlabel = get_string('tabitemspage4', 'survey');
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
            if (!$hassubmissions || $CFG->survey_forcemodifications) { // if submissions were done, do not change the list of fields
                $elementurl = new moodle_url('/mod/survey/utemplates_manage.php', $paramurl); // manage
                $strlabel = get_string('tabutemplatepage1', 'survey');
                $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
            }

            if ($cancreateusertemplates) { // create
                $elementurl = new moodle_url('/mod/survey/utemplates_create.php', $paramurl);
                $strlabel = get_string('tabutemplatepage2', 'survey');
                $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
            }

            if ($canimportusertemplates) { // import
                $elementurl = new moodle_url('/mod/survey/utemplates_import.php', $paramurl);
                $strlabel = get_string('tabutemplatepage3', 'survey');
                $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
            }

            if ( (!$hassubmissions || $CFG->survey_forcemodifications) && $canapplyusertemplates ) { // if submissions were done, do not change the list of fields
                $elementurl = new moodle_url('/mod/survey/utemplates_apply.php', $paramurl); // apply
                $strlabel = get_string('tabutemplatepage4', 'survey');
                $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
            }
        }

        $tabs[] = $row;

        break;
    case SURVEY_TABMTEMPLATES:
        $tabname = get_string('tabmtemplatename', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        $row = array();
        if ($cancreatemastertemplate) { // create
            $elementurl = new moodle_url('/mod/survey/mtemplates_create.php', $paramurl);
            $strlabel = get_string('tabmtemplatepage1', 'survey');
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
        }

        if ( (!$hassubmissions || $CFG->survey_forcemodifications) && $canapplymastertemplate ) { // if submissions were done, do not change the list of fields
            $elementurl = new moodle_url('/mod/survey/mtemplates_apply.php', $paramurl); // apply
            $strlabel = get_string('tabmtemplatepage2', 'survey');
            $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
        }
        $tabs[] = $row;

        //$pageid = $tabname;

        break;
    default:
        print_error('incorrectaccessdetected', 'survey');
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