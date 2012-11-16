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


/**
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

/**
 * The base class representing a field
 */
class surveyitem_base {

    /************************************************************************************/
    /* BEGIN OF FIELDS OF SURVEY_ITEMS */
    /************************************************************************************/

    /**
     * unique itemid of the surveyitem in survey_item table
     */
    public $itemid = 0;

    /**
     * $surveyid = the id of the survey
     */
    public $surveyid = 0;

    /**
     * $type = the type of the item. It can only be: SURVEY_FIELD or SURVEY_FORMAT
     */
    public $type = '';

    /**
     * $plugin = the item plugin
     */
    public $plugin = '';

    /**
     * $externalname = a string specifing the origin of the item.
     * empty: user made it
     * non empty: belong to a built-in survey
     */
    public $externalname = '';

    /**
     * $content_sid = a number specifing the ID of the builtin survey item.
     * empty: user made it
     * non empty: belong to a built-in survey
     */
    public $content_sid = null;

    /**
     * $content = the text content of the item.
     */
    public $content = '';

    /**
     * $contentformat = the text format of the item.
     * public $contentformat = '';
     */
    public $contentformat = '';

    /**
     * $customnumber = the custom number of the item.
     * It usually is 1. 1.1, a, 2.1.a...
     */
    public $customnumber = '';

    /**
     * $extrarow = is the extrarow required?
     */
    public $extrarow = 0;

    /**
     * $softinfo = an optional text describing the item
     */
    public $softinfo = '';

    /**
     * $required = boolean. O == optional item; 1 == mandatory item
     */
    public $required = 0;

    /**
     * $fieldname = the name of the field storing data in the db table
     */
    public $fieldname = '';

    /**
     * $indent = the indent of the item in the form layout/template
     */
    public $indent = 0;

    /**
     * $basicform = will this item be part of users edit/search forms?
     * SURVEY_NOTPRESENT   : no
     * SURVEY_FILLONLY     : yes, only in the "edit" form
     * SURVEY_FILLANDSEARCH: yes, in the "edit" and in the "search" form too
     */
    public $basicform = 1;

    /**
     * $advancedsearch = will this item be part of the advanced search form?
     * SURVEY_ADVFILLONLY     : no, it will not be part
     * SURVEY_ADVFILLANDSEARCH: yes, it will be part of the advanced search form
     */
    public $advancedsearch = 0;

    /**
     * $draft = is this field going to be shown in the form?
     */
    public $draft = 0;

    /**
     * $sortindex = the order of this item in the survey form
     */
    public $sortindex = 0;

    /**
     * $basicformpage = the user survey page for this item
     */
    public $basicformpage = 0;

    /**
     * $advancedformpage = the advanced survey page for this item
     */
    public $advancedformpage = 0;

    /**
     * $parentid = the item this item depends from
     */
    public $parentid = 0;

    /**
     * $parentcontent = the constrain given by item parentid as entered by the survey creator (25/4/1860)
     */
    public $parentcontent = '';

    /**
     * $parentvalue = the "well written" constrain given by parentid (1594832670)
     */
    public $parentvalue = '';

    /**
     * $timecreated = the creation time of this item
     */
    public $timecreated = 0;

    /**
     * $timemodified = the modification time of this item
     */
    public $timemodified = null;

    /**
     * $flag = features describing the object
     * I can redeclare the public and protected method/property, but not private
     * so I choose to not declare this properties here
     * public $flag = null;
     */

    /**
     * $item_form_requires = list of fields the survey creator will need/see/use in the item definition form
     * By default each item is present in the form
     * so, in each child class, I only need to "deactivate" fields I don't want to see
     */
    public $item_form_requires = array(
        'common_fs' => true,
        'content_editor' => true,
        'customnumber' => true,
        'extrarow' => true,
        'softinfo' => true,
        'required' => true,
        'fieldname' => true,
        'indent' => true,
        'basicform' => true,
        'advancedsearch' => true,
        'draft' => true,
        'parentid' => true,
        'parentcontent' => true
    );
    /************************************************************************************/
    /* END OF FIELDS OF SURVEY_ITEMS */
    /************************************************************************************/

