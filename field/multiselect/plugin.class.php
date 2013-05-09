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
require_once($CFG->dirroot.'/mod/survey/field/multiselect/lib.php');

class surveyfield_multiselect extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_multiselect record
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
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $heightinrows = the height of the multiselect in rows
     */
    public $heightinrows = 4;

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
        $this->plugin = 'multiselect';

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
     * @param $itemid
     * @return
     */
    public function item_load($itemid) {
        // Do parent item loading stuff here (surveyitem_base::item_load($itemid)))
        parent::item_load($itemid);

        // multilang load support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $fieldlist = array('content', 'options', 'defaultvalue');
        $this->item_builtin_string_load_support($fieldlist);
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

        // drop empty rows and trim edging rows spaces from each textarea field
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
        $optionstr = get_string('option', 'surveyfield_multiselect');
        foreach ($valuelabel as $value => $label) {
            $constraints[] = $optionstr.': '.$value;
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
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $valuelabel = $this->item_get_value_label_array('options');
        $defaults = $this->item_get_one_word_per_row('defaultvalue');

        $select = $mform->addElement('select', $this->itemname, $elementlabel, $valuelabel, array('size' => $this->heightinrows));
        $select->setMultiple(true);
        if ($defaults) {
            $mform->setDefault($this->itemname, $defaults);
        }
        /* this last item is needed because:
         * the JS validation MAY BE missing even if the field is required
         * (JS validation is never added because I do not want it when the "previous" button is pressed and when an item is disabled even if mandatory)
         * so the check for the non empty field is performed in the validation routine.
         * The validation routine is executed ONLY ON ITEM that are really submitted.
         * For multiselect, nothing is submitted if no items are checked
         * so, if the user neglects the mandatory multiselect item AT ALL, it is not submitted and, as conseguence, not validated.
         * TO ALWAYS SUBMIT A CHECKNBOXES SET I add a dummy hidden item.
         *
         * TAKE CARE: I choose a name for this item that IS UNIQUE BUT is missing the SURVEY_ITEMPREFIX.'_'
         *            In this way I am sure the item will never be saved in the database
         */
        $placeholderitemname = SURVEY_NEGLECTPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';
        $mform->addElement('hidden', $placeholderitemname, SURVEYFIELD_MULTISELECT_PLACEHOLDER);
        $mform->setType($placeholderitemname, PARAM_INT);

        if (!$searchform) {
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
        if ($this->required) {
            if ($this->extrarow) {
                $errorkey = $this->itemname.'_extrarow';
            } else {
                $errorkey = $this->itemname;
            }

            if (empty($data[$this->itemname])) {
                $errors[$errorkey] = get_string('required');
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
        $disabilitationinfo = array();

        $options = survey_textarea_to_array($child_parentcontent);

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $this->itemname.'[]';
        $mformelementinfo->operator = 'eq'; // TODO: Should be 'neq', waiting for MDL-39280
        $mformelementinfo->content = $options;
        $disabilitationinfo[] = $mformelementinfo;

        return $disabilitationinfo;
    }

    /*
     * userform_child_item_allowed_static
     * as parentitem defines whether a child item is supposed to be enabled in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called when $survey->newpageforchild == false
     * so the current survey lives in just one single web page (unless page break is manually added)
     * ----------------------------------------------------------------------
     * Am I getting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_item_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use userform_child_item_allowed_static
     * ----------------------------------------------------------------------
     * @param: $parentcontent, $parentsubmitted
     * @return
     */
    function userform_child_item_allowed_static($submissionid, $childitemrecord) {
        global $DB;

        if (!$childitemrecord->parentid) {
            return true;
        }

        $where = array('submissionid' => $submissionid, 'itemid' => $this->itemid);
        $givenanswer = $DB->get_field('survey_userdata', 'content', $where);

        $cleanvalue = explode("\n", $childitemrecord->parentvalue);
        $cleanvalue = implode(SURVEY_DBMULTIVALUESEPARATOR, $cleanvalue);

        return ($givenanswer === $cleanvalue);
    }

    /*
     * userform_child_item_allowed_dynamic
     * as parentitem defines whether a child item is supposed to be enabled in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called when $survey->newpageforchild == false
     * so the current survey lives in just one single web page (unless page break is manually added)
     * ----------------------------------------------------------------------
     * Am I getting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_item_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use userform_child_item_allowed_static
     * ----------------------------------------------------------------------
     * @param: $parentcontent, $parentsubmitted
     * @return
     */
    public function userform_child_item_allowed_dynamic($child_parentcontent, $data) {
        // if a child has me as parent, its parentcontent attribute will be a list of elements
        $content = survey_textarea_to_array($child_parentcontent);
        asort($content);

        $childconstraints = $data[$this->itemname];
        asort($childconstraints);

        return ($content === $childconstraints);
    }

    /*
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * I define the info to store in the db
     * @param $itemdetail, $olduserdata, $saving
     * @return
     */
    public function userform_save_preprocessing($itemdetail, $olduserdata, $saving) {
        if (!is_null($itemdetail['mainelement'])) {
            if ($saving) {
                $olduserdata->content = implode(SURVEY_DBMULTIVALUESEPARATOR, $itemdetail['mainelement']);
            } else { // searching
                $olduserdata->content = urlencode( implode(SURVEY_URLMULTIVALUESEPARATOR, $itemdetail['mainelement']) );
            }
        } else {
            $olduserdata->content = null;
        }

        if (!isset($itemdetail['mainelement'])) {
            throw new moodle_exception('unhandled return value from user submission');
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

        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            if (isset($olduserdata->content)) {
                $preset = explode(SURVEY_DBMULTIVALUESEPARATOR, $olduserdata->content);
                $prefill[$this->itemname] = $preset;
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
        return false;
    }
}