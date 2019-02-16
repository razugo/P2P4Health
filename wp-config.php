<?php
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
define('DB_NAME', 'website');

/** MySQL database username */
define('DB_USER', 'drdoshi');

/** MySQL database password */
define('DB_PASSWORD', 'Capstone2019');

/** MySQL hostname */
define('DB_HOST', 'p2p4health.citxpawxscki.us-east-1.rds.amazonaws.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'bvt2^|z yx:^Zy! ro.l<p+r>@a:9*$&Xb*k:FR8(L.`HvgE2l+x 1F]WNkB5=Wj');
define('SECURE_AUTH_KEY',  '`mz<!8,2k&^A>7r5WG3OBSYf}pvO0Gn^z,kW/IMnO?S|lw6Dv%.f lx)+xcI7^w+');
define('LOGGED_IN_KEY',    '$ k<ZXQv&EWObbz}0?0/ce1(}|<cSpPqs>&boF9=9$rC&eU*OT[,3Y0E^6PW~nl#');
define('NONCE_KEY',        'D ]w~Q3n0*)U&AP@Qk0odXYLw=/e9P/DXLNW(-c+.PLx@3rJ+KRnU+p1j-Uq_{?C');
define('AUTH_SALT',        '..vf.B`1 ~r(KIqy/R@OjE!mYvy>#=2E:Qz~#/bauv%X~EAYP72eeq389KxJ!nBa');
define('SECURE_AUTH_SALT', 'wPL-qyf9=#54u{@J|4^2|@?0?565v/BLRf-: IV6/</oEn@tJ|MR@tq~S_ES3hFS');
define('LOGGED_IN_SALT',   '02mAj*q|@22elB_Cf[+)nIsW#|66{AZ<2s;b7*s>X^|j)Ja|  phZ8A^lta0oB/a');
define('NONCE_SALT',       '@xQ %Bn>t@Me4G#>t!G&YEkW(FGaPc]/17?y%10,/Q4&cD;d;.750@w.O<HXl061');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
