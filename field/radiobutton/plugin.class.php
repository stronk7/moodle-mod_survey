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
require_once($CFG->dirroot.'/mod/survey/field/radiobutton/lib.php');

class surveyfield_radiobutton extends mod_survey_itembase {

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
     * $options = list of options in the form of "$value SURVEY_VALUELABELSEPARATOR $label"
     */
    public $options = '';

    /*
     * $defaultoption
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $labelother = the text label for the optional option "other" in the form of "$value SURVEY_OTHERSEPARATOR $label"
     */
    public $labelother = '';

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = '';

    /*
     * $adjustment = the orientation of the list of options.
     */
    public $adjustment = 0;

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
        $this->type = SURVEY_TYPEFIELD;
        $this->plugin = 'radiobutton';

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;

        // list of fields I do not want to have in the item definition form
        $this->itembase_form_requires['hideinstructions'] = false; // <-- actually the field has been removed so I do not need it in the item form

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
        // drop empty rows and trim edging rows spaces from each textarea field
        $fieldlist = array('options');
        $this->item_clean_textarea_fields($record, $fieldlist);

        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        $record->hideinstructions = 1;
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
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the date custom item
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
        if ($record->defaultoption != SURVEY_CUSTOMDEFAULT) {
            $record->defaultvalue = null;
        }
    }

    /*
     * item_generate_standard_default
     * sets record field to store the correct value to db for the date custom item
     *
     * @param $record
     * @return
     */
    public function item_generate_standard_default() {
        $optionarray = survey_textarea_to_array($this->options);
        $firstoption = reset($optionarray);

        if (preg_match('/^(.*)'.SURVEY_VALUELABELSEPARATOR.'(.*)$/', $firstoption, $match)) { // do not warn: it can never be equal to zero
            // print_object($match);
            $default = $match[1];
        } else {
            $default = $firstoption;
        }

        return $default;
    }

    /*
     * item_list_constraints
     *
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        $constraints = array();

        $labels = $this->item_get_labels_array('options');
        $optionstr = get_string('option', 'surveyfield_radiobutton');
        foreach ($labels as $label) {
            $constraints[] = $optionstr.': '.$label;
        }
        if (!empty($this->labelother)) {
            $constraints[] = get_string('labelother', 'surveyfield_radiobutton').': '.get_string('allowed', 'surveyfield_radiobutton');
        }

        return implode($constraints, '<br />');
    }

    /*
     * item_get_friendlyformat
     * returns true if the useform mform element for this item id is a group and false if not
     *
     * @param
     * @return
     */
    public function item_get_friendlyformat() {
        return SURVEYFIELD_RADIOBUTTON_RETURNLABELS;
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['radiobutton'] = array('content', 'options', 'labelother', 'defaultvalue');

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
        $labels = $this->item_get_labels_array('options');

        if (empty($this->labelother)) {
            $status = (array_search($childvalue, $labels) !== false) ? true : false;
        } else {
            $status = true;
        }

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
    <xs:element name="survey_radiobutton">
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

                <xs:element type="xs:string" name="options"/>
                <xs:element type="xs:string" name="labelother" minOccurs="0"/>
                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:int" name="downloadformat"/>
                <xs:element type="xs:int" name="adjustment"/>
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

        $labels = $this->item_get_labels_array('options');
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            $labels = array(SURVEY_INVITATIONVALUE => get_string('choosedots')) + $labels;
        }

        $class = array('class' => 'indent-'.$this->indent);
        $elementgroup = array();
        foreach ($labels as $k => $label) {
            $elementgroup[] = $mform->createElement('radio', $this->itemname, '', $label, "$k", $class);
            $class = ($this->adjustment == SURVEY_VERTICAL) ? array('class' => 'indent-'.$this->indent) : '';
        }

        if (!empty($this->labelother)) {
            list($othervalue, $otherlabel) = $this->item_get_other();
            $labels['other'] = $otherlabel;

            $class = ($this->adjustment == SURVEY_VERTICAL) ? array('class' => 'indent-'.$this->indent) : '';
            $elementgroup[] = $mform->createElement('radio', $this->itemname, '', $otherlabel, 'other', $class);
            $elementgroup[] = $mform->createElement('text', $this->itemname.'_text', '');
            $mform->setType($this->itemname.'_text', PARAM_ALPHANUMEXT);

            $mform->disabledIf($this->itemname.'_text', $this->itemname, 'neq', 'other');
        }

        if ( (!$this->required) || $searchform ) {
            $noanswer_label = ($searchform) ? get_string('star', 'survey') : get_string('noanswer', 'survey');
            $elementgroup[] = $mform->createElement('radio', $this->itemname, '', $noanswer_label, SURVEY_NOANSWERVALUE, $class);
        }

        if ($this->adjustment == SURVEY_VERTICAL) {
            if (!empty($this->labelother)) {
                // I take 2 <br /> out because:
                //     having n elements, I only need (n-2) separators
                // for instance:
                // $elementgroup =
                //   0 => (radio) 'Italy'
                //   1 => (radio) 'Spain'
                //   2 => (radio) 'Greece'
                //   3 => (label) 'Other (please specify a nationality)'
                //   4 => (editfield) 'Great Britain'

                // $elementgroup =
                //   0 => (radio) 'Italy'
                //   1 => (radio) 'Spain'
                //   2 => (radio) 'Greece'
                //   3 => (label) 'Other (please specify a nationality)'
                //   4 => (editfield) 'Great Britain'
                //   5 => (radio) '__n0__Answer__'

                $separatorcount = $this->required ? count($elementgroup)-2 : count($elementgroup)-3;
                $separator = array_fill(0, $separatorcount, '<br />');
                $separator[] = ' ';
                if (!$this->required) {
                    $separator[] = '<br />';
                }
            } else {
                $separator = '<br />';
            }
        } else { // SURVEY_HORIZONTAL
            $separator = ' ';
        }
        $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. THIS IS A MOODLE ISSUE. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                $starplace = ($this->extrarow) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            }

            switch ($this->defaultoption) {
                case SURVEY_CUSTOMDEFAULT:
                    $index = array_search($this->defaultvalue, $labels);
                    if ($index !== false) {
                        $mform->setDefault($this->itemname, "$index");
                    }
                    break;
                case SURVEY_INVITATIONDEFAULT:
                    $mform->setDefault($this->itemname, SURVEY_INVITATIONVALUE);
                    break;
                case SURVEY_NOANSWERDEFAULT:
                    $mform->setDefault($this->itemname, SURVEY_NOANSWERVALUE);
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
            }
            // $this->itemname.'_text' has to ALWAYS get a default (if required) even if it is not selected
            if (!empty($this->labelother)) {
                $mform->setDefault($this->itemname.'_text', $othervalue);
            }
        } else {
            $mform->setDefault($this->itemname, SURVEY_NOANSWERVALUE); // free
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
        // this plugin displays as a set of radio buttons. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if ($this->extrarow) {
            $errorkey = $this->itemname.'_extrarow';
        } else {
            $errorkey = $this->itemname.'_group';
        }

        if ( ($data[$this->itemname] == 'other') && empty($data[$this->itemname.'_text']) ) {
            $errors[$this->itemname.'_text'] = get_string('required');
            return;
        }

        // I need to check value is different from SURVEY_INVITATIONVALUE even if it is not required
        if ($data[$this->itemname] == SURVEY_INVITATIONVALUE) {
            $errors[$errorkey] = get_string('uerr_optionnotset', 'surveyfield_radiobutton');
            return;
        }
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

        $labels = $this->item_get_labels_array('options');

        $index = array_search($child_parentvalue, $labels);
        if ($index !== false) {
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname;
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = $index;
            $disabilitationinfo[] = $mformelementinfo;
        } else {
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname;
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = 'other';
            $disabilitationinfo[] = $mformelementinfo;

            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'_text';
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = $child_parentvalue;
            $disabilitationinfo[] = $mformelementinfo;
        }

        return $disabilitationinfo;
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

        $child_parentvalue = $childitemrecord->parentvalue;

        $values = $this->item_get_labels_array('options');
        $index = array_search($child_parentvalue, $values);

        if ($index !== false) {
            $status = ($givenanswer == $index);
        } else {
            $status = ($givenanswer == $child_parentvalue);
        }

        return $status;
    }

    /*
     * userform_child_item_allowed_dynamic
     * as parentitem defines whether a child item is supposed to be enabled in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called at submit time if (and only if) parent item and child item live in the same form page
     * this function is supposed to classify disabled element as unexpected in order to drop their reported value
     * ----------------------------------------------------------------------
     * Am I getting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_item_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use userform_child_item_allowed_static
     * ----------------------------------------------------------------------
     *
     * @param: $child_parentcontent, $data
     * @return $status: true: the item is welcome; false: the item must be dropped out
     */
    public function userform_child_item_allowed_dynamic($child_parentcontent, $data) {
        $labels = $this->item_get_labels_array('options');
        $index = array_search($child_parentcontent, $labels);
        if ($index !== false) {
            $status = ($data[$this->itemname] == $index);
        } else {
            $status = ($data[$this->itemname] == 'other');
            $status = $status && ($data[$this->itemname.'_text'] == $child_parentcontent);
        }

        return $status;
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
        if (isset($answer['mainelement'])) {
            switch ($answer['mainelement']) {
                case 'other':
                    $olduserdata->content = $answer['text'];
                    break;
                case '':
                    $olduserdata->content = null;
                    break;
                default:
                    $olduserdata->content = $answer['mainelement'];
                    break;
            }
            return;
        }

        print_error('unhandled return value from user submission');
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
                $labels = $this->item_get_labels_array('options');
                if (array_key_exists($fromdb->content, $labels)) {
                    $prefill[$this->itemname] = $fromdb->content;
                } else {
                    if ($fromdb->content == SURVEY_NOANSWERVALUE) {
                        $prefill[$this->itemname] = SURVEY_NOANSWERVALUE;
                    } else {
                        // it is, for sure, the content of _text
                        $prefill[$this->itemname] = 'other';
                        $prefill[$this->itemname.'_text'] = $fromdb->content;
                    }
                }
            } else {
                // nothing was set
                // do not accept defaults but overwrite them
                // but... if this is a group of radio buttons, how can it be empty($fromdb->content)? Because user selected "Not answering" or question was disabled
                $prefill[$this->itemname] = '';
            }
        } // else use item defaults

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
        // content
        $content = $answer->content;
        if ($content == SURVEY_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'survey');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'survey');
        }

        // format
        if ($format == SURVEY_FIRENDLYFORMAT) {
            $format = $this->item_get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        switch ($format) {
            case SURVEYFIELD_RADIOBUTTON_RETURNVALUES:
                $values = $this->item_get_values_array('options');
                if (array_key_exists($content, $values)) {
                    $return = $values[$content];
                } else {
                    $return = $content;
                }
                break;
            case SURVEYFIELD_RADIOBUTTON_RETURNLABELS:
                $values = $this->item_get_labels_array('options');
                if (array_key_exists($content, $values)) {
                    $return = $values[$content];
                } else {
                    $return = $content;
                }
                break;
            case SURVEYFIELD_RADIOBUTTON_RETURNPOSITION:
                $return = $content;
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $format = '.$format);
        }

        return $return;
    }

    /*
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     *
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return true;
    }
}