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


defined('MOODLE_INTERNAL') OR die();

switch ($currenttab) {
    case SURVEY_TABSUBMISSIONS:
        switch ($currentpage) {
            case SURVEY_SUBMISSION_NEW: // new
                include_once($CFG->dirroot.'/mod/survey/pages/submissions/submissions.php');
                break;
            case SURVEY_SUBMISSION_EDIT: // edit
                include_once($CFG->dirroot.'/mod/survey/pages/submissions/submissions.php');
                break;
            case SURVEY_SUBMISSION_READONLY: // readonly
                include_once($CFG->dirroot.'/mod/survey/pages/submissions/readonly.php');
                break;
            case SURVEY_SUBMISSION_MANAGE: // manage
                include_once($CFG->dirroot.'/mod/survey/pages/submissions/managesubmissions.php');
                break;
            case SURVEY_SUBMISSION_SEARCH: // search
                include_once($CFG->dirroot.'/mod/survey/pages/submissions/search.php');
                break;
            case SURVEY_SUBMISSION_EXPORT: // export
                include_once($CFG->dirroot.'/mod/survey/pages/submissions/export.php');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $currentpage = '.$currentpage.'<br />';
                echo 'and the right "case" is missing<br />';
        }
        break;
    case SURVEY_TABITEMS:
        switch ($currentpage) {
            case SURVEY_ITEMS_MANAGE: // Manage
            case SURVEY_ITEMS_REORDER: // Reorder
                include_once($CFG->dirroot.'/mod/survey/pages/items/manageitems.php');
                break;
            case SURVEY_ITEMS_ADD: // Add
                include_once($CFG->dirroot.'/mod/survey/pages/items/itemtype.php');
                break;
            case SURVEY_ITEMS_CONFIGURE: // Configure
                include_once($CFG->dirroot.'/mod/survey/itembase.php');
                break;
            case SURVEY_ITEMS_ADDSET: // add itemset
                include_once($CFG->dirroot.'/mod/survey/pages/items/addset.php');
                break;
            case SURVEY_ITEMS_VALIDATE: // Check
                include_once($CFG->dirroot.'/mod/survey/pages/items/validation.php');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $currentpage = '.$currentpage.'<br />';
                echo 'and the right "case" is missing<br />';
        }
        break;
    case SURVEY_TABTEMPLATES:
        switch ($currentpage) {
            case SURVEY_TEMPLATES_MANAGE: // Manage
                include_once($CFG->dirroot.'/mod/survey/pages/templates/managetemplate.php');
                break;
            case SURVEY_TEMPLATES_BUILD: // Build
                include_once($CFG->dirroot.'/mod/survey/pages/templates/createtemplate.php');
                break;
            case SURVEY_TEMPLATES_IMPORT: // Import
                include_once($CFG->dirroot.'/mod/survey/pages/templates/importtemplate.php');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $currentpage = '.$currentpage.'<br />';
                echo 'and the right "case" is missing<br />';
        }
        break;
    case SURVEY_TABPLUGINS:
        include_once($CFG->dirroot.'/mod/survey/pages/plugins/pluginbuild.php');
        break;
    default:
        echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
        echo 'I have $currenttab = '.$currenttab.'<br />';
        echo 'and the right "case" is missing<br />';
}
