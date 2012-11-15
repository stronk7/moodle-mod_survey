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
 * Internal library of functions for module survey
 *
 * All the survey specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/survey/lib.php');

/**
 * survey_user_can_do_anything
 * @param
 * @return
 */
function survey_user_can_do_anything() {
    $context = context_system::instance();

    return (has_capability('moodle/site:doanything', $context));
}

/**
 * survey_user_can_access_advanced_form
 * @param $cm
 * @return
 */
function survey_user_can_access_advanced_form($cm) {
    $context = context_module::instance($cm->id);

    return (has_capability('mod/survey:accessadvancedform', $context, null, true));
}

/**
 * survey_user_can_export_data
 * @param $cm
 * @return
 */
function survey_user_can_export_data($cm) {
    $context = context_module::instance($cm->id);

    return (has_capability('mod/survey:exportdata', $context, null, true));
}

/**
 * survey_user_can_read_all_submissions
 * @param $cm
 * @return
 */
function survey_user_can_read_all_submissions($cm) {
    $context = context_module::instance($cm->id);

    return (has_capability('mod/survey:readall', $context, null, true));
}

/**
 * survey_user_can_edit_all_submissions
 * @param $cm
 * @return
 */
function survey_user_can_edit_all_submissions($cm) {
    $context = context_module::instance($cm->id);

    return (has_capability('mod/survey:editall', $context, null, true));
}

/**
 * survey_user_can_delete_all_submissions
 * @param $cm
 * @return
 */
function survey_user_can_delete_all_submissions($cm) {
    $context = context_module::instance($cm->id);

    return (has_capability('mod/survey:deleteall', $context, null, true));
}

/**
 * survey_get_item
 * @param $itemid, $type, $plugin
 * @return
 */
function survey_get_item($itemid=0, $type='', $plugin='') {
    global $CFG, $DB;

    if (empty($itemid)) {
        if (empty($type) || empty($plugin)) {
            debugging('Can not get an item without its type, plugin and ID');
        }
    }

    if (empty($type) && empty($plugin)) { // I am asking for a template only
        $itemseed = $DB->get_record('survey_item', array('id' => $itemid), 'type, plugin', MUST_EXIST);
        $type = $itemseed->type;
        $plugin = $itemseed->plugin;
    }

    require_once($CFG->dirroot.'/mod/survey/'.$type.'/'.$plugin.'/plugin.class.php');
    $classname = 'survey'.$type.'_'.$plugin;
    $item = new $classname($itemid);

    return $item;
}

/**
 * survey_non_empty_only
 * @param $arrayelement
 * @return
 */
function survey_non_empty_only($arrayelement) {
    return strlen(trim($arrayelement)); // returns 0 if the arrayelement is empty
}

/**
 * survey_textarea_to_array
 * @param $textareacontent
 * @return
 */
function survey_textarea_to_array($textareacontent) {

    $textareacontent = trim($textareacontent);
    $textareacontent = str_replace("\r",'', $textareacontent);

    $rows = explode("\n", $textareacontent);

    $arraytextarea = array_filter($rows, 'survey_non_empty_only');

    return $arraytextarea;
}

/**
 * survey_clean_textarea_fields
 * @param $record, $fieldlist
 * @return
 */
function survey_clean_textarea_fields($record, $fieldlist) {
    foreach ($fieldlist as $field) {
        // do not forget some item may be undefined causing:
        // Notice: Undefined property: stdClass::$defaultvalue
        // as, for instance, disabled $defaultvalue field when $delaultoption == invitation
        if (isset($record->{$field})) {
            $temparray = survey_textarea_to_array($record->{$field});
            $record->{$field} = implode("\n", $temparray);
        }
    }
}

/**
 * survey_manage_item_deletion
 * @param $confirm, $cm, $itemid, $type, $plugin, $itemtomove, $surveyid
 * @return
 */
function survey_manage_item_deletion($confirm, $cm, $itemid, $type, $plugin, $itemtomove, $surveyid) {
    global $CFG, $DB, $OUTPUT;

    if (!$confirm) {
        // ask for confirmation
        // in the frame of the confirmation I need to declare whether some child will break the link
        $context = context_module::instance($cm->id);

        $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $itemid), MUST_EXIST);
        $a = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', 'items', $itemid);
        $message = get_string('askdeleteoneitem', 'survey', $a);

        // is there any child item link to break
        if ($childitems = $DB->get_records('survey_item', array('parentid' => $itemid), 'sortindex', 'sortindex')) { // sortindex is suposed to be a valid key
            $childitems = array_keys($childitems);
            $nodes = implode(', ', $childitems);
            $message .= get_string('deletebreaklinks', 'survey', $nodes);
            $labelyes = get_string('confirmitemsdeletion', 'survey');
        } else {
            $labelyes = get_string('yes');
        }

        $optionbase = array('id' => $cm->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_MANAGE, 'act' => SURVEY_DELETEITEM);

        $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRM, 'plugin' => $plugin, 'type' => $type, 'itemid' => $itemid, 'itm' => $itemtomove);
        $urlyes = new moodle_url('view.php', $optionsyes);
        $buttonyes = new single_button($urlyes, $labelyes);

        $optionsno = $optionbase + array('cnf' => SURVEY_NEGATE);
        $urlno = new moodle_url('view.php', $optionsno);
        $buttonno = new single_button($urlno, get_string('no'));

        echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
        echo $OUTPUT->footer();
        die;
    } else {
        switch ($confirm) {
            case SURVEY_CONFIRM:
                $deleted = array();
                $maxsortindex = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $cm->instance));
                if ($childrenseeds = $DB->get_records('survey_item', array('parentid' => $itemid), 'id', 'id, type, plugin')) {
                    // deleting an item with children
                    // I can not reorder - cancel - reorder - cancel
                    // because one I reorder orderindex in childdren items (in db) changes from the one stored in $childitems
                    // and at the second cycle I reorder wrong items
                    foreach ($childrenseeds as $childseed) {
                        require_once($CFG->dirroot.'/mod/survey/'.$childseed->type.'/'.$childseed->plugin.'/plugin.class.php');
                        $itemclass = 'survey'.$childseed->type.'_'.$childseed->plugin;
                        $item = new $itemclass($childseed->id);
                        $item->item_delete_item($childseed->id);
                    }
                }

                // get the content of the item for the closing message
                $context = context_module::instance($cm->id);

                $deletingrecord = $DB->get_record('survey_item', array('id' => $itemid), 'id, content, content_sid, externalname, sortindex', MUST_EXIST);
                $killedsortindex = $deletingrecord->sortindex;
                $a = survey_get_sid_field_content($deletingrecord, 'content');

                require_once($CFG->dirroot.'/mod/survey/'.$type.'/'.$plugin.'/plugin.class.php');
                $itemclass = 'survey'.$type.'_'.$plugin;
                $item = new $itemclass($itemid);
                $item->item_delete_item($itemid);

                // renum sortindex
                $sql = 'SELECT id
                        FROM {survey_item}
                        WHERE surveyid = :surveyid
                        AND sortindex > :killedsortindex
                        ORDER BY sortindex';
                $itemlist = $DB->get_recordset_sql($sql, array('surveyid' => $surveyid, 'killedsortindex' => $killedsortindex));
                $currentsortindex = $killedsortindex;
                foreach ($itemlist as $item) {
                    $DB->set_field('survey_item', 'sortindex', $currentsortindex, array('id' => $item->id));
                    $currentsortindex++;
                }
                $itemlist->close();

                if ($childrenseeds) {
                    $message = get_string('chaindeleted', 'survey', $a);
                } else {
                    $message = get_string('itemdeleted', 'survey', $a);
                }
                echo $OUTPUT->box($message, 'notice centerpara');
                break;
            case SURVEY_NEGATE:
                $message = get_string('usercanceled', 'survey');
                echo $OUTPUT->notification($message, 'notifyproblem');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $confirm = '.$confirm.'<br />';
                echo 'and the right "case" is missing<br />';
        }
    }
}

/**
 * survey_add_tree_node
 * @param $confirm, $cm, $itemid, $type
 * @return
 */
function survey_add_tree_node(&$tohidelist, &$sortindextohidelist) {
    global $DB;

    $i = count($tohidelist);
    $itemid = $tohidelist[$i-1];
    if ($childitems = $DB->get_records('survey_item', array('parentid' => $itemid, 'draft' => 0), 'sortindex', 'id, sortindex')) { // potrebbero non esistere
        foreach ($childitems as $childitem) {
            $tohidelist[] = (int)$childitem->id;
            $sortindextohidelist[] = $childitem->sortindex;
            survey_add_tree_node($tohidelist, $sortindextohidelist);
        }
    }
}

/**
 * survey_manage_item_hide
 * @param $confirm, $cm, $itemid, $type
 * @return
 */
function survey_manage_item_hide($confirm, $cm, $itemid, $type) {
    global $DB, $OUTPUT;

    // build tohidelist
    // qui devo selezionare tutto l'albero discendente
    $tohidelist = array($itemid);
    $sortindextohidelist = array();
    survey_add_tree_node($tohidelist, $sortindextohidelist);

    $itemstoprocess = count($tohidelist);
    if (!$confirm) {
        if (count($tohidelist) > 1) { // ask for confirmation
            $context = context_module::instance($cm->id);
            $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $itemid), MUST_EXIST);

            $a = new stdClass();
            $a->parentid = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', 'items', $itemid);
            $a->dependencies = implode(', ', $sortindextohidelist);
            $message = get_string('askitemstodraft', 'survey', $a);

            $optionbase = array('id' => $cm->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_MANAGE, 'act' => SURVEY_HIDEITEM);

            $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRM, 'itemid' => $itemid, 'type' => $type);
            $urlyes = new moodle_url('view.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('confirmitemstodraft', 'survey'));

            $optionsno = $optionbase + array('cnf' => SURVEY_NEGATE);
            $urlno = new moodle_url('view.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die;
        } else { // draft without asking
            $DB->set_field('survey_item', 'draft', 1, array('id' => $itemid));
            survey_reset_items_pages($cm->instance);
        }
    } else {
        switch ($confirm) {
            case SURVEY_CONFIRM:
                // draft items
                foreach ($tohidelist as $tohideitemid) {
                    $DB->set_field('survey_item', 'draft', 1, array('id' => $tohideitemid));
                }
                survey_reset_items_pages($cm->instance);
                break;
            case SURVEY_NEGATE:
                $itemstoprocess = 0;
                $message = get_string('usercanceled', 'survey');
                echo $OUTPUT->notification($message, 'notifyproblem');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $confirm = '.$confirm.'<br />';
                echo 'and the right "case" is missing<br />';
        }
    }
    return $itemstoprocess; // did you do something?
}

