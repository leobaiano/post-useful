<?php
	/**
	 * Plugin Name: Post Useful
	 * Plugin URI: 
	 * Description: Allows users to enter if the post is useful or not
	 * Author: leobaiano
	 * Author URI: http://lbideias.com.br
	 * Version: 1.0.0
	 * License: GPLv2 or later
	 * Text Domain: post_useful
 	 * Domain Path: /languages/
	 */
	if ( ! defined( 'ABSPATH' ) )
		exit; // Exit if accessed directly.
	/**
	 * Post_Useful
	 *
	 * @author   Leo Baiano <leobaiano@lbideias.com.br>
	 */
	class Post_Useful {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin
		 */
		private function __construct() {
			// Load plugin text domain
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Load scripts js and styles css
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );

			// Print useful in post
			add_filter( 'the_content', array( $this, 'print_useful' ) );

			// Ajax send rate
			add_action( 'wp_ajax_send_rate', array( $this, 'send_rate' ) );
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'pimap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Require classes admin
		 */
		protected function require_admin() {
			require_once 'admin/class-post-useful-options.php';
		}

		/**
		 * Print useful rate in post
		 *
		 * @param string $content - Post Original Content
		 * @return string $content - Post Original Content More Useful Question
		 */
		public function print_useful( $content ) {
			$box_useful_rate = '<div class="wrap-post-useful">' . "\n";
				$box_useful_rate .= '<p>' . __( 'This content has been helpful to you?', 'post_useful' ) . '</p>' . "\n";
				$box_useful_rate .= '<div class="post-useful-buttons">' . "\n";
					$box_useful_rate .= '<a href="javascript:;" title="' . __( 'Yes', 'post_useful' ) . '" class="post-useful-vote post-useful-vote-yes" data-id="' . get_the_ID() . '" data-rate="yes">Yes</a>' . "\n";
					$box_useful_rate .= '<a href="javascript:;" title="' . __( 'No', 'post_useful' ) . '" class="post-useful-vote post-useful-vote-no" data-id="' . get_the_ID() . '" data-rate="no">No</a>' . "\n";
				$box_useful_rate .= '</div>' . "\n";
			$box_useful_rate .= '</div>' . "\n";

			return $content . $box_useful_rate;
		}

		/**
		 * Load scripts js and styles css
		 */
		public function enqueue_scripts() {
			wp_enqueue_style( 'post_useful_css_main', plugins_url( 'assets/css/main.css', __FILE__ ), array(), null, 'all' );
			wp_enqueue_script( 'post_useful_js_main', plugins_url( 'assets/js/main.js', __FILE__ ), array( 'jquery' ), null, true );
			wp_localize_script( 'post_useful_js_main', 'postUsefulAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		}

		/**
		 * Send rate
		 */
		public function send_rate() {
			echo "te: " . $_POST['post'];
			wp_die();
		}
	}
	add_action( 'plugins_loaded', array( 'Post_Useful', 'get_instance' ), 0 );