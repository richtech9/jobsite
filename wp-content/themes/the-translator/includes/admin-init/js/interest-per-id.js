/**
 * @typedef {object} DatPerID
 * @property {number} id
 * @property {number} da_id
 * @property {string} type
 * @property {string} title
 */

/**
 * @typedef {object} AjaxPerIDResponse
 * @property {number} status
 * @property {DatPerID[]} id_list
 * @property {string} message
 * @property {number} flag
 * @property {number} is_title_hidden
 * @property {string} interest_name
 *
 */

// noinspection JSValidateTypes
/**
 * @typedef {object} AjaxHelperObject
 * @property {string} ajax_url
 */
jQuery(function($) {

    // noinspection JSUnresolvedVariable
    /**
     * @var {AjaxHelperObject} ajax_helper
     */
    let ajax_helper = ajax_object;

    let selected_interest_id = null;

    //code-bookmark-js when the homepage tag is clicked on
    $('body').on('click','div.fl-homepage-tag-table table.wp-list-table td',function(e) {
        let that = $(this);
        if (that.find('a').length > 0) {return;}
        e.stopPropagation();
        e.preventDefault();

        let tr = that.closest('tr');
        let table = tr.closest('table');
        table.find('tr').removeClass('highlight-this-row');

        let first_cell = tr.find('td.column-primary');
        selected_interest_id = parseInt(first_cell[0].firstChild.textContent);
        let parent_div = $('div.per-id-homepage');
        if (selected_interest_id) {
            tr.addClass('highlight-this-row');
            refresh_table();
        } else {
            parent_div.hide();
            selected_interest_id = null;
        }

    });

    //code-bookmark-js when deleting a per-id in the homepage tags
    $('body').on('click','div.per-id-list-holder a.delete-me',function(e) {
        e.stopPropagation();
        e.preventDefault();
        let id = $(this).data('id');
        $.ajax({
            type: "post",
            dataType: "json",
            url: ajax_helper.ajax_url,
            data: {
                action:'per_id_interest_delete',
                id:id
            },

            /**
             * @param {AjaxPerIDResponse} response
             */
            success: function(response){
                if(response.status===1){
                    refresh_table();
                } else {
                    alert(response.message);
                }
            }
        });
    });


    //code-bookmark-js when deciding if a homepage tag should have a title
    $('input#interest-check-is-title-hidden').live('change', function(){
        if (!selected_interest_id) {
            alert("select an interest first");
            return;
        }
        let check ;
        if($(this).is(':checked')){
            check = 1;
        } else {
            check = 0;
        }

        $.ajax({
            type: "post",
            dataType: "json",
            url: ajax_helper.ajax_url,
            data: {
                action:'interest_is_title_hidden',
                interest_id: selected_interest_id,
                is_title_hidden: check
            },
            /**
             * @param {AjaxPerIDResponse} response
             */
            success: function(response){
                if(response.status===1){
                    refresh_table();
                } else {
                    alert(response.message);
                }
            }
        });
    });


    //code-bookmark-js when adding things to a per-id tag in the homepage tags
    jQuery('#per-id-new-btn').click(function(){

        let ids = $('textarea.per-id-textarea-new').val();
        let per_id_type = $('select.per-id-select-type').val();
        if (!per_id_type) {
            alert("select a type first");
            return;
        }

        if (!selected_interest_id) {
            alert("select an interest first");
            return;
        }
        $.ajax({
            type: "post",
            dataType: "json",
            url: ajax_helper.ajax_url,
            data: {
                action:'per_id_interest_create',
                interest_id: selected_interest_id,
                per_id_type: per_id_type,
                ids : ids
            },
            /**
             * @param {AjaxPerIDResponse} response
             */
            success: function(response){
                if(response.status===1){
                    $('select.per-id-select-type').val('');
                    $('textarea.per-id-textarea-new').val('');
                    refresh_table();
                } else {
                    alert(response.message);
                }
            }
        });
    });

    //code-bookmark-js shows the table for the per-id when a tag is selected in the homepage tags
    function refresh_table() {
        $.ajax({
            type: "post",
            dataType: "json",
            url: ajax_helper.ajax_url,
            data: {
                action:'per_id_interest_list',
                interest_id: selected_interest_id
            },
            /**
             *
             * @param {AjaxPerIDResponse} response
             */
            success: function(response){
                if(response.status===1){
                    let parent_div = $('div.per-id-homepage');
                    parent_div.show();

                    let checker = $('input#per-id-check-is');
                    let is_per_id = parseInt(response.flag.toString());
                    checker.prop('checked', !!is_per_id);

                    let checker_is_title_hidden = $('input#interest-check-is-title-hidden');
                    let is_title_hidden = parseInt(response.is_title_hidden.toString());
                    checker_is_title_hidden.prop('checked', !!is_title_hidden);



                    let interest_name = response.interest_name;
                    $('span.per-id-parent-name').text(interest_name);
                    let tbody = $('div.per-id-list-holder table tbody');
                    tbody.html('');
                    for(let i = 0; i < response.id_list.length; i++) {


                        /**
                         * @type {DatPerID} node
                         */
                        let node = response.id_list[i];
                        let id = node.id;
                        let dat_id = node.da_id;
                        let dat_type = node.type;
                        let dat_title = node.title;
                        let row = $("<tr></tr>");
                        row.append('<td><span class="per-id-dat-id">'+dat_id+'</span></td>');
                        row.append('<td><span class="per-id-dat-type">'+dat_type+'</span></td>');
                        row.append('<td><span class="per-id-dat-title">'+dat_title+'</span></td>');
                        row.append('<td><a class="delete-me" data-id="'+id+'"><i class="fa fa-trash"></i></a></td>');
                        tbody.append(row);
                    }
                } else {
                    alert(response.message);
                }
            }
        });
    }

    //code-bookmark-js shows logs in a popup box
    jQuery('button.freelinguist-show-logs').click(function() {
       let that = $(this);
       let hash = '';
       if (that.data('hash')) {
           hash = that.data('hash');
       }
       let log_section = that.closest('div.freelinguist-log-panel');
       if (log_section.length === 0) {
           console.warn("no log panel as parent");
           return;
       }

        let logs = log_section.find('div.freelinguist-log-outer-container');
        if (logs.length === 0) {
            console.warn("no logs inside log panel");
            return;
        }

        if (that.hasClass('freelinguist-action-popup')) {

            let logs_themselves = logs.find('div.freelinguist-log-area');
            if (logs_themselves.length === 0) {
                console.warn("no log area inside outer container");
                return;
            }

            let logs_to_show = logs_themselves.clone()[0];

            // noinspection JSPotentiallyInvalidConstructorUsage
            var modal = new tingle.modal({
                footer: true,
                stickyFooter: true,
                closeMethods: ['overlay', 'button', 'escape'],
                closeLabel: "Close",
                cssClass: ['freelinguist-log-display-popup'],
                onOpen: function() {
                    //console.log('modal open');
                },
                onClose: function() {
                   // console.log('modal closed');
                },
                beforeClose: function() {
                    // here's goes some logic
                    // e.g. save content before closing the modal
                    return true; // close the modal
                    //return false; // nothing happens
                }
            });

            // set content
            modal.setContent(logs_to_show);

            // add a button
            modal.addFooterBtn('Close', 'tingle-btn tingle-btn--primary tingle-btn--default', function() {
                // here goes some logic
                modal.close();
            });

            // add another button
            modal.addFooterBtn('Close and Refresh Page', 'tingle-btn tingle-btn--danger', function() {
                // if there is an anchor set inside the parent, go there first, then reload
                if (hash) {
                    location.href = "#"+hash;
                }

                location.reload(true);
                modal.close();
            });

            // open modal
            modal.open();
        }
        else {
            logs.toggle();
        }

    });

});