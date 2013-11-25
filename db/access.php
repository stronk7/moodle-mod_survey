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
 * Capability definitions for the survey module
 *
 * The capabilities are loaded into the database table when the module is
 * installed or updated. Whenever the capability definitions are updated,
 * the module version number should be bumped up.
 *
 * The system has four possible values for a capability:
 * CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
 *
 * It is important that capability names are unique. The naming convention
 * for capabilities that are specific to modules and blocks is as follows:
 *   [mod/block]/<plugin_name>:<capabilityname>
 *
 * component_name should be the same as the directory name of the mod or block.
 *
 * Core moodle capabilities are defined thus:
 *    moodle/<capabilityclass>:<capabilityname>
 *
 * Examples: mod/forum:viewpost
 *           block/recent_activity:view
 *           moodle/site:deleteuser
 *
 * The variable name for the capability definitions array is $capabilities
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
Let's start with a summary:.
It follows the list of TABS detailed with corresponding sub-tabs and php file name.
For each sub-tab, I would define a capability at first but, I will find, sometimes it is useless.

// -----------------------------------------------------------------------------
// TWO MODULE GENERAL CAPABILITIES
// -----------------------------------------------------------------------------
    mod/survey:addinstance
    mod/survey:view

// -----------------------------------------------------------------------------
// TAB SURVEY
// -----------------------------------------------------------------------------
    SUB-TAB == SURVEY_ITEMS_PREVIEW
        $elementurl = new moodle_url('/mod/survey/view.php', $localparamurl);
        mod/survey:preview

    SUB-TAB == SURVEY_SUBMISSION_ATTEMPT
        $elementurl = new moodle_url('/mod/survey/view.php', $paramurl);
        mod/survey:view
        mod/survey:accessadvanceditems
        mod/survey:submit

    SUB-TAB == SURVEY_SUBMISSION_MANAGE
        $elementurl = new moodle_url('/mod/survey/view_manage.php', $paramurl);
        mod/survey:manageallsubmissions
        mod/survey:seegroupsubmissions
        mod/survey:editgroupsubmissions
        mod/survey:deletegroupsubmissions
        mod/survey:managesubmissions <-- Guest is not allowed to manage submissions because they are really anonymous

    SUB-TAB == SURVEY_SUBMISSION_EDIT
    SUB-TAB == SURVEY_SUBMISSION_READONLY
        $elementurl = new moodle_url('/mod/survey/view.php', $localparamurl);
        mod/survey:readsubmissions   <-- USELESS I think.
                                         Each user is allowed to manage submissions.
                                         At worst none depending on general module advanced permissions.
        mod/survey:editsubmissions   <-- USELESS I think.
                                         Each user is allowed to manage submissions.
                                         At worst none depending on general module advanced permissions.
        mod/survey:deletesubmissions <-- USELESS I think.
                                         Each user is allowed to manage submissions.
                                         At worst none depending on general module advanced permissions.
        mod/survey:submissiontopdf

    SUB-TAB == SURVEY_SUBMISSION_SEARCH
        $elementurl = new moodle_url('/mod/survey/view_search.php', $paramurl);
        mod/survey:searchsubmissions

    SUB-TAB == SURVEY_SUBMISSION_REPORT
        $elementurl = new moodle_url('/mod/survey/view_report.php', $paramurl);
        mod/survey:accessreports

    SUB-TAB == SURVEY_SUBMISSION_EXPORT
        $elementurl = new moodle_url('/mod/survey/view_export.php', $paramurl);
        mod/survey:exportdata

// -----------------------------------------------------------------------------
// TAB ELEMENTS
// -----------------------------------------------------------------------------
    SUB-TAB == SURVEY_ITEMS_MANAGE
        $elementurl = new moodle_url('/mod/survey/items_manage.php', $localparamurl);
        mod/survey:manageitems
        mod/survey:additems

    SUB-TAB == SURVEY_ITEMS_SETUP
        $elementurl = new moodle_url('/mod/survey/items_setup.php', $localparamurl);
        mod/survey:setupitems        <-- USELESS I think.
                                         Each user allowed to add items,
                                         should be allowed to validate branching too.

    SUB-TAB == SURVEY_ITEMS_VALIDATE
        $elementurl = new moodle_url('/mod/survey/items_validate.php', $localparamurl);
        mod/survey:validatebranching <-- USELESS I think.
                                         Each user allowed to add items,
                                         should be allowed to validate branching too.

// -----------------------------------------------------------------------------
// TAB USER TEMPLATES
// -----------------------------------------------------------------------------
    SUB-TAB == SURVEY_UTEMPLATES_MANAGE
        $elementurl = new moodle_url('/mod/survey/utemplates_manage.php', $localparamurl);
        mod/survey:manageusertemplates
        mod/survey:deleteusertemplates
        mod/survey:downloadusertemplates

    SUB-TAB == SURVEY_UTEMPLATES_BUILD
        $elementurl = new moodle_url('/mod/survey/utemplates_create.php', $localparamurl);
        mod/survey:saveusertemplates @ CONTEXT_COURSE

    SUB-TAB == SURVEY_UTEMPLATES_IMPORT
        $elementurl = new moodle_url('/mod/survey/utemplates_import.php', $localparamurl);
        mod/survey:importusertemplates

    SUB-TAB == SURVEY_UTEMPLATES_APPLY
        $elementurl = new moodle_url('/mod/survey/utemplates_apply.php', $localparamurl);
        mod/survey:applyusertemplates

// -----------------------------------------------------------------------------
// TAB MASTER TEMPLATES
// -----------------------------------------------------------------------------
    SUB-TAB == SURVEY_MTEMPLATES_BUILD
        $elementurl = new moodle_url('/mod/survey/mtemplates_create.php', $localparamurl);
        mod/survey:savemastertemplate

    SUB-TAB == SURVEY_MTEMPLATES_APPLY
        $elementurl = new moodle_url('/mod/survey/mtemplates_apply.php', $localparamurl);
        mod/survey:applymastertemplate

*/

$capabilities = array(
    'mod/survey:addinstance' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),

    'mod/survey:view' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'guest' => CAP_ALLOW,
            'frontpage' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/survey:preview' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/survey:accessadvanceditems' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/survey:submit' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'frontpage' => CAP_ALLOW,
            'student' => CAP_ALLOW
        )
    ),

    'mod/survey:manageallsubmissions' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:seegroupsubmissions' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:editgroupsubmissions' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:deletegroupsubmissions' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:managesubmissions' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'frontpage' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:deleteallsubmissions' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:submissiontopdf' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/survey:searchsubmissions' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/survey:accessreports' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:accessownreports' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
        )
    ),

    'mod/survey:exportdata' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:manageitems' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:additems' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:setupitems' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:manageusertemplates' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:deleteusertemplates' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:downloadusertemplates' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:saveusertemplates' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:importusertemplates' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:applyusertemplates' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:savemastertemplate' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/survey:applymastertemplate' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

);