    /**
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
        } else {
            debugging('Something was wrong at line '.__LINE__.' of file '.__FILE__.'!<br />I can not find the survey_item ID = '.$itemid.' using:<br />'.$sql);
        }
    }

    /**
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

        // special mention for the "editor" field
        if ($this->item_form_requires['content_editor']) { // i.e. content
            $context = context_module::instance($cm->id);
            $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
            $record = file_postupdate_standard_editor($record, 'content', $editoroptions, $context, 'mod_survey', 'items', $record->itemid);
            $record->contentformat = FORMAT_HTML;
        } else { // i.e. fieldset
            $record->content = null;
            $record->contentformat = null;
        }

        // extrarow
        // $record->extrarow = (isset($record->extrarow)) ? 1 : 0; // extrarow is advcheckbox so doesn't need my intervention

        // draft
        // draft/regular part 1
        if ($this->item_form_requires['draft']) {
            $record->draft = isset($record->draft) ? 1 : 0;
        } else {
            $record->draft = null;
        }
        // end of: draft/regular part 1

        // advancedsearch
        if ($this->item_form_requires['advancedsearch']) {
            $record->advancedsearch = isset($record->advancedsearch) ? SURVEY_ADVFILLANDSEARCH : SURVEY_ADVFILLONLY;
        } else {
            $record->advancedsearch = SURVEY_ADVFILLONLY;
        }

        // encode $fromform->parentcontent to $item->parentvalue on the basis of the parentplugin specified in $record->parentid
        if ($record->parentid) { // I am sure parentcontent is here too
            $parentplugin = $DB->get_field('survey_item', 'plugin', array('id' => $record->parentid));
            require_once($CFG->dirroot.'/mod/survey/field/'.$parentplugin.'/plugin.class.php');
            $itemclass = 'surveyfield_'.$parentplugin;
            $parentitem = new $itemclass($record->parentid);

            $record->parentvalue = $parentitem->item_parent_content_encode_value($record->parentcontent);
        }

        // $userfeedback
        //   +--- children moved out from user entry form
        //   |       +--- children moved in the user entry form
        //   |       |       +--- child drafted because of this item draft
        //   |       |       |       +--- parent undrafted because of this item undraft
        //   |       |       |       |       +--- new|edit
        //   |       |       |       |       |       +--- success|fail
        // [0|1] - [0|1] - [0|1] - [0|1] - [0|1] - [0|1]
        // l'ultima cifra == 1 significa che il processo è andato a buon fine
        // l'ultima cifra == 0 significa che il processo NON è andato a buon fine
        // la penultima cifra == 0 significa NEW
        // la penultima cifra == 1 significa EDIT

        // la (cifra in posizione 2) == 1 significa item undrafted
        // la (cifra in posizione 3) == 1 significa item set to draft because of this item draft
        // la (cifra in posizione 4) == 1 significa item set to draft because this item was removed from the user entry form

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
                        $userfeedback += 1; //0*2^1+1*2^0
                    }
                } else {
                    $userfeedback += 1; //0*2^1+1*2^0
                }
            } else {
                $userfeedback += 0; //0*2^1+0*2^0
            }

            $logaction = ($userfeedback) ? 'add item' : 'add item failed';
        } else {
            // draft/regular part 2
            $olddraft = $DB->get_field('survey_item', 'draft', array('id' => $record->itemid)); // used later
            // end of: draft/regular 2

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
                        $userfeedback += 3; //1*2^1+1*2^0
                    } else {
                        // $status = SURVEY_ITEMEDITFAIL;
                        $userfeedback += 2; //1*2^1+0*2^0
                    }
                } else {
                    // $status = SURVEY_ITEMADDED;
                    $userfeedback += 3; //1*2^1+1*2^0
                }
            } else {
                // $status = SURVEY_ITEMEDITFAIL;
                $userfeedback += 2; //1*2^1+0*2^0
            }

            $logaction = ($userfeedback) ? 'update item' : 'update item failed';

            if ($record->id) { // if the item was successfully saved
                // draft/regular part 3
                if ( ($olddraft == 1) && ($record->draft == 0) ) {
                    if (survey_manage_item_show(1, $cm, $record->itemid, $record->type))  {
                        // una catena undrafted
                        $userfeedback += 4; //1*2^2
                    }
                }
                if ( ($olddraft == 0) && ($record->draft == 1) ) {
                    if (survey_manage_item_hide(1, $cm, $record->itemid, $record->type)) {
                        // una catena drafted
                        $userfeedback += 8; //1*2^3
                    }
                }
                // end of: draft/regular part 3


                // adesso, indipendentemente dalla paternità, verifica che i figli siano nella stessa user form
                // se stanno in una differente form, spostali
                if (isset($record->basicform)) { // if the item is not in the user form
                    if ($record->basicform != SURVEY_NOTPRESENT) { // if the item is not in the user form
                        if (survey_move_regular_items($record->itemid, $record->basicform)) {
                            // una catena drafted
                            $userfeedback += 16; //1*2^4
                        }
                    }

                    if ($record->basicform == SURVEY_NOTPRESENT) { // if the item is in the user form
                        if (survey_move_regular_items($record->itemid, 0)) {
                            // una catena drafted
                            $userfeedback += 32; //1*2^5
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

    /**
     * item_builtin_string_load_support
     * questa funzione serve a popolare le stringhe vuote con le stringhe nella lingua dell'utente
     * @param $fields
     * @return
     */
    public function item_builtin_string_load_support($fields=null) {
        if (empty($this->externalname)) return;

        if (is_null($fields)) {
            $fields = array('content');
        }
        if (!is_array($fields)) {
            throw new moodle_exception('Array or null are expected in item_builtin_string_load_support');
        }

        // special care for content editor
        foreach ($fields as $fieldname) {
            if (!isset($this->{$fieldname.'_sid'})) continue;
            if (!strlen($this->{$fieldname.'_sid'})) continue;

            $stringindex = $fieldname.sprintf('%02d', $this->{$fieldname.'_sid'});
            $this->{$fieldname} = get_string($stringindex, 'surveytemplate_'.$this->externalname);
        }
    }

