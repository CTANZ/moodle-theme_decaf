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

/**
 * Responsive Decaf theme for Moodle 2.6 and above.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_decaf
 * @copyright 2014 Paul Nicholls
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialises various Decaf options.  $PAGE thinks that output has already started when this is called,
 * so we need to create a list of supplementary body classes in case various Decaf options are enabled.
 */
function theme_decaf_init() {
    global $CFG, $PAGE, $USER, $OUTPUT;
    $decaf = clone $PAGE->theme->settings;
    $decaf->bodyclasses = array();
    $decaf->awesome_nav = '';
    $decaf->awesome_settings = '';

    if (!empty($decaf->alwaysexpandsiteadmin) || !empty($USER->profile['decafFullAdminTree'])) {
        navigation_node::require_admin_tree();
    }

    // Initialise and generate Awesomebar before handling Persistent Edit mode.
    if (empty($PAGE->layout_options['noawesomebar'])) {
        // Ensure that navigation has been initialised properly, in case Navigation block is not visible.
        $PAGE->navigation->initialise();
        $PAGE->requires->yui_module('moodle-theme_decaf-awesomebar', 'M.theme_decaf.initAwesomeBar');
        $decaf->topsettings = $PAGE->get_renderer('theme_decaf','topsettings');
        $decaf->awesome_nav = $decaf->topsettings->navigation_tree($PAGE->navigation);
        $decaf->awesome_settings = $decaf->topsettings->settings_tree($PAGE->settingsnav);
        if (!strlen($decaf->awesome_nav) && !strlen($decaf->awesome_settings)) {
            if (!$decaf->custommenuinawesomebar || !(empty($PAGE->layout_options['nocustommenu']) && strlen($OUTPUT->custom_menu()))) {
                // No Awesomebar content - hide it.
                $decaf->bodyclasses[] = 'decaf_no_awesomebar';
            }
        }
    } else {
        $decaf->bodyclasses[] = 'decaf_no_awesomebar';
    }

    if(!empty($decaf->persistentedit) || !empty($USER->profile['decafPersistentEdit'])) {
        if(property_exists($USER, 'editing') && $USER->editing) {
            $OUTPUT->set_really_editing(true);
        }
        if ($PAGE->user_allowed_editing()) {
            $USER->editing = 1;
            $decaf->bodyclasses[] = 'decaf_persistent_edit';
            if (!$OUTPUT->is_really_editing()) {
                // Persistent editing mode - initialise action menus since core won't.
                $PAGE->requires->yui_module('moodle-core-actionmenu', 'M.core.actionmenu.init');
            }
        }
    }

    if(!empty($decaf->usemodchoosertiles)) {
        $decaf->bodyclasses[] = 'decaf_modchooser_tiles';
    }

    // Initialise group mode fix for action menu if applicable.
    if (!empty($CFG->modeditingmenu)) {
        $decaf->bodyclasses[] = 'decaf_with_actionmenus';
        $PAGE->requires->yui_module('moodle-theme_decaf-actionmenu', 'M.theme_decaf.initActionMenu');
    }

    // Add classes used to account for perf/page info fixed to bottom of screen.
    if (!empty($CFG->perfdebug) && $CFG->perfdebug > 7) {
        $decaf->bodyclasses[] = 'decaf_perfdebug';
    }
    if (!empty($CFG->debugpageinfo)) {
        $decaf->bodyclasses[] = 'decaf_pagedebug';
    }

    // Initialise "Back to Top" button JS.
    $PAGE->requires->yui_module('moodle-theme_decaf-backtotop', 'M.theme_decaf.initBackToTop');

    return $decaf;
}

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_decaf_process_css($css, $theme) {

    // Set the page background colour.
    if (!empty($theme->settings->backgroundcolor)) {
        $backgroundcolor = $theme->settings->backgroundcolor;
    } else {
        $backgroundcolor = null;
    }
    $css = theme_decaf_set_backgroundcolor($css, $backgroundcolor);

    // Set the background image for the logo.
    $logo = $theme->setting_file_url('logo', 'logo');
    $css = theme_decaf_set_logo($css, $logo);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_decaf_set_customcss($css, $customcss);

    return $css;
}

/**
 * Adds the logo to CSS.
 *
 * @param string $css The CSS.
 * @param string $logo The URL of the logo.
 * @return string The parsed CSS
 */
