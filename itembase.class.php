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

/*
 * The base class representing a field
 */
class surveyitem_base {

    /***********************************************************************************/
    /* BEGIN OF FIELDS OF SURVEY_ITEMS */
    /***********************************************************************************/

    /*
     * unique itemid of the surveyitem in survey_item table
     */
    public $itemid = 0;

    /*
     * $surveyid = the id of the survey
     */
    public $surveyid = 0;

    /*
     * $type = the type of the item. It can only be: SURVEY_TYPEFIELD or SURVEY_TYPEFORMAT
     */
    public $type = '';

    /*
     * $plugin = the item plugin
     */
    public $plugin = '';

    /*
     * $itemname = the name of the field as it is in the attempt_form
     */
    public $itemname = '';

    /*
     * $externalname = a string specifing the origin of the item.
     * empty: user made it
     * non empty: belong to a built-in survey
     */
    public $externalname = '';

    /*
     * $content_sid = a number specifing the ID of the builtin survey item.
     * empty: user made it
     * non empty: belong to a built-in survey
     */
    public $content_sid = null;

    /*
     * $content = the text content of the item.
     */
    public $content = '';

    /*
     * $contentformat = the text format of the item.
     * public $contentformat = '';
     */
    public $contentformat = '';

    /*
     * $customnumber = the custom number of the item.
     * It usually is 1. 1.1, a, 2.1.a...
     */
    public $customnumber = '';

    /*
     * $extrarow = is the extrarow required?
     */
    public $extrarow = 0;

    /*
     * $extranote = an optional text describing the item
     */
    public $extranote = '';

    /*
     * $required = boolean. O == optional item; 1 == mandatory item
     */
    public $required = 0;

    /*
     * $hideinstructions = boolean. Exceptionally hide filling instructions
     */
    public $hideinstructions = 0;

    /*
     * $fieldname = the name of the field storing data in the db table
     */
    public $fieldname = '';

    /*
     * $indent = the indent of the item in the form layout/template
     */
    public $indent = 0;

    /*
     * $basicform = will this item be part of basic edit/search forms?
     * SURVEY_NOTPRESENT   : no
     * SURVEY_FILLONLY     : yes, only in the "edit" form
     * SURVEY_FILLANDSEARCH: yes, in the "edit" and in the "search" form too
     */
    public $basicform = SURVEY_FILLANDSEARCH;

    /*
     * $advancedsearch = will this item be part of the advanced search form?
     * SURVEY_ADVFILLONLY     : no, it will not be part
     * SURVEY_ADVFILLANDSEARCH: yes, it will be part of the advanced search form
     */
    public $advancedsearch = SURVEY_ADVFILLANDSEARCH;

    /*
     * $hide = is this field going to be shown in the form?
     */
    public $hide = 0;

    /*
     * $sortindex = the order of this item in the survey form
     */
    public $sortindex = 0;

    /*
     * $basicformpage = the user survey page for this item
     */
    public $basicformpage = 0;

    /*
     * $advancedformpage = the advanced survey page for this item
     */
    public $advancedformpage = 0;

    /*
     * $parentid = the item this item depends from
     */
    public $parentid = 0;

    /*
     * $parentcontent = the constrain given by item parentid as entered by the survey creator (25/4/1860)
     */
    public $parentcontent = '';

    /*
     * $parentvalue = the "well written" constrain given by parentid (1594832670)
     */
    public $parentvalue = '';

    /*
     * $timecreated = the creation time of this item
     */
    public $timecreated = 0;

    /*
     * $timemodified = the modification time of this item
     */
    public $timemodified = null;

    /*
     * $flag = features describing the object
     * I can redeclare the public and protected method/property, but not private
     * so I choose to not declare this properties here
     * public $flag = null;
     */

    /*
     * $item_form_requires = list of fields the survey creator will need/see/use in the item definition form
     * By default each item is present in the form
     * so, in each child class, I only need to "deactivate" fields I don't want to see
     */
    public $item_form_requires = array(
        'common_fs' => true,
        'content_editor' => true,
        'customnumber' => true,
        'extrarow' => true,
        'extranote' => true,
        'hideinstructions' => true,
        'required' => true,
        'fieldname' => true,
        'indent' => true,
        'basicform' => true,
        'advancedsearch' => true,
        'hide' => true,
        'parentid' => true,
        'parentcontent' => true
    );
    /***********************************************************************************/
    /* END OF FIELDS OF SURVEY_ITEMS */
    /***********************************************************************************/

    /*
     * item_load
     * @param $itemid
     * @return
     */
    public function item_load($itemid) {
        global $DB;

        // Do own loading stuff here
        if (!$itemid) {
            debugging('Something was wrong at line '.__LINE__.' of file '.__FILE__.'! Can not load an item without its ID');
        }

        if ($this->flag->useplugintable) {
            $sql = 'SELECT *, si.id as itemid, plg.id as pluginid
                    FROM {survey_item} si
                        JOIN {survey_'.$this->plugin.'} plg ON si.id = plg.itemid
                    WHERE si.id = :surveyitemid';
        } else {
            $sql = 'SELECT *, si.id as itemid
                    FROM {survey_item} si
                    WHERE si.id = :surveyitemid';
        }

        if ($record = $DB->get_record_sql($sql, array('surveyitemid' => $itemid))) {
            foreach ($record as $option => $value) {
                $this->{$option} = $value;
            }
            unset($this->id); // I do not care it. I already heave: itemid and pluginid
            $this->itemname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
        } else {
            debugging('Something was wrong at line '.__LINE__.' of file '.__FILE__.'!<br />I can not find the survey_item ID = '.$itemid.' using:<br />'.$sql);
        }
    }

