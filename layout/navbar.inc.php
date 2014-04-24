<?php
/**
 * Responsive Decaf theme for Moodle 2.6 and above - navbar common to main layouts.
 *
 * @package   theme_decaf
 * @copyright 2014 Paul Nicholls
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));
?>
<header role="banner" class="navbar navbar-fixed-top<?php echo $html->navbarclass ?> moodle-has-zindex">
    <?php if (empty($PAGE->layout_options['noawesomebar'])) { ?>
        <div id="awesomebar" class="decaf-awesome-bar">
            <?php
                if( $this->page->pagelayout != 'maintenance' // Don't show awesomebar if site is being upgraded
                    && !(get_user_preferences('auth_forcepasswordchange') && !session_is_loggedinas()) // Don't show it when forcibly changing password either
                  ) {
                    echo $decaf->awesome_nav;
                    if ($hascustommenu && !empty($decaf->custommenuinawesomebar) && empty($decaf->custommenuafterawesomebar)) {
                        echo $custommenu;
                    }
                    echo $decaf->awesome_settings;
                    if ($hascustommenu && !empty($decaf->custommenuinawesomebar) && !empty($decaf->custommenuafterawesomebar)) {
                        echo $custommenu;
                    }
                    echo $decaf->topsettings->settings_search_box();
                }
            ?>
        </div>
    <?php } ?>
    <nav role="navigation" class="navbar-inner">
        <div class="container-fluid">
            <div class="breadcrumb-nav">
                <a class="brand" href="<?php echo $CFG->wwwroot;?>"><?php echo $SITE->shortname; ?></a>
                <?php echo $OUTPUT->navbar(); ?>
            </div>
            <a class="btn btn-navbar" data-toggle="workaround-collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="nav-collapse collapse">
                <?php if ($hascustommenu && empty($decaf->custommenuinawesomebar)) {
                    echo $OUTPUT->custom_menu();
                } ?>
                <ul class="nav pull-right">
                    <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                    <li class="navbar-text"><?php echo $OUTPUT->login_info() ?></li>
                </ul>
            </div>
        </div>
    </nav>
</header>