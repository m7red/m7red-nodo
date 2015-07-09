<?php
/**
 * m7red-nodo functions and definitions
 *
 * The child theme's functions.php.
 * Sets up the theme.
 *
 * @package     m7red-nodo
 * @author      m7red (http://www.m7red.info)
 * @copyright   Copyright (c) 2015, m7red
 * @license     http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 */

define( 'M7RED_THEME_NAME', 'm7red-nodo' ); // Theme name.
define( 'THEME_PREFIX', 'm7red_'); // Theme prefix.
define( 'M7RED_VERSION', '2015.07.09' ); // Theme version.
define( 'CHILD_THEME_NAME', 'm7red-nodo' ); // Child theme name.

// Some constants and variables for universal use...
define( 'M7RED_SITE_URL', get_site_url() );

define( 'M7RED_ROOT_DIR', get_stylesheet_directory() );
define( 'M7RED_DATA_DIR', M7RED_ROOT_DIR . '/data');
define( 'M7RED_LIB_DIR', M7RED_ROOT_DIR . '/lib' );
define( 'M7RED_CSS_DIR', M7RED_ROOT_DIR . '/css' );
define( 'M7RED_IMAGES_DIR', M7RED_ROOT_DIR . '/images' );

define( 'M7RED_ROOT_URI', get_stylesheet_directory_uri() );
define( 'M7RED_DATA_URI',  get_stylesheet_directory_uri() . '/data');
define( 'M7RED_LIB_URI', get_stylesheet_directory_uri() . '/lib' );
define( 'M7RED_CSS_URI', get_stylesheet_directory_uri() . '/css' );
define( 'M7RED_IMAGES_URI', get_stylesheet_directory_uri() . '/images' );

// Retrieve template directory URI for the PARENT theme!
define( 'IMBALANCE2_LIBS_DIR', get_template_directory_uri() . '/libs' );

define( 'POSTS_PER_PAGE', 10 ); // Number of related posts that will be shown.

$prefix = '_m7red_';

// init defaults for node attributes.
define('NODE_COLOR', '#D1492E');
define('NODE_BORDER_COLOR', '#D1492E');
define('NODE_TYPE', 'circle');
define('NODE_SIZE', 6);

// Load PHP Functions and Classes
require_once( M7RED_LIB_DIR . '/related_posts_widget.php' );
require_once( M7RED_LIB_DIR . '/simple-term-meta.php' );
require_once( M7RED_ROOT_DIR . '/options.php' );

// Globals
$categories = array();
$posts = array();

/**
 * Theme Setup
 *
 * This setup function attaches all of the site-wide functions
 * to the correct hooks and filters. All the functions themselves
 * are defined below this setup function.
 *
 */
add_action( 'after_setup_theme', 'm7red_child_theme_setup' );

function m7red_child_theme_setup() {
  // Remove WordPress Version Number.
  remove_action('wp_head', 'wp_generator');

    // Load theme's translated strings.
    load_child_theme_textdomain( 'm7red', get_stylesheet_directory() . '/languages' );

    // Set theme specific favicon.
    add_action( 'wp_head', 'm7red_set_favicon_link' );

    // Load frontend and backend scripts.
    add_filter( 'wp_default_scripts', 'm7red_change_default_jquery' );
    add_action( 'wp_enqueue_scripts', 'm7red_load_frontend_scripts' );
    add_action( 'admin_enqueue_scripts', 'm7red_load_backend_scripts' );

    // Add options if not exists.
    add_action( 'admin_init', 'm7red_register_options' );

    // Set m7red specific login logo.
//     add_action( 'login_enqueue_scripts', 'm7red_set_login_logo' );

    // Remove editor from dashboard menu.
    add_action('_admin_menu', 'm7red_remove_editor_menu', 1);

    // Initialize Metabox Class.
    add_action( 'init', 'm7red_initialize_cmb_meta_boxes', 9999 );

    // Load widgets.
    add_action('widgets_init', 'm7red_load_widgets');

    // Add custom box on edit page
    add_action('admin_menu', 'm7red_add_meta_custom_box_to_edit_posts');

    // Save related posts
    add_action('save_post', 'm7red_save_related_postdata');

    // Listen for AJAX search call
    add_action('admin_init', 'm7red_search_related_posts_ajax_listener');

    // Remove related posts
    add_action("delete_post", "m7red_delete_relationships");

    // Automatically add related posts list to post content
    add_filter('the_content', 'm7red_set_auto_related_posts');

    // Add [related-posts] shortcode support
    add_shortcode('related-posts', 'm7red_get_shortcode');

    // Create necessary meta boxes in posts.
    add_filter( 'cmb_meta_boxes' , 'm7red_create_post_metaboxes' );

    // Create necessary meta boxes w/ edit and save functionality in categories.
    add_action('category_add_form_fields', 'category_metabox_add', 10, 1);
    add_action('category_edit_form_fields', 'category_metabox_edit', 10, 1);
    add_action('created_category', 'save_category_metadata', 10, 1);
    add_action('edited_category', 'save_category_metadata', 10, 1);

    // Determine relations between posts and categories for vizualization.
//     add_action('init', 'm7red_create_relations_graphical');

  // Register project specific taxonomies.
  // Please note lower priority of 3 for nodosur_create_relations_graphical() as of
  // 1 for nodosur_register_taxonomies().
  // If taxonomies aren't registered, result of wp_get_post_terms() which is called in
  // m7red_create_relations_graphical() causes an 'invalid taxonomy' wp_error.
  add_action( 'init', 'm7red_register_taxonomies', 1);
  add_action( 'init', 'm7red_create_relations_graphical', 3);



    // Set author information - Overwrite orginal theme function here.
    if ( ! function_exists( 'imbalance2_posted_by' ) ) {
        imbalance2_posted_by();
    }

    m7red_get_all_categories();
} // end of m7red_child_theme_setup()

/**
 *  Add and register options.
 *
 */
function m7red_register_options() {
  add_option( 'm7red_version', M7RED_VERSION);
  add_option( 'm7red_display_auto', 1);
  add_option( 'm7red_display_reciprocal', 1);
  add_option( 'm7red_title', 'Entradas relacionadas');
  add_option( 'm7red_header_element', 'h2');
  add_option( 'm7red_default_css', 1);
  add_option( 'm7red_hide_if_empty', 0);
  add_option( 'm7red_text_if_empty', 'ninguna');
  add_option( 'm7red_order_type', 'manual');
  add_option( 'm7red_order', 'date_desc');
  add_option( 'm7red_graphics_zoom_init_scale', 1);

  $post_types = array('post');

  // if( $custom_post_types = get_post_types( array( '_builtin' => false ) ) ) {
  //     foreach( $custom_post_types as $custom_post_type ) {
  //         if ($custom_post_type !== '') {
  //           $post_types .= ",$custom_post_type";
  //         }
  //     }
  // }
  add_option( 'm7red_post_types', $post_types);
  add_option( 'm7red_combine_post_types', 1);
  add_option( 'm7red_show_thumbnails', 0);
  add_option( 'm7red_thumbnail_size', 'thumbnail');
  add_option( 'm7red_max_nodes_graph', 100); // Max nodes shown in graphics.
  add_option( 'm7red_refresh_graph', 30); // Create new file w/ graphics data after n minutes.
  add_option( 'm7red_threshold_create_new_file', 4); // Safety threshold for creating a new file w/ graphics data after n minutes.

  register_setting( 'm7red-options', 'm7red_version' );
  register_setting( 'm7red-options', 'm7red_display_auto' );
  register_setting( 'm7red-options', 'm7red_display_reciprocal' );
  register_setting( 'm7red-options', 'm7red_title' );
  register_setting( 'm7red-options', 'm7red_header_element' );
  register_setting( 'm7red-options', 'm7red_default_css' );
  register_setting( 'm7red-options', 'm7red_hide_if_empty' );
  register_setting( 'm7red-options', 'm7red_text_if_empty' );
  register_setting( 'm7red-options', 'm7red_order_type' );
  register_setting( 'm7red-options', 'm7red_order' );
  register_setting( 'm7red-options', 'm7red_post_types' );
  register_setting( 'm7red-options', 'm7red_combine_post_types' );
  register_setting( 'm7red-options', 'm7red_show_thumbnails' );
  register_setting( 'm7red-options', 'm7red_thumbnail_size' );
  register_setting( 'm7red-options', 'm7red_max_nodes_graph');
  register_setting( 'm7red-options', 'm7red_refresh_graph');
  register_setting( 'm7red-options', 'm7red_graphics_zoom_init_scale');
  register_setting( 'm7red-options', 'm7red_threshold_create_new_file');

  global $wpdb;
  // Check if post_relationships table exists, if not, create it
  $query = "SHOW TABLES LIKE '".$wpdb->prefix."post_relationships'";
  if( !count( $wpdb->get_results( $query ) ) ) {
      $query = "CREATE TABLE ".$wpdb->prefix."post_relationships (
                  post1_id bigint(20) unsigned NOT NULL,
                  post2_id bigint(20) unsigned NOT NULL,
                  position1 int(10) unsigned DEFAULT 0,
                  position2 int(10) unsigned DEFAULT 0,
                  PRIMARY KEY  (post1_id,post2_id)
              )";
      $create = $wpdb->query( $query );
  }
  else {
      $query = "SHOW COLUMNS FROM ".$wpdb->prefix."post_relationships LIKE 'position1'";
      $check_column_exist = $wpdb->get_results($query);
      if(!$check_column_exist){
          // Upgrading
          $query = "ALTER TABLE `".$wpdb->prefix."post_relationships` ADD COLUMN `position1` BIGINT(20) UNSIGNED NULL DEFAULT 0  AFTER `post2_id`";
          $add_ordering = $wpdb->query( $query );
          $query = "ALTER TABLE `".$wpdb->prefix."post_relationships` ADD COLUMN `position2` BIGINT(20) UNSIGNED NULL DEFAULT 0  AFTER `post2_id`";
          $add_ordering = $wpdb->query( $query );
          // Add new ordering settings
          update_option('m7red_order_type', 'auto');
      }
  }

  // Check and update version information.
  $current_version = get_option('m7red_version');

  if(!$current_version || version_compare($current_version, M7RED_VERSION, '<')) {
      update_option('m7red_version', M7RED_VERSION);
  }
}

