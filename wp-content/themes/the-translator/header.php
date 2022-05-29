<!doctype html>
<?php
global $wp;
    /*
   * current-php-code 2020-Sep-30
   * input-sanitized : lang,spage, text
   * current-wp-template:  header
   */
$lang_code =    FLInput::get('lang', 'en');
$text =         FLInput::get('text');
$spage =        FLInput::get('spage',1);
FreelinguistDebugFramework::note('standard header');
?>

<html <?php language_attributes(); ?> class="no-js">
<?php

//code-notes when disabling the WPML Multilingual CMS the icl_get_languages goes away
if (function_exists('icl_get_languages') ) {
    /** @noinspection PhpDeprecationInspection */
    $currentLang = icl_get_languages();

    if (!empty($currentLang)) {
        $b_not_found = true;
        foreach($currentLang as $lang){
            if(FLInput::exists('lang') && $lang_code === $lang['language_code']){
                $b_not_found = false;
                break;
            }
        }

        if ($b_not_found && empty($_POST)) {
            //get the query string and add on the lang
            $new_url = modify_this_site_query_string('lang',$lang['language_code']);
            wp_redirect($new_url);
            exit;
        }
    }
}





$language_is_url_param = FLInput::exists('lang') ? 'lang='.$lang_code : '';
	 
$current_user_id 	= get_current_user_id();

global $wpdb;

$releatedTags = $wpdb->get_results("SELECT * FROM `wp_interest_tags` ORDER BY rand() LIMIT 0,4",ARRAY_A);


if(is_user_logged_in() && (xt_user_role() == "translator" || xt_user_role() == "customer")){
   if(get_user_meta(get_current_user_id(),'total_user_balance',true)>=0){
	   
   }else{
	   
	   $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
	   
	   if (strpos($actual_link, 'wallet-detail') == false) { 
			wp_redirect('/wallet-detail/');
			exit();
	   }
	  
   }
}
?>

