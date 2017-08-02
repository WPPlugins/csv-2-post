<?php         
/*
Plugin Name: CSV 2 POST
Version: 8.3.0
Plugin URI: http://ryanbayne.wordpress.com
Description: CSV 2 POST data importer for WordPress by Ryan R. Bayne.
Author: Ryan Bayne
Author URI: http://ryanbayne.wordpress.com
Text Domain: csv2post
Domain Path: /languages

GPL v3 

This program is free software downloaded from WordPress.org: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. This means
it can be provided for the sole purpose of being developed further
and we do not promise it is ready for any one persons specific needs.
See the GNU General Public License for more details.

See <http://www.gnu.org/licenses/>.
*/           
  
// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

// exit early if CSV 2 POST doesn't have to be loaded
if ( ( 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) ) // Login screen
    || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
    || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
    return;
}
              
// define constants                              
if(!defined( "CSV2POST_VERSION") ){define( "CSV2POST_VERSION", '8.3.0' );}
if(!defined( "CSV2POST_WPVERSIONMINIMUM") ){define( "CSV2POST_WPVERSIONMINIMUM", '4.4.0' );}// The minimum php version that will allow the plugin to work
if(!defined( "CSV2POST_PHPVERSIONMINIMUM") ){define( "CSV2POST_PHPVERSIONMINIMUM", '5.4.0' );}// The minimum php version that will allow the plugin to work 
if(!defined( "CSV2POST_NAME") ){define( "CSV2POST_NAME", trim( dirname( plugin_basename( __FILE__ ) ), '/') );} 
if(!defined( "CSV2POST__FILE__") ){define( "CSV2POST__FILE__", __FILE__);}
if(!defined( "CSV2POST_DIR_PATH") ){define( "CSV2POST_DIR_PATH", plugin_dir_path( __FILE__) );}
if(!defined( "CSV2POST_BASENAME") ){define( "CSV2POST_BASENAME",plugin_basename( CSV2POST__FILE__ ) );}
if(!defined( "CSV2POST_ABSPATH") ){define( "CSV2POST_ABSPATH", plugin_dir_path( __FILE__) );}//C:\AppServ\www\wordpress-testing\wtgplugintemplate\wp-content\plugins\wtgplugintemplate/                                                                 
if(!defined( "CSV2POST_IMAGES_URL") ){define( "CSV2POST_IMAGES_URL",plugins_url( 'images/' , __FILE__ ) );}

// Functions required on loading (will be autoloaded eventually)
require_once( CSV2POST_DIR_PATH . 'functions/functions.debug.php');

// require very common classes and finally the main class for loading the plugin 
require_once( CSV2POST_ABSPATH . 'classes/class-wpdb.php' );
require_once( CSV2POST_ABSPATH . 'classes/class-options.php');
require_once( CSV2POST_ABSPATH . 'classes/class-install.php');                                                  
require_once( CSV2POST_ABSPATH . 'classes/class-configuration.php' );
require_once( CSV2POST_ABSPATH . 'classes/class-csv2post.php' );
require_once( CSV2POST_ABSPATH . 'classes/class-schedule.php' );
require_once( CSV2POST_ABSPATH . 'classes/class-automation.php' );

add_action( 'plugins_loaded', array( 'CSV2POST', 'init' ));

// localization
function csv2post_textdomain() {
    load_plugin_textdomain( 'csv2post', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}
add_action( 'plugins_loaded', 'csv2post_textdomain' );  

// Install the plugin on activation only.
$install = new CSV2POST_Install();
register_activation_hook( __FILE__, array( $install, 'install_plugin' ) ); 
register_deactivation_hook( __FILE__, array( $install, 'deactivate_plugin' ) );                                                                                                    
?>