/** Set theme specific favicon. */
function m7red_set_favicon_link() {
    echo '<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />' . "\n";
}

/** Register widgets */
function m7red_load_widgets() {
    register_widget( 'WP_Widget_M7red_Related_Posts' );
}

/** Create and register multiple taxonomies. */
function m7red_register_taxonomies() {
  $taxonomies = array(
    // Add new «Processes» taxonomy to posts.
    array(
      'slug' => 'process',
      'single_name' => __('Process', 'm7red'),
      'plural_name' => __('Processes', 'm7red'),
      'post_type' => 'post',
    )
  );

  foreach( $taxonomies as $taxonomy ) {
    $labels = array(
      'name' => __($taxonomy['plural_name']),
      'singular_name' => __($taxonomy['single_name']),
      'search_items' => __('Search', 'm7red') . ' ' . lcfirst(__($taxonomy['plural_name'])),
      'all_items' => __('All', 'm7red') . ' ' . lcfirst(__($taxonomy['plural_name'])),
      'parent_item' => __('Parent', 'm7red') . ' ' . lcfirst(__($taxonomy['single_name'])),
      'parent_item_colon' => __('Parent', 'm7red') . ' ' . lcfirst(__($taxonomy['single_name'])) . ':',
      'edit_item' => __('Edit', 'm7red') . ' ' . lcfirst(__($taxonomy['single_name'])),
      'update_item' => __('Update', 'm7red') . ' ' . lcfirst(__($taxonomy['single_name'])),
      'add_new_item' => __('Add new', 'm7red') . ' ' . lcfirst(__($taxonomy['single_name'])),
      'new_item_name' => __('New', 'm7red') .' '. lcfirst(__($taxonomy['single_name'])).' '. __('name'),
      'menu_name' => __($taxonomy['plural_name'])
    );

    register_taxonomy( $taxonomy['slug'], $taxonomy['post_type'], array(
      'hierarchical' => true,
      'labels' => $labels,
      'show_ui' => true,
      'query_var' => true,
      'public' => true,
      'show_tagcloud' => true,
      'show_in_nav_menus' => true,
      'rewrite' => array( 'slug' => $taxonomy['slug'] )
    ));
  }
}

/** Deregister jquery */
function m7red_change_default_jquery( &$scripts ) {
    if(!is_admin()){
        $scripts->remove( 'jquery');
        $scripts->add( 'jquery', false, array( 'jquery-core' ), '1.10.2' );
    }
}

/**
 * Load backend scripts.
 */
function m7red_load_backend_scripts() {
  wp_enqueue_style('jquery-ui');
  wp_enqueue_style('wp-color-picker');

  wp_register_style ('m7red_backend',
    M7RED_CSS_URI . '/backend.css', array(), M7RED_VERSION, 'all'
  );
  wp_enqueue_style('m7red_backend');

  wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-masonry');
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-tabs');
  wp_enqueue_script('wp-color-picker');

  wp_register_script('backend',
    M7RED_LIB_URI . '/backend.js',
    array('jquery', 'jquery-ui-core', 'wp-color-picker'), M7RED_VERSION, false
  );
  wp_enqueue_script('backend');

  // Call Media Uploader components.
  // Please make sure that jQuery has been loaded before!
  // If not, effect of forced «footer load» (see above) goes away because WordPress
  // will load this into page head.
  wp_enqueue_media();
}

/**
 * Load frontend scripts.
 */
function m7red_load_frontend_scripts() {
  wp_enqueue_style('jquery-ui');

  wp_register_style ('m7red-style',
    M7RED_ROOT_URI . '/style.css', array(), M7RED_VERSION, 'all'
  );
  wp_enqueue_style('m7red-style');

  // Dequeueing and deregistering some imbalance2 scripts.
//   wp_dequeue_script('jquery');
//   wp_dequeue_script('jquery_masonry');
//   wp_dequeue_script('jquery_ui');
//
//   wp_deregister_script('jquery');
//   wp_deregister_script('jquery_masonry');
//   wp_deregister_script('jquery_ui');

  // Enqueueing and registering scripts
//   wp_register_script('jquery');
//   wp_register_script('jquery-masonry');
//   wp_register_script('jquery-ui-core');
//   wp_register_script('jquery-ui-tabs');

//   wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-masonry');
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-tabs');

// Libraries from imbalance2. - Not used at present.
//   wp_enqueue_script('jquery-infinitescroll', IMBALANCE2_LIBS_DIR.'/jquery.infinitescroll.min.js', '', '', true);

  // D3.js (D3 = Data Driven Documents) is a JavaScript library for
  // manipulating documents based on data.
  wp_enqueue_script('d3', M7RED_LIB_URI.'/d3/d3.min.js', '', '', true);
  // d3.tip: Tooltips for d3.js visualizations.
  wp_enqueue_script('d3-tip', M7RED_LIB_URI.'/d3-tip/d3.tip.js', '', '', true);

  // js-session library for using cookie-less session vars in JavaScript.
  wp_register_script('json-serialization',
    M7RED_LIB_URI .'/js-session/json-serialization.js', '', '', true);
  wp_enqueue_script('json-serialization');
  wp_register_script('json-session',
    M7RED_LIB_URI .'/js-session/session.js', '', '', true);
  wp_enqueue_script('json-session');

  // slimScroll is a small jQuery plugin that transforms any div into a
  // scrollable area with a nice scrollbar.
  wp_enqueue_script('slimscroll',
    M7RED_LIB_URI.'/slimscroll/jquery.slimscroll.min.js',
    array('jquery', 'jquery-ui-core'), '', true);

  // m7red specific coding.
  wp_register_script('nodo-d3',
    M7RED_LIB_URI . '/nodo-d3.js',
    array(), M7RED_VERSION, true);
  wp_enqueue_script('nodo-d3');

  wp_register_script('frontend',
    M7RED_LIB_URI . '/frontend.js',
    array('jquery', 'jquery-ui-core', 'jquery-ui-tabs'),
            M7RED_VERSION, true);
  wp_enqueue_script('frontend');

  // Infinite Scroll to dynamically load fresh content into a site
  // as a user scrolls down through it.
  wp_register_script( 'infinite_scroll',
    M7RED_LIB_URI . '/infinitescroll/jquery.infinitescroll.min.js',
    array('jquery'), null, true);
  if (! is_singular()) {
    wp_enqueue_script('infinite_scroll');
  }

  // Call Media Uploader components.
  // Please make sure that jQuery has been loaded before!
  // If not, effect of forced «footer load» (see above) goes away because WordPress
  // will load this into page head.
  wp_enqueue_media();
}

/**
 * Remove editor from dashboard menu.
 *
 * This code will remove the Editor menu from the dashboard
 * so that users cannot accidentally ruin their own website.
 */
function m7red_remove_editor_menu() {
    remove_action('admin_menu', '_add_themes_utility_last', 101);
}

/**
 * Determine all associated post ids for a given custom taxonomy.
 *
 *
 * @return array Post ids
 */
