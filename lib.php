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
 * Library of interface functions and constants for module survey
 *
 * All the core Moodle functions, needed to allow the module to work
 * integrated in Moodle should be placed here.
 * All the survey specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Some constants
define('SURVEY_MAX_ENTRIES'        , 50);
define('SURVEY_MINEVERYEAR'        , 1970);
define('SURVEY_MAXEVERYEAR'        , 2020);
define('SURVEY_VALUELABELSEPARATOR', '::');
define('SURVEY_OTHERSEPARATOR'     , '->');

// to change tabs order, just exchange numbers if the following lines
define('SURVEY_TABSUBMISSIONS', 1);
define('SURVEY_TABITEMS'      , 2);
define('SURVEY_TABUTEMPLATES' , 3);
define('SURVEY_TABMTEMPLATES' , 4);

// TAB NAMES
define('SURVEY_TAB'.SURVEY_TABSUBMISSIONS.'NAME', get_string('tabsubmissionsname', 'survey'));
define('SURVEY_TAB'.SURVEY_TABITEMS.'NAME', get_string('tabitemname', 'survey'));
define('SURVEY_TAB'.SURVEY_TABUTEMPLATES.'NAME', get_string('tabutemplatename', 'survey'));
define('SURVEY_TAB'.SURVEY_TABMTEMPLATES.'NAME', get_string('tabmtemplatename', 'survey'));

// PAGES
    // SUBMISSIONS PAGES
    // do not start numbering with 1 as it conflicts with define('SURVEY_ITEMS_PREVIEW' , 1);
    define('SURVEY_SUBMISSION_ATTEMPT' , 2);
    define('SURVEY_SUBMISSION_MANAGE'  , 3);
    define('SURVEY_SUBMISSION_EDIT'    , 4);
    define('SURVEY_SUBMISSION_READONLY', 5);
    define('SURVEY_SUBMISSION_SEARCH'  , 6);
    define('SURVEY_SUBMISSION_REPORT'  , 7);
    define('SURVEY_SUBMISSION_EXPORT'  , 8);

    // ITEMS PAGES
    define('SURVEY_ITEMS_PREVIEW' , 1);
    define('SURVEY_ITEMS_MANAGE'  , 2);
    define('SURVEY_ITEMS_SETUP'   , 3);
    define('SURVEY_ITEMS_VALIDATE', 4);

    // USER TEMPLATES PAGES
    define('SURVEY_UTEMPLATES_MANAGE', 1);
    define('SURVEY_UTEMPLATES_BUILD' , 2);
    define('SURVEY_UTEMPLATES_IMPORT', 3);
    define('SURVEY_UTEMPLATES_APPLY' , 4);

    // MASTER TEMPLATES PAGES
    define('SURVEY_MTEMPLATES_BUILD', 1);
    define('SURVEY_MTEMPLATES_APPLY', 2);

// ITEM TYPES
define('SURVEY_TYPEFIELD' , 'field');
define('SURVEY_TYPEFORMAT', 'format');

// ACTIONS
    define('SURVEY_NOACTION'          , '0');

    // ITEM MANAGEMENT section
    define('SURVEY_CHANGEORDER'       , '1');
    define('SURVEY_HIDEITEM'          , '2');
    define('SURVEY_SHOWITEM'          , '3');
    define('SURVEY_DELETEITEM'        , '4');
    define('SURVEY_DROPMULTILANG'     , '5');
    define('SURVEY_REQUIREDOFF'       , '6');
    define('SURVEY_REQUIREDON'        , '7');
    define('SURVEY_CHANGEINDENT'      , '8');
    define('SURVEY_ADDTOSEARCH'       , '9');
    define('SURVEY_OUTOFSEARCH'       , '10');
    define('SURVEY_MAKEFORALL'        , '11');
    define('SURVEY_MAKELIMITED'       , '12');

    // RESPONSES section
    define('SURVEY_DELETERESPONSE'    , '13');
    define('SURVEY_DELETEALLRESPONSES', '14');

    // UTEMPLATE section
    define('SURVEY_DELETEUTEMPLATE'   , '16');
    define('SURVEY_EXPORTUTEMPLATE'   , '17');

