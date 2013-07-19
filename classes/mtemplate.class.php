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

define('SURVEYMTEMPLATE_NAMEPLACEHOLDER', '@@mTemplateNamePlaceholder@@');

require_once($CFG->dirroot.'/mod/survey/classes/template.class.php');

class mod_survey_mastertemplate extends mod_survey_template {
    /*
     * $survey: the record of this survey
     */
    public $survey = null;

    /*
     * $libcontent: the content of the file lib.php that is going to populate the master template
     */
    public $libcontent = '';

    /*
     * $langtree
     */
    public $langtree = array();

    /*
     * $si_sid: Survey_item seed ID
     */
    public $si_sid = array();

    /*
     * $mtemplatename: name of the master template to work with
     */
    public $mtemplatename = '';

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
     * create_mtemplate
     *
     * @param
     * @return
     */
    public function create_mtemplate() {
        global $CFG, $DB;

        $pluginname = clean_filename($this->formdata->mastertemplatename);
        $temp_subdir = "mod_survey/surveyplugins/$pluginname";
        $temp_basedir = $CFG->tempdir.'/'.$temp_subdir;

        $master_basepath = "$CFG->dirroot/mod/survey/templatemaster";
        $master_filelist = get_directory_list($master_basepath);

        foreach ($master_filelist as $master_file) {
            $master_fileinfo = pathinfo($master_file);
            // create the structure of the temporary folder
            // the folder has to be created WITHOUT $CFG->tempdir/
            $temp_path = $temp_subdir.'/'.dirname($master_file);
            make_temp_directory($temp_path); // <-- just created the folder for the current plugin

            $temp_fullpath = $CFG->tempdir.'/'.$temp_path;

// echo '<hr />Operate on the file: '.$master_file.'<br />';
// echo $master_fileinfo["dirname"] . "<br />";
// echo $master_fileinfo["basename"] . "<br />";
// echo $master_fileinfo["extension"] . "<br />";
// echo dirname($master_file) . "<br />";

            if ($master_fileinfo['basename'] == 'icon.gif') {
                // copia icon.gif
                copy($master_basepath.'/'.$master_file, $temp_fullpath.'/'.$master_fileinfo['basename']);
                continue;
            }

            if ($master_fileinfo['dirname'] == 'lang/en') { // it is the lang file. It has already been done!
                continue;
            }

            if ($master_fileinfo['basename'] == 'lib.php') {
                // I need to scan all my surveyitem and plugin
                // and copy them
                // Start by reading the master
                $this->libcontent = file_get_contents($master_basepath.'/'.$master_file);
                // delete any trailing spaces or \n at the and of the file
                $this->libcontent = rtrim($this->libcontent);
                // drop off the closed brace at the end of the file
                $this->libcontent = substr($this->libcontent, 0, -1);
                // replace surveyTemplatePluginMaster with the name of the current survey
                $this->libcontent = str_replace(SURVEYMTEMPLATE_NAMEPLACEHOLDER, $pluginname, $this->libcontent);
                // finalize the libcontent
                $this->lib_write_content();
                // open
                $filehandler = fopen($temp_basedir.'/'.$master_file, 'w');
                // write
                fwrite($filehandler, $this->libcontent);
                // close
                fclose($filehandler);

                // /////////////////////////////////////////////////////////////////////////////////////
                // now write string file
                // /////////////////////////////////////////////////////////////////////////////////////

                // in which language the user is using Moodle?
                $userlang = current_language();
                $temp_path = $CFG->tempdir.'/'.$temp_subdir.'/lang/'.$userlang;

                // this is the language folder of the strings hardcoded in the survey
                // the folder lang/en already exist
                if ($userlang != 'en') {
                    // I need to create the folder lang/it
                    make_temp_directory($temp_subdir.'/lang/'.$userlang);
                }

                // echo '$master_basepath = '.$master_basepath.'<br />';

                $filecopyright = file_get_contents($master_basepath.'/lang/en/surveytemplate_pluginname.php');
                // replace surveyTemplatePluginMaster with the name of the current survey
                $filecopyright = str_replace(SURVEYMTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecopyright);

                $savedstrings = $filecopyright.$this->extract_original_string();

                // echo '<textarea rows="30" cols="100">'.$savedstrings.'</textarea>';
                // die;

                // create - this could be 'en' such as 'it'
                $filehandler = fopen($temp_path.'/surveytemplate_'.$pluginname.'.php', 'w');
                // write inside all the strings
                fwrite($filehandler, $savedstrings);
                // close
                fclose($filehandler);

                // this is the folder of the language en in case the user language is different from en
                if ($userlang != 'en') {
                    $temp_path = $CFG->tempdir.'/'.$temp_subdir.'/lang/en';
                    // create
                    $filehandler = fopen($temp_path.'/surveytemplate_'.$pluginname.'.php', 'w');
                    // write inside all the strings in teh form: 'english translation of $string[stringxx]'
                    $savedstrings = $filecopyright.$this->get_translated_strings($userlang);
                    // save into surveytemplate_<<$pluginname>>.php
                    fwrite($filehandler, $savedstrings);
                    // close
                    fclose($filehandler);
                }
                continue;
            }

            // for all the other files: survey.class.php, version.php
            // read the master
            $filecontent = file_get_contents($master_basepath.'/'.$master_file);
            // replace surveyTemplatePluginMaster with the name of the current survey
            $filecontent = str_replace(SURVEYMTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecontent);
            if ($master_fileinfo['basename'] == 'version.php') {
                $currentdate = gmdate("Ymd").'01';
                $filecontent = str_replace('1965100401', $currentdate, $filecontent);
            }
            // open
            $filehandler = fopen($temp_basedir.'/'.$master_file, 'w');
            // write
            fwrite($filehandler, $filecontent);
            // close
            fclose($filehandler);
        }

        $filenames = array(
            'db/install.php',
            'db/upgrade.php',
            'db/upgradelib.php',
            'lib.php',
            'pix/icon.gif',
            'survey.class.php',
            'version.php',
            'lang/en/surveytemplate_'.$pluginname.'.php',
        );
        if ($userlang != 'en') {
            $filenames[] = 'lang/'.$userlang.'/surveytemplate_'.$pluginname.'.php';
        }

        $filelist = array();
        foreach ($filenames as $filename) {
            $filelist[$filename] = $temp_basedir.'/'.$filename;
        }

        $exportfile = $temp_basedir.'.zip';
        file_exists($exportfile) && unlink($exportfile);

        $fp = get_file_packer('application/zip');
        $fp->archive_to_pathname($filelist, $exportfile);

        $dirnames = array('db/', 'pix/', 'lang/en/');
        if ($userlang != 'en') {
            $dirnames[] = 'lang/'.$userlang.'/';
        }
        $dirnames[] = 'lang/';

        // if (false) {
        foreach ($filelist as $file) {
            unlink($file);
        }
        foreach ($dirnames as $dir) {
            rmdir($temp_basedir.'/'.$dir);
        }
        rmdir($temp_basedir);
        // }

        // Return the full path to the exported template file:
        return $exportfile;
    }

