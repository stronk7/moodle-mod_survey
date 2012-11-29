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
require_once($CFG->dirroot.'/mod/survey/field/date/lib.php');

class surveyfield_date extends surveyitem_base {

    /**
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_date record
     */
    public $pluginid = 0;

    /********************************************************************/

    /*
     * $defaultoption = the value of the field when the form is initially displayed.
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;

    /*
     * $lowerbound = the minimum allowed date
     */
    public $lowerbound = 0;

    /*
     * $upperbound = the maximum allowed date
     */
    public $upperbound = 0;

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
        global $survey;

        $this->type = SURVEY_FIELD;
        $this->plugin = 'date';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->ismatchable = true;
        $this->flag->useplugintable = true;

        // override properties depending from $survey settings
        $this->lowerbound = $this->item_date_to_unix_time($survey->startyear, 1, 1);
        $this->upperbound = $this->item_date_to_unix_time($survey->stopyear, 12, 31);
        $this->defaultvalue = $this->lowerbound;

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

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
    }

    /**
     * item_date_to_unix_time
     * @param $year, $month, $day
     * @return
     */
    public function item_date_to_unix_time($year, $month, $day) {
        return (gmmktime(12, 0, 0, $month, $day, $year)); // This is GMT
    }

    /**
     * item_custom_fields_to_form
     * translates the date class property $fieldlist in $field.'_year' and $field.'_month'
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
                        $this->{$field} = $this->item_date_to_unix_time($survey->startyear, 1, 1);
                        break;
                    case 'upperbound':
                        $this->{$field} = $this->item_date_to_unix_time($survey->stopyear, 1, 1);
                        break;
                }
            }
            $datearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'_year'} = $datearray['year'];
            $this->{$field.'_month'} = $datearray['mon'];
            $this->{$field.'_day'} = $datearray['mday'];
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /**
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
            if (isset($record->{$field.'_year'}) && isset($record->{$field.'_month'}) && isset($record->{$field.'_day'})) {
                $record->{$field} = $this->item_date_to_unix_time($record->{$field.'_year'}, $record->{$field.'_month'}, $record->{$field.'_day'});
                unset($record->{$field.'_year'});
                unset($record->{$field.'_month'});
                unset($record->{$field.'_day'});
            } else {
                $record->{$field} = null;
            }
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /**
     * item_composite_fields
     * get the list of composite fields
     * @param
     * @return
     */
    public function item_composite_fields() {
        return array('defaultvalue', 'lowerbound', 'upperbound');
    }

    /**
     * item_parent_content_format_validation
     * checks whether the user input format in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_format_validation($parentcontent) {
        $format = SURVEYFIELD_DATE_FORMAT; // '[dd/mm/yyyy]'

        $matches = $this->item_atomize_parent_content($parentcontent);

        if (!empty($matches)) {
            if (!checkdate($matches[2], $matches[1], $matches[3])) {
                return (get_string('parentcontentinvaliddate_err', 'surveyfield_date'));
            }
        } else {
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
        $format = SURVEYFIELD_DATE_FORMAT; // '[dd/mm/yyyy]'

        $matches = $this->item_atomize_parent_content($parentcontent);

        if (!empty($matches)) {
            if (!checkdate($matches[2], $matches[1], $matches[3])) {
                throw new moodle_exception('Unexpected invalid date for date item: id: '.$this->itemid.', type '.$this->type.', plugin: '.$this->plugin);
            }
            $inputdate = $this->item_date_to_unix_time($matches[3], $matches[2], $matches[1]);
            if ( ($inputdate < $this->lowerbound) || ($inputdate > $this->upperbound) ) {
                return (get_string('parentcontentdateoutofrange_err', 'surveyfield_date'));
            }
        } else {
            throw new moodle_exception('Unexpected invalid format for date item: id: '.$this->itemid.', type '.$this->type.', plugin: '.$this->plugin);
        }
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
        $matches = $this->item_atomize_parent_content($parentcontent);

        return $this->item_date_to_unix_time($matches[3], $matches[2], $matches[1]);
    }

    /**
     * item_atomize_parent_content
     * starting from parentcontent, this function returns it splitted into an array
     * @param $parentcontent
     * @return
     */
    public function item_atomize_parent_content($parentcontent) {
        $pattern = '~^([0-9]+)/([0-9]+)/([0-9]{4})$~';
        preg_match($pattern, $parentcontent, $matches);

        return $matches;
    }

    /**
     * item_get_hard_info
     * @param
     * @return
     */
    public function item_get_hard_info() {
        global $survey;

        $haslowerbound = ($this->lowerbound != $this->item_date_to_unix_time($survey->startyear, 1, 1));
        $hasupperbound = ($this->upperbound != $this->item_date_to_unix_time($survey->stopyear, 12, 31));

        $format = get_string('strftimedate', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            $a = userdate($this->lowerbound, $format).get_string('and', 'surveyfield_date').userdate($this->upperbound, $format);
            $hardinfo = get_string('restriction_lowerupper', 'surveyfield_date', $a);
        } else {
            $hardinfo = '';
            if ($haslowerbound) {
                $a = userdate($this->lowerbound, $format);
                $hardinfo = get_string('restriction_lower', 'surveyfield_date', $a);
            }
            if ($hasupperbound) {
                $a = userdate($this->upperbound, $format);
                $hardinfo = get_string('restriction_upper', 'surveyfield_date', $a);
            }
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

        $datearray = $this->item_split_unix_time($this->lowerbound, false);
        $constraints[] = get_string('lowerbound', 'surveyfield_age').': '.$this->item_date_to_text($datearray);

        $datearray = $this->item_split_unix_time($this->upperbound, false);
        $constraints[] = get_string('upperbound', 'surveyfield_age').': '.$this->item_date_to_text($datearray);

        return implode($constraints, '<br />');
    }

    /**
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

    /**
     * item_date_to_text
     * strating from a datearray returns the corresponding date in text format
     * @param $datearray
     * @return
     */
    public function item_date_to_text($datearray) {
        $return = $datearray['mday'].'/'.$datearray['mon'].'/'.$datearray['year'];
        return $return;
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
        $errindex = array_search('err', $values, true);
        if ($errindex !== false) {
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
        global $DB, $USER;

        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $days = array();
        $months = array();
        $years = array();
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            $days[SURVEY_INVITATIONVALUE] = get_string('invitationday', 'surveyfield_date');
            $months[SURVEY_INVITATIONVALUE] = get_string('invitationmonth', 'surveyfield_date');
            $years[SURVEY_INVITATIONVALUE] = get_string('invitationyear', 'surveyfield_date');
        }
        $days += array_combine(range(1, 31), range(1, 31));
        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B"); // january, february, march...
        }
        $years += array_combine(range($this->lowerbound_year, $this->upperbound_year), range($this->lowerbound_year, $this->upperbound_year));

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days, array('class' => 'indent-'.$this->indent));
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);

        if ( $this->required && (!$searchform) ) {
            $mform->addGroup($elementgroup, $fieldname.'_group', $elementlabel, ' ', false);
        } else {
            $check_label = ($searchform) ? get_string('star', 'survey') : get_string('noanswer', 'survey');
            $elementgroup[] = $mform->createElement('checkbox', $fieldname.'_noanswer', '', $check_label);
            $mform->addGroup($elementgroup, $fieldname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($fieldname.'_group', $fieldname.'_noanswer', 'checked');
        }

        // default section
        if (!$searchform) {
            if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                $mform->setDefault($fieldname.'_day', SURVEY_INVITATIONVALUE);
                $mform->setDefault($fieldname.'_month', SURVEY_INVITATIONVALUE);
                $mform->setDefault($fieldname.'_year', SURVEY_INVITATIONVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEY_CUSTOMDEFAULT:
                        $datearray = $this->item_split_unix_time($this->defaultvalue, true);
                        break;
                    case SURVEY_TIMENOWDEFAULT:
                        $datearray = $this->item_split_unix_time(time(), true);
                        break;
                    case SURVEY_NOANSWERDEFAULT:
                        $datearray = $this->item_split_unix_time($this->lowerbound, true);
                        $mform->setDefault($fieldname.'_noanswer', '1');
                        break;
                    case SURVEY_LIKELASTDEFAULT:
                        // cerca la più recente submission fatta da me
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $mylastsubmissionid = $DB->get_field_select('survey_submissions', 'id', $sql, array('userid' => $USER->id), IGNORE_MISSING);
                        if ($time = $DB->get_field('survey_userdata', 'content', array('itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid), IGNORE_MISSING)) {
                            $datearray = $this->item_split_unix_time($time, false);
                        } else { // as in standard default
                            $datearray = $this->item_split_unix_time(time(), true);
                        }
                        break;
                    default:
                        echo '$this->itemid = '.$this->itemid.'<br />';
                        echo '$this->pluginid = '.$this->pluginid.'<br />';
                        echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                        echo 'I have $this->defaultoption = '.$this->defaultoption.'<br />';
                        echo 'and the right "case" is missing<br />';
                }
                $mform->setDefault($fieldname.'_day', $datearray['mday']);
                $mform->setDefault($fieldname.'_month', $datearray['mon']);
                $mform->setDefault($fieldname.'_year', $datearray['year']);
            }
        } else {
            $datearray = $this->item_split_unix_time($this->lowerbound);
            $mform->setDefault($fieldname.'_day', $datearray['mday']);
            $mform->setDefault($fieldname.'_month', $datearray['mon']);
            $mform->setDefault($fieldname.'_year', $datearray['year']);
            $mform->setDefault($fieldname.'_noanswer', '1');
        }
    }

    /**
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        if (isset($data[$fieldname.'_noanswer'])) {
            return; // nothing to validate
        }
        if ($data[$fieldname.'_day'] == SURVEY_INVITATIONVALUE) {
            $errors[$fieldname.'_group'] = get_string('uerr_daynotset', 'surveyfield_date');
            return;
        }
        if ($data[$fieldname.'_month'] == SURVEY_INVITATIONVALUE) {
            $errors[$fieldname.'_group'] = get_string('uerr_monthnotset', 'surveyfield_date');
            return;
        }
        if ($data[$fieldname.'_year'] == SURVEY_INVITATIONVALUE) {
            $errors[$fieldname.'_group'] = get_string('uerr_yearnotset', 'surveyfield_date');
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_date_to_unix_time($survey->startyear, 1, 1));
        $hasupperbound = ($this->upperbound != $this->item_date_to_unix_time($survey->stopyear, 12, 31));

        $userinput = $this->item_date_to_unix_time($data[$fieldname.'_year'], $data[$fieldname.'_month'], $data[$fieldname.'_day']);

        if ($haslowerbound && ($userinput < $this->lowerbound)) {
            $errors[$fieldname.'_group'] = get_string('uerr_lowerthanminimum', 'surveyfield_date');
        }
        if ($hasupperbound && ($userinput > $this->upperbound)) {
            $errors[$fieldname.'_group'] = get_string('uerr_greaterthanmaximum', 'surveyfield_date');
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

        $matches = $this->item_atomize_parent_content($child_parentcontent);

        $disabilitationinfo = array();

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $fieldname.'_day';
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $matches[1];
        $disabilitationinfo[] = $mformelementinfo;

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $fieldname.'_month';
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $matches[2];
        $disabilitationinfo[] = $mformelementinfo;

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $fieldname.'_year';
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $matches[3];
        $disabilitationinfo[] = $mformelementinfo;

        if (!$this->required) {
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $fieldname.'_noanswer';
            $mformelementinfo->operator = 'eq';
            $mformelementinfo->content = '1';
            $disabilitationinfo[] = $mformelementinfo;
        }

        return $disabilitationinfo;
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
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $matches = $this->item_atomize_parent_content($child_parentcontent);

        $status = true;
        $status = $status && ($data[$fieldname.'_day'] == $matches[1]);
        $status = $status && ($data[$fieldname.'_month'] == $matches[2]);
        $status = $status && ($data[$fieldname.'_year'] == $matches[3]);
        if (isset($data[$fieldname.'_noanswer'])) {
            $status = $status && ($data[$fieldname.'_noanswer'] != '1');
        }

        return $status;
    }

    /**
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
            $olduserdata->content = $this->item_date_to_unix_time($itemdetail['year'], $itemdetail['month'], $itemdetail['day']);
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
                if ($olduserdata->content == SURVEY_NOANSWERVALUE) {
                    $prefill[$fieldname.'_noanswer'] = 1;
                } else {
                    $datearray = $this->item_split_unix_time($olduserdata->content);
                    $prefill[$fieldname.'_day'] = $datearray['mday'];
                    $prefill[$fieldname.'_month'] = $datearray['mon'];
                    $prefill[$fieldname.'_year'] = $datearray['year'];
                }
            // } else {
                // nothing was set
                // do not accept defaults but overwrite them
            }

            // _noanswer
            if (!$this->required) { // if this item foresaw the $fieldname.'_noanswer'
                $prefill[$fieldname.'_noanswer'] = is_null($olduserdata->content) ? 1 : 0;
            }
        } // else use item defaults

        return $prefill;
    }

    /**
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     * @param $richsubmission
     * @return
     */
    public function userform_db_to_export($itemvalue) {
        $content = $itemvalue->content;
        $datearray = $this->item_split_unix_time($content);
        return $this->item_date_to_text($datearray);
    }

    /**
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        return true;
    }
}