function m7red_get_all_posts_for_taxonomy($single_term_title, $taxonomy) {
  // Get term.
  $term = get_term_by('name', $single_term_title, $taxonomy);
  // Preparate query.
  $args = array(
    'posts_per_page' => -1,       // all posts
    'cache_results' => false,     // performance tuning
    'post_type' => 'post',
    'post_status' => 'publish',
    'fields' => ids,              // we get only the ids
    'tax_query' => array(         // where clause...
      array(
        'taxonomy' => $taxonomy,
        'field' => 'slug',
        'terms' => $term->slug
      )
    )
  );
  // Get all posts of given term.
  $posts = get_posts($args);
  return $posts;
}

/** Custom meta boxes for dealing with custom specific fields. */
function m7red_initialize_cmb_meta_boxes() {
    if (!class_exists('cmb_Meta_Box')) {
        require_once( M7RED_LIB_DIR . '/metabox/init.php' );
    }
}

/* Set an m7red specific login logo. */
function m7red_set_login_logo() {
    $login_logo = M7RED_IMAGES_URI.'/m7red-login-logo.png';
    echo '<style type="text/css">';
    echo 'body.login div#login h1 a {';
    echo 'width: 58px;';
    echo 'height: 50px;';
    echo 'background: url('.$login_logo.') no-repeat top center !important;';
    echo 'padding-bottom: 16px;';
    echo '}';
    echo '</style>';
}

/**  Set author
 * overwrite the original theme function...
 */
function imbalance2_posted_by() {
    printf( __( '<span class="meta-sep">por</span> %1$s', 'm7red' ),
        sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
            get_author_posts_url( get_the_author_meta( 'ID' ) ),
            sprintf( esc_attr__( 'View all posts by %s', 'm7red' ), get_the_author() ),
            get_the_author()
        )
    );
}

function m7red_get_category_post_view() {
    if ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
        $posted_in = __( '%1$s', 'm7red' );
    } else {
        $posted_in = __( 'Bookmark the <a href="%2$s" title="Permalink to %3$s" rel="bookmark">permalink</a>.', 'm7red' );
    }
    printf(
        $posted_in,
        get_the_category_list( ', ' ),
        get_permalink(),
        the_title_attribute( 'echo=0' )
    );
}

function m7red_get_tags_post_view() {
    $tag_list = get_the_tag_list( '', ', ' );
    if ( $tag_list ) {
        printf('<div class="entry-tags">%1$s</div>', $tag_list );
    }
}

function m7red_get_short_description_post_view() {
    // Get post excerpt (from field directly or from post text).
    $post_excerpt = trim(get_post_field('post_excerpt', get_the_ID()));
    if (!$post_excerpt) {
      $post_excerpt = get_the_excerpt();
    }
    // Cut off excerpt to 140 characters.
    $post_excerpt = m7red_cut_off_text($post_excerpt, 140);
    echo $post_excerpt;
}

/** Set the data visualization box on front page. */
function m7red_set_graph_container() {
    if ( is_home() || is_front_page() ||
          is_single() || is_category() || is_tag() || is_tax() ) {
        echo '<div id="graph_container"></div>';
        echo '<div id="node-info" class="tooltip" style="display: none;"></div>';
        echo '<div style="float:right;">';
//         echo '<button id="graph-refresh-btn" class="clean-gray" style="width: 110px; margin-top: 10px;">reescalar grafo</button>&nbsp;';
        echo '</div>';
        echo '<div style="float:right;">';
//         echo '<form name="sel_gtype" id="sel_gtype" style="color:#999999; margin-top:15px;">';
//         if (is_home()) { // Set graphical selection possibility for home page only.
//             echo '<input type="radio" name="gtype"  value="0" checked />force directed&nbsp;&nbsp;';
//             echo '<input type="radio" name="gtype"  value="1" />force atlas&nbsp;&nbsp;';
//         } else {
//             echo '<input type="radio" name="gtype"  value="0" checked />force directed&nbsp;&nbsp;';
//         }
//         echo '</form>';
        echo '</div>';
    }
}

/** Create necessary meta boxes in posts. */
function m7red_create_post_metaboxes( $metaboxes ) {
    // Start with an underscore to hide fields from custom fields list.
    global $prefix;

    $metaboxes['m7red_short_title'] = array(
        'id'         => 'm7red_metabox',
        'title'      => __( 'm7red espec&iacute;fico', 'm7red' ),
        'pages'      => array( 'post', ), // Post type
        'context'    => 'normal',
        'priority'   => 'high',
        'show_names' => true, // Show field names on the left
        // 'cmb_styles' => true, // Enqueue the CMB stylesheet on the frontend
        'fields'     => array(
            array(
                'name' => __('Eslogan', 'm7red'),
                'desc' => __('T&iacute;tulo de entrada para la visualizaci&oacute;n gr&aacute;fica.', 'm7red'),
                'std' => '',
                'id' => $prefix . 'short_title',
                'type' => 'text_medium'
            ),
        )
    );

    return $metaboxes;
}

/** Create necessary meta boxes in categories. */
// Add metaboxes
function category_metabox_add($tag) { ?>
    <div class="form-field">
        <label for="category_color"><?php _e('Color del nodo') ?></label>
        <input name="category_color" id="category_color" class="colorpicker" type="text" value="#ec5148" data-default-color="#ec5148" size="10" />
        <p class="description"><?php _e('Color de la categor&iacute;a para la visualizaci&oacute;n gr&aacute;fica. Introducci&oacute;n manual es hexadecimal, por ejemplo #FF0000 es un tono de rojo.'); ?></p>
    </div>

    <div class="form-field">
        <label for="category_size"><?php _e('Tama&ntilde;o del nodo') ?></label>
        <select name="category_size" id="category_size">
            <option value="2">2px</option>
            <option value="3">3px</option>
            <option value="4">4px</option>
            <option value="5">5px</option>
            <option value="6" selected>6px</option>
            <option value="7">7px</option>
            <option value="8">8px</option>
            <option value="8">9px</option>
            <option value="10">10px</option>
        </select>
        <p class="description"><?php _e('Seleccione el tama&ntilde;o en p&iacute;xeles que desea. Nodos se visualizan como c&iacute;rculos.'); ?></p>
    </div>
<?php
} // end category_metabox_add()

// Edit metabox content.
function category_metabox_edit($tag) { ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_color"><?php _e('Color del nodo'); ?></label>
        </th>
        <td>
            <input name="category_color" id="category_color" class="colorpicker" type="text" value="<?php echo m7red_get_term_meta($tag->term_id, 'category_color', true); ?>" size="10" />
            <p class="description"><?php _e('Color de la categor&iacute;a para la visualizaci&oacute;n gr&aacute;fica. Introducci&oacute;n manual es hexadecimal, por ejemplo #FF0000 es un tono de rojo.'); ?></p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_size"><?php _e('Tama&ntilde;o del nodo'); ?></label>
        </th>
        <td>
            <select name="category_size" id="category_size">
                <?php
                    $sizes = array('2', '3', '4', '5', '6', '7', '8', '9', '10');
                    $issel_size = m7red_get_term_meta($tag->term_id, 'category_size', true);
                    foreach ($sizes as $size) {
                        $option = '<option value="'.$size.'"';
                        $option .= ($size == $issel_size) ? ' selected>' : '>';
                        $option .= $size.'px</option>';
                        echo $option;
                    }
                ?>
            </select>
            <!-- <input name="image-url" id="image-url" type="text" value="<?php echo m7red_get_term_meta($tag->term_id, 'category_size', true); ?>" size="40" /> -->
            <p class="description"><?php _e('Seleccione el tamaño en píxeles que desea. Nodos se visualizan como círculos.'); ?></p>
        </td>
    </tr>
<?php
} // end category_metabox_edit()

// Save metabox content.
function save_category_metadata($term_id) {
    if (isset($_POST['category_size'])) {
        m7red_update_term_meta( $term_id, 'category_size', $_POST['category_size']);
    }

    if (isset($_POST['category_color'])) {
        m7red_update_term_meta( $term_id, 'category_color', $_POST['category_color']);
    }
}

/**
 * Related Posts by Category
 * Textual representation
 *
 * This algorithm will find other posts within the same
 * category as the current post, and it will list them as
 * related posts. The advantage of this technique is that
 * you will never have a blank spot for your related
 * posts section.
 */
