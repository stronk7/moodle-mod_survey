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


/**
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/lib/pear/HTML/QuickForm/element.php');

class survey_submissionform extends moodleform {

    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $lastformpage = $this->_customdata->lastformpage;
        $survey = $this->_customdata->survey;
        $submissionid = $this->_customdata->submissionid;
        $formpage = $this->_customdata->formpage;
        $canaccessadvancedform = $this->_customdata->canaccessadvancedform;

        $mform->addElement('hidden', 's', $survey->id);
        $mform->addElement('hidden', 'submissionid', 0);
        $mform->addElement('hidden', 'formpage', 0); // <-- this value comes from default just set before $mform->display(); in submissions.php

        if (!$formpage) {
            // if !$formpage then I am at the END of the survey otherwise, $formpage == 1 at least
            // no more pages have fields to show
            // let's display final message
            $mform->addElement('static', 'nomoreitems', get_string('note', 'survey'), get_string('nomoreitems', 'survey'));
        } else {
            $sql = survey_fetch_items_seeds($canaccessadvancedform);
            $params = array('surveyid' => $survey->id, 'formpage' => $formpage);
            $itemseeds = $DB->get_recordset_sql($sql, $params);
            // I do not need to be sure items are found because I already know this
            // In submissions.php if items are not found I display a message and execution is stopped

            $context = context_module::instance($cmid);

            foreach ($itemseeds as $itemseed) {
//echo '$itemseed->basicformpage):';
//var_dump($itemseed->basicformpage);
                // Show the item only if:
                //     - all has to going to the same page
                //       OR
                //     - the current item matches the parent value
                if ($itemseed->parentid) {
                    // get it now AND NEVER MORE
                    $parentitem = survey_get_item($itemseed->parentid);
                } else {
                    $parentitem = null;
                }

                if ($survey->newpageforchild) {
                    $itemaschildisallowed = survey_child_is_allowed_static($submissionid, $itemseed);
                } else {
                    $itemaschildisallowed = true;
                }

                if ($itemaschildisallowed) {
                    $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

                    if (isset($item->extrarow) && $item->extrarow) {
                        $elementnumber = $item->customnumber ? $item->customnumber.':' : '';

                        $output = file_rewrite_pluginfile_urls($item->content, 'pluginfile.php', $context->id, 'mod_survey', 'items', $item->itemid);
                        $mform->addElement('static', $item->type.'_'.$item->itemid.'_extrarow', $elementnumber, $output, array('class' => 'indent-'.$item->indent)); // here I  do not strip tags to content
                    }

                    $item->userform_mform_element($mform, $survey, $canaccessadvancedform, $parentitem);

                    if ($fullinfo = $item->item_get_full_info(false)) {
                        $mform->addElement('static', $item->type.'_'.$item->itemid.'_info', get_string('note', 'survey'), $fullinfo);
                    }

                    if (!$survey->newpageforchild) {
                        $item->userform_disable_element($mform);
                    }
                }
            }
            $itemseeds->close();

            if (!empty($survey->captcha)) {
                $mform->addElement('recaptcha', 'captcha_form_footer');
            }
        }

        //-------------------------------------------------------------------------------
        // buttons
        $buttonarray = array();
        if ($formpage != 1) { // 0 or greater than 1
            $buttonarray[] = $mform->createElement('submit', 'prevbutton', get_string('previousformpage', 'survey'));
        }
        if ($survey->saveresume) {
            $buttonarray[] = $mform->createElement('submit', 'pausebutton', get_string('pause', 'survey'));
        }
        if (($formpage == $lastformpage) || (!$formpage)) {
            if ($survey->history) {
                $buttonarray[] = $mform->createElement('submit', 'saveasnewbutton', get_string('saveasnew', 'survey'));
            } else {
                $buttonarray[] = $mform->createElement('submit', 'savebutton', get_string('submit'));
            }
        }
        if (($formpage < $lastformpage) && ($formpage)) { // lower than $lastformpage but different from 0
            $buttonarray[] = $mform->createElement('submit', 'nextbutton', get_string('nextformpage', 'survey'));
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
    }

    function validation($data, $files) {
        global $DB;

        //$cmid = $this->_customdata->cmid;
        //$lastformpage = $this->_customdata->lastformpage;
        $survey = $this->_customdata->survey;
        $submissionid = $this->_customdata->submissionid;
        $formpage = $this->_customdata->formpage;
        $canaccessadvancedform = $this->_customdata->canaccessadvancedform;

        $errors = parent::validation($data, $files);

        // Show the item only if: the current item matches the parent value
        $olditemid = 0;
        foreach ($data as $k => $v) {
            if (preg_match('~^'.SURVEY_ITEMPREFIX.'_~', $k)) { // if it starts with SURVEY_ITEMPREFIX_
                $parts = explode('_', $k);
                $type = $parts[1]; // item type
                $plugin = $parts[2]; // item plugin
                $itemid = $parts[3]; // item id

                if ($itemid == $olditemid) continue;

                $olditemid = $itemid;

                $item = survey_get_item($itemid, $type, $plugin);
                if (empty($item->parentid)) {
                    $itemallowed = true;
                    $parentitem = null;
                } else {
                    // chiama il padre
                    $parentitem = survey_get_item($item->parentid);
                    // digli che il figlio ha come parentcontent 12/4/1968
                    $itemallowed = $parentitem->userform_child_is_allowed_dynamic($item->parentcontent, $data);
                    // il padre, sapendo come è fatto, compara quello che vuole e risponde
                }

                if ($itemallowed) {
                    $item->userform_mform_validation($data, $errors, $survey, $canaccessadvancedform, $parentitem);
//                 } else {
// echo 'Non ho consentito la validazione dell\'item '.$item->itemid.', plugin = '.$item->plugin.'<br />';
                }
            }
        }

        return $errors;
    }
}