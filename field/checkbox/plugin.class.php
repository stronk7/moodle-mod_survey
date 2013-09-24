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
require_once($CFG->dirroot.'/mod/survey/field/checkbox/lib.php');

class surveyfield_checkbox extends mod_survey_itembase {

    /*
     * $rawcontent = the text content using @@PLUGINFILE@@
     */
    public $rawcontent = '';

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

    // -----------------------------

    /*
     * $options = list of options in the form of "$value SURVEY_VALUELABELSEPARATOR $label"
     */
    public $options = '';

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

    // -----------------------------

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
        $this->plugin = 'checkbox';

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEY_ITEMCONTENTFILEAREA);

        // list of fields I do not want to have in the item definition form
        $this->formrequires['hideinstructions'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid);
            $this->rawcontent = $this->content;
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
        // -----------------------------
        // Now execute very specific plugin level actions
        // -----------------------------

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
        // drop empty rows and trim trailing spaces from each textarea field
        $fieldlist = array('options', 'defaultvalue');
        $this->item_clean_textarea_fields($record, $fieldlist);

        $record->hideinstructions = 1;
        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (mod_survey_itembase::item_save($record)))
        return parent::item_save($record);
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
        $optionstr = get_string('option', 'surveyfield_checkbox');
        foreach ($labels as $label) {
            $constraints[] = $optionstr.': '.$label;
        }
        if (!empty($this->labelother)) {
            $constraints[] = get_string('labelother', 'surveyfield_checkbox').': '.get_string('allowed', 'surveyfield_checkbox');
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
        return SURVEYFIELD_CHECKBOX_RETURNLABELS;
    }

    // MARK parent

    /*
     * parent_validate_child_constraints
     *
     * @param
     * @return status of child relation
     */
    public function parent_validate_child_constraints($childvalue) {
        $childlabels = survey_textarea_to_array($childvalue);

        $labels = $this->item_get_labels_array('options');

        $errcount = 0;
        foreach ($childlabels as $childlabel) {
            if (array_search($childlabel, $labels) === false) {
                $errcount++;
            }
        }
        switch ($errcount) {
            case 0:
                $status = true;
                break;
            case 1:
                $status = !empty($this->labelother);
                break;
            default:
                $status = false;
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
        $arraycontent = survey_textarea_to_array($parentcontent);
        $parentcontent = implode("\n", $arraycontent);

        return $parentcontent;
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['checkbox'] = array('options', 'defaultvalue');

        return $fieldlist;
    }

    /**
     * item_get_plugin_schema
     * Return the xml schema for survey_<<plugin>> table.
     *
     * @return string
     *
     */
    public static function item_get_plugin_schema() {
        $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
    <xs:element name="survey_checkbox">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="content"/>
                <xs:element name="embedded" minOccurs="0" maxOccurs="unbounded">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element type="xs:string" name="filename"/>
                            <xs:element type="xs:base64Binary" name="filecontent"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                <xs:element type="xs:int" name="contentformat"/>

                <xs:element type="xs:string" name="customnumber" minOccurs="0"/>
                <xs:element type="xs:int" name="extrarow"/>
                <xs:element type="xs:string" name="extranote" minOccurs="0"/>
                <xs:element type="xs:int" name="required"/>
                <xs:element type="xs:string" name="variable" minOccurs="0"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:string" name="options"/>
                <xs:element type="xs:string" name="labelother" minOccurs="0"/>
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
        $defaults = survey_textarea_to_array($this->defaultvalue);

        $elementgroup = array();
        $i = 0;
        $class = '';
        foreach ($labels as $value => $label) {
            $uniqueid = $this->itemname.'_'.$i;
            $class = ( ($this->adjustment == SURVEY_VERTICAL) || (!$class) ) ? array('class' => 'indent-'.$this->indent) : '';
            $elementgroup[] = $mform->createElement('checkbox', $uniqueid, '', $label, $class);

            if (!$searchform) {
                $index = in_array($label, $defaults);
                if ($index !== false) {
                    $mform->setDefault($uniqueid, '1');
                }
            }
            $i++;
        }
        if (!empty($this->labelother)) {
            list($othervalue, $otherlabel) = $this->item_get_other();

            $class = ($this->adjustment == SURVEY_VERTICAL) ? array('class' => 'indent-'.$this->indent) : '';
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_other', '', $otherlabel, $class);
            $elementgroup[] = $mform->createElement('text', $this->itemname.'_text', '');
            $mform->setType($this->itemname.'_text', PARAM_RAW);

            if (!$searchform) {
                $index = in_array($otherlabel, $defaults);
                if ($index !== false) {
                    $mform->setDefault($this->itemname.'_other', '1');
                    $mform->setDefault($this->itemname.'_text', $othervalue);
                }
            }
            $mform->disabledIf($this->itemname.'_text', $this->itemname.'_other', 'notchecked');
        }

        if ($this->adjustment == SURVEY_VERTICAL) {
            if (!empty($this->labelother)) {
                $separator = array_fill(0, count($elementgroup)-2, '<br />');
                $separator[] = ' ';
                $separator[] = '<br />';
            } else {
                $separator = '<br />';
            }
        } else { // SURVEY_HORIZONTAL
            $separator = ' ';
        }
        $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

        /* this last item is needed because:
         * the JS validation MAY BE missing even if the field is required
         * (JS validation is never added because I do not want it when the "previous" button is pressed and when an item is disabled even if mandatory)
         * so the check for the non empty field is performed in the validation routine.
         * The validation routine is executed ONLY ON ITEM that are really submitted.
         * For checkboxes, nothing is submitted if no box is checked
         * so, if the user neglects the mandatory checkboxes item AT ALL, it is not submitted and, as conseguence, not validated.
         * TO ALWAYS SUBMIT A CHECKNBOXES SET I add a dummy hidden item.
         *
         * TAKE CARE: I choose a name for this item that IS UNIQUE BUT is missing the SURVEY_ITEMPREFIX.'_'
         *            In this way I am sure the item will never be saved in the database
         */
        $placeholderitemname = SURVEY_NEGLECTPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';
        $mform->addElement('hidden', $placeholderitemname, SURVEYFIELD_CHECKBOX_PLACEHOLDER);
        $mform->setType($placeholderitemname, PARAM_INT);

        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. THIS IS A MOODLE ISSUE. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                $starplace = ($this->extrarow) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
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
        if ($this->required) {
            $labels = $this->item_get_labels_array('options');

            if ($this->extrarow) {
                $errorkey = $this->itemname.'_extrarow';
            } else {
                $errorkey = $this->itemname.'_group';
            }

            $missinganswer = true;
            foreach ($labels as $k => $label) {
                $uniqueid = $this->itemname.'_'.$k;
                if (isset($data[$uniqueid])) { // if the checkbox was not selected, $data[$uniqueid] does not even exist
                    $missinganswer = false;
                    break;
                }
            }

            if ($missinganswer) {
                if (!empty($this->labelother)) {
                    if ((!empty($data[$this->itemname.'_other'])) && (!empty($data[$this->itemname.'_text']))) {
                        $missinganswer = false;
                    }
                }
            }

            if ($missinganswer) {
                $errors[$errorkey] = get_string('required');
            }
        }
    }

    /*
     * userform_get_parent_disabilitation_info
     * from childparentvalue defines syntax for disabledIf
     *
     * @param: $childparentvalue
     * @return
     */
    public function userform_get_parent_disabilitation_info($childparentvalue) {
        $disabilitationinfo = array();

        // I need to know the names of mfrom element corresponding to the content of $childparentvalue
        $labels = $this->item_get_labels_array('options');
        $request = survey_textarea_to_array($childparentvalue);

        foreach ($labels as $k => $label) {
            $mformelementinfo = new stdClass();

            $mformelementinfo->parentname = $this->itemname.'_'.$k;
            $constrainindex = array_search($label, $request);
            if ($constrainindex !== false) { // 0 or a different index
                unset($request[$constrainindex]);
                $mformelementinfo->content = 'notchecked';
            } else {
                $mformelementinfo->content = 'checked';
            }
            $disabilitationinfo[] = $mformelementinfo;
        }

        // if among $request ​​there is one that is not among $valueLabel
        if (count($request)) { // if I STILL have $request
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'_other';
            $mformelementinfo->content = 'notchecked';
            $disabilitationinfo[] = $mformelementinfo;

            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'_text';
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = reset($request);
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

        $values = $this->item_get_labels_array('options');

        $constraints = explode("\n", $childitemrecord->parentvalue);
        $elementscount = count(explode(SURVEY_DBMULTIVALUESEPARATOR, $givenanswer));
        if (!$this->labelother) {
            $key = array_fill(0, $elementscount, 0);
        } else {
            $key = array_fill(0, $elementscount-1, 0);
            $key[] = '';
        }

        foreach ($constraints as $constraint) {
            $index = array_search($constraint, $values);
            if ($index !== false) {
                $key[$index] = 1;
            } else {
                // it is the 'other' option
                // put $constraint in the last position
                $key[$elementscount-1] = $constraint;
            }
        }

        $required = implode(SURVEY_DBMULTIVALUESEPARATOR, $key);

        return ($givenanswer === $required);
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
     * @param: $parentcontent, $parentsubmitted
     * @return $status: true: the item is welcome; false: the item must be dropped out
     */
    public function userform_child_item_allowed_dynamic($childparentcontent, $data) {
        // I need to verify (checkbox per checkbox) if they hold the same value the user entered
        $labels = $this->item_get_labels_array('options');
        $request = survey_textarea_to_array($childparentcontent);

        $status = true;
        foreach ($labels as $k => $label) {
            $index = array_search($label, $request);
            if ($index !== false) {
                $status = $status && (isset($data[$this->itemname.'_'.$k]));
            } else {
                $status = $status && (!isset($data[$this->itemname.'_'.$k]));
            }
        }
        if ($this->labelother) {
            if (array_search($this->itemname.'_text', $request) !== false) {
                $status = $status && (isset($data[$this->itemname.'_check']));
            } else {
                $status = $status && (!isset($data[$this->itemname.'_check']));
            }
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

        $return = array();
        $values = $this->item_get_values_array('options');

        foreach ($values as $k => $value) {
            if (isset($answer["$k"])) {
                $return[] = '1';
            } else {
                $return[] = '0';
            }
        }
        if (!empty($this->labelother)) {
            $return[] = isset($answer['other']) ? $answer['text'] : '';
        }

        if (empty($return)) {
            $olduserdata->content = null;
        } else {
            $olduserdata->content = implode(SURVEY_DBMULTIVALUESEPARATOR, $return);
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

        if (isset($fromdb->content)) { // I made some selection
            // count of answers is == count of checkboxes
            $answers = explode(SURVEY_DBMULTIVALUESEPARATOR, $fromdb->content);

            // here $answers is an array like: array(1,1,0,0,'dummytext')
            foreach ($answers as $k => $checkboxvalue) {
                $uniqueid = $this->itemname.'_'.$k;
                $prefill[$uniqueid] = $checkboxvalue;
            }
            if ($this->labelother) {
                // delete last item of $prefill
                unset($prefill[$uniqueid]);

                // add last element of the $prefill
                $lastanswer = end($answers);

                if (strlen($lastanswer)) {
                    $prefill[$this->itemname.'_other'] = 1;
                    $prefill[$this->itemname.'_text'] = $lastanswer;
                } else {
                    $prefill[$this->itemname.'_other'] = 0;
                    $prefill[$this->itemname.'_text'] = '';
                }
            }
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
        // content
        $content = $answer->content;
        // SURVEY_NOANSWERVALUE does not exist here
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

        // output
        // $answers is an array like: array(1,1,0,0,'dummytext')
        switch ($format) {
            case SURVEYFIELD_CHECKBOX_RETURNVALUES:
            case SURVEYFIELD_CHECKBOX_RETURNLABELS:
                $answers = explode(SURVEY_DBMULTIVALUESEPARATOR, $content);
                $output = array();
                if ($format == SURVEYFIELD_CHECKBOX_RETURNVALUES) {
                    $values = $this->item_get_values_array('options');
                } else { // $format == SURVEYFIELD_CHECKBOX_RETURNLABELS
                    $values = $this->item_get_labels_array('options');
                }
                $standardanswerscount = count($values);
                foreach ($values as $k => $value) {
                    if ($answers[$k] == 1) {
                        $output[] = $value;
                    }
                }
                if (!empty($this->labelother)) {
                    $value = end($answers);
                    if (!empty($value)) {
                        $output[] = $value; // last element of the array $answers
                    }
                }

                if (!empty($output)) {
                    $return = implode(SURVEY_OUTPUTMULTIVALUESEPARATOR, $output);
                } else {
                    if ($format == SURVEYFIELD_CHECKBOX_RETURNLABELS) {
                        $return = get_string('emptyanswer', 'survey');
                    }
                }
                break;
            case SURVEYFIELD_CHECKBOX_RETURNPOSITION:
                // here I will ALWAYS HAVE 0/1 so each separator is welcome, even ','
                // I do not like pass the idea that ',' can be a separator so, I do not use it
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
