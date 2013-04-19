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
require_once($CFG->dirroot.'/mod/survey/field/rate/lib.php');

class surveyfield_rate extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_rate record
     */
    public $pluginid = 0;

    /*******************************************************************/

    /*
     * $options = list of options in the form of "$value SURVEY_VALUELABELSEPARATOR $label"
     */
    public $options = '';

    /*
     * $rates = list of allowed rates in the form: "$value SURVEY_VALUELABELSEPARATOR $label"
     */
    public $rates = '';

    /*
     * $defaultoption
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $style = how is this rate item displayed? with radiobutton or with dropdown menu?
     */
    public $style = 0;

    /*
     * $allowsamerate = is the user allowed to provide two equal rates for two different options?
     */
    public $forcedifferentrates = false;

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
        $this->plugin = 'rate';

        $this->flag = new stdclass();
        $this->flag->issearchable = false;
        $this->flag->couldbeparent = false;
        $this->flag->useplugintable = true;

        $this->extrarow = 1; // define the value of the checkbox
        // item_form_requires['extrarow'] = true: show extrarow checkbox
        // item_form_requires['extrarow'] = false: do not show extrarow checkbox
        // item_form_requires['extrarow'] = 'disable': disable the extrarow checkbox
        $this->item_form_requires['extrarow'] = 'disable'; // show the checkbox

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
        $fieldlist = array('content', 'options', 'rates', 'defaultvalue');
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
        // drop empty rows and trim edging rows spaces from each textarea field
        $fieldlist = array('options', 'rates', 'defaultvalue');
        survey_clean_textarea_fields($record, $fieldlist);

        // remember: extrarow is mandatory
        $record->extrarow = 1;

        // forcedifferentrates
        $record->forcedifferentrates = isset($record->forcedifferentrates) ? 1 : 0;

        $this->item_custom_fields_to_db($record);

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
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
        // nothing to do: they don't exist in this plugin

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
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        if ($record->defaultoption != SURVEY_CUSTOMDEFAULT) {
            $record->defaultvalue = null;
        }
    }

    /*
     * item_generate_standard_default
     * sets record field to store the correct value to db for the date custom item
     * @param $record
     * @return
     */
    public function item_generate_standard_default($options=null, $rates=null, $forcedifferentrates=null) {

        if (is_null($options)) {
            $options = $this->options;
        }
        if (is_null($rates)) {
            $rates = $this->rates;
        }
        if (is_null($forcedifferentrates)) {
            $forcedifferentrates = $this->forcedifferentrates;
        }

        if ($optionscount = count(survey_textarea_to_array($options))) {
            $ratesarray = survey_textarea_to_array($rates);
            if ($forcedifferentrates) {
                $default = array();
                foreach ($ratesarray as $k => $singlerate) {
                    if (strpos($singlerate, SURVEY_VALUELABELSEPARATOR) === false) {
                        $defaultrate = $singlerate;
                    } else {
                        $pair = explode(SURVEY_VALUELABELSEPARATOR, $singlerate);
                        $defaultrate = $pair[0];
                    }
                    $default[] = $defaultrate;
                    if (count($default) == $optionscount) {
                        break;
                    }
                }
            } else {
                $firstrate = reset($ratesarray);

                if (strpos($firstrate, SURVEY_VALUELABELSEPARATOR) === false) {
                    $defaultrate = $firstrate;
                } else {
                    $pair = explode(SURVEY_VALUELABELSEPARATOR, $firstrate);
                    $defaultrate = $pair[0];
                }

                $default = array_fill(1, $optionscount, $defaultrate);
            }
            return implode("\n", $default);
        }
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
        // $this->flag->couldbeparent = false
        // this method is never called
    }

    /*
     * item_get_hard_info
     * @param
     * @return
     */
    public function item_get_hard_info() {

        if (!empty($this->forcedifferentrates)) {
            $hardinfo = get_string('diffratesrequired', 'surveyfield_rate');
        } else {
            $hardinfo = '';
        }

        return $hardinfo;
    }

    /*
     * item_list_constraints
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        return 'item_list_constraints method is still under construction for '.$this->plugin;
    }

    /*
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        return 'item_parent_validate_child_constraints needs refinements in plugin: '.$this->plugin;
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
        // here I manage style once again because they were not written using constants

        // override: $value['style']
        /*------------------------------------------------*/
        switch ($this->style) {
            case SURVEYFIELD_RATE_USERADIO:
                $values['style'] = 'SURVEYFIELD_RATE_USERADIO';
                break;
            case SURVEYFIELD_RATE_USESELECT:
                $values['style'] = 'SURVEYFIELD_RATE_USESELECT';
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->style = '.$this->style);
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
        // this plugin has $this->flag->issearchable = false; so it will never be part of a search form

        $options = $this->item_get_one_word_per_row('options');
        $valuelabel = $this->item_get_value_label_array('rates');
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                $valuelabel += array(SURVEY_INVITATIONVALUE => get_string('choosedots'));
            } else {
                $valuelabel = array(SURVEY_INVITATIONVALUE => get_string('choosedots')) + $valuelabel;
            }
        }
        $defaultvalue = $this->item_get_one_word_per_row('defaultvalue');

        $optionindex = 0;
        $couldbedisabled = $this->userform_could_be_disabled($survey, $canaccessadvancedform, $parentitem);
        if ($this->style == SURVEYFIELD_RATE_USERADIO) {
            foreach ($options as $option) {
                $uniquename = $this->itemname.'_'.$optionindex;
                $elementgroup = array();
                foreach ($valuelabel as $value => $label) {
                    $elementgroup[] = $mform->createElement('radio', $uniquename, '', $label, $value);
                }
                $mform->addGroup($elementgroup, $uniquename.'_group', $option, ' ', false);
                if ($this->required && (!$searchform) && (!$couldbedisabled)) {
                    // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                    // -> I do not want JS form validation if the page is submitted trough the "previous" button
                    // -> I do not want JS field validation even if this item is required AND disabled too. THIS IS A MOODLE BUG. See: MDL-34815
                    // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields

                    // $mform->addRule($uniquename.'_group', get_string('required'), 'required', null, 'client');
                    // $mform->addRule($this->itemname.'_group', get_string('required'), 'nonempty_rule', $mform);
                    $mform->_required[] = $uniquename.'_group';
                }

                if (!$searchform) {
                    switch ($this->defaultoption) {
                        case SURVEY_CUSTOMDEFAULT:
                            $mform->setDefault($uniquename, $defaultvalue[$optionindex]);
                            break;
                        case SURVEY_INVITATIONDEFAULT:
                            $mform->setDefault($uniquename, SURVEY_INVITATIONVALUE);
                            break;
                        case SURVEY_NOANSWERDEFAULT:
                            $mform->setDefault($uniquename, SURVEY_NOANSWERVALUE);
                            break;
                        default:
                            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
                    }
                } else {
                    $mform->setDefault($this->itemname, SURVEY_NOANSWERVALUE); // free
                }

                $optionindex++;
            }
        } else { // SURVEYFIELD_RATE_USESELECT
            foreach ($options as $option) {
                $uniquename = $this->itemname.'_'.$optionindex;
                $mform->addElement('select', $uniquename, $option, $valuelabel, array('class' => 'indent-'.$this->indent));
                if ($this->required && (!$searchform) && (!$couldbedisabled)) {
                    // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                    // -> I do not want JS form validation if the page is submitted trough the "previous" button
                    // -> I do not want JS field validation even if this item is required AND disabled too. THIS IS A MOODLE BUG. See: MDL-34815
                    // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields

                    // $mform->addRule($uniquename, get_string('required'), 'required', null, 'client');
                    // $mform->addRule($this->itemname, get_string('required'), 'nonempty_rule', $mform);
                    $mform->_required[] = $this->itemname; // add the star for mandatory fields at the end of the page with server side validation too
                }

                if (!$searchform) {
                    switch ($this->defaultoption) {
                        case SURVEY_CUSTOMDEFAULT:
                            $mform->setDefault($uniquename, $defaultvalue[$optionindex]);
                            break;
                        case SURVEY_INVITATIONDEFAULT:
                            $mform->setDefault($uniquename, SURVEY_INVITATIONVALUE);
                            break;
                        case SURVEY_NOANSWERDEFAULT:
                            $mform->setDefault($uniquename, SURVEY_NOANSWERVALUE);
                            break;
                        default:
                            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
                    }
                } else {
                    $mform->setDefault($this->itemname, SURVEY_NOANSWERVALUE); // free
                }

                $optionindex++;
            }
        }

        if (!$this->required) {
            $mform->addElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'), array('class' => 'indent-'.$this->indent));
            $optionindex = 0;
            foreach ($options as $option) {
                if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                    $uniquename = $this->itemname.'_'.$optionindex.'_group';
                } else {
                    $uniquename = $this->itemname.'_'.$optionindex;
                }

                $mform->disabledIf($uniquename, $this->itemname.'_noanswer', 'checked');
                $optionindex++;
            }
            if ($this->defaultoption == SURVEY_NOANSWERDEFAULT) {
                $mform->setDefault($this->itemname.'_noanswer', 1);
            }
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        // this plugin displays as a set of dropdown menu or radio buttons. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        // if different rates were requested, it is time to verify this
        $options = $this->item_get_one_word_per_row('options');

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // nothing to validate
        }

        $optionindex = 0;
        $return = false;
        foreach ($options as $option) {
            $uniquename = $this->itemname.'_'.$optionindex;
            if ($data[$uniquename] == SURVEY_INVITATIONVALUE) {
                if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                    $elementname = $uniquename.'_group';
                } else {
                    $elementname = $uniquename;
                }
                $errors[$elementname] = get_string('uerr_optionnotset', 'surveyfield_rate');
                $return = true;
            }
            $optionindex++;
        }
        if ($return) {
            return;
        }

        if (!empty($this->forcedifferentrates)) {
            $optionscount = count($this->item_get_value_label_array('options'));
            $rates = array();
            for ( $i = 0; $i < $optionscount; $i++) {
                $rates[] = $data[$this->itemname.'_'.$i];
            }

            $uniquerates = array_unique($rates);
            $duplicaterates = array_diff_assoc($rates, $uniquerates);

            foreach ($duplicaterates as $k => $v) {
                if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                    $elementname = $this->itemname.'_'.$k.'_group';
                } else {
                    $elementname = $this->itemname.'_'.$k;
                }
                $errors[$elementname] = get_string('uerr_duplicaterate', 'surveyfield_rate');
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
        // $this->flag->couldbeparent = false
        // this method is never called
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
        } else {
            $valuelabel = $this->item_get_value_label_array('options');
            // var_dump($valuelabel);
            $optioncount = count($valuelabel);
            $i = 0;
            $return = array();
            foreach ($valuelabel as $value) {
                if (isset($itemdetail["$i"])) {
                    $return[] = $value.SURVEYFIELD_RATE_VALUERATE_SEPARATOR.$itemdetail["$i"];
                }
                $i++;
            }

            // $return can be empty but I do not care of it
            $olduserdata->content = implode(', ', $return);
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
        // [survey_field_rate_157_0] => italian: 3
        // [survey_field_rate_157_1] => english: 2
        // [survey_field_rate_157_2] => french: 1
        // [survey_field_rate_157_noanswer] => 0

        $prefill = array();

        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            if (!empty($olduserdata->content)) {
                if ($olduserdata->content == SURVEY_NOANSWERVALUE) {
                    $prefill[$this->itemname.'_noanswer'] = 1;
                } else {
                    $valueindex = 0;
                    $values = explode(',', $olduserdata->content);
                    foreach ($values as $rowvalue) {
                        $uniquename = $this->itemname.'_'.$valueindex;
                        $rowvalue = trim($rowvalue); // Example: italiano: 3
                        $value = explode(SURVEYFIELD_RATE_VALUERATE_SEPARATOR, $rowvalue); // 3
                        $prefill[$uniquename] = $value[1];
                        $valueindex++;
                    }
                }
            // } else {
                // nothing was set
                // do not accept defaults but overwrite them
            }

            // _noanswer
            if (!$this->required) { // if this item foresaw the $this->itemname.'_noanswer'
                $prefill[$this->itemname.'_noanswer'] = is_null($olduserdata->content) ? 1 : 0;
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