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
        $this->plugin = 'boolean';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->couldbeparent = true;
        $this->flag->useplugintable = true;

        $this->item_form_requires['hideinstructions'] = false;

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
        // Do parent item loading stuff here (surveyitem_base::item_load($itemid)))
        parent::item_load($itemid);

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

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

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
     * item_get_plugin_values
     *
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
     * item_get_downloadformats
     *
     * @param
     * @return
     */
    public function item_get_downloadformats() {
        $option = array();
        $option['strfbool1'] = get_string('strfbool1', 'surveyfield_boolean'); // yes/no
        $option['strfbool2'] = get_string('strfbool2', 'surveyfield_boolean'); // y/n
        $option['strfbool3'] = get_string('strfbool3', 'surveyfield_boolean'); // up/down
        $option['strfbool4'] = get_string('strfbool4', 'surveyfield_boolean'); // true/false
        $option['strfbool5'] = get_string('strfbool5', 'surveyfield_boolean'); // 0/1
        $option['strfbool6'] = get_string('strfbool6', 'surveyfield_boolean'); // +/-

        return $option;
    }

    // MARK parent

    /*
     * parent_validate_child_constraints
     *
     * @param
     * @return status of child relation
     */
    public function parent_validate_child_constraints($childvalue) {
        $status = (($childvalue == 0) || ($childvalue == 1));

        return $status;
    }

    /*
     * parent_encode_content_to_value
     * This method is used by items handled as parent
     * starting from the user input, this method stores to the db the value as it is stored during survey submission
     * this method manages the $parentcontent of its child item, not its own $parentcontent
     * (take care: here we are not submitting a survey but we are submitting an item)
     *
     * @param $parentcontent
     * @return
     */
    public function parent_encode_content_to_value($parentcontent) {
        return $parentcontent;
    }

    // MARK userform

    /*
     * userform_mform_element
     *
     * @param $mform
     * @param $survey
     * @param $canaccesslimiteditems
     * @param $parentitem
     * @param $searchform
     * @return
     */
    public function userform_mform_element($mform, $survey, $canaccesslimiteditems, $parentitem=null, $searchform=false) {
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $labels = explode('/', get_string($this->downloadformat, 'surveyfield_boolean'));
        $yes_label = $labels[0];
        $no_label = $labels[1];

        if ($this->style == SURVEYFIELD_BOOLEAN_USESELECT) {
            $options = array();
            if ( ($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform) ) {
                $options[SURVEY_INVITATIONVALUE] = get_string('choosedots');
            }
            $options += array('1' => $yes_label, '0' => $no_label);

            if ( (!$this->required) || $searchform ) {
                $check_label = ($searchform) ? get_string('star', 'survey') : get_string('noanswer', 'survey');
                $options += array(SURVEY_NOANSWERVALUE => $check_label);
            }
            $mform->addElement('select', $this->itemname, $elementlabel, $options, array('class' => 'indent-'.$this->indent));
        } else { // SURVEYFIELD_BOOLEAN_USERADIOV or SURVEYFIELD_BOOLEAN_USERADIOH
            $class = '';
            $separator = ($this->style == SURVEYFIELD_BOOLEAN_USERADIOV) ? '<br />' : ' ';
            $elementgroup = array();

            if ( ($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform) ) {
                $elementgroup[] = $mform->createElement('radio', $this->itemname, '', get_string('choosedots'), SURVEY_INVITATIONVALUE, array('class' => 'indent-'.$this->indent));
                $class = ($this->style == SURVEYFIELD_BOOLEAN_USERADIOV) ? array('class' => 'indent-'.$this->indent) : '';
            } else {
                $class = array('class' => 'indent-'.$this->indent);
            }
            $elementgroup[] = $mform->createElement('radio', $this->itemname, '', $yes_label, '1', $class);
            $class = ($this->style == SURVEYFIELD_BOOLEAN_USERADIOV) ? array('class' => 'indent-'.$this->indent) : '';
            $elementgroup[] = $mform->createElement('radio', $this->itemname, '', $no_label, '0', $class);

            if (!$searchform) {
                if (!$this->required) {
                    $elementgroup[] = $mform->createElement('radio', $this->itemname, '', get_string('noanswer', 'survey'), SURVEY_NOANSWERVALUE, $class);
                }
            } else {
                $elementgroup[] = $mform->createElement('radio', $this->itemname, '', get_string('star', 'survey'), SURVEY_NOANSWERVALUE, $class);
            }
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
        }

        // default section
        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. THIS IS A MOODLE ISSUE. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                if ($this->extrarow) {
                    $starplace = $this->itemname.'_extrarow';
                } else {
                    $starplace = $this->itemname;
                }
                $mform->_required[] = $starplace; // add the star for mandatory fields at the end of the page with server side validation too
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
            $mform->setDefault($this->itemname, SURVEY_NOANSWERVALUE); // free
        }
    }

    /*
     * userform_mform_validation
     *
     * @param $data, &$errors
     * @param $survey
     * @param $canaccesslimiteditems
     * @param $parentitem
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccesslimiteditems, $parentitem=null) {
        // this plugin displays as dropdown menu or a radio buttons set. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if ($this->extrarow) {
            $errorkey = $this->itemname.'_extrarow';
        } else {
            if ($this->userform_mform_element_is_group()) {
                $errorkey = $this->itemname.'_group';
            } else {
                $errorkey = $this->itemname;
            }
        }

        // I need to check value is different from SURVEY_INVITATIONVALUE even if it is not required
        if ($data[$this->itemname] == SURVEY_INVITATIONVALUE) {
            $errors[$errorkey] = get_string('uerr_booleannotset', 'surveyfield_boolean');
            return;
        }
    }

    /*
     * userform_get_parent_disabilitation_info
     * from child_parentvalue defines syntax for disabledIf
     *
     * @param: $child_parentvalue
     * @return
     */
    public function userform_get_parent_disabilitation_info($child_parentvalue) {
        $disabilitationinfo = array();

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $this->itemname;
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $child_parentvalue;
        $disabilitationinfo[] = $mformelementinfo;

        return $disabilitationinfo;
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
        if (isset($answer['noanswer'])) {
            $olduserdata->content = null;
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

        if ($fromdb) { // $fromdb may be boolean false for not existing data
            $prefill[$this->itemname] = $fromdb->content;
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
        $content = $answer->content;
        if (strlen($content) == 0) {
            return get_string('answerisnoanswer', 'survey');
        }

        if (empty($format)) {
            $format = $this->downloadformat;
        }

        $answers = explode('/', get_string($format, 'surveyfield_boolean'));
        $return = ($content) ? $answers[0] : $answers[1];

        return $return;
    }

    /*
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     *
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return ($this->style != SURVEYFIELD_BOOLEAN_USESELECT);
    }
}