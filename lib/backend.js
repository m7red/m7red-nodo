/**
 * backend.js
 *
 * @package     m7red-nodo
 * @author      m7red (http://www.m7red.info)
 * @copyright   Copyright (c) 2015, m7red
 * @license     http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 */

// Check wether script is loading right.
// var hello = "backend script is loading...";
// alert(hello);
// console.debug(hello);

jQuery.noConflict();
jQuery(document).ready( function ($) {
    "use strict";
    ////////////////////////////////////////////////////////////////
    // Colorpicker for node color in categories.
    // Every node could have an indivual color.
    ////////////////////////////////////////////////////////////////
    jQuery('#category_color').wpColorPicker();

    ////////////////////////////////////////////////////////////////
    // Related Posts by Posts
    // Textual representation included in posts.
    ////////////////////////////////////////////////////////////////
    // Activate tabs
    var current_tab = 1;
    jQuery("#m7red_tabs .m7red_tab a").click(function() {
        jQuery("#m7red_relatedposts .m7red_post_type").removeClass('m7red_current');
        jQuery("#m7red_relatedposts .m7red_post_type").hide();
        jQuery("#m7red_relatedposts .m7red_tab").removeClass('m7red_current_tab');
        var tabToShowId = jQuery(this).attr('rel');
        jQuery(this).parent().addClass('m7red_current_tab');
        jQuery('#'+tabToShowId).show();
        m7red_current = tabToShowId;
        var parts = tabToShowId.split("-");
        var current_tab = parts[parts.length-1];
        return false;
    });

    jQuery(".m7red_search").bind( 'keydown', function(e){
        if( e.keyCode == 13 ){
            return false;
        }
    });

    var timer = 0;
    jQuery(".m7red_search").bind('keyup', function(e){
        if(jQuery(this).val().length > 2) {
            var id = jQuery(this).attr('id');
            if((e.keyCode > 47 && e.keyCode < 91 ) || e.keyCode == 8 || e.keyCode == 13){
                clearTimeout(timer);
                timer = setTimeout(function() {
                    m7red_related_posts_search(id);
                }, 200);
            }
        }
    });
    jQuery(".m7red_scope input").each( function() {
        jQuery(this).change(function() {
            m7red_related_posts_search(jQuery(this).attr('id'));
        });
    });

    function m7red_related_posts_search(id) {
        var parts = id.split("-");
        var postTypeIndex = parts[parts.length-1];
        if( jQuery("#m7red_search-"+postTypeIndex).val() != '' ) {
            var searchResults = "?arp_s=" + encodeURIComponent( jQuery("#m7red_search-"+postTypeIndex).val() );
            searchResults += "&arp_scope=" + escape( jQuery("input[name='m7red_scope-"+postTypeIndex+"']:checked").val() );
            searchResults += "&arp_post_type=" + escape( jQuery("#m7red_post_type_name-"+postTypeIndex).val() );
            if( jQuery("#post_ID").val() ) {
                searchResults += "&arp_id=" + escape( jQuery("#post_ID").val() );
            }
            jQuery("#m7red_loader-"+postTypeIndex).addClass("m7red_loader_active");
            jQuery("#m7red_results-"+postTypeIndex).load( searchResults, '',
                function() { jQuery("#m7red_results-"+postTypeIndex+" li .m7red_result").each(function(i) {
                        jQuery(this).click(function() {
                            var postID = this.id.substring(7);
                            var resultID = "related-post_" + postID;
                            if( jQuery("#"+resultID).text() == '' ) {
                                jQuery("#m7red_related_posts_replacement-"+postTypeIndex).hide();
                                var newLI = document.createElement("li");
                                jQuery(newLI).attr('id', resultID);
                                jQuery(newLI).text(jQuery(this).text());
                                jQuery("#m7red_relatedposts_list-"+postTypeIndex).append( '<li id="'+resultID+'"><span class="m7red_moovable"><strong>'+jQuery(this).text()+'</strong><span><a class="m7red_deletebtn" onclick="m7red_remove_relationship(\''+resultID+'\')">X</a></span></span><input type="hidden" name="m7red_related_posts['+m7red_current+'][]" value="'+postID+'" /></li>' );
                                jQuery("#m7red_related_count-"+postTypeIndex).text( ( parseInt(jQuery("#m7red_related_count-"+postTypeIndex).text())+1 ) );
                            }
                            else {
                                jQuery("#"+resultID ).focus();
                                jQuery("#"+resultID ).css("color", "red");
                                setTimeout('document.getElementById("'+resultID+'").style.color = "#000000";', 1350);
                            }
                        });
                    });
                    jQuery("#m7red_loader-"+postTypeIndex).removeClass("m7red_loader_active");
                }
            );
        }
        else {
            jQuery("#m7red_results-"+postTypeIndex).html("");
        }
    }

    if(!(typeof m7red_CRs_count === 'undefined') && jQuery('#m7red_relatedposts').hasClass('m7red_manual_order')){
        for( var i=0; i <= m7red_CRs_count; i++){
            jQuery('#m7red_relatedposts_list-'+i).sortable({
                 handle : '.m7red_moovable',
                 update : function () {}
             });
        }
        jQuery("#m7red_relatedposts li").css("cursor", "move");
    }
    var m7red_current = 'm7red_post_type-1';

}); // end jQuery(document).ready()

// Control for post add on.
function m7red_remove_relationship( postID ) {
    var current_tab = 1;
    jQuery(document).ready(function() {
        jQuery("#"+postID).remove();
        jQuery("#m7red_related_count-"+current_tab).text( ( parseInt(jQuery("#m7red_related_count-"+current_tab).text(), 10)-1 ) );
        if( jQuery("#m7red_relatedposts_list-"+current_tab+" li").length < 2 ){
            jQuery("#m7red_related_posts_replacement-"+current_tab).show();
        }
    });
}