    /*
     * save
     * Executes surveyitem_<<plugin>> global level actions
     * this is the save point of the global part of each plugin
     * @param $record
     * @return
     */
    public function item_save($record) {
        global $CFG, $DB, $PAGE;

        $cm = $PAGE->cm;

        // you are going to change item content (maybe sortindex, maybe the basicform)
        // so, do not forget to reset items per page
        survey_reset_items_pages($cm->instance);

        $timenow = time();

        // the surveyitem lies in two different tables
        // survey_item
        // survey_<<plugin>>
        $tablename = 'survey_'.$this->plugin;

        // do not forget surveyid
        $record->surveyid = $cm->instance;
        $record->timemodified = $timenow;

        // required
        if ($this->item_form_requires['required']) {
            $record->required = isset($record->required) ? 1 : 0;
        } else {
            $record->required = null;
        }

        // extrarow
        // $record->extrarow = (isset($record->extrarow)) ? 1 : 0; // extrarow is advcheckbox so doesn't need my intervention

        // hideinstructions
        $record->hideinstructions = (isset($record->hideinstructions)) ? 1 : 0;

        // hide
        // hide/regular part 1
        if ($this->item_form_requires['hide']) {
            $record->hide = isset($record->hide) ? 1 : 0;
        } else {
            $record->hide = null;
        }
        // end of: hide/regular part 1

        // advancedsearch
        if ($this->item_form_requires['advancedsearch']) {
            $record->advancedsearch = isset($record->advancedsearch) ? SURVEY_ADVFILLANDSEARCH : SURVEY_ADVFILLONLY;
        } else {
            $record->advancedsearch = SURVEY_ADVFILLONLY;
        }

        // encode $fromform->parentcontent to $item->parentvalue on the basis of the parentplugin specified in $record->parentid
        if (isset($record->parentid) && $record->parentid) { // I am sure parentcontent is here too
            $parentplugin = $DB->get_field('survey_item', 'plugin', array('id' => $record->parentid));
            require_once($CFG->dirroot.'/mod/survey/field/'.$parentplugin.'/plugin.class.php');
            $itemclass = 'surveyfield_'.$parentplugin;
            $parentitem = new $itemclass($record->parentid);

            $record->parentvalue = $parentitem->item_parent_content_encode_value($record->parentcontent);
        }

        // $userfeedback
        //   +--- children moved out from user entry form
        //   |       +--- children moved in the user entry form
        //   |       |       +--- child hided because of this item hide
        //   |       |       |       +--- parent was shown because this item was shown
        //   |       |       |       |       +--- new|edit
        //   |       |       |       |       |       +--- success|fail
        // [0|1] - [0|1] - [0|1] - [0|1] - [0|1] - [0|1]
        // last digit == 1 means that the process was successfull
        // last digit == 0 means that the process was NOT successfull
        // beforelast digit == 0 means NEW
        // beforelast digit == 1 means EDIT

        // (digit in place 2) == 1 means item shown
        // (digit in place 3) == 1 means item hided because this item was hided
        // (digit in place 4) == 1 means item hided because this item was removed from the user entry form

        $userfeedback = SURVEY_NOFEEDBACK;
        // Does this record need to be saved as new record or as un update on a preexisting record?
        if (empty($record->itemid)) {
            // record is new
            // timecreated
            $record->timecreated = $timenow;

            // sortindex
            $sql = 'SELECT COUNT(\'x\')
                    FROM {survey_item}
                    WHERE surveyid = :surveyid
                        AND sortindex > 0';
            $sqlparam = array('surveyid' => $cm->instance);
            $record->sortindex = 1+$DB->count_records_sql($sql, $sqlparam);

            // itemid
            if ($record->itemid = $DB->insert_record('survey_item', $record)) {
                // $tablename
                if ($this->flag->useplugintable) {
                    if ($DB->insert_record($tablename, $record)) {
                        $userfeedback += 1; // 0*2^1+1*2^0
                    }
                } else {
                    $userfeedback += 1; // 0*2^1+1*2^0
                }
            } else {
                $userfeedback += 0; // 0*2^1+0*2^0
            }

            $logaction = ($userfeedback) ? 'add item' : 'add item failed';

            // special mention for the "editor" field
            if ($this->item_form_requires['content_editor']) { // i.e. content
                $context = context_module::instance($cm->id);
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
                $record = file_postupdate_standard_editor($record, 'content', $editoroptions, $context, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $record->itemid);
                $record->contentformat = FORMAT_HTML;

                // survey_item
                // id
                $record->id = $record->itemid;

                $DB->update_record('survey_item', $record);
            }

        } else {

            // special mention for the "editor" field
            if ($this->item_form_requires['content_editor']) { // i.e. content
                $context = context_module::instance($cm->id);
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
                $record = file_postupdate_standard_editor($record, 'content', $editoroptions, $context, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $record->itemid);
                $record->contentformat = FORMAT_HTML;
            } else { // i.e. fieldset
                $record->content = null;
                $record->contentformat = null;
            }

            // hide/regular part 2
            $oldhide = $DB->get_field('survey_item', 'hide', array('id' => $record->itemid)); // used later
            // end of: hide/regular 2

            // survey_item
            // id
            $record->id = $record->itemid;

            // sortindex
            // doesn't change at item editing time

            if ($DB->update_record('survey_item', $record)) {
                // $tablename
                if ($this->flag->useplugintable) {
                    $record->id = $record->pluginid;
                    if ($DB->update_record($tablename, $record)) {
                        // $status = SURVEY_ITEMEDITED;
                        $userfeedback += 3; // 1*2^1+1*2^0
                    } else {
                        // $status = SURVEY_ITEMEDITFAIL;
                        $userfeedback += 2; // 1*2^1+0*2^0
                    }
                } else {
                    // $status = SURVEY_ITEMADDED;
                    $userfeedback += 3; // 1*2^1+1*2^0
                }
            } else {
                // $status = SURVEY_ITEMEDITFAIL;
                $userfeedback += 2; // 1*2^1+0*2^0
            }

            $logaction = ($userfeedback) ? 'update item' : 'update item failed';

            if ($record->id) { // if the item was successfully saved
                // hide/regular part 3
                if ( ($oldhide == 1) && ($record->hide == 0) ) {
                    if (survey_manage_item_show(1, $cm, $record->itemid, $record->type)) {
                        // a chain of record has been shown
                        $userfeedback += 4; // 1*2^2
                    }
                }
                if ( ($oldhide == 0) && ($record->hide == 1) ) {
                    if (survey_manage_item_hide(1, $cm, $record->itemid, $record->type)) {
                        // a chain of record has been shown
                        $userfeedback += 8; // 1*2^3
                    }
                }
                // end of: hide/regular part 3

                // adesso, indipendentemente dalla paternità, verifica che i figli siano nella stessa user form
                // se stanno in una differente form, spostali
                if (isset($record->basicform)) { // if the item is not in the user form
                    if ($record->basicform != SURVEY_NOTPRESENT) { // if the item is not in the user form
                        if (survey_move_regular_items($record->itemid, $record->basicform)) {
                            // a chain of record has been shown
                            $userfeedback += 16; // 1*2^4
                        }
                    }

                    if ($record->basicform == SURVEY_NOTPRESENT) { // if the item is in the user form
                        if (survey_move_regular_items($record->itemid, 0)) {
                            // a chain of record has been shown
                            $userfeedback += 32; // 1*2^5
                        }
                    }
                }
            }
        }
        $logurl = 'itembase.php?id='.$cm->id.'&tab='.SURVEY_TABITEMS.'&itemid='.$record->itemid.'&type='.$record->type.'&plugin='.$record->plugin.'&pag='.SURVEY_ITEMS_MANAGE;
        add_to_log($cm->course, 'survey', $logaction, $logurl, $record->itemid, $cm->id);

        // $userfeedback is for user feedback purpose only in manageitems.php
        return $userfeedback;
    }

