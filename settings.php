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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('survey_maxinputdelay', get_string('maxinputdelay', 'survey'),
                       get_string('maxinputdelay_descr', 'survey'), 168, PARAM_INT)); // alias: 7*24 hours == 1 week

    $settings->add(new admin_setting_configcheckbox('survey_softinfoinsearch', get_string('softinfoinsearch', 'survey'),
                       get_string('softinfoinsearch_descr', 'survey'), 0));

    $settings->add(new admin_setting_configcheckbox('survey_hardinfoinsearch', get_string('hardinfoinsearch', 'survey'),
                       get_string('hardinfoinsearch_descr', 'survey'), 1));

    $settings->add(new admin_setting_configcheckbox('survey_useadvancedpermissions', get_string('useadvancedpermissions', 'survey'),
                       get_string('useadvancedpermissions_descr', 'survey'), 0));

    // include  settings of field subplugins
    $fields = get_plugin_list('surveyfield');
    foreach ($fields as $field => $path) {
        $settingsfile = $path . '/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$field,
                    get_string('fieldplugin', 'survey') . ' - ' . get_string('pluginname', 'surveyfield_' . $field), ''));
            include($settingsfile);
        }
    }

    // include settings of format subplugins
    $surveys = get_plugin_list('surveyformat');
    foreach ($surveys as $survey => $path) {
        $settingsfile = $path . '/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$survey,
                    get_string('formatplugin', 'survey') . ' - ' . get_string('pluginname', 'surveyformat_' . $survey), ''));
            include($settingsfile);
        }
    }

    // include settings of template subplugins
    $surveys = get_plugin_list('surveytemplate');
    foreach ($surveys as $survey => $path) {
        $settingsfile = $path . '/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$survey,
                    get_string('templateplugin', 'survey') . ' - ' . get_string('pluginname', 'surveytemplate_' . $survey), ''));
            include($settingsfile);
        }
    }

    // include settings of report subplugins
    $surveys = get_plugin_list('surveyreport');
    foreach ($surveys as $survey => $path) {
        $settingsfile = $path . '/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$survey,
                    get_string('reportplugin', 'survey') . ' - ' . get_string('pluginname', 'surveyreport_' . $survey), ''));
            include($settingsfile);
        }
    }
}
