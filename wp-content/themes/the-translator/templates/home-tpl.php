<?php
/*
Template Name: Home Page Template
*/

/*
* current-php-code 2020-Oct-16
* input-sanitized : id,lang,type
* current-wp-template:  Homepage for unlogged and customers
* current-wp-top-template
*/

$id = (int)FLInput::get('id', '');

$type = FLInput::get('type', '');

$lang = FLInput::get('lang', 'en');

if (is_user_logged_in() && (xt_user_role() == "translator")) {
    wp_redirect(freeling_links('translator_homepage_url'));
}


global $wpdb;
get_header();
$check_login = (is_user_logged_in()) ? 1 : 0;
//getHomepageInterestList();

$contentTagsAr = [];
$current_user_id = get_current_user_id();
$tags = get_user_meta($current_user_id, '_user_default_tag_save', true);
if ($tags) {
    $contentTags = $wpdb->get_results("select tag_name from wp_interest_tags where id IN (" . $tags . ")", ARRAY_A);

    $contentTagsAr = [];
    if ($contentTags) {
        $contentTagsAr = array_column($contentTags, 'tag_name');
    }
}
$prefilled = json_encode($contentTagsAr);

?>

    <!-- code-notes removed /js/jquerymin.js (version 1.11.3) -->

    <!-- New Code -->
    <section class="banner new">
        <div class="container">
            <div class="row">
                <div class="hero-cont">
                    <span class="bold-and-blocking large-text"><?php get_custom_string('Post Project - Hire Freelancers - Receive Content'); ?></span>
                </div>
                <div class="typing-container">

                    <form method="post" id="htrans-process" action="<?php echo freeling_links('order_process'); ?>"
                          onsubmit="return submit_home_trans(<?php echo $check_login; ?>,this);">
                        <div class="form-main">
                            <input class="enhanced-text" type="text" value=""
                                   name="project_title"
                                   id="project_title" placeholder="<?php get_custom_string('Project Title'); ?>">
                            <textarea class="input-area enhanced-text" maxlength="10000"
                                      placeholder="<?php get_custom_string('Type or Upload Project Description'); ?>."
                                      name="project_description"  autocomplete="off"
                                      id="project_description"></textarea>
                        </div>
                        <div class="inc-files upload-text enhanced-text">
                            <div id="progress" class="progress">
                                <div class="progress-bar progress-bar-success">
                                    <div class="percent"></div>
                                </div>
                            </div>
                            <div id="files_name_container" class="files"></div>
                        </div>
                        <div class="btn-row">

                            <button class="btn blue-btn next-btn postproject redirect_to_order_page regular-text"
                                    name="submit_order__"><img
                                        src="<?php echo get_template_directory_uri() . '/images/post-icon.png'; ?>"><?php get_custom_string('Post My Project'); ?>
                            </button>

                            <?php if ($lang) { ?>
                                <input type="hidden" value="<?= $lang ?>" name="lang" class="">
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>


    <!--suppress JSValidateTypes -->
    <script type="text/javascript">
        jQuery(function ($) {
            $('.load-more').hide();

            $(window).scroll(function () {



                // if ($(window).scrollTop() == $(document).height() - $(window).height()){
                if ($(window).scrollTop() >= ($(document).height() - $(window).height()) * 0.2) {

                    var finish = $('input[name=page_property]').attr('data-finish');
                    if (parseInt(finish) === 1) {
                        return false;
                    }

                    var page = $('input[name=page_property]').attr('data-current');
                    var ajax = $('input[name=page_property]').attr('data-ajax');
                    if (parseInt(ajax) === 0) {
                        return false;
                    }
                    var data = {'action': 'getHomepageInterestList', 'paged': (parseInt(page) + 1)};
                    $.ajax({
                        type: 'POST',
                        dataType: "json",
                        data: data,
                        url: adminAjax.url,
                        beforeSend: function (/*html*/) {
                            //$('.load-more').show(); //stop showing the rotating load icon
                            $('input[name=page_property]').attr('data-ajax', 0);
                        },
                        success: function (html) {
                            console.log(html);
                            if (html) {
                                console.log('html');

                                $('.load-more').hide();
                                //console.log(html);
                                console.log(parseInt(html.finish));
                                $('input[name=page_property]').attr('data-finish', parseInt(html.finish));
                                if (html.html === 'empty' || html.html === '') {
                                    //do not print out anything
                                } else {
                                    // if(page)
                                    // $('#all_container input[name=page]').attr('data-end',1);
                                    $('input[name=page_property]').attr('data-ajax', 1);
                                    $('#all_container').append(html.html);
                                    jQuery('.flexslider').flexslider({
                                        animation: "slide",
                                        animationLoop: false,
                                        slideshow: false,
                                        itemWidth: 272,
                                        itemMargin: 15,
                                        after: function (/*slider*/) {
                                            console.log('flexslider: after');

                                        },
                                        end: function (slider) {
                                            console.log('flexslider: end');

                                        }
                                    });
                                    $('input[name=page_property]').attr('data-current', (parseInt(page) + 1));
                                }
                            } else {

                                console.log('empty');
                                $('.load-more').hide();
                                //console.log(html);
                                $('.empty_data').html('No record exist');
                            }
                        }
                    });
                }
            });
        });
    </script>





<?php
$data = FreelinguistUnitDisplay::getHomepageInterestList();
?>

    <input type="hidden" name="page_property" value="<?php //echo $data['page']; ?>"
           data-tpages="<?php echo $data['total_pages']; ?>" data-finish="<?php echo $data['finish']; ?>"
           data-current="<?php echo $data['current_page']; ?>" data-ajax="1"/>
    <div id="all_container">
        <?php echo $data['html']; ?>
    </div>
    <div class="container" style="position:relative;clear:both;">
        <div class="empty_data" style="">
        </div>
        <div class="load-more" style="display: none"></div>
    </div>


    <script type="text/javascript">


        jQuery(function ($) {
            $('div#all_container').imagesLoaded().always( function( instance ) {
                jQuery('.flexslider').flexslider({
                    animation: "slide",
                    animationLoop: false,
                    slideshow: false,
                    itemWidth: 272,
                    itemMargin: 15,
                    after: function (/*slider*/) {
                        //console.log('hello there: after');

                    },
                    end: function (slider) {
                        //console.log('hello there: end');
                        //code-notes called when get to end of scrolling single line
                    }
                });
                $('div#all_container').show();

            });
            jQuery('body').on('click', '.add-favourited', function () {
                ajaxindicatorstart('loading data.. please wait..');
                var elem = jQuery(this);
                // alert(id);
                var id = jQuery(this).attr('data-id');
                // var c_type = $(this).attr('data-c_type');
                var login = parseInt(jQuery(this).attr('data-login'));
                var fav = parseInt(jQuery(this).attr('data-fav'));
                var type_favorite = jQuery(this).attr('data-c_type'); //either content or translator

                if (login === 0) {
                    window.location.href = devscript_getsiteurl.getsiteurl + "/login/?redirect_to=" + devscript_getsiteurl.getsiteurl;
                    return false;
                }
                // alert(id);
                if (id) {
                    if (fav === 1) {
                        elem.removeClass('favourited');
                        elem.attr('data-fav', 0);
                    } else {
                        elem.addClass('favourited');
                        elem.attr('data-fav', 1);
                    }
                    jQuery.ajax({
                        type: "post",
                        dataType: "json",
                        url: adminAjax.url,
                        data: {action: 'user_add_favorite', id: id, c_type: type_favorite, fav: fav},
                        success: function (response) {
                            ajaxindicatorstop();
                            if (response.status !== 1) {
                                will_handle_ajax_error("favorite error",response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>

<?php
get_template_part('includes/user/author-user-info/translator', 'hire-dialog');
get_template_part('includes/user/contentdetail/contentdetail', 'customer-button-buy-dialogs');
?>
<?php get_footer('homepagenew'); ?>