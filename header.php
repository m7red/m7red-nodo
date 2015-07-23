<?php
/** Slight modification by m7red-nodo: theme's original file is overwritten with this copy. */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
    global $page, $paged;
    wp_title( '|', true, 'right' );
    bloginfo( 'name' );
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) ) echo " | $site_description";
    if ( $paged >= 2 || $page >= 2 ) echo ' | ' . sprintf( __( 'Page %s', 'm7red' ), max( $paged, $page ) );
?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );
    wp_head();
?>
<style type="text/css">
/* color from theme options */
<?php $color = getColor() ?>
body, input, textarea { font-family: <?php echo getFonts() ?>; }
a, .menu a:hover, #nav-above a:hover, #footer a:hover, .entry-meta a:hover { color: <?php echo $color ?>; }
.fetch:hover { background: <?php echo $color ?>; }
blockquote { border-color: <?php echo $color ?>; }
.menu ul .current-menu-item a { color: <?php echo $color ?>; }
#respond .form-submit input { background: <?php echo $color ?>; }

/* fluid grid */
<?php if (!fluidGrid()): ?>
.wrapper { width: 960px; margin: 0 auto; }
<?php else: ?>
.wrapper { margin: 0 40px; }
<?php endif ?>

.box .texts { border: 20px solid <?php echo $color ?>; background: <?php echo $color ?>;  }
<?php if (!imagesOnly()): ?>
.box .categories { padding-top: 15px; }
<?php endif ?>
</style>

<script type="text/javascript">

