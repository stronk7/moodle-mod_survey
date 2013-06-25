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
class mod_survey_exportmanager {
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
     * $canmanageallsubmissions
     */
    public $canmanageallsubmissions = false;

    /********************** this will be provided later
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;


    /*
     * Class constructor
     */
    public function __construct($cm, $survey) {
        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);
        $this->survey = $survey;
        $this->canmanageallsubmissions = has_capability('mod/survey:manageallsubmissions', $this->context, null, true);
    }

    /*
     * survey_export
     * @param
     * @return
     */
    public function survey_export() {
        global $CFG, $DB;

        $params = array();
        $params['surveyid'] = $this->survey->id;

        // do I need to filter groups?
        $filtergroups = survey_need_group_filtering($this->cm, $this->context);

        // ////////////////////////////
        // get the field list
        //     no matter for the page
        $itemlistsql = 'SELECT si.id, si.variable, si.plugin
                        FROM {survey_item} si
                        WHERE si.surveyid = :surveyid
                            AND si.type = "'.SURVEY_TYPEFIELD.'"'; // <-- ONLY FIELDS hold data, COLELCTION_FORMAT items do not hold data
        if ($this->formdata->basicform == SURVEY_FILLONLY) {
            // I need records with:
            //     basicform == SURVEY_FILLONLY OR basicform == SURVEY_FILLANDSEARCH
            $itemlistsql .= ' AND si.basicform <> '.SURVEY_NOTPRESENT;
        }
        if (!isset($this->formdata->includehide)) {
            $itemlistsql .= ' AND si.hide = 0';
        }
        $itemlistsql .= ' ORDER BY si.sortindex';

        // I need get_records_sql instead of get_records because of '<> SURVEY_NOTPRESENT'
        if (!$fieldidlist = $DB->get_records_sql($itemlistsql, $params)) {
            return SURVEY_NOFIELDSSELECTED;
            die;
        }
        // end of: get the field list
        // ////////////////////////////

        if ($filtergroups) {
            $grouprow = array();
            $andgroup = ' AND (';
            foreach ($mygroups as $mygroup) {
                $grouprow[] = '(gm.groupid = '.$mygroup.')';
            }
            $andgroup .= implode(' OR ', $grouprow);
            $andgroup .= ') ';
        }

        // ////////////////////////////
        // write the query
        $richsubmissionssql = 'SELECT s.id, s.status, s.timecreated, s.timemodified, ';
        if (empty($this->survey->anonymous)) {
            $richsubmissionssql .= 'u.id as userid, u.firstname,  u.lastname, ';
        }
        $richsubmissionssql .= 'ud.id as userdataid, ud.itemid, ud.content,
                                si.sortindex, si.variable, si.plugin
                            FROM {survey_submissions} s
                                INNER JOIN {user} u ON u.id = s.userid
                                INNER JOIN {survey_userdata} ud ON ud.submissionid = s.id
                                INNER JOIN {survey_item} si ON si.id = ud.itemid
                            WHERE s.surveyid = :surveyid';
        if ($this->formdata->basicform == SURVEY_FILLONLY) {
            // I need records with:
            //     basicform == SURVEY_FILLONLY OR basicform == SURVEY_FILLANDSEARCH
            $richsubmissionssql .= ' AND si.basicform <> '.SURVEY_NOTPRESENT;
        }
        if (!isset($this->formdata->includehidden)) {
            $richsubmissionssql .= ' AND si.hide = 0';
        }
        if ($this->formdata->status != SURVEY_STATUSALL) {
            $richsubmissionssql .= ' AND s.status = :status';
            $params['status'] = $this->formdata->status;
        }
        if ($filtergroups) {
            $richsubmissionssql .= $andgroup;
        }
        $richsubmissionssql .= ' AND si.type = "'.SURVEY_TYPEFIELD.'" ';
        $richsubmissionssql .= ' ORDER BY s.id ASC, si.sortindex ASC';
        // end of: write the query
        // ////////////////////////////

        $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $params);
        if ($richsubmissions->valid()) {
            if ($this->formdata->downloadtype == SURVEY_DOWNLOADXLS) {
                require_once($CFG->libdir.'/excellib.class.php');
                $filename = str_replace(' ', '_', $this->survey->name).'.xls';
                $workbook = new MoodleExcelWorkbook('-');
                $workbook->send($filename);

                $worksheet = array();
                $worksheet[0] = $workbook->add_worksheet(get_string('survey', 'survey'));
            } else { // SURVEY_DOWNLOADCSV or SURVEY_DOWNLOADCTV
                header('Content-Transfer-Encoding: utf-8');
                header('Content-Disposition: attachment; filename='.str_replace(' ', '_', $this->survey->name).'.csv');
                header('Content-Type: text/comma-separated-values');

                $worksheet = null;
            }

            $this->export_print_header($fieldidlist, $worksheet);

            // reduce the weight of $fieldidlist storing no longer relevant infos
            $fieldidlistkeys = array_keys($fieldidlist);
            $notsetstring = get_string('notanswereditem', 'survey');
            $placeholders = array_fill_keys($fieldidlistkeys, $notsetstring);

            // echo '$placeholders:';
            // var_dump($placeholders);

            // get user group (to filter survey to download)
            $mygroups = survey_get_my_groups($this->cm);

            $oldrichsubmissionid = 0;

            foreach ($richsubmissions as $richsubmission) {
                if (!$this->canmanageallsubmissions && !has_extrapermission('read', $this->survey, $mygroups, $richsubmission->userid)) {
                    continue;
                }

                if ($oldrichsubmissionid == $richsubmission->id) {
                    $recordtoexport[$richsubmission->itemid] = $this->decode_content($richsubmission);
                } else {
                    if (!empty($oldrichsubmissionid)) { // new richsubmissionid, stop managing old record
                        // write old record
                        $this->export_close_record($recordtoexport, $worksheet);
                    }
                    $oldrichsubmissionid = $richsubmission->id;

                    // begin a new record
                    $recordtoexport = array();
                    if (empty($this->survey->anonymous)) {
                        $recordtoexport['firstname'] = $richsubmission->firstname;
                        $recordtoexport['lastname'] = $richsubmission->lastname;
                    }
                    // I add to my almost empy associative array a dummy array of empty values.
                    // I do this only to fix the order of elements in the array.
                    $recordtoexport += $placeholders;

                    $recordtoexport['timecreated'] = userdate($richsubmission->timecreated);
                    $recordtoexport['timemodified'] = userdate($richsubmission->timemodified);
                    $recordtoexport[$richsubmission->itemid] = $this->decode_content($richsubmission);
                }
            }
            $richsubmissions->close();
            $this->export_close_record($recordtoexport, $worksheet);

            if ($this->formdata->downloadtype == SURVEY_DOWNLOADXLS) {
                $workbook->close();
            }
        } else {
            return SURVEY_NORECORDSFOUND;
        }
    }

    /*
     * export_print_header
     * @param $fieldidlist, $worksheet
     * @return
     */
    public function export_print_header($fieldidlist, $worksheet) {
        // write the names of the fields in the header of the file to export
        $recordtoexport = array();
        if (empty($this->survey->anonymous)) {
            $recordtoexport[] = get_string('firstname');
            $recordtoexport[] = get_string('lastname');
        }
        foreach ($fieldidlist as $singlefield) {
            $recordtoexport[] = empty($singlefield->variable) ? $singlefield->plugin.'_'.$singlefield->id : $singlefield->variable;
        }
        $recordtoexport[] = get_string('timecreated', 'survey');
        $recordtoexport[] = get_string('timemodified', 'survey');

        if ($this->formdata->downloadtype == SURVEY_DOWNLOADXLS) {
            $col = 0;
            foreach ($recordtoexport as $header) {
                $worksheet[0]->write(0, $col, $header, '');
                $col++;
            }
        } else { // SURVEY_DOWNLOADCSV or SURVEY_DOWNLOADTSV
            $separator = ($this->formdata->downloadtype == SURVEY_DOWNLOADCSV) ? ',' : "\t";
            echo implode($separator, $recordtoexport)."\n";
        }
    }

    /*
     * export_close_record
     * @param $recordtoexport, $worksheet
     * @return
     */
    public function export_close_record($recordtoexport, $worksheet) {
        static $row = 0;

        if ($this->formdata->downloadtype == SURVEY_DOWNLOADXLS) {
            $row++;
            $col = 0;
            foreach ($recordtoexport as $value) {
                $worksheet[0]->write($row, $col, $value, '');
                $col++;
            }
        } else { // SURVEY_DOWNLOADCSV or SURVEY_DOWNLOADTSV
            $separator = ($this->formdata->downloadtype == SURVEY_DOWNLOADCSV) ? ',' : "\t";
            echo implode($separator, $recordtoexport)."\n";
        }
    }

    /*
     * decode_content
     * @param $richsubmission
     * @return
     */
    public function decode_content($richsubmission) {
        global $CFG;

        $plugin = $richsubmission->plugin;
        $itemid = $richsubmission->itemid;
        $content = $richsubmission->content;
        $item = survey_get_item($itemid, SURVEY_TYPEFIELD, $plugin);

        $return = isset($content) ? $item->userform_db_to_export($richsubmission) : '';

        return $return;
    }
}