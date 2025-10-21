<?php
/**
 * Side Tiles Menu
 *
 * @package           SideTilesMenu
 * @author            Your Name
 * @copyright         2025 Your Name
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Side Tiles Menu
 * Plugin URI:        https://example.com/side-tiles-menu
 * Description:       Dodaje pionowe menu kafelków przy krawędzi strony z pełną personalizacją, animacjami i dostępnością.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       side-tiles-menu
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'SIDE_TILES_MENU_VERSION', '1.0.0' );
define( 'SIDE_TILES_MENU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIDE_TILES_MENU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SIDE_TILES_MENU_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
class Side_Tiles_Menu {

	/**
	 * Single instance of the class
	 *
	 * @var Side_Tiles_Menu|null
	 */
	private static $instance = null;

	/**
	 * Settings instance
	 *
	 * @var Side_Tiles_Menu_Settings|null
	 */
	public $settings = null;

	/**
	 * Frontend instance
	 *
	 * @var Side_Tiles_Menu_Frontend|null
	 */
	public $frontend = null;

	/**
	 * Get single instance
	 *
	 * @return Side_Tiles_Menu
	 */
	public static function get_instance(): Side_Tiles_Menu {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies(): void {
		require_once SIDE_TILES_MENU_PLUGIN_DIR . 'includes/class-settings.php';
		require_once SIDE_TILES_MENU_PLUGIN_DIR . 'includes/class-frontend.php';
		require_once SIDE_TILES_MENU_PLUGIN_DIR . 'includes/class-gutenberg-block.php';
		require_once SIDE_TILES_MENU_PLUGIN_DIR . 'includes/class-analytics.php';
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks(): void {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'init' ) );

		// Activation/Deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize plugin components
	 */
	public function init(): void {
		$this->settings = new Side_Tiles_Menu_Settings();
		$this->frontend = new Side_Tiles_Menu_Frontend();

		// Initialize Gutenberg block
		new Side_Tiles_Menu_Gutenberg_Block();

		// Register shortcode
		add_shortcode( 'side_tiles', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Load plugin textdomain for translations
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'side-tiles-menu',
			false,
			dirname( SIDE_TILES_MENU_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Activate plugin
	 */
	public function activate(): void {
		// Set default options
		$default_options = array(
			'auto_display'       => true,
			'position'           => 'right',
			'offset_top'         => '100',
			'offset_top_unit'    => 'px',
			'offset_bottom'      => '',
			'offset_bottom_unit' => 'px',
			'sticky'             => false,
			'tile_width'         => '60',
			'tile_width_unit'    => 'px',
			'tile_height'        => '60',
			'tile_height_unit'   => 'px',
			'tile_spacing'       => '10',
			'tile_spacing_unit'  => 'px',
			'tile_shape'         => 'rounded',
			'border_radius'      => '8',
			'bg_color'           => '#333333',
			'text_color'         => '#ffffff',
			'use_gradient'       => false,
			'gradient_start'     => '#333333',
			'gradient_end'       => '#666666',
			'gradient_angle'     => '135',
			'border_width'       => '0',
			'border_color'       => '#000000',
			'shadow_enabled'     => true,
			'shadow_x'           => '0',
			'shadow_y'           => '2',
			'shadow_blur'        => '8',
			'shadow_color'       => 'rgba(0,0,0,0.3)',
			'hover_bg_color'     => '#555555',
			'hover_scale'        => '1.1',
			'z_index'            => '9999',
			'animation_type'     => 'slide',
			'animation_duration' => '300',
			'animation_delay'    => '0',
			'desktop_enabled'    => true,
			'tablet_enabled'     => true,
			'mobile_enabled'     => true,
			'desktop_breakpoint' => '1024',
			'tablet_breakpoint'  => '768',
			'exclude_pages'      => array(),
			'exclude_post_types' => array(),
			'show_logged_in'     => 'both',
			'ga4_enabled'        => false,
			'ga4_measurement_id' => '',
			'custom_css'         => '',
			'tiles'              => array(),
		);

		add_option( 'side_tiles_menu_options', $default_options );
		add_option( 'side_tiles_menu_clicks', array() );

		// Clear rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Deactivate plugin
	 */
	public function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Render shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ): string {
		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts,
			'side_tiles'
		);

		if ( ! empty( $atts['id'] ) ) {
			return $this->frontend->render_single_tile( intval( $atts['id'] ) );
		}

		return $this->frontend->render();
	}
}

/**
 * Get main plugin instance
 *
 * @return Side_Tiles_Menu
 */
function side_tiles_menu(): Side_Tiles_Menu {
	return Side_Tiles_Menu::get_instance();
}

// Initialize plugin
side_tiles_menu();
