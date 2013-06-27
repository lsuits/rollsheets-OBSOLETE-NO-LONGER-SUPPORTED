<?php

// Written at Louisiana State University

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('create_form.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->libdir . '/grouplib.php');

require_login();

$_s = function($key) { return get_string($key, 'block_rollsheets'); };

/**
 * Generates an FDF file representing a given section's rollsheet
 * $course_name is to be of format: CSC 4330 001
 * $student_names is to be an array of student names, last, first middle
 */
function create_rollsheet($course_name, $section_name, $student_names) {
    global $CFG;
    $out  = "%FDF-1.2\r\n";
    $out .= "1 0 obj\r\n";
    $out .= "<</FDF\r\n";
    $out .= '<< /Fields[<</T(Course) /V(' . $course_name . ' - ' . $section_name;
    $out .= ')>><</T(Professor) /V()>>';

    if(count($student_names)) {
        // Fill fields with student names
        foreach($student_names as $key => $student_name) {
            $num = $key + 1;
            $out .= '<</T(NameField' . $num . ') /V(' . $student_name;
            $out .= ')>><</T(Row' . $num . ') /V(' . $num . ')>>';
        }
    } else {
        $out .= '<</T(NameField1) /V(There are no users enrolled in this ';
        $out .= 'section)>><</T(Row1) /V(1)>>';
    }

    $out .= "]\r\n";
    $out .= "/F(" . $CFG->wwwroot . "/blocks/rollsheets/RollSheet.pdf)>>\r\n";
    $out .= ">>\r\n";
    $out .= "endobj\r\n";
    $out .= "trailer\r\n";
    $out .= "<<\r\n";
    $out .= "/Root 1 0 R\r\n";
    $out .= ">>\r\n";
    $out .= "%%EOF";

    return $out;
}

$courseid = required_param('id', PARAM_INT);

$context = get_context_instance(CONTEXT_COURSE, $courseid);

require_capability('moodle/course:update', $context);

$course = $DB->get_record('course', array('id' => $courseid));
$groups = $DB->get_records('groups', array('courseid' => $courseid));

$header = $_s('create_rollsheets');
$pluginname = $_s('pluginname');

$PAGE->set_context(get_context_instance(CONTEXT_COURSE, $course->id));
$PAGE->set_course($course);

$PAGE->navbar->add($pluginname);
$PAGE->navbar->add($header);

$PAGE->set_title($pluginname . ': ' . $header);
$PAGE->set_heading($pluginname . ': ' . $header);

$PAGE->set_url('/blocks/rollsheets/create.php?id=' . $course->id);
$PAGE->set_pagetype($pluginname);

$form = new create_form();

if ($form->is_cancelled()) {
    redirect($CFG->wwwroot);
} else if ($data = $form->get_data()) {
    foreach($data as $key => $value) {
        $matches = array();
        if(preg_match(CREATE_ROLLSHEET_PATTERN, $key, $matches) && $groups[$matches[1]]) {
            if (!$context = get_context_instance(CONTEXT_COURSE, $course->id)){
                print_error('wrong_context', 'block_rollsheets');
            }

            $course_name = $course->shortname;
            $section_name = explode(' ', groups_get_group_name($matches[1]));

            // Get role and member information to filter out non-students
            $members = groups_get_members($matches[1]);

            $all_students = array();

            foreach (explode(',', $CFG->gradebookroles) as $roleid) {
                $role_users = get_role_users($roleid, $context, false, 'u.id');
                $all_students += array_keys($role_users);
            }

            $student_names = array();

            // Filter out non-students
            foreach ($members as $member) {
                if (in_array($member->id, $all_students)) {
                    $student_names[] = $member->lastname . ', ' . $member->firstname;
                }
            }

            sort($student_names);
            
            header("Content-type: application/vnd.fdf");
            header("Content-Disposition: attachment; filename=rollsheet.fdf");
            echo create_rollsheet($course_name, $section_name[2], $student_names);
            
            die();
        }
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

$form->display();

echo $OUTPUT->footer();
