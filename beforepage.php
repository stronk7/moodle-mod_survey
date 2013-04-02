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

defined('MOODLE_INTERNAL') OR die();

$defaulttab = ($canmanageitems) ? SURVEY_TABITEMS : SURVEY_TABSUBMISSIONS;

$currenttab = optional_param('tab', $defaulttab, PARAM_INT);
switch ($currenttab) {
    case SURVEY_TABSUBMISSIONS:
        $defaultpage = SURVEY_SUBMISSION_NEW;
        break;
    case SURVEY_TABITEMS:
        $itemcount = $DB->count_records('survey_item', array('surveyid' => $survey->id));
        $defaultpage = ($itemcount) ? SURVEY_ITEMS_MANAGE : SURVEY_ITEMS_ADD;
        break;
    case SURVEY_TABTEMPLATES:
        $defaultpage = SURVEY_TEMPLATES_BUILD;
        break;
    case SURVEY_TABPLUGINS:
        $defaultpage = SURVEY_PLUGINS_BUILD;
        break;
    default:
        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $currenttab = '.$currenttab);
}
$currentpage = optional_param('pag', $defaultpage, PARAM_INT);

// ////////////////////////////////////////////////////////////////////////////////////
// here I manage all the redirection forced before the beginning of the output ($PAGE)
// ////////////////////////////////////////////////////////////////////////////////////

