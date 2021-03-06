<?php
/**
 * Deal Manager Plugin
 *
 * This is a wordpress plugin intended to make mortgage brokers life better.
 *
 * @package   MH_Deal_Manager
 * @author    Michael Hume <m.p.hume@gmail.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Michael Hume
 *
 * @wordpress-plugin
 * Plugin Name:       Deal Manager
 * Plugin URI:        N/A
 * Description:       A deal management plugin to help manage documentation and deal progression.
 * Version:           1.0.0
 * Author:            Michael Hume
 * Author URI:        vivahume.com
 * Text Domain:       mh-deal-manager-en
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/michaelhume/deal-manager
 * GitHub Branch:     master
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Constants and Definitions

if ( ! defined( 'MHDM_PLUGIN_BASENAME' ) )
	define( 'MHDM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	
if ( ! defined( 'MHDM_PLUGIN_DIR' ) )
	define( 'MHDM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'MHDM_PLUGIN_URL' ) )
	define( 'MHDM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
	
// get rid of the tempting file editor!
if ( !defined ('DISALLOW_FILE_EDIT') ) 
 	define( 'DISALLOW_FILE_EDIT', true ); 
 
 // pull in the meta-box functionality
if ( !defined ( 'RWMB_URL' ) )
    define( 'RWMB_URL', MHDM_PLUGIN_URL .  '/admin/includes/plugin/meta-box/' );

if ( !defined ( 'RWMB_DIR' ) )
    define( 'RWMB_DIR', MHDM_PLUGIN_DIR . '/admin/includes/plugin/meta-box/' );

	
/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/


require_once( plugin_dir_path( __FILE__ ) . 'public/class-mh-deal-manager.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'MH_Deal_Manager', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MH_Deal_Manager', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'MH_Deal_Manager', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-mh-deal-manager-admin.php' );
	add_action( 'plugins_loaded', array( 'MH_Deal_Manager_Admin', 'get_instance' ) );

}
