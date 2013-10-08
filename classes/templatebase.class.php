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

/*
 * The base class representing a field
 */
class mod_survey_templatebase {
    /*
     * $survey: the record of this survey
     */
    public $survey = null;

    /********************** this will be provided later
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /*
     * Class constructor
     */
    public function __construct($survey) {
        $this->survey = $survey;
    }

    /*
     * prevent_direct_user_input
     */
    // the link may be available even if the template has submissions
    // I can not hide the link, otherwise people complain for its absence
    // so, I leave it and the I kindly stop the process from friendly_halt
    // Because of this and, as far as I know, this method is not used any more
    public function prevent_direct_user_input() {
        $forceediting = ($this->survey->riskyeditdeadline > time());
        $hassubmissions = survey_count_submissions($this->survey->id);

        if ($hassubmissions && (!$forceediting)) {
            print_error('incorrectaccessdetected', 'survey');
        }
        if ($this->survey->template && (!$forceediting)) {
            print_error('incorrectaccessdetected', 'survey');
        }
    }

    /*
     * friendly_halt
     */
    public function friendly_halt() {
        global $CFG, $OUTPUT;

        $forceediting = ($this->survey->riskyeditdeadline > time());
        $hassubmissions = survey_count_submissions($this->survey->id);

        if ($hassubmissions && (!$forceediting)) {
            echo $OUTPUT->box(get_string('applyusertemplatedenied01', 'survey'));
            $url = $CFG->wwwroot.'/mod/survey/view.php?s='.$this->survey->id;
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->survey->template && (!$forceediting)) {
            echo $OUTPUT->box(get_string('applyusertemplatedenied02', 'survey'));
            $url = $CFG->wwwroot.'/mod/survey/view.php?s='.$this->survey->id;
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }
    }

    /*
     * get_table_structure
     *
     * @param $tablename
     * @param $dropid
     * @return
     */
    public function get_table_structure($tablename, $dropid=true) {
        global $DB;

        $dbman = $DB->get_manager();
        if ($dbman->table_exists($tablename)) {
            $dbstructure = array();

            if ($dbfields = $DB->get_columns($tablename)) {
                foreach ($dbfields as $dbfield) {
                    $dbstructure[] = $dbfield->name;
                }
            }

            if ($dropid) {
                array_shift($dbstructure); // ID is always the first item
            }
            return $dbstructure;
        } else {
            return false;
        }
    }

    /*
     * write_template_content
     *
     * @param
     * @return
     */
    public function write_template_content($templatetype) {
        global $DB;

        $where = array('surveyid' => $this->survey->id);
        $itemseeds = $DB->get_records('survey_item', $where, 'sortindex', 'id, type, plugin');

        $fs = get_file_storage();

        $counter = array();
        $xmltemplate = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><items></items>');
        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);
            $xmlitem = $xmltemplate->addChild('item');

            // survey_item
            $xmltable = $xmlitem->addChild('survey_item');

            if ($templatetype == SURVEY_MASTERTEMPLATE) {
                if ($multilangfields = $item->item_get_multilang_fields()) { // pagebreak and fieldset have not multilang_fields
                    $this->build_langtree('item', $multilangfields, $item);
                }
            }

