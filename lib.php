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
 * All the core Moodle functions, neeeded to allow the module to work
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

// Subjects for permissions
define('SURVEY_ALL'  , 3);
define('SURVEY_GROUP', 2);
define('SURVEY_OWNER', 1);
define('SURVEY_NONE' , 0);

// Some constants
define('SURVEY_MAX_ENTRIES'        , 50);
define('SURVEY_MINEVERYEAR'        , 1970);
define('SURVEY_MAXEVERYEAR'        , 2020);
define('SURVEY_VALUELABELSEPARATOR', '::');
define('SURVEY_OTHERSEPARATOR'     , '->');
define('SURVEY_REFERRALYEAR'       , 1970);
define('SURVEY_REFERRALMONTH'      , 0);

// TAB NUMBERS ALIAS COUNT OF THE TABS IN THE TAB BAR
// to change tabs order, just exchange numbers if the following lines

$i = 0;
$i++; define('SURVEY_TABSUBMISSIONS', $i);
$i++; define('SURVEY_TABITEMS'      , $i);
$i++; define('SURVEY_TABTEMPLATES'  , $i);
$i++; define('SURVEY_TABPLUGINS'    , $i);
      define('SURVEY_TABCOUNT'      , $i);

// TAB NAMES
define('SURVEY_TAB'.SURVEY_TABSUBMISSIONS.'NAME', get_string('tabsubmissionsname', 'survey'));
define('SURVEY_TAB'.SURVEY_TABITEMS.'NAME'  , get_string('tabitemname', 'survey'));
define('SURVEY_TAB'.SURVEY_TABTEMPLATES.'NAME', get_string('tabtemplatename', 'survey'));
define('SURVEY_TAB'.SURVEY_TABPLUGINS.'NAME', get_string('tabpluginsname', 'survey'));

// PAGES
    // SUBMISSIONS PAGES
    define('SURVEY_SUBMISSION_EXPLORE' , 1);
    define('SURVEY_SUBMISSION_NEW'     , 2);
    define('SURVEY_SUBMISSION_EDIT'    , 3);
    define('SURVEY_SUBMISSION_READONLY', 4);
    define('SURVEY_SUBMISSION_MANAGE'  , 5);
    define('SURVEY_SUBMISSION_SEARCH'  , 6);
    define('SURVEY_SUBMISSION_REPORT'  , 7);
    define('SURVEY_SUBMISSION_EXPORT'  , 8);

    // ITEMS PAGES
    define('SURVEY_ITEMS_MANAGE'       , 1);
    define('SURVEY_ITEMS_REORDER'      , 2);
    define('SURVEY_ITEMS_ADD'          , 3);
    define('SURVEY_ITEMS_CONFIGURE'    , 4);
    define('SURVEY_ITEMS_ADDSET'       , 5);
    define('SURVEY_ITEMS_VALIDATE'     , 6);

    // TEMPLATES PAGES
    define('SURVEY_TEMPLATES_MANAGE'   , 1);
    define('SURVEY_TEMPLATES_BUILD'    , 2);
    define('SURVEY_TEMPLATES_IMPORT'   , 3);

    // PLUGINS PAGES
    define('SURVEY_PLUGINS_BUILD'      , 1);

// ITEM TYPES
define('SURVEY_TYPEFIELD' , 'field');
define('SURVEY_TYPEFORMAT', 'format');

// ACTIONS
define('SURVEY_NOACTION'           , '0');
define('SURVEY_CHOOSEFTYPE'        , '1');
define('SURVEY_EDITITEM'           , '2');
define('SURVEY_HIDEITEM'           , '3');
define('SURVEY_SHOWITEM'           , '4');
define('SURVEY_DELETEITEM'         , '5');
define('SURVEY_CHANGEORDERASK'     , '6');
define('SURVEY_CHANGEORDER'        , '7');
define('SURVEY_REQUIREDOFF'        , '8');
define('SURVEY_REQUIREDON'         , '9');
define('SURVEY_CHANGEINDENT'       , '10');
define('SURVEY_EDITSURVEY'         , '11');
define('SURVEY_VIEWSURVEY'         , '12');
define('SURVEY_DELETESURVEY'       , '13');
define('SURVEY_DELETEALLRESPONSES' , '14');
define('SURVEY_VALIDATE'           , '15');
define('SURVEY_DELETETEMPLATE'     , '16');
define('SURVEY_EXPORTTEMPLATE'     , '17');

// SAVESTATUS
define('SURVEY_NOFEEDBACK', 0);

