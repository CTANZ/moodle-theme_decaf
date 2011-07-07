<?php

/**
 * get_performance_output() override get_peformance_info()
 *  in moodlelib.php. Returns a string
 * values ready for use.
 *
 * @return string
 */
function decaf_performance_output($param) {
	
    $html = '<div class="performanceinfo"><ul>';
	if (isset($param['realtime'])) $html .= '<li><a class="red" href="#"><var>'.$param['realtime'].' secs</var><span>Load Time</span></a></li>';
	if (isset($param['memory_total'])) $html .= '<li><a class="orange" href="#"><var>'.display_size($param['memory_total']).'</var><span>Memory Used</span></a></li>';
    if (isset($param['includecount'])) $html .= '<li><a class="blue" href="#"><var>'.$param['includecount'].' Files </var><span>Included</span></a></li>';
    if (isset($param['dbqueries'])) $html .= '<li><a class="purple" href="#"><var>'.$param['dbqueries'].' </var><span>DB Read/Write</span></a></li>';
    $html .= '</ul></div>';

    return $html;
}

/**
 * Makes our changes to the CSS
 *
 * @param string $css
 * @param theme_config $theme
 * @return string
 */
function decaf_process_css($css, $theme) {

    if (!empty($theme->settings->backgroundcolor)) {
        $backgroundcolor = $theme->settings->backgroundcolor;
    } else {
        $backgroundcolor = null;
    }
    $css = decaf_set_backgroundcolor($css, $backgroundcolor);

    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = decaf_set_customcss($css, $customcss);

    return $css;
}

/**
 * Sets the background colour variable in CSS
 *
 * @param string $css
 * @param mixed $backgroundcolor
 * @return string
 */
