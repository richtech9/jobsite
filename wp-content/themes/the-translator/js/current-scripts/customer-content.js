//for loading in content units

/**
 * @typedef {object} DaAjaxObjectContentUnitResponse
 * @property {bool} status
 * @property {string} message
 * @property {string} action
 * @property {string[]} log
 * @property {string} html
 * @property {number} units
 */

//code-bookmark-js  assists loading in the content mall for customers
function freelinguist_load_more_content_units() {
    if (getObj.b_content_mall_loader_busy) {
        return;
    }
    getObj.b_content_mall_loader_busy = true;
    //console.debug("starting to load more content units");
    let $ = jQuery;
    let container = $('section.content-mall-area div.container');
    if (!container.length) {
        console.error("Cannot find parent area for content units @section.content-mall-area div.container ");
        return;
    }
    let data_holder = $('section.content-mall-area');
    let page_string = data_holder.data('page');
    if (!page_string) {
        console.error("Cannot find page data for content units @section.content-mall-area ");
        return;
    }

    //add in temp loader at bottom
    let loader_div = $('<div class="freelinguist-ajax-loader freelinguist-content-mall-loader"></div>');
    container.append(loader_div);
    let page = parseInt(page_string);
    page ++;

    let data = {
        action: 'get_content_units',
        page: page,
    };
    //console.debug("next page number is ", page);
    $.post(getObj.ajaxurl,
        data,

        /**
         * @param {string|object} response_thing
         */
        function (response_thing) {

            /**
             * @var {DaAjaxObjectContentUnitResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_thing);
            //console.debug(response);

            if (response.status) {
                if (response.units > 0) {
                    //add html, and update page number
                    let nu = $(response.html);
                    container.append(nu);
                    data_holder.data('page',page);
                    data_holder.attr('data-page',page.toString());
                } else {
                    //no more units
                }
            } else {
                console.error(response.message);
            }
            getObj.b_content_mall_loader_busy = false;
            container.find('div.freelinguist-content-mall-loader').remove();

        }
    ).fail(function(xhr, status, error) {
        console.error("cannot load content units: " + error);
        getObj.b_content_mall_loader_busy = false;
        container.find('div.freelinguist-content-mall-loader').remove();
        }
    ); //end post ajax

}

//code-bookmark-js  assists loading in the content mall for customers
function freelinguist_load_content_mall_scroll_listener() {

    getObj.b_content_mall_loader_busy = false;
    jQuery(window).scroll(function() {

        let window_scroll_top = jQuery(window).scrollTop();
        let window_height = jQuery(window).height();
        let document_height = jQuery(document).height();
        let window_position = window_height + window_scroll_top;

        if(window_scroll_top >= document_height - window_height - 300) { //if on the last row of the unit (unit height 275)
            //console.log('scrolled into area with + version ' + Date.now());
            freelinguist_load_more_content_units();
        }
    });

}
