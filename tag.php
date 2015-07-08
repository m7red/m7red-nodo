<?php
/** Slight modification by m7red-nodo: theme's original file is overwritten with this copy. */
get_header();

$post_ids = m7red_get_all_posts_by_tag(m7red_single_term_slug( '', false ));
$tags_filter = implode(';', $post_ids); // Returns a string from the array elements.
?>
		<div id="container">
			<div id="content" role="main">
				<input type="hidden" id="is_tag" value="1"/>
        <input type="hidden" id="is_tag_post_ids" value="<?php echo $tags_filter; ?>"/>
	      <input type="hidden" id="single_term_title" value="<?php single_term_title( '', true); ?>"/>
			<h1 class="page-title"><?php
					printf( __( 'Posts Tagged \'%s\'', 'imbalance2' ), '<span>' . single_tag_title( '', false ) . '</span>' );
				?></h1>

<?php get_template_part( 'loop', 'tag' ); ?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_footer(); ?>
