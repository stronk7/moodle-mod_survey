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

require_once($CFG->dirroot.'/mod/survey/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/survey/field/fileupload/lib.php');

class surveyfield_fileupload extends mod_survey_itembase {

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
     * $variable = the name of the field storing data in the db table
     */
    public $variable = '';

    /*
     * $indent = the indent of the item in the form page
     */
    public $indent = 0;

    /*******************************************************************/

    /*
     * $maxfiles = the maximum number of files allowed to upload
     */
    public $maxfiles = '1';

    /*
     * $maxbytes = the maximum allowed size of the file to upload
     */
    public $maxbytes = '1024';

    /*
     * $filetypes = list of allowed file extension
     */
    public $filetypes = '*';

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*
     * $canbeparent
     */
    public static $canbeparent = false;

    /*******************************************************************/

    /*
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional survey_item ID
     */
    public function __construct($itemid=0) {
        global $PAGE;

        $cm = $PAGE->cm;

        if (isset($cm)) { // it is not set during upgrade whther this item is loaded
            $this->context = context_module::instance($cm->id);
        }

        $this->type = SURVEY_TYPEFIELD;
        $this->plugin = 'fileupload';

        $this->flag = new stdClass();
        $this->flag->issearchable = false;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEY_ITEMCONTENTFILEAREA);

        // list of fields I do not want to have in the item definition form
        $this->itembase_form_requires['insearchform'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid);
            $this->content = file_rewrite_pluginfile_urls($this->content, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
        }
    }

    /*
     * item_load
     *
     * @param $itemid
     * @return
     */
    public function item_load($itemid) {
        // Do parent item loading stuff here (mod_survey_itembase::item_load($itemid)))
        parent::item_load($itemid);

        // multilang load support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_load_support();
    }

    /*
     * item_save
     *
     * @param $record
     * @return
     */
    public function item_save($record) {
        // //////////////////////////////////
        // Now execute very specific plugin level actions
        // //////////////////////////////////

        // ------ begin of fields saved in survey_items ------ //
        /* surveyid
         * type
         * plugin

         * hide
         * insearchform
         * advanced

         * sortindex
         * formpage

         * timecreated
         * timemodified
         */
        // ------- end of fields saved in survey_items ------- //

        // ------ begin of fields saved in this plugin table ------ //
        $record->hideinstructions = 1;
        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (mod_survey_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();

        return $fieldlist;
    }

    /**
     * item_get_plugin_schema
     * Return the xml schema for survey_<<plugin>> table.
     *
     * @return string
     *
     */
    static function item_get_plugin_schema() {
        $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
    <xs:element name="survey_fileupload">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="content"/>
                <xs:element type="xs:int" name="contentformat"/>

                <xs:element type="xs:string" name="customnumber" minOccurs="0"/>
                <xs:element type="xs:int" name="extrarow"/>
                <xs:element type="xs:string" name="extranote" minOccurs="0"/>
                <xs:element type="xs:int" name="required"/>
                <xs:element type="xs:string" name="variable" minOccurs="0"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:int" name="maxfiles"/>
                <xs:element type="xs:int" name="maxbytes"/>
                <xs:element type="xs:string" name="filetypes"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK userform

    /*
     * userform_mform_element
     *
     * @param $mform
     * @param $survey
     * @param $canaccessadvanceditems
     * @param $parentitem
     * @param $searchform
     * @return
     */
    public function userform_mform_element($mform, $searchform) {
        // this plugin has $this->flag->issearchable = false; so it will never be part of a search form

        $fieldname = $this->itemname.'_filemanager';

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $attachmentoptions = array('maxbytes' => $this->maxbytes, 'accepted_types' => $this->filetypes, 'subdirs' => false, 'maxfiles' => $this->maxfiles);
        $mform->addElement('filemanager', $fieldname, $elementlabel, null, $attachmentoptions);

        if ($this->required) {
            // even if the item is required I CAN NOT ADD ANY RULE HERE because:
            // -> I do not want JS form validation if the page is submitted through the "previous" button
            // -> I do not want JS field validation even if this item is required BUT disabled. THIS IS A MOODLE ISSUE. See: MDL-34815
            // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
            $starplace = ($this->extrarow) ? $this->itemname.'_extrarow' : $this->itemname;
            $mform->_required[] = $starplace;
        }
    }

    /*
     * userform_mform_validation
     *
     * @param $data, &$errors
     * @param $survey
     * @param $canaccessadvanceditems
     * @param $parentitem
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey) {
        if ($this->required) {
            if ($this->extrarow) {
                $errorkey = $this->itemname.'_extrarow';
            } else {
                $errorkey = $this->itemname.'_filemanager';
            }

            $fieldname = $this->itemname.'_filemanager';
            if (empty($data[$fieldname])) {
                $errors[$errorkey] = get_string('required');
                return;
            }
        }
    }

    /*
     * userform_get_filling_instructions
     *
     * @param
     * @return
     */
    public function userform_get_filling_instructions() {

        if ($this->filetypes != '*') {
            // $filetypelist = preg_replace('/([a-zA-Z0-9]+,)([^\s])/', "$1 $2", $this->filetypes);
            $filetypelist = preg_replace('~,(?! )~', ', ', $this->filetypes); // Credits to Sam Marshall

            $fillinginstruction = get_string('allowedtypes', 'surveyfield_fileupload').$filetypelist;
        } else {
            $fillinginstruction = '';
        }

        return $fillinginstruction;
    }

    /*
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * this method calculates what to save in the db
     *
     * @param $answer
     * @param $olduserdata
     * @return
     */
    public function userform_save_preprocessing($answer, $olduserdata) {
        if (!empty($answer)) {
            $fieldname = $this->itemname.'_filemanager';

            $attachmentoptions = array('maxbytes' => $this->maxbytes, 'accepted_types' => $this->filetypes, 'subdirs' => false, 'maxfiles' => $this->maxfiles);
            file_save_draft_area_files($answer['filemanager'], $this->context->id, 'surveyfield_fileupload', SURVEYFIELD_FILEUPLOAD_FILEAREA, $olduserdata->id, $attachmentoptions);

            $olduserdata->content = ''; // nothing is expected here
        }
    }

    /*
     * this method is called from survey_set_prefill (in locallib.php) to set $prefill at user form display time
     * (defaults are set in userform_mform_element)
     *
     * userform_set_prefill
     *
     * @param $fromdb
     * @return
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if ($fromdb) { // $fromdb may be boolean false for not existing data
            $fieldname = $this->itemname.'_filemanager';

            // $prefill->id = $fromdb->submissionid;
            $draftitemid = 0;
            $attachmentoptions = array('maxbytes' => $this->maxbytes, 'accepted_types' => $this->filetypes, 'subdirs' => false, 'maxfiles' => $this->maxfiles);
            file_prepare_draft_area($draftitemid, $this->context->id, 'surveyfield_fileupload', SURVEYFIELD_FILEUPLOAD_FILEAREA, $fromdb->id, $attachmentoptions);

            $prefill[$fieldname] = $draftitemid;
        }

        return $prefill;
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
        // SURVEY_NOANSWERVALUE does not exist here
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'surveyfield_fileupload', SURVEYFIELD_FILEUPLOAD_FILEAREA, $answer->id);
        $filename = array();
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $filename[] = $file->get_filename();
        }
        return implode(',', $filename);
    }

    /*
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     *
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return false;
    }
}