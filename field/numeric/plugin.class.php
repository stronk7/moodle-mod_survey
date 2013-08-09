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
require_once($CFG->dirroot.'/mod/survey/field/numeric/lib.php');

class surveyfield_numeric extends mod_survey_itembase {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_numeric record
     */
    public $pluginid = 0;

    /*******************************************************************/

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $decimalseparator
     */
    public $decimalseparator = '.';

    /*
     * $signed = will be, the expected number, signed
     */
    public $signed = 0;

    /*
     * $lowerbound = the minimun allowed value
     */
    public $lowerbound = '';

    /*
     * $upperbound = the maximum allowed value
     */
    public $upperbound = '';

    /*
     * $decimals = number of decimals allowed for this number
     */
    public $decimals = 0;

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
        $this->plugin = 'numeric';
        $this->decimalseparator = get_string('decsep', 'langconfig');

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
     *
     * @param $itemid
     * @return
     */
    public function item_load($itemid) {
        // Do parent item loading stuff here (mod_survey_itembase::item_load($itemid)))
        parent::item_load($itemid);

        // float numbers need more attention because I can write them using , or .
        if (strlen($this->defaultvalue)) {
            $this->defaultvalue = format_float($this->defaultvalue, $this->decimals);
        }
        if (strlen($this->lowerbound)) {
            $this->lowerbound = format_float($this->lowerbound, $this->decimals);
        }
        if (strlen($this->upperbound)) {
            $this->upperbound = format_float($this->upperbound, $this->decimals);
        }

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

        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        // signed
        $record->signed = isset($record->signed) ? 1 : 0;

        // float numbers need more attention because I can write them using , or .
        if (strlen($record->defaultvalue)) {
            $record->defaultvalue = unformat_float($record->defaultvalue, true);
        } else {
            $record->defaultvalue = null;
        }
        if (strlen($record->lowerbound)) {
            $record->lowerbound = unformat_float($record->lowerbound, true);
        } else {
            $record->lowerbound = null;
        }
        if (strlen($record->upperbound)) {
            $record->upperbound = unformat_float($record->upperbound, true);
        } else {
            $record->upperbound = null;
        }

        // Do parent item saving stuff here (mod_survey_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_custom_fields_to_form
     * add checkboxes selection for empty fields
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
        // nothing to do: defaultvalue doesn't need any further care
        if ($record->defaultvalue === '') {
            $record->defaultvalue = null;
        }
    }

    /*
     * item_atomize_parent_content
     * starting from parentcontent, this function returns it splitted into an array
     *
     * @param $parentcontent
     * @return
     */
    public function item_atomize_parent_content($parentcontent) {
        $pattern = '~^\s*([0-9]+)'.get_string('decsep', 'langconfig').'?([0-9]*)\s*$~';
        preg_match($pattern, $parentcontent, $matches);

        return $matches;
    }

