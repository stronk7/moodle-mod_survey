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

defined('MOODLE_INTERNAL') OR die();

$itemid = optional_param('itemid', 0,    PARAM_INT);
$type = optional_param('type'  , null, PARAM_TEXT);
$plugin = optional_param('plugin', null, PARAM_TEXT);
$action = optional_param('act', SURVEY_NOACTION, PARAM_INT);
$itemtomove = optional_param('itm'   , 0,    PARAM_INT); // itm == item to move (sortindex of the item to move)
$lastitembefore = optional_param('lib'   , 0,    PARAM_INT); // lib == last item before the place where the moving item has to go

$confirm = optional_param('cnf', 0, PARAM_INT);
$nextindent = optional_param('ind', 0, PARAM_INT);
$parentid = optional_param('pit', 0, PARAM_INT);
$userfeedback = optional_param('ufd', SURVEY_NOFEEDBACK, PARAM_INT);

$restrictedaccess = get_string('restrictedaccess', 'survey');

switch ($action) {
    case SURVEY_NOACTION:
        break;
    case SURVEY_EDITITEM:
        break;
    case SURVEY_HIDEITEM:
        survey_manage_item_hide($confirm, $cm, $itemid, $type);
        break;
    case SURVEY_SHOWITEM:
        survey_manage_item_show($confirm, $cm, $itemid, $type);
        break;
    case SURVEY_DELETEITEM:
        survey_manage_item_deletion($confirm, $cm, $itemid, $type, $plugin, $itemtomove, $survey->id);
        break;
    case SURVEY_CHANGEORDERASK:
        // è stato richiesto di spostare l'item $itemid
        // echo 'devo spostare l\'item con sortindex = '.$itemtomove; // sortindex of the item to move
        break;
    case SURVEY_CHANGEORDER:
        // è stato richiesto di spostare l'item $itemid
        survey_reorder_items($itemtomove, $lastitembefore, $survey->id);
        break;
    case SURVEY_REQUIREDON:
        $DB->set_field('survey_item', 'required', '1', array('id' => $itemid));
        break;
    case SURVEY_REQUIREDOFF:
        $DB->set_field('survey_item', 'required', '0', array('id' => $itemid));
        break;
    case SURVEY_CHANGEINDENT:
        $DB->set_field('survey_item', 'indent', $nextindent, array('id' => $itemid));
        break;
    default:
        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $action = '.$action);
}

if ($userfeedback != SURVEY_NOFEEDBACK) {
    $message = survey_display_user_feedback($userfeedback);
    echo $OUTPUT->box($message, 'notice centerpara');
}

require_once($CFG->libdir.'/tablelib.php');
$context = context_module::instance($cm->id);


$table = new flexible_table('itemslist');

$paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_MANAGE);
$table->define_baseurl(new moodle_url('view.php', $paramurl));

$tablecolumns = array();
$tablecolumns[] = 'plugin';
$tablecolumns[] = 'sortindex';
$tablecolumns[] = 'parentid';
$tablecolumns[] = 'uavailability';
$tablecolumns[] = 'mavailability';
$tablecolumns[] = 'basicformpage';
$tablecolumns[] = 'content';
$tablecolumns[] = 'customnumber';
$tablecolumns[] = 'actions';
$table->define_columns($tablecolumns);

$tableheaders = array();
$tableheaders[] = get_string('plugin', 'survey');
$tableheaders[] = get_string('sortindex', 'survey');
$tableheaders[] = get_string('parentid', 'survey');
$tableheaders[] = get_string('basic', 'survey');
$tableheaders[] = get_string('advanced_header', 'survey');
$tableheaders[] = get_string('page');
$tableheaders[] = get_string('content', 'survey');
$tableheaders[] = get_string('customnumber_header', 'survey');
$tableheaders[] = get_string('actions');
$table->define_headers($tableheaders);

// $table->collapsible(true);
$table->sortable(true, 'sortindex'); // sorted by sortindex by default
$table->no_sorting('uavailability');
$table->no_sorting('mavailability');
$table->no_sorting('actions');

$table->column_class('plugin', 'plugin');
$table->column_class('sortindex', 'sortindex');
$table->column_class('parentid', 'parentitem');
$table->column_class('uavailability', 'uavailability');
$table->column_class('mavailability', 'mavailability');
$table->column_class('basicformpage', 'basicformpage');

$table->column_class('content', 'content');
$table->column_class('customnumber', 'customnumber');
$table->column_class('actions', 'actions');

// $table->initialbars(true);

// hide the same info whether in two consecutive rows
// $table->column_suppress('picture');
// $table->column_suppress('fullname');

