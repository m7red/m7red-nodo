<?php
/** Slight modification by m7red-nodo: theme's original file is overwritten with this copy. */
get_header();
?>
		<div id="container">
			<div id="content" role="main">
<?php	if (have_posts()) the_post(); ?>
          <?php if ( is_tag() || is_category() || is_tax() ) :
					  if (is_tax('process')) {
					    // In case of processes we apply all associated posts as filter criteria for
					    // graphics. => $process_filter
					    $post_ids = m7red_get_all_posts_for_taxonomy(single_term_title( '', false ), 'process');
              $process_filter = implode(';', $post_ids); // Returns a string from the array elements.
              ?>
					    <input type="hidden" id="is_process" value="1"/>
              <input type="hidden" id="is_process_post_ids" value="<?php echo $process_filter; ?>"/>
              <input type="hidden" id="single_term_title" value="<?php single_term_title( '', true); ?>"/>
              <?php
              $term_title = '&#35;&nbsp;'. __( single_term_title('', false), 'm7red' );
							$term_description = term_description('', 'process');
						  echo '<h1 class="page-title">' . $term_title . '</h1>';
					    if (! empty( $term_description)) {
						    echo '<div class="archive-meta">' . $term_description . '</div>';
              }
					  } else {
						  printf( __( '%s', 'm7red' ), single_term_title() );
					  }
					elseif ( is_day() ) :
						printf( __( 'Daily Archives: %s', 'm7red' ), get_the_date() );
					elseif ( is_month() ) :
						printf( __( 'Monthly Archives: %s', 'm7red' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'm7red' ) ) );
					elseif ( is_year() ) :
						printf( __( 'Yearly Archives: %s', 'm7red' ), get_the_date( _x( 'Y', 'yearly archives date format', 'm7red' ) ) );
					else :
						_e( 'Archives', 'm7red' );
					endif; ?>
<?php
	rewind_posts();
	get_template_part( 'loop', 'archive' );
?>
			</div><!-- #content -->
		</div><!-- #container -->
<?php get_footer(); ?>
