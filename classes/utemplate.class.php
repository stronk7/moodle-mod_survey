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

require_once($CFG->dirroot.'/mod/survey/classes/templatebase.class.php');

class mod_survey_usertemplate extends mod_survey_templatebase {
    /*
     * $cm
     */
    public $cm = null;

    /*
     * $context
     */
    public $context = null;

    /*
     * $utemplateid: the ID of the current working user template
     */
    public $utemplateid = 0;

    /*
     * $confirm: is the action confirmed by the user?
     */
    public $confirm = SURVEY_UNCONFIRMED;

    /*
     * $candownloadutemplates
     */
    public $candownloadutemplates = false;

    /*
     * $candeleteutemplates
     */
    public $candeleteutemplates = false;

    /*
     * Class constructor
     */
    public function __construct($cm, $survey, $context, $utemplateid, $action, $confirm) {
        parent::__construct($survey);

        $this->cm = $cm;
        $this->context = $context;
        $this->confirm = $confirm;
        $this->utemplateid = $utemplateid;
        $this->action = $action;
        $this->candownloadutemplates = has_capability('mod/survey:downloadusertemplates', $context, null, true);
        $this->candeleteutemplates = has_capability('mod/survey:deleteusertemplates', $context, null, true);
    }

    /*
     * export_utemplate
     *
     * @param
     * @return
     */
    public function export_utemplate() {
        global $CFG;

        $fs = get_file_storage();
        $xmlfile = $fs->get_file_by_id($this->utemplateid);
        $filename = $xmlfile->get_filename();
        $content = $xmlfile->get_content();

        // echo '<textarea rows="10" cols="100">'.$content.'</textarea>';

        $templatename = clean_filename('temptemplate-' . gmdate("Ymd_Hi"));
        $exportsubdir = "mod_survey/templateexport";
        make_temp_directory($exportsubdir);
        $exportdir = "$CFG->tempdir/$exportsubdir";
        $exportfile = $exportdir.'/'.$templatename.'.xml';
        $exportfilename = basename($exportfile);

        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
        header('Pragma: public');
        $xmlfile = fopen($exportdir.'/'.$exportfilename, 'w');
        print $content;
        fclose($xmlfile);
        unlink($exportdir.'/'.$exportfilename);
        die();
    }

    /*
     * survey_get_template_options
     *
     * @param
     * @return $filemanageroptions ?????
     */
    public function get_filemanager_options() {
        $templateoptions = array();
        $templateoptions['accepted_types'] = '.xml';
        $templateoptions['maxbytes'] = 0;
        $templateoptions['maxfiles'] = -1;
        $templateoptions['mainfile'] = true;
        $templateoptions['subdirs'] = false;

        return $templateoptions;
    }

    /*
     * get_contextid_from_sharinglevel
     *
     * @param sharinglevel
     * @return $filemanageroptions ??????
     */
    public function get_contextid_from_sharinglevel($sharinglevel='') {
        if (empty($sharinglevel)) {
            $sharinglevel = $this->formdata->sharinglevel;
        }

        $parts = explode('_', $sharinglevel);
        $contextlevel = $parts[0];
        $contextid = $parts[1];

        //       $parts[0]    |   $parts[1]
        //  ----------------------------------
        //     CONTEXT_SYSTEM | 0
        //  CONTEXT_COURSECAT | $category->id
        //     CONTEXT_COURSE | $COURSE->id
        //     CONTEXT_MODULE | $cm->id
        //       CONTEXT_USER | $USER->id

        if (!isset($parts[0]) || !isset($parts[1])) {
            print_error('Wrong $sharinglevel passed in get_contextid_from_sharinglevel');
        }

        switch ($contextlevel) {
            case CONTEXT_USER:
                $context = context_user::instance($contextid);
                break;
            case CONTEXT_MODULE:
                $context = context_module::instance($contextid);
                break;
            case CONTEXT_COURSE:
                $context = context_course::instance($contextid);
                break;
            case CONTEXT_COURSECAT:
                $context = context_coursecat::instance($contextid);
                break;
            case CONTEXT_SYSTEM:
                $context = context_system::instance();
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $contextlevel = '.$contextlevel);
        }

        return $context->id;
    }