    /*
     * item_get_parent_format
     *
     * @param
     * @return
     */
    public function item_get_parent_format() {
        return get_string('parentformatdecimal', 'surveyfield_'.$this->plugin, $this->decimalseparator);
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        return parent::item_get_multilang_fields();
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
    <xs:element name="survey_numeric">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:decimal" name="defaultvalue"/>
                <xs:element type="xs:int" name="signed"/>
                <xs:element type="xs:decimal" name="lowerbound"/>
                <xs:element type="xs:decimal" name="upperbound"/>
                <xs:element type="xs:int" name="decimals"/>
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

        $mform->addElement('text', $this->itemname, $elementlabel, array('class' => 'indent-'.$this->indent, 'itemid' => $this->itemid));
        $mform->setType($this->itemname, PARAM_RAW); // see: moodlelib.php lines 133+
        if (!$searchform) {
            if (strlen($this->defaultvalue)) {
                $mform->setDefault($this->itemname, "$this->defaultvalue");
            }

            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. THIS IS A MOODLE ISSUE. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                $starplace = ($this->extrarow) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace; // add the star for mandatory fields at the end of the page with server side validation too
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
        if ($this->extrarow) {
            $errorkey = $this->itemname.'_extrarow';
        } else {
            $errorkey = $this->itemname;
        }

        $draftuserinput = $data[$this->itemname];
        if ($this->required) {
            if (strlen($draftuserinput) == 0) {
                $errors[$errorkey] = get_string('required');
                return;
            }
        }

        if (!isset($draftuserinput)) {
            return;
        }

        // if it is not a number, shouts
        if (strlen($draftuserinput)) {
            $pattern = '~^\s*([0-9]+)'.$this->decimalseparator.'?([0-9]*)\s*$~';
            if (!preg_match($pattern, $draftuserinput, $matches)) {
                $errors[$errorkey] = get_string('uerr_notanumber', 'surveyfield_numeric');
                return;
            } else {
                $userinput = $matches[1].'.'.$matches[2];
                // if it is < 0 but has been defined as unsigned, shouts
                if (!$this->signed && ($userinput < 0)) {
                    $errors[$errorkey] = get_string('uerr_negative', 'surveyfield_numeric');
                }
                // if it has decimal but has been defined as integer, shouts
                $is_integer = (bool)(strval(intval($userinput)) == strval($userinput));
                if (($this->decimals == 0) && (!$is_integer)) {
                    $errors[$errorkey] = get_string('uerr_notinteger', 'surveyfield_numeric');
                }
            }
        }

        $haslowerbound = (strlen($this->lowerbound));
        $hasupperbound = (strlen($this->upperbound));

        if ($haslowerbound && $hasupperbound) {
            if ($this->lowerbound < $this->upperbound) {
                // internal range
                if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                    $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyfield_numeric');
                }
            }

            if ($this->lowerbound > $this->upperbound) {
                // external range
                if (($userinput > $this->lowerbound) && ($userinput < $this->upperbound)) {
                    $format = get_string($this->item_get_friendlyformat(), 'surveyfield_numeric');
                    $a = new stdclass();
                    $a->lowerbound = $this->lowerbound;
                    $a->upperbound = $this->upperbound;
                    $errors[$errorkey] = get_string('uerr_outofexternalrange', 'surveyfield_numeric', $a);
                }
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyfield_numeric');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyfield_numeric');
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

        $haslowerbound = (strlen($this->lowerbound));
        $hasupperbound = (strlen($this->upperbound));
        $fillinginstruction = array();

        if (!empty($this->signed)) {
            $fillinginstruction[] = get_string('restriction_hassign', 'surveyfield_numeric');
        }

        if ($haslowerbound && $hasupperbound) {
            $a = new StdClass();
            $a->lowerbound = $this->lowerbound;
            $a->upperbound = $this->upperbound;

            if ($this->lowerbound < $this->upperbound) {
                $fillinginstruction[] = get_string('restriction_lowerupper', 'surveyfield_numeric', $a);
            }

            if ($this->lowerbound > $this->upperbound) {
                $fillinginstruction[] = get_string('restriction_upperlower', 'surveyfield_numeric', $a);
            }
        } else {
            if ($haslowerbound) {
                $a = $this->lowerbound;
                $fillinginstruction[] = get_string('restriction_lower', 'surveyfield_numeric', $a);
            }

            if ($hasupperbound) {
                $a = $this->upperbound;
                $fillinginstruction[] = get_string('restriction_upper', 'surveyfield_numeric', $a);
            }
        }

        if (!empty($this->decimals)) {
            $a = $this->decimals;
            $fillinginstruction[] = get_string('restriction_hasdecimals', 'surveyfield_numeric', $a);
            $fillinginstruction[] = get_string('decimalautofix', 'surveyfield_numeric');
            // this sentence dials about decimal separator not about the expected value
            // so I leave it as last sentence
            $fillinginstruction[] = get_string('declaredecimalseparator', 'surveyfield_numeric', $this->decimalseparator);
        } else {
            $fillinginstruction[] = get_string('restriction_isinteger', 'surveyfield_numeric');
        }

        if (count($fillinginstruction)) {
            $fillinginstruction = get_string('number', 'surveyfield_numeric').implode('; ', $fillinginstruction);
        } else {
            $fillinginstruction = '';
        }

        return $fillinginstruction;
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
        if (strlen($answer['mainelement']) == 0) {
            $olduserdata->content = null;
        } else {
            if (empty($this->decimals)) {
                $olduserdata->content = $answer['mainelement'];
            } else {
                $matches = $this->item_atomize_parent_content($answer['mainelement']);
                $decimals = isset($matches[2]) ? $matches[2] : '';
                if (strlen($decimals) > $this->decimals) {
                    // round it
                    $decimals = round((float)$decimals, $this->decimals);
                }
                if (strlen($decimals) < $this->decimals) {
                    // padright
                    $decimals = str_pad($decimals, $this->decimals, '0', STR_PAD_RIGHT);
                }
                if (isset($matches[1])) {
                    // I DO ALWATYS save using english decimal separator
                    // At load time, the number will be formatted according to user settings
                    $olduserdata->content = $matches[1].'.'.$decimals;
                } else {
                    // in the SEARCH form the remote user entered something very wrong
                    // remember: the for search form NO VALIDATION IS PERFORMED
                    // user is free to waste his/her time as he/she like
                    $olduserdata->content = $answer['mainelement'];
                }
            }
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
                $prefill[$this->itemname] = number_format((double)$fromdb->content, $this->decimals, $this->decimalseparator, '');
            // } else {
                // nothing was set
                // do not accept defaults but overwrite them
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
        $content = trim($answer->content);
        // SURVEY_NOANSWERVALUE does not exist here
        if (strlen($content) == 0) { // item was disabled
            return get_string('notanswereditem', 'survey');
        }

        return $content;
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