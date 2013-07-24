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
require_once($CFG->dirroot.'/mod/survey/field/recurrence/lib.php');

class surveyfield_recurrence extends mod_survey_itembase {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_recurrence record
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
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = '';

    /*
     * $lowerbound = the minimum allowed recurrence
     */
    public $lowerbound = 0;

    /*
     * $upperbound = the maximum allowed recurrence
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
        $this->plugin = 'recurrence';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->couldbeparent = false;
        $this->flag->useplugintable = true;

        // override properties depending from $survey settings
        $this->lowerbound = $this->item_recurrence_to_unix_time(1, 1);
        $this->upperbound = $this->item_recurrence_to_unix_time(12, 31);
        $this->defaultvalue = $this->lowerbound;

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
        $fieldlist = $this->item_get_multilang_fields();
        $this->item_builtin_string_save_support($record, $fieldlist);

        // Do parent item saving stuff here (mod_survey_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_recurrence_to_unix_time
     *
     * @param $year
     * @param $month
     * @param $day
     * @return
     */
    public function item_recurrence_to_unix_time($month, $day) {
        return (gmmktime(12, 0, 0, $month, $day, SURVEYFIELD_RECURRENCE_YEAROFFSET)); // This is GMT
    }

    /*
     * item_custom_fields_to_form
     * translates the recurrence class property $fieldlist in $field.'_year' and $field.'_month'
     *
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
                        $this->{$field} = $this->item_recurrence_to_unix_time(1, 1);
                        break;
                    case 'upperbound':
                        $this->{$field} = $this->item_recurrence_to_unix_time(1, 1);
                        break;
                }
            }
            $recurrencearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'_month'} = $recurrencearray['mon'];
            $this->{$field.'_day'} = $recurrencearray['mday'];
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the recurrence custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_month'}) && isset($record->{$field.'_day'})) {
                $record->{$field} = $this->item_recurrence_to_unix_time($record->{$field.'_month'}, $record->{$field.'_day'});
                unset($record->{$field.'_month'});
                unset($record->{$field.'_day'});
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
     *
     * @param
     * @return
     */
    public function item_composite_fields() {
        return array('defaultvalue', 'lowerbound', 'upperbound');
    }

    /*
     * item_atomize_parent_content
     * starting from parentcontent, this function returns it splitted into an array
     *
     * @param $parentcontent
     * @return
     */
    public function item_atomize_parent_content($parentcontent) {
        $pattern = '~^([0-9]+)/([0-9]+)/([0-9]{4})$~';
        preg_match($pattern, $parentcontent, $matches);

        return $matches;
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

        // just a check before assuming all has been done correctly
        $errindex = array_search('err', $values, true);
        if ($errindex !== false) {
            print_error('$values[\''.$errindex.'\'] of survey_'.$this->plugin.' was not properly managed');
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
        $timenow = time();

        $option['unixtime'] = get_string('unixtime', 'survey');
        $option['strftime1'] = userdate($timenow, get_string('strftime1', 'surveyfield_recurrence')); // 21 Giugno
        $option['strftime2'] = userdate($timenow, get_string('strftime2', 'surveyfield_recurrence')); // 21 Giu
        $option['strftime3'] = userdate($timenow, get_string('strftime3', 'surveyfield_recurrence')); // 21/06

        return $option;
    }

    /*
     * item_check_monthday
     *
     * @param
     * @return
     */
    public function item_check_monthday($day, $month) {
        if ($month == 2) {
            return ($day <= 29);
        }

        $longmonths = array(1, 3, 5, 7, 8, 10, 12);
        if (in_array($month, $longmonths)) {
            return ($day <= 31);
        } else {
            return ($day <= 30);
        }
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
        global $DB, $USER;

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $days = array();
        $months = array();
        $years = array();
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            $days[SURVEY_INVITATIONVALUE] = get_string('invitationday', 'surveyfield_recurrence');
            $months[SURVEY_INVITATIONVALUE] = get_string('invitationmonth', 'surveyfield_recurrence');
            $years[SURVEY_INVITATIONVALUE] = get_string('invitationyear', 'surveyfield_recurrence');
        }
        $days += array_combine(range(1, 31), range(1, 31));
        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // january, february, march...
        }

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_day', '', $days, array('class' => 'indent-'.$this->indent));
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_month', '', $months);