// general properties for the whole table
// $table->set_attribute('cellpadding', '5');
$table->set_attribute('id', 'manageitems');
$table->set_attribute('class', 'generaltable');
// $table->set_attribute('width', '90%');
$table->setup();

/*****************************************************************************/
$edittitle = get_string('edit');
$requiredtitle = get_string('switchrequired', 'survey');
$optionaltitle = get_string('switchoptional', 'survey');
$onlyoptionaltitle = get_string('onlyoptional', 'survey');
$changetitle = get_string('changeorder', 'survey');
$hidetitle = get_string('hidefield', 'survey');
$showtitle = get_string('showfield', 'survey');
$deletetitle = get_string('delete');
$indenttitle = get_string('indent', 'survey');
$moveheretitle = get_string('movehere');

// /////////////////////////////////////////////////
// $paramurl_move definition
$paramurl_move = array();
$paramurl_move['id'] = $cm->id;
$paramurl_move['tab'] = SURVEY_TABITEMS;
$paramurl_move['pag'] = SURVEY_ITEMS_MANAGE;
$paramurl_move['act'] = SURVEY_CHANGEORDER;
$paramurl_move['itm'] = $itemtomove;
// end of $paramurl_move definition
// /////////////////////////////////////////////////

if ($hassubmissions) {
    echo $OUTPUT->box(get_string('hassubmissions', 'survey'));
}

$sql = 'SELECT si.*, si.id as itemid, si.plugin, si.type
        FROM {survey_item} si
        WHERE si.surveyid = :surveyid';
if ($table->get_sql_sort()) {
    $sql .= ' ORDER BY '.$table->get_sql_sort();
} else {
    $sql .= ' ORDER BY si.sortindex';
}

if (!$itemseeds = $DB->get_records_sql($sql, array('surveyid' => $survey->id), $table->get_sql_sort())) {
    $a = new stdClass();
    $paramurl['tab'] = SURVEY_TABITEMS;
    $paramurl['pag'] = SURVEY_ITEMS_ADD;
    $url = new moodle_url('/mod/survey/view.php', $paramurl);
    $a->href = $url->out();

    $a->title = get_string('noitemsfoundtitle', 'survey');
    echo $OUTPUT->box(get_string('noitemsfound', 'survey', $a));
}

$drawmovearrow = (count($itemseeds) > 1);

if (($action == SURVEY_CHANGEORDERASK) && (!$parentid)) {
    $drawmoveherebox = true;
    $paramurl = $paramurl_move + array('lib' => 0); // lib == just after this sortindex (lib == last item before)
    $basepath = new moodle_url('view.php', $paramurl);

    $icons = '<a class="editing_update" title="'.$moveheretitle.'" href="'.$basepath.'">';
    $icons .= '<img src="'.$OUTPUT->pix_url('movehere').'" class="movetarget" alt="'.$moveheretitle.'" title="'.$moveheretitle.'" /></a>&nbsp;';

    $tablerow = array();
    $tablerow[] = $icons;
    $tablerow = array_pad($tablerow, 9, '');

    $table->add_data($tablerow);
} else {
    $drawmoveherebox = false;
}

