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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_survey_mod_form extends moodleform_mod {

    public function definition() {
        global $COURSE, $DB, $CFG, $cm;

        $useadvancedpermissions = get_config('survey', 'useadvancedpermissions');

        $mform = $this->_form;

        // -------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $fieldname = 'name';
        $mform->addElement('text', $fieldname, get_string('survey'.$fieldname, 'survey'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType($fieldname, PARAM_TEXT);
        } else {
            $mform->setType($fieldname, PARAM_CLEANHTML);
        }
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->addRule($fieldname, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton($fieldname, 'surveyname', 'survey');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor(false);

        // Open date
        $fieldname = 'timeopen';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'survey'), array('optional' => true));

        // Close date
        $fieldname = 'timeclose';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'survey'), array('optional' => true));

        // I can save a query because I know in which $COURSE I am
        //$groupmode = isset($cm) ? groups_get_activity_groupmode($cm) : 0;
        if (isset($cm)) {
            $groupmode = empty($COURSE->groupmodeforce) ? $cm->groupmode : $COURSE->groupmode;
        } else {
            $groupmode = 0;
        }
        if ($useadvancedpermissions) {
            // -------------------------------------------------------------------------------
            $fieldname = 'access';
            $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

            // note about access rights
            $fieldname = 'accessrightsnote';
            // prepare access right
            if (!empty($groupmode)) {
                $mform->addElement('static', $fieldname, 'Note:', get_string($fieldname.'_group', 'survey'));
                $subjects = array('none', 'owner', 'group', 'all');
            } else {
                $mform->addElement('static', $fieldname, get_string('note', 'survey'), get_string($fieldname.'_nogroup', 'survey'));
                $subjects = array('none', 'owner', 'all');
            }

            // access right
            $ro_label = get_string('readonly', 'survey');
            $rw_label = get_string('readwrite', 'survey');
            $del_label = get_string('delete', 'survey');

            $accessrights = array();
            $i = ($groupmode) ? SURVEY_ALL : SURVEY_GROUP;
            while ($i >= SURVEY_NONE) {
                $j = $i;
                while ($j >= SURVEY_NONE) {
                    $k = $j;
                    while ($k >= SURVEY_NONE) {
                        $index = $i.'.'.$j.'.'.$k;
                        $index = ($groupmode) ? $index : str_replace(SURVEY_GROUP, SURVEY_ALL, $index);
                        $accessrights[$index] = $ro_label.': '.$subjects[$i].', '.$rw_label.': '.$subjects[$j].', '.$del_label.': '.$subjects[$k];
                        $k--;
                    }
                    $j--;
                }
                $i--;
            }

            $fieldname = 'accessrights';
            $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $accessrights);
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
        }

        // -------------------------------------------------------------------------------
        $fieldname = 'dataentry';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'survey'));

        // newpageforchild
        $fieldname = 'newpageforchild';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // allow/deny saveresume
        $fieldname = 'saveresume';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // history
        $fieldname = 'history';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // allow/deny anonymous
        $fieldname = 'anonymous';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // recaptcha
        if (survey_site_recaptcha_enabled()) {
            $fieldname = 'captcha';
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
        }

        // startyear
        $boundaryyear = array_combine(range(1655, 2285), range(1655, 2285));

        $fieldname = 'startyear';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $boundaryyear);
        $mform->setDefault($fieldname, SURVEY_MINEVERYEAR);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // stopyear
        $fieldname = 'stopyear';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $boundaryyear);
        $mform->setDefault($fieldname, SURVEY_MAXEVERYEAR);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // userstyle
        $fieldname = 'userstyle';
        $filemanager_options = survey_get_user_style_options();
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'survey'), null, $filemanager_options);
        $mform->addHelpButton($fieldname.'_filemanager', $fieldname, 'survey');

        // maxentries
        $fieldname = 'maxentries';
        $countoptions = array(0 => get_string('unlimited', 'survey'))+
                        (array_combine(range(1, SURVEY_MAX_ENTRIES),   // keys
                                       range(1, SURVEY_MAX_ENTRIES))); // values
        $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $countoptions);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // notifyrole
        $fieldname = 'notifyrole';
        $options = array();
        $context = context_course::instance($COURSE->id);
        $roles = get_roles_used_in_context($context);
        $guestrole = get_guest_role();
        $roles[$guestrole->id] = $guestrole;
        $roleoptions = role_fix_names($roles, $context, ROLENAME_ALIAS, true);
        foreach ($roleoptions as $roleid => $rolename) {
            $options[$roleid] = $rolename;
        }
        $select = $mform->addElement('select', $fieldname, get_string($fieldname, 'survey'), $options);
        $select->setMultiple(true);
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // notifymore
        $fieldname = 'notifymore';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'survey'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'survey');

        // define thanks page
        $fieldname = 'thankshtml';
        // $context = context_course::instance($COURSE->id); <-- just defined 20 rows above
        $editoroptions = survey_get_editor_options();
        $mform->addElement('editor', $fieldname.'_editor', get_string($fieldname, 'survey'), null, $editoroptions);
        $mform->addHelpButton($fieldname.'_editor', $fieldname, 'survey');
        $mform->setType($fieldname.'_editor', PARAM_RAW); // no XSS prevention here, users must be trusted

        $allowalwaysediting = get_config('survey', 'allowalwaysediting');
        if ($allowalwaysediting) {
            // forceediting
            $fieldname = 'forceediting';
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'survey'));
            $mform->addHelpButton($fieldname, $fieldname, 'survey');
        }

        // -------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();

        // -------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }

    // questa funzione viene eseguita dopo aver mostrato la mod_form
    // e serve per preparare i dati al salvataggio
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        $data->thankshtmlformat = $data->thankshtml_editor['format'];
        $data->thankshtml = $data->thankshtml_editor['text'];

        // notifyrole
        if (isset($data->notifyrole)) {
            $data->notifyrole = implode($data->notifyrole, ',');
        } else {
            $data->notifyrole = '';
        }

        // Turn off completion settings if the checkboxes aren't ticked
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && ($data->completion == COMPLETION_TRACKING_AUTOMATIC);
            if (empty($data->completionsubmit_check) || !$autocompletion) {
                $data->completionsubmit = 0;
            }
        }

        return $data;
    }

    // questa funzione viene eseguita prima di mostrare la mod_form
    // e serve per definire eventuali preset
    public function data_preprocessing(&$default_values) {
        global $DB;

        parent::data_preprocessing($default_values);

        if (isset($default_values['readaccess'])) { // if one has been set, then all of them have been set
            $default_values['accessrights'] = $default_values['readaccess'].'.'.$default_values['editaccess'].'.'.$default_values['deleteaccess'];
        }

        if ($this->current->instance) {
            // manage userstyle filemanager
            $filename = 'userstyle';
            $filemanager_options = survey_get_user_style_options();
            $draftitemid = file_get_submitted_draft_itemid($filename.'_filemanager');

            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_survey', SURVEY_STYLEFILEAREA, 0, $filemanager_options);
            $default_values[$filename.'_filemanager'] = $draftitemid;

            // manage thankshtml editor
            $filename = 'thankshtml';
            $editoroptions = survey_get_editor_options();
            // editing an existing feedback - let us prepare the added editor elements (intro done automatically)
            $draftitemid = file_get_submitted_draft_itemid($filename);
            $default_values[$filename.'_editor']['text'] =
                                    file_prepare_draft_area($draftitemid, $this->context->id,
                                    'mod_survey', SURVEY_THANKSHTMLFILEAREA, false,
                                    $editoroptions,
                                    $default_values[$filename]);

            $default_values[$filename.'_editor']['format'] = $default_values['thankshtmlformat'];
            $default_values[$filename.'_editor']['itemid'] = $draftitemid;

            // notifyrole
            $presetroles = explode(',', $default_values['notifyrole']);
            foreach ($presetroles as $roleid) {
                $values[] = $roleid;
            }
            $default_values['notifyrole'] = $values;
        }

        $fieldname = 'completionsubmit';
        $default_values[$fieldname.'_check'] = !empty($default_values[$fieldname]) ? 1 : 0;
        if (empty($default_values[$fieldname])) {
            $default_values[$fieldname] = 1;
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        $fieldname = 'completionsubmit';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('checkbox', $fieldname.'_check', '', get_string($fieldname.'_check', 'survey'));
        $elementgroup[] = $mform->createElement('text', $fieldname, '', array('size' => 3));
        $mform->setType($fieldname, PARAM_INT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname.'_group', 'survey'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname.'_group', 'survey');
        $mform->disabledIf($fieldname, $fieldname.'_check', 'notchecked');

        return array($fieldname.'_group');
    }

    public function completion_rule_enabled($data) {
        return (!empty($data['completionsubmit_check']) && ($data['completionsubmit'] != 0));
    }
}
