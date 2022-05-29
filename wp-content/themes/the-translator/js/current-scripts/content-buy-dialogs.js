jQuery(function($) {



    $(document).on('click','button.fl-content-action-buy',function() {


        let that = $(this);
        let content_link = that.data('href');
        let author = that.data('author');
        let fee_amount = that.data('fee_amount');
        let total_amount = that.data('total_amount');
        let content_amount = that.data('content_amount');
        let content_title = that.data('content_title');
        let content_id = that.data('content_id');

        if (!user_id_for_content_dialogs) {
            let encoded_ref = encodeURIComponent(content_link);
            let full_login_link = login_link_for_content_dialogs + '&redirect_to='+encoded_ref;
            window.location = full_login_link;
            return;
        }

        $('div#buyModal span.fl-content-author').text(author);
        $('div#buyModal span.fl-content-processing-fee ').text(fee_amount);
        $('div#buyModal span.fl-content-amount-with-processing-fee').text(total_amount);
        $('div#buyModal span.fl-content-amount').text(content_amount);
        $('div#buyModal span.fl-content-title').text(content_title);
        $('div#buyModal input.fl-content-id').data('content_id',content_id);



        var data = {'action':'freelinguist_get_wallet_amount' };
        jQuery.post(adminAjax.url, data, function(response_raw) {

            /**
             * @type {FreelinguistWalletResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);

            if(response.status){

                $('div#buyModal span.fl-wallet-amount').text(response.wallet_amount);

                $('div#buyModal').modal();
            }else{
                console.error('button.fl-content-action-buy --> freelinguist_get_wallet_amount ',response);
            }

        });

    });

    $(document).on('click','button.fl-content-action-make-offer',function() {

        let that = $(this);
        let min_bid = that.data('min_bid');
        let content_id = that.data('content_id');
        let content_link = that.data('href');
        $('div#makeOfferModel button#offerSend').data('min_bid',min_bid);
        $('div#makeOfferModel button#offerSend').data('content_id',content_id);

        if (!user_id_for_content_dialogs) {
            let encoded_ref = encodeURIComponent(content_link);
            let full_login_link = login_link_for_content_dialogs + '&redirect_to='+encoded_ref;
            window.location = full_login_link;
            return;
        }

        var data = {'content_id': content_id,'action':'get_highest_bid' };

        jQuery.post(adminAjax.url, data, function(response_raw) {

            /**
             * @type {FreelinguistContentBidsResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if(response.status){
                if (response.message) {
                    jQuery('#offer_max_bid').html("Highest Bid: "+response.message);
                    jQuery('#maximum_bid_value').val(response.message);
                } else {
                    jQuery('#offer_max_bid').html("No Bids Yet ");
                    jQuery('#maximum_bid_value').val(response.bid_floor);
                }


                $('div#makeOfferModel').modal();
            }else{
                console.error('fl-content-action-make-offer --> get_highest_bid ',values);
            }

        });

    });


    $(document).on('click','button.fl-content-action-view-offers',function() {
        let content_link = jQuery(this).data('href');
        let content_id  = jQuery(this).data('content_id');

        if (!user_id_for_content_dialogs) {
            let encoded_ref = encodeURIComponent(content_link);
            let full_login_link = login_link_for_content_dialogs + '&redirect_to='+encoded_ref;
            window.location = full_login_link;
            return;
        }

        var data = {'content_id': content_id,'action':'get_latest_all_offers' };

        jQuery.post(adminAjax.url, data, function(response) {

            var values = JSON.parse(response);

            if(values.msg){
                jQuery('#viewAllOfferModel .modal-body').html(values.msg);

                $(".a-timestamp-full-date-time").each(function () {
                    var qthis = $(this);
                    var ts = $(this).data('ts');
                    if (ts === 0 || ts === '0' || ts === undefined || ts === '') {
                        qthis.text('');
                    } else {
                        var ts_number = parseInt(ts.toString());
                        var m = moment(ts_number * 1000);
                        qthis.text(m.format('MMM D YYYY H:mm'));
                    }
                });

                $('div#viewAllOfferModel').modal();
            }else{
                //bootbox.alert('Error.');
            }
        });//end post


    });



    //code-bookmark this javascript validates an offer being entered see #offershoot
    $('#offershoot').keyup(function(){
        var offerShoot = jQuery('#offershoot').val();
        var maximum_bid_value = jQuery('#maximum_bid_value').val();
        var numbers = /^[0-9]+$/;
        if((offerShoot === '') || (offerShoot === '0') || (offerShoot === 0)){
            jQuery('.offer_notice').html('<div style="color:red;">Please give an offer amount!</div>');

        }else if(!offerShoot.match(numbers)){
            jQuery('.offer_notice').html('<div style="color:red;">Please enter a valid number!</div>');
        }
        else if(parseFloat(offerShoot) <=  parseFloat(maximum_bid_value)){
            jQuery('.offer_notice').html('<div style="color:red;">The offer amount has to be greater than the maximum bid!</div>');
        }else{
            jQuery('.offer_notice').html('');
        }
    });

    jQuery('#offerSend').on('click',function(){

        var numbers = /^[0-9]+$/;
        var offerShoot = jQuery('#offershoot').val();
        var maximum_bid_value = jQuery('#maximum_bid_value').val();
        var min_bid = jQuery(this).data('min_bid');



        if((offerShoot === '') || (offerShoot === '0') || (offerShoot === 0)){

            jQuery('.offer_notice').html('<div style="color:red;">Please give an offer amount!</div>');

        }else if(!offerShoot.match(numbers)){
            jQuery('.offer_notice').html('<div style="color:red;">Please enter a valid number!</div>');
        }
        else if(parseFloat(offerShoot) <=  parseFloat(maximum_bid_value)){
            jQuery('.offer_notice').html('<div style="color:red;">The offer amount has to be greater than the maximum bid!</div>');
        }
        else{

            var cusid = user_id_for_content_dialogs;

            var contestId = jQuery(this).data('content_id');

            var data = {

                'action': 'hz_Offer_send_cus',

                'contestId': contestId,

                'offerShoot': offerShoot,

                'cusid': cusid,
                'min_bid': min_bid,

            };

            jQuery.post(getObj.ajaxurl, data, function(response){

                jQuery('.offer_notice').html(response);

                jQuery('#offershoot').val('');

                setTimeout(function(){
                    //jQuery('#openCCbox').hide();
                    jQuery('#makeOfferModel').modal('hide');
                },3000);


            });

        }

    });

}); //end jQuery on ready

function hz_buy_content2(button_element){
    let content_id = $(button_element).data('content_id');
    //code-bookmark The php ajax handler where the javascript buys the content when purchased directly

    var data    = { 'action': 'hz_buy_content_ajxcback', 'content_id':content_id };

    jQuery.ajax({
        type: 'POST',
        url: adminAjax.url,
        data: data,
        global:false,
        /**
         * @param {string} response_raw
         */
        success: function( response_raw ){

            /**
             * @type {FreelinguistContentBuyResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if(response.status){
                var url = devscript_getsiteurl.getsiteurl+'?'+response.contentreciept;
                jQuery("#FavoriteBtn").remove();

                jQuery('input.freelinguist-go-to-purchased-content').one("click", function () {
                    window.location.href=  response.content_link;
                });
                let iframe = jQuery('iframe.freelinguist-receipt');
                iframe.attr('src', url);
                jQuery('tr.freelinguist-order-step-1').hide();
                jQuery('tr.freelinguist-order-step-2').show();

            }else{
                bootbox.alert(response.message);
                return false;
            }
        }
    });
}