foreach ($itemseeds as $itemseed) {
    $item = survey_get_item($itemseed->itemid, $itemseed->type, $itemseed->plugin);

    $tablerow = array();

    if (($action == SURVEY_CHANGEORDERASK) && ($item->itemid == $itemid)) {
        continue;
    }

    // *************************************** plugin
    $plugintitle = get_string('userfriendlypluginname', 'survey'.$item->type.'_'.$item->plugin);
    $content = '<img src="'.$OUTPUT->pix_url('icon', 'survey'.$item->type.'_'.$item->plugin).'" class="icon" alt="'.$plugintitle.'" title="'.$plugintitle.'" />';
    $tablerow[] = $content;

    // *************************************** sortindex
    $tablerow[] = $item->sortindex;

    // *************************************** parentid
    if ($item->parentid) {
        // if (!empty($content)) $content .= ' ';
        $message = get_string('parentid', 'survey');
        $parentsortindex = $DB->get_field('survey_item', 'sortindex', array('id' => $item->parentid));
        $content = $parentsortindex;
        $content .= '&nbsp;<img src="'.$OUTPUT->pix_url('link', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
        $content .= $item->parentcontent;
    } else {
        $content = '';
    }
    $tablerow[] = $content;

    // *************************************** user availability
    if ($item->hide) {
        $message = get_string('basicnoedit', 'survey');
        $content = '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
        $message = get_string('basicnosearch', 'survey');
        $content .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />';
    } else {
        switch ($item->basicform) {
            case SURVEY_NOTPRESENT:
                $message = get_string('basicnoedit', 'survey');
                $content = '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
                $message = get_string('basicnosearch', 'survey');
                $content .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />';
                break;
            case SURVEY_FILLONLY:
                $message = get_string('basicedit', 'survey');
                $content = '<img src="'.$OUTPUT->pix_url('i/grades').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
                $message = get_string('basicnosearch', 'survey');
                $content .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
                break;
            case SURVEY_FILLANDSEARCH:
                $message = get_string('basicedit', 'survey');
                $content = '<img src="'.$OUTPUT->pix_url('i/grades').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
                $message = get_string('basicsearch', 'survey');
                $content .= '<img src="'.$OUTPUT->pix_url('t/preview').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />';
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->basicform = '.$this->basicform);
        }
    }
    $tablerow[] = $content;

    // *************************************** advanced availability
    if ($item->hide) {
        $message = get_string('advancednoedit', 'survey');
        $content = '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
        $message = get_string('advancednosearch', 'survey');
        $content .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />';
    } else {
        $message = get_string('advancededit', 'survey');
        $content = '<img src="'.$OUTPUT->pix_url('i/grades').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
        if ($item->advancedsearch == SURVEY_ADVFILLANDSEARCH) {
            $message = get_string('advancedsearch', 'survey');
            $content .= '<img src="'.$OUTPUT->pix_url('t/preview').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />';
        } else {
            $message = get_string('advancednosearch', 'survey');
            $content .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
        }
    }
    $tablerow[] = $content;

    // *************************************** page
    if ($item->plugin != 'pagebreak') {
        $content = ($item->basicformpage) ? $item->basicformpage : '..';
        $content .= '/';
        $content .= ($item->advancedformpage) ? $item->advancedformpage : '..';
    } else {
        $content = '';
    }
    $tablerow[] = $content;

    // *************************************** content
    $itemcontent = $item->item_get_main_text();
    $item->contentformat = FORMAT_HTML;
    $item->contenttrust = 1;

    $output = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $item->itemid);
    $tablerow[] = $output;

    // *************************************** customnumber
    $tablerow[] = ($item->type == SURVEY_FIELD) ? $item->customnumber : '';

    if ($action != SURVEY_CHANGEORDERASK) {
        // *************************************** actions
        // /////////////////////////////////////////////////
        // $paramurl_base definition
        $paramurl_base = array();
        $paramurl_base['id'] = $cm->id;
        $paramurl_base['tab'] = SURVEY_TABITEMS;
        $paramurl_base['itemid'] = $item->itemid;
        $paramurl_base['type'] = $item->type;
        $paramurl_base['plugin'] = $item->plugin;
        // end of $paramurl_base definition
        // /////////////////////////////////////////////////

        $icons = '';
        // *************************************** SURVEY_EDITITEM
        $paramurl = $paramurl_base + array('pag' => SURVEY_ITEMS_CONFIGURE, 'act' => SURVEY_EDITITEM);
        $basepath = new moodle_url('view.php', $paramurl);

        $icons .= '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
        $icons .= '<img src="'.$OUTPUT->pix_url('t/edit').'" class="iconsmall" alt="'.$edittitle.'" title="'.$edittitle.'" /></a>&nbsp;';

        // *************************************** SURVEY_CHANGEORDERASK
        if (!empty($drawmovearrow)) {
            $paramurl = $paramurl_base + array('pag' => SURVEY_ITEMS_REORDER, 'act' => SURVEY_CHANGEORDERASK, 'itm' => $item->sortindex);
            if (!empty($item->parentid)) {
                $paramurl = $paramurl + array('pit' => $item->parentid);
            }
            $basepath = new moodle_url('view.php', $paramurl);

            $icons .= '<a class="editing_update" title="'.$changetitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('t/move').'" class="iconsmall" alt="'.$changetitle.'" title="'.$changetitle.'" /></a>&nbsp;';
        }

        // *************************************** SURVEY_HIDEITEM/SURVEY_SHOWITEM
        if (!$hassubmissions) {
            $paramurl = $paramurl_base + array('pag' => SURVEY_ITEMS_MANAGE);
            if (!empty($item->hide)) {
                $icopath = 't/show';
                $paramurl = $paramurl + array('act' => SURVEY_SHOWITEM);
                $message = $showtitle;
            } else {
                $icopath = 't/hide';
                $paramurl = $paramurl + array('act' => SURVEY_HIDEITEM);
                $message = $hidetitle;
            }
            $basepath = new moodle_url('view.php', $paramurl);

            $icons .= '<a class="editing_update" title="'.$message.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url($icopath).'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
        }

        // *************************************** SURVEY_DELETEITEM
        if (!$hassubmissions) {
            $paramurl = $paramurl_base + array('pag' => SURVEY_ITEMS_MANAGE, 'act' => SURVEY_DELETEITEM, 'itm' => $item->sortindex);
            $basepath = new moodle_url('view.php', $paramurl);

            $icons .= '<a class="editing_update" title="'.$deletetitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletetitle.'" title="'.$deletetitle.'" /></a>&nbsp;';
        }

        // *************************************** SURVEY_REQUIRED ON/OFF
        if (isset($item->required)) { // it may not be set as in page_break, autofill or some more
            $paramurl = $paramurl_base + array('pag' => SURVEY_ITEMS_MANAGE);

            if ($item->required) {
                $icopath = 'red';
                $paramurl = $paramurl + array('act' => SURVEY_REQUIREDOFF);
                $message = $optionaltitle;
            } else {
                if ($item->item_mandatory_is_allowed()) {
                    $icopath = 'green';
                    $paramurl = $paramurl + array('act' => SURVEY_REQUIREDON);
                    $message = $requiredtitle;
                } else {
                    $icopath = 'greenlock';
                    $message = $onlyoptionaltitle;
                }
            }

            if ($icopath == 'greenlock') {
                $icons .= '<img src="'.$OUTPUT->pix_url($icopath, 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
            } else {
                $basepath = new moodle_url('view.php', $paramurl);
                $icons .= '<a class="editing_update" title="'.$message.'" href="'.$basepath.'">';
                $icons .= '<img src="'.$OUTPUT->pix_url($icopath, 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
            }
        }

        // *************************************** SURVEY_CHANGEINDENT
        if (isset($item->indent)) { // it may not be set as in page_break, autofill and some more
            $paramurl = $paramurl_base + array('pag' => SURVEY_ITEMS_MANAGE, 'act' => SURVEY_CHANGEINDENT);

            if ($item->indent > 0) {
                $indentvalue = $item->indent - 1;
                $paramurl['ind'] = $indentvalue;
                $basepath = new moodle_url('view.php', $paramurl);
                $icons .= '<a class="editing_update" title="'.$indenttitle.'" href="'.$basepath.'">';
                $icons .= '<img src="'.$OUTPUT->pix_url('t/left').'" class="iconsmall" alt="'.$indenttitle.'" title="'.$indenttitle.'" /></a>';
            }
            $icons .= '['.$item->indent.']';
            if ($item->indent < 9) {
                $indentvalue = $item->indent + 1;
                $paramurl['ind'] = $indentvalue;
                $basepath = new moodle_url('view.php', $paramurl);
                $icons .= '<a class="editing_update" title="'.$indenttitle.'" href="'.$basepath.'">';
                $icons .= '<img src="'.$OUTPUT->pix_url('t/right').'" class="iconsmall" alt="'.$indenttitle.'" title="'.$indenttitle.'" /></a>&nbsp;';
            }
        }
    } else {
        $icons = '';
    }

    $tablerow[] = $icons;

    $addedclass = empty($item->hide) ? '' : 'dimmed';
    $table->add_data($tablerow, $addedclass);

    // print_object($item);
    if ($action == SURVEY_CHANGEORDERASK) {
        // ho chiesto di spostare l'item caratterizzato da:
        // $itemid e $parentid
        if ($parentid) { // <-- questo è il parentid dell'item che sto spostando
            // se c'è un parentid
            // disegna solo dopo l'item con id == $parentid
            // una volta che cominci a disegnare non smettere più
            $drawmoveherebox = $drawmoveherebox || ($item->itemid == $parentid);

            // se hai appena superato un item con $item->parentid == $itemid, fermati per sempre
            if ($item->parentid == $itemid) {
                $drawmoveherebox = false;
            }
        } else {
            $drawmoveherebox = $drawmoveherebox && ($item->parentid != $itemid);
        }

        if (!empty($drawmoveherebox)) {
            $paramurl = $paramurl_move + array('lib' => $item->sortindex);
            $basepath = new moodle_url('view.php', $paramurl);

            $icons = '<a class="editing_update" title="'.$moveheretitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('movehere').'" class="movetarget" alt="'.$moveheretitle.'" title="'.$moveheretitle.'" /></a>&nbsp;';

            $tablerow = array();
            $tablerow[] = $icons;
            $tablerow = array_pad($tablerow, 9, '');

            $table->add_data($tablerow);
        }
    }
}

$table->set_attribute('align', 'center');
$table->summary = get_string('itemlist', 'survey');
$table->print_html();