// VIEW
    // ITEM MANAGEMENT section
    define('SURVEY_NOVIEW'          , '0');
    define('SURVEY_SERVESURVEY'     , '1');
    define('SURVEY_PREVIEWSURVEY'   , '2');
    define('SURVEY_EDITRESPONSE'    , '3');
    define('SURVEY_READONLYRESPONSE', '4');
    define('SURVEY_EDITITEM'        , '5');
    define('SURVEY_CHANGEORDERASK'  , '6');

    // RESPONSES section
    define('SURVEY_RESPONSETOPDF'   , '6');

// OVERFLOW
define('SURVEY_LEFT_OVERFLOW' , -10);
define('SURVEY_RIGHT_OVERFLOW', -20);

// SAVESTATUS
define('SURVEY_NOFEEDBACK', 0);

// ITEMPREFIX
define('SURVEY_ITEMPREFIX', 'survey');
define('SURVEY_PLACEHOLDERPREFIX', 'placeholder');

// INVITATION AND NO ANSWER VALUE
define('SURVEY_INVITATIONVALUE', '__invItat10n__'); // user should never guess it
define('SURVEY_NOANSWERVALUE', '__n0__Answer__');   // user should never guess it
define('SURVEY_IGNOREME', '__1gn0rE__me__');        // user should never guess it

// ADJUSTMENTS
define('SURVEY_VERTICAL',   0);
define('SURVEY_HORIZONTAL', 1);

// SURVEY STATUS
define('SURVEY_STATUSINPROGRESS', 1);
define('SURVEY_STATUSCLOSED'    , 0);
define('SURVEY_STATUSALL'       , 2);

// DOWNLOAD
define('SURVEY_DOWNLOADCSV', 1);
define('SURVEY_DOWNLOADTSV', 2);
define('SURVEY_DOWNLOADXLS', 3);
define('SURVEY_NOFIELDSSELECTED', 1);
define('SURVEY_NORECORDSFOUND'  , 2);

define('SURVEY_DBMULTIVALUESEPARATOR', ';');
define('SURVEY_OUTPUTMULTIVALUESEPARATOR', ' - ');

// CONFIRMATION
define('SURVEY_UNCONFIRMED',   0);
define('SURVEY_CONFIRMED_YES', 1);
define('SURVEY_CONFIRMED_NO' , 2);

// values for defaultvalue_option
define('SURVEY_CUSTOMDEFAULT'    , 1);
define('SURVEY_INVITATIONDEFAULT', 2);
define('SURVEY_NOANSWERDEFAULT'  , 3);
define('SURVEY_LIKELASTDEFAULT'  , 4);
define('SURVEY_TIMENOWDEFAULT'   , 5);

define('SURVEY_INVITATIONDBVALUE', -1);

// mandatory field
define('SURVEY_REQUIREDITEM', 1);
define('SURVEY_OPTIONALITEM', 0);

// fileareas
define('SURVEY_STYLEFILEAREA'      , 'userstyle');
define('SURVEY_TEMPLATEFILEAREA'   , 'templatefilearea');
define('SURVEY_ITEMCONTENTFILEAREA', 'itemcontent');
define('SURVEY_THANKSHTMLFILEAREA' , 'thankshtml');

// otheritems
define('SURVEY_IGNOREITEMS'       , '1');
define('SURVEY_HIDEITEMS'         , '2');
define('SURVEY_DELETEALLITEMS'    , '3');
define('SURVEY_DELETEVISIBLEITEMS', '4');
define('SURVEY_DELETEHIDDENITEMS' , '5');

// friendly format
define('SURVEY_FIRENDLYFORMAT', -1);

// position of the content
define('SURVEY_POSITIONLEFT', 0);
define('SURVEY_POSITIONTOP', 1);
define('SURVEY_POSITIONFULLWIDTH', 2);

// friendly format
define('SURVEY_USERTEMPLATE', 0);
define('SURVEY_MASTERTEMPLATE', 1);

// relation condition format
define('SURVEY_CONDITIONOK', 0);
define('SURVEY_CONDITIONNEVERMATCH', 1);
define('SURVEY_CONDITIONMALFORMED', 2);

// -----------------------------
// Moodle core API
// -----------------------------

/*
 * Saves a new instance of the survey into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $survey An object from the form in mod_form.php
 * @param mod_survey_mod_form $mform
 * @return int The id of the newly inserted survey record
 */
