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

// prevent direct user input
$context = context_module::instance($cm->id);
if ($currenttab == SURVEY_TABITEMS) {
    require_capability('mod/survey:manageitems', $context);
}
if ($currenttab == SURVEY_TABPLUGINS) {
    require_capability('mod/survey:manageplugin', $context);
}

$inactive = null;
$activetwo = null;

// ==> single tab definition
$row = array();

// -----------------------------------------------------------------------------
// row for tabs
// -----------------------------------------------------------------------------
$paramurl = array('id' => $cm->id);

$paramurl['tab'] = SURVEY_TABSUBMISSIONS;
$defaultpage = $canmanageitems ? SURVEY_SUBMISSION_MANAGE : SURVEY_SUBMISSION_NEW;
$paramurl['pag'] = $defaultpage;
$elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
$row[] = new tabobject(SURVEY_TAB1NAME, $elementurl->out(), SURVEY_TAB1NAME);

if ($canmanageitems) {
    $paramurl['tab'] = SURVEY_TABITEMS;
    // leave the decision of the page to the software, do not hardcode it
    unset($paramurl['pag']);
    $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
    $row[] = new tabobject(SURVEY_TAB2NAME, $elementurl->out(), SURVEY_TAB2NAME);
}

$paramurl['tab'] = SURVEY_TABTEMPLATES;
$paramurl['pag'] = SURVEY_TEMPLATES_MANAGE;
$elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
$row[] = new tabobject(SURVEY_TAB3NAME, $elementurl->out(), SURVEY_TAB3NAME);

if ($canmanageplugin) {
    $paramurl['tab'] = SURVEY_TABPLUGINS;
    $paramurl['pag'] = SURVEY_PLUGINS_BUILD;
    $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
    $row[] = new tabobject(SURVEY_TAB4NAME, $elementurl->out(), SURVEY_TAB4NAME);
}

// ==> tab row definition
$tabs = array();
$tabs[] = $row; // Array of tabs. Closes the tab row element definition
                // next tabs element is going to define the pages row


// echo '$currentpage = '.$currentpage.'<br />';
$pageid = 'idpage'.$currentpage;
// $pageid is here because I leave open the door to override it during next switch

// -----------------------------------------------------------------------------
// row for pages
// -----------------------------------------------------------------------------
// $paramurl = array('id' => $cm->id); has already been defined
$paramurl['tab'] = $currenttab;
switch ($currenttab) {
    case SURVEY_TABSUBMISSIONS:
        $tabname = get_string('tabsubmissionsname', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        $row = array();

        if (!empty($canmanageitems)) {
            $paramurl['pag'] = SURVEY_SUBMISSION_EXPLORE;
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage1', 'survey');
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
        }

        $paramurl['pag'] = SURVEY_SUBMISSION_NEW;
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        $strlabel = get_string('tabsubmissionspage2', 'survey');
        $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);

        if ($currentpage == SURVEY_SUBMISSION_EDIT) { // edit form
            $paramurl['pag'] = SURVEY_SUBMISSION_EDIT;
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage3', 'survey');
            $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
        }

        if ($currentpage == SURVEY_SUBMISSION_READONLY) { // read only form
            $paramurl['pag'] = SURVEY_SUBMISSION_READONLY;
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage4', 'survey');
            $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
        }

        $paramurl['pag'] = SURVEY_SUBMISSION_MANAGE;
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        $strlabel = get_string('tabsubmissionspage5', 'survey'); // manage data
        $row[] = new tabobject('idpage5', $elementurl->out(), $strlabel);

        $paramurl['pag'] = SURVEY_SUBMISSION_SEARCH;
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        $strlabel = get_string('tabsubmissionspage6', 'survey'); // search data
        $row[] = new tabobject('idpage6', $elementurl->out(), $strlabel);

        if ($currentpage == SURVEY_SUBMISSION_REPORT) {
            if (!empty($canaccessreports)) {
                $paramurl['pag'] = SURVEY_SUBMISSION_REPORT;
                $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
                $strlabel = get_string('tabsubmissionspage7', 'survey'); // search data
                $row[] = new tabobject('idpage7', $elementurl->out(), $strlabel);
            }
        }

        if (!empty($canexportdata)) {
            $paramurl['pag'] = SURVEY_SUBMISSION_EXPORT;
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage7', 'survey'); // export data
            $row[] = new tabobject('idpage7', $elementurl->out(), $strlabel);
        }

        $tabs[] = $row;

        break;
    case SURVEY_TABITEMS:
        $tabname = get_string('tabitemname', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        $row = array();
        $paramurl['pag'] = SURVEY_ITEMS_MANAGE;
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        $strlabel = get_string('tabitemspage1', 'survey'); // manage
        $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);

        if ($currentpage == SURVEY_ITEMS_REORDER) {
            $paramurl['pag'] = SURVEY_ITEMS_REORDER;
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabitemspage2', 'survey'); // reorder
            $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
        }

        if (!$hassubmissions) {
            $paramurl['pag'] = SURVEY_ITEMS_ADD;
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabitemspage3', 'survey'); // add
            $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
        }

        if ($currentpage == SURVEY_ITEMS_CONFIGURE) {
            $paramurl['pag'] = SURVEY_ITEMS_CONFIGURE;
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabitemspage4', 'survey'); // confifure
            $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
        }

        if (!$hassubmissions) {
            $paramurl['pag'] = SURVEY_ITEMS_ADDSET;
            $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
            $strlabel = get_string('tabitemspage5', 'survey'); // add itemset
            $row[] = new tabobject('idpage5', $elementurl->out(), $strlabel);
        }

        $paramurl['pag'] = SURVEY_ITEMS_VALIDATE;
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        $strlabel = get_string('tabitemspage6', 'survey'); // verify parent child relations
        $row[] = new tabobject('idpage6', $elementurl->out(), $strlabel);

        $tabs[] = $row;

        break;
    case SURVEY_TABTEMPLATES:
        $tabname = get_string('tabtemplatename', 'survey');
        $inactive = array($tabname);
        $activetwo = array($tabname);

        $row = array();
        $paramurl['pag'] = SURVEY_TEMPLATES_MANAGE;
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        $strlabel = get_string('tabtemplatepage1', 'survey'); // build
        $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);

        $paramurl['pag'] = SURVEY_TEMPLATES_BUILD;
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        $strlabel = get_string('tabtemplatepage2', 'survey'); // build
        $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);

        $paramurl['pag'] = SURVEY_TEMPLATES_IMPORT;
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        $strlabel = get_string('tabtemplatepage3', 'survey'); // manage
        $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);

        $tabs[] = $row;

        break;
    case SURVEY_TABPLUGINS:
        $tabname = get_string('tabpluginsname', 'survey');
        $inactive = null;
        $activetwo = null;

        $pageid = $tabname;

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