    /*
     * lib_write_content
     *
     * @param
     * @return
     */
    public function lib_write_content() {
        global $DB;

        $pluginname = clean_filename($this->formdata->mastertemplatename);
        $structures = array();
        $sid = array();

        // STEP 01: make a list of used plugins
        $sql = 'SELECT si.plugin
                FROM {survey_item} si
                WHERE si.surveyid = :surveyid
                GROUP BY si.plugin';
        $params = array('surveyid' => $this->survey->id);
        $itemseeds = $DB->get_records_sql($sql, $params);

        // STEP 02: verify $itemseeds is not empty
        if (!count($itemseeds)) {
            return;
        }

        // STEP 03: before adding the fictitious plugin 'item'
        //          replace '// require_once(_LIBRARIES_)' with the list of require_once
        $librarycall = 'require_once($CFG->dirroot.\'/mod/survey/lib.php\');'."\n";
        $librarycall .= 'require_once($CFG->dirroot.\'/mod/survey/template/lib.php\');'."\n";
        foreach ($itemseeds as $itemseed) {
            $librarycall .= 'require_once($CFG->dirroot.\'/mod/survey/field/'.$itemseed->plugin.'/lib.php\');'."\n";
        }
        $this->libcontent = str_replace('// require_once(_LIBRARIES_);', $librarycall, $this->libcontent);

        // STEP 04: add, at top of $itemseeds, the 'item' element
        $base = new stdClass();
        $base->plugin = 'item';
        $itemseeds = array_merge(array('item' => $base), $itemseeds);

        // STEP 05: create survey_$plugin table structure array
        foreach ($itemseeds as $itemseed) {
            $tablename = 'survey_'.$itemseed->plugin;
            if ($structure = $this->get_table_structure($tablename)) {
                $structures[$tablename] = $structure;

                // if there is a field ending in '_sid' create the line initializing the index
                $currentsid = array();
                foreach ($structure as $field) {
                    if (substr($field, -4) == '_sid') {
                        $currentsid[] .= $field;
                        $field = substr($field, 0, -4);
                        $this->langtree[$field] = array();
                    }
                }

                $sid[$tablename] = $currentsid;
                $this->lib_write_table_structure($structure, $itemseed->plugin, $sid[$tablename]);
            }
        }

        $this->lib_write_structure_values_separator($pluginname);

        // STEP 06: make a list of all itemseeds
        $sql = 'SELECT si.id, si.type, si.plugin
                FROM {survey_item} si
                WHERE si.surveyid = :surveyid
                ORDER BY si.sortindex';
        $params = array('surveyid' => $this->survey->id);
        $itemseeds = $DB->get_records_sql($sql, $params);

        foreach ($itemseeds as $itemseed) {
            $this->lib_write_intro_si_values($sid['survey_item']);

            $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

            $values = $item->item_get_si_values($this->formdata, $structures['survey_item'], $sid['survey_item']);

            $this->lib_write_si_values($values);
            $this->collect_strings($sid['survey_item'], $item);

            if ($item->get_useplugintable()) { // only page break does not use the plugin table
                $tablename = 'survey_'.$itemseed->plugin;
                $currentsid = $sid[$tablename];
                $currentstructure = $structures[$tablename];
                $this->lib_write_intro_plugin_values($itemseed->plugin, $currentsid);

                $values = $item->item_get_plugin_values($currentstructure, $currentsid);

                $this->lib_write_plugin_values($values, $tablename, $itemseed->plugin);

                $this->collect_strings($sid[$tablename], $item);
            }

            $this->libcontent .= '//----------------------------------------------------------------------------//'."\n";
        }

        $this->libcontent .= '}'."\n";
    }

