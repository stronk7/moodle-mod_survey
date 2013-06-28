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

require_once($CFG->dirroot.'/lib/formslib.php');

class surveyitem_baseform extends moodleform {

    public function definition() {
        global $DB;

        // -------------------------------------------------------------------------------
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
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::pluginid
        // ----------------------------------------
        $fieldname = 'pluginid';
        $mform->addElement('hidden', $fieldname, '');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::type
        // ----------------------------------------
        $fieldname = 'type';
        $mform->addElement('hidden', $fieldname, 'bloodytype');
        $mform->setType($fieldname, PARAM_RAW);

        // ----------------------------------------
        // newitem::plugin
        // ----------------------------------------
        $fieldname = 'plugin';
        $mform->addElement('hidden', $fieldname, 'bloodyplugin');
        $mform->setType($fieldname, PARAM_RAW);

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'common_fs';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));
        }

        // ----------------------------------------
        // newitem::externalname
        // ----------------------------------------
        $fieldname = 'externalname';
        $mform->addElement('hidden', $fieldname, '');
        $mform->setType($fieldname, PARAM_RAW);

        // ----------------------------------------
        // newitem::content_sid
        // ----------------------------------------
        $fieldname = 'content_sid';
        $mform->addElement('hidden', $fieldname, '');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // newitem::content & contentformat
        // ----------------------------------------
        $fieldname = 'content_editor';
        if ($item->get_item_form_requires($fieldname)) {
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
        if ($forceextrarow = $item->get_item_form_requires($fieldname)) {
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
        // newitem::extranote
        // ----------------------------------------
        $fieldname = 'extranote';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'), array('class' => 'longfield'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // ----------------------------------------
        // newitem::hideinstructions
        // ----------------------------------------
        $fieldname = 'hideinstructions';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::customnumber
        // ----------------------------------------
        $fieldname = 'customnumber';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // ----------------------------------------
        // newitem::indent
        // ----------------------------------------
        $fieldname = 'indent';
        if ($item->get_item_form_requires($fieldname)) {
            $options = array_combine(range(0, 9), range(0, 9));
            $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setDefault($fieldname, '0');
        }

        // ----------------------------------------
        // newitem::required
        // ----------------------------------------
        $fieldname = 'required';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::variable
        // ----------------------------------------
        // for SURVEY_TYPEFIELD only
        $fieldname = 'variable';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'), array('class' => 'longfield'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // /////////////////////////////////////////////////////////////////////////////////////////////////
        // here I open a new fieldset
        // /////////////////////////////////////////////////////////////////////////////////////////////////
        $fieldname = 'availability_fs';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::hide
        // ----------------------------------------
        $fieldname = 'hide';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::insearchform
        // ----------------------------------------
        $fieldname = 'insearchform';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::limitedaccess
        // ----------------------------------------
        $fieldname = 'limitedaccess';
        if ($item->get_item_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        if ($item->get_item_form_requires('parentid')) {
            // /////////////////////////////////////////////////////////////////////////////////////////////////
            // here I open a new fieldset
            // /////////////////////////////////////////////////////////////////////////////////////////////////
            $fieldname = 'branching_fs';
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

            // ----------------------------------------
            // newitem::parentid
            // ----------------------------------------
            $fieldname = 'parentid';
            // create the list of each item with:
            //     sortindex lower than mine (whether already exists)
            //     $plugintemplate->flag->couldbeparent == true
            //     basicform == my one <-- I jump this verification because the survey creator can, at every time, change the basicform of the current item
            //         So I shify the verification of the holding form at the form verification time.

            // build the list only for searchable plugins
            $pluginlist = survey_get_plugin_list(SURVEY_TYPEFIELD);
            foreach ($pluginlist as $plugin) {
                $plugintemplate = survey_get_item(null, SURVEY_TYPEFIELD, $plugin);
                if (!$plugintemplate->flag->couldbeparent) {
                    unset($pluginlist[$plugin]);
                }
            }
            $pluginwhere = '(\''.implode("','", $pluginlist).'\')';

            $sql = 'SELECT *
                    FROM {survey_item}
                    WHERE surveyid = :surveyid';
            $sqlparams = array('surveyid' => $survey->id);
            if ($item->get_sortindex()) {
                $sql .= ' AND sortindex < :sortindex';
                $sqlparams['sortindex'] = $item->get_sortindex();
            }
            $sql .= ' AND plugin IN '.$pluginwhere.'
                        ORDER BY sortindex';
            $records = $DB->get_recordset_sql($sql, $sqlparams);

            $maxlength = 80;
            $quickform = new HTML_QuickForm();
            $select = $quickform->createElement('select', $fieldname, get_string($fieldname, 'survey'));
            $select->addOption(get_string('choosedots'), 0);
            foreach ($records as $record) {
                $star = ($record->limitedaccess) ? '(*) ' : '';
                $thiscontent = survey_get_sid_field_content($record);

                $content = $star.get_string('pluginname', 'surveyfield_'.$record->plugin).' ['.$record->sortindex.']: '.strip_tags($thiscontent);
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

            // ----------------------------------------
            // newitem::parentcontent
            // ----------------------------------------
            $fieldname = 'parentcontent';
            $params = array('wrap' => 'virtual', 'rows' => '5', 'cols' => '45');
            $mform->addElement('textarea', $fieldname, get_string($fieldname, 'survey'), $params);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_RAW);

            // ----------------------------------------
            // newitem::parentformat
            // ----------------------------------------
            $fieldname = 'parentformat';
            $a = '<ul>';
            foreach ($pluginlist as $plugin) {
                $a .= '<li><div>';
                $a .= '<div class="pluginname">'.get_string('pluginname', 'surveyfield_'.$plugin).': </div>';
                $a .= '<div class="inputformat">'.get_string('parentformat', 'surveyfield_'.$plugin).'</div>';
                $a .= '</div></li>'."\n";
            }
            $a .= '</ul>';
            $mform->addElement('static', $fieldname, get_string('note', 'survey'), get_string($fieldname, 'survey', $a));
        }

        if ($item->get_type() == SURVEY_TYPEFIELD) {
            // /////////////////////////////////////////////////////////////////////////////////////////////////
            // here I open a new fieldset
            // /////////////////////////////////////////////////////////////////////////////////////////////////
            $fieldname = 'specializations';
            $typename = get_string('pluginname', 'surveyfield_'.$item->get_plugin());
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey', $typename));
        }
    }

    public function add_item_buttons() {
        global $CFG;

        $mform = $this->_form;

        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        $hassubmissions = $this->_customdata->hassubmissions;

        // -------------------------------------------------------------------------------
        // buttons
        $item_itemid = $item->get_itemid();
        if (!empty($item_itemid)) {
            $fieldname = 'buttons';
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('submit', 'save', get_string('savechanges'));
            if (!$hassubmissions || $CFG->survey_forcemodifications) {
                $elementgroup[] = $mform->createElement('submit', 'saveasnew', get_string('saveasnew', 'survey'));
            }
            $elementgroup[] = $mform->createElement('cancel');
            $mform->addGroup($elementgroup, $fieldname.'_group', '', ' ', false);
            $mform->closeHeaderBefore($fieldname.'_group');
        } else {
            $this->add_action_buttons(true, get_string('add'));
        }
    }

    public function validation($data, $files) {
        global $CFG, $DB;

        // -------------------------------------------------------------------------------
        $item = $this->_customdata->item;
        // $survey = $this->_customdata->survey;
        // $hassubmissions = $this->_customdata->hassubmissions;

        $errors = array();

        // if (default == noanswer) but item is required => error
        if ( isset($data['defaultvalue_check']) && isset($data['required']) ) {
            $a = get_string('noanswer', 'survey');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'survey', $a);
        }

        if (empty($data['parentid']) && empty($data['parentcontent'])) {
            // stop verification here
            return $errors;
        }

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

        if (!empty($data['parentid']) && ($data['parentcontent'] != '')) { // $data['parentcontent'] can be = 0
            // $data['parentid'] == 148
            // $type = 'field' for sure
            $plugin = $DB->get_field('survey_item', 'plugin', array('id' => $data['parentid']));
            require_once($CFG->dirroot.'/mod/survey/field/'.$plugin.'/plugin.class.php');
            $itemclass = 'surveyfield_'.$plugin;
            $parentitem = new $itemclass($data['parentid']);

            if (!isset($data['hide'])) {
                // verify $parentitem is in the basicform as of this item
                if (isset($data['limitedaccess'])) {
                    $childlimitedaccess = $data['limitedaccess'];
                } else {
                    $childlimitedaccess = 0;
                }
                $parentlimitedaccess = $parentitem->get_limitedaccess();
                if ($parentlimitedaccess != $childlimitedaccess) {
                    $a = ($parentlimitedaccessm) ? get_string('isnotinbasicform', 'survey') : get_string('isinbasicform', 'survey');
                    // echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                    // echo '$a = '.$a.'<br />';
                    $errors['basicform'] = get_string('differentbasicform', 'survey', $a);
                }
            }
        }

        return $errors;
    }
}
