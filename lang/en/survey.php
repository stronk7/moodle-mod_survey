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
    $string['tabsubmissionspage3'] = 'Edit';
    $string['tabsubmissionspage4'] = 'Read only';
    $string['tabsubmissionspage5'] = 'Responses';
    $string['tabsubmissionspage6'] = 'Search';
    $string['tabsubmissionspage7'] = 'Reports';
    $string['tabsubmissionspage8'] = 'Export responses';
$string['tabitemname'] = 'Elements';
    $string['tabitemspage1'] = 'Manage elements';
    $string['tabitemspage2'] = 'Reorder elements';
    $string['tabitemspage3'] = 'Add element';
    $string['tabitemspage4'] = 'Configure element';
    $string['tabitemspage5'] = 'Apply templates';
    $string['tabitemspage6'] = 'Validate branching';
$string['tabtemplatename'] = 'User templates';
    $string['tabtemplatepage1'] = 'Manage templates';
    $string['tabtemplatepage2'] = 'Create as template';
    $string['tabtemplatepage3'] = 'Import templates';
$string['tabpluginsname'] = 'Master templates';

$string['access'] = 'User access to responses';
$string['accessrights_help'] = 'Once a record has been submitted by a user, who is allowed to read it?, who is allowed to edit it?, who is allowed to delete it?';
$string['accessrights'] = 'Advanced permissions';
$string['accessrightsnote_group'] = 'If you plan to dismiss groups, take them off and come here again. The access list will reduce accordingly.';
$string['accessrightsnote_nogroup'] = 'If you plan to use groups, create them and come here again. The access list will change accordingly.';
$string['actionoverother_help'] = 'Operate on questions already present in the survey with the following action';
$string['actionoverother'] = 'Other questions action';
$string['additem'] = 'Choose the element to add to your survey. It can be a question type or a format type.';
$string['additemsetinfo'] = 'You can enrich your survey adding set of questions coming from:<ul><li>{$a->mastertemplates}</li><li>{$a->usertemplates}.</li></ul> If one of this two template types (or both) are missing in the list below this is probably because you have no availability of them. To create a "{$a->mastertemplates}" visit the {$a->pluginstab} tab, to create a "{$a->usertemplates}" go to {$a->tabtemplatename} > {$a->exporttemplate}.<br /><br /><strong>Be warned: by setting "{$a->itemset}" to "{$a->none}" and "{$a->actionoverother}" to "{$a->delete}" you bring your survey back to just created state</strong>';
$string['advanced_header'] = 'Adv.';
$string['advancededit'] = 'Each non hidden question is displayed in the advanced entry form';
$string['advancednoedit'] = 'Not in advanced entry form as hidden';
$string['advancednosearch'] = 'Not in advanced search form';
$string['advancedsearch_help'] = 'Is this item going to be part of the advanced search form?<br />Take care: each not hidden question is always part of the advanced entry form.';
$string['advancedsearch'] = 'Advanced search form';
$string['allsurveysdeleted'] = 'All the attempts of this survey have been successfully deleted';
$string['anonymous_help'] = 'This survey will be totally anonymous. Each infos about submitting user will be deleted';
$string['anonymous'] = 'Anonymous';
$string['applytemplate'] = 'Apply template';
$string['askdeleteallsurveys'] = 'Are you sure you want delete ALL the stored surveys?';
$string['askdeletemysurvey'] = 'Are you sure you want delete your attempt created on {$a->timecreated} and modified on {$a->timemodified}?';
$string['askdeletemysurveynevermodified'] = 'Are you sure you want delete your attempt created on {$a->timecreated} and never modified?';
$string['askdeleteoneitem'] = 'Are you sure you want delete the survey element: {$a}';
$string['askdeleteonesurvey'] = 'Are you sure you want delete the selected survey owned by {$a->fullname}, created on {$a->timecreated} and modified on {$a->timemodified}?';
$string['askdeleteonesurveynevermodified'] = 'Are you sure you want delete the selected survey owned by {$a->fullname}, created on {$a->timecreated} and never modified?';
$string['askdeleteonetemplate'] = 'Are you sure you want delete the selected template';
$string['askitemsshow'] = 'Showing the question {$a->lastitem} you are going to show all its ancestors.<br />Ancestors are questions in position: {$a->ancestors}.<br />Do you confirm this action?';
$string['askitemstohide'] = 'Hiding the question {$a->parentid} all its dependencies will be hided too.<br />Dependencies is/are the question/s in position: {$a->dependencies}.<br />Do you confirm this action?';
$string['basic'] = 'Basic';
$string['availability_fs'] = 'Availability';
$string['basicform_help'] = 'Is this question going to be available in the basic entry form? Also in basic search form? Advanced form will hold this question unless it is set for advanced form only.';
$string['basicform'] = 'Basic entry form';
$string['branching_fs'] = 'Branching';
$string['builplugin'] = 'Create master template';
$string['captcha_help'] = 'Add to this collectoin the captcha to increase the security.';
$string['captcha'] = 'Add captcha';
$string['category'] = 'Course category';
$string['chaindeleted'] = 'Question {$a} and some depending questions have been successfully deleted';
$string['changeorder'] = 'Reorder';
$string['collesactual'] = 'COLLES (Actual)';
$string['collesboth'] = 'COLLES (Preferred and Actual)';
$string['collespreferred'] = 'COLLES (Preferred)';
$string['common_fs'] = 'General settings';
$string['confirmallsurveysdeletion'] = 'Yes, delete them all';
$string['confirmitemsdeletion'] = 'Yes, delete them all';
$string['confirmitemsshow'] = 'Yes, show them all';
$string['confirmitemstohide'] = 'Yes, hide them all';
$string['confirmsurveydeletion'] = 'Yes, delete this attempt';
$string['content_editor_err'] = 'The content is mandatory';
$string['content_editor_help'] = 'The content of the question as it will be shown to remote user';
$string['content_editor'] = 'Content';
$string['content_help'] = 'The content to show as readonly text';
$string['content'] = 'Content';
$string['course'] = 'Course';
$string['currenttotemplate'] = 'Save current survey as master template in zip format.<br />To install a master template, unzip it to mod/survey/template/ and visit the notification page.';
$string['customnumber_header'] = '#';
$string['customnumber_help'] = 'Use this field to add custom number to the question. It may be a natural number such as 1 or 1.a or what ever you may choose. Take in mind that you are responsible for coherence of that numbers. Because of this take care if you plan to change the order of the questions.';
$string['customnumber'] = 'Question number';
$string['customsurvey'] = 'Custom';
$string['dataentry'] = 'Data survey settings';
$string['defaultthanksmessage'] = 'Thank you. Your survey has been successfully submitted!';
$string['delete'] = 'Delete';
$string['deleteallsubmissions'] = 'Delete all attempts';
$string['deletebreaklinks'] = 'The current question has child question(s) that are going to be deleted too. The child question(s) position is: {$a}';
$string['deleteitems'] = 'Delete';
$string['differentavailability'] = 'The availability has to match the parent availability to allow the requested relation';
$string['differentbasicform'] = 'This question is forced to belong to the same user entry form of the the parent question.{$a}';
$string['download_advancedonly'] = 'download all fields';
$string['download_usercanfill'] = 'download only fields available in basic entry form';
$string['downloadtocsv'] = 'download to csv';
$string['downloadtoxls'] = 'download to xls';
$string['downloadtype'] = 'Exported file type';
$string['emptydownload'] = 'The required export has no fields';
$string['emptymaxformpage'] = 'Required max form page (whether $add = true) is missing';
$string['emptysearchform'] = 'No questions were found for this search form.<br />This could be due to questions:<ul><li>still not created;</li><li>not visible;</li><li>not set to belong to this form.</li></ul>To add a question to the search form use its availability feature.<br />Take care because only searchable questions can be defined as part of the search form.';
$string['enteruniquename'] = 'Please choose a unique name since {$a} already exists in the choosen context';
$string['exporttemplate'] = 'export template';
$string['extrarow_help'] = 'Use this option to put the content of the item in a dedicated row just upper the elements to enter the answer. Leaving this option untouched, the content will be displayed on the left of the elements to enter the answer. This extra row is usually needed for questions containing images such as text longer than a short line!';
$string['extrarow'] = 'Extra row for question';
$string['extrarowisforced'] = '(Extra row has been forced by the plugin)';
$string['fieldname_help'] = 'The name of the variable as it will be once downloaded';
$string['fieldname'] = 'Variable name';
$string['fieldplugin'] = 'Question plugin';
$string['findall'] = 'Find all';
$string['formatplugin'] = 'Format plugin';
$string['free'] = 'free';
$string['gotolist'] = 'Continue to responses list';
$string['fillinginstructioninsearch_descr'] = 'Are filling instructions needed in the search form?';
$string['fillinginstructioninsearch'] = 'Filling instruction in search form';
$string['hassubmissions'] = 'This survey has already been submitted so only minimum changes are allowed for existing items and new ones are definitly not allowed';
$string['hide_help'] = 'Use this option to hide the question. Hided questions will not be available to anyone. You can consider them as not part of the survey.';
$string['hide'] = 'Hide';
$string['hidefield'] = 'Hide the question';
$string['hideitems'] = 'Hide';
$string['history_help'] = 'Asking for history, user will no longer be able to modify a submitted record but only to edit and save a copy of it, leaving all the history of submitted survey available.';
$string['history'] = 'Preserve history';
$string['ignoreitems'] = 'Ignore';
$string['importfile'] = 'Choose files to import';
$string['includehide'] = 'Include hidden questions';
$string['indent_help'] = 'The indent of the question alias the left margin the question will respect once drawn';
$string['indent'] = 'Indent';
$string['invalidformat_err'] = 'Parent format is not valid. It has to follow: {$a}';
$string['invalidtemplate'] = 'File {$a} is an invalid xml file. Please verify it.';
$string['invitationdefault'] = 'Invitation';
$string['isinbasicform'] = '<br />[The choosed parent question does belong to basic form]';
$string['isnotinbasicform'] = '<br />[The choosed parent question does not belong to basic form]';
$string['item'] = 'Question';
$string['itemaddfail'] = 'The new question has not been added';
$string['itemaddok'] = 'Element has been successfully added';
$string['itemdeleted'] = 'Survey element: {$a} has been successfully deleted';
$string['itemeditfail'] = 'An error occurred saving the question';
$string['itemedithidefrombasicform'] = 'Moving this question out from the user entry form, some depending questions were forced out of the user entry form too.';
$string['itemedithidehide'] = 'Hiding this question, some depending questions were hided too.';
$string['itemeditok'] = 'Question successfully modified';
$string['itemeditshow'] = 'Showing this question, some parent questions were showed too.';
$string['itemeditshowinbasicform'] = 'Moving this question in the basic entry form, some depending items were forced into the basic entry form too.';
$string['itemlinkbroken'] = 'Some parent/child link was deleted because of the change of the target form';
$string['itemlist'] = 'Question list';
$string['itemset_help'] = 'Choose the question set you want to add to your survey. It can be a master template or a user template';
$string['itemset'] = 'Question set';
$string['label_help'] = 'the label identifying the question';
$string['label'] = 'Label';
$string['likelast'] = 'Like last attempt';
$string['mastertemplatename_help'] = 'Choose the name of the master template name that is going to be downloaded in zip format';
$string['mastertemplatename'] = 'Master template name';
$string['mastertemplates'] = 'master templates';
$string['maxentries_help'] = 'The maximum number of entries a student is allowed to submit for this activity.';
$string['maxentries'] = 'Maximum attempts';
$string['maxinputdelay_descr'] = 'The maximum allowed delay in hours for users to submit a survey. Even if the user is allowed to pause the data entry to restart it later, after the time defined here each partial attempt will be deleted. Default of 168 hours is equivalent to a week.';
$string['maxinputdelay'] = 'Max input delay';
$string['missinganswer'] = '-- missing answer --';
$string['missingparentcontent_err'] = 'You need to specify a parent content otherwise clear the "{$a}" field';
$string['missingparentid_err'] = 'You need to select a question to branch the survey. Otherwise clear the "{$a}" field';
$string['module'] = 'This instance of survey';
$string['months'] = 'months';
$string['newpageforchild_help'] = 'Use this option to force a new page after each branching element.';
$string['newpageforchild'] = 'Branching element increases page';
$string['newsubmissionbody'] = 'A new record has been submitted in {$a}';
$string['newsubmissionsubject'] = 'New attempt';
$string['nextformpage'] = 'Next page >>';
$string['noadvanceditemsfound'] = 'No questions were found for this (advanced) entry form.<br />This could be due to questions:<ul><li>still not created;</li><li>not visible.</li></ul> Please have a check and come back again.';
$string['noanswer'] = 'No answer';
$string['nogroupsincourse'] = 'This course has not beed divided into groups';
$string['noitemsfound'] = 'This survey has no question at the moment. Please proceed to <a href="{$a->href}" title="{$a->title}">add them</a> before coming here again.';
$string['noitemsfoundtitle'] = 'Add questions before calling this page';
$string['nomoreitems'] = 'On the basis of the answers provided, no more questions remain to display.<br />Your survey is over. You only need to submit{$a}.';
$string['nomorerecordsallowed'] = 'The maximun number of {$a} attempts was already reached. No more attempts are allowed.';
$string['noreadaccess'] = 'Read acces is not allowed in this survey.';
$string['nosubmissionfound'] = 'No attempt were found for this survey. This report has no use.';
$string['notalloweddefault'] = '"{$a}" is not allowed with required questions';
$string['notanyset'] = 'none';
$string['note'] = 'Note:';
$string['nothingtodownload'] = 'Nothing to download';
$string['notifymore_help'] = 'Some additional email addresses to notify about new attempts. Addresses are supposed to be one per row.';
$string['notifymore'] = 'More notifications';
$string['notifyrole_help'] = 'Send an email to each component of the selected roles at each attempt. The email will only advise about attempt from the user, not about its content and without sender details.';
$string['notifyrole'] = 'Notify role';
$string['notinbasicform'] = 'hide this question to students';
$string['nouseritemsfound'] = 'No questions were found for this survey.<br />This could be due to questions:<ul><li>still not created;</li><li>not visible;</li><li>not set to belong to your form.</li></ul>Please have a check and come back again.';
$string['onemorerecord'] = 'Let me add one more response, please';
$string['onlyoptional'] = 'Optional is forced by the value of default.';
$string['onlyreview'] = ' or review';
$string['overwrite_help'] = 'Selecting this checkbox you will overwrite an older template with the same name. If you leave this checkbox unselected, in case of conflicts, you will be asked for a new unique name.';
$string['overwrite'] = 'Overwrite older template';
$string['pagenotassigned'] = 'Page of question {$a} is still undefined!';
$string['pagexofy'] = 'Page {$a->formpage} of {$a->lastformpage}';
$string['parentconstraints'] = 'Parent constraints';
$string['parentcontent_help'] = 'This is the answer that a user has to provide for the parent question to let the current question be displayed.';
$string['parentcontent'] = 'Parent content';
$string['parentformat'] = 'Define the content format of the answer as in the following legend: {$a}';
$string['parentid_help'] = 'Parent questions allow you to create conditional branching. Dimmed questions were set as hidden. Show them to have them available here.<br />Questions preceded by an asterisk are supposed to belong ONLY to the form for users equipped with specific capability.';
$string['parentid'] = 'Parent question';
$string['parentid'] = 'Parent';
$string['pause'] = 'Pause';
$string['plugin_help'] = 'This is the list of question plugin available in this moodle installation. Choose one of them to define the kind of question to add.';
$string['plugin'] = 'Type';
$string['pluginname_help'] = 'Write here the name of the survey plugin you are going to create';
$string['plugintype'] = 'Plugin type';
$string['previousformpage'] = '<< Previous  page';
$string['readonly'] = 'Read';
$string['readwrite'] = 'Edit';
$string['relation_status'] = 'Status';
$string['required_help'] = 'Will the user be forced to fill this question?';
$string['required'] = 'Required';
$string['restrictedaccess'] = 'View only allowed access';
$string['revieworpause'] = ', review or pause';
$string['saveasnew'] = 'Save as new';
$string['saveresume_help'] = 'Allow to save a survey in order to resume data entry and submit you survey next time';
$string['saveresume'] = 'Allow Save/Resume';
$string['sharinglevel_help'] = 'Choose at which level your template will be shared with other courses. If you choose "course" this template will be available in this course ONLY, if you choose course category this template will be available ONLY to courses sharing the same course "category" with this course, if you choose "site" this template will be available to each other courses in this platform.';
$string['sharinglevel'] = 'Sharing level';
$string['showfield'] = 'Show the question';
$string['extranote_help'] = 'Write here a description/note about extra informations the user is supposed to know about this question.';
$string['extranote'] = 'Question notes';
$string['extranoteinsearch_descr'] = 'Are user notes needed in the search form?';
$string['extranoteinsearch'] = 'Extra note in search form';
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
$string['survey:deleteall'] = 'Delete all surveys';
$string['survey:editall'] = 'Edit all surveys';
$string['survey:exportdata'] = 'Export collected data';
$string['survey:manageitems'] = 'Manage questions';
$string['survey:manageplugin'] = 'Manage plugin';
$string['survey:readall'] = 'View all surveys';
$string['survey:view'] = 'View surveys';
$string['surveydeleted'] = 'User attempts have been successfully deleted';
$string['surveyfield'] = 'survey questions';
$string['surveymessage'] = 'Survey type is no longer available because the current one has already been submitted';
$string['switchoptional'] = 'Set the question as optional';
$string['switchrequired'] = 'Set the question as required';
$string['system'] = 'Site';
$string['templatecreateinfo'] = 'Create a template with the current survey. At any time you can download and share it with other moodle users or restore it to your own server. Be careful to "{$a}" if you want to reuse your templates without downloading and reloading them.';
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
$string['typefield'] = 'Question types';
$string['typeformat'] = 'Format elements';
$string['unlimited'] = 'Unlimited';
$string['useadvancedpermissions_descr'] = 'Use advanced permissions to allow students to see/edit/delete responses from other students';
$string['useadvancedpermissions'] = 'Use advanced permissions';
$string['user'] = 'User';
$string['usercanceled'] = 'Action canceled by the user';
$string['usercanfill'] = 'display in the basic entry form but not in the search one';
$string['usercansearch'] = 'display in the basic entry and search form both';
$string['basicedit'] = 'In basic entry form';
$string['basicnoedit'] = 'Not in basic entry form';
$string['basicnosearch'] = 'Not in basic search form';
$string['basicsearch'] = 'In basic search form';
$string['userstyle_help'] = 'Add here one or more cascade style sheet (css) you want to apply to this survey';
$string['userstyle'] = 'Custom style sheet';
$string['usertemplates'] = 'user templates';
$string['validate'] = 'Start validation';
$string['validation'] = 'Validation options';
$string['validationinfo'] = 'This tool let you verify the reliability of your current survey. If the survey includes parent child relations, this tool checks each relations consistency identifying the bad ones that will never allow child questions to be included within the survey.';
$string['wrongrelation'] = '"{$a}" will never match';
$string['xmltemplate_help'] = 'Choose the template you want to download as zip file to share it with other moodle users';
$string['xmltemplate'] = 'Preset to export';
$string['you'] = 'You';
$string['youarenotinagroup'] = 'You do not belong to any of the group in which this course is divided';
$string['notanswereditem'] = 'Answer not submitted';
$string['exploremode'] = 'You are in preview: buttons for data saving are not supposed to display';
$string['hideinstructions'] = 'Hide filling instruction';
$string['hideinstructions_help'] = 'Use this checkbox to show/hide filling instruction for this question';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
$string['aaa'] = 'bbb';
