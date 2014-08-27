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
 * Strings for component 'theme_decaf', language 'en'
 *
 * @package   theme_decaf
 * @copyright 2014 Paul Nicholls
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['choosereadme'] = '
<div class="clearfix">
<div class="well">
<h2>Decaf</h2>
<p><img class=img-polaroid src="decaf/pix/screenshot.jpg" /></p>
</div>
<div class="well">
<h3>About</h3>
<p>Decaf is a three-column, fluid-width theme for Moodle that was created by <a href="http://moodle.org/user/view.php?id=390408">Lei Zhang</a>.
It improves usability by shifting setting blocks to top of the page, so called "Moodle awesome bar".
For developers, It transfers the performance info into a colorful floated bar at bottom of the page, which brings the performance awareness as well as improves usability.</p>
<h3>Parents</h3>
<p>This theme is based upon the Bootstrap theme, which was created for Moodle 2.5, with the help of:<br>
Stuart Lamour, Mark Aberdour, Paul Hibbitts, Mary Evans.</p>
<h3>Theme Credits</h3>
<p>Authors: Paul Nicholls, Lei Zhang</p>
<h3>Report a bug:</h3>
<p><a href="https://github.com/CTANZ/moodle-theme_decaf/issues">Github</a></p>
</div></div>';

$string['configtitle'] = 'Decaf';

$string['customcss'] = 'Custom CSS';
$string['customcssdesc'] = 'Whatever CSS rules you add to this textarea will be reflected in every page, making for easier customization of this theme.';

$string['footnote'] = 'Footnote';
$string['footnotedesc'] = 'Whatever you add to this textarea will be displayed in the footer throughout your Moodle site.';

$string['backgroundcolor'] = 'Background colour';
$string['backgroundcolordesc'] = 'This sets the background colour for the theme.';
$string['configtitle'] = 'Decaf theme settings';
$string['customcss'] = 'Custom CSS';
$string['customcssdesc'] = 'Any CSS you enter here will be added to every page allowing your to easily customise this theme.';
$string['footnote'] = 'Footnote';
$string['footnotedesc'] = 'The content from this textarea will be displayed in the footer of every page.';
$string['logo'] = 'Logo';
$string['logodesc'] = 'Enter the URL to an image to use as the logo for this site. Should be http://www.yoursite.com/path/to/logo.png';
$string['regionwidth'] = 'Column width';
$string['regionwidthdesc'] = 'This sets the width of the two block regions that form the left and right columns.';
$string['hidesettingsblock'] = 'Hide Settings block';
$string['hidesettingsblockdesc'] = 'This setting removes the standard Settings block from all pages.';
$string['hidenavigationblock'] = 'Hide Navigation block';
$string['hidenavigationblockdesc'] = 'This setting removes the standard Navigation block from all pages.';
$string['showuserpicture'] = 'Show user picture';
$string['showuserpicturedesc'] = 'This setting add user picture in the page heading';
$string['custommenuinawesomebar'] = 'Add custom menu to Awesomebar';
$string['custommenuinawesomebardesc'] = 'Moves the custom menu into the Awesomebar.  Otherwise, it will appear below the header.';
$string['custommenuafterawesomebar'] = 'Place custom menu after Awesomebar';
$string['custommenuafterawesomebardesc'] = 'Places custom menu after Awesomebar content.  Otherwise, it will appear before the Settings menus.  (Only applicable if previous option is enabled.)';
$string['persistentedit'] = 'Enable Persistent Editing Mode';
$string['persistenteditdesc'] = 'Persistent Editing Mode keeps editing mode turned on permanently for users with editing privileges.<br />Block editing can still be turned on and off independently via the "Turn editing on/off" option under Course Administration.';
$string['coursesloggedinonly'] = 'Hide Courses menu from non-logged-in users';
$string['coursesloggedinonlydesc'] = 'If you do not want users who are not logged in to see the "Courses" menu in the Awesomebar, enable this setting.';
$string['expandtoactivities'] = 'Expand to show activities within sections';
$string['expandtoactivitiesdesc'] = 'Enabling this option shows activities underneath the sections in the Awesomebar, but degrades performance - especially in courses with lots of activities/resources.';
$string['usemodchoosertiles'] = 'Use Mod Chooser Tiles';
$string['usemodchoosertilesdesc'] = 'In the Mod Chooser, mods will be shown as "tiles" (similar to icons on the desktop of most OSes).';
$string['awesomebarsettings'] = 'Awesomebar / Navigation Settings';
$string['awesomebarsettingsdesc'] = 'The "Awesomebar" is the black navigation bar that is fixed to the top of the browser window when using Decaf.<br>The following settings relate to the "Awesomebar" and other page navigation.';
$string['editingsettings'] = 'Editing Mode Settings';
$string['editingsettingsdesc'] = 'The following settings relate to Editing Mode, and aim to make it tidier and easier to use.<br />The "Use Edit Buttons" setting from previous versions of Decaf has been replaced by the "Activity editing menus" setting under Appearance / AJAX and Javascript ($CFG->modeditingmenu).';
$string['coursesleafonly'] = 'Populate "Courses" menu'; // This seems backwards, but makes it easier for users to understand as it eliminates the double-negative.
$string['coursesleafonlydesc'] = 'On sites with large numbers of courses, generating the full list of courses in the Awesomebar can cause performance issues.  Disable this setting (set it to "No") to prevent the list from being generated - the "Courses" node will remain as a quick way to reach the course list.';
$string['alwaysexpandsiteadmin'] = 'Always populate site administration menu';
$string['alwaysexpandsiteadmindesc'] = 'Enabling this option will populate the Site Administration menu (if applicable), at the expense of performance.';

$string['logo'] = 'Logo';
$string['logodesc'] = 'Please upload your custom logo here if you want to add it to the header.<br>
If the height of your logo is more than 75px add the following CSS rule to the Custom CSS box below.<br>
a.logo {height: 100px;} or whatever height in pixels the logo is.';

$string['pluginname'] = 'Decaf';

$string['region-side-post'] = 'Right';
$string['region-side-pre'] = 'Left';