<head>

	<meta charset="<?php bloginfo('charset'); ?>">

	<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--    code-notes removed jquery cdn version of 1.11.3-->
    <?php
    $current_url = home_url( add_query_arg( array(), $wp->request ) );
    if(strpos($current_url, "peerok-technical-translation") == true){
    ?>
    <title>Freelance Translators, Find Paid Work, Quality Assured, Free to Post</title>
    <meta name="title" content=" Freelance experts, Find Paid Work, Quality Assured, Free to Post | PeerOK">
    <meta name="description"
          content=" Our freelancers translate your voice into multiple languages opens doors for your business and maximizes your worldwide appeal. ">
    <meta name="keywords" content=" Translation, PeerOK, Paid Work">
    <meta name="robots" content="index, follow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="English">

    <?php
    }else  if(strpos($current_url, "peerok-editing-proofreading") == true){
        ?>
        <title>Freelancer Jobs, Post Competition and Award Winners</title>
        <meta name="title" content=" Freelancer Jobs, Post Competition and Award Winners">
        <meta name="description" content="Post a competition and our freelancers will deliver many proposals for you. Only award the best.">
        <meta name="keywords" content="Competition, Award, Proofreading, Editing, Logo">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php

    }else  if(strpos($current_url, "peerok-press-release-and-speech-writing") == true){
        ?>
        <title>Writing Services & Buy or Sell Content | PeerOK Marketplace</title>
        <meta name="title" content=" Writing Services & Buy or Sell Content | PeerOK Marketplace ">
        <meta name="description" content=" Freelance writers and editors • Buy texts, articles, or even local news report.">
        <meta name="keywords" content="Writing services, Buy article, news report">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php

    }else  if(strpos($current_url, "peerok-localization-and-internationalization") == true){
        ?>
        <title>Hire Freelance Translators| Affordable & Reliable Experts‎</title>
        <meta name="title" content=" Hire Freelance Translators| Affordable & Reliable Experts‎ ">
        <meta name="description" content=" Access Digital Services and Content From PeerOK. 24H Delivery. Unbeatable value. Professional sellers. ">
        <meta name="keywords" content="Translation, Digital Services, Freelancer">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php

    }else  if(strpos($current_url, "peerok-multilingual-websites") == true){
        ?>
        <title>Hire Freelance Translator| PeerOK </title>
        <meta name="title" content=" Hire Freelance Translator| PeerOK ">
        <meta name="description" content=" Find Experts In Almost Every Language In The World. Cloud-based Ecosystem. Steps: Post a Project, Hire A Translator, Happy Delivery, Pay!">
        <meta name="keywords" content="Freelancers, Translation, Hire, Find Jobs">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php

    }else  if(strpos($current_url, "peerok-document-translation") == true){
        ?>
        <title>Buy Original Articles Online | Get Yours Today on ‎Freeliguist</title>
        <meta name="title" content=" Buy Original Articles Online | Get Yours Today on ‎Freeliguist ">
        <meta name="description" content=" Stop Postponing, Get It Done. Creative, Original Content. Hire Your Freelance Writer Today on Fiverr. Unbeatable value.">
        <meta name="keywords" content="Translation, Buy Articles, Content Mall">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php

    }else  if(strpos($current_url, "peerok-personal-translation-services") == true){
        ?>
        <title>Sell articles online and make Over $5k a Month | PeerOK C-Mall </title>
        <meta name="title" content=" Sell articles online and make Over $5k a Month | PeerOK C-Mall ">
        <meta name="description" content=" Content & Article Writing Service – Buy and Sell Articles.">
        <meta name="keywords" content="Article for sale, Make Money, Ghost Writing">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php

    }else  if(strpos($current_url, "peerok-video-translation-transcription-subtitling") == true){
        ?>

        <title>Work from home, Translation Jobs ‎ | PeerOK </title>
        <meta name="title" content=" Work from home, Translation Jobs ‎  | PeerOK">
        <meta name="description" content="Find jobs in Translation and land a remote Translation freelance contract today.">
        <meta name="keywords" content=" Translation, Find Jobs, Freelance work, Earnings">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php

    }else  if(strpos($current_url, "peerok-documentation-writing") == true){
        ?>
        <title>Online Writing Jobs & Freelance Content Writing Opportunities</title>
        <meta name="title" content="Online Writing Jobs & Freelance Content Writing Opportunities">
        <meta name="description" content=" Find $$$ Online Writing Jobs or hire an Online Writer to bid on your Online Writing Job at Freelancer. Are you passionate about writing on a specific topic? Do you work well with a variety of clients and under tight deadlines? ">
        <meta name="keywords" content=" Writing, Online Jobs, Freelance, PeerOK">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php

    }else  if(strpos($current_url, "peerok-gaming-translation") == true){
        ?>
        <title>Graphic Design Jobs | PeerOK </title>
        <meta name="title" content=" Graphic Design Jobs | PeerOK ">
        <meta name="description" content="Find freelance Graphic designer work on PeerOK. Work on Graphic Design Jobs Online and Find Freelance Graphic Design Jobs from Home Online at PeerOK. Search Jobs and apply for freelance Graphic.">
        <meta name="keywords" content="Graphic design, Find Jobs, Search Jobs ">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php
    }else  if(strpos($current_url, "peerok-article-writing-for-blogs-newsletters") == true){
        ?>
        <title>Article Writing | PeerOK Marketplace</title>
        <meta name="title" content="Article Writing | PeerOK Marketplace ">
        <meta name="description" content="We provide Article writing services for blogs, newsletters and many more. Our freelancers have experience of several years in this field.">
        <meta name="keywords" content="Article Writing, Creative Writing, Blogs">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php
    }else  if(strpos($current_url, "peerok-resume-writing") == true){
        ?>
        <title>Resume Writing | PeerOK Marketplace</title>
        <meta name="title" content="Resume Writing | PeerOK Marketplace ">
        <meta name="description" content="Are you looking for a Professional Resume to describe your profile in an attracting and eye catching way. Our Iinguistics are here to help you.">
        <meta name="keywords" content="Resume Writing, Portfolio writing, PeerOK">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php
    }else  if(strpos($current_url, "support-for-linguists") == true){
        //code-unused there is no longer this page, which was a redirect to the freelancer homepage for a while, but later was dropped
        ?>
        <title>Help Desk | PeerOK Marketplace </title>
        <meta name="title" content="Help Desk | PeerOK Marketplace">
        <meta name="description" content="PeerOK is a marketplace for exchanging digital content services. For any Questions, Queries contact us. Our staff is available 24/7.">
        <meta name="keywords" content="Help, Queries, Freelancers, Freelance jobs">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php
    }else  if(strpos($current_url, "translatoreditorwriter") == true){
        ?>
        <title>Sign Up Now to Find Jobs online, or sell your articles, earn $$$ today | PeerOK Marketplace</title>
        <meta name="title" content="Sign Up Now to Find Jobs online, or sell your articles, earn $$$ today | PeerOK Marketplace">
        <meta name="description" content=" Work on jobs now .">
        <meta name="keywords" content="Find Jobs, Make Money, Freelance, Online Jobs, PeerOK">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php
    }else  if(strpos($current_url, "about-peerok") == true){
        ?>
        <title>Freelance Jobs and Articles Marketplace | PeerOK        </title>
        <meta name="title" content=" Freelance Jobs and Articles Marketplace | PeerOK ">
        <meta name="description" content=" Premium marketplace for workers and buyers. Fulfill all your content writing needs. Free to post! Unlimited FREE revisions. Not pay until 100% satisfied! ">
        <meta name="keywords" content=" Freelancers, Writing, freelance, marketplace">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php
    }else  if(strpos($current_url, "terms-of-service") == true){
        ?>
        <title>Sell Designs Online & Earn Passive Income ‎ | PeerOK, Better Value</title>
        <meta name="title" content=" Sell Designs Online & Earn Passive Income ‎ | PeerOK, Better Value ">
        <meta name="description" content=" Are you an illustrator? Graphic Designer?  Perfect for artists, graphic designers, & photographers to sell all the work and earn money from creative work.  ">
        <meta name="keywords" content="graphic design, passive income, Freelilnguist">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php
    }else  if(strpos($current_url, "privacy-peerok") == true){
        ?>

        <title>Buy Custom Written Content | For Your Website, Blog, Report, or Shop‎
        </title>
        <meta name="title" content=" Buy Custom Written Content | For Your Website, Blog, Report, or Shop ">
        <meta name="description" content="No Agency Rates, High Quality, Exceptional Value · Get It Done on any Budget. Post Your Job & Find Affordable Graphic Designers Today For Free. Start Now. Save Time & Money.">
        <meta name="keywords" content="Custom content, Writing, Budget, graphic designer">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php
    }else  if(strpos($current_url, "careers-peerok") == true){
        ?>
        <title>Earn Good Money with Freelance Writing Jobs</title>
        <meta name="title" content="Earn Good Money with Freelance Writing Jobs">
        <meta name="description" content=" Want to freelance full-time? Put your strong writing skills to work for a profit. You can either sell your work or work on projects at home. Sign up today.">
        <meta name="keywords" content="Careers, Freelancers, Jobs, Freelance, work at home">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php
    }else  if(strpos($current_url, "contact-peerok") == true){
        ?>
        <title>Freelancers | For An Entrepreneur's Budget‎ </title>
        <meta name="title" content=" Freelancers | For An Entrepreneur's Budget‎ ">
        <meta name="description" content=" Congratulations. You Now Have A Staff Of Millions. Hire Global Freelancers Now! Hire Real Doers. Professional sellers. Unbeatable value. 24H Delivery. Categories: Graphics & Design, Digital Marketing, Writing & Translation. ">
        <meta name="keywords" content=" Entrepreneur, Marketing experts, PeerOK">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <?php
    }else  if(strpos($current_url, "peerok-faq") == true){
        ?>
        <title>Online Freelance Writing: Make $100+ An Hour Writing | PeerOK Marketplace</title>
        <meta name="title" content=" Online Freelance Writing: Make $100+ An Hour Writing | PeerOK Marketplace ">
        <meta name="description" content=" Earn money as a freelancer. Making money online. Turn your creativity into cash with PeerOK. Online Freelance Writing ..">
        <meta name="keywords" content="earn cash, Freelancers, freelance writing">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php
    }else  if(strpos($current_url, "pricing") == true){
        //code-unused there is no pricing page currently in the code
        ?>
        <title>Budget Pricing for all your writing needs | PeerOK mall</title>
        <meta name="title" content=" Budget Pricing for all your writing needs | PeerOK mall ">
        <meta name="description" content=" Find freelance jobs. Make Money Online. professionals, consultants, freelancers & contractors and get your project done remotely online. Announce your profile for free and get hired now!">
        <meta name="keywords" content="Pricing, Rates, Quality, Freelance">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">

        <?php
    }else{

    ?>

        <title>Best Freelancers and Unique Content, Find Paid Work, Quality Assured, Free to Post | PeerOK ®
        </title>
        <meta name="title" content=" Best Freelancers and Unique Content,  Free to View and Post | PeerOK">
        <meta name="description" content=" Get your jobs done at PeerOK with Professional freelancers.">
        <meta name="keywords" content="Linguist, Freelance, Content marketplace">
        <meta name="robots" content="index, follow">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="language" content="English">


        <!--    <meta itemprop="name" content="TRANSLATION, EDITING, OR WRITING WE MAKE IT FAST, EASY, AND AFFORDABLE TO GET QUALITY CONTENT FOR YOU">-->
<!---->
<!--    <meta name="description" content="TRANSLATION, EDITING, OR WRITING WE MAKE IT FAST, EASY, AND AFFORDABLE TO GET QUALITY CONTENT FOR YOU">-->
<!---->
<!--    <meta itemprop="description" content="TRANSLATION, EDITING, OR WRITING WE MAKE IT FAST, EASY, AND AFFORDABLE TO GET QUALITY CONTENT FOR YOU">-->
<!---->
<!--    <meta name="keywords" content="TRANSLATION, EDITING, OR WRITING">-->
<!---->
<!--    <meta itemprop="keywords" content="TRANSLATION, EDITING, OR WRITING WE MAKE IT FAST, EASY, AND AFFORDABLE TO GET QUALITY CONTENT FOR YOU">-->

    <?php } ?>

    <meta property="og:title" content="<?php wp_title(''); ?><?php if(wp_title('', false)) { echo ' :'; } ?> <?php bloginfo('name'); ?>">

    <meta property="og:url" content="<?php echo get_site_url(); ?>">

    <meta property="og:description" content="TRANSLATION, EDITING, OR WRITING WE MAKE IT FAST, EASY, AND AFFORDABLE TO GET QUALITY CONTENT FOR YOU">

    <meta property="og:type" content="article">

    <meta property="og:image" content="<?php bloginfo('template_url'); ?>/images/logo-1000-by-200.png">

    <meta property="og:site_name" content="PeerOK">

    <meta name="HandheldFriendly" content="True" />

    <meta name="MobileOptimized" content="320" />

   
	<!--suppress HtmlUnknownTarget -->
    <link rel="shortcut icon" href="<?php bloginfo('template_url'); ?>/images/fav-icon.ico?v=3" type="image/x-icon"/>

	<title> <?php echo get_custom_string_return('PeerOK: hire best freelancers and buy digital content'); ?></title>

	<?php wp_head(); ?>
	
	<!--suppress JSUnusedLocalSymbols -->
    <script>
		var site_url = '<?php echo site_url();?>';
		var base_url = '<?php echo home_url();?>';
		var xt_user_role = '<?= xt_user_role() ?>';
	</script>

    <script>
        //code-notes add centralized form keys here, for things that appear on multiple pages but never more than once
        if (adminAjax) {
            adminAjax.form_keys.hz_post_fl_discussion  = '<?= FreeLinguistFormKey::create_form_key('hz_post_fl_discussion') ?>';
            adminAjax.form_keys.content_public_discussion  = '<?= FreeLinguistFormKey::create_form_key('content_public_discussion') ?>';
            adminAjax.form_keys.hz_submit_report  = '<?= FreeLinguistFormKey::create_form_key('hz_submit_report') ?>';
            adminAjax.form_keys.hz_post_fl_content_discussion
                = '<?= FreeLinguistFormKey::create_form_key('hz_post_fl_content_discussion') ?>';

        }
    </script>

