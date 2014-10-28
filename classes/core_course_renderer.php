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
}
