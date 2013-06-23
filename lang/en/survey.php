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
 * English strings for survey
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Survey';
$string['modulename_help'] = 'Survey allows the creation of custom surveys as far as built in surveys like ATTLS, COLLES and CRITICAL INCIDENTS. You can also save and reuse parts or whole of your own custom survey.';
$string['modulename_link'] = 'mod/survey/view';
$string['modulenameplural'] = 'surveys';
$string['surveyname'] = 'Survey name';
$string['surveyname_help'] = 'Choose the name of this survey.';
$string['survey'] = 'survey';
$string['pluginadministration'] = 'survey administration';
$string['pluginname'] = 'Survey';

$string['tabsubmissionsname'] = 'Survey';
    $string['tabsubmissionspage1'] = 'Preview';
    $string['tabsubmissionspage2'] = 'Attempt';
    $string['tabsubmissionspage3'] = 'Responses';
    $string['tabsubmissionspage4'] = 'Edit';
    $string['tabsubmissionspage5'] = 'Read only';
    $string['tabsubmissionspage6'] = 'Search';
    $string['tabsubmissionspage7'] = 'Reports';
    $string['tabsubmissionspage8'] = 'Export';
$string['tabitemname'] = 'Elements';
    $string['tabitemspage1'] = 'Manage elements';
    $string['tabitemspage2'] = 'Add element';
    $string['tabitemspage3'] = 'Setup element';
    $string['tabitemspage4'] = 'Validate branching';
$string['tabutemplatename'] = 'User templates';
    $string['tabutemplatepage1'] = 'Manage';
    $string['tabutemplatepage2'] = 'Create';
    $string['tabutemplatepage3'] = 'Import';
    $string['tabutemplatepage4'] = 'Apply';
$string['tabmtemplatename'] = 'Master templates';
    $string['tabmtemplatepage1'] = 'Create';
    $string['tabmtemplatepage2'] = 'Apply';