function m7red_create_relations_by_category_textual() {
    $orig_post = $post;
    global $post;

    $categories = get_the_category($post->ID);
    if ($categories) {
        $category_ids = array();
        foreach($categories as $individual_category) {
            $category_ids[] = $individual_category->term_id;
        }
        $args = array(
            'category__in' => $category_ids,
            'post__not_in' => array($post->ID),
            'posts_per_page' => POSTS_PER_PAGE, // Number of related posts that will be shown.
            'caller_get_posts' => 1
        );
        $my_query = new wp_query($args);

        if ($my_query->have_posts()) {
            echo '<div class="related_posts">';
            echo '<h2>Entradas relacionadas</h2>';
            echo '<ul>';
            while ($my_query->have_posts()) {
                $my_query->the_post();
                echo '<li>';
                    // Show thumbnail.
                    // echo '<div class="related_thumb">';
                    // echo '<a href="'.get_permalink($post->ID).'" rel="bookmark" title="'.$title.'">'.
                    //     get_the_post_thumbnail($post->ID).'</a>';
                    // echo '</div>';

                    // Show content.
                    echo '<div class="related_content">';

                    // Title an info.
                    echo '<h3><a href="'.get_permalink($post->ID).'" rel="bookmark" title="'
                        .get_the_title($post->ID).'">'.get_the_title($post->ID).'</a></h3>';
                    echo 'por&nbsp;<a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) )
                        .'" title="'.esc_attr( sprintf( __( "Ver todas las entradas por %s" ), get_the_author() ) ).'">'
                        .get_the_author().'</a>';
                    echo '&nbsp;&nbsp;'.get_the_time('d/m/Y').'&nbsp;&nbsp;';

                    // Categories
                    $categories = get_the_category($post->ID);
                    $separator = ', ';
                    $output = '';
                    if ($categories) {
                        foreach ($categories as $category) {
                            $output .= '<a href="'.get_category_link( $category->term_id ).'" title="'
                            .esc_attr( sprintf( __( "Ver todas las entradas en %s" ), $category->name ) )
                            . '">'.$category->cat_name.'</a>'.$separator;
                        }
                        echo trim($output, $separator);
                    }
                    echo '</div>';
                echo '</li>';
            } // end-while
            echo '</ul>';
            echo '</div>';
        }
    }
    $post = $orig_post;
    wp_reset_query();
}

/**
 * Related Posts by Posts or Category
 * Graphical representation
 *
 * Determines relations between posts and posts/categories
 * directly from database tables and writes it's result
 * into a json file.
 */
function m7red_create_relations_graphical($base) {
    global $wpdb;
    global $categories;
    global $prefix;

    // Change this to «false» for test proposals only!!!
    // Otherwise graphics will be always regenerated.
    $last_file_mod_check = true;

    // Check refresh intervall value in options.
    $refresh_graph = (int) get_option( 'm7red_refresh_graph' );

    // Check threshold in options.
    $threshold_create_new_file = (int) get_option('m7red_threshold_create_new_file');

    // Check last file modification.
    $posts_relations_file = M7RED_DATA_DIR . '/post_relations_d3.json';

    // Creation of new file necessary?
    if ($last_file_mod_check) {
      if ( file_exists($posts_relations_file) ) {
          // Get difference in minutes between last file modification and now.
          $now = time();
          $seconds = $now - filemtime($posts_relations_file);
          $minutes = (int) floor( $seconds / (int) 60 );

          // Refresh intervall not yet exceeded?
          if ( $minutes < $refresh_graph ) {
              return;
          }

          // Threshold not yet exceeded? - See selection in options page for value.
          if ( $minutes <= $threshold_create_new_file ) {
              return;
          }
      }
    }

    // Set max posts value selected for graphics.
    $max_nodes_graph = (int) get_option( 'm7red_max_nodes_graph' );
    $max_nodes_graph = ($max_nodes_graph > 0) ? $max_nodes_graph : 5000;

    // get all categories which has posts.
    m7red_get_all_categories();

    // get published posts - at the moment there is a limit here!
    $posts = $wpdb->get_results($wpdb->prepare(
        "SELECT ID, post_title FROM $wpdb->posts"
        ." WHERE post_status = %s"
        ." AND post_type = %s"
        ." AND (post_title != %s OR post_title IS NOT NULL) LIMIT "
        .(string)$max_nodes_graph,
        'publish', 'post', ''
    )); // 3 parameters. Note: Last prevents also an empty string for post title!

    $color = NODE_COLOR;
    $size = NODE_SIZE;

    // Set also a default decimal color number for color. Necessary for graphics.
    $color_hex = str_replace('#', '', $color);
    $color_dec_default = hexdec($color_hex);

    $base = 'post'; // For posts only.

    $base = $base || false;

    ////////////////////////////////////////////////////////////
    // post based visualization part (default).
    ////////////////////////////////////////////////////////////
    if ($base == 'post') {
        $response = array();
        $force_directed = array();
        ////////////////////////
        // get/set nodes.
        ////////////////////////
        $i = 0;
        $nodes = array();
        $nodes_fd = array();

        foreach ($posts as $post_entry) {
            // get cluster information from related category.
            $cats = get_the_category($post_entry->ID); // this should be only one category per post.
            if ($cats) {
                $cat_id = false;
                $cat_name = '';
                foreach ($cats as $cat) {
                    if ($cat->cat_ID) {
                        $cat_id = $cat->cat_ID;
                        $cat_name = $cat->name;
                        break;
                    }
                }
                if ($cat_id) {
                    foreach ($categories as $cat) {
                        if ($cat_id == $cat->cat_ID) {
                            $color = $cat->color;
                            // Set a unique decimal color number for color. Necessary for graphics.
                            $color_hex = str_replace('#', '', $cat->color);
                            $color_dec = hexdec($color_hex);
                            $color_dec = ($color_dec == 0) ? $color_dec_default : $color_dec;
                            // echo '<br>'.$color_dec;

                            $size = (int)$cat->size;
                            break;
                        }
                    }
                }
            }

            // Get post processes.
            $post_processes = '';
            $post_processes_commasep = '';
            $post_id = $post_entry->ID;
            $processes = wp_get_post_terms($post_id, 'process', array("fields" => "names"));

            if ($processes && !is_wp_error($processes)) {
              $tmp_array = array();
              foreach($processes as $process) {
                $post_processes .= $process.' ';
                $tmp_array[] = $process;
              }
              $post_processes_commasep = implode(', ', $tmp_array);
              // There's a problem with selected taxonomy.
            } elseif (is_wp_error($processes)) {
              $post_processes_commasep = $processes->get_error_message();
            } else {
              $post_processes = ' ';
              $post_processes_commasep = ' ';
            }

            // Get post tags.
            $post_tags = '';
            $post_tags_commasep = '';
            $tags = get_the_tags($post_entry->ID);
            if ($tags) {
              $tmp_array = array();
              foreach($tags as $tag) {
                $post_tags .= $tag->name.' ';
                $tmp_array[] = $tag->name;
              }
              $post_tags_commasep = implode(', ', $tmp_array);
            } else {
              $post_tags = ' ';
              $post_tags_commasep = ' ';
            }

            $color = empty($color) ? NODE_COLOR : $color;
            $size = empty($size) ? NODE_SIZE : $size;

            // Get post excerpt (from field directly or from post text).
            $post_excerpt = trim(get_post_field('post_excerpt', $post_entry->ID));
            if (is_wp_error($post_excerpt) || empty($post_excerpt) || $post_excerpt === ' ') {
              $post_excerpt = apply_filters('the_excerpt',
                get_post_field('post_content', $post_entry->ID)
              );
              // Strip html tags and shortcodes away.
              $post_excerpt = wp_strip_all_tags($post_excerpt);
              $post_excerpt = strip_shortcodes($post_excerpt);
              // Strip images and videos tags away.
              $post_excerpt = preg_replace("/<img[^>]+\>/i", " ", $post_excerpt);
              $post_excerpt = preg_replace("/<iframe[^>]+\>/i", " ", $post_excerpt);
            }
            // Cut off excerpt to 140 characters.
            if ($post_excerpt) {
              $post_excerpt = m7red_cut_off_text($post_excerpt, 140);
            } else {
              $post_excerpt = '---'; // No text available in post.
            }

            // Set tooltip content - title, category, processes, tags, excerpt.
            $excerpt =
              '<table style="border-collapse:collapse; color:#ffffff; width:250px">'
              .'<tr><td colspan="2" style="font-size:12px; font-weight:600; padding-bottom:3px">'
                .get_post_meta($post_entry->ID, $prefix.'short_title', true)
              .'</td></tr>'
              .'<tr><td width="13px" style="vertical-align:middle;">'
                .'<div class="cat_circle" style="padding:0; display:inline-block; background-color:'.$color.'">'
                .'</div></td><td style="color:#e3e3e3; font-size:12px;">'.$cat_name
              .'</td></tr>'
              .'<tr><td colspan="2" style="color:#e3e3e3; font-size:10px;">'
                .'&#35;&nbsp;&nbsp;'.$post_processes_commasep
              .'</td></tr>'
              .'<tr style="border-bottom:1px solid #727272;">'
                .'<td colspan="2" style="padding-top:5px; padding-bottom:5px; color:#e3e3e3; font-size:10px;">'
                .'<span class="lsf" style="font-size:12px;">&#xE128;&nbsp;</span>'.$post_tags_commasep
              .'</td></tr>'
              .'<tr><td colspan="2" style="padding-top:5px; font-size:12px;">'
                .$post_excerpt
              .'</td></tr>'
              .'</table>';

            $node = array(
                'id' => (string)$post_entry->ID,
                'label' => get_post_meta($post_entry->ID, $prefix.'short_title', true), // eslogan
                'short_title' => get_post_meta($post_entry->ID, $prefix.'short_title', true), // eslogan
                'url' => get_permalink($post_entry->ID),
                // 'excerpt' => $excerpt . ' ' . $read_more,
                'excerpt' => $excerpt, // breve descripción
                'x' => '',
                'y' => '',
                'type' => NODE_TYPE,
                'color' => $color, // color de categoría
                'borderColor' => $color, // color de categoría
                'cluster' => $cat_name, // nombre de categoría
                'tags' => $post_tags, // etiquetas
                'size' => $size // tamaño de categoría
                // 'weight' => '8.0'
            );
            $nodes[$i++] = $node;

            $node_fd = array(
                'id' => (string)$post_entry->ID,
                'label' => get_post_meta($post_entry->ID, $prefix.'short_title', true), // eslogan
                'short_title' => get_post_meta($post_entry->ID, $prefix.'short_title', true), // eslogan
                'url' => get_permalink($post_entry->ID),
                'excerpt' => $excerpt, // breve descripción
                'color' => $color, // color de categoría
                'border_color' => $color, // color de categoría
                'cluster' => $cat_name, // nombre de categoría
                'tags' => $post_tags, // etiquetas
                'size' => $size // tamaño de categoría
            );
            $nodes_fd[$post_entry->ID] = $node_fd;
        } // end foreach
        $response['posts']['nodes'] = $nodes;

        $force_directed['posts']['nodes_attr'] = $nodes_fd;

        /////////////////////////
        // get/set edges (links).
        /////////////////////////
        $links = array();
        $edges = array();
        $table_name = $wpdb->prefix . 'post_relationships';
        $post_relationships = $wpdb->get_results("SELECT post1_id, post2_id FROM {$table_name} WHERE 1");
        $i = 0;
        $j = 0;
        foreach ($post_relationships as $postrel) {
            // Check if post id in relationships table is also a valid post id.
            // If exists, apply related post as edge source.
            $is_post_entry = get_post($postrel->post1_id);
            $is_published = false;
            if ($is_post_entry) {
                $is_published = (get_post_status($postrel->post1_id) == 'publish') ? true : false;
            }
            $is_valid_post_id = ($is_post_entry && $is_published) ? true : false;

            if ($is_valid_post_id) {
                $edge = array(
                    'id' => (string)$i,
                    'source' => $postrel->post1_id,
                    'target' => $postrel->post2_id,
                    'type' => 'line', // 'curve' or 'line'.
                    'Arrow' => 'source'
                );
                $edges[$i++] = $edge;

                $link = array(
                    'source' => $postrel->post1_id,
                    'target' => $postrel->post2_id
                );
                $links[$j++] = $link;
            }
        }
        $response['posts']['edges'] = $edges;

        $force_directed['posts']['links'] = $links;
    }

  ////////////////////////
  // Write json file.
  ////////////////////////
  $fp = fopen($posts_relations_file, 'w');
  if ($fp) {
    fwrite($fp, json_encode($force_directed['posts']));
    fclose($fp);
  }
}

