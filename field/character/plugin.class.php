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

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/mod/survey/itembase.class.php');
require_once($CFG->dirroot.'/mod/survey/field/character/lib.php');

class surveyfield_character extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_character record
     */
    public $pluginid = 0;

    /*******************************************************************/

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
    public $maxlength = '255';

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
        $this->type = SURVEY_FIELD;
        $this->plugin = 'character';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->ismatchable = true;
        $this->flag->useplugintable = true;

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
        $fieldlist = array('content', 'defaultvalue');
        $this->item_builtin_string_load_support($fieldlist);

        $this->item_custom_fields_to_form();
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

        // set custom fields value as defined for this field
        $this->item_custom_fields_to_db($record);

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

        if (!isset($record->minlength)) {
            $record->minlength = 0;
        }
        if (!isset($record->maxlength)) {
            $record->maxlength = 255;
        }
        // Do parent item saving stuff here (surveyitem_base::save($record)))
        return parent::item_save($record);
    }

    /*
     * item_custom_fields_to_form
     * translates the date class property $fieldlist in $field.'_year' and $field.'_month'
     * @param
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // here, at local level (in survey_character ONLY) I manage the <not standard> "pattern" field
        // $this->pattern_text and $this->pattern_check don't exist in the database
        $options = array(SURVEYFIELD_CHARACTER_EMAILPATTERN, SURVEYFIELD_CHARACTER_URLPATTERN); // SURVEYFIELD_CHARACTER_CUSTOMPATTERN can not be used

        $fieldlist = $this->item_fields_with_free_checkbox();
        foreach ($fieldlist as $field) {
            if (!isset($this->{$field})) {
                $this->{$field.'_check'} = 1;
            } else {
                $this->{$field.'_check'} = 0;
                if (!in_array($this->{$field}, $options)) {
                    $this->{$field.'_text'} = $this->{$field};
                    $this->{$field} = SURVEYFIELD_CHARACTER_CUSTOMPATTERN;
                }
            }
        }

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the date custom item
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        $fieldlist = $this->item_fields_with_free_checkbox();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_check'})) {
                $record->{$field} = null;
                $record->{$field.'_text'} = null;
            } else {
                if ($record->{$field} == SURVEYFIELD_CHARACTER_CUSTOMPATTERN) {
                    $record->{$field} = $record->pattern_text;
                }
            }
        }

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_fields_with_free_checkbox
     * get the list of composite fields
     * @param
     * @return
     */
    public function item_fields_with_free_checkbox() {
        return array('pattern');
    }

    /*
     * item_fields_with_checkbox_todb
     * this function is called to empty fields where $record->{$field.'_check'} == 1
     * @param $record, $fieldlist
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
     * item_parent_content_format_validation
     * checks whether the user input format in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_format_validation($parentcontent) {
        // '[an_alphanum_string]'; alias, whatever you write is correct
    }

    /*
     * item_parent_content_content_validation
     * checks whether the user input content in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_content_validation($parentcontent) {
        // I am not supposed to add more strict checks here
        // because they are useless until I can still change the parent item
    }

    /*
     * item_parent_content_encode_value
     * starting from the user input, this function stores to the db the value as it is stored during survey submission
     * this method manages the $parentcontent of its child item, not its own $parentcontent
     * (take care: here we are not submitting a survey but we are submitting an item)
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_encode_value($parentcontent) {
        return $parentcontent;
    }

    /*
     * item_get_hard_info
     * @param
     * @return
     */
    public function item_get_hard_info() {

        if (is_null($this->pattern)) {
            $hardinfo = '';
        } else {
            switch ($this->pattern) {
                case SURVEYFIELD_CHARACTER_EMAILPATTERN:
                    $hardinfo = get_string('restrictions_email', 'surveyfield_character');
                    break;
                case SURVEYFIELD_CHARACTER_URLPATTERN:
                    $hardinfo = get_string('restrictions_url', 'surveyfield_character');
                    break;
                default:
                    $hardinfo = get_string('restrictions_custom', 'surveyfield_character', $this->pattern_text);
            }
        }

        return $hardinfo;
    }

    /*
     * item_list_constraints
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        $constraints = array();
        if (isset($this->pattern)) {
            switch ($this->pattern) {
                case SURVEYFIELD_CHARACTER_EMAILPATTERN:
                    $constraints[] = get_string('pattern', 'surveyfield_character').': '.get_string('mail', 'surveyfield_character');
                    break;
                case SURVEYFIELD_CHARACTER_URLPATTERN:
                    $constraints[] = get_string('pattern', 'surveyfield_character').': '.get_string('url', 'surveyfield_character');
                    break;
                default:
                    $constraints[] = get_string('pattern', 'surveyfield_character').': '.$this->pattern_text;
                    break;
            }
        } else {
            $constraints[] = get_string('minlength', 'surveyfield_character').': '.$this->minlength;
            $constraints[] = get_string('maxlength', 'surveyfield_character').': '.$this->maxlength;
        }

        return implode($constraints, '<br />');
    }

    /*
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        $status = true;
        if (isset($this->pattern)) {
            switch ($this->pattern) {
                case SURVEYFIELD_CHARACTER_EMAILPATTERN:
                    $status = validate_email($childvalue);
                    break;
                case SURVEYFIELD_CHARACTER_URLPATTERN:
                    $status = survey_character_is_valid_url($childvalue);
                    break;
                default:
                    $status = survey_character_text_match_pattern($childvalue, $this->pattern_text);
            }
        } else {
            $status = $status && (strlen($childvalue) >= $this->minlength);
            $status = $status && (strlen($childvalue) <= $this->maxlength);
        }

        return $status;
    }

    /*
     * item_get_plugin_values
     * @param $pluginstructure
     * @param $pluginsid
     * @return
     */
    public function item_get_plugin_values($pluginstructure, $pluginsid) {
        $values = parent::item_get_plugin_values($pluginstructure, $pluginsid);

        // STEP 02: make corrections
        // $si_fields = array('id', 'surveyid', 'itemid',
        //                    'defaultvalue_sid', 'defaultvalue', 'pattern',
        //                    'minlength', 'maxlength');
        // 'id', 'surveyid', 'itemid' were managed by parent class
        // here I manage pattern once again because they were not written using constants

        // override: $value['pattern']
        /*------------------------------------------------*/
        switch ($this->pattern) {
            case SURVEYFIELD_CHARACTER_EMAILPATTERN:
                $values['pattern'] = 'SURVEYFIELD_CHARACTER_EMAILPATTERN';
                break;
            case SURVEYFIELD_CHARACTER_URLPATTERN:
                $values['pattern'] = 'SURVEYFIELD_CHARACTER_URLPATTERN';
                break;
            case null:
                $values['pattern'] = 'null';
                break;
            default:
                $values['pattern'] = '\''.$this->pattern.'\'';
        }

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
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $mform->addElement('text', $fieldname, $elementlabel, array('class' => 'indent-'.$this->indent));
        $mform->setType($fieldname, PARAM_RAW);
        if (!$searchform) {
            $mform->setDefault($fieldname, $this->defaultvalue);
            $maybedisabled = $this->userform_can_be_disabled($survey, $canaccessadvancedform, $parentitem);
            if ($this->required && (!$maybedisabled)) {
                // $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
                $mform->addRule($fieldname, get_string('required'), 'nonempty_rule', $mform);
                $mform->_required[] = $fieldname;
            }
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        // useless: empty values are checked in Server Side Validation in submissions_form.php
        // if (empty($data[$fieldname])) {
        //     $errors[$fieldname] = get_string('required');
        //     return;
        // }

        $fieldlength = strlen($data[$fieldname]);
        if ($fieldlength > $this->maxlength) {
            $errors[$fieldname] = get_string('uerr_texttoolong', 'surveyfield_character');
        }
        if ($fieldlength < $this->minlength) {
            $errors[$fieldname] = get_string('uerr_texttooshort', 'surveyfield_character');
        }
        if (!empty($data[$fieldname]) && !empty($this->pattern)) {
            switch ($this->pattern) {
                case SURVEYFIELD_CHARACTER_EMAILPATTERN:
                    if (!validate_email($data[$fieldname])) {
                        $errors[$fieldname] = get_string('uerr_invalidemail', 'surveyfield_character');
                    }
                    break;
                case SURVEYFIELD_CHARACTER_URLPATTERN:
                    // if (!preg_match('~^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$~i', $data[$fieldname])) {
                    if (!survey_character_is_valid_url($data[$fieldname])) {
                        $errors[$fieldname] = get_string('uerr_invalidurl', 'surveyfield_character');
                    }
                    break;
                case SURVEYFIELD_CHARACTER_CUSTOMPATTERN: // it is a custom pattern done with "A", "a", "*" and "0"
                    // "A" UPPER CASE CHARACTERS
                    // "a" lower case characters
                    // "*" UPPER case, LOWER case or any special characters like '@', ',', '%', '5', ' ' or whatever
                    // "0" numbers

                    if ($fieldlength != strlen($this->pattern_text)) {
                        $errors[$fieldname] = get_string('uerr_badlength', 'surveyfield_character');
                    }

                    if (!survey_character_text_match_pattern($data[$fieldname], $this->pattern_text)) {
                        $errors[$fieldname] = get_string('uerr_nopatternmatch', 'surveyfield_character');
                    }
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->pattern = '.$this->pattern);
            }
        }
        // return $errors; is not needed because $errors is passed by reference
    }

    /*
     * userform_get_parent_disabilitation_info
     * from child_parentcontent defines syntax for disabledIf
     * @param: $child_parentcontent
     * @return
     */
    public function userform_get_parent_disabilitation_info($child_parentcontent) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $disabilitationinfo = array();

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $fieldname;
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $child_parentcontent;
        $disabilitationinfo[] = $mformelementinfo;

        return $disabilitationinfo;
    }

    /*
     * userform_save
     * starting from the info set by the user in the form
     * I define the info to store in the db
     * @param $itemdetail, $olduserdata
     * @return
     */
    public function userform_save($itemdetail, $olduserdata) {
        if (isset($itemdetail['noanswer'])) {
            $olduserdata->content = null;
            return;
        }

        if (isset($itemdetail['mainelement'])) {
            $olduserdata->content = $itemdetail['mainelement'];
            return;
        }

        throw new moodle_exception('unhandled return value from user submission');
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
            $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
            $prefill[$fieldname] = $olduserdata->content;
        } // else use item defaults

        return $prefill;
    }

    /*
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return false;
    }
}