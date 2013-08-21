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
 * This file keeps track of upgrades to the survey module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
 * xmldb_survey_upgrade
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_survey_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();


    if ($oldversion < 2013060903) {
        // Rename field hidehardinfo on table survey_item to hideinstructions.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('hidehardinfo', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'required');

        // Launch rename field hidehardinfo.
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'hideinstructions');
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013060903, 'survey');
    }

    if ($oldversion < 2013062701) {
        // Define field insearchform to be added to survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('insearchform', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'hide');

        // Conditionally launch add field insearchform.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $DB->set_field('survey_item', 'insearchform', 0, array('basicform' => 1));
        $DB->set_field('survey_item', 'insearchform', 1, array('basicform' => 2));


        // Define field limitedaccess to be added to survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('limitedaccess', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'insearchform');

        // Conditionally launch add field limitedaccess.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $DB->set_field('survey_item', 'limitedaccess', 0);

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013062701, 'survey');
    }

    if ($oldversion < 2013062702) {
        // Define field basicform to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('basicform');

        // Conditionally launch drop field basicform.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field advancedsearch to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('advancedsearch');

        // Conditionally launch drop field advancedsearch.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Rename field basicformpage on table survey_item to formpage.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('basicformpage', XMLDB_TYPE_INTEGER, '7', null, XMLDB_NOTNULL, null, '0', 'sortindex');

        // Launch rename field basicformpage.
        $dbman->rename_field($table, $field, 'formpage');


        // Define field advancedformpage to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('advancedformpage');

        // Conditionally launch drop field advancedsearch.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013062702, 'survey');
    }

    if ($oldversion < 2013071101) {

        // Rename field limitedaccess on table survey_item to advanced.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('limitedaccess', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'insearchform');

        // Launch rename field limitedaccess.
        $dbman->rename_field($table, $field, 'advanced');

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013071101, 'survey');
    }

    if ($oldversion < 2013071901) {

        // Rename field externalname on table survey_item to template.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('externalname', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'plugin');

        // Launch rename field externalname.
        $dbman->rename_field($table, $field, 'template');

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013071901, 'survey');
    }

    if ($oldversion < 2013072901) {
        require_once($CFG->dirroot.'/mod/survey/locallib.php');

        $where = array('surveyid' => 0);
        $itemseeds = $DB->get_recordset('survey_item', $where, 'id', 'id, type, plugin');

        foreach ($itemseeds as $itemseed) {
            $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

            $recordtokill = $DB->get_record('survey_item', array('id' => $itemseed->id));
            if (!$DB->delete_records('survey_item', array('id' => $itemseed->id))) {
                print_error('Unable to delete survey_item id='.$itemseed->id);
            }

            if (!$DB->delete_records('survey_'.$itemseed->plugin, array('id' => $item->get_pluginid()))) {
                print_error('Unable to delete record id = '.$item->get_pluginid().' from surveyitem_'.$itemseed->plugin);
            }
        }
        $itemseeds->close();

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013072901, 'survey');
    }

    if ($oldversion < 2013072902) {

        // Define field template to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('template');

        // Conditionally launch drop field template.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field template to be added to survey.
        $table = new xmldb_table('survey');
        $field = new xmldb_field('template', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'thankshtmlformat');

        // Conditionally launch add field template.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field parentcontent_sid to be added to survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('parentcontent_sid', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'parentid');

        // Conditionally launch add field parentcontent_sid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013072902, 'survey');
    }

    if ($oldversion < 2013073001) {

        // Define field content_sid to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('content_sid');

        // Conditionally launch drop field content_sid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field parentcontent_sid to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('parentcontent_sid');

        // Conditionally launch drop field parentcontent_sid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013073001, 'survey');
    }

    if ($oldversion < 2013082001) {
        require_once($CFG->dirroot.'/mod/survey/locallib.php');

        $allitems = $DB->get_recordset('survey_item');
        foreach ($allitems as $currentitem) {
            $item = survey_get_item($currentitem->id, $currentitem->type, $currentitem->plugin);
            if ( ($currentitem->plugin == 'pagebreak') || ($currentitem->plugin == 'fieldsetend') ) {
                $record = new stdClass();
                $record->surveyid = $currentitem->surveyid;
                $record->itemid = $currentitem->id;
                if ($currentitem->plugin == 'pagebreak') {
                    $record->content = '<hr />';
                }
                if ($currentitem->plugin == 'fieldsetend') {
                    $record->content = '<div style="text-align:right;">__|</div>';
                }
                $DB->insert_record('survey_'.$currentitem->plugin, $record);
            } else {
                $record = $DB->get_record('survey_'.$currentitem->plugin, array('itemid' => $currentitem->id));
                $record->content = $currentitem->content;
                $record->contentformat = $currentitem->contentformat;
                if ($item->get_customnumber() !== false) {
                    $record->customnumber = $currentitem->customnumber;
                }
                if ($item->get_extrarow() !== false) {
                    $record->extrarow = $currentitem->extrarow;
                }
                if ($item->get_extranote() !== false) {
                    $record->extranote = $currentitem->extranote;
                }
                if ($item->get_required() !== false) {
                    $record->required = $currentitem->required;
                }
                if ($item->get_hideinstructions() !== false) {
                    $record->hideinstructions = $currentitem->hideinstructions;
                }
                if ($item->get_variable() !== false) {
                    $record->variable = $currentitem->variable;
                }
                if ($item->get_indent() !== false) {
                    $record->indent = $currentitem->indent;
                }
                $DB->update_record('survey_'.$currentitem->plugin, $record);
            }
        }
        $allitems->close();

        // Define field content to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('content');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field contentformat to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('contentformat');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field customnumber to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('customnumber');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field extrarow to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('extrarow');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field extranote to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('extranote');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field required to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('required');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field hideinstructions to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('hideinstructions');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field variable to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('variable');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field indent to be dropped from survey_item.
        $table = new xmldb_table('survey_item');
        $field = new xmldb_field('indent');

        // Conditionally launch drop field plugin.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Survey savepoint reached.
        upgrade_mod_savepoint(true, 2013082001, 'survey');
    }

    return true;
}
