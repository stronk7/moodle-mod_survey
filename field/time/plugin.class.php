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
require_once($CFG->dirroot.'/mod/survey/field/time/lib.php');

class surveyfield_time extends mod_survey_itembase {

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
     * $hideinstructions = boolean. Exceptionally hide filling instructions
     */
    public $hideinstructions = 0;

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
     * $step = the step for minutes drop down menu
     */
    public $step = 1;

    /*
     * $defaultoption = the value of the field when the form is initially displayed.
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = '';

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;

    /*
     * $defaultvalue_hour
     */
    public $defaultvalue_hour = null;

    /*
     * $defaultvalue_minute
     */
    public $defaultvalue_minute = null;

    /*
     * $lowerbound = the minimum allowed time
     */
    public $lowerbound = 0;

    /*
     * $lowerbound_hour
     */
    public $lowerbound_hour = null;

    /*
     * $lowerbound_minute
     */
    public $lowerbound_minute = null;

    /*
     * $upperbound = the maximum allowed time
     */
    public $upperbound = 86340;

    /*
     * $upperbound_hour
     */
    public $upperbound_hour = null;

    /*
     * $upperbound_minute
     */
    public $upperbound_minute = null;

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
    public function __construct($itemid=0, $evaluateparentcontent) {
        global $PAGE;

        $cm = $PAGE->cm;

        if (isset($cm)) { // it is not set during upgrade whther this item is loaded
            $this->context = context_module::instance($cm->id);
        }

        $this->type = SURVEY_TYPEFIELD;
        $this->plugin = 'time';

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEY_ITEMCONTENTFILEAREA);

        // list of fields I do not want to have in the item definition form
        // EMPTY LIST

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /*
     * item_load
     *
     * @param $itemid
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (mod_survey_itembase::item_load($itemid)))
        parent::item_load($itemid, $evaluateparentcontent);

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

         * hidden
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

        // round defaultvalue according to step
        $timearray = $this->item_split_unix_time($record->defaultvalue);
        $defaultvaluehour = $timearray['hours'];
        $defaultvalueminute = $timearray['minutes'];

        $stepscount = intval($defaultvalueminute/$record->step);
        $exceed = $defaultvalueminute % $record->step;
        if ($exceed < ($record->step/2)) {
            $defaultvalueminute = $stepscount * $record->step;
        } else {
            $defaultvalueminute = (1 + $stepscount) * $record->step;
        }
        $record->defaultvalue = $this->item_time_to_unix_time($defaultvaluehour, $defaultvalueminute);
        // end of: round defaultvalue according to step
        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (mod_survey_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_time_to_unix_time
     *
     * @param $hour
     * @param $minute
     * @return
     */
    public function item_time_to_unix_time($hour, $minute) {
        return (gmmktime($hour, $minute, 0, SURVEYFIELD_TIME_MONTHOFFSET, SURVEYFIELD_TIME_DAYOFFSET, SURVEYFIELD_TIME_YEAROFFSET)); // This is GMT
    }

    /*
     * item_custom_fields_to_form
     * sets record field to store the correct value to the form for customfields of the time item
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
     *
     * @param
     * @return
     */
    public function item_composite_fields() {
        return array('defaultvalue', 'lowerbound', 'upperbound');
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

        $option['strftime1'] = userdate($timenow, get_string('strftime1', 'surveyfield_time')); // 05:15
        $option['strftime2'] = userdate($timenow, get_string('strftime2', 'surveyfield_time')); // 5:15 am
        $option['unixtime'] = get_string('unixtime', 'survey');

        return $option;
    }

    /*
     * item_get_friendlyformat
     *
     * @param
     * @return
     */
    public function item_get_friendlyformat() {
        return 'strftime1';
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
    <xs:element name="surveyfield_time">
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

                <xs:element type="xs:int" name="step"/>
                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:int" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:string" name="downloadformat"/>
                <xs:element type="xs:int" name="lowerbound"/>
                <xs:element type="xs:int" name="upperbound"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK userform

    /*
     * userform_mform_element
     *
     * @param $mform
     * @param $searchform
     * @return
     */
    public function userform_mform_element($mform, $searchform) {
        global $DB, $USER;

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = ($this->position == SURVEY_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        // element values
        $hours = array();
        $minutes = array();
        if (!$searchform) {
            if ($this->defaultoption == SURVEY_INVITATIONDEFAULT) {
                $hours[SURVEY_INVITATIONVALUE] = get_string('invitationhour', 'surveyfield_time');
                $minutes[SURVEY_INVITATIONVALUE] = get_string('invitationminute', 'surveyfield_time');
            }
        } else {
            $hours[SURVEY_IGNOREME] = '';
            $minutes[SURVEY_IGNOREME] = '';
        }

        if ($this->lowerbound_hour <= $this->upperbound_hour) {
            for ($i = (int)$this->lowerbound_hour; $i <= $this->upperbound_hour; $i++) {
                $hours[$i] = sprintf("%02d", $i);
            }
        } else {
            for ($i = (int)$this->lowerbound_hour; $i <= 24; $i++) {
                $hours[$i] = sprintf("%02d", $i);
            }
            for ($i = (int)1; $i <= $this->upperbound_hour; $i++) {
                $hours[$i] = sprintf("%02d", $i);
            }
        }
        for ($i = 0; $i <= 59; $i += $this->step) {
            $minutes[$i] = sprintf("%02d", $i);
        }
        // End of: element values

        // mform element
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_hour', '', $hours, array('class' => 'indent-'.$this->indent));
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_minute', '', $minutes);

        $separator = array(':');
        if ($this->required) {
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

            if (!$searchform) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEY_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            }
        } else {
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'));
            $separator[] = ' ';
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }
        // End of: mform element

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
                        // look for the last submission I made
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $mylastsubmissionid = $DB->get_field_select('survey_submission', 'id', $sql, array('userid' => $USER->id), IGNORE_MISSING);
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
            $mform->setDefault($this->itemname.'_hour', SURVEY_IGNOREME);
            $mform->setDefault($this->itemname.'_minute', SURVEY_IGNOREME);
            if (!$this->required) {
                $mform->setDefault($this->itemname.'_noanswer', '0');
            }
        }
    }

