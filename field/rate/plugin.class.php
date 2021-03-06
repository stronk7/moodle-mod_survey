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
require_once($CFG->dirroot.'/mod/survey/field/rate/lib.php');

class surveyfield_rate extends mod_survey_itembase {

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
     * $options = list of options in the form of "$value SURVEY_VALUELABELSEPARATOR $label"
     */
    public $options = '';

    /*
     * $rates = list of allowed rates in the form: "$value SURVEY_VALUELABELSEPARATOR $label"
     */
    public $rates = '';

    /*
     * $defaultoption
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = '';

    /*
     * $style = how is this rate item displayed? with radiobutton or with dropdown menu?
     */
    public $style = 0;

    /*
     * $allowsamerate = is the user allowed to provide two equal rates for two different options?
     */
    public $differentrates = false;

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*
     * $canbeparent
     */
    public static $canbeparent = false;

    /*
     * $MDL41767wasfixed
     * temporary property adapting the code to the status of MDL-41767
     */
    public $MDL41767wasfixed = false;

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
        $this->plugin = 'rate';

        $this->flag = new stdClass();
        $this->flag->issearchable = false;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEY_ITEMCONTENTFILEAREA);

        // list of fields I do not want to have in the item definition form
        $this->formrequires['insearchform'] = false;
        $this->formrequires['hideinstructions'] = false;
        $this->formrequires['position'] = SURVEY_POSITIONLEFT;

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
        // drop empty rows and trim edging rows spaces from each textarea field
        $fieldlist = array('options', 'rates', 'defaultvalue');
        $this->item_clean_textarea_fields($record, $fieldlist);

        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        // remember: position is mandatory and equal to SURVEY_POSITIONTOP by design
        $record->position = SURVEY_POSITIONTOP;
        $record->hideinstructions = 1;
        $record->differentrates = isset($record->differentrates) ? 1 : 0;
        // ------- end of fields saved in this plugin table ------- //

        // Do parent item saving stuff here (mod_survey_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_custom_fields_to_form
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
    }

    /*
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the date custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        if ($record->defaultoption != SURVEY_CUSTOMDEFAULT) {
            $record->defaultvalue = null;
        }
    }

    /*
     * item_left_position_allowed
     *
     * @return: boolean
     */
    public function item_left_position_allowed() {
        return false;
    }

    /*
     * item_generate_standard_default
     * sets record field to store the correct value to db for the date custom item
     *
     * @param $record
     * @return
     */
    public function item_generate_standard_default($options=null, $rates=null, $differentrates=null) {

        if (is_null($options)) {
            $options = $this->options;
        }
        if (is_null($rates)) {
            $rates = $this->rates;
        }
        if (is_null($differentrates)) {
            $differentrates = $this->differentrates;
        }

        if ($optionscount = count(survey_textarea_to_array($options))) {
            $ratesarray = survey_textarea_to_array($rates);
            if ($differentrates) {
                $default = array();
                foreach ($ratesarray as $k => $singlerate) {
                    if (strpos($singlerate, SURVEY_VALUELABELSEPARATOR) === false) {
                        $defaultrate = $singlerate;
                    } else {
                        $pair = explode(SURVEY_VALUELABELSEPARATOR, $singlerate);
                        $defaultrate = $pair[0];
                    }
                    $default[] = $defaultrate;
                    if (count($default) == $optionscount) {
                        break;
                    }
                }
            } else {
                $firstrate = reset($ratesarray);

                if (strpos($firstrate, SURVEY_VALUELABELSEPARATOR) === false) {
                    $defaultrate = $firstrate;
                } else {
                    $pair = explode(SURVEY_VALUELABELSEPARATOR, $firstrate);
                    $defaultrate = $pair[0];
                }

                $default = array_fill(1, $optionscount, $defaultrate);
            }
            return implode("\n", $default);
        }
    }

    /*
     * item_get_friendlyformat
     *
     * @param
     * @return
     */
    public function item_get_friendlyformat() {
        return SURVEYFIELD_RATE_RETURNLABELS;
    }

    /*
     * item_get_multilang_fields
     *
     * @param
     * @return
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['rate'] = array('content', 'options', 'rates', 'defaultvalue');

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
    <xs:element name="surveyfield_rate">
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

                <xs:element type="xs:string" name="options"/>
                <xs:element type="xs:string" name="rates"/>
                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:int" name="downloadformat"/>
                <xs:element type="xs:int" name="style"/>
                <xs:element type="xs:int" name="differentrates"/>
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
        // this plugin has $this->flag->issearchable = false; so it will never be part of a search form

        $options = survey_textarea_to_array($this->options);
        $rates = $this->item_get_labels_array('rates');
        $defaultvalues = survey_textarea_to_array($this->defaultvalue);

        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT)) {
            if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                $rates += array(SURVEY_INVITATIONVALUE => get_string('choosedots'));
            } else {
                $rates = array(SURVEY_INVITATIONVALUE => get_string('choosedots')) + $rates;
            }
        }

        if ($this->MDL41767wasfixed) {
            $elementlabel = '';
            if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                foreach ($options as $option) {
                    $elementlabel .= html_writer::tag('p', $option, array('class' => 'optionsradio'));
                }

                $separatorblock = array_fill(0, count($rates) - 1, ' ');

                $separator = array();
                $elementgroup = array();
                foreach ($options as $k => $option) {
                    $class = array('class' => 'indent-'.$this->indent);
                    foreach ($rates as $j => $rate) {
                        $uniquename = $this->itemname.'_'.$k;
                        $elementgroup[] = $mform->createElement('radio', $uniquename, '', $rate, $j, $class);
                        $class = '';
                    }
                    $separator += array_merge($separator, $separatorblock);
                    $separator[] = '<br />';
                }

                if (!$this->required) {
                    $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'), array('class' => 'indent-'.$this->indent));
                    // no need to add one more $separator, the group stops here
                }

                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            }

            if ($this->style == SURVEYFIELD_RATE_USESELECT) {
                foreach ($options as $option) {
                    $elementlabel .= html_writer::tag('p', $option, array('class' => 'optionsselect'));
                }

                $elementgroup = array();
                foreach ($options as $k => $option) {
                    $uniquename = $this->itemname.'_'.$k;
                    $elementgroup[] = $mform->createElement('select', $uniquename, $option, $rates, array('class' => 'indent-'.$this->indent));
                }

                if (!$this->required) {
                    $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'), array('class' => 'indent-'.$this->indent));
                    // no need to add one more $separator, the group stops here
                }

                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '<br />', false);
            }
        } else {
            if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                foreach ($options as $k => $option) {
                    $class = array('class' => 'indent-'.$this->indent);
                    $uniquename = $this->itemname.'_'.$k;
                    $elementgroup = array();
                    foreach ($rates as $j => $rate) {
                        $elementgroup[] = $mform->createElement('radio', $uniquename, '', $rate, $j, $class);
                        $class = '';
                    }
                    $mform->addGroup($elementgroup, $uniquename.'_group', $option, ' ', false);
                }
            }

            if ($this->style == SURVEYFIELD_RATE_USESELECT) {
                foreach ($options as $k => $option) {
                    $uniquename = $this->itemname.'_'.$k;
                    $mform->addElement('select', $uniquename, $option, $rates, array('class' => 'indent-'.$this->indent));
                }
            }

            if (!$this->required) {
                $mform->addElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'survey'), array('class' => 'indent-'.$this->indent));
            }
        }

        if ($this->required) {
            // even if the item is required I CAN NOT ADD ANY RULE HERE because:
            // -> I do not want JS form validation if the page is submitted through the "previous" button
            // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
            // simply add a dummy star to the item and the footer note about mandatory fields
            $mform->_required[] = $this->itemname.'_extrarow';
        } else {
            // disable if $this->itemname.'_noanswer' is selected
            if ($this->MDL41767wasfixed) {
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
            } else {
                $optionindex = 0;
                foreach ($options as $option) {
                    if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                        $uniquename = $this->itemname.'_'.$optionindex.'_group';
                    } else {
                        $uniquename = $this->itemname.'_'.$optionindex;
                    }

                    $mform->disabledIf($uniquename, $this->itemname.'_noanswer', 'checked');
                    $optionindex++;
                }
                if ($this->defaultoption == SURVEY_NOANSWERDEFAULT) {
                    $mform->setDefault($this->itemname.'_noanswer', '1');
                }
            }
        }

        switch ($this->defaultoption) {
            case SURVEY_CUSTOMDEFAULT:
                foreach ($options as $k => $option) {
                    $uniquename = $this->itemname.'_'.$k;
                    $defaultindex = array_search($defaultvalues[$k], $rates);
                    $mform->setDefault($uniquename, "$defaultindex");
                }
                break;
            case SURVEY_INVITATIONDEFAULT:
                foreach ($options as $k => $option) {
                    $uniquename = $this->itemname.'_'.$k;
                    $mform->setDefault($uniquename, SURVEY_INVITATIONVALUE);
                }
                break;
            case SURVEY_NOANSWERDEFAULT:
                $uniquename = $this->itemname.'_noanswer';
                $mform->setDefault($uniquename, SURVEY_NOANSWERVALUE);
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
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
        // this plugin displays as a set of dropdown menu or radio buttons. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if ($searchform) {
            return;
        }

        // if different rates were requested, it is time to verify this
        $options = survey_textarea_to_array($this->options);

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // nothing to validate
        }

        $optionindex = 0;
        $return = false;
        foreach ($options as $option) {
            $uniquename = $this->itemname.'_'.$optionindex;
            if ($data[$uniquename] == SURVEY_INVITATIONVALUE) {
                if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                    $elementname = $uniquename.'_group';
                } else {
                    $elementname = $uniquename;
                }
                $errors[$elementname] = get_string('uerr_optionnotset', 'surveyfield_rate');
                $return = true;
            }
            $optionindex++;
        }
        if ($return) {
            return;
        }

        if (!empty($this->differentrates)) {
            $optionscount = count($this->item_get_labels_array('options'));
            $rates = array();
            for ($i = 0; $i < $optionscount; $i++) {
                $rates[] = $data[$this->itemname.'_'.$i];
            }

            $uniquerates = array_unique($rates);
            $duplicaterates = array_diff_assoc($rates, $uniquerates);

            foreach ($duplicaterates as $k => $v) {
                if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                    $elementname = $this->itemname.'_'.$k.'_group';
                } else {
                    $elementname = $this->itemname.'_'.$k;
                }
                $errors[$elementname] = get_string('uerr_duplicaterate', 'surveyfield_rate');
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

        if (!empty($this->differentrates)) {
            $fillinginstruction = get_string('diffratesrequired', 'surveyfield_rate');
        } else {
            $fillinginstruction = '';
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
        if (isset($answer['noanswer'])) {
            $olduserdata->content = SURVEY_NOANSWERVALUE;
        } else {
            $return = array();
            foreach ($answer as $answeredrate) {
                $return[] = $answeredrate;
            }
            $olduserdata->content = implode(SURVEY_DBMULTIVALUESEPARATOR, $return);
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
        // [survey_field_rate_157_0] => italian: 3
        // [survey_field_rate_157_1] => english: 2
        // [survey_field_rate_157_2] => french: 1
        // [survey_field_rate_157_noanswer] => 0

        $prefill = array();

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEY_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = 1;
            } else {
                $answers = explode(SURVEY_DBMULTIVALUESEPARATOR, $fromdb->content);

                foreach ($answers as $optionindex => $value) {
                    $uniquename = $this->itemname.'_'.$optionindex;
                    $prefill[$uniquename] = $answers[$optionindex];
                }
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

        // $answers is an array like: array(1,1,0,0)
        switch ($format) {
            case SURVEYFIELD_RATE_RETURNVALUES:
            case SURVEYFIELD_RATE_RETURNLABELS:
                $answers = explode(SURVEY_DBMULTIVALUESEPARATOR, $content);
                $output = array();
                $labels = $this->item_get_labels_array('options');
                if ($format == SURVEYFIELD_RATE_RETURNVALUES) {
                    $rates = $this->item_get_values_array('rates');
                } else { // $format == SURVEYFIELD_RATE_RETURNLABELS
                    $rates = $this->item_get_labels_array('rates');
                }
                foreach ($labels as $k => $label) {
                    $index = $answers[$k];
                    $output[] = $label.SURVEYFIELD_RATE_VALUERATESEPARATOR.$rates[$index];
                }
                $return = implode(SURVEY_OUTPUTMULTIVALUESEPARATOR, $output);
                break;
            case SURVEYFIELD_RATE_RETURNPOSITION:
                // here I will ALWAYS HAVE 0;1;6;4;0;7 so each separator is welcome, even ','
                // I do not like pass the idea that ',' can be a separator so, I do not use it
                $return = $content;
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $format = '.$format);
        }

        return $return;
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

        if ($this->MDL41767wasfixed) {
            $elementnames[] = $this->itemname.'_group';
        } else {
            $options = survey_textarea_to_array($this->options);
            if ($this->style == SURVEYFIELD_RATE_USERADIO) {
                foreach ($options as $k => $option) {
                    $elementnames[] = $this->itemname.'_'.$k.'_group';
                }
            }

            if ($this->style == SURVEYFIELD_RATE_USESELECT) {
                foreach ($options as $k => $option) {
                    $elementnames[] = $this->itemname.'_'.$k;
                }
            }
        }
        if (!$this->required) {
            $elementnames[] = $this->itemname.'_noanswer';
        }

        return $elementnames;
    }
}