switch ($currenttab) {
    case SURVEY_TABSUBMISSIONS:
        $formpage = optional_param('formpage' , 1, PARAM_INT); // form page number
        $submissionid = optional_param('submissionid', 0, PARAM_INT);
        $confirm = optional_param('cnf' , 0, PARAM_INT); // confirm submission deletion

        switch ($currentpage) {
            case SURVEY_SUBMISSION_NEW: // new
            case SURVEY_SUBMISSION_EDIT: // edit
            case SURVEY_SUBMISSION_READONLY: // read only
                survey_add_custom_css($survey->id, $cm->id);

                // whether it comes from the form or from the redirect in GET, $submissionid is fetched here
                // if the form (once submitted) send $submissionid == 0, the value will be overwritten later in if ($fromform = $mform->get_data()) {

                // ////////////////////////////
                // group items per basicform/advancedform
                $lastformpage = survey_assign_pages($canaccessadvancedform);
                // end of: group items per basicform/advancedform whether needed
                // ////////////////////////////

                // ////////////////////////////
                // prepare params for the form
                $formparams = new stdClass();
                $formparams->cmid = $cm->id;
                $formparams->survey = $survey;
                $formparams->submissionid = $submissionid;
                $formparams->lastformpage = $lastformpage;
                $formparams->canaccessadvancedform = $canaccessadvancedform; // Help selecting the fields to show
                $formparams->formpage = $formpage;
                $formparams->currentpage = $currentpage;

                require_once($CFG->dirroot.'/mod/survey/pages/submissions/attempt_form.php');
                $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => $currentpage);
                $formurl = new moodle_url('view.php', $paramurl);
                if ($currentpage == SURVEY_SUBMISSION_READONLY) {
                    $mform = new survey_submissionform($formurl, $formparams, 'post', '', null, false);
                } else {
                    $mform = new survey_submissionform($formurl, $formparams);
                }
                // end of: prepare params for the form
                // ////////////////////////////

                // ////////////////////////////
                // manage form submission
                if ($mform->is_cancelled()) {
                    $paramurl['pag'] = SURVEY_SUBMISSION_MANAGE;
                    $redirecturl = new moodle_url('view.php', $paramurl);
                    redirect($redirecturl, get_string('usercanceled', 'survey'));
                }

                if ($fromform = $mform->get_data()) {

                    // start by saving unless the "previous" button has been pressed
                    if (!isset($fromform->prevbutton)) {

                        if (!$survey->newpageforchild) {
                            survey_drop_unexpected_values($fromform);
                        }

                        $timenow = time();
                        $savebutton = (isset($fromform->savebutton) && ($fromform->savebutton));
                        $saveasnewbutton = (isset($fromform->saveasnewbutton) && ($fromform->saveasnewbutton));

                        if ($saveasnewbutton || empty($fromform->submissionid)) { // new record needed
                            // add a new record to survey_submissions
                            // this record stub is the basis to build all other possible bailouts
                            $record = new stdClass();
                            $record->surveyid = $survey->id;
                            $record->userid = $USER->id;

                            if (empty($fromform->submissionid)) {
                                $record->status = SURVEY_STATUSINPROGRESS;
                                $record->timecreated = $timenow;
                            }
                            if ($savebutton) {
                                $record->status = SURVEY_STATUSCLOSED;
                                $record->timemodified = $timenow;
                            }
                            if ($saveasnewbutton) {
                                $record->status = SURVEY_STATUSCLOSED;
                                $record->timecreated = $timenow;
                                $record->timemodified = $timenow;
                            }

                            $submissionid = $DB->insert_record('survey_submissions', $record);

                            $fromform->submissionid = $submissionid;
                        } else {
                            $record = new stdClass();
                            $record->id = $fromform->submissionid;
                            if ($savebutton) {
                                $record->status = SURVEY_STATUSCLOSED;
                                $record->timemodified = $timenow;
                                $DB->update_record('survey_submissions', $record);
                            }
                        }

                        survey_save_user_data($fromform);

                        // now, I saved

                        // BEGIN: send email whether requested
                        if ($record->status = SURVEY_STATUSCLOSED) {
                            if (!empty($survey->notifyrole) || !empty($survey->notifymore)) {
                                survey_notifyroles($survey, $cm);
                            }
                        }
                        // END: send email whether requested
                    }

                    // $fromform->formpage is the currently displayed attempt page and it is where I come from

                    // if I am here, the form has been submitted using: <<, >>, save o saveasnew
                    // formpage has the following life:
                    // quando la form viene caricata per la prima volta:
                    //     $formpage get the default value "1" in getparam
                    //     it is stored in the form through set_data($prefill); at the end of the attempt.php file
                    // if execution comes from the submission of the form through << o >>:
                    //     $formpage get a the old $formpage value in getparam (steals it from the form)
                    //     it is used to build the old form to execute the validation form routine (called as child process of $mform->get_data())
                    //     $fromform->formpage is used to get the next available page
                    //     the next available page is used as a GET param in the url for the redirect
                    //     $formpage get this value in getparam
                    //     it is stored in the form through set_data($prefill); at the end of the attempt.php file

                    // the management of the "pause/previous/next" buttons MUST BE DONE HERE because MUST BE preceded by data save
                    // if "pause" button has been pressed, redirect
                    $pausebutton = (isset($fromform->pausebutton) && ($fromform->pausebutton));
                    if ($pausebutton) {
                        $redirecturl = new moodle_url('view.php', $paramurl);
                        redirect($redirecturl); // -> go somewhere
                        die; // <-- never reached
                    }

                    $paramurl['submissionid'] = $submissionid;

                    $prevbutton = (isset($fromform->prevbutton) && ($fromform->prevbutton));
                    if ($prevbutton) {
                        // $fromform->formpage in the worst case becomes 1
                        $paramurl['formpage'] = survey_next_not_empty_page($survey->id, $canaccessadvancedform, $fromform->formpage, false, $submissionid);
                        redirect(new moodle_url('view.php', $paramurl)); // -> go to the first non empty previous page of the form
                        die; // <-- never reached
                    }

                    $nextbutton = (isset($fromform->nextbutton) && ($fromform->nextbutton));
                    if ($nextbutton) {
                        // $fromform->formpage in the worst case could become $lastformpage such as 0
                        $paramurl['formpage'] = survey_next_not_empty_page($survey->id, $canaccessadvancedform, $fromform->formpage, true, $submissionid, $lastformpage);
                        redirect(new moodle_url('view.php', $paramurl)); // -> go to the first non empty next page of the form
                        die; // <-- never reached
                    }
                }
                // end of: manage form submission
                // ////////////////////////////
                break;
            case SURVEY_SUBMISSION_MANAGE:
                $action = optional_param('act', SURVEY_NOACTION, PARAM_INT);
                $searchfields_get = optional_param('searchquery', '', PARAM_RAW);

                break;
            case SURVEY_SUBMISSION_SEARCH:       // display the submission search form
                $sqlparams = array('surveyid' => $survey->id, 'hide' => 0);
                if ($canaccessadvancedform) {
                    $sqlparams['advancedsearch'] = SURVEY_ADVFILLANDSEARCH;
                } else {
                    $sqlparams['basicform'] = SURVEY_FILLANDSEARCH;
                }

                // if no items are available, stop the intervention here
                if (!$DB->count_records('survey_item', $sqlparams)) {
                    break;
                }

                // ////////////////////////////
                // prepare params for the search form
                $formparams = new stdClass();
                $formparams->cmid = $cm->id;
                $formparams->survey = $survey;
                $formparams->canaccessadvancedform = $canaccessadvancedform; // Help selecting the fields to show
                $formparams->formpage = $formpage;

                require_once($CFG->dirroot.'/mod/survey/pages/submissions/search_form.php');
                $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_SEARCH);
                $formurl = new moodle_url('view.php', $paramurl);
                $mform = new survey_searchform($formurl, $formparams);
                // end of: prepare params for the form
                // ////////////////////////////

                if ($fromform = $mform->get_data()) { // $mform, here, is the search form
                    // in questa routine non eseguo una vera e propria ricerca
                    // mi limito a definire la stringa di parametri per la chiamata a SURVEY_SUBMISSION_MANAGE
                    $regexp = '~'.SURVEY_ITEMPREFIX.'_('.SURVEY_FIELD.'|'.SURVEY_FORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';

                    $infoperitem = array();
                    foreach ($fromform as $elementname => $content) {
                        // echo '$elementname = '.$elementname.'<br />';
                        // echo '$content = '.$content.'<br />';
                        // TODO: this routine needs more testing
                        // mi interessano solo i campi della search form che contengono qualcosa ma che non contengono SURVEY_NOANSWERVALUE
                        if (isset($content) && ($content != SURVEY_NOANSWERVALUE)) {
                            if (preg_match($regexp, $elementname, $matches)) {
                                $itemid = $matches[3]; // itemid dell'elemento della form (o della famiglia di elementi della form)
                                if (!isset($infoperitem[$itemid])) {
                                    $infoperitem[$itemid] = new stdClass();
                                    $infoperitem[$itemid]->type = $matches[1];
                                    $infoperitem[$itemid]->plugin = $matches[2];
                                    $infoperitem[$itemid]->itemid = $itemid;
                                    if (!isset($matches[4])) {
                                        $infoperitem[$itemid]->extra['mainelement'] = $content;
                                    } else {
                                        $infoperitem[$itemid]->extra[$matches[4]] = $content;
                                    }
                                } else {
                                    $infoperitem[$itemid]->extra[$matches[4]] = $content;
                                }
                            }
                        }
                    }
                    // echo '$infoperitem:';
                    // var_dump($infoperitem);
                    $searchfields = array();
                    foreach ($infoperitem as $iteminfo) {
                        // echo '$iteminfo:';
                        // var_dump($iteminfo);
                        // do not waste your time
                        if ( isset($iteminfo->extra['noanswer']) && $iteminfo->extra['noanswer'] ) {
                            continue;
                        }

                        $item = survey_get_item($iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

                        $userdata = new stdClass();
                        $item->userform_save($iteminfo->extra, $userdata);

                        $searchfields[] = $userdata->content.SURVEY_URLVALUESEPARATOR.$iteminfo->itemid;
                    }
                    // echo '$searchfields:';
                    // var_dump($searchfields);
                    // define searchfields_get to let it carry all the information to the next URL
                    $searchfields_get = implode(SURVEY_URLPARAMSEPARATOR, $searchfields);

                    $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_MANAGE);
                    $paramurl['searchquery'] = $searchfields_get;
                    $returnurl = new moodle_url('view.php', $paramurl);
                    redirect($returnurl);
                }

                break;
            case SURVEY_SUBMISSION_REPORT: // report
                $reportname = optional_param('rname', '', PARAM_ALPHA);
                $hassubmissions = survey_has_submissions($survey->id);
                break;
            case SURVEY_SUBMISSION_EXPORT: // export
                require_once($CFG->dirroot.'/mod/survey/pages/submissions/export_form.php');

                $formparams = new stdClass();
                $formparams->canaccessadvancedform = $canaccessadvancedform;
                $formparams->saveresume = $survey->saveresume;

                $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_EXPORT);
                $formurl = new moodle_url('view.php', $paramurl);
                $mform = new survey_exportform($formurl, $formparams);

                if ($fromform = $mform->get_data()) {
                    $exportoutcome = survey_export($cm, $fromform, $survey);
                    if (empty($exportoutcome)) {
                        die;
                    }
                } else {
                    $exportoutcome = null;
                }
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $currentpage = '.$currentpage);
        }
        break;
    case SURVEY_TABITEMS:
        $itemid = optional_param('itemid', 0, PARAM_INT);
        $plugin = optional_param('plugin', null, PARAM_TEXT);

        $hassubmissions = survey_has_submissions($survey->id);

        switch ($currentpage) {
            case SURVEY_ITEMS_CONFIGURE:
                if (preg_match('~^('.SURVEY_FIELD.'|'.SURVEY_FORMAT.')_(\w+)$~', $plugin, $match)) {
                    // execution comes from /pages/items/itemtype.php
                    $type = $match[1]; // field or format
                    $plugin = $match[2]; // boolean or char ... or fieldset ...
                } else {
                    // execution comes from /pages/items/manageitems.php
                    $type = optional_param('type', null, PARAM_TEXT);
                    $saveasnew = optional_param('saveasnew', null, PARAM_TEXT);
                }

                /*
                 * get item
                 */
                require_once($CFG->dirroot.'/mod/survey/'.$type.'/'.$plugin.'/plugin.class.php');
                $itemclass = 'survey'.$type.'_'.$plugin;
                $item = new $itemclass($itemid);
                if (method_exists($item, 'item_set_editor')) {
                    $item->item_set_editor($cm->id, $item);
                }

                /*
                 * include the form
                 */
                require_once($CFG->dirroot.'/mod/survey/'.$type.'/'.$plugin.'/plugin_form.php');
                $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_CONFIGURE);
                $formurl = new moodle_url('view.php', $paramurl);

                /*
                 * prepare variables to call the form
                 */
                $formparams = new stdClass();
                $formparams->survey = $survey;         // needed to setup date boundaries in date fields
                $formparams->item = $item;                     // needed in many situations
                $formparams->hassubmissions = $hassubmissions; // are editing features restricted?

                $mform = new survey_pluginform($formurl, $formparams);

                if ($mform->is_cancelled()) {
                    $paramurl['pag'] = SURVEY_ITEMS_MANAGE;
                    $returnurl = new moodle_url('view.php', $paramurl);
                    redirect($returnurl);
                }

                if ($fromform = $mform->get_data()) {
                    // has this submission been forced to be new?
                    if (!empty($saveasnew)) {
                        $fromform->itemid = 0;
                    }

                    $userfeedback = $item->item_save($fromform);
                    $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_MANAGE, 'ufd' => $userfeedback);
                    $returnurl = new moodle_url('view.php', $paramurl);
                    redirect($returnurl);
                }
                break;
            case SURVEY_ITEMS_ADDSET: // add itemset
                require_once($CFG->dirroot.'/mod/survey/pages/items/addset_form.php');

                // ////////////////////////////
                // prepare params for the form
                $formparams = new stdClass();
                $formparams->cmid = $cm->id;
                $formparams->survey = $survey;

                $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_ADDSET);
                $formurl = new moodle_url('view.php', $paramurl);
                $mform = new survey_addsetform($formurl, $formparams);

                if ($mform->is_cancelled()) {
                    $paramurl['pag'] = SURVEY_ITEMS_ADD;
                    $returnurl = new moodle_url('view.php', $paramurl);
                    redirect($returnurl);
                }

                if ($formdata = $mform->get_data()) {
                    $dbman = $DB->get_manager();

                    if ($formdata->actionoverother == SURVEY_HIDEITEMS) {
                        // BEGIN: hide all other items
                        $DB->set_field('survey_item', 'hide', 1, array('surveyid' => $survey->id, 'hide' => 0));
                        // END: hide all other items
                    }

                    if ($formdata->actionoverother == SURVEY_DELETEITEMS) {
                        // BEGIN: delete all other items
                        $sql = 'SELECT si.plugin
                                FROM {survey_item} si
                                WHERE si.surveyid = :surveyid
                                GROUP BY si.plugin';

                        $pluginseeds = $DB->get_records_sql($sql, array('surveyid' => $survey->id));

                        foreach ($pluginseeds as $pluginseed) {
                            $tablename = 'survey_'.$pluginseed->plugin;
                            if ($dbman->table_exists($tablename)) {
                                $DB->delete_records($tablename, array('surveyid' => $survey->id));
                            }
                        }
                        $DB->delete_records('survey_item', array('surveyid' => $survey->id));
                        // END: delete all other items

                    }

                    $parts = explode('_', $formdata->itemset);
                    $itemsettype = $parts[0];
                    // Take care: the name of the user survey/template may include '_'
                    $itemsetidentifier = substr($formdata->itemset, strlen($itemsettype)+1);

                    if ($itemsettype == SURVEY_MASTERTEMPLATE) {
                        // BEGIN: add records from survey plugin
                        survey_add_items_from_plugin($survey, $itemsetidentifier);
                        // END: add records from survey plugin
                    }
                    if ($itemsettype == SURVEY_USERTEMPLATE) {
                        // BEGIN: add records from template
                        survey_add_items_from_template($survey, $itemsetidentifier);
                        // END: add records from template
                    }

                    $paramurl = array();
                    $paramurl['s'] = $survey->id;
                    $paramurl['tab'] = SURVEY_TABITEMS;
                    $paramurl['pag'] = SURVEY_ITEMS_MANAGE;
                    $redirecturl = new moodle_url('view.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            default:
                // nothing to do with all the other pages
                // no warning: leave them untouched
        }
        break;
    case SURVEY_TABTEMPLATES:
        switch ($currentpage) {
            case SURVEY_TEMPLATES_MANAGE: // Manage
                $action = optional_param('act', SURVEY_NOACTION, PARAM_INT);
                $fileid = optional_param('fid', '', PARAM_INT);
                $confirm = optional_param('cnf', 0, PARAM_INT);

                switch ($action) {
                    case SURVEY_NOACTION:
                    case SURVEY_DELETETEMPLATE:
                        break;
                    case SURVEY_EXPORTTEMPLATE:
                        $fs = get_file_storage();
                        $xmlfile = $fs->get_file_by_id($fileid);
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
                        exit(0);
                        break;
                    default:
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $action = '.$action);
                }

                break;
            case SURVEY_TEMPLATES_BUILD: // Build
                require_once($CFG->dirroot.'/mod/survey/pages/templates/createtemplate_form.php');

                $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABTEMPLATES, 'pag' => SURVEY_TEMPLATES_BUILD);
                $formurl = new moodle_url('view.php', $paramurl);

                $formparams = new stdClass();
                $formparams->cmid = $cm->id;
                $formparams->survey = $survey;
                $mform = new survey_templatebuildform($formurl, $formparams);

                if ($formdata = $mform->get_data()) {
                    $xmlcontent = survey_create_template_content($survey);
                    // echo '<textarea rows="80" cols="100">'.$xmlcontent.'</textarea>';

                    survey_save_template($formdata, $xmlcontent);

                    $paramurl = array();
                    $paramurl['s'] = $survey->id;
                    $paramurl['tab'] = SURVEY_TABTEMPLATES;
                    $paramurl['pag'] = SURVEY_TEMPLATES_MANAGE;
                    $redirecturl = new moodle_url('view.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            case SURVEY_TEMPLATES_IMPORT: // Import
                require_once($CFG->dirroot.'/mod/survey/pages/templates/importtemplate_form.php');

                $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABTEMPLATES, 'pag' => SURVEY_TEMPLATES_IMPORT);
                $formurl = new moodle_url('view.php', $paramurl);

                $formparams = new stdClass();
                $formparams->cmid = $cm->id;
                $formparams->survey = $survey;
                $mform = new survey_templateimportform($formurl, $formparams);

                if ($formdata = $mform->get_data()) {

                    survey_upload_template($formdata);

                    $paramurl = array();
                    $paramurl['s'] = $survey->id;
                    $paramurl['tab'] = SURVEY_TABTEMPLATES;
                    $paramurl['pag'] = SURVEY_TEMPLATES_MANAGE;
                    $redirecturl = new moodle_url('view.php', $paramurl);
                    redirect($redirecturl);
                }

                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $currentpage = '.$currentpage);
        }
        break;
    case SURVEY_TABPLUGINS:
        require_once($CFG->dirroot.'/mod/survey/pages/plugins/pluginbuild_form.php');

        $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABPLUGINS, 'pag' => SURVEY_PLUGINS_BUILD);
        $formurl = new moodle_url('view.php', $paramurl);
        $mform = new survey_exportplugin($formurl);

        if ($formdata = $mform->get_data()) {

            if (headers_sent()) {
                print_error('headersent');
            }

            $exportfile = survey_plugin_build($formdata);
            $exportfilename = basename($exportfile);
            header("Content-Type: application/download\n");
            header("Content-Disposition: attachment; filename=\"$exportfilename\"");
            header('Expires: 0');
            header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
            header('Pragma: public');
            $exportfilehandler = fopen($exportfile, 'rb');
            print fread($exportfilehandler, filesize($exportfile));
            fclose($exportfilehandler);
            unlink($exportfile);
            exit(0);
        }
        break;
    default:
        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $currenttab = '.$currenttab);
}
