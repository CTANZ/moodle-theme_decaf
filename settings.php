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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Background colour setting
    $name = 'theme_decaf/backgroundcolor';
    $title = get_string('backgroundcolor','theme_decaf');
    $description = get_string('backgroundcolordesc', 'theme_decaf');
    $default = '#EEE';
    $previewconfig = array('selector'=>'html', 'style'=>'backgroundColor');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Logo file setting.
    $name = 'theme_decaf/logo';
    $title = get_string('logo','theme_decaf');
    $description = get_string('logodesc', 'theme_decaf');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Custom CSS file.
    $name = 'theme_decaf/customcss';
    $title = get_string('customcss', 'theme_decaf');
    $description = get_string('customcssdesc', 'theme_decaf');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Footnote setting.
    $name = 'theme_decaf/footnote';
    $title = get_string('footnote', 'theme_decaf');
    $description = get_string('footnotedesc', 'theme_decaf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Editing Mode heading
    $settings->add(new admin_setting_heading('themedecafeditingsettings', get_string('editingsettings', 'theme_decaf'), get_string('editingsettingsdesc', 'theme_decaf')));

    // Enable mod chooser "tiles"
    $name = 'theme_decaf/usemodchoosertiles';
    $title = get_string('usemodchoosertiles','theme_decaf');
    $description = get_string('usemodchoosertilesdesc', 'theme_decaf');
    $default = 0;
    $choices = array(0=>'No', 1=>'Yes');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Enable "persistent editing mode" (no need to turn on/off edit mode)
    $name = 'theme_decaf/persistentedit';
    $title = get_string('persistentedit','theme_decaf');
    $description = get_string('persistenteditdesc', 'theme_decaf');
    $default = 0;
    $choices = array(0=>'No', 1=>'Yes');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Awesomebar / Navigation heading
    $settings->add(new admin_setting_heading('themedecafawesombarsettings', get_string('awesomebarsettings', 'theme_decaf'), get_string('awesomebarsettingsdesc', 'theme_decaf')));

    // Hide Settings block
    $name = 'theme_decaf/hidesettingsblock';
    $title = get_string('hidesettingsblock','theme_decaf');
    $description = get_string('hidesettingsblockdesc', 'theme_decaf');
    $default = 1;
    $choices = array(1=>'Yes', 0=>'No');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Hide Navigation block
    $name = 'theme_decaf/hidenavigationblock';
    $title = get_string('hidenavigationblock','theme_decaf');
    $description = get_string('hidenavigationblockdesc', 'theme_decaf');
    $default = 0;
    $choices = array(1=>'Yes', 0=>'No');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Add custom menu to Awesomebar
    $name = 'theme_decaf/custommenuinawesomebar';
    $title = get_string('custommenuinawesomebar','theme_decaf');
    $description = get_string('custommenuinawesomebardesc', 'theme_decaf');
    $default = 0;
    $choices = array(1=>'Yes', 0=>'No');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Place custom menu after Awesomebar
    $name = 'theme_decaf/custommenuafterawesomebar';
    $title = get_string('custommenuafterawesomebar','theme_decaf');
    $description = get_string('custommenuafterawesomebardesc', 'theme_decaf');
    $default = 0;
    $choices = array(0=>'No', 1=>'Yes');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Hide courses menu from non-logged-in users
    $name = 'theme_decaf/coursesloggedinonly';
    $title = get_string('coursesloggedinonly','theme_decaf');
    $description = get_string('coursesloggedinonlydesc', 'theme_decaf');
    $default = 0;
    $choices = array(0=>'No', 1=>'Yes');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Don't actually show courses under "Courses" menu item
    $name = 'theme_decaf/coursesleafonly';
    $title = get_string('coursesleafonly','theme_decaf');
    $description = get_string('coursesleafonlydesc', 'theme_decaf');
    $default = 0;
    $choices = array(0=>'Yes', 1=>'No'); // This seems backwards, but makes it easier for users to understand as it eliminates the double-negative.
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Expand to activities at cost of performance
    $name = 'theme_decaf/expandtoactivities';
    $title = get_string('expandtoactivities','theme_decaf');
    $description = get_string('expandtoactivitiesdesc', 'theme_decaf');
    $default = 0;
    $choices = array(0=>'No', 1=>'Yes');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Expand site admin tree at cost of performance
    $name = 'theme_decaf/alwaysexpandsiteadmin';
    $title = get_string('alwaysexpandsiteadmin','theme_decaf');
    $description = get_string('alwaysexpandsiteadmindesc', 'theme_decaf');
    $default = 0;
    $choices = array(0=>'No', 1=>'Yes');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
