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
require_once($CFG->dirroot.'/mod/survey/format/fieldsetend/lib.php');

class surveyformat_fieldsetend extends mod_survey_itembase {

    /*
     * $content = the text content of the item.
     */
    public $content = '';

    /*******************************************************************/

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*******************************************************************/

    /*
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional survey_item ID
     */
    public function __construct($itemid=0) {
        $this->type = SURVEY_TYPEFORMAT;
        $this->plugin = 'fieldsetend';

        $this->flag = new stdClass();
        $this->flag->issearchable = false;
        $this->flag->couldbeparent = false;
        $this->flag->usescontenteditor = false;

        // list of fields I do not want to have in the item definition form
        $this->item_form_requires['common_fs'] = false;
        $this->item_form_requires['customnumber'] = false;
        $this->item_form_requires['extrarow'] = false;
        $this->item_form_requires['extranote'] = false;
        $this->item_form_requires['required'] = false;
        $this->item_form_requires['variable'] = false;
        $this->item_form_requires['indent'] = false;
        $this->item_form_requires['hideinstructions'] = false; // <-- actually the field has been removed so I do not need it in the item form

        if (!empty($itemid)) {
            $this->item_load($itemid);
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
        $record->content = SURVEYFORMAT_FIELDSETEND_CONTENT;
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
    <xs:element name="survey_label">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="content"/>
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
        global $DB, $USER, $PAGE;

        // this plugin has $this->flag->issearchable = false; so it will never be part of a search form

        /* I hate the first solution with all my soul because it leave an empty row in the user form page
         * but, as opposite solution, I have to:
         * -> add global $DB, $USER, $PAGE;
         * -> get $cm = $PAGE->cm;
         * -> get $context = context_module::instance($cm->id);
         * -> get has_capability('mod/survey:accessadvanceditems', $context, null, true);
         * -> make the query to get the ID of the next item (remember that next item depends from your permissions to see advanced items)
         * -> instanciate $item class
         * -> ask if $item uses an extrarow
         * -> ask $item->userform_mform_element_is_group()
         * finally write the simple:
         *     $mform->closeHeaderBefore($nextitem->itemname.'_extrarow');
         * or
         *     $mform->closeHeaderBefore($nextitem->itemname);
         * or
         *     $mform->closeHeaderBefore($nextitem->itemname.'_group');
         * ALL OF THIS TO CLOSE A FIELDSET? CRAZY!!!
         * yes, we are.
         */
        if (false) {
            $mform->addElement('static', $this->itemname, '', '', array('class' => 'hidefull')); // <-- class does not work for labels. See: MDL-28194
            $mform->closeHeaderBefore($this->itemname);
        } else {
            $cm = $PAGE->cm;
            $context = context_module::instance($cm->id);
            $canaccessadvanceditems = has_capability('mod/survey:accessadvanceditems', $context, null, true);
            $sql = 'SELECT id, type, plugin
                FROM {survey_item}
                WHERE surveyid = :surveyid
                    AND sortindex > :sortindex
                    AND hide = 0
                    AND plugin <> "pagebreak"';
            if (!$canaccessadvanceditems) {
                $sql .= ' AND advanced = 0';
            }
            $sql .= ' ORDER BY sortindex
                LIMIT 1';

            $sqlparams = array('surveyid' => $cm->instance, 'sortindex' => $this->sortindex);
            $itemseed = $DB->get_record_sql($sql, $sqlparams, IGNORE_MISSING);
            if ($itemseed) { // The element really exists
                $nextitem = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);
                if ($nextitem->extrarow) {
                    $mform->closeHeaderBefore($nextitem->itemname.'_extrarow');
                } else {
                    if ($nextitem->userform_mform_element_is_group()) {
                        $mform->closeHeaderBefore($nextitem->itemname.'_group');
                    } else {
                        $mform->closeHeaderBefore($nextitem->itemname);
                    }
                }
            }
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
        // nothing to do here
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
        return '';
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