function survey_add_instance($survey) {
    global $CFG, $DB, $COURSE;

    $survey->timecreated = time();

    // You may have to add extra stuff in here
    $checkboxes = array('newpageforchild', 'history', 'saveresume', 'anonymous', 'notifyteachers');
    foreach ($checkboxes as $checkbox) {
        if (!isset($survey->{$checkbox})) {
            $survey->{$checkbox} = 0;
        }
    }

    $survey->id = $DB->insert_record('survey', $survey);

    // manage userstyle filemanager
    // we need to use context now, so we need to make sure all needed info is already in db
    $cmid = $survey->coursemodule;
    $DB->set_field('course_modules', 'instance', $survey->id, array('id' => $cmid));
    $context = context_module::instance($cmid);
    survey_save_user_style($survey, $context);

    // manage thankshtml editor
    // if (!isset($survey->coursemodule)) {
        // $cm = get_coursemodule_from_id('survey', $survey->id, 0, false, MUST_EXIST);
        // $survey->coursemodule = $cm->id;
    // }
    $editoroptions = survey_get_editor_options();
    if ($draftitemid = $survey->thankshtml_editor['itemid']) {
        $survey->thankshtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_survey', SURVEY_THANKSHTMLFILEAREA,
                $survey->id, $editoroptions, $survey->thankshtml_editor['text']);
        $survey->thankshtmlformat = $survey->thankshtml_editor['format'];
    }
    $DB->update_record('survey', $survey);

    return $survey->id;
}

/*
 * Updates an instance of the survey in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $survey An object from the form in mod_form.php
 * @param mod_survey_mod_form $mform
 * @return boolean Success/Fail
 */
function survey_update_instance($survey) {
    global $CFG, $DB, $COURSE;

    $survey->timemodified = time();
    $survey->id = $survey->instance;

    $checkboxes = array('newpageforchild', 'history', 'saveresume', 'anonymous', 'notifyteachers');
    foreach ($checkboxes as $checkbox) {
        if (!isset($survey->{$checkbox})) {
            $survey->{$checkbox} = 0;
        }
    }

    survey_reset_items_pages($survey->id);

    $context = context_module::instance($survey->coursemodule);

    // manage userstyle filemanager
    survey_save_user_style($survey, $context);

    // manage thankshtml editor
    $editoroptions = survey_get_editor_options();
    if ($draftitemid = $survey->thankshtml_editor['itemid']) {
        $survey->thankshtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_survey', SURVEY_THANKSHTMLFILEAREA,
                $survey->id, $editoroptions, $survey->thankshtml_editor['text']);
        $survey->thankshtmlformat = $survey->thankshtml_editor['format'];
    }

    return $DB->update_record('survey', $survey);
}

/*
 * survey_save_user_style
 *
 * @param $survey, $context
 * @return null
 */
function survey_save_user_style($survey, $context) {
    global $CFG;

    $filemanageroptions = survey_get_user_style_options();

    $fieldname = 'userstyle';
    if ($draftitemid = $survey->{$fieldname.'_filemanager'}) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_survey', SURVEY_STYLEFILEAREA, 0, $filemanageroptions);
    }

    $fs = get_file_storage();
    if ($files = $fs->get_area_files($context->id, 'mod_survey', SURVEY_STYLEFILEAREA, 0, 'sortorder', false)) {
        if (count($files) == 1) {
            // only one file attached, set it as main file automatically
            $file = reset($files);
            file_set_sortorder($context->id, 'mod_survey', SURVEY_STYLEFILEAREA, 0, $file->get_filepath(), $file->get_filename(), 1);
        }
    }
}

