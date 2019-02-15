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
define('DB_USER', 'drsanjaydoshi');

/** MySQL database password */
define('DB_PASSWORD', 'Capstone2019');

/** MySQL hostname */
define('DB_HOST', 'p2p4health-cluster.cluster-citxpawxscki.us-east-1.rds.amazonaws.com');

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
define('AUTH_KEY',         'n}:Q)e}eDG9_ORPmE<dknHy~ZKV++l^ 90yXRCp#(T;3QC65(PK :uf$W1ymO]TL');
define('SECURE_AUTH_KEY',  '1-E0kVzcK-P&fOM>[8R(F]^g1G^s*BhQy=*h&>&:c(?T<!rrXx8SO#9U=N@9 16 ');
define('LOGGED_IN_KEY',    'E|W4ly5#5Pjdb|atEe=`<C/u<R(@1>[$~;B@9 ]C`& zuj2o[ebK78f*#HKID:PV');
define('NONCE_KEY',        '+4{It(]K?[bx/#@D|k/rI0bD3fS^Z7!A(nm5{L*T5S(6L>KV8Ehu#Pg IXWj(2)p');
define('AUTH_SALT',        'LK#kalQX;e0V|8+Akx`Lv!)&HyJ~XP R_W`3YYPd,*DEBq%,Dslq!za`v?n>DuZ-');
define('SECURE_AUTH_SALT', '[myZe rFa$=ekP0&h{G=}g?0mLfHFY+neD>%@.IS b]#B-8j<wJu`-%yEff~J?#d');
define('LOGGED_IN_SALT',   'wvGB]X^RR0VhS?d9r^c0PA6_ [y)%>v6?_NTva%kphO (I%Tf/8(MwuFc8nBxT@9');
define('NONCE_SALT',       'T#quKd:Ss{<iu(g{:Kr|;^/uyYp;jkmUI0|vf_8QkO.@ZH12Vf9b9*qd/jfk=7P|');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'website_';

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
