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
     * $content = the text content of the item.
     */
    public $content = '';

    /*
     * $contenttrust
     */
    public $contenttrust = 1;

    /*
     * public $contentformat = '';
     */
    public $contentformat = '';

    /*
     * $customnumber = the custom number of the item.
     * It usually is 1. 1.1, a, 2.1.a...
     */
    public $customnumber = '';

    /*
     * $position = where does the question go?
     */
    public $position = SURVEY_POSITIONLEFT;

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
    public function __construct($itemid=0, $evaluateparentcontent) {
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
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /*
     * item_load
     *
     * @param $itemid
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (mod_survey_itembase::item_load($itemid)))
        parent::item_load($itemid, $evaluateparentcontent);

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
     * item_encode_parentcontent
     *
     * @param $childparentcontent
     * return childparentvalue
     */
    public function item_encode_parentcontent($childparentcontent) {
        $parentcontent = survey_textarea_to_array($childparentcontent);
        $labels = $this->item_get_labels_array('options');

        $childparentvalue = array();
        foreach ($labels as $k => $label) {
            $key = array_search($label, $parentcontent);
            if ($key !== false) {
                $childparentvalue[] = 1;
                unset($parentcontent[$key]);
            } else {
                $childparentvalue[] = 0;
            }
        }

        if (count($parentcontent)) {
            $childparentvalue['other'] = 1;
            $childparentvalue['text'] = $parentcontent[0];
        } else {
            $childparentvalue['text'] = '';
        }

        return implode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue);
    }

    /*
     * item_decode_parentvalue
     *
     * @param $childparentvalue
     * return $childparentcontent
     */
    public function item_decode_parentvalue($childparentvalue) {
        $labels = $this->item_get_labels_array('options');
        $parentvalue = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue);
        $childparentcontent = array();
        foreach ($parentvalue as $k => $value) {
            if ($value == 1) {
                $childparentcontent[] = $labels[$k];
            }
        }

        $lastvalue = end($parentvalue);
        if ($lastvalue) {
            $childparentcontent[] = $lastvalue;
        }

        return implode("\n", $childparentcontent);
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
     *
     * @param
     * @return
     */
    public function item_get_friendlyformat() {
        return SURVEYFIELD_CHECKBOX_RETURNLABELS;
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

    /*
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
    <xs:element name="surveyfield_checkbox">
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
                <xs:element type="xs:int" name="position"/>
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

    // MARK parent

    /*
     * parent_validate_child_constraints
     *
     * @param $childparentvalue
     * @return status of child relation
     */
    public function parent_validate_child_constraints($childparentvalue) {
        // I have here $childparentvalue that was calculated at child save time
        // I can not be sure the parent item (this item) was not changed in a second time
        // I need to check for:
        // - count of elements
        // - presence of $this->labelother

        $childparentvalues = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue);
        $parentoptionscount = count($childparentvalues);
        $labelcount = count($this->item_get_labels_array('options'));
        if ($this->labelother) {
            $labelcount++;
        }

        if ($parentoptionscount != $labelcount) {
            return false;
        }

        // the count is equal: good
        // now I have to consider two following scenarios:
        // 1) the parent item (this item) may have n label + $this->labelother && the child may have n keys without a label
        // 2) the parent item (this item) may have n label without $this->labelother && the child may have (n-1) keys + a label

        // case 1: whatever is written as last key of $childparentvalues is compatible with $this->labelother, so nothing to check
        if ($this->labelother) {
            return true;
        }

        // case 2:
        $lastkey = end($childparentvalues);
        return ($lastkey == $labelcount);
    }

    // MARK userform

    /*
     * userform_mform_element
     *
     * @param $mform
     * @param $searchform
     * @return
     */
    public function userform_mform_element($mform, $searchform) {
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = ($this->position == SURVEY_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $labels = $this->item_get_labels_array('options');
        $defaults = survey_textarea_to_array($this->defaultvalue);

        $firstclass = array('class' => 'indent-'.$this->indent, 'group' => 1);
        $class = ($this->adjustment == SURVEY_VERTICAL) ? $firstclass : array('group' => 1);

        $elementgroup = array();
        $i = 0;
        foreach ($labels as $value => $label) {
            $uniqueid = $this->itemname.'_'.$i;
            $maybeclass = (count($elementgroup)) ? $class : $firstclass;
            $elementgroup[] = $mform->createElement('advcheckbox', $uniqueid, '', $label, $maybeclass, array('0', '1'));

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

            $elementgroup[] = $mform->createElement('advcheckbox', $this->itemname.'_other', '', $otherlabel, $class, array('0', '1'));
            $elementgroup[] = $mform->createElement('text', $this->itemname.'_text', '');
            $mform->setType($this->itemname.'_text', PARAM_RAW);

            if (!$searchform) {
                $mform->setDefault($this->itemname.'_text', $othervalue);
                if (in_array($othervalue, $defaults)) {
                    $mform->setDefault($this->itemname.'_other', '1');
                }
            }
            $mform->disabledIf($this->itemname.'_text', $this->itemname.'_other', 'notchecked');
        }

        if (!$this->required) {
            $elementgroup[] = $mform->createElement('advcheckbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'), $class, array('0', '1'));
        }

        if ($searchform) {
            unset($class['group']);
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'survey'), $class);
        }

        if ($this->adjustment == SURVEY_VERTICAL) {
            if (!empty($this->labelother)) {
                $separator = array_fill(0, count($elementgroup)-3, '<br />');
                $separator[] = ' ';
                $separator[] = '<br />';
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

        if (!$this->required) {
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }

        if ($searchform) {
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }

        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEY_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            }
        }
    }

    /*
     * userform_mform_validation
     *
     * @param $data
     * @param &$errors
     * @param $survey
     * @param $searchform
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $searchform) {
        if ($searchform) {
            return;
        }

        if ($this->required) {
            $labels = $this->item_get_labels_array('options');
            $errorkey = $this->itemname.'_group';

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

        $labels = $this->item_get_labels_array('options');
        $request = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue); // 1;1;0;

        foreach ($labels as $k => $label) {
            $mformelementinfo = new stdClass();

            $mformelementinfo->parentname = $this->itemname.'_'.$k;
            if ($request[$k] == 1) {
                $mformelementinfo->content = 'notchecked';
            } else {
                $mformelementinfo->content = 'checked';
            }
            unset($request[$k]);
            $disabilitationinfo[] = $mformelementinfo;
        }

        // if among $request ​​there is one that is not among $valueLabel
        if (count($request)) { // if I STILL have $request
            if ($this->labelother) {
                $lastlabel = reset($request);
                if ($lastlabel) {
                    $mformelementinfo = new stdClass();
                    $mformelementinfo->parentname = $this->itemname.'_other';
                    $mformelementinfo->content = 'notchecked';
                    $disabilitationinfo[] = $mformelementinfo;

                    $mformelementinfo = new stdClass();
                    $mformelementinfo->parentname = $this->itemname.'_text';
                    $mformelementinfo->operator = 'neq';
                    $mformelementinfo->content = reset($request);
                    $disabilitationinfo[] = $mformelementinfo;
                } else {
                    $mformelementinfo = new stdClass();
                    $mformelementinfo->parentname = $this->itemname.'_other';
                    $mformelementinfo->content = 'checked';
                    $disabilitationinfo[] = $mformelementinfo;
                }
            } else {
                debugging('Probably $childparentvalue is not correct: '.$childparentvalue);
            }
        } else {
            // even if no more request are found,
            // I have to add one more $disabilitationinfo if $this->other is not empty
            if ($this->labelother) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname.'_other';
                $mformelementinfo->content = 'checked';
                $disabilitationinfo[] = $mformelementinfo;
            }
        }

        return $disabilitationinfo;
    }

    /*
     * userform_child_item_allowed_dynamic
     * this method is called if (and only if) parent item and child item live in the same form page
     * this method has two purposes:
     * - stop userpageform item validation
     * - drop unexpected returned values from $userpageform->formdata
     *
     * as parentitem declare whether my child item is allowed to return a value (is enabled) or is not (is disabled)
     *
     * @param string $childparentvalue:
     * @param array $data:
     * @return boolean: true: if the item is welcome; false: if the item must be dropped out
     */
    public function userform_child_item_allowed_dynamic($childparentvalue, $data) {
        // 1) I am a checkbox item
        // 2) in $data I can ONLY find $this->itemname, $this->itemname.'_other', $this->itemname.'_text'

        // I need to verify (checkbox per checkbox) if they hold the same value the user entered
        $labels = $this->item_get_labels_array('options');
        $request = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue); // 2;3;shark

        $status = true;
        foreach ($labels as $k => $label) {
            $index = array_search($k, $request);
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
     * or what to return for the search form
     *
     * @param $answer
     * @param $olduserdata
     * @param $searchform
     * @return
     */
    public function userform_save_preprocessing($answer, $olduserdata, $searchform) {
        if (isset($answer['ignoreme']) && ($answer['ignoreme'] == 1)) { // it ia an advcheckbox
            $olduserdata->content = null;
            return;
        }

        if (isset($answer['noanswer']) && ($answer['noanswer'] == 1)) { // it ia an advcheckbox
            $olduserdata->content = SURVEY_NOANSWERVALUE;
            return;
        }

        $return = $answer;
        if (!empty($this->labelother)) {
            $return[] = isset($answer['other']) ? $answer['text'] : '';
            unset($return['other']);
            unset($return['text']);
        }
        if (!$this->required) {
            unset($return['noanswer']);
        }
        $olduserdata->content = implode(SURVEY_DBMULTIVALUESEPARATOR, $return);
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

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

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
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param
     * @return
     */
    public function userform_get_root_elements_name() {
        $elementnames = array();
        $elementnames[] = $this->itemname.'_group';

        return $elementnames;
    }
}