/*
 * Removes an instance of the survey from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function survey_delete_instance($id) {
    global $DB;

    if (!$survey = $DB->get_record('survey', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here
    $submissions = $DB->get_records('survey_submission', array('surveyid' => $survey->id), '', 'id');

    // delete all associated survey_userdata
    $DB->delete_records_list('survey_userdata', 'submissionid', array_keys($submissions));

    // delete all associated survey_submission
    $DB->delete_records('survey_submission', array('surveyid' => $survey->id));

    // get all item_<<plugin>> and format_<<plugin>>
    $surveytypes = array(SURVEY_TYPEFIELD, SURVEY_TYPEFORMAT);
    foreach ($surveytypes as $surveytype) {
        $pluginlist = survey_get_plugin_list($surveytype);

        // delete all associated item_<<plugin>>
        foreach ($pluginlist as $plugin) {
            $tablename = 'survey'.$surveytype.'_'.$plugin;
            $DB->delete_records($tablename, array('surveyid' => $survey->id));
        }
    }

    // delete all associated survey_items
    $DB->delete_records('survey_item', array('surveyid' => $survey->id));

    // finally, delete the survey record
    $DB->delete_records('survey', array('id' => $survey->id));

    // -----------------------------
    // TODO: Am I supposed to delete files too?
    // -----------------------------

    // AREAS:
    //     SURVEY_STYLEFILEAREA
    //     SURVEY_TEMPLATEFILEAREA
    //     SURVEY_THANKSHTMLFILEAREA

    //     SURVEY_ITEMCONTENTFILEAREA <-- does this is supposed to go to its delete_instance plugin?
    //     SURVEYFIELD_FILEUPLOAD_FILEAREA <-- does this is supposed to go to its delete_instance plugin?
    //     SURVEYFIELD_TEXTAREAFILEAREA <-- does this is supposed to go to its delete_instance plugin?

    // never delete mod_survey files in each AREA in $context = context_user::instance($userid);

    // always delete mod_survey files in each AREA in $context = context_module::instance($contextid);

    // if this is the last survey of this course, delete also:
    // delete mod_survey files in each AREA in $context = context_course::instance($contextid);

    // if this is the last survey of the category, delete also:
    // delete mod_survey files in each AREA in $context = context_coursecat::instance($contextid);

    // if this is the very last survey, delete also:
    // delete mod_survey files in each AREA in $context = context_system::instance();

    return true;
}

/*
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function survey_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/*
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function survey_user_outline($course, $user, $mod, $survey) {
    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/*
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $survey the module instance record
 * @return void, is supposed to echp directly
 */
function survey_user_complete($course, $user, $mod, $survey) {
    return true;
}

/*
 * Given a course and a time, this module should find recent activity
 * that has occurred in survey activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function survey_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/*
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link survey_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function survey_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/*
 * Prints single activity item prepared by {@see survey_get_recent_mod_activity()}
 *
 * @return void
 */
function survey_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/*
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function survey_cron() {
    global $CFG, $DB;

    // delete too old submissions from survey_userdata and survey_submission

    $permission = array(0, 1);
    // permission == 0:  saveresume is not allowed
    //     users leaved records in progress more than four hours ago...
    //     I can not believe they are still working on them so
    //     I delete records now
    // permission == 1:  saveresume is allowed
    //     these records are older than maximum allowed time delay
    $maxinputdelay = get_config('survey', 'maxinputdelay');
    foreach ($permission as $saveresume) {
        if (($saveresume == 1) && ($maxinputdelay == 0)) { // maxinputdelay == 0 means, please don't delete
            continue;
        }
        if ($surveys = $DB->get_records('survey', array('saveresume' => $saveresume), null, 'id')) {
            $where = 'surveyid IN ('.implode(',', array_keys($surveys)).') AND status = :status AND timecreated < :sofar';
            $sofar = ($saveresume == 0) ? (4*3600) : ($maxinputdelay*3600);
            $sofar = time() - $sofar;
            $whereparams = array('status' => SURVEY_STATUSINPROGRESS, 'sofar' => $sofar);
            if ($submissionidlist = $DB->get_fieldset_select('survey_submission', 'id', $where, $whereparams)) {
                $DB->delete_records_list('survey_userdata', 'submissionid', $submissionidlist);
                $DB->delete_records_list('survey_submission', 'id', $submissionidlist);
            }
        }
    }

    return true;
}

/*
 * Returns an array of users who are participanting in this survey
 *
 * Must return an array of users who are participants for a given instance
 * of survey. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $surveyid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function survey_get_participants($surveyid) {
    return false;
}

/*
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function survey_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames', 'moodle/rating:view', 'moodle/rating:viewany', 'moodle/rating:viewall', 'moodle/rating:rate');
}

// -----------------------------
// Gradebook API
// -----------------------------

/*
 * Is a given scale used by the instance of survey?
 *
 * This function returns if a scale is being used by one survey
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $surveyid ID of an instance of this module
 * @return bool true if the scale is used by the given survey instance
 */
function survey_scale_used($surveyid, $scaleid) {
    global $DB;

    /* @example */
    // if ($scaleid and $DB->record_exists('survey', array('id' => $surveyid, 'grade' => -$scaleid))) {
    //     return true;
    // } else {
    //     return false;
    // }
    return false;
}

/*
 * Checks if scale is being used by any instance of survey.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any survey instance
 */