/**
 * survey_move_regular_items
 * @param $itemid, $in
 * @return
 */
function survey_move_regular_items($itemid, $newbasicform) {
    global $DB;

    // build tohidelist
    // qui devo selezionare tutto l'albero discendente
    $tohidelist = array($itemid);
    $sortindextohidelist = array();
    survey_add_regular_item_node($tohidelist, $sortindextohidelist, $newbasicform);
    array_shift($tohidelist); // $itemid has already been saved

    $itemstoprocess = count($tohidelist);

    foreach ($tohidelist as $tohideitemid) {
        $DB->set_field('survey_item', 'basicform', $newbasicform, array('id' => $tohideitemid));
    }

    return $itemstoprocess; // did you do something?
}

/**
 * survey_add_regular_item_node
 * @param $tohidelist, $sortindextohidelist, $in
 * @return
 */
function survey_add_regular_item_node(&$tohidelist, &$sortindextohidelist, $newbasicform) {
    global $DB;

    $i = count($tohidelist);
    $itemid = $tohidelist[$i-1];
    $comparison = ($newbasicform == SURVEY_NOTPRESENT) ? '<>' : '=';
    $where = 'parentid = :parentid AND basicform '.$comparison.' :basicform';
    $params = array('parentid' => $itemid, 'basicform' => SURVEY_NOTPRESENT);
    if ($childitems = $DB->get_records_select('survey_item', $where, $params, 'sortindex', 'id, sortindex')) { // potrebbero non esistere
        foreach ($childitems as $childitem) {
            $tohidelist[] = (int)$childitem->id;
            $sortindextohidelist[] = $childitem->sortindex;
            survey_add_regular_item_node($tohidelist, $sortindextohidelist, $newbasicform);
        }
    }
}

/**
 * survey_manage_item_show
 * @param $confirm, $cm, $itemid, $type
 * @return
 */
function survey_manage_item_show($confirm, $cm, $itemid, $type) {
    global $DB, $OUTPUT;

    // build toshowlist
    $toshowlist = array($itemid);
    $parentitem = $DB->get_record('survey_item', array('id' => $itemid), 'id, parentid, sortindex', MUST_EXIST);
    while (isset($parentitem->parentid)) {
        if ($parentitem = $DB->get_record('survey_item', array('id' => $parentitem->parentid, 'draft' => 1), 'id, parentid, sortindex')) { // potrebbe non esistere
            $toshowlist[] = $parentitem->id;
            $sortindextoshowlist[] = $parentitem->sortindex;
        }
    }

    $itemstoprocess = count($toshowlist);
    if (!$confirm) {
        if ($itemstoprocess > 1) { // ask for confirmation
            $context = context_module::instance($cm->id);
            $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $itemid), MUST_EXIST);

            $a = new stdClass();
            $a->lastitem = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', 'items', $itemid);
            $a->ancestors = implode(', ', $sortindextoshowlist);
            $message = get_string('askitemsshow', 'survey', $a);

            $optionbase = array('id' => $cm->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_MANAGE, 'act' => SURVEY_SHOWITEM, 'itemid' => $itemid);

            $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRM, 'type' => $type);
            $urlyes = new moodle_url('view.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('confirmitemsshow', 'survey'));

            $optionsno = $optionbase + array('cnf' => SURVEY_NEGATE);
            $urlno = new moodle_url('view.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die;
        } else { // show without asking
            $DB->set_field('survey_item', 'draft', 0, array('id' => $itemid));
            survey_reset_items_pages($cm->instance);
        }
    } else {
        switch ($confirm) {
            case SURVEY_CONFIRM:
                // draft items
                foreach ($toshowlist as $toshowitemid) {
                    $DB->set_field('survey_item', 'draft', 0, array('id' => $toshowitemid));
                }
                survey_reset_items_pages($cm->instance);
                break;
            case SURVEY_NEGATE:
                $itemstoprocess = 0;
                $message = get_string('usercanceled', 'survey');
                echo $OUTPUT->notification($message, 'notifyproblem');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $confirm = '.$confirm.'<br />';
                echo 'and the right "case" is missing<br />';
        }
    }
    return $itemstoprocess; // did you do something?
}

/**
 * survey_reorder_items
 * @param $itemtomove, $lastitembefore, $surveyid
 * @return
 */
function survey_reorder_items($itemtomove, $lastitembefore, $surveyid) {
    global $DB;

    // I start loading the id of the item I want to move
    $itemid = $DB->get_field('survey_item', 'id', array('surveyid' => $surveyid, 'sortindex' => $itemtomove));

    // Am I moving it backward or forward?
    if ($itemtomove > $lastitembefore) {
        // moving the item backward
        $searchitem = $itemtomove-1;
        $replaceitem = $itemtomove;

        while ($searchitem > $lastitembefore) {
            $DB->set_field('survey_item', 'sortindex', $replaceitem, array('surveyid' => $surveyid, 'sortindex' => $searchitem));
            $replaceitem = $searchitem;
            $searchitem--;
        }

        $DB->set_field('survey_item', 'sortindex', $replaceitem, array('surveyid' => $surveyid, 'id' => $itemid));
    } else {
        // moving the item forward
        $searchitem = $itemtomove+1;
        $replaceitem = $itemtomove;

        while ($searchitem <= $lastitembefore) {
            $DB->set_field('survey_item', 'sortindex', $replaceitem, array('surveyid' => $surveyid, 'sortindex' => $searchitem));
            $replaceitem = $searchitem;
            $searchitem++;
        }

        $DB->set_field('survey_item', 'sortindex', $replaceitem, array('id' => $itemid));
    }

    // you changed item order
    // so, do no forget to reset items per page
    survey_reset_items_pages($surveyid);
}

/**
 * survey_assign_pages
 * @param $canaccessadvancedform=0, $reset=false
 * @return
 */
function survey_assign_pages($canaccessadvancedform=false) {
    global $DB, $survey;

    // were pages assigned?
    $pagefield = ($canaccessadvancedform) ? 'advancedformpage' : 'basicformpage';
    if (!$pagenumber = $DB->get_field('survey_item', 'MIN('.$pagefield.')', array('surveyid' => $survey->id, 'draft' => 0))) {
        $lastwaspagebreak = true; // whether 2 page breaks in line, the second one is ignored
        $pagenumber = 1;
        $conditions = array('surveyid' => $survey->id, 'draft' => 0);
        if ($items = $DB->get_recordset('survey_item', $conditions, 'sortindex', 'id, type, plugin, parentid, '.$pagefield.', sortindex')) {
            foreach ($items as $item) {

                if ($item->plugin == 'pagebreak') { // è un page break
                    if (!$lastwaspagebreak) {
                        $pagenumber++;
                    }
                    $lastwaspagebreak = true;
                    continue;
                } else {
                    $lastwaspagebreak = false;
                }
                if ($survey->newpageforchild) {
                    if (!empty($item->parentid)) {
                        if (!$parentpage = $DB->get_field('survey_item', $pagefield, array('id' => $item->parentid))) { // is still == 0
                            throw new moodle_exception('pagenotassigned', 'survey', $item->parentid);
                        }
                        if ($parentpage == $pagenumber) {
                            $pagenumber++;
                        }
                    }
                }
                $DB->set_field('survey_item', $pagefield, $pagenumber, array('id' => $item->id));
            }
            $items->close();
        }
    }

    return $pagenumber;
}

/**
 * survey_next_not_empty_page
 * @param $surveyid, $canaccessadvancedform, $formpage, $forward, $submissionid=0, $maxformpage=0
 * @return
 */
function survey_next_not_empty_page($surveyid, $canaccessadvancedform, $formpage, $forward, $submissionid=0, $maxformpage=0) {
    global $DB;
    // a seguito delle risposte ottenute, nella pagina >> o << a quella che ti passo potrebbero non esserci domande da mostrare
    // trova la prima pagina successiva CON domande
    // nel caso peggiore otterrò 1 o $maxformpage
    // se anche in $maxformpage non dovessi avere item da mostrare, restituisco returnpage = 0

    if (!empty($forward) && empty($maxformpage)) {
        throw new moodle_exception('emptymaxformpage', 'survey');
    }

    // mi trovo in $formpage
    if ($forward) {
        $i = ++$formpage;
        $lastpage = $maxformpage+1; // maxpage = $maxformpage, but I have to add      1 because of ($i != $lastpage)
    } else {
        $i = --$formpage;
        $lastpage = 0;              // minpage = 1,            but I have to subtract 1 because of ($i != $lastpage)
    }

    do {
        if ($returnpage = survey_page_has_items($surveyid, $canaccessadvancedform, $i, $submissionid)) {
            break;
        }
        $i = ($forward) ? ++$i : --$i;
    } while ($i != $lastpage);

    return $returnpage;
}

/**
 * survey_page_has_items
 * @param $surveyid, $canaccessadvancedform, $formpage, $submissionid
 * @return
 */
function survey_page_has_items($surveyid, $canaccessadvancedform, $formpage, $submissionid) {
    global $DB;

    $sql = survey_fetch_items_seeds($canaccessadvancedform);
    $params = array('surveyid' => $surveyid, 'formpage' => $formpage);
    $itemseeds = $DB->get_records_sql($sql, $params);

    // start looking ONLY at empty($item->parentid) because it doesn't involve extra queries
    foreach ($itemseeds as $itemseed) {
        // se è un elemento di formato, non conta
        if ($itemseed->type == SURVEY_FORMAT) continue;

        if (empty($itemseed->parentid)) {
            // se almeno uno ha il parentid vuoto, ho finito
            return $formpage;
        }
    }

    foreach ($itemseeds as $itemseed) {
        // devo verificare che la condizione di visibilità sia verificata
        if (survey_child_is_allowed_static($submissionid, $itemseed)) {
            return $formpage;
        }
    }

    // se non sei riuscito ad uscire nelle due occasioni precedenti... dichiara la sconfitta
    return 0;
}