    /*
     * item_builtin_string_load_support
     * questa funzione serve a popolare le stringhe vuote con le stringhe nella lingua dell'utente
     * @param $fields
     * @return
     */
    public function item_builtin_string_load_support($fields=null) {
        if (empty($this->externalname)) {
            return;
        }

        if (is_null($fields)) {
            $fields = array('content');
        }
        if (!is_array($fields)) {
            throw new moodle_exception('Array or null are expected in item_builtin_string_load_support');
        }

        // special care for content editor
        foreach ($fields as $fieldname) {
            if (!isset($this->{$fieldname.'_sid'})) {
                continue;
            }
            if (!strlen($this->{$fieldname.'_sid'})) {
                continue;
            }

            $stringindex = $fieldname.sprintf('%02d', $this->{$fieldname.'_sid'});
            $this->{$fieldname} = get_string($stringindex, 'surveytemplate_'.$this->externalname);
        }
    }

    /*
     * item_builtin_string_save_support
     * starting from the item object, replace 'content' and $fields for builtin survey
     * @param $fields
     * @return
     */
    public function item_builtin_string_save_support(&$record, $fields=null) {
        if (empty($this->externalname)) {
            return;
        }

        if (is_null($fields)) {
            $fields = array();
            $fields[] = 'content';
        }
        if (!is_array($fields)) {
            throw new moodle_exception('Array or null are expected in item_builtin_string_save_support');
        }

        if (in_array('content', $fields)) {
            // special care for content editor
            if (!is_null($this->content_sid)) { // se è previsto che il campo 'content' sia multilang
                $stringindex = 'content'.sprintf('%02d', $this->content_sid);
                $referencestring = get_string($stringindex, 'surveytemplate_'.$this->externalname);

                if ($record->content_editor['text'] === $referencestring) {
                    // leave the field empty
                    $record->content_editor['text'] = null;
                // } else {
                    // $record->content_editor['text'] holds a custom text. Do not touch it.
                }
            }

            // content has already been managed: take it off now
            $fields = array_diff($fields, array('content'));
        }

        // usually this routine is not executed
        // $fields['options'] = 'options_sid';
        foreach ($fields as $fieldname) {
            if (!is_null($this->{$fieldname.'_sid'})) { // se è previsto che il campo $fieldname sia multilang
                $stringindex = $fieldname.sprintf('%02d', $this->{$fieldname.'_sid'});
                $referencestring = get_string($stringindex, 'surveytemplate_'.$this->externalname);

                $record->{$fieldname} = str_replace("\r", '', $record->{$fieldname});
                if ($record->{$fieldname} === $referencestring) {
                    // leave the field empty
                    $record->{$fieldname} = null;
                // } else {
                    // $this->{$fieldname} holds a custom text. Do not touch it.
                }
            }
        }
    }

    /*
     * item_get_full_info == extranote + fillinginstruction
     * provides extra description THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form
     * @param
     * @return
     */
    public function item_get_full_info($searchform) {
        global $CFG;

        if (!$searchform) {
            if (!$this->hideinstructions) {
               $fillinginstruction = $this->item_get_filling_instructions();
            }
            if (isset($this->extranote)) {
                $extranote = strip_tags($this->extranote);
            }
        } else {
            if ($CFG->survey_fillinginstructioninsearch) {
                $fillinginstruction = $this->item_get_filling_instructions();
            }
            if ($CFG->survey_extranoteinsearch) {
                $extranote = strip_tags($this->extranote);
            }
        }
        if (isset($fillinginstruction) && $fillinginstruction && isset($extranote) && $extranote) {
            return ($fillinginstruction.'<br />'.$extranote);
        } else {
            if (isset($fillinginstruction) && $fillinginstruction) {
                return $fillinginstruction;
            }
            if (isset($extranote) && $extranote) {
                return $extranote;
            }
        }
    }

