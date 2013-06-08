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
class mod_survey_itemelement {
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
     * Class constructor
     */
    public function __construct($survey, $type, $plugin) {
        $this->survey = $survey;
        $this->type = $type;
        $this->plugin = $plugin;
    }

    /*
     * manage_actions
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
                $DB->set_field('survey_item', 'required', '1', array('id' => $this->itemid));
                break;
            case SURVEY_REQUIREDOFF:
                $DB->set_field('survey_item', 'required', '0', array('id' => $this->itemid));
                break;
            case SURVEY_CHANGEINDENT:
                $DB->set_field('survey_item', 'indent', $this->nextindent, array('id' => $this->itemid));
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $action = '.$action);
        }
    }

    /*
     * manage_items
     * @param
     * @return
     */
    public function manage_items() {
        global $PAGE, $CFG, $DB, $OUTPUT;

        $cm = $PAGE->cm;

        require_once($CFG->libdir.'/tablelib.php');

        $context = context_module::instance($cm->id);

        $table = new flexible_table('itemslist');

        $paramurl = array('id' => $cm->id);
        $table->define_baseurl(new moodle_url('items_manage.php', $paramurl));

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

        // /////////////////////////////////////////////////
        // $paramurl_move definition
        $paramurl_move = array();
        $paramurl_move['id'] = $cm->id;
        $paramurl_move['act'] = SURVEY_CHANGEORDER;
        $paramurl_move['itm'] = $this->itemtomove;
        // end of $paramurl_move definition
        // /////////////////////////////////////////////////

        if ($this->hassubmissions) {
            echo $OUTPUT->box(get_string('hassubmissions', 'survey'));
        }

        $sql = 'SELECT si.*, si.id as itemid, si.plugin, si.type
                FROM {survey_item} si
                WHERE si.surveyid = :surveyid';
        if ($table->get_sql_sort()) {
            $sort = ' ORDER BY '.$table->get_sql_sort();
        } else {
            $sort = ' ORDER BY si.sortindex';
        }
        $sql .= $sort;

        if (!$itemseeds = $DB->get_records_sql($sql, array('surveyid' => $this->survey->id), $sort)) {
            $a = new stdClass();
            $url = new moodle_url('/mod/survey/items_add.php', $paramurl);
            $a->href = $url->out();

            $a->title = get_string('noitemsfoundtitle', 'survey');
            echo $OUTPUT->box(get_string('noitemsfound', 'survey', $a));
        }

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
            $tablerow = array_pad($tablerow, 9, '');

            $table->add_data($tablerow);
        } else {
            $drawmoveherebox = false;
        }

        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->itemid, $itemseed->type, $itemseed->plugin);

            $tablerow = array();

            if (($this->action == SURVEY_CHANGEORDERASK) && ($item->itemid == $this->itemid)) {
                // do not draw the item you are going to move
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
                $content .= $this->multiline_to_condition_union($item->parentcontent);
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
            if ($item->type == SURVEY_TYPEFIELD) {
                $tablerow[] = $item->customnumber;
            } else {
                if ($item->plugin == 'label') {
                    $tablerow[] = $item->labelintro;
                } else {
                    $tablerow[] = '';
                }
            }

            if ($this->action != SURVEY_CHANGEORDERASK) {
                // *************************************** actions
                // /////////////////////////////////////////////////
                // $paramurl_base definition
                $paramurl_base = array();
                $paramurl_base['id'] = $cm->id;
                $paramurl_base['itemid'] = $item->itemid;
                $paramurl_base['type'] = $item->type;
                $paramurl_base['plugin'] = $item->plugin;
                // end of $paramurl_base definition
                // /////////////////////////////////////////////////

                $icons = '';
                // *************************************** SURVEY_EDITITEM
                $paramurl = $paramurl_base + array('act' => SURVEY_EDITITEM);
<<<<<<< HEAD
                $basepath = new moodle_url('items_setup.php', $paramurl);
=======
                $basepath = new moodle_url('items_configure.php', $paramurl);
>>>>>>> 5be0a9a1b0149babfc062c50aa455db64239ab8c

                $icons .= '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
                $icons .= '<img src="'.$OUTPUT->pix_url('t/edit').'" class="iconsmall" alt="'.$edittitle.'" title="'.$edittitle.'" /></a>&nbsp;';

                // *************************************** SURVEY_CHANGEORDERASK
                if (!empty($drawmovearrow)) {
                    $paramurl = $paramurl_base + array('act' => SURVEY_CHANGEORDERASK, 'itm' => $item->sortindex);
                    if (!empty($item->parentid)) {
                        $paramurl = $paramurl + array('pit' => $item->parentid);
                    }
                    $basepath = new moodle_url('items_manage.php', $paramurl);

                    $icons .= '<a class="editing_update" title="'.$changetitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('t/move').'" class="iconsmall" alt="'.$changetitle.'" title="'.$changetitle.'" /></a>&nbsp;';
                }

                // *************************************** SURVEY_HIDEITEM/SURVEY_SHOWITEM
                if (!$this->hassubmissions) {
                    $paramurl = $paramurl_base;
                    if (!empty($item->hide)) {
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
                if (!$this->hassubmissions) {
                    $paramurl = $paramurl_base + array('act' => SURVEY_DELETEITEM, 'itm' => $item->sortindex);
                    $basepath = new moodle_url('items_manage.php', $paramurl);

                    $icons .= '<a class="editing_update" title="'.$deletetitle.'" href="'.$basepath.'">';
                    $icons .= '<img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletetitle.'" title="'.$deletetitle.'" /></a>&nbsp;';
                }

                // *************************************** SURVEY_REQUIRED ON/OFF
                if (isset($item->required)) { // it may not be set as in page_break, autofill or some more
                    $paramurl = $paramurl_base;

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
                        $basepath = new moodle_url('items_manage.php', $paramurl);
                        $icons .= '<a class="editing_update" title="'.$message.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url($icopath, 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" /></a>&nbsp;';
                    }
                }

                // *************************************** SURVEY_CHANGEINDENT
                if (isset($item->indent)) { // it may not be set as in page_break, autofill and some more
                    $paramurl = $paramurl_base + array('act' => SURVEY_CHANGEINDENT);

                    if ($item->indent > 0) {
                        $indentvalue = $item->indent - 1;
                        $paramurl['ind'] = $indentvalue;
                        $basepath = new moodle_url('items_manage.php', $paramurl);
                        $icons .= '<a class="editing_update" title="'.$indenttitle.'" href="'.$basepath.'">';
                        $icons .= '<img src="'.$OUTPUT->pix_url('t/left').'" class="iconsmall" alt="'.$indenttitle.'" title="'.$indenttitle.'" /></a>';
                    }
                    $icons .= '['.$item->indent.']';
                    if ($item->indent < 9) {
                        $indentvalue = $item->indent + 1;
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

            $addedclass = empty($item->hide) ? '' : 'dimmed';
            $table->add_data($tablerow, $addedclass);

            // print_object($item);
            if ($this->action == SURVEY_CHANGEORDERASK) {
                // It was asked to move the item with:
                // $this->itemid e $this->parentid
                if ($this->parentid) { // <-- this is the parentid of the item that I am going to move
                    // if a parentid is foreseen
                    // draw the moveherebox only if the current (already displayed) item has: $item->itemid == $this->parentid
                    // once you start to draw the moveherebox, you will never stop
                    $drawmoveherebox = $drawmoveherebox || ($item->itemid == $this->parentid);

                    // se hai appena superato un item con $item->parentid == $itemid, fermati per sempre
                    if ($item->parentid == $this->itemid) {
                        $drawmoveherebox = false;
                    }
                } else {
                    $drawmoveherebox = $drawmoveherebox && ($item->parentid != $this->itemid);
                }

                if (!empty($drawmoveherebox)) {
                    $paramurl = $paramurl_move + array('lib' => $item->sortindex);
                    $basepath = new moodle_url('items_manage.php', $paramurl);

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
    }

    /*
     * manage_item_hide
     * @param
     * @return
     */
    public function manage_item_hide() {
        global $DB, $OUTPUT, $PAGE;

        $cm = $PAGE->cm;

        // build tohidelist
        // here I must select the whole tree down
        $tohidelist = array($this->itemid);
        $sortindextohidelist = array();
        survey_add_tree_node($tohidelist, $sortindextohidelist);

        $itemstoprocess = count($tohidelist);
        if ($this->confirm == SURVEY_UNCONFIRMED) {
            if (count($tohidelist) > 1) { // ask for confirmation
                $context = context_module::instance($cm->id);
                $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $this->itemid), MUST_EXIST);

                $a = new stdClass();
                $a->parentid = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
                $a->dependencies = implode(', ', $sortindextohidelist);
                $message = get_string('askitemstohide', 'survey', $a);

                $optionbase = array('id' => $cm->id, 'act' => SURVEY_HIDEITEM);

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
                survey_reset_items_pages($cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    // hide items
                    foreach ($tohidelist as $tohideitemid) {
                        $DB->set_field('survey_item', 'hide', 1, array('id' => $tohideitemid));
                    }
                    survey_reset_items_pages($cm->instance);
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
     * @param
     * @return
     */
    public function manage_item_show() {
        global $DB, $OUTPUT, $PAGE;

        $cm = $PAGE->cm;

        // build toshowlist
        $toshowlist = array($this->itemid);
        $parentitem = $DB->get_record('survey_item', array('id' => $this->itemid), 'id, parentid, sortindex', MUST_EXIST);
        while (isset($parentitem->parentid)) {
            if ($parentitem = $DB->get_record('survey_item', array('id' => $parentitem->parentid, 'hide' => 1), 'id, parentid, sortindex')) { // potrebbe non esistere
                $toshowlist[] = $parentitem->id;
                $sortindextoshowlist[] = $parentitem->sortindex;
            }
        }

        $itemstoprocess = count($toshowlist);
        if ($this->confirm == SURVEY_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // ask for confirmation
                $context = context_module::instance($cm->id);
                $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $this->itemid), MUST_EXIST);

                $a = new stdClass();
                $a->lastitem = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
                $a->ancestors = implode(', ', $sortindextoshowlist);
                $message = get_string('askitemsshow', 'survey', $a);

                $optionbase = array('id' => $cm->id, 'act' => SURVEY_SHOWITEM, 'itemid' => $this->itemid);

                $optionsyes = $optionbase + array('cnf' => SURVEY_CONFIRMED_YES, 'type' => $this->type);
                $urlyes = new moodle_url('items_manage.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemsshow', 'survey'));

                $optionsno = $optionbase + array('cnf' => SURVEY_CONFIRMED_NO);
                $urlno = new moodle_url('items_manage.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die;
            } else { // show without asking
                $DB->set_field('survey_item', 'hide', 0, array('id' => $this->itemid));
                survey_reset_items_pages($cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEY_CONFIRMED_YES:
                    // hide items
                    foreach ($toshowlist as $toshowitemid) {
                        $DB->set_field('survey_item', 'hide', 0, array('id' => $toshowitemid));
                    }
                    survey_reset_items_pages($cm->instance);
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
     * @param
     * @return
     */
    public function manage_item_deletion() {
        global $CFG, $DB, $OUTPUT, $PAGE;

        $cm = $PAGE->cm;

        if ($this->confirm == SURVEY_UNCONFIRMED) {
            // ask for confirmation
            // in the frame of the confirmation I need to declare whether some child will break the link
            $context = context_module::instance($cm->id);

            $itemcontent = $DB->get_field('survey_item', 'content', array('id' => $this->itemid), MUST_EXIST);
            $a = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $this->itemid);
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

            $optionbase = array('id' => $cm->id, 'act' => SURVEY_DELETEITEM);

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
                    $maxsortindex = $DB->get_field('survey_item', 'MAX(sortindex)', array('surveyid' => $cm->instance));
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
                    $context = context_module::instance($cm->id);

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
     * @param
     * @return
     */
    public function validate_relations() {
        global $CFG, $PAGE, $DB, $OUTPUT;

        require_once($CFG->libdir.'/tablelib.php');

        $cm = $PAGE->cm;

        $context = context_module::instance($cm->id);

        $table = new flexible_table('itemslist');

        $paramurl = array('id' => $cm->id);
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
        if (!$itemseeds->valid()) {
            $a = new stdClass();
            $url = new moodle_url('/mod/survey/items_add.php', $paramurl);
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
            $plugintitle = get_string('pluginname', 'survey'.$item->type.'_'.$item->plugin);
            $content = '<img src="'.$OUTPUT->pix_url('icon', 'survey'.$item->type.'_'.$item->plugin).'" class="icon" alt="'.$plugintitle.'" title="'.$plugintitle.'" />';
            $tablerow[] = $content;

            // *************************************** content
            $itemcontent = $item->content;
            $item->contentformat = FORMAT_HTML;
            $item->contenttrust = 1;

            $output = file_rewrite_pluginfile_urls($itemcontent, 'pluginfile.php', $context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $item->itemid);
            $tablerow[] = $output;

            // *************************************** sortindex
            $tablerow[] = $item->sortindex;

            // *************************************** parentid
            if ($item->parentid) {
                $message = get_string('parentid', 'survey');
                $content = $parentitem->sortindex;
                $content .= '&nbsp;<img src="'.$OUTPUT->pix_url('link', 'survey').'" class="iconsmall" alt="'.$message.'" title="'.$message.'" />&nbsp;';
                $content .= $this->multiline_to_condition_union($item->parentcontent);
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
                        if (empty($item->hide)) {
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
            $paramurl_base['itemid'] = $item->itemid;
            $paramurl_base['type'] = $item->type;
            $paramurl_base['plugin'] = $item->plugin;
            // end of $paramurl_base definition
            // /////////////////////////////////////////////////

            // *************************************** SURVEY_EDITITEM
            $paramurl = $paramurl_base + array('act' => SURVEY_EDITITEM);
<<<<<<< HEAD
            $basepath = new moodle_url('items_setup.php', $paramurl);
=======
            $basepath = new moodle_url('items_configure.php', $paramurl);
>>>>>>> 5be0a9a1b0149babfc062c50aa455db64239ab8c

            $icons = '<a class="editing_update" title="'.$edittitle.'" href="'.$basepath.'">';
            $icons .= '<img src="'.$OUTPUT->pix_url('t/edit').'" class="iconsmall" alt="'.$edittitle.'" title="'.$edittitle.'" /></a>&nbsp;';

            $tablerow[] = $icons;

            $addedclass = empty($item->hide) ? '' : 'dimmed';
            $table->add_data($tablerow, $addedclass);
        }
        $itemseeds->close();

        $table->set_attribute('align', 'center');
        $table->summary = get_string('itemlist', 'survey');
        $table->print_html();
    }

    /*
     * survey_wlib_write_plugin_values
     * @param &$libcontent, $values, $tablename, $currentplugin
     * @return
     */
    public function multiline_to_condition_union($parentcontent) {
        $constarains = str_replace("\r", '', $parentcontent);
        $constarains = explode("\n", $constarains);

        return implode(' & ', $constarains);
    }

    /*
     * survey_wlib_write_plugin_values
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
                        $message .= '<br />'.get_string('itemedithidefrombasicform', 'survey');
                    }
                    break;
            }
        }

        echo $OUTPUT->box($message, 'notice centerpara');
    }
}