        if (!$searchform) {
            if ($this->required) {
                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);

                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. THIS IS A MOODLE ISSUE. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                $starplace = ($this->extrarow) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            } else {
                $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'));
                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
            }
        } else {
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('star', 'survey'));
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }

        // default section
        if (!$searchform) {
            if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                $mform->setDefault($this->itemname.'_day', SURVEY_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_month', SURVEY_INVITATIONVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEY_CUSTOMDEFAULT:
                        $recurrencearray = $this->item_split_unix_time($this->defaultvalue, true);
                        break;
                    case SURVEY_TIMENOWDEFAULT:
                        $recurrencearray = $this->item_split_unix_time(time(), true);
                        break;
                    case SURVEY_NOANSWERDEFAULT:
                        $recurrencearray = $this->item_split_unix_time($this->lowerbound, true);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                    case SURVEY_LIKELASTDEFAULT:
                        // look for the most recent submission I made
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $mylastsubmissionid = $DB->get_field_select('survey_submissions', 'id', $sql, array('userid' => $USER->id), IGNORE_MISSING);
                        if ($time = $DB->get_field('survey_userdata', 'content', array('itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid), IGNORE_MISSING)) {
                            $recurrencearray = $this->item_split_unix_time($time, false);
                        } else { // as in standard default
                            $recurrencearray = $this->item_split_unix_time(time(), true);
                        }
                        break;
                    default:
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
                }
                $mform->setDefault($this->itemname.'_day', $recurrencearray['mday']);
                $mform->setDefault($this->itemname.'_month', $recurrencearray['mon']);
            }
        } else {
            $recurrencearray = $this->item_split_unix_time($this->lowerbound);
            $mform->setDefault($this->itemname.'_day', $recurrencearray['mday']);
            $mform->setDefault($this->itemname.'_month', $recurrencearray['mon']);
            $mform->setDefault($this->itemname.'_noanswer', '1');
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
             ($data[$this->itemname.'_month'] == SURVEY_INVITATIONVALUE) ) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_recurrencenotsetrequired', 'surveyfield_recurrence');
            } else {
                $a = get_string('noanswer', 'survey');
                $errors[$errorkey] = get_string('uerr_recurrencenotset', 'surveyfield_recurrence', $a);
            }
            return;
        }

        if (!$this->item_check_monthday($data[$this->itemname.'_day'], $data[$this->itemname.'_month'])) {
            $errors[$errorkey] = get_string('incorrectrecurrence', 'surveyfield_recurrence');
        }

        $userinput = $this->item_recurrence_to_unix_time($data[$this->itemname.'_month'], $data[$this->itemname.'_day']);

        $format = get_string('strftimedateshort', 'langconfig');
        if ($this->lowerbound < $this->upperbound) {
            // internal range
            if ($userinput < $this->lowerbound) {
                $dummy = new StdClass();
                $dummy->content = $this->lowerbound;
                $a = $this->userform_db_to_export($dummy, $format);
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyfield_recurrence', $a);
            }
            if ($userinput > $this->upperbound) {
                $dummy = new StdClass();
                $dummy->content = $this->upperbound;
                $a = $this->userform_db_to_export($dummy, $format);
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyfield_recurrence', $a);
            }
        }

        if ($this->lowerbound > $this->upperbound) {
            // external range
            if ($userinput > $this->upperbound) {
                $dummy = new StdClass();
                $dummy->content = $this->lowerbound;
                $a = $this->userform_db_to_export($dummy, $format);
                $errors[$errorkey] = get_string('uerr_greaterthanminimum', 'surveyfield_recurrence', $a);
            }
            if ($userinput < $this->upperbound) {
                $dummy = new StdClass();
                $dummy->content = $this->upperbound;
                $a = $this->userform_db_to_export($dummy, $format);
                $errors[$errorkey] = get_string('uerr_lowerthanmaximum', 'surveyfield_recurrence', $a);
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
        global $survey;

        $format = get_string('strftimedateshort', 'langconfig');

        $a = new StdClass();
        $a->lowerbound = userdate($this->lowerbound, $format, 0);
        $a->upperbound = userdate($this->upperbound, $format, 0);

        if ($this->lowerbound < $this->upperbound) {
            // internal range
            $fillinginstruction = get_string('restriction_internal', 'surveyfield_recurrence', $a);
        }

        if ($this->lowerbound > $this->upperbound) {
            // external range
            $fillinginstruction = get_string('restriction_external', 'surveyfield_recurrence', $a);
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
        if (isset($answer['noanswer'])) {
            $olduserdata->content = SURVEY_NOANSWERVALUE;
        } else {
            $olduserdata->content = $this->item_recurrence_to_unix_time($answer['month'], $answer['day']);
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
                if ($fromdb->content == SURVEY_NOANSWERVALUE) {
                    $prefill[$this->itemname.'_noanswer'] = 1;
                } else {
                    $recurrencearray = $this->item_split_unix_time($fromdb->content);
                    $prefill[$this->itemname.'_day'] = $recurrencearray['mday'];
                    $prefill[$this->itemname.'_month'] = $recurrencearray['mon'];
                }
            // } else {
                // nothing was set
                // do not accept defaults but overwrite them
            }

            // _noanswer
            if (!$this->required) { // if this item foresaw the $this->itemname.'_noanswer'
                $prefill[$this->itemname.'_noanswer'] = is_null($fromdb->content) ? 1 : 0;
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
        // content
        $content = $answer->content;
        if ($content == SURVEY_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'survey');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'survey');
        }

        // format
        if ($format == SURVEY_FIRENDLYFORMAT) {
            $format = $this->get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // output
        if ($format == 'unixtime') {
            return $content;
        } else {
            return userdate($content, get_string($format, 'surveyfield_date'), 0);
        }
    }

    /*
     * get_friendlyformat
     * returns true if the useform mform element for this item id is a group and false if not
     *
     * @param
     * @return
     */
    public function get_friendlyformat() {
        return 'strftime3';
    }

    /*
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     *
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return true;
    }
}