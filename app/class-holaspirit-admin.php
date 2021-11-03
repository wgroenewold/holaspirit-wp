<?php
/**
 * Holaspirit Admin
 *
 * @category PHP.
 * @package  Holaspirit.
 * @author   Wouter Groenewold <wgroenewold@gmail.com>.
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPLv3.
 * @link     https://github.com/wgroenewold.
 */

namespace holaspirit;

/**
 * Admin stuff for Holaspirit.
 */
class Holaspirit_Admin {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'acf/init', array( $this, 'add_options_page' ), 10, 0 );
		add_action( 'init', array( $this, 'create_posttype' ), 10, 0 );
		add_action( 'init', array( $this, 'create_taxonomy' ), 10, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 10, 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 10, 0 );
		add_action( 'wp_ajax_generate_token', array( $this, 'generate_token' ), 10, 0 );
		add_action( 'token_cron_hook', array( $this, 'token_cron_execute' ), 10, 0 );
		add_action( 'acf/init', array( $this, 'add_acf_blocks' ), 10, 0 );
	}

	/**
	 * Add Settings page
	 */
	public function add_options_page() {
		if ( function_exists( 'acf_add_options_page' ) ) {
			$option_page = acf_add_options_page(
				array(
					'page_title' => __( 'Holaspirit Instellingen' ),
					'menu_title' => __( 'Holaspirit Instellingen' ),
					'menu_slug'  => 'holaspirit-settings',
					'capability' => 'edit_posts',
					'redirect'   => false,
					'icon_url'   => 'dashicons-image-filter',
				)
			);
		}
	}

	/**
	 * Create posttypes for storage.
	 */
	public function create_posttype() {
		register_post_type(
			'holaspirit_cpt',
			array(
				'label'               => 'Holaspirit',
				'singular_label'      => 'Holaspirit',
				'has_archive'         => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_rest'        => true,
				'capability_type'     => 'post',
				'hierarchical'        => true,
				'rewrite'             => array(
					'slug'        => 'holaspirit',
					'with_front'  => false,
					'has_archive' => false,
				),
				'supports'            => array( 'title' ),
				'exclude_from_search' => true,
				'menu_icon'           => 'dashicons-image-filter',
			)
		);
	}

	/**
	 * Create taxonomy for tree-like storage
	 */
	public function create_taxonomy() {
		$args = array(
			'labels'            => array( 'menu_name' => 'Cirkels' ),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
		);
		register_taxonomy( 'holaspirit_tax', array( 'holaspirit_cpt' ), $args );
	}

	/**
	 * Add some assets.
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'holaspirit-js', plugins_url( '../assets/js/holaspirit.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		wp_localize_script( 'holaspirit-js', 'ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		wp_enqueue_style( 'holaspirit-css', plugins_url( '../assets/css/holaspirit.css', __FILE__ ), array(), '1.0.0' );
	}

	/**
	 * Add some frontend assets.
	 */
	public function frontend_scripts() {
		wp_enqueue_style( 'holaspirit-frontend-css', plugins_url( '../assets/css/holaspirit-frontend.css', __FILE__ ), array(), '1.0.0' );
	}

	/**
	 * AJAX callback to generate a token.
	 */
	public function generate_token() {
		$instance = new Holaspirit_API();
		$token    = $instance->obtain_access_token();
		echo esc_textarea( $token );
		die();
	}

	/**
	 * Add tasks to scheduler.
	 */
	public function schedule_tasks() {
		if ( ! wp_next_scheduled( 'token_cron_hook' ) ) {
			wp_schedule_event( time(), 'daily', 'token_cron_hook' );
		}
	}

	/**
	 * Automatically refresh token.
	 */
	public function token_cron_execute() {
		$instance = new Holaspirit_API();
		$instance->obtain_access_token();
	}

	/**
	 * Expose content in Gutenberg blocks.
	 */
	public function add_acf_blocks() {
		if ( function_exists( 'acf_register_block' ) ) {
			acf_register_block(
				array(
					'name'            => 'circles',
					'title'           => __( 'Circles' ),
					'description'     => __( 'Block for circles' ),
					'render_callback' => array( $this, 'acf_block_callback' ),
					'category'        => 'formatting',
					'icon'            => 'image-filter',
					'keywords'        => array( 'circles', 'holaspirit' ),
				)
			);

			acf_register_block(
				array(
					'name'            => 'roles',
					'title'           => __( 'Roles' ),
					'description'     => __( 'Block for roles' ),
					'render_callback' => array( $this, 'acf_block_callback' ),
					'category'        => 'formatting',
					'icon'            => 'image-rotate',
					'keywords'        => array( 'roles', 'holaspirit' ),
				)
			);
		}
	}

	/**
	 * Callback function to connect to Gutenberg frontend.
	 *
	 * @param array $block ACF block.
	 */
	public function acf_block_callback( $block ) {
		$slug = str_replace( 'acf/', '', $block['name'] );

		include( HOLASPIRIT_ROOT . "/blocks/$slug.php" );
	}
}