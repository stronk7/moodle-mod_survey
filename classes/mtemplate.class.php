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

require_once($CFG->dirroot.'/mod/survey/classes/templatebase.class.php');

class mod_survey_mastertemplate extends mod_survey_templatebase {
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

        // I need to get xml content now because, to save time, I get xml AND $this->langtree contemporary
        $xmlcontent = $this->build_xml();

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
                // simply copy icon.gif
                copy($master_basepath.'/'.$master_file, $temp_fullpath.'/'.$master_fileinfo['basename']);
                continue;
            }

            if ($master_fileinfo['basename'] == 'template.class.php') {
                $templateclass = file_get_contents($master_basepath.'/'.$master_file);
                // replace surveyTemplatePluginMaster with the name of the current survey
                $templateclass = str_replace(SURVEYMTEMPLATE_NAMEPLACEHOLDER, $pluginname, $templateclass);

                $temp_path = $CFG->tempdir.'/'.$temp_subdir.'/'.$master_fileinfo['basename'];

                // create $temp_path
                $filehandler = fopen($temp_path, 'w');
                // write inside all the strings
                fwrite($filehandler, $templateclass);
                // close
                fclose($filehandler);
                continue;
            }

            if ($master_fileinfo['basename'] == 'template.xml') {
                $temp_path = $CFG->tempdir.'/'.$temp_subdir.'/'.$master_fileinfo['basename'];

                // create $temp_path
                $filehandler = fopen($temp_path, 'w');
                // write inside all the strings
                fwrite($filehandler, $xmlcontent);
                // close
                fclose($filehandler);
                continue;
            }

            if ($master_fileinfo['dirname'] == 'lang/en') {
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

            // for all the other files: version.php
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
            'template.xml',
            'pix/icon.gif',
            'template.class.php',
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

        $dirnames = array('pix/', 'lang/en/');
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
     * get_used_plugin
     *
     * @param
     * @return
     */
    public function get_used_plugin() {
        global $DB;

        // STEP 01: make a list of used plugins
        $sql = 'SELECT si.plugin
                FROM {survey_item} si
                WHERE si.surveyid = :surveyid
                GROUP BY si.plugin';
        $params = array('surveyid' => $this->survey->id);
        $templateplugins = $DB->get_records_sql($sql, $params);

        // STEP 02: add, at top of $templateplugins, the fictitious 'item' plugin
        $base = new stdClass();
        $base->plugin = 'item';
        return array_merge(array('item' => $base), $templateplugins);
    }

    /*
     * get_used_plugin
     *
     * @param
     * @return
     */
    public function get_structures_sid_plugin($templateplugins) {
        $structures = array();
        $tablesid = array();
        foreach ($templateplugins as $templateplugin) {
            $tablename = 'survey_'.$templateplugin->plugin;
            if ($structure = $this->get_table_structure($tablename)) {
                $structures[$tablename] = $structure;

                // if there is a field ending in '_sid' create the line initializing the index
                $currenttablesid = array();
                foreach ($structure as $field) {
                    if (substr($field, -4) == '_sid') {
                        $currenttablesid[] = $field;
                        $field = substr($field, 0, -4);
                        $this->langtree[$templateplugin->plugin.'_'.$field] = array();
                    }
                }

                $tablesid[$tablename] = $currenttablesid;
            }
        }

        return array($structures, $tablesid);
    }


    /*
     * create_mtemplate
     *
     * @param
     * @return
     */
    public function build_xml() {
        global $DB;

        // preliminary steps
        $templateplugins = $this->get_used_plugin();

// echo '$templateplugins:';
// var_dump($templateplugins);

        // STEP 03n: create survey_$plugin table structure array
        list($structures, $tablesid) = $this->get_structures_sid_plugin($templateplugins);

// echo '$structures:';
// var_dump($structures);

// echo '$tablesid:';
// var_dump($tablesid);

        // STEP 04n: make a list of all itemseeds
        $sql = 'SELECT si.id, si.type, si.plugin
                FROM {survey_item} si
                WHERE si.surveyid = :surveyid
                ORDER BY si.sortindex';
        $params = array('surveyid' => $this->survey->id);
        $itemseeds = $DB->get_records_sql($sql, $params);

        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

            // get values to write xml
            $values = $item->item_get_si_values($this->formdata, $structures['survey_item'], $tablesid['survey_item']);

            // build lang tree for lang file
            $this->build_langtree('item', $tablesid['survey_item'], $item);

            if ($item->get_useplugintable()) { // only page break does not use the plugin table
                $tablename = 'survey_'.$itemseed->plugin;
                $currentsid = $tablesid[$tablename];
                $currentstructure = $structures[$tablename];

                // get values to write xml
                $values = $item->item_get_plugin_values($currentstructure, $currentsid);

                // build lang tree for lang file
                $this->build_langtree($itemseed->plugin, $tablesid[$tablename], $item);
            }
        }

        // echo '$this->langtree:';
        // var_dump($this->langtree);

        // return XML content
        return $this->write_template_content(SURVEY_MASTERTEMPLATE);
    }

    /*
     * build_langtree
     *
     * @param $currentsid
     * @param $values
     * @return
     */
    public function build_langtree($plugin, $currentsid, $item) {
        foreach ($currentsid as $singlesid) {
            $field = substr($singlesid, 0, -4);
            $frankenstinname = $plugin.'_'.$field;
            $stringindex = sprintf('%02d', 1+count($this->langtree[$frankenstinname]));
            $this->langtree[$frankenstinname][$frankenstinname.'_'.$stringindex] = str_replace("\r", '', $item->item_get_generic_field($field));
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
}