    /**
     * item_builtin_string_save_support
     * starting from the item object, replace 'content' and $fields for builtin survey
     * @param $fields
     * @return
     */
    public function item_builtin_string_save_support(&$record, $fields=null) {
        if (empty($this->externalname)) return;

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

    /**
     * item_get_full_info == softinfo + hardinfo
     * provides extra description THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form
     * @param
     * @return
     */
    public function item_get_full_info($searchform) {
        global $CFG;

        if (!$searchform) {
            $hardinfo = $this->item_get_hard_info();
            $softinfo = (isset($this->softinfo)) ? strip_tags($this->softinfo) : '';
        } else {
            $hardinfo = ($CFG->survey_hardinfoinsearch) ? $this->item_get_hard_info() : '';
            $softinfo = ($CFG->survey_softinfoinsearch) ? strip_tags($this->softinfo) : '';
        }
        $separator = ($hardinfo && $softinfo) ? '<br />' : '';
        return ($hardinfo.$separator.$softinfo);
    }

    /**
     * item_get_hard_info
     * provides extra hardinfo THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form
     * @param
     * @return
     */
    public function item_get_hard_info() {
        // if this method is not handled at plugin level,
        // it means it is supposed to return an empty hardinfo
        return '';
    }

    /**
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

    /**
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
            $a = survey_get_sid_field_content($recordtokill, 'content');
            $message = get_string('itemdeleted', 'survey', $a);
            echo $OUTPUT->box($message, 'notice centerpara');
        }
    }

    /**
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

        $saveditem = file_prepare_standard_editor($saveditem, $fieldname, $editoroptions, $context, 'mod_survey', 'items', $saveditem->itemid);
    }

    /**
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

    /**
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

    /**
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

    /**
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

    /**
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

    /**
     * item_get_type
     * @param
     * @return
     */
    public function item_get_type() {
        return $this->type;
    }

    /**
     * item_get_plugin
     * @param
     * @return
     */
    public function item_get_plugin() {
        return $this->plugin;
    }

    /**
     * item_get_extrarow
     * @param
     * @return
     */
    public function item_get_extrarow() {
        return $this->extrarow;
    }

    /**
     * item_is_searchable
     * @param
     * @return
     */
    public function item_is_searchable() {
        return $this->flag->issearchable;
    }

    /**
     * item_is_matchable
     * @param
     * @return
     */
    public function item_is_matchable() {
        return $this->flag->ismatchable;
    }

    /**
     * item_has_sortindex
     * @param
     * @return
     */
    public function item_has_sortindex() {
        return !empty($this->sortindex);
    }

    /**
     * item_get_sortindex
     * @param
     * @return
     */
    public function item_get_sortindex() {
        return $this->sortindex;
    }

    /**
     * item_get_parent_format
     * @param
     * @return
     */
    public function item_get_parent_format() {
        return get_string('parentformat', 'surveyfield_'.$this->plugin);
    }

    /**
     * item_get_db_structure
     * returns true if the useform mform element for this item id is a group and false if not
     * @param
     * @return
     */
    public function item_get_db_structure($tablename=null, $dropid=true) {
        global $DB;

        if (empty($tablename)) $tablename = 'survey_'.$this->plugin;

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

    /**
     * item_get_main_text
     * returns the content of the field defined as main
     * @param
     * @return
     */
    public function item_get_main_text() {
        return $this->content;
    }

    /**
     * item_get_si_values
     * returns the content of the field defined as main
     * @param $data, $sistructure, $sisid
     * @return
     */
    public function item_get_si_values($data, $sistructure, $sisid) {
        global $DB;
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
        //                    'extrarow', 'softinfo', 'required', 'fieldname',
        //                    'indent', 'basicform', 'advancedsearch', 'draft',
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
        switch ($this->type) {
            case SURVEY_FIELD:
                $values['type'] = 'SURVEY_FIELD';
                break;
            case SURVEY_FORMAT:
                $values['type'] = 'SURVEY_FORMAT';
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $this->type = '.$this->type.'<br />';
                echo 'and the right "case" is missing<br />';
        }

        // update: $this->plugin
        /*------------------------------------------------*/
        $values['plugin'] = '\''.$this->plugin.'\'';

        // update: $data->externalname
        /*------------------------------------------------*/
        $values['externalname'] = '\''.$data->pluginname.'\'';



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
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $this->contentformat = '.$this->contentformat.'<br />';
                echo 'and the right "case" is missing<br />';
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


        // $si_fields = array(...'extrarow', 'softinfo', 'required', 'fieldname',

        // override: $value['extrarow']
        /*------------------------------------------------*/
        $values['extrarow'] = $this->extrarow;

        // override: $value['softinfo']
        /*------------------------------------------------*/
        $values['softinfo'] = empty($this->softinfo) ? '\'\'' : '\''.$this->softinfo.'\'';

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
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $this->required = '.$this->required.'<br />';
                echo 'and the right "case" is missing<br />';
        }

        // override: $value['fieldname']
        /*------------------------------------------------*/
        $values['fieldname'] = empty($this->fieldname) ? '\'\'' : '\''.$this->fieldname.'\'';


        // $si_fields = array(...'indent', 'basicform', 'advancedsearch', 'draft',

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
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $this->basicform = '.$this->basicform.'<br />';
                echo 'and the right "case" is missing<br />';
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
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $this->advancedsearch = '.$this->advancedsearch.'<br />';
                echo 'and the right "case" is missing<br />';
        }

        // override: $value['draft']
        /*------------------------------------------------*/
        $values['draft'] = $this->draft;


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
        $errindex = array_search('err', $values, TRUE);
        if ($errindex !== FALSE) {
            throw new moodle_exception('$values[\''.$errindex.'\'] of survey_items was not properly managed');
        }

        return $values;
    }

