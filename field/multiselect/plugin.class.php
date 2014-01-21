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
require_once($CFG->dirroot.'/mod/survey/field/multiselect/lib.php');

class surveyfield_multiselect extends mod_survey_itembase {

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
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = '';

    /*
     * $heightinrows = the height of the multiselect in rows
     */
    public $heightinrows = 4;

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
        $this->plugin = 'multiselect';

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
        // drop empty rows and trim edging rows spaces from each textarea field
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

        $answer = array();
        foreach ($parentcontent as $label) {
            $key = array_search($label, $labels);
            $answer[] = $key;
        }

        return implode(SURVEY_DBMULTIVALUESEPARATOR, $answer);
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
        foreach ($parentvalue as $key) {
            if (isset($labels[$key])) {
                $childparentcontent[] = $labels[$key];
            } else {
                // The "Validate branching" page will inform the user that this relation will never match
                $childparentcontent[] = $key;
            }
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
        $optionstr = get_string('option', 'surveyfield_multiselect');
        foreach ($labels as $label) {
            $constraints[] = $optionstr.': '.$label;
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
        return SURVEYFIELD_MULTISELECT_RETURNLABELS;
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['multiselect'] = array('content', 'options', 'defaultvalue');

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
    <xs:element name="surveyfield_multiselect">
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
                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:string" name="downloadformat"/>
                <xs:element type="xs:int" name="heightinrows"/>
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
        // I need to check for the count of elements

        $childparentvalues = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue);
        $maxindex = max($childparentvalues);

        $labelcount = count($this->item_get_labels_array('options'));

        return ($maxindex <= $labelcount);
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

        $class = array('size' => $this->heightinrows, 'class' => 'indent-'.$this->indent);
        if (!$searchform) {
            if ($this->required) {
                $select = $mform->addElement('select', $this->itemname, $elementlabel, $labels, $class);
                $select->setMultiple(true);
            } else {
                $elementgroup = array();
                $select = $mform->createElement('select', $this->itemname, '', $labels, $class);
                $select->setMultiple(true);
                $elementgroup[] = $select;
                $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'), array('class' => 'indent-'.$this->indent));
                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '<br />', false);
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
            }
        } else {
            $elementgroup = array();
            $select = $mform->createElement('select', $this->itemname, '', $labels, $class);
            $select->setMultiple(true);
            $elementgroup[] = $select;
            if (!$this->required) {
                $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'), array('class' => 'indent-'.$this->indent));
            }
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'survey'), array('class' => 'indent-'.$this->indent));
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '<br />', false);
            if (!$this->required) {
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
            }
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }

        // defaults
        if (!$searchform) {
            if ($defaults = survey_textarea_to_array($this->defaultvalue)) {
                $defaultkeys = array();
                foreach ($defaults as $default) {
                    $defaultkeys[] = array_search($default, $labels);
                }
                $mform->setDefault($this->itemname, $defaultkeys);
            }
            // } else {
            // $mform->setDefault($this->itemname, array());
        }
        // End of: defaults

        /*
         * this last item is needed because:
         * the check for the not empty field is performed in the validation routine. (not by JS)
         * (JS validation is never added because I do not want it when the "previous" button is pressed and when an item is disabled even if mandatory)
         * The validation routine is executed ONLY ON ITEM that are actually submitted.
         * For multiselect, nothing is submitted if no item is selected
         * so, if the user neglects the mandatory multiselect AT ALL, it is not submitted and, as conseguence, not validated.
         * TO ALWAYS SUBMIT A MULTISELECT I add a dummy hidden item.
         *
         * TAKE CARE: I choose a name for this item that IS UNIQUE BUT is missing the SURVEY_ITEMPREFIX.'_'
         *            In this way I am sure the item will never be saved in the database
         */
        $placeholderitemname = SURVEY_PLACEHOLDERPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';
        $mform->addElement('hidden', $placeholderitemname, SURVEYFIELD_MULTISELECT_PLACEHOLDER);
        $mform->setType($placeholderitemname, PARAM_INT);

        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEY_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
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
            $errorkey = $this->itemname;

            if (!isset($data[$this->itemname])) {
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
        $requests = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue); // 2;3

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $this->itemname.'[]';
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $requests;
        $disabilitationinfo[] = $mformelementinfo;
        // $mform->disabledIf('survey_field_select_2491', 'survey_field_multiselect_2490[]', 'neq', array(0,4));

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
        // 1) I am a multiselect item
        // 2) in $data I can ONLY find $this->itemname

        // I need to verify (checkbox per checkbox) if they hold the same value the user entered
        $labels = $this->item_get_labels_array('options');
        $request = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue); // 2;3

        $status = true;
        foreach ($labels as $k => $label) {
            $index = array_search($k, $request);
            if ($index !== false) {
                $status = $status && (isset($data[$this->itemname.'_'.$k]));
            } else {
                $status = $status && (!isset($data[$this->itemname.'_'.$k]));
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
        if (isset($answer['noanswer'])) {
            $olduserdata->content = SURVEY_NOANSWERVALUE;
            return;
        }

        if (!isset($answer['mainelement'])) { // only placeholder arrived here
            $labels = $this->item_get_labels_array('options');
            $olduserdata->content = implode(SURVEY_DBMULTIVALUESEPARATOR, array_fill(1, count($labels), '0'));
        } else {
            // $answer is an array with the keys of the selected elements
            $olduserdata->content = implode(SURVEY_DBMULTIVALUESEPARATOR, $answer['mainelement']);
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

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if (isset($fromdb->content)) {
            $preset = explode(SURVEY_DBMULTIVALUESEPARATOR, $fromdb->content);
            $prefill[$this->itemname] = $preset;
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

        // $answers is an array like: array(1,1,0,0)
        switch ($format) {
            case SURVEYFIELD_MULTISELECT_RETURNVALUES:
            case SURVEYFIELD_MULTISELECT_RETURNLABELS:
                $answers = explode(SURVEY_DBMULTIVALUESEPARATOR, $content);
                $output = array();
                if ($format == SURVEYFIELD_MULTISELECT_RETURNVALUES) {
                    $values = $this->item_get_values_array('options');
                } else { // $format == SURVEYFIELD_MULTISELECT_RETURNLABELS
                    $values = $this->item_get_labels_array('options');
                }

                $standardanswerscount = count($values);
                foreach ($values as $k => $value) {
                    if (isset($answers[$k])) {
                        $output[] = $value;
                    }
                }
                $return = implode(SURVEY_OUTPUTMULTIVALUESEPARATOR, $output);
                break;
            case SURVEYFIELD_MULTISELECT_RETURNPOSITION:
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
        $elementnames[] = $this->itemname;
        $elementnames[] = SURVEY_PLACEHOLDERPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';

        return $elementnames;
    }
}
