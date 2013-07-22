<?php

class theme_decaf_core_renderer extends core_renderer {

    protected $really_editing = false;

    public function set_really_editing($editing) {
        $this->really_editing = $editing;
    }
    /**
     * Outputs the page's footer
     * @return string HTML fragment
     */
    public function footer() {
        global $CFG, $DB, $USER;

        $output = $this->container_end_all(true);

        $footer = $this->opencontainers->pop('header/footer');

        if (debugging() and $DB and $DB->is_transaction_started()) {
            // TODO: MDL-20625 print warning - transaction will be rolled back
        }

        // Provide some performance info if required
        $performanceinfo = '';
        if (defined('MDL_PERF') || (!empty($CFG->perfdebug) and $CFG->perfdebug > 7)) {
            $perf = get_performance_info();
            if (defined('MDL_PERFTOLOG') && !function_exists('register_shutdown_function')) {
                error_log("PERF: " . $perf['txt']);
            }
            if (defined('MDL_PERFTOFOOT') || debugging() || $CFG->perfdebug > 7) {
                $performanceinfo = decaf_performance_output($perf);
            }
        }

        $footer = str_replace($this->unique_performance_info_token, $performanceinfo, $footer);

        $footer = str_replace($this->unique_end_html_token, $this->page->requires->get_end_code(), $footer);

        $this->page->set_state(moodle_page::STATE_DONE);

        if(!empty($this->page->theme->settings->persistentedit) && property_exists($USER, 'editing') && $USER->editing && !$this->really_editing) {
            $USER->editing = false;
        }

        return $output . $footer;
    }

        /**
     * The standard tags (typically performance information and validation links,
     * if we are in developer debug mode) that should be output in the footer area
     * of the page. Designed to be called in theme layout.php files.
     * @return string HTML fragment.
     */
    public function standard_footer_html() {
        global $CFG;

        // This function is normally called from a layout.php file in {@link header()}
        // but some of the content won't be known until later, so we return a placeholder
        // for now. This will be replaced with the real content in {@link footer()}.
        $output = $this->unique_performance_info_token;
        // Moodle 2.1 uses a magic accessor for $this->page->devicetypeinuse so we need to
        // check for the existence of the function that uses as
        // isset($this->page->devicetypeinuse) returns false
        if ($this->page->devicetypeinuse=='legacy') {
            // The legacy theme is in use print the notification
            $output .= html_writer::tag('div', get_string('legacythemeinuse'), array('class'=>'legacythemeinuse'));
        }

        // Get links to switch device types (only shown for users not on a default device)
        $output .= $this->theme_switch_links();
        
       // if (!empty($CFG->debugpageinfo)) {
       //     $output .= '<div class="performanceinfo">This page is: ' . $this->page->debug_summary() . '</div>';
       // }
        if (debugging(null, DEBUG_DEVELOPER)) {  // Only in developer mode
            $output .= '<div class="purgecaches"><a href="'.$CFG->wwwroot.'/admin/purgecaches.php?confirm=1&amp;sesskey='.sesskey().'">'.get_string('purgecaches', 'admin').'</a></div>';
        }
        if (!empty($CFG->debugvalidators)) {
            $output .= '<div class="validators"><ul>
              <li><a href="http://validator.w3.org/check?verbose=1&amp;ss=1&amp;uri=' . urlencode(qualified_me()) . '">Validate HTML</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=-1&amp;url1=' . urlencode(qualified_me()) . '">Section 508 Check</a></li>
              <li><a href="http://www.contentquality.com/mynewtester/cynthia.exe?rptmode=0&amp;warnp2n3e=1&amp;url1=' . urlencode(qualified_me()) . '">WCAG 1 (2,3) Check</a></li>
            </ul></div>';
        }
        if (!empty($CFG->additionalhtmlfooter)) {
            $output .= "\n".$CFG->additionalhtmlfooter;
        }
        return $output;
    }