function theme_decaf_set_logo($css, $logo) {
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Sets the background colour variable in CSS.
 *
 * @param string $css
 * @param mixed $backgroundcolor
 * @return string
 */
function theme_decaf_set_backgroundcolor($css, $backgroundcolor) {
    $tag = '[[setting:backgroundcolor]]';
    $replacement = $backgroundcolor;
    if (is_null($replacement)) {
        $replacement = '#EEEEEE';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_decaf_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'logo') {
        $theme = theme_config::load('decaf');
        return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_decaf_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_decaf_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    if (!empty($page->theme->settings->logo)) {
        $return->heading = html_writer::link($CFG->wwwroot, '', array('title' => get_string('home'), 'class' => 'logo'));
    } else {
        $return->heading = $output->page_heading();
    }

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote text-center">'.$page->theme->settings->footnote.'</div>';
    }

    return $return;
}

/**
 * All theme functions should start with theme_decaf_
 * @deprecated since 2.5.1
 */
function decaf_process_css() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_decaf_
 * @deprecated since 2.5.1
 */
function decaf_set_logo() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_decaf_
 * @deprecated since 2.5.1
 */
function decaf_set_customcss() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * get_performance_output() override get_peformance_info()
 *  in moodlelib.php. Returns a string
 * values ready for use.
 *
 * @return string
 */
function theme_decaf_performance_output($param) {

    $html = '<div class="performanceinfo"><ul>';
    if (isset($param['realtime'])) $html .= '<li><a class="red" href="#"><var>'.$param['realtime'].' secs</var><span>Load Time</span></a></li>';
    if (isset($param['memory_total'])) $html .= '<li><a class="orange" href="#"><var>'.display_size($param['memory_total']).'</var><span>Memory Used</span></a></li>';
    if (isset($param['includecount'])) $html .= '<li><a class="blue" href="#"><var>'.$param['includecount'].' Files </var><span>Included</span></a></li>';
    if (isset($param['dbqueries'])) $html .= '<li><a class="purple" href="#"><var>'.$param['dbqueries'].' </var><span>DB Read/Write</span></a></li>';
    $html .= '</ul></div>';

    return $html;
}

class theme_decaf_dummy_page extends moodle_page {
    /**
     * REALLY Set the main context to which this page belongs.
     * @param object $context a context object, normally obtained with context_XXX::instance.
     */
    public function set_context($context) {
        if ($context === null) {
            // extremely ugly hack which sets context to some value in order to prevent warnings,
            // use only for core error handling!!!!
            if (!$this->_context) {
                $this->_context = context_system::instance();
            }
            return;
        }
        $this->_context = $context;
    }
}

class theme_decaf_expand_navigation extends global_navigation {

    /** @var array */
    protected $expandable = array();
    private $expandtocourses = true;
    private $expandedcourses = array();

    /**
     * Constructs the navigation for use in AJAX request
     */
    public function __construct($page, $branchtype, $id) {
        $this->page = $page;
        $this->cache = new navigation_cache(NAVIGATION_CACHE_NAME);
        $this->children = new navigation_node_collection();
        $this->branchtype = $branchtype;
        $this->instanceid = $id;
        $this->initialise();
    }
    /**
     * Initialise the navigation given the type and id for the branch to expand.
     *
     * @param int $branchtype One of navigation_node::TYPE_*
     * @param int $id
     * @return array The expandable nodes
     */
    public function initialise() {
        global $CFG, $DB, $SITE, $PAGE;

        if ($this->initialised || during_initial_install()) {
            return $this->expandable;
        }
        $this->initialised = true;

        $this->rootnodes = array();
        $this->rootnodes['site']      = $this->add_course($SITE);
        $this->rootnodes['currentcourse'] = $this->add(get_string('currentcourse'), null, self::TYPE_ROOTNODE, null, 'currentcourse');
        $this->rootnodes['mycourses'] = $this->add(get_string('mycourses'), new moodle_url('/my'), self::TYPE_ROOTNODE, null, 'mycourses');
        $this->rootnodes['courses'] = $this->add(get_string('courses'), null, self::TYPE_ROOTNODE, null, 'courses');

        if (!empty($PAGE->theme->settings->coursesleafonly) || (!empty($PAGE->theme->settings->coursesloggedinonly) && !isloggedin())) {
            $this->expandtocourses = false;
        }

        if(function_exists('enrol_user_sees_own_courses')) {
            // Determine if the user is enrolled in any course.
            $enrolledinanycourse = enrol_user_sees_own_courses();

            if ($enrolledinanycourse) {
                $this->rootnodes['mycourses']->isexpandable = true;
                if ($CFG->navshowallcourses) {
                    // When we show all courses we need to show both the my courses and the regular courses branch.
                    $this->rootnodes['courses']->isexpandable = true;
                }
            } else {
                $this->rootnodes['courses']->isexpandable = true;
            }
        }

        $PAGE->requires->data_for_js('siteadminexpansion', false);

        $this->expand($this->branchtype, $this->instanceid);
        $this->expand(self::TYPE_ROOTNODE, 'mycourses'); // Force expansion of "My Courses" branch.
    }

    public function expand($branchtype, $id) {
        global $CFG, $DB, $PAGE, $USER;
        static $decaf_course_activities = array();
        // Branchtype will be one of navigation_node::TYPE_*
        switch ($branchtype) {
            case self::TYPE_ROOTNODE :
            case self::TYPE_SITE_ADMIN :
                if ($id === 'mycourses') {
                    $this->rootnodes['mycourses']->isexpandable = true;
                    $this->load_courses_enrolled();
                } else if ($id === 'courses') {
                    if ($this->expandtocourses) {
                        $this->rootnodes['courses']->isexpandable = true;
                        $this->load_courses_other();
                    } else {
                        // Don't load courses - theme settings say we shouldn't.
                        $this->rootnodes['courses']->isexpandable = false;
                        $this->rootnodes['courses']->nodetype = self::NODETYPE_LEAF;
                    }
                }
                break;
            case self::TYPE_CATEGORY :
            case self::TYPE_MY_CATEGORY :
                if (!empty($PAGE->theme->settings->coursesleafonly)) {
                    return false;
                }
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
                try {
                    if(!array_key_exists($course->id, $this->expandedcourses)) {
                        $coursenode = $this->add_course($course);
                        if (!$coursenode) {
                            break;
                        }
                        if ($PAGE->course->id !== $course->id) {
                            $coursenode->nodetype = navigation_node::NODETYPE_LEAF;
                            $coursenode->isexpandable = false;
                            break;
                        }
                        $coursecontext = context_course::instance($course->id);
                        $this->page->set_context($coursecontext);
                        $this->add_course_essentials($coursenode, $course);
                        if ($PAGE->course->id == $course->id && (!method_exists($this, 'format_display_course_content') || $this->format_display_course_content($course->format))) {
                            if (is_enrolled($coursecontext, $USER, '', true)) {
                                $this->expandedcourses[$course->id] = $this->expand_course($course, $coursenode);
                            }
                        }
                    }
                } catch(require_login_exception $rle) {
                    $coursenode = $this->add_course($course);
                }
                break;
            case self::TYPE_SECTION :
                $sql = 'SELECT c.*, cs.section AS sectionnumber
                        FROM {course} c
                        LEFT JOIN {course_sections} cs ON cs.course = c.id
                        WHERE cs.id = ?';
                $course = $DB->get_record_sql($sql, array($id), MUST_EXIST);
                try {
                    $this->page->set_context(context_course::instance($course->id));
                    if(!array_key_exists($course->id, $this->expandedcourses)) {
                        $coursenode = $this->add_course($course);
                        if (!$coursenode) {
                            break;
                        }
                        $this->add_course_essentials($coursenode, $course);
                        $this->expandedcourses[$course->id] = $this->expand_course($course, $coursenode);
                    }
                    if (property_exists($PAGE->theme->settings, 'expandtoactivities') && $PAGE->theme->settings->expandtoactivities) {
                        if(!array_key_exists($course->id, $decaf_course_activities)) {
                            list($sectionarray, $activities) = $this->generate_sections_and_activities($course);
                            $decaf_course_activities[$course->id] = $activities;
                        }
                        $sections = $this->expandedcourses[$course->id];
                        $activities = $decaf_course_activities[$course->id];

                        if (!array_key_exists($course->sectionnumber, $sections)) break;
                        $section = $sections[$course->sectionnumber];
                        if (is_null($section) || is_null($section->sectionnode)) break;
                        $activitynodes = $this->load_section_activities($section->sectionnode, $course->sectionnumber, $activities);
                        foreach ($activitynodes as $id=>$node) {
                            // load all section activities now
                            $cm_stub = new stdClass();
                            $cm_stub->id = $id;
                            $this->load_activity($cm_stub, $course, $node);
                        }
                    }
                } catch(require_login_exception $rle) {
                    $coursenode = $this->add_course($course);
                }
                break;
            case self::TYPE_ACTIVITY :
                // Now expanded above, as part of the section expansion
                break;
            default:
                throw new Exception('Unknown type');
                return $this->expandable;
        }
        return $this->expandable;
    }
    private function expand_course(stdClass $course, navigation_node $coursenode) {
        $sectionnodes = $this->load_course_sections($course, $coursenode);
        if (empty($sectionnodes)) {
            // 2.4 compat - load_course_sections no longer returns the list of sections
            $sectionnodes = array();
            foreach ($coursenode->children as $node) {
                if($node->type != self::TYPE_SECTION) continue;
                $section = new stdClass();
                $section->sectionnode = $node;
                // Get section number out of action URL (it doesn't seem to be available elsewhere!)
                $hasparams = false;
                if (!empty($node->action) && $params = $node->action->params()){
                    $hasparams = true;
                }
                if ($hasparams && array_key_exists('section', $params)) {
                    $sectionnodes[$params['section']] = $section;
                } else {
                    $sectionnodes[] = $section;
                }
            }
            $expanded = $sectionnodes;
        }
        return $sectionnodes;
    }

    /**
     * Loads all of the activities for a section into the navigation structure.
     *
     * @param navigation_node $sectionnode
     * @param int $sectionnumber
     * @param array $activities An array of activites as returned by {@link global_navigation::generate_sections_and_activities()}
     * @param stdClass $course The course object the section and activities relate to.
     * @return array Array of activity nodes
     */
    protected function load_section_activities(navigation_node $sectionnode, $sectionnumber, array $activities, $course = null) {
        global $CFG, $SITE;
        // A static counter for JS function naming
        static $legacyonclickcounter = 0;

        $activitynodes = array();
        if (empty($activities)) {
            return $activitynodes;
        }

        if (!is_object($course)) {
            $activity = reset($activities);
            $courseid = $activity->course;
        } else {
            $courseid = $course->id;
        }
        $showactivities = ($courseid != $SITE->id || !empty($CFG->navshowfrontpagemods));

        foreach ($activities as $activity) {
            if ($activity->section != $sectionnumber) {
                continue;
            }
            if ($activity->icon) {
                $icon = new pix_icon($activity->icon, get_string('modulename', $activity->modname), $activity->iconcomponent);
            } else {
                $icon = new pix_icon('icon', get_string('modulename', $activity->modname), $activity->modname);
            }

            // Prepare the default name and url for the node
            $activityname = format_string($activity->name, true, array('context' => context_module::instance($activity->id)));
            $action = new moodle_url($activity->url);

            // Legacy onclick removed from Decaf - clicking in Awesomebar should go to the page, not trigger popups etc.

            $activitynode = $sectionnode->add($activityname, $action, navigation_node::TYPE_ACTIVITY, null, $activity->id, $icon);
            $activitynode->title(get_string('modulename', $activity->modname));
            $activitynode->hidden = $activity->hidden;
            $activitynode->display = $showactivities && $activity->display;
            $activitynode->nodetype = $activity->nodetype;
            $activitynodes[$activity->id] = $activitynode;
        }

        return $activitynodes;
    }

    /**
     * They've expanded the 'my courses' branch.
     */
    protected function load_courses_enrolled() {
        global $DB;

        static $expandedmycourses = false;
        if ($expandedmycourses) {
            return;
        } else {
            $expandedmycourses = true;
        }

        $courses = enrol_get_my_courses();
        if (count($courses) == 0) {
            // No enrolments, so don't try to populate.
            return;
        }

        if ($this->show_my_categories(true)) {
            // OK Actually we are loading categories. We only want to load categories that have a parent of 0.
            // In order to make sure we load everything required we must first find the categories that are not
            // base categories and work out the bottom category in thier path.
            $categoryids = array();
            $toplevelcats = array();
            foreach ($courses as $course) {
                $categoryids[] = $course->category;
                $toplevelcats[$course->category] = $course->category;
            }
            $categoryids = array_unique($categoryids);
            list($sql, $params) = $DB->get_in_or_equal($categoryids);
            $categories = $DB->get_recordset_select('course_categories', 'id '.$sql.' AND parent <> 0', $params, 'sortorder, id', 'id, path');
            foreach ($categories as $category) {
                $bits = explode('/', trim($category->path,'/'));
                $toplevelcats[$category->id] = $bits[0];
                $categoryids[] = array_shift($bits);
            }
            $categoryids = array_unique($categoryids);
            $categories->close();

            // Now we load the base categories.
            list($sql, $params) = $DB->get_in_or_equal($categoryids);
            $categories = $DB->get_recordset_select('course_categories', 'id '.$sql.' AND parent = 0', $params, 'sortorder, id');
            foreach ($categories as $category) {
                $this->add_category($category, $this->rootnodes['mycourses'], self::TYPE_MY_CATEGORY);
            }
            $categories->close();
            foreach ($courses as $course) {
                $cat = $this->rootnodes['mycourses']->find($toplevelcats[$course->category], self::TYPE_MY_CATEGORY);
                $node = $this->add_course_to($course, false, self::COURSE_MY, $cat);
            }
        } else {
            foreach ($courses as $course) {
                $node = $this->add_course($course, false, self::COURSE_MY);
                if (!$this->rootnodes['mycourses']->find($node->key, self::TYPE_COURSE)) {
                    // Hasn't been added to this node
                    $this->rootnodes['mycourses']->add_node($node);
                }
            }
        }
    }

    /**
     * Adds a structured category to the navigation in the correct order/place
     *
     * @param stdClass $category
     * @param navigation_node $parent
     */
    protected function add_category(stdClass $category, navigation_node $parent, $nodetype = self::TYPE_CATEGORY) {
        if ((!$this->expandtocourses && $parent->key=='courses') || $parent->find($category->id, self::TYPE_CATEGORY)) {
            return;
        }
        $url = new moodle_url('/course/category.php', array('id' => $category->id));
        $context = context_coursecat::instance($category->id);
        $categoryname = format_string($category->name, true, array('context' => $context));
        $categorynode = $parent->add($categoryname, $url, $nodetype, $categoryname, $category->id);
        if (empty($category->visible)) {
            if (has_capability('moodle/category:viewhiddencategories', context_system::instance())) {
                $categorynode->hidden = true;
            } else {
                $categorynode->display = false;
            }
        }
        if ($nodetype !== self::TYPE_MY_CATEGORY) {
            $this->addedcategories[$category->id] = $categorynode;
        }
    }

    /**
     * Adds the given course to the navigation structure, under a specified parent.
     *
     * @param stdClass $course
     * @param bool $forcegeneric
     * @param bool $ismycourse
     * @return navigation_node
     */
    public function add_course_to(stdClass $course, $forcegeneric = false, $coursetype = self::COURSE_OTHER, navigation_node $parent) {
        global $CFG, $SITE;
        $coursecontext = context_course::instance($course->id);

        if ($course->id != $SITE->id && !$course->visible) {
            if (is_role_switched($course->id)) {
                // user has to be able to access course in order to switch, let's skip the visibility test here
            } else if (!has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                return false;
            }
        }

        $shortname = format_string($course->shortname, true, array('context' => $coursecontext));

        $url = new moodle_url('/course/view.php', array('id'=>$course->id));

        $coursenode = $parent->add($shortname, $url, self::TYPE_COURSE, $shortname, $course->id);
        $coursenode->nodetype = self::NODETYPE_BRANCH;
        $coursenode->hidden = (!$course->visible);
        $coursenode->title(format_string($course->fullname, true, array('context' => $coursecontext)));

        return $coursenode;
    }
    public function add_course(stdClass $course, $forcegeneric = false, $coursetype = self::COURSE_OTHER) {
        global $PAGE;
        if (!$forcegeneric && array_key_exists($course->id, $this->addedcourses)) {
            return $this->addedcourses[$course->id];
        }
        if ($coursetype == self::COURSE_OTHER && $PAGE->course->id == $course->id) {
            $coursetype = self::COURSE_CURRENT;
        }
        if ($this->expandtocourses || $coursetype == self::COURSE_MY || $coursetype == self::COURSE_CURRENT) {
            return parent::add_course($course, $forcegeneric, $coursetype);
        }
        return false;
    }

    /**
     * They've expanded the general 'courses' branch.
     */
    protected function load_courses_other() {
        if (!$this->expandtocourses) {
            return;
        }
        $this->load_all_courses();
    }
    protected function load_all_courses($categoryids = null) {
        if (!$this->expandtocourses) {
            return array();
        }
        return parent::load_all_courses($categoryids);
    }


    public function get_expandable() {
        return $this->expandable;
    }
}