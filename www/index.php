<?php
/* SVN FILE: $Id$ */
/**
 * Short description for file.
 *
 * Long description for file
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
 * @subpackage    cake.app.webroot
 * @since         CakePHP(tm) v 0.2.9
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Use the DS to separate the directories in other defines
 */
	if (!defined('DS')) {
		define('DS', DIRECTORY_SEPARATOR);
	}


    if( file_exists( dirname(__FILE__) . '/.production' ) ) { 
        //The full path to the directory which holds "app", WITHOUT a trailing DS.
        if (!defined('ROOT')) {
            define( 'ROOT', DS.'Library'.DS.'WebServer' );
        }
    
        // The actual directory name for the "app".
        if (!defined('APP_DIR')) {
            define ('APP_DIR', 'www.endofseasons.com');
        }
    
        //The absolute path to the "cake" directory, WITHOUT a trailing DS.
        if (!defined('CAKE_CORE_INCLUDE_PATH')) {
            define('CAKE_CORE_INCLUDE_PATH', DS.'home'.DS.'gadhra'.DS.'cake_1.2.5' );
        }
    } elseif( file_exists( dirname(__FILE__) . '/.labs' ) ) { 
         //The full path to the directory which holds "app", WITHOUT a trailing DS.
        if (!defined('ROOT')) {
            define( 'ROOT', DS.'Library'.DS.'WebServer' );
        }
    
        // The actual directory name for the "app".
        if (!defined('APP_DIR')) {
            define ('APP_DIR', 'www.endofseasons.com');
        }
    
        //The absolute path to the "cake" directory, WITHOUT a trailing DS.
        if (!defined('CAKE_CORE_INCLUDE_PATH')) {
            define('CAKE_CORE_INCLUDE_PATH', DS.'Library'.DS.'WebServer'.DS.'cake_core' );
        }       
        
    } else {
        if (!defined('ROOT')) {
            define( 'ROOT', DS.'Library'.DS.'WebServer' );
        }
    
        if (!defined('APP_DIR')) {
            define ('APP_DIR', 'www.endofseasons.com');
        }
    
        if (!defined('CAKE_CORE_INCLUDE_PATH')) {
            define('CAKE_CORE_INCLUDE_PATH', DS.'Library'.DS.'WebServer'.DS.'cake_core');
        }
    }        
        
/**
 * Editing below this line should NOT be necessary.
 * Change at your own risk.
 *
 */
	if (!defined('WEBROOT_DIR')) {
		define('WEBROOT_DIR', basename(dirname(__FILE__)));
	}
	if (!defined('WWW_ROOT')) {
		define('WWW_ROOT', dirname(__FILE__) . DS);
	}
	if (!defined('CORE_PATH')) {
		if (function_exists('ini_set') && ini_set('include_path', CAKE_CORE_INCLUDE_PATH . PATH_SEPARATOR . ROOT . DS . APP_DIR . DS . PATH_SEPARATOR . ini_get('include_path'))) {
			define('APP_PATH', null);
			define('CORE_PATH', null);
		} else {
			define('APP_PATH', ROOT . DS . APP_DIR . DS);
			define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);

		}
	}


	if (!include(CORE_PATH . 'cake' . DS . 'bootstrap.php')) {
		trigger_error("CakePHP core could not be found.  Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/index.php.  It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
	}
	if (isset($_GET['url']) && $_GET['url'] === 'favicon.ico') {
		return;
	} else {
		$Dispatcher = new Dispatcher();
		$Dispatcher->dispatch($url);
	}
	if (Configure::read() > 0) {
		echo "<!-- " . round(getMicrotime() - $TIME_START, 4) . "s -->";
	}
?>