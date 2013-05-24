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
require_once($CFG->dirroot.'/mod/survey/field/datetime/lib.php');

class surveyfield_datetime extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_datetime record
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
     * $lowerbound = the minimum allowed date and time
     */
    public $lowerbound = 0;

    /*
     * $upperbound = the maximum allowed date and time
     */
    public $upperbound = 0;

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
        global $survey;

        $this->type = SURVEY_TYPEFIELD;
        $this->plugin = 'datetime';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->couldbeparent = false;
        $this->flag->useplugintable = true;

        // override properties depending from $survey settings
        $this->lowerbound = $this->item_datetime_to_unix_time($survey->startyear, 1, 1, 0, 0);
        $this->upperbound = $this->item_datetime_to_unix_time($survey->stopyear, 12, 31, 23, 59);
        $this->defaultvalue = $this->lowerbound;

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

        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_datetime_to_unix_time
     * @param $year, $month, $day, $hour, $minute
     * @return
     */
    public function item_datetime_to_unix_time($year, $month, $day, $hour, $minute) {
        return (gmmktime($hour, $minute, 0, $month, $day, $year)); // This is GMT
    }

    /*
     * item_custom_fields_to_form
     * translates the datetime class property $fieldlist in $field.'_year' and $field.'_month' and so forth
     * @param
     * @return
     */
    public function item_custom_fields_to_form() {
        global $survey;

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
                        $this->{$field} = $this->item_datetime_to_unix_time($survey->startyear, 1, 1, 0, 0);
                        break;
                    case 'upperbound':
                        $this->{$field} = $this->item_datetime_to_unix_time($survey->stopyear, 12, 31, 23, 59);
                        break;
                }
            }
            $datetimearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'_year'} = $datetimearray['year'];
            $this->{$field.'_month'} = $datetimearray['mon'];
            $this->{$field.'_day'} = $datetimearray['mday'];
            $this->{$field.'_hour'} = $datetimearray['hours'];
            $this->{$field.'_minute'} = $datetimearray['minutes'];
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
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_year'}) && isset($record->{$field.'_month'}) && isset($record->{$field.'_day'}) &&
                isset($record->{$field.'_hour'}) && isset($record->{$field.'_minute'})) {
                $record->{$field} = $this->item_datetime_to_unix_time($record->{$field.'_year'}, $record->{$field.'_month'}, $record->{$field.'_day'}, $record->{$field.'_hour'}, $record->{$field.'_minute'});
                unset($record->{$field.'_year'});
                unset($record->{$field.'_month'});
                unset($record->{$field.'_day'});
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
        $pattern = '~^([0-9]+)/([0-9]+)/([0-9]{4});([0-9]+):([0-9]+)$~';
        preg_match($pattern, $parentcontent, $matches);

        return $matches;
    }

    /*
     * item_get_filling_instructions
     * @param
     * @return
     */
    public function item_get_filling_instructions() {
        global $survey;

        $haslowerbound = ($this->lowerbound != $this->item_datetime_to_unix_time($survey->startyear, 1, 1, 0, 0));
        $hasupperbound = ($this->upperbound != $this->item_datetime_to_unix_time($survey->stopyear, 12, 31, 23, 59));

        $format = get_string('strftimedatetime', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            $a = userdate($this->lowerbound, $format).get_string('and', 'surveyfield_datetime').userdate($this->upperbound, $format);
            $fillinginstruction = get_string('restriction_lowerupper', 'surveyfield_datetime', $a);
        } else {
            $fillinginstruction = '';
            if ($haslowerbound) {
                $a = userdate($this->lowerbound, $format);
                $fillinginstruction = get_string('restriction_lower', 'surveyfield_datetime', $a);
            }
            if ($hasupperbound) {
                $a = userdate($this->upperbound, $format);
                $fillinginstruction = get_string('restriction_upper', 'surveyfield_datetime', $a);
            }
        }

        return $fillinginstruction;
    }

    /*
     * item_datetime_to_text
     * starting from an agearray returns the corresponding age in text format
     * @param $agearray
     * @return
     */
    public function item_datetime_to_text($datetimearray) {
        $return = userdate($unixtime, '%d/%m/%y, %H:%M');
        // $return = $datetimearray['mday'].'/'.$datetimearray['mon'].'/'.$datetimearray['year'].'; '.
        //     $datetimearray['hours'].':'.$datetimearray['minutes'];

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

        $days = array();
        $months = array();
        $years = array();
        $hours = array();
        $minutes = array();
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            $days[SURVEY_INVITATIONVALUE] = get_string('invitationday', 'surveyfield_datetime');
            $months[SURVEY_INVITATIONVALUE] = get_string('invitationmonth', 'surveyfield_datetime');
            $years[SURVEY_INVITATIONVALUE] = get_string('invitationyear', 'surveyfield_datetime');
            $hours[SURVEY_INVITATIONVALUE] = get_string('invitationhour', 'surveyfield_datetime');
            $minutes[SURVEY_INVITATIONVALUE] = get_string('invitationminute', 'surveyfield_datetime');
        }

        $days += array_combine(range(1, 31), range(1, 31));
        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B"); // january, february, march...
        }
        $years += array_combine(range($this->lowerbound_year, $this->upperbound_year), range($this->lowerbound_year, $this->upperbound_year));
        for ($i = 0; $i < 24; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i < 60; $i++) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_day', '', $days, array('class' => 'indent-'.$this->indent));
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_minute', '', $minutes);

        $separator = array(' ', ' ', ' ', ':');
        if (!$searchform) {
            if ($this->required) {
                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted trough the "previous" button
                // -> I do not want JS field validation even if this item is required AND disabled too. THIS IS A MOODLE BUG. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                if ($this->extrarow) {
                    $starplace = $this->itemname.'_extrarow';
                } else {
                    $starplace = $this->itemname.'_group';
                }
                $mform->_required[] = $starplace;
            } else {
                $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'));
                $separator[] = ' ';
                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
            }
        } else {
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('star', 'survey'));
            $separator[] = ' ';
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }

        // default section
        if (!$searchform) {
            if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                $mform->setDefault($this->itemname.'_day', SURVEY_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_month', SURVEY_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_year', SURVEY_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_hour', SURVEY_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_minute', SURVEY_INVITATIONVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEY_CUSTOMDEFAULT:
                        $datetimearray = $this->item_split_unix_time($this->defaultvalue, true);
                        break;
                    case SURVEY_TIMENOWDEFAULT:
                        $datetimearray = $this->item_split_unix_time(time(), true);
                        break;
                    case SURVEY_NOANSWERDEFAULT:
                        $datetimearray = $this->item_split_unix_time($this->lowerbound, true);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                    case SURVEY_LIKELASTDEFAULT:
                        // look for my last submission
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $mylastsubmissionid = $DB->get_field_select('survey_submissions', 'id', $sql, array('userid' => $USER->id), IGNORE_MISSING);
                        if ($time = $DB->get_field('survey_userdata', 'content', array('itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid), IGNORE_MISSING)) {
                            $datetimearray = $this->item_split_unix_time($time, false);
                        } else { // as in standard default
                            $datetimearray = $this->item_split_unix_time(time(), true);
                        }
                        break;
                    default:
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
                }
                $mform->setDefault($this->itemname.'_day', $datetimearray['mday']);
                $mform->setDefault($this->itemname.'_month', $datetimearray['mon']);
                $mform->setDefault($this->itemname.'_year', $datetimearray['year']);
                $mform->setDefault($this->itemname.'_hour', $datetimearray['hours']);
                $mform->setDefault($this->itemname.'_minute', $datetimearray['minutes']);
            }
        } else {
            $datetimearray = $this->item_split_unix_time($this->lowerbound);
            $mform->setDefault($this->itemname.'_day', $datetimearray['mday']);
            $mform->setDefault($this->itemname.'_month', $datetimearray['mon']);
            $mform->setDefault($this->itemname.'_year', $datetimearray['year']);
            $mform->setDefault($this->itemname.'_hour', $datetimearray['hours']);
            $mform->setDefault($this->itemname.'_minute', $datetimearray['minutes']);
            $mform->setDefault($this->itemname.'_noanswer', '1');
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        // this plugin displays as dropdown menu. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // nothing to validate
        }

        if ($this->extrarow) {
            $errorkey = $this->itemname.'_extrarow';
        } else {
            $errorkey = $this->itemname.'_group';
        }

        if ( ($data[$this->itemname.'_day'] == SURVEY_INVITATIONVALUE) ||
             ($data[$this->itemname.'_month'] == SURVEY_INVITATIONVALUE) ||
             ($data[$this->itemname.'_year'] == SURVEY_INVITATIONVALUE) ||
             ($data[$this->itemname.'_hour'] == SURVEY_INVITATIONVALUE) ||
             ($data[$this->itemname.'_minute'] == SURVEY_INVITATIONVALUE) ) {
            if ($this->required) {
                $errors[errorkey] = get_string('uerr_datetimenotsetrequired', 'surveyfield_datetime');
            } else {
                $a = get_string('noanswer', 'survey');
                $errors[$errorkey] = get_string('uerr_datetimenotset', 'surveyfield_datetime', $a);
            }
            $errors[$errorkey] = get_string('uerr_daynotset', 'surveyfield_datetime');
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_datetime_to_unix_time($survey->startyear, 1, 1, 0, 0));
        $hasupperbound = ($this->upperbound != $this->item_datetime_to_unix_time($survey->stopyear, 12, 31, 23, 59));

        $userinput = $this->item_datetime_to_unix_time($data[$this->itemname.'_year'], $data[$this->itemname.'_month'], $data[$this->itemname.'_day'], $data[$this->itemname.'_hour'], $data[$this->itemname.'_minute']);

        if ($haslowerbound && ($userinput < $this->lowerbound)) {
            $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyfield_datetime');
        }
        if ($hasupperbound && ($userinput > $this->upperbound)) {
            $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyfield_datetime');
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
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * I define the info to store in the db
     * @param $itemdetail, $olduserdata, $saving
     * @return
     */
    public function userform_save_preprocessing($itemdetail, $olduserdata, $saving) {
        if (isset($itemdetail['noanswer'])) {
            $olduserdata->content = null;
        } else {
            $olduserdata->content = $this->item_datetime_to_unix_time($itemdetail['year'], $itemdetail['month'], $itemdetail['day'], $itemdetail['hour'], $itemdetail['minute']);
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
                if ($olduserdata->content == SURVEY_NOANSWERVALUE) {
                    $prefill[$this->itemname.'_noanswer'] = 1;
                } else {
                    $datetimearray = $this->item_split_unix_time($olduserdata->content);
                    $prefill[$this->itemname.'_day'] = $datetimearray['mday'];
                    $prefill[$this->itemname.'_month'] = $datetimearray['mon'];
                    $prefill[$this->itemname.'_year'] = $datetimearray['year'];
                    $prefill[$this->itemname.'_hour'] = $datetimearray['hours'];
                    $prefill[$this->itemname.'_minute'] = $datetimearray['minutes'];
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
        if (!$this->downloadformat) { // return unixtime
            return $content;
        } else {
            // TODO: is userdate correct?
            // if I fill the survey from a different timezone and I write 5pm,
            // the teacher has to get the same datetime not a different one
            return userdate($content, get_string($this->downloadformat, 'core_langconfig'));
        }
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