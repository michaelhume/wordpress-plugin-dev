<?php
/**
 * Deal Manager.
 *
 * @package   MH_Deal_Manager
 * @author    Michael Hume <m.p.hume@gmail.com>
 * @license   GPL-2.0+
 * @link      vivahume.com
 * @copyright 2014 Michael Hume
 */

/**
 * 
 *	Actions:    http://codex.wordpress.org/Plugin_API#Actions
 *	Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
 *	Filters: http://codex.wordpress.org/Plugin_API#Filters
 *  Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
 *
 *
 * @package MH_Deal_Manager
 * @author  Michael Hume <m.p.hume@gmail.com>
 */
if ( ! class_exists( 'MH_Deal_Manager' ) ) {
	class MH_Deal_Manager {

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		const VERSION = '1.0.0';
	
		/**
		 *
		 * Unique identifier for your plugin.
		 *
		 * The variable name is used as the text domain when internationalizing strings
		 * of text. Its value should match the Text Domain file header in the main
		 * plugin file.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $plugin_slug = 'mh-deal-manager';
		
		
		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;
	
		/**
		 * Initialize the plugin by setting localization and loading public scripts
		 * and styles.
		 *
		 * @since     1.0.0
		 */
		private function __construct() {
	
			// Load plugin text domain
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	
			// Activate plugin when new blog is added
			add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
	
			// Load public-facing style sheet and JavaScript.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			
	
			// setup the plugin
			//add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );
			add_action( 'after_setup_theme', array( $this, 'load_custom_post_types' ) );

			// add filters for custom post types
			add_filter( 'single_template', array( $this, 'get_single_cpt_template') );
			add_filter( 'archive_template', array( $this, 'get_archive_cpt_template') );
			
			// play with the post titles
			add_filter( 'wp_insert_post_data' , array( $this, 'set_cpt_post_title') , '99', 2 );
			
			// add shortcodes
			//add_shortcode( 'short_code_here', array( $this, 'shortcode_handler' ) );
	
		}

		/**
		 * Return the plugin slug.
		 *
		 * @since    1.0.0
		 *
		 * @return    Plugin slug variable.
		 */
		public function get_plugin_slug() {
			return $this->plugin_slug;
		}
	
		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {
	
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
	
			return self::$instance;
		}
				
		/**
		 * add_theme_support function.
		 * 
		 * @access public
		 * @return void
	 	 * @since    1.0.0
		 */
		public function add_theme_support(){
			
			add_theme_support(
	     		$this->plugin_slug .'-post-types',
	    		array(  'deal', 
	    		        'requirement', 
	    		        'associate', 
	    		        'property',
	    		       )
	    	);
	    	
	    	add_theme_support(
	     		$this->plugin_slug .'-extended-user'
	    	);
	    	
	    	add_theme_support(
	    	    $this->plugin_slug .'-custom-meta'
	    	);
		}
		
		/**
		 * load_custom_post_types function.
		 * 
		 * @access public
		 * @return void
		 * @since    1.0.0
		 */
		public function load_custom_post_types(){
			require_once( MHDM_PLUGIN_DIR . '/includes/core/post-types.php' );	
		}
		
		
		/**
		 * Function to handle [all-shortcodes]
		 *
		 *  Passes shortcode off to our shortcode class
		 *
		 *  @since    1.00.00
		 *  @return     null
		 */
		
		public function shortcode_handler( $atts, $content, $tag ){
	    	require_once( MHDM_PLUGIN_DIR . '/includes/core/shortcodes.php' );
	    	return MHDM_Shortcodes::shortcode( $atts, $content, $tag );
	    	
		}
		
		/**
		 * Fired when the plugin is activated.
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses
		 *                                       "Network Activate" action, false if
		 *                                       WPMU is disabled or plugin is
		 *                                       activated on an individual blog.
		 */
		public static function activate( $network_wide ) {
	
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	
				if ( $network_wide  ) {
	
					// Get all blog ids
					$blog_ids = self::get_blog_ids();
	
					foreach ( $blog_ids as $blog_id ) {
	
						switch_to_blog( $blog_id );
						self::single_activate();
					}
	
					restore_current_blog();
	
				} else {
					self::single_activate();
				}
	
			} else {
				self::single_activate();
			}
	
		}
	
		/**
		 * Fired when the plugin is deactivated1
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses
		 *                                       "Network Deactivate" action, false if
		 *                                       WPMU is disabled or plugin is
		 *                                       deactivated on an individual blog.
		 *
		 *	@TODO remove costom caps during deactivation (setup in post-types)
		 */
		public static function deactivate( $network_wide ) {
	
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	
				if ( $network_wide ) {
	
					// Get all blog ids
					$blog_ids = self::get_blog_ids();
	
					foreach ( $blog_ids as $blog_id ) {
	
						switch_to_blog( $blog_id );
						self::single_deactivate();
	
					}
	
					restore_current_blog();
	
				} else {
					self::single_deactivate();
				}
	
			} else {
				self::single_deactivate();
			}
	
		}
	
		/**
		 * Fired when a new site is activated with a WPMU environment.
		 *
		 * @since    1.0.0
		 *
		 * @param    int    $blog_id    ID of the new blog.
		 */
		public function activate_new_site( $blog_id ) {
	
			if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
				return;
			}
	
			switch_to_blog( $blog_id );
			self::single_activate();
			restore_current_blog();
	
		}
	
		/**
		 * Get all blog ids of blogs in the current network that are:
		 * - not archived
		 * - not spam
		 * - not deleted
		 *
		 * @since    1.0.0
		 *
		 * @return   array|false    The blog ids, false if no matches.
		 */
		private static function get_blog_ids() {
	
			global $wpdb;
	
			// get an array of blog ids
			$sql = "SELECT blog_id FROM $wpdb->blogs
				WHERE archived = '0' AND spam = '0'
				AND deleted = '0'";
	
			return $wpdb->get_col( $sql );
	
		}
	
		/**
		 * Fired for each blog when the plugin is activated.
		 *
		 * @since    1.0.0
		 */
		private static function single_activate() {
			
			// create groups @see www.itthinx.com/documentation/groups/
			if ( class_exists ( 'Groups_Group' ) ){
	
				$clients_id = Groups_Group::create( array( 'name' => 'Clients',	'description' 	=> 'Default Group for Deal Manager Clients' ) );
				$brokers_id = Groups_Group::create( array( 'name' => 'Brokers', 'description'	=> 'Default Group for Deal Manager Brokers' ) );
				
				$client_cap_id = Groups_Capability::create( array( 'capability' => 'client' ) );
				$broker_cap_id = Groups_Capability::create( array( 'capability' => 'broker' ) );
						
				$client_group_cap = Groups_Group_Capability::create( array( 'group_id' => $clients_id, 'capability_id' => $client_cap_id ) );		
				$broker_group_cap = Groups_Group_Capability::create( array( 'group_id' => $brokers_id, 'capability_id' => $broker_cap_id ) );		
				
				// need to get the plugin slug
				$class = self::get_instance();
				$slug = $class->get_plugin_slug();
				$class = null;
				
				// store the ID's in options for deactivation
				update_option( $slug . '-' .'client-group-id', $clients_id );
				update_option( $slug . '-' .'broker-group-id', $brokers_id );
				update_option( $slug . '-' .'client-group-cap', $client_cap_id );
				update_option( $slug . '-' .'broker-group-cap', $broker_cap_id );
			}
			
			// register post types as flush rewrite rules
			require_once( MHDM_PLUGIN_DIR . '/includes/core/post-types.php' );	
			MH_Deal_Manager_Post_Types::register_post_types_now();
			flush_rewrite_rules();	
		}
	
		/**
		 * Fired for each blog when the plugin is deactivated.
		 *
		 * @since    1.0.0
		 */
		private static function single_deactivate() {
			
			if ( class_exists ( 'Groups_Group' ) ){
				// need to get the plugin slug
				$class = self::get_instance();
				$slug = $class->get_plugin_slug();
				$class = null;
	
				// remove the caps
				$id = get_option( $slug . '-' .'client-group-cap', false );
				if ( $id ){
					Groups_Capability::delete( $id );	
				}
	
				$id = get_option( $slug . '-' .'broker-group-cap', false );
				if ( $id ){
					Groups_Capability::delete( $id );	
				}
				
				// remove the groups
				$id = get_option( $slug . '-' .'client-group-id', false );
				if ( $id ){
					Groups_Group::delete( $id );	
				}
				
				$id = get_option( $slug . '-' .'broker-group-id', false );
				if ( $id ){
					Groups_Group::delete( $id );	
				}
	
			}
			
			flush_rewrite_rules();
		}
	
		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {
	
			$domain = $this->plugin_slug;
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	
			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	
		}
	
		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {
			wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
		}
	
		/**
		 * Register and enqueues public-facing JavaScript files.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {
			wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		}
	
	
		/**
		 * get_single_cpt_tempalte function.
		 *
		 * 
		 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/single_template
		 * @since 1.0.0
		 * @access public
		 * @param mixed $template
		 * @return void
		 */
		public function get_single_cpt_template( $template ) {
		    global $post;
			
			$obj = MH_Deal_Manager_Post_Types::get_instance();
			$types = $obj->get_registered_types();
			
			// Check for templates in our public views
			if ( in_array( $post->post_type , $types ) ){
			    if( file_exists( MHDM_PLUGIN_DIR. '/public/views/single-'. $post->post_type .'.php' ) )
		        	$template = MHDM_PLUGIN_DIR . '/public/views/single-'. $post->post_type .'.php';
		    }
			return $template;
		}
		
		
		/**
		 * get_archive_cpt_tempalte function.
		 *
		 *	Set CPT template, allow override by theme template system
		 * 
		 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/archive_template
		 * @since 1.0.0
		 * @access public
		 * @param mixed $template
		 * @return void
		 */
		public function get_archive_cpt_template( $template ) {
		    global $post;
			
			$obj = MH_Deal_Manager_Post_Types::get_instance();
			$types = $obj->get_registered_types();
			
			// Check for templates in our public views
			if ( in_array( $post->post_type , $types ) ){
			    if( file_exists( MHDM_PLUGIN_DIR. '/public/views/archive-'. $post->post_type .'.php' ) )
		        	$template = MHDM_PLUGIN_DIR . '/public/views/archive-'. $post->post_type .'.php';
		    }
			return $template;
		}
		
		
		/**
		 * set_cpt_post_title function.
		 * 
		 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/wp_insert_post_data
		 * @since 1.0.0
		 * @access public
		 * @param mixed $data
		 * @param mixed $postarr
		 * @return void
		 */
		public function set_cpt_post_title( $data , $postarr ) {
		
			switch ( $data['post_type'] ){
			
				case 'deal':

					if ( isset( $postarr[ $this->plugin_slug . '_ref_number' ] ) ){
		                $data['post_title'] = trim ( $postarr[ $this->plugin_slug . '_ref_number' ] );      
		            }
		            
		            if ( isset( $postarr[ $this->plugin_slug . '_client' ] ) ){

		            	$users = get_users( array( 'include' => $postarr[ $this->plugin_slug . '_client' ] ) );
		            	$data['post_title'] .= ' - ';
		            	
		            	foreach( $users as $u ){
			            	$data['post_title'] .= $u->display_name . ', ';
		            	}
			            
			            $data['post_title'] = substr( $data['post_title'], 0, strlen( $data['post_title'] ) - 2 );
		            }
		            
				break;
				
				case 'property':

					if ( isset( $postarr[ $this->plugin_slug . '_address' ] ) ){
		                $data['post_title'] = trim ( $postarr[ $this->plugin_slug . '_address' ] );      
		            }
		            
		            if ( isset( $postarr[ $this->plugin_slug . '_city' ] ) ){
		                $data['post_title'] .= ', ' . trim ( $postarr[ $this->plugin_slug . '_city' ] );      
		            }

		            if ( isset( $postarr[ $this->plugin_slug . '_province' ] ) ){
		                $data['post_title'] .= ', ' . trim ( $postarr[ $this->plugin_slug . '_province' ] );      
		            }
		            
		            if ( isset( $postarr[ $this->plugin_slug . '_postal_code' ] ) ){
		                $data['post_title'] .= ', ' . trim ( $postarr[ $this->plugin_slug . '_postal_code' ] );      
		            }
		            
				break;
				
				case 'associate':

		            if ( isset( $postarr[ $this->plugin_slug . '_first_name' ] ) ){
		                $data['post_title']  = trim ( $postarr[ $this->plugin_slug . '_first_name' ] );      
		            }

		            if ( isset( $postarr[ $this->plugin_slug . '_last_name' ] ) ){
		                $data['post_title'] .= ' ' . trim ( $postarr[ $this->plugin_slug . '_last_name' ] );      
		            }
		            
		            if ( isset( $postarr[ $this->plugin_slug . '_company' ] ) ){
		                $data['post_title'] .= ' (' . trim ( array_pop ( $postarr[ $this->plugin_slug . '_company' ] ) ) . ')';      
		            }
		            
				break;
				
				case 'requirement':

		            if ( isset( $postarr[ $this->plugin_slug . '_deal' ] ) ){
		            	$ref_number = get_post_meta( intval ( $postarr[ $this->plugin_slug . '_deal' ] ), $this->plugin_slug . '_ref_number', true );
		                $data['post_title']  = trim ( $ref_number );      
		            }
		            
		            if ( isset( $postarr[ $this->plugin_slug . '_for_client' ] ) ){
			            $users = get_users( array( 'include' => $postarr[ $this->plugin_slug . '_for_client' ] ) );
		            	$data['post_title'] .= ' - ';
						
		            	foreach( $users as $u ){
			            	$data['post_title'] .= $u->display_name . ', ';
		            	}
						$data['post_title'] = substr( $data['post_title'], 0, strlen( $data['post_title'] ) - 2 );
					
					}
					
					$data['post_title'] .= ' [' . $postarr['ID'] .']' ; 
		            
				break;
								
				default:
				break;
				
			}
			
			//$slug = sanitize_title ( $data['post_title'] );
			//wp_unique_post_slug( $slug, $postarr['ID'], $data['post_status'], $data['post_type'], $data['post_parent'] );
			return $data;
			
		}
		
	}// end class
}// end if class exists