    /*
     * lib_write_table_structure
     *
     * @param $structure
     * @param $plugin
     * @param $sid
     * @return
     */
    public function lib_write_table_structure($structure, $plugin, $sid) {
        $varprefix = ($plugin == 'item') ? 'si' : $plugin;

        foreach ($sid as $singlesid) {
            $this->libcontent .= '    $'.$singlesid.' = 0;'."\n";
        }
        $this->libcontent .= '    // ////////////// SURVEY_'.strtoupper($plugin)."\n";
        $this->libcontent .= '    $'.$varprefix.'_fields = array(\'';
        $this->libcontent .= implode('\',\'', $structure);
        $this->libcontent .= '\');'."\n";
        $this->libcontent .= "\n";
    }

    /*
     * lib_write_structure_values_separator
     *
     * @param $pluginname
     * @return
     */
    public function lib_write_structure_values_separator($pluginname) {
        $this->libcontent .= '    // ////////////////////////////////////////////////////////////////////////////////////////////'."\n";
        $this->libcontent .= '    // ////////////////////////////////////////////////////////////////////////////////////////////'."\n";
        $this->libcontent .= '    // // '.strtoupper($pluginname)."\n";
        $this->libcontent .= '    // ////////////////////////////////////////////////////////////////////////////////////////////'."\n";
        $this->libcontent .= '    // ////////////////////////////////////////////////////////////////////////////////////////////'."\n";
    }

    /*
     * lib_write_intro_si_values
     *
     * @param $si_sid
     * @return
     */
    public function lib_write_intro_si_values($si_sid) {
        $this->libcontent .= "\n".'    $sortindex++; // <--- new item is going to be added'."\n\n";
        $indent = '';

        $this->libcontent .= '    // survey_item'."\n";
        $this->libcontent .= '    /*------------------------------------------------*/'."\n";

        // TODO: where do I assign a valur to $this->si_sid?
        foreach ($si_sid as $singlesid) {
            $this->libcontent .= $indent.'    $'.$singlesid.'++;'."\n";
        }
    }

    /*
     * lib_write_si_values
     *
     * @param $values
     * @return
     */
    public function lib_write_si_values($values) {
        $this->libcontent .= '    $values = array(';
        // $this->libcontent .= implode(',', $values);
        $this->libcontent .= $this->wrap_line($values, 20);
        $this->libcontent .= ');'."\n";
        // Take care you always write sortindex instead of parentid
        $this->libcontent .= '    $itemid = $DB->insert_record(\'survey_item\', array_combine($si_fields, $values));'."\n";

        $this->libcontent .= "\n";
    }

    /*
     * lib_write_intro_plugin_values
     *
     * @param $currentplugin
     * @param $currentsid
     * @return
     */
    public function lib_write_intro_plugin_values($currentplugin, $currentsid) {
        $this->libcontent .= '        // survey_'.$currentplugin."\n";
        $this->libcontent .= '        /*------------------------------------------------*/'."\n";

        foreach ($currentsid as $singlesid) {
            $this->libcontent .= '        $'.$singlesid.'++;'."\n";
        }
    }

    /*
     * lib_write_plugin_values
     *
     * @param $values
     * @param $tablename
     * @param $currentplugin
     * @return
     */
    public function lib_write_plugin_values($values, $tablename, $currentplugin) {
        $this->libcontent .= '        $values = array(';
        // $this->libcontent .= implode(',', $values);
        $this->libcontent .= $this->wrap_line($values, 24);
        $this->libcontent .= ');'."\n";
        $this->libcontent .= '        $itemid = $DB->insert_record(\''.$tablename.'\', array_combine($'.$currentplugin.'_fields, $values));'."\n";
        $this->libcontent .= "    //---------- end of this item\n\n";
    }