function survey_scale_used_anywhere($scaleid) {
    global $DB;

    /* @example */
    if ($scaleid and $DB->record_exists('survey', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}
/*
 * Creates or updates grade item for the give survey instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $survey instance object with extra cmidnumber and modname property
 * @return void
 */
function survey_grade_item_update(stdClass $survey) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /* @example */
    $item = array();
    $item['itemname'] = clean_param($survey->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax'] = $survey->grade;
    $item['grademin'] = 0;

    grade_update('mod/survey', $survey->course, 'mod', 'survey', $survey->id, 0, null, $item);
}

/*
 * Update survey grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $survey instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function survey_update_grades(stdClass $survey, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /* @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/survey', $survey->course, 'mod', 'survey', $survey->id, 0, $grades);
}

// -----------------------------
// File API
// -----------------------------

/*
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function survey_get_file_areas($course, $cm, $context) {
    return array();
}

/*
 * Serves the files from the survey file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function survey_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    $debug = false;
    if ($debug) {
        $debugfile = $CFG->dataroot.'/debug_'.date("m.d.y_H:i:s").'.txt';

        $debughandle = fopen($debugfile, 'w');
        fwrite($debughandle, 'Scrivo dalla riga '.__LINE__.' di '.__FILE__."\n");
        fwrite($debughandle, '$course'."\n");
        foreach ($course as $k => $v) {
            fwrite($debughandle, '$args['.$k.'] = '.$v."\n");
        }

        fwrite($debughandle, "\n".'$cm'."\n");
        foreach ($cm as $k => $v) {
            fwrite($debughandle, '$args['.$k.'] = '.$v."\n");
        }

        fwrite($debughandle, "\n".'$context'."\n");
        foreach ($context as $k => $v) {
            fwrite($debughandle, '$args['.$k.'] = '.$v."\n");
        }

        fwrite($debughandle, "\n".'$filearea = '.$filearea."\n");

        fwrite($debughandle, "\n".'$args'."\n");
        foreach ($args as $k => $v) {
            fwrite($debughandle, '$args['.$k.'] = '.$v."\n");
        }

        fwrite($debughandle, "\n".'$forcedownload = '.$forcedownload."\n");
    }

    $itemid = (int)array_shift($args);
    if ($debug) {
        fwrite($debughandle, "\n".'$itemid = '.$itemid."\n");
    }

    $relativepath = implode('/', $args);
    if ($debug) {
        fwrite($debughandle, "\n".'$relativepath = '.$relativepath."\n");
    }

    $fs = get_file_storage();

    $fullpath = "/$context->id/mod_survey/$filearea/$itemid/$relativepath";
    if ($debug) {
        fwrite($debughandle, "\n".'$fullpath = '.$fullpath."\n");
    }

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        if ($debug) {
            fwrite($debughandle, "\n".'$file da problemi: riporterei false'."\n");
        } else {
            return false;
        }
    }

    if ($debug) {
        fclose($debughandle);
    }

    // finally send the file
    send_stored_file($file, 0, 0, true); // download MUST be forced - security!

    return false;
}

// -----------------------------
// Navigation API
// -----------------------------

/*
 * Extends the settings navigation with the survey settings
 *
 * This function is called when the context for the page is a survey module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $surveynode {@link navigation_node}
 */
function survey_extend_settings_navigation(settings_navigation $settings, navigation_node $surveynode) {
    global $CFG, $PAGE, $DB;

    $cm = $PAGE->cm;
    if (!$cm = $PAGE->cm) {
        return;
    }
    $survey = $DB->get_record('survey', array('id' => $cm->instance), '*', MUST_EXIST);

    $context = context_module::instance($cm->id);

    $riskyediting = ($survey->riskyeditdeadline > time());

    $canpreview = has_capability('mod/survey:preview', $context, null, true);
    $canmanageitems = has_capability('mod/survey:manageitems', $context, null, true);

    $canmanageusertemplates = has_capability('mod/survey:manageusertemplates', $context, null, true);
    $cansaveusertemplates = has_capability('mod/survey:saveusertemplates', $context, null, true);
    $canimportusertemplates = has_capability('mod/survey:importusertemplates', $context, null, true);
    $canapplyusertemplates = has_capability('mod/survey:applyusertemplates', $context, null, true);

    $cansavemastertemplates = has_capability('mod/survey:savemastertemplates', $context, null, true);
    $canapplymastertemplates = has_capability('mod/survey:applymastertemplates', $context, null, true);

    $canaccessreports = has_capability('mod/survey:accessreports', $context, null, true);
    $canaccessownreports = has_capability('mod/survey:accessownreports', $context, null, true);

    $hassubmissions = survey_count_submissions($cm->instance);

    $whereparams = array('surveyid' => $cm->instance);
    $countparents = $DB->count_records_select('survey_item', 'surveyid = :surveyid AND parentid <> 0', $whereparams);

    /*
     * SURVEY_TABITEMS
     */
    // PARENT
    if (($canpreview) || ($canmanageitems && (!$survey->template))) {
        $paramurl = array('s' => $cm->instance);
        $navnode = $surveynode->add(SURVEY_TAB2NAME,  new moodle_url('/mod/survey/items_manage.php', $paramurl), navigation_node::TYPE_CONTAINER);
    }

    // CHILDREN
    if ($canpreview) {
        $localparamurl = array('s' => $cm->instance, 'view' => SURVEY_PREVIEWSURVEY);
        $navnode->add(get_string('tabitemspage1', 'survey'), new moodle_url('/mod/survey/view.php', $localparamurl), navigation_node::TYPE_SETTING);
    }
    if ($canmanageitems) {
        $navnode->add(get_string('tabitemspage2', 'survey'), new moodle_url('/mod/survey/items_manage.php', $paramurl), navigation_node::TYPE_SETTING);
        if (!$survey->template) {
            if ($countparents) {
                $navnode->add(get_string('tabitemspage4', 'survey'), new moodle_url('/mod/survey/items_validate.php', $paramurl), navigation_node::TYPE_SETTING);
            }
        }
    }

    /*
     * SURVEY_TABUTEMPLATES
     */
    if ($canmanageusertemplates && (!$survey->template)) {
        // PARENT
        $paramurl = array('s' => $cm->instance);
        $navnode = $surveynode->add(SURVEY_TAB3NAME,  new moodle_url('/mod/survey/utemplates_create.php', $paramurl), navigation_node::TYPE_CONTAINER);

        // CHILDREN
        $navnode->add(get_string('tabutemplatepage1', 'survey'), new moodle_url('/mod/survey/utemplates_manage.php', $paramurl), navigation_node::TYPE_SETTING);
        if ($cansaveusertemplates) {
            $navnode->add(get_string('tabutemplatepage2', 'survey'), new moodle_url('/mod/survey/utemplates_create.php', $paramurl), navigation_node::TYPE_SETTING);
        }
        if ($canimportusertemplates) {
            $navnode->add(get_string('tabutemplatepage3', 'survey'), new moodle_url('/mod/survey/utemplates_import.php', $paramurl), navigation_node::TYPE_SETTING);
        }
        if ( (!$hassubmissions || $riskyediting) && $canapplyusertemplates ) {
            $navnode->add(get_string('tabutemplatepage4', 'survey'), new moodle_url('/mod/survey/utemplates_apply.php', $paramurl), navigation_node::TYPE_SETTING);
        }
    }

    /*
     * SURVEY_TABMTEMPLATES
     */
    if (!$survey->template) {
        // PARENT
        $paramurl = array('s' => $cm->instance);
        $navnode = $surveynode->add(SURVEY_TAB4NAME, new moodle_url('/mod/survey/mtemplates_create.php', $paramurl), navigation_node::TYPE_CONTAINER);

        // CHILDREN
        if ($cansavemastertemplates) {
            $navnode->add(get_string('tabmtemplatepage1', 'survey'), new moodle_url('/mod/survey/mtemplates_create.php', $paramurl), navigation_node::TYPE_SETTING);
        }
        if ( (!$hassubmissions || $riskyediting) && $canapplymastertemplates ) {
            $navnode->add(get_string('tabmtemplatepage2', 'survey'), new moodle_url('/mod/survey/mtemplates_apply.php', $paramurl), navigation_node::TYPE_SETTING);
        }
    }

    /*
     * SURVEY REPORTS
     */
    if ($surveyreportlist = get_plugin_list('surveyreport')) {
        $canaccessownreports = has_capability('mod/survey:accessownreports', $context, null, true);
        $icon = new pix_icon('i/report', '', 'moodle', array('class' => 'icon'));
        $reportnode = $surveynode->add(get_string('report'), null, navigation_node::TYPE_CONTAINER);
        $paramurl = array('s' => $PAGE->cm->instance);
        foreach ($surveyreportlist as $pluginname => $pluginpath) {
            require_once($CFG->dirroot.'/mod/survey/report/'.$pluginname.'/report.class.php');
            $classname = 'report_'.$pluginname;
            $restricttemplates = $classname::restrict_templates();
            if ((!$restricttemplates) || in_array($survey->template, $restricttemplates)) {
                if ($canaccessreports || ($classname::has_student_report() && $canaccessownreports)) {
                    if ($childreports = $classname::get_childreports($canaccessreports)) {
                        $childnode = $reportnode->add(get_string('pluginname', 'surveyreport_'.$pluginname),
                                                      null, navigation_node::TYPE_CONTAINER);
                        foreach ($childreports as $childname => $childparams) {
                            $childparams['s'] = $PAGE->cm->instance;
                            $url = new moodle_url('/mod/survey/report/'.$pluginname.'/view.php', $childparams);
                            $childnode->add($childname, $url, navigation_node::TYPE_SETTING, null, null, $icon);
                        }
                    } else {
                        $url = new moodle_url('/mod/survey/report/'.$pluginname.'/view.php', $paramurl);
                        $reportnode->add(get_string('pluginname', 'surveyreport_'.$pluginname),
                                         $url, navigation_node::TYPE_SETTING, null, null, $icon);
                    }
                }
            }
        }
    }
}

/*
 * Extends the global navigation tree by adding survey nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the survey module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function survey_extend_navigation(navigation_node $navref, stdClass $course, stdClass $survey, cm_info $cm) {
    global $CFG, $OUTPUT, $USER, $DB;

    // $context = context_system::instance();
    $context = context_module::instance($cm->id);

    $cansearch = has_capability('mod/survey:searchsubmissions', $context, null, true);
    $canexportdata = has_capability('mod/survey:exportdata', $context, null, true);

    // $currentgroup = groups_get_activity_group($cm);
    // $groupmode = groups_get_activity_groupmode($cm);

    /*
     * SURVEY_TABSUBMISSIONS
     */
    // CHILDREN ONLY
    $paramurl = array('s' => $cm->instance);
    $navref->add(get_string('tabsubmissionspage2', 'survey'), new moodle_url('/mod/survey/view_manage.php', $paramurl), navigation_node::TYPE_SETTING);
    if ($cansearch) {
        $navref->add(get_string('tabsubmissionspage5', 'survey'), new moodle_url('/mod/survey/view_search.php', $paramurl), navigation_node::TYPE_SETTING);
    }
    if ($canexportdata) {
        $navref->add(get_string('tabsubmissionspage7', 'survey'), new moodle_url('/mod/survey/view_export.php', $paramurl), navigation_node::TYPE_SETTING);
    }
}

// -----------------------------
// CUSTOM SURVEY API
// -----------------------------

/*
 * Is re-captcha enabled at site level
 *
 * @return boolean true if true
 */
function survey_site_recaptcha_enabled() {
    global $CFG;

    return !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey);
}

