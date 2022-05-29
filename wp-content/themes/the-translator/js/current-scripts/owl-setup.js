jQuery(function($){


	//code-bookmark-js displaying the files for the customer seeing the content (reading)
	$(".carousel-area").owlCarousel({
		items: 4,
		loop: true,
		autoplay: false,
		autoplayTimeout: 1000,
		dots: false,
		nav: true,
		navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>",],
		responsive : {
            0 : {
                items: 1
            },
            768 : {
                items: 2
            },
            992 : {
                items: 4
            }
        }
	});



	
});