<?php
/**
 * Responsive Decaf theme for Moodle 2.6 and above - page header common to main layouts.
 *
 * @package   theme_decaf
 * @copyright 2014 Paul Nicholls
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
?>
<header id="page-header" class="clearfix decaf-border decaf-border-bottom">
    <nav class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></nav>
    <?php echo $html->heading; ?>
    <div id="course-header">
        <?php echo $OUTPUT->course_header(); ?>
    </div>
</header>