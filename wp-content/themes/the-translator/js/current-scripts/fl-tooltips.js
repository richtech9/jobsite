function freelinguist_tag_help(input_text_element,b_right){
    let b_displayed = false;
    let message = "Press 'enter' or comma ',' to enter a keyword";

    b_right = !!b_right;
    let position = 'top left';
    if (b_right) {
        position = "top right";
    }


    input_text_element.keydown(function(){
        if (b_displayed) {return;}
        b_displayed = true;
        input_text_element.notify(
            message,
            {
                position:position,
                autoHide:true,
                className:'success',
                autoHideDelay: 60000
            }
        );
    });
}

jQuery(function($) {

    let b_displayed = false;

    function show_tip(element,message) {

        if (b_displayed) {return;}
        b_displayed = true;

        let position = "top center";
        let timer_wait = 10000;
        element.notify(
            message,
            {
                position:position,
                autoHide:true,
                className:'success',
                autoHideDelay: timer_wait
            }
        );

        setTimeout(function() {
            b_displayed = false;
        },timer_wait)
    }

   $('div.freelinguist-change-project-type').mouseenter(function() {
       let element = $(this);
       let message = element.data('message');
       show_tip(element,message);
   });

    $('div.freelinguist-change-project-type').click(function() {
        let element = $(this);
        let message = element.data('message');
        show_tip(element,message);
    });


    $('div.freelinguist-change-project-type input[type=radio][name=freelinguist_project_type]').change(function() {

        let that = $(this);
        let link = that.data('link');

        if (!link) {return;}
        window.location.href = link;

    });
});