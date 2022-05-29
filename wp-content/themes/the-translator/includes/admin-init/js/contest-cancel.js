// noinspection JSValidateTypes
jQuery(function($) {
    //code-bookmark-js when a cancel request is undone in the cancel request page
    $('div.fl-undo-cancel-request').click(function() {
        let that = $(this);
        let cancel_form = that.find('form.fl-undo-cancel-request');
        let wallet = parseFloat(that.data('cust_balance').toString());
        let budget = parseFloat(that.data('budget').toString());
        let difference = wallet - budget;
        let diff_string = difference.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        let shoe_string = "The customer's wallet will be $" + diff_string + ' after this';
        if (difference < 0) {
            shoe_string += "\n Which is a negative amount";
        }
        let cancel_id = that.data('id');
        let b_what = confirm("Do you want to undo this decision for cancel id "
            + cancel_id + '?' + "\n" + shoe_string);
        if(b_what) {cancel_form.submit();}
    });

    //code-bookmark-js showing local time and date for specially marked spans and divs
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

    //code-bookmark-js when days extended is changed in a cancel request
    $("select.days-extended-for-redux-in-same-row").change(function() {
        let that = $(this);
        let days = that.val();
        let dat_tr = that.closest('tr');
        let redux = dat_tr.find('input.days-extended-redux');
        redux.val(days);
    });
});
