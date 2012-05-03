<?php
require_once ($CFG->dirroot."/theme/decaf/lib.php");

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));

// $PAGE->blocks->region_has_content('region_name') doesn't work as we do some sneaky stuff 
// to hide nav and/or settings blocks if requested
$blocks_side_pre = trim($OUTPUT->blocks_for_region('side-pre'));
$hassidepre = strlen($blocks_side_pre);
$blocks_side_post = trim($OUTPUT->blocks_for_region('side-post'));
$hassidepost = strlen($blocks_side_post);

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

decaf_initialise_editbuttons($PAGE);

$bodyclasses = array();
if ($hassidepre && !$hassidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($hassidepost && !$hassidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$hassidepost && !$hassidepre) {
    $bodyclasses[] = 'content-only';
}

if(!empty($PAGE->theme->settings->persistentedit) && $PAGE->user_allowed_editing()) {
    $USER->editing = 1;
    $bodyclasses[] = 'decaf_persistent_edit';
}

if (!empty($PAGE->theme->settings->footnote)) {
    $footnote = $PAGE->theme->settings->footnote;
} else {
    $footnote = '<!-- There was no custom footnote set -->';
}

if (check_browser_version("MSIE", "0")) {
    header('X-UA-Compatible: IE=edge');
}
echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <script type="text/javascript">
    YUI().use('node', function(Y) {
        window.thisisy = Y;
    	Y.one(window).on('scroll', function(e) {
    	    var node = Y.one('#back-to-top');

    	    if (Y.one('window').get('docScrollY') > Y.one('#page-content-wrapper').getY()) {
    		    node.setStyle('display', 'block');
    	    } else {
    		    node.setStyle('display', 'none');
    	    }
    	});

    });
    </script>
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html(); ?>
<div id="awesomebar">
    <?php
        if( $this->page->pagelayout != 'maintenance' // Don't show awesomebar if site is being upgraded
            && !(get_user_preferences('auth_forcepasswordchange') && !session_is_loggedinas()) // Don't show it when forcibly changing password either
          ) {
            $topsettings = $this->page->get_renderer('theme_decaf','topsettings');
            echo $topsettings->navigation_tree($this->page->navigation);
            if ($hascustommenu && !empty($PAGE->theme->settings->custommenuinawesomebar) && empty($PAGE->theme->settings->custommenuafterawesomebar)) {
                echo $custommenu;
            }
            echo $topsettings->settings_tree($this->page->settingsnav);
            if ($hascustommenu && !empty($PAGE->theme->settings->custommenuinawesomebar) && !empty($PAGE->theme->settings->custommenuafterawesomebar)) {
                echo $custommenu;
            }
            echo $topsettings->settings_search_box();
        }
    ?>
</div>

<div id="page">

<?php if ($hasheading || $hasnavbar) { ?>

    <div id="page-header">
		<div id="page-header-wrapper">
	        
	        <?php if ($hasheading) { ?>
		    	<h1 class="headermain"><?php echo $PAGE->heading ?></h1>
    		    <div class="headermenu">
        			<?php
        			if (!empty($PAGE->theme->settings->showuserpicture)) {
        				if (isloggedin())
        				{
        					echo ''.$OUTPUT->user_picture($USER, array('size'=>55)).'';
        				}
        				else {
        					?>
						<img class="userpicture" src="<?php echo $CFG->wwwroot .'/theme/'. current_theme().'/pix/image.png' ?>" />
						<?php
        				}
        			}
        		echo $OUTPUT->login_info();
    	        echo $OUTPUT->lang_menu();
	        	echo $PAGE->headingmenu;
        			?>
	        	</div>
	        <?php } ?>        
        	
	    </div>
    </div>
    <?php if ($hascustommenu && empty($PAGE->theme->settings->custommenuinawesomebar)) { ?>
      <div id="custommenu"><?php echo $custommenu; ?></div>
 	<?php } ?>
    
    <?php if ($hasnavbar) { ?>
	    <div class="navbar clearfix">
    	    <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
            <div class="navbutton"> <?php echo $PAGE->button; ?></div>
        </div>
    <?php } ?>

<?php } ?>
<!-- END OF HEADER -->
<div id="page-content-wrapper" class="clearfix">
    <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">
            
                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">
                            <?php echo method_exists($OUTPUT, "main_content")?$OUTPUT->main_content():core_renderer::MAIN_CONTENT_TOKEN ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($hassidepre) { ?>
                <div id="region-pre" class="block-region">
                    <div class="region-content">
                        <?php echo $blocks_side_pre ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if ($hassidepost) { ?>
                <div id="region-post" class="block-region">
                    <div class="region-content">
                        <?php echo $blocks_side_post ?>
                    </div>
                </div>
                <?php } ?>
                
            </div>
        </div>
    </div>
</div>

<!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>
    <div id="page-footer" class="clearfix">
		<div class="footnote"><?php echo $footnote; ?></div>
        <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?></p>
        <?php
        echo $OUTPUT->login_info();
        echo $OUTPUT->home_link();
        echo $OUTPUT->standard_footer_html();
        ?>
    </div>
    <?php } ?>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
<div id="back-to-top"> 
    <a class="arrow" href="#">▲</a> 
    <a class="text" href="#">Back to Top</a> 
</div>
</body>
</html>