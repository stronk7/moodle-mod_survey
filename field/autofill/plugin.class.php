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
require_once($CFG->dirroot.'/mod/survey/field/autofill/lib.php');

class surveyfield_autofill extends mod_survey_itembase {

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
     * $hiddenfield = is the static text visible in the mform?
     */
    public $hiddenfield = false;

    /*
     * $element01 = element for $content
     */
    public $element01 = '';

    /*
     * $element01_select
     */
    public $element01_select = '';

    /*
     * $element01_text
     */
    public $element01_text = '';

    /*
     * $element02 = element for $content
     */
    public $element02 = '';

    /*
     * $element02_select
     */
    public $element02_select = '';

    /*
     * $element02_text
     */
    public $element02_text = '';

    /*
     * $element03 = element for $content
     */
    public $element03 = '';

    /*
     * $element03_select
     */
    public $element03_select = '';

    /*
     * $element03_text
     */
    public $element03_text = '';

    /*
     * $element04 = element for $content
     */
    public $element04 = '';

    /*
     * $element04_select
     */
    public $element04_select = '';

    /*
     * $element04_text
     */
    public $element04_text = '';

    /*
     * $element05 = element for $content
     */
    public $element05 = '';

    /*
     * $element05_select
     */
    public $element05_select = '';

    /*
     * $element05_text
     */
    public $element05_text = '';

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
        $this->plugin = 'autofill';

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEY_ITEMCONTENTFILEAREA);

        // list of fields I do not want to have in the item definition form
        $this->formrequires['required'] = false;         // <-- it will be set to 0 at save time
        $this->formrequires['hideinstructions'] = false;
        $this->formrequires['parentid'] = false;

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

        $record->hideinstructions = 1;
        $record->required = 0;
        $checkboxes = array('hiddenfield');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }
        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (mod_survey_itembase::save($record)))
        return parent::item_save($record);
    }

    /*
     * item_custom_fields_to_form
     * translates the class properties to form fields value
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
        // nothing to do: defaultvalue doesn't need any further care

        // 4. special management for autofill contents
        $referencearray = array(''); // <-- take care, the first element is already on board
        for ($i = 1; $i <= SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT; $i++) {
            $referencearray[] = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        }

        $items = array();
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            $fieldname = 'element'.$index.'_select';
            if (in_array($this->{'element'.$index}, $referencearray)) {
                $this->{$fieldname} = $this->{'element'.$index};
            } else {
                $constantname = 'SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                $this->{$fieldname} = constant($constantname);
                $fieldname = 'element'.$index.'_text';
                $this->{$fieldname} = $this->{'element'.$index};
            }
        }
    }

    /*
     * item_custom_fields_to_db
     *
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
            $index = sprintf('%02d', $i);
            if (!empty($record->{'element'.$index.'_select'})) {
                $constantname = 'SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                // intanto aggiorna le variabili
                // per i campi della tabella survey_autofill
                if ($record->{'element'.$index.'_select'} == constant($constantname)) {
                    $record->{'element'.$index} = $record->{'element'.$index.'_text'};
                } else {
                    $record->{'element'.$index} = $record->{'element'.$index.'_select'};
                }
            }
        }
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
    <xs:element name="surveyfield_autofill">
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
                <xs:element type="xs:string" name="variable" minOccurs="0"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:int" name="hiddenfield"/>
                <xs:element type="xs:string" name="element01" minOccurs="0"/>
                <xs:element type="xs:string" name="element02" minOccurs="0"/>
                <xs:element type="xs:string" name="element03" minOccurs="0"/>
                <xs:element type="xs:string" name="element04" minOccurs="0"/>
                <xs:element type="xs:string" name="element05" minOccurs="0"/>
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
        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = ($this->position == SURVEY_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        if (!$searchform) {
            // $referencearray = array(''); // <-- take care, the first element is already on board
            // for ($i = 1; $i <= SURVEYFIELD_AUTOFILL_CONTENTELEMENT_COUNT; $i++) {
            //     $referencearray[] = constant('SURVEYFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
            // }

            $mform->addElement('hidden', $this->itemname, $this->userform_get_content());
            $mform->setType($this->itemname, PARAM_RAW);

            if (!$this->hiddenfield) {
                // workaround suggested by Marina Glancy in MDL-42946
                $label = html_writer::tag('span', $this->userform_get_content(), array('class' => 'indent-'.$this->indent));
                $mform->addElement('static', $this->itemname.'_static', $elementlabel, $label);
            }
        } else {
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', array('class' => 'indent-'.$this->indent));
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'survey'));
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
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
        // nothing to do here
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
        global $USER, $COURSE, $survey;

        if ($searchform) {
            if (isset($answer['ignoreme'])) {
                $olduserdata->content = null;
            } else {
                if (isset($answer['mainelement'])) {
                    $olduserdata->content = $answer['mainelement'];
                } else {
                    print_error('unhandled return value from user submission');
                }
            }
            return;
        }

        $olduserdata->content = '';
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            if (!empty($this->{'element'.$index})) {
                switch ($this->{'element'.$index}) {
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT01:
                        $olduserdata->content .= $olduserdata->id;
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT02:
                        $formattime = get_string('strftimedaytime');
                        $olduserdata->content .= userdate($olduserdata->time, $formattime, 0);
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT03:
                        $formatdate = get_string('strftimedate');
                        $olduserdata->content .= userdate($olduserdata->time, $formatdate, 0);
                        break;
                    case SURVEYFIELD_AUTOFILL_CONTENTELEMENT04:
                        $formatdatetime = get_string('strftimedatetime');
                        $olduserdata->content .= userdate($olduserdata->time, $formatdatetime, 0);
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
                        $olduserdata->content .= $this->{'element'.$index};
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

        $prefill[$this->itemname] = $fromdb->content;

        return $prefill;
    }

    /*
     * userform_get_content
     *
     * @param $item
     * @return
     */
    public function userform_get_content() {
        global $USER, $COURSE, $survey;

        $label = '';
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            if (!empty($this->{'element'.$index})) {
                switch ($this->{'element'.$index}) {
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
                        $label .= $this->{'element'.$index};
                }
            }
        }
        return $label;
    }

    /*
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param
     * @return
     */
    public function userform_get_root_elements_name() {
        $elementnames = array();
        $elementnames[] = $this->itemname;
        if (!$this->hiddenfield) {
            $elementnames[] = $this->itemname.'_static';
        }

        return $elementnames;
    }
}
