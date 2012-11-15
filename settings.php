<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('survey_maxinputdelay', get_string('maxinputdelay', 'survey'),
                       get_string('maxinputdelay_descr', 'survey'), 168, PARAM_INT)); // alias: 7*24 hours == 1 week

    $settings->add(new admin_setting_configcheckbox('survey_softinfoinsearch', get_string('softinfoinsearch', 'survey'),
                       get_string('softinfoinsearch_descr', 'survey'), 0));

    $settings->add(new admin_setting_configcheckbox('survey_hardinfoinsearch', get_string('hardinfoinsearch', 'survey'),
                       get_string('hardinfoinsearch_descr', 'survey'), 1));

    // include the settings of field subplugins
    $fields = get_plugin_list('surveyfield');
    foreach ($fields as $field => $path) {
        $settingsfile = $path . '/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$field,
                    get_string('fieldplugin', 'survey') . ' - ' . get_string('pluginname', 'surveyfield_' . $field), ''));
            include($settingsfile);
        }
    }

    // include the settings of format subplugins
    $surveys = get_plugin_list('surveyformat');
    foreach ($surveys as $survey => $path) {
        $settingsfile = $path . '/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$survey,
                    get_string('formatplugin', 'survey') . ' - ' . get_string('pluginname', 'surveyformat_' . $survey), ''));
            include($settingsfile);
        }
    }

    // include the settings of survey subplugins
    $surveys = get_plugin_list('surveytemplate');
    foreach ($surveys as $survey => $path) {
        $settingsfile = $path . '/settings.php';
        if (file_exists($settingsfile)) {
            $settings->add(new admin_setting_heading('surveytemplate_'.$survey,
                    get_string('surveyplugin', 'survey') . ' - ' . get_string('pluginname', 'surveytemplate_' . $survey), ''));
            include($settingsfile);
        }
    }
}
