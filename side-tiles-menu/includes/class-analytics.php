<?php
/**
 * Analytics class
 *
 * @package SideTilesMenu
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Side Tiles Menu Analytics class
 */
class Side_Tiles_Menu_Analytics {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'add_ga4_tracking' ) );
	}

	/**
	 * Add Google Analytics 4 tracking code
	 */
	public function add_ga4_tracking(): void {
		$options = get_option( 'side_tiles_menu_options', array() );

		if ( ! ( $options['ga4_enabled'] ?? false ) ) {
			return;
		}

		$measurement_id = $options['ga4_measurement_id'] ?? '';

		if ( empty( $measurement_id ) ) {
			return;
		}

		?>
		<!-- Google Analytics 4 - Side Tiles Menu -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $measurement_id ); ?>"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', '<?php echo esc_js( $measurement_id ); ?>');
		</script>
		<?php
	}
}

// Initialize analytics
new Side_Tiles_Menu_Analytics();