</head>

<body <?php body_class(); ?>>




	<!--suppress JSUnusedLocalSymbols -->
<script>

	var templateUrl = '<?php echo get_bloginfo("template_url"); ?>'; 

	var siteUrl = '<?php echo get_site_url(); ?>'; 

	</script>

	<?php $current_user = wp_get_current_user(); ?>

	<style>

		@media( max-width: 767px ){

			.customer-review .data-table tbody td:first-child::before {content: "<?php echo get_custom_string_return('Rating'); ?>";}

			.customer-review .data-table tbody td:nth-child(2)::before {content: "<?php echo get_custom_string_return('Comments'); ?>";}

			.customer-review .data-table tbody td:nth-child(3)::before {content: "<?php echo get_custom_string_return('Customer'); ?> ";}

			.customer-review .data-table tbody td:nth-child(4)::before {content: "<?php echo get_custom_string_return('JOB ID'); ?>";}

			.customer-review .data-table tbody td:nth-child(5)::before {content: "<?php echo get_custom_string_return('Completion date'); ?>";}



			.accdetal-table .data-table{border-top: 1px solid #e8f0f5;word-break:break-all;}

			.education-data tbody td:first-child::before{content:"<?php echo get_custom_string_return('Years attended'); ?>";}

			.education-data tbody td:nth-child(2)::before{content:"<?php echo get_custom_string_return('Institution '); ?>";}

			.education-data tbody td:nth-child(3)::before{content:"<?php echo get_custom_string_return('Degree'); ?>";}



			.license-data td:first-child::before{content:"<?php echo get_custom_string_return('Year received'); ?>";}

			.license-data td:nth-child(2)::before{content:"<?php echo get_custom_string_return('Received From'); ?>";}

			.license-data td:nth-child(3)::before{content:"<?php echo get_custom_string_return('License / Certifi-cate / Awards'); ?>";}

			.experience-data td:first-child::before{content: "<?php echo get_custom_string_return('Years in service'); ?>";}

			.experience-data td:nth-child(2)::before{content: "<?php echo get_custom_string_return('Employer'); ?>";}

			.experience-data td:nth-child(3)::before{content: "<?php echo get_custom_string_return('Duties'); ?>";}


			.wallet-table tbody td:first-child:before {content: "<?php echo get_custom_string_return('Date'); ?>";}

			.wallet-table tbody td:nth-child(2):before {content: "<?php echo get_custom_string_return('Transaction ID'); ?>";}

			.wallet-table tbody td:nth-child(3):before {content: "<?php echo get_custom_string_return('Description'); ?>";}

			.wallet-table tbody td:nth-child(4):before {content: "<?php echo get_custom_string_return('Printable receipt'); ?>";}

			.wallet-table tbody td:nth-child(5):before {content: "<?php echo get_custom_string_return('Amount'); ?>";}

			.wallet-table thead {display: none;}

			.wallet-table tbody td:first-child {border-top: 1px solid #c8d5dc;}

			.wallet-table.transaction-table td:nth-child(5):before{content:"<?php echo get_custom_string_return('Total Amount'); ?>";}

			.wallet-table.transaction-table td:nth-child(6):before{content:"<?php echo get_custom_string_return('FREE Credits'); ?>";}

			.wallet-table.transaction-table td:nth-child(7):before{content:"<?php echo get_custom_string_return('Amount'); ?>";}

		}
		
	#notification {
		width: 100%;
		margin: 0 auto;
		background: #EE2B31;
		color: #fff;
		display: none;
		text-align: left;
		position: fixed;
		bottom: 0px;
		z-index: 999;
	}
	#notification #msg a{color:#fff;text-align:center;}
	#notification #close{
		position: relative;
		right: 0px;
		cursor: pointer;
		text-align:right
	}

	</style>
	
	<header class="header">

		<div class="container">

			<div class="row">



				<div class="col-lg-3 col-md-3 col-sm-6">



					<?php if( $_SERVER['SERVER_NAME'] != 'www.wenren8.com'){  $logo_title = "PeerOK"; } else { $logo_title = "Wenren8"; } ?>

					<?php if(is_user_logged_in() && (xt_user_role() == "translator")){ ?>


						<a href="<?php echo freeling_links('translator_homepage_url'); ?>" class="logo"><!--suppress HtmlUnknownTarget -->
                            <img src="<?php echo get_template_directory_uri(); ?>/images/logo-1000-by-200.png"  alt="<?php echo $logo_title; ?>"></a>

					<?php }else{ ?>

						<a href="<?php bloginfo('url'); ?>" class="logo"><!--suppress HtmlUnknownTarget -->
                            <img  src="<?php echo get_template_directory_uri(); ?>/images/logo-1000-by-200.png"  alt="<?php echo $logo_title; ?>"></a>

					<?php } ?>

				</div>

				<?php if(is_user_logged_in()){ ?>
					<div class="col-lg-3 col-md-5 col-sm-6 search-header" style="margin-left:0;">

						<div>

                            <form id="tag_submit" method="get" action="<?php echo get_site_url().'/searches'; ?>">
                                <input type="hidden" id="lang" name="lang" value="<?= $lang_code ?>" />
                                <input type="hidden" id="spage" name="spage" value="<?=  $spage  ?>" />
								<input id="headerSearch" name="f" class="" type="text" value="<?= $text; ?>" placeholder="Try “Las Vegas”">
								<button type="submit"  class="search_button"><i class="fa fa-search large-text"></i></button>
							</form>
						</div>

						<div id="searchedValues" style="display:none;">
						<?php foreach($releatedTags as $rTags){ ?>
							<span><a href="<?php echo get_site_url().'/searches/?lang='.$lang_code.'&mode='.$rTags['tag_name']; ?>"><?php echo $rTags['tag_name']; ?></a></span>
						<?php } ?>
						</div>

					</div>
					<div class="col-lg-6 col-md-12 col-sm-12 newmenusec-">


						<?php

						$user = wp_get_current_user();

						$role = ( array ) $user->roles;
						//code-notes not everyone coming through will have a role, set it to something never used elsewhere so that the menu creation will not throw warnings
						if (empty($role)) {
						    $role = ['none'];
                        }

						?>
							<div class="clearfix"></div>
							<div class="signin-sec">

								<ul class="signin">

									<?php 

									if($role[0] == 'translator')

									{

										?>

										<li class="add-job">											

											<a href="<?php echo freeling_links('job_listing_url'); ?>"
                                               class="red-btn-no-hover login-btn-n freelinguist-find-jobs-button enhanced-text"
                                            >
                                                <div>
                                                    <i class="fa fa-plus red-background-white-text"></i>
                                                    <span>
                                                        <?php get_header_menu_string('Find Jobs'); ?>
                                                    </span>
                                                </div>
											</a>

										</li>

									<?php

									}

									elseif($role[0] == "customer")

									{

										?>

									<li class="menu_disbled add-job">

											<a href="#" class="add-job-btn signin-bttn login-btn-n enhanced-text" name="<?= $lang_code ?>" id="add_new_job">

											<i class="enhanced-text"></i><?php get_header_menu_string('New Project'); ?></a>

										</li>

									<?php

									}

									else

									{

										?>

										<li class="menu_disbled"><a href="#" class="add-job-btn signin-bttn login-btn-n enhanced-text"><i class="enhanced-text">+</i><?php get_header_menu_string('Find Jobs'); ?></a></li>

									<?php

									}

									?>

								</ul>

							</div>

							<div class="loginmenu" <?php if( $role[0] == 'customer'){ ?> style="float:none;" <?php }?>>

								<ul>

									<?php

									if($role[0] == 'translator')

									{

										?>

										<li class="">
                                            <a href="<?php echo freeling_links('linguist_content_url'); ?>">
                                                <?php get_header_menu_string('Sell'); ?>
                                                <?= FLRedDot::generate_dot_html_for_user(
                                                    [FLRedDot::TYPE_CONTENT]
                                                )?>
                                            </a>
                                        </li>

										<li class="">
                                            <a href="<?php echo freeling_links('dashboard_url'); ?>">
                                                <?php get_header_menu_string('My Jobs'); ?>
                                                <?= FLRedDot::generate_dot_html_for_user(
                                                        [FLRedDot::TYPE_CONTESTS,FLRedDot::TYPE_PROJECTS,]
                                                )?>
                                            </a>
                                        </li>

										

										<li class="dropmenu">

											<a href="#">
                                                Buy
                                                <?= FLRedDot::generate_dot_html_for_user(
                                                    [],null,FreelinguistUserLookupDataHelpers::get_logged_in_role_id(true)
                                                )?>
                                            </a>

											<ul>

												<li>

													<a href="#"
                                                       currentrole="<?php echo $role[0]; ?>"
                                                       currentid="<?php echo $current_user_id; ?>"
                                                       data-url="contentl"
                                                       class="change_cust_role"
                                                    >
                                                        Content Mall
                                                        <?= FLRedDot::generate_dot_html_for_user(
                                                            [FLRedDot::TYPE_CONTENT],
                                                            null,
                                                            FreelinguistUserLookupDataHelpers::get_logged_in_role_id(true)
                                                        )?>
                                                    </a>

												</li>

												<li>

													<a href="#"
                                                       currentrole="<?php echo $role[0]; ?>"
                                                       currentid="<?php echo $current_user_id; ?>"
                                                       data-url="jobs"
                                                       class="change_cust_role"
                                                    >
                                                        <?php get_header_menu_string('My projects'); ?>
                                                        <?= FLRedDot::generate_dot_html_for_user(
                                                            [FLRedDot::TYPE_CONTESTS,FLRedDot::TYPE_PROJECTS],
                                                            null,
                                                            FreelinguistUserLookupDataHelpers::get_logged_in_role_id(true)
                                                        )?>
                                                    </a>

												</li>

											</ul>

										</li>

										<li class="dropmenu">

										  <a href="javascript:;"><?php echo mb_substr($current_user->display_name,0,10); ?></a>

										  <ul>

										    <li><a href="<?php echo freeling_links('my_account_url'); ?>"><?php get_header_menu_string('View Profile'); ?></a></li>

											<li><a href="<?php echo freeling_links('wallet_url'); ?>"><?php get_header_menu_string('Wallet'); ?> </a></li>
											
										    <li><a href="<?php echo freeling_links('linguist_support_page_url'); ?>"><?php get_header_menu_string('Support'); ?></a></li>


										    <li><a href="<?php echo freeling_links('setting_page_url'); ?>"><?php get_header_menu_string('Settings'); ?></a></li>

										    <li>

										    	<a href="<?php echo get_site_url(); ?>?<?=  $language_is_url_param.'&'; ?>action=logout_me"><?php get_header_menu_string('Logout'); ?></a></li>

										  </ul>

										</li>

										<?php

									}

									elseif($role[0] == 'customer')

									{

										?>

										
										<li class="dropmenu">

											<a href="<?php echo freeling_links('content_detail_url'); ?>">
                                                <?php get_header_menu_string('Mall'); ?>
                                                <?= FLRedDot::generate_dot_html_for_user(
                                                    [FLRedDot::TYPE_CONTENT]
                                                )?>
                                            </a>
											<ul>
												<li>
                                                    <a href="<?php echo home_url( '/my-content/' ); ?>">
                                                        <?php get_header_menu_string('My Purchase'); ?>
                                                        <?= FLRedDot::generate_dot_html_for_user(
                                                            [FLRedDot::TYPE_CONTENT]
                                                        )?>
                                                    </a>
                                                </li>
											</ul>
										</li>

										<li>
                                            <a href="<?php echo freeling_links('dashboard_url'); ?>">
                                                <?php get_header_menu_string('My Projects'); ?>
                                                <?= FLRedDot::generate_dot_html_for_user(
                                                    [FLRedDot::TYPE_CONTESTS,FLRedDot::TYPE_PROJECTS,]
                                                )?>
                                            </a>
                                        </li>


										<li class="dropmenu">

											<a href="#">
                                                Sell
                                                <?=  FLRedDot::generate_dot_html_for_user(
                                                    [],null,FreelinguistUserLookupDataHelpers::get_logged_in_role_id(true)
                                                )?>
                                            </a>
											<ul>

												<li>

													<a href="#"
                                                       currentrole="<?php echo $role[0]; ?>"
                                                       currentid="<?php echo $current_user_id; ?>"
                                                       data-url="content"
                                                       class="change_cust_role"
                                                    >
                                                        Sell Content
                                                        <?=  FLRedDot::generate_dot_html_for_user(
                                                            [FLRedDot::TYPE_CONTENT],
                                                            null,FreelinguistUserLookupDataHelpers::get_logged_in_role_id(true)
                                                        )?>
                                                    </a>

												</li>

												<li>

													<a href="#"
                                                       currentrole="<?php echo $role[0]; ?>"
                                                       currentid="<?php echo $current_user_id; ?>"
                                                       data-url="jobs" class="change_cust_role"
                                                    >
                                                        <?php get_header_menu_string('Find Jobs'); ?>
                                                        <?=  FLRedDot::generate_dot_html_for_user(
                                                            [FLRedDot::TYPE_CONTESTS,FLRedDot::TYPE_PROJECTS],
                                                            null,FreelinguistUserLookupDataHelpers::get_logged_in_role_id(true)
                                                        )?>
                                                    </a>

												</li>


											</ul>

										</li>

										<li class="dropmenu">

										  <a href="javascript:;"><?php echo mb_substr($current_user->display_name,0,10); ?></a>

										  <ul>

										    <li><a href="<?php echo freeling_links('my_account_url'); ?>"><?php get_header_menu_string('View Profile'); ?></a></li>
											
											<li><a href="<?php echo site_url('favourite'); ?>"><?php get_header_menu_string('Favorite'); ?></a></li>
											
											<li><a href="<?php echo freeling_links('wallet_url'); ?>"><?php get_header_menu_string('Wallet'); ?> </a></li>
											
										    <li><a href="<?php echo freeling_links('linguist_support_page_url'); ?>"><?php get_header_menu_string('Support'); ?></a></li>


										    <li><a href="<?php echo freeling_links('setting_page_url'); ?>"><?php get_header_menu_string('Settings'); ?></a></li>

										    <li>

										    	<a href="<?php echo get_site_url(); ?>?<?=  $language_is_url_param.'&'; ?>action=logout_me"><?php get_header_menu_string('Logout'); ?></a></li>

										  </ul>

										</li>


										<?php

									}

									else

									{

										?>

										<li class="dropmenu">

										  <a href="javascript:;"><?php echo mb_substr($current_user->display_name,0,10); ?></a>

										  <ul>

										    <li>

										    <a href="<?php echo get_site_url(); ?>?lang=<?php echo $lang_code; ?>&action=logout_me"><?php get_header_menu_string('Logout'); ?></a></li>

										  </ul>

										</li>

										<?php

									}

									?>

								</ul>

							</div>	

					</div>

				<?php } else { /* not logged in */?>

					<div class="col-md-5 col-sm-5 search-header">

						<div>
							<form id="tag_submit" method="get" action="<?php echo get_site_url().'/searches'; ?>">
                                <input type="hidden" id="lang" name="lang" value="<?= $lang_code ?>" />
                                <input type="hidden" id="spage" name="spage" value="<?= $spage ?>" />
                                <input id="headerSearch" name="text" class="" type="text" placeholder="Try “Las Vegas”" value="<?= $text ?>">

                                <button type="submit" class="search_button"><i class="fa fa-search large-text"></i></button>
							</form>
						</div>

						<div id="searchedValues" style="display:none;">
						<?php foreach($releatedTags as $rTags){ ?>
							<span><a href="<?php echo get_site_url().'/searches/?lang='. $lang_code.'&mode='.$rTags['tag_name']; ?>"><?php echo $rTags['tag_name']; ?></a></span>
						<?php } ?>
						</div>

					</div>







					<div class="col-md-4 col-sm-4 headeright">

						<div class="signin-sec">

							<ul class="signin">

								<li>

									<a class="signin-bttn login-btn-n enhanced-text" href="<?php echo freeling_links('login_url'); ?>">Sign in</a>

								</li>

							</ul>

							<ul class="list-item headmenu">

								<li>

									<a class="dotmain"  href="#"></a>

									<ul class="shp-d">

									<?php $languageis = $lang_code; ?>

										<li><a href="<?php echo get_site_url(). '/translatoreditorwriter/?lang=' ?><?php echo $languageis; ?>">Expert Freelancers</a></li>

										<li><a href="<?php echo get_site_url() .'/how-it-works/?lang=' ?><?php echo $languageis; ?>">How It Works</a></li>

										<li><a href="<?php echo get_site_url() .'/peerok-faq/?lang=' ?><?php echo $languageis; ?>">FAQ</a></li>

										<li><a href="<?php echo get_site_url() .'/contact-peerok/?lang=' ?><?php echo $languageis; ?>">Contact Us</a></li>

										<li><a href="<?php echo get_site_url() .'/about-peerok/?lang=' ?><?php echo $languageis; ?>">About Us</a></li>

										<li><a href="<?php echo get_site_url() .'/careers-peerok/?lang=' ?><?php echo $languageis; ?>">Career</a></li>

										<li><a href="<?php echo get_site_url() .'/terms-of-service/?lang=' ?><?php echo $languageis; ?>">Terms of Service</a></li>

										<li><a href="<?php echo get_site_url() .'/privacy-peerok/?lang=' ?><?php echo $languageis; ?>">Privacy</a></li>

										<li><!--suppress HtmlUnknownTarget -->
                                            <img src="<?php bloginfo('template_url'); ?>/images/payment-methods.jpg"></li>

									</ul>

								</li>

							</ul>


							<ul class="for-linguists">

								<li>

									<a class="enhanced-text" href="">For Freelancers</a>

									<ul>

										<li><a class="enhanced-text" href="<?php echo site_url().'/login?redirect_to=jobs'?>">Find Jobs</a></li>

										<li>
                                            <a class="enhanced-text" href="<?php echo site_url().'/login?redirect_to=linguist-content'?>">
                                                Sell
                                                <?php get_header_menu_string('Sell Content'); ?>
                                                <?= FLRedDot::generate_dot_html_for_user(
                                                    [FLRedDot::TYPE_CONTENT]
                                                )?>
                                            </a>
                                        </li>

									</ul>

								</li>

							</ul>

						</div>

					</div>	

				<?php } ?>

			</div>

		</div>

	</header>
	<div id="notification" class="large-text">
      <div class="col-lg-11"><span id="msg"><span>testsxts</span></span></div>
      <div class="col-lg-1" style="text-align:right"><span id="close" class="fa fa-times"></span></div>
    </div>
	<div class="alert_message"></div>

	<?php set_cookie_for_language(); ?>