            $structure = $this->get_table_structure('survey_item');
            foreach ($structure as $field) {
                if ($field == 'surveyid') {
                    continue;
                }
                if ($field == 'formpage') {
                    continue;
                }
                if ($field == 'timecreated') {
                    continue;
                }
                if ($field == 'timemodified') {
                    continue;
                }
                if ($field == 'parentid') {
                    $parentid = $item->get_parentid();
                    if ($parentid) {
                        $whereparams = array('id' => $parentid);
                        // I store sortindex instead of parentid, because at restore time parent id will change
                        $val = $DB->get_field('survey_item', 'sortindex', $whereparams);
                        $xmlfield = $xmltable->addChild($field, $val);
                        // } else {
                        // it is empty, do not evaluate: jump
                    }

                    continue;
                }

                if ($templatetype == SURVEY_MASTERTEMPLATE) {
                    $val = $this->xml_get_field_content($item, 'item', $field, $multilangfields);
                } else {
                    $val = $item->item_get_generic_field($field);
                }

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                    // } else {
                    // it is empty, do not evaluate: jump
                }
            }

            // child table
            $xmltable = $xmlitem->addChild('survey_'.$itemseed->plugin);

            $structure = $this->get_table_structure('survey_'.$itemseed->plugin);
            foreach ($structure as $field) {
                if ($field == 'surveyid') {
                    continue;
                }
                if ($field == 'itemid') {
                    continue;
                }

                if ($templatetype == SURVEY_MASTERTEMPLATE) {
                    $val = $this->xml_get_field_content($item, $itemseed->plugin, $field, $multilangfields);
                } else {
                    $val = $item->item_get_generic_field($field);
                }

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                    // } else {
                    // it is empty, do not evaluate: jump
                }

                if ($field == 'content') {
                    if ($files = $fs->get_area_files($item->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $item->itemid)) {
                        foreach ($files as $file) {
                            $filename = $file->get_filename();
                            if ($filename == '.') {
                                continue;
                            }
                            $xmlembedded = $xmltable->addChild('embedded');
                            $xmlembedded->addChild('filename', $filename);
                            $xmlembedded->addChild('filecontent', base64_encode($file->get_content()));
                        }
                    }
                }
            }
        }

        // $option == false if 100% waste of time BUT BUT BUT
        // the output in the file is well written
        // I prefer a more readable xml file instead of few nanoseconds saved
        $option = false;
        if ($option) {
            // echo '$xmltemplate->asXML() = <br />';
            // print_object($xmltemplate->asXML());

            return $xmltemplate->asXML();
        } else {
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xmltemplate->asXML());

            // echo '$xmltemplate = <br />';
            // print_object($xmltemplate);

            return $dom->saveXML();
        }
    }

    /*
     * xml_get_field_content
     *
     * @param
     * @return
     */
    public function xml_get_field_content($item, $dummyplugin, $field, $multilangfields) {

        // 1st: which fields are multilang for the current item?
        if (in_array($field, $multilangfields[$dummyplugin])) { // if the field that is going to be assigned belongs to your multilang fields
            $frankenstinname = $dummyplugin.'_'.$field;

            if (isset($this->langtree[$frankenstinname])) {
                end($this->langtree[$frankenstinname]);
                $val = key($this->langtree[$frankenstinname]);
                return $val;
            }
        }

        $content = $item->item_get_generic_field($field);
        if (strlen($content)) {
            $val = $content;
        } else {
            // it is empty, do not evaluate: jump
            $val = null;
        }

        return $val;
    }

    /*
     * apply_template
     *
     * @param
     * @return null
     */
    public function apply_template($templatetype) {
        global $DB;

        $dbman = $DB->get_manager();

        if ($templatetype == SURVEY_USERTEMPLATE) {
            $actionoverother = $this->formdata->actionoverother;
        } else {
            $actionoverother = SURVEY_DELETEALLITEMS;
        }

        switch ($actionoverother) {
            case SURVEY_IGNOREITEMS:
                break;
            case SURVEY_HIDEITEMS:
                // BEGIN: hide all other items
                $DB->set_field('survey_item', 'hide', 1, array('surveyid' => $this->survey->id, 'hide' => 0));
                // END: hide all other items
                break;
            case SURVEY_DELETEALLITEMS:
                // BEGIN: delete all other items
                $sqlparam = array('surveyid' => $this->survey->id);
                $sql = 'SELECT si.plugin
                        FROM {survey_item} si
                        WHERE si.surveyid = :surveyid
                        GROUP BY si.plugin';

                $pluginseeds = $DB->get_records_sql($sql, $sqlparam);

                foreach ($pluginseeds as $pluginseed) {
                    $tablename = 'survey_'.$pluginseed->plugin;
                    if ($dbman->table_exists($tablename)) {
                        $DB->delete_records($tablename, $sqlparam);
                    }
                }
                $DB->delete_records('survey_item', $sqlparam);
                // END: delete all other items
                break;
            case SURVEY_DELETEVISIBLEITEMS:
            case SURVEY_DELETEHIDDENITEMS:
                // BEGIN: delete other items
                $sqlparam = array('surveyid' => $this->survey->id);
                if ($this->formdata->actionoverother == SURVEY_DELETEVISIBLEITEMS) {
                    $sqlparam['hide'] = 0;
                }
                if ($this->formdata->actionoverother == SURVEY_DELETEHIDDENITEMS) {
                    $sqlparam['hide'] = 1;
                }

                $sql = 'SELECT si.plugin
                        FROM {survey_item} si
                        WHERE si.surveyid = :surveyid
                            AND si.hide = :hide
                        GROUP BY si.plugin';
                $pluginseeds = $DB->get_records_sql($sql, $sqlparam);

                $pluginonly = $sqlparam;
                foreach ($pluginseeds as $pluginseed) {
                    $tablename = 'survey_'.$pluginseed->plugin;
                    if ($dbman->table_exists($tablename)) {
                        $pluginonly['plugin'] = $pluginseed->plugin;
                        $deletelist = $DB->get_recordset('survey_item', $pluginonly, 'id', 'id');
                        foreach ($deletelist as $todelete) {
                            $DB->delete_records($tablename, array('itemid' => $todelete->id));
                        }
                    }
                    $deletelist->close();
                }
                $DB->delete_records('survey_item', $sqlparam);
                // END: delete other items
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->formdata->actionoverother = '.$this->formdata->actionoverother);
        }

        if ($templatetype == SURVEY_USERTEMPLATE) { // it is multilang
            $this->utemplateid = $this->formdata->usertemplate;
            if (!empty($this->utemplateid)) { // something was selected
                $this->add_survey_from_template($templatetype);
            }
        } else {
            $this->mtemplatename = $this->formdata->mastertemplate;
            $record = new stdClass();

            $record->id = $this->survey->id;
            $record->template = $this->mtemplatename;
            $DB->update_record('survey', $record);

            $this->add_survey_from_template($templatetype);
        }
    }

    /*
     * add_items_from_utemplate
     *
     * @param $templateid
     * @return
     */
    public function add_survey_from_template($templatetype) {
        global $DB, $CFG, $PAGE;

        $cm = $PAGE->cm;
        $context = context_module::instance($cm->id);
        $fs = get_file_storage();

        if ($templatetype == SURVEY_MASTERTEMPLATE) { // it is multilang
            $templatename = $this->mtemplatename;
            $templatepath = $CFG->dirroot.'/mod/survey/template/'.$templatename.'/template.xml';
            $templatecontent = file_get_contents($templatepath);
        } else {
            $templatename = $this->get_utemplate_name();
            $templatecontent = $this->get_utemplate_content();
        }

        // create the class to apply mastertemplate settings
        if ($templatetype == SURVEY_MASTERTEMPLATE) {
            $classfile = $CFG->dirroot.'/mod/survey/template/'.$templatename.'/template.class.php';
            include_once($classfile);
            $classname = 'surveytemplate_'.$templatename;
            $mastertemplate = new $classname();
        }

        $simplexml = new SimpleXMLElement($templatecontent);
        // echo '<h2>Items saved in the file ('.count($simplexml->item).')</h2>';

        $sortindexoffset = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $this->survey->id));
        foreach ($simplexml->children() as $xmlitem) {
            // echo '<h3>Count of tables for the current item: '.count($xmlitem->children()).'</h3>';
            foreach ($xmlitem->children() as $xmltable) { // survey_item and survey_<<plugin>>
                $tablename = $xmltable->getName();
                // echo '<h4>Count of fields of the table '.$tablename.': '.count($xmltable->children()).'</h4>';
                $record = array();
                foreach ($xmltable->children() as $xmlfield) {
                    $fieldname = $xmlfield->getName();

                    // tag <embedded> always belong to survey_<<plugin>> table
                    // so: ($fieldname == 'embedded') only when survey_item has already been saved
                    // so: $itemid is known
                    if ($fieldname == 'embedded') {
                        // echo '<h5>Count of attributes of the field '.$fieldname.': '.count($xmlfield->children()).'</h5>';
                        foreach ($xmlfield->children() as $xmlfileattribute) {
                            $fileattributename = $xmlfileattribute->getName();
                            if ($fileattributename == 'filename') {
                                $filename = $xmlfileattribute;
                            }
                            if ($fileattributename == 'filecontent') {
                                $filecontent = base64_decode($xmlfileattribute);
                            }
                        }

                        // echo 'I need to add: "'.$filename.'" to the filearea<br />';

                        // add the file described by $filename and $filecontent to filearea
                        $filerecord = new stdClass();
                        $filerecord->contextid = $context->id;
                        $filerecord->component = 'mod_survey';
                        $filerecord->filearea = SURVEY_ITEMCONTENTFILEAREA;
                        $filerecord->itemid = $itemid;
                        $filerecord->filepath = '/';
                        $filerecord->filename = $filename;
                        $fileinfo = $fs->create_file_from_string($filerecord, $filecontent);
                    } else {
                        $fieldvalue = (string)$xmlfield;

                        $record[$fieldname] = $fieldvalue;
                    }
                }

                unset($record['id']);
                $record['surveyid'] = $this->survey->id;

                // apply template settings
                if ($templatetype == SURVEY_MASTERTEMPLATE) {
                    $mastertemplate->apply_template_settings($record);
                }

                if ($tablename == 'survey_item') {
                    $record['sortindex'] += $sortindexoffset;
                    if (!empty($record['parentid'])) {
                        $whereparams = array('surveyid' => $this->survey->id, 'sortindex' => ($record['parentid'] + $sortindexoffset));
                        $record['parentid'] = $DB->get_field('survey_item', 'id', $whereparams, MUST_EXIST);
                    }

                    $itemid = $DB->insert_record($tablename, $record);
                } else {
                    $record['itemid'] = $itemid;
                    $DB->insert_record($tablename, $record, false);
                }
            }
        }
    }

    /*
     * validate_xml
     *
     * @param $templateid
     * @return
     */
    public function validate_xml($xml) {
        global $CFG;

        // $debug = true; if you want to stop anyway to see where the xml template is buggy
        $debug = false;

        $simplexml = new SimpleXMLElement($xml);
        foreach ($simplexml->children() as $xmlitem) {
            foreach ($xmlitem->children() as $xmltable) {
                // for instance:
                //     <survey_item>
                //     <survey_radiobutton>
                $tablename = $xmltable->getName();

                // I am assuming that survey_item table is ALWAYS before the survey_<<plugin>> table
                if ($tablename == 'survey_item') {
                    $type = null;
                    $plugin = null;
                    foreach ($xmltable->children() as $xmlfield) {
                        $fieldname = $xmlfield->getName();
                        $fieldvalue = (string)$xmlfield;

                        if ($fieldname == 'type') {
                            $type = $fieldvalue;
                        }
                        if ($fieldname == 'plugin') {
                            $plugin = $fieldvalue;
                        }
                        if (($type) && ($plugin)) {
                            // I could use a random class here because they all share the same parent item_get_item_schema
                            // but, I need the right class name for the next table, so I start loading the correct class now
                            require_once($CFG->dirroot.'/mod/survey/'.$type.'/'.$plugin.'/plugin.class.php');
                            $classname = 'survey'.$type.'_'.$plugin;
                            $xsd = $classname::item_get_item_schema(); // <- itembase schema
                            break;
                        }
                    }
                    if ((!$type) || (!$plugin)) {
                        return false;
                    }
                } else {
                    // $classname is already onboard because of the previous loop over survey_item fields
                    $xsd = $classname::item_get_plugin_schema(); // <- plugin schema
                }

                if (empty($xsd)) {
                    debugging('$xsd was not found at '.__LINE__.' of '.__FILE__.'.');
                }

                $mdom = new DOMDocument();
                $status = $mdom->loadXML($xmltable->asXML());
                if (!$debug) {
                    $status = $status && @$mdom->schemaValidateSource($xsd);
                } else {
                    $status = $status && $mdom->schemaValidateSource($xsd);
                }
                if (!$status) {
                    // Stop here. Continuing is useless
                    if ($debug) {
                        echo '<hr /><textarea rows="10" cols="100">'.$xmltable->asXML().'</textarea>';
                        echo '<textarea rows="10" cols="100">'.$xsd.'</textarea>';
                    }
                    break 2; // it is the second time I use it! Coooool :-)
                }
            }
        }

        return $status;
    }
}