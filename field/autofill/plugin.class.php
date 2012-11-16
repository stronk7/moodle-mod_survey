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
require_once($CFG->dirroot.'/mod/survey/field/autofill/lib.php');

class surveyfield_autofill extends surveyitem_base {

    /**
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_autofill record
     */
    public $pluginid = 0;

    /********************************************************************/

    /*
     * $element_1 = is the static text visible in the mform?
     */
    public $showfield = false;

    /*
     * $element_1 = element for $content
     */
    public $element_1 = '';

    /*
     * $element_2 = element for $content
     */
    public $element_2 = '';

    /*
     * $element_3 = element for $content
     */
    public $element_3 = '';

    /*
     * $element_4 = element for $content
     */
    public $element_4 = '';

    /*
     * $element_5 = element for $content
     */
    public $element_5 = '';

    /*
     * $content = the content of the message
     */
    public $content = '';

    /*
     * $contentformat = the message format
     */
    public $contentformat = FORMAT_HTML;

    /**
     * $flag = features describing the object
     */
    public $flag;

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
        $this->plugin = 'autofill';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->ismatchable = false;
        $this->flag->useplugintable = true;

        // list of fields I do not want to have in the item definition form
        $this->item_form_requires['indent'] = false;
        $this->item_form_requires['required'] = false;

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

        // multilang load support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_load_support();

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

        // showfield
        $record->showfield = (isset($record->showfield)) ? 1 : 0;

