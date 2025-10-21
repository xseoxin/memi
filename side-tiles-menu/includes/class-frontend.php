<?php
/**
 * Frontend class
 *
 * @package SideTilesMenu
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Side Tiles Menu Frontend class
 */
class Side_Tiles_Menu_Frontend {

	/**
	 * Option name
	 *
	 * @var string
	 */
	private $option_name = 'side_tiles_menu_options';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_tiles' ) );
		add_action( 'wp_ajax_side_tiles_track_click', array( $this, 'ajax_track_click' ) );
		add_action( 'wp_ajax_nopriv_side_tiles_track_click', array( $this, 'ajax_track_click' ) );
	}

	/**
	 * Check if tiles should be displayed
	 *
	 * @return bool
	 */
	private function should_display_tiles(): bool {
		$options = get_option( $this->option_name, array() );

		// Check user login status
		$show_logged_in = $options['show_logged_in'] ?? 'both';
		if ( 'logged_in' === $show_logged_in && ! is_user_logged_in() ) {
			return false;
		}
		if ( 'logged_out' === $show_logged_in && is_user_logged_in() ) {
			return false;
		}

		// Check excluded pages
		$exclude_pages = $options['exclude_pages'] ?? array();
		if ( is_page() && in_array( get_the_ID(), $exclude_pages, true ) ) {
			return false;
		}

		// Check excluded post types
		$exclude_post_types = $options['exclude_post_types'] ?? array();
		if ( is_singular() && in_array( get_post_type(), $exclude_post_types, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_assets(): void {
		if ( ! $this->should_display_tiles() ) {
			return;
		}

		$options = get_option( $this->option_name, array() );

		wp_enqueue_style(
			'side-tiles-menu-frontend',
			SIDE_TILES_MENU_PLUGIN_URL . 'public/css/frontend.css',
			array(),
			SIDE_TILES_MENU_VERSION
		);

		// Add inline CSS for customizations
		$custom_css = $this->generate_custom_css( $options );
		wp_add_inline_style( 'side-tiles-menu-frontend', $custom_css );

		// Add user custom CSS
		if ( ! empty( $options['custom_css'] ) ) {
			wp_add_inline_style( 'side-tiles-menu-frontend', $options['custom_css'] );
		}

		wp_enqueue_script(
			'side-tiles-menu-frontend',
			SIDE_TILES_MENU_PLUGIN_URL . 'public/js/frontend.js',
			array(),
			SIDE_TILES_MENU_VERSION,
			true
		);

		wp_localize_script(
			'side-tiles-menu-frontend',
			'sideTilesMenuData',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'side_tiles_menu_nonce' ),
				'animationType'     => $options['animation_type'] ?? 'slide',
				'animationDuration' => $options['animation_duration'] ?? 300,
				'animationDelay'    => $options['animation_delay'] ?? 0,
				'ga4Enabled'        => $options['ga4_enabled'] ?? false,
				'ga4MeasurementId'  => $options['ga4_measurement_id'] ?? '',
				'desktopEnabled'    => $options['desktop_enabled'] ?? true,
				'tabletEnabled'     => $options['tablet_enabled'] ?? true,
				'mobileEnabled'     => $options['mobile_enabled'] ?? true,
				'desktopBreakpoint' => $options['desktop_breakpoint'] ?? 1024,
				'tabletBreakpoint'  => $options['tablet_breakpoint'] ?? 768,
			)
		);
	}

	/**
	 * Generate custom CSS based on options
	 *
	 * @param array $options Options array.
	 * @return string
	 */
	private function generate_custom_css( $options ): string {
		$css = '';

		// Position
		$position        = $options['position'] ?? 'right';
		$offset_top      = ( $options['offset_top'] ?? 100 ) . ( $options['offset_top_unit'] ?? 'px' );
		$offset_bottom   = ! empty( $options['offset_bottom'] ) ? ( $options['offset_bottom'] . ( $options['offset_bottom_unit'] ?? 'px' ) ) : 'auto';
		$sticky          = $options['sticky'] ?? false;

		$css .= ".side-tiles-menu-container {\n";
		$css .= "  {$position}: 0;\n";
		$css .= "  top: {$offset_top};\n";
		if ( 'auto' !== $offset_bottom ) {
			$css .= "  bottom: {$offset_bottom};\n";
		}
		if ( $sticky ) {
			$css .= "  position: fixed;\n";
		} else {
			$css .= "  position: absolute;\n";
		}
		$css .= "  z-index: " . ( $options['z_index'] ?? 9999 ) . ";\n";
		$css .= "}\n";

		// Size
		$tile_width   = ( $options['tile_width'] ?? 60 ) . ( $options['tile_width_unit'] ?? 'px' );
		$tile_height  = ( $options['tile_height'] ?? 60 ) . ( $options['tile_height_unit'] ?? 'px' );
		$tile_spacing = ( $options['tile_spacing'] ?? 10 ) . ( $options['tile_spacing_unit'] ?? 'px' );

		$css .= ".side-tiles-menu-container {\n";
		$css .= "  gap: {$tile_spacing};\n";
		$css .= "}\n";

		$css .= ".side-tile {\n";
		$css .= "  width: {$tile_width};\n";
		$css .= "  height: {$tile_height};\n";

		// Shape
		$tile_shape = $options['tile_shape'] ?? 'rounded';
		if ( 'circle' === $tile_shape ) {
			$css .= "  border-radius: 50%;\n";
		} elseif ( 'rounded' === $tile_shape ) {
			$border_radius = ( $options['border_radius'] ?? 8 ) . 'px';
			$css .= "  border-radius: {$border_radius};\n";
		} else {
			$css .= "  border-radius: 0;\n";
		}

		// Colors and gradients
		if ( $options['use_gradient'] ?? false ) {
			$gradient_start = $options['gradient_start'] ?? '#333333';
			$gradient_end   = $options['gradient_end'] ?? '#666666';
			$gradient_angle = $options['gradient_angle'] ?? 135;
			$css           .= "  background: linear-gradient({$gradient_angle}deg, {$gradient_start}, {$gradient_end});\n";
		} else {
			$bg_color = $options['bg_color'] ?? '#333333';
			$css     .= "  background-color: {$bg_color};\n";
		}

		$text_color = $options['text_color'] ?? '#ffffff';
		$css       .= "  color: {$text_color};\n";

		// Border
		$border_width = $options['border_width'] ?? 0;
		if ( $border_width > 0 ) {
			$border_color = $options['border_color'] ?? '#000000';
			$css         .= "  border: {$border_width}px solid {$border_color};\n";
		}

		// Shadow
		if ( $options['shadow_enabled'] ?? true ) {
			$shadow_x     = $options['shadow_x'] ?? 0;
			$shadow_y     = $options['shadow_y'] ?? 2;
			$shadow_blur  = $options['shadow_blur'] ?? 8;
			$shadow_color = $options['shadow_color'] ?? 'rgba(0,0,0,0.3)';
			$css         .= "  box-shadow: {$shadow_x}px {$shadow_y}px {$shadow_blur}px {$shadow_color};\n";
		}

		$css .= "  transition: transform 0.3s ease, background-color 0.3s ease;\n";
		$css .= "}\n";

		// Hover effects
		$hover_bg_color = $options['hover_bg_color'] ?? '#555555';
		$hover_scale    = $options['hover_scale'] ?? 1.1;

		$css .= ".side-tile:hover {\n";
		if ( ! ( $options['use_gradient'] ?? false ) ) {
			$css .= "  background-color: {$hover_bg_color};\n";
		}
		$css .= "  transform: scale({$hover_scale});\n";
		$css .= "}\n";

		// Focus states for accessibility
		$css .= ".side-tile:focus {\n";
		$css .= "  outline: 2px solid {$text_color};\n";
		$css .= "  outline-offset: 2px;\n";
		$css .= "}\n";

		// SVG icon styling
		$css .= ".side-tile svg {\n";
		$css .= "  width: 60%;\n";
		$css .= "  height: 60%;\n";
		$css .= "  fill: {$text_color};\n";
		$css .= "}\n";

		// Image styling
		$css .= ".side-tile img {\n";
		$css .= "  width: 80%;\n";
		$css .= "  height: 80%;\n";
		$css .= "  object-fit: cover;\n";
		$css .= "}\n";

		// Responsive visibility
		$desktop_enabled    = $options['desktop_enabled'] ?? true;
		$tablet_enabled     = $options['tablet_enabled'] ?? true;
		$mobile_enabled     = $options['mobile_enabled'] ?? true;
		$desktop_breakpoint = $options['desktop_breakpoint'] ?? 1024;
		$tablet_breakpoint  = $options['tablet_breakpoint'] ?? 768;

		if ( ! $mobile_enabled ) {
			$css .= "@media (max-width: {$tablet_breakpoint}px) {\n";
			$css .= "  .side-tiles-menu-container { display: none !important; }\n";
			$css .= "}\n";
		}

		if ( ! $tablet_enabled ) {
			$css .= "@media (min-width: " . ( $tablet_breakpoint + 1 ) . "px) and (max-width: {$desktop_breakpoint}px) {\n";
			$css .= "  .side-tiles-menu-container { display: none !important; }\n";
			$css .= "}\n";
		}

		if ( ! $desktop_enabled ) {
			$css .= "@media (min-width: " . ( $desktop_breakpoint + 1 ) . "px) {\n";
			$css .= "  .side-tiles-menu-container { display: none !important; }\n";
			$css .= "}\n";
		}

		return $css;
	}

	/**
	 * Render tiles in footer
	 */
	public function render_tiles(): void {
		if ( ! $this->should_display_tiles() ) {
			return;
		}

		echo $this->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render tiles HTML
	 *
	 * @return string
	 */
	public function render(): string {
		$options = get_option( $this->option_name, array() );
		$tiles   = $options['tiles'] ?? array();

		if ( empty( $tiles ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="side-tiles-menu-container" role="navigation" aria-label="<?php esc_attr_e( 'Menu boczne', 'side-tiles-menu' ); ?>">
			<?php foreach ( $tiles as $tile ) : ?>
				<?php echo $this->render_single_tile_html( $tile ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render single tile (for shortcode)
	 *
	 * @param int $tile_id Tile ID.
	 * @return string
	 */
	public function render_single_tile( $tile_id ): string {
		$options = get_option( $this->option_name, array() );
		$tiles   = $options['tiles'] ?? array();

		foreach ( $tiles as $tile ) {
			if ( (string) $tile['id'] === (string) $tile_id ) {
				return $this->render_single_tile_html( $tile );
			}
		}

		return '';
	}

	/**
	 * Render single tile HTML
	 *
	 * @param array $tile Tile data.
	 * @return string
	 */
	private function render_single_tile_html( $tile ): string {
		$tile_id     = esc_attr( $tile['id'] ?? '' );
		$title       = esc_attr( $tile['title'] ?? '' );
		$link_url    = esc_url( $tile['link_url'] ?? '#' );
		$link_target = esc_attr( $tile['link_target'] ?? '_self' );
		$aria_label  = esc_attr( $tile['aria_label'] ?? $title );

		$rel = '_blank' === $link_target ? ' rel="noopener noreferrer"' : '';

		ob_start();
		?>
		<a href="<?php echo $link_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		   class="side-tile"
		   data-tile-id="<?php echo $tile_id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		   target="<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		   <?php echo $rel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		   title="<?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		   aria-label="<?php echo $aria_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		   tabindex="0">
			<?php
			$content_type = $tile['content_type'] ?? 'svg';

			if ( 'svg' === $content_type && ! empty( $tile['svg_code'] ) ) {
				echo wp_kses_post( $tile['svg_code'] );
			} elseif ( 'image' === $content_type && ! empty( $tile['image_url'] ) ) {
				echo '<img src="' . esc_url( $tile['image_url'] ) . '" alt="' . esc_attr( $title ) . '" loading="lazy">';
			} elseif ( 'text' === $content_type && ! empty( $tile['text_content'] ) ) {
				echo '<span class="tile-text">' . esc_html( $tile['text_content'] ) . '</span>';
			}
			?>
		</a>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX: Track click
	 */
	public function ajax_track_click(): void {
		check_ajax_referer( 'side_tiles_menu_nonce', 'nonce' );

		$tile_id = sanitize_text_field( $_POST['tile_id'] ?? '' );

		if ( empty( $tile_id ) ) {
			wp_send_json_error();
		}

		$clicks = get_option( 'side_tiles_menu_clicks', array() );

		if ( ! isset( $clicks[ $tile_id ] ) ) {
			$clicks[ $tile_id ] = 0;
		}

		$clicks[ $tile_id ]++;

		update_option( 'side_tiles_menu_clicks', $clicks );

		wp_send_json_success( array( 'count' => $clicks[ $tile_id ] ) );
	}
}
