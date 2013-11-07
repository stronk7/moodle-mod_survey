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
 * This file adds the settings pages to the navigation menu
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/mod/survey/adminlib.php');

$ADMIN->add('modsettings', new admin_category('modsurveyfolder', new lang_string('pluginname', 'mod_survey'), !$module->is_enabled()));

$settings = new admin_settingpage($section, get_string('settings', 'mod_survey'), 'moodle/site:config', !$module->is_enabled());

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
}

$ADMIN->add('modsurveyfolder', $settings);

// Tell core we already added the settings structure.
$settings = null;

// folder 'survey field'
$ADMIN->add('modsurveyfolder', new admin_category('surveyfieldplugins',
                new lang_string('fieldplugins', 'survey'), !$module->is_enabled()));
$ADMIN->add('surveyfieldplugins', new survey_admin_page_manage_survey_plugins('surveyfield'));

// folder 'survey format'
$ADMIN->add('modsurveyfolder', new admin_category('surveyformatplugins',
                new lang_string('formatplugins', 'survey'), !$module->is_enabled()));
$ADMIN->add('surveyformatplugins', new survey_admin_page_manage_survey_plugins('surveyformat'));

// folder 'survey (master) templates'
$ADMIN->add('modsurveyfolder', new admin_category('surveytemplateplugins',
                new lang_string('mastertemplateplugins', 'survey'), !$module->is_enabled()));
$ADMIN->add('surveytemplateplugins', new survey_admin_page_manage_survey_plugins('surveytemplate'));

// folder 'survey reports'
$ADMIN->add('modsurveyfolder', new admin_category('surveyreportplugins',
                new lang_string('reportplugins', 'survey'), !$module->is_enabled()));
$ADMIN->add('surveyreportplugins', new survey_admin_page_manage_survey_plugins('surveyreport'));

foreach (core_plugin_manager::instance()->get_plugins_of_type('surveyfield') as $plugin) {
    /** @var \mod_assign\plugininfo\assignsubmission $plugin */
    $plugin->load_settings($ADMIN, 'surveyfieldplugins', $hassiteconfig);
}

foreach (core_plugin_manager::instance()->get_plugins_of_type('surveyformat') as $plugin) {
    /** @var \mod_assign\plugininfo\assignsubmission $plugin */
    $plugin->load_settings($ADMIN, 'surveyformatplugins', $hassiteconfig);
}

foreach (core_plugin_manager::instance()->get_plugins_of_type('surveytemplate') as $plugin) {
    /** @var \mod_assign\plugininfo\assignsubmission $plugin */
    $plugin->load_settings($ADMIN, 'surveytemplateplugins', $hassiteconfig);
}

foreach (core_plugin_manager::instance()->get_plugins_of_type('surveyreport') as $plugin) {
    /** @var \mod_assign\plugininfo\assignsubmission $plugin */
    $plugin->load_settings($ADMIN, 'surveyreportplugins', $hassiteconfig);
}

