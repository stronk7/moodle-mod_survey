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
require_once($CFG->dirroot.'/mod/survey/field/time/lib.php');

class surveyfield_time extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_time record
     */
    public $pluginid = 0;

    /*******************************************************************/

    /*
     * $defaultoption = the value of the field when the form is initially displayed.
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;

    /*
     * $lowerbound = the minimum allowed time
     */
    public $lowerbound = 0;

    /*
     * $upperbound = the maximum allowed time
     */
    public $upperbound = 86340;

    /*
     * $range = is the allowed date in between or external to boundary dates?
     */
    public $rangetype = 0;

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
        $this->plugin = 'time';

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

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_time_to_unix_time
     * @param $hour, $minute
     * @return
     */
    public function item_time_to_unix_time($hour, $minute) {
        return (gmmktime($hour, $minute, 0, 1, 1, SURVEYFIELD_TIME_YEAROFFSET)); // This is GMT
    }

    /*
     * item_custom_fields_to_form
     * sets record field to store the correct value to the form for customfields of the time item
     * @param
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (!isset($this->{$field})) {
                switch ($field) {
                    case 'defaultvalue':
                        continue 2; // it may be; continues switch and foreach too
                    case 'lowerbound':
                        $this->{$field} = 0;
                        break;
                    case 'upperbound':
                        $this->{$field} = 86340;
                        break;
                }
            }
            $timearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'_hour'} = $timearray['hours'];
            $this->{$field.'_minute'} = $timearray['minutes'];
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the time custom item
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_hour'}) && isset($record->{$field.'_minute'})) {
                $record->{$field} = $this->item_time_to_unix_time($record->{$field.'_hour'}, $record->{$field.'_minute'});
                unset($record->{$field.'_hour'});
                unset($record->{$field.'_minute'});
            } else {
                $record->{$field} = null;
            }
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_composite_fields
     * get the list of composite fields
     * @param
     * @return
     */
    public function item_composite_fields() {
        return array('defaultvalue', 'lowerbound', 'upperbound');
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
     * item_atomize_parent_content
     * starting from parentcontent, this function returns it splitted into an array
     * @param $parentcontent
     * @return
     */
    public function item_atomize_parent_content($parentcontent) {
        $pattern = '~^([0-9]+):([0-9]+)$~';
        preg_match($pattern, $parentcontent, $matches);

        return $matches;
    }

    /*
     * item_get_hard_info
     * @param
     * @return
     */
    public function item_get_hard_info() {

        $haslowerbound = ($this->lowerbound != $this->item_time_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_time_to_unix_time(23, 59));

        $format = get_string('strftimetime', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            $lowerbound = $this->item_split_unix_time($this->lowerbound);
            $upperbound = $this->item_split_unix_time($this->upperbound);
            if ($this->rangetype == SURVEYFIELD_TIME_INTERNALRANGE) {
                $a = $lowerbound['hours'].':'.$lowerbound['minutes'];
                $a .= get_string('and', 'surveyfield_time');
                $a .= $upperbound['hours'].':'.$upperbound['minutes'];
                $hardinfo = get_string('restriction_lowerupper_internal', 'surveyfield_time', $a);
            } else { // $this->rangetype == SURVEYFIELD_TIME_EXTERNALRANGE
                $a->lowerbound = $lowerbound['hours'].':'.$lowerbound['minutes'];
                $a->upperbound = $upperbound['hours'].':'.$upperbound['minutes'];
                $hardinfo = get_string('restriction_lowerupper_external', 'surveyfield_time', $a);
            }
        } else {
            $hardinfo = '';
            if ($haslowerbound) {
                $lowerbound = $this->item_split_unix_time($this->lowerbound);
                $a = $lowerbound['hours'].':'.$lowerbound['minutes'];
                $hardinfo = get_string('restriction_lower', 'surveyfield_time', $a);
            }
            if ($hasupperbound) {
                $upperbound = $this->item_split_unix_time($this->upperbound);
                $a = $upperbound['hours'].':'.$upperbound['minutes'];
                $hardinfo = get_string('restriction_upper', 'surveyfield_time', $a);
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

        $timearray = $this->item_split_unix_time($this->lowerbound, false);
        $constraints[] = get_string('lowerbound', 'surveyfield_age').': '.$this->item_time_to_text($timearray);

        $timearray = $this->item_split_unix_time($this->upperbound, false);
        $constraints[] = get_string('upperbound', 'surveyfield_age').': '.$this->item_time_to_text($timearray);

        return implode($constraints, '<br />');
    }

    /*
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        $status = true;
        $status = $status && ($childvalue >= $this->lowerbound);
        $status = $status && ($childvalue <= $this->upperbound);

        return $status;
    }

    /*
     * item_time_to_text
     * starting from an agearray returns the corresponding age in text format
     * @param $agearray
     * @return
     */
    public function item_time_to_text($timearray) {
        $return = $timearray['hours'].':'.$timearray['minutes'];
        return $return;
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
        global $DB, $USER;

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $hours = array();
        $minutes = array();
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            $hours[SURVEY_INVITATIONVALUE] = get_string('invitationhour', 'surveyfield_time');
            $minutes[SURVEY_INVITATIONVALUE] = get_string('invitationminute', 'surveyfield_time');
        }

        for ($i = (int)$this->lowerbound_hour; $i <= $this->upperbound_hour; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i <= 59; $i++) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_hour', '', $hours, array('class' => 'indent-'.$this->indent));
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_minute', '', $minutes);

        $separator = array(':');
        if ($this->required && !$searchform) {
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            $maybedisabled = $this->userform_can_be_disabled($survey, $canaccessadvancedform, $parentitem);
            if ($maybedisabled) {
                // $mform->addRule($this->itemname.'_group', get_string('required'), 'required', null, 'client');
                $mform->addRule($this->itemname.'_group', get_string('required'), 'nonempty_rule', $mform);
                $mform->_required[] = $this->itemname.'_group';
            }
        } else {
            $check_label = ($searchform) ? get_string('star', 'survey') : get_string('noanswer', 'survey');
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', $check_label);
            $separator[] = ' ';
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }

        // default section
        if (!$searchform) {
            if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                $mform->setDefault($this->itemname.'_hour', SURVEY_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_minute', SURVEY_INVITATIONVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEY_CUSTOMDEFAULT:
                        $timearray = $this->item_split_unix_time($this->defaultvalue, true);
                        break;
                    case SURVEY_TIMENOWDEFAULT:
                        $timearray = $this->item_split_unix_time(time(), true);
                        break;
                    case SURVEY_NOANSWERDEFAULT:
                        $timearray = $this->item_split_unix_time($this->lowerbound, true);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                    case SURVEY_LIKELASTDEFAULT:
                        // cerca la più recente submission fatta da me
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $mylastsubmissionid = $DB->get_field_select('survey_submissions', 'id', $sql, array('userid' => $USER->id), IGNORE_MISSING);
                        if ($time = $DB->get_field('survey_userdata', 'content', array('itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid), IGNORE_MISSING)) {
                            $timearray = $this->item_split_unix_time($time, false);
                        } else { // as in standard default
                            $timearray = $this->item_split_unix_time(time(), true);
                        }
                        break;
                    default:
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
                }
                $mform->setDefault($this->itemname.'_hour', $timearray['hours']);
                $mform->setDefault($this->itemname.'_minute', $timearray['minutes']);
            }
        } else {
            $timearray = $this->item_split_unix_time($this->lowerbound);
            $mform->setDefault($this->itemname.'_hour', $timearray['hours']);
            $mform->setDefault($this->itemname.'_minute', $timearray['minutes']);
            $mform->setDefault($this->itemname.'_noanswer', '1');
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // nothing to validate
        }
        if ($data[$this->itemname.'_hour'] == SURVEY_INVITATIONVALUE) {
            $errors[$this->itemname.'_group'] = get_string('uerr_hournotset', 'surveyfield_time');
            return;
        }
        if ($data[$this->itemname.'_minute'] == SURVEY_INVITATIONVALUE) {
            $errors[$this->itemname.'_group'] = get_string('uerr_minutenotset', 'surveyfield_time');
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_time_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_time_to_unix_time(23, 59));

        $userinput = $this->item_time_to_unix_time($data[$this->itemname.'_hour'], $data[$this->itemname.'_minute']);

        if ($haslowerbound && ($userinput < $this->lowerbound)) {
            $errors[$this->itemname.'_group'] = get_string('uerr_lowerthanminimum', 'surveyfield_time');
        }
        if ($hasupperbound && ($userinput > $this->upperbound)) {
            $errors[$this->itemname.'_group'] = get_string('uerr_greaterthanmaximum', 'surveyfield_time');
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
            $olduserdata->content = $this->item_time_to_unix_time($itemdetail['hour'], $itemdetail['minute']);
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
            if (!empty($olduserdata->content)) {
                if ($olduserdata->content == SURVEY_NOANSWERVALUE) {
                    $prefill[$this->itemname.'_noanswer'] = 1;
                } else {
                    $datearray = $this->item_split_unix_time($olduserdata->content);
                    $prefill[$this->itemname.'_hour'] = $datearray['hours'];
                    $prefill[$this->itemname.'_minute'] = $datearray['minutes'];
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
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     * @param $richsubmission
     * @return
     */
    public function userform_db_to_export($itemvalue) {
        $content = $itemvalue->content;
        $timearray = $this->item_split_unix_time($content);
        return $this->item_time_to_text($timearray);
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