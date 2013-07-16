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
class mod_survey_itemlist {
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
     * $type
     */
    public $type = '';

    /*
     * $plugin
     */
    public $plugin = '';

    /*
     * $itemid
     */
    public $itemid = 0;

    /*
     * $action
     */
    public $action = SURVEY_NOACTION;

    /*
     * $itemtomove
     */
    public $itemtomove = 0;

    /*
     * $lastitembefore
     */
    public $lastitembefore = 0;

    /*
     * $confirm
     */
    public $confirm = SURVEY_UNCONFIRMED;

    /*
     * $nextindent
     */
    public $nextindent = 0;

    /*
     * $parentid
     */
    public $parentid = 0;

    /*
     * $userfeedback
     */
    public $userfeedback = SURVEY_NOFEEDBACK;

    /*
     * $saveasnew
     */
    public $saveasnew = 0;

    /*
     * $hassubmissions
     */
    public $hassubmissions = null;

    /*
     * Class constructor
     */
    public function __construct($cm, $context, $survey, $type, $plugin, $itemid, $action, $itemtomove,
                                $lastitembefore, $confirm, $nextindent, $parentid, $userfeedback, $saveasnew) {
        $this->cm = $cm;
        $this->context = $context;
        $this->survey = $survey;
        if (preg_match('~^('.SURVEY_TYPEFIELD.'|'.SURVEY_TYPEFORMAT.')_(\w+)$~', $plugin, $match)) {
            // execution comes from /forms/items/selectitem_form.php
            $this->type = $match[1]; // field or format
            $this->plugin = $match[2]; // boolean or char ... or fieldset ...
        } else {
            // execution comes from /forms/items/items_manage.php
            $this->type = $type;
            $this->plugin = $plugin;
        }
        $this->itemid = $itemid;
        $this->action = $action;
        $this->itemtomove = $itemtomove; // itm == Item To Move (sortindex of the item to move)
        $this->lastitembefore = $lastitembefore; // lib == Last Item Before the place where the moving item has to go
        $this->confirm = $confirm;
        $this->nextindent = $nextindent;
        $this->parentid = $parentid;
        $this->userfeedback = $userfeedback;
        $this->saveasnew = $saveasnew;
        $this->hassubmissions = survey_count_submissions($survey->id, SURVEY_STATUSALL);
    }

    /*
     * manage_actions
     *
     * @param
     * @return
     */
    public function manage_actions() {
        global $OUTPUT, $DB;

        switch ($this->action) {
            case SURVEY_NOACTION:
                break;
            case SURVEY_EDITITEM:
                break;
            case SURVEY_HIDEITEM:
                $this->manage_item_hide();
                break;
            case SURVEY_SHOWITEM:
                $this->manage_item_show();
                break;
            case SURVEY_DELETEITEM:
                $this->manage_item_deletion();
                break;
            case SURVEY_CHANGEORDERASK:
                // it was required to move the item $this->itemid
                // no action is foreseen, only page reload
                break;
            case SURVEY_CHANGEORDER:
                // it was required to move the item $this->itemid
                $this->reorder_items();
                break;
            case SURVEY_REQUIREDON:
                $DB->set_field('survey_item', 'required', 1, array('id' => $this->itemid));
                break;
            case SURVEY_REQUIREDOFF:
                $DB->set_field('survey_item', 'required', 0, array('id' => $this->itemid));
                break;
            case SURVEY_CHANGEINDENT:
                $DB->set_field('survey_item', 'indent', $this->nextindent, array('id' => $this->itemid));
                break;
            case SURVEY_ADDTOSEARCH:
                $item = survey_get_item($this->itemid, $this->type, $this->plugin);
                if ($item->get_item_form_requires('insearchform')) {
                    $DB->set_field('survey_item', 'insearchform', 1, array('id' => $this->itemid));
                }
                break;
            case SURVEY_OUTOFSEARCH:
                $DB->set_field('survey_item', 'insearchform', 0, array('id' => $this->itemid));
                break;
            case SURVEY_MAKELIMITED:
                $this->manage_item_makeadvanced();
                break;
            case SURVEY_MAKEFORALL:
                $this->manage_item_makestandard();
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $action = '.$action);
        }
    }

