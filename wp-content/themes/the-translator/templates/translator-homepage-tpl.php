<?php
/*
Template Name: Translator Homepage
*/

/*
* current-php-code 2020-Oct-15
* input-sanitized :
* current-wp-template:  homepage for the freelancer
* post-content-title: translatoreditorwriter
* current-wp-top-template
*/
ob_start();
get_header('new');
$static_section = FreelinguistStaticPageLogic::get_static_page('translatoreditorwriter')
?>
<style>
    span.fl-freelancer-faq-header {
        text-decoration: underline;

    }

    p.fl-freelancer-faq-answer {
        text-align: justify;
        color:  #000000;
        font-weight: bold;
    }
</style>
<?= $static_section?>

<!--this task drop in static here-->

<section class="faq-sec">
	<div class="container">
		<span class="bold-and-blocking large-text"><?php echo get_custom_string_return('Frequently Asked Questions'); ?></span>
		<ul>

            <li>
                <span class="fl-freelancer-faq-header enhanced-text" >
                    What services are provided?
                    <i class="arrowicon"></i>
                </span>

                <p class="fl-freelancer-faq-answer enhanced-text">
                    PeerOK is a marketplace for exchanging digital content and digital content services.
                    You can buy digital content services directly from linguists, or buy pre-made digital contents directly. <br />
                    There’re three ways to obtain the digital content services. <br />
                     a. Post a Project: you can post a project and then select linguists that have placed bids to work on it. <br />
                     b. Post a Competition: you can post a competition. Our linguists will submit proposals with proposed contents. Then you can award winning proposals. <br />
                     c. Buy a digital Content: Our linguists have posted pre-made contents ready for sale. You can review the list and buy as many as you want. <br />
                    You own the IP rights of the content purchased from our platform.
                </p>
            </li>

            <li>
                <span class="fl-freelancer-faq-header enhanced-text" >
                    Will my jobs be kept confidential?
                    <i class="arrowicon"></i>
                </span>

                <p class="fl-freelancer-faq-answer enhanced-text">
                    All linguists providing services on PeerOK  have to follow a non-disclosure agreement.
                    No freelancer is allowed to share any information related to your job to any third party, which ensures your privacy is upheld.
                    Please refer to the freelancer NDA in Terms of Service for details.
                </p>
            </li>

            <li>
                <span class="fl-freelancer-faq-header enhanced-text" >
                    How will I get paid? How can I withdraw my earnings?
                    <i class="arrowicon"></i>
                </span>

                <p class="fl-freelancer-faq-answer enhanced-text">
                    All payments made by the client are paid first to the PeerOK<sup>® </sup> escrow account.
                    After the work is approved completed by the client, the earnings will be transferred to your account immediately. <br><br>
                    You can request withdrawal from your account by using the Withdrawal function in the Wallet page in your dashboard.
                    Once you have sent in a withdrawal request, our accounting team will process your request and send you payment after deducting no later than the upcoming payment date.
                    You will receive the amount after the deduction of potential fees. <br><br>
                    Please note that in order for us to send you the payment, you need to upload the proper signed tax form in the Wallet page.
                    As per the U.S.A. tax laws, this form declares your taxpayer status and is mandatory. The tax form is just for record keeping purposes, which is required by IRS.
                    We will not use your tax form for any purpose other than record keeping.
                </p>
            </li>

            <li>
                <span class="fl-freelancer-faq-header enhanced-text" >
                    How do I create an outstanding profile and make more money?
                    <i class="arrowicon"></i>
                </span>

                <p class="fl-freelancer-faq-answer enhanced-text">
                    Make your profile an overview, resume, and freelancer brochure rolled into one.
                    It must highlight your professional credentials, job experience and portfolio, education accomplishments, and official certifications.
                    Satisfactory profiles are complete, well-written, and error-free.
                </p>
            </li>

            <li>
                <span class="fl-freelancer-faq-header enhanced-text" >
                    Can I get refund if I’m not happy with the delivered content?
                    <i class="arrowicon"></i>
                </span>

                <p class="fl-freelancer-faq-answer enhanced-text">
                    Of course. This can be done easily by requesting to reject the ongoing job. Once the request is approved by linguist, the job will be cancelled and you will be fully refunded.
                </p>
            </li>



		</ul>
	</div>
</section>

<script type="text/javascript">
	jQuery(function($){


		jQuery('.faq-sec ul li span.fl-freelancer-faq-header').click(function(){
		    $('p.fl-toggled').slideToggle('slow').removeClass('fl-toggled').find('i').toggleClass("down");
			$(this).next('p').slideToggle('slow').addClass('fl-toggled');
			$(this).find('i').toggleClass("down");
		});
	})
	
</script>
<?php get_footer('homepagenew'); ?>