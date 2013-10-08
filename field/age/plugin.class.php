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
require_once($CFG->dirroot.'/mod/survey/field/age/lib.php');

class surveyfield_age extends mod_survey_itembase {

    /*
     * $content = the text content of the item.
     */
    public $content = '';

    /*
     * $contenttrust
     */
    public $contenttrust = 1;

    /*
     * public $contentformat = '';
     */
    public $contentformat = '';

    /*
     * $customnumber = the custom number of the item.
     * It usually is 1. 1.1, a, 2.1.a...
     */
    public $customnumber = '';

    /*
     * $position = where does the question go?
     */
    public $position = SURVEY_POSITIONLEFT;

    /*
     * $extranote = an optional text describing the item
     */
    public $extranote = '';

    /*
     * $required = boolean. O == optional item; 1 == mandatory item
     */
    public $required = 0;

    /*
     * $variable = the name of the field storing data in the db table
     */
    public $variable = '';

    /*
     * $indent = the indent of the item in the form page
     */
    public $indent = 0;

    // -----------------------------

    /*
     * $defaultoption
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = -2635200;

    /*
     * $defaultvalue_year
     */
    public $defaultvalue_year = null;

    /*
     * $defaultvalue_month
     */
    public $defaultvalue_month = null;

    /*
     * $lowerbound = the minimum allowed age
     */
    public $lowerbound = -2635200;

    /*
     * $lowerbound_year
     */
    public $lowerbound_year = null;

    /*
     * $lowerbound_month
     */
    public $lowerbound_month = null;

    /*
     * $upperbound = the maximum allowed age
     */
    public $upperbound = 0;

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*
     * $canbeparent
     */
    public static $canbeparent = false;

    // -----------------------------

    /*
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional survey_item ID
     */
    public function __construct($itemid=0) {
        global $PAGE;

        $cm = $PAGE->cm;

        if (isset($cm)) { // it is not set during upgrade whther this item is loaded
            $this->context = context_module::instance($cm->id);
        }

        $this->type = SURVEY_TYPEFIELD;
        $this->plugin = 'age';

        $maximumage = get_config('surveyfield_age', 'maximumage');
        $this->upperbound = $this->item_age_to_unix_time($maximumage, 11);

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEY_ITEMCONTENTFILEAREA);

        // list of fields I do not want to have in the item definition form
        // EMPTY LIST

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
        // -----------------------------
        // Now execute very specific plugin level actions
        // -----------------------------

        // ------ begin of fields saved in survey_items ------ //
        /* surveyid
         * type
         * plugin

         * hide
         * insearchform
         * advanced

         * sortindex
         * formpage

         * timecreated
         * timemodified
         */
        // ------- end of fields saved in survey_items ------- //

        // ------ begin of fields saved in this plugin table ------ //
        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (mod_survey_itembase::save($record)))
        return parent::item_save($record);
    }

    /*
     * item_split_unix_time
     *
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
     *
     * @param $year
     * @param $month
     * @return
     */
    public function item_age_to_unix_time($year, $month) {
        $year += SURVEYFIELD_AGE_YEAROFFSET;
        return (gmmktime(12, 0, 0, $month, 1, $year)); // This is GMT
    }

    /*
     * item_custom_fields_to_form
     * translates the age class property $fieldlist in $field.'_year' and $field.'_month'
     *
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
     * item_age_to_text
     * starting from an agearray returns the corresponding age in text format
     *
     * @param $agearray
     * @return
     */
    public function item_age_to_text($agearray) {
        $stryears = get_string('years');
        $strmonths = get_string('months', 'surveyfield_age');

        $return = '';
        if (!empty($agearray['year'])) {
            $return .= $agearray['year'].' '.$stryears;
            if (!empty($agearray['mon'])) {
                $return .= ' '.get_string('and', 'surveyfield_age').' '.$agearray['mon'].' '.$strmonths;
            }
        } else {
            $return .= $agearray['mon'].' '.$strmonths;
        }

        return $return;
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();

        return $fieldlist;
    }

    /*
     * item_get_plugin_schema
     * Return the xml schema for survey_<<plugin>> table.
     *
     * @return string
     *
     */
    public static function item_get_plugin_schema() {
        $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
    <xs:element name="survey_age">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="content"/>
                <xs:element name="embedded" minOccurs="0" maxOccurs="unbounded">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element type="xs:string" name="filename"/>
                            <xs:element type="xs:base64Binary" name="filecontent"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                <xs:element type="xs:int" name="contentformat"/>

                <xs:element type="xs:string" name="customnumber" minOccurs="0"/>
                <xs:element type="xs:int" name="position"/>
                <xs:element type="xs:string" name="extranote" minOccurs="0"/>
                <xs:element type="xs:int" name="required"/>
                <xs:element type="xs:int" name="hideinstructions"/>
                <xs:element type="xs:string" name="variable" minOccurs="0"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="unixtime" name="defaultvalue" minOccurs="0"/>
                <xs:element type="unixtime" name="lowerbound"/>
                <xs:element type="unixtime" name="upperbound"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    <xs:simpleType name="unixtime">
        <xs:restriction base="xs:string">
            <xs:pattern value="-?\d{0,10}"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>
EOS;

        return $schema;
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
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = ($this->position == SURVEY_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';
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
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. THIS IS A MOODLE ISSUE. See: MDL-34815
                // $mform->_required[] = $this->itemname.'_group'; only adds the star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEY_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
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
     *
     * @param $answer
     * @param $olduserdata
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey) {
        // this plugin displays as dropdown menu. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // nothing to validate
        }

        $maximumage = get_config('surveyfield_age', 'maximumage');
        $errorkey = $this->itemname.'_group';

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

        if ($haslowerbound && $hasupperbound) {
            // internal range
            if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyfield_age');
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyfield_age');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyfield_age');
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
        $maximumage = get_config('surveyfield_age', 'maximumage');

        $haslowerbound = ($this->lowerbound != $this->item_age_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_age_to_unix_time($maximumage, 11));

        $a = '';
        $lowerbound = $this->item_split_unix_time($this->lowerbound);
        $upperbound = $this->item_split_unix_time($this->upperbound);

        $fillinginstruction = '';
        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = $this->item_age_to_text($lowerbound);
            $a->upperbound = $this->item_age_to_text($upperbound);

            $fillinginstruction .= get_string('restriction_lowerupper', 'surveyfield_age', $a);
        } else {
            if ($haslowerbound) {
                $a = $this->item_age_to_text($lowerbound);
                $fillinginstruction .= get_string('restriction_lower', 'surveyfield_age', $a);
            }

            if ($hasupperbound) {
                $a = $this->item_age_to_text($upperbound);
                $fillinginstruction .= get_string('restriction_upper', 'surveyfield_age', $a);
            }
        }

        return $fillinginstruction;
    }

    /*
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * this method calculates what to save in the db
     *
     * @param $answers
     * @param $format
     * @return
     */
    public function userform_save_preprocessing($answer, $olduserdata) {
        if (isset($answer['noanswer'])) {
            $olduserdata->content = SURVEY_NOANSWERVALUE;
        } else {
            $olduserdata->content = $this->item_age_to_unix_time($answer['year'], $answer['month']);
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
                    $datearray = $this->item_split_unix_time($fromdb->content);
                    $prefill[$this->itemname.'_month'] = $datearray['mon'];
                    $prefill[$this->itemname.'_year'] = $datearray['year'];
                }
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
        $content = $answer->content;
        if ($content == SURVEY_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'survey');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'survey');
        }

        $agearray = $this->item_split_unix_time($content);
        return $this->item_age_to_text($agearray);
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