/**
 * userform_child_is_allowed_dynamic
 * from parentcontent defines whether an item is supposed to be active (not disabled) in the form so needs validation
 * ----------------------------------------------------------------------
 * this function is called when $survey->newpageforchild == false
 * that is the current survey lives in just one single web page
 * ----------------------------------------------------------------------
 * Am I geting submitted data from $fromform or from table 'survey_userdata'?
 *     - if I get it from $fromform or from $data[] I need to use userform_child_is_allowed_dynamic
 *     - if I get it from table 'survey_userdata'   I need to use survey_child_is_allowed_static
 * ----------------------------------------------------------------------
 * @param: $parentcontent, $parentsubmitted
 * @return
 */
function survey_child_is_allowed_static($submissionid, $itemrecord) {
    global $DB;

    if (!$itemrecord->parentid) return TRUE;

    $where = array('submissionid' => $submissionid, 'itemid' => $itemrecord->parentid);
    $givenanswer = $DB->get_field('survey_userdata', 'content', $where);
    return ($givenanswer === $itemrecord->parentvalue);
}

/**
 * survey_set_prefill
 * @param $survey, $canaccessadvancedform, $formpage, $submissionid
 * @return
 */
function survey_set_prefill($survey, $canaccessadvancedform, $formpage, $submissionid) {
    global $CFG, $DB;

    $prefill = array();
    $sql = survey_fetch_items_seeds($canaccessadvancedform);
    $params = array('surveyid' => $survey->id, 'formpage' => $formpage);
    if ($itemseeds = $DB->get_recordset_sql($sql, $params)) {
        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

            $olduserdata = $DB->get_record('survey_userdata', array('submissionid' => $submissionid, 'itemid' => $item->itemid));
            $prefill = array_merge($prefill, $item->userform_set_prefill($olduserdata));
        }
        $itemseeds->close();
    }
    return $prefill;
}

/*
 * there are items spreading out their value over more than one single field
 * so you may have more than one $fromform element referring to the same item
 * Es.:
 *   $fieldname = survey_datetime_1452_day
 *   $fieldname = survey_datetime_1452_year
 *   $fieldname = survey_datetime_1452_month
 *   $fieldname = survey_datetime_1452_hour
 *   $fieldname = survey_datetime_1452_minute
 *
 *   $fieldname = survey_select_1452_select
 *
 *   $fieldname = survey_age_1452_check
 *
 *   $fieldname = survey_rate_1452_group
 *   $fieldname = survey_rate_1452_1
 *   $fieldname = survey_rate_1452_2
 *   $fieldname = survey_rate_1452_3
 *
 *   $fieldname = survey_radio_1452_noanswer
 *   $fieldname = survey_radio_1452_text
 *
 * questa funzione svolge il seguente compito:
 * 1. raggruppa le informazioni, (eventualmente) distribuite sui vari elementi della
 *    form che fanno riferimento allo stesso itemid, nel vettore $infoperitem
 *
 *    Es.:
 *    $infoperitem = Array (
 *        [148] => stdClass Object (
 *            [surveyid] => 1
 *            [submissionid] => 60
 *            [type] => field
 *            [plugin] => age
 *            [itemid] => 148
 *            [extra] => Array (
 *                [year] => 5
 *                [month] => 9
 *            )
 *        )
 *        [149] => stdClass Object (
 *            [surveyid] => 1
 *            [submissionid] => 63
 *            [type] => field
 *            [plugin] => boolean
 *            [itemid] => 149
 *            [extra] => Array (
 *                [noanswer] => 1
 *            )
 *        )
 *        [150] => stdClass Object (
 *            [surveyid] => 1
 *            [submissionid] => 63
 *            [type] => field
 *            [plugin] => character
 *            [itemid] => 150
 *            [extra] => Array (
 *                [mainelement] => horse
 *            )
 *        )
 *        [151] => stdClass Object (
 *            [surveyid] => 1
 *            [submissionid] => 60
 *            [type] => field
 *            [plugin] => fileupload
 *            [itemid] => 151
 *            [extra] => Array (
 *                [filemanager] => 667420320
 *            )
 *        )
 * 2. once $infoperitem is onboard...
 *    aggiorno o creo il record appropriato
 *    chiedendo alla parent class di gestire l'informazione che le appartiene
 *    passandole $iteminfo->extra
 */
function survey_save_user_data($fromform) {
    global $CFG, $DB, $OUTPUT;

    $regexp = '~'.SURVEY_ITEMPREFIX.'_([a-z]+)_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';

    $infoperitem = array();
    foreach ($fromform as $itemname => $content) {
        // var_dump($matches);
        // $matches = array{
        //   0 => string 'survey_field_radiobutton_1452' (length=27)
        //   1 => string 'field' (length=5)
        //   2 => string 'radiobutton' (length=11)
        //   3 => string '1452' (length=4)
        // }
        // $matches = array{
        //   0 => string 'survey_field_radiobutton_1452_check' (length=33)
        //   1 => string 'field' (length=5)
        //   2 => string 'radiobutton' (length=11)
        //   3 => string '1452' (length=4)
        //   4 => string 'check' (length=5)
        // }
        // $matches = array{}
        //   0 => string 'survey_field_checkbox_1452_73' (length=30)
        //   1 => string 'field' (length=5)
        //   2 => string 'checkbox' (length=8)
        //   3 => string '1452' (length=4)
        //   4 => string '73' (length=2)
        if (!preg_match($regexp, $itemname, $matches)) {
            // button or something not relevant
            switch ($itemname) {
                case 's': // <-- s is the survey id
                    $surveyid = $content;
                    break;
                case 'submissionid':
                    $submissionid = $content;
                    break;
                default:
                    // this is the black hole where is thrown each useless info like:
                    // - formpage
                    // - nextbutton
                    // and some more
            }
            continue;
        }

        $itemid = $matches[3]; // itemid dell'elemento della form (o del group di elementi della form)
        if (!isset($infoperitem[$itemid])) {
            $infoperitem[$itemid] = new stdClass();
            $infoperitem[$itemid]->surveyid = $surveyid;
            $infoperitem[$itemid]->submissionid = $submissionid;
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

//     if (isset($infoperitem)) {
//         echo '$infoperitem = <br />';
//         print_object($infoperitem);
//     } else {
//         echo 'Non ho trovato nulla<br />';
//     }

// once $infoperitem is onboard...
//    aggiorno o creo il record appropriato
//    chiedendo alla parent class di gestire l'informazione che le appartiene
//    passandole $iteminfo->extra

    foreach ($infoperitem as $iteminfo) {
        if (!$olduserdata = $DB->get_record('survey_userdata', array('submissionid' => $iteminfo->submissionid, 'itemid' => $iteminfo->itemid))) {
            // Quickly make one now!
            $olduserdata = new stdClass();
            $olduserdata->surveyid = $iteminfo->surveyid;
            $olduserdata->submissionid = $iteminfo->submissionid;
            $olduserdata->itemid = $iteminfo->itemid;
            $olduserdata->content = 'dummy_content';

            $id = $DB->insert_record('survey_userdata', $olduserdata);
            $olduserdata = $DB->get_record('survey_userdata', array('id'=>$id));
        }
        $olduserdata->timecreated = time();

        // ora che sai la plugin passa tutti i campi necessari per il salvataggio del dato alla classe padre
        $item = survey_get_item($iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

        // in this method I update $olduserdata->content
        // I do not save to database
        $item->userform_save($iteminfo->extra, $olduserdata);

        $DB->update_record('survey_userdata', $olduserdata);
    }
}

/**
 * survey_i_can_read
 * @param $survey, $mygroup, $ownerid
 * @return whether I am allowed to see the survey submitted by the user belonging to $ownergroup
 */
function survey_i_can_read($survey, $mygroup, $ownerid) {
    global $USER, $COURSE;

    switch ($survey->readaccess) {
        case SURVEY_NONE:
            return false;
            break;
        case SURVEY_OWNER:
            return ($USER->id == $ownerid);
            break;
        case SURVEY_GROUP:
            $return = false;
            // $ownergroupid the group ID of the owner of the submitted survey record
            $ownergroup = groups_get_user_groups($COURSE->id, $ownerid);
            foreach ($ownergroup[0] as $ownergroupid) { // [0] is for all groupings combined
                if (in_array($ownergroupid, $mygroup)) {
                    $return = true;
                    break;
                }
            }
            return $return;
            break;
        case SURVEY_ALL:
            return true;
            break;
        default:
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I have $survey->readaccess = '.$survey->readaccess.'<br />';
            echo 'and the right "case" is missing<br />';
    }
}

/**
 * survey_i_can_edit
 * @param $survey, $mygroup, $ownerid
 * @return whether I am allowed to edit the survey submitted by the user belonging to $ownergroup
 */
function survey_i_can_edit($survey, $mygroup, $ownerid) {
    global $USER, $COURSE;

    switch ($survey->editaccess) {
        case SURVEY_NONE:
            return false;
            break;
        case SURVEY_OWNER:
            return ($USER->id == $ownerid);
            break;
        case SURVEY_GROUP:
            $return = false;
            // $ownergroupid the group ID of the owner of the submitted survey record
            $ownergroup = groups_get_user_groups($COURSE->id, $ownerid);
            foreach ($ownergroup[0] as $ownergroupid) { // [0] is for all groupings combined
                if (in_array($ownergroupid, $mygroup)) {
                    $return = true;
                    break;
                }
            }
            return $return;
            break;
        case SURVEY_ALL:
            return true;
            break;
        default:
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I have $survey->editaccess = '.$survey->editaccess.'<br />';
            echo 'and the right "case" is missing<br />';
    }
}

/**
 * survey_i_can_delete
 * @param $survey, $mygroup, $ownerid
 * @return whether I am allowed to delete the survey submitted by the user belonging to $ownergroup
 */
function survey_i_can_delete($survey, $mygroup, $ownerid) {
    global $USER, $COURSE;

    switch ($survey->deleteaccess) {
        case SURVEY_NONE:
            return false;
            break;
        case SURVEY_OWNER:
            return ($USER->id == $ownerid);
            break;
        case SURVEY_GROUP:
            $return = false;
            // $ownergroupid the group ID of the owner of the submitted survey record
            $ownergroup = groups_get_user_groups($COURSE->id, $ownerid);
            foreach ($ownergroup[0] as $ownergroupid) { // [0] is for all groupings combined
                if (in_array($ownergroupid, $mygroup)) {
                    $return = true;
                    break;
                }
            }
            return $return;
            break;
        case SURVEY_ALL:
            return true;
            break;
        default:
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I have $survey->deleteaccess = '.$survey->deleteaccess.'<br />';
            echo 'and the right "case" is missing<br />';
    }
}

/**
 * survey_manage_submission_deletion
 * @param $cm, $confirm, $submissionid
 * @return
 */
function survey_manage_submission_deletion($cm, $confirm, $submissionid) {
    global $USER, $DB, $OUTPUT;

    if (!$confirm) {
        // ask for confirmation
        $submission = $DB->get_record('survey_submissions', array('id' => $submissionid));

        $a = new stdClass();
        $a->timecreated = userdate($submission->timecreated);
        $a->timemodified = userdate($submission->timemodified);
        if ($submission->userid != $USER->id) {
            $a->fullname = fullname($DB->get_record('user', array('id' => $submission->userid), 'firstname, lastname', MUST_EXIST));
            if ($a->timecreated == $a->timemodified) {
                $message = get_string('askdeleteonesurveynevermodified', 'survey', $a);
            } else {
                $message = get_string('askdeleteonesurvey', 'survey', $a);
            }
        } else {
            if ($a->timecreated == $a->timemodified) {
                $message = get_string('askdeletemysurveynevermodified', 'survey', $a);
            } else {
                $message = get_string('askdeletemysurvey', 'survey', $a);
            }
        }

        $optionbase = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_MANAGE, 'act' => SURVEY_DELETESURVEY);

        $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRM, 'submissionid' => $submissionid);
        $urlyes = new moodle_url('view.php', $optionsyes);
        $buttonyes = new single_button($urlyes, get_string('confirmsurveydeletion', 'survey'));

        $optionsno = $optionbase + array('cnf' => SURVEY_NEGATE);
        $urlno = new moodle_url('view.php', $optionsno);
        $buttonno = new single_button($urlno, get_string('no'));

        echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
        echo $OUTPUT->footer();
        die;
    } else {
        switch ($confirm) {
            case SURVEY_CONFIRM:
                $DB->delete_records('survey_userdata', array('submissionid' => $submissionid));
                $DB->delete_records('survey_submissions', array('id' => $submissionid));
                echo $OUTPUT->notification(get_string('surveydeleted', 'survey'), 'notifyproblem');
                break;
            case SURVEY_NEGATE:
                $message = get_string('usercanceled', 'survey');
                echo $OUTPUT->notification($message, 'notifyproblem');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $confirm = '.$confirm.'<br />';
                echo 'and the right "case" is missing<br />';
        }
    }
}

/**
 * survey_manage_all_surveys_deletion
 * @param $cm, $confirm, $submissionid
 * @return
 */
function survey_manage_all_surveys_deletion($confirm, $surveyid) {
    global $DB, $OUTPUT;

    if (!$confirm) {
        // ask for confirmation
        $message = get_string('askdeleteallsurveys', 'survey');

        $optionbase = array('s' => $surveyid, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_MANAGE, 'surveyid' => $surveyid, 'act' => SURVEY_DELETEALLSURVEYS);

        $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRM);
        $urlyes = new moodle_url('view.php', $optionsyes);
        $buttonyes = new single_button($urlyes, get_string('confirmallsurveysdeletion', 'survey'));

        $optionsno = $optionbase + array('cnf' => SURVEY_NEGATE);
        $urlno = new moodle_url('view.php', $optionsno);
        $buttonno = new single_button($urlno, get_string('no'));

        echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
        echo $OUTPUT->footer();
        die;
    } else {
        switch ($confirm) {
            case SURVEY_CONFIRM:
                $sql = 'SELECT s.id
                            FROM {survey_submissions} s
                            WHERE s.surveyid = :surveyid';
                $idlist = $DB->get_records_sql($sql, array('surveyid' => $surveyid));

                foreach ($idlist as $submissionid) {
                    $DB->delete_records('survey_userdata', array('submissionid' => $submissionid->id));
                }

                $DB->delete_records('survey_submissions', array('surveyid' => $surveyid));
                echo $OUTPUT->notification(get_string('allsurveysdeleted', 'survey'), 'notifyproblem');
                break;
            case SURVEY_NEGATE:
                $message = get_string('usercanceled', 'survey');
                echo $OUTPUT->notification($message, 'notifyproblem');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $confirm = '.$confirm.'<br />';
                echo 'and the right "case" is missing<br />';
        }
    }
}

