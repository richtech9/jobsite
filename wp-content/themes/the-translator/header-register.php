<?php
/*
* current-php-code 2020-Oct-16
* input-sanitized : lang,spage, text
* current-wp-template:  header
*/
?>
<!doctype html>
<?php FreelinguistDebugFramework::note('register header'); ?>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<title><?php wp_title(''); ?><?php if(wp_title('', false)) { echo ' :'; } ?> <?php bloginfo('name'); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php bloginfo('description'); ?>">
	

    <!--suppress HtmlUnknownTarget -->
    <link rel="shortcut icon" href="<?php bloginfo('template_url'); ?>/images/fav-icon.ico?" type="image/x-icon"/>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php set_cookie_for_language(); ?>