        // Do parent item saving stuff here (surveyitem_base::save($record)))
        return parent::item_save($record);
    }

    /**
     * item_custom_fields_to_form
     * translates the class properties to form fields value
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

        // 4. special management for autofill contents
        $referencearray = array(''); // <-- take care, the first element is already on board
        for ($i = 1; $i <= SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT; $i++) {
            $referencearray[] = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        }

        $items = array();
        for ($i = 1; $i < 6; $i++) {
            $fieldname = 'element_'.$i.'_select';
            if (in_array($this->{'element_'.$i}, $referencearray)) {
                $this->{$fieldname} = $this->{'element_'.$i};
            } else {
                $constantname = 'SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                $this->{$fieldname} = constant($constantname);
                $fieldname = 'element_'.$i.'_text';
                $this->{$fieldname} = $this->{'element_'.$i};
            }
        }
    }

    /**
     * item_custom_fields_to_db
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {

        // this item can't be required or not required
        $record->required = null;
        // unset($record->required);

        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care

        // 4. special management for autofill contents
        for ($i = 1; $i < 6; $i++) {
            if (!empty($record->{'element_'.$i.'_select'})) {
                $constantname = 'SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                // intanto aggiorna le variabili
                // per i campi della tabella survey_autofill
                if ($record->{'element_'.$i.'_select'} == constant($constantname)) {
                    $record->{'element_'.$i} = $record->{'element_'.$i.'_text'};
                } else {
                    $record->{'element_'.$i} = $record->{'element_'.$i.'_select'};
                }
            }
        }
    }

    /**
     * item_parent_content_format_validation
     * check whether the user input in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_format_validation($parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * item_parent_content_content_validation
     * checks whether the user input content in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_content_validation($parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
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
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * item_list_constraints
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        return '';
    }

    /**
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        return '<span class="warningmessage">'.get_string('cannotcheck', 'surveyformat_label').'</span>';
    }

    /**
     * item_get_plugin_values
     * @param $pluginstructure
     * @param $pluginsid
     * @return
     */
    public function item_get_plugin_values($pluginstructure, $pluginsid) {
        $values = parent::item_get_plugin_values($pluginstructure, $pluginsid);

        // STEP 02: make corrections
        // $si_fields = array('id', 'surveyid', 'itemid',
        //                    'showfield', 'element_1', 'element_2',
        //                    'element_3', 'element_4', 'element_5');
        // 'id', 'surveyid', 'itemid' were managed by parent class
        // here I manage element_x once again because they were not written using constants

        // override: $value['element_x']
        /*------------------------------------------------*/
        for ($i = 1; $i <= 5; $i++) {
            $fieldname = 'element_'.$i;
            $values[$fieldname] = 'err';

            if (empty($this->{$fieldname})) {
                $values[$fieldname] = '\'\'';
            } else {
                for ($j = 1; $j <= SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT; $j++) {
                    $refindex = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $j));
                    if ($this->{$fieldname} == $refindex) {
                        $values[$fieldname] = 'SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i);
                        break;
                    }
                }
                if ($values[$fieldname] === 'err') {
                    $values[$fieldname] === '\''.$this->{$fieldname}.'\'';
                }
            }
        }

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

        if (!$searchform) {
            $referencearray = array(''); // <-- take care, the first element is already on board
            for ($i = 1; $i <= SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT; $i++) {
                $referencearray[] = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
            }

            $label = $this->survey_autofill_mform_label();
            if ($this->showfield) {
                // class doesn't work for this mform element
                // $mform->addElement('static', 'dummyfieldname', $elementlabel, $label, array('class' => 'indent-'.$this->indent));
                $mform->addElement('static', $fieldname.'_static', $elementlabel, $label);
            }
            $mform->addElement('hidden', $fieldname, $label);
        } else {
            // $mform->addElement('text', $fieldname, $elementlabel, array('class' => 'indent-'.$this->indent));
            $mform->addElement('text', $fieldname, $elementlabel);
        }
    }

    /**
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        $canaddrequiredrule = $this->userform_can_add_required_rule($survey, $canaccessadvancedform, $parentitem);
        if ($this->required && (!$canaddrequiredrule)) {
            // CS validaition was not permitted
            // so, here, I need to manually look after the 'required' rule
            // nothing to do here
        }
    }

    /**
     * userform_get_parent_disabilitation_info
     * from child_parentcontent defines syntax for disabledIf
     * @param: $child_parentcontent
     * @return
     */
    public function userform_get_parent_disabilitation_info($child_parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * userform_child_is_allowed_dynamic
     * from parentcontent defines whether an item is supposed to be active (not disabled) in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called when $survey->newpageforchild == false
     * that is the current survey lives in just one single web page
     * ----------------------------------------------------------------------
     * Am I geting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_is_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use survey_child_is_allowed_static
     * ----------------------------------------------------------------------
     * @param: $parentcontent, $parentsubmitted
     * @return
     */
    public function userform_child_is_allowed_dynamic($child_parentcontent, $data) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * userform_dispose_unexpected_values
     * this method is responsible for deletion of unexpected $fromform elements
     * @param $fromform
     * @return
     */
    public function userform_dispose_unexpected_values(&$fromform) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * userform_save
     * starting from the info set by the user in the form
     * I define the info to store in the db
     * @param $itemdetail, $olduserdata
     * @return
     */
    public function userform_save($itemdetail, $olduserdata) {
        global $USER, $COURSE, $survey;

        $olduserdata->content = '';
        for ($i = 1; $i < 6; $i++) {
            if (!empty($this->{'element_'.$i})) {
                switch ($this->{'element_'.$i}) {
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT01:
                        $olduserdata->content .= $olduserdata->id;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT02:
                        $format_time = get_string('strftimedaytime');
                        $olduserdata->content .= userdate($olduserdata->time, $format_time);
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT03:
                        $format_date = get_string("strftimedate");
                        $olduserdata->content .= userdate($olduserdata->time, $format_date);
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT04:
                        $format_datetime = get_string("strftimedatetime");
                        $olduserdata->content .= userdate($olduserdata->time, $format_datetime);
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT05:
                        $olduserdata->content .= $USER->id;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT06:
                        $olduserdata->content .= $USER->firstname;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT07:
                        $olduserdata->content .= $USER->lastname;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT08:
                        $olduserdata->content .= fullname($USER);
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT09:
                        $olduserdata->content .= 'usergroupid';
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT10:
                        $olduserdata->content .= 'usergroupname';
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT11:
                        $olduserdata->content .= $survey->id;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT12:
                        $olduserdata->content .= $survey->name;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT13:
                        $olduserdata->content .= $COURSE->id;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT14:
                        $olduserdata->content .= $COURSE->name;
                        break;
                    default:
                        $olduserdata->content .= $this->{'element_'.$i};
                }
            }
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

        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
            $prefill[$fieldname] = $olduserdata->content;
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
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /**
     * survey_autofill_mform_label
     * @param $item
     * @return
     */
    function survey_autofill_mform_label() {
        global $USER, $COURSE, $survey;

        $label = '';
        for ($i = 1; $i < 6; $i++) {
            if (!empty($this->{'element_'.$i})) {
                switch ($this->{'element_'.$i}) {
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT01: // submissionid
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT02: // submissiontime
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT03: // submissiondate
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT04: // submissiondateandtime
                        // if during string build you find a element that can not be valued now,
                        // overwrite $label, break switch and continue in the for
                        $label = get_string('latevalue', 'surveyfield_autofill');
                        break 2; // it is the first time I use it! Coooool :-)
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT05: // userid
                        $label .= $USER->id;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT06: // userfirstname
                        $label .= $USER->firstname;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT07: // userlastname
                        $label .= $USER->lastname;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT08: // userfullname
                        $label .= fullname($USER);
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT09: // usergroupid
                        $label .= 'usergroupid';
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT10: // usergroupname
                        $label .= 'usergroupname';
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT11: // surveyid
                        $label .= $survey->id;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT12: // surveyname
                        $label .= $survey->name;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT13: // courseid
                        $label .= $COURSE->id;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT14: // coursename
                        $label .= $COURSE->name;
                        break;
                    default:                                    // label
                        $label .= $this->{'element_'.$i};
                }
            }
        }
        return $label;
    }

}