    /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method override the render_custom_menu function
     * in outputrenderers.php
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
    protected function render_custom_menu(custom_menu $menu) {
        // If the menu has no children return an empty string
        if (!$menu->has_children()) {
            return '';
        }
        // Initialise this custom menu
        $content = html_writer::start_tag('ul', array('class'=>'dropdown dropdown-horizontal decaf-custom-menu'));
        // Render each child
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item);
        }
        // Close the open tags
        $content .= html_writer::end_tag('ul');
        // Return the custom menu
        return $content;
    }

    /**
     * Renders a custom menu node as part of a submenu
     *
     * The custom menu this method override the render_custom_menu_item function
     * in outputrenderers.php
     *
     * @see render_custom_menu()
     *
     * @staticvar int $submenucount
     * @param custom_menu_item $menunode
     * @return string
     */
    protected function render_custom_menu_item(custom_menu_item $menunode) {
        // Required to ensure we get unique trackable id's
        static $submenucount = 0;
        $content = html_writer::start_tag('li');
        if ($menunode->has_children()) {
            // If the child has menus render it as a sub menu
            $submenucount++;
            $extra = '';
            if ($menunode->get_url() === null) {
                $extra = ' customitem-nolink';
            }
            $content .= html_writer::start_tag('span', array('class'=>'customitem'.$extra));
            if ($menunode->get_url() !== null) {
                $content .= html_writer::link($menunode->get_url(), $menunode->get_text(), array('title'=>$menunode->get_title()));
            } else {
                $content .= $menunode->get_text();
            }
            
            $content .= html_writer::end_tag('span');
            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
        } else {
            // The node doesn't have children so produce a final menuitem

            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title(), 'class'=>'customitem-no-children'));
        }
        $content .= html_writer::end_tag('li');
        // Return the sub menu
        return $content;
    }

    // Copied from core_renderer with one minor change - changed $this->output->render() call to $this->render()
    protected function render_navigation_node(navigation_node $item) {
        $content = $item->get_content();
        $title = $item->get_title();
        if ($item->icon instanceof renderable && !$item->hideicon) {
            $icon = $this->render($item->icon);
            $content = $icon.$content; // use CSS for spacing of icons
        }
        if ($item->helpbutton !== null) {
            $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton'));
        }
        if ($content === '') {
            return '';
        }
        if ($item->action instanceof action_link) {
            //TODO: to be replaced with something else
            $link = $item->action;
            if ($item->hidden) {
                $link->add_class('dimmed');
            }
            $content = $this->render($link);
        } else if ($item->action instanceof moodle_url) {
            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            $content = html_writer::link($item->action, $content, $attributes);

        } else if (is_string($item->action) || empty($item->action)) {
            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            $content = html_writer::tag('span', $content, $attributes);
        }
        return $content;
    }

    /**
     * blocks_for_region() overrides core_renderer::blocks_for_region()
     *  in moodlelib.php. Returns a string
     * values ready for use.
     *
     * @return string
     */
    public function blocks_for_region($region) {
        if (!$this->page->blocks->is_known_region($region)) {
            return '';
        }
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $output = '';
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                if (!($this->page->theme->settings->hidesettingsblock && substr($bc->attributes['class'], 0, 15) == 'block_settings ')
                        && !($this->page->theme->settings->hidenavigationblock && substr($bc->attributes['class'], 0, 17) == 'block_navigation ')) {
                    $output .= $this->block($bc, $region);
                }
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }

}

class theme_decaf_topsettings_renderer extends plugin_renderer_base {