/**
 * Cuts off a given string to a given number of characters.
 * It makes also sure that truncating the string to the nearest
 * whole word, while staying under the maximum string length.
 * In this version a '...' is also append at the end of string.
 * @param string  $text     source string
 * @param integer $cut_off  character limit
 * @return string $text     cutted text
 */
function m7red_cut_off_text($text, $cut_off = 150) {
  $append = "&hellip;"; // html code for '...'.
  if ($text && strlen($text) > $cut_off) {
    $text = substr($text, 0,
      strrpos(substr($text, 0, $cut_off - 3), ' ')
    );
    $text .= $append;
  }
  return $text;
}

/**
 * m7red_delete_relationships - Delete all relationships for a post
 *
 * @param int   $post_id - The id of the post for which the relationships are deleted
 */
 function m7red_delete_relationships( $post_id ) {
    global $wpdb;
    if(get_option('m7red_display_reciprocal')) {
        $query = "DELETE FROM ".$wpdb->prefix."post_relationships WHERE post1_id = $post_id OR post2_id = $post_id";
    }
    else {
        $query = "DELETE FROM ".$wpdb->prefix."post_relationships WHERE post1_id = $post_id";
    }
    $delete = $wpdb->query( $query );
}

/**
 * m7red_get_related_posts - Get the related posts for a post
 *
 * @param int       $post_id - The id of the post
 * @param bool      $return_object - Whether to return the related posts as an object
 *                  If false it will return the posts as an array $related_posts[related_post_id] => related_post_title
 * @param bool      $hide_unpublished - When false drafts will be included
 * @param string    $post_type - The post type of the related posts to return i.e. post, page, or any custom post types
 *                  When null all post types will be returned
*/
function m7red_get_related_posts( $post_id, $return_object = false, $hide_unpublished = true, $post_type = null ) {
    global $wpdb;
    $post_status = array( "'publish'" );
    // Display private posts for users with the correct permissions
    if( current_user_can( "read_private_posts" ) ) {
        $post_status[] = "'private'";
    }
    // Generate order SQL based on themes settings
    $order = " ORDER BY ";
    if(get_option('m7red_order_type') == "manual") {
        $order .= " `position_unified` ASC, `post_date` ASC "; //sort by date related items inside all posts with unspecified ordering
    }
    else {
        switch( get_option('m7red_order') ) {
            case 'random' :
                $order .= " RAND() ";
            break;
            case 'date_asc' :
                $order .= " post_date ASC ";
            break;
            case 'title_desc' :
                $order .= " post_title DESC ";
            break;
            case 'title_asc' :
                $order .= " post_title ASC ";
            break;
            default: // date_desc
                $order .= " post_date DESC ";
            break;

        }
    }
    if(get_option('m7red_display_reciprocal')) {
        // Reciprocal query by Peter Raganitsch @ http://blog.oracleapex.at)
        $query = "
        SELECT * FROM (
        SELECT position2 as position_unified, wp.*, wpr.* ".
            "FROM ".$wpdb->prefix."post_relationships   wpr ".
            ",".$wpdb->prefix."posts                    wp ".
            "WHERE wpr.post1_id = $post_id ".
            "AND wp.id = wpr.post2_id ";
        // Hide unpublished?
        if( $hide_unpublished ) {
            $query .= " AND wp.post_status IN (".implode( ",", $post_status ).") ";
        }
        // Show only specified post type?
        if( isset( $post_type ) ) {
            if( is_array( $post_type ) ) {
                $query .= " AND wp.post_type IN (".implode( ",", $post_type ).") ";
            }
            else {
                $query .= " AND wp.post_type = '$post_type' ";
            }
        }
        //$query .= $order;
        $query .= ") AS tab1 UNION ALL ".
            "SELECT * FROM (".
            "SELECT position1 as position_unified, wp.*, wpr.* ".
            "FROM ".$wpdb->prefix."post_relationships   wpr ".
            ",".$wpdb->prefix."posts                    wp ".
            "WHERE wpr.post2_id = $post_id ".
            "AND wp.id = wpr.post1_id ";
        // Hide unpublished?
        if( $hide_unpublished ) {
            $query .= "AND wp.post_status IN (".implode( ",", $post_status ).") ";
        }
        // Show only specified post type?
        if( isset( $post_type ) ) {
            if( is_array( $post_type ) ) {
                $query .= " AND wp.post_type IN (".implode( ",", $post_type ).") ";
            }
            else {
                $query .= " AND wp.post_type = '$post_type' ";
            }
        }
        $query.= ") AS tab2";
        // Add order SQL
        $query .= $order;
        //echo $query;die();
    }
    // Not reciprocal
    else {
        $query = "SELECT *, position1 AS position_unified ".
            "FROM ".$wpdb->prefix."post_relationships   wpr ".
            " JOIN ".$wpdb->prefix."posts               wp ".
            "   ON wpr.post2_id = wp.ID ".
            "WHERE wpr.post1_id = $post_id";
        // Hide unpublished?
        if( $hide_unpublished) {
            $query .= " AND wp.post_status IN (".implode( ",", $post_status ).") ";
        }
        // Show only specified post type?
        if( isset( $post_type ) ) {
            if( is_array( $post_type ) ) {
                $query .= " AND wp.post_type IN (".implode( ",", $post_type ).") ";
            }
            else {
                $query .= " AND wp.post_type = '$post_type' ";
            }
        }
        $query .= $order;
    }
    // Run query
    $results = $wpdb->get_results( $query );
    if( $results ) {
        if( $return_object ) {
            // Return the complete results set as an object
            return $results;
        }
        else {
            // Create array (legacy)
            $related_posts = array();
            foreach( $results as $result ) {
                $related_posts[$result->ID] = $result->post_title;
            }
            return $related_posts;
        }
    }
    return null;
}

/**
* m7red_set_related_posts_ajax_search_result - Display AJAX search results
*
*/
function m7red_set_related_posts_ajax_search_result() {
    global $wpdb;
    $s = $wpdb->_escape( rawurldecode( $_GET['arp_s'] ) );
    $scope = (int) $_GET['arp_scope'];
    $post_type = $wpdb->_escape( $_GET['arp_post_type'] );
    $regexp = "[[:<:]]" . $s;
    $where = "";
    switch( $scope ) {
        case 1 :
            $where = "post_title REGEXP '$regexp'";
            break;
        case 2 :
            $where = "post_content REGEXP '$regexp'";
            break;
        default :
            $where = "( post_title REGEXP '$regexp' OR post_content REGEXP '$regexp' )";
            break;
    }
    $query = "SELECT ID, post_title, post_type, post_status FROM $wpdb->posts WHERE $where AND post_type = '$post_type' ";
    if( $_GET['arp_id'] ) {
        $this_id = (int) $_GET['arp_id'];
        $query .= " AND ID != $this_id ";
    }
    $query .= " AND post_status NOT IN ('inherit', 'auto-draft')";
    $query .= " ORDER BY post_date DESC LIMIT 50";
    $results = $wpdb->get_results( $query );
    if( $results ) {
        echo "<ul>";
        $n = 1;
        foreach( $results as $result ) {
            echo '<li';
            echo ( $n % 2 ) ? ' class="alt"' : '';
            echo '> <a href="javascript:void(0)" id="result-'.$result->ID.'" title="Añadir entrada" class="m7red_result">';
            echo $result->post_title;
            if( $result->post_status != 'publish') {
                echo ' ('.$result->post_status.')';
            }
            echo '</a> <a href="'.get_permalink( $result->ID ).'" title="Mostrar entrada" class="m7red_view_post" target="_blank">&rsaquo;</a></li>';
            $n++;
        }
        echo "</ul>";
    }
}

/**
 * m7red_add_meta_custom_box_to_edit_posts - Add the related posts custom box to the post add/edit screen
 */
function m7red_add_meta_custom_box_to_edit_posts() {
    foreach( m7red_get_supported_post_types() as $post_type ) {
        add_meta_box( 'm7red_relatedposts_sectionid', __( 'Entradas relacionadas', 'm7red' ), 'm7red_set_meta_custom_box_in_posts_edit', $post_type, 'normal' );
    }
}

/**
 * m7red_get_supported_post_types - Get the post types that can display and be related posts
 *
 * @param bool  $details -  Whether to return the entire object for each post type,
 *                          if false only an array of names will be returned
 */
function m7red_get_supported_post_types($details = false) {
// Changed because there's an error in options page (during save...)
//     $post_types = explode(',', get_option('m7red_post_types'));
    $post_types = array('post');

    if (false === $details) {
        return $post_types;
    }
    $details = array();
    foreach ($post_types as $post_type) {
        $post_type_details = get_post_types(array('name' => $post_type), 'object');
        $details[$post_type] = $post_type_details[$post_type];
    }
    return $details;
}

/**
 * m7red_save_related_postdata - Prepare to save the selected relations
 *
 * @param int   $post_id - The id of the post being saved
 */
function m7red_save_related_postdata( $post_id ) {
    if( !isset($_POST['m7red_noncename'])) {
        return $post_id;
    }
    if ( !wp_verify_nonce( $_POST['m7red_noncename'], M7RED_THEME_NAME )) {
        return $post_id;
    }
    if ( 'page' == $_POST['post_type'] ) {
        if ( !current_user_can( 'edit_page', $post_id )) {
            return $post_id;
        }
    }
    else {
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }
    // Do not create a relationship with the revisions and autosaves in WP 2.6
    if( function_exists("wp_is_post_revision") ) {
        if( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return $post_id;
        }
    }
    m7red_save_relationships( $post_id, $_POST['m7red_related_posts'] );
}

/**
 * m7red_search_related_posts_ajax_listener - Listen for AJAX call from search box
 */
function m7red_search_related_posts_ajax_listener() {
    if( is_admin() && isset( $_GET['arp_s'] ) ) {
        m7red_set_related_posts_ajax_search_result();
        exit;
    }
    return;
}

/**
 * m7red_show_related_posts - Display the related posts for the current post
 */
function m7red_show_related_posts() {
    global $post;
    if( $post ) {
        echo m7red_get_related_posts_html( $post->ID );
    }
}

/**
 * m7red_set_auto_related_posts()
 *  Called by the_content filter, automatically add the
 * related posts at the bottom of the content.
 *
 * @param string    $content - The text/content of the post
 */
function m7red_set_auto_related_posts( $content ) {
    if( is_single() || is_page() ) {
        if( get_option('m7red_display_auto') ) {
            global $post;
            if( $post ) {
                $related_posts_html = m7red_get_related_posts_html( $post->ID );
            }
            $content .= $related_posts_html;
        }
    }
    return $content;
}

/**
 * m7red_get_related_post_title - Replaces %posttype% from the title with the name/label of the post type
 *
 * @param string    $title - The title as set on the themes options page
 * @param string    $post_type_name - The name/label of the post type
 */
function m7red_get_related_post_title( $title, $post_type_name = null ) {
    if( $post_type_name != 'm7red_all' ) {
        $post_type = array_shift( get_post_types( array( 'name' => $post_type_name ), 'object' ) );
        $title = str_replace( '%posttype%', __( $post_type->label, 'm7red' ), $title );
    }
    else {
        $title = str_replace( '%posttype%', __( 'Posts' ), $title );
    }
    return $title;
}

/**
 * m7red_get_shortcode - Add [related-post] shortcode support through standard Worpress API
 *
 * @param array    $atts - An array of attributes given to the shortcode i.e. [related-posts posttype=page]
 */
function m7red_get_shortcode( $atts ) {
    global $post;
    $post_type = null;
    if( $post->ID ) {
        if( $atts ) {
            $post_type = esc_attr( $atts['posttype'] );
        }
        return m7red_get_related_posts_html( $post->ID, true, $post_type );
    }
}

/**
 * Loads addional selection of related posts into posts add/edit screen.
 */
function m7red_set_meta_custom_box_in_posts_edit() {
    global $post_ID;
    $post_types = m7red_get_supported_post_types();
    $order_class = (get_option('m7red_order_type') == 'manual') ? ' class="m7red_manual_order"' : '';
    echo '<div id="m7red_relatedposts"'.$order_class.'>';
    $n = 1;
    echo '<div id="m7red_tabs">';
    $related_posts = array();
    // Create tabs
    foreach($post_types as $post_type) {
        $related_posts[$post_type] = m7red_get_related_posts( $post_ID, 1, 0, $post_type );
        $ext = "-".$n;
        $post_type_details = array_shift(get_post_types(array('name' => $post_type), 'objects'));
        $current = ($n==1) ? ' m7red_current_tab' : '';
        $related_posts_count = count($related_posts[$post_type]);
        echo '<div class="m7red_tab'.$current.'"><a href="javascript:void(0)" rel="m7red_post_type'.$ext.'">'.__( $post_type_details->labels->name, 'm7red' ).' (<span id="m7red_related_count'.$ext.'" class="m7red_related_count">'.$related_posts_count.'</span>)</a></div>';
        $n++;
    }
    echo '</div>';
    $n = 1;
    // Loop through post types and create form elements for each
    foreach($post_types as $post_type) {
        $ext = "-".$n;
        $current = ($n==1) ? ' m7red_current' : '';
        echo '<div id="m7red_post_type'.$ext.'" class="m7red_post_type'.$current.'">';
        echo '<label id="m7red_relatedposts_list_label'.$ext.'" class="m7red_relatedposts_list_label" style="font-weight:bold;">'.__( 'relacionadas', 'm7red' ).'</label>';
        echo '<ul id="m7red_relatedposts_list'.$ext.'" class="m7red_relatedposts_list">';
        if( $post_ID ) {
            if( count($related_posts[$post_type]) > 0 ) {
                foreach( $related_posts[$post_type] as $related_post ) {
                    $post_title = m7red_truncate_related_posts( $related_post->post_title, 80);
                    if( $related_post->post_status != 'publish' ) {
                        $post_title = $post_title . ' ('.$related_post->post_status.')';
                    }
                    echo '<li id="related-post_'.$related_post->ID.'"><span class="m7red_moovable"><strong>'.$post_title.' </strong> <span class="m7red_related_post_options"><a href="'.get_permalink( $related_post->ID ).'" class="m7red_view_post" target="_blank">&rsaquo;</a><a class="m7red_deletebtn" title="Eliminar entrada" onclick="m7red_remove_relationship(\'related-post_'.$related_post->ID.'\')">X</a></span></span>';
                    echo '<input type="hidden" name="m7red_related_posts[m7red_post_type-'.$n.'][]" value="'.$related_post->ID.'" /></li>';
                }
            }
            else {
                echo '<li id="m7red_related_posts_replacement'.$ext.'" class="m7red_related_posts_replacement howto" style="color:#AAA;font-style:italic;margin:2px;"><span>'.__( 'Utilice el buscador de abajo para seleccionar las entradas relacionadas.', 'm7red' ).'</span></li>';
            }
        }
        else {
            echo '<li id="m7red_related_posts_replacement'.$ext.'" class="m7red_related_posts_replacement" style="color:#AAA;font-style:italic;margin:2px;"><em>'.__( 'Utilice el buscador de abajo para seleccionar las entradas relacionadas.', 'm7red' ).'</em></li>';
        }
        echo '</ul>';

        echo '<input type="hidden" name="m7red_post_type_name'.$ext.'" id="m7red_post_type_name'.$ext.'" value="'.$post_type.'"/>';
        echo '<div id="m7red_add_related_posts'.$ext.'" class="m7red_add_related_posts"><label for="m7red_search" id="m7red_search_label'.$ext.'" class="m7red_search_label" style="font-weight:bold;">'.__( 'buscar entradas', 'm7red' ).'</label> <input type="text" id="m7red_search'.$ext.'" class="m7red_search" name="m7red_related_posts_search'.$ext.'" value="" size="16" />';
        echo '<div id="m7red_scope'.$ext.'" class="m7red_scope"><label for="m7red_scope_1'.$ext.'"><input type="radio" name="m7red_scope'.$ext.'" id="m7red_scope_1'.$ext.'" class="m7red_scope_1" value="1"> '.__( 't&iacute;tulo', 'm7red' ).'</label> <label for="m7red_scope_2'.$ext.'"><input type="radio" name="m7red_scope'.$ext.'" id="m7red_scope_2'.$ext.'" class="m7red_scope_2" value="2"> '.__( 'contenido', 'm7red' ).'</label> <label for="m7red_scope_3'.$ext.'"><input type="radio" name="m7red_scope'.$ext.'" id="m7red_scope_3'.$ext.'" class="m7red_scope_3" value="3" checked="checked"> <strong>'.__( 'ambos', 'm7red' ).'</strong></label></div>';
        echo '<div id="m7red_loader'.$ext.'" class="m7red_loader">&nbsp;</div>';
        echo '<div id="m7red_results'.$ext.'" class="ui-tabs-panel m7red_results"></div></div>';
        echo '</div>';
        $n++;
    }
    echo '<input type="hidden" name="m7red_noncename" id="m7red_noncename" value="'.wp_create_nonce( M7RED_THEME_NAME ).'" />';
    ?>
        <script type="text/javascript">
        var m7red_CRs_count = <?php echo ($n-1); ?>;
        </script>
    <?php
    echo '</div>';
}

/**
 * m7red_save_relationships - Save the relations
 *
 * @param int   $post_id - The id of the post being saved
 * @param array $related_posts - A list of post_id's
 */
 function m7red_save_relationships( $post_id, $related_posts ) {
    global $wpdb;
    // First delete the relationships that were there before
    m7red_delete_relationships( $post_id );
    // Now add/update the relations
    if( $related_posts ) {
        if(get_option('m7red_display_reciprocal')) {
            $query = "SELECT * FROM ".$wpdb->prefix."post_relationships WHERE post1_id=".$post_id." OR post2_id=".$post_id.";";
            $existing_ones = $wpdb->get_results( $query );
            $query="";
            foreach($related_posts as $rel_cct_list){
                $order_counter = 0;
                foreach($rel_cct_list as $rel_post){
                    $not_updatable = true;
                    foreach($existing_ones AS $k=>$v){
                        if($rel_post == $v->post1_id OR $rel_post == $v->post2_id){
                            if($v->post1_id == $post_id){
                                $left_q = "post1_id=".$post_id;
                                $right_q = "post2_id=".$v->post2_id;
                                $relation_order = "position1=".$order_counter;
                            } else {
                                $left_q = "post1_id=".$post_id;
                                $right_q = "post1_id=".$v->post1_id;
                                $relation_order = "position2=".$order_counter;
                            }
                            $query = "UPDATE ".$wpdb->prefix."post_relationships SET $relation_order WHERE $left_q AND $right_q;";
                            $result = $wpdb->query( $query );
                            $existing_ones[$k]->remains = true;
                            $not_updatable = false;
                            break;
                        } else {
                            $not_updatable = true;
                        }
                    }
                    if($not_updatable){
                        $query = "INSERT INTO ".$wpdb->prefix."post_relationships VALUES ($post_id,$rel_post,$order_counter,0);";
                        $result = $wpdb->query( $query );
                    }
                    $order_counter++;
                }
            }
            foreach($existing_ones AS $k=>$v){
                if(!$v->remains){
                    if($v->post1_id==$post_id){
                        $side = "post1_id";
                        $post_in_relation = "post2_id=".$v->post2_id;
                    } else {
                        $side = "post2_id";
                        $post_in_relation = "post1_id=".$v->post1_id;
                    }
                    $query = "DELETE FROM ".$wpdb->prefix."post_relationships WHERE ".$side."=".$post_id." AND ".$post_in_relation.";";
                    $result = $wpdb->query( $query );
                }
            }
        } else {
            m7red_delete_relationships($post_id);
            foreach( $related_posts as $related_post_sub_list ) {
                $counter = 0;
                foreach($related_post_sub_list AS $related_post){
                    $related_post = (int) $related_post;
                    $new_count = $counter++;
                    $query = "INSERT INTO ".$wpdb->prefix."post_relationships VALUES( $post_id, $related_post , 0, $new_count )";
                    $result = $wpdb->query( $query );
                }
            }

        }
    }
}

/**
 * m7red_truncate_related_posts- Truncates text.
 *
 * Cuts a string to the length of $length and replaces the last characters
 * with the ending if the text is longer than length.
 *
 * http://dodona.wordpress.com/2009/04/05/how-do-i-truncate-an-html-string-without-breaking-the-html-code/
 *
 * @param string  $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param string  $ending Ending to be appended to the trimmed string.
 * @param boolean $exact If false, $text will not be cut mid-word
 * @param boolean $considerHtml If true, HTML tags would be handled correctly
 * @return string Trimmed string.
 */
function m7red_truncate_related_posts($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
    if ($considerHtml) {
        // if the plain text is shorter than the maximum length, return the whole text
        if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
            return $text;
        }
        // splits all html-tags to scanable lines
        preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
        $total_length = strlen($ending);
        $open_tags = array();
        $truncate = '';
        foreach ($lines as $line_matchings) {
            // if there is any html-tag in this line, handle it and add it (uncounted) to the output
            if (!empty($line_matchings[1])) {
                // if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
                if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                    // do nothing
                // if tag is a closing tag (f.e. </b>)
                } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                    // delete tag from $open_tags list
                    $pos = array_search($tag_matchings[1], $open_tags);
                    if ($pos !== false) {
                        unset($open_tags[$pos]);
                    }
                // if tag is an opening tag (f.e. <b>)
                } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                    // add tag to the beginning of $open_tags list
                    array_unshift($open_tags, strtolower($tag_matchings[1]));
                }
                // add html-tag to $truncate'd text
                $truncate .= $line_matchings[1];
            }
            // calculate the length of the plain text part of the line; handle entities as one character
            $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
            if ($total_length+$content_length> $length) {
                // the number of characters which are left
                $left = $length - $total_length;
                $entities_length = 0;
                // search for html entities
                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                    // calculate the real length of all entities in the legal range
                    foreach ($entities[0] as $entity) {
                        if ($entity[1]+1-$entities_length <= $left) {
                            $left--;
                            $entities_length += strlen($entity[0]);
                        } else {
                            // no more characters left
                            break;
                        }
                    }
                }
                $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                // maximum length is reached, so get off the loop
                break;
            } else {
                $truncate .= $line_matchings[2];
                $total_length += $content_length;
            }
            // if the maximum length is reached, get off the loop
            if($total_length>= $length) {
                break;
            }
        }
    } else {
        if (strlen($text) <= $length) {
            return $text;
        } else {
            $truncate = substr($text, 0, $length - strlen($ending));
        }
    }
    // if the words shouldn't be cut in the middle...
    if (!$exact) {
        // ...search the last occurance of a space...
        $spacepos = strrpos($truncate, ' ');
        if (isset($spacepos)) {
            // ...and cut the text in this position
            $truncate = substr($truncate, 0, $spacepos);
        }
    }
    // add the defined ending to the text
    $truncate .= $ending;
    if($considerHtml) {
        // close all unclosed html-tags
        foreach ($open_tags as $tag) {
            $truncate .= '</' . $tag . '>';
        }
    }
    return $truncate;
}

