<?php

class theme_decaf_block_manager extends block_manager {

    /**
     * Get block contents, with settings/navigation filtered out if applicable.
     *
     * @param block_manager $manager The core block manager for this page.
     * @param core_renderer $output The core renderer for this page.
     * @param string $region The name of the region to get contents of.
     */
    public static function get_filtered_content($manager, $output, $region) {
        global $USER;
        $manager->check_is_loaded();
        $manager->ensure_instances_exist($region);

        $skipsettings = $manager->page->theme->settings->hidesettingsblock || !empty($USER->profile['decafSkipSettingsBlock']);
        $skipnavigation = $manager->page->theme->settings->hidenavigationblock || !empty($USER->profile['decafSkipNavigationBlock']);

        if (!array_key_exists($region, $manager->visibleblockcontent)) {
            $blockinstances = array();
            foreach ($manager->blockinstances[$region] as $block) {
                // Skip settings and/or navigation blocks as per Decaf theme settings.
                $skipblock = $block->blockname == 'block_settings' && $skipsettings;
                $skipblock = $skipblock || ($block->blockname == 'block_navigation' && $skipnavigation);
                if (!$skipblock) {
                    $blockinstances[] = $block;
                }
            }
            $contents = array();
            if (array_key_exists($region, $manager->extracontent)) {
                $contents = $manager->extracontent[$region];
            }
            $contents = array_merge($contents, $manager->create_block_contents($blockinstances, $output, $region));
            if ($region == $manager->defaultregion) {
                $addblockui = block_add_block_ui($manager->page, $output);
                if ($addblockui) {
                    $contents[] = $addblockui;
                }
            }
            $manager->visibleblockcontent[$region] = $contents;
        } else {
            // Block content has already been set, so we may need to remove blocks from the list of visible content.
            $blocks = array();
            foreach ($manager->visibleblockcontent[$region] as $block) {
                if (($skipsettings && strstr($block->attributes['class'], 'block_settings')) ||
                        ($skipnavigation && strstr($block->attributes['class'], 'block_navigation'))) {
                    continue;
                }
                $blocks[] = $block;
            }
            $manager->visibleblockcontent[$region] = $blocks;
        }

        return $manager->visibleblockcontent[$region];
    }

    /**
     * Determine whether a region contains anything. (Either any real blocks, or
     * the add new block UI.)
     *
     * (You may wonder why the $output parameter is required. Unfortunately,
     * because of the way that blocks work, the only reliable way to find out
     * if a block will be visible is to get the content for output, and to
     * get the content, you need a renderer. Fortunately, this is not a
     * performance problem, because we cache the output that is generated, and
     * in almost every case where we call region_has_content, we are about to
     * output the blocks anyway, so we are not doing wasted effort.)
     *
     * @param string $region a block region that exists on this page.
     * @param core_renderer $output a core_renderer. normally the global $OUTPUT.
     * @return boolean Whether there is anything in this region.
     */
    public static function region_has_visible_content($manager, $region, $output) {
        if (!$manager->is_known_region($region)) {
            return false;
        }
        $content = self::get_filtered_content($manager, $output, $region);

        return $manager->region_has_content($region, $output);
    }
}