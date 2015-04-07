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
		 * Global $wpdb
		 * 
		 * @var object
		 */
		private $wpdb;

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Define DB version
		 */
		protected static $post_useful_db_version = '1.0';

		/**
		 * Table in DB
		 * 
		 * @var string
		 */
		private $table;

		/**
		 * Initialize the plugin
		 */
		private function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
			$this->table = $wpdb->prefix . 'post_useful';

			// Load plugin text domain
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Create table in DB
			add_action( 'init', array( $this, 'create_table' ) );

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
		 * Create table Rating
		 */
		public function create_table() {
			$wpdb = $this->wpdb;
			$table = $this->table;

			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				
				$sql = "CREATE TABLE IF NOT EXISTS `$table` (
				  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `post_id` int(11) NOT NULL,
				  `rating` int(1) NOT NULL,
				  `user_ip` varchar(13) NOT NULL,
				  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				  `status` int(1) NOT NULL
				);";
				dbDelta( $sql );
			}
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
			$post_id = get_the_ID();
			$user_ip = $_SERVER['REMOTE_ADDR'];

			$check = self::check_rate( $post_id, $user_ip );
			if ( ! empty( $check ) ) {
				$user_rate = $check->rating;
			}

			$box_useful_rate = '<div class="wrap-post-useful post_useful_' . get_the_ID() . '">' . "\n";
				$box_useful_rate .= '<p>' . __( 'This content has been helpful to you?', 'post_useful' ) . '</p>' . "\n";
				$box_useful_rate .= '<p class="post_useful_success post_useful_success_' . get_the_ID() . '">' . __( 'Thanks for contributing!', 'post_useful' ) . '</p>' . "\n";
				$box_useful_rate .= '<div class="post-useful-buttons post_useful_buttons_' . get_the_ID() . '">' . "\n";
					$box_useful_rate .= '<a href="javascript:;" title="' . __( 'Yes', 'post_useful' ) . '" class="post-useful-vote post-useful-vote-yes" data-id="' . get_the_ID() . '" data-rate="1">Yes</a>' . "\n";
					$box_useful_rate .= '<a href="javascript:;" title="' . __( 'No', 'post_useful' ) . '" class="post-useful-vote post-useful-vote-no" data-id="' . get_the_ID() . '" data-rate="0">No</a>' . "\n";
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
		 * Check if user classify post
		 * 
		 * @param int $post_id
		 * @param string $user_ip
		 * @return boolean true or false
		 */
		public function check_rate( $post_id, $user_ip ) {
			$wpdb = $this->wpdb;
			$table = $this->table;

			$check = $wpdb->get_row("SELECT * FROM $table WHERE post_id = '$post_id' AND user_ip = '$user_ip'");
			return $check;
		}

		/**
		 * Send rate
		 */
		public function send_rate() {
			$wpdb = $this->wpdb;
			$table = $this->table;

			$post_id = sanitize_text_field( $_POST['post'] );
			$rate = sanitize_text_field( $_POST['rate'] );
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$check = self::check_rate( $post_id, $user_ip );
			if ( empty( $check ) ) {
				$data = array(
								'post_id'	=>	$post_id,
								'rating'	=>	$rate,
								'user_ip'	=>	$user_ip,
								'created'	=> date( 'Y-m-d H:i' )
							);
				$wpdb->insert( $table, $data );
				echo 'ok';
			}
			else
				echo 'erro';
			wp_die();
		}
	}
	add_action( 'plugins_loaded', array( 'Post_Useful', 'get_instance' ), 0 );



























