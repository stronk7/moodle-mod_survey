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
require_once($CFG->dirroot.'/mod/survey/field/checkbox/lib.php');

class surveyfield_checkbox extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_checkbox record
     */
    public $pluginid = 0;

    /*******************************************************************/

    /*
     * $options = list of options in the form of "$value SURVEY_VALUELABELSEPARATOR $label"
     */
    public $options = '';

    /*
     * $options_sid
     */
    public $options_sid = null;

    /*
     * $labelother = the text label for the optional option "other" in the form of "$value SURVEY_OTHERSEPARATOR $label"
     */
    public $labelother = '';

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $adjustment = the orientation of the list of options.
     */
    public $adjustment = 0;

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
        $this->plugin = 'checkbox';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->couldbeparent = true;
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
        // //////////////////////////////////
        // Now execute very specific plugin level actions
        // //////////////////////////////////
        // multilang load support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $fieldlist = array('content', 'options', 'labelother', 'defaultvalue');
        $this->item_builtin_string_load_support($fieldlist);

        // Do parent item loading stuff here (surveyitem_base::item_load($itemid)))
        parent::item_load($itemid);
    }

    /*
     * item_save
     * @param $record
     * @return
     */
    public function item_save($record) {
        // //////////////////////////////////
        // Now execute very specific survey_numeric level actions
        // //////////////////////////////////

        // drop empty rows and trim trailing spaces from each textarea field
        $fieldlist = array('options', 'defaultvalue');
        survey_clean_textarea_fields($record, $fieldlist);

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record, $fieldlist);

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
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
        $arraycontent = survey_textarea_to_array($parentcontent);
        $parentcontent = implode("\n", $arraycontent);

        return $parentcontent;
    }

    /*
     * item_list_constraints
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        $constraints = array();

        $valuelabel = $this->item_get_value_label_array('options');
        $optionstr = get_string('option', 'surveyfield_checkbox');
        foreach ($valuelabel as $value => $label) {
            $constraints[] = $optionstr.': '.$value;
        }
        if (!empty($this->labelother)) {
            $constraints[] = get_string('labelother', 'surveyfield_checkbox').': '.get_string('allowed', 'surveyfield_checkbox');
        }

        return implode($constraints, '<br />');
    }

    /*
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        $childvalue = survey_textarea_to_array($childvalue);

        $valuelabel = $this->item_get_value_label_array('options');
        $valuelabelkeys = array_keys($valuelabel);

        $errcount = 0;
        foreach ($childvalue as $value) {
            if (array_search($value, $valuelabelkeys) === false) {
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
                break;
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
                break;
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

        $valuelabel = $this->item_get_value_label_array('options');
        $defaults = $this->item_get_one_word_per_row('defaultvalue');

        $elementgroup = array();
        $i = 0;
        $class = '';
        foreach ($valuelabel as $value => $label) {
            $uniqueid = $fieldname.'_'.$i;
            $class = ( ($this->adjustment == SURVEY_VERTICAL) || (!$class) ) ? array('class' => 'indent-'.$this->indent) : '';
            $elementgroup[] = $mform->createElement('checkbox', $uniqueid, '', $label, $class);

            if (!$searchform) {
                if (in_array($value, $defaults)) {
                    $mform->setDefault($uniqueid, '1');
                }
            }
            $i++;
        }
        if (!empty($this->labelother)) {
            list($othervalue, $otherlabel) = $this->item_get_other();

            $class = ($this->adjustment == SURVEY_VERTICAL) ? array('class' => 'indent-'.$this->indent) : '';
            $elementgroup[] = $mform->createElement('checkbox', $fieldname.'_other', '', $otherlabel, $class);
            $elementgroup[] = $mform->createElement('text', $fieldname.'_text', '');

            if (!$searchform) {
                $mform->setDefault($fieldname.'_text', $othervalue);
                if (($othervalue) && in_array($othervalue, $defaults)) {
                    $mform->setDefault($fieldname.'_other', '1');
                }
            }
            $mform->disabledIf($fieldname.'_text', $fieldname.'_other', 'notchecked');
        }

        if ($this->adjustment == SURVEY_VERTICAL) {
            $separator = array_fill(0, count($valuelabel), '<br />');
            $separator[] = ' ';
        } else { // SURVEY_HORIZONTAL
            $separator = ' ';
        }
        $mform->addGroup($elementgroup, $fieldname.'_group', $elementlabel, $separator, false);

        $maybedisabled = $this->userform_can_be_disabled($survey, $canaccessadvancedform, $parentitem);
        if ($this->required && (!$searchform) && (!$maybedisabled)) {
            // $mform->addRule($fieldname.'_group', get_string('required'), 'required', null, 'client');
            $mform->addRule($fieldname.'_group', get_string('required'), 'nonempty_rule', $mform);
            $mform->_required[] = $fieldname.'_group';
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        if ($this->required) {
            // $mform->addRule validaition was not permitted
            // so, here, I need to manually look after the 'required' rule
            $valuelabel = $this->item_get_value_label_array('options');

            $missinganswer = true;
            foreach ($valuelabel as $value => $label) {
                $uniqueid = $fieldname.'_'.$i;

                if (!empty($data[$uniqueid])) {
                    $missinganswer = false;
                    break;
                }
                $i++;
            }

            if (!empty($this->labelother)) {
                if ((!empty($data[$fieldname.'_other'])) && (!empty($data[$fieldname.'_text']))) {
                    $missinganswer = false;
                }
            }

            if ($missinganswer) {
                $errors[$fieldname] = get_string('required');
                return;
            }
        }
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

        // I need to know the names of mfrom element corresponding to the content of $child_parentcontent
        $valuelabel = $this->item_get_value_label_array('options');
        $valuelabel = array_keys($valuelabel);

        $constraintsvalues = survey_textarea_to_array($child_parentcontent);

        foreach ($valuelabel as $index => $value) { // index and value because I issued: array_keys
            $mformelementinfo = new stdClass();

            $mformelementinfo->parentname = $fieldname.'_'.$index;
            $mformelementinfo->operator = 'eq';
            $constrainindex = array_search($value, $constraintsvalues);
            if ($constrainindex === false) {
                $mformelementinfo->content = '1';
            } else {
                unset($constraintsvalues[$constrainindex]);
                $mformelementinfo->content = '0';
            }
            $disabilitationinfo[] = $mformelementinfo;
        }

        // if among $constraintsvalues ​​there is one that is not among $valueLabel
        if (count($constraintsvalues)) {
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $fieldname.'_other';
            $mformelementinfo->operator = 'eq';
            $mformelementinfo->content = '0';
            $disabilitationinfo[] = $mformelementinfo;

            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $fieldname.'_text';
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = reset($constraintsvalues);
            $disabilitationinfo[] = $mformelementinfo;
        }

        return $disabilitationinfo;
    }

    /*
     * userform_child_is_allowed_dynamic
     * from parentcontent defines whether an item is supposed to be active (not disabled) in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called when $survey->newpageforchild == false
     * that is the current survey lives in just one single web page
     * ----------------------------------------------------------------------
     * Am I getting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_is_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use survey_child_is_allowed_static
     * ----------------------------------------------------------------------
     * @param: $parentcontent, $parentsubmitted
     * @return
     */
    public function userform_child_is_allowed_dynamic($child_parentcontent, $data) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        // devo sapere come si chiamano gli mfrom element che corrispondono al contenuto di $child_parentcontent
        $valuelabel = $this->item_get_value_label_array('options');
        $valuelabel = array_keys($valuelabel);

        $constraintsvalues = survey_textarea_to_array($child_parentcontent);

        $status = true;
        foreach ($constraintsvalues as $constraintsvalue) {
            if ($index = array_search($constraintsvalue, $valuelabel)) {
                $status = $status && ($data[$fieldname.'_'.$index] == 1);
            } else {
                // $constraintsvalue has not been found
                // it is the other value
                $status = $status && ($data[$fieldname.'_other'] == 1);
                $status = $status && ($data[$fieldname.'_text'] == $constraintsvalue);
            }
        }

        return $status;
    }

    /*
     * userform_save
     * starting from the info set by the user in the form
     * I define the info to store in the db
     * @param $itemdetail, $olduserdata
     * @return
     */
    public function userform_save($itemdetail, $olduserdata) {
        $i = 0;
        $return = array();
        $options = $this->item_complete_option_array();
        foreach ($options as $value => $label) {
            if (isset($itemdetail["$i"])) {
                $return[] = $value;
            }
            $i++;
        }
        if (isset($itemdetail['other'])) {
            $return[] = $itemdetail['text'];
        }

        if (empty($return)) {
            // $return[] = get_string('missinganswer', 'survey');
            $olduserdata->content = null;
        } else {
            $olduserdata->content = implode(SURVEYFIELD_CHECKBOX_VALUESEPARATOR, $return);
        }
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

        // parto da un elenco separato da virgole
        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
            $valuelabel = array_keys($this->item_get_value_label_array('options'));
            if (!empty($olduserdata->content)) { // I did not unselect each checkbox
                // something was set
                $answers = explode(SURVEYFIELD_CHECKBOX_VALUESEPARATOR, $olduserdata->content);
                foreach ($answers as $answer) {
                    $checkboxindex = array_search($answer, $valuelabel);
                    if ($checkboxindex !== false) {
                        $uniqueid = $fieldname.'_'.$checkboxindex;
                        $prefill[$uniqueid] = 1;
                    } else {
                        $prefill[$fieldname.'_other'] = 1;
                        $prefill[$fieldname.'_text'] = $answer;
                    }
                }
            } else {
                // nothing was set
                // do not accept defaults but overwrite them
                foreach ($valuelabel as $checkboxindex => $label) {
                    $uniqueid = $fieldname.'_'.$checkboxindex;
                    $prefill[$uniqueid] = 0;
                }
                if ($this->labelother) {
                    $prefill[$fieldname.'_other'] = 0;
                    $prefill[$fieldname.'_text'] = '';
                }
            }
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
        return true;
    }
}