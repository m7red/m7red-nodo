<?php
/** Slight modification: theme original file is overwritten with this copy. */
?>
<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<h1 class="page-title"><?php
					printf( __( '%s', 'm7red' ), '<span>' . single_cat_title( '', false ) . '</span>' );
				?></h1>

        <?php // Hidden fields used in javascript routines. ?>
        <input type="hidden" id="is_category" value="1"/>
        <input type="hidden" id="single_cat_title" value="<?php single_cat_title( '', true ); ?>"/>

				<?php
					$category_description = category_description();
					if ( ! empty( $category_description ) )
						echo '<div class="archive-meta">' . $category_description . '</div>';

					get_template_part( 'loop', 'category' );
				?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_footer();
