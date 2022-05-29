<?php
/*
Template Name: HTMLPAGE How it work Template
*/

/*
* current-php-code 2021-Feb-10
* input-sanitized : lang
* current-wp-template:  How It Works Page
* current-wp-top-template
*/
$lang = FLInput::get('lang', 'en');
if(is_user_logged_in() && (xt_user_role() == "translator")) {
	wp_redirect(freeling_links('translator_homepage_url'));
}
get_header();
$check_login = (is_user_logged_in()) ? 1 : 0;

?>


<section class="howitwork-banner">
	<div class="container">
		<div class="row">
			<span class="bold-and-blocking large-text">How it Works</span>
			<p class="large-text">We Make it Fast, Easy, and Affordable to get Quality Content<br>only three Steps</p>
			<ul class="banner-icon">
				<li>
					<div class="icon-sec">
						<img src="<?php echo get_template_directory_uri().'/images/static/small-clipboard-pencil.png' ?>">
					</div>
					<h3>Post Project</h3>
					<p class="large-text">
						Post a Project Need <br>
                        for FREE
                    </p>
				</li>
				<li>
					<div class="icon-sec" style="background:#ee2b31;">
						<img src="<?php echo get_template_directory_uri() . '/images/security-expert-icon.png' ?>">
					</div>
					<h3>Select Expert Freelancers</h3>
					<p class="large-text">
						Freelancers Place Bids<br>
						Choose the Best
					</p>
				</li>
				<li>
					<div class="icon-sec">
						<img src="<?php echo get_template_directory_uri(). '/images/static/small-red-open-folder.png' ?>">
					</div>
					<h3>Receive Content</h3>
					<p class="large-text">
						Download Service Content<br>
						Revise until 100% Satisfied
					</p>
				</li>
			</ul>
			<div class="registerbttn">
				<a class="btn blue-btn next-btn postproject regular-text" name="submit_order" href="<?php echo site_url().'/registration';?>">
                    <img src="<?php echo get_template_directory_uri().'/images/post-icon.png' ?>">
                    Register Now
                </a>
			</div>
		</div>
	</div>
</section>

<!--How it Works -->
<section class="how-works text-center">
	<script src="<?php echo get_site_url(); ?>/wp-content/themes/the-translator/js/lib/flip.js"></script>
	<script>
    jQuery(function(){
    	jQuery(".flip-horizontal").flip({
  			trigger: 'hover'
		});
    });
    jQuery(function(){
    	jQuery(".flip-horizontal1").flip({
  			trigger: 'hover'
		});
    });
    jQuery(function(){
    	jQuery(".flip-horizontal2").flip({
  			trigger: 'hover'
		});
    });
  	</script>
	<div class="container">
		<div class="row">
			<div class="page-header">
				<?php //echo get_field('content_editor'); ?>
			</div>

        </div> <!-- /.row -->
	</div> <!-- /.container -->
</section>



<section class="col3">
	<div class="container">
		<ul class="approch">
			<li class="large-text">Unique Approach</li>
			<li class="large-text">Original Services</li>
			<li class="large-text">No Starting Fees</li>
			<li class="large-text">1 + 1 = 2</li>
		</ul>
		<ul class="iconsec">
			<li>
				<img src="<?php echo get_template_directory_uri(). '/images/translation.png' ?>">
				<p class="large-text">Design</p>
			</li>
			<li>
				<img src="<?php echo get_template_directory_uri(). '/images/editing.png' ?>">
				<p class="large-text">Linguistic</p>
			</li>
			<li>
				<img src="<?php echo get_template_directory_uri(). '/images/writing.png' ?>">
				<p class="large-text">Development</p>
			</li>
		</ul>
	</div>
</section>

<section class="quality-services">
	<div class="container">
		<div class="quality-services-left">
			<span class="bold-and-blocking larger-text">Affordable Price for Quality Services<br><label>Unique Approach<label></span>
			<p class="large-text">
				We are not just a freelance service platform.<br>
				With expert freelancers all over the world, we offer careful and detailed freelance<br>
				services at cost 80% lower than the traditional services.
			</p>
		</div>
		<div class="quality-services-right">
			<img src="<?php echo get_template_directory_uri().'/images/quality-service.png' ?>">
		</div>
	</div>
</section>

<section class="ofthedeliver">
	<div class="container">
		<div class="ofthedeliver-left larger-text">
			<label>Exclusively</label> Original Freelance Work<br><label>top-quality</label>Guaranteed
		</div>
		<div class="ofthedeliver-right larger-text">
            <label>of the delivered</label>
		</div>
	</div>
</section>

<section class="midsec larger-text">
	<div class="midsec-wrap">
		<img src="<?php echo get_template_directory_uri().'/images/midsec.png' ?>">
		<div class="midsec-wrap-right">
			<h4>No Starting Fees or Minimum Fees</h4>
			<p class="large-text">There is no down or up limit.<br> We provide freelance services for anything and everything.</p>
		</div>
	</div>
</section>

