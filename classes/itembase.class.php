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

    /***********************************************************************************/
    /* BEGIN OF FIELDS OF SURVEY_ITEMS CLASS */
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
     * $itemname = the name of the field as it is in the userpage_form
     */
    public $itemname = '';

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
     * $variable = the name of the field storing data in the db table
     */
    public $variable = '';

    /*
     * $indent = the indent of the item in the form page
     */
    public $indent = 0;

    /*
     * $hide = is this field going to be shown in the form?
     */
    public $hide = 0;

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
        'variable' => true,
        'indent' => true,
        'hide' => true,
        'advanced' => true,
        'insearchform' => true,
        'parentid' => true
    );
    /***********************************************************************************/
    /* END OF FIELDS OF SURVEY_ITEMS */
    /***********************************************************************************/

    /*
     * item_load
     *
     * @param integer $itemid
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
     *
     * @param stdClass $record
     * @return
     */
    public function item_save($record) {
        global $CFG, $DB, $PAGE;

        $cm = $PAGE->cm;
        $context = context_module::instance($cm->id);

        // you are going to change item content (maybe sortindex, maybe the parentitem)
        // so, do not forget to reset items per page
        survey_reset_items_pages($cm->instance);

        $timenow = time();

        // is this useless?
        // foreach ($this->item_form_requires as $k => $v) {
        //     if (!$v) {
        //         unset($record->{$k});
        //     }
        // }

        // the surveyitem lies in two different tables
        // survey_item
        // survey_<<plugin>>
        $tablename = 'survey_'.$this->plugin;

        // do not forget surveyid
        $record->surveyid = $cm->instance;
        $record->timemodified = $timenow;

        // TAKE CARE: do not manage extrarow
        // it is an advcheckbox

        // manage other checkboxes content
        $checkboxessettings = array('advanced', 'insearchform', 'hideinstructions', 'required', 'hide');
        foreach ($checkboxessettings as $checkboxessetting) {
            $record->{$checkboxessetting} = isset($record->{$checkboxessetting}) ? 1 : 0;
        }

        // encode $fromform->parentcontent to $item->parentvalue on the basis of the parentplugin specified in $record->parentid
        if (isset($record->parentid) && $record->parentid) { // I am sure parentcontent is here too
            $parentplugin = $DB->get_field('survey_item', 'plugin', array('id' => $record->parentid));
            require_once($CFG->dirroot.'/mod/survey/field/'.$parentplugin.'/plugin.class.php');
            $itemclass = 'surveyfield_'.$parentplugin;
            $parentitem = new $itemclass($record->parentid);

            $record->parentcontent = trim($record->parentcontent, " \t\n\r");

            $record->parentvalue = $parentitem->parent_encode_content_to_value($record->parentcontent);
        }

        // $this->userfeedback
        //   +--- children got limited access
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
        // (digit in place 4) == 1 means items inherited limited access because this (as parent) got a limited access

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
            if ($record->itemid = $DB->insert_record('survey_item', $record)) {
                // $tablename
                if ($this->flag->useplugintable) {
                    if ($DB->insert_record($tablename, $record)) {
                        $this->userfeedback += 1; // 0*2^1+1*2^0
                    }
                } else {
                    $this->userfeedback += 1; // 0*2^1+1*2^0
                }
            }

            $logaction = ($this->userfeedback == SURVEY_NOFEEDBACK) ? 'add item failed' : 'add item';

            // special care for the "editor" field
            if ($this->item_form_requires['content_editor']) { // i.e. content
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
                $record = file_postupdate_standard_editor($record, 'content', $editoroptions, $context, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $record->itemid);
                $record->contentformat = FORMAT_HTML;

                // survey_item
                // id
                $record->id = $record->itemid;

                $DB->update_record('survey_item', $record);
            }

        } else {

            // special care for the "editor" field
            if ($this->item_form_requires['content_editor']) { // i.e. content
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
                $record = file_postupdate_standard_editor($record, 'content', $editoroptions, $context, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $record->itemid);
                $record->contentformat = FORMAT_HTML;
            }

            // hide/unhide part 1
            $oldhide = $DB->get_field('survey_item', 'hide', array('id' => $record->itemid)); // used later
            // end of: hide/unhide 1

            // limit/unlimit access part 1
            $oldadvanced = $DB->get_field('survey_item', 'advanced', array('id' => $record->itemid)); // used later
            // end of: limit/unlimit access part 1

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
                        $this->userfeedback += 3; // 1*2^1+1*2^0 alias: editing + success
                    } else {
                        $this->userfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail
                    }
                } else {
                    $this->userfeedback += 3; // 1*2^1+1*2^0 alias: editing + success
                }
            } else {
                $this->userfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail
            }

            $logaction = ($this->userfeedback == SURVEY_NOFEEDBACK) ? 'add item failed' : 'add item';

            // save process is over. Good.
            // now hide or unhide (whether needed) chain of ancestors or descendents
            if ($this->userfeedback & 1) { // bitwise logic, alias: if the item was successfully saved
                // /////////////////////////////////////////////////
                // manage ($oldhide != $record->hide)
                // /////////////////////////////////////////////////
                if ($oldhide != $record->hide) {
                    $survey = $DB->get_record('survey', array('id' => $cm->instance), '*', MUST_EXIST);
                    $action = ($oldhide) ? SURVEY_SHOWITEM : SURVEY_HIDEITEM;
                    $itemtomove = 0;
                    $lastitembefore = 0;
                    $confirm = SURVEY_CONFIRMED_YES;
                    $nextindent = 0;
                    $parentid = 0;
                    $userfeedback = 0;
                    $saveasnew  = 0;
                    $itemlist_manager = new mod_survey_itemlist($cm, $context, $survey, $record->type, $record->plugin,
                                           $record->itemid, $action, $itemtomove, $lastitembefore,
                                           $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);
                }

                // hide/unhide part 2
                if ( ($oldhide == 1) && ($record->hide == 0) ) {
                    if ($itemlist_manager->manage_item_show()) {
                        // a chain of parent items has been showed
                        $this->userfeedback += 4; // 1*2^2
                    }
                }
                if ( ($oldhide == 0) && ($record->hide == 1) ) {
                    if ($itemlist_manager->manage_item_hide()) {
                        // a chain of child items has been hided
                        $this->userfeedback += 8; // 1*2^3
                    }
                }
                // end of: hide/unhide part 2

                // /////////////////////////////////////////////////
                // manage ($oldadvanced != $record->advanced)
                // /////////////////////////////////////////////////
                if ($oldadvanced != $record->advanced) {
                    $survey = $DB->get_record('survey', array('id' => $cm->instance), '*', MUST_EXIST);
                    $action = ($oldadvanced) ? SURVEY_MAKEFORALL : SURVEY_MAKELIMITED;
                    $itemtomove = 0;
                    $lastitembefore = 0;
                    $confirm = SURVEY_CONFIRMED_YES;
                    $nextindent = 0;
                    $parentid = 0;
                    $userfeedback = 0;
                    $saveasnew  = 0;
                    $itemlist_manager = new mod_survey_itemlist($cm, $context, $survey, $record->type, $record->plugin,
                                           $record->itemid, $action, $itemtomove, $lastitembefore,
                                           $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);
                }
                // limit/unlimit access part 2
                if ( ($oldadvanced == 1) && ($record->advanced == 0) ) {
                    if ($itemlist_manager->manage_item_makestandard()) {
                        // a chain of parent items has been made available for all
                        $this->userfeedback += 16; // 1*2^4
                    }
                }
                if ( ($oldadvanced == 0) && ($record->advanced == 1) ) {
                    if ($itemlist_manager->manage_item_makeadvanced()) {
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
        // Luglio_Mercoledì_193_2012_07_3_11_16_03_59

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
        // if, at the end, the related survey_submission has no data, then, delete it too.
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
    }

    /*
     * item_set_editor
     * defines presets for the editor field of surveyitem in itembase_form.php
     * (copied from moodle20/cohort/edit.php)
     *
     * @param $cmid
     * @param &$saveditem
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
     * item_get_values_array
     * translates the class property $this->{$field} in the array array[$value] = $label
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
     * item_get_values_array
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
    function item_clean_textarea_fields($record, $fieldlist) {
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
     * item_get_main_text
     * returns the content of the field defined as main
     *
     * @param
     * @return
     */
    public function item_get_main_text() {
        return $this->content;
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

    /**
     * item_get_plugin_schema
     * Return the xml schema for survey_<<plugin>> table.
     *
     * @return string
     *
     */
    static function item_get_item_schema() {
        $schema = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $schema .= '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">'."\n";
        $schema .= '    <xs:element name="survey_item">'."\n";
        $schema .= '        <xs:complexType>'."\n";
        $schema .= '            <xs:sequence>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="surveyid"/>'."\n";

        $schema .= '                <xs:element type="xs:string" name="type"/>'."\n";
        $schema .= '                <xs:element type="xs:string" name="plugin"/>'."\n";

        $schema .= '                <xs:element type="xs:string" name="content"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="contentformat"/>'."\n";

        $schema .= '                <xs:element type="xs:string" name="customnumber" minOccurs="0"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="extrarow"/>'."\n";
        $schema .= '                <xs:element type="xs:string" name="extranote" minOccurs="0"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="required"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="hideinstructions"/>'."\n";
        $schema .= '                <xs:element type="xs:string" name="variable" minOccurs="0"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="indent"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="hide"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="insearchform"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="advanced"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="sortindex"/>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="formpage"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="parentid" minOccurs="0"/>'."\n";
        $schema .= '                <xs:element type="xs:string" name="parentcontent" minOccurs="0"/>'."\n";
        $schema .= '                <xs:element type="xs:string" name="parentvalue" minOccurs="0"/>'."\n";

        $schema .= '                <xs:element type="xs:int" name="timecreated"/>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="timemodified"/>'."\n";
        $schema .= '            </xs:sequence>'."\n";
        $schema .= '        </xs:complexType>'."\n";
        $schema .= '    </xs:element>'."\n";
        $schema .= '</xs:schema>';

        return $schema;
    }

    // MARK get

    /*
     * item_get_generic_field
     *
     * @param $field
     * @return
     */
    public function item_get_generic_field($field) {
        return $this->{$field};
    }

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
        // TODO: what if this is an html with images, for instance?
        return $this->content;
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
    public function get_parentcontent() {
        return $this->parentcontent;
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
     * get_sortindex
     *
     * @param
     * @return
     */
    public function get_sortindex() {
        return $this->sortindex;
    }

    /*
     * get_hide
     *
     * @param
     * @return
     */
    public function get_hide() {
        return $this->hide;
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
     * get_insearchform
     *
     * @param
     * @return
     */
    public function get_insearchform() {
        return $this->insearchform;
    }

    /*
     * get_variable
     *
     * @param
     * @return
     */
    public function get_variable() {
        return $this->variable;
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
     * get_customnumber
     *
     * @param
     * @return
     */
    public function get_customnumber() {
        return $this->customnumber;
    }

    /*
     * get_labelintro
     *
     * @param
     * @return
     */
    public function get_labelintro() {
        return $this->labelintro;
    }

    /*
     * get_required
     *
     * @param
     * @return
     */
    public function get_required() {
        return $this->required;
    }

    /*
     * get_indent
     *
     * @param
     * @return
     */
    public function get_indent() {
        return $this->indent;
    }

    /*
     * get_extrarow
     *
     * @param
     * @return
     */
    public function get_extrarow() {
        return $this->extrarow;
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
     * get_useplugintable
     *
     * @param
     * @return
     */
    public function get_useplugintable() {
        return $this->flag->useplugintable;
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
     * get_item_form_requires
     *
     * @param $setup_itemform_element
     * @return
     */
    public function get_item_form_requires($setup_itemform_element) {
        return $this->item_form_requires[$setup_itemform_element];
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
    public function parent_validate_child_constraints($childvalue) {
        // whether not overridden by specific class method...
        // nothing to do!
    }

    /*
     * parent_encode_content_to_value
     * This method is used by items handled as parent
     * starting from the user input, this method stores to the db the value as it is stored during survey submission
     * this method manages the $parentcontent of its child item, not its own $parentcontent
     * (take care: here we are not submitting a survey but we are submitting an item)
     *
     * @param $parentcontent
     * @return
     */
    public function parent_encode_content_to_value($parentcontent) {
        // whether not overridden by specific class method, return true
        return true; // nothing to do!
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = array();
        $fieldlist['item'] = array('content', 'parentcontent');

        return $fieldlist;
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

        if (!$searchform) {
            if (!$this->hideinstructions) {
               $fillinginstruction = $this->userform_get_filling_instructions();
            }
            if (isset($this->extranote)) {
                $extranote = strip_tags($this->extranote);
            }
        } else {
            if ($CFG->survey_fillinginstructioninsearch) {
                if (!$this->hideinstructions) {
                    $fillinginstruction = $this->userform_get_filling_instructions();
                }
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
     * as parentitem defines whether a child item is supposed to be enabled in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called at submit time if (and only if) parent item and child item live in different form page
     * this function is supposed to classify disabled element as unexpected in order to drop their reported value
     * ----------------------------------------------------------------------
     * Am I getting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_item_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use userform_child_item_allowed_static
     * ----------------------------------------------------------------------
     *
     * @param: $submissionid, $childitemrecord
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
     * userform_child_item_allowed_dynamic
     * as parentitem defines whether a child item is supposed to be enabled in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called at submit time if (and only if) parent item and child item live in the same form page
     * this function is supposed to classify disabled element as unexpected in order to drop their reported value
     * ----------------------------------------------------------------------
     * Am I geting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_item_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use userform_child_item_allowed_static
     * ----------------------------------------------------------------------
     *
     * @param: $child_parentcontent, $data
     * @return
     */
    public function userform_child_item_allowed_dynamic($child_parentcontent, $data) {
        return ($data[$this->itemname] == $child_parentcontent);
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

        if ($this->userform_mform_element_is_group()) {
            $fieldname = $this->itemname.'_group';
        } else {
            $fieldname = $this->itemname;
        }

        $parentrestrictions = array();

        // if I am here this means I have a parent FOR SURE
        // instead of making one more query, I assign two variables manually
        // at the beginning, $currentitem is me
        $currentitem = new StdClass();
        $currentitem->parentid = $this->get_parentid();
        $currentitem->parentcontent = $this->get_parentcontent();
        $mypage = $this->get_formpage(); // once and forever
        do {
            /*
             * Take care.
             * Even if (!$survey->newpageforchild) I can have all my ancestors into previous pages by adding pagebreaks manually
             * Because of this, I need to chech page numbers
             */
            $parentitem = $DB->get_record('survey_item', array('id' => $currentitem->parentid), 'parentid, parentcontent, formpage');
            $parentpage = $parentitem->formpage;
            if ($parentpage == $mypage) {
                $parentid = $currentitem->parentid;
                $parentcontent = $currentitem->parentcontent;
                $parentrestrictions[$parentid] = $parentcontent; // The element with ID == $parentid requires, as constain, $parentcontent
            } else {
                // my parent is in a page before mine
                // no need to investigate more for older ancestors
                break;
            }

            $currentitem = $parentitem;
        } while (!empty($parentitem->parentid));
        // $parentrecord is an associative array
        // The array key is the ID of the parent item, the corresponding value is the constrain that $this has to be submitted to

        foreach ($parentrestrictions as $parentid => $childconstrain) {
            $parentitem = survey_get_item($parentid);
            $disabilitationinfo = $parentitem->userform_get_parent_disabilitation_info($childconstrain);

            $displaydebuginfo = false;
            if ($displaydebuginfo) {
                foreach ($disabilitationinfo as $parentinfo) {
                    if (is_array($parentinfo->content)) {
                        $contentdisplayed = 'array('.implode(',', $parentinfo->content).')';
                    } else {
                        $contentdisplayed = '\''.$parentinfo->content.'\'';
                    }
                    if (isset($parentinfo->operator)) {
                        echo '<span style="color:green;">$mform->disabledIf(\''.$fieldname.'\', \''.$parentinfo->parentname.'\', \''.$parentinfo->operator.'\', '.$contentdisplayed.');</span><br />';
                    } else {
                        echo '<span style="color:green;">$mform->disabledIf(\''.$fieldname.'\', \''.$parentinfo->parentname.'\', '.$contentdisplayed.');</span><br />';
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
            //$mform->disabledIf('survey_field_select_2491', 'survey_field_multiselect_2490[]', 'neq', array(0,4));
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