/**
 * survey_get_my_groups
 * @param $cm
 * @return
 */
function survey_get_my_groups($cm) {
    global $USER, $COURSE;

    if (groups_get_activity_groupmode($cm, $COURSE) == SEPARATEGROUPS) {   // Separate groups are being used
        $mygroupslist = groups_get_user_groups($COURSE->id, $USER->id); // this is 0 whether no groups are set
        $mygroups = array();
        foreach ($mygroupslist[0] as $mygroupid) { // [0] is for all groupings combined
            $mygroups[] = $mygroupid;
        }
    } else {
        $mygroups = array();
    }

    return $mygroups;
}

/**
 * survey_show_thanks_page
 * @param $survey, $cm
 * @return
 */
function survey_show_thanks_page($survey, $cm) {
    global $OUTPUT;

//$output = file_rewrite_pluginfile_urls($item->content, 'pluginfile.php', $context->id, 'mod_survey', 'items', $item->itemid);
//$mform->addElement('static', $item->type.'_'.$item->itemid.'_extrarow', $elementnumber, $output, array('class' => 'indent-'.$item->indent)); // here I  do not strip tags to content


    if (!empty($survey->thankshtml)) {
        $context = context_module::instance($cm->id);

        $message = file_rewrite_pluginfile_urls($survey->thankshtml, 'pluginfile.php', $context->id, 'mod_survey', 'thankshtml', $survey->id);
    } else {
        $message = get_string('defaultthanksmessage', 'survey');
    }

    $paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABSUBMISSIONS);
    // just to save a query
    $alreadysubmitted = empty($survey->maxentries) ? 0 : $DB->count_records('survey_submissions', array('userid' => $USER->id));
    if (($alreadysubmitted < $survey->maxentries) || empty($survey->maxentries)) { // se può inviare una nuova survey
        $paramurl['pag'] = SURVEY_SUBMISSION_NEW;
        $buttonurl = new moodle_url('view.php', $paramurl);
        $onemore = new single_button($buttonurl, get_string('onemorerecord', 'survey'));

        $paramurl['pag'] = SURVEY_SUBMISSION_MANAGE;
        $buttonurl = new moodle_url('view.php', $paramurl);
        $gotolist = new single_button($buttonurl, get_string('gotolist', 'survey'));

        echo $OUTPUT->confirm($message, $onemore, $gotolist);
    } else {
        echo $OUTPUT->box_start();
        echo $message;
        $paramurl['pag'] = SURVEY_SUBMISSION_MANAGE;
        $buttonurl = new moodle_url('view.php', $paramurl);
        echo $OUTPUT->single_button($buttonurl, get_string('gotolist'));
        echo $OUTPUT->box_end();
    }
}

/**
 * survey_export
 * @param $cm, $fromform, $survey
 * @return
 */
function survey_export($cm, $fromform, $survey) {
    global $CFG, $DB;

    $params = array();
    $params['surveyid'] = $survey->id;

    // only fields
    // no matter for the page
    // elenco dei campi che l'utente vuole vedere nel file esportato
    $itemlistsql = 'SELECT si.id, si.fieldname, si.plugin
                    FROM {survey_item} si
                    WHERE si.surveyid = :surveyid
                        AND si.type = "'.SURVEY_FIELD.'"'; //<-- ONLY FIELDS hold data, COLELCTION_FORMAT items do not hold data
    if ($fromform->basicform == SURVEY_FILLONLY) {
        // I need records with:
        //     basicform == SURVEY_FILLONLY OR basicform == SURVEY_FILLANDSEARCH
        $itemlistsql .= ' AND si.basicform <> '.SURVEY_NOTPRESENT;
    }
    if (!isset($fromform->includedraft)) {
        $itemlistsql .= ' AND si.draft = 0';
    }
    $itemlistsql .= ' ORDER BY sortindex';

    // I need get_records_sql instead of get_records because of '<> SURVEY_NOTPRESENT'
    if (!$fieldidlist = $DB->get_records_sql($itemlistsql, $params)) {
        return SURVEY_NOFIELDSSELECTED;
        die;
    }

// echo '$fieldidlist:';
// var_dump($fieldidlist);
// die;

    $richsubmissionssql = 'SELECT s.id, s.status, s.timecreated, s.timemodified, ';
    if (empty($survey->anonymous)) {
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
    if ($fromform->basicform == SURVEY_FILLONLY) {
        $richsubmissionssql .= ' AND si.basicform <> '.SURVEY_NOTPRESENT;
    }
    if ($fromform->status != SURVEY_STATUSBOTH) {
        $richsubmissionssql .= ' AND s.status = :status';
        $params['status'] = $fromform->status;
    }
    $richsubmissionssql .= ' ORDER BY s.id ASC, s.timecreated ASC, si.sortindex ASC';

    $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $params);
    if ($richsubmissions->valid()) {
        if ($fromform->downloadtype == SURVEY_DOWNLOADCSV) {
            header('Content-Transfer-Encoding: utf-8');
            header('Content-Disposition: attachment; filename='.$survey->name.'.csv');
            header('Content-Type: text/comma-separated-values');

            $worksheet = null;
        } else { // SURVEY_DOWNLOADXLS
            require_once($CFG->libdir.'/excellib.class.php');
            $filename = $survey->name.'.xls';
            $workbook = new MoodleExcelWorkbook('-');
            $workbook->send($filename);

            $worksheet = array();
            $worksheet[0] =& $workbook->add_worksheet(get_string('survey', 'survey'));
        }

        survey_export_print_header($survey, $fieldidlist, $fromform, $worksheet);

        // reduce the weight of $fieldidlist storing no longer relevant infos
        $fieldidlist = array_flip(array_keys($fieldidlist));

        // get user group (to filter survey to download)
        $mygroups = survey_get_my_groups($cm);
        $canreadallsubmissions = survey_user_can_read_all_submissions($cm);

        $oldrichsubmissionid = 0;
        $fieldscount = count($fieldidlist);

        foreach ($richsubmissions as $richsubmission) {

            if (!$canreadallsubmissions && !survey_i_can_read($survey, $mygroups, $richsubmission->userid)) continue;

            if ($oldrichsubmissionid == $richsubmission->id) {
                $recordtoexport[$richsubmission->itemid] = survey_decode_content($richsubmission);
            } else {
                if (!empty($oldrichsubmissionid)) { // new richsubmissionid, stop managing old record
                    // write old record
                    survey_export_close_record($recordtoexport, $fromform->downloadtype, $worksheet);
                }
                $oldrichsubmissionid = $richsubmission->id;

                // begin a new record
                $recordtoexport = array();
                if (empty($survey->anonymous)) {
                    $recordtoexport['firstname'] = $richsubmission->firstname;
                    $recordtoexport['lastname'] = $richsubmission->lastname;
                }
                $recordtoexport += $fieldidlist;
                $recordtoexport['timecreated'] = userdate($richsubmission->timecreated);
                $recordtoexport['timemodified'] = userdate($richsubmission->timemodified);
                $recordtoexport[$richsubmission->itemid] = survey_decode_content($richsubmission);
            }
        }
        $richsubmissions->close();
        survey_export_close_record($recordtoexport, $fromform->downloadtype, $worksheet);

        if ($fromform->downloadtype == SURVEY_DOWNLOADXLS) {
            $workbook->close();
        }
    } else {
        return SURVEY_NORECORDSFOUND;
    }
}