    /*
     * get_contextstring_from_sharinglevel
     *
     * @param $contextlevel
     * @return $contextstring
     */
    public function get_contextstring_from_sharinglevel($contextlevel) {
        // depending on the context level the component can be:
        // system, category, course, module, user
        switch ($contextlevel) {
            case CONTEXT_SYSTEM:
                $contextstring = 'system';
                break;
            case CONTEXT_COURSECAT:
                $contextstring = 'category';
                break;
            case CONTEXT_COURSE:
                $contextstring = 'course';
                break;
            case CONTEXT_MODULE:
                $contextstring = 'module';
                break;
            case CONTEXT_USER:
                $contextstring = 'user';
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $contextlevel = '.$contextlevel);
        }

        return $contextstring;
    }

    /*
     * upload_utemplate
     *
     * @param
     * @return null
     */
    public function upload_utemplate() {

        $templateoptions = $this->get_filemanager_options();
        $contextid = $this->get_contextid_from_sharinglevel();
        $fs = get_file_storage();

        /*
         * look at what was already on board
         */
        $oldfiles = array();
        if ($files = $fs->get_area_files($contextid, 'mod_survey', SURVEY_TEMPLATEFILEAREA, 0, 'sortorder', false)) {
            foreach ($files as $file) {
                $oldfiles[] = $file->get_filename();
            }
        }

        /*
         * add current files
         */
        $fieldname = 'importfile';
        if ($draftitemid = $this->formdata->{$fieldname.'_filemanager'}) {
            if (isset($templateoptions['return_types']) && !($templateoptions['return_types'] & FILE_REFERENCE)) {
                // we assume that if $options['return_types'] is NOT specified, we DO allow references.
                // this is not exactly right. BUT there are many places in code where filemanager options
                // are not passed to file_save_draft_area_files()
                $allowreferences = false;
            }

            file_save_draft_area_files($draftitemid, $contextid, 'mod_survey', 'temporaryarea', 0, $templateoptions);
            $files = $fs->get_area_files($contextid, 'mod_survey', 'temporaryarea');
            $filecount = 0;
            foreach ($files as $file) {
                if (in_array($file->get_filename(), $oldfiles)) {
                    continue;
                }

                $filerecord = array('contextid' => $contextid, 'component' => 'mod_survey', 'filearea' => SURVEY_TEMPLATEFILEAREA, 'itemid' => 0, 'timemodified' => time());
                if (!$templateoptions['subdirs']) {
                    if ($file->get_filepath() !== '/' or $file->is_directory()) {
                        continue;
                    }
                }
                if ($templateoptions['maxbytes'] and $templateoptions['maxbytes'] < $file->get_filesize()) {
                    // oversized file - should not get here at all
                    continue;
                }
                if ($templateoptions['maxfiles'] != -1 and $templateoptions['maxfiles'] <= $filecount) {
                    // more files - should not get here at all
                    break;
                }
                if (!$file->is_directory()) {
                    $filecount++;
                }

                if ($file->is_external_file()) {
                    if (!$allowreferences) {
                        continue;
                    }
                    $repoid = $file->get_repository_id();
                    if (!empty($repoid)) {
                        $filerecord['repositoryid'] = $repoid;
                        $filerecord['reference'] = $file->get_reference();
                    }
                }

                $fs->create_file_from_storedfile($filerecord, $file);
            }
        }

        if ($files = $fs->get_area_files($contextid, 'mod_survey', SURVEY_TEMPLATEFILEAREA, 0, 'sortorder', false)) {
            if (count($files) == 1) {
                // only one file attached, set it as main file automatically
                $file = reset($files);
                file_set_sortorder($contextid, 'mod_survey', SURVEY_TEMPLATEFILEAREA, 0, $file->get_filepath(), $file->get_filename(), 1);
            }
        }

        $this->utemplateid = $file->get_id();
    }

