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
require_once($CFG->dirroot.'/mod/survey/field/boolean/lib.php');

class surveyfield_boolean extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_boolean record
     */
    public $pluginid = 0;

    /*******************************************************************/

    /*
     * $defaultoption
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;

    /*
     * $options = style for the rate: radiobuttons or select menu
     */
    public $style = 0;

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
        $this->plugin = 'boolean';

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
        $this->item_builtin_string_load_support();

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

        // Do parent item saving stuff here (field_base::save($record)))
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
     * item_parent_content_format_validation
     * checks whether the user input format in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_format_validation($parentcontent) {
        // '[0] or [1]';
        if (($parentcontent != 0) && ($parentcontent != 1)) {
            $format = get_string('parentformat', 'surveyfield_boolean');
            return (get_string('invalidformat_err', 'survey', $format));
        }
    }

    /*
     * item_parent_content_content_validation
     * checks whether the user input content in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_content_validation($parentcontent) {
        // '[0] or [1]';
        if (($parentcontent != '0') && ($parentcontent != '1')) {
            throw new moodle_exception('Unexpected invalid format for boolean item: id: '.$this->itemid.', type '.$this->type.', plugin: '.$this->plugin);
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
        return $parentcontent;
    }

    /*
     * item_list_constraints
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        return '';
    }

    /*
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        return true;
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
        //                    'defaultoption', 'defaultvalue', 'style');
        // 'id', 'surveyid', 'itemid' were managed by parent class
        // here I manage style once again because they were not written using constants

        // override: $value['style']
        /*------------------------------------------------*/
        switch ($this->style) {
            case SURVEYFIELD_BOOLEAN_USESELECT:
                $values['style'] = 'SURVEYFIELD_BOOLEAN_USESELECT';
                break;
            case SURVEYFIELD_BOOLEAN_USERADIOV:
                $values['style'] = 'SURVEYFIELD_BOOLEAN_USERADIOV';
                break;
            case SURVEYFIELD_BOOLEAN_USERADIOH:
                $values['style'] = 'SURVEYFIELD_BOOLEAN_USERADIOH';
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
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        if ($this->style == SURVEYFIELD_BOOLEAN_USESELECT) {
            $options = array();
            if ( ($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform) ) {
                $options[SURVEY_INVITATIONVALUE] = get_string('choosedots');
            }
            $options += array('1' => get_string('yes'), '0' => get_string('no'));

            if ( (!$this->required) || $searchform ) {
                $check_label = ($searchform) ? get_string('star', 'survey') : get_string('noanswer', 'survey');
                $options += array(SURVEY_NOANSWERVALUE => $check_label);
            }
            $mform->addElement('select', $fieldname, $elementlabel, $options, array('class' => 'indent-'.$this->indent));
            if ($this->required && (!$searchform)) {
                // $mform->addRule($fieldname.'_group', get_string('required'), 'required', null, 'client');
                $mform->addRule($fieldname, get_string('required'), 'nonempty_rule', $mform);
                $mform->_required[] = $fieldname;
            }
        } else { // SURVEYFIELD_BOOLEAN_USERADIOV or SURVEYFIELD_BOOLEAN_USERADIOH
            $class = '';
            $separator = ($this->style == SURVEYFIELD_BOOLEAN_USERADIOV) ? '<br />' : ' ';
            $elementgroup = array();

            if ( ($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform) ) {
                $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('choosedots'), SURVEY_INVITATIONVALUE, array('class' => 'indent-'.$this->indent));
                $class = ($this->style == SURVEYFIELD_BOOLEAN_USERADIOV) ? array('class' => 'indent-'.$this->indent) : '';
            } else {
                $class = array('class' => 'indent-'.$this->indent);
            }
            $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('yes'), '1', $class);
            $class = ($this->style == SURVEYFIELD_BOOLEAN_USERADIOV) ? array('class' => 'indent-'.$this->indent) : '';
            $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('no'), '0', $class);

            if ( $this->required && (!$searchform) ) {
                $mform->addGroup($elementgroup, $fieldname.'_group', $elementlabel, $separator, false);

                // $mform->addRule($fieldname.'_group', get_string('required'), 'required', null, 'client');
                $mform->addRule($fieldname.'_group', get_string('required'), 'nonempty_rule', $mform);
                $mform->_required[] = $fieldname.'_group';
            } else {
                $check_label = ($searchform) ? get_string('star', 'survey') : get_string('noanswer', 'survey');
                $elementgroup[] = $mform->createElement('radio', $fieldname, '', $check_label, SURVEY_NOANSWERVALUE, $class);

                $mform->addGroup($elementgroup, $fieldname.'_group', $elementlabel, $separator, false);
            }
        }

        // default section
        if (!$searchform) {
            if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                $mform->setDefault($fieldname, SURVEY_INVITATIONVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEY_CUSTOMDEFAULT:
                        $mform->setDefault($fieldname, $this->defaultvalue);
                        break;
                    case SURVEY_NOANSWERDEFAULT:
                        $mform->setDefault($fieldname, SURVEY_NOANSWERVALUE);
                        break;
                }
            }
        } else {
            $mform->setDefault($fieldname, SURVEY_NOANSWERVALUE); // free
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        // I need to check value is different from SURVEY_INVITATIONVALUE even if it is not required
        if ($data[$fieldname] == SURVEY_INVITATIONVALUE) {
            $errors[$fieldname] = get_string('uerr_booleannotset', 'surveyfield_boolean');
            return;
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
        $olduserdata->content = $itemdetail['mainelement'];
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

        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            $prefill[$fieldname] = $olduserdata->content;
        } // else use item defaults

        return $prefill;
    }

    /*
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     * @param $richsubmission
     * @return
     */
    public function userform_db_to_export($itemvalue) {
        $content = $itemvalue->content;
        $return = ($content) ? 'true' : 'false';
        return $return;
    }

    /*
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return ($this->style != SURVEYFIELD_BOOLEAN_USESELECT);
    }
}