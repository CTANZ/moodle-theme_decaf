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

        if (!array_key_exists($region, $manager->visibleblockcontent)) {
        	$blockinstances = array();
        	foreach ($manager->blockinstances[$region] as $block) {
	            // Skip settings and/or navigation blocks as per Decaf theme settings.
                $skipsettings = $output->page->theme->settings->hidesettingsblock || !empty($USER->profile['decafSkipSettingsBlock']);
                $skipnavigation = $output->page->theme->settings->hidenavigationblock || !empty($USER->profile['decafSkipNavigationBlock']);
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
        }

        return $manager->visibleblockcontent[$region];
    }
}