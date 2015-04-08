<?php

class Post_Useful_Options {

	/**
	 * Holds the statistics metabox slug
	 *
	 * @var string
	 */
	protected $statistics_metabox_slug;

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Instance of the main class
	 * 
	 * @var object
	 */
	protected $post_useful_class = null;


	/**
	 * Posts types where we need to show statistics metabox
	 * 
	 * @var array
	 */
	protected $post_types_metabox;

	/**
	 * Global $wpdb
	 * 
	 * @var object
	 */
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->statistics_metabox_slug 	= Post_Useful::$plugin_slug . '-statistics_metabox';
		$this->post_useful_class 		= Post_Useful::get_instance();


		$this->post_types_metabox = apply_filters( 'post_useful_statistics_post_types', array('post') );
		foreach($this->post_types_metabox as $post_type) {
			add_action( 'add_meta_boxes_' . $post_type, array( $this, 'statistics_metabox' ) );
		}

		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts') ); 
		

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
	 * Load scripts js and styles css for admin
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		//Load style.css for metabox
		if ( in_array($screen->id, $this->post_types_metabox) ) {
			wp_enqueue_style( 'post_useful_css_main', plugins_url( '../assets/css/main.css', __FILE__ ), array(), null, 'all' );
		}
		
	}

	/**
	 * Add a meta box to a post edit screen
	 */
	public function statistics_metabox() {
		add_meta_box(
			$this->statistics_metabox_slug,
			__('Post Useful Statistics', 'post_useful'),
			array( $this, 'statistics_metabox_display'),
			null,
			'side',
			'default'
		);
	}


	/**
	 * Display the contento of the statistics metabox
	 */
	public function statistics_metabox_display() {
		$vote_yes = $this->wpdb->get_var( 
								$this->wpdb->prepare('SELECT COUNT(*) FROM ' . $this->post_useful_class->table .' WHERE post_id = %d AND rating = %d', 
							  	array(get_the_ID(), 1) ) 
							);

		$vote_no = $this->wpdb->get_var( 
								$this->wpdb->prepare('SELECT COUNT(*) FROM ' . $this->post_useful_class->table .' WHERE post_id = %d AND rating = %d', 
							  	array(get_the_ID(), 0) ) 
							);

		$metabox = '<div class="post-useful-buttons post-useful-metabox">';
			$metabox .= '<span class="post-useful-vote post-useful-vote-yes">' . $vote_yes . '</span>';
			$metabox .= '<span class="post-useful-vote post-useful-vote-no">'  . $vote_no  . '</span>';
		$metabox .= '</div>';

		echo $metabox;
	}
}