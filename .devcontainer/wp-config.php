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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

if (file_exists(__DIR__ . '/wp-config-local.php')) {
    require __DIR__ . '/wp-config-local.php';
}

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
if (!defined('DB_NAME')) {
    define('DB_NAME', 'dev');
}

/** Database username */
if (!defined('DB_USER')) {
    define('DB_USER', 'dev');
}

/** Database password */
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', 'dev');
}

/** Database hostname */
if (!defined('DB_HOST')) {
    define('DB_HOST', 'db');
}

/** Database charset to use in creating database tables. */
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

/** The database collate type. Don't change this if in doubt. */
if (!defined('DB_COLLATE')) {
    define('DB_COLLATE', '');
}

if (!defined('WP_SITEURL') && isset($_SERVER['HTTP_HOST'])) {
    define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST']);

    if (!defined('WP_HOME')) {
        define('WP_HOME', WP_SITEURL);
    }

    if (!defined('WP_CONTENT_URL')) {
        define('WP_CONTENT_URL', WP_SITEURL . '/wp-content');
    }
}

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
define('AUTH_KEY', '(J.hL1=7inY7O%xo.1K^b1*=NQ!AvkBfq>HHW}D6T#6ktdvZ4qMpm7OW+vR:pHxm');
define('SECURE_AUTH_KEY', 'pPwN{M1g=?j`me{7rE-7OtJN$zDS(y#uM7.1~.X~>m+#/ln~%Myo$Ca2mBFEa0s:');
define('LOGGED_IN_KEY', '8-AQ.XlAXQTq8:f2ddzxIs]xeC cE`ZdvAv9S=*bg>I~bHT/jGLyXW4=)!rhjz+g');
define('NONCE_KEY', '9{1MgwMd|^-i3OIy(`4jmEk5T/%1Q_i^bB&h%Jc7: :m]Ic7e&fkc=8YV_2|A~Fs');
define('AUTH_SALT', 'P(}]`j]rI]Rzd<NTP`1A|aNg`)I&5cNjxZ3cb,C]PDRFeOgys@?}lV)8omoqq|UZ');
define('SECURE_AUTH_SALT', 'r./W6cc]R;#&[Isz7$`<G@4UM0qIe|mLwu;kZzG:o@[T^ML&QyN!K!Lb88X3n{<h');
define('LOGGED_IN_SALT', 'R:O%d{!<Ua/sf9zc6/^G;I*!+,&<UFgJ&hD r6GZ]x@Q[j#;&;X&&JQdJG~eqN1[');
define('NONCE_SALT', '^G^t@e9n!ys-{?yz V9&U2zCsyEVI_P3C4>S*{U]Y#(n~NzR|*Xx)6{0*/kvY^pc');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
if (!isset($table_prefix)) {
    $table_prefix = 'wp_';
}

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