function decaf_set_backgroundcolor($css, $backgroundcolor) {
    $tag = '[[setting:backgroundcolor]]';
    $replacement = $backgroundcolor;
    if (is_null($replacement)) {
        $replacement = '#EEEEEE';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Sets the custom css variable in CSS
 *
 * @param string $css
 * @param mixed $customcss
 * @return string
 */
function decaf_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}


class decaf_expand_navigation extends global_navigation {

    /** @var array */
    protected $expandable = array();

    /**
     * Constructs the navigation for use in AJAX request
     */
    public function __construct($page, $branchtype, $id) {
        $this->page = $page;
        $this->cache = new navigation_cache(NAVIGATION_CACHE_NAME);
        $this->children = new navigation_node_collection();
        $this->initialise($branchtype, $id);
    }
    /**
     * Initialise the navigation given the type and id for the branch to expand.
     *
     * @param int $branchtype One of navigation_node::TYPE_*
     * @param int $id
     * @return array The expandable nodes
     */
    public function initialise($branchtype, $id) {
        global $CFG, $DB, $SITE;

        if ($this->initialised || during_initial_install()) {
            return $this->expandable;
        }
        $this->initialised = true;

        $this->rootnodes = array();
        $this->rootnodes['site']      = $this->add_course($SITE);
        $this->rootnodes['courses'] = $this->add(get_string('courses'), null, self::TYPE_ROOTNODE, null, 'courses');

        // Branchtype will be one of navigation_node::TYPE_*
        switch ($branchtype) {
            case self::TYPE_CATEGORY :
                $this->load_all_categories($id);
                $limit = 20;
                if (!empty($CFG->navcourselimit)) {
                    $limit = (int)$CFG->navcourselimit;
                }
                $courses = $DB->get_records('course', array('category' => $id), 'sortorder','*', 0, $limit);
                foreach ($courses as $course) {
                    $this->add_course($course);
                }
                break;
            case self::TYPE_COURSE :
                $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
                //require_course_login($course);
                //$this->page = $PAGE;
                $this->page->set_context(get_context_instance(CONTEXT_COURSE, $course->id));
                $coursenode = $this->add_course($course);
                $this->add_course_essentials($coursenode, $course);
                if ($this->format_display_course_content($course->format)) {
                    $this->load_course_sections($course, $coursenode);
                }
                break;
            case self::TYPE_SECTION :
                $sql = 'SELECT c.*, cs.section AS sectionnumber
                        FROM {course} c
                        LEFT JOIN {course_sections} cs ON cs.course = c.id
                        WHERE cs.id = ?';
                $course = $DB->get_record_sql($sql, array($id), MUST_EXIST);
                //require_course_login($course);
                //$this->page = $PAGE;
                $this->page->set_context(get_context_instance(CONTEXT_COURSE, $course->id));
                $coursenode = $this->add_course($course);
                $this->add_course_essentials($coursenode, $course);
                $sections = $this->load_course_sections($course, $coursenode);
                if(method_exists($this,'generate_sections_and_activities')) {
                    list($sectionarray, $activities) = $this->generate_sections_and_activities($course);
                    $this->load_section_activities($sections[$course->sectionnumber]->sectionnode, $course->sectionnumber, $activities);
                } else {
                    // pre-Moodle 2.1
                    $this->load_section_activities($sections[$course->sectionnumber]->sectionnode, $course->sectionnumber, get_fast_modinfo($course));
                }
                break;
            case self::TYPE_ACTIVITY :
                if(method_exists($this,'generate_sections_and_activities')) {
                    $sql = "SELECT c.*
                              FROM {course} c
                              JOIN {course_modules} cm ON cm.course = c.id
                             WHERE cm.id = :cmid";
                    $params = array('cmid' => $id);
                    $course = $DB->get_record_sql($sql, $params, MUST_EXIST);
                    $modinfo = get_fast_modinfo($course);
                    $cm = $modinfo->get_cm($id);
                } else {
                    // pre-Moodle 2.1
                    $cm = get_coursemodule_from_id(false, $id, 0, false, MUST_EXIST);
                    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
                }
                //require_course_login($course, true, $cm);
                //$this->page = $PAGE;
                $this->page->set_context(get_context_instance(CONTEXT_MODULE, $cm->id));
                $coursenode = $this->load_course($course);
                if(!method_exists($this,'generate_sections_and_activities')) {
                    // pre-Moodle 2.1
                    $sections = $this->load_course_sections($course, $coursenode);
                    foreach ($sections as $section) {
                        if ($section->id == $cm->section) {
                            $cm->sectionnumber = $section->section;
                            break;
                        }
                    }
                }
                if ($course->id == SITEID) {
                    $modulenode = $this->load_activity($cm, $course, $coursenode->find($cm->id, self::TYPE_ACTIVITY));
                } else {
                    if(method_exists($this,'generate_sections_and_activities')) {
                        $sections   = $this->load_course_sections($course, $coursenode);
                        list($sectionarray, $activities) = $this->generate_sections_and_activities($course);
                        $activities = $this->load_section_activities($sections[$cm->sectionnum]->sectionnode, $cm->sectionnum, $activities);
                    } else {
                        // pre-Moodle 2.1
                        $activities = $this->load_section_activities($sections[$cm->sectionnumber]->sectionnode, $cm->sectionnumber, get_fast_modinfo($course));
                    }
                    $modulenode = $this->load_activity($cm, $course, $activities[$cm->id]);
                }
                break;
            default:
                throw new Exception('Unknown type');
                return $this->expandable;
        }
        $this->find_expandable($this->expandable);
        return $this->expandable;
    }

    public function get_expandable() {
        return $this->expandable;
    }
}

class decaf_dummy_page extends moodle_page {
    /**
     * REALLY Set the main context to which this page belongs.
     * @param object $context a context object, normally obtained with get_context_instance.
     */
    public function set_context($context) {
        if ($context === null) {
            // extremely ugly hack which sets context to some value in order to prevent warnings,
            // use only for core error handling!!!!
            if (!$this->_context) {
                $this->_context = get_context_instance(CONTEXT_SYSTEM);
            }
            return;
        }
        $this->_context = $context;
    }
}