<?php

// Written at Louisiana State University

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->libdir . '/grouplib.php');

// Prefix used for section buttons
define('CREATE_ROLLSHEET_BUTTON', 'create_rollsheet_for_section');

// Button pattern
define('CREATE_ROLLSHEET_PATTERN', '/^' . CREATE_ROLLSHEET_BUTTON . '(\d+)$/');

class create_form extends moodleform {
    function definition() {
        global $CFG, $DB, $OUTPUT;
        global $courseid, $_s;;

        $form =& $this->_form;

        $groups = $DB->get_records('groups', array('courseid' => $courseid), 'name ASC');

        $icon = $OUTPUT->pix_icon('f/pdf', 'pdf');

        $form->addElement('hidden', 'id', $courseid);
        $form->setType('id',PARAM_INT);

        $form->addElement('header', 'sections', $_s('sections'));

        foreach($groups as $group) {
            $row = array();
            $row[] =& $form->createElement('static', 'static',  $group->id, $icon . ' ' .  $group->name . ' ');
            $row[] =& $form->createElement('submit', CREATE_ROLLSHEET_BUTTON . $group->id, $_s('create'));

            $form->addGroup($row, 'group', '', array(' '), false);
        }
    }
}
