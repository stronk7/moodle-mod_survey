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
        $firstpageright = $this->_customdata->firstpageright;
        $maxassignedpage = $this->_customdata->maxassignedpage;
        $survey = $this->_customdata->survey;
        $submissionid = $this->_customdata->submissionid;
        $formpage = $this->_customdata->formpage;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;
        $modulepage = $this->_customdata->modulepage;
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
        $mform->addElement('hidden', 'formpage', 0); // <-- this value comes from default set just before $mform->display(); in view.php
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

        if ($formpage >= 0) {
            // $canaccessadvanceditems, $searchform=false, $type=false, $formpage
            list($sql, $whereparams) = survey_fetch_items_seeds($survey->id, $canaccessadvanceditems, false, false, $formpage);
            $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

            if (!$itemseeds->valid()) {
                // no items are in this page
                // display an error message
                $mform->addElement('static', 'noitemshere', get_string('note', 'survey'), 'ERROR: How can I be here if ($formpage > 0) ?');
            }

            $context = context_module::instance($cmid);
            foreach ($itemseeds as $itemseed) {
                if ($modulepage == SURVEY_ITEMS_PREVIEW) {
                    $itemaschildisallowed = true;
                } else {
                    // is the current item allowed to be displayed in this page?
                    if ($itemseed->parentid) {
                        // get it now AND NEVER MORE
                        $parentitem = survey_get_item($itemseed->parentid);

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
                        $parentitem = null;
                        $itemaschildisallowed = true;
                    }
                }

                if ($itemaschildisallowed) {
                    $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

                    // position
                    $position = $item->get_position();
                    $elementnumber = $item->get_customnumber() ? $item->get_customnumber().':' : '';
                    if ($position == SURVEY_POSITIONTOP) {
                        // workaround suggested by Marina Glancy in MDL-42946
                        $content = html_writer::tag('div', $item->get_content(), array('class' => 'indent-'.$item->get_indent()));

                        $mform->addElement('static', $item->get_itemname().'_extrarow', $elementnumber, $content);
                    }
                    if ($position == SURVEY_POSITIONFULLWIDTH) {
                        $questioncontent = $item->get_content();
                        if ($elementnumber) {
                            // I want to change "4.2:<p>Do you live in NY?</p>" to "<p>4.2: Do you live in NY?</p>"
                            if (preg_match('/^<p>(.*)$/', $questioncontent, $match)) {
                                // print_object($match);
                                $questioncontent = '<p>'.$elementnumber.' '.$match[1];
                            }
                        }
                        $content = '';
                        $content .= html_writer::start_tag('fieldset', array('class' => 'hidden'));
                        $content .= html_writer::start_tag('div');
                        $content .= html_writer::start_tag('div', array('class' => 'fitem'));
                        $content .= html_writer::start_tag('div', array('class' => 'fstatic fullwidth'));
                        // $content .= html_writer::start_tag('div', array('class' => 'indent-'.$this->indent));
                        $content .= $questioncontent;
                        // $content .= html_writer::end_tag('div');
                        $content .= html_writer::end_tag('div');
                        $content .= html_writer::end_tag('div');
                        $content .= html_writer::end_tag('div');
                        $content .= html_writer::end_tag('fieldset');
                        $mform->addElement('html', $content);
                    }

                    // element
                    $item->userform_mform_element($mform, false);

                    // note
                    if ($fullinfo = $item->userform_get_full_info(false)) {
                        // workaround suggested by Marina Glancy in MDL-42946
                        $content = html_writer::tag('span', $fullinfo, array('class' => 'indent-'.$item->get_indent()));

                        $mform->addElement('static', $item->get_itemname().'_note', get_string('note', 'survey'), $content);
                    }

                    if (!$survey->newpageforchild) {
                        $item->userform_disable_element($mform, $canaccessadvanceditems);
                    }
                }
            }
            $itemseeds->close();

            if ($modulepage != SURVEY_ITEMS_PREVIEW) {
                if (!empty($survey->captcha)) {
                    $mform->addElement('recaptcha', 'captcha_form_footer');
                }
            }
        }

        // buttons
        $buttonlist = array();

        if ( ($formpage == SURVEY_RIGHT_OVERFLOW) || ($formpage > 1) ) {
            $buttonlist['prevbutton'] = get_string('previousformpage', 'survey');
        }
        if ($modulepage != SURVEY_ITEMS_PREVIEW) {
            if ($survey->saveresume) {
                $buttonlist['pausebutton'] = get_string('pause', 'survey');
            }
            if (($formpage == $maxassignedpage) || ($formpage == SURVEY_RIGHT_OVERFLOW)) {
                if ($survey->history) {
                    $submissionstatus = $DB->get_field('survey_submission', 'status', array('id' => $submissionid), IGNORE_MISSING);
                    if ($submissionstatus === false) { // submissions still does not exist
                        $usesimplesavebutton = true;
                    } else {
                        $usesimplesavebutton = ($submissionstatus == SURVEY_STATUSINPROGRESS);
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
        } else { // only one button here
            foreach ($buttonlist as $name => $label) {
                $mform->closeHeaderBefore($name);
                $mform->addElement('submit', $name, $label);
            }
        }
    }

    public function validation($data, $files) {
        $mform = $this->_form;

        // $cmid = $this->_customdata->cmid;
        $modulepage = $this->_customdata->modulepage;

        if (isset($data['prevbutton']) || ($modulepage == SURVEY_ITEMS_PREVIEW)) {
            // skip validation
            return array();
        }

        $survey = $this->_customdata->survey;
        $submissionid = $this->_customdata->submissionid;
        $formpage = $this->_customdata->formpage;
        $firstpageright = $this->_customdata->firstpageright;
        $maxassignedpage = $this->_customdata->maxassignedpage;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;

        $errors = parent::validation($data, $files);

        // Show the item only if: the current item matches the parent value
        $regexp = '~('.SURVEY_ITEMPREFIX.'|'.SURVEY_PLACEHOLDERPREFIX.')_('.SURVEY_TYPEFIELD.'|'.SURVEY_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
        $olditemid = 0;
        foreach ($data as $itemname => $v) {
            if (preg_match($regexp, $itemname, $matches)) {
                $type = $matches[2]; // item type
                $plugin = $matches[3]; // item plugin
                $itemid = $matches[4]; // item id
                // $option = $matches[5]; // _text or _noanswer or...

                if ($itemid == $olditemid) {
                    continue;
                }

                $olditemid = $itemid;

                $item = survey_get_item($itemid, $type, $plugin);
                if ($survey->newpageforchild) {
                    $itemisenabled = true; // since it is displayed, it is enabled
                    $parentitem = null;
                } else {
                    $parentitemid = $item->get_parentid();
                    if (!$parentitemid) {
                        $itemisenabled = true;
                        $parentitem = null;
                    } else {
                        // call its parent
                        $parentitem = survey_get_item($parentitemid);
                        // tell parent that his child has parentvalue = 1;3
                        if ($parentitem->get_formpage() == $item->get_formpage()) {
                            $itemisenabled = $parentitem->userform_child_item_allowed_dynamic($item->get_parentvalue(), $data);
                        } else {
                            $itemisenabled = true;
                        }
                        // parent item, knowing how itself exactly is, compare what is needed and provide an answer
                    }
                }

                if ($itemisenabled) {
                    $item->userform_mform_validation($data, $errors, $survey, false);
                    // } else {
                    // echo 'parent item doesn\'t allow the validation of the child item '.$item->itemid.', plugin = '.$item->plugin.'('.$item->content.')<br />';
                }
            }
        }

        return $errors;
    }
}
