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
require_once($CFG->dirroot.'/mod/survey/field/character/lib.php');

class surveyfield_character extends mod_survey_itembase {

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

    // -----------------------------

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $pattern = a string defining which character is expected in each position of the incoming string
     * [a regular expression?]
     */
    public $pattern = '';

    /*
     * $minlength = the minimum allowed length
     */
    public $minlength = '0';

    /*
     * $maxlength = the maximum allowed length
     */
    public $maxlength = null;

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*
     * $canbeparent
     */
    public static $canbeparent = false;

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
        $this->plugin = 'character';

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEY_ITEMCONTENTFILEAREA);

        // list of fields I do not want to have in the item definition form
        // EMPTY LIST

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

        if (!isset($record->minlength)) {
            $record->minlength = 0;
        }
        // maxlength is a PARAM_INT. If the user leaves it empty in the form, maxlength becomes = 0
        if (empty($record->maxlength)) {
            $record->maxlength = null;
        }
        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (mod_survey_itembase::save($record)))
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
        switch ($this->pattern) {
            case SURVEYFIELD_CHARACTER_FREEPATTERN:
            case SURVEYFIELD_CHARACTER_EMAILPATTERN:
            case SURVEYFIELD_CHARACTER_URLPATTERN:
                break;
            default:
                $this->pattern_text = $this->pattern;
                $this->pattern = SURVEYFIELD_CHARACTER_CUSTOMPATTERN;
        }

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
        if ($record->pattern == SURVEYFIELD_CHARACTER_CUSTOMPATTERN) {
            $record->pattern = $record->pattern_text;

            $record->minlength = strlen($record->pattern_text);
            $record->maxlength = $record->minlength;
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_fields_with_checkbox_todb
     * this function is called to empty fields where $record->{$field.'_check'} == 1
     *
     * @param $record
     * @param $fieldlist
     * @return
     */
    public function item_fields_with_checkbox_todb($record, $fieldlist) {
        foreach ($fieldlist as $fieldbase) {
            if (isset($record->{$fieldbase.'_check'})) {
                $record->{$fieldbase} = null;
                $record->{$fieldbase.'_text'} = null;
            }
        }
    }

    /*
     * item_get_generic_field
     *
     * @param
     * @return
     */
    public function item_get_generic_field($field) {
        if ($field == 'pattern') {
            if ($this->pattern == SURVEYFIELD_CHARACTER_CUSTOMPATTERN) {
                return $this->pattern_text;
            } else {
                return $this->pattern;
            }
        } else {
            return parent::item_get_generic_field($field);
        }
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['character'] = array('defaultvalue');

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
    <xs:element name="surveyfield_character">
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
                <xs:element type="xs:int" name="hideinstructions"/>
                <xs:element type="xs:string" name="variable" minOccurs="0"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:string" name="pattern"/>
                <xs:element type="xs:int" name="minlength" minOccurs="0"/>
                <xs:element type="xs:int" name="maxlength" minOccurs="0"/>
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
     * @param $searchform
     * @return
     */
    public function userform_mform_element($mform, $searchform) {
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = ($this->position == SURVEY_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $thresholdsize = 48;
        $options = array('class' => 'indent-'.$this->indent);
        if (!empty($this->maxlength)) {
            $options['maxlength'] = $this->maxlength;
            if ($this->maxlength < $thresholdsize) {
                $options['size'] = $this->maxlength;
            } else {
                $options['size'] = $thresholdsize;
            }
        } else {
            $options['size'] = $thresholdsize;
        }
        if (!$searchform) {
            $mform->addElement('text', $this->itemname, $elementlabel, $options);
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->setDefault($this->itemname, $this->defaultvalue);

            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEY_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace;
            }
        } else {
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', array('class' => 'indent-'.$this->indent));
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'survey'));
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
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

        $errorkey = $this->itemname;

        if ($this->required) {
            if (empty($data[$this->itemname])) {
                $errors[$errorkey] = get_string('required');
                return;
            }
        }

        if ($this->pattern == SURVEYFIELD_CHARACTER_FREEPATTERN) {
            return;
        }

        if (!empty($data[$this->itemname])) {
            $fieldlength = strlen($data[$this->itemname]);
            if (!empty($this->maxlength)) {
                if ($fieldlength > $this->maxlength) {
                    $errors[$errorkey] = get_string('uerr_texttoolong', 'surveyfield_character');
                }
            }
            if ($fieldlength < $this->minlength) {
                $errors[$errorkey] = get_string('uerr_texttooshort', 'surveyfield_character');
            }
            if (!empty($data[$this->itemname]) && !empty($this->pattern)) {
                switch ($this->pattern) {
                    case SURVEYFIELD_CHARACTER_EMAILPATTERN:
                        if (!validate_email($data[$this->itemname])) {
                            $errors[$errorkey] = get_string('uerr_invalidemail', 'surveyfield_character');
                        }
                        break;
                    case SURVEYFIELD_CHARACTER_URLPATTERN:
                        // if (!preg_match('~^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$~i', $data[$this->itemname])) {
                        if (!survey_character_is_valid_url($data[$this->itemname])) {
                            $errors[$errorkey] = get_string('uerr_invalidurl', 'surveyfield_character');
                        }
                        break;
                    case SURVEYFIELD_CHARACTER_CUSTOMPATTERN: // it is a custom pattern done with "A", "a", "*" and "0"
                        // "A" UPPER CASE CHARACTERS
                        // "a" lower case characters
                        // "*" UPPER case, LOWER case or any special characters like '@', ',', '%', '5', ' ' or whatever
                        // "0" numbers

                        if ($fieldlength != strlen($this->pattern_text)) {
                            $errors[$errorkey] = get_string('uerr_badlength', 'surveyfield_character');
                        }

                        if (!survey_character_text_match_pattern($data[$this->itemname], $this->pattern_text)) {
                            $errors[$errorkey] = get_string('uerr_nopatternmatch', 'surveyfield_character');
                        }
                        break;
                    default:
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->pattern = '.$this->pattern);
                }
            }
        }
        // return $errors; is not needed because $errors is passed by reference
    }

    /*
     * userform_get_filling_instructions
     *
     * @param
     * @return
     */
    public function userform_get_filling_instructions() {

        if ($this->pattern == SURVEYFIELD_CHARACTER_FREEPATTERN) {
            if ($this->minlength) {
                if ($this->maxlength) {
                    $a = new stdClass();
                    $a->minlength = $this->minlength;
                    $a->maxlength = $this->maxlength;
                    $fillinginstruction = get_string('restrictions_minmax', 'surveyfield_character', $a);
                } else {
                    $a = $this->minlength;
                    $fillinginstruction = get_string('restrictions_min', 'surveyfield_character', $a);
                }
            } else {
                if ($this->maxlength) {
                    $a = $this->maxlength;
                    $fillinginstruction = get_string('restrictions_max', 'surveyfield_character', $a);
                } else {
                    $fillinginstruction = '';
                }
            }
        } else {
            switch ($this->pattern) {
                case SURVEYFIELD_CHARACTER_EMAILPATTERN:
                    $fillinginstruction = get_string('restrictions_email', 'surveyfield_character');
                    break;
                case SURVEYFIELD_CHARACTER_URLPATTERN:
                    $fillinginstruction = get_string('restrictions_url', 'surveyfield_character');
                    break;
                default:
                    $fillinginstruction = get_string('restrictions_custom', 'surveyfield_character', $this->pattern_text);
            }
        }

        return $fillinginstruction;
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
        if (isset($answer['ignoreme'])) {
            $olduserdata->content = null;
            return;
        }

        if (strlen($answer['mainelement']) == 0) {
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
     * @param $olduserdata
     * @return
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if ($fromdb->content == SURVEY_NOANSWERVALUE) {
            $prefill[$this->itemname] = '';
        } else {
            $prefill[$this->itemname] = $fromdb->content;
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
        $content = trim($answer->content);
        // SURVEY_NOANSWERVALUE does not exist here
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'survey');
        }

        // output
        if (strlen($content)) {
            $return = $content;
        } else {
            if ($format == SURVEY_FIRENDLYFORMAT) {
                $return = get_string('emptyanswer', 'survey');
            } else {
                $return = '';
            }
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
        $elementnames = array($this->itemname);

        return $elementnames;
    }
}
