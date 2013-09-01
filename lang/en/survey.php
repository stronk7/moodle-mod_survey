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
    $string['tabsubmissionspage2'] = 'Attempt';
    $string['tabsubmissionspage3'] = 'Responses';
    $string['tabsubmissionspage4'] = 'Edit';
    $string['tabsubmissionspage5'] = 'Read only';
    $string['tabsubmissionspage6'] = 'Search';
    $string['tabsubmissionspage7'] = 'Reports';
    $string['tabsubmissionspage8'] = 'Export';
$string['tabitemname'] = 'Elements';
    $string['tabitemspage1'] = 'Preview';
    $string['tabitemspage2'] = 'Manage';
    $string['tabitemspage3'] = 'Setup';
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
$string['advanced_help'] = 'Is this element going to be available only to users equipped with a special permission or generally available to each user?';
$string['advanced'] = 'Advanced elements';
$string['advancededit'] = 'Each non hidden element is displayed in the advanced entry form';
$string['advancednoedit'] = 'Not in advanced entry form as hidden';
$string['advancednosearch'] = 'Not in advanced search form';
$string['allowalwaysediting_descr'] = 'Include among survey settings the possibility to allow modifications of a survey even once answered';
$string['allowalwaysediting'] = 'Include "Always allow modifications" setting';
$string['allsurveysdeleted'] = 'All the attempts of this survey have been successfully deleted';
$string['anonymous_help'] = 'This survey will be totally anonymous. Each infos about submitting user will be deleted';
$string['anonymous'] = 'Anonymous';
$string['answerisnoanswer'] = 'Answer refused';
$string['applymtemplateinfo'] = 'You can build your survey applying set of elements taken from a master template plugin<br />Take care: all other preexisting elements (if any) will be definitly deleted.';
$string['applytemplate'] = 'Apply template';
$string['applyutemplateinfo'] = 'You can enrich your survey applying set of elements taken from an XML user template<br /><strong>Be warned: by setting "{$a->usertemplate}" to "{$a->none}" and "{$a->actionoverother}" to "{$a->deleteallitems}" you bring your survey back to blank</strong>';
$string['askdeleteallsubmissions'] = 'Are you sure you want delete ALL the stored attempt?';
$string['askdeletemysubmissions'] = 'Are you sure you want delete your attempt created on {$a->timecreated} and modified on {$a->timemodified}?';
$string['askdeletemysubmissionsnevermodified'] = 'Are you sure you want delete your attempt created on {$a->timecreated} and never modified?';
$string['askdeleteoneitem'] = 'Are you sure you want delete the survey element: {$a}';
$string['askdeleteonesurvey'] = 'Are you sure you want delete the selected attempt owned by {$a->fullname}, created on {$a->timecreated} and modified on {$a->timemodified}?';
$string['askdeleteonesurveynevermodified'] = 'Are you sure you want delete the selected attempts owned by {$a->fullname}, created on {$a->timecreated} and never modified?';
$string['askdeleteonetemplate'] = 'Are you sure you want delete the user template "{$a}"';
$string['askitemstoadvanced'] = 'Switching to "Advanced" the element {$a->parentid} all its dependencies will be switched to "Advanced" too.<br />Dependencies is (are) the element(s) in position: {$a->dependencies}.<br />Do you confirm this action?';
$string['askitemstohide'] = 'Hiding the element {$a->parentid} all its dependencies will be hided too.<br />Dependencies is (are) the element(s) in position: {$a->dependencies}.<br />Do you confirm this action?';
$string['askitemstoshow'] = 'Showing the element {$a->lastitem} you are going to show all its ancestors.<br />Ancestors is (are) the element(s) in position: {$a->ancestors}.<br />Do you confirm this action?';
$string['askitemstostandard'] = 'Switching to "Standard" the element {$a->lastitem} you are going to switch to "Standard" all its ancestors.<br />Ancestors is (are) the element(s) in position: {$a->ancestors}.<br />Do you confirm this action?';
$string['askmultilangrestore'] = 'Are you sure you want restore the multilang content of the element: {$a}';
$string['availability_fs'] = 'Availability';
$string['availability'] = 'Availability';
$string['available'] = 'Element available to each user';
$string['basicedit'] = 'In basic entry form';
$string['basicnoedit'] = 'Not in basic entry form';
$string['basicnosearch'] = 'Not in basic search form';
$string['basicsearch'] = 'In basic search form';
$string['belongtosearchform'] = 'Element available in the search form';
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
$string['completionsubmit'] = 'this is the title of the \'help\'. Where does it appear?';
$string['confirmallsurveysdeletion'] = 'Yes, delete them all';
$string['confirmitemsdeletion'] = 'Yes, delete them all';
$string['confirmitemstoadvanced'] = 'Yes, change to advanced them all';
$string['confirmitemstohide'] = 'Yes, hide them all';
$string['confirmitemstoshow'] = 'Yes, show them all';
$string['confirmitemstostandard'] = 'Yes, change to standard them all';
$string['confirmsurveydeletion'] = 'Yes, delete this attempt';
$string['content_editor_err'] = 'The content is mandatory';
$string['content_editor_help'] = 'The content of the element as it will be shown to remote user';
$string['content_editor'] = 'Content';
$string['content_help'] = 'The content to show as readonly text';
$string['content'] = 'Content';
$string['course'] = 'Course';
$string['currenttotemplate'] = 'Save current survey as master template in zip format.<br />To install a master template, unzip it to mod/survey/template/ and visit the notification page.';
$string['customnumber_header'] = '#';
$string['customnumber_help'] = 'Use this field to add custom number to the element. It may be a natural number such as 1 or 1.a or what ever you may choose. Take in mind that you are responsible for coherence of that numbers. Because of this take care if you plan to change the order of the elements.';
$string['customnumber'] = 'Element number';
$string['customsurvey'] = 'Custom';
$string['dataentry'] = 'Survey settings';
$string['defaultthanksmessage'] = 'Thank you. Your survey has been successfully submitted!';
$string['delete'] = 'Delete';
$string['deleteallitems'] = 'Delete all items';
$string['deleteallsubmissions'] = 'Delete all attempts';
$string['deletebreaklinks'] = 'The current element has child element(s) that are going to be deleted too. The child element(s) position is: {$a}';
$string['deletehiddenitems'] = 'Delete hidden items';
$string['deletepluginmessage'] = 'You are about to completely delete the survey plugin "{$a}". This will completely delete everything in the database associated with this plugin. Are you SURE you want to continue?';
$string['deletevisibleitems'] = 'Delete visible items';
$string['deletingplugin'] = 'Deleting plugin {$a}.';
$string['denyinstantiation_descr'] = 'Unchecking this option to disable this master template. You may like to disable it because a new release of it is already available and this one is here only for backward compatibility feeding its old instances. Once disabled you deny its new instantiation forcing course creators to use the new available release.';
$string['differentaccess'] = 'This element is forced to have the same accessibility level of the parent element.{$a}';
$string['download_advancedonly'] = 'download all fields';
$string['download_usercanfill'] = 'download only fields available in basic entry form';
$string['downloadpdf'] = 'download in pdf';
$string['downloadtocsv'] = 'download to comma separated values';
$string['downloadtotsv'] = 'download to TAB separated values';
$string['downloadtoxls'] = 'download to xls';
$string['downloadtype'] = 'Exported file type';
$string['emptyanswer'] = 'Empty answer';
$string['emptydownload'] = 'The required export has no fields';
$string['emptymaxformpage'] = 'Required max form page (whether $add = true) is missing';
$string['emptysearchform'] = 'No elements were found for this search form.<br />This could be due to elements:<ul><li>still not created;</li><li>not visible;</li><li>not searchable;</li><li>not set to belong to this form.</li></ul>To add an element to the search form use its availability feature.<br />Take care because only searchable questions type elements can be added to the search form.';
$string['enabletemplate'] = 'Enable {$a}';
$string['enabletemplateplugin'] = 'Enable/Disable master templates';
$string['enteruniquename'] = 'Please choose a unique name since {$a} already exists in the choosen context';
$string['exporttemplate'] = 'export template';
$string['extranote_help'] = 'Write here a description/note about extra informations the user is supposed to know about this element.';
$string['extranote'] = 'Additional note';
$string['extranoteinsearch_descr'] = 'Are user notes needed in the search form?';
$string['extranoteinsearch'] = 'Extra note in search form';
$string['extrarow_help'] = 'Use this option to put the content of the element in a dedicated row just upper the interface to enter the answer. Leaving this option unchecked, the content will be displayed on the left of the elements interface. This extra row is usually needed for questions containing images such as text longer than few words!';
$string['extrarow'] = 'Extra row for content';
$string['extrarowisforced'] = '(Extra row forced by plugin)';
$string['field'] = 'field element';
$string['fieldplugin'] = 'Element plugin';
$string['fieldplugins'] = 'Field pugin';
$string['fillinginstructioninsearch_descr'] = 'Are filling instructions needed in the search form?';
$string['fillinginstructioninsearch'] = 'Filling instruction in search form';
$string['findall'] = 'Find all';
$string['forceediting_help'] = 'Allow users, permitted to manage survey elements, to force modifications of this survey even once answered';
$string['forceediting'] = 'Always allow modifications';
$string['format'] = 'format element';
$string['formatplugin'] = 'Format plugin';
$string['formatplugins'] = 'Format pugin';
$string['free'] = 'free';
$string['gotolist'] = 'Continue to responses list';
$string['hassubmissions_alert'] = 'This survey has already been submitted at least once.<br />Please proceed with extreme caution and make only minimal changes to not compromise the validity of the whole survey.';
$string['hidden'] = 'hidden';
$string['hide_help'] = 'Use this option to hide the element. Hided elements will not be available to anyone. You can consider these elements as not part of the survey.';
$string['hide'] = 'Hide';
$string['hidefield'] = 'Hide the element';
$string['hideinstructions_help'] = 'Use this checkbox to show/hide filling instruction for this element';
$string['hideinstructions'] = 'Hide filling instruction';
$string['hideitems'] = 'Hide';
$string['hideshow'] = 'Hide/Show';
$string['history_help'] = 'Asking for history, user will no longer be able to modify a submitted record but only to edit and save a copy of it, leaving all the history of submitted survey available.';
$string['history'] = 'Preserve history';
$string['ignoreitems'] = 'Ignore';
$string['importfile'] = 'Choose files to import';
$string['includehidden'] = 'Include hidden elements';
$string['incorrectaccessdetected'] = 'Incorrect access detected';
$string['indent_help'] = 'The indent of the element alias the left margin the element will respect once drawn';
$string['indent'] = 'Indent';
$string['insearchform_help'] = 'Is this element going to be used in the search form?';
$string['insearchform'] = 'Search form';
$string['invalidformat_err'] = 'Parent format is not valid. It has to follow: {$a}';
$string['invalidtemplate'] = 'File {$a} is an invalid xml file. Please verify it.';
$string['invitationdefault'] = 'Invitation';
$string['item'] = 'Element';
$string['itemaddfail'] = 'The new element has not been added';
$string['itemaddok'] = 'Element has been successfully added';
$string['itemdeleted'] = 'Survey element: {$a} has been successfully deleted';
$string['itemeditfail'] = 'An error occurred saving the element';
$string['itemedithidehide'] = 'Hiding this element, some depending elements were hided too.';
$string['itemeditmakeadvanced'] = 'Marking this element as "Advanced", some depending elements were forced to "Advanced" too.';
$string['itemeditok'] = 'Element successfully modified';
$string['itemeditshow'] = 'Showing this element, some parent elements were showed too.';
$string['itemeditshowinbasicform'] = 'Removing the "Advanced" attribute from this element, some parent elements were forced to "Standard" element too.';
$string['itemlinkbroken'] = 'Some parent/child link was deleted because of the change of the target form';
$string['itemlist'] = 'Elements list';
$string['likelast'] = 'Like last attempt';
$string['managesurveyfieldplugins'] = 'Manage survey field plugins';
$string['managesurveyformatplugins'] = 'Manage survey format plugins';
$string['managesurveyreportplugins'] = 'Manage survey report plugins';
$string['managesurveytemplateplugins'] = 'Manage survey template plugins';
$string['mastertemplate_help'] = 'Choose the master template you want to add to your survey.';
$string['mastertemplate_noedit'] = 'Current survey supports multilanguage as imported from a master template.<br />This means that the survey displays questions and labels according to the user preferred language (if available).<br />By editing this kind of survey you will lose the multilanguage support returning to the standard indifferenciated labels all along the survey.<br />Be warned that once you drop the multilanguage support even by generating again a master template, you still no longer get missed languages and, last but not least, the drop of the multilanguage support is not undoable.<br />Are you sure you want to edit this multilanguage survey?';
$string['mastertemplate'] = 'Master templates';
$string['mastertemplatename_help'] = 'Choose the name of the master template name that is going to be downloaded in zip format';
$string['mastertemplatename'] = 'Master template name';
$string['mastertemplateplugins'] = 'Master template plugin';
$string['mastertemplates'] = 'master templates';
$string['maxentries_help'] = 'The maximum number of entries a student is allowed to submit for this activity.';
$string['maxentries'] = 'Maximum attempts';
$string['maxinputdelay_descr'] = 'The maximum allowed delay in hours for users to submit a survey. Even if the user is allowed to pause the data entry to restart it later, after the time defined here each partial attempt will be deleted. Default of 168 hours is equivalent to a week.';
$string['maxinputdelay'] = 'Max input delay';
$string['missinganswer'] = '-- missing answer --';
$string['missingfile'] = 'It seems no file was selected';
$string['missingparentcontent_err'] = 'You need to specify a parent content otherwise clear the "{$a}" field';
$string['missingparentid_err'] = 'You need to select a element to branch the survey. Otherwise clear the "{$a}" field';
$string['module'] = 'This instance of survey';
$string['months'] = 'months';
$string['multilang'] = 'Restore multilang content';
$string['multilangdropped'] = 'Multilang content has been dropped for this survey';
$string['namenotset'] = 'not set';
$string['needrole'] = 'Element available to selected users only';
$string['newpageforchild_help'] = 'Use this option to force a new page after each branching element.';
$string['newpageforchild'] = 'Branches increase pages';
$string['newsubmissionbody'] = 'A new record has been submitted in {$a}';
$string['newsubmissionsubject'] = 'New attempt';
$string['nextformpage'] = 'Next page >>';
$string['noanswer'] = 'No answer';
$string['nogroupsincourse'] = 'This course has not beed divided into groups';
$string['noitemsfound'] = 'The survey you are accessing is still a work in progress.<br />Please try access again later.';
$string['nomoreitems'] = 'On the basis of the answers provided, no more elements remain to display.<br />Your survey is over. You only need to submit{$a}.';
$string['nomorerecordsallowed'] = 'The maximun number of {$a} attempts was already reached.<br />No more attempts are allowed.';
$string['nomtemplates_help'] = 'Course creator probably denied the instantiation of each master tempalte. Contact your course creator for further details.';
$string['nomtemplates_message'] = 'Sorry. Not any master template is available for the instantiation.';
$string['nomtemplates'] = 'Missing master templates';
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
$string['notinsearchform'] = 'Element not available in the search form';
$string['numinstances'] = 'Instances';
$string['onemorerecord'] = 'Let me add one more response, please';
$string['onlyadvanceditemhere'] = 'The current page holds only advanced elements you are not supposed to access';
$string['onlyoptional'] = 'Optional is forced by the value of default.';
$string['onlyreview'] = ' or review';
$string['overwrite_help'] = 'Selecting this checkbox you will overwrite an older template with the same name. If you leave this checkbox unselected, in case of conflicts, you will be asked for a new unique name.';
$string['overwrite'] = 'Overwrite older template';
$string['pagexofy'] = 'Page {$a->formpage} of {$a->maxassignedpage}';
$string['parentconstraints'] = 'Parent constraints';
$string['parentcontent_help'] = 'This is what the user is supposed to enter in the parent element in order to enable/display this element.';
$string['parentcontent'] = 'Parent content';
$string['parentformat'] = 'Define the content format of the answer as in the following legend: {$a}';
$string['parentid_alt'] = 'Parent element';
$string['parentid_header'] = 'Relation';
$string['parentid_help'] = 'Parent elements allow you to create conditional branching. Dimmed elements in the list identify hidden parent elments. Show them to have them available in this list.<br />Elements preceded by an asterisk are supposed to belong ONLY to advanced form.';
$string['parentid'] = 'Parent element';
$string['parentisadvanced'] = '<br />[The choosed parent element was marked as advanced item]';
$string['parentisstandard'] = '<br />[The choosed parent element was not marked as advanced item]';
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
$string['reportplugin'] = 'Report plugin';
$string['reportplugins'] = 'Report pugin';
$string['required_help'] = 'Will the user be forced to answer this element?';
$string['required'] = 'Required';
$string['responseauthor'] = 'Author: ';
$string['responsedeleted'] = 'User response has been successfully deleted';
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
$string['survey:accessadvanceditems'] = 'Access advanced items';
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
$string['survey:downloadusertemplates'] = 'Download user templates';
$string['survey:exportdata'] = 'Export collected attempt';
$string['survey:manageallsubmissions'] = 'Manage all attempts';
$string['survey:manageitems'] = 'Manage survey elements';
$string['survey:managesubmissions'] = 'Manage personal attempts';
$string['survey:manageusertemplates'] = 'Manage user templates';
$string['survey:preview'] = 'Preview a survey';
$string['survey:searchsubmissions'] = 'Search responses';
$string['survey:setupitems'] = 'Setup items';
$string['survey:submissiontopdf'] = 'Download your submission in PDF';
$string['survey:submit'] = 'Submit attempts';
$string['survey:uploadusertemplates'] = 'Upload user templates';
$string['survey:validatebranching'] = 'Validate branching';
$string['survey:view'] = 'View surveys';
$string['surveyfield'] = 'survey questions';
$string['surveyfieldpluginname'] = 'Field element plugin';
$string['surveyformatpluginname'] = 'Item element plugin';
$string['surveymessage'] = 'Survey type is no longer available because the current one has already been submitted';
$string['surveyplugins'] = 'Survey plugins';
$string['surveyreportpluginname'] = 'Report plugin';
$string['surveytemplatepluginname'] = 'Master template plugin';
$string['switchoptional'] = 'Set the question tye element as optional';
$string['switchrequired'] = 'Set the question tye element as required';
$string['system'] = 'Site';
$string['templatecreateinfo'] = 'Create a user template with the current survey. At any time you can download and share it with other moodle users or restore it to your own server. Be careful to "{$a}" if you want to reuse your templates without downloading and reloading them.';
$string['templateimport'] = 'Import template';
$string['templatelist'] = 'list of available templates';
$string['templatename_help'] = 'Write here the name of the template you are going to create';
$string['templatename'] = 'Template name';
$string['templateplugin'] = 'Master template plugin';
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
$string['validationinfo'] = 'This tool let you verify the reliability of the current survey. This tool checks the validity of each relation identifying the bad ones that will never allow child element to be included in the survey.';
$string['variable_help'] = 'The name of the variable as it will be once downloaded';
$string['variable'] = 'Variable';
$string['wrongrelation'] = '"{$a}" will never match';
$string['xmltemplate_help'] = 'Choose the template you want to download as zip file to share it with other moodle users';
$string['xmltemplate'] = 'Preset to export';
$string['you'] = 'You';
$string['youarenotinagroup'] = 'You do not belong to any of the group in which this course is divided';
