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

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/mod/survey/adminlib.php');

// folder 'survey plugins'
$ADMIN->add('modules', new admin_category('surveyplugins',
                new lang_string('surveyplugins', 'survey'), $module->is_enabled() === false));

// folder 'survey field'
$ADMIN->add('surveyplugins', new admin_category('surveyfieldplugins',
                new lang_string('fieldplugins', 'survey'), $module->is_enabled() === false));
$ADMIN->add('surveyfieldplugins', new survey_admin_page_manage_survey_plugins('surveyfield'));

// folder 'survey format'
$ADMIN->add('surveyplugins', new admin_category('surveyformatplugins',
                new lang_string('formatplugins', 'survey'), $module->is_enabled() === false));
$ADMIN->add('surveyformatplugins', new survey_admin_page_manage_survey_plugins('surveyformat'));

// folder 'survey (master) templates'
$ADMIN->add('surveyplugins', new admin_category('surveytemplateplugins',
                new lang_string('mastertemplateplugins', 'survey'), $module->is_enabled() === false));
$ADMIN->add('surveytemplateplugins', new survey_admin_page_manage_survey_plugins('surveytemplate'));

// folder 'survey reports'
$ADMIN->add('surveyplugins', new admin_category('surveyreportplugins',
                new lang_string('reportplugins', 'survey'), $module->is_enabled() === false));
$ADMIN->add('surveyreportplugins', new survey_admin_page_manage_survey_plugins('surveyreport'));

survey_plugin_manager::add_admin_survey_plugin_settings('surveyfield', $ADMIN, $settings, $module);
survey_plugin_manager::add_admin_survey_plugin_settings('surveyformat', $ADMIN, $settings, $module);
survey_plugin_manager::add_admin_survey_plugin_settings('surveytemplate', $ADMIN, $settings, $module);
survey_plugin_manager::add_admin_survey_plugin_settings('surveyreport', $ADMIN, $settings, $module);

if ($ADMIN->fulltree) {
    $name = new lang_string('maxinputdelay', 'mod_survey');
    $description = new lang_string('maxinputdelay_descr', 'mod_survey');
    $settings->add(new admin_setting_configtext('survey/maxinputdelay', $name, $description, 168, PARAM_INT)); // alias: 7*24 hours == 1 week

    $name = new lang_string('extranoteinsearch', 'mod_survey');
    $description = new lang_string('extranoteinsearch_descr', 'mod_survey');
    $settings->add(new admin_setting_configcheckbox('survey/extranoteinsearch', $name, $description, 0));

    $name = new lang_string('fillinginstructioninsearch', 'mod_survey');
    $description = new lang_string('fillinginstructioninsearch_descr', 'mod_survey');
    $settings->add(new admin_setting_configcheckbox('survey/fillinginstructioninsearch', $name, $description, 0));

    $name = new lang_string('useadvancedpermissions', 'mod_survey');
    $description = new lang_string('useadvancedpermissions_descr', 'mod_survey');
    $settings->add(new admin_setting_configcheckbox('survey/useadvancedpermissions', $name, $description, 0));

    $name = new lang_string('allowalwaysediting', 'mod_survey');
    $description = new lang_string('allowalwaysediting_descr', 'mod_survey');
    $settings->add(new admin_setting_configcheckbox('survey/allowalwaysediting', $name, $description, 0));

    // include  settings of field subplugins
    $surveyplugin = get_plugin_list('surveyfield');
    foreach ($surveyplugin as $field => $path) {
        $settingsfile = $path.'/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$field,
                    get_string('fieldplugin', 'survey').' - '.get_string('pluginname', 'surveyfield_'.$field), ''));
            include($settingsfile);
        }
    }

    // include settings of format subplugins
    $surveyplugin = get_plugin_list('surveyformat');
    foreach ($surveyplugin as $format => $path) {
        $settingsfile = $path.'/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$format,
                    get_string('formatplugin', 'survey').' - '.get_string('pluginname', 'surveyformat_'.$format), ''));
            include($settingsfile);
        }
    }

    // include settings of template subplugins
    $surveyplugin = get_plugin_list('surveytemplate');
    foreach ($surveyplugin as $mastertemplate => $path) {
        $settingsfile = $path.'/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$mastertemplate,
                    get_string('templateplugin', 'survey').' - '.get_string('pluginname', 'surveytemplate_'.$mastertemplate), ''));
            include($settingsfile);
        }
    }

    // allow to deny instantiation of old master templates
    // $description = new lang_string('denyinstantiation_descr', 'mod_survey');
    // $settings->add(new admin_setting_heading('surveytemplates',
    //                                         get_string('enabletemplateplugin', 'survey'), ''));
    // $surveyplugin = get_plugin_list('surveytemplate');
    // foreach ($surveyplugin as $mastertemplate => $path) {
    //     $a = new lang_string('pluginname', 'surveytemplate_'.$mastertemplate);
    //     $name = new lang_string('enabletemplate', 'survey', $a);
    //
    //     $settings->add(new admin_setting_configcheckbox('surveytemplate/'.$mastertemplate.'_enable', $name, $description, '1'));
    // }

    // include settings of report subplugins
    $surveyplugin = get_plugin_list('surveyreport');
    foreach ($surveyplugin as $report => $path) {
        $settingsfile = $path.'/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$report,
                    get_string('reportplugin', 'survey').' - '.get_string('pluginname', 'surveyreport_'.$report), ''));
            include($settingsfile);
        }
    }
}