    public function settings_tree(settings_navigation $navigation) {
        global $CFG;
        $content = $this->navigation_node($navigation, array('class' => 'dropdown  dropdown-horizontal'));
        return $content;
    }
    public function settings_search_box() {
        global $CFG;
        $content = "";
        if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
            $content .= $this->search_form(new moodle_url("$CFG->wwwroot/$CFG->admin/search.php"), optional_param('query', '', PARAM_RAW));
        }
        $content .= html_writer::empty_tag('br', array('clear' => 'all'));
        return $content;
    }

    public function navigation_tree(global_navigation $navigation) {
        global $CFG;
        $content = html_writer::start_tag('ul', array('id' => 'awesomeHomeMenu', 'class' => 'dropdown  dropdown-horizontal'));
        $content .= html_writer::start_tag('li');
        $content .= html_writer::start_tag('a', array('href' => "$CFG->wwwroot", 'id' =>'home'));
        $content .= html_writer::empty_tag('img', array('alt' => '', 'src' =>$this->pix_url('home_icon', 'theme')));
        $content .= html_writer::end_tag('a');
        $content .= html_writer::end_tag('li');
        $content .= html_writer::start_tag('li');
        $content .= html_writer::start_tag('span', array('id' =>'awesomeNavMenu'));
        $content .= html_writer::empty_tag('img', array('alt' => '', 'src' =>$this->pix_url('user_silhouette', 'theme')));
        $content .= html_writer::end_tag('span');
        $content .= $this->navigation_node($navigation, array());
        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');
        return $content;
    }

    protected function navigation_node(navigation_node $node, $attrs=array()) {
        global $CFG, $PAGE;
        static $mainsubnav;
        static $coursessubnav;
        $items = $node->children;
        $hidecourses = (property_exists($PAGE->theme->settings, 'coursesloggedinonly') && $PAGE->theme->settings->coursesloggedinonly && !isloggedin());

        // exit if empty, we don't want an empty ul element
        if ($items->count() == 0) {
            return '';
        }

        // array of nested li elements
        $lis = array();
        foreach ($items as $item) {
            if (!$item->display) {
                continue;
            }
            if ($item->key === 'courses' && $hidecourses) {
                continue;
            }

            // Skip pointless "Current course" node, go straight to its last (sole) child
            if ($item->key === 'currentcourse') {
                $item = $item->children->last();
            }

            $isbranch = ($item->children->count() > 0 || $item->nodetype == navigation_node::NODETYPE_BRANCH || (property_exists($item, 'isexpandable') && $item->isexpandable));
            $hasicon = (!$isbranch && $item->icon instanceof renderable);

            if ($isbranch) {
                $item->hideicon = true;
            }

            if ($item->action instanceof action_link && $hasicon && !$item->hideicon && (strip_tags($item->action->text)==$item->action->text)) {
                // Icon hasn't already been rendered - render it now.
                $item->action->text = $this->output->render($item->icon) . $item->action->text;
            }

            $content = $this->output->render($item);
            if($isbranch && $item->children->count()==0) {
                $expanded = false;
                // Navigation block does this via AJAX - we'll merge it in directly instead
                if (!empty($CFG->navshowallcourses) && $item->key === 'courses') {
                    if(!$coursessubnav) {
                        // Prepare dummy page for subnav initialisation
                        $dummypage = new decaf_dummy_page();
                        $dummypage->set_context($PAGE->context);
                        $dummypage->set_url($PAGE->url);
                        $coursessubnav = new decaf_expand_navigation($dummypage, $item->type, $item->key);
                        $expanded = true;
                    }
                    $subnav = $coursessubnav;
                } else {
                    if(!$mainsubnav) {
                        // Prepare dummy page for subnav initialisation
                        $dummypage = new decaf_dummy_page();
                        $dummypage->set_context($PAGE->context);
                        $dummypage->set_url($PAGE->url);
                        $mainsubnav = new decaf_expand_navigation($dummypage, $item->type, $item->key);
                        $expanded = true;
                    }
                    $subnav = $mainsubnav;
                }
                if (!$expanded) {
                    // re-use subnav so we don't have to reinitialise everything
                    $subnav->expand($item->type, $item->key);
                }
                if (!isloggedin() || isguestuser()) {
                    $subnav->set_expansion_limit(navigation_node::TYPE_COURSE);
                }
                $branch = $subnav->find($item->key, $item->type);
                if($branch!==false) $content .= $this->navigation_node($branch);
            } else {
                $content .= $this->navigation_node($item);
            }


            if($isbranch && !(is_string($item->action) || empty($item->action))) {
                $content = html_writer::tag('li', $content, array('class' => 'clickable-with-children'));
            } else {
                $content = html_writer::tag('li', $content);
            }
            $lis[] = $content;
        }

        if (count($lis)) {
            return html_writer::nonempty_tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }

    public function search_form(moodle_url $formtarget, $searchvalue) {
        global $CFG;

        if (empty($searchvalue)) {
            $searchvalue = 'Search Settings..';
        }

        $content = html_writer::start_tag('form', array('class' => 'topadminsearchform', 'method' => 'get', 'action' => $formtarget));
        $content .= html_writer::start_tag('div', array('class' => 'search-box'));
        $content .= html_writer::tag('label', s(get_string('searchinsettings', 'admin')), array('for' => 'adminsearchquery', 'class' => 'accesshide'));
        $content .= html_writer::empty_tag('input', array('id' => 'topadminsearchquery', 'type' => 'text', 'name' => 'query', 'value' => s($searchvalue),
                    'onfocus' => "if(this.value == 'Search Settings..') {this.value = '';}",
                    'onblur' => "if (this.value == '') {this.value = 'Search Settings..';}"));
        //$content .= html_writer::empty_tag('input', array('class'=>'search-go','type'=>'submit', 'value'=>''));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('form');

        return $content;
    }

}

require_once($CFG->dirroot.'/course/renderer.php');
class theme_decaf_core_course_renderer extends core_course_renderer {
    protected function course_modchooser_module($module, $classes = array('option')) {
        global $PAGE;
        if(empty($PAGE->theme->settings->usemodchoosertiles)) {
            return parent::course_modchooser_module($module, $classes);
        }
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