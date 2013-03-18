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

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/lib/formslib.php');

class surveyitem_baseform extends moodleform {

    function definition() {
        global $DB, $CFG;

        // start getting $customdata
        $item = $this->_customdata->item;
        $survey = $this->_customdata->survey;
        $hassubmissions = $this->_customdata->hassubmissions;

        $mform = $this->_form;

        // ----------------------------------------
        // newitem::itemid
        // ----------------------------------------
        $fieldname = 'itemid';
        $mform->addElement('hidden', $fieldname, '');

        // ----------------------------------------
        // newitem::pluginid
        // ----------------------------------------
        $fieldname = 'pluginid';
        $mform->addElement('hidden', $fieldname, '');

        // ----------------------------------------
        // newitem::type
        // ----------------------------------------
        $fieldname = 'type';
        $mform->addElement('hidden', $fieldname, 'bloodytype');

        // ----------------------------------------
        // newitem::plugin
        // ----------------------------------------
        $fieldname = 'plugin';
        $mform->addElement('hidden', $fieldname, 'bloodyplugin');

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'common_fs';
        if ($item->item_form_requires[$fieldname]) {
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));
        }

        // ----------------------------------------
        // newitem::externalname
        // ----------------------------------------
        $fieldname = 'externalname';
        $mform->addElement('hidden', $fieldname, '');

        // ----------------------------------------
        // newitem::content_sid
        // ----------------------------------------
        $fieldname = 'content_sid';
        $mform->addElement('hidden', $fieldname, '');

        // ----------------------------------------
        // newitem::content & contentformat
        // ----------------------------------------
        $fieldname = 'content_editor';
        if ($item->item_form_requires[$fieldname]) {
            $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES);
            $mform->addElement('editor', $fieldname, get_string($fieldname, 'survey'), null, $editoroptions);
            $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_CLEANHTML);
        }

        // ----------------------------------------
        // newitem::extrarow
        // ----------------------------------------
        $fieldname = 'extrarow';
        if ($forceextrarow = $item->item_form_requires[$fieldname]) {
            if ($forceextrarow === 'disable') {
                $helplabel = get_string('extrarowisforced', 'survey');
                $options = array('group' => '1', 'disabled' => 'disabled');
            } else {
                $helplabel = '';
                $options = array('group' => $forceextrarow);
            }
            $mform->addElement('advcheckbox', $fieldname, get_string($fieldname, 'survey'), $helplabel, $options);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            if ($forceextrarow === 'disable') {
                $mform->setDefault($fieldname, $forceextrarow);
            }
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::softinfo
        // ----------------------------------------
        $fieldname = 'softinfo';
        if ($item->item_form_requires[$fieldname]) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'), array('class' => 'longfield'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // ----------------------------------------
        // newitem::customnumber
        // ----------------------------------------
        $fieldname = 'customnumber';
        if ($item->item_form_requires[$fieldname]) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // ----------------------------------------
        // newitem::indent
        // ----------------------------------------
        $fieldname = 'indent';
        if ($item->item_form_requires[$fieldname]) {
            $options = array_combine(range(0, 9), range(0, 9));
            $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setDefault($fieldname, '0');
        }

        // ----------------------------------------
        // newitem::required
        // ----------------------------------------
        $fieldname = 'required';
        if ($item->item_form_requires[$fieldname]) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::fieldname
        // ----------------------------------------
        // for SURVEY_FIELD only
        $fieldname = 'fieldname';
        if ($item->item_form_requires[$fieldname]) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'), array('class' => 'longfield'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'basicform_fs';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::hide
        // ----------------------------------------
        if (!$hassubmissions) {
            $fieldname = 'hide';
            if ($item->item_form_requires[$fieldname]) {
                $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
                $mform->addHelpButton($fieldname, $fieldname, 'survey');
                $mform->setType($fieldname, PARAM_INT);
            }
        }

        // ----------------------------------------
        // newitem::basicform
        // ----------------------------------------
        $fieldname = 'basicform';
        if ($item->item_form_requires[$fieldname]) {
            $options = array();
            if ($hassubmissions) {
                if ($item->{$fieldname} != SURVEY_NOTPRESENT) {
                    // if the item is NOT_PRESENT you can not add it when survey $hassubmissions
                    $options[SURVEY_FILLONLY] = get_string('usercanfill', 'survey');
                    if ($item->flag->issearchable || ($item->type == SURVEY_FORMAT)) {
                        $options[SURVEY_FILLANDSEARCH] = get_string('usercansearch', 'survey');
                    }
                }
            } else {
                $options[SURVEY_NOTPRESENT] = get_string('notinbasicform', 'survey');
                $options[SURVEY_FILLONLY] = get_string('usercanfill', 'survey');
                if ($item->flag->issearchable || ($item->type == SURVEY_FORMAT)) {
                    $options[SURVEY_FILLANDSEARCH] = get_string('usercansearch', 'survey');
                }
            }
            $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
            $mform->disabledIf($fieldname, 'hide', 'checked');
        }

        // ----------------------------------------
        // newitem::advancedsearch
        // ----------------------------------------
        $fieldname = 'advancedsearch';
        if ($item->item_form_requires[$fieldname]) {
            if ($item->flag->issearchable) {
                $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
                $mform->addHelpButton($fieldname, $fieldname, 'survey');
                $mform->setType($fieldname, PARAM_INT);
                $mform->disabledIf($fieldname, 'hide', 'checked');
            }
        }

        if (!$hassubmissions) {
            // /////////////////////////////////////////////////////////////////////////////////////////////////
            // here I open a new fieldset
            // /////////////////////////////////////////////////////////////////////////////////////////////////
            $fieldname = 'branching_fs';
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

            // ----------------------------------------
            // newitem::parentid
            // ----------------------------------------
            $fieldname = 'parentid';
            if ($item->item_form_requires[$fieldname]) {
                // create the list of each item with:
                //     sortindex lower than mine (whether already exists)
                //     $plugintemplate->flag->couldbeparent == true
                //     basicform == my one <-- I jump this verification because the survey creator can, at every time, change the basicform of the current item
                //         So I shify the verification of the holding form at the form verification time.

                // build the list only for searchable plugins
                $pluginarray = survey_get_plugin_list(SURVEY_FIELD);
                foreach ($pluginarray as $plugin) {
                    $plugintemplate = survey_get_item(null, SURVEY_FIELD, $plugin);
                    if (!$plugintemplate->flag->couldbeparent) {
                        unset($pluginarray[$plugin]);
                    }
                }
                $pluginlist = '(\''.implode("','", $pluginarray).'\')';

                $sql = 'SELECT *
                        FROM {survey_item}
                        WHERE surveyid = :surveyid';
                $sqlparams = array('surveyid' => $survey->id);
                if ($item->item_has_sortindex()) {
                    $sql .= ' AND sortindex < :sortindex';
                    $sqlparams['sortindex'] = $item->sortindex;
                }
                $sql .= ' AND plugin IN '.$pluginlist.'
                            ORDER BY sortindex';
                $records = $DB->get_recordset_sql($sql, $sqlparams);

                $quickform = new HTML_QuickForm();
                $select = $quickform->createElement('select', $fieldname, get_string($fieldname, 'survey'));
                $select->addOption(get_string('choosedots'), 0);
                $maxlength = 80;
                foreach ($records as $record) {
                    $star = ($record->basicform == SURVEY_NOTPRESENT) ? '(*) ' : '';
                    $thiscontent = survey_get_sid_field_content($record);

                    $content = $star.get_string('pluginname', 'surveyfield_'.$record->plugin).': '.strip_tags($thiscontent);
                    if (strlen($content) > $maxlength) {
                        $content = substr($content, 0, $maxlength);
                    }
                    $disabled = ($record->hide == 1) ? array('disabled' => 'disabled') : null;
                    $select->addOption($content, $record->id, $disabled);
                }
                $records->close();

                $mform->addElement($select);
                $mform->addHelpButton($fieldname, $fieldname, 'survey');
                $mform->setType($fieldname, PARAM_INT);
            }

            // ----------------------------------------
            // newitem::parentcontent
            // ----------------------------------------
            $fieldname = 'parentcontent';
            if ($item->item_form_requires[$fieldname]) {
                $mform->addElement('textarea', $fieldname, get_string($fieldname, 'survey'), array('wrap' => 'virtual', 'rows' => '5', 'cols' => '45'));
                $mform->addHelpButton($fieldname, $fieldname, 'survey');
                $mform->setType($fieldname, PARAM_RAW);

                // ----------------------------------------
                // newitem::parentformat
                // ----------------------------------------
                $fieldname = 'parentformat';
                $a = '<ul>';
                foreach ($pluginarray as $plugin) {
                    $a .= '<li><div>';
                    $a .= '<div class="pluginname">'.get_string('pluginname', 'surveyfield_'.$plugin).': </div>';
                    $a .= '<div class="inputformat">'.get_string('parentformat', 'surveyfield_'.$plugin).'</div>';
                    $a .= '</div></li>'."\n";
                }
                $a .= '</ul>';
                $mform->addElement('static', $fieldname, get_string('note', 'survey'), get_string($fieldname, 'survey', $a));
            }

            // ----------------------------------------
            // newitem::parentvalue
            // ----------------------------------------
            // $fieldname = 'parentvalue';
            // $mform->addElement('hidden', $fieldname, '');
        }

        if ($item->item_get_type() == SURVEY_FIELD) {
            // /////////////////////////////////////////////////////////////////////////////////////////////////
            // here I open a new fieldset
            // /////////////////////////////////////////////////////////////////////////////////////////////////
            $fieldname = 'specializations';
            $typename = get_string('pluginname', 'surveyfield_'.$item->item_get_plugin());
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey', $typename));
        }
    }

    function add_item_buttons() {
        $mform = $this->_form;

        $item = $this->_customdata->item;
        $survey = $this->_customdata->survey;
        $hassubmissions = $this->_customdata->hassubmissions;

        // -------------------------------------------------------------------------------
        // buttons
        if (!empty($item->itemid)) {
            $fieldname = 'buttons';
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('submit', 'save', get_string('savechanges'));
            if (!$hassubmissions) {
                $elementgroup[] = $mform->createElement('submit', 'saveasnew', get_string('saveasnew', 'survey'));
            }
            $elementgroup[] = $mform->createElement('cancel');
            $mform->addGroup($elementgroup, $fieldname.'_group', '', ' ', false);
            $mform->closeHeaderBefore($fieldname.'_group');
        } else {
            $this->add_action_buttons(true, get_string('add'));
        }
    }

    function validation($data, $files) {
        global $CFG, $DB;

        $item = $this->_customdata->item;
        $survey = $this->_customdata->survey;
        $hassubmissions = $this->_customdata->hassubmissions;

        $errors = array();

        // if (default == noanswer se default == noanswer ma è obbligatorio => errorese default == noanswer ma è obbligatorio => errore the field is mandatory) => error
        if ( isset($data['defaultvalue_check']) && isset($data['required']) ) {
            $a = get_string('noanswer', 'survey');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'survey', $a);
        }

        if (!$hassubmissions) {
            // you choosed a parentid but you are missing the parentcontent
            if (empty($data['parentid']) && ($data['parentcontent'] != '')) { // $data['parentcontent'] can be = 0
                $a = get_string('parentcontent', 'survey');
                $errors['parentcontent'] = get_string('missingparentid_err', 'survey', $a);
            }

            // you did not choose a parent item but you entered an answer
            if (!empty($data['parentid']) && ($data['parentcontent'] == '')) { // $data['parentcontent'] can be = 0
                $a = get_string('parentid', 'survey');
                $errors['parentid'] = get_string('missingparentcontent_err', 'survey', $a);
            }
        }

        // now validate the format of the "parentcontent" fields against the format of the parent item
        if (!empty($data['parentid']) && ($data['parentcontent'] != '')) { // $data['parentcontent'] can be = 0
            // $data['parentid'] == 148
            // $type = 'field' for sure
            $plugin = $DB->get_field('survey_item', 'plugin', array('id' => $data['parentid']));
            require_once($CFG->dirroot.'/mod/survey/field/'.$plugin.'/plugin.class.php');
            $itemclass = 'surveyfield_'.$plugin;
            $parentitem = new $itemclass($data['parentid']);
            if ($errormessage = $parentitem->item_parentcontent_format_validation($data['parentcontent'])) {
                $errors['parentcontent'] = $errormessage;
            }

            // verify $parentitem is in the basicform as of this item
            $childbasicform = $data['basicform'];
            $parentbasicform = $parentitem->basicform;
            if ( (($parentbasicform == SURVEY_NOTPRESENT) && ($childbasicform != SURVEY_NOTPRESENT)) ||
                 (($parentbasicform != SURVEY_NOTPRESENT) && ($childbasicform == SURVEY_NOTPRESENT)) ) {
                $a = ($parentbasicform == SURVEY_NOTPRESENT) ? get_string('isnotinbasicform', 'survey') : get_string('isinbasicform', 'survey');
                $errors['basicform'] = get_string('differentbasicform', 'survey', $a);
            }
        }

        return $errors;
    }
}
