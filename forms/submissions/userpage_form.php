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

require_once($CFG->dirroot.'/lib/formslib.php');

class survey_submissionform extends moodleform {

    public function definition() {
        global $DB, $CFG;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $firstpage_right = $this->_customdata->firstpage_right;
        $maxassignedpage = $this->_customdata->maxassignedpage;
        $survey = $this->_customdata->survey;
        $submissionid = $this->_customdata->submissionid;
        $formpage = $this->_customdata->formpage;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;
        $tabpage = $this->_customdata->tabpage;
        $cansubmit = $this->_customdata->cansubmit;

        // ----------------------------------------
        // newitem::s
        // ----------------------------------------
        $mform->addElement('hidden', 's', $survey->id);
        $mform->setType('s', PARAM_INT);

        // ----------------------------------------
        // newitem::submissionid
        // ----------------------------------------
        $mform->addElement('hidden', 'submissionid', 0);
        $mform->setType('submissionid', PARAM_INT);

        // ----------------------------------------
        // newitem::formpage
        // ----------------------------------------
        $mform->addElement('hidden', 'formpage', 0); // <-- this value comes from default just set before $mform->display(); in attempt.php
        $mform->setType('formpage', PARAM_INT);

        if ($formpage == SURVEY_LEFT_OVERFLOW) {
            $mform->addElement('static', 'nomoreitems', get_string('note', 'survey'), get_string('onlyadvanceditemhere', 'survey'));
            // $mform->addElement('static', 'nomoreitems', get_string('note', 'survey'), 'SURVEY_LEFT_OVERFLOW');
        }

        if ($formpage == SURVEY_RIGHT_OVERFLOW) {
            $a = $survey->saveresume ? get_string('revieworpause', 'survey') : get_string('onlyreview', 'survey');
            $mform->addElement('static', 'nomoreitems', get_string('note', 'survey'), get_string('nomoreitems', 'survey', $a));
            // $mform->addElement('static', 'nomoreitems', get_string('note', 'survey'), 'SURVEY_RIGHT_OVERFLOW');
        }

        if ($formpage > 0) {
            //$canaccessadvanceditems, $searchform=false, $type=false, $formpage
            list($sql, $params) = survey_fetch_items_seeds($survey->id, $canaccessadvanceditems, false, false, $formpage);
            $itemseeds = $DB->get_recordset_sql($sql, $params);
            // I do not need to be sure items are found because I already know this
            // In attempt.php if items are not found I display a message and execution is stopped

            if (!$itemseeds->valid()) {
                // no items are in this page
                // display a message
                $mform->addElement('static', 'noitemshere', get_string('note', 'survey'), 'ciccia');
            }

            $context = context_module::instance($cmid);
            foreach ($itemseeds as $itemseed) {
                if ($itemseed->parentid) {
                    // get it now AND NEVER MORE
                    $parentitem = survey_get_item($itemseed->parentid);
                } else {
                    $parentitem = null;
                }

                // is the current item allowed to be displayed in this page?
                if ($itemseed->parentid) {
                    // if parentitem is in a previous page, have a check
                    // otherwise
                    // display the current item
                    if ($parentitem->get_formpage() < $formpage) {
                        require_once($CFG->dirroot.'/mod/survey/'.$itemseed->type.'/'.$itemseed->plugin.'/plugin.class.php');

                        $itemaschildisallowed = $parentitem->userform_child_item_allowed_static($submissionid, $itemseed);
                    } else {
                        $itemaschildisallowed = true;
                    }
                } else {
                    // current item has no parent: display it
                    $itemaschildisallowed = true;
                }

                if ($itemaschildisallowed) {
                    $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

                    if ($item->get_extrarow()) {
                        $elementnumber = $item->get_customnumber() ? $item->get_customnumber().':' : '';

                        $output = file_rewrite_pluginfile_urls($item->get_content(), 'pluginfile.php', $context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $item->get_itemid());
                        //echo '<textarea rows="10" cols="100">'.$output.'</textarea>';
                        //die;
                        //$this->itemname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
                        $mform->addElement('static', $item->get_itemname().'_extrarow', $elementnumber, $output, array('class' => 'indent-'.$item->get_indent())); // here I  do not strip tags to content
                    }

                    $item->userform_mform_element($mform, $survey, $canaccessadvanceditems, $parentitem);

                    if ($fullinfo = $item->userform_get_full_info(false)) {
                        $mform->addElement('static', $item->get_itemname().'_info', get_string('note', 'survey'), $fullinfo);
                    }

                    if (!$survey->newpageforchild) {
                        $item->userform_disable_element($mform, $canaccessadvanceditems);
                    }
                }
            }
            $itemseeds->close();

            if ($tabpage != SURVEY_SUBMISSION_PREVIEW) {
                if (!empty($survey->captcha)) {
                    $mform->addElement('recaptcha', 'captcha_form_footer');
                }
            }
        }

        if ($cansubmit) {
            // -------------------------------------------------------------------------------
            // buttons
            $buttonlist = array();

            // SURVEY_LEFT_OVERFLOW or greater than 1
            if ( ($formpage == SURVEY_RIGHT_OVERFLOW) || ($formpage > 1) ) {
                $buttonlist['prevbutton'] = get_string('previousformpage', 'survey');
            }
            if ($tabpage != SURVEY_SUBMISSION_PREVIEW) {
                if ($survey->saveresume) {
                    $buttonlist['pausebutton'] = get_string('pause', 'survey');
                }
                if (($formpage == $maxassignedpage) || ($formpage == SURVEY_RIGHT_OVERFLOW)){
                    if ($survey->history) {
                        $submission_status = $DB->get_field('survey_submissions', 'status', array('id' => $submissionid), IGNORE_MISSING);
                        if ($submission_status === false) { // submissions still does not exist
                            $usesimplesavebutton = true;
                        } else {
                            $usesimplesavebutton = ($submission_status == SURVEY_STATUSINPROGRESS);
                        }
                    } else {
                        $usesimplesavebutton = true;
                    }
                    if ($usesimplesavebutton) {
                        $buttonlist['savebutton'] = get_string('submit');
                    } else {
                        $buttonlist['saveasnewbutton'] = get_string('saveasnew', 'survey');
                    }
                }
            }
            if ( ($formpage == SURVEY_LEFT_OVERFLOW) || ($formpage > 0 && $formpage < $maxassignedpage) ) {
                $buttonlist['nextbutton'] = get_string('nextformpage', 'survey');
            }

            if (count($buttonlist) > 1) {
                $buttonarray = array();
                foreach ($buttonlist as $name => $label) {
                    $buttonarray[] = $mform->createElement('submit', $name, $label);
                }
                $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
                $mform->setType('buttonar', PARAM_RAW);
                $mform->closeHeaderBefore('buttonar');
            } else {
                foreach ($buttonlist as $name => $label) {
                    $mform->addElement('submit', $name, $label);
                }
            }
        }
// echo '$mform:';
// var_dump($mform);
    }

