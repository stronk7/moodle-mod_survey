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
require_once($CFG->dirroot.'/mod/survey/field/boolean/lib.php');

class surveyfield_boolean extends mod_survey_itembase {

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
     * $defaultoption
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;

    /*
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = '';

    /*
     * $options = style for the rate: radiobuttons or select menu
     */
    public $style = 0;

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
        $this->plugin = 'boolean';

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

        $this->item_custom_fields_to_form();
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

         * hidden
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

        $record->hideinstructions = 1;
        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (field_base::save($record)))
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
     * sets record field to store the correct value to db for the age custom item
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
    }

    /*
     * item_list_constraints
     *
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        $constraints = array();

        $optionstr = get_string('option', 'surveyfield_boolean');
        $constraints[] = $optionstr.': 0';
        $constraints[] = $optionstr.': 1';

        return implode($constraints, '<br />');
    }

    /*
     * item_get_downloadformats
     *
     * @param
     * @return
     */
    public function item_get_downloadformats() {
        $option = array();
        $option['strfbool01'] = get_string('strfbool01', 'surveyfield_boolean'); // yes/no
        $option['strfbool02'] = get_string('strfbool02', 'surveyfield_boolean'); // Yes/No
        $option['strfbool03'] = get_string('strfbool03', 'surveyfield_boolean'); // y/n
        $option['strfbool04'] = get_string('strfbool04', 'surveyfield_boolean'); // Y/N
        $option['strfbool05'] = get_string('strfbool05', 'surveyfield_boolean'); // up/down
        $option['strfbool06'] = get_string('strfbool06', 'surveyfield_boolean'); // true/false
        $option['strfbool07'] = get_string('strfbool07', 'surveyfield_boolean'); // True/False
        $option['strfbool08'] = get_string('strfbool08', 'surveyfield_boolean'); // T/F
        $option['strfbool09'] = get_string('strfbool09', 'surveyfield_boolean'); // 1/0
        $option['strfbool10'] = get_string('strfbool10', 'surveyfield_boolean'); // +/-

        return $option;
    }

    /*
     * item_get_friendlyformat
     *
     * @param
     * @return
     */
    public function item_get_friendlyformat() {
        return 'strfbool01';
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
    <xs:element name="surveyfield_boolean">
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

                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:int" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:string" name="downloadformat"/>
                <xs:element type="xs:int" name="style"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK parent

    /*
     * parent_encode_child_parentcontent
     *
     * this method is called ONLY at item save time
     * it encodes parentcontent to parentindex
     * @param $childparentcontent
     * return childparentvalue
     */
    public function parent_encode_child_parentcontent($childparentcontent) {
        $parentcontents = array_unique(survey_textarea_to_array($childparentcontent));
        $exportvalues = array('0', '1');

        $childparentvalue = array();
        $labels = array();
        foreach ($parentcontents as $parentcontent) {
            $key = array_search($parentcontent, $exportvalues);
            if ($key !== false) {
                $childparentvalue[] = $key;
            } else {
                // only garbage, but user wrote it
                $labels[] = $parentcontent;
            }
        }
        if (!empty($labels)) {
            $childparentvalue[] = '>';
            $childparentvalue = array_merge($childparentvalue, $labels);
        }

        return implode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue);
    }

    /*
     * parent_decode_child_parentvalue
     *
     * this method decodes parentindex to parentcontent
     * @param $childparentvalue
     * return $childparentcontent
     */
    public function parent_decode_child_parentvalue($childparentvalue) {
        /*
         * I can not make ANY assumption about $childparentvalue because of the following explanation:
         * At child save time, I encode its $parentcontent to $parentvalue.
         * The encoding is done through a parent method according to parent exportvalues.
         * Once the child is saved, I can return to parent and I can change it as much as I want.
         * For instance by changing the number and the content of its options.
         * At parent save time, the child parentvalue is rewritten
         * -> but it may result in a too short or too long list of keys
         * -> or with a wrong number of unrecognized keys so I need to...
         * ...implement all possible checks to avoid crashes/malfunctions during code execution.
         */

        $exportvalues = array('0', '1');
        $parentvalues = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $childparentcontent = array();
        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            for ($i = 0; $i < $key; $i++) {
                $k = $parentvalues[$i];
                if (isset($exportvalues[$k])) {
                    $childparentcontent[] = $exportvalues[$k];
                } else {
                    $childparentcontent[] = $k;
                }
            }

            $key++;
            // only garbage but user wrote it
            for ($i = $key; $i < $actualcount; $i++) {
                $childparentcontent[] = $parentvalues[$i];
            }
        } else {
            foreach ($parentvalues as $parentvalue) {
                if (isset($exportvalues[$parentvalue])) {
                    $childparentcontent[] = $exportvalues[$parentvalue];
                } else {
                    $childparentcontent[] = $parentvalue;
                }
            }
        }

        return implode("\n", $childparentcontent);
    }

    /*
     * parent_validate_child_constraints
     *
     * this method sarting from parentindex declare if the child has chances to be alive
     * @param $childparentvalue
     * @return status of child relation
     *     0 = it will never match
     *     1 = OK
     *     2 = $childparentvalue is malformed
     */
    public function parent_validate_child_constraints($childparentvalue) {
        // see parent method for explanation

        $exportvalues = array('0', '1');
        $parentvalues = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $return = ($actualcount == 2) ? SURVEY_CONDITIONNEVERMATCH : SURVEY_CONDITIONMALFORMED;
        } else {
            if ($actualcount == 1) {
                $k = $parentvalues[0];
                $return = (isset($exportvalues[$k])) ? SURVEY_CONDITIONOK : SURVEY_CONDITIONNEVERMATCH;
            } else {
                $return = SURVEY_CONDITIONMALFORMED;
            }
        }

        return ($return);
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

        $yeslabel = get_string('yes');
        $nolabel = get_string('no');

        if ($this->style == SURVEYFIELD_BOOLEAN_USESELECT) {
            // element values
            $options = array();
            if (!$searchform) {
                if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                    $options[SURVEY_INVITATIONVALUE] = get_string('choosedots');
                }
            } else {
                $options[SURVEY_IGNOREME] = '';
            }
            $options['1'] = $yeslabel;
            $options['0'] = $nolabel;
            if (!$this->required) {
                $options += array(SURVEY_NOANSWERVALUE => get_string('noanswer', 'survey'));
            }
            // End of: element values

            // mform element
            if ($this->required) {
                if (!$searchform) {
                    // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                    // -> I do not want JS form validation if the page is submitted through the "previous" button
                    // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                    // simply add a dummy star to the item and the footer note about mandatory fields
                    $starplace = ($this->position != SURVEY_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
                    $mform->_required[] = $starplace;
                }
            }
            $mform->addElement('select', $this->itemname, $elementlabel, $options, array('class' => 'indent-'.$this->indent));
            // End of: mform element
        } else { // SURVEYFIELD_BOOLEAN_USERADIOV or SURVEYFIELD_BOOLEAN_USERADIOH
            $separator = ($this->style == SURVEYFIELD_BOOLEAN_USERADIOV) ? '<br />' : ' ';
            $elementgroup = array();

            $firstclass = array('class' => 'indent-'.$this->indent);
            $class = ($this->style == SURVEYFIELD_BOOLEAN_USERADIOV) ? $firstclass : '';

            // mform element
            if (!$searchform) {
                if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                    $elementgroup[] = $mform->createElement('radio', $this->itemname, '', get_string('choosedots'), SURVEY_INVITATIONVALUE, $firstclass);
                }
            } else {
                $elementgroup[] = $mform->createElement('radio', $this->itemname, '', get_string('star', 'survey'), SURVEY_IGNOREME, $firstclass);
            }

            $maybeclass = (count($elementgroup)) ? $class : $firstclass;
            $elementgroup[] = $mform->createElement('radio', $this->itemname, '', $yeslabel, '1', $maybeclass);
            $elementgroup[] = $mform->createElement('radio', $this->itemname, '', $nolabel, '0', $class);

            if (!$this->required) {
                $elementgroup[] = $mform->createElement('radio', $this->itemname, '', get_string('noanswer', 'survey'), SURVEY_NOANSWERVALUE, $class);
            }
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            // End of: element values
        }

        // default section
        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                if ($this->position != SURVEY_POSITIONLEFT) {
                    $starplace = $this->itemname.'_extrarow';
                } else {
                    if ($this->style == SURVEYFIELD_BOOLEAN_USESELECT) {
                        $starplace = $this->itemname;
                    } else { // SURVEYFIELD_BOOLEAN_USERADIOV or SURVEYFIELD_BOOLEAN_USERADIOH
                        $starplace = $this->itemname.'_group';
                    }
                }
                $mform->_required[] = $starplace;
            }

            switch ($this->defaultoption) {
                case SURVEY_INVITATIONDEFAULT:
                    $mform->setDefault($this->itemname, SURVEY_INVITATIONVALUE);
                    break;
                case SURVEY_CUSTOMDEFAULT:
                    $mform->setDefault($this->itemname, $this->defaultvalue);
                    break;
                case SURVEY_NOANSWERDEFAULT:
                    $mform->setDefault($this->itemname, SURVEY_NOANSWERVALUE);
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
            }
        } else {
            $mform->setDefault($this->itemname, SURVEY_IGNOREME);
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
        // this plugin displays as dropdown menu or a radio buttons set. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if ($searchform) {
            return;
        }

        if ($this->style != SURVEYFIELD_BOOLEAN_USESELECT) {
            $errorkey = $this->itemname.'_group';
        } else {
            $errorkey = $this->itemname;
        }

        // I need to check value is different from SURVEY_INVITATIONVALUE even if it is not required
        if ($data[$this->itemname] == SURVEY_INVITATIONVALUE) {
            $errors[$errorkey] = get_string('uerr_booleannotset', 'surveyfield_boolean');
            return;
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

        $parentvalues = explode(SURVEY_DBMULTIVALUESEPARATOR, $childparentvalue); // 1;1;0;

        $indexsubset = array();
        $labelsubset = array();
        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $indexsubset = array_slice($parentvalues, 0, $key);
            $labelsubset = array_slice($parentvalues, $key+1);
        } else {
            $indexsubset = $parentvalues;
        }

        if ($indexsubset) {
            // only garbage after the first index, but user wrote it
            foreach ($indexsubset as $index) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname;
                $mformelementinfo->operator = 'neq';
                $mformelementinfo->content = $index;
                $disabilitationinfo[] = $mformelementinfo;
            }
        }

        // only garbage but user wrote it
        if ($labelsubset) {
            foreach ($labelsubset as $k => $label) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname;
                $mformelementinfo->operator = 'neq';
                $mformelementinfo->content = $label;
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
        // 1) I am a boolean item
        // 2) in $data I can ONLY find $this->itemname
        return ($data[$this->itemname] == $childparentvalue);
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

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        $prefill[$this->itemname] = $fromdb->content;

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

        // output
        $answers = explode('/', get_string($format, 'surveyfield_boolean'));
        $return = ($content) ? $answers[0] : $answers[1];

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
        if ($this->style == SURVEYFIELD_BOOLEAN_USESELECT) {
            $elementnames[] = $this->itemname;
        } else {
            $elementnames[] = $this->itemname.'_group';
        }

        return $elementnames;
    }
}