    /*
     * item_get_filling_instructions
     * provides extra fillinginstruction THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form
     * @param
     * @return
     */
    public function item_get_filling_instructions() {
        // if this method is not handled at plugin level,
        // it means it is supposed to return an empty fillinginstruction
        return '';
    }

    /*
     * item_split_unix_time
     * @param $time
     * @return
     */
    public function item_split_unix_time($time, $applyusersettings=false) {
        if ($applyusersettings) {
            $datestring = userdate($time, '%B_%A_%j_%Y_%m_%w_%d_%H_%M_%S');
        } else {
            $datestring = gmstrftime('%B_%A_%j_%Y_%m_%w_%d_%H_%M_%S', $time);
        }
        // Luglio_Mercoledì_193_2012_07_3_11_16_03_59

        // be careful to ensure the returned array matches that produced by getdate() above
        list(
            $getdate['month'],
            $getdate['weekday'],
            $getdate['yday'],
            $getdate['year'],
            $getdate['mon'],
            $getdate['wday'],
            $getdate['mday'],
            $getdate['hours'],
            $getdate['minutes'],
            $getdate['seconds']
        ) = explode('_', $datestring);

        // print_object($getdate);
        return $getdate;
    }

    /*
     * item_delete_item
     * @param $itemid, $displaymessage=false
     * @return
     */
    public function item_delete_item($itemid, $displaymessage=false) {
        global $DB, $cm, $USER, $COURSE, $OUTPUT;

        $recordtokill = $DB->get_record('survey_item', array('id' => $itemid));
        if (!$DB->delete_records('survey_item', array('id' => $itemid))) {
            print_error('Unable to delete survey_item id='.$itemid);
        }

        if ($this->flag->useplugintable) {
            if (!$DB->delete_records('survey_'.$this->plugin, array('id' => $this->pluginid))) {
                print_error('Unable to delete record id = '.$this->pluginid.' from surveyitem_'.$this->plugin);
            }
        }

        if (isset($cm)) {
            add_to_log($COURSE->id, 'survey', 'delete item', 'view.php?id='.$cm->id, get_string('item', 'survey'), $cm->id, $USER->id);
        }

        survey_reset_items_pages($cm->instance);

        // delete records from survey_userdata
        // if, at the end, the related survey_submissions has no data, then, delete it too.
        if ($DB->delete_records('survey_userdata', array('itemid' => $itemid))) {
            add_to_log($COURSE->id, 'survey', 'delete fields', 'view.php?id='.$cm->id, get_string('surveyfield', 'survey'), $cm->id, $USER->id);
        } else {
            print_error('Unable to delete records with itemid = '.$itemid.' from survey_userdata');
        }

        $emptysurveys = 'SELECT c.id
                             FROM {survey_submissions} c
                                 LEFT JOIN {survey_userdata} d ON c.id = d.submissionid
                             WHERE (d.id IS null)';
        if ($surveytodelete = $DB->get_records_sql($emptysurveys)) {
            $surveytodelete = array_keys($surveytodelete);
            if ($DB->delete_records_select('survey_submissions', 'id IN ('.implode(',', $surveytodelete).')')) {
                add_to_log($COURSE->id, 'survey', 'item deletet', 'view.php?id='.$cm->id, get_string('survey', 'survey'), $cm->id, $USER->id);
            } else {
                print_error('Unable to delete record id IN '.implode(',', $surveytodelete).' from survey_submissions');
            }
        }

        if ($displaymessage) {
            $a = survey_get_sid_field_content($recordtokill);
            if (empty($a)) {
                $a = get_string('userfriendlypluginname', 'surveyformat_'.$plugin);
            }
            $message = get_string('itemdeleted', 'survey', $a);
            echo $OUTPUT->box($message, 'notice centerpara');
        }
    }

    /*
     * item_set_editor
     * defines presets for the editor field of surveyitem in itembase_form.php
     * (copied from moodle20/cohort/edit.php)
     * @param $cmid, &$saveditem
     * @return
     */
    public function item_set_editor($cmid, &$saveditem) {
        $fieldname = 'content';

        $context = context_module::instance($cmid);

        $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => -1, 'context' => $context);

        $saveditem->{$fieldname.'format'} = FORMAT_HTML;
        $saveditem->{$fieldname.'trust'} = 1;

