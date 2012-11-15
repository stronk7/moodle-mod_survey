<?php

defined('MOODLE_INTERNAL') || die();

echo $OUTPUT->notification(get_string('currenttopreset', 'survey'), 'generaltable generalbox boxaligncenter boxwidthwide');

$record = new stdClass();
$record->surveyid = $survey->id;

$mform->set_data($record);
$mform->display();
