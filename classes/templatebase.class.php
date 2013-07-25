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
                array_shift($dbstructure); // drop the first item: ID
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

        $counter = array();
        $xmltemplate = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><items></items>');
        foreach ($itemseeds as $itemseed) {

            $id = $itemseed->id;
            $type = $itemseed->type;
            $plugin = $itemseed->plugin;
            $item = survey_get_item($id, $type, $plugin);
            $xmlitem = $xmltemplate->addChild('item');

            // survey_item
            $structure = $this->get_table_structure('survey_item');

            $xmltable = $xmlitem->addChild('survey_item');
            foreach ($structure as $field) {
                if ($field == 'surveyid') {
                    continue;
                }
                if ($field == 'parentid') {
                    $parentid = $item->get_parentid();
                    if ($parentid) {
                        $sqlparams = array('id' => $parentid);
                        // I store sortindex instead of parentid, because at restore time parent id will change
                        $val = $DB->get_field('survey_item', 'sortindex', $sqlparams);
                    } else {
                        $val = 0;
                    }

                    $xmlfield = $xmltable->addChild($field, $val);
                    continue;
                }

                $val = $this->xml_get_field_content($item, 'item', $structure, $field, $counter, $templatetype);
                $xmlfield = $xmltable->addChild($field, $val);
            }

            if ($item->get_useplugintable()) { // only page break does not use the plugin table
                // child table
                $structure = $this->get_table_structure('survey_'.$plugin);

                $xmltable = $xmlitem->addChild('survey_'.$plugin);
                foreach ($structure as $field) {
                    if ($field == 'surveyid') {
                        continue;
                    }
                    $val = $this->xml_get_field_content($item, $plugin, $structure, $field, $counter, $templatetype);
                    $xmlfield = $xmltable->addChild($field, $val);
                }
            }
        }

        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmltemplate->asXML());

        // echo '$xmltemplate = <br />';
        // print_object($xmltemplate);

        return $dom->saveXML();
    }

    /*
     * xml_get_field_content
     *
     * @param
     * @return
     */
    public function xml_get_field_content($item, $plugin, $structure, $field, &$counter, $templatetype) {
        if ($templatetype == SURVEY_MASTERTEMPLATE) { // it is multilang
            if (substr($field, -4) == '_sid') { // end with _sid
                $counter[$plugin.'_'.$field] = (isset($counter[$plugin.'_'.$field])) ? ++$counter[$plugin.'_'.$field] : 1;
                $val = $counter[$plugin.'_'.$field];

                return $val;
            }

            $field_sid = $field.'_sid';
            if (in_array($field_sid, $structure)) { // has corresponding _sid
                $applymultilang = true;
            } else {
                $applymultilang = false;
            }
        } else {
            $applymultilang = false;
        }

        if ($applymultilang) {
            $val = '';
        } else {
            $item_field = $item->item_get_generic_field($field);
            if (is_null($item_field)) {
                $val = SURVEY_EMPTYTEMPLATEFIELD;
            } else {
                $val = $item_field;
                if ($val == 0) {
                    if (substr($field, -4) == '_sid') { // end with _sid
                        $val = SURVEY_EMPTYTEMPLATEFIELD;
                    }
                }
            }
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

        switch ($this->formdata->actionoverother) {
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
                        foreach($deletelist as $todelete) {
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
        global $DB, $CFG;

        if ($templatetype == SURVEY_MASTERTEMPLATE) { // it is multilang
            $templatename = $this->mtemplatename;
            $template_path = $CFG->dirroot.'/mod/survey/template/'.$templatename.'/template.xml';
            $templatecontent = file_get_contents($template_path);
        } else {
            $templatename = $this->get_utemplate_name();
            $templatecontent = $this->get_utemplate_content();
        }

        $xmltext = simplexml_load_string($templatecontent);
        // echo '<h2>Items saved in the file ('.count($xmltext->item).')</h2>';

        $sortindexoffset = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $this->survey->id));
        foreach ($xmltext->children() as $item) {
            // echo '<h3>Count of tables for the current item: '.count($item->children()).'</h3>';
            foreach ($item->children() as $table) {
                $tablename = $table->getName();
                // echo '<h4>Count of fields of the table '.$tablename.': '.count($table->children()).'</h4>';
                $plugin = substr($tablename, strlen('survey_'));
                $record = array();
                foreach ($table->children() as $field) {
                    $fieldname = $field->getName();
                    if ($templatetype == SURVEY_MASTERTEMPLATE) { // it is multilang
                        if (isset($record[$fieldname.'_sid']) && $record[$fieldname.'_sid']) { // has corresponding _sid
                            $applymultilang = true;
                        } else {
                            $applymultilang = false;
                        }
                    } else {
                        $applymultilang = false;
                    }
                    if ($applymultilang) {
                        // $index = sprintf('%02d', $record[$fieldname.'_sid']);;
                        // $record[$fieldname] = get_string($plugin.'_'.$fieldname.'_'.$index, 'surveytemplate_'.$templatename);
                        // LEAVE THE FIELD EMPTY
                    } else {
                        $fieldvalue = (string)$field;
// echo '$fieldname = '.$fieldname.'<br />';
// echo '$fieldvalue = '.$fieldvalue.'<hr />';
                        // echo '<div>Table: '.$table->getName().', Field: '.$fieldname.', content: '.$field.'</div>';
                        if ($fieldvalue == SURVEY_EMPTYTEMPLATEFIELD) {
                            $record[$fieldname] = null;
                        } else {
                            $record[$fieldname] = $fieldvalue;
                        }
                    }
                }

                unset($record['id']);
                $record['surveyid'] = $this->survey->id;
                if ($tablename == 'survey_item') {
                    $record['sortindex'] += $sortindexoffset;
                    if (!empty($record['parentid'])) {
                        $sqlparams = array('surveyid' => $this->survey->id, 'sortindex' => ($record['parentid'] + $sortindexoffset));
                        $record['parentid'] = $DB->get_field('survey_item', 'id', $sqlparams, MUST_EXIST);
                    }
                    $itemid = $DB->insert_record($tablename, $record);
                } else {
                    $record['itemid'] = $itemid;
                    $DB->insert_record($tablename, $record, false);
                }
            }
        }
    }
}