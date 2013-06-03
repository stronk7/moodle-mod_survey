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
     * $survey: the record of this survey
     */
    public $survey = null;

    /*
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
     * survey_export
     * @param
     * @return
     */
    function survey_export() {
        global $CFG, $DB, $PAGE;

        $cm = $PAGE->cm;

        $params = array();
        $params['surveyid'] = $this->survey->id;

        // only fields
        // no matter for the page
        // elenco dei campi che l'utente vuole vedere nel file esportato
        $itemlistsql = 'SELECT si.id, si.fieldname, si.plugin
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

    // echo '$fieldidlist:';
    // var_dump($fieldidlist);
    // die;

        $richsubmissionssql = 'SELECT s.id, s.status, s.timecreated, s.timemodified, ';
        if (empty($this->survey->anonymous)) {
            $richsubmissionssql .= 'u.id as userid, u.firstname,  u.lastname, ';
        }
        $richsubmissionssql .= 'ud.id as userdataid, ud.itemid, ud.content,
                si.sortindex, si.fieldname, si.plugin
            FROM {survey_submissions} s
                INNER JOIN {user} u ON s.userid = u.id
                INNER JOIN {survey_userdata} ud ON ud.submissionid = s.id
                INNER JOIN {survey_item} si ON si.id = ud.itemid
            WHERE s.surveyid = :surveyid
                AND si.id IN ('.implode(',', array_keys($fieldidlist)).')';
        if ($this->formdata->basicform == SURVEY_FILLONLY) {
            $richsubmissionssql .= ' AND si.basicform <> '.SURVEY_NOTPRESENT;
        }
        if ($this->formdata->status != SURVEY_STATUSALL) {
            $richsubmissionssql .= ' AND s.status = :status';
            $params['status'] = $this->formdata->status;
        }
        $richsubmissionssql .= ' ORDER BY s.id ASC, s.timecreated ASC, si.sortindex ASC';

        $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $params);
        if ($richsubmissions->valid()) {
            if ($this->formdata->downloadtype == SURVEY_DOWNLOADCSV) {
                header('Content-Transfer-Encoding: utf-8');
                header('Content-Disposition: attachment; filename='.str_replace(' ', '_', $this->survey->name).'.csv');
                header('Content-Type: text/comma-separated-values');

                $worksheet = null;
            } else { // SURVEY_DOWNLOADXLS
                require_once($CFG->libdir.'/excellib.class.php');
                $filename = str_replace(' ', '_', $this->survey->name).'.xls';
                $workbook = new MoodleExcelWorkbook('-');
                $workbook->send($filename);

                $worksheet = array();
                $worksheet[0] = $workbook->add_worksheet(get_string('survey', 'survey'));
            }

            survey_export_print_header($this->survey, $fieldidlist, $this->formdata, $worksheet);

            // reduce the weight of $fieldidlist storing no longer relevant infos
            $fieldidlistkeys = array_keys($fieldidlist);
            $notsetstring = get_string('notanswereditem', 'survey');
            $placeholders = array_fill_keys($fieldidlistkeys, $notsetstring);

            // echo '$placeholders:';
            // var_dump($placeholders);

            // get user group (to filter survey to download)
            $mygroups = survey_get_my_groups($cm);
            $canreadallsubmissions = survey_user_can_read_all_submissions($cm);

            $oldrichsubmissionid = 0;

            foreach ($richsubmissions as $richsubmission) {
                if (!$canreadallsubmissions && !survey_i_can_read($this->survey, $mygroups, $richsubmission->userid)) {
                    continue;
                }

                if ($oldrichsubmissionid == $richsubmission->id) {
                    $recordtoexport[$richsubmission->itemid] = survey_decode_content($richsubmission);
                } else {
                    if (!empty($oldrichsubmissionid)) { // new richsubmissionid, stop managing old record
                        // write old record
                        survey_export_close_record($recordtoexport, $this->formdata->downloadtype, $worksheet);
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
                    $recordtoexport[$richsubmission->itemid] = survey_decode_content($richsubmission);
                }
            }
            $richsubmissions->close();
            survey_export_close_record($recordtoexport, $this->formdata->downloadtype, $worksheet);

            if ($this->formdata->downloadtype == SURVEY_DOWNLOADXLS) {
                $workbook->close();
            }
        } else {
            return SURVEY_NORECORDSFOUND;
        }
    }
}