/**
 * survey_get_plugin_list
 *
 * @param $plugintype
 * @param $includetype
 * @param $count
 * @return
 */
function survey_get_plugin_list($plugintype=null, $includetype=false, $count=false) {
    $plugincount = 0;
    $fieldplugins = array();
    $formatplugins = array();

    if ($plugintype == SURVEY_TYPEFIELD || is_null($plugintype)) {
        if ($count) {
            $plugincount += count(get_plugin_list('survey'.SURVEY_TYPEFIELD));
        } else {
            $fieldplugins = core_component::get_plugin_list('survey'.SURVEY_TYPEFIELD);
            if (!empty($includetype)) {
                foreach ($fieldplugins as $k => $v) {
                    if (!get_config('surveyfield_'.$k, 'disabled')) {
                        $fieldplugins[$k] = SURVEY_TYPEFIELD.'_'.$k;
                    } else {
                        unset($fieldplugins[$k]);
                    }
                }
                $fieldplugins = array_flip($fieldplugins);
            } else {
                foreach ($fieldplugins as $k => $v) {
                    if (!get_config('surveyfield_'.$k, 'disabled')) {
                        $fieldplugins[$k] = $k;
                    } else {
                        unset($fieldplugins[$k]);
                    }
                }
            }
        }
    }
    if ($plugintype == SURVEY_TYPEFORMAT || is_null($plugintype)) {
        if ($count) {
            $plugincount += count(core_component::get_plugin_list('survey'.SURVEY_TYPEFORMAT));
        } else {
            if (!empty($includetype)) {
                $formatplugins = core_component::get_plugin_list('survey'.SURVEY_TYPEFORMAT);
                foreach ($formatplugins as $k => $v) {
                    if (!get_config('surveyformat_'.$k, 'disabled')) {
                        $formatplugins[$k] = SURVEY_TYPEFORMAT.'_'.$k;
                    } else {
                        unset($formatplugins[$k]);
                    }
                }
                $formatplugins = array_flip($formatplugins);
            } else {
                foreach ($formatplugins as $k => $v) {
                    if (!get_config('surveyformat_'.$k, 'disabled')) {
                        $formatplugins[$k] = $k;
                    } else {
                        unset($formatplugins[$k]);
                    }
                }
            }
        }
    }

    if ($count) {
        return $plugincount;
    } else {
        $pluginlist = $fieldplugins + $formatplugins;
        asort($pluginlist);
        return $pluginlist;
    }
}

