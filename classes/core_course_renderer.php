<?php
/**
 * Core course renderer for Decaf.
 *
 * @package    theme_decaf
 * @copyright  2014 Paul Nicholls
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_decaf_core_course_renderer extends core_course_renderer {

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm_list_item($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        global $PAGE, $USER;

        // Enable editing if we're in persistent editing mode and not "really editing".
        $wasediting = (empty($USER->editing) ? false : $USER->editing);
        if (!empty($this->page->theme->settings->persistentedit) && $this->page->user_allowed_editing() && !$wasediting) {
            $USER->editing = 1;
        }

        $output = parent::course_section_cm_list_item($course, $completioninfo, $mod, $sectionreturn, $displayoptions);

        // Set editing flag back to what it was before we did this.
        if ($this->page->user_allowed_editing()) {
            $USER->editing = $wasediting;
        }

        return $output;
    }

    /**
     * Return the HTML for the specified module adding any required classes
     *
     * @param object $module An object containing the title, and link. An
     * icon, and help text may optionally be specified. If the module
     * contains subtypes in the types option, then these will also be
     * displayed.
     * @param array $classes Additional classes to add to the encompassing
     * div element
     * @return string The composed HTML for the module
     */
    protected function course_modchooser_module($module, $classes = array('option')) {
        $output = '';
        $output .= html_writer::start_tag('div', array('class' => implode(' ', $classes)));
        $output .= html_writer::start_tag('label', array('for' => 'module_' . $module->name));
        if (!isset($module->types)) {
            $output .= html_writer::tag('input', '', array('type' => 'radio',
                    'name' => 'jumplink', 'id' => 'module_' . $module->name, 'value' => $module->link));
        }

        $attributes = array('class' => 'modicon');
        if (isset($module->icon)) {
            // Add an icon if we have one
            $attributes['style'] = 'background-image:url('.$this->pix_url('icon', $module->name).');';
        }
        $output .= html_writer::tag('span', '', $attributes);

        $output .= html_writer::tag('span', $module->title, array('class' => 'typename'));
        if (!isset($module->help)) {
            // Add help if found
            $module->help = get_string('nohelpforactivityorresource', 'moodle');
        }

        // Format the help text using markdown with the following options
        $options = new stdClass();
        $options->trusted = false;
        $options->noclean = false;
        $options->smiley = false;
        $options->filter = false;
        $options->para = true;
        $options->newlines = false;
        $options->overflowdiv = false;
        $module->help = format_text($module->help, FORMAT_MARKDOWN, $options);
        $output .= html_writer::tag('span', $module->help, array('class' => 'typesummary'));
        $output .= html_writer::end_tag('label');
        $output .= html_writer::end_tag('div');

        return $output;
    }
}