    /*
     * manage_items
     *
     * @param
     * @return
     */
    public function manage_items() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir.'/tablelib.php');

        $table = new flexible_table('itemslist');

        $paramurl = array('id' => $this->cm->id);
        $table->define_baseurl(new moodle_url('items_manage.php', $paramurl));

        $tablecolumns = array();
        $tablecolumns[] = 'plugin';
        $tablecolumns[] = 'sortindex';
        $tablecolumns[] = 'parentid';
        $tablecolumns[] = 'customnumber';
        $tablecolumns[] = 'content';
        $tablecolumns[] = 'variable';
        $tablecolumns[] = 'formpage';
        $tablecolumns[] = 'availability';
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('plugin', 'survey');
        $tableheaders[] = get_string('sortindex', 'survey');
        $tableheaders[] = get_string('parentid_header', 'survey');
        $tableheaders[] = get_string('customnumber_header', 'survey');
        $tableheaders[] = get_string('content', 'survey');
        $tableheaders[] = get_string('variable', 'survey');
        $tableheaders[] = get_string('page');
        $tableheaders[] = get_string('availability', 'survey');
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        // $table->collapsible(true);
        $table->sortable(true, 'sortindex'); // sorted by sortindex by default
        $table->no_sorting('availability');
        $table->no_sorting('actions');

        $table->column_class('plugin', 'plugin');
        $table->column_class('sortindex', 'sortindex');
        $table->column_class('parentid', 'parentitem');
        $table->column_class('availability', 'availability');
        $table->column_class('formpage', 'formpage');

        $table->column_class('content', 'content');
        $table->column_class('customnumber', 'customnumber');
        $table->column_class('actions', 'actions');

        // $table->initialbars(true);

        // hide the same info whether in two consecutive rows
        // $table->column_suppress('picture');
        // $table->column_suppress('fullname');

        // general properties for the whole table
        // $table->set_attribute('cellpadding', '5');
        if ($this->action == SURVEY_CHANGEORDERASK) {
            $table->set_attribute('id', 'sortitems');
        } else {
            $table->set_attribute('id', 'manageitems');
        }
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
        $namenotset = get_string('namenotset', 'survey');

        // /////////////////////////////////////////////////
        // $paramurl_move definition
        $paramurl_move = array();
        $paramurl_move['id'] = $this->cm->id;
        $paramurl_move['act'] = SURVEY_CHANGEORDER;
        $paramurl_move['itm'] = $this->itemtomove;
        // end of $paramurl_move definition
        // /////////////////////////////////////////////////

        $where = array('surveyid' => $this->survey->id);
        $itemseeds = $DB->get_records('survey_item', $where, $table->get_sql_sort(), '*, id as itemid');
        $drawmovearrow = (count($itemseeds) > 1);

        // this is the very first position, so if the item has a parent, no "moveherebox" must appear
        if (($this->action == SURVEY_CHANGEORDERASK) && (!$this->parentid)) {
            $drawmoveherebox = true;
            $paramurl = $paramurl_move + array('lib' => 0); // lib == just after this sortindex (lib == last item before)
            $basepath = new moodle_url('items_manage.php', $paramurl);

            $icons = '<a class="editing_update" title="'.$moveheretitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('movehere').'" class="movetarget" alt="'.$moveheretitle.'" title="'.$moveheretitle.'" /></a>&nbsp;';

            $tablerow = array();
            $tablerow[] = $icons;
            $tablerow = array_pad($tablerow, count($table->columns), '');

            $table->add_data($tablerow);
        } else {
            $drawmoveherebox = false;
        }

        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->itemid, $itemseed->type, $itemseed->plugin);

            // /////////////////////////////////////////////////
            // $paramurl_base definition
            $paramurl_base = array();
            $paramurl_base['id'] = $this->cm->id;
            $paramurl_base['itemid'] = $item->get_itemid();
            $paramurl_base['type'] = $item->get_type();
            $paramurl_base['plugin'] = $item->get_plugin();
            // end of $paramurl_base definition
            // /////////////////////////////////////////////////

            $tablerow = array();

            if (($this->action == SURVEY_CHANGEORDERASK) && ($item->get_itemid() == $this->itemid)) {
                // do not draw the item you are going to move
                continue;
            }

            // *************************************** plugin
            $plugintitle = get_string('userfriendlypluginname', 'survey'.$item->get_type().'_'.$item->get_plugin());
            $content = '<img src="'.$OUTPUT->pix_url('icon', 'survey'.$item->get_type().'_'.$item->get_plugin()).'" class="icon" alt="'.$plugintitle.'" title="'.$plugintitle.'" />';
            $tablerow[] = $content;

            // *************************************** sortindex
            $tablerow[] = $item->get_sortindex();

            // *************************************** parentid
            if ($item->get_parentid()) {
                // if (!empty($content)) $content .= ' ';
                $message = get_string('parentid_alt', 'survey');
                $parentsortindex = $DB->get_field('survey_item', 'sortindex', array('id' => $item->get_parentid()));
                $content = $parentsortindex;
                $content .= '&nbsp;<img src="'.$OUTPUT->pix_url('link', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
                $content .= $this->condition_from_multiline($item->get_parentcontent());
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // *************************************** customnumber
            if (($item->get_type() == SURVEY_TYPEFIELD) || ($item->get_plugin() == 'label')) {
                $tablerow[] = $item->get_customnumber();
            } else {
                $tablerow[] = '';
            }

            // *************************************** content
            $itemcontent = $item->item_get_main_text();
            $item->set_contentformat(FORMAT_HTML);
            $item->set_contenttrust(1);

            $output = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $item->get_itemid());
            $tablerow[] = $output;

            // *************************************** variable
            if ($item->get_type() == SURVEY_TYPEFIELD) {
                if ($variable = $item->get_variable()) {
                    $content = $variable;
                } else {
                    $content = $namenotset;
                }
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // *************************************** page
            if ($item->get_plugin() != 'pagebreak') {
                $content = $item->get_formpage();
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // *************************************** availability
            if ($item->get_hide()) {
                $message = get_string('hidden', 'survey');
                $icons = '<img src="'.$OUTPUT->pix_url('missing', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';

                // $message = get_string('hidden', 'survey');
                $icons .= '<img src="'.$OUTPUT->pix_url('missing', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />';
            } else {
                if (!$item->get_advanced()) {
                    $message = get_string('available', 'survey');
                    if ($item->get_item_form_requires('advanced')) {
                        $paramurl = $paramurl_base + array('act' => SURVEY_MAKELIMITED);
                        $basepath = new moodle_url('items_manage.php', $paramurl);

                        $icons = '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url('all', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
                    } else {
                        $icons = '<img src="'.$OUTPUT->pix_url('all', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
                    }
                } else {
                    $message = get_string('needrole', 'survey');

                    $paramurl = $paramurl_base + array('act' => SURVEY_MAKEFORALL);
                    $basepath = new moodle_url('items_manage.php', $paramurl);

                    $icons = '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('limited', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
                }

                if ($item->get_insearchform()) {
                    $message = get_string('belongtosearchform', 'survey');

                    $paramurl = $paramurl_base + array('act' => SURVEY_OUTOFSEARCH);
                    $basepath = new moodle_url('items_manage.php', $paramurl);

                    $icons .= '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('insearch', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
                } else {
                    $message = get_string('notinsearchform', 'survey');
                    if ($item->get_item_form_requires('insearchform')) {

                        $paramurl = $paramurl_base + array('act' => SURVEY_ADDTOSEARCH);
                        $basepath = new moodle_url('items_manage.php', $paramurl);

                        $icons .= '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url('missing', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>';
                    } else {
                        $icons .= '<img src="'.$OUTPUT->pix_url('missing', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />';
                    }
                }
            }
            $tablerow[] = $icons;

            // *************************************** actions
            $current_hide = $item->get_hide();
            if ($this->action != SURVEY_CHANGEORDERASK) {

                $icons = '';
                // *************************************** SURVEY_EDITITEM
                $paramurl = $paramurl_base + array('act' => SURVEY_EDITITEM);
                $basepath = new moodle_url('items_setup.php', $paramurl);

                $icons .= '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
                $icons .= '<img src="'.$OUTPUT->pix_url('t/edit').'" class="iconsmall" alt="'.$edittitle.'" title="'.$edittitle.'" /></a>&nbsp;';

                // *************************************** SURVEY_CHANGEORDERASK
                if (!empty($drawmovearrow)) {
                    $paramurl = $paramurl_base + array('act' => SURVEY_CHANGEORDERASK, 'itm' => $item->get_sortindex());
                    $current_parentid = $item->get_parentid();
                    if (!empty($current_parentid)) {
                        $paramurl = $paramurl + array('pid' => $current_parentid);
                    }
                    $basepath = new moodle_url('items_manage.php', $paramurl);

                    $icons .= '<a class="editing_update" title="'.$changetitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('t/move').'" class="iconsmall" alt="'.$changetitle.'" title="'.$changetitle.'" /></a>&nbsp;';
                }

                // *************************************** SURVEY_HIDEITEM/SURVEY_SHOWITEM
                if (!$this->hassubmissions || $CFG->survey_forcemodifications) {
                    $paramurl = $paramurl_base;
                    if (!empty($current_hide)) {
                        $icopath = 't/show';
                        $paramurl = $paramurl + array('act' => SURVEY_SHOWITEM);
                        $message = $showtitle;
                    } else {
                        $icopath = 't/hide';
                        $paramurl = $paramurl + array('act' => SURVEY_HIDEITEM);
                        $message = $hidetitle;
                    }
                    $basepath = new moodle_url('items_manage.php', $paramurl);

                    $icons .= '<a class="editing_update" title="'.$message.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url($icopath).'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
                }

                // *************************************** SURVEY_DELETEITEM
                if (!$this->hassubmissions || $CFG->survey_forcemodifications) {
                    $paramurl = $paramurl_base + array('act' => SURVEY_DELETEITEM, 'itm' => $item->sortindex);
                    $basepath = new moodle_url('items_manage.php', $paramurl);

                    $icons .= '<a class="editing_update" title="'.$deletetitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletetitle.'" title="'.$deletetitle.'" /></a>&nbsp;';
                }

                // *************************************** SURVEY_REQUIRED ON/OFF
                $current_required = $item->get_required();
                if (isset($current_required)) { // it may not be set as in page_break, autofill or some more
                    $paramurl = $paramurl_base;

                    if ($item->get_required()) {
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
                        $basepath = new moodle_url('items_manage.php', $paramurl);
                        $icons .= '<a class="editing_update" title="'.$message.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url($icopath, 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
                    }
                }

                // *************************************** SURVEY_CHANGEINDENT
                $current_indent = $item->get_indent();
                if (isset($current_indent)) { // it may not be set as in page_break, autofill and some more
                    $paramurl = $paramurl_base + array('act' => SURVEY_CHANGEINDENT);

                    if ($item->get_indent() > 0) {
                        $indentvalue = $item->get_indent() - 1;
                        $paramurl['ind'] = $indentvalue;
                        $basepath = new moodle_url('items_manage.php', $paramurl);
                        $icons .= '<a class="editing_update" title="'.$indenttitle.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url('t/left').'" class="iconsmall" alt="'.$indenttitle.'" title="'.$indenttitle.'" /></a>';
                    }
                    $icons .= '['.$item->get_indent().']';
                    if ($item->get_indent() < 9) {
                        $indentvalue = $item->get_indent() + 1;
                        $paramurl['ind'] = $indentvalue;
                        $basepath = new moodle_url('items_manage.php', $paramurl);
                        $icons .= '<a class="editing_update" title="'.$indenttitle.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url('t/right').'" class="iconsmall" alt="'.$indenttitle.'" title="'.$indenttitle.'" /></a>&nbsp;';
                    }
                }
            } else {
                $icons = '';
            }

            $tablerow[] = $icons;

            $addedclass = ($current_hide) ? 'dimmed' : '';
            $table->add_data($tablerow, $addedclass);

            // print_object($item);
            if ($this->action == SURVEY_CHANGEORDERASK) {
                // It was asked to move the item with:
                // $this->itemid e $this->parentid
                if ($this->parentid) { // <-- this is the parentid of the item that I am going to move
                    // if a parentid is foreseen
                    // draw the moveherebox only if the current (already displayed) item has: $item->itemid == $this->parentid
                    // once you start to draw the moveherebox, you will never stop
                    $drawmoveherebox = $drawmoveherebox || ($item->get_itemid() == $this->parentid);

                    // if you just passed an item with $item->get_parentid == $itemid, stop forever
                    if ($item->get_parentid() == $this->itemid) {
                        $drawmoveherebox = false;
                    }
                } else {
                    $drawmoveherebox = $drawmoveherebox && ($item->get_parentid() != $this->itemid);
                }

                if (!empty($drawmoveherebox)) {
                    $paramurl = $paramurl_move + array('lib' => $item->get_sortindex());
                    $basepath = new moodle_url('items_manage.php', $paramurl);

                    $icons = '<a class="editing_update" title="'.$moveheretitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('movehere').'" class="movetarget" alt="'.$moveheretitle.'" title="'.$moveheretitle.'" /></a>&nbsp;';

                    $tablerow = array();
                    $tablerow[] = $icons;
                    $tablerow = array_pad($tablerow, count($table->columns), '');

                    $table->add_data($tablerow);
                }
            }
        }

        $table->set_attribute('align', 'center');
        $table->summary = get_string('itemlist', 'survey');
        $table->print_html();
    }

    /*
     * add_child_node
     * @param &$nodelist
     * @param &$sortindexnodelist
     * @param $additionalcondition
     * @return
     */
    function add_child_node(&$nodelist, &$sortindexnodelist, $additionalcondition) {
        global $DB;

        if (!is_array($additionalcondition)) {
            print_error('Array is expected in add_child_node');
        }

        $i = count($nodelist);
        $itemid = $nodelist[$i-1];
        $where = array('parentid' => $itemid) + $additionalcondition;
        if ($childitems = $DB->get_records('survey_item', $where, 'sortindex', 'id, sortindex')) {
            foreach ($childitems as $childitem) {
                $nodelist[] = (int)$childitem->id;
                $sortindexnodelist[] = $childitem->sortindex;
                $this->add_child_node($nodelist, $sortindexnodelist, $additionalcondition);
            }
        }
    }

    /*
     * add_parent_node
     * @param $additionalcondition
     * @return
     */
    function add_parent_node($additionalcondition) {
        global $DB;

        if (!is_array($additionalcondition)) {
            print_error('Array is expected in add_parent_node');
        }

        $nodelist = array($this->itemid);
        $sortindexnodelist = array();

        // get the first parentid
        $parentitem = new stdClass();
        $parentitem->parentid = $DB->get_field('survey_item', 'parentid', array('id' => $this->itemid));

        $where = array('id' => $parentitem->parentid) + $additionalcondition;

        while ($parentitem = $DB->get_record('survey_item', $where, 'id, parentid, sortindex')) {
            $nodelist[] = (int)$parentitem->id;
            $sortindexnodelist[] = $parentitem->sortindex;
            $where = array('id' => $parentitem->parentid) + $additionalcondition;
        }

        return array($nodelist, $sortindexnodelist);
    }

    /*
     * manage_item_hide
     *
     * @param
     * @return
     */
    public function manage_item_hide() {
        global $DB, $OUTPUT;

        // build tohidelist
        // here I must select the whole tree down
        $tohidelist = array($this->itemid);
        $sortindextohidelist = array();
        $this->add_child_node($tohidelist, $sortindextohidelist, array('hide' => 0));

        $itemstoprocess = count($tohidelist);
        if ($this->confirm == SURVEY_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // ask for confirmation
                $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $this->itemid), MUST_EXIST);

                $a = new stdClass();
                $a->parentid = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
                $a->dependencies = implode(', ', $sortindextohidelist);
                $message = get_string('askitemstohide', 'survey', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_HIDEITEM);

                $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRMED_YES, 'itemid' => $this->itemid, 'type' => $this->type);
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstohide', 'survey'));

                $optionsno = $optionbase + array('cnf' => SURVEY_CONFIRMED_NO);
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die;
            } else { // hide without asking
                $DB->set_field('survey_item', 'hide', 1, array('id' => $this->itemid));
                survey_reset_items_pages($this->cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    // hide items
                    foreach ($tohidelist as $tohideitemid) {
                        $DB->set_field('survey_item', 'hide', 1, array('id' => $tohideitemid));
                    }
                    survey_reset_items_pages($this->cm->instance);
                    break;
                case SURVEY_CONFIRMED_NO:
                    $itemstoprocess = 0;
                    $message = get_string('usercanceled', 'survey');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $confirm = '.$this->confirm);
            }
        }
        return $itemstoprocess; // did you do something?
    }

    /*
     * manage_item_show
     *
     * @param
     * @return
     */
    public function manage_item_show() {
        global $DB, $OUTPUT;

        // build toshowlist
        list($toshowlist, $sortindextoshowlist) = $this->add_parent_node(array('hide' => 1));

        $itemstoprocess = count($toshowlist); // this is the list of ancestors
        if ($this->confirm == SURVEY_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // ask for confirmation
                $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $this->itemid), MUST_EXIST);

                $a = new stdClass();
                $a->lastitem = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
                $a->ancestors = implode(', ', $sortindextoshowlist);
                $message = get_string('askitemstoshow', 'survey', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_SHOWITEM, 'itemid' => $this->itemid);

                $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRMED_YES, 'type' => $this->type);
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstoshow', 'survey'));

                $optionsno = $optionbase + array('cnf' => SURVEY_CONFIRMED_NO);
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die;
            } else { // show without asking
                $DB->set_field('survey_item', 'hide', 0, array('id' => $this->itemid));
                survey_reset_items_pages($this->cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    // hide items
                    foreach ($toshowlist as $toshowitemid) {
                        $DB->set_field('survey_item', 'hide', 0, array('id' => $toshowitemid));
                    }
                    survey_reset_items_pages($this->cm->instance);
                    break;
                case SURVEY_CONFIRMED_NO:
                    $itemstoprocess = 0;
                    $message = get_string('usercanceled', 'survey');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    print_error('codingerror');
            }
        }
        return $itemstoprocess; // did you do something?
    }

    /*
     * manage_item_makeadvanced
     *
     * the idea is: in a chain of parent-child items,
     *     -> items available to each user (standard items) can be parent of item available to each user such as item with limited access (advanced)
     *     -> item with limited access (advanced) can ONLY BE parent of items with limited access (advanced)
     * @param
     * @return
     */
    public function manage_item_makeadvanced() {
        global $DB, $OUTPUT;

        // build toadvancedlist
        // here I must select the whole tree down
        $toadvancedlist = array($this->itemid);
        $sortindextoadvancedlist = array();
        $this->add_child_node($toadvancedlist, $sortindextoadvancedlist, array('advanced' => 0));

        $itemstoprocess = count($toadvancedlist);
        if ($this->confirm == SURVEY_UNCONFIRMED) {
            if (count($toadvancedlist) > 1) { // ask for confirmation
                $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $this->itemid), MUST_EXIST);

                $a = new stdClass();
                $a->parentid = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
                $a->dependencies = implode(', ', $sortindextoadvancedlist);
                $message = get_string('askitemstoadvanced', 'survey', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_MAKELIMITED);

                $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRMED_YES, 'itemid' => $this->itemid, 'type' => $this->type);
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstoadvanced', 'survey'));

                $optionsno = $optionbase + array('cnf' => SURVEY_CONFIRMED_NO);
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die;
            } else { // hide without asking
                $DB->set_field('survey_item', 'advanced', 1, array('id' => $this->itemid));
                survey_reset_items_pages($this->cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    // hide items
                    foreach ($toadvancedlist as $tohideitemid) {
                        $DB->set_field('survey_item', 'advanced', 1, array('id' => $tohideitemid));
                    }
                    survey_reset_items_pages($this->cm->instance);
                    break;
                case SURVEY_CONFIRMED_NO:
                    $itemstoprocess = 0;
                    $message = get_string('usercanceled', 'survey');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $confirm = '.$this->confirm);
            }
        }
        return $itemstoprocess; // did you do something?
    }

    /*
     * manage_item_makestandard
     *
     * @param
     * @return
     */
    public function manage_item_makestandard() {
        global $DB, $OUTPUT;

        // build tostandardlist
        list($tostandardlist, $sortindextostandardlist) = $this->add_parent_node(array('advanced' => 1));

        $itemstoprocess = count($tostandardlist); // this is the list of ancestors
        if ($this->confirm == SURVEY_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // ask for confirmation
                $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $this->itemid), MUST_EXIST);

                $a = new stdClass();
                $a->lastitem = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
                $a->ancestors = implode(', ', $sortindextostandardlist);
                $message = get_string('askitemstostandard', 'survey', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_MAKEFORALL, 'itemid' => $this->itemid);

                $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRMED_YES, 'type' => $this->type);
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstostandard', 'survey'));

                $optionsno = $optionbase + array('cnf' => SURVEY_CONFIRMED_NO);
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die;
            } else { // show without asking
                $DB->set_field('survey_item', 'advanced', 0, array('id' => $this->itemid));
                survey_reset_items_pages($this->cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    // hide items
                    foreach ($tostandardlist as $toshowitemid) {
                        $DB->set_field('survey_item', 'advanced', 0, array('id' => $toshowitemid));
                    }
                    survey_reset_items_pages($this->cm->instance);
                    break;
                case SURVEY_CONFIRMED_NO:
                    $itemstoprocess = 0;
                    $message = get_string('usercanceled', 'survey');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm);
            }
        }
        return $itemstoprocess; // did you do something?
    }

    /*
     * manage_item_deletion
     *
     * @param
     * @return
     */
    public function manage_item_deletion() {
        global $CFG, $DB, $OUTPUT;

        if ($this->confirm == SURVEY_UNCONFIRMED) {
            // ask for confirmation
            // in the frame of the confirmation I need to declare whether some child will break the link
            $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $this->itemid), MUST_EXIST);
            $a = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
            if (empty($a)) {
                $a = get_string('userfriendlypluginname', 'surveyformat_'.$plugin);
            }
            $message = get_string('askdeleteoneitem', 'survey', $a);

            // is there any child item link to break
            if ($childitems = $DB->get_records('survey_item', array('parentid' => $this->itemid), 'sortindex', 'sortindex')) { // sortindex is suposed to be a valid key
                $childitems = array_keys($childitems);
                $nodes = implode(', ', $childitems);
                $message .= get_string('deletebreaklinks', 'survey', $nodes);
                $labelyes = get_string('confirmitemsdeletion', 'survey');
            } else {
                $labelyes = get_string('yes');
            }

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_DELETEITEM);

            $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRMED_YES, 'plugin' => $this->plugin, 'type' => $this->type, 'itemid' => $this->itemid, 'itm' => $this->itemtomove);
            $urlyes = new moodle_url('items_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, $labelyes);

            $optionsno = $optionbase + array('cnf' => SURVEY_CONFIRMED_NO);
            $urlno = new moodle_url('items_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die;
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    $deleted = array();
                    $maxsortindex = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $this->cm->instance));
                    if ($childrenseeds = $DB->get_records('survey_item', array('parentid' => $this->itemid), 'id', 'id, type, plugin')) {
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
                    $deletingrecord = $DB->get_record('survey_item', array('id' => $this->itemid), 'id, content, content_sid, externalname, sortindex', MUST_EXIST);
                    $killedsortindex = $deletingrecord->sortindex;

                    $a = survey_get_sid_field_content($deletingrecord);
                    if (empty($a)) {
                        $a = get_string('userfriendlypluginname', 'surveyformat_'.$this->plugin);
                    }

                    require_once($CFG->dirroot.'/mod/survey/'.$this->type.'/'.$this->plugin.'/plugin.class.php');
                    $itemclass = 'survey'.$this->type.'_'.$this->plugin;
                    $item = new $itemclass($this->itemid);

                    $item->item_delete_item($this->itemid);

                    // renum sortindex
                    $sql = 'SELECT id
                            FROM {survey_item}
                            WHERE surveyid = :surveyid
                            AND sortindex > :killedsortindex
                            ORDER BY sortindex';
                    $itemlist = $DB->get_recordset_sql($sql, array('surveyid' => $this->survey->id, 'killedsortindex' => $killedsortindex));
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
     * reorder_items
     *
     * @param
     * @return
     */
    public function reorder_items() {
        global $DB;

        // I start loading the id of the item I want to move starting from its known sortindex
        $itemid = $DB->get_field('survey_item', 'id', array('surveyid' => $this->survey->id, 'sortindex' => $this->itemtomove));

        // Am I moving it backward or forward?
        if ($this->itemtomove > $this->lastitembefore) {
            // moving the item backward
            $searchitem = $this->itemtomove-1;
            $replaceitem = $this->itemtomove;

            while ($searchitem > $this->lastitembefore) {
                $DB->set_field('survey_item', 'sortindex', $replaceitem, array('surveyid' => $this->survey->id, 'sortindex' => $searchitem));
                $replaceitem = $searchitem;
                $searchitem--;
            }

            $DB->set_field('survey_item', 'sortindex', $replaceitem, array('surveyid' => $this->survey->id, 'id' => $itemid));
        } else {
            // moving the item forward
            $searchitem = $this->itemtomove+1;
            $replaceitem = $this->itemtomove;

            while ($searchitem <= $this->lastitembefore) {
                $DB->set_field('survey_item', 'sortindex', $replaceitem, array('surveyid' => $this->survey->id, 'sortindex' => $searchitem));
                $replaceitem = $searchitem;
                $searchitem++;
            }

            $DB->set_field('survey_item', 'sortindex', $replaceitem, array('id' => $itemid));
        }

        // you changed item order
        // so, do no forget to reset items per page
        survey_reset_items_pages($this->survey->id);
    }

    /*
     * reorder_items
     *
     * @param
     * @return
     */
    public function validate_relations() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir.'/tablelib.php');

        $table = new flexible_table('itemslist');

        $paramurl = array('id' => $this->cm->id);
        $table->define_baseurl(new moodle_url('items_validate.php', $paramurl));

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
        $tableheaders[] = get_string('parentid_header', 'survey');
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

        // hide the same info whether in two consecutive rows
        // $table->column_suppress('picture');
        // $table->column_suppress('fullname');

        // general properties for the whole table
        // $table->set_attribute('cellpadding', '5');
        $table->set_attribute('id', 'validaterelations');
        $table->set_attribute('class', 'generaltable');
        // $table->set_attribute('width', '90%');
        $table->setup();

        /*****************************************************************************/
        $edittitle = get_string('edit');
        $okstring = get_string('ok');

        $sql = 'SELECT si.*, si.id as itemid, si.plugin, si.type
                FROM {survey_item} si
                WHERE si.surveyid = :surveyid';
        if ($table->get_sql_sort()) {
            $sql .= ' ORDER BY '.$table->get_sql_sort();
        } else {
            $sql .= ' ORDER BY si.sortindex';
        }

        $itemseeds = $DB->get_recordset_sql($sql, array('surveyid' => $this->survey->id), $table->get_sql_sort());
        echo $OUTPUT->box(get_string('validationinfo', 'survey'));

        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->itemid, $itemseed->type, $itemseed->plugin);
            $current_hide = $item->get_hide();

            if ($item->get_parentid()) {
                $parentseed = $DB->get_record('survey_item', array('id' => $item->get_parentid()), 'plugin', MUST_EXIST);
                require_once($CFG->dirroot.'/mod/survey/field/'.$parentseed->plugin.'/plugin.class.php');
                $itemclass = 'surveyfield_'.$parentseed->plugin;
                $parentitem = new $itemclass($item->get_parentid());
            }

            $tablerow = array();

            // *************************************** plugin
            $plugintitle = get_string('pluginname', 'survey'.$item->get_type().'_'.$item->get_plugin());
            $content = '<img src="'.$OUTPUT->pix_url('icon', 'survey'.$item->get_type().'_'.$item->get_plugin()).'" class="icon" alt="'.$plugintitle.'" title="'.$plugintitle.'" />';
            $tablerow[] = $content;

            // *************************************** content
            $itemcontent = $item->get_content();
            $item->set_contentformat(FORMAT_HTML);
            $item->set_contenttrust(1);

            $output = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $this->context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $item->get_itemid());
            $tablerow[] = $output;

            // *************************************** sortindex
            $tablerow[] = $item->get_sortindex();

            // *************************************** parentid
            if ($item->get_parentid()) {
                $message = get_string('parentid_alt', 'survey');
                $content = $parentitem->get_sortindex();
                $content .= '&nbsp;<img src="'.$OUTPUT->pix_url('link', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
                $content .= $this->condition_from_multiline($item->get_parentcontent());
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // *************************************** parentconstraints
            if ($item->get_parentid()) {
                $tablerow[] = $parentitem->item_list_constraints();
            } else {
                $tablerow[] = '-';
            }

            // *************************************** status
            if ($item->get_parentid()) {
                $status = $parentitem->parent_validate_child_constraints($item->parentvalue);
                if ($status === true) {
                    $tablerow[] = $okstring;
                } else {
                    if ($status === false) {
                        if (empty($current_hide)) {
                            $tablerow[] = '<span class="errormessage">'.get_string('wrongrelation', 'survey', $item->get_parentcontent()).'</span>';
                        } else {
                            $tablerow[] = get_string('wrongrelation', 'survey', $item->get_parentcontent());
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
            $paramurl_base['id'] = $this->cm->id;
            $paramurl_base['itemid'] = $item->get_itemid();
            $paramurl_base['type'] = $item->get_type();
            $paramurl_base['plugin'] = $item->get_plugin();
            // end of $paramurl_base definition
            // /////////////////////////////////////////////////

            // *************************************** SURVEY_EDITITEM
            $paramurl = $paramurl_base + array('act' => SURVEY_EDITITEM);
            $basepath = new moodle_url('items_setup.php', $paramurl);

            $icons = '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('t/edit').'" class="iconsmall" alt="'.$edittitle.'" title="'.$edittitle.'" /></a>&nbsp;';

            $tablerow[] = $icons;

            $addedclass = empty($current_hide) ? '' : 'dimmed';
            $table->add_data($tablerow, $addedclass);
        }
        $itemseeds->close();

        $table->set_attribute('align', 'center');
        $table->summary = get_string('itemlist', 'survey');
        $table->print_html();
    }

    /*
     * condition_from_multiline
     *
     * @param &$libcontent, $values, $tablename, $currentplugin
     * @return
     */
    public function condition_from_multiline($parentcontent) {
        $constarains = str_replace("\r", '', $parentcontent);
        $constarains = explode("\n", $constarains);

        return implode(' & ', $constarains);
    }

    /*
     * display_user_feedback
     *
     * @param &$libcontent, $values, $tablename, $currentplugin
     * @return
     */
    public function display_user_feedback() {
        global $OUTPUT;

        if ($this->userfeedback == SURVEY_NOFEEDBACK) {
            return;
        }

        // look at position 1
        $bit = $this->userfeedback & 2; // bitwise logic
        if ($bit) { // edit
            $bit = $this->userfeedback & 1; // bitwise logic
            if ($bit) {
                $message = get_string('itemeditok', 'survey');
            } else {
                $message = get_string('itemeditfail', 'survey');
            }
        } else {    // add
            $bit = $this->userfeedback & 1; // bitwise logic
            if ($bit) {
                $message = get_string('itemaddok', 'survey');
            } else {
                $message = get_string('itemaddfail', 'survey');
            }
        }

        for ($position = 2; $position <= 5; $position++) {
            $bit = $this->userfeedback & pow(2, $position); // bitwise logic
            switch ($position) {
                case 2: // a chain of items is now shown
                    if ($bit) {
                        $message .= '<br />'.get_string('itemeditshow', 'survey');
                    }
                    break;
                case 3: // a chain of items is now hided because one item was hided
                    if ($bit) {
                        $message .= '<br />'.get_string('itemedithidehide', 'survey');
                    }
                    break;
                case 4: // a chain of items was moved in the user entry form
                    if ($bit) {
                        $message .= '<br />'.get_string('itemeditshowinbasicform', 'survey');
                    }
                    break;
                case 5: // a chain of items was removed from the user entry form
                    if ($bit) {
                        $message .= '<br />'.get_string('itemeditmakeadvanced', 'survey');
                    }
                    break;
            }
        }

        echo $OUTPUT->box($message, 'notice centerpara');
    }

    /*
     * itemwelcome
     *
     * @param &$libcontent, $values, $tablename, $currentplugin
     * @return
     */
    public function item_welcome() {
        global $OUTPUT;

        $plugintitle = get_string('userfriendlypluginname', 'survey'.$this->type.'_'.$this->plugin);

        $message = '<img src="'.$OUTPUT->pix_url('icon', 'survey'.$this->type.'_'.$this->plugin).'" class="icon" alt="'.$plugintitle.'" title="'.$plugintitle.'" />';
        $message .= get_string($this->type, 'survey').': '.$plugintitle;

        echo $OUTPUT->box($message);
    }

}