/**
 * m7red_get_related_posts_html - Generate the related posts HTML for a post and return it as a string
 *
 * @param int       $post_id - The id of the post
 * @param bool      $hide_unpublished - When false drafts will be included
 * @param string    $post_type - The post type of the related posts to return i.e. post, page, or any custom post types
 *                  When null all post types will be returned
 */
function m7red_get_related_posts_html( $post_id, $hide_unpublished = true, $post_type = null ) {
    // Get theme settings
    // Get the supported post types (selected in theme settings)
    $supported_post_types = m7red_get_supported_post_types();
    if(!$supported_post_types) {
        return false;
    }
    $related_posts = array();
    // If only related posts for specified post type are needed
    if( isset( $post_type ) ) {
        $related_posts[$post_type] = m7red_get_related_posts( $post_id, true, $hide_unpublished, $post_type );
    }
    // If we need related posts from all post types
    else {
        // First get the entire set of related posts for all post types
        $all_related_posts = m7red_get_related_posts( $post_id, true, $hide_unpublished, null );
        // If related posts should be displayed as one list
        if( get_option('m7red_combine_post_types') == 1 ) {
            $related_posts['m7red_all'] = $all_related_posts;
        }
        // If related posts should be grouped by post types
        else {
            if( $all_related_posts ) {
                foreach( $all_related_posts as $related_post ) {
                    $related_posts[$related_post->post_type][] = $related_post;
                }
            }
        }
    }
    // Start HTML output
    $output = "<div class=\"related-posts\">\n";
    // Loop through different post types
    foreach( $related_posts as $post_type => $post_type_related_posts ) {
        // This filters %posttype% from the title
        $title = m7red_get_related_post_title( __(get_option('m7red_title'), 'm7red'), $post_type );
        if( count( $post_type_related_posts ) ) {
            $output .= "<div id=\"related-posts-$post_type\" class=\"related-posts-type\">\n";
            // Create the title with the selected HTML header
            $output .= "<".get_option('m7red_header_element').">".$title."</".get_option('m7red_header_element').">\n";
            $output .= "<ul>\n";
            // Add related posts
            foreach( $post_type_related_posts as $related_post ) {
                $output .= "<li>";
                if(get_option('m7red_show_thumbnails') && has_post_thumbnail($related_post->ID)) {
                    $output .= "<a href=\"".get_permalink($related_post->ID)."\">";
                    $output .= get_the_post_thumbnail($related_post->ID, get_option('m7red_thumbnail_size'));
                    $output .= "</a>";
                }
                $output .= "<a href=\"".get_permalink( $related_post->ID )."\">".$related_post->post_title."</a>";
                $output .= "</li>\n";
            }
            $output .= "</ul></div>\n";
        }
        // If there are no related posts for this post type
        else {
            if( !get_option('m7red_hide_if_empty') ) {
                $output .= "<div id=\"related-posts-$post_type\" class=\"related-posts-type\">\n";
                $output .= "<".get_option('m7red_header_element').">".$title."</".get_option('m7red_header_element').">\n";
                $output .= "<p>".get_option('m7red_text_if_empty')."</p>\n";
                $output .= "</div>";
            }
            else {
                // Show nothing
                return "";
            }
        }
    }
    $output .= "</div>";
    return $output;
}

