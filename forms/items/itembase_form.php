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

class mod_survey_itembaseform extends moodleform {

    public function definition() {
        global $DB, $CFG;

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
        $mform->addElement('hidden', $fieldname, 'dummytype');
        $mform->setType($fieldname, PARAM_RAW);

        // ----------------------------------------
        // newitem::plugin
        // ----------------------------------------
        $fieldname = 'plugin';
        $mform->addElement('hidden', $fieldname, 'dummyplugin');
        $mform->setType($fieldname, PARAM_RAW);

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'common_fs';
        if ($item->get_form_requires($fieldname)) {
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));
        }

        // ----------------------------------------
        // newitem::content & contentformat
        // ----------------------------------------
        if ($item->get_form_requires('content')) {
            if ($item->flag->usescontenteditor) {
                $fieldname = 'content_editor';
                $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES);
                $mform->addElement('editor', $fieldname, get_string($fieldname, 'survey'), null, $editoroptions);
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
                $mform->addHelpButton($fieldname, $fieldname, 'survey');
                $mform->setType($fieldname, PARAM_CLEANHTML);
            } else {
                $fieldname = 'content';
                $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'), array('maxlength' => '128', 'size' => '50'));
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
                $mform->addHelpButton($fieldname, $fieldname, 'survey');
                $mform->setType($fieldname, PARAM_TEXT);
            }
        }

        // ----------------------------------------
        // newitem::required
        // ----------------------------------------
        $fieldname = 'required';
        if ($item->get_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::indent
        // ----------------------------------------
        $fieldname = 'indent';
        if ($item->get_form_requires($fieldname)) {
            $options = array_combine(range(0, 9), range(0, 9));
            $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setDefault($fieldname, '0');
        }

        // ----------------------------------------
        // newitem::extrarow
        // ----------------------------------------
        $fieldname = 'extrarow';
        if ($forceextrarow = $item->get_form_requires($fieldname)) {
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
        // newitem::customnumber
        // ----------------------------------------
        $fieldname = 'customnumber';
        if ($item->get_form_requires($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // ----------------------------------------
        // newitem::hideinstructions
        // ----------------------------------------
        $fieldname = 'hideinstructions';
        if ($item->get_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::variable
        // ----------------------------------------
        // for SURVEY_TYPEFIELD only
        $fieldname = 'variable';
        if ($item->get_form_requires($fieldname)) {
            $options = array('maxlength' => 64, 'size' => 12, 'class' => 'longfield');

            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // ----------------------------------------
        // newitem::extranote
        // ----------------------------------------
        $fieldname = 'extranote';
        if ($item->get_form_requires($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'survey'), array('class' => 'longfield'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'availability_fs';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // ----------------------------------------
        // newitem::hide
        // ----------------------------------------
        $fieldname = 'hide';
        if ($item->get_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::insearchform
        // ----------------------------------------
        $fieldname = 'insearchform';
        if ($item->get_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // newitem::advanced
        // ----------------------------------------
        $fieldname = 'advanced';
        if ($item->get_form_requires($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
            $mform->setType($fieldname, PARAM_INT);
        }

        if ($item->get_form_requires('parentid')) {
            // -----------------------------
            // here I open a new fieldset
            // -----------------------------
            $fieldname = 'branching_fs';
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

            // ----------------------------------------
            // newitem::parentid
            // ----------------------------------------
            $fieldname = 'parentid';
            // create the list of each item with:
            //     sortindex lower than mine (whether already exists)
            //     $itemtemplate->flag->canbeparent == true
            //     advanced == my one <-- I jump this verification because the survey creator can, at every time, change the basicform of the current item
            //                            So I move the verification of the holding form at the form verification time.

            // build the list only for searchable plugins
            $pluginlist = survey_get_plugin_list(SURVEY_TYPEFIELD);
            foreach ($pluginlist as $plugin) {
                require_once($CFG->dirroot.'/mod/survey/'.SURVEY_TYPEFIELD.'/'.$plugin.'/plugin.class.php');
                $classname = 'survey'.SURVEY_TYPEFIELD.'_'.$plugin;
                if (!$classname::$canbeparent) {
                    unset($pluginlist[$plugin]);
                }
            }
            $where = '(\''.implode("','", $pluginlist).'\')';
            $sql = 'SELECT *
                    FROM {survey_item}
                    WHERE surveyid = :surveyid';
            $whereparams = array('surveyid' => $survey->id);
            if ($item->get_sortindex()) {
                $sql .= ' AND sortindex < :sortindex';
                $whereparams['sortindex'] = $item->get_sortindex();
            }
            $sql .= ' AND plugin IN '.$where.'
                        ORDER BY sortindex';
            $parentsseeds = $DB->get_recordset_sql($sql, $whereparams);

            $quickform = new HTML_QuickForm();
            $select = $quickform->createElement('select', $fieldname, get_string($fieldname, 'survey'));
            $select->addOption(get_string('choosedots'), 0);
            foreach ($parentsseeds as $parentsseed) {
                $parentitem = survey_get_item($parentsseed->id, $parentsseed->type, $parentsseed->plugin);
                $star = ($parentitem->get_advanced()) ? '(*) ' : '';

                // I do not need to take care of contents of items of master templates because if I am here, $parent is a standard item and not a multilang one
                $content = $star.get_string('pluginname', 'surveyfield_'.$parentitem->get_plugin()).' ['.$parentitem->get_sortindex().']: '.strip_tags($parentitem->get_content());
                $content = survey_fixlength($content, 60);

                $disabled = ($parentitem->get_hide() == 1) ? array('disabled' => 'disabled') : null;
                $select->addOption($content, $parentitem->itemid, $disabled);
            }
            $parentsseeds->close();

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
            // -----------------------------
            // here I open a new fieldset
            // -----------------------------
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
        $survey = $this->_customdata->survey;
        $hassubmissions = $this->_customdata->hassubmissions;

        $forceediting = ($survey->riskyeditdeadline > time());

        // -------------------------------------------------------------------------------
        // buttons
        $itemid = $item->get_itemid();
        if (!empty($itemid)) {
            $fieldname = 'buttons';
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('submit', 'save', get_string('savechanges'));
            if (!$hassubmissions || $forceediting) {
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

        // -----------------------------
        // mform issue (never rose up)
        // I have a parent-child couple of items.
        // After the relation was been done, the parent was made hidden.
        // Now I eit the child.
        // The parentid drop down menu should:
        //     -> have the item, corresponding to the parentid of the current item, disabled
        //     -> have that item selected
        // In this tricky case, parentid is not set at all.
        // I fix this issue by assigning "manually" the parentid
        // and I continue as if the parent item is visible
        if (!isset($data['parentid'])) { // parentid is disabled because parent is hidden
            $data['parentid'] = $item->get_parentid();
        }
        // -----------------------------

        // you choosed a parentid but you are missing the parentcontent
        if (empty($data['parentid']) && (strlen($data['parentcontent']) > 0)) { // $data['parentcontent'] can be = 0
            $a = get_string('parentcontent', 'survey');
            $errors['parentid'] = get_string('missingparentid_err', 'survey', $a);
        }

        // you did not choose a parent item but you entered an answer
        if ( !empty($data['parentid']) && (strlen($data['parentcontent']) == 0) ) { // $data['parentcontent'] can be = 0
            $a = get_string('parentid', 'survey');
            $errors['parentcontent'] = get_string('missingparentcontent_err', 'survey', $a);
        }

        return $errors;
    }
}