// ITEMS AVAILABILITY
define('SURVEY_NOTPRESENT'      , 0);
define('SURVEY_FILLONLY'        , 1);
define('SURVEY_FILLANDSEARCH'   , 2);
define('SURVEY_ADVFILLONLY'     , 0);
define('SURVEY_ADVFILLANDSEARCH', 1);

// ITEMPREFIX
define('SURVEY_ITEMPREFIX', 'survey');
define('SURVEY_NEGLECTPREFIX', 'neglect');

// INVITATION AND NO ANSWER VALUE
define('SURVEY_INVITATIONVALUE', '__invItat10n__'); // user should never guess it
define('SURVEY_NOANSWERVALUE', '__n0__Answer__');   // user should never guess it

// ADJUSTMENTS
define('SURVEY_VERTICAL',   0);
define('SURVEY_HORIZONTAL', 1);

// COLELCTION STATUS
define('SURVEY_STATUSINPROGRESS', 1);
define('SURVEY_STATUSCLOSED'    , 0);
define('SURVEY_STATUSALL'       , 2);

// DOWNLOAD
define('SURVEY_DOWNLOADCSV', 1);
define('SURVEY_DOWNLOADXLS', 2);
define('SURVEY_NOFIELDSSELECTED', 1);
define('SURVEY_NORECORDSFOUND'  , 2);

// SEARCH
define('SURVEY_URLPARAMSEPARATOR', '-');
define('SURVEY_URLVALUESEPARATOR', '_');
define('SURVEY_URLMULTIVALUESEPARATOR', '+');
define('SURVEY_DBMULTIVALUESEPARATOR', ', ');

// CONFIRMATION
define('SURVEY_CONFIRM', 1);
define('SURVEY_NEGATE' , 2);

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
define('SURVEY_HIDEITEMS'  , '1');
define('SURVEY_DELETEITEMS', '2');
define('SURVEY_IGNOREITEMS', '3');

// empty template field
define('SURVEY_EMPTYTEMPLATEFIELD', '@@NULL@@');

define('SURVEY_USERTEMPLATE', 'TEMPLATE');
define('SURVEY_MASTERTEMPLATE', 'SURVEYPLUGIN');