    /*
     * save_utemplate
     *
     * @param
     * @return
     */
    public function save_utemplate() {
        global $USER;

        $xmlcontent = $this->write_template_content(SURVEY_USERTEMPLATE);
        // echo '<textarea rows="80" cols="100">'.$xmlcontent.'</textarea>';

        $fs = get_file_storage();
        $filerecord = new stdClass;

        $contextid = $this->get_contextid_from_sharinglevel();
        $filerecord->contextid = $contextid;

        $filerecord->component = 'mod_survey';
        $filerecord->filearea = SURVEY_TEMPLATEFILEAREA;
        $filerecord->itemid = 0;
        $filerecord->filepath = '/';
        $filerecord->userid = $USER->id;

        $filerecord->filename = str_replace(' ', '_', $this->formdata->templatename).'.xml';
        $fs->create_file_from_string($filerecord, $xmlcontent);

        return true;
    }

    /*
     * manage_utemplates
     *
     * @param
     * @return
     */
    public function manage_utemplates() {
        global $USER, $OUTPUT, $CFG;

        require_once($CFG->libdir.'/tablelib.php');

        // -----------------------------
        // $paramurl definition
        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        // end of $paramurl definition
        // -----------------------------

        $table = new flexible_table('templatelist');

        $table->define_baseurl(new moodle_url('utemplates_manage.php', array('id' => $this->cm->id)));

        $tablecolumns = array();
        $tablecolumns[] = 'templatename';
        $tablecolumns[] = 'sharinglevel';
        $tablecolumns[] = 'timecreated';
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('templatename', 'survey');
        $tableheaders[] = get_string('sharinglevel', 'survey');
        $tableheaders[] = get_string('timecreated', 'survey');
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        // $table->collapsible(true);
        $table->sortable(true, 'templatename'); // sorted by sortindex by default
        $table->no_sorting('actions');

        $table->column_class('templatename', 'templatename');
        $table->column_class('sharinglevel', 'sharinglevel');
        $table->column_class('timecreated', 'timecreated');
        $table->column_class('actions', 'actions');

        // general properties for the whole table
        // $table->set_attribute('cellpadding', '5');
        $table->set_attribute('id', 'managetemplates');
        $table->set_attribute('class', 'generaltable');
        // $table->set_attribute('width', '90%');
        $table->setup();

        $applytitle = get_string('applytemplate', 'survey');
        $deletetitle = get_string('delete');
        $exporttitle = get_string('exporttemplate', 'survey');

        $options = $this->get_sharinglevel_options($this->cm->id);

        // echo '$options:';
        // var_dump($options);

        $templates = new stdClass();
        foreach ($options as $sharinglevel => $v) {
            $parts = explode('_', $sharinglevel);
            $contextlevel = $parts[0];

            $contextid = $this->get_contextid_from_sharinglevel($sharinglevel);
            $contextstring = $this->get_contextstring_from_sharinglevel($contextlevel);
            $templates->{$contextstring} = $this->get_available_templates($contextid);
        }
        // echo '$templates:';
        // var_dump($templates);

        $dummysort = $this->create_fictitious_table($templates, $table->get_sql_sort());

        $row = 0;
        foreach ($templates as $contextstring => $contextfiles) {
            foreach ($contextfiles as $xmlfile) {
                // echo '$xmlfile:';
                // var_dump($xmlfile);
                $tablerow = array();
                // $tablerow[] = $xmlfile->get_filename();
                $tablerow[] = $dummysort[$row]['templatename'];
                // $tablerow[] = get_string($contextstring, 'survey');
                $tablerow[] = $dummysort[$row]['sharinglevel'];
                // $tablerow[] = userdate($xmlfile->get_timecreated());
                $tablerow[] = userdate($dummysort[$row]['creationdate']);

                // $paramurl['fid'] = $xmlfile->get_id();
                $paramurl['fid'] = $dummysort[$row]['xmlfileid'];
                $row++;

                $icons = '';
                // *************************************** SURVEY_DELETEUTEMPLATE
                if ($this->candeleteutemplates) {
                    if ($xmlfile->get_userid() == $USER->id) { // only the owner can delete his/her template
                        $paramurl['act'] = SURVEY_DELETEUTEMPLATE;
                        $basepath = new moodle_url('utemplates_manage.php', $paramurl);

                        $icons .= '<a class="editing_update" title="'.$deletetitle.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletetitle.'" title="'.$deletetitle.'" /></a>&nbsp;';
                    }
                }

                // *************************************** SURVEY_EXPORTUTEMPLATE
                if ($this->candownloadutemplates) {
                    $paramurl['act'] = SURVEY_EXPORTUTEMPLATE;
                    $basepath = new moodle_url('utemplates_manage.php', $paramurl);

                    $icons .= '<a class="editing_update" title="'.$exporttitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('i/export').'" class="iconsmall" alt="'.$exporttitle.'" title="'.$exporttitle.'" /></a>';
                }

                $tablerow[] = $icons;

                $table->add_data($tablerow);
            }
        }
        $table->set_attribute('align', 'center');
        $table->summary = get_string('templatelist', 'survey');
        $table->print_html();
    }