jQuery(document).ready( function() {
    // shortcodes
    jQuery('.wide').detach().appendTo('#wides');
    jQuery('.aside').detach().appendTo('.entry-aside');

    // fluid grid
    <?php if (fluidGrid()): ?>
    function wrapperWidth() {
        var wrapper_width = jQuery('body').width() - 20;
        wrapper_width = Math.floor(wrapper_width / 250) * 250 - 40;
        if (wrapper_width < 1000) wrapper_width = 1000;
        jQuery('.wrapper').css('width', wrapper_width);
    }
    wrapperWidth();
    jQuery(window).resize(function() {
        wrapperWidth();
    });
    <?php endif ?>

    // search
    jQuery(document).ready(function() {
        jQuery('#s').val('Buscar');
    });

    jQuery('#s').bind('focus', function() {
        jQuery(this).css('border-color', '<?php echo $color ?>');
        if (jQuery(this).val() == 'Buscar') jQuery(this).val('');
    });

    jQuery('#s').bind('blur', function() {
        jQuery(this).css('border-color', '#DEDFE0');
        if (jQuery(this).val() == '') jQuery(this).val('Buscar');
    });

    // grid
    jQuery('#boxes').masonry({
        itemSelector: '.box',
        columnWidth: 210,
        gutterWidth: 40
    });

    jQuery('#related').masonry({
        itemSelector: '.box',
        columnWidth: 210,
        gutterWidth: 40
    });

    // Code refactoring because jQuery's .live() has been removed in version 1.9.
    jQuery('body').on({
//     jQuery('.texts').live({
        'mouseenter': function() {
            if (jQuery(this).height() < jQuery(this).find('.abs').height()) {
                jQuery(this).height(jQuery(this).find('.abs').height());
            }
            jQuery(this).stop(true, true).animate({
                'opacity': '1',
                'filter': 'alpha(opacity=100)'
            }, 0);
        },
        'mouseleave': function() {
            jQuery(this).stop(true, true).animate({
                'opacity': '0',
                'filter': 'alpha(opacity=0)'
            }, 0);
        }
//     });
    }, '.texts');

    // comments
    jQuery('.comment-form-author label').hide();
    jQuery('.comment-form-author span').hide();
    jQuery('.comment-form-email label').hide();
    jQuery('.comment-form-email span').hide();
    jQuery('.comment-form-url label').hide();
    jQuery('.comment-form-comment label').hide();

    if (jQuery('.comment-form-author input').val() == '')
    {
        jQuery('.comment-form-author input').val('Name (required)');
    }
    if (jQuery('.comment-form-email input').val() == '')
    {
        jQuery('.comment-form-email input').val('Email (required)');
    }
    if (jQuery('.comment-form-url input').val() == '')
    {
        jQuery('.comment-form-url input').val('URL');
    }
    if (jQuery('.comment-form-comment textarea').html() == '')
    {
        jQuery('.comment-form-comment textarea').html('Your message');
    }

    jQuery('.comment-form-author input').bind('focus', function() {
        jQuery(this).css('border-color', '<?php echo $color ?>').css('color', '#333');
        if (jQuery(this).val() == 'Name (required)') jQuery(this).val('');
    });
    jQuery('.comment-form-author input').bind('blur', function() {
        jQuery(this).css('border-color', '<?php echo '#ccc' ?>').css('color', '#6b6b6b');
        if (jQuery(this).val().trim() == '') jQuery(this).val('Name (required)');
    });
    jQuery('.comment-form-email input').bind('focus', function() {
        jQuery(this).css('border-color', '<?php echo $color ?>').css('color', '#333');
        if (jQuery(this).val() == 'Email (required)') jQuery(this).val('');
    });
    jQuery('.comment-form-email input').bind('blur', function() {
        jQuery(this).css('border-color', '<?php echo '#ccc' ?>').css('color', '#6b6b6b');
        if (jQuery(this).val().trim() == '') jQuery(this).val('Email (required)');
    });
    jQuery('.comment-form-url input').bind('focus', function() {
        jQuery(this).css('border-color', '<?php echo $color ?>').css('color', '#333');
        if (jQuery(this).val() == 'URL') jQuery(this).val('');
    });
    jQuery('.comment-form-url input').bind('blur', function() {
        jQuery(this).css('border-color', '<?php echo '#ccc' ?>').css('color', '#6b6b6b');
        if (jQuery(this).val().trim() == '') jQuery(this).val('URL');
    });
    jQuery('.comment-form-comment textarea').bind('focus', function() {
        jQuery(this).css('border-color', '<?php echo $color ?>').css('color', '#333');
        if (jQuery(this).val() == 'Your message') jQuery(this).val('');
    });
    jQuery('.comment-form-comment textarea').bind('blur', function() {
        jQuery(this).css('border-color', '<?php echo '#ccc' ?>').css('color', '#6b6b6b');
        if (jQuery(this).val().trim() == '') jQuery(this).val('Your message');
    });
    jQuery('#commentform').bind('submit', function(e) {
        if (jQuery('.comment-form-author input').val() == 'Name (required)')
        {
            jQuery('.comment-form-author input').val('');
        }
        if (jQuery('.comment-form-email input').val() == 'Email (required)')
        {
            jQuery('.comment-form-email input').val('');
        }
        if (jQuery('.comment-form-url input').val() == 'URL')
        {
            jQuery('.comment-form-url input').val('');
        }
        if (jQuery('.comment-form-comment textarea').val() == 'Your message')
        {
            jQuery('.comment-form-comment textarea').val('');
        }
    })

    jQuery('.commentlist li div').bind('mouseover', function() {
        var reply = jQuery(this).find('.reply')[0];
        jQuery(reply).find('.comment-reply-link').show();
    });

    jQuery('.commentlist li div').bind('mouseout', function() {
        var reply = jQuery(this).find('.reply')[0];
        jQuery(reply).find('.comment-reply-link').hide();
    });
});
</script>

<?php // echo getFavicon() ?>
</head>

<body <?php body_class(); ?>>

<div class="wrapper">
    <div id="header">
        <div id="site-title">
            <div class="m7red-header-bloginfo-left">
              <a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
                <?php echo '<img id="m7red-header-logo" src="'.M7RED_IMAGES_URI.'/m7red-login-logo.png"'.' alt="m7red logotipo">';?>
              </a>
            </div>
            <div class="m7red-header-bloginfo-right">
              <a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
                <?php bloginfo( 'name' ); ?>
              </a>
              <span id="site-subtitle"><?php bloginfo('description'); ?></span>
            </div>
            <div id="header-left-m7red"><?php wp_nav_menu( array( 'container_class' => 'menu', 'theme_location' => 'header-left', 'walker' => new Imbalance2_Walker_Nav_Menu(), 'depth' => 1 ) ); ?></div>
        </div>
        <div id="header-center"><?php wp_nav_menu( array( 'container_class' => 'menu', 'theme_location' => 'header-center', 'walker' => new Imbalance2_Walker_Nav_Menu(), 'depth' => 1 ) ); ?></div>
        <div id="search">
            <?php get_search_form(); ?>
            <div id="header-right"><?php wp_nav_menu( array( 'container_class' => 'cat-menu', 'theme_location' => 'header-right', 'walker' => new Imbalance2_Walker_Nav_Menu(), 'depth' => 1 ) ); ?></div>
        </div>
        <div class="clear"></div>

        <input type="hidden" name="m7red_site_url" id="m7red_site_url" value="<?php echo M7RED_SITE_URL ?>"/>

        <?php m7red_set_processes_menu(); ?>
        <div style="clear:both"></div>

        <div class="box-shell">
          <div class="box-left">
            <?php m7red_set_graph_container(); // Set the graphics container on front page. ?>
          </div>
          <div class="box-right">
            <?php // m7red_show_legend_container('graph'); ?>
            <?php m7red_show_refresh_btn(); ?>
          </div>
        </div>
        <div style="clear:both"></div>

        <?php m7red_set_categories_menu(); ?>
        <div style="clear:both"></div>

        <?php // Hidden fields used in javascript routines. ?>
        <input type="hidden" name="the_site_url" id="the_site_url" value="<?php echo M7RED_SITE_URL ?>"/>
        <input type="hidden" name="data_uri" id="data_uri" value="<?php echo M7RED_DATA_URI ?>"/>
        <input type="hidden" id="graphics_zoom_init_scale" value="<?php echo get_option('m7red_graphics_zoom_init_scale')?>" />
    </div>

    <div id="main">