/**
 * survey_fetch_items_seeds
 * @param $canaccessadvanceditems
 * @param $searchform
 * @param $type
 * @param $formpage
 * @return
 */
function survey_fetch_items_seeds($surveyid, $canaccessadvanceditems, $searchform, $type=false, $formpage=false) {
    $sql = 'SELECT si.*
               FROM {survey_item} si
               WHERE si.surveyid = :surveyid
                   AND si.hidden = 0';
    $params = array();
    $params['surveyid'] = $surveyid;

    if (!$canaccessadvanceditems) {
        $sql .= ' AND si.advanced = 0';
    }
    if ($searchform) { // advanced search
        $sql .= ' AND si.insearchform = 1';
        $sql .= ' AND si.plugin <> "pagebreak"';
    }
    if ($type) {
        $sql .= ' AND si.type = :type';
        $params['type'] = $type;
    }
    if ($formpage) { // if I am asking for a single page ONLY
        $sql .= ' AND si.formpage = :formpage';
        $params['formpage'] = $formpage;
    }
    $sql .= ' ORDER BY si.sortindex';

    return array($sql, $params);
}

/**
 * survey_get_view_actions
 * @param
 * @return
 */
function survey_get_view_actions() {
    return array('view', 'view all');
}

/**
 * survey_get_post_actions
 * @param
 * @return
 */