    /**
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
            $values = $this->update_values_defaultoption($values);
        }

        foreach($values as $k => $v) {
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

    /**
     * update_values_defaultoption
     * @param $values
     * @return
     */
    public function update_values_defaultoption($values) {
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
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $this->defaultoption = '.$this->defaultoption.'<br />';
                echo 'and the right "case" is missing<br />';
        }

        return $values;
    }

    /**
     * userform_can_add_required_rule
     * @param
     * @return
     */
    public function userform_can_add_required_rule($survey, $canaccessadvancedform, $parentitem=null) {
        global $DB;

        if ($survey->newpageforchild) return true;
        if (empty($parentitem)) return true;

        // is its parentitem in its same page?
        $pagefield = ($canaccessadvancedform) ? 'advancedformpage' : 'basicformpage';
        return ($parentitem->{$pagefield} < $this->{$pagefield});
    }

    /**
     * userform_child_is_allowed_dynamic
     * from parentcontent defines whether an item is supposed to be active (not disabled) in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called when $survey->newpageforchild == false
     * that is the current survey lives in just one single web page
     * ----------------------------------------------------------------------
     * Am I geting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_is_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use survey_child_is_allowed_static
     * ----------------------------------------------------------------------
     * @param: $parentcontent, $parentsubmitted
     * @return
     */
    public function userform_child_is_allowed_dynamic($child_parentcontent, $data) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        return ($data[$fieldname] == $child_parentcontent);
    }

    /**
     * userform_dispose_unexpected_values
     * this method is responsible for deletion of unexpected $fromform elements
     * @param $fromform
     * @return
     */
    public function userform_dispose_unexpected_values(&$fromform) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        if (isset($fromform->{$fieldname})) unset($fromform->{$fieldname});
    }

    /**
     * userform_disable_element
     * this function is used ONLY if $survey->newpageforchild == true
     * it disables items where parent condition does not match
     * @param
     * @return
     */
    public function userform_disable_element($mform, $searchform=false) {
        global $DB;

        if ($searchform) return;
        if (!$this->parentid || ($this->type == SURVEY_FORMAT)) return;

        $childname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
        if ($this->userform_mform_element_is_group()) {
            $childname .= '_group';
        }

        $parentseeds = array();
        $parentrecord = $this;
        do {
            $parentid = $parentrecord->parentid;
            $parentcontent = $parentrecord->parentcontent;
            $parentseeds[$parentcontent] = $parentid; // L'item ID = $parentid come vincolo ha: $parentcontent

            $parentrecord = $DB->get_record('survey_item', array('id' => $parentid), 'parentid, parentcontent');
        } while (!empty($parentrecord->parentid));

        // $parentseeds must have at least one item
        foreach ($parentseeds as $childcontent => $parentid) {
            $parentitem = survey_get_item($parentid);
            // ask to parent item which mform element stores relevant informations ($fieldname? $fieldname.'_month'?)
            $disabilitationinfo = $parentitem->userform_get_parent_disabilitation_info($childcontent);

            // write disableIf
            foreach ($disabilitationinfo as $parentinfo) {
                $mform->disabledIf($childname, $parentinfo->parentname, $parentinfo->operator, $parentinfo->content);
                // echo '$mform->disabledIf(\''.$childname.'\', \''.$parentinfo->parentname.'\', \''.$parentinfo->operator.'\', \''.$parentinfo->content.'\');<br />';
            }
        }
    }

    /**
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

    /**
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     * @param $richsubmission
     * @return
     */
    public function userform_db_to_export($itemvalue) {
        return $itemvalue->content;
    }
}