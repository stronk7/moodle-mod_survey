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

require_once($CFG->libdir.'/tablelib.php');
$context = context_module::instance($cm->id);

$table = new flexible_table('itemslist');

$paramurl = array('id' => $cm->id, 'tab' => SURVEY_TABITEMS, 'pag' => SURVEY_ITEMS_VALIDATE);
$table->define_baseurl(new moodle_url('view.php', $paramurl));

$tablecolumns = array();
$tablecolumns[] = 'plugin';
$tablecolumns[] = 'content';
$tablecolumns[] = 'sortindex';
$tablecolumns[] = 'parentitem';
$tablecolumns[] = 'parentconstraints';
$tablecolumns[] = 'status';
$tablecolumns[] = 'actions';
$table->define_columns($tablecolumns);

$tableheaders = array();
$tableheaders[] = get_string('plugin', 'survey');
$tableheaders[] = get_string('content', 'survey');
$tableheaders[] = get_string('sortindex', 'survey');
$tableheaders[] = get_string('parentid', 'survey');
$tableheaders[] = get_string('parentconstraints', 'survey');
$tableheaders[] = get_string('relation_status', 'survey');
$tableheaders[] = get_string('actions');
$table->define_headers($tableheaders);

// $table->collapsible(true);
$table->sortable(true, 'sortindex', 'ASC'); // sorted by sortindex by default
$table->no_sorting('uavailability');
$table->no_sorting('mavailability');
$table->no_sorting('actions');

$table->column_class('plugin', 'plugin');
$table->column_class('content', 'content');
$table->column_class('sortindex', 'sortindex');
$table->column_class('parentitem', 'parentitem');
$table->column_class('parentconstraints', 'parentconstraints');
$table->column_class('status', 'status');
$table->column_class('actions', 'actions');

// $table->initialbars(true);

// ometti la casella se duplica la precedente
// $table->column_suppress('picture');
// $table->column_suppress('fullname');

// definisco delle proprietà generali per tutta la tabella
// $table->set_attribute('cellpadding', '5');
$table->set_attribute('id', 'validaterelations');
$table->set_attribute('class', 'generaltable');
// $table->set_attribute('width', '90%');
$table->setup();

/******************************************************************************/
$edittitle = get_string('edit');
$okstring = get_string('ok');

$sql = 'SELECT si.*, si.id as itemid, si.plugin, si.type
        FROM {survey_item} si
        WHERE si.surveyid = :surveyid';
if ($table->get_sql_sort()) {
    $sql .= ' ORDER BY '.$table->get_sql_sort();
} else {
    $sql .= ' ORDER BY sortindex';
}

$itemseeds = $DB->get_recordset_sql($sql, array('surveyid' => $survey->id), $table->get_sql_sort());
if (!$itemseeds->valid()) {
    $a = new stdClass();
    $paramurl['tab'] = SURVEY_TABITEMS;
    $paramurl['pag'] = SURVEY_ITEMS_ADD;
    $url = new moodle_url('/mod/survey/view.php', $paramurl);
    $a->href = $url->out();

    $a->title = get_string('noitemsfoundtitle', 'survey');
    echo $OUTPUT->box(get_string('noitemsfound', 'survey', $a));
} else {
    echo $OUTPUT->box(get_string('validationinfo', 'survey'));
}

foreach ($itemseeds as $itemseed) {
    $item = survey_get_item($itemseed->itemid, $itemseed->type, $itemseed->plugin);
    if ($item->parentid) {
        $parentseed = $DB->get_record('survey_item', array('id' => $item->parentid), 'plugin', MUST_EXIST);
        require_once($CFG->dirroot.'/mod/survey/field/'.$parentseed->plugin.'/plugin.class.php');
        $itemclass = 'surveyfield_'.$parentseed->plugin;
        $parentitem = new $itemclass($item->parentid);
    }

    $tablerow = array();

    // *************************************** plugin
    $plugin = $item->plugin;
    $plugintitle = get_string('pluginname', 'survey'.$item->type.'_'.$plugin);
    $content = '<img src="'.$OUTPUT->pix_url('icon', 'survey'.$item->type.'_'.$plugin).'" class="icon" alt="'.$plugintitle.'" title="'.$plugintitle.'" />';
    $tablerow[] = $content;

    // *************************************** content
    $itemcontent = $item->content;
    $item->contentformat = FORMAT_HTML;
    $item->contenttrust = 1;

    $output = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', 'items', $item->itemid);
    $tablerow[] = $output;

    // *************************************** sortindex
    $tablerow[] = $item->sortindex;

    // *************************************** parentid
    if ($item->parentid) {
        $message = get_string('parentid', 'survey');
        $content = $parentitem->sortindex;
        $content .= '&nbsp;<img src="'.$OUTPUT->pix_url('link', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
        $content .= $item->parentcontent;
    } else {
        $content = '';
    }
    $tablerow[] = $content;

    // *************************************** parentconstraints
    if ($item->parentid) {
        $tablerow[] = $parentitem->item_list_constraints();
    } else {
        $tablerow[] = '-';
    }

    // *************************************** status
    if ($item->parentid) {
        $status = $parentitem->item_parent_validate_child_constraints($item->parentvalue);
        if ($status === true) {
            $tablerow[] = $okstring;
        } else {
            if ($status === false) {
                if (empty($item->draft)) {
                    $tablerow[] = '<span class="errormessage">'.get_string('wrongrelation', 'survey', $item->parentcontent).'</span>';
                } else {
                    $tablerow[] = get_string('wrongrelation', 'survey', $item->parentcontent);
                }
            } else {
                $tablerow[] = $status;
            }
        }
    } else {
        $tablerow[] = '-';
    }

    // *************************************** actions
    // /////////////////////////////////////////////////
    // $paramurl_base definition
    $paramurl_base = array();
    $paramurl_base['id'] = $cm->id;
    $paramurl_base['tab'] = SURVEY_TABITEMS;
    $paramurl_base['itemid'] = $item->itemid;
    $paramurl_base['type'] = $item->type;
    $paramurl_base['plugin'] = $plugin;
    // end of $paramurl_base definition
    // /////////////////////////////////////////////////

    // *************************************** SURVEY_EDITITEM
    $paramurl = $paramurl_base + array('pag' => SURVEY_ITEMS_CONFIGURE, 'act' => SURVEY_EDITITEM);
    $basepath = new moodle_url('view.php', $paramurl);

    $icons = '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
    $icons .= '<img src="'.$OUTPUT->pix_url('t/edit').'" class="iconsmall" alt="'.$edittitle.'" title="'.$edittitle.'" /></a>&nbsp;';

    $tablerow[] = $icons;

    $addedclass = empty($item->draft) ? '' : 'dimmed';
    $table->add_data($tablerow, $addedclass);
}
$itemseeds->close();

$table->set_attribute('align', 'center');
$table->summary = get_string('itemlist', 'survey');
$table->print_html();