// //////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
// //////////////////////////////////////////////////////////////////////////////

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
    if ($CFG->survey_useadvancedpermissions) {
        list($survey->readaccess, $survey->editaccess, $survey->deleteaccess) = explode('.', $survey->accessrights);
    } else {
        // since $cm->groupmode will be updated once this method is over, I here use $survey->groupmode instead of $cm->groupmode to get $groupmode
        $groupmode = empty($COURSE->groupmodeforce) ? $survey->groupmode : $COURSE->groupmode;
        if ($groupmode) {
            $survey->readaccess = SURVEY_GROUP;
            $survey->editaccess = SURVEY_GROUP;
            $survey->deleteaccess = SURVEY_OWNER;
        } else {
            $survey->readaccess = SURVEY_OWNER;
            $survey->editaccess = SURVEY_OWNER;
            $survey->deleteaccess = SURVEY_OWNER;
        }
    }

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
    $DB->set_field('course_modules', 'instance', $survey->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);
    survey_save_user_style($survey, $context);

    // manage thankshtml editor
    // if (!isset($survey->coursemodule)) {
        // $cm = get_coursemodule_from_id('survey', $survey->id, 0, false, MUST_EXIST);
        // $survey->coursemodule = $cm->id;
    // }
    $editoroptions = survey_get_editor_options();
    if ($draftitemid = $survey->thankshtml_editor['itemid']) {
        $survey->thankshtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_survey', SURVEY_THANKSHTMLFILEAREA, $survey->id, $editoroptions, $survey->thankshtml_editor['text']);
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

    if ($CFG->survey_useadvancedpermissions) {
        list($survey->readaccess, $survey->editaccess, $survey->deleteaccess) = explode('.', $survey->accessrights);
    } else {
        // since $cm->groupmode will be updated once this method is over, I here use $survey->groupmode instead of $cm->groupmode to get $groupmode
        $groupmode = empty($COURSE->groupmodeforce) ? $survey->groupmode : $COURSE->groupmode;
        if ($groupmode) {
            $survey->readaccess = SURVEY_GROUP;
            $survey->editaccess = SURVEY_GROUP;
            $survey->deleteaccess = SURVEY_OWNER;
        } else {
            $survey->readaccess = SURVEY_OWNER;
            $survey->editaccess = SURVEY_OWNER;
            $survey->deleteaccess = SURVEY_OWNER;
        }
    }

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
// echo '$survey->readaccess = '.$survey->readaccess.'<br />';
// echo '$survey->editaccess = '.$survey->editaccess.'<br />';
// echo '$survey->deleteaccess = '.$survey->deleteaccess.'<br />';

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

    $filemanager_options = survey_get_user_style_options();

    $fieldname = 'userstyle';
    if ($draftitemid = $survey->{$fieldname.'_filemanager'}) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_survey', SURVEY_STYLEFILEAREA, 0, $filemanager_options);
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

    $DB->delete_records('survey', array('id' => $survey->id));
    // AREAS:
    //     SURVEY_STYLEFILEAREA
    //     SURVEY_TEMPLATEFILEAREA
    //     SURVEY_ITEMCONTENTFILEAREA
    //     SURVEY_THANKSHTMLFILEAREA
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
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
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

    $status = SURVEY_STATUSINPROGRESS;
    $permission = array(0, 1);
    // permission == 0:  saveresume is not allowed
    //     users leaved records in progress more than four hours ago...
    //     I can not trust they are still working on them so
    //     I delete records now
    // permission == 1:  saveresume is allowed
    //     these records are older than maximum allowed time delay
    foreach ($permission as $saveresume) {
        if ($surveys = $DB->get_records('survey', array('saveresume' => $saveresume), null, 'id')) {
            $where = 'surveyid IN ('.implode(',', array_keys($surveys)).') AND status = :status AND timecreated < :sofar';
            $sofar = ($saveresume == 1) ? ($CFG->survey_maxinputdelay*3600) : (4*3600);
            $sofar = time() - $sofar;
            $sqlparams = array('status' => SURVEY_STATUSINPROGRESS, 'sofar' => $sofar);
            if ($submissionidlist = $DB->get_fieldset_select('survey_submissions', 'id', $where, $sqlparams)) {
                $DB->delete_records_list('survey_userdata', 'submissionid', $submissionidlist);
                $DB->delete_records_list('survey_submissions', 'id', $submissionidlist);
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

// //////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
// //////////////////////////////////////////////////////////////////////////////

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

// //////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
// //////////////////////////////////////////////////////////////////////////////

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

// //////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
// //////////////////////////////////////////////////////////////////////////////

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
function survey_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
    global $CFG, $OUTPUT, $USER, $DB;

    $canmanageitems = survey_user_can_manage_items($cm);
    $canmanageplugin = survey_user_can_manage_plugin($cm);
    $canexportdata = survey_user_can_export_data($cm);
    $hassubmissions = survey_has_submissions($cm->instance);

    $survey = $DB->get_record('survey', array('id' => $cm->instance));
    // $currentgroup = groups_get_activity_group($cm);
    // $groupmode = groups_get_activity_groupmode($cm);

    $paramurl = array('s' => $cm->instance, 'tab' => SURVEY_TABSUBMISSIONS);
    $navnode = $navref->add(SURVEY_TAB1NAME,  new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_CONTAINER);

    if (!empty($canmanageitems)) {
        $paramurl['pag'] = SURVEY_SUBMISSION_EXPLORE;
        $navnode->add(get_string('tabsubmissionspage1', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
    }
    $paramurl['pag'] = SURVEY_SUBMISSION_NEW;
    $navnode->add(get_string('tabsubmissionspage2', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
    $paramurl['pag'] = SURVEY_SUBMISSION_MANAGE;
    $navnode->add(get_string('tabsubmissionspage5', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
    $paramurl['pag'] = SURVEY_SUBMISSION_SEARCH;
    $navnode->add(get_string('tabsubmissionspage6', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
    if (!empty($canexportdata)) {
        $paramurl['pag'] = SURVEY_SUBMISSION_EXPORT;
        $navnode->add(get_string('tabsubmissionspage8', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
    }
    //$navref->add(SURVEY_TAB1NAME, new moodle_url('/mod/survey/view.php', array('s' => $cm->instance, 'tab' => SURVEY_TABSUBMISSIONS)));

    if (!empty($canmanageitems)) {
        $paramurl = array('s' => $cm->instance, 'tab' => SURVEY_TABITEMS);
        $navnode = $navref->add(SURVEY_TAB2NAME,  new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_CONTAINER);

        $paramurl['pag'] = SURVEY_ITEMS_MANAGE;
        $navnode->add(get_string('tabitemspage1', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
        if (!$hassubmissions) {
            $paramurl['pag'] = SURVEY_ITEMS_ADD;
            $navnode->add(get_string('tabitemspage3', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
            $paramurl['pag'] = SURVEY_ITEMS_ADDSET;
            $navnode->add(get_string('tabitemspage5', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
        }
        $paramurl['pag'] = SURVEY_ITEMS_VALIDATE;
        $navnode->add(get_string('tabitemspage6', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
        //$navref->add(SURVEY_TAB2NAME, new moodle_url('/mod/survey/view.php', $paramurl));
    }

    $paramurl = array('s' => $cm->instance, 'tab' => SURVEY_TABTEMPLATES);
    $navnode = $navref->add(SURVEY_TAB3NAME,  new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_CONTAINER);

    $paramurl['pag'] = SURVEY_TEMPLATES_MANAGE;
    $navnode->add(get_string('tabtemplatepage1', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
    $paramurl['pag'] = SURVEY_TEMPLATES_BUILD;
    $navnode->add(get_string('tabtemplatepage2', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
    $paramurl['pag'] = SURVEY_TEMPLATES_IMPORT;
    $navnode->add(get_string('tabtemplatepage3', 'survey'), new moodle_url('/mod/survey/view.php', $paramurl), navigation_node::TYPE_SETTING);
    // $navref->add(SURVEY_TAB3NAME, new moodle_url('/mod/survey/view.php', array('s' => $cm->instance, 'tab' => SURVEY_TABTEMPLATES)));

    if ($canmanageplugin) {
        $paramurl = array('s' => $cm->instance, 'tab' => SURVEY_TABPLUGINS);
        $navref->add(SURVEY_TAB4NAME, new moodle_url('/mod/survey/view.php', $paramurl));
    }
}

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
    global $PAGE;

//     $cm = $PAGE->cm;
//     if (!$cm) {
//         return;
//     }
//
//     $link = new moodle_url('/mod/survey/view.php', array('id' => $cm->id, 'action'=>'grading'));
//     $surveynode->add(get_string('report'), $link, navigation_node::TYPE_SETTING);

    if ($surveyreportlist = get_plugin_list('surveyreport')) {
        $icon = new pix_icon('i/report', '', 'moodle', array('class'=>'icon'));
        $reportnode = $surveynode->add(get_string('report'), null, navigation_node::TYPE_CONTAINER);
        $paramurl = array('s' => $PAGE->cm->instance, 'tab' => SURVEY_TABSUBMISSIONS, 'pag' => SURVEY_SUBMISSION_REPORT);
        foreach ($surveyreportlist as $pluginname => $pluginpath) {
            $paramurl['rname'] = $pluginname;
            $reportnode->add(get_string('pluginname', 'surveyreport_'.$pluginname), new moodle_url('view.php', $paramurl), navigation_node::TYPE_SETTING, null, null, $icon);
        }
    }
}

/*
 * survey_user_can_manage_items
 * @param $cm
 * @return
 */
function survey_user_can_manage_items($cm) {
    $context = context_module::instance($cm->id);

    return (has_capability('mod/survey:manageitems', $context, null, true));
}

/*
 * survey_user_can_manage_plugin
 * @param $cm
 * @return
 */
function survey_user_can_manage_plugin($cm) {
    $context = context_module::instance($cm->id);

    return (has_capability('mod/survey:manageplugin', $context, null, true));
}

/*
 * survey_user_can_export_data
 * @param $cm
 * @return
 */
function survey_user_can_export_data($cm) {
    $context = context_module::instance($cm->id);

    return (has_capability('mod/survey:exportdata', $context, null, true));
}

// //////////////////////////////////////////////////////////////////////////////
// CUSTOM SURVEY API                                                           //
// //////////////////////////////////////////////////////////////////////////////

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
 * @param $plugintype=null, $includetype=false, $count=false
 * @return
 */
function survey_get_plugin_list($plugintype=null, $includetype=false, $count=false) {
    $plugincount = 0;
    $field_pluginlist = array();
    $format_pluginlist = array();

    if ($plugintype == SURVEY_TYPEFIELD || is_null($plugintype)) {
        if ($count) {
            $plugincount += count(get_plugin_list('survey'.SURVEY_TYPEFIELD));
        } else {
            $field_pluginlist = get_plugin_list('survey'.SURVEY_TYPEFIELD);
            if (!empty($includetype)) {
                foreach ($field_pluginlist as $k => $v) {
                    $field_pluginlist[$k] = SURVEY_TYPEFIELD.'_'.$k;
                }
                $field_pluginlist = array_flip($field_pluginlist);
            } else {
                foreach ($field_pluginlist as $k => $v) {
                    $field_pluginlist[$k] = $k;
                }
            }
        }
    }
    if ($plugintype == SURVEY_TYPEFORMAT || is_null($plugintype)) {
        if ($count) {
            $plugincount += count(get_plugin_list('survey'.SURVEY_TYPEFORMAT));
        } else {
            if (!empty($includetype)) {
                $format_pluginlist = get_plugin_list('survey'.SURVEY_TYPEFORMAT);
                foreach ($format_pluginlist as $k => $v) {
                    $format_pluginlist[$k] = SURVEY_TYPEFORMAT.'_'.$k;
                }
                $format_pluginlist = array_flip($format_pluginlist);
            } else {
                foreach ($format_pluginlist as $k => $v) {
                    $format_pluginlist[$k] = $k;
                }
            }
        }
    }

    if ($count) {
        return $plugincount;
    } else {
        $pluginlist = $field_pluginlist + $format_pluginlist;
        asort($pluginlist);
        return $pluginlist;
    }
}

/**
 * survey_fetch_items_seeds
 * @param $canaccessadvancedform, $searchform, $allpages=false
 * @return
 */
function survey_fetch_items_seeds($canaccessadvancedform, $searchform, $allpages=false) {
    $return = 'SELECT si.*
               FROM {survey_item} si
               WHERE si.surveyid = :surveyid';
    if ($canaccessadvancedform) {
        if ($searchform) { // advanced search
            $return .= ' AND si.advancedsearch = '.SURVEY_ADVFILLANDSEARCH;
        } else {            // advanced entry
            if (!$allpages) { // if I am not asking for all the pages, I focus on a single page only
                $return .= ' AND si.advancedformpage = :formpage';
            }
        }
    } else {
        if ($searchform) { // user search
            $return .= ' AND si.basicform = '.SURVEY_FILLANDSEARCH;
        } else {            // user entry
            $return .= ' AND si.basicform <> '.SURVEY_NOTPRESENT;
            if (!$allpages) { // if I am not asking for all the pages, I focus on a single page only
                $return .= ' AND si.basicformpage = :formpage';
            }
        }
    }
    $return .= ' AND si.hide = 0
            ORDER BY si.sortindex';

    return $return;
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

    $targets = array('basicformpage', 'advancedformpage');

    $sqlparam = array('surveyid' => $surveyid);
    foreach ($targets as $target) {
        $DB->set_field('survey_item', $target, 0, $sqlparam);
    }
}

/*
 * survey_has_submissions
 * @param $findparams
 * @return
 */
function survey_has_submissions($surveyid, $status=SURVEY_STATUSALL) {
    global $DB;

    $params = array('surveyid' => $surveyid);
    if ($status != SURVEY_STATUSALL) {
        $params['status'] = $status;
    }

    return $DB->count_records('survey_submissions', $params);
}

/*
 * survey_get_user_style_options
 * @param none
 * @return $filemanager_options
 */
function survey_get_user_style_options() {
    $filemanager_options = array();
    $filemanager_options['accepted_types'] = '.css';
    $filemanager_options['maxbytes'] = 0;
    $filemanager_options['maxfiles'] = -1;
    $filemanager_options['mainfile'] = true;
    $filemanager_options['subdirs'] = false;

    return $filemanager_options;
}

/*
 * survey_attempt_save_preprocessing
 * @param none
 * @return survey_submissions record
 */
function survey_save_survey_submissions($survey, $fromform) {
    global $USER, $DB;

    if (!$survey->newpageforchild) {
        survey_drop_unexpected_values($fromform);
    }

    $timenow = time();
    $savebutton = (isset($fromform->savebutton) && ($fromform->savebutton));
    $saveasnewbutton = (isset($fromform->saveasnewbutton) && ($fromform->saveasnewbutton));

    $survey_submissions = new stdClass();
    if ($saveasnewbutton || empty($fromform->submissionid)) { // new record needed
        // add a new record to survey_submissions
        $survey_submissions->surveyid = $survey->id;
        $survey_submissions->userid = $USER->id;

        if (empty($fromform->submissionid)) {
            $survey_submissions->status = SURVEY_STATUSINPROGRESS;
            $survey_submissions->timecreated = $timenow;
        }
        if ($savebutton) {
            $survey_submissions->status = SURVEY_STATUSCLOSED;
            $survey_submissions->timemodified = $timenow;
        }
        if ($saveasnewbutton) {
            $survey_submissions->status = SURVEY_STATUSCLOSED;
            $survey_submissions->timecreated = $timenow;
            $survey_submissions->timemodified = $timenow;
        }

        $survey_submissions->id = $DB->insert_record('survey_submissions', $survey_submissions);

        $fromform->submissionid = $submissionid;
    } else {
        $survey_submissions->id = $fromform->submissionid;
        if ($savebutton) {
            $survey_submissions->status = SURVEY_STATUSCLOSED;
            $survey_submissions->timemodified = $timenow;
            $DB->update_record('survey_submissions', $survey_submissions);
        }
    }

    return $survey_submissions;
}