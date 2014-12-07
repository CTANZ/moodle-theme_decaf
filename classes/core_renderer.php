<?php
/**
 * Core renderers for Decaf.
 *
 * @package    theme_decaf
 * @copyright  2014 Paul Nicholls
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_decaf_core_renderer extends theme_bootstrapbase_core_renderer {

    protected $really_editing = false;

    public function set_really_editing($editing) {
        $this->really_editing = $editing;
    }

    public function is_really_editing() {
        return $this->really_editing;
    }

    public function standard_head_html() {
        global $USER;

        if (property_exists($USER, 'editing')) {
            // Disable editing if we're in persistent editing mode and not "really editing".
            $wasediting = $USER->editing;
            if (!empty($this->page->theme->settings->persistentedit) && !$this->is_really_editing()) {
                $USER->editing = 0;
            }

            // Generate block contents now, so we don't get editing controls if we're not "really editing".
            foreach ($this->page->blocks->get_regions() as $region) {
                $this->page->blocks->ensure_content_created($region, $this);
            }

            // Set editing flag back to what it was before we did this.
            $USER->editing = $wasediting;
        }

        // No point in duplicating code unnecessarily, just call the parent and return what it does.
        return parent::standard_head_html();
    }

    /**
     * Returns the CSS classes to apply to the body tag.
     *
     * @since Moodle 2.5.1 2.6
     * @param array $additionalclasses Any additional classes to apply.
     * @return string
     */
    public function body_css_classes(array $additionalclasses = array()) {
        // Add a class for each block region on the page.
        // We use the block manager here because the theme object makes get_string calls.
        foreach ($this->page->blocks->get_regions() as $region) {
            $additionalclasses[] = 'has-region-'.$region;
            if (theme_decaf_block_manager::region_has_visible_content($this->page->blocks, $region, $this)) {
                $additionalclasses[] = 'used-region-'.$region;
            } else {
                $additionalclasses[] = 'empty-region-'.$region;
            }
            if ($this->page->blocks->region_completely_docked($region, $this)) {
                $additionalclasses[] = 'docked-region-'.$region;
            }
        }
        foreach ($this->page->layout_options as $option => $value) {
            if ($value) {
                $additionalclasses[] = 'layout-option-'.$option;
            }
        }
        $css = $this->page->bodyclasses .' '. join(' ', $additionalclasses);
        return $css;
    }

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        if (!empty($items[0]) && $items[0]->key == 'home') {
            array_shift($items);
        }
        if (count($items) === 0) {
            return ''; // Don't bother with any output if nothing to display - i.e. we're on the front page.
        }
        $breadcrumbs = array();
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }
        $divider = '<span class="divider">/</span>';
        $list_items = "<li>$divider ".join("</li><li>$divider ", $breadcrumbs).'</li>';
        $title = '<span class="accesshide">'.get_string('pagepath').'</span>';
        return $title . "<ul class=\"breadcrumb\">$list_items</ul>";
    }

    /**
     * Produces a header for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_header(block_contents $bc) {

        $title = '';
        if ($bc->title) {
            $attributes = array();
            if ($bc->blockinstanceid) {
                $attributes['id'] = 'instance-'.$bc->blockinstanceid.'-header';
            }
            $title = html_writer::tag('h2', $bc->title, $attributes);
        }

        $blockid = null;
        if (isset($bc->attributes['id'])) {
            $blockid = $bc->attributes['id'];
        }
        $controlshtml = $this->block_controls($bc->controls, $blockid);

        $output = '';
        if ($title || $controlshtml) {
            $output .= html_writer::tag('div', html_writer::tag('div', html_writer::tag('div', '', array('class'=>'block_action')). $title . $controlshtml, array('class' => 'title decaf-border decaf-border-bottom')), array('class' => 'header'));
        }
        return $output;
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
                $performanceinfo = theme_decaf_performance_output($perf);
            }
        }

        // We always want performance data when running a performance test, even if the user is redirected to another page.
        if (MDL_PERF_TEST && strpos($footer, $this->unique_performance_info_token) === false) {
            $footer = $this->unique_performance_info_token . $footer;
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
     * Renders an action menu component.
     *
     * ARIA references:
     *   - http://www.w3.org/WAI/GL/wiki/Using_ARIA_menus
     *   - http://stackoverflow.com/questions/12279113/recommended-wai-aria-implementation-for-navigation-bar-menu
     *
     * @param action_menu $menu
     * @return string HTML
     */
    public function render_action_menu(action_menu $menu) {
        if (!empty($menu->attributessecondary['data-constraint']) && $menu->attributessecondary['data-constraint'] == '.block-region') {
            // This is a block - don't mess with the primary vs secondary actions.
            return parent::render_action_menu($menu);
        }

        $actions = $menu->get_primary_actions();
        $actions = array_merge($actions, $menu->get_secondary_actions());

        $menu->initialise_js($this->page);

        $output = html_writer::start_tag('div', $menu->attributes);
        $output .= html_writer::start_tag('ul', $menu->attributesprimary);
        foreach ($actions as $action) {
            if (!($action instanceof renderable)) {
                $output .= html_writer::tag('li', $action, array('role' => 'presentation'));
                break;
            }
        }
        $output .= html_writer::end_tag('ul');
        $output .= html_writer::start_tag('ul', $menu->attributessecondary);
        foreach ($actions as $action) {
            if ($action instanceof action_menu_link_primary) {
                // Make it secondary so that the text label will be rendered.
                $action->primary = false;
            } else if ($action instanceof action_menu_filler) {
                // Skip fillers; we don't want gaps in the dropdown menu.
                continue;
            }
            if ($action instanceof renderable) {
                $content = $this->render($action);
            } else {
                // "Edit" link - don't put it in the menu too.
                continue;
            }
            $output .= html_writer::tag('li', $content, array('role' => 'presentation'));
        }
        $output .= html_writer::end_tag('ul');
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Output all the blocks in a particular region.
     *
     * @param string $region the name of a region on this page.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region) {
        global $USER;
        $blockswanted = array();
        $blocks = $this->page->blocks->get_blocks_for_region($region);
        $blockcontents = theme_decaf_block_manager::get_filtered_content($this->page->blocks, $this, $region);
        $lastblock = null;
        $zones = array();
        foreach ($blocks as $block) {
            $zones[] = $block->title;
        }
        $output = '';

        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                // Skip settings and/or navigation blocks as per Decaf theme settings.
                $skipsettings = $this->page->theme->settings->hidesettingsblock || !empty($USER->profile['decafSkipSettingsBlock']);
                $skipnavigation = $this->page->theme->settings->hidenavigationblock || !empty($USER->profile['decafSkipNavigationBlock']);
                $skipblock = $skipsettings && substr($bc->attributes['class'], 0, 15) == 'block_settings ';
                $skipblock = $skipblock || ($skipnavigation && substr($bc->attributes['class'], 0, 17) == 'block_navigation ');
                if (!$skipblock) {
                    $output .= $this->block($bc, $region);
                    $lastblock = $bc->title;
                }
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc, $zones, $lastblock);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }
}
