<?php
/*
* current-php-code 2020-Nov-13
* input-sanitized : lang
* current-wp-template:  freelancer hire dialog and js / setup button with data: freelancer_id, freelancer_nicename
*/

/**
 * @usage get_template_part('includes/user/author-user-info/translator', 'hire-dialog');
 * wp-content/themes/the-translator/includes/user/author-user-info/translator-hire-dialog.php
 */

$lang = FLInput::get('lang','en');
?>

<script>
    jQuery(function($){
        jQuery("#delivery_date").datepicker({minDate: 0});

        jQuery('button.hire-freelancer').click(function() {
            let dat = $(this);
            let modal_thing = $('div#hireTranslatorModel');

            let da_id = dat.data('freelancer_id');
            modal_thing.find('input#linguist_id').val(da_id);

            let da_nicename = dat.data('freelancer_nicename');
            modal_thing.find('input#user').val(da_nicename);
            modal_thing.find('span.freelancer-name').html(da_nicename);

            $('div#hireTranslatorModel').modal();
        });
    });
</script>
<div role="dialog" id="hireTranslatorModel" class="modal fade">

    <div class="modal-dialog">

        <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

                <button data-dismiss="modal" class="close huge-text" type="button">×</button>

                <h4 class="modal-title">Hire <span class="freelancer-name"></span> </h4>

            </div>

            <div class="modal-body">

                <div id="alert_message_model"></div>

                <form id="hire_linguist_by_customer">


                    <div class="form-group">


                        <textarea maxlength="10000" class="form-control" style="height:200px"
                                  name="description" id="hire_linguist_description"  autocomplete="off"
                                  placeholder="Description" required></textarea>

                    </div>

                    <div class="form-group">
                        <label for="budget">Budget($)</label>
                        <input title="Budget" type="number" name="estimated_budgets" id="estimated_budgets"
                               value="" class="form-control" maxlength="10000" required>

                    </div>


                    <div class="form-group">
                        <label for="budget">Delivery Date</label>
                        <input type="text" name="delivery_date" id="delivery_date" value=""
                               class="form-control" maxlength="10000" placeholder="" readonly
                               required>
                    </div>

                    <div class="form-group">&nbsp;</div>

                    <p class="form-submit">

                        <button type="button" value="" class="btn blue-btn" id="submit"
                                name="submit_hire"
                                onclick="return hire_linguist();"><?php get_custom_string('Hire'); ?> </button>

                        <input type="hidden" id="linguist_id" value=""
                               name="linguist_id">

                        <input type="hidden" id="user" value=""
                               name="user">

                        <input type="hidden" id=""
                               value="<?= $lang; ?>"
                               name="lang">

                    </p>

                </form>

            </div>

        </div>


    </div>

</div>