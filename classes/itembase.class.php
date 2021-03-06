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

/*
 * The base class defining an item
 */
class mod_survey_itembase {

    /*
     * unique itemid of the surveyitem in survey_item table
     */
    public $itemid = 0;

    /*
     * $surveyid = the id of the survey
     */
    public $surveyid = 0;

    /*
     * $context
     */
    public $context = '';

    /*
     * $type = the type of the item. It can only be: SURVEY_TYPEFIELD or SURVEY_TYPEFORMAT
     */
    public $type = '';

    /*
     * $plugin = the item plugin
     */
    public $plugin = '';

    /*
     * $itemname = the name of the field as it is in userpageform
     */
    public $itemname = '';

    /*
     * $hidden = is this field going to be shown in the form?
     */
    public $hidden = 0;

    /*
     * $insearchform = is this field going to be part of the search form?
     */
    public $insearchform = 0;

    /*
     * $advanced = is this field going to be available only to users with accessadvanceditems capability?
     */
    public $advanced = 0;

    /*
     * $sortindex = the order of this item in the survey form
     */
    public $sortindex = 0;

    /*
     * $formpage = the user survey page for this item
     */
    public $formpage = 0;

    /*
     * $parentid = the item this item depends from
     */
    public $parentid = 0;

    /*
     * $parentvalue = the answer the parent item has to have in order to show this item as child
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
     * $userfeedback
     */
    public $userfeedback = SURVEY_NOFEEDBACK;

    /*
     * $flag = features describing the object
     * I can redeclare the public and protected method/property, but not private
     * so I choose to not declare this properties here
     * public $flag = null;
     */

    /*
     * $formrequires = list of fields the survey creator will need/see/use in the item definition form
     * By default each item is present in the form
     * so, in each child class, I only need to "deactivate" mform element I don't want to have/see/use
     */
    public $formrequires = array(
        'common_fs' => true,
        'content' => true,
        'customnumber' => true,
        'position' => true,
        'extranote' => true,
        'hideinstructions' => true,
        'required' => true,
        'variable' => true,
        'indent' => true,
        'hidden' => true,
        'advanced' => true,
        'insearchform' => true,
        'parentid' => true
    );