    /*
     * create_fictitious_table
     *
     * @param
     * @return null
     */
    public function create_fictitious_table($templates, $usersort) {
        // original table per columns: originaltablepercols
        $templatenamecol = array();
        $sharinglevelcol = array();
        $creationdatecol = array();
        $xmlfileidcol = array();
        foreach ($templates as $contextstring => $contextfiles) {
            foreach ($contextfiles as $xmlfile) {
                $templatenamecol[] = $xmlfile->get_filename();
                $sharinglevelcol[] = get_string($contextstring, 'survey');
                $creationdatecol[] = $xmlfile->get_timecreated();
                $xmlfileidcol[] = $xmlfile->get_id();
            }
        }
        $originaltablepercols = array($templatenamecol, $sharinglevelcol, $creationdatecol, $xmlfileidcol);

        // original table per rows: originaltableperrows
        $originaltableperrows = array();
        foreach ($templatenamecol as $k => $value) {
            $tablerow = array();
            $tablerow['templatename'] = $templatenamecol[$k];
            $tablerow['sharinglevel'] = $sharinglevelcol[$k];
            $tablerow['creationdate'] = $creationdatecol[$k];
            $tablerow['xmlfileid'] = $xmlfileidcol[$k];

            $originaltableperrows[] = $tablerow;
        }

        // $usersort
        $orderparts = explode(', ', $usersort);
        $orderparts = str_replace('templatename', '0', $orderparts);
        $orderparts = str_replace('sharinglevel', '1', $orderparts);
        $orderparts = str_replace('timecreated', '2', $orderparts);

        // $fieldindex and $sortflag
        $fieldindex = array(0, 0, 0);
        $sortflag = array(SORT_ASC, SORT_ASC, SORT_ASC);
        foreach ($orderparts as $k => $orderpart) {
            $pair = explode(' ', $orderpart);
            $fieldindex[$k] = (int)$pair[0];
            $sortflag[$k] = ($pair[1] == 'ASC') ? SORT_ASC : SORT_DESC;
        }

        array_multisort($originaltablepercols[$fieldindex[0]], $sortflag[0],
                        $originaltablepercols[$fieldindex[1]], $sortflag[1],
                        $originaltablepercols[$fieldindex[2]], $sortflag[2], $originaltableperrows);

        return $originaltableperrows;
    }

    /*
     * delete_utemplate
     *
     * @param
     * @return null
     */
    public function delete_utemplate() {
        global $OUTPUT, $PAGE;

        if ($this->action != SURVEY_DELETEUTEMPLATE) {
            return;
        }
        if ($this->confirm == SURVEY_UNCONFIRMED) {
            // ask for confirmation
            $a = $this->get_utemplate_name();
            $message = get_string('askdeleteonetemplate', 'survey', $a);
            $optionsbase = array('s' => $this->survey->id, 'act' => SURVEY_DELETEUTEMPLATE);

            $optionsyes = $optionsbase + array('cnf' => SURVEY_CONFIRMED_YES, 'fid' => $this->utemplateid);
            $urlyes = new moodle_url('utemplates_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('yes'));

            $optionsno = $optionsbase + array('cnf' => SURVEY_CONFIRMED_NO);
            $urlno = new moodle_url('utemplates_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    $fs = get_file_storage();
                    $xmlfile = $fs->get_file_by_id($this->utemplateid);
                    $xmlfile->delete();
                    break;
                case SURVEY_CONFIRMED_NO:
                    $message = get_string('usercanceled', 'survey');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm);
            }
        }
    }

