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

require_once($CFG->dirroot.'/mod/survey/classes/template.class.php');

class mod_survey_usertemplate extends mod_survey_template {
    /*
     * $cm
     */
    public $cm = null;

    /*
     * $context
     */
    public $context = null;

    /*
     * $survey: the record of this survey
     */
    public $survey = null;

    /*
     * $utemplateid: the ID of the current working user template
     */
    public $utemplateid = 0;

    /*
     * $confirm: is the action confirmed by the user?
     */
    public $confirm = SURVEY_UNCONFIRMED;

    /*
     * $canexportutemplates
     */
    public $canexportutemplates = false;

    /*
     * $candeleteutemplates
     */
    public $candeleteutemplates = false;

    /********************** this will be provided later
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;


    /*
     * Class constructor
     */
    public function __construct($cm, $survey, $context, $utemplateid, $action, $confirm) {
        $this->cm = $cm;
        $this->context = $context;
        $this->survey = $survey;
        $this->confirm = $confirm;
        $this->utemplateid = $utemplateid;
        $this->action = $action;
        $this->canexportutemplates = has_capability('mod/survey:exportusertemplates', $context, null, true);
        $this->candeleteutemplates = has_capability('mod/survey:deleteusertemplates', $context, null, true);
    }

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
        die;
    }

    /*
     * write_utemplate
     * @param
     * @return
     */
    public function write_utemplate() {
        global $DB;

        $sql = 'SELECT si.id, si.type, si.plugin
                FROM {survey_item} si
                WHERE si.surveyid = :surveyid
                ORDER BY si.sortindex';
        $params = array('surveyid' => $this->survey->id);
        $itemseeds = $DB->get_records_sql($sql, $params);

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
                if ($field == 'parentid') {
                    if ($item->get_parentid()) {
                        $sqlparams = array('id' => $item->get_parentid());
                        // I store sortindex instead of parentid, because at restore time parent id will change
                        $val = $DB->get_field('survey_item', 'sortindex', $sqlparams);
                    } else {
                        $val = 0;
                    }
                } else {
                    $item_field = get_generic_field($field);
                    if (is_null($item_field)) { // TODO: how can I get this?
                        $val = SURVEY_EMPTYTEMPLATEFIELD;
                    } else {
                        $val = $item_field; // TODO: how can I get this?
                    }
                }
                $xmlfield = $xmltable->addChild($field, $val);
            }

            if ($item->get_useplugintable()) { // only page break does not use the plugin table
                // child table
                $structure = $this->get_table_structure('survey_'.$plugin);

                $xmltable = $xmlitem->addChild('survey_'.$plugin);
                foreach ($structure as $field) {
                    $item_field = get_generic_field($field);
                    if (is_null($item_field)) { // TODO: how can I get this?
                        $xmlfield = $xmltable->addChild($field, SURVEY_EMPTYTEMPLATEFIELD);
                    } else {
                        $xmlfield = $xmltable->addChild($field, $item_field); // TODO: how can I get this?
                    }
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
     * survey_get_template_options
     * @param none
     * @return $filemanager_options
     */
    public function get_filemanager_options() {
        $template_options = array();
        $template_options['accepted_types'] = '.xml';
        $template_options['maxbytes'] = 0;
        $template_options['maxfiles'] = -1;
        $template_options['mainfile'] = true;
        $template_options['subdirs'] = false;

        return $template_options;
    }

    /*
     * get_contextid_from_sharinglevel
     * @param sharinglevel
     * @return $filemanager_options
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
            throw new moodle_exception('Wrong $sharinglevel passed in get_contextid_from_sharinglevel');
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

        $template_options = $this->get_filemanager_options();
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
            if (isset($template_options['return_types']) && !($template_options['return_types'] & FILE_REFERENCE)) {
                // we assume that if $options['return_types'] is NOT specified, we DO allow references.
                // this is not exactly right. BUT there are many places in code where filemanager options
                // are not passed to file_save_draft_area_files()
                $allowreferences = false;
            }

            file_save_draft_area_files($draftitemid, $contextid, 'mod_survey', 'temporaryarea', 0, $template_options);
            $files = $fs->get_area_files($contextid, 'mod_survey', 'temporaryarea');
            $filecount = 0;
            foreach ($files as $file) {
                if (in_array($file->get_filename(), $oldfiles)) {
                    continue;
                }

                $file_record = array('contextid' => $contextid, 'component' => 'mod_survey', 'filearea' => SURVEY_TEMPLATEFILEAREA, 'itemid' => 0, 'timemodified' => time());
                if (!$template_options['subdirs']) {
                    if ($file->get_filepath() !== '/' or $file->is_directory()) {
                        continue;
                    }
                }
                if ($template_options['maxbytes'] and $template_options['maxbytes'] < $file->get_filesize()) {
                    // oversized file - should not get here at all
                    continue;
                }
                if ($template_options['maxfiles'] != -1 and $template_options['maxfiles'] <= $filecount) {
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
                        $file_record['repositoryid'] = $repoid;
                        $file_record['reference'] = $file->get_reference();
                    }
                }

                $fs->create_file_from_storedfile($file_record, $file);
            }
        }

        if ($files = $fs->get_area_files($contextid, 'mod_survey', SURVEY_TEMPLATEFILEAREA, 0, 'sortorder', false)) {
            if (count($files) == 1) {
                // only one file attached, set it as main file automatically
                $file = reset($files);
                file_set_sortorder($contextid, 'mod_survey', SURVEY_TEMPLATEFILEAREA, 0, $file->get_filepath(), $file->get_filename(), 1);
            }
        }
    }

    /*
     * save_utemplate
     * @param
     * @return
     */
    public function save_utemplate() {
        global $USER;

        $xmlcontent = $this->write_utemplate();
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
     * @param
     * @return
     */
    public function manage_utemplates() {
        global $USER, $OUTPUT, $CFG;

        require_once($CFG->libdir.'/tablelib.php');

        // /////////////////////////////////////////////////
        // $paramurl definition
        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        // end of $paramurl definition
        // /////////////////////////////////////////////////

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

        foreach ($templates as $contextstring => $contextfiles) {
            foreach ($contextfiles as $xmlfile) {
                // echo '$xmlfile:';
                // var_dump($xmlfile);
                $tablerow = array();
                $tablerow[] = $xmlfile->get_filename();
                $tablerow[] = get_string($contextstring, 'survey');
                $tablerow[] = userdate($xmlfile->get_timecreated());

                $paramurl['fid'] = $xmlfile->get_id();

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
                if ($this->canexportutemplates) {
                    $paramurl['act'] = SURVEY_EXPORTUTEMPLATE;
                    $basepath = new moodle_url('utemplates_manage.php', $paramurl);

                    $icons .= '<a class="editing_update" title="'.$exporttitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('download', 'survey').'" class="iconsmall" alt="'.$exporttitle.'" title="'.$exporttitle.'" /></a>';
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
            die;
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
        global $DB, $COURSE, $USER;

        $options = array();
        $options[CONTEXT_USER.'_'.$USER->id] = get_string('user').': '.fullname($USER);

        $options[CONTEXT_MODULE.'_'.$this->cm->id] = get_string('module', 'survey').': '.$this->survey->name;

        $options[CONTEXT_COURSE.'_'.$COURSE->id] = get_string('course').': '.$COURSE->shortname;

        $categorystr = get_string('category').': ';
        $category = $DB->get_record('course_categories', array('id' => $COURSE->category), 'id, name');
        $options[CONTEXT_COURSECAT.'_'.$COURSE->category] = $categorystr.$category->name;
        while (!empty($category->parent)) {
            $category = $DB->get_record('course_categories', array('id' => $category->parent), 'id, name');
            $options[CONTEXT_COURSECAT.'_'.$category->id] = $categorystr.$category->name;
        }

        $options[CONTEXT_SYSTEM.'_0'] = get_string('site');

        return $options;
    }

    /*
     * apply_utemplate
     *
     * @param
     * @return null
     */
    public function apply_utemplate() {
        global $DB;

        $dbman = $DB->get_manager();

        if ($this->formdata->actionoverother == SURVEY_HIDEITEMS) {
            // BEGIN: hide all other items
            $DB->set_field('survey_item', 'hide', 1, array('surveyid' => $this->survey->id, 'hide' => 0));
            // END: hide all other items
        }

        if ($this->formdata->actionoverother == SURVEY_DELETEITEMS) {
            // BEGIN: delete all other items
            $sql = 'SELECT si.plugin
                    FROM {survey_item} si
                    WHERE si.surveyid = :surveyid
                    GROUP BY si.plugin';

            $pluginseeds = $DB->get_records_sql($sql, array('surveyid' => $this->survey->id));

            foreach ($pluginseeds as $pluginseed) {
                $tablename = 'survey_'.$pluginseed->plugin;
                if ($dbman->table_exists($tablename)) {
                    $DB->delete_records($tablename, array('surveyid' => $this->survey->id));
                }
            }
            $DB->delete_records('survey_item', array('surveyid' => $this->survey->id));
            // END: delete all other items

        }

        $this->utemplateid = $this->formdata->usertemplate;
        if (!empty($this->utemplateid)) { // something was selected
            // BEGIN: add records from template
            $this->add_items_from_utemplate();
            // END: add records from template
        }
    }

    /*
     * add_items_from_utemplate
     * @param $templateid
     * @return
     */
    public function add_items_from_utemplate() {
        global $DB;

        $templatecontent = $this->get_utemplate_content();

        $xmltext = simplexml_load_string($templatecontent);

        // echo '<h2>Items saved in the file ('.count($xmltext->item).')</h2>';

        $sortindexoffset = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $this->survey->id));
        foreach ($xmltext->children() as $item) {
            // echo '<h3>Count of tables for the current item: '.count($item->children()).'</h3>';
            foreach ($item->children() as $table) {
                $tablename = $table->getName();
                // echo '<h4>Count of fields of the table '.$tablename.': '.count($table->children()).'</h4>';
                $record = array();
                foreach ($table->children() as $field) {
                    $fieldname = $field->getName();
                    $fieldvalue = (string)$field;
                    // echo '<div>Table: '.$table->getName().', Field: '.$fieldname.', content: '.$field.'</div>';
                    if ($fieldvalue == SURVEY_EMPTYTEMPLATEFIELD) {
                        $record[$fieldname] = null;
                    } else {
                        $record[$fieldname] = $fieldvalue;
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

    /*
     * get_utemplate_content
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