$string['access'] = 'User access to responses';
$string['accessrights_help'] = 'Once a record has been submitted by a user, who is allowed to read it?, who is allowed to edit it?, who is allowed to delete it?';
$string['accessrights'] = 'Advanced permissions';
$string['accessrightsnote_group'] = 'If you plan to dismiss groups, take them off and come here again. The access list will reduce accordingly.';
$string['accessrightsnote_nogroup'] = 'If you plan to use groups, create them and come here again. The access list will change accordingly.';
$string['actionoverother_help'] = 'Operate on elements already present in the survey with the following action';
$string['actionoverother'] = 'Other elements action';
$string['additem'] = 'Choose the element to add to your survey. It can be a question type or a format type.';
$string['advanced_header'] = 'Adv.';
$string['advancededit'] = 'Each non hidden question type element is displayed in the advanced entry form';
$string['advancednoedit'] = 'Not in advanced entry form as hidden';
$string['advancednosearch'] = 'Not in advanced search form';
$string['advancedsearch_help'] = 'Is this element going to be part of the advanced search form?<br />Take care: each not hidden element is always part of the advanced entry form.';
$string['advancedsearch'] = 'Advanced search form';
$string['allsurveysdeleted'] = 'All the attempts of this survey have been successfully deleted';
$string['anonymous_help'] = 'This survey will be totally anonymous. Each infos about submitting user will be deleted';
$string['anonymous'] = 'Anonymous';
$string['answerisnoanswer'] = 'Answer refused';
$string['applymtemplateinfo'] = 'You can enrich your survey applying set of elements taken from a master template plugin';
$string['applytemplate'] = 'Apply template';
$string['applyutemplateinfo'] = 'You can enrich your survey applying set of elements taken from an XML user template<br /><strong>Be warned: by setting "{$a->usertemplate}" to "{$a->none}" and "{$a->actionoverother}" to "{$a->delete}" you bring your survey back to just created state</strong>';
$string['askdeleteallsubmissions'] = 'Are you sure you want delete ALL the stored attempt?';
$string['askdeletemysubmissions'] = 'Are you sure you want delete your attempt created on {$a->timecreated} and modified on {$a->timemodified}?';
$string['askdeletemysubmissionsnevermodified'] = 'Are you sure you want delete your attempt created on {$a->timecreated} and never modified?';
$string['askdeleteoneitem'] = 'Are you sure you want delete the survey element: {$a}';
$string['askdeleteonesurvey'] = 'Are you sure you want delete the selected attempt owned by {$a->fullname}, created on {$a->timecreated} and modified on {$a->timemodified}?';
$string['askdeleteonesurveynevermodified'] = 'Are you sure you want delete the selected attempts owned by {$a->fullname}, created on {$a->timecreated} and never modified?';
$string['askdeleteonetemplate'] = 'Are you sure you want delete the user template "{$a}"';
$string['askitemsshow'] = 'Showing the question type element {$a->lastitem} you are going to show all its ancestors.<br />Ancestors are elements in position: {$a->ancestors}.<br />Do you confirm this action?';
$string['askitemstohide'] = 'Hiding the question type element {$a->parentid} all its dependencies will be hided too.<br />Dependencies is (are) the element(s) in position: {$a->dependencies}.<br />Do you confirm this action?';
$string['availability_fs'] = 'Availability';
$string['basic'] = 'Basic';
$string['basicedit'] = 'In basic entry form';
$string['basicform_help'] = 'Is this element going to be available in the basic entry form? Also in basic search form?';
$string['basicform'] = 'Basic entry form';
$string['basicnoedit'] = 'Not in basic entry form';
$string['basicnosearch'] = 'Not in basic search form';
$string['basicsearch'] = 'In basic search form';
$string['branching_fs'] = 'Branching';
$string['builplugin'] = 'Create master template';
$string['captcha_help'] = 'Add to this collectoin the captcha to increase the security.';
$string['captcha'] = 'Add captcha';
$string['category'] = 'Course category';
$string['chaindeleted'] = 'Element {$a} and depending element(s) have been successfully deleted';
$string['changeorder'] = 'Reorder';
$string['collesactual'] = 'COLLES (Actual)';
$string['collesboth'] = 'COLLES (Preferred and Actual)';
$string['collespreferred'] = 'COLLES (Preferred)';
$string['common_fs'] = 'General settings';
$string['completionsubmit_check'] = 'Student must submit the survey at least ';
$string['completionsubmit_group_help'] = 'This survey is considered completed when the student submit if at least as much as times how it is written here';
$string['completionsubmit_group'] = 'Require submission';
$string['completionsubmit'] = 'questo è il title dell\'help';
$string['confirmallsurveysdeletion'] = 'Yes, delete them all';
$string['confirmitemsdeletion'] = 'Yes, delete them all';
$string['confirmitemsshow'] = 'Yes, show them all';
$string['confirmitemstohide'] = 'Yes, hide them all';
$string['confirmsurveydeletion'] = 'Yes, delete this attempt';
$string['content_editor_err'] = 'The content is mandatory';
$string['content_editor_help'] = 'The content of the element as it will be shown to remote user';
$string['content_editor'] = 'Content';
$string['content_help'] = 'The content to show as readonly text';
$string['content'] = 'Content';
$string['course'] = 'Course';
$string['currenttotemplate'] = 'Save current survey as master template in zip format.<br />To install a master template, unzip it to mod/survey/template/ and visit the notification page.';
$string['customnumber_header'] = '#';
$string['customnumber_help'] = 'Use this field to add custom number to the question type element. It may be a natural number such as 1 or 1.a or what ever you may choose. Take in mind that you are responsible for coherence of that numbers. Because of this take care if you plan to change the order of the elements.';
$string['customnumber'] = 'Element number';
$string['customsurvey'] = 'Custom';
$string['dataentry'] = 'Data survey settings';
$string['defaultthanksmessage'] = 'Thank you. Your survey has been successfully submitted!';
$string['delete'] = 'Delete';
$string['deleteallsubmissions'] = 'Delete all attempts';
$string['deletebreaklinks'] = 'The current question type element has child element(s) that are going to be deleted too. The child element(s) position is: {$a}';
$string['deleteitems'] = 'Delete';
$string['differentavailability'] = 'The availability has to match the parent availability to allow the requested relation';
$string['differentbasicform'] = 'This element is forced to belong to the same user entry form of the the parent question type element.{$a}';
$string['download_advancedonly'] = 'download all fields';
$string['download_usercanfill'] = 'download only fields available in basic entry form';
$string['downloadpdf'] = 'download in pdf';
$string['downloadtocsv'] = 'download to comma separated values';
$string['downloadtotsv'] = 'download to TAB separated values';
$string['downloadtoxls'] = 'download to xls';
$string['downloadtype'] = 'Exported file type';
$string['emptydownload'] = 'The required export has no fields';
$string['emptymaxformpage'] = 'Required max form page (whether $add = true) is missing';
$string['emptysearchform'] = 'No elements were found for this search form.<br />This could be due to elements:<ul><li>still not created;</li><li>not visible;</li><li>not searchable;</li><li>not set to belong to this form.</li></ul>To add an element to the search form use its availability feature.<br />Take care because only searchable questions type elements can be added to the search form.';
$string['enteruniquename'] = 'Please choose a unique name since {$a} already exists in the choosen context';
$string['exporttemplate'] = 'export template';
$string['extranote_help'] = 'Write here a description/note about extra informations the user is supposed to know about this question type element.';
$string['extranote'] = 'Additional note';
$string['extranoteinsearch_descr'] = 'Are user notes needed in the search form?';
$string['extranoteinsearch'] = 'Extra note in search form';
$string['extrarow_help'] = 'Use this option to put the content of the element in a dedicated row just upper the interface to enter the answer. Leaving this option unchecked, the content will be displayed on the left of the elements interface. This extra row is usually needed for questions containing images such as text longer than few words!';
$string['extrarow'] = 'Extra row for question';
$string['extrarowisforced'] = '(Extra row has been forced by the plugin)';
$string['fieldplugin'] = 'Element plugin';
$string['fillinginstructioninsearch_descr'] = 'Are filling instructions needed in the search form?';
$string['fillinginstructioninsearch'] = 'Filling instruction in search form';
$string['findall'] = 'Find all';
$string['forcemodifications_descr'] = 'Allow roles, allowed to manage survey elements, to add new elements to the survey even if responses were already provided for it';
$string['forcemodifications'] = 'Allow answered survey modifcation';
$string['formatplugin'] = 'Format plugin';
$string['free'] = 'free';
$string['gotolist'] = 'Continue to responses list';
$string['hassubmissions_alert'] = 'This survey has already been submitted at least once.<br />Please proceed with extreme caution and make only minimal changes to not compromise the validity of the whole survey.';
$string['hide_help'] = 'Use this option to hide the element. Hided elements will not be available to anyone. You can consider these elements as not part of the survey.';
$string['hide'] = 'Hide';
$string['hidefield'] = 'Hide the element';
$string['hideinstructions_help'] = 'Use this checkbox to show/hide filling instruction for this element';
$string['hideinstructions'] = 'Hide filling instruction';
$string['hideitems'] = 'Hide';
$string['history_help'] = 'Asking for history, user will no longer be able to modify a submitted record but only to edit and save a copy of it, leaving all the history of submitted survey available.';
$string['history'] = 'Preserve history';
$string['ignoreitems'] = 'Ignore';
$string['importfile'] = 'Choose files to import';
$string['includehidden'] = 'Include hidden elements';
$string['incorrectaccessdetected'] = 'Incorrect access detected';
$string['indent_help'] = 'The indent of the element alias the left margin the element will respect once drawn';
$string['indent'] = 'Indent';
$string['invalidformat_err'] = 'Parent format is not valid. It has to follow: {$a}';
$string['invalidtemplate'] = 'File {$a} is an invalid xml file. Please verify it.';
$string['invitationdefault'] = 'Invitation';
$string['isinbasicform'] = '<br />[The choosed parent question type element does belong to basic form]';
$string['isnotinbasicform'] = '<br />[The choosed parent question type element does not belong to basic form]';
$string['item'] = 'Element';
$string['itemaddfail'] = 'The new element has not been added';
$string['itemaddok'] = 'Element has been successfully added';
$string['itemdeleted'] = 'Survey element: {$a} has been successfully deleted';
$string['itemeditfail'] = 'An error occurred saving the element';
$string['itemedithidefrombasicform'] = 'Moving this element out from the basic entry form, some depending elements were forced out of the basic entry form too.';
$string['itemedithidehide'] = 'Hiding this element, some depending elements were hided too.';
$string['itemeditok'] = 'Element successfully modified';
$string['itemeditshow'] = 'Showing this element, some parent elements were showed too.';
$string['itemeditshowinbasicform'] = 'Moving this element in the basic entry form, some depending elements were forced into the basic entry form too.';
$string['itemlinkbroken'] = 'Some parent/child link was deleted because of the change of the target form';
$string['itemlist'] = 'Elements list';
$string['likelast'] = 'Like last attempt';
$string['mastertemplate_help'] = 'Choose the master template you want to add to your survey.';
$string['mastertemplate'] = 'Master templates';
$string['mastertemplatename_help'] = 'Choose the name of the master template name that is going to be downloaded in zip format';
$string['mastertemplatename'] = 'Master template name';
$string['mastertemplates'] = 'master templates';
$string['maxentries_help'] = 'The maximum number of entries a student is allowed to submit for this activity.';
$string['maxentries'] = 'Maximum attempts';
$string['maxinputdelay_descr'] = 'The maximum allowed delay in hours for users to submit a survey. Even if the user is allowed to pause the data entry to restart it later, after the time defined here each partial attempt will be deleted. Default of 168 hours is equivalent to a week.';
$string['maxinputdelay'] = 'Max input delay';
$string['missinganswer'] = '-- missing answer --';
$string['missingparentcontent_err'] = 'You need to specify a parent content otherwise clear the "{$a}" field';
$string['missingparentid_err'] = 'You need to select a question type element to branch the survey. Otherwise clear the "{$a}" field';
$string['module'] = 'This instance of survey';
$string['months'] = 'months';
$string['newpageforchild_help'] = 'Use this option to force a new page after each branching element.';
$string['newpageforchild'] = 'Branching element increases page';
$string['newsubmissionbody'] = 'A new record has been submitted in {$a}';
$string['newsubmissionsubject'] = 'New attempt';
$string['nextformpage'] = 'Next page >>';
$string['noadvanceditemsfound'] = 'No elements were found for this (advanced) entry form.<br />This could be due to elements:<ul><li>still not created;</li><li>not visible.</li></ul> Please have a check and come back again.';
$string['noanswer'] = 'No answer';
$string['nobasicitemsfound'] = 'The survey you are accessing is still a work in progress.<br />Please try access again later.';
$string['nogroupsincourse'] = 'This course has not beed divided into groups';
$string['noitemsfound'] = 'This survey has no elements at the moment. Please proceed to <a href="{$a->href}" title="{$a->title}">add them</a> before coming here again.';
$string['noitemsfoundtitle'] = 'Add elements before calling this page';
$string['nomoreitems'] = 'On the basis of the answers provided, no more question type elements remain to display.<br />Your survey is over. You only need to submit{$a}.';
$string['nomorerecordsallowed'] = 'The maximun number of {$a} attempts was already reached. No more attempts are allowed.';
$string['noreadaccess'] = 'Read acces is not allowed in this survey.';
$string['nosubmissionfound'] = 'No attempt were found for this survey. This report has no use.';
$string['notalloweddefault'] = '"{$a}" is not an allowed default whether the element is set to "required"';
$string['notanswereditem'] = 'Answer not submitted';
$string['notanyset'] = 'none';
$string['note'] = 'Note:';
$string['nothingtodownload'] = 'Nothing to download';
$string['notifymore_help'] = 'Some additional email addresses to notify about new attempts. Addresses are supposed to be one per row.';
$string['notifymore'] = 'More notifications';
$string['notifyrole_help'] = 'Send an email to each component of the selected roles at each attempt. The email will only advise about attempt from the user, not about its content and without sender details.';
$string['notifyrole'] = 'Notify role';
$string['notinbasicform'] = 'hide this element to students';
$string['onemorerecord'] = 'Let me add one more response, please';
$string['onlyoptional'] = 'Optional is forced by the value of default.';
$string['onlyreview'] = ' or review';
$string['overwrite_help'] = 'Selecting this checkbox you will overwrite an older template with the same name. If you leave this checkbox unselected, in case of conflicts, you will be asked for a new unique name.';
$string['overwrite'] = 'Overwrite older template';
$string['pagexofy'] = 'Page {$a->formpage} of {$a->lastformpage}';
$string['parentconstraints'] = 'Parent constraints';
$string['parentcontent_help'] = 'This is what the user is supposed to enter in the parent question type element in order to enable/display this element.';
$string['parentcontent'] = 'Parent content';
$string['parentformat'] = 'Define the content format of the answer as in the following legend: {$a}';
$string['parentid_help'] = 'Parent question type elements allow you to create conditional branching. Dimmed elements in the list identify hidden parent question type elments. Show them to have them available in this list.<br />Elements preceded by an asterisk are supposed to belong ONLY to advanced form.';
$string['parentid_alt'] = 'Parent question type element';
$string['parentid_header'] = 'Relation';
$string['parentid'] = 'Parent element';
$string['pause'] = 'Pause';
$string['plugin_help'] = 'This is the list of available elements. Survey elements are of two types: "field" type and "format" type. Choose the element that better suite your special need.';
$string['plugin'] = 'Element';
$string['pluginname_help'] = 'Write here the name of the survey plugin you are going to create';
$string['plugintype'] = 'Plugin type';
$string['previewmode'] = 'You are in preview mode: buttons for data saving are not supposed to display';
$string['previousformpage'] = '<< Previous  page';
$string['readonly'] = 'Read';
$string['readwrite'] = 'Edit';
$string['relation_status'] = 'Status';
$string['required_help'] = 'Will the user be forced to answer this question type element?';
$string['required'] = 'Required';
$string['responseauthor'] = 'Author: ';
$string['responsetimecreated'] = 'Response sbmitted on: ';
$string['responsetimemodified'] = ', Last modified on: ';
$string['restrictedaccess'] = 'View only allowed access';
$string['revieworpause'] = ', review or pause';
$string['saveasnew'] = 'Save as new';
$string['saveresume_help'] = 'Allow to save a survey in order to resume data entry and submit you survey next time';
$string['saveresume'] = 'Allow Save/Resume';
$string['sharinglevel_help'] = 'Choose at which level your template will be shared with other courses. If you choose "course" this template will be available in this course ONLY, if you choose course category this template will be available ONLY to courses sharing the same course "category" with this course, if you choose "site" this template will be available to each other courses in this platform.';
$string['sharinglevel'] = 'Sharing level';
$string['showfield'] = 'Show the element';
$string['sortindex'] = 'Order';
$string['specializations'] = '{$a} specific settings';
$string['star'] = '*';
$string['startyear_help'] = 'Define the lower year that each question will require';
$string['startyear'] = 'Minimum allowed year';
$string['status'] = 'Survey status';
$string['statusboth'] = 'closed and in progress both';
$string['statusclosed'] = 'closed';
$string['statusinprogress'] = 'in progress';
$string['stopyear_help'] = 'Define the upper year that each question will require';
$string['stopyear'] = 'Maximum allowed year';
$string['submissions'] = 'Attempts';
$string['submissionslist'] = 'Attempts list';
$string['survey:accessadvancedform'] = 'Access advanced form';
$string['survey:accessreports'] = 'Access reports';
$string['survey:addinstance'] = 'Add a new survey activity';
$string['survey:additems'] = 'Add survey elements';
$string['survey:applymastertemplate'] = 'Apply master template';
$string['survey:applymastertemplates'] = 'Apply master templates';
$string['survey:applyusertemplates'] = 'Apply user templates';
$string['survey:createmastertemplate'] = 'Create master template';
$string['survey:createmastertemplates'] = 'Create master templates';
$string['survey:createusertemplates'] = 'Create user templates';
$string['survey:deleteallsubmissions'] = 'Delete all submissions';
$string['survey:deleteusertemplates'] = 'Delete user templates';
$string['survey:exportdata'] = 'Export collected attempt';
$string['survey:exportusertemplates'] = 'Export user templates';
$string['survey:importusertemplates'] = 'Import user templates';
$string['survey:manageallsubmissions'] = 'Manage all atempts';
$string['survey:manageitems'] = 'Manage survey elements';
$string['survey:manageusertemplates'] = 'Manage user templates';
$string['survey:preview'] = 'Preview a survey';
$string['survey:searchsubmissions'] = 'Search attempts';
$string['survey:setupitems'] = 'Setup items';
$string['survey:submissiontopdf'] = 'Download your submission in PDF';
$string['survey:submit'] = 'Submit attempts';
$string['survey:validatebranching'] = 'Validate branching';
$string['survey:view'] = 'View surveys';
$string['surveydeleted'] = 'User attempts have been successfully deleted';
$string['surveyfield'] = 'survey questions';
$string['surveymessage'] = 'Survey type is no longer available because the current one has already been submitted';
$string['switchoptional'] = 'Set the question tye element as optional';
$string['switchrequired'] = 'Set the question tye element as required';
$string['system'] = 'Site';
$string['templatecreateinfo'] = 'Create a user template with the current survey. At any time you can download and share it with other moodle users or restore it to your own server. Be careful to "{$a}" if you want to reuse your templates without downloading and reloading them.';
$string['templateimport'] = 'Import template';
$string['templatelist'] = 'list of available templates';
$string['templatename_help'] = 'Write here the name of the template you are going to create';
$string['templatename'] = 'Template name';
$string['templateplugin'] = 'Template plugin';
$string['thankshtml_help'] = 'The html code of the web page the user will read at each attempt';
$string['thankshtml'] = 'Thanks web page';
$string['timeclose'] = 'Available to';
$string['timecreated'] = 'Created';
$string['timemodified'] = 'Modified';
$string['timeopen'] = 'Available from';
$string['translatedstring'] = '$string[\'{$a->stringindex}\'] = \'English translation of corresponding string from "{$a->userlang}" language file\';';
$string['type'] = 'Type';
$string['typefield'] = 'Question';
$string['typeformat'] = 'Format';
$string['unixtime'] = 'unix time';
$string['unlimited'] = 'Unlimited';
$string['useadvancedpermissions_descr'] = 'Use advanced permissions to allow students to see/edit/delete responses from other students';
$string['useadvancedpermissions'] = 'Use advanced permissions';
$string['user'] = 'User';
$string['usercanceled'] = 'Action canceled by the user';
$string['usercanfill'] = 'display in the basic entry form but not in the search one';
$string['usercansearch'] = 'display in the basic entry and search form both';
$string['userstyle_help'] = 'Add here one or more cascade style sheet (css) you want to apply to this survey';
$string['userstyle'] = 'Custom style sheet';
$string['usertemplate_help'] = 'Choose the user template you want to add to your survey.';
$string['usertemplate'] = 'User templates';
$string['usertemplates'] = 'user templates';
$string['validate'] = 'Start validation';
$string['validation'] = 'Validation options';
$string['validationinfo'] = 'This tool let you verify the reliability of your current survey. If the survey includes parent child relations, this tool checks each relations consistency identifying the bad ones that will never allow child elements to be included within the survey.';
$string['variable_help'] = 'The name of the variable as it will be once downloaded';
$string['variable'] = 'Variable';
$string['wrongrelation'] = '"{$a}" will never match';
$string['xmltemplate_help'] = 'Choose the template you want to download as zip file to share it with other moodle users';
$string['xmltemplate'] = 'Preset to export';
$string['you'] = 'You';
$string['youarenotinagroup'] = 'You do not belong to any of the group in which this course is divided';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
