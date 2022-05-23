<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wp_user' );

/** Database password */
define( 'DB_PASSWORD', 'wp_pass' );

/** Database hostname */
define( 'DB_HOST', 'db' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'sl}Igbzc6o&(%TR8H,.K3|0Kr-KmPJ_V]D14*LC@&pJorM[$,LXIx0[pY5hyC`R-' );
define( 'SECURE_AUTH_KEY',  'ye?H2$H*B7z8WNU n7WzVOF)BRU$kR|e6|sptf?jIc8RAWw[T290X +u%vp.tfy0' );
define( 'LOGGED_IN_KEY',    'VYT+esv=SsxL,oYZH6N{=h.Hb?k><dDy_dDF]czeT#GMD{eXY D8VN+O30fmc6]v' );
define( 'NONCE_KEY',        'XxtM~^?J[5-FYNh:&ev%]G.CE: qg>Bt{mWD9*UGf[+TC[,+%#hE(2+R}N/G?$%?' );
define( 'AUTH_SALT',        'J^)_rJp19RPRByZT<>IAOw)7}*~%)NW5+.iv_/m:GCpQ:+nZNvcbLbDvT DBq$d;' );
define( 'SECURE_AUTH_SALT', '`@~JZS~bY/K=%=5 ab NQk2CP<P0 b2wi6kXTnJKMa)}jT2]t%0xUt_&i-IASOg ' );
define( 'LOGGED_IN_SALT',   '|vl4Ic@h999]lc/Tv:5y1}v)pL0Ix|S(^KSWjY&aiAG}F}Nina3%2JO9Q}$+/Vdp' );
define( 'NONCE_SALT',       'G1|}<@=oYNc]|bpAnu/ue5!^_6/7OJ@1jKD=/UAjx_p:vXG2e-tq[KH7=IsADtc.' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST']);
define('WP_HOME', 'http://' . $_SERVER['HTTP_HOST']);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