/**
 * Get all information (attributes) of all exisiting
 * categories which has posts.
 *
 * $category->term_id
 * $category->name
 * $category->slug
 * $category->term_group
 * $category->term_taxonomy_id
 * $category->taxonomy
 * $category->description
 * $category->parent
 * $category->count
 * $category->cat_ID
 * $category->category_count
 * $category->category_description
 * $category->cat_name
 * $category->category_nicename
 * $category->category_parent
 */
function m7red_get_all_categories() {
    global $categories;
    global $wpdb;

    $categories = array();

    // Get all categories which has posts.
    $categories_tmp = get_categories();

    // Get custom specific attributes (metadata) from termmeta.
    // $attrs = $wpdb->get_results( "SELECT term_id as cat_ID, meta_key, meta_value FROM wp_termmeta WHERE 1");

    // Add attributes to it's categories.
    $table_name = $wpdb->prefix . 'termmeta';
    foreach ($categories_tmp as $cat) {
        // Get custom specific attributes (metadata) from termmeta per category.
        $attrs = $wpdb->get_results("SELECT meta_key, meta_value FROM $table_name WHERE term_id = $cat->cat_ID");
//         $attrs = $wpdb->get_results("SELECT meta_key, meta_value FROM wp_termmeta WHERE term_id = $cat->cat_ID");
        $cat->color = '';
        $cat->size = '';
        foreach ($attrs as $attr) {
            if ($attr->meta_key == 'category_color') {
                $cat->color = $attr->meta_value;
            }
            if ($attr->meta_key == 'category_size') {
                $cat->size = $attr->meta_value;
            }
        }
        // Apply extended category data.
        $categories[] = $cat;
    }
    $result = $categories;
    return $result;
}

