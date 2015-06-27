/**
 * frontend.js
 *
 * @package     m7red-nodo
 * @author      m7red (http://www.m7red.info)
 * @copyright   Copyright (c) 2015, m7red
 * @license     http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 */

// Check whether script is loading right.
// var hello = "frontend script is loading...";
// alert(hello);
// console.debug(hello);

jQuery.noConflict();
jQuery(document).ready( function ($) {
  // "use strict";

  ///////////////////////////////////////////////////////////////
  // Executing actions on specific sections
  // WordPress adds several CSS classes to the body element via
  // the body_class() function. These classes reflect the current
  // hierarchy of our WordPress theme.
  // e.g. <body class="home blog logged-in">
  //
  // We use these classes to execute specific actions on specific
  // sections of a WordPress site by defining an utility object.
  ///////////////////////////////////////////////////////////////
  if (typeof jQuery.WP === 'undefined') {
    jQuery.WP = {
      // Home page
      is_home: function() { return ((jQuery('body').hasClass('home')) ? true : false); },
      // A page
      is_page: function() { return ((jQuery('body').hasClass('page')) ? true : false); },
      // Search result
      is_search: function() { return ((jQuery('body').hasClass('search')) ? true : false); },
      // Error 404
      is_error404: function() { return ((jQuery('body').hasClass('error404')) ? true : false); },
      // A category
      is_category: function() { return ((jQuery('#is_category').length) ? true : false); },
      // A region
      is_region: function() { return ((jQuery('#is_region').length) ? true : false); },
      // A single post
      is_single: function() { return ((jQuery('#is_single').length) ? true : false); }
    };
  }

  function handle_ajax_error(request, type, error_thrown) {
    console.error(request.status + ' (' + request.statusText + ') ' +  type + ' ' + error_thrown);
  }

  /////////////////////////////////////////////////////////////////
  // Related Posts Graphics
  //
  // We use d3.js JavaScript library (http://d3js.org/) for this.
  // D3 allows you to bind arbitrary data to a Document Object
  // Model (DOM), and then apply data-driven transformations to
  // the document.
  /////////////////////////////////////////////////////////////////
  function draw_force_directed_graphics() {
    // Set NG object attributes and draw graphics.
    NG.debug = 0;
    NG.w = 960;
    // Set different graphics heights for individual views.
    if (jQuery.WP.is_home()) {
      NG.h = 400;
      jQuery('#graph_container').css("height", "400px");
    } else {
      NG.h = 300;
      jQuery('#graph_container').css("height", "300px");
    }
    // Set d3 tooltip position in dependence of graphics container left upper corner.
    if (jQuery('#graph_container').length) {
        var elem_graph_container = document.getElementById('graph_container');
//         console.log(elem_graph_container.offsetLeft, elem_graph_container.offsetTop);
        NG.d3_tip_offsetTop = elem_graph_container.offsetTop;
        NG.d3_tip_offsetLeft = elem_graph_container.offsetLeft;
    }
    NG.container = '#graph_container';
    NG.dataURL = jQuery('#data_uri').val() + '/post_relations_d3.json';
    NG.strokeColorNeighbourLink = '#FF555D';
    NG.nodeTextLabelColor = 'black';
    NG.filterGrayShadeColor = '#b8b8b8';

    NG.zoomInitScale = 1; // Set default if validation below fails.
    if (jQuery('#graphics_zoom_init_scale').length > 0) {
      var graphics_zoom_init_scale = jQuery('#graphics_zoom_init_scale').val();
      var decimal = /^\d{1,2}([\.\,][\d{1,2}])$/;
      var integer = /^\d{1,2}$/;
      if ( graphics_zoom_init_scale &&
          (graphics_zoom_init_scale.match(decimal) ||
           graphics_zoom_init_scale.match(integer)) ) {
        graphics_zoom_init_scale = graphics_zoom_init_scale.replace(/\,/g,".");
        var zoom_init = parseFloat(graphics_zoom_init_scale);
        if ( zoom_init >= 1 && zoom_init < 4 ) {
          NG.zoomInitScale = zoom_init;
        }
      }
    }

    ////// Single posts filter
    // Filtering links data in case of a single post view.
    // Only posts witch are related with selected post id
    // remain in the links object.
    if (jQuery.WP.is_single() && jQuery('#post_id').length > 0) {
      NG.filterType = 'post';
      NG.filterValues[0] = jQuery('#post_id').val(); // One post id only.
    ////// Categories filter
    // Filtering nodes data in case of category view.
    } else if (jQuery.WP.is_category() && jQuery('#single_cat_title').length > 0) {
      NG.filterType = 'category';
      NG.filterValues[0] = jQuery('#single_cat_title').val(); // One category only.
      // Set styles to selected category in legend.
      var single_cat_title = jQuery('#single_cat_title').val();
      jQuery('.graph-legend-box-categories ul li:contains('+single_cat_title+')').css({
        'font-weight' : 'bold',
        'background' : '#333333',
        'padding-top' : '3px',
        'padding-bottom' : '3px',
        'padding-left' : '5px',
        'margin-right' : '-5px',
        'margin-left' : '-5px'
      });
    ////// Regions filter
    // Filtering nodes data in case of regions view.
    } else if (jQuery.WP.is_region() && jQuery('#is_region_post_ids').length > 0) {
      NG.filterType = 'region';
      var filterVals = jQuery('#is_region_post_ids').val(); // One or many post ids.
      NG.filterValues = filterVals.split(';');
      // Set styles to selected region in legend.
      var single_term_title = jQuery('#single_term_title').val();
      jQuery('.graph-legend-box-regions ul li:contains('+single_term_title+')').css({
        'font-weight' : 'bold',
        'background' : '#333333',
        'padding-top' : '3px',
        'padding-bottom' : '3px',
        'padding-left' : '5px',
        'margin-right' : '-5px',
        'margin-left' : '-5px'
      });
    }
    NG.run();
  } // end draw_force_directed_graphics()

  ////////////////////////////////////////////////////////////////
  // Related Posts by Posts - Graphical representation
  //
  // This code part proceeds only on pages width graphical data!!!
  ////////////////////////////////////////////////////////////////
  if (jQuery('#graph_container').length) {
    jQuery('#graph_container').empty();
    draw_force_directed_graphics();

    jQuery('#graph-refresh-btn').click(function() {
      jQuery('#graph_container').empty();
      draw_force_directed_graphics();
    });
  }

}); // end jQuery(document).ready()
