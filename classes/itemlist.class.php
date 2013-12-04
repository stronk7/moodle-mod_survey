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
    public function __construct($cm, $context, $survey, $type, $plugin, $itemid, $action, $view, $itemtomove,
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
        $this->view = $view;
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
    public function drop_multilang() {
        if ($this->survey->template) {
            $this->action = SURVEY_DROPMULTILANG;
            if ($this->confirm != SURVEY_UNCONFIRMED) {
                $this->manage_actions();
            }
        }
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
            case SURVEY_HIDEITEM:
                $this->manage_item_hide();
                break;
            case SURVEY_SHOWITEM:
                $this->manage_item_show();
                break;
            case SURVEY_DELETEITEM:
                $this->manage_item_deletion();
                break;
            case SURVEY_DROPMULTILANG:
                $this->manage_item_dropmultilang();
                break;
            case SURVEY_CHANGEORDER:
                // it was required to move the item $this->itemid
                $this->reorder_items();
                break;
            case SURVEY_REQUIREDON:
                $DB->set_field('survey'.$this->type.'_'.$this->plugin, 'required', 1, array('itemid' => $this->itemid));
                break;
            case SURVEY_REQUIREDOFF:
                $DB->set_field('survey'.$this->type.'_'.$this->plugin, 'required', 0, array('itemid' => $this->itemid));
                break;
            case SURVEY_CHANGEINDENT:
                $DB->set_field('survey'.$this->type.'_'.$this->plugin, 'indent', $this->nextindent, array('itemid' => $this->itemid));
                break;
            case SURVEY_ADDTOSEARCH:
                $item = survey_get_item($this->itemid, $this->type, $this->plugin);
                if ($item->get_form_requires('insearchform')) {
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

        $riskyediting = ($this->survey->riskyeditdeadline > time());

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
        $table->no_sorting('content');
        $table->no_sorting('variable');
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
        if ($this->view == SURVEY_CHANGEORDERASK) {
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
        $multilangtitle = get_string('multilang', 'survey');
        $indenttitle = get_string('indent', 'survey');
        $moveheretitle = get_string('movehere');
        $namenotset = get_string('namenotset', 'survey');

        // -----------------------------
        // $paramurlmove definition
        $paramurlmove = array();
        $paramurlmove['id'] = $this->cm->id;
        $paramurlmove['act'] = SURVEY_CHANGEORDER;
        $paramurlmove['itm'] = $this->itemtomove;
        // end of $paramurlmove definition
        // -----------------------------

        $where = array('surveyid' => $this->survey->id);
        $itemseeds = $DB->get_records('survey_item', $where, $table->get_sql_sort(), '*, id as itemid');
        $drawmovearrow = (count($itemseeds) > 1);

        // this is the very first position, so if the item has a parent, no "moveherebox" must appear
        if (($this->view == SURVEY_CHANGEORDERASK) && (!$this->parentid)) {
            $drawmoveherebox = true;
            $paramurl = $paramurlmove;
            $paramurl['lib'] = 0; // lib == just after this sortindex (lib == last item before)
            $paramurl['sesskey'] = sesskey();

            $icons = $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                new pix_icon('movehere', $moveheretitle, 'moodle', array('title' => $moveheretitle)),
                null, array('title' => $moveheretitle));

            $tablerow = array();
            $tablerow[] = $icons;
            $tablerow = array_pad($tablerow, count($table->columns), '');

            $table->add_data($tablerow);
        } else {
            $drawmoveherebox = false;
        }

        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->itemid, $itemseed->type, $itemseed->plugin);

            // -----------------------------
            // $paramurlbase definition
            $paramurlbase = array();
            $paramurlbase['id'] = $this->cm->id;
            $paramurlbase['itemid'] = $item->get_itemid();
            $paramurlbase['type'] = $item->get_type();
            $paramurlbase['plugin'] = $item->get_plugin();
            // end of $paramurlbase definition
            // -----------------------------

            $tablerow = array();

            if (($this->view == SURVEY_CHANGEORDERASK) && ($item->get_itemid() == $this->itemid)) {
                // do not draw the item you are going to move
                continue;
            }

            // plugin
            $plugintitle = get_string('userfriendlypluginname', 'survey'.$item->get_type().'_'.$item->get_plugin());
            $content = $OUTPUT->pix_icon('icon', $plugintitle, 'survey'.$item->get_type().'_'.$item->get_plugin(),
                    array('title' => $plugintitle, 'class' => 'icon'));

            $tablerow[] = $content;

            // sortindex
            $tablerow[] = $item->get_sortindex();

            // parentid
            if ($item->get_parentid()) {
                // if (!empty($content)) $content .= ' ';
                $message = get_string('parentid_alt', 'survey');
                $parentsortindex = $DB->get_field('survey_item', 'sortindex', array('id' => $item->get_parentid()));
                $content = $parentsortindex;
                $content .= $OUTPUT->pix_icon('link', $message, 'survey',
                        array('title' => $message, 'class' => 'iconsmall'));
                $content .= $this->condition_from_multiline($item->get_parentcontent());
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // customnumber
            if (($item->get_type() == SURVEY_TYPEFIELD) || ($item->get_plugin() == 'label')) {
                $tablerow[] = $item->get_customnumber();
            } else {
                $tablerow[] = '';
            }

            // content
            $item->set_contentformat(FORMAT_HTML);
            $item->set_contenttrust(1);

            $output = $item->get_content();
            $tablerow[] = $output;

            // variable
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

            // page
            if ($item->item_uses_form_page()) {
                $content = $item->get_formpage();
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // availability
            $currenthide = $item->get_hide();
            if ($currenthide) {
                $message = get_string('hidden', 'survey');
                $icons = $OUTPUT->pix_icon('absent', $message, 'survey', array('title' => $message, 'class' => 'iconsmall'));

                // $message = get_string('hidden', 'survey');
                $icons .= $OUTPUT->pix_icon('absent', $message, 'survey', array('title' => $message, 'class' => 'iconsmall'));
            } else {
                // first icon: advanced vs generally available
                if (!$item->get_advanced()) {
                    $message = get_string('available', 'survey');
                    if ($item->get_form_requires('advanced')) {
                        $paramurl = $paramurlbase;
                        $paramurl['act'] = SURVEY_MAKELIMITED;
                        $paramurl['sesskey'] = sesskey();

                        $icons = $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                            new pix_icon('all', $message, 'survey', array('title' => $message)),
                            null, array('title' => $edittitle));
                    } else {
                        $icons = $OUTPUT->pix_icon('all', $message, 'survey', array('title' => $message, 'class' => 'iconsmall'));
                    }
                } else {
                    $message = get_string('needrole', 'survey');
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEY_MAKEFORALL;
                    $paramurl['sesskey'] = sesskey();

                    $icons = $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                        new pix_icon('limited', $message, 'survey', array('title' => $message)),
                        null, array('title' => $edittitle));
                }

                // second icon: insearchform vs not insearchform
                if ($item->get_insearchform()) {
                    $message = get_string('belongtosearchform', 'survey');
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEY_OUTOFSEARCH;
                    $paramurl['sesskey'] = sesskey();

                    $icons .= $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                        new pix_icon('insearch', $message, 'survey', array('title' => $message)),
                        null, array('title' => $edittitle));
                } else {
                    $message = get_string('notinsearchform', 'survey');
                    if ($item->get_form_requires('insearchform')) {
                        $paramurl = $paramurlbase;
                        $paramurl['act'] = SURVEY_ADDTOSEARCH;
                        $paramurl['sesskey'] = sesskey();

                        $icons .= $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                            new pix_icon('absent', $message, 'survey', array('title' => $message)),
                            null, array('title' => $edittitle));
                    } else {
                        $icons .= $OUTPUT->pix_icon('absent', $message, 'survey', array('title' => $message, 'class' => 'iconsmall'));
                    }
                }
            }

            // third icon: hide vs show
            if (!$this->hassubmissions || $riskyediting) {
                $paramurl = $paramurlbase;
                $paramurl['sesskey'] = sesskey();
                if (!empty($currenthide)) {
                    $icopath = 't/show';
                    $paramurl['act'] = SURVEY_SHOWITEM;
                    $message = $showtitle;
                } else {
                    $icopath = 't/hide';
                    $paramurl['act'] = SURVEY_HIDEITEM;
                    $message = $hidetitle;
                }

                $icons .= $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                    new pix_icon($icopath, $message, 'moodle', array('title' => $message)),
                    null, array('title' => $message));
            }
            $tablerow[] = $icons;

            // actions
            if ($this->view != SURVEY_CHANGEORDERASK) {

                $icons = '';
                // SURVEY_EDITITEM
                $paramurl = $paramurlbase;
                $paramurl['view'] = SURVEY_EDITITEM;
                $paramurl['sesskey'] = sesskey();

                $icons .= $OUTPUT->action_icon(new moodle_url('items_setup.php', $paramurl),
                    new pix_icon('t/edit', $edittitle, 'moodle', array('title' => $edittitle)),
                    null, array('title' => $edittitle));

                // SURVEY_CHANGEORDERASK
                if (!empty($drawmovearrow)) {
                    $paramurl = $paramurlbase;
                    $paramurl['view'] = SURVEY_CHANGEORDERASK;
                    $paramurl['itm'] = $item->get_sortindex();

                    $currentparentid = $item->get_parentid();
                    if (!empty($currentparentid)) {
                        $paramurl['pid'] = $currentparentid;
                    }

                    $icons .= $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                        new pix_icon('t/move', $edittitle, 'moodle', array('title' => $edittitle)),
                        null, array('title' => $edittitle));
                }

                // SURVEY_DELETEITEM
                if (!$this->hassubmissions || $riskyediting) {
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEY_DELETEITEM;
                    $paramurl['sesskey'] = sesskey();

                    $icons .= $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                        new pix_icon('t/delete', $deletetitle, 'moodle', array('title' => $deletetitle)),
                        null, array('title' => $deletetitle));
                }

                // SURVEY_REQUIRED ON/OFF
                $currentrequired = $item->get_required();
                if ($currentrequired !== false) { // it may not be set as in page_break, autofill or some more
                    $paramurl = $paramurlbase;
                    $paramurl['sesskey'] = sesskey();

                    if ($item->get_required()) {
                        $icopath = 'red';
                        $paramurl['act'] = SURVEY_REQUIREDOFF;
                        $message = $optionaltitle;
                    } else {
                        if ($item->item_mandatory_is_allowed()) {
                            $icopath = 'green';
                            $paramurl['act'] = SURVEY_REQUIREDON;
                            $message = $requiredtitle;
                        } else {
                            $icopath = 'greenlock';
                            $message = $onlyoptionaltitle;
                        }
                    }

                    if ($icopath == 'greenlock') {
                        $icons .= $OUTPUT->pix_icon($icopath, $message, 'survey', array('title' => $message, 'class' => 'icon'));
                    } else {
                        $icons .= $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                            new pix_icon($icopath, $message, 'survey', array('title' => $message)),
                            null, array('title' => $message));
                    }
                }

                // SURVEY_CHANGEINDENT
                $currentindent = $item->get_indent();
                if ($currentindent !== false) { // it may not be set as in page_break, autofill and some more
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEY_CHANGEINDENT;
                    $paramurl['sesskey'] = sesskey();

                    if ($item->get_indent() > 0) {
                        $indentvalue = $item->get_indent() - 1;
                        $paramurl['ind'] = $indentvalue;

                        $icons .= $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                            new pix_icon('t/left', $indenttitle, 'moodle', array('title' => $indenttitle)),
                            null, array('title' => $indenttitle));
                    }
                    $icons .= '&nbsp;['.$item->get_indent().']';
                    if ($item->get_indent() < 9) {
                        $indentvalue = $item->get_indent() + 1;
                        $paramurl['ind'] = $indentvalue;

                        $icons .= $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                            new pix_icon('t/right', $indenttitle, 'moodle', array('title' => $indenttitle)),
                            null, array('title' => $indenttitle));
                    }
                }
            } else {
                $icons = '';
            }

            $tablerow[] = $icons;

            $addedclass = ($currenthide) ? 'dimmed' : '';
            $table->add_data($tablerow, $addedclass);

            // print_object($item);
            if ($this->view == SURVEY_CHANGEORDERASK) {
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
                    $paramurl = $paramurlmove;
                    $paramurl['lib'] = $item->get_sortindex();
                    $paramurl['sesskey'] = sesskey();

                    $icons = $OUTPUT->action_icon(new moodle_url('items_manage.php', $paramurl),
                        new pix_icon('movehere', $moveheretitle, 'moodle', array('title' => $moveheretitle)),
                        null, array('title' => $moveheretitle));

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
    public function add_child_node(&$nodelist, &$sortindexnodelist, $additionalcondition) {
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
    public function add_parent_node($additionalcondition) {
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
                $item = survey_get_item($this->itemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->parentid = $item->get_content();
                $a->dependencies = implode(', ', $sortindextohidelist);
                $message = get_string('askitemstohide', 'survey', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_HIDEITEM);

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEY_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->itemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstohide', 'survey'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEY_CONFIRMED_NO;
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
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
                $item = survey_get_item($this->itemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->lastitem = $item->get_content();
                $a->ancestors = implode(', ', $sortindextoshowlist);
                $message = get_string('askitemstoshow', 'survey', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_SHOWITEM, 'itemid' => $this->itemid);

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEY_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->itemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstoshow', 'survey'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEY_CONFIRMED_NO;
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
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
                $item = survey_get_item($this->itemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->parentid = $item->get_content();
                $a->dependencies = implode(', ', $sortindextoadvancedlist);
                $message = get_string('askitemstoadvanced', 'survey', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_MAKELIMITED);

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEY_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->itemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstoadvanced', 'survey'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEY_CONFIRMED_NO;
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
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
                $item = survey_get_item($this->itemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->lastitem = $item->get_content();
                $a->ancestors = implode(', ', $sortindextostandardlist);
                $message = get_string('askitemstostandard', 'survey', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_MAKEFORALL, 'itemid' => $this->itemid);

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEY_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->itemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstostandard', 'survey'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEY_CONFIRMED_NO;
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
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
            $item = survey_get_item($this->itemid, $this->type, $this->plugin);

            $message = get_string('askdeleteoneitem', 'survey', $item->get_content());

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

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEY_CONFIRMED_YES;
            $optionsyes['itemid'] = $this->itemid;
            $optionsyes['plugin'] = $this->plugin;
            $optionsyes['type'] = $this->type;
            $urlyes = new moodle_url('items_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, $labelyes);

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEY_CONFIRMED_NO;
            $urlno = new moodle_url('items_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
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
                            $item = survey_get_item($childseed->id, $childseed->type, $childseed->plugin);
                            $item->item_delete_item($childseed->id);
                        }
                    }

                    // get the content of the item for the closing message
                    $item = survey_get_item($this->itemid, $this->type, $this->plugin);

                    $a = $item->get_content();
                    $killedsortindex = $item->get_sortindex();
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
     * manage_item_dropmultilang
     *
     * @param
     * @return
     */
    public function manage_item_dropmultilang() {
        global $CFG, $DB, $OUTPUT;

        if ($this->confirm == SURVEY_UNCONFIRMED) {
            // ask for confirmation
            $message = get_string('mastertemplate_noedit', 'survey');

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEY_DROPMULTILANG);

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEY_CONFIRMED_YES;
            $urlyes = new moodle_url('items_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('yes'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEY_CONFIRMED_NO;
            $urlno = new moodle_url('items_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    $template = $this->survey->template;
                    $where = array('surveyid' => $this->survey->id);
                    $itemseeds = $DB->get_records('survey_item', $where, 'sortindex', 'id, type, plugin');
                    foreach ($itemseeds as $itemseed) {
                        $id = $itemseed->id;
                        $type = $itemseed->type;
                        $plugin = $itemseed->plugin;
                        $item = survey_get_item($id, $type, $plugin);
                        if ($multilangfields = $item->item_get_multilang_fields()) {
                            foreach ($multilangfields as $plugin => $fieldnames) {
                                $record = new stdClass();
                                if ($plugin == 'item') {
                                    $record->id = $item->get_itemid();
                                } else {
                                    $record->id = $item->get_pluginid();
                                }

                                $where = array('id' => $record->id);
                                $fieldlist = implode(',', $multilangfields[$plugin]);
                                $reference = $DB->get_record('survey'.$type.'_'.$plugin, $where, $fieldlist, MUST_EXIST);

                                foreach ($fieldnames as $fieldname) {
                                    $stringkey = $reference->{$fieldname};
                                    if (strlen($stringkey)) {
                                        $record->{$fieldname} = get_string($stringkey, 'surveytemplate_'.$template);
                                    } else {
                                        $record->{$fieldname} = null;
                                    }
                                }
                                $DB->update_record('survey'.$type.'_'.$plugin, $record);
                            }
                        }
                    }

                    $record = new stdClass();
                    $record->id = $this->survey->id;
                    $record->template = null;
                    $DB->update_record('survey', $record);

                    $returnurl = new moodle_url('items_manage.php', array('id' => $this->cm->id));
                    redirect($returnurl);
                    break;
                case SURVEY_CONFIRMED_NO:
                    $paramurl = array('id' => $this->cm->id, 'view' => SURVEY_PREVIEWSURVEY);
                    $returnurl = new moodle_url('view.php', $paramurl);
                    redirect($returnurl);
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
        $table->no_sorting('content');
        $table->no_sorting('parentitem');
        $table->no_sorting('parentconstraints');
        $table->no_sorting('status');
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
            $currenthide = $item->get_hide();

            if ($item->get_parentid()) {
                $parentitem = survey_get_item($item->get_parentid()); // here I do not know type and plugin
            }

            $tablerow = array();

            // plugin
            $plugintitle = get_string('pluginname', 'survey'.$item->get_type().'_'.$item->get_plugin());
            $content = $OUTPUT->pix_icon('icon', $plugintitle, 'survey'.$item->get_type().'_'.$item->get_plugin(),
                    array('title' => $plugintitle, 'class' => 'iconsmall'));
            $tablerow[] = $content;

            // content
            $item->set_contentformat(FORMAT_HTML);
            $item->set_contenttrust(1);

            $output = $item->get_content();
            $tablerow[] = $output;

            // sortindex
            $tablerow[] = $item->get_sortindex();

            // parentid
            if ($item->get_parentid()) {
                $message = get_string('parentid_alt', 'survey');
                $content = $parentitem->get_sortindex();
                $content .= $OUTPUT->pix_icon('link', $message, 'survey',
                        array('title' => $message, 'class' => 'iconsmall'));
                $content .= $this->condition_from_multiline($item->get_parentcontent());
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // parentconstraints
            if ($item->get_parentid()) {
                $tablerow[] = $parentitem->item_list_constraints();
            } else {
                $tablerow[] = '-';
            }

            // status
            if ($item->get_parentid()) {
                $status = $parentitem->parent_validate_child_constraints($item->parentvalue);
                if ($status === true) {
                    $tablerow[] = $okstring;
                } else {
                    if ($status === false) {
                        if (empty($currenthide)) {
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

            // actions
            // -----------------------------
            // $paramurlbase definition
            $paramurlbase = array();
            $paramurlbase['id'] = $this->cm->id;
            $paramurlbase['itemid'] = $item->get_itemid();
            $paramurlbase['type'] = $item->get_type();
            $paramurlbase['plugin'] = $item->get_plugin();
            // end of $paramurlbase definition
            // -----------------------------

            // SURVEY_EDITITEM
            $paramurl = $paramurlbase;
            $paramurl['view'] = SURVEY_EDITITEM;

            $icons = $OUTPUT->action_icon(new moodle_url('items_setup.php', $paramurl),
                new pix_icon('t/edit', $edittitle, 'moodle', array('title' => $edittitle)),
                null, array('title' => $edittitle));

            $tablerow[] = $icons;

            $addedclass = empty($currenthide) ? '' : 'dimmed';
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

        $message = $OUTPUT->pix_icon('icon', $plugintitle, 'survey'.$this->type.'_'.$this->plugin,
                array('title' => $plugintitle, 'class' => 'icon'));
        $message .= get_string($this->type, 'survey').': '.$plugintitle;

        echo $OUTPUT->box($message);
    }

    /*
     * prevent_direct_user_input
     *
     * @param
     * @return
     */
    public function prevent_direct_user_input() {
        if ($this->survey->template) {
            print_error('incorrectaccessdetected', 'survey');
        }
    }
}
