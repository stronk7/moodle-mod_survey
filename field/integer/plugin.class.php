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
require_once($CFG->dirroot.'/mod/survey/field/integer/lib.php');

class surveyfield_integer extends mod_survey_itembase {

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

    /*******************************************************************/

    /*
     * $defaultoption
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;

    /*
     * $lowerbound = the minimum allowed integer
     */
    public $lowerbound = 0;

    /*
     * $upperbound = the maximum allowed integer
     */
    public $upperbound = 0;

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*
     * $canbeparent
     */
    public static $canbeparent = true;

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
        $this->plugin = 'integer';

        $maximuminteger = get_config('surveyfield_integer', 'maximuminteger');
        $this->upperbound = $maximuminteger;

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEY_ITEMCONTENTFILEAREA);

        // list of fields I do not want to have in the item definition form
        // EMPTY LIST

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

        $this->item_custom_fields_to_form();
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
        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);
        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (mod_survey_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_custom_fields_to_form
     *
     * @param
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        if (!isset($this->defaultvalue)) {
            $this->defaultoption = SURVEY_NOANSWERDEFAULT;
        } else {
            if ($this->defaultvalue == SURVEY_INVITATIONDBVALUE) {
                $this->defaultoption = SURVEY_INVITATIONDEFAULT;
            } else {
                $this->defaultoption = SURVEY_CUSTOMDEFAULT;
            }
        }
    }

    /*
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the integer custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        switch ($record->defaultoption) {
            case SURVEY_CUSTOMDEFAULT:
                // $record->defaultvalue has already been set
                break;
            case SURVEY_NOANSWERDEFAULT:
                $record->defaultvalue = null;
                break;
            case SURVEY_INVITATIONDEFAULT:
                $record->defaultvalue = SURVEY_INVITATIONDBVALUE;
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $record->defaultoption = '.$record->defaultoption);
        }
        unset($record->defaultvalue_integer);
    }

    /*
     * item_list_constraints
     *
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        $constraints = array();
        $constraints[] = get_string('lowerbound', 'surveyfield_integer').': '.$this->lowerbound;
        $constraints[] = get_string('upperbound', 'surveyfield_integer').': '.$this->upperbound;

        return implode($constraints, '<br />');
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

    // MARK parent

    /*
     * parent_validate_child_constraints
     *
     * @param
     * @return status of child relation
     */
    public function parent_validate_child_constraints($childvalue) {
        $status = true;
        $status = $status && ($childvalue >= $this->lowerbound);
        $status = $status && ($childvalue <= $this->upperbound);

        return $status;
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
        return $parentcontent;
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
    <xs:element name="survey_integer">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="content"/>
                <xs:element type="xs:int" name="contentformat"/>

                <xs:element type="xs:string" name="customnumber" minOccurs="0"/>
                <xs:element type="xs:int" name="extrarow"/>
                <xs:element type="xs:string" name="extranote" minOccurs="0"/>
                <xs:element type="xs:int" name="required"/>
                <xs:element type="xs:int" name="hideinstructions"/>
                <xs:element type="xs:string" name="variable" minOccurs="0"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:int" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:int" name="lowerbound"/>
                <xs:element type="xs:int" name="upperbound"/>
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
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $integers = array();
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            $integers[SURVEY_INVITATIONVALUE] = get_string('choosedots');
        }
        $integers += array_combine(range($this->lowerbound, $this->upperbound), range($this->lowerbound, $this->upperbound));

        if ( (!$this->required) || $searchform ) {
            $check_label = ($searchform) ? get_string('star', 'survey') : get_string('noanswer', 'survey');
            $integers += array(SURVEY_NOANSWERVALUE => $check_label);
        }

        $mform->addElement('select', $this->itemname, $elementlabel, $integers, array('class' => 'indent-'.$this->indent));

        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. THIS IS A MOODLE ISSUE. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                $starplace = ($this->extrarow) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace; // add the star for mandatory fields at the end of the page with server side validation too
            }
        }

        // default section
        if (!$searchform) {
            if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                $mform->setDefault($this->itemname, SURVEY_INVITATIONVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEY_CUSTOMDEFAULT:
                        $defaultinteger = $this->defaultvalue;
                        break;
                    case SURVEY_NOANSWERDEFAULT:
                        $defaultinteger = SURVEY_NOANSWERVALUE;
                        break;
                }
                $mform->setDefault($this->itemname, "$defaultinteger");
            }
        } else {
            $mform->setDefault($this->itemname, SURVEY_NOANSWERVALUE);
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
        // this plugin displays as dropdown menu. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if ($this->extrarow) {
            $errorkey = $this->itemname.'_extrarow';
        } else {
            $errorkey = $this->itemname;
        }

        $maximuminteger = get_config('surveyfield_integer', 'maximuminteger');

        // I need to check value is different from SURVEY_INVITATIONVALUE even if it is not required
        if ($data[$this->itemname] == SURVEY_INVITATIONVALUE) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_integernotsetrequired', 'surveyfield_integer');
            } else {
                $a = get_string('noanswer', 'survey');
                $errors[$errorkey] = get_string('uerr_integernotset', 'surveyfield_integer', $a);
            }
            return;
        }

        $haslowerbound = ($this->lowerbound != 0);
        $hasupperbound = ($this->upperbound != $maximuminteger);

        $userinput = $data[$this->itemname];

        if ($userinput == SURVEY_NOANSWERVALUE) {
            return;
        }
        if ($haslowerbound && $hasupperbound) {
            // internal range
            if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyfield_integer');
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyfield_integer');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyfield_integer');
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

        $maximuminteger = get_config('surveyfield_integer', 'maximuminteger');

        $haslowerbound = ($this->lowerbound != 0);
        $hasupperbound = ($this->upperbound != $maximuminteger);

        $a = new StdClass();
        $lowerbound = $this->lowerbound;
        $upperbound = $this->upperbound;

        if ($haslowerbound && $hasupperbound) {
            $a = new StdClass();
            $a->lowerbound = $lowerbound;
            $a->upperbound = $upperbound;

            $fillinginstruction = get_string('restriction_lowerupper', 'surveyfield_integer', $a);
        } else {
            if ($haslowerbound) {
                $a = $lowerbound;
                $fillinginstruction = get_string('restriction_lower', 'surveyfield_integer', $a);
            }

            if ($hasupperbound) {
                $a = $upperbound;
                $fillinginstruction = get_string('restriction_upper', 'surveyfield_integer', $a);
            }
        }

        return $fillinginstruction;
    }

    /*
     * userform_get_parent_disabilitation_info
     * from child_parentvalue defines syntax for disabledIf
     *
     * @param: $child_parentvalue
     * @return
     */
    public function userform_get_parent_disabilitation_info($child_parentvalue) {
        $disabilitationinfo = array();

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $this->itemname;
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $child_parentvalue;
        $disabilitationinfo[] = $mformelementinfo;

        return $disabilitationinfo;
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
        if (isset($answer['noanswer'])) {
            $olduserdata->content = SURVEY_NOANSWERVALUE;
        } else {
            $olduserdata->content = $answer['mainelement'];
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
            if (isset($fromdb->content)) {
                $prefill[$this->itemname] = $fromdb->content;
            }
        } // else use item defaults

        return $prefill;
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