/**
 * survey_export_print_header
 * @param $survey, $fieldidlist, $fromform, $worksheet
 * @return
 */
function survey_export_print_header($survey, $fieldidlist, $fromform, $worksheet) {

    // write the names of the fields in the header of the file to export
    $recordtoexport = array();
    if (empty($survey->anonymous)) {
        $recordtoexport[] = get_string('firstname');
        $recordtoexport[] = get_string('lastname');
    }
    foreach ($fieldidlist as $singlefield) {
        $recordtoexport[] = empty($singlefield->fieldname) ? $singlefield->plugin.'_'.$singlefield->id : $singlefield->fieldname;
    }
    $recordtoexport[] = get_string('timecreated', 'survey');
    $recordtoexport[] = get_string('timemodified', 'survey');

    if ($fromform->downloadtype == SURVEY_DOWNLOADCSV) {
        echo implode(',', $recordtoexport)."\n";
    } else { // SURVEY_DOWNLOADXLS
        $col = 0;
        foreach ($recordtoexport as $header) {
            $worksheet[0]->write(0, $col, $header, '');
            $col++;
        }
    }
}

/**
 * survey_decode_content
 * @param $richsubmission
 * @return
 */
function survey_decode_content($richsubmission) {
    global $CFG;

    $plugin = $richsubmission->plugin;
    $itemid = $richsubmission->itemid;
    $content = $richsubmission->content;
    $item = survey_get_item($itemid, SURVEY_FIELD, $plugin);

    $return = empty($content) ? '' : $item->userform_db_to_export($richsubmission);
    return $return;
}

/**
 * survey_export_close_record
 * @param $recordtoexport, $downloadtype, $worksheet
 * @return
 */
function survey_export_close_record($recordtoexport, $downloadtype, $worksheet) {
    static $row = 0;

    if ($downloadtype == SURVEY_DOWNLOADCSV) {
        echo implode(',', $recordtoexport)."\n";
    } else {
        // SURVEY_DOWNLOADXLS
        $row++;
        $col = 0;
        foreach ($recordtoexport as $value) {
            $worksheet[0]->write($row, $col, $value, '');
            $col++;
        }
    }
}

/**
 * survey_find_submissions
 * @param $findparams
 * @return
 */
function survey_find_submissions($findparams) {
    global $DB;

    foreach ($findparams as $itemid => $elementcontent) {
        // mi interessano solo i campi della search form che contengono qualcosa ma che non contengono SURVEY_NOANSWERVALUE
        if ($elementcontent == SURVEY_NOANSWERVALUE) {
            unset($findparams[$itemid]);
        }
    }

    // il processo di ricerca è complicato
    // un procedimento è il seguente:
    // step 1:
    //     trova tutti gli ID delle submissions che soddisfano la prima condizione
    // step 2:
    //     verifica che ogni submissionid trovata soddisfi tutte le altre condizioni
    //     se almeno una fallisce cancella la submission id dalla collezione iniziale
    //     altrimenti, hai trovato la submission che cerchi

    // la form di ricerca è vuota: restituisci tutte le submissions
    if (!$findparams) return;

    $keys = array_keys($findparams);
    $firstitemid = $keys[0];
    $firstcontent = $findparams[$firstitemid];

    // array_shift never does what I would
    unset($findparams[$firstitemid]); // drop the first element of $findparams

    // should work but does not: MDL-27629
    //$submissionidlist = $DB->get_records('survey_userdata', array('itemid' => $firstitemid, $DB->sql_compare_text('content') => $firstcontent), 'submissionid');

    $where = 'itemid = :itemid AND '.$DB->sql_compare_text('content').' = :content';
    $params = array('itemid' => $firstitemid, 'content' => (string)$firstcontent);
    if (!$submissionidlist = $DB->get_records_select('survey_userdata', $where, $params, 'submissionid', 'submissionid')) {
        // nessuna submission soddisfa le richieste
        return array();
    } else {
        $submissionidlist = array_keys($submissionidlist); // list of submission id matching the first constraint

    }

    if (!$findparams) {
        // se non ci sono altri vincoli: hai finito
        return $submissionidlist;
    }

    foreach ($findparams as $itemid => $elementcontent) {
        $where = 'submissionid IN ('.implode(',', $submissionidlist).')
                      AND itemid = :itemid
                      AND content = :elementcontent';
        $params = array('itemid' => $itemid, 'elementcontent' => (string)$elementcontent);
        if ($submissionidlist = $DB->get_records_select('survey_userdata', $where, $params, 'submissionid', 'submissionid')) {
            $submissionidlist = array_keys($submissionidlist);
        } else {
            // nessuna submission soddisfa le richieste
            return array();
        }
    }
    return $submissionidlist;
}

/**
 * survey_display_user_feedback
 * @param $userfeedback
 * @return
 */
function survey_display_user_feedback($userfeedback) {
    // look at position 1
    $bit = $userfeedback & 2; // bitwise logic
    if ($bit) { // edit
        $bit = $userfeedback & 1; // bitwise logic
        if ($bit) {
            $message = get_string('itemeditok', 'survey');
        } else {
            $message = get_string('itemeditfail', 'survey');
        }
    } else {    // add
        $bit = $userfeedback & 1; // bitwise logic
        if ($bit) {
            $message = get_string('itemaddok', 'survey');
        } else {
            $message = get_string('itemaddfail', 'survey');
        }
    }

    for ($position = 2; $position <= 5; $position++) {
        $bit = $userfeedback & pow(2, $position); // bitwise logic
        switch ($position) {
            case 2: // a chain of items is now no longer drafted
                if ($bit) {
                    $message .= '<br />'.get_string('itemeditshow', 'survey');
                }
                break;
            case 3: // a chain of items is now drafted because one item was drafted
                if ($bit) {
                    $message .= '<br />'.get_string('itemedithidedraft', 'survey');
                }
                break;
            case 4: // a chain of items was moved in the user entry form
                if ($bit) {
                    $message .= '<br />'.get_string('itemeditshowinbasicform', 'survey');
                }
                break;
            case 5: // a chain of items was removed from the user entry form
                if ($bit) {
                    $message .= '<br />'.get_string('itemedithidefrombasicform', 'survey');
                }
                break;
        }
    }
    return $message;
}

/**
 * survey_plugin_build
 * @param $targetuser, $surveyid
 * @return
 */