<div class="serviced-professionals">
	<div class="container">
		<h3>Get Your Content Serviced by Professionals</h3>
		<ul>
			<li>
				<img src="<?php echo get_template_directory_uri() .'/images/max-value.png' ?>">
				<span class="bold-and-blocking large-text">Maximum Value</span>
				<p class="enhanced-text">Minimizes your cost.</p>
			</li>
			<li>
				<img src="<?php echo get_template_directory_uri() .'/images/cutting-edge-tech.png' ?>">
				<span class="bold-and-blocking large-text">Cutting-Edge Tech</span>
				<p class="enhanced-text">automates and simplifies <br>the service process.</p>
			</li>
			<li>
				<img src="<?php echo get_template_directory_uri().'/images/instant-qut.png' ?>">
				<span class="bold-and-blocking large-text">Instant Quote</span>
				<p class="enhanced-text">starts your services<br>RIGHT NOW!</p>
			</li>
		</ul>
	</div>
</div>

<!-- Our Connections -->

<section class="connection-sec new text-center">
	<div class="page-header">
		<div class="container">
			<div class="row">
				<span class="bold-and-blocking large-text"><?php get_custom_string('Our network of Native Freelancers'); ?></span>
			</div>
		</div>
	</div>
	<div class="connection-container">
		<div class="container">
			<div class="connection-panel">

                <div class="row border-btm">


                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-multilingual-websites/?lang=$lang" ?>" title="<?php __('Multilingual Websites') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-multilingual-websites.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-multilingual-websites/?lang=$lang" ?>">
                                    <?= __('Multilingual Websites') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('You can just ask customers')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-document-translation/?lang=$lang" ?>" title="<?php __('Document Translation') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-document-translation.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-document-translation/?lang=$lang" ?>">
                                    <?= __('Document Translation') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-personal-translation-services/?lang=$lang" ?>" title="<?php __('Multilingual Websites') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-personal-translation-services.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-personal-translation-services/?lang=$lang" ?>">
                                    <?= __('Voice Over and Voice Casting') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-video-translation-transcription-subtitling/?lang=$lang" ?>" title="<?php __('Video Translation, Transcription & Subtitling') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-video-translation-transcription-subtitling.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-video-translation-transcription-subtitling/?lang=$lang" ?>">
                                    <?= __('Video Translation, Transcription & Subtitling') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                </div><!-- /.row -->






                <div class="row border-btm">


                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-gaming-translation/?lang=$lang" ?>" title="<?php __('Gaming Translation') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-gaming-translation.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-gaming-translation/?lang=$lang" ?>">
                                    <?= __('Gaming Translation') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-technical-translation/?lang=$lang" ?>" title="<?php __('Audio Translation, Editing, and Production') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-technical-translation.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-technical-translation/?lang=$lang" ?>">
                                    <?= __('Audio Translation, Editing, and Production') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-documentation-writing/?lang=$lang" ?>" title="<?php __('Documentation Writing') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-documentation-writing.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-documentation-writing/?lang=$lang" ?>">
                                    <?= __('Documentation Writing') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-article-writing-for-blogs-newsletters/?lang=$lang" ?>" title="<?php __('Article Writing for Blogs, Newsletters,…') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-article-writing-for-blogs-newsletters.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-article-writing-for-blogs-newsletters/?lang=$lang" ?>">
                                    <?= __('Article Writing for Blogs, Newsletters,…') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                </div><!-- /.row -->





                <div class="row border-btm">


                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-resume-writing/?lang=$lang" ?>" title="<?php __('Resume Writing') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-resume-writing.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-resume-writing/?lang=$lang" ?>">
                                    <?= __('Resume Writing') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-editing-proofreading/?lang=$lang" ?>" title="<?php __('Editing / Proofreading') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-editing-proofreading.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-editing-proofreading/?lang=$lang" ?>">
                                    <?= __('Editing / Proofreading') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-press-release-and-speech-writing/?lang=$lang" ?>" title="<?php __('Press Releases and Speech Writing') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-press-release-and-speech-writing.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-press-release-and-speech-writing/?lang=$lang" ?>">
                                    <?= __('Press Releases and Speech Writing') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                    <div class="col-md-3 fl-page-panel">
                        <div class="panel-img">
                            <a href="<?= get_site_url()."/peerok-localization-and-internationalization/?lang=$lang" ?>" title="<?php __('Localization and Internationalization') ?>">
                                <img class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                                     src="<?=get_site_url()."/wp-content/themes/the-translator/images/page-thumbnails/peerok-localization-and-internationalization.png"?>">
                            </a>
                        </div>
                        <div class="panel-content">
                            <strong class="large-text">
                                <a href="<?= get_site_url()."/peerok-localization-and-internationalization/?lang=$lang" ?>">
                                    <?= __('Localization and Internationalization') ?>
                                </a>
                            </strong>
                            <p>
                                <span><?= __('')?></span>
                            </p>
                        </div>
                    </div> <!-- /.fl-page-panel -->

                </div><!-- /.row -->




			</div><!-- /.connection-panel -->
		</div><!-- /.container -->
	</div><!-- /.connection-container -->
</section>
<section class="satisfaction-guaranteed">
	<div class="container">
		<div class="satisfaction-guaranteed-left large-text">
			Our freelancers offer an iron clad guarantee for a full refund<br>
			if the service does not live up to your expectations
		</div>
		<div class="satisfaction-guaranteed-mid">
			<img src="<?php echo get_template_directory_uri().'/images/sad.png' ?>">
		</div>	
		<div class="satisfaction-guaranteed-right large-text">
			100% satisfaction guaranteed<br>
			otherwise, full refund
		</div>
	</div>
</section>
<?php get_footer('homepagenew'); ?>