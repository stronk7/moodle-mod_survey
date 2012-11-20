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


/**
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
require_once($CFG->dirroot.'/mod/survey/field/numeric/lib.php');

class surveyfield_numeric extends surveyitem_base {

    /**
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

    /********************************************************************/

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;

    /*
     * $signed = will be, the expected number, signed
     */
    public $signed = 0;

    /*
     * $lowerbound = the minimun allowed value
     */
    public $lowerbound = 0;

    /*
     * $upperbound = the maximum allowed value
     */
    public $upperbound = 0;

    /*
     * $decimals = number of decimals allowed for this number
     */
    public $decimals = 0;

    /**
     * $flag = features describing the object
     */
    public $flag;

    /**
     * $item_form_requires = list of fields I will see in the form
     * public $item_form_requires;
     */

    /********************************************************************/

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional survey_item ID
     */
    public function __construct($itemid=0) {
        $this->type = SURVEY_FIELD;
        $this->plugin = 'numeric';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->ismatchable = true;
        $this->flag->useplugintable = true;

        if (!empty($itemid)) {
                $this->item_load($itemid);
        }
    }

    /**
     * item_load
     * @param $itemid
     * @return
     */
    public function item_load($itemid) {
        // Do parent item loading stuff here (surveyitem_base::item_load($itemid)))
        parent::item_load($itemid);

        // float numbers need more attention because I can write them using , or .
        if (!empty($this->defaultvalue)) $this->defaultvalue = format_float($this->defaultvalue, $this->decimals);
        if (!empty($this->lowerbound)) $this->lowerbound = format_float($this->lowerbound, $this->decimals);
        if (!empty($this->upperbound)) $this->upperbound = format_float($this->upperbound, $this->decimals);

        // multilang load support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $fieldlist = array('content', 'defaultvalue');
        $this->item_builtin_string_load_support($fieldlist);

        $this->item_custom_fields_to_form();
    }

    /**
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

        // signed
        $record->signed = isset($record->signed) ? 1 : 0;

        // float numbers need more attention because I can write them using , or .
        if (!empty($this->defaultvalue)) $this->defaultvalue = unformat_float($this->defaultvalue);
        if (!empty($this->lowerbound)) $this->lowerbound = unformat_float($this->lowerbound);
        if (!empty($this->upperbound)) $this->upperbound = unformat_float($this->upperbound);

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
    }

    /**
     * item_custom_fields_to_form
     * add checkboxes selection for empty fields
     * @param
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        $fieldlist = $this->item_fields_with_free_checkbox();
        foreach ($fieldlist as $field) {
            if (!isset($this->{$field})) {
                $this->{$field.'_check'} = 1;
            } else {
                $this->{$field.'_check'} = 0;
            }
        }

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the age custom item
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        $fieldlist = $this->item_fields_with_free_checkbox();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_check'})) {
                $record->{$field} = null;
            }
        }

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
        if ($record->defaultvalue === '') {
            $record->defaultvalue = null;
        }
    }

    /**
     * item_parent_content_format_validation
     * checks whether the user input format in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_format_validation($parentcontent) {
        if (!$thenumber = unformat_float($parentcontent)) {
            $decimalseparator = get_string('decsep', 'langconfig');
            $format = get_string('parentformatdecimal', 'surveyfield_numeric', $decimalseparator);
            return (get_string('invalidformat_err', 'survey', $format));
        }
    }

    /**
     * item_parent_content_content_validation
     * checks whether the user input content in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_content_validation($parentcontent) {
        if (!$thenumber = unformat_float($parentcontent)) {
            return (get_string('parentcontent_isnotanumber', 'surveyfield_numeric'));
        }
        // I am not supposed to add more strict checks here
        // because they are useless until I can still change the parent item
    }

    /**
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

    /**
     * item_fields_with_free_checkbox
     * get the list of composite fields
     * @param
     * @return
     */
    public function item_fields_with_free_checkbox() {
        return array('decimals', 'lowerbound', 'upperbound');
    }

    /**
     * item_atomize_parent_content
     * starting from parentcontent, this function returns it splitted into an array
     * @param $parentcontent
     * @return
     */
    public function item_atomize_parent_content($parentcontent) {
        $pattern = '~^([0-9]+)'.get_string('decsep', 'langconfig').'([0-9]+)$~';
        preg_match($pattern, $parentcontent, $matches);

        return $matches;
    }

    /**
     * item_get_hard_info
     * @param
     * @return
     */
    public function item_get_hard_info() {

        $hardinfo = array();

        if (!empty($this->signed)) {
            $hardinfo[] = get_string('hassign', 'surveyfield_numeric');
        }
        if (!empty($this->lowerbound)) {
            $a = $this->lowerbound;
            $hardinfo[] = get_string('hasminvalue', 'surveyfield_numeric', $a);
        }
        if (!empty($this->upperbound)) {
            $a = $this->upperbound;
            $hardinfo[] = get_string('hasmaxvalue', 'surveyfield_numeric', $a);
        }
        if (!empty($this->decimals)) {
            $a = $this->decimals;
            $hardinfo[] = get_string('hasdecimals', 'surveyfield_numeric', $a);
            $hardinfo[] = get_string('decimalautofix', 'surveyfield_numeric');
        } else {
            $hardinfo[] = get_string('isinteger', 'surveyfield_numeric');
        }
        if (!empty($this->decimals)) {
            // this sentence talks about decinal separator not about the expected value
            // so I leave it as last sentence
            $decimalseparator = get_string('decsep', 'langconfig');
            $hardinfo[] = get_string('declaredecimalseparator', 'surveyfield_numeric', $decimalseparator);
        }
        if (count($hardinfo)) {
            $hardinfo = get_string('number', 'surveyfield_numeric').implode(', ', $hardinfo);
        } else {
            $hardinfo = '';
        }

        return $hardinfo;
    }

    /**
     * item_list_constraints
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        $constraints = array();

        if (!empty($this->signed)) {
            $constraints[] = get_string('signed', 'surveyfield_numeric').': '.get_string('allowed', 'surveyfield_numeric');
        }
        if (isset($this->decimals)) {
            $constraints[] = get_string('decimals', 'surveyfield_numeric').': '.$this->decimals;
        }
        if (isset($this->lowerbound)) {
            $constraints[] = get_string('lowerbound', 'surveyfield_numeric').': '.$this->lowerbound;
        }
        if (isset($this->upperbound)) {
            $constraints[] = get_string('upperbound', 'surveyfield_numeric').': '.$this->upperbound;
        }

        return implode($constraints, '<br />');
    }

    /**
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        $status = true;

        $matches = $this->item_atomize_parent_content($childvalue);
        $decimals = $matches[2];
        // $status = true only if strlen($decimals) il lower or equal than $this->decimals
        $status = $status && (strlen($decimals) <= $this->decimals);

        $status = $status && ($childvalue >= $this->lowerbound);
        $status = $status && ($childvalue <= $this->upperbound);

        return $status;
    }

    /**
     * item_get_parent_format
     * @param
     * @return
     */
    public function item_get_parent_format() {
        $decimalseparator = get_string('decsep', 'langconfig');

        return get_string('parentformatdecimal', 'surveyfield_'.$this->plugin, $decimalseparator);
    }

    /**
     * item_get_plugin_values
     * @param $pluginstructure
     * @param $pluginsid
     * @return
     */
    public function item_get_plugin_values($pluginstructure, $pluginsid) {
        $values = parent::item_get_plugin_values($pluginstructure, $pluginsid);

        // just a check before assuming all has been done correctly
        $errindex = array_search('err', $values, TRUE);
        if ($errindex !== FALSE) {
            throw new moodle_exception('$values[\''.$errindex.'\'] of survey_'.$this->plugin.' was not properly managed');
        }

        return $values;
    }

    /**
     * userform_mform_element
     * @param $mform
     * @return
     */
    public function userform_mform_element($mform, $survey, $canaccessadvancedform, $parentitem=null, $searchform=false) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $mform->addElement('text', $fieldname, $elementlabel, array('class' => 'indent-'.$this->indent, 'itemid' => $this->itemid));
        $mform->setType($fieldname, PARAM_RAW); // see: moodlelib.php lines 133+
        if (!$searchform) {
            $decimalseparator = get_string('decsep', 'langconfig');
            $mform->setDefault($fieldname, number_format((double)$this->defaultvalue, $this->decimals, $decimalseparator, ''));
            $canaddrequiredrule = $this->userform_can_add_required_rule($survey, $canaccessadvancedform, $parentitem);
            if ($this->required && $canaddrequiredrule) {
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
            }
        }
    }

    /**
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        $decimalseparator = get_string('decsep', 'langconfig');

        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $canaddrequiredrule = $this->userform_can_add_required_rule($survey, $canaccessadvancedform, $parentitem);
        if ($this->required && (!$canaddrequiredrule)) {
            // CS validaition was not permitted
            // so, here, I need to manually look after the 'required' rule
            if (empty($data[$fieldname])) {
                $errors[$fieldname] = get_string('required');
                return;
            }
        }

        if (!isset($data[$fieldname])) return;

        // if it is not a number, shouts
        $thenumber = unformat_float($data[$fieldname]);
        if (is_null($thenumber)) {
            $errors[$fieldname] = get_string('uerr_notanumber', 'surveyfield_numeric');
        } else {
            // if it is < 0 but has been defined as unsigned, shouts
            if (!$this->signed && ($thenumber < 0)) {
                $errors[$fieldname] = get_string('uerr_negative', 'surveyfield_numeric');
            }
            // if it is < $this->lowerbound, shouts
            if (isset($this->lowerbound) && ($thenumber < $this->lowerbound)) {
                $errors[$fieldname] = get_string('uerr_lowerthanminimum', 'surveyfield_numeric');
            }
            // if it is > $this->upperbound, shouts
            if (isset($this->upperbound) && ($thenumber > $this->upperbound)) {
                $errors[$fieldname] = get_string('greaterthanmaximum', 'surveyfield_numeric', $subject);
            }
            // if it has decimal but has been defined as integer, shouts
            $is_integer = (bool)(strval(intval($thenumber)) == strval($thenumber));
            if (($this->decimals == 0) && (!$is_integer)) {
                $errors[$fieldname] = get_string('uerr_notinteger', 'surveyfield_numeric', $subject);
            }
        }
    }

    /**
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

    /**
     * userform_dispose_unexpected_values
     * this method is responsible for deletion of unexpected $fromform elements
     * @param $fromform
     * @return
     */
    public function userform_dispose_unexpected_values(&$fromform) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $itemname = $fieldname;
        if (isset($fromform->{$itemname})) unset($fromform->{$itemname});
    }

    /**
     * userform_save
     * starting from the info set by the user in the form
     * I define the info to store in the db
     * @param $itemdetail, $olduserdata
     * @return
     */
    public function userform_save($itemdetail, $olduserdata) {
        if (empty($itemdetail['mainelement'])) {
            $olduserdata->content = null;
        } else {
            $decimalseparator = get_string('decsep', 'langconfig');
            $matches = $this->item_atomize_parent_content($itemdetail['mainelement']);
            $decimals = $matches[2];
            if (strlen($matches[2]) > $this->decimals) {
                // round it
                $decimals = round($matches[2], $this->decimals);
            }
            if (strlen($matches[2]) < $this->decimals) {
                // padright
                $decimals = str_pad($matches[2], $this->decimals, '0', STR_PAD_RIGHT);
            }
            $olduserdata->content = $matches[1].$decimalseparator.$decimals;
        }
    }

    /**
     * this method is called from survey_set_prefill (in locallib.php) to set $prefill at user form display time
     * (defaults are set in userform_mform_element)
     *
     * userform_set_prefill
     * @param $olduserdata
     * @return
     */
    public function userform_set_prefill($olduserdata) {
        $prefill = array();
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            if (!empty($olduserdata->content)) {
                $prefill[$fieldname] = $olduserdata->content;
            } else {
                // nothing was set
                // do not accept defaults but override them
            }
        } // else use item defaults

        return $prefill;
    }

    /**
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return false;
    }
}