<script type="text/javascript">
 
	jQuery(function($){



		jQuery('.dotmain').click(function(){

			jQuery(this).next('ul').slideToggle();

		});
		
		jQuery('body').click(function(evt){    
			   if(jQuery(evt.target).closest('.dotmain').length)
				  return;  
				jQuery('.dotmain').next('ul').hide();

		});
		
		jQuery('.for-linguists li').hover(function(){
				jQuery('.dotmain').next('ul').hide();
		});




		$(document).on('click','.change_cust_role',function(){

	        var curRole = jQuery(this).attr('currentrole');

	        var curId = jQuery(this).attr('currentid');

	        var url = jQuery(this).attr('data-url');

	        var data = {

	            'action': 'hz_change_role_cus_ling',

	            'curRole': curRole,

	            'curId': curId,

	            'gotoUrl': url,

	        };

	        jQuery.post(getObj.ajaxurl, data, function(response){
				response = response.replace(/(\r\n|\n|\r)/gm,"");
	            if(response === 'content'){

	                window.location.href= '<?php echo get_site_url().'/linguist-content/?lang=en' ?>';

	            }

	            else if(response === 'contentl'){

	                window.location.href= '<?php echo get_site_url().'/content/?lang=en' ?>';

	            }

	            else if(response === 'jobs'){

	            	window.location.href= '<?php echo get_site_url().'/dashboard/?lang=en' ?>';

	            }

	            else if(response === 'wallet'){

	            	window.location.href= '<?php echo get_site_url(); ?>/wallet-detail/?lang=en';

	            }

	            else{

	            	alert('no results');

	            }

	        });

	   	});



	});

</script>

<!--suppress JSUnresolvedVariable -->
<script>
    jQuery(function() {
        //"wp-json/?lang=en/contact-form-7/v1"
        if (typeof wpcf7 !== 'undefined') {
            if (wpcf7) {
                if (wpcf7.hasOwnProperty('apiSettings')) {
                    if (wpcf7.apiSettings.hasOwnProperty('root') ) {
                        wpcf7.apiSettings.root = wpcf7.apiSettings.root.replace('?lang=en/','');
                    }

                }
            }
        }

        //wpcf7.apiSettings.root = "(base)/wp-json/contact-form-7/v1";
    });
</script>


