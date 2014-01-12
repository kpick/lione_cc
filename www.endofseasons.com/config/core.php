<?php
/* SVN FILE: $Id$ */
/**
 * This is core configuration file.
 *
 * Use it to configure core behavior of Cake.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.2.9
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
 
 
/**
 * Application wide charset encoding
 */

    Configure::write('App.encoding', 'UTF-8');
/**
 * Session stuff
 */

    Configure::write('Session.cookie', 'lr');
	Configure::write('Session.timeout', '120'); // actual value depends on Security.level setting - x10,x100,x300
	Configure::write('Session.start', true);
	Configure::write('Session.checkAgent', true);
	//Configure::write('Security.salt', 'jucruph5thehe4ujujetrar7spAsPAtrep4uPReprab36egacastaf9ehuy2drad');

//To use database sessions, execute the SQL file found at /app/config/sql/sessions.sql.
// database, cake, or php
Configure::write('Session.save', 'database');
// if database, the name of the session table
Configure::write( 'Session.table', 'sessions' );
//The DATABASE_CONFIG::$var to use for database session handling.
Configure::write('Session.database', 'default');

 
 
if( file_exists( dirname(__FILE__) . '/.production' ) ) {
    Configure::write( 'environment', 'production' );
    Configure::write( 'debug', 0 );
    Configure::write('Cache.disable', false);
    Configure::write( 'Chache.check', true );
    define('LOG_ERROR', 0);
    Configure::write('Security.level', 'high'); // timeout x10,100,300
    Configure::write( 'Email.file', false );
    Configure::write( 'Info.url', 'http://www.endofseasons.com' );
    Configure::write( 'Paypal.live', true );
} elseif( file_exists( dirname(__FILE__) . '/.labs' ) ) {
    Configure::write( 'environment', 'labs' );
    Configure::write( 'debug', 0 );  
    Configure::write('Cache.disable', true);
    Configure::write( 'Cache.check', false );
    define('LOG_ERROR', 1);
    Configure::write('Security.level', 'low');  // timeout x10,100,300
    Configure::write( 'Email.file', false );
    Configure::write( 'Info.url', 'http://labs.endofseasons.com' );
    Configure::write( 'Paypal.live', false );      
} else {
    Configure::write( 'environment', 'development' );
    Configure::write( 'debug', 2 );  
    Configure::write('Cache.disable', true);
    Configure::write( 'Cache.check', false );
    define('LOG_ERROR', 1);
    Configure::write('Security.level', 'low');  // timeout x10,100,300
    Configure::write( 'Email.file', "/Users/stefan/Sites/eos.local/tmp" );
    Configure::write( 'Info.url', 'http://eos.local' );
    Configure::write( 'Paypal.live', false );
}  


Configure::write('Routing.admin', 'admin');
Configure::write('Acl.classname', 'DbAcl');
Configure::write('Acl.database', 'default');

/** caching, compressing, etc. **/
Cache::config('default', array('engine' => 'File')); // see notes below for more caching info
//Configure::write('Asset.filter.css', 'css.php');
//Configure::write('Asset.filter.js', 'custom_javascript_output_filter.php');

Configure::write('Routing.admin', 'admin');



/**
 * CakePHP Debug Level:
 *
 * Production Mode:
 * 	0: No error messages, errors, or warnings shown. Flash messages redirect.
 *
 * Development Mode:
 * 	1: Errors and warnings shown, model caches refreshed, flash messages halted.
 * 	2: As in 1, but also with full debug messages and SQL output.
 * 	3: As in 2, but also with full controller dump.
 *
 * In production mode, flash messages redirect after a time interval.
 * In development mode, you need to click the flash message to continue.
 */

/**
 * To configure CakePHP *not* to use mod_rewrite and to
 * use CakePHP pretty URLs, remove these .htaccess
 * files:
 *
 * /.htaccess
 * /app/.htaccess
 * /app/webroot/.htaccess
 *
 * And uncomment the App.baseUrl below:
 */
	//Configure::write('App.baseUrl', env('SCRIPT_NAME'));

	//


/**
 * Defines the default error type when using the log() function. Used for
 * differentiating error logging and debugging. Currently PHP supports LOG_DEBUG.
 */
	





/**
 * Compress CSS output by removing comments, whitespace, repeating tags, etc.
 * This requires a/var/cache directory to be writable by the web server for caching.
 * and /vendors/csspp/csspp.php
 *
 * To use, prefix the CSS link URL with '/ccss/' instead of '/css/' or use HtmlHelper::css().
 */
	
/**
 * Plug in your own custom JavaScript compressor by dropping a script in your webroot to handle the
 * output, and setting the config below to the name of the script.
 *
 * To use, prefix your JavaScript link URLs with '/cjs/' instead of '/js/' or use JavaScriptHelper::link().
 */
	
/**
 * The classname and database used in CakePHP's
 * access control lists.
 */

/**
 * If you are on PHP 5.3 uncomment this line and correct your server timezone
 * to fix the date & time related errors.
 */
	//date_default_timezone_set('UTC');
/**
 *
 * Cache Engine Configuration
 * Default settings provided below
 *
 * File storage engine.
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'File', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 * 		'path' => CACHE, //[optional] use system tmp directory - remember to use absolute path
 * 		'prefix' => 'cake_', //[optional]  prefix every cache file with this string
 * 		'lock' => false, //[optional]  use file locking
 * 		'serialize' => true, [optional]
 *	));
 *
 *
 * APC (http://pecl.php.net/package/APC)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Apc', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 *	));
 *
 * Xcache (http://xcache.lighttpd.net/)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Xcache', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional] prefix every cache file with this string
 *		'user' => 'user', //user from xcache.admin.user settings
 *      'password' => 'password', //plaintext password (xcache.admin.pass)
 *	));
 *
 *
 * Memcache (http://www.danga.com/memcached/)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Memcache', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 * 		'servers' => array(
 * 			'127.0.0.1:11211' // localhost, default port 11211
 * 		), //[optional]
 * 		'compress' => false, // [optional] compress data in Memcache (slower, but uses less memory)
 *	));
 *
 */
	
?>