/** Get all tags with its links. */
function m7red_get_tags($used_only=false) {
  $result = null;
  $tags = array();
  $tags_ori = get_tags();
  if (count($tags_ori) > 0) {
    if ($used_only) { // only tags w/ posts.
      foreach ($tags_ori as $tag) {
        $post_ids = m7red_get_all_posts_by_tag($tag->slug);
        if (count($post_ids) > 0) {
          $tags[] = $tag;
        }
      }
    }
    else { // all defined tags, w/ and w/o posts.
      $tags = $tags_ori;
    }
    foreach ($tags as $tag) {
	    $tag_link = get_tag_link( $tag->term_id );
	    $tag->link = $tag_link;
	  }
	  $result = $tags;
  }

  return $result;
}

/** Get all post ids by given tag */
function m7red_get_all_posts_by_tag($tag_slug) {
  $post_ids = array();
  $args = array(
	  'posts_per_page' => -1,
    'tag' => $tag_slug,
  );
  $posts = get_posts($args);
  foreach ($posts as $post) {
    $post_ids[] = $post->ID;
  }
  return $post_ids;
}

/** Set processes container. */
function m7red_show_legend_container($pane) {
    $cats = m7red_get_all_categories();
    $processes = get_terms( 'process', array('orderby' => 'name', 'hide_empty' => true) );

    echo '<div id="graph_legend_container">';
      // processes
      echo '<div class="graph-legend-box-header"># '. __('Processes', 'm7red').'</div>';
      echo '<div class="graph-legend-box-processes">';
        if (!empty( $processes) && !is_wp_error($processes)) {
          echo '<ul>';
          foreach ($processes as $process) {
            // $process is an object, so we don't need to specify the $taxonomy.
            $process_link = get_term_link($process);
            if (is_wp_error( $process_link ) ) {
              continue;
            }
            echo '<li>#&nbsp;<a href="'.esc_url( $process_link ).'">'.$process->name.'</a></li>';
          }
          echo '</ul>';
        }
      echo '</div>';

      // Refresh button.
      if ($pane === 'graph') {
        m7red_show_refresh_btn();
      }
    echo '</div>'; // end graph_legend_container
}

/** Set refresh button. */
function m7red_show_refresh_btn() {
  // Refresh graphics button.
  echo '<div class="graph-refresh-btn-box">';
    echo '<button id="graph-refresh-btn" class="clean-gray" style="width:195px;">reescalar grafo</button>&nbsp;';
//     echo '<div class="graph-refresh-btn" id="graph-refresh-btn">reescalar grafo</div>';
  echo '</div>';
}

/**
 * Get single term slug
 * Which is a forked function out of WordPress single_term_title().
 * Found in http://wordpress.stackexchange.com/
 * Thanks for sharing.
 */
function m7red_single_term_slug($prefix='', $display=true) {
  $term = get_queried_object();
  if (!$term) {
    return;
  }
  if (is_category()) {
    $term_slug = apply_filters('single_cat_slug', $term->slug);
  }
  elseif (is_tag()) {
    $term_slug = apply_filters('single_tag_slug', $term->slug);
  }
  elseif (is_tax()) {
    $term_slug = apply_filters('single_term_slug', $term->slug);
  }
  else {
    return;
  }
  if (empty($term_slug)) {
    return;
  }
  if ($display) {
    echo $prefix . $term_slug;
  }
  else {
    return $term_slug;
  }
}
