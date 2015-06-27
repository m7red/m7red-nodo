<?php
/**
 * m7red-nodo options
 *
 * @package     m7red-nodo
 * @author      m7red (http://www.m7red.info)
 * @copyright   Copyright (c) 2015, m7red
 * @license     http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 */

/**
 *  Add options page into menu.
 *
 */
function m7red_register_options_page() {
    $page_title = M7RED_THEME_NAME.' Configuración';
    $menu_title = M7RED_THEME_NAME.' Configuración';
    $access_privileges = 'administrator';
    $page_name = 'm7red_options';
    $callback = 'm7red_options_page';

    add_options_page($page_title, $menu_title, $access_privileges, $page_name, $callback);
}
add_action('admin_menu', 'm7red_register_options_page');

/**
 *  Create options page.
 *
 */
function m7red_options_page() {
?>
    <script type="text/javascript">
    function m7red_disable_empty_text() {
        document.getElementById('m7red_text_if_empty').disabled = ( document.getElementById('m7red_hide_if_empty_true').checked ) ? "disabled" : "";
    }
    </script>

<div class="wrap">
  <h2><?php _e( M7RED_THEME_NAME.' Configuración (ahora sólo disponible en inglés)', 'm7red' ); ?></h2>
  <span style="font-size:80%; color:#8D99A4;"><?php echo M7RED_THEME_NAME.' versión '.M7RED_VERSION?></span>
  <form method="post" action="options.php">
    <?php settings_fields( 'm7red-options' ); ?>
    <p>
      <?php printf( __( 'Various settings and modes for textual and visual representation of related posts.')); ?>
    </p>
    <table class="form-table">
      <tr valign="top">
        <th scope="row" style="width:300px; padding-top:0; padding-bottom:0;">
          <h3><?php _e( 'Textual Representation', 'm7red' ); ?></h3>
        </th>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'Should related posts be reciprocal? If so, the link will appear on both pages. If not, it will only appear on the post/page where it was selected.', 'm7red' ); ?>
        </th>
        <td>
          <input name="m7red_display_reciprocal" type="radio" id="m7red_display_reciprocal_true" value="1" <?php if( get_option( 'm7red_display_reciprocal') ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_display_reciprocal_true">
            <?php _e( 'Yes, include the link on both pages.', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_display_reciprocal" type="radio" id="m7red_display_reciprocal_false" value="0" <?php if( !get_option( 'm7red_display_reciprocal') ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_display_reciprocal_false">
            <?php _e( 'No, only show the link on one page.', 'm7red' ); ?>
          </label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'What title for related posts should be displayed?', 'm7red' ); ?>
        </th>
        <td>
          <input name="m7red_title" type="text" id="m7red_title" value="<?php echo get_option('m7red_title')?>" />
          <div style="margin: 10px 0 10px 0;">
            <label for="m7red_header_element">
              <?php _e( 'Using HTML header element' , 'm7red'); ?>:</label>
            <select name="m7red_header_element" id="m7red_header_element" style="font-size: 80%">
              <option value="h1" <?php if( get_option( 'm7red_header_element')=='h1' ) : ?>selected="selected"
                <?php endif; ?>>&lt;h1&gt;</option>
              <option value="h2" <?php if( get_option( 'm7red_header_element')=='h2' ) : ?>selected="selected"
                <?php endif; ?>>&lt;h2&gt;</option>
              <option value="h3" <?php if( get_option( 'm7red_header_element')=='h3' ) : ?>selected="selected"
                <?php endif; ?>>&lt;h3&gt;</option>
              <option value="h4" <?php if( get_option( 'm7red_header_element')=='h4' ) : ?>selected="selected"
                <?php endif; ?>>&lt;h4&gt;</option>
              <option value="h5" <?php if( get_option( 'm7red_header_element')=='h5' ) : ?>selected="selected"
                <?php endif; ?>>&lt;h5&gt;</option>
              <option value="h6" <?php if( get_option( 'm7red_header_element')=='h6' ) : ?>selected="selected"
                <?php endif; ?>>&lt;h6&gt;</option>
            </select>
          </div>
          <!-- <p class="m7red_help"><?php _e( '<strong>Note:</strong> if you choose to display different post types as seperate lists, you can place <strong>%posttype%</strong> in the title to display the label of the post type.', 'm7red' ); ?></p> -->
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'What should be displayed when there are no related posts?', 'm7red' ); ?>
        </th>
        <td>
          <input name="m7red_hide_if_empty" type="radio" id="m7red_hide_if_empty_true" value="1" <?php if( get_option( 'm7red_hide_if_empty') ) : ?>checked="checked"
          <?php endif; ?>onclick="m7red_disable_empty_text()" />
          <label for="m7red_hide_if_empty_true" onclick="m7red_disable_empty_text()">
            <?php _e( 'Nothing', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_hide_if_empty" type="radio" id="m7red_hide_if_empty_false" value="0" <?php if( !get_option( 'm7red_hide_if_empty') ) : ?>checked="checked"
          <?php endif; ?>onclick="m7red_disable_empty_text()" />
          <label for="m7red_hide_if_empty_false" onclick="m7red_disable_empty_text()">
            <?php _e( 'Show this text', 'm7red' ); ?>:</label>
          <input type="text" name="m7red_text_if_empty" id="m7red_text_if_empty" value="<?php echo get_option('m7red_text_if_empty'); ?>" <?php if( get_option( 'm7red_hide_if_empty') ) : ?>disabled="disabled"
          <?php endif; ?>style="width:250px;" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'Would you like to display thumbnails with your related posts?', 'm7red' ); ?>
        </th>
        <td>
          <?php if(current_theme_supports( 'post-thumbnails')) : ?>
          <input name="m7red_show_thumbnails" type="radio" id="m7red_show_thumbnails_true" value="1" <?php if( get_option( 'm7red_show_thumbnails') ) : ?>checked="checked"
          <?php endif; ?>onclick="m7red_disable_empty_text()" />
          <label for="m7red_show_thumbnails_true">
            <?php _e( 'Yes, display thumbnails with this size:', 'm7red' ); ?>
          </label>
          <select name="m7red_thumbnail_size">
            <?php global $_wp_additional_image_sizes; foreach ($_wp_additional_image_sizes as $size_name=>$size_attrs): ?>
            <option value="<?php echo $size_name ?>" <?php if ( get_option( 'm7red_thumbnail_size' ) == $size_name ) : ?>selected="selected"
              <?php endif ?>>
              <?php echo $size_name ?>(
              <?php echo $size_attrs[ 'width'] ?>x
              <?php echo $size_attrs[ 'height'] ?>)</option>
            <?php endforeach; ?>
          </select>
          <br/>
          <input name="m7red_show_thumbnails" type="radio" id="m7red_show_thumbnails_false" value="0" <?php if( !get_option( 'm7red_show_thumbnails') ) : ?>checked="checked"
          <?php endif; ?>onclick="m7red_disable_empty_text()" />
          <label for="m7red_show_thumbnails_false">
            <?php _e( 'No', 'm7red' ); ?>
          </label>
          <!-- <p class="m7red_help"><?php _e( sprintf('<strong>Note:</strong> You can only select sizes that are supported by your theme. See the <a href="%s">WordPress Codex</a> on how to add thumbnail sizes.', 'http://codex.wordpress.org/Function_Reference/add_image_size'), 'm7red' ); ?></p> -->
          <?php else : ?>
          <p class="m7red_thumbnails_no_support">
            <?php _e(sprintf( 'Thumbnails are not supported by current theme. Please check <a href="%s">the WordPress Codex</a> for more information about activating thumbnail support.', 'http://codex.wordpress.org/Post_Thumbnails'), 'm7red');?>
            <?php endif; ?>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'How would you like to order your related posts?', 'm7red' ); ?>
        </th>
        <td>
          <input name="m7red_display_auto" type="hidden" id="m7red_display_auto" value="1"<?php if (get_option('m7red_display_auto') == '1') : ?> checked="checked"<?php endif; ?> />
          <input name="m7red_order_type" type="radio" id="m7red_order_type_manual" value="manual" <?php if( get_option( 'm7red_order_type')=="manual" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_order_type_manual">
            <?php _e( 'Manually', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_order_type" type="radio" id="m7red_order_type_auto" value="auto" <?php if( get_option( 'm7red_order_type')=="auto" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_order_type_auto">
            <?php _e( 'Automatically', 'm7red' ); ?>
          </label>
          <select name="m7red_order" id="m7red_order">
            <option value="date_desc" <?php if(get_option( 'm7red_order')=='date_desc' ) : ?>selected="selected"
              <?php endif; ?>>
              <?php _e( 'by date, new to old', 'm7red' ); ?>
            </option>
            <option value="date_asc" <?php if(get_option( 'm7red_order')=='date_asc' ) : ?>selected="selected"
              <?php endif; ?>>
              <?php _e( 'by date, old to new', 'm7red' ); ?>
            </option>
            <option value="title_asc" <?php if(get_option( 'm7red_order')=='title_asc' ) : ?>selected="selected"
              <?php endif; ?>>
              <?php _e( 'alphabetically A→Z', 'm7red' ); ?>
            </option>
            <option value="title_desc" <?php if(get_option( 'm7red_order')=='title_desc' ) : ?>selected="selected"
              <?php endif; ?>>
              <?php _e( 'alphabetically Z→A', 'm7red' ); ?>
            </option>
            <option value="random" <?php if(get_option( 'm7red_order')=='random' ) : ?>selected="selected"
              <?php endif; ?>>
              <?php _e( 'randomly', 'm7red' ); ?>
            </option>
            <select>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px; padding-bottom:0;">
          <h3><?php _e( 'Visual Representation', 'm7red' ); ?></h3>
        </th>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'Initalize graphics zoom factor. This have to be a value between 1 and 3. For example 1.2 or 1,4 are valid. You can use a dot or a comma as decimal sign. Default is set by 1.', 'nsur' ); ?>
        </th>
        <td>
          <input name="m7red_graphics_zoom_init_scale" type="text" id="m7red_graphics_zoom_init_scale" size="2" value="<?php echo get_option('m7red_graphics_zoom_init_scale')?>" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'How many posts (maximum number) should be displayed in graphics?', 'm7red' ); ?>
        </th>
        <td>
          <input name="m7red_max_nodes_graph" type="radio" id="m7red_maximum number_100" value="100" <?php if( get_option( 'm7red_max_nodes_graph') == "100" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_max_nodes_graph_100">
            <?php _e( '100', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_max_nodes_graph" type="radio" id="m7red_maximum number_200" value="200" <?php if( get_option( 'm7red_max_nodes_graph') == "200" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_max_nodes_graph_200">
            <?php _e( '200', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_max_nodes_graph" type="radio" id="m7red_maximum number_300" value="300" <?php if( get_option( 'm7red_max_nodes_graph') == "300" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_max_nodes_graph_300">
            <?php _e( '300', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_max_nodes_graph" type="radio" id="m7red_maximum number_400" value="400" <?php if( get_option( 'm7red_max_nodes_graph') == "400" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_max_nodes_graph_400">
            <?php _e( '400', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_max_nodes_graph" type="radio" id="m7red_maximum number_500" value="500" <?php if( get_option( 'm7red_max_nodes_graph') == "500" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_max_nodes_graph_500">
            <?php _e( '500', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_max_nodes_graph" type="radio" id="m7red_maximum number_0" value="0" <?php if( get_option( 'm7red_max_nodes_graph') == "0" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_max_nodes_graph_0" style="color:red;">
            <?php _e( 'all (This is not recommended and only useful for test purposes.)', 'm7red' ); ?>
          </label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'The refresh cycle determines the time intervals in minutes at which a new file for displaying the graphics is stored.', 'm7red' ); ?>
        </th>
        <td>
          <input name="m7red_refresh_graph" type="radio" id="m7red_refresh_graph_10" value="10" <?php if( get_option( 'm7red_refresh_graph') == "10" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_refresh_graph_10">
            <?php _e( '10 min', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_refresh_graph" type="radio" id="m7red_refresh_graph_20" value="20" <?php if( get_option( 'm7red_refresh_graph') == "20" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_refresh_graph_20">
            <?php _e( '20 min', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_refresh_graph" type="radio" id="m7red_refresh_graph_30" value="30" <?php if( get_option( 'm7red_refresh_graph') == "30" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_refresh_graph_30">
            <?php _e( '30 min', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_refresh_graph" type="radio" id="m7red_refresh_graph_60" value="60" <?php if( get_option( 'm7red_refresh_graph') == "60" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_refresh_graph_60">
            <?php _e( '60 min', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_refresh_graph" type="radio" id="m7red_refresh_graph_0" value="0" <?php if( get_option( 'm7red_refresh_graph') == "0" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_refresh_graph_0" style="color:red;">
            <?php _e( 'always (This is not recommended and only useful for test purposes.)', 'm7red' ); ?>
          </label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" style="width:300px;">
          <?php _e( 'Safety threshold (minimum waiting period) in minutes when a new file shall be created. If set, it overrides a refresh cycle selection to "always" above.', 'm7red' ); ?>
        </th>
        <td>
          <input name="m7red_threshold_create_new_file" type="radio" id="m7red_threshold_create_new_file_1" value="1" <?php if( get_option( 'm7red_threshold_create_new_file') == "1" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_threshold_create_new_file_1">
            <?php _e( '1 min', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_threshold_create_new_file" type="radio" id="m7red_threshold_create_new_file_2" value="2" <?php if( get_option( 'm7red_threshold_create_new_file') == "2" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_threshold_create_new_file_2">
            <?php _e( '2 min', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_threshold_create_new_file" type="radio" id="m7red_threshold_create_new_file_3" value="3" <?php if( get_option( 'm7red_threshold_create_new_file') == "3" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_threshold_create_new_file_3">
            <?php _e( '3 min', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_threshold_create_new_file" type="radio" id="m7red_threshold_create_new_file_4" value="4" <?php if( get_option( 'm7red_threshold_create_new_file') == "4" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_threshold_create_new_file_4">
            <?php _e( '4 min', 'm7red' ); ?>
          </label>
          <br/>
          <input name="m7red_threshold_create_new_file" type="radio" id="m7red_threshold_create_new_file_0" value="0" <?php if( get_option( 'm7red_threshold_create_new_file') == "0" ) : ?>checked="checked"
          <?php endif; ?>/>
          <label for="m7red_threshold_create_new_file_0" style="color:red;">
            <?php _e( 'none (This is not recommended and only useful for test purposes.)', 'm7red' ); ?>
          </label>
        </td>
      </tr>
    </table>
    <p class="submit">
      <input name="m7red_options_submit" class="button-primary" value="<?php _e( 'Guardar cambios', 'm7red' ); ?>" type="submit" />
    </p>
  </form>
</div>
<?php } ?>