        $saveditem = file_prepare_standard_editor($saveditem, $fieldname, $editoroptions, $context, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $saveditem->itemid);
    }

    /*
     * item_get_value_label_array
     * translates the class property $this->{$field} in the array array[$value] = $label
     * @param $field='options'
     * @return array $valuelabel
     */
    public function item_get_value_label_array($field='options') {
        $options = survey_textarea_to_array($this->{$field});

        $valuelabel = array();
        foreach ($options as $option) {
            if (preg_match('/^(.*)'.SURVEY_VALUELABELSEPARATOR.'(.*)$/', $option, $match)) { // do not worry: it can never be equal to zero
                // print_object($match);
                $valuelabel[$match[1]] = $match[2];
            } else {
                $valuelabel[$option] = $option;
            }
        }

        return $valuelabel;
    }

    /*
     * item_get_other
     * @param
     * @return
     */
    public function item_get_other() {
        if (preg_match('/^(.*)'.SURVEY_OTHERSEPARATOR.'(.*)$/', $this->labelother, $match)) { // do not warn: it can never be equal to zero
            $value = trim($match[2]);
            $label = trim($match[1]);
        } else {
            $value = '';
            $label = trim($this->labelother);
        }

        return array($value, $label);
    }

    /*
     * item_get_one_word_per_row
     * @param $field='defaultvalue'
     * @return
     */
    public function item_get_one_word_per_row($field='defaultvalue') {
        // it doesn't matter if default is single- o multi-row
        // this function works fine in both cases

        $content = survey_textarea_to_array($this->{$field});

        return $content;
    }

    /*
     * item_complete_option_array
     * @param
     * @return
     */
    public function item_complete_option_array() {

        $options = explode("\n", $this->options);

        $return = array();
        foreach ($options as $option) {
            if (strpos($option, SURVEY_VALUELABELSEPARATOR) === false) {
                $return[$option] = $option;
            } else {
                $pair = explode(SURVEY_VALUELABELSEPARATOR, $option);
                $return[$pair[0]] = $pair[1];
            }
        }
        return $return;
    }

    /*
     * item_mandatory_is_allowed
     * @param
     * @return
     */
    public function item_mandatory_is_allowed() {
        // ATTENZIONE:
        //     c'è differenza fra un default vuoto
        //     e il default pari a SURVEY_NOANSWERDEFAULT
        // il primo prevede una risposta senza un default (questa opzione è compatibile con le domande obbligatorie)
        // il secondo prevede la prewsenza della checkbox == no answer CHE NON HA SENSO per le domande obbligatorie
        if (isset($this->defaultoption)) {
            return ($this->defaultoption != SURVEY_NOANSWERDEFAULT);
        } else {
            return true;
        }
    }

    /*
     * item_parentcontent_format_validation
     * checks whether the format of the "parentcontent" content is correct
     *
     * I loaded this class as the class of the parent item.
     * My final goal is to check whether the content of the "parentcontent" field (of the child item) is correct
     * At first it may seem I may need to validate format and content both. This is not true because...
     * I only need to validate the format because the content does not matter.
     * The content does not matter because it has to match the allowed answers of a different question.
     * Even if I successfull check for the content match (between the "parentcontent" here and the allowed answers in the parent question)...
     * ... I can still edit the parent question later and change the list of allowed answers. So the content validation is useless, here.
     * I will perform it in the "Validate branching" age only whether requested.
     * @param $parentcontent
     * @return
     */
    public function item_parentcontent_format_validation($parentcontent) {
        // whether not overridden by specific class method, return false
        return false; // no format validation error has been found
    }

    /*
     * item_get_type
     * @param
     * @return
     */
    public function item_get_type() {
        return $this->type;
    }

    /*
     * item_get_plugin
     * @param
     * @return
     */
    public function item_get_plugin() {
        return $this->plugin;
    }

    /*
     * item_get_extrarow
     * @param
     * @return
     */
    public function item_get_extrarow() {
        return $this->extrarow;
    }

    /*
     * item_is_searchable
     * @param
     * @return
     */
    public function item_is_searchable() {
        return $this->flag->issearchable;
    }

    /*
     * item_has_sortindex
     * @param
     * @return
     */
    public function item_has_sortindex() {
        return !empty($this->sortindex);
    }

    /*
     * item_get_sortindex
     * @param
     * @return
     */
    public function item_get_sortindex() {
        return $this->sortindex;
    }

    /*
     * item_get_parent_format
     * @param
     * @return
     */
    public function item_get_parent_format() {
        return get_string('parentformat', 'surveyfield_'.$this->plugin);
    }

    /*
     * item_get_db_structure
     * returns true if the useform mform element for this item id is a group and false if not
     * @param
     * @return
     */
    public function item_get_db_structure($tablename=null, $dropid=true) {
        global $DB;

        if (empty($tablename)) {
            $tablename = 'survey_'.$this->plugin;
        }

        $dbstructure = array();
        if ($dbfields = $DB->get_columns($tablename)) {
            foreach ($dbfields as $dbfield) {
                $dbstructure[] = $dbfield->name;
            }
        }

        if ($dropid) {
            array_shift($dbstructure); // drop the first item: ID
        }

        return $dbstructure;
    }

    /*
     * item_get_main_text
     * returns the content of the field defined as main
     * @param
     * @return
     */
    public function item_get_main_text() {
        return $this->content;
    }

    /*
     * item_get_si_values
     * returns the content of the field defined as main
     * @param $data, $sistructure, $sisid
     * @return
     */
    public function item_get_si_values($data, $sistructure, $sisid) {
        global $DB;

        $pluginname = clean_filename($data->mastertemplatename);
        // echo '$data:';
        // var_dump($data);
        // echo '$sistructure:';
        // var_dump($sistructure);
        // echo '$sisid:';
        // var_dump($sisid);
        // die;

        $tablename = 'survey_item';

        // STEP 01: define the value aray
        // This loop assign the correct order to array elements
        // Now I am free to access/modify array elements with random order
        $values = array_combine(array_values($sistructure), array_pad(array('err'), count($sistructure), 'err'));

        // STEP 02: make corrections
        // $si_fields = array('surveyid', 'type', 'plugin', 'externalname',
        //                    'content_sid', 'content', 'contentformat', 'customnumber',
        //                    'extrarow', 'extranote', 'required', 'fieldname',
        //                    'indent', 'basicform', 'advancedsearch', 'hide',
        //                    'sortindex', 'basicformpage', 'advancedformpage', 'parentid',
        //                    'parentcontent', 'parentvalue', 'timecreated', 'timemodified');

        // let's start with _sid fields
        foreach ($sisid as $sidfield) { // today is one but, maybe, tomorrow...
            // override: $values[$content_sid];
            /*------------------------------------------------*/
            $values[$sidfield] = '$'.$sidfield;

            // override: $value['content']
            /*------------------------------------------------*/
            $field = substr($sidfield, 0, -4);
            $values[$field] = 'null';
        }

        // unset: $values['id']
        /*------------------------------------------------*/
        unset($values['id']);

        // $si_fields = array('surveyid', 'type', 'plugin', 'externalname'...

        // override: $value['surveyid']
        /*------------------------------------------------*/
        $values['surveyid'] = 0;

        // override: $value['type']
        /*------------------------------------------------*/
        $values['type'] = $this->type;

        // update: $this->plugin
        /*------------------------------------------------*/
        $values['plugin'] = '\''.$this->plugin.'\'';

        // update: $data->externalname
        /*------------------------------------------------*/
        $values['externalname'] = '\''.$pluginname.'\'';

        // $si_fields = array(...'content_sid', 'content', 'contentformat', 'customnumber',

        // override: $value['content_sid'] has already been done
        /*------------------------------------------------*/

        // override: $value['content'] has already been done
        /*------------------------------------------------*/

        // override: $value['contentformat']
        /*------------------------------------------------*/
        switch ($this->contentformat) {
            case FORMAT_PLAIN:
                $values['contentformat'] = 'FORMAT_PLAIN'; // <-- TODO: is this correct?
                break;
            case FORMAT_HTML:
                $values['contentformat'] = 'FORMAT_HTML';
                break;
            case '': // <-- whether $this->item_form_requires['content_editor'] = false;
                $values['contentformat'] = '\'\'';
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->contentformat = '.$this->contentformat);
        }

        // override: $value['customnumber']
        /*------------------------------------------------*/
        if (empty($this->customnumber)) { // it may be '' or '0'
            if (strlen($this->customnumber)) {
                $values['customnumber'] = '0';
            } else {
                $values['customnumber'] = '\'\'';
            }
        } else {
            $values['customnumber'] = '\''.$this->customnumber.'\'';
        }

        // $si_fields = array(...'extrarow', 'extranote', 'required', 'fieldname',

        // override: $value['extrarow']
        /*------------------------------------------------*/
        $values['extrarow'] = $this->extrarow;

        // override: $value['extranote']
        /*------------------------------------------------*/
        $values['extranote'] = empty($this->extranote) ? '\'\'' : '\''.$this->extranote.'\'';

        // override: $value['required']
        /*------------------------------------------------*/
        switch ($this->required) {
            case SURVEY_REQUIREDITEM:
                $values['required'] = 'SURVEY_REQUIREDITEM';
                break;
            case SURVEY_OPTIONALITEM:
                $values['required'] = 'SURVEY_OPTIONALITEM';
                break;
            case '\'\'': // <-- each item with $this->item_form_requires['required'] = false;
                $values['required'] = 'null';
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->required = '.$this->required);
        }

        // override: $value['fieldname']
        /*------------------------------------------------*/
        $values['fieldname'] = empty($this->fieldname) ? '\'\'' : '\''.$this->fieldname.'\'';

        // $si_fields = array(...'indent', 'basicform', 'advancedsearch', 'hide',

        // override: $value['indent']
        /*------------------------------------------------*/
        $values['indent'] = $this->indent;

        // override: $value['basicform']
        /*------------------------------------------------*/
        switch ($this->basicform) {
            case SURVEY_NOTPRESENT:
                $values['basicform'] = 'SURVEY_NOTPRESENT';
                break;
            case SURVEY_FILLONLY:
                $values['basicform'] = 'SURVEY_FILLONLY';
                break;
            case SURVEY_FILLANDSEARCH:
                $values['basicform'] = 'SURVEY_FILLANDSEARCH';
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->basicform = '.$this->basicform);
        }

        // override: $value['advancedsearch']
        /*------------------------------------------------*/
        switch ($this->advancedsearch) {
            case SURVEY_ADVFILLONLY:
                $values['advancedsearch'] = 'SURVEY_ADVFILLONLY';
                break;
            case SURVEY_ADVFILLANDSEARCH:
                $values['advancedsearch'] = 'SURVEY_ADVFILLANDSEARCH';
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->advancedsearch = '.$this->advancedsearch);
        }

        // override: $value['hide']
        /*------------------------------------------------*/
        $values['hide'] = $this->hide;

        // $si_fields = array(...'sortindex', 'basicformpage', 'advancedformpage', 'parentid',

        // override: $value['sortindex']
        /*------------------------------------------------*/
        $values['sortindex'] = '$sortindex';

        // override: $value['basicformpage']
        /*------------------------------------------------*/
        $values['basicformpage'] = '\'\'';

        // override: $value['advancedformpage']
        /*------------------------------------------------*/
        $values['advancedformpage'] = '\'\'';

        // override: $value['parentid']
        /*------------------------------------------------*/
        // nelle plugin metto nel campo parentid il sortindex del parent record per identificare il parent record al momento del caricamento della plugin
        if (empty($this->parentid)) {
            $values['parentid'] = '0';
        } else {
            // I save sortindex instead of parentid for portability reason
            $sqlparams = array('id' => $this->parentid);
            $values['parentid'] = $DB->get_field('survey_item', 'sortindex', $sqlparams, MUST_EXIST);
        }

        // $si_fields = array(...'parentcontent', 'parentvalue', 'timecreated', 'timemodified');

        // override: $value['parentcontent']
        /*------------------------------------------------*/
        if (empty($this->parentcontent)) { // it may be '' or '0'
            if (strlen($this->parentcontent)) {
                $values['parentcontent'] = '0';
            } else {
                $values['parentcontent'] = '\'\'';
            }
        } else {
            $values['parentcontent'] = $this->parentcontent;
        }

        // override: $value['parentvalue']
        /*------------------------------------------------*/
        if (empty($this->parentvalue)) { // it may be '' or '0'
            if (strlen($this->parentvalue)) {
                $values['parentvalue'] = '0';
            } else {
                $values['parentvalue'] = 'null';
            }
        } else {
            $values['parentvalue'] = $this->parentvalue;
        }

        // override: $value['timecreated']
        /*------------------------------------------------*/
        $values['timecreated'] = time();

        // override: $value['timemodified']
        /*------------------------------------------------*/
        $values['timemodified'] = '\'\'';

        // just a check before assuming all has been done correctly
        $errindex = array_search('err', $values, true);
        if ($errindex !== false) {
            throw new moodle_exception('$values[\''.$errindex.'\'] of survey_items was not properly managed');
        }

        return $values;
    }

    /*
     * item_get_plugin_values
     * @param $pluginstructure, $pluginsid
     * @return
     */
    public function item_get_plugin_values($pluginstructure, $pluginsid) {

        // STEP 01: define the value aray
        // This loop assign the correct order to array elements
        // Now I am free to access/modify array elements with random order
        $values = array_combine (array_values($pluginstructure), array_pad(array('err'), count($pluginstructure), 'err'));

        // STEP 02: make few corrections

        // let's start with _sid fields
        foreach ($pluginsid as $sidfield) {
            // update: $values['content_sid']
            /*------------------------------------------------*/
            $values[$sidfield] = '$'.$sidfield;

            // override: $value['content']
            /*------------------------------------------------*/
            $field = substr($sidfield, 0, -4);
            $values[$field] = 'null';
        }

        // unset: $values['id']
        /*------------------------------------------------*/
        unset($values['id']);

        // override: $value['surveyid']
        /*------------------------------------------------*/
        $values['surveyid'] = 0;

        // override: $value['itemid']
        /*------------------------------------------------*/
        $values['itemid'] = '$itemid';

        if (in_array('defaultoption', $pluginstructure)) {
            $values = $this->item_update_values_defaultoption($values);
        }

        foreach ($values as $k => $v) {
            if ($v === 'err') { // the field has not been touched
                // look at the value stored in $this
                if (is_null($this->{$k})) {
                    $values[$k] = 'null';
                } else {
                    if (empty($this->{$k})) {
                        if (strlen($this->{$k})) {
                            $values[$k] = '0';
                        } else {
                            $values[$k] = '\'\'';
                        }
                    } else {
                        $values[$k] = '\''.$this->{$k}.'\'';
                    }
                }
            }
        }

        return $values;
    }

    /*
     * item_update_values_defaultoption
     * @param $values
     * @return
     */
    public function item_update_values_defaultoption($values) {
        // override: $value['defaultoption']
        /*------------------------------------------------*/
        switch ($this->defaultoption) {
            case SURVEY_CUSTOMDEFAULT:
                $values['defaultoption'] = 'SURVEY_CUSTOMDEFAULT';
                break;
            case SURVEY_INVITATIONDEFAULT:
                $values['defaultoption'] = 'SURVEY_INVITATIONDEFAULT';
                break;
            case SURVEY_NOANSWERDEFAULT:
                $values['defaultoption'] = 'SURVEY_NOANSWERDEFAULT';
                break;
            case SURVEY_LIKELASTDEFAULT:
                $values['defaultoption'] = 'SURVEY_LIKELASTDEFAULT';
                break;
            case SURVEY_TIMENOWDEFAULT:
                $values['defaultoption'] = 'SURVEY_TIMENOWDEFAULT';
                break;
            case SURVEY_INVITATIONDBVALUE:
                $values['defaultoption'] = 'SURVEY_INVITATIONDBVALUE';
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
        }

        return $values;
    }

    /*
     * userform_child_item_allowed_static
     * as parentitem defines whether a child item is supposed to be enabled in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called when $survey->newpageforchild == false
     * so the current survey lives in just one single web page (unless page break is manually added)
     * ----------------------------------------------------------------------
     * Am I getting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_item_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use userform_child_item_allowed_static
     * ----------------------------------------------------------------------
     * @param: $parentcontent, $parentsubmitted
     * @return
     */
    function userform_child_item_allowed_static($submissionid, $childitemrecord) {
        global $DB;

        if (!$childitemrecord->parentid) {
            return true;
        }

        $where = array('submissionid' => $submissionid, 'itemid' => $this->itemid);
        $givenanswer = $DB->get_field('survey_userdata', 'content', $where);

        return ($givenanswer === $childitemrecord->parentvalue);
    }

    /* *****************************
     * THIS METHOD IS NO LONGER USED
     * *****************************
     *
     * userform_could_be_disabled
     * This function returns true if an item can be disabled because of the answer to the parent item
     * The rationale is:
     * if ($survey->newpageforchild) { then the parent is in a previous page so:
     *     if its condition was satisfied, the child item ($this) is displayed
     *     if its condition was NOT satisfied, the child item ($this) is NOT displayed
     *     in both cases the child item ($this) will always be enabled
     *
     * if (empty($parentitem))
     *     the child item ($this) will always be enabled because the parent does not exist
     *
     * if no pagebreaks were added between parent and child (alias, if they are displayed in the same page)
     *     the child item ($this) can be disabled
     * @param
     * @return
     */
    public function userform_could_be_disabled($survey, $canaccessadvancedform, $parentitem=null) {
        global $DB;

        if ($survey->newpageforchild) {
            return false;
        }
        if (empty($parentitem)) {
            return false;
        }

        // is its parentitem in its same page?
        $pagefield = ($canaccessadvancedform) ? 'advancedformpage' : 'basicformpage';
        return ($parentitem->{$pagefield} == $this->{$pagefield});
    }

    /*
     * userform_can_show_item_as_child
     * @param
     * @return
     */
    public function userform_can_show_item_as_child($submissionid, $data) {
        global $DB;

        if (!$this->parentid) { // item is not a child, show it
            return true;
        }

        if (!$survey->newpageforchild) { // all in the same page (if page breaks are not added manually)
            // parent item is probably in this same page BUT CAN even be in a previous page
            foreach ($data as $itemname => $itemvalue) {
                if (preg_match('~^(\w+)_('.SURVEY_TYPEFIELD.'|'.SURVEY_TYPEFORMAT.')_(\w+)_([0-9]+)$~', $itemname, $match)) {
                    if ($match[4] == $this->parentid) { // parent item has been found in this page
                        return ($data[$itemname] == $this->parentcontent);
                    }
                }
            }
        }

        // if execution is still here,
        // parent item is in a previous page
        $where = array('submissionid' => $submissionid, 'itemid' => $this->parentid);
        $givenanswer = $DB->get_field('survey_userdata', 'content', $where);
        return ($givenanswer === $this->parentvalue);
    }

    /*
     * userform_child_item_allowed_dynamic
     * as parentitem defines whether a child item is supposed to be enabled in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called when $survey->newpageforchild == false
     * so the current survey lives in just one single web page (unless page break is manually added)
     * ----------------------------------------------------------------------
     * Am I geting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_item_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use userform_child_item_allowed_static
     * ----------------------------------------------------------------------
     * @param: $parentcontent, $parentsubmitted
     * @return
     */
    public function userform_child_item_allowed_dynamic($child_parentcontent, $data) {
        return ($data[$this->itemname] == $child_parentcontent);
    }

    /*
     * userform_disable_element
     * this function is used ONLY if $survey->newpageforchild == true
     * it adds as much as needed $mform->disabledIf to disable items when parent condition does not match
     * This method is used by the child item
     * In the frame of this method the parent item is calculated and is requested to provide the disabledif conditions to disable its child item
     * @param
     * @return
     */
    public function userform_disable_element($mform, $canaccessadvancedform) {
        global $DB;

        if (!$this->parentid || ($this->type == SURVEY_TYPEFORMAT)) {
            return;
        }

        if ($this->userform_mform_element_is_group()) {
            $fieldname = $this->itemname.'_group';
        } else {
            $fieldname = $this->itemname;
        }

        $pagefield = ($canaccessadvancedform) ? 'advancedformpage' : 'basicformpage';
        $parentrestrictions = array();
        $parentrecord = $this;
        do {
            /*
             * Take care.
             * Even if (!$survey->newpageforchild) I can have all my ancestors into previous pages because I added pagebreak manually
             * Because of this, I need to chech page numbers
             */
            $parentpage = $DB->get_field('survey_item', $pagefield, array('id' => $parentrecord->parentid));
            if ($parentpage == $this->{$pagefield}) {
                $parentid = $parentrecord->parentid;
                $parentcontent = $parentrecord->parentcontent;
                $parentrestrictions[$parentid] = $parentcontent; // Item ID $parentid has as constain $parentcontent
            } else {
                // my parent is in a page before mine
                // no need to investigate more for older ancestors
                break;
            }

            $parentrecord = $DB->get_record('survey_item', array('id' => $parentid), 'parentid, parentcontent');
        } while (!empty($parentrecord->parentid));
        // $parentrecord is an associative array
        // In the array key is the ID of the parent item, the corresponding value is the constrain that $this has to be submitted to

        foreach ($parentrestrictions as $parentid => $childconstrain) {
            $parentitem = survey_get_item($parentid);
            $disabilitationinfo = $parentitem->userform_get_parent_disabilitation_info($childconstrain);

            $displaydebuginfo = false;
            if ($displaydebuginfo) {
                foreach ($disabilitationinfo as $parentinfo) {
                    if (isset($parentinfo->operator)) {
                        echo '<span style="color:green;">$mform->disabledIf(\''.$fieldname.'\', \''.$parentinfo->parentname.'\', \''.$parentinfo->operator.'\', \''.$parentinfo->content.'\');</span><br />';
                    } else {
                        echo '<span style="color:green;">$mform->disabledIf(\''.$fieldname.'\', \''.$parentinfo->parentname.'\', \''.$parentinfo->content.'\');</span><br />';
                    }
                }
            }

            // write disableIf
            foreach ($disabilitationinfo as $parentinfo) {
                if (isset($parentinfo->operator)) {
                    $mform->disabledIf($fieldname, $parentinfo->parentname, $parentinfo->operator, $parentinfo->content);
                } else {
                    $mform->disabledIf($fieldname, $parentinfo->parentname, $parentinfo->content);
                }
            }
        }
    }

    /*
     * userform_display_as_read_only
     * @param
     * @return
     */
    public function userform_display_as_read_only($itemvalue) { // no longer used - obsolete

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $missinganswer = get_string('missinganswer', 'survey');
        $submitted = empty($itemvalue->content) ? $missinganswer : $this->userform_db_to_export($itemvalue);
        echo '<div class="fitem">
            <div class="fitemtitle">
                <div class="fstaticlabel">
                    <label>'.$elementlabel.'</label>
                </div>
            </div>
            <div class="felement fstatic">'.
                $submitted.'
            </div>
        </div>';
    }

    /*
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     * @param $richsubmission
     * @return
     */
    public function userform_db_to_export($itemvalue) {
        return $itemvalue->content;
    }
}