    /*
     * userform_mform_validation
     *
     * @param $data
     * @param &$errors
     * @param $survey
     * @param $searchform
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $searchform) {
        // this plugin displays as dropdown menu. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // nothing to validate
        }

        $errorkey = $this->itemname.'_group';

        // verify the content of each drop down menu
        if (!$searchform) {
            $testpassed = true;
            $testpassed = $testpassed && ($data[$this->itemname.'_hour'] != SURVEY_INVITATIONVALUE);
            $testpassed = $testpassed && ($data[$this->itemname.'_minute'] != SURVEY_INVITATIONVALUE);
        } else {
            // both drop down menues are allowed to be == SURVEY_IGNOREME
            // but not only 1
            $testpassed = true;
            if ($data[$this->itemname.'_hour'] == SURVEY_IGNOREME) {
                $testpassed = $testpassed && ($data[$this->itemname.'_minute'] == SURVEY_IGNOREME);
            } else {
                $testpassed = $testpassed && ($data[$this->itemname.'_minute'] != SURVEY_IGNOREME);
            }
        }
        if (!$testpassed) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_timenotsetrequired', 'surveyfield_time');
            } else {
                $a = get_string('noanswer', 'survey');
                $errors[$errorkey] = get_string('uerr_timenotset', 'surveyfield_time', $a);
            }
            return;
        }
        // End of: verify the content of each drop down menu

        if ($searchform) {
            // stop here your investigation. I don't further validations.
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_time_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_time_to_unix_time(23, 59));

        $userinput = $this->item_time_to_unix_time($data[$this->itemname.'_hour'], $data[$this->itemname.'_minute']);

        if ($haslowerbound && $hasupperbound) {
            $format = get_string('strftimetime', 'langconfig');
            if ($this->lowerbound < $this->upperbound) {
                // internal range
                if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                    $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyfield_time');
                }
            }

            if ($this->lowerbound > $this->upperbound) {
                // external range
                if ( ($userinput > $this->lowerbound) && ($userinput < $this->upperbound) ) {
                    $format = $this->item_get_friendlyformat();
                    $a = new stdClass();
                    $a->lowerbound = userdate($this->lowerbound, get_string($format, 'surveyfield_time'), 0);
                    $a->upperbound = userdate($this->upperbound, get_string($format, 'surveyfield_time'), 0);
                    $errors[$errorkey] = get_string('uerr_outofexternalrange', 'surveyfield_time', $a);
                }
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyfield_time');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyfield_time');
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

        $haslowerbound = ($this->lowerbound != $this->item_time_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_time_to_unix_time(23, 59));

        $format = get_string('strftimetime', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = userdate($this->lowerbound, $format, 0);
            $a->upperbound = userdate($this->upperbound, $format, 0);

            if ($this->lowerbound < $this->upperbound) {
                // internal range
                $fillinginstruction = get_string('restriction_lowerupper', 'surveyfield_time', $a);
            }

            if ($this->lowerbound > $this->upperbound) {
                // external range
                $fillinginstruction = get_string('restriction_upperlower', 'surveyfield_time', $a);
            }
        } else {
            $fillinginstruction = '';
            if ($haslowerbound) {
                $a = userdate($this->lowerbound, $format, 0);
                $fillinginstruction = get_string('restriction_lower', 'surveyfield_date', $a);
            }
            if ($hasupperbound) {
                $a = userdate($this->upperbound, $format, 0);
                $fillinginstruction = get_string('restriction_upper', 'surveyfield_date', $a);
            }
        }

        return $fillinginstruction;
    }

    /*
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * this method calculates what to save in the db
     * or what to return for the search form
     *
     * @param $answer
     * @param $olduserdata
     * @param $searchform
     * @return
     */
    public function userform_save_preprocessing($answer, $olduserdata, $searchform) {
        if (isset($answer['noanswer'])) { // this is correct for input and search form both
            $olduserdata->content = SURVEY_NOANSWERVALUE;
        } else {
            if (!$searchform) {
                $olduserdata->content = $this->item_time_to_unix_time($answer['hour'], $answer['minute']);
            } else {
                if ($answer['hour'] == SURVEY_IGNOREME) {
                    $olduserdata->content = null;
                } else {
                    $olduserdata->content = $this->item_time_to_unix_time($answer['hour'], $answer['minute']);
                }
            }
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

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEY_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = 1;
            } else {
                $datearray = $this->item_split_unix_time($fromdb->content);
                $prefill[$this->itemname.'_hour'] = $datearray['hours'];
                $prefill[$this->itemname.'_minute'] = $datearray['minutes'];
            }
        }

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
            $format = $this->item_get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // output
        if ($format == 'unixtime') {
            return $content;
        } else {
            return userdate($content, get_string($format, 'surveyfield_time'), 0);
        }
    }

    /*
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param
     * @return
     */
    public function userform_get_root_elements_name() {
        $elementnames = array($this->itemname.'_group');

        return $elementnames;
    }
}