function survey_get_post_actions() {
    return array('add', 'update');
}

/*
 * This gets an array with default options for the editor
 *
 * @return array the options
 */
function survey_get_editor_options() {
    return array('trusttext' => true, 'subdirs' => false, 'maxfiles' => EDITOR_UNLIMITED_FILES);
}

/*
 * survey_reset_items_pages
 * @param $targetuser, $surveyid
 * @return
 */
function survey_reset_items_pages($surveyid) {
    global $DB;

    $sqlparam = array('surveyid' => $surveyid);
    $DB->set_field('survey_item', 'formpage', 0, $sqlparam);
}

/*
 * survey_count_submissions
 * @param $findparams
 * @return
 */
function survey_count_submissions($surveyid, $status=SURVEY_STATUSALL) {
    global $DB;

    $params = array('surveyid' => $surveyid);
    if ($status != SURVEY_STATUSALL) {
        $params['status'] = $status;
    }

    return $DB->count_records('survey_submission', $params);
}

/*
 * survey_get_user_style_options
 * @param none
 * @return $filemanageroptions
 */
function survey_get_user_style_options() {
    $filemanageroptions = array();
    $filemanageroptions['accepted_types'] = '.css';
    $filemanageroptions['maxbytes'] = 0;
    $filemanageroptions['maxfiles'] = -1;
    $filemanageroptions['mainfile'] = true;
    $filemanageroptions['subdirs'] = false;

    return $filemanageroptions;
}

/**
 * Obtains the automatic completion state for this module based on any conditions
 * in survey settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function survey_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    // Get forum details
    if (!$survey = $DB->get_record('survey', array('id' => $cm->instance))) {
        throw new Exception('Can\'t find survey '.$cm->instance);
    }

    // If completion option is enabled, evaluate it and return true/false.
    if ($survey->completionsubmit) {
        $params = array('surveyid' => $cm->instance, 'userid' => $userid, 'status' => SURVEY_STATUSCLOSED);
        $submissioncount = $DB->count_records('survey_submission', $params);
        return ($submissioncount >= $completionsubmit);
    } else {
        // Completion option is not enabled so just return $type.
        return $type;
    }
}