function survey_plugin_build($data) {
    global $CFG, $DB;

    $langtree = array();

    $pluginname = clean_filename($data->pluginname);
    $temp_subdir = "mod_survey/surveyplugins/$pluginname";
    $temp_basedir = $CFG->tempdir.'/'.$temp_subdir;

    $master_basepath = "$CFG->dirroot/mod/survey/templatemaster";
    $master_filelist = get_directory_list($master_basepath);

    foreach ($master_filelist as $master_file) {
        $master_fileinfo = pathinfo($master_file);
        // crea la struttura nella cartella temporanea
        // la cartella si crea SENZA il $CFG->tempdir/
        $temp_path = $temp_subdir.'/'.dirname($master_file);
        make_temp_directory($temp_path); // <-- creata la cartella del file corrente

        $temp_fullpath = $CFG->tempdir.'/'.$temp_path;

// echo '<hr />Intervengo sul file: '.$master_file.'<br />';
// echo $master_fileinfo["dirname"] . "<br />";
// echo $master_fileinfo["basename"] . "<br />";
// echo $master_fileinfo["extension"] . "<br />";
// echo dirname($master_file) . "<br />";

        if ($master_fileinfo['basename'] == 'icon.gif') {
            // copia icon.gif
            copy($master_basepath.'/'.$master_file, $temp_fullpath.'/'.$master_fileinfo['basename']);
            continue;
        }

        if ($master_fileinfo['dirname'] == 'lang/en') { // è il file di lingua. Già fatto!
            continue;
        }

        if ($master_fileinfo['basename'] == 'lib.php') {
            // I need to scan all my surveyitem and plugin
            // and copy them
            // Start by reading the master
            $libcontent = file_get_contents($master_basepath.'/'.$master_file);
            // delete any trailing spaces or \n at the and of the file
            $libcontent = rtrim($libcontent);
            // drop off the closed brace at the end of the file
            $libcontent = substr($libcontent, 0, -1);
            // replace surveyTemplatePluginMaster with the name of the current survey
            $libcontent = str_replace('surveyTemplatePluginNamePlaceholder', $pluginname, $libcontent);
            // finalize the libcontent
            survey_wlib_content($libcontent, $data->surveyid, $data, $langtree);
            // open
            $filehandler = fopen($temp_basedir.'/'.$master_file, 'w');
            // write
            fwrite($filehandler, $libcontent);
            // close
            fclose($filehandler);

            // /////////////////////////////////////////////////////////////////////////////////////
            // now write string file
            // /////////////////////////////////////////////////////////////////////////////////////

            // in which language the user is using Moodle?

            $userlang = current_language();
            $temp_path = $CFG->tempdir.'/'.$temp_subdir.'/lang/'.$userlang;

            // this is the language folder of the strings hardcoded in the survey
            // the folder lang/en already exist
            if ($userlang != 'en') {
                // I need to create the folder lang/it
                make_temp_directory($temp_subdir.'/lang/'.$userlang);
            }

//echo '$master_basepath = '.$master_basepath.'<br />';

            $filecopyright = file_get_contents($master_basepath.'/lang/en/surveytemplate_pluginname.php');
            // replace surveyTemplatePluginMaster with the name of the current survey
            $filecopyright = str_replace('surveyTemplatePluginNamePlaceholder', $pluginname, $filecopyright);

            $savedstrings = $filecopyright.survey_extract_original_string($langtree);

//echo '<textarea rows="30" cols="100">'.$savedstrings.'</textarea>';
//die;

            // create - this could be 'en' such as 'it'
            $filehandler = fopen($temp_path.'/surveytemplate_'.$data->pluginname.'.php', 'w');
            // write inside all the strings
            fwrite($filehandler, $savedstrings);
            // close
            fclose($filehandler);

            // this is the folder of the language en in case the user language is different from en
            if ($userlang != 'en') {
                $temp_path = $CFG->tempdir.'/'.$temp_subdir.'/lang/en';
                // create
                $filehandler = fopen($temp_path.'/surveytemplate_'.$data->pluginname.'.php', 'w');
                // write inside all the strings in teh form: 'english translation of $string[stringxx]'
                $savedstrings = $filecopyright.survey_get_translated_strings($langtree, $userlang);
                // save into surveytemplate_<<$pluginname>>.php
                fwrite($filehandler, $savedstrings);
                // close
                fclose($filehandler);
            }
            continue;
        }

        // for all the other files: survey.class.php, version.php
        // read the master
        $filecontent = file_get_contents($master_basepath.'/'.$master_file);
        // replace surveyTemplatePluginMaster with the name of the current survey
        $filecontent = str_replace('surveyTemplatePluginNamePlaceholder', $pluginname, $filecontent);
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
        'db/install.php',
        'db/upgrade.php',
        'db/upgradelib.php',
        'lib.php',
        'pix/icon.gif',
        'survey.class.php',
        'version.php',
        'lang/en/surveytemplate_'.$data->pluginname.'.php',
    );
    if ($userlang != 'en') {
        $filenames[] = 'lang/'.$userlang.'/surveytemplate_'.$data->pluginname.'.php';
    }

    $filelist = array();
    foreach ($filenames as $filename) {
        $filelist[$filename] = $temp_basedir.'/'.$filename;
    }

    $exportfile = $temp_basedir.'.zip';
    file_exists($exportfile) && unlink($exportfile);

    $fp = get_file_packer('application/zip');
    $fp->archive_to_pathname($filelist, $exportfile);

    $dirnames = array('db/', 'pix/', 'lang/en');
    if ($userlang != 'en') {
        $filenames[] = 'lang/'.$userlang;
    }

if (false) {
    foreach ($filelist as $file) {
        unlink($file);
    }
    foreach ($dirnames as $dir) {
        rmdir($temp_basedir.'/'.$file);
    }
    rmdir($temp_basedir);
}

    // Return the full path to the exported preset file:
    return $exportfile;
}

/**
 * survey_get_db_structure
 * @param $tablename, $dropid=true
 * @return
 */
function survey_get_db_structure($tablename, $dropid=true) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($dbman->table_exists($tablename)) {
        $dbstructure = array();

        if ($dbfields = $DB->get_columns($tablename)) {
            foreach ($dbfields as $dbfield) {
                $dbstructure[] = $dbfield->name;
            }
        }

        if ($dropid) {
            array_shift($dbstructure); // drop the first item: ID
        }
        return $dbstructure;
    } else {
        return false;
    }
}

/**
 * survey_wlib_content
 * @param &$libcontent, $surveyid, $data, &$langtree
 * @return
 */
function survey_wlib_content(&$libcontent, $surveyid, $data, &$langtree) {
    global $DB;

    $structures = array();
    $sid = array();

   // STEP 01: make a list of used plugins
    $sql = 'SELECT si.plugin
            FROM {survey_item} si
            WHERE si.surveyid = :surveyid
            GROUP BY si.plugin';
    $params = array('surveyid' => $surveyid);
    $itemseeds = $DB->get_records_sql($sql, $params);

    // STEP 02: verify $itemseeds is not empty
    if (!count($itemseeds)) return;

    // STEP 03: prima di aggiungere la plugin fittizia 'item'
    //          sostituisci '// require_once(_LIBRARIES_)' con l'elenco delle require_once
    $librarycall = 'require_once($CFG->dirroot.\'/mod/survey/lib.php\');'."\n";
    $librarycall .= 'require_once($CFG->dirroot.\'/mod/survey/template/lib.php\');'."\n";
    foreach ($itemseeds as $itemseed) {
        $librarycall .= 'require_once($CFG->dirroot.\'/mod/survey/field/'.$itemseed->plugin.'/lib.php\');'."\n";
    }
    $libcontent = str_replace('// require_once(_LIBRARIES_);', $librarycall, $libcontent);

    // STEP 04: aggiungi in testa l'elemento 'item'
    $base = new stdClass();
    $base->plugin = 'item';
    $itemseeds = array_merge(array('item' => $base), $itemseeds);

    // STEP 05: build survey_$plugin table structure array
    foreach ($itemseeds as $itemseed) {
        $tablename = 'survey_'.$itemseed->plugin;
        if ($structure = survey_get_db_structure($tablename)) { // <-- only page break returns false
            $structures[$tablename] = $structure;

            // se c'è un campo che finisce in _sid crea la riga di inizializzazione dell'indice
            $currentsid = array();
            foreach ($structure as $field) {
                if (substr($field, -4) == '_sid') {
                    $currentsid[] .= $field;
                    $field = substr($field, 0, -4);
                    $langtree[$field] = array();
                }
            }

            $sid[$tablename] = $currentsid;
            survey_wlib_write_table_structure($libcontent, $structure, $itemseed->plugin, $sid[$tablename]);
        }
    }

    survey_wlib_structure_values_separator($libcontent, $data->pluginname);

    // STEP 06: make a list of all itemseeds
    $sql = 'SELECT si.id, si.type, si.plugin
            FROM {survey_item} si
            WHERE si.surveyid = :surveyid
            ORDER BY si.sortindex';
    $params = array('surveyid' => $surveyid);
    $itemseeds = $DB->get_records_sql($sql, $params);

    foreach ($itemseeds as $itemseed) {
        survey_wlib_intro_si_values($libcontent, $sid['survey_item']);

        $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

        $values = $item->item_get_si_values($data, $structures['survey_item'], $sid['survey_item']);

        survey_wlib_write_si_values($libcontent, $values);
        survey_collect_strings($langtree, $sid['survey_item'], $item);

        if ($item->flag->useplugintable) { // only page break does not use the plugin table
            $tablename = 'survey_'.$itemseed->plugin;
            $currentsid = $sid[$tablename];
            $currentstructure = $structures[$tablename];
            survey_wlib_intro_plugin_values($libcontent, $itemseed->plugin, $currentsid);

            $values = $item->item_get_plugin_values($currentstructure, $currentsid);

            survey_wlib_write_plugin_values($libcontent, $values, $tablename, $itemseed->plugin);

            survey_collect_strings($langtree, $sid[$tablename], $item);
        }

        $libcontent .= '//----------------------------------------------------------------------------//'."\n";
    }

    $libcontent .= '}'."\n";

}

/**
 * survey_wlib_write_table_structure
 * @param &$libcontent, $structure, $plugin, $sid
 * @return
 */
function survey_wlib_write_table_structure(&$libcontent, $structure, $plugin, $sid) {
    $varprefix = ($plugin == 'item') ? 'si' : $plugin;

    foreach ($sid as $singlesid) {
        $libcontent .= '    $'.$singlesid.' = 0;'."\n";
    }
    $libcontent .= '    // ////////////// SURVEY_'.strtoupper($plugin)."\n";
    $libcontent .= '    $'.$varprefix.'_fields = array(\'';
    $libcontent .= implode('\',\'', $structure);
    $libcontent .= '\');'."\n";
    $libcontent .= "\n";
}

/**
 * survey_wlib_structure_values_separator
 * @param &$libcontent, $pluginname
 * @return
 */
function survey_wlib_structure_values_separator(&$libcontent, $pluginname) {
    $libcontent .= '    // ////////////////////////////////////////////////////////////////////////////////////////////'."\n";
    $libcontent .= '    // ////////////////////////////////////////////////////////////////////////////////////////////'."\n";
    $libcontent .= '    // // '.strtoupper($pluginname)."\n";
    $libcontent .= '    // ////////////////////////////////////////////////////////////////////////////////////////////'."\n";
    $libcontent .= '    // ////////////////////////////////////////////////////////////////////////////////////////////'."\n";
}

/**
 * survey_wlib_intro_si_values
 * @param &$libcontent, $si_sid
 * @return
 */
function survey_wlib_intro_si_values(&$libcontent, $si_sid) {
    $libcontent .= "\n".'    $sortindex++; // <--- new item is going to be added'."\n\n";
    $indent = '';

    $libcontent .= '    // survey_item'."\n";
    $libcontent .= '    /*------------------------------------------------*/'."\n";

    foreach ($si_sid as $singlesid) {
        $libcontent .= $indent.'    $'.$singlesid.'++;'."\n";
    }
}

/**
 * survey_wlib_write_si_values
 * @param &$libcontent, $values
 * @return
 */
