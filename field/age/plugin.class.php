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
require_once($CFG->dirroot.'/mod/survey/field/age/lib.php');

class surveyfield_age extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_age record
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
    public $defaultvalue = -2635200;

    /*
     * $lowerbound = the minimum allowed age
     */
    public $lowerbound = -2635200;

    /*
     * $upperbound = the maximum allowed age
     */
    public $upperbound = 0;

    /*
     * $flag = features describing the object
     */
    public $flag;

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
        $this->plugin = 'age';

        $maximumage = get_config('surveyfield_age', 'maximumage');
        $this->upperbound = $this->item_age_to_unix_time($maximumage, 11);

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

        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

        // Do parent item saving stuff here (surveyitem_base::save($record)))
        return parent::item_save($record);
    }

    /*
     * item_split_unix_time
     * @param $time
     * @return
     */
    public function item_split_unix_time($time, $applyusersettings=false) {
        $getdate = parent::item_split_unix_time($time, $applyusersettings);

        $getdate['year'] -= SURVEYFIELD_AGE_YEAROFFSET;
        if ($getdate['mon'] == 12) {
            $getdate['year']++;
            $getdate['mon'] = 0;
        }

        return $getdate;
    }

    /*
     * item_age_to_unix_time
     * @param $year, $month
     * @return
     */
    public function item_age_to_unix_time($year, $month) {
        $year += SURVEYFIELD_AGE_YEAROFFSET;
        return (gmmktime(12, 0, 0, $month, 1, $year)); // This is GMT
    }

    /*
     * item_custom_fields_to_form
     * translates the age class property $fieldlist in $field.'_year' and $field.'_month'
     * @param
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            $agearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'_year'} = $agearray['year'];
            $this->{$field.'_month'} = $agearray['mon'];
        }

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
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_year'}) && isset($record->{$field.'_month'})) {
                $record->{$field} = $this->item_age_to_unix_time($record->{$field.'_year'}, $record->{$field.'_month'});
                unset($record->{$field.'_year'});
                unset($record->{$field.'_month'});
            } else {
                $record->{$field} = null;
            }
        }

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
        unset($record->defaultvalue_year);
        unset($record->defaultvalue_month);
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
        $pattern = '~^([0-9]+)/([0-9]+)$~';
        preg_match($pattern, $parentcontent, $matches);

        return $matches;
    }

    /*
     * item_composite_fields
     * get the list of composite fields
     * @param
     * @return
     */
    public function item_composite_fields() {
        return array('lowerbound', 'upperbound');
    }

    /*
     * item_get_filling_instructions
     * @param
     * @return
     */
    public function item_get_filling_instructions() {
        $maximumage = get_config('surveyfield_age', 'maximumage');

        $haslowerbound = ($this->lowerbound != $this->item_age_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_age_to_unix_time($maximumage, 11));

        $strmonths = ' '.get_string('months', 'surveyfield_age');
        $stryears = ' '.get_string('years');

        $a = '';
        $lowerbound = $this->item_split_unix_time($this->lowerbound);
        $upperbound = $this->item_split_unix_time($this->upperbound);

        if ($haslowerbound) {
            if (!empty($lowerbound['year'])) {
                $a .= $lowerbound['year'].$stryears;
                if (!empty($lowerbound['mon'])) {
                    $a .= get_string('and', 'surveyfield_age').$lowerbound['mon'].$strmonths;
                }
            } else {
                $a .= $lowerbound['mon'].$strmonths;
            }
        }

        if ($haslowerbound && $hasupperbound) {
            $a .= get_string('and', 'surveyfield_age');
        }

        if ($hasupperbound) {
            if (!empty($upperbound['year'])) {
                $a .= $upperbound['year'].$stryears;
                if (!empty($upperbound['mon'])) {
                    $a .= get_string('and', 'surveyfield_age').$upperbound['mon'].$strmonths;
                }
            } else {
                if (!empty($upperbound['mon'])) {
                    $a .= $upperbound['mon'].$strmonths;
                }
            }
        }

        if ($haslowerbound && $hasupperbound) {
            $fillinginstruction = get_string('restriction_lowerupper', 'surveyfield_age', $a);
        } else {
            $fillinginstruction = '';
            if ($haslowerbound) {
                $fillinginstruction = get_string('restriction_lower', 'surveyfield_age', $a);
            }
            if ($hasupperbound) {
                $fillinginstruction = get_string('restriction_upper', 'surveyfield_age', $a);
            }
        }

        return $fillinginstruction;
    }

    /*
     * item_age_to_text
     * starting from an agearray returns the corresponding age in text format
     * @param $agearray
     * @return
     */
    public function item_age_to_text($agearray) {
        $return = $agearray['year'].' '.get_string('years').' '.$agearray['mon'].' '.get_string('months', 'surveyfield_age');

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
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);
        $years = array();
        $months = array();
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            $years[SURVEY_INVITATIONVALUE] = get_string('invitationyear', 'surveyfield_age');
            $months[SURVEY_INVITATIONVALUE] = get_string('invitationmonth', 'surveyfield_age');
        }
        $years += array_combine(range($this->lowerbound_year, $this->upperbound_year), range($this->lowerbound_year, $this->upperbound_year));
        $months += array_combine(range(0, 11), range(0, 11));

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_year', '', $years, array('class' => 'indent-'.$this->indent));
        // $elementgroup[] = $mform->createElement('static', 'yearlabel_'.$this->itemid, null, get_string('years'));
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_month', '', $months);
        // $elementgroup[] = $mform->createElement('static', 'monthlabel_'.$this->itemid, null, get_string('months', 'survey'));

        if (!$searchform) {
            if ($this->required) {
                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);

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
                $mform->setDefault($this->itemname.'_year', SURVEY_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_month', SURVEY_INVITATIONVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEY_CUSTOMDEFAULT:
                        $agearray = $this->item_split_unix_time($this->defaultvalue);
                        break;
                    case SURVEY_NOANSWERDEFAULT:
                        $agearray = $this->item_split_unix_time($this->lowerbound);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                }
                $mform->setDefault($this->itemname.'_year', $agearray['year']);
                $mform->setDefault($this->itemname.'_month', $agearray['mon']);
            }
        } else {
            $agearray = $this->item_split_unix_time($this->lowerbound);
            $mform->setDefault($this->itemname.'_year', $agearray['year']);
            $mform->setDefault($this->itemname.'_month', $agearray['mon']);
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

        $maximumage = get_config('surveyfield_age', 'maximumage');

        if ($this->extrarow) {
            $errorkey = $this->itemname.'_extrarow';
        } else {
            $errorkey = $this->itemname.'_group';
        }

        if ( ($data[$this->itemname.'_year'] == SURVEY_INVITATIONVALUE) ||
             ($data[$this->itemname.'_month'] == SURVEY_INVITATIONVALUE) ) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_agenotsetrequired', 'surveyfield_age');
            } else {
                $a = get_string('noanswer', 'survey');
                $errors[$errorkey] = get_string('uerr_agenotset', 'surveyfield_age', $a);
            }
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_age_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_age_to_unix_time($maximumage, 11));

        $userinput = $this->item_age_to_unix_time($data[$this->itemname.'_year'], $data[$this->itemname.'_month']);

        if ($haslowerbound && ($userinput < $this->lowerbound)) {
            $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyfield_age');
        }
        if ($hasupperbound && ($userinput > $this->upperbound)) {
            $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyfield_age');
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
            $olduserdata->content = $this->item_age_to_unix_time($itemdetail['year'], $itemdetail['month']);
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
                    $datearray = $this->item_split_unix_time($olduserdata->content);
                    $prefill[$this->itemname.'_month'] = $datearray['mon'];
                    $prefill[$this->itemname.'_year'] = $datearray['year'];
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
     * TODO: perché gli passo $itemvalue? non basterebbe $this->content o $this->item_get_main_text
     */
    public function userform_db_to_export($itemvalue) {
        $content = $itemvalue->content;
        $agearray = $this->item_split_unix_time($content);

        return $this->item_age_to_text($agearray);
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