    /*
     * item_load
     *
     * @param integer $itemid
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        global $DB;

        if (!$itemid) {
            debugging('Something was wrong at line '.__LINE__.' of file '.__FILE__.'! Can not load an item without its ID');
        }

        $sql = 'SELECT *, si.id as itemid, plg.id as pluginid
                FROM {survey_item} si
                    JOIN {survey'.$this->type.'_'.$this->plugin.'} plg ON si.id = plg.itemid
                WHERE si.id = :surveyitemid';

        if ($record = $DB->get_record_sql($sql, array('surveyitemid' => $itemid))) {
            foreach ($record as $option => $value) {
                $this->{$option} = $value;
            }
            unset($this->id); // I do not care it. I already heave: itemid and pluginid
            $this->itemname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
            if ($evaluateparentcontent && $this->parentid) {
                $parentitem = survey_get_item($this->parentid, null, null, false);
                $this->parentcontent = $parentitem->parent_decode_child_parentvalue($this->parentvalue);
            }
        } else {
            debugging('Something was wrong at line '.__LINE__.' of file '.__FILE__.'!<br />I can not find the survey item ID = '.$itemid.' using:<br />'.$sql);
        }
    }

    /*
     * save
     * Executes surveyitem_<<plugin>> global level actions
     * this is the save point of the global part of each plugin
     *
     * @param stdClass $record
     * @return
     */
    public function item_save($record) {
        global $CFG, $DB, $PAGE;

        $cm = $PAGE->cm;

        // you are going to change item content (maybe sortindex, maybe the parentitem)
        // so, do not forget to reset items per page
        survey_reset_items_pages($cm->instance);

        $timenow = time();

        // is this useless?
        // foreach ($this->formrequires as $k => $v) {
        //     if (!$v) {
        //         unset($record->{$k});
        //     }
        // }

        $tablename = 'survey'.$this->type.'_'.$this->plugin;

        // truncate extranote if longer than maximum allowed (255 characters)
        if (isset($record->extranote) && (strlen($record->extranote) > 255)) {
            $record->extranote = substr($record->extranote, 0, 255);
        }

        // do not forget surveyid
        $record->surveyid = $cm->instance;
        $record->timemodified = $timenow;

        // manage other checkboxes content
        $checkboxessettings = array('advanced', 'insearchform', 'hideinstructions', 'required', 'hidden');
        foreach ($checkboxessettings as $checkboxessetting) {
            if ($this->formrequires[$checkboxessetting]) {
                $record->{$checkboxessetting} = isset($record->{$checkboxessetting}) ? 1 : 0;
            } else {
                $record->{$checkboxessetting} = 0;
            }
        }

        // survey can be multilang
        // so I can not save labels to parentvalue as they may change
        // because of this, even if the user writes, for instance, "bread\nmilk" to parentvalue
        // I have to encode it to key(bread);key(milk)
        if (isset($record->parentid) && $record->parentid) {
            $parentitem = survey_get_item($record->parentid);
            $record->parentvalue = $parentitem->parent_encode_child_parentcontent($record->parentcontent);
            unset($record->parentcontent);
        }

        // $this->userfeedback
        //   +--- children inherited limited access
        //   |       +--- parents were made available for all
        //   |       |       +--- children were hided because this item was hided
        //   |       |       |       +--- parents were shown because this item was shown
        //   |       |       |       |       +--- new|edit
        //   |       |       |       |       |       +--- success|fail
        // [0|1] - [0|1] - [0|1] - [0|1] - [0|1] - [0|1]
        // last digit (on the right, of course) == 1 means that the process was globally successfull
        // last digit (on the right, of course) == 0 means that the process was globally NOT successfull

        // beforelast digit == 0 means NEW
        // beforelast digit == 1 means EDIT

        // (digit in place 2) == 1 means items were shown because this (as child) was shown
        // (digit in place 3) == 1 means items were hided because this (as parent) was hided
        // (digit in place 4) == 1 means items reamin as they are because unlimiting parent does not force any change to children
        // (digit in place 5) == 1 means items inherited limited access because this (as parent) got a limited access

        $this->userfeedback = SURVEY_NOFEEDBACK;

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
            $record->sortindex = 1 + $DB->count_records_sql($sql, $sqlparam);

            // itemid
            if ($itemid = $DB->insert_record('survey_item', $record)) { // <-- first save
                // $tablename
                $record->itemid = $itemid;
                if ($pluginid = $DB->insert_record($tablename, $record)) { // <-- first save
                    $this->userfeedback += 1; // 0*2^1+1*2^0
                }
            }

            $logaction = ($this->userfeedback == SURVEY_NOFEEDBACK) ? 'add item failed' : 'add item';

            // special care for "editors"
            if ($this->flag->editorslist) {
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $this->context);
                foreach ($this->flag->editorslist as $fieldname => $filearea) {
                    $record = file_postupdate_standard_editor($record, $fieldname, $editoroptions, $this->context, 'mod_survey', $filearea, $record->itemid);
                    $record->{$fieldname.'format'} = FORMAT_HTML;
                }

                // tablename
                // id
                $record->id = $pluginid;

                $DB->update_record($tablename, $record); // <-- update
                // } else {
                // record->content follows standard flow and has already been saved at first save time
            }
        } else {

            // special care for "editors"
            if ($this->flag->editorslist) {
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $this->context);
                foreach ($this->flag->editorslist as $fieldname => $filearea) {
                    $record = file_postupdate_standard_editor($record, $fieldname, $editoroptions, $this->context, 'mod_survey', $filearea, $record->itemid);
                    $record->{$fieldname.'format'} = FORMAT_HTML;
                }
                // } else {
                // record->content follows standard flow and will be evaluated in the standard way
            }

            // hide/unhide part 1
            $oldhide = $DB->get_field('survey_item', 'hidden', array('id' => $record->itemid)); // used later
            // end of: hide/unhide 1

            // limit/unlimit access part 1
            $oldadvanced = $DB->get_field('survey_item', 'advanced', array('id' => $record->itemid)); // used later
            // end of: limit/unlimit access part 1

            // sortindex
            // doesn't change at item editing time

            // survey_item
            // id
            $record->id = $record->itemid;

            if ($DB->update_record('survey_item', $record)) {
                // $tablename
                $record->id = $record->pluginid;
                if ($DB->update_record($tablename, $record)) {
                    $this->userfeedback += 3; // 1*2^1+1*2^0 alias: editing + success
                } else {
                    $this->userfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail
                }
            } else {
                $this->userfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail
            }

            $logaction = ($this->userfeedback == SURVEY_NOFEEDBACK) ? 'add item failed' : 'add item';

            // save process is over. Good.

            // now hide or unhide (whether needed) chain of ancestors or descendents
            if ($this->userfeedback & 1) { // bitwise logic, alias: if the item was successfully saved
                // -----------------------------
                // manage ($oldhide != $record->hidden)
                // -----------------------------
                if ($oldhide != $record->hidden) {
                    $survey = $DB->get_record('survey', array('id' => $cm->instance), '*', MUST_EXIST);
                    $action = ($oldhide) ? SURVEY_SHOWITEM : SURVEY_HIDEITEM;
                    $view = 0;
                    $itemtomove = 0;
                    $lastitembefore = 0;
                    $confirm = SURVEY_CONFIRMED_YES;
                    $nextindent = 0;
                    $parentid = 0;
                    $userfeedback = 0;
                    $saveasnew  = 0;
                    $itemlistman = new mod_survey_itemlist($cm, $this->context, $survey, $record->type, $record->plugin,
                                           $record->itemid, $action, $view, $itemtomove, $lastitembefore,
                                           $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);
                }

                // hide/unhide part 2
                if ( ($oldhide == 1) && ($record->hidden == 0) ) {
                    if ($itemlistman->manage_item_show()) {
                        // a chain of parent items has been showed
                        $this->userfeedback += 4; // 1*2^2
                    }
                }
                if ( ($oldhide == 0) && ($record->hidden == 1) ) {
                    if ($itemlistman->manage_item_hide()) {
                        // a chain of child items has been hided
                        $this->userfeedback += 8; // 1*2^3
                    }
                }
                // end of: hide/unhide part 2

                // -----------------------------
                // manage ($oldadvanced != $record->advanced)
                // -----------------------------
                if ($oldadvanced != $record->advanced) {
                    $survey = $DB->get_record('survey', array('id' => $cm->instance), '*', MUST_EXIST);
                    $action = ($oldadvanced) ? SURVEY_MAKEFORALL : SURVEY_MAKELIMITED;
                    $view = 0;
                    $itemtomove = 0;
                    $lastitembefore = 0;
                    $confirm = SURVEY_CONFIRMED_YES;
                    $nextindent = 0;
                    $parentid = 0;
                    $userfeedback = 0;
                    $saveasnew  = 0;
                    $itemlistman = new mod_survey_itemlist($cm, $context, $survey, $record->type, $record->plugin,
                                           $record->itemid, $action, $view, $itemtomove, $lastitembefore,
                                           $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);
                }
                // limit/unlimit access part 2
                if ( ($oldadvanced == 1) && ($record->advanced == 0) ) {
                    if ($itemlistman->manage_item_makestandard()) {
                        // a chain of parent items has been made available for all
                        $this->userfeedback += 16; // 1*2^4
                    }
                }
                if ( ($oldadvanced == 0) && ($record->advanced == 1) ) {
                    if ($itemlistman->manage_item_makeadvanced()) {
                        // a chain of child items got a limited access
                        $this->userfeedback += 32; // 1*2^5
                    }
                }
                // end of: limit/unlimit access part 2
            }
        }

        $logurl = 'itembase.php?id='.$cm->id.'&tab='.SURVEY_TABITEMS.'&itemid='.$record->itemid.'&type='.$record->type.'&plugin='.$record->plugin.'&pag='.SURVEY_ITEMS_MANAGE;
        add_to_log($cm->course, 'survey', $logaction, $logurl, $record->itemid, $cm->id);

        // $this->userfeedback is going to be part of $returnurl in items_setup.php and to be send to items_manage.php
    }

    /*
     * item_update_childparentvalue
     *
     * @param stdClass $survey
     * @return
     */
    public function item_update_childrenparentvalue() {
        global $DB;

        if ($this::$canbeparent) {
            // take care: you can not use $this->item_get_exportvalues_array('options') to evaluate $exportvalues
            // because $item was loaded before last save, so $this->item_get_exportvalues_array('options')
            // is still returning the previous $exportvalues

            $children = $DB->get_records('survey_item', array('parentid' => $this->itemid), 'id', 'id, parentvalue');
            foreach ($children as $child) {
                $childparentvalue = $child->parentvalue;

                // decode $childparentvalue to $childparentcontent
                $childparentcontent = $this->parent_decode_child_parentvalue($childparentvalue);

                // encode $childparentcontent to $childparentvalue, once again
                $child->parentvalue = $this->parent_encode_child_parentcontent($childparentcontent);

                // save the child
                $DB->update_record('survey_item', $child);
            }
        }
    }


    /*
     * item_builtin_string_load_support
     * This function is used to populate empty strings according to the user language
     *
     * @param stdClass $survey
     * @return
     */
    public function item_builtin_string_load_support() {
        global $CFG, $DB;

        $surveyid = $this->get_surveyid();
        $template = $DB->get_field('survey', 'template', array('id' => $surveyid), MUST_EXIST);
        if (empty($template)) {
            return;
        }

        // Take care: I verify the existence of the english folder even if, maybe, I will ask for the string in a different language
        if (!file_exists($CFG->dirroot.'/mod/survey/template/'.$template.'/lang/en/surveytemplate_'.$template.'.php')) {
            // this template does not support multilang
            return;
        }

        if ($multilangfields = $this->item_get_multilang_fields()) {
            foreach ($multilangfields as $plugin => $fieldnames) {
                foreach ($fieldnames as $fieldname) {
                    $stringkey = $this->{$fieldname};
                    $this->{$fieldname} = get_string($stringkey, 'surveytemplate_'.$template);
                }
            }
        }
    }

    /*
     * item_split_unix_time
     *
     * @param $time
     * @param $applyusersettings
     * @return
     */
    public function item_split_unix_time($time, $applyusersettings=false) {
        if ($applyusersettings) {
            $datestring = userdate($time, '%B_%A_%j_%Y_%m_%w_%d_%H_%M_%S', 0);
        } else {
            $datestring = gmstrftime('%B_%A_%j_%Y_%m_%w_%d_%H_%M_%S', $time);
        }
        // May_Tuesday_193_2012_07_3_11_16_03_59

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
     *
     * @param $itemid
     * @return
     */
    public function item_delete_item($itemid) {
        global $DB, $cm, $USER, $COURSE, $OUTPUT;

        $recordtokill = $DB->get_record('survey_item', array('id' => $itemid));
        if (!$DB->delete_records('survey_item', array('id' => $itemid))) {
            print_error('Unable to delete survey_item id='.$itemid);
        }

        if (!$DB->delete_records('survey'.$this->type.'_'.$this->plugin, array('id' => $this->pluginid))) {
            print_error('Unable to delete record id = '.$this->pluginid.' from survey'.$this->type.'_'.$this->plugin);
        }

        if (isset($cm)) {
            add_to_log($COURSE->id, 'survey', 'delete item', 'view.php?id='.$cm->id, get_string('item', 'survey'), $cm->id, $USER->id);
        }

        survey_reset_items_pages($cm->instance);

        // delete records from survey_userdata
        // if, at the end, the related survey_submission has no data, then, delete it too.
        if ($DB->delete_records('survey_userdata', array('itemid' => $itemid))) {
            add_to_log($COURSE->id, 'survey', 'delete fields', 'view.php?id='.$cm->id, get_string('surveyfield', 'survey'), $cm->id, $USER->id);
        } else {
            print_error('Unable to delete records with itemid = '.$itemid.' from survey_userdata');
        }

        $emptysurveys = 'SELECT c.id
                             FROM {survey_submission} c
                                 LEFT JOIN {survey_userdata} d ON c.id = d.submissionid
                             WHERE (d.id IS null)';
        if ($surveytodelete = $DB->get_records_sql($emptysurveys)) {
            $surveytodelete = array_keys($surveytodelete);
            if ($DB->delete_records_select('survey_submission', 'id IN ('.implode(',', $surveytodelete).')')) {
                add_to_log($COURSE->id, 'survey', 'item deletet', 'view.php?id='.$cm->id, get_string('survey', 'survey'), $cm->id, $USER->id);
            } else {
                print_error('Unable to delete record id IN '.implode(',', $surveytodelete).' from survey_submission');
            }
        }
    }

    /*
     * item_uses_form_page
     *
     * @return: boolean
     */
    public function item_uses_form_page() {
        return true;
    }

    /*
     * item_left_position_allowed
     *
     * @return: boolean
     */
    public function item_left_position_allowed() {
        return true;
    }

    /*
     * item_set_editor
     * defines presets for the editor field of surveyitem in itembase_form.php
     * (copied from moodle20/cohort/edit.php)
     *
     * @param &$saveditem
     * @return
     */
    public function item_set_editor() {
        if (!$this->flag->editorslist) {
            return;
        }

        // some examples
        // each SURVEY_ITEMFIELD has: $this->formrequires['content'] == true  and $this->flag->editorslist == array('content')
        // fieldset              has: $this->formrequires['content'] == true  and $this->flag->editorslist == null
        // pagebreak             has: $this->formrequires['content'] == false and $this->flag->editorslist == null
        $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => -1, 'context' => $this->context);
        foreach ($this->flag->editorslist as $fieldname => $filearea) {
            $this->{$fieldname.'format'} = FORMAT_HTML;
            $this->{$fieldname.'trust'} = 1;
            file_prepare_standard_editor($this, $fieldname, $editoroptions, $this->context, 'mod_survey', $filearea, $this->itemid);
        }
    }

    /*
     * item_get_exportvalues_array
     * translates the class property $this->{$field} in the array array[$value] = $exportvalues
     *
     * @param $field
     * @return array $values
     */
    public function item_get_exportvalues_array($field='options') {
        $options = survey_textarea_to_array($this->{$field});

        $values = array();
        foreach ($options as $k => $option) {
            if (preg_match('/^(.*)'.SURVEY_VALUELABELSEPARATOR.'(.*)$/', $option, $match)) { // do not worry: it can never be equal to zero
                // print_object($match);
                $values[] = $match[1];
            } else {
                $values[] = $option;
            }
        }

        return $values;
    }

    /*
     * item_get_values_array
     * translates the class property $this->{$field} in the array array[$value] = $values
     *
     * @param $field
     * @return array $values
     */
    public function item_get_values_array($field='options') {
        $options = survey_textarea_to_array($this->{$field});

        $values = array();
        foreach ($options as $k => $option) {
            if (preg_match('/^(.*)'.SURVEY_VALUELABELSEPARATOR.'(.*)$/', $option, $match)) { // do not worry: it can never be equal to zero
                // print_object($match);
                $values[] = $match[1];
            } else {
                $values[] = $k;
            }
        }

        return $values;
    }

    /*
     * item_get_labels_array
     * translates the class property $this->{$field} in the array array[$value] = $label
     *
     * @param $field
     * @return array $labels
     */
    public function item_get_labels_array($field='options') {
        $options = survey_textarea_to_array($this->{$field});

        $labels = array();
        foreach ($options as $k => $option) {
            if (preg_match('/^(.*)'.SURVEY_VALUELABELSEPARATOR.'(.*)$/', $option, $match)) { // do not worry: it can never be equal to zero
                // print_object($match);
                $labels[] = $match[2];
            } else {
                $labels[] = $option;
            }
        }

        return $labels;
    }

    /*
     * $this->item_clean_textarea_fields
     *
     * @param $record
     * @param $fieldlist
     * @return
     */
    public function item_clean_textarea_fields($record, $fieldlist) {
        foreach ($fieldlist as $field) {
            // do not forget some item may be undefined causing:
            // Notice: Undefined property: stdClass::$defaultvalue
            // as, for instance, disabled $defaultvalue field when $delaultoption == invitation
            if (isset($record->{$field})) {
                $temparray = survey_textarea_to_array($record->{$field});
                $record->{$field} = implode("\n", $temparray);
            }
        }
    }

    /*
     * item_get_other
     *
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
     * item_mandatory_is_allowed
     *
     * @param
     * @return
     */
    public function item_mandatory_is_allowed() {
        // a mandatory field is allowed ONLY if
        //     -> !isset($this->defaultoption)
        //     -> $this->defaultoption != SURVEY_NOANSWERDEFAULT
        if (isset($this->defaultoption)) {
            return ($this->defaultoption != SURVEY_NOANSWERDEFAULT);
        } else {
            return true;
        }
    }

    /*
     * item_get_parent_format
     *
     * @param
     * @return
     */
    public function item_get_parent_format() {
        return get_string('parentformat', 'surveyfield_'.$this->plugin);
    }

    /*
     * item_list_constraints
     *
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        // whether not overridden by specific class method...
        // nothing to do!
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = array();
        $fieldlist[$this->plugin] = array('content');

        return $fieldlist;
    }

    /*
     * item_get_plugin_schema
     * Return the xml schema for survey_<<plugin>> table.
     *
     * @return string
     *
     */
    public static function item_get_item_schema() {
        $schema = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $schema .= '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">'."\n";
        $schema .= '    <xs:element name="survey_item">'."\n";
        $schema .= '        <xs:complexType>'."\n";
        $schema .= '            <xs:sequence>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="surveyid"/>'."\n";

        $schema .= '                <xs:element type="xs:string" name="type"/>'."\n";
        $schema .= '                <xs:element type="xs:string" name="plugin"/>'."\n";

        $schema .= '                <xs:element type="xs:int" name="hidden"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="insearchform"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="advanced"/>'."\n";

        $schema .= '                <xs:element type="xs:int" name="sortindex"/>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="formpage"/>'."\n";

        $schema .= '                <xs:element type="xs:int" name="parentid" minOccurs="0"/>'."\n";
        $schema .= '                <xs:element type="xs:string" name="parentvalue" minOccurs="0"/>'."\n";

        // $schema .= '                <xs:element type="xs:int" name="timecreated"/>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="timemodified"/>'."\n";
        $schema .= '            </xs:sequence>'."\n";
        $schema .= '        </xs:complexType>'."\n";
        $schema .= '    </xs:element>'."\n";
        $schema .= '</xs:schema>';

        return $schema;
    }

    /*
     * item_get_generic_field
     *
     * @param $field
     * @return
     */
    public function item_get_generic_field($field) {
        if (isset($this->{$field})) {
            return $this->{$field};
        } else {
            return false;
        }
    }

    // MARK get

    /*
     * get_itemid
     *
     * @param
     * @return
     */
    public function get_itemid() {
        return $this->itemid;
    }

    /*
     * get_type
     *
     * @param
     * @return
     */
    public function get_type() {
        return $this->type;
    }

    /*
     * get_plugin
     *
     * @param
     * @return
     */
    public function get_plugin() {
        return $this->plugin;
    }

    /*
     * get_content
     *
     * @param
     * @return
     */
    public function get_content() {
        return file_rewrite_pluginfile_urls($this->content, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
    }

    /*
     * get_surveyid
     *
     * @param
     * @return
     */
    public function get_surveyid() {
        return $this->surveyid;
    }

    /*
     * get_pluginid
     *
     * @param
     * @return
     */
    public function get_pluginid() {
        return $this->pluginid;
    }

    /*
     * get_itemname
     *
     * @param
     * @return
     */
    public function get_itemname() {
        return $this->itemname;
    }

    /*
     * get_hide
     *
     * @param
     * @return
     */
    public function get_hide() {
        return $this->hidden;
    }

    /*
     * get_insearchform
     *
     * @param
     * @return
     */
    public function get_insearchform() {
        return $this->insearchform;
    }

    /*
     * get_advanced
     *
     * @param
     * @return
     */
    public function get_advanced() {
        return $this->advanced;
    }

    /*
     * get_sortindex
     *
     * @param
     * @return
     */
    public function get_sortindex() {
        return $this->sortindex;
    }

    /*
     * get_formpage
     *
     * @param
     * @return
     */
    public function get_formpage() {
        return $this->formpage;
    }

    /*
     * get_parentid
     *
     * @param
     * @return
     */
    public function get_parentid() {
        return $this->parentid;
    }

    /*
     * get_parentcontent
     *
     * @param
     * @return
     */
    public function get_parentcontent($separator="\n") {
        if ($separator != "\n") {
            $parentcontent = explode("\n", $this->parentcontent);
            $parentcontent = implode($separator, $parentcontent);
            return $parentcontent;
        } else {
            return $this->parentcontent;
        }
    }

    /*
     * get_parentvalue
     *
     * @param
     * @return
     */
    public function get_parentvalue() {
        return $this->parentvalue;
    }

    /*
     * get_variable
     *
     * @param
     * @return
     */
    public function get_variable() {
        if (isset($this->variable)) {
            return $this->variable;
        } else {
            return false;
        }
    }

    /*
     * get_customnumber
     *
     * @param
     * @return
     */
    public function get_customnumber() {
        if (isset($this->customnumber)) {
            return $this->customnumber;
        } else {
            return false;
        }
    }

    /*
     * get_required
     *
     * @param
     * @return
     */
    public function get_required() {
        if (isset($this->required)) {
            return $this->required;
        } else {
            return false;
        }
    }

    /*
     * get_indent
     *
     * @param
     * @return
     */
    public function get_indent() {
        if (isset($this->indent)) {
            return $this->indent;
        } else {
            return false;
        }
    }

    /*
     * get_hideinstructions
     *
     * @param
     * @return
     */
    public function get_hideinstructions() {
        if (isset($this->hideinstructions)) {
            return $this->hideinstructions;
        } else {
            return false;
        }
    }

    /*
     * get_position
     *
     * @param
     * @return
     */
    public function get_position() {
        if (isset($this->position)) {
            return $this->position;
        } else {
            return false;
        }
    }

    /*
     * get_extranote
     *
     * @param
     * @return
     */
    public function get_extranote() {
        if (isset($this->extranote)) {
            return $this->extranote;
        } else {
            return false;
        }
    }

    /*
     * get_issearchable
     *
     * @param
     * @return
     */
    public function get_issearchable() {
        return $this->flag->issearchable;
    }

    /*
     * get_form_requires
     *
     * @param $itemformelement
     * @return
     */
    public function get_form_requires($itemformelement) {
        return $this->formrequires[$itemformelement];
    }

    // MARK set

    /*
     * set_contentformat
     *
     * @param $contentformat
     * @return
     */
    public function set_contentformat($contentformat) {
        $this->contentformat = $contentformat;
    }

    /*
     * set_contenttrust
     *
     * @param $contenttrust
     * @return
     */
    public function set_contenttrust($contenttrust) {
        $this->contenttrust = $contenttrust;
    }

    // MARK parent

    /*
     * parent_validate_child_constraints
     *
     * @param $childvalue
     * @return status of child relation
     */
    public function parent_validate_child_constraints($childparentvalue) {
        /*
         * I can not make ANY assumption about $childparentvalue because of the following explanation:
         * At child save time, I encode its $parentcontent to $parentvalue.
         * The encoding is done through a parent method according to parent exportvalues.
         * Once the child is saved, I can return to parent and I can change it as much as I want.
         * For instance by changing the number and the content of its options.
         * At parent save time, the child parentvalue is rewritten
         * -> but it may result in a too short or too long list of keys
         * -> or with a wrong number of unrecognized keys so I need to...
         * ...implement all possible checks to avoid crashes/malfunctions during code execution.
         */
    }


    // MARK userform

    /*
     * userform_get_full_info == extranote + fillinginstruction
     * provides extra description THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form
     *
     * @param $searchform
     * @return
     */
    public function userform_get_full_info($searchform) {
        global $CFG;

        $config = get_config('survey');

        if (!$searchform) {
            if (!$this->get_hideinstructions()) {
                $fillinginstruction = $this->userform_get_filling_instructions();
            }
            if (isset($this->extranote)) {
                $extranote = strip_tags($this->extranote);
            }
        } else {
            if ($config->fillinginstructioninsearch) {
                if (!$this->get_hideinstructions()) {
                    $fillinginstruction = $this->userform_get_filling_instructions();
                }
            }
            if ($config->extranoteinsearch) {
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
     * userform_get_filling_instructions
     * provides extra fillinginstruction THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form
     *
     * @param
     * @return
     */
    public function userform_get_filling_instructions() {
        // if this method is not handled at plugin level,
        // it means it is supposed to return an empty fillinginstruction
        return '';
    }

    /*
     * userform_child_item_allowed_static
     * this method is called if (and only if) parent item and child item DON'T live in the same form page
     * this method has two purposes:
     * - skip the iitem from the current page of $userpageform
     * - get if a page has items
     *
     * as parentitem declare whether my child item is allowed to in the page that is going to be displayed
     *
     * @param int $submissionid:
     * @param array $childitemrecord:
     * @return $status: true: the item is welcome; false: the item must be dropped out
     */
    public function userform_child_item_allowed_static($submissionid, $childitemrecord) {
        global $DB;

        if (!$childitemrecord->parentid) {
            return true;
        }

        $where = array('submissionid' => $submissionid, 'itemid' => $this->itemid);
        $givenanswer = $DB->get_field('survey_userdata', 'content', $where);

        return ($givenanswer === $childitemrecord->parentvalue);
    }

    /*
     * userform_disable_element
     * this function is used ONLY if $survey->newpageforchild == false
     * it adds as much as needed $mform->disabledIf to disable items when parent condition does not match
     * This method is used by the child item
     * In the frame of this method the parent item is calculated and is requested to provide the disabledif conditions to disable its child item
     *
     * @param $mform
     * @param $canaccessadvanceditems
     * @return
     */
    public function userform_disable_element($mform, $canaccessadvanceditems) {
        global $DB;

        if (!$this->parentid || ($this->type == SURVEY_TYPEFORMAT)) {
            return;
        }

        $fieldnames = $this->userform_get_root_elements_name();

        $parentrestrictions = array();

        // if I am here this means I have a parent FOR SURE
        // instead of making one more query, I assign two variables manually
        // at the beginning, $currentitem is me
        $currentitem = new stdClass();
        $currentitem->parentid = $this->get_parentid();
        $currentitem->parentvalue = $this->get_parentvalue();
        $mypage = $this->get_formpage(); // once and forever
        do {
            /*
             * Take care.
             * Even if (!$survey->newpageforchild) I can have all my ancestors into previous pages by adding pagebreaks manually
             * Because of this, I need to chech page numbers
             */
            $parentitem = $DB->get_record('survey_item', array('id' => $currentitem->parentid), 'parentid, parentvalue, formpage');
            $parentpage = $parentitem->formpage;
            if ($parentpage == $mypage) {
                $parentid = $currentitem->parentid;
                $parentvalue = $currentitem->parentvalue;
                $parentrestrictions[$parentid] = $parentvalue; // The element with ID == $parentid requires, as constain, $parentvalue
            } else {
                // my parent is in a page before mine
                // no need to investigate more for older ancestors
                break;
            }

            $currentitem = $parentitem;
        } while (!empty($parentitem->parentid));
        // $parentrecord is an associative array
        // The array key is the ID of the parent item, the corresponding value is the constrain that $this has to be submitted to

        $displaydebuginfo = false;
        foreach ($parentrestrictions as $parentid => $childparentvalue) {
            $parentitem = survey_get_item($parentid);
            $disabilitationinfo = $parentitem->userform_get_parent_disabilitation_info($childparentvalue);

            if ($displaydebuginfo) {
                foreach ($disabilitationinfo as $parentinfo) {
                    if (is_array($parentinfo->content)) {
                        $contentdisplayed = 'array('.implode(',', $parentinfo->content).')';
                    } else {
                        $contentdisplayed = '\''.$parentinfo->content.'\'';
                    }
                    foreach ($fieldnames as $fieldname) {
                        if (isset($parentinfo->operator)) {
                            echo '<span style="color:green;">$mform->disabledIf(\''.$fieldname.'\', \''.
                                    $parentinfo->parentname.'\', \''.$parentinfo->operator.'\', '.$contentdisplayed.');</span><br />';
                        } else {
                            echo '<span style="color:green;">$mform->disabledIf(\''.$fieldname.'\', \''.
                                    $parentinfo->parentname.'\', '.$contentdisplayed.');</span><br />';
                        }
                    }
                }
            }

            // write disableIf
            foreach ($disabilitationinfo as $parentinfo) {
                foreach ($fieldnames as $fieldname) {
                    if (isset($parentinfo->operator)) {
                        $mform->disabledIf($fieldname, $parentinfo->parentname, $parentinfo->operator, $parentinfo->content);
                    } else {
                        $mform->disabledIf($fieldname, $parentinfo->parentname, $parentinfo->content);
                    }
                }
            }
            // $mform->disabledIf('survey_field_select_2491', 'survey_field_multiselect_2490[]', 'neq', array(0,4));
        }
    }

    /*
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     *
     * @param $answers
     * @param $format
     * @return
     */
    public function userform_db_to_export($answer, $format='') {
        $content = trim($answer->content);
        if ($content == SURVEY_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'survey');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'survey');
        }

        return $content;
    }
}