    /*
     * get_sharinglevel_options
     *
     * @param
     * @return null
     */
    public function get_sharinglevel_options() {
        global $DB, $COURSE, $USER, $SITE;

        $modulecontext = context_module::instance($this->cm->id);

        $options = array();
        $options[CONTEXT_USER.'_'.$USER->id] = get_string('user').': '.fullname($USER);
        // $options[CONTEXT_MODULE.'_'.$this->cm->id] = get_string('module', 'survey').': '.$this->survey->name;

        $parentcontexts = $modulecontext->get_parent_contexts();
        foreach ($parentcontexts as $context) {
            if (has_capability('mod/survey:saveusertemplates', $context)) {
                $options[$context->contextlevel.'_'.$context->instanceid] = $context->get_context_name();
            }
        }

        $context = context_system::instance();
        if (has_capability('mod/survey:saveusertemplates', $context)) {
            $options[CONTEXT_SYSTEM.'_0'] = get_string('site');
        }

        // $context = context_coursecat::instance($COURSE->category);
        // $canmanagecat = has_capability('moodle/category:manage', $context);
        // $cansavetocategotylevel = has_capability('mod/survey:saveusertemplates', $context);
        //
        // $options = array();
        // $options[CONTEXT_USER.'_'.$USER->id] = get_string('user').': '.fullname($USER);
        //
        // $options[CONTEXT_MODULE.'_'.$this->cm->id] = get_string('module', 'survey').': '.$this->survey->name;
        //
        // if ($COURSE->id != $SITE->id) { // I am not in homepage
        //     $options[CONTEXT_COURSE.'_'.$COURSE->id] = get_string('course').': '.$COURSE->shortname;
        //
        //     if ($canmanagecat && $cansavetocategotylevel) { // is more than a teacher, is an admin
        //         $categorystr = get_string('category').': ';
        //         $category = $DB->get_record('course_categories', array('id' => $COURSE->category), 'id, name');
        //         $options[CONTEXT_COURSECAT.'_'.$COURSE->category] = $categorystr.$category->name;
        //
        //         while (!empty($category->parent)) {
        //             $category = $DB->get_record('course_categories', array('id' => $category->parent), 'id, name');
        //             $options[CONTEXT_COURSECAT.'_'.$category->id] = $categorystr.$category->name;
        //         }
        //     }
        // }
        // if ($canmanagecat && $cansavetocategotylevel) {
        //     $options[CONTEXT_SYSTEM.'_0'] = get_string('site');
        // }

        return $options;
    }

    /*
     * get_utemplate_content
     *
     * @param
     * @return
     */
    public function get_utemplate_content($utemplateid=0) {
        $fs = get_file_storage();
        $utemplateid = ($utemplateid) ? $utemplateid : $this->utemplateid;
        $xmlfile = $fs->get_file_by_id($utemplateid);

        return $xmlfile->get_content();
    }

    /*
     * get_utemplate_name
     *
     * @param
     * @return
     */
    public function get_utemplate_name() {
        $fs = get_file_storage();
        $xmlfile = $fs->get_file_by_id($this->utemplateid);

        return $xmlfile->get_filename();
    }

    /*
     * Gets an array of all of the templates that users have saved to the site.
     *
     * @param stdClass $context The context that we are looking from.
     * @param array $templates
     * @return array An array of templates
     */
    public function get_available_templates($contextid) {

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'mod_survey', SURVEY_TEMPLATEFILEAREA, 0, 'sortorder', false);
        if (empty($files)) {
            return array();
        }

        $templates = array();
        foreach ($files as $file) {
            $templates[] = $file;
        }

        return $templates;
    }
}
