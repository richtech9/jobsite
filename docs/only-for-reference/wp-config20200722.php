<?php
global $will_log_with_dat_flag; //code-notes with the will-debug functions for tracing
$will_log_with_dat_flag = false;
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'guowangm_wrdp5' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'KOSUnN]r1X?{*d =lBtc-T02-qc]1SgVG^{ UxJ_<L8/t]q|u%uQ37jfJf3=4`2@' );
define( 'SECURE_AUTH_KEY',  'uS]x{FaUdhSB$(Z&j{!X?;>sXqz!U#ywn8j$73@z!yS(:fh<P08-J*wBqTO_GQoV' );
define( 'LOGGED_IN_KEY',    'd]N{b.z`rdTz7atbwb53:`!xA4q4+q94$A+=rgHGel4nd$neK,@Gt|L346R_{4Hh' );
define( 'NONCE_KEY',        'fHSV!3+P%(qLWYlK`M)4A1kTtJ>g(79D XpsDGkJ)bVwhQIN|[^x|f.rjL/y:.{a' );
define( 'AUTH_SALT',        'C.!u~[&%kq8eVQgNBK4z(W{N@EG.MbFiTYR_m&4eE/xF7qLV<KUmKK4r/HFpg^_j' );
define( 'SECURE_AUTH_SALT', 'rRTxUGXE5TBfgh4}JL`(SNSb=jB+i.ld< I!~4p58o!c6Zj#,_sD;|U&IiSV~Is<' );
define( 'LOGGED_IN_SALT',   'ZKmy7wz^wGHXAXCb,xkfp>v8lupr_.M_mb*ReT87*EbQ@BJ|`Kc(,xVuh(eZ`daX' );
define( 'NONCE_SALT',       'ID]Or{$#Mu{OSFqgQ^wqW{tR/.sl5>24M@ry@9AJD*s1UU~xM,WtyW63L=^W`<3u' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
define('WP_ALLOW_REPAIR', true);
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
define('WP_DEBUG_LOG', true );
define( 'SAVEQUERIES', true );

// this allows direct update/installation of plugin if it's enabled. comment it if you don't want to allow
// direct update by WP.
define('FS_METHOD', 'direct');
// enable the following altenatve WP Cron if there is issue with email in background, and action scheduler
// WP Cron is not real cron. It is called when user visits some pages .
//define('ALTERNATE_WP_CRON', true); // it's only needed if there is password protection to website.

//Add cron job to system, and disable WP cron. This will run really in background. .
//define('DISABLE_WP_CRON', true);
// crontab -e
//*/10 * * * *  curl https://development:development@development.daiyan8.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	//define( 'ABSPATH', dirname( __FILE__ ) . '/' ); //todo uncomment
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