function survey_wlib_write_si_values(&$libcontent, $values) {
    $libcontent .= '    $values = array(';
    //$libcontent .= implode(',', $values);
    $libcontent .= survey_wrap_line($values, 20);
    $libcontent .= ');'."\n";
    // Take care you always write sortindex instead of parentid
    $libcontent .= '    $itemid = $DB->insert_record(\'survey_item\', array_combine($si_fields, $values));'."\n";

    $libcontent .= "\n";
}

/**
 * survey_wlib_intro_plugin_values
 * @param &$libcontent, $currentplugin, $currentsid
 * @return
 */
function survey_wlib_intro_plugin_values(&$libcontent, $currentplugin, $currentsid) {
    $libcontent .= '        // survey_'.$currentplugin."\n";
    $libcontent .= '        /*------------------------------------------------*/'."\n";

    foreach ($currentsid as $singlesid) {
        $libcontent .= '        $'.$singlesid.'++;'."\n";
    }
}

/**
 * survey_wlib_write_plugin_values
 * @param &$libcontent, $values, $tablename, $currentplugin
 * @return
 */
function survey_wlib_write_plugin_values(&$libcontent, $values, $tablename, $currentplugin) {
    $libcontent .= '        $values = array(';
    //$libcontent .= implode(',', $values);
    $libcontent .= survey_wrap_line($values, 24);
    $libcontent .= ');'."\n";
    $libcontent .= '        $itemid = $DB->insert_record(\''.$tablename.'\', array_combine($'.$currentplugin.'_fields, $values));'."\n";
    $libcontent .= "    //---------- end of this item\n\n";
}

/**
 * survey_wrap_line
 * @param $values, $lineindent=20
 * @return
 */
function survey_wrap_line($values, $lineindent=20) {
    $return = '';
    $segments = array_chunk($values, 4);
    $countsegments = count($segments)-1;
    foreach ($segments as $k => $segment) {
        $return .= implode(',', $segment);
        if ($k < $countsegments) {
            $return .= ",\n".str_repeat(' ', $lineindent);
        }
    }

    return $return;
}

/**
 * survey_collect_strings
 * @param &$langtree, $currentsid, $values
 * @return
 */
function survey_collect_strings(&$langtree, $currentsid, $values) {
    foreach ($currentsid as $singlesid) {
        $field = substr($singlesid, 0, -4);
        $stringindex = sprintf('%02d', 1+count($langtree[$field]));
        $langtree[$field][$field.$stringindex] = str_replace("\r", '', $values->{$field});
    }
}

/**
 * survey_extract_original_string
 * @param $langtree
 * @return
 */
function survey_extract_original_string($langtree) {
    $stringsastext = array();
    foreach ($langtree as $langbranch) {
        foreach ($langbranch as $k => $stringcontent) {
            $stringsastext[] = '$string[\''.$k.'\'] = \''.addslashes($stringcontent).'\';';
        }
    }
    return "\n".implode("\n", $stringsastext);
}

/**
 * survey_get_translated_strings
 * @param $langtree, $userlang
 * @return
 */
function survey_get_translated_strings($langtree, $userlang) {
    $stringsastext = array();
    $a = new stdClass();
    $a->userlang = $userlang;
    foreach ($langtree as $langbranch) {
        foreach ($langbranch as $k => $stringcontent) {
            $a->stringindex = $k;
            $stringsastext[] = get_string('translatedstring', 'survey', $a);
        }
    }
    return "\n".implode("\n", $stringsastext);
}

/**
 * survey_drop_unexpected_values
 * @param &$fromform
 * @return
 */
function survey_drop_unexpected_values(&$fromform) {
    // BEGIN: delete all the bloody values that were NOT supposed to be returned: MDL-34815
    $dirtydata = (array)$fromform;
    $indexes = array_keys($dirtydata);
    $indexes = array_reverse($indexes);
    // $indexes lists all the item names in reverse order
    // devo usare il reverse array altrimenti...
    // ...nel confronto con il parent item potrei trovare che un parent è satato eliminato

    $olditemid = 0;
    foreach ($indexes as $itemname) {
        if (preg_match('~^'.SURVEY_ITEMPREFIX.'_~', $itemname)) { // if it starts with SURVEY_ITEMPREFIX_
            $parts = explode('_', $itemname);
            $type = $parts[1]; // item type
            $plugin = $parts[2]; // item plugin
            $itemid = $parts[3]; // item id

            if ($itemid == $olditemid) continue;

            $olditemid = $itemid;

            $childitem = survey_get_item($itemid, $type, $plugin);

            $expectedvalue = true;
            while (!empty($childitem->parentid)) {

                // call parentitem
                $parentitem = survey_get_item($childitem->parentid);

                // tell parentitem what child needs to be displayed and compare with what was answered to parentitem
                $expectedvalue = $parentitem->userform_child_is_allowed_dynamic($childitem->parentcontent, $dirtydata);
                // il padre, sapendo come è fatto, compara quello che vuole e risponde

                if ($expectedvalue) {
                    $childitem = $parentitem;
                } else {
                    $childitem->userform_dispose_unexpected_values($fromform);
                    break;
                }
            }
        }
    }
    // END: delete all the bloody values that were supposed to NOT be returned: MDL-34815
}

/**
 * survey_add_custom_css
 * @param $surveyid, $cmid
 * @return
 */
function survey_add_custom_css($surveyid, $cmid) {
    global $PAGE;

    $filearea = SURVEY_STYLEFILEAREA;
    $context = context_module::instance($cmid);

    $fs = get_file_storage();
    if ($files = $fs->get_area_files($context->id, 'mod_survey', $filearea, 0, 'sortorder', false)) {
        $PAGE->requires->css('/mod/survey/userstyle.php?id='.$surveyid.'&amp;cmid='.$cmid); // not overridable via themes!
    }
}

/**
 * survey_get_sid_field_content
 * @param $record, $fieldname='content'
 * @return
 */
function survey_get_sid_field_content($record, $fieldname='content') {
    // this function is the equivalent of the method item_builtin_string_load_support in itembase.class.php
    if (empty($record->externalname)) {
        return $record->{$fieldname};
    }

    // prendi la strings {$fieldname.'_sid'}
    // sul file surveytemplate_{$this->externalname}.php
    $stringindex = $fieldname.sprintf('%02d', $record->{$fieldname.'_sid'});
    $return = get_string($stringindex, 'surveytemplate_'.$record->externalname);

    return $return;
}

/**
 * survey_notifyroles
 * @param $survey, $cm
 * @return
 */
function survey_notifyroles($survey, $cm) {
    global $CFG, $DB, $COURSE;

    require_once($CFG->dirroot.'/group/lib.php');

    $context = context_course::instance($COURSE->id);

    if (groups_get_activity_groupmode($cm, $COURSE) == SEPARATEGROUPS) {   // Separate groups are being used
        if ($mygroups = survey_get_my_groups($cm)) { // se non appartengo ad un gruppo, non ho compagni di gruppo
            $roles = explode(',', $survey->notifyrole);
            $receivers = array();
            foreach ($mygroups as $mygroup) {
                $groupmemberroles = groups_get_members_by_role($mygroup, $COURSE->id, 'u.firstname, u.lastname, u.email');

                foreach ($roles as $role) {
                    if (isset($groupmemberroles[$role])) {
                        $roledata = $groupmemberroles[$role];

                        foreach($roledata->users as $member) {
                            $shortmember = new stdClass();
                            $shortmember->id = $member->id;
                            $shortmember->firstname = $member->firstname;
                            $shortmember->lastname = $member->lastname;
                            $shortmember->email = $member->email;
                            $receivers[] = $shortmember;
                        }
                    }
                }
            }
        } else {
            $receivers = array();
        }
    } else {
        // get_enrolled_users($courseid, $options = array()) <-- manca il ruolo
        // get_users_from_role_on_context($role, $context); <-- questa va bene ma fa una query per volta, sotto faccio la stessa query una sola volta
        if ($survey->notifyrole) {
            $sql = 'SELECT DISTINCT ra.userid, u.firstname, u.lastname, u.email
                    FROM (SELECT *
                          FROM {role_assignments}
                          WHERE contextid = '.$context->id.'
                              AND roleid IN ('.$survey->notifyrole.')) ra
                    JOIN {user} u ON u.id = ra.userid';
            $receivers = $DB->get_records_sql($sql);
        } else {
            $receivers = array();
        }
    }

    if (!empty($survey->notifymore)) {
        $morereceivers = survey_textarea_to_array($survey->notifymore);
        foreach ($morereceivers as $extraemail) {
            $singleuser = new stdClass();
            $singleuser->id = null;
            $singleuser->firstname = '';
            $singleuser->lastname = '';
            $singleuser->email = $extraemail;
            $receivers[] = $singleuser;
        }
    }

    $mailheader = '<head></head>
<body id="email"><div>';
    $mailfooter = '</div></body>';

    $from = new object;
    $from->firstname = $COURSE->shortname;
    $from->lastname = $survey->name;
    $from->email = $CFG->noreplyaddress;
    $from->maildisplay = 1;
    $from->mailformat = 1;

    $htmlbody = $mailheader;
    $htmlbody .= get_string('newsubmissionbody', 'survey', $survey->name);
    $htmlbody .= $mailfooter;

    $body = strip_tags($htmlbody);

    $subject = get_string('newsubmissionsubject', 'survey');

    $recipient = new object;
    $recipient->maildisplay = 1;
    $recipient->mailformat = 1;

    foreach ($receivers as $receiver) {
        $recipient->firstname = $receiver->firstname;
        $recipient->lastname = $receiver->lastname;
        $recipient->email = $receiver->email;

        email_to_user($recipient, $from, $subject, $body, $htmlbody);
    }
}

/**
 * survey_add_items_from_plugin
 * @param $survey, $externalname
 * @return
 */
