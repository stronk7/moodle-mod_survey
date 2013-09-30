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

define('SURVEYTEMPLATE_NAMEPLACEHOLDER', '@@templateNamePlaceholder@@');

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
     * $mtemplatename: name of the master template to work with
     */
    public $mtemplatename = '';

    /*
     * Class constructor
     */
    public function __construct($survey) {
        parent::__construct($survey);
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
        $pluginname = str_replace(' ', '_', $pluginname);
        $tempsubdir = "mod_survey/surveyplugins/$pluginname";
        $tempbasedir = $CFG->tempdir.'/'.$tempsubdir;

        $masterbasepath = "$CFG->dirroot/mod/survey/templatemaster";
        $masterfilelist = get_directory_list($masterbasepath);

        // I need to get xml content now because, to save time, I get xml AND $this->langtree contemporary
        $xmlcontent = $this->write_template_content(SURVEY_MASTERTEMPLATE);

        foreach ($masterfilelist as $masterfile) {
            $masterfileinfo = pathinfo($masterfile);
            // create the structure of the temporary folder
            // the folder has to be created WITHOUT $CFG->tempdir/
            $temppath = $tempsubdir.'/'.dirname($masterfile);
            make_temp_directory($temppath); // <-- just created the folder for the current plugin

            $tempfullpath = $CFG->tempdir.'/'.$temppath;

            // echo '<hr />Operate on the file: '.$masterfile.'<br />';
            // echo $masterfileinfo["dirname"] . "<br />";
            // echo $masterfileinfo["basename"] . "<br />";
            // echo $masterfileinfo["extension"] . "<br />";
            // echo dirname($masterfile) . "<br />";

            if ($masterfileinfo['basename'] == 'icon.gif') {
                // simply copy icon.gif
                copy($masterbasepath.'/'.$masterfile, $tempfullpath.'/'.$masterfileinfo['basename']);
                continue;
            }

            if ($masterfileinfo['basename'] == 'template.class.php') {
                $templateclass = file_get_contents($masterbasepath.'/'.$masterfile);

                // replace surveyTemplatePluginMaster with the name of the current survey
                $templateclass = str_replace(SURVEYTEMPLATE_NAMEPLACEHOLDER, $pluginname, $templateclass);

                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/'.$masterfileinfo['basename'];

                // create $temppath
                $filehandler = fopen($temppath, 'w');
                // write inside all the strings
                fwrite($filehandler, $templateclass);
                // close
                fclose($filehandler);
                continue;
            }

            if ($masterfileinfo['basename'] == 'template.xml') {
                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/'.$masterfileinfo['basename'];

                // create $temppath
                $filehandler = fopen($temppath, 'w');
                // write inside all the strings
                fwrite($filehandler, $xmlcontent);
                // close
                fclose($filehandler);
                continue;
            }

            if ($masterfileinfo['dirname'] == 'lang/en') {
                // in which language the user is using Moodle?
                $userlang = current_language();
                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/lang/'.$userlang;

                // this is the language folder of the strings hardcoded in the survey
                // the folder lang/en already exist
                if ($userlang != 'en') {
                    // I need to create the folder lang/it
                    make_temp_directory($tempsubdir.'/lang/'.$userlang);
                }

                // echo '$masterbasepath = '.$masterbasepath.'<br />';

                $filecopyright = file_get_contents($masterbasepath.'/lang/en/surveytemplate_pluginname.php');
                // replace surveyTemplatePluginMaster with the name of the current survey
                $filecopyright = str_replace(SURVEYTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecopyright);

                $savedstrings = $filecopyright.$this->extract_original_string();
                // echo '<textarea rows="30" cols="100">'.$savedstrings.'</textarea>';
                // die();

                // create - this could be 'en' such as 'it'
                $filehandler = fopen($temppath.'/surveytemplate_'.$pluginname.'.php', 'w');
                // write inside all the strings
                fwrite($filehandler, $savedstrings);
                // close
                fclose($filehandler);

                // this is the folder of the language en in case the user language is different from en
                if ($userlang != 'en') {
                    $temppath = $CFG->tempdir.'/'.$tempsubdir.'/lang/en';
                    // create
                    $filehandler = fopen($temppath.'/surveytemplate_'.$pluginname.'.php', 'w');
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
            $filecontent = file_get_contents($masterbasepath.'/'.$masterfile);
            // replace surveyTemplatePluginMaster with the name of the current survey
            $filecontent = str_replace(SURVEYTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecontent);
            if ($masterfileinfo['basename'] == 'version.php') {
                $currentdate = gmdate("Ymd").'01';
                $filecontent = str_replace('1965100401', $currentdate, $filecontent);
            }
            // open
            $filehandler = fopen($tempbasedir.'/'.$masterfile, 'w');
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
            $filelist[$filename] = $tempbasedir.'/'.$filename;
        }

        $exportfile = $tempbasedir.'.zip';
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
            rmdir($tempbasedir.'/'.$dir);
        }
        rmdir($tempbasedir);
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
        $whereparams = array('surveyid' => $this->survey->id);
        $templateplugins = $DB->get_records_sql($sql, $whereparams);

        // STEP 02: add, at top of $templateplugins, the fictitious 'item' plugin
        $base = new stdClass();
        $base->plugin = 'item';
        return array_merge(array('item' => $base), $templateplugins);
    }

    /*
     * build_langtree
     *
     * @param $currentsid
     * @param $values
     * @return
     */
    public function build_langtree($dummyplugin, $multilangfields, $item) {
        foreach ($multilangfields as $dummyplugin => $fieldnames) {
            foreach ($fieldnames as $fieldname) {
                $frankenstinname = $dummyplugin.'_'.$fieldname;
                if (isset($this->langtree[$frankenstinname])) {
                    $index = count($this->langtree[$frankenstinname]);
                } else {
                    $index = 0;
                }
                $stringindex = sprintf('%02d', 1+$index);
                $this->langtree[$frankenstinname][$frankenstinname.'_'.$stringindex] = str_replace("\r", '', $item->item_get_generic_field($fieldname));
            }
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