    public function validation($data, $files) {
        $mform = $this->_form;

        // $cmid = $this->_customdata->cmid;
        $tabpage = $this->_customdata->tabpage;

        if (isset($data['prevbutton']) || ($tabpage == SURVEY_SUBMISSION_PREVIEW)) {
            // skip validation
            return array();
        }

        $survey = $this->_customdata->survey;
        $submissionid = $this->_customdata->submissionid;
        $formpage = $this->_customdata->formpage;
        $firstpage_right = $this->_customdata->firstpage_right;
        $maxassignedpage = $this->_customdata->maxassignedpage;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;

        $errors = parent::validation($data, $files);

        // Show the item only if: the current item matches the parent value
        $olditemid = 0;
        foreach ($data as $k => $v) {
            if (preg_match('~^('.SURVEY_ITEMPREFIX.'|'.SURVEY_NEGLECTPREFIX.')_~', $k)) { // if it starts with SURVEY_ITEMPREFIX_
                $parts = explode('_', $k);
                $type = $parts[1]; // item type
                $plugin = $parts[2]; // item plugin
                $itemid = $parts[3]; // item id

                if ($itemid == $olditemid) {
                    continue;
                }

                $olditemid = $itemid;

                $item = survey_get_item($itemid, $type, $plugin);
                if ($survey->newpageforchild) {
                    $itemisenabled = true; // if it is displayed, it is enabled
                    $parentitem = null;
                } else {
                    $item_parentid = $item->get_parentid();
                    if (!$item_parentid) {
                        $itemisenabled = true;
                        $parentitem = null;
                    } else {
                        // call its parent
                        $parentitem = survey_get_item($item_parentid);
                        // tell parent that his child has parentcontent = 12/4/1968
                        if ($parentitem->get_formpage() == $item->get_formpage()) {
                            $itemisenabled = $parentitem->userform_child_item_allowed_dynamic($item->get_parentcontent(), $data);
                        } else {
                            // If ($parentitem is in a previous page) && ($item is displayed because it was found) {
                            //     $item IS ENABLED FOR SURE
                            // }
                            $itemisenabled = true;
                        }
                        // parent item, knowing how itself exactly is, compare what is needed and provide an answer
                    }
                }

                if ($itemisenabled) {
                    $item->userform_mform_validation($data, $errors, $survey, $canaccessadvanceditems, $parentitem);
                // } else {
                    // echo 'parent item didn\'t allow the validation of the child item '.$item->itemid.', plugin = '.$item->plugin.'('.$item->content.')<br />';
                }
            }
        }

        return $errors;
    }
}