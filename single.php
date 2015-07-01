<?php
/** Slight modification: theme original file is overwritten with this copy. */
get_header(); ?>
<div id="content">
    <?php get_template_part( 'loop', 'single' ); ?>
    <?php // m7red_create_relations_by_category_textual(); ?>

    <?php // Hidden fields used in javascript routines. ?>
    <input type="hidden" id="is_single" value="1"/>
    <input type="hidden" id="post_id" value="<?php echo get_the_ID(); ?>"/>
</div>
<?php get_footer();
