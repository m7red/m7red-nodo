<?php
/** Slight modification by m7red-nodo: theme's original file is overwritten with this copy. */
?>
<?php if ( ! have_posts() ) : ?>
    <div id="post-0" class="post error404 not-found">
        <h1 class="entry-title"><?php _e( 'Not Found', 'm7red' ); ?></h1>
        <div class="entry-content">
            <p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'm7red' ); ?></p>
            <?php get_search_form(); ?>
        </div><!-- .entry-content -->
    </div><!-- #post-0 -->
<?php endif; ?>
<?php $imbalance2_theme_options = get_option('imbalance2_theme_options') ?>

<div id="boxes">
<?php while ( have_posts() ) : the_post(); ?>
    <div class="box">
        <div class="rel">
            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('homepage-thumb', array('alt' => '', 'title' => '')) ?></a>
    <?php if ($imbalance2_theme_options['images_only'] == 0): ?>
            <div class="categories"><?php imbalance2_posted_in(); ?></div>
            <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
        <?php the_excerpt() ?>
            <div class="posted"><?php imbalance2_posted_on() ?> <span class="main_separator">/</span>
                <?php echo comments_popup_link( __( 'No comments', 'm7red' ), __( 'One comment', 'm7red' ), __( '% comments', 'm7red' ) ); ?>
            </div>
    <?php endif ?>
            <div class="texts">
    <?php if ($imbalance2_theme_options['images_only'] == 1): ?>
                <a class="transparent" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('homepage-thumb', array('alt' => '', 'title' => '')) ?></a>
    <?php endif ?>
                <div class="abs">
    <?php if ($imbalance2_theme_options['images_only'] == 0): ?>
                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('homepage-thumb', array('alt' => '', 'title' => '')) ?></a>
    <?php endif ?>
                    <div class="categories"><?php imbalance2_posted_in(); ?></div>
                    <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
        <?php the_excerpt() ?>
                    <div class="posted"><?php imbalance2_posted_on() ?> <span class="main_separator">/</span>
                    <?php echo comments_popup_link( __( 'No comments', 'm7red' ), __( 'One comment', 'm7red' ), __( '% comments', 'm7red' ) ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endwhile; ?>
</div>

<?php if ( $wp_query->max_num_pages > 1 ) :
    if ( $imbalance2_theme_options['navigation'] == 0 ) : // Default ?>
<div class="fetch">
    <?php next_posts_link( __( 'Load more posts', 'm7red' ) ); ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
    // Ajax-fetching "Load more posts"
    // Code refactoring because jQuery's .live() has been removed in version 1.9.
    jQuery('.fetch').on('click', 'a', function(e) {
//     jQuery('.fetch a').live('click', function(e) {
        e.preventDefault();
        jQuery(this).addClass('loading').text('Carga...');
        jQuery.ajax({
            type: "GET",
            url: jQuery(this).attr('href') + '#boxes',
            dataType: "html",
            success: function(out) {
                result = jQuery(out).find('#boxes .box');
                nextlink = jQuery(out).find('.fetch a').attr('href');
                jQuery('#boxes').append(result).masonry('appended', result);
                jQuery('.fetch a').removeClass('loading').text('Mostrar más entradas');
                if (nextlink != undefined) {
                    jQuery('.fetch a').attr('href', nextlink);
                } else {
                    jQuery('.fetch').remove();
                }
            }
        });
    });
});
</script>

    <?php elseif ( $imbalance2_theme_options['navigation'] == 1 ) : // Infinite scroll ?>

<div class="infinitescroll">
    <?php next_posts_link( __( 'Load more posts', 'm7red' ) ); ?>
</div>

<script type="text/javascript">
// Infinite Scroll
var href = 'first';
jQuery(document).ready(function() {
    jQuery('#boxes').infinitescroll({
        navSelector : '.infinitescroll',
        nextSelector : '.infinitescroll a',
        itemSelector : '#boxes .box',
        loadingImg : '<?php echo get_bloginfo('stylesheet_directory') ?>/images/loading.gif',
        loadingText : 'Loading...',
        donetext : 'No more pages to load.',
        debug : false
    }, function(arrayOfNewElems) {
        jQuery('#boxes').masonry('appended', jQuery(arrayOfNewElems));
        if (href != jQuery('.infinitescroll a').attr('href'))
        {
            href = jQuery('.infinitescroll a').attr('href');
        }
    });
});
</script>

    <?php endif; ?>
<?php endif;
