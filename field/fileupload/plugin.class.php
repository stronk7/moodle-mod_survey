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

class surveyfield_fileupload extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_fileupload record
     */
    public $pluginid = 0;

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
     * $context = context as it is always required to dial with editors
     */
    private $context;

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*
     * $item_form_requires = list of fields I will see in the form
     * public $item_form_requires;
     */

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

        $this->type = SURVEY_TYPEFIELD;
        $this->plugin = 'fileupload';

        $this->flag = new stdclass();
        $this->flag->issearchable = false;
        $this->flag->couldbeparent = false;
        $this->flag->useplugintable = true;

        $this->context = context_module::instance($cm->id);

        /*
         * this item is not issearchable
         * so the default inherited from itembase.class.php
         * public $basicform = SURVEY_FILLANDSEARCH;
         * can not match the plugin_form element
         * So I change it to SURVEY_FILLONLY
         */
        $this->basicform = SURVEY_FILLONLY;

        if (!empty($itemid)) {
            $this->item_load($itemid);
        }
    }

    /*
     * item_load
     * @param $itemid
     * @return
     */
    public function item_load($itemid) {
        // Do parent item loading stuff here (surveyitem_base::item_load($itemid)))
        parent::item_load($itemid);

        // multilang load support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_load_support();
    }

    /*
     * item_save
     * @param $record
     * @return
     */
    public function item_save($record) {
        // //////////////////////////////////
        // Now execute very specific plugin level actions
        // //////////////////////////////////

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_get_filling_instructions
     * @param
     * @return
     */
    public function item_get_filling_instructions() {

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
     * item_get_plugin_values
     * @param $pluginstructure
     * @param $pluginsid
     * @return
     */
    public function item_get_plugin_values($pluginstructure, $pluginsid) {
        $values = parent::item_get_plugin_values($pluginstructure, $pluginsid);

        // just a check before assuming all has been done correctly
        $errindex = array_search('err', $values, true);
        if ($errindex !== false) {
            throw new moodle_exception('$values[\''.$errindex.'\'] of survey_'.$this->plugin.' was not properly managed');
        }

        return $values;
    }

    /*
     * userform_mform_element
     * @param $mform
     * @return
     */
    public function userform_mform_element($mform, $survey, $canaccessadvancedform, $parentitem=null, $searchform=false) {
        // this plugin has $this->flag->issearchable = false; so it will never be part of a search form

        $fieldname = $this->itemname.'_filemanager';

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $attachmentoptions = array('maxbytes' => $this->maxbytes, 'accepted_types' => $this->filetypes, 'subdirs' => false, 'maxfiles' => $this->maxfiles);
        $mform->addElement('filemanager', $fieldname, $elementlabel, null, $attachmentoptions);

        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted trough the "previous" button
                // -> I do not want JS field validation even if this item is required AND disabled too. THIS IS A MOODLE BUG. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                if ($this->extrarow) {
                    $starplace = $this->itemname.'_extrarow';
                } else {
                    $starplace = $fieldname;
                }
                $mform->_required[] = $starplace;
            }
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
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
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * this method calculates what to save in the db
     * @param $itemdetail, $olduserdata
     * @return
     */
    public function userform_save_preprocessing($itemdetail, $olduserdata) {
        if (!empty($itemdetail)) {
            $fieldname = $this->itemname.'_filemanager';

            $attachmentoptions = array('maxbytes' => $this->maxbytes, 'accepted_types' => $this->filetypes, 'subdirs' => false, 'maxfiles' => $this->maxfiles);
            file_save_draft_area_files($itemdetail['filemanager'], $this->context->id, 'mod_survey', $fieldname, $olduserdata->submissionid, $attachmentoptions);

            // needed only for export purposes
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($this->context->id, 'mod_survey', $fieldname, $olduserdata->submissionid, 'sortorder', false)) {
                foreach ($files as $file) {
                    $oldfiles[] = $file->get_filename();
                }
                $olduserdata->content = implode(', ', $oldfiles);
            } else {
                $olduserdata->content = '';
            }
        }
    }

    /*
     * this method is called from survey_set_prefill (in locallib.php) to set $prefill at user form display time
     * (defaults are set in userform_mform_element)
     *
     * userform_set_prefill
     * @param $olduserdata
     * @return
     */
    public function userform_set_prefill($olduserdata) {
        $prefill = array();

        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            $fieldname = $this->itemname.'_filemanager';

            // $prefill->id = $olduserdata->submissionid;
            $draftitemid = 0;
            $attachmentoptions = array('maxbytes' => $this->maxbytes, 'accepted_types' => $this->filetypes, 'subdirs' => false, 'maxfiles' => $this->maxfiles);
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_survey', $fieldname, $olduserdata->submissionid, $attachmentoptions);

            $prefill[$fieldname] = $draftitemid;
        }

        return $prefill;
    }

    /*
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     * @param $richsubmission
     * @return
     */
    public function userform_db_to_export($itemvalue) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $itemvalue->id);
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
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        // $this->flag->couldbeparent = false
        // this method is never called
    }
}