    /*
     * collect_strings
     *
     * @param $currentsid
     * @param $values
     * @return
     */
    public function collect_strings($currentsid, $values) {
        foreach ($currentsid as $singlesid) {
            $field = substr($singlesid, 0, -4);
            $stringindex = sprintf('%02d', 1+count($this->langtree[$field]));
            $this->langtree[$field][$field.$stringindex] = str_replace("\r", '', $values->{$field});
        }
    }

    /*
     * extract_original_string
     *
     * @param
     * @return
     */
    public function extract_original_string() {
        $stringsastext = array();
        foreach ($this->langtree as $langbranch) {
            foreach ($langbranch as $k => $stringcontent) {
                $stringsastext[] = '$string[\''.$k.'\'] = \''.addslashes($stringcontent).'\';';
            }
        }

        return "\n".implode("\n", $stringsastext);
    }

    /*
     * get_translated_strings
     *
     * @param $userlang
     * @return
     */
    public function get_translated_strings($userlang) {
        $stringsastext = array();
        $a = new stdClass();
        $a->userlang = $userlang;
        foreach ($this->langtree as $langbranch) {
            foreach ($langbranch as $k => $stringcontent) {
                $a->stringindex = $k;
                $stringsastext[] = get_string('translatedstring', 'survey', $a);
            }
        }
        return "\n".implode("\n", $stringsastext);
    }

    /*
     * wrap_line
     *
     * @param $values
     * @param $lineindent
     * @return
     */
    public function wrap_line($values, $lineindent=20) {
        $return = '';
        $segments = array_chunk($values, 4);
        $countsegments = count($segments)-1;
        foreach ($segments as $k => $segment) {
            $return .= implode(',', $segment);
            if ($k < $countsegments) {
                $return .= ",\n".str_repeat(' ', $lineindent);
            }
        }

        return $return;
    }

    /*
     * apply_mtemplate
     *
     * @param
     * @return null
     */
    public function apply_mtemplate() {
        global $DB;

        $dbman = $DB->get_manager();

        switch ($this->formdata->actionoverother) {
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
                        $pluginonly['plugin'] = $pluginseed->plugin;
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

        $this->mtemplatename = $this->formdata->mastertemplate;

        // BEGIN: add records from survey plugin
        $this->add_items_from_plugin();
        // END: add records from survey plugin
    }

    /*
     * add_items_from_plugin
     *
     * @param
     * @return
     */
    public function add_items_from_plugin() {
        global $DB;

        $dbman = $DB->get_manager();

        if ($itemseeds = $DB->get_recordset('survey_item', array('surveyid' => 0, 'externalname' => $this->mtemplatename), 'id', 'id, plugin')) {
            $sortindexoffset = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $this->survey->id));
            foreach ($itemseeds as $itemseed) {
                $plugintable = 'survey_'.$itemseed->plugin;
                if ($dbman->table_exists($plugintable)) {
                    $sql = 'SELECT *
                            FROM {survey_item} si
                                JOIN {'.$plugintable.'} plugin ON plugin.itemid = si.id
                            WHERE si.surveyid = 0
                                AND si.id = :surveyitemid
                                AND si.externalname = :externalname';
                } else {
                    $sql = 'SELECT *
                            FROM {survey_item} si
                            WHERE si.surveyid = 0
                                AND si.id = :surveyitemid
                                AND si.externalname = :externalname';
                }
                $record = $DB->get_record_sql($sql, array('surveyitemid' => $itemseed->id, 'externalname' => $this->mtemplatename));

                unset($record->id);
                $record->surveyid = $this->survey->id;
                $record->sortindex += $sortindexoffset;
                // recalculate parentid that is still pointing to the record with surveyid = 0
                if (!empty($record->parentid)) {
                    // in the atabase, records of plugins (the ones with surveyid = 0) store sortorder in the parentid field. This for portability reasons.
                    $newsortindex = $record->parentid + $sortindexoffset;
                    $sqlparams = array('surveyid' => $this->survey->id, 'externalname' => $this->mtemplatename, 'sortindex' => $newsortindex);
                    $record->parentid = $DB->get_field('survey_item', 'id', $sqlparams, MUST_EXIST);
                }

                // survey_item
                $record->itemid = $DB->insert_record('survey_item', $record);

                // $plugintable
                if ($dbman->table_exists($plugintable)) {
                    $DB->insert_record($plugintable, $record, false);
                }
            }
            $itemseeds->close();
        }
    }
}