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
        $this->type = SURVEY_TYPEFIELD;
        $this->plugin = 'character';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->couldbeparent = false;
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

        // set custom fields value as defined for this question plugin
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
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        if ($record->pattern == SURVEYFIELD_CHARACTER_CUSTOMPATTERN) {
            $record->pattern = $record->pattern_text;
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
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

    // MARK userform

    /*
     * userform_mform_element
     * @param $mform
     * @return
     */
    public function userform_mform_element($mform, $survey, $canaccessadvancedform, $parentitem=null, $searchform=false) {
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $mform->addElement('text', $this->itemname, $elementlabel, array('class' => 'indent-'.$this->indent));
        $mform->setType($this->itemname, PARAM_RAW);
        if (!$searchform) {
            $mform->setDefault($this->itemname, $this->defaultvalue);

            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted trough the "previous" button
                // -> I do not want JS field validation even if this item is required AND disabled too. THIS IS A MOODLE BUG. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                if ($this->extrarow) {
                    $starplace = $this->itemname.'_extrarow';
                } else {
                    $starplace = $this->itemname;
                }
                $mform->_required[] = $starplace; // add the star for mandatory fields at the end of the page with server side validation too
            }
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        if ($this->extrarow) {
            $errorkey = $this->itemname.'_extrarow';
        } else {
            $errorkey = $this->itemname;
        }

        if ($this->required) {
            if (empty($data[$this->itemname])) {
                $errors[$errorkey] = get_string('required');
                return;
            }
        }

        if (!empty($data[$this->itemname])) {
            $fieldlength = strlen($data[$this->itemname]);
            if ($fieldlength > $this->maxlength) {
                $errors[$errorkey] = get_string('uerr_texttoolong', 'surveyfield_character');
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
     * @param
     * @return
     */
    public function userform_get_filling_instructions() {

        if (is_null($this->pattern)) {
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
     * @param $itemdetail, $olduserdata
     * @return
     */
    public function userform_save_preprocessing($itemdetail, $olduserdata) {
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
            $prefill[$this->itemname] = $olduserdata->content;
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