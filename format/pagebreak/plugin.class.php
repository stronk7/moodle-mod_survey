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

require_once($CFG->dirroot.'/mod/survey/itembase.class.php');
require_once($CFG->dirroot.'/mod/survey/format/pagebreak/lib.php');

class surveyformat_pagebreak extends surveyitem_base {

    /**
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_pagebreak record
     */
    public $pluginid = 0;

    /**
     * $flag = features describing the object
     */
    public $flag;

    /**
     * $item_form_requires = list of fields I will see in the form
     * public $item_form_requires;
     */

    /********************************************************************/

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional survey_item ID
     */
    public function __construct($itemid=0) {
        $this->type = SURVEY_FORMAT;
        $this->plugin = 'pagebreak';

        $this->flag = new stdclass();
        $this->flag->issearchable = false;
        $this->flag->ismatchable = false;
        $this->flag->useplugintable = false;

        // list of fields I do not want to have in the item definition form
        $this->item_form_requires['common_fs'] = false;
        $this->item_form_requires['content_editor'] = false;
        $this->item_form_requires['customnumber'] = false;
        $this->item_form_requires['extrarow'] = false;
        $this->item_form_requires['softinfo'] = false;
        $this->item_form_requires['required'] = false;
        $this->item_form_requires['fieldname'] = false;
        $this->item_form_requires['indent'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid);
        }
    }

    /**
     * item_load
     * @param $itemid
     * @return
     */
    public function item_load($itemid) {
        // Do parent item loading stuff here (surveyitem_base::item_load($itemid)))
        parent::item_load($itemid);
    }

    /**
     * item_save
     * @param $record
     * @return
     */
    public function item_save($record) {
        // //////////////////////////////////
        // Now execute very specific plugin level actions
        // //////////////////////////////////

        $record->content = SURVEYFORMAT_PAGEBREAK_CONTENT;

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
    }

    /**
     * item_parent_content_format_validation
     * checks whether the user input format in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_format_validation($parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * item_parent_content_content_validation
     * checks whether the user input content in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_content_validation($parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * item_parent_content_encode_value
     * starting from the user input, this function stores to the db the value as it is stored during survey submission
     * this method manages the $parentcontent of its child item, not its own $parentcontent
     * (take care: here we are not submitting a survey but we are submitting an item)
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_encode_value($parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * item_get_plugin_values
     * @param $pluginstructure
     * @param $pluginsid
     * @return
     */
    public function item_get_plugin_values($pluginstructure, $pluginsid) {
        $values = parent::item_get_plugin_values($pluginstructure, $pluginsid);

        if ($reviewcounter != count($values)) {
            throw new moodle_exception('survey_items values were not all checked. '.$reviewcounter.' reviewes vs '.count($values).' fields in the structure');
        }
        return $values;
    }


    /**
     * userform_mform_element
     * @param $mform
     * @return
     */
    public function userform_mform_element($mform, $survey, $canaccessadvancedform, $parentitem=null, $searchform=false) {
        // this plugin has $this->flag->issearchable = false; so it will never be part of a search form
        // this function is never called because to simulate a page break, I show anly fields before this field
    }

    /**
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        $canaddrequiredrule = $this->userform_can_add_required_rule($survey, $canaccessadvancedform, $parentitem);
        if ($this->required && (!$canaddrequiredrule)) {
            // CS validaition was not permitted
            // so, here, I need to manually look after the 'required' rule
            $this->userform_manualrequiredvalidation($data, $errors);
        }
    }

    /**
     * this method is called from survey_set_prefill (in locallib.php) to set $prefill at user form display time
     * (defaults are set in userform_mform_element)
     *
     * userform_set_prefill
     * @param $olduserdata
     * @return
     */
    public function userform_set_prefill($olduserdata) {
        $prefill = array();
        return $prefill;
    }

    /**
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     * @param $richsubmission
     * @return
     */
    public function userform_db_to_export($itemvalue) {
        return '';
    }

    /**
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return false;
    }

    /**
     * item_get_main_text
     * returns the content of the field defined as main
     * @param
     * @return
     */
    public function item_get_main_text() {
        return SURVEYFORMAT_PAGEBREAK_CONTENT;
    }
}