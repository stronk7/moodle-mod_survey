<?php

require_once('../../../../config.php');
require_once($CFG->libdir.'/graphlib.php');
require_once($CFG->dirroot.'/mod/survey/locallib.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$itemid = required_param('itemid', PARAM_INT); // Item ID
$submissionscount = required_param('submissionscount', PARAM_INT); // Submissions count
$group = optional_param('group', 0, PARAM_INT); // Group ID

require_once($CFG->dirroot.'/mod/survey/report/frequency/lib.php');

$url = new moodle_url('/mod/survey/report/frequency/graph.php', array('id' => $id, 'itemid' => $itemid));
if ($group !== 0) {
    $url->param('group', $group);
}
$PAGE->set_url($url);

if (!$cm = get_coursemodule_from_id('survey', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

require_login($course, false, $cm);

$groupmode = groups_get_activity_groupmode($cm);   // Groups are being used
$context = context_module::instance($cm->id);

if (!has_capability('mod/survey:accessreports', $context)) {
    print_error('nopermissiontoshow');
}

if (!$survey = $DB->get_record('survey', array('id' => $cm->instance))) {
    print_error('invalidsurveyid', 'survey');
}

$item = survey_get_item($itemid);

$sql = 'SELECT content, count(id) as absolute
        FROM {survey_userdata}
        WHERE itemid = :itemid
        GROUP BY content';

$params['itemid'] = $itemid;

$answers = $DB->get_recordset_sql($sql, $params, 'ud.content');

$counted = 0;
$content = array();
$absolute = array();
foreach ($answers as $answer) {
    $content[] = $item->userform_db_to_export($answer);
    $absolute[] = $answer->absolute;
    $counted += $answer->absolute;
}
if ($counted < $submissionscount) {
    $content[] = get_string('answernotpresent', 'surveyreport_frequency');
    $absolute[] = ($submissionscount - $counted);
}

$answers->close();

if (true) {
    $graph = new graph(SURVEY_GWIDTH, SURVEY_GHEIGHT);
    $graph->parameter['title'] = '';

    $graph->x_data = $content;
    $graph->y_data['answers1'] = $absolute;
    $graph->y_format['answers1'] = array('colour' => 'ltblue', 'bar' => 'fill', 'legend' => strip_tags($item->get_content()), 'bar_size' => 0.4);


    $graph->parameter['legend'] = 'outside-left';
    $graph->parameter['inner_padding'] = 20;
    $graph->parameter['legend_size'] = 9;
    $graph->parameter['legend_border'] = 'black';
    $graph->parameter['legend_offset'] = 4;

    $graph->y_order = array('answers1');

    // $graph->parameter['x_axis_gridlines'] can not be set to a number because X axis is not numeric
    $graph->parameter['y_axis_gridlines'] = 2 + max($absolute);
    $graph->parameter['y_resolution_left'] = 1;
    $graph->parameter['y_decimal_left'] = 0;
    $graph->parameter['y_max_left'] = 1 + max($absolute);
    $graph->parameter['y_max_right'] = 1 + max($absolute);
    $graph->parameter['x_axis_angle'] = 0;
    $graph->parameter['shadow'] = 'none';

    //$graph->y_tick_labels = $absolute;
    $graph->y_tick_labels = null;
    $graph->offset_relation = null;

    $graph->draw_stack();
} else {
    $graph = new graph(SURVEY_GWIDTH, SURVEY_GHEIGHT);
    $graph->parameter['title']   = 'Raperonzolo';

    $graph->x_data               = array('io','tu','egli','noi','voi','essi');

    $graph->y_data['answers1']   = array(1,6,2,5,3,4);
    $graph->y_format['answers1'] = array('colour' => 'ltblue', 'bar' => 'fill', 'legend' => 'legenda2', 'bar_size' => 0.4);

    $graph->y_data['answers2']   = array(1,2,3,4,5,6);
    $graph->y_format['answers2'] = array('colour' => 'ltorange', 'line' => 'line', 'point' => 'square',
                                            'shadow_offset' => 4, 'legend' => 'legenda1');

    $graph->parameter['legend']        = 'outside-top';
    $graph->parameter['legend_border'] = 'black';
    $graph->parameter['legend_offset'] = 4;

    $graph->y_order = array('answers2', 'answers1');

    $graph->parameter['y_axis_gridlines']  = 5;
    $graph->parameter['y_resolution_left'] = 1;
    $graph->parameter['y_decimal_left']    = 0;
    $graph->parameter['x_axis_angle']      = 0;
    $graph->parameter['shadow']            = 'none';

    $graph->y_tick_labels = null;
    $graph->offset_relation = null;

    $graph->draw_stack();
}

exit;
