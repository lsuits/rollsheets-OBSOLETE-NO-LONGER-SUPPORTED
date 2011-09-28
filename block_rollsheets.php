<?php

// Written at Louisiana State University

class block_rollsheets extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_rollsheets');
    }

    function applicable_formats() {
        return array('site' => false, 'my' => false, 'course' => true);
    }

    function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        global $CFG, $COURSE, $OUTPUT;

        $this->content = new stdclass;
        $this->content->icons = array();
        $this->content->items = array();
        $this->content->footer = '';

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if (!has_capability('moodle/course:update', $context)) {
            return $this->content;
        }

        $label = get_string('create', 'block_rollsheets');

        $params = array('id' => $COURSE->id);
        $url = new moodle_url('/blocks/rollsheets/create.php', $params);

        $create_link = html_writer::link($url, $label);

        $help = $OUTPUT->help_icon('pluginname', 'block_rollsheets');

        $this->content->items[] = $create_link . $help;

        return $this->content;
    }
}