function survey_add_items_from_plugin($survey, $externalname) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($itemseeds = $DB->get_recordset('survey_item', array('surveyid' => 0, 'externalname' => $externalname), 'id', 'id, plugin')) {
        $sortindexoffset = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $survey->id));
        foreach ($itemseeds as $itemseed) {
            $plugintable = 'survey_'.$itemseed->plugin;
            if ($dbman->table_exists($plugintable)) {
                $sql = 'SELECT *
                        FROM {survey_item} si
                            JOIN {'.$plugintable.'} plugin ON plugin.itemid = si.id
                        WHERE si.surveyid = 0
                            AND si.id = :surveyitemid
                            AND si.externalname = :externalname';
            } else {
                $sql = 'SELECT *
                        FROM {survey_item} si
                        WHERE si.surveyid = 0
                            AND si.id = :surveyitemid
                            AND si.externalname = :externalname';
            }
            $record = $DB->get_record_sql($sql, array('surveyitemid' => $itemseed->id, 'externalname' => $externalname));

            unset($record->id);
            $record->surveyid = $survey->id;
            $record->sortindex += $sortindexoffset;
            // recalculate parentid that is still pointing to the record with surveyid = 0
            if (!empty($record->parentid)) {
                // in the atabase, records of plugins (the ones with surveyid = 0) store sortorder in the parentid field. This for portability reasons.
                $newsortindex = $record->parentid + $sortindexoffset;
                $sqlparams = array('surveyid' => $survey->id, 'externalname' => $externalname, 'sortindex' => $newsortindex);
                $record->parentid = $DB->get_field('survey_item', 'id', $sqlparams, MUST_EXIST);
            }

            // survey_item
            $record->itemid = $DB->insert_record('survey_item', $record);

            // $plugintable
            if ($dbman->table_exists($plugintable)) {
                $DB->insert_record($plugintable, $record, false);
            }
        }
        $itemseeds->close();
    }
}

/**
 * survey_add_items_from_preset
 * @param $survey, $presetid
 * @return
 */
function survey_add_items_from_preset($survey, $presetid) {
    global $DB;

    $presetcontent = survey_get_preset_content($presetid);

    $xmltext = simplexml_load_string($presetcontent);

    // echo '<h2>Items saved in the file ('.count($xmltext->item).')</h2>';

    $sortindexoffset = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $survey->id));
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
                if ($fieldvalue == SURVEY_EMPTYPRESETFIELD) {
                    $record[$fieldname] = null;
                } else {
                    $record[$fieldname] = $fieldvalue;
                }
            }

            unset($record['id']);
            $record['surveyid'] = $survey->id;
            if ($tablename == 'survey_item') {
                $record['sortindex'] += $sortindexoffset;
                if (!empty($record['parentid'])) {
                    $sqlparams = array('surveyid' => $survey->id, 'sortindex' => ($record['parentid'] + $sortindexoffset));
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

/**
 * survey_get_preset_content
 * @param $presetid
 * @return
 */
function survey_get_preset_content($presetid) {
    $fs = get_file_storage();
    $xmlfile = $fs->get_file_by_id($presetid);

    return $xmlfile->get_content();
}

/**
 * survey_build_preset_content
 * @param $survey, $externalname
 * @return
 */
function survey_build_preset_content($survey) {
    global $CFG, $DB;

    $sql = 'SELECT si.id, si.type, si.plugin
            FROM {survey_item} si
            WHERE si.surveyid = :surveyid
            ORDER BY si.sortindex';
    $params = array('surveyid' => $survey->id);
    $itemseeds = $DB->get_records_sql($sql, $params);

    $xmlpreset = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><items></items>');
    foreach ($itemseeds as $itemseed) {

        $id = $itemseed->id;
        $type = $itemseed->type;
        $plugin = $itemseed->plugin;
        $item = survey_get_item($id, $type, $plugin);
        $xmlitem = $xmlpreset->addChild('item');

        // survey_item
        $structure = survey_get_db_structure('survey_item');

        $xmltable = $xmlitem->addChild('survey_item');
        foreach ($structure as $field) {
            if ($field == 'parentid') {
                $sqlparams = array('id' => $item->parentid);
                // I store sortindex instead of parentid, because at restore time parent id will change
                $parentvalue = $DB->get_field('survey_item', 'sortindex', $sqlparams);
                $val = $parentvalue;
            } else {
                $val = $item->{$field};
            }
            $xmlfield = $xmltable->addChild($field, $val);
        }

        if ($item->flag->useplugintable) { // only page break does not use the plugin table
            // child table
            $structure = survey_get_db_structure('survey_'.$plugin);

            $xmltable = $xmlitem->addChild('survey_'.$plugin);
            foreach ($structure as $field) {
                if (is_null($item->{$field})) {
                    $xmlfield = $xmltable->addChild($field, SURVEY_EMPTYPRESETFIELD);
                } else {
                    $xmlfield = $xmltable->addChild($field, $item->{$field});
                }
            }
        }

        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmlpreset->asXML());
    }

    return $dom->saveXML();
}

/**
 * Gets an array of all of the presets that users have saved to the site.
 *
 * @param stdClass $context The context that we are looking from.
 * @param array $presets
 * @return array An array of presets
 */
function survey_get_available_presets($contextid, $component) {

    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, $component, SURVEY_PRESETFILEAREA);
    if (empty($files)) {
        return array();
    }

    $presets = array();
    foreach ($files as $file) {
        if ($file->is_directory()) {
            continue;
        }

        $presets[] = $file;
    }

    return $presets;
}

/**
 * survey_save_preset
 * @param $survey, $externalname
 * @return
 */
function survey_save_preset($formadata, $xmlcontent) {
    global $USER;

    $fs = get_file_storage();
    $filerecord = new stdClass;

    $contextid = survey_get_contextid_from_sharinglevel($formadata->sharinglevel);
    $filerecord->contextid = $contextid;

    $filerecord->component = 'mod_survey';
    $filerecord->filearea = SURVEY_PRESETFILEAREA;
    $filerecord->itemid = 0;
    $filerecord->filepath = '/';
    $filerecord->userid = $USER->id;

    $filerecord->filename = str_replace(' ', '_', $formadata->presetname).'.xml';
    $fs->create_file_from_string($filerecord, $xmlcontent);

    return true;
}

/**
 * survey_get_sharinglevel_options
 *
 * @param $cmid, $survey
 * @return null
 */
function survey_get_sharinglevel_options($cmid, $survey) {
    global $DB, $COURSE;

    $options = array();
    $options[CONTEXT_MODULE.'_'.$cmid] = get_string('module', 'survey').': '.$survey->name;

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

/**
 * survey_get_contextstring_from_sharinglevel
 *
 * @param $contextlevel
 * @return null
 */
function survey_get_contextstring_from_sharinglevel($contextlevel) {
    // a secondo del livello di condivisione la component può essere:
    // system, category, mod_survey
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
        default:
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I have $sharinglevel = '.$sharinglevel.'<br />';
            echo 'and the right "case" is missing<br />';
    }

    return $contextstring;
}

/**
 * survey_delete_preset
 *
 * @param $cm, $confirm, $fileid
 * @return null
 */
function survey_delete_preset($cm, $confirm, $fileid) {
    global $OUTPUT;

    if (!$confirm) {
        // ask for confirmation
        $message = get_string('askdeleteonepreset', 'survey');
        $optionsbase = array('id' => $cm->id, 'tab' => SURVEY_TABPRESETS, 'pag' => SURVEY_PRESETS_MANAGE, 'act' => SURVEY_DELETEPRESET);

        $optionsyes = $optionsbase + array('cnf' => SURVEY_CONFIRM, 'fid' => $fileid);
        $urlyes = new moodle_url('view.php', $optionsyes);
        $buttonyes = new single_button($urlyes, get_string('yes'));

        $optionsno = $optionsbase + array('cnf' => SURVEY_NEGATE);
        $urlno = new moodle_url('view.php', $optionsno);
        $buttonno = new single_button($urlno, get_string('no'));

        echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
        echo $OUTPUT->footer();
        die;
    } else {
        switch ($confirm) {
            case 1:
                $fs = get_file_storage();
                $xmlfile = $fs->get_file_by_id($fileid);
                $xmlfile->delete();
                break;
            case 2:
                $message = get_string('usercanceled', 'survey');
                echo $OUTPUT->notification($message, 'notifyproblem');
                break;
            default:
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo 'I have $confirm = '.$confirm.'<br />';
                echo 'and the right "case" is missing<br />';
        }
    }
}

/**
 * survey_upload_preset
 *
 * @param $survey, $context
 * @return null
 */
function survey_upload_preset($formdata) {

    $contextid = survey_get_contextid_from_sharinglevel($formdata->sharinglevel);

    $preset_options = survey_get_preset_options();

    $fieldname = 'importfile';
    if ($draftitemid = $formdata->{$fieldname.'_filemanager'}) {
        file_save_draft_area_files($draftitemid, $contextid, 'mod_survey', SURVEY_PRESETFILEAREA, 0, $preset_options);
    }

    $fs = get_file_storage();
    if ($files = $fs->get_area_files($contextid, 'mod_survey', SURVEY_PRESETFILEAREA, 0, 'sortorder', false)) {
        if (count($files) == 1) {
            // only one file attached, set it as main file automatically
            $file = reset($files);
            file_set_sortorder($contextid, 'mod_survey', SURVEY_PRESETFILEAREA, 0, $file->get_filepath(), $file->get_filename(), 1);
        }
    }
}

/**
 * survey_get_preset_options
 * @param none
 * @return $filemanager_options
 */
function survey_get_preset_options() {
    $preset_options = array();
    $preset_options['accepted_types'] = '.xml';
    $preset_options['maxbytes'] = 0;
    $preset_options['maxfiles'] = -1;
    $preset_options['mainfile'] = true;
    $preset_options['subdirs'] = false;

    return $preset_options;
}

/**
 * survey_get_preset_options
 * @param none
 * @return $filemanager_options
 */
function survey_get_contextid_from_sharinglevel($sharinglevel) {
    $parts = explode('_', $sharinglevel);
    $contextlevel = $parts[0];
    $contextid = $parts[1];

    //       $parts[0]    |   $parts[1]
    //  ----------------------------------
    //     CONTEXT_SYSTEM | 0
    //  CONTEXT_COURSECAT | $category->id
    //     CONTEXT_COURSE | $COURSE->id
    //     CONTEXT_MODULE | $cm->id

    if (!isset($parts[0]) || !isset($parts[1])) {
        throw new moodle_exception('Wrong $sharinglevel passed in survey_get_contextid_from_sharinglevel');
    }

    switch ($contextlevel) {
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
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I have $contextlevel = '.$contextlevel.'<br />';
            echo 'and the right "case" is missing<br />';
    }

    return $context->id;
}