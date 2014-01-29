<?php
/**
 * Plugin Name.
 *
 * @package   MH_Deal_Manager_Admin
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 *
 * @package MH_Deal_Manager_Admin
 * @author  Michael Hume <m.p.hume@gmail.com>
 */
if ( ! class_exists( 'MH_Deal_Manager_Admin' ) ) {
	class MH_Deal_Manager_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = MH_Deal_Manager::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		
		// pull in the meta-box functionality
        if ( !defined ( 'RWMB_URL' ) ){
            define( 'RWMB_URL', MHDM_PLUGIN_URL .  '/admin/includes/plugin/meta-box/' );
        }
        
        if ( !defined ( 'RWMB_DIR' ) ){    
            define( 'RWMB_DIR', MHDM_PLUGIN_DIR . '/admin/includes/plugin/meta-box/' );
        }
		
		// load custom meta boxes
		add_action( 'after_setup_theme', array( $this, 'setup_meta_boxes' ) );				

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	
	/**
	 * setup_meta_boxes function.
	 *
	 *	Setup the custom meta boxes for our custom post types
	 * 
	 * @access public
	 * @return void
	 */
	public function setup_meta_boxes(){
		require_if_theme_supports( $this->plugin_slug . '-custom-meta', MHDM_PLUGIN_DIR . '/admin/includes/plugin/meta-box/meta-box.php' );
		require_if_theme_supports( $this->plugin_slug . '-custom-meta', MHDM_PLUGIN_DIR . '/admin/includes/core/meta-boxes.php' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), MH_Deal_Manager::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), MH_Deal_Manager::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
				 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		/*
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Deal Manager Settings', $this->plugin_slug ),
			__( 'Deal Manager', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
		*/
		// create a new top level menu for all options
        $this->plugin_screen_hook_suffix[] = 
                add_menu_page( 
                            __('Deal Manager Configuration', $this->plugin_slug ),      // Page Title
                            __('Deal Manager', $this->plugin_slug),                     // Menu Title
                            'administrator',                                            // capability
                            $this->plugin_slug . '-main',                               // page slug
                            array( $this, 'display_main_admin_page'),            // callback function for display
                            'dashicons-lightbulb' ,               						// icon url
                            '99.1'                               	                    // position
                            );
                            
        // add submenu to dashboard
        $this->plugin_screen_hook_suffix[] = 
                add_submenu_page( 
                            $this->plugin_slug . '-main',                       // top level slug
                            'Shortcodes',                                       // Page title  
                            'Shortcodes',                                       // menu title
                            'manage_options',                                  // capability
                            $this->plugin_slug . '-shortcodes',                 // page slug
                            array( $this, 'display_shortcodes_admin_page' )             // callback to handle display
                            );


	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_main_admin_page() {
		include_once( 'views/main.php' );
	}
	
	/**
	 * Render the shortcodes settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_shortcodes_admin_page() {
		include_once( 'views/shortcodes.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
}
