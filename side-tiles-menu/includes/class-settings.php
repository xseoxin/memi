<?php
/**
 * Settings class
 *
 * @package SideTilesMenu
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Side Tiles Menu Settings class
 */
class Side_Tiles_Menu_Settings {

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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_side_tiles_save_tile', array( $this, 'ajax_save_tile' ) );
		add_action( 'wp_ajax_side_tiles_delete_tile', array( $this, 'ajax_delete_tile' ) );
		add_action( 'wp_ajax_side_tiles_export_settings', array( $this, 'ajax_export_settings' ) );
		add_action( 'wp_ajax_side_tiles_import_settings', array( $this, 'ajax_import_settings' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			__( 'Side Tiles Menu', 'side-tiles-menu' ),
			__( 'Side Tiles Menu', 'side-tiles-menu' ),
			'manage_options',
			'side-tiles-menu',
			array( $this, 'render_settings_page' ),
			'dashicons-grid-view',
			65
		);

		add_submenu_page(
			'side-tiles-menu',
			__( 'Ustawienia', 'side-tiles-menu' ),
			__( 'Ustawienia', 'side-tiles-menu' ),
			'manage_options',
			'side-tiles-menu',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'side-tiles-menu',
			__( 'Kafelki', 'side-tiles-menu' ),
			__( 'Kafelki', 'side-tiles-menu' ),
			'manage_options',
			'side-tiles-menu-tiles',
			array( $this, 'render_tiles_page' )
		);

		add_submenu_page(
			'side-tiles-menu',
			__( 'Statystyki', 'side-tiles-menu' ),
			__( 'Statystyki', 'side-tiles-menu' ),
			'manage_options',
			'side-tiles-menu-stats',
			array( $this, 'render_stats_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings(): void {
		register_setting(
			'side_tiles_menu_settings',
			$this->option_name,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// Position section
		add_settings_section(
			'side_tiles_position',
			__( 'Pozycja i Ułożenie', 'side-tiles-menu' ),
			null,
			'side_tiles_menu_settings'
		);

		// Size section
		add_settings_section(
			'side_tiles_size',
			__( 'Rozmiar i Kształt', 'side-tiles-menu' ),
			null,
			'side_tiles_menu_settings'
		);

		// Style section
		add_settings_section(
			'side_tiles_style',
			__( 'Styl i Kolory', 'side-tiles-menu' ),
			null,
			'side_tiles_menu_settings'
		);

		// Animation section
		add_settings_section(
			'side_tiles_animation',
			__( 'Animacje', 'side-tiles-menu' ),
			null,
			'side_tiles_menu_settings'
		);

		// Visibility section
		add_settings_section(
			'side_tiles_visibility',
			__( 'Widoczność', 'side-tiles-menu' ),
			null,
			'side_tiles_menu_settings'
		);

		// Analytics section
		add_settings_section(
			'side_tiles_analytics',
			__( 'Analityka', 'side-tiles-menu' ),
			null,
			'side_tiles_menu_settings'
		);

		// Custom CSS section
		add_settings_section(
			'side_tiles_custom',
			__( 'Personalizacja', 'side-tiles-menu' ),
			null,
			'side_tiles_menu_settings'
		);
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input Input data.
	 * @return array
	 */
	public function sanitize_settings( $input ): array {
		$sanitized = array();

		// General
		$sanitized['auto_display'] = isset( $input['auto_display'] );

		// Position
		$sanitized['position']           = in_array( $input['position'] ?? '', array( 'left', 'right' ), true ) ? $input['position'] : 'right';
		$sanitized['offset_top']         = absint( $input['offset_top'] ?? 100 );
		$sanitized['offset_top_unit']    = in_array( $input['offset_top_unit'] ?? '', array( 'px', '%' ), true ) ? $input['offset_top_unit'] : 'px';
		$sanitized['offset_bottom']      = isset( $input['offset_bottom'] ) ? absint( $input['offset_bottom'] ) : '';
		$sanitized['offset_bottom_unit'] = in_array( $input['offset_bottom_unit'] ?? '', array( 'px', '%' ), true ) ? $input['offset_bottom_unit'] : 'px';
		$sanitized['sticky']             = isset( $input['sticky'] );

		// Size
		$sanitized['tile_width']        = absint( $input['tile_width'] ?? 60 );
		$sanitized['tile_width_unit']   = in_array( $input['tile_width_unit'] ?? '', array( 'px', '%' ), true ) ? $input['tile_width_unit'] : 'px';
		$sanitized['tile_height']       = absint( $input['tile_height'] ?? 60 );
		$sanitized['tile_height_unit']  = in_array( $input['tile_height_unit'] ?? '', array( 'px', '%' ), true ) ? $input['tile_height_unit'] : 'px';
		$sanitized['tile_spacing']      = absint( $input['tile_spacing'] ?? 10 );
		$sanitized['tile_spacing_unit'] = in_array( $input['tile_spacing_unit'] ?? '', array( 'px', '%' ), true ) ? $input['tile_spacing_unit'] : 'px';
		$sanitized['tile_shape']        = in_array( $input['tile_shape'] ?? '', array( 'square', 'rounded', 'circle' ), true ) ? $input['tile_shape'] : 'rounded';
		$sanitized['border_radius']     = absint( $input['border_radius'] ?? 8 );

		// Colors
		$sanitized['bg_color']       = sanitize_hex_color( $input['bg_color'] ?? '#333333' );
		$sanitized['text_color']     = sanitize_hex_color( $input['text_color'] ?? '#ffffff' );
		$sanitized['use_gradient']   = isset( $input['use_gradient'] );
		$sanitized['gradient_start'] = sanitize_hex_color( $input['gradient_start'] ?? '#333333' );
		$sanitized['gradient_end']   = sanitize_hex_color( $input['gradient_end'] ?? '#666666' );
		$sanitized['gradient_angle'] = absint( $input['gradient_angle'] ?? 135 );

		// Border and shadow
		$sanitized['border_width']   = absint( $input['border_width'] ?? 0 );
		$sanitized['border_color']   = sanitize_hex_color( $input['border_color'] ?? '#000000' );
		$sanitized['shadow_enabled'] = isset( $input['shadow_enabled'] );
		$sanitized['shadow_x']       = intval( $input['shadow_x'] ?? 0 );
		$sanitized['shadow_y']       = intval( $input['shadow_y'] ?? 2 );
		$sanitized['shadow_blur']    = absint( $input['shadow_blur'] ?? 8 );
		$sanitized['shadow_color']   = sanitize_text_field( $input['shadow_color'] ?? 'rgba(0,0,0,0.3)' );

		// Hover
		$sanitized['hover_bg_color'] = sanitize_hex_color( $input['hover_bg_color'] ?? '#555555' );
		$sanitized['hover_scale']    = floatval( $input['hover_scale'] ?? 1.1 );
		$sanitized['z_index']        = absint( $input['z_index'] ?? 9999 );

		// Animation
		$sanitized['animation_type']     = in_array( $input['animation_type'] ?? '', array( 'none', 'slide', 'fade', 'scale' ), true ) ? $input['animation_type'] : 'slide';
		$sanitized['animation_duration'] = absint( $input['animation_duration'] ?? 300 );
		$sanitized['animation_delay']    = absint( $input['animation_delay'] ?? 0 );

		// Visibility
		$sanitized['desktop_enabled']    = isset( $input['desktop_enabled'] );
		$sanitized['tablet_enabled']     = isset( $input['tablet_enabled'] );
		$sanitized['mobile_enabled']     = isset( $input['mobile_enabled'] );
		$sanitized['desktop_breakpoint'] = absint( $input['desktop_breakpoint'] ?? 1024 );
		$sanitized['tablet_breakpoint']  = absint( $input['tablet_breakpoint'] ?? 768 );
		$sanitized['exclude_pages']      = isset( $input['exclude_pages'] ) && is_array( $input['exclude_pages'] ) ? array_map( 'absint', $input['exclude_pages'] ) : array();
		$sanitized['exclude_post_types'] = isset( $input['exclude_post_types'] ) && is_array( $input['exclude_post_types'] ) ? array_map( 'sanitize_text_field', $input['exclude_post_types'] ) : array();
		$sanitized['show_logged_in']     = in_array( $input['show_logged_in'] ?? '', array( 'both', 'logged_in', 'logged_out' ), true ) ? $input['show_logged_in'] : 'both';

		// Analytics
		$sanitized['ga4_enabled']        = isset( $input['ga4_enabled'] );
		$sanitized['ga4_measurement_id'] = sanitize_text_field( $input['ga4_measurement_id'] ?? '' );

		// Custom CSS
		$sanitized['custom_css'] = wp_strip_all_tags( $input['custom_css'] ?? '' );

		// Keep existing tiles
		$options              = get_option( $this->option_name, array() );
		$sanitized['tiles']   = $options['tiles'] ?? array();

		return $sanitized;
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ): void {
		if ( ! str_contains( $hook, 'side-tiles-menu' ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_media();

		wp_enqueue_style(
			'side-tiles-menu-admin',
			SIDE_TILES_MENU_PLUGIN_URL . 'admin/css/admin.css',
			array( 'wp-color-picker' ),
			SIDE_TILES_MENU_VERSION
		);

		wp_enqueue_script(
			'side-tiles-menu-admin',
			SIDE_TILES_MENU_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			SIDE_TILES_MENU_VERSION,
			true
		);

		wp_localize_script(
			'side-tiles-menu-admin',
			'sideTilesMenu',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'side_tiles_menu_nonce' ),
				'strings' => array(
					'confirmDelete' => __( 'Czy na pewno chcesz usunąć ten kafelek?', 'side-tiles-menu' ),
					'saveSuccess'   => __( 'Kafelek zapisany pomyślnie', 'side-tiles-menu' ),
					'saveError'     => __( 'Błąd podczas zapisywania kafelka', 'side-tiles-menu' ),
					'deleteSuccess' => __( 'Kafelek usunięty pomyślnie', 'side-tiles-menu' ),
					'deleteError'   => __( 'Błąd podczas usuwania kafelka', 'side-tiles-menu' ),
					'exportSuccess' => __( 'Ustawienia wyeksportowane', 'side-tiles-menu' ),
					'importSuccess' => __( 'Ustawienia zaimportowane pomyślnie', 'side-tiles-menu' ),
					'importError'   => __( 'Błąd podczas importowania ustawień', 'side-tiles-menu' ),
				),
			)
		);
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = get_option( $this->option_name, array() );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors(); ?>

			<div class="side-tiles-menu-admin-wrapper">
				<div class="side-tiles-menu-settings">
					<form method="post" action="options.php">
						<?php
						settings_fields( 'side_tiles_menu_settings' );
						?>

						<div class="side-tiles-settings-tabs">
							<nav class="nav-tab-wrapper">
								<a href="#position" class="nav-tab nav-tab-active"><?php esc_html_e( 'Pozycja', 'side-tiles-menu' ); ?></a>
								<a href="#size" class="nav-tab"><?php esc_html_e( 'Rozmiar', 'side-tiles-menu' ); ?></a>
								<a href="#style" class="nav-tab"><?php esc_html_e( 'Styl', 'side-tiles-menu' ); ?></a>
								<a href="#animation" class="nav-tab"><?php esc_html_e( 'Animacje', 'side-tiles-menu' ); ?></a>
								<a href="#visibility" class="nav-tab"><?php esc_html_e( 'Widoczność', 'side-tiles-menu' ); ?></a>
								<a href="#analytics" class="nav-tab"><?php esc_html_e( 'Analityka', 'side-tiles-menu' ); ?></a>
								<a href="#custom" class="nav-tab"><?php esc_html_e( 'Personalizacja', 'side-tiles-menu' ); ?></a>
							</nav>

							<div id="position" class="tab-content tab-content-active">
								<?php $this->render_position_settings( $options ); ?>
							</div>

							<div id="size" class="tab-content">
								<?php $this->render_size_settings( $options ); ?>
							</div>

							<div id="style" class="tab-content">
								<?php $this->render_style_settings( $options ); ?>
							</div>

							<div id="animation" class="tab-content">
								<?php $this->render_animation_settings( $options ); ?>
							</div>

							<div id="visibility" class="tab-content">
								<?php $this->render_visibility_settings( $options ); ?>
							</div>

							<div id="analytics" class="tab-content">
								<?php $this->render_analytics_settings( $options ); ?>
							</div>

							<div id="custom" class="tab-content">
								<?php $this->render_custom_settings( $options ); ?>
							</div>
						</div>

						<?php submit_button( __( 'Zapisz zmiany', 'side-tiles-menu' ) ); ?>
					</form>

					<div class="side-tiles-import-export">
						<h2><?php esc_html_e( 'Eksport/Import Ustawień', 'side-tiles-menu' ); ?></h2>
						<p>
							<button type="button" class="button" id="side-tiles-export"><?php esc_html_e( 'Eksportuj ustawienia', 'side-tiles-menu' ); ?></button>
						</p>
						<p>
							<input type="file" id="side-tiles-import-file" accept=".json" style="display:none;">
							<button type="button" class="button" id="side-tiles-import"><?php esc_html_e( 'Importuj ustawienia', 'side-tiles-menu' ); ?></button>
						</p>
					</div>
				</div>

				<div class="side-tiles-menu-preview">
					<h2><?php esc_html_e( 'Podgląd na żywo', 'side-tiles-menu' ); ?></h2>
					<div class="side-tiles-preview-wrapper">
						<div id="side-tiles-live-preview"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render position settings
	 *
	 * @param array $options Options array.
	 */
	private function render_position_settings( $options ): void {
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Automatyczne wyświetlanie', 'side-tiles-menu' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[auto_display]" value="1" <?php checked( $options['auto_display'] ?? true ); ?>>
						<?php esc_html_e( 'Wyświetlaj kafelki automatycznie na wszystkich stronach', 'side-tiles-menu' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Jeśli wyłączone, musisz użyć shortcode [side_tiles] lub bloku Gutenberg aby wyświetlić kafelki', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Pozycja', 'side-tiles-menu' ); ?></th>
				<td>
					<label>
						<input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[position]" value="left" <?php checked( $options['position'] ?? 'right', 'left' ); ?>>
						<?php esc_html_e( 'Lewa krawędź', 'side-tiles-menu' ); ?>
					</label><br>
					<label>
						<input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[position]" value="right" <?php checked( $options['position'] ?? 'right', 'right' ); ?>>
						<?php esc_html_e( 'Prawa krawędź', 'side-tiles-menu' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Offset od góry', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[offset_top]" value="<?php echo esc_attr( $options['offset_top'] ?? 100 ); ?>" min="0" class="small-text">
					<select name="<?php echo esc_attr( $this->option_name ); ?>[offset_top_unit]">
						<option value="px" <?php selected( $options['offset_top_unit'] ?? 'px', 'px' ); ?>>px</option>
						<option value="%" <?php selected( $options['offset_top_unit'] ?? 'px', '%' ); ?>>%</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Offset od dołu', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[offset_bottom]" value="<?php echo esc_attr( $options['offset_bottom'] ?? '' ); ?>" min="0" class="small-text" placeholder="<?php esc_attr_e( 'Opcjonalnie', 'side-tiles-menu' ); ?>">
					<select name="<?php echo esc_attr( $this->option_name ); ?>[offset_bottom_unit]">
						<option value="px" <?php selected( $options['offset_bottom_unit'] ?? 'px', 'px' ); ?>>px</option>
						<option value="%" <?php selected( $options['offset_bottom_unit'] ?? 'px', '%' ); ?>>%</option>
					</select>
					<p class="description"><?php esc_html_e( 'Jeśli ustawione, kafelki będą ograniczone do tego obszaru', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Tryb sticky', 'side-tiles-menu' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[sticky]" value="1" <?php checked( $options['sticky'] ?? false ); ?>>
						<?php esc_html_e( 'Włącz tryb sticky (kafelki będą podążać za scrollowaniem)', 'side-tiles-menu' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render size settings
	 *
	 * @param array $options Options array.
	 */
	private function render_size_settings( $options ): void {
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Szerokość kafelka', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[tile_width]" value="<?php echo esc_attr( $options['tile_width'] ?? 60 ); ?>" min="1" class="small-text">
					<select name="<?php echo esc_attr( $this->option_name ); ?>[tile_width_unit]">
						<option value="px" <?php selected( $options['tile_width_unit'] ?? 'px', 'px' ); ?>>px</option>
						<option value="%" <?php selected( $options['tile_width_unit'] ?? 'px', '%' ); ?>>%</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Wysokość kafelka', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[tile_height]" value="<?php echo esc_attr( $options['tile_height'] ?? 60 ); ?>" min="1" class="small-text">
					<select name="<?php echo esc_attr( $this->option_name ); ?>[tile_height_unit]">
						<option value="px" <?php selected( $options['tile_height_unit'] ?? 'px', 'px' ); ?>>px</option>
						<option value="%" <?php selected( $options['tile_height_unit'] ?? 'px', '%' ); ?>>%</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Odstęp między kafelkami', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[tile_spacing]" value="<?php echo esc_attr( $options['tile_spacing'] ?? 10 ); ?>" min="0" class="small-text">
					<select name="<?php echo esc_attr( $this->option_name ); ?>[tile_spacing_unit]">
						<option value="px" <?php selected( $options['tile_spacing_unit'] ?? 'px', 'px' ); ?>>px</option>
						<option value="%" <?php selected( $options['tile_spacing_unit'] ?? 'px', '%' ); ?>>%</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Kształt kafelka', 'side-tiles-menu' ); ?></th>
				<td>
					<select name="<?php echo esc_attr( $this->option_name ); ?>[tile_shape]" id="tile-shape-select">
						<option value="square" <?php selected( $options['tile_shape'] ?? 'rounded', 'square' ); ?>><?php esc_html_e( 'Kwadrat', 'side-tiles-menu' ); ?></option>
						<option value="rounded" <?php selected( $options['tile_shape'] ?? 'rounded', 'rounded' ); ?>><?php esc_html_e( 'Zaokrąglony', 'side-tiles-menu' ); ?></option>
						<option value="circle" <?php selected( $options['tile_shape'] ?? 'rounded', 'circle' ); ?>><?php esc_html_e( 'Koło', 'side-tiles-menu' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="border-radius-row" style="display: <?php echo ( $options['tile_shape'] ?? 'rounded' ) === 'rounded' ? 'table-row' : 'none'; ?>;">
				<th scope="row"><?php esc_html_e( 'Promień narożników', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[border_radius]" value="<?php echo esc_attr( $options['border_radius'] ?? 8 ); ?>" min="0" class="small-text"> px
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render style settings
	 *
	 * @param array $options Options array.
	 */
	private function render_style_settings( $options ): void {
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Kolor tła', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[bg_color]" value="<?php echo esc_attr( $options['bg_color'] ?? '#333333' ); ?>" class="color-picker">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Kolor tekstu/ikon', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[text_color]" value="<?php echo esc_attr( $options['text_color'] ?? '#ffffff' ); ?>" class="color-picker">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Gradient', 'side-tiles-menu' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[use_gradient]" value="1" <?php checked( $options['use_gradient'] ?? false ); ?> id="use-gradient-checkbox">
						<?php esc_html_e( 'Użyj gradientu zamiast jednolitego koloru', 'side-tiles-menu' ); ?>
					</label>
				</td>
			</tr>
			<tr class="gradient-settings" style="display: <?php echo ( $options['use_gradient'] ?? false ) ? 'table-row' : 'none'; ?>;">
				<th scope="row"><?php esc_html_e( 'Gradient - kolor początkowy', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[gradient_start]" value="<?php echo esc_attr( $options['gradient_start'] ?? '#333333' ); ?>" class="color-picker">
				</td>
			</tr>
			<tr class="gradient-settings" style="display: <?php echo ( $options['use_gradient'] ?? false ) ? 'table-row' : 'none'; ?>;">
				<th scope="row"><?php esc_html_e( 'Gradient - kolor końcowy', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[gradient_end]" value="<?php echo esc_attr( $options['gradient_end'] ?? '#666666' ); ?>" class="color-picker">
				</td>
			</tr>
			<tr class="gradient-settings" style="display: <?php echo ( $options['use_gradient'] ?? false ) ? 'table-row' : 'none'; ?>;">
				<th scope="row"><?php esc_html_e( 'Gradient - kąt', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[gradient_angle]" value="<?php echo esc_attr( $options['gradient_angle'] ?? 135 ); ?>" min="0" max="360" class="small-text"> °
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Obramowanie', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[border_width]" value="<?php echo esc_attr( $options['border_width'] ?? 0 ); ?>" min="0" max="20" class="small-text"> px
					<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[border_color]" value="<?php echo esc_attr( $options['border_color'] ?? '#000000' ); ?>" class="color-picker">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Cień', 'side-tiles-menu' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[shadow_enabled]" value="1" <?php checked( $options['shadow_enabled'] ?? true ); ?> id="shadow-enabled-checkbox">
						<?php esc_html_e( 'Włącz cień', 'side-tiles-menu' ); ?>
					</label>
				</td>
			</tr>
			<tr class="shadow-settings" style="display: <?php echo ( $options['shadow_enabled'] ?? true ) ? 'table-row' : 'none'; ?>;">
				<th scope="row"><?php esc_html_e( 'Cień - parametry', 'side-tiles-menu' ); ?></th>
				<td>
					X: <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[shadow_x]" value="<?php echo esc_attr( $options['shadow_x'] ?? 0 ); ?>" class="small-text"> px
					Y: <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[shadow_y]" value="<?php echo esc_attr( $options['shadow_y'] ?? 2 ); ?>" class="small-text"> px
					Blur: <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[shadow_blur]" value="<?php echo esc_attr( $options['shadow_blur'] ?? 8 ); ?>" min="0" class="small-text"> px<br>
					Kolor: <input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[shadow_color]" value="<?php echo esc_attr( $options['shadow_color'] ?? 'rgba(0,0,0,0.3)' ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Można użyć rgba dla przezroczystości, np. rgba(0,0,0,0.3)', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Kolor tła hover', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[hover_bg_color]" value="<?php echo esc_attr( $options['hover_bg_color'] ?? '#555555' ); ?>" class="color-picker">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Skalowanie hover', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[hover_scale]" value="<?php echo esc_attr( $options['hover_scale'] ?? 1.1 ); ?>" min="0.5" max="2" step="0.1" class="small-text">
					<p class="description"><?php esc_html_e( '1.0 = brak skalowania, 1.1 = 110% rozmiaru', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Z-index', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[z_index]" value="<?php echo esc_attr( $options['z_index'] ?? 9999 ); ?>" min="0" class="small-text">
					<p class="description"><?php esc_html_e( 'Określa kolejność nakładania elementów (wyższa wartość = na wierzchu)', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render animation settings
	 *
	 * @param array $options Options array.
	 */
	private function render_animation_settings( $options ): void {
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Typ animacji', 'side-tiles-menu' ); ?></th>
				<td>
					<select name="<?php echo esc_attr( $this->option_name ); ?>[animation_type]">
						<option value="none" <?php selected( $options['animation_type'] ?? 'slide', 'none' ); ?>><?php esc_html_e( 'Brak', 'side-tiles-menu' ); ?></option>
						<option value="slide" <?php selected( $options['animation_type'] ?? 'slide', 'slide' ); ?>><?php esc_html_e( 'Wysuwanie', 'side-tiles-menu' ); ?></option>
						<option value="fade" <?php selected( $options['animation_type'] ?? 'slide', 'fade' ); ?>><?php esc_html_e( 'Fade', 'side-tiles-menu' ); ?></option>
						<option value="scale" <?php selected( $options['animation_type'] ?? 'slide', 'scale' ); ?>><?php esc_html_e( 'Skalowanie', 'side-tiles-menu' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Czas trwania animacji', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[animation_duration]" value="<?php echo esc_attr( $options['animation_duration'] ?? 300 ); ?>" min="0" step="50" class="small-text"> ms
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Opóźnienie animacji', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[animation_delay]" value="<?php echo esc_attr( $options['animation_delay'] ?? 0 ); ?>" min="0" step="50" class="small-text"> ms
					<p class="description"><?php esc_html_e( 'Opóźnienie przed rozpoczęciem animacji (każdy kolejny kafelek będzie miał dodatkowe opóźnienie)', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render visibility settings
	 *
	 * @param array $options Options array.
	 */
	private function render_visibility_settings( $options ): void {
		$pages      = get_pages();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Widoczność na urządzeniach', 'side-tiles-menu' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[desktop_enabled]" value="1" <?php checked( $options['desktop_enabled'] ?? true ); ?>>
						<?php esc_html_e( 'Desktop', 'side-tiles-menu' ); ?>
					</label><br>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[tablet_enabled]" value="1" <?php checked( $options['tablet_enabled'] ?? true ); ?>>
						<?php esc_html_e( 'Tablet', 'side-tiles-menu' ); ?>
					</label><br>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[mobile_enabled]" value="1" <?php checked( $options['mobile_enabled'] ?? true ); ?>>
						<?php esc_html_e( 'Mobile', 'side-tiles-menu' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Breakpointy', 'side-tiles-menu' ); ?></th>
				<td>
					Desktop (min): <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[desktop_breakpoint]" value="<?php echo esc_attr( $options['desktop_breakpoint'] ?? 1024 ); ?>" min="0" class="small-text"> px<br>
					Tablet (min): <input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[tablet_breakpoint]" value="<?php echo esc_attr( $options['tablet_breakpoint'] ?? 768 ); ?>" min="0" class="small-text"> px
					<p class="description"><?php esc_html_e( 'Mobile: 0 - tablet breakpoint, Tablet: tablet breakpoint - desktop breakpoint, Desktop: desktop breakpoint+', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Wyklucz strony', 'side-tiles-menu' ); ?></th>
				<td>
					<select name="<?php echo esc_attr( $this->option_name ); ?>[exclude_pages][]" multiple size="10" style="width: 100%; max-width: 400px;">
						<?php foreach ( $pages as $page ) : ?>
							<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( in_array( $page->ID, $options['exclude_pages'] ?? array(), true ) ); ?>>
								<?php echo esc_html( $page->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Wybierz strony, na których kafelki nie będą wyświetlane (Ctrl+klik aby zaznaczyć wiele)', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Wyklucz typy wpisów', 'side-tiles-menu' ); ?></th>
				<td>
					<?php foreach ( $post_types as $post_type ) : ?>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[exclude_post_types][]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $options['exclude_post_types'] ?? array(), true ) ); ?>>
							<?php echo esc_html( $post_type->label ); ?>
						</label><br>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Widoczność dla użytkowników', 'side-tiles-menu' ); ?></th>
				<td>
					<select name="<?php echo esc_attr( $this->option_name ); ?>[show_logged_in]">
						<option value="both" <?php selected( $options['show_logged_in'] ?? 'both', 'both' ); ?>><?php esc_html_e( 'Wszyscy użytkownicy', 'side-tiles-menu' ); ?></option>
						<option value="logged_in" <?php selected( $options['show_logged_in'] ?? 'both', 'logged_in' ); ?>><?php esc_html_e( 'Tylko zalogowani', 'side-tiles-menu' ); ?></option>
						<option value="logged_out" <?php selected( $options['show_logged_in'] ?? 'both', 'logged_out' ); ?>><?php esc_html_e( 'Tylko niezalogowani', 'side-tiles-menu' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render analytics settings
	 *
	 * @param array $options Options array.
	 */
	private function render_analytics_settings( $options ): void {
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Integracja z Google Analytics 4', 'side-tiles-menu' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[ga4_enabled]" value="1" <?php checked( $options['ga4_enabled'] ?? false ); ?> id="ga4-enabled-checkbox">
						<?php esc_html_e( 'Włącz wysyłanie zdarzeń do GA4', 'side-tiles-menu' ); ?>
					</label>
				</td>
			</tr>
			<tr class="ga4-settings" style="display: <?php echo ( $options['ga4_enabled'] ?? false ) ? 'table-row' : 'none'; ?>;">
				<th scope="row"><?php esc_html_e( 'Measurement ID', 'side-tiles-menu' ); ?></th>
				<td>
					<input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[ga4_measurement_id]" value="<?php echo esc_attr( $options['ga4_measurement_id'] ?? '' ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
					<p class="description"><?php esc_html_e( 'Twoje Google Analytics 4 Measurement ID', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render custom settings
	 *
	 * @param array $options Options array.
	 */
	private function render_custom_settings( $options ): void {
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Własny CSS', 'side-tiles-menu' ); ?></th>
				<td>
					<textarea name="<?php echo esc_attr( $this->option_name ); ?>[custom_css]" rows="10" class="large-text code"><?php echo esc_textarea( $options['custom_css'] ?? '' ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Dodaj własny CSS do personalizacji wyglądu kafelków', 'side-tiles-menu' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render tiles management page
	 */
	public function render_tiles_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = get_option( $this->option_name, array() );
		$tiles   = $options['tiles'] ?? array();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Zarządzanie kafelkami', 'side-tiles-menu' ); ?></h1>

			<button type="button" class="button button-primary" id="add-new-tile"><?php esc_html_e( 'Dodaj nowy kafelek', 'side-tiles-menu' ); ?></button>

			<table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
				<thead>
					<tr>
						<th style="width: 60px;"><?php esc_html_e( 'Ikona', 'side-tiles-menu' ); ?></th>
						<th><?php esc_html_e( 'Tytuł', 'side-tiles-menu' ); ?></th>
						<th><?php esc_html_e( 'Typ treści', 'side-tiles-menu' ); ?></th>
						<th><?php esc_html_e( 'Link', 'side-tiles-menu' ); ?></th>
						<th><?php esc_html_e( 'Kolejność', 'side-tiles-menu' ); ?></th>
						<th style="width: 100px;"><?php esc_html_e( 'Akcje', 'side-tiles-menu' ); ?></th>
					</tr>
				</thead>
				<tbody id="tiles-list">
					<?php if ( empty( $tiles ) ) : ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'Brak kafelków. Kliknij "Dodaj nowy kafelek" aby rozpocząć.', 'side-tiles-menu' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $tiles as $tile ) : ?>
							<?php $this->render_tile_row( $tile ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Tile Editor Modal -->
		<div id="tile-editor-modal" class="side-tiles-modal" style="display: none;">
			<div class="side-tiles-modal-content">
				<span class="side-tiles-modal-close">&times;</span>
				<h2 id="tile-editor-title"><?php esc_html_e( 'Edytuj kafelek', 'side-tiles-menu' ); ?></h2>

				<form id="tile-editor-form">
					<input type="hidden" id="tile-id" name="tile_id" value="">
					<?php wp_nonce_field( 'side_tiles_menu_nonce', 'side_tiles_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th><label for="tile-title"><?php esc_html_e( 'Tytuł/Tooltip', 'side-tiles-menu' ); ?></label></th>
							<td><input type="text" id="tile-title" name="title" class="regular-text" required></td>
						</tr>
						<tr>
							<th><label><?php esc_html_e( 'Typ treści', 'side-tiles-menu' ); ?></label></th>
							<td>
								<label><input type="radio" name="content_type" value="svg" checked> <?php esc_html_e( 'Ikona SVG', 'side-tiles-menu' ); ?></label>
								<label><input type="radio" name="content_type" value="image"> <?php esc_html_e( 'Obraz', 'side-tiles-menu' ); ?></label>
								<label><input type="radio" name="content_type" value="text"> <?php esc_html_e( 'Tekst', 'side-tiles-menu' ); ?></label>
							</td>
						</tr>
						<tr class="content-field svg-field">
							<th><label for="tile-svg"><?php esc_html_e( 'Kod SVG', 'side-tiles-menu' ); ?></label></th>
							<td><textarea id="tile-svg" name="svg_code" rows="5" class="large-text code"></textarea></td>
						</tr>
						<tr class="content-field image-field" style="display: none;">
							<th><label for="tile-image"><?php esc_html_e( 'URL obrazu', 'side-tiles-menu' ); ?></label></th>
							<td>
								<input type="text" id="tile-image" name="image_url" class="regular-text">
								<button type="button" class="button upload-image-button"><?php esc_html_e( 'Wybierz obraz', 'side-tiles-menu' ); ?></button>
							</td>
						</tr>
						<tr class="content-field text-field" style="display: none;">
							<th><label for="tile-text"><?php esc_html_e( 'Tekst', 'side-tiles-menu' ); ?></label></th>
							<td><input type="text" id="tile-text" name="text_content" class="regular-text"></td>
						</tr>
						<tr>
							<th><label for="tile-link"><?php esc_html_e( 'Link (URL)', 'side-tiles-menu' ); ?></label></th>
							<td>
								<input type="url" id="tile-link" name="link_url" class="regular-text" placeholder="<?php esc_attr_e( 'Opcjonalnie - pozostaw puste dla kafelka bez linku', 'side-tiles-menu' ); ?>">
								<p class="description"><?php esc_html_e( 'Jeśli pozostawisz puste, kafelek będzie tylko dekoracyjny (bez linku)', 'side-tiles-menu' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="tile-target"><?php esc_html_e( 'Cel linku', 'side-tiles-menu' ); ?></label></th>
							<td>
								<select id="tile-target" name="link_target">
									<option value="_self"><?php esc_html_e( 'Ta sama karta', 'side-tiles-menu' ); ?></option>
									<option value="_blank"><?php esc_html_e( 'Nowa karta', 'side-tiles-menu' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="tile-order"><?php esc_html_e( 'Kolejność', 'side-tiles-menu' ); ?></label></th>
							<td><input type="number" id="tile-order" name="order" value="0" min="0" class="small-text"></td>
						</tr>
						<tr>
							<th><label for="tile-aria-label"><?php esc_html_e( 'ARIA Label', 'side-tiles-menu' ); ?></label></th>
							<td><input type="text" id="tile-aria-label" name="aria_label" class="regular-text"></td>
						</tr>
					</table>

					<p class="submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Zapisz kafelek', 'side-tiles-menu' ); ?></button>
						<button type="button" class="button" id="cancel-tile-edit"><?php esc_html_e( 'Anuluj', 'side-tiles-menu' ); ?></button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render single tile row
	 *
	 * @param array $tile Tile data.
	 */
	private function render_tile_row( $tile ): void {
		$tile_id = $tile['id'] ?? '';
		?>
		<tr data-tile-id="<?php echo esc_attr( $tile_id ); ?>">
			<td>
				<?php if ( 'svg' === ( $tile['content_type'] ?? 'svg' ) && ! empty( $tile['svg_code'] ) ) : ?>
					<div style="width: 40px; height: 40px;"><?php echo wp_kses_post( $tile['svg_code'] ); ?></div>
				<?php elseif ( 'image' === ( $tile['content_type'] ?? 'svg' ) && ! empty( $tile['image_url'] ) ) : ?>
					<img src="<?php echo esc_url( $tile['image_url'] ); ?>" alt="" style="width: 40px; height: 40px; object-fit: cover;">
				<?php elseif ( 'text' === ( $tile['content_type'] ?? 'svg' ) && ! empty( $tile['text_content'] ) ) : ?>
					<strong><?php echo esc_html( $tile['text_content'] ); ?></strong>
				<?php endif; ?>
			</td>
			<td><?php echo esc_html( $tile['title'] ?? '' ); ?></td>
			<td>
				<?php
				$content_types = array(
					'svg'   => __( 'Ikona SVG', 'side-tiles-menu' ),
					'image' => __( 'Obraz', 'side-tiles-menu' ),
					'text'  => __( 'Tekst', 'side-tiles-menu' ),
				);
				echo esc_html( $content_types[ $tile['content_type'] ?? 'svg' ] ?? '' );
				?>
			</td>
			<td><?php echo esc_html( $tile['link_url'] ?? '' ); ?></td>
			<td><?php echo esc_html( $tile['order'] ?? 0 ); ?></td>
			<td>
				<button type="button" class="button button-small edit-tile" data-tile-id="<?php echo esc_attr( $tile_id ); ?>"><?php esc_html_e( 'Edytuj', 'side-tiles-menu' ); ?></button>
				<button type="button" class="button button-small delete-tile" data-tile-id="<?php echo esc_attr( $tile_id ); ?>"><?php esc_html_e( 'Usuń', 'side-tiles-menu' ); ?></button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render tiles list HTML (for AJAX refresh)
	 *
	 * @param array $tiles Tiles array.
	 * @return string
	 */
	private function render_tiles_list( $tiles ): string {
		ob_start();

		if ( empty( $tiles ) ) {
			?>
			<tr>
				<td colspan="6"><?php esc_html_e( 'Brak kafelków. Kliknij "Dodaj nowy kafelek" aby rozpocząć.', 'side-tiles-menu' ); ?></td>
			</tr>
			<?php
		} else {
			foreach ( $tiles as $tile ) {
				$this->render_tile_row( $tile );
			}
		}

		return ob_get_clean();
	}

	/**
	 * Render stats page
	 */
	public function render_stats_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = get_option( $this->option_name, array() );
		$tiles   = $options['tiles'] ?? array();
		$clicks  = get_option( 'side_tiles_menu_clicks', array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Statystyki kliknięć', 'side-tiles-menu' ); ?></h1>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Kafelek', 'side-tiles-menu' ); ?></th>
						<th><?php esc_html_e( 'Liczba kliknięć', 'side-tiles-menu' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $clicks ) ) : ?>
						<tr>
							<td colspan="2"><?php esc_html_e( 'Brak danych statystycznych.', 'side-tiles-menu' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $tiles as $tile ) : ?>
							<?php
							$tile_id = $tile['id'] ?? '';
							$count   = $clicks[ $tile_id ] ?? 0;
							?>
							<tr>
								<td><?php echo esc_html( $tile['title'] ?? '' ); ?></td>
								<td><?php echo esc_html( $count ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * AJAX: Save tile
	 */
	public function ajax_save_tile(): void {
		// Enable error logging for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Side Tiles Menu: ajax_save_tile called' );
			error_log( 'POST data: ' . print_r( $_POST, true ) );
		}

		// Check nonce
		if ( ! isset( $_POST['side_tiles_nonce'] ) || ! wp_verify_nonce( $_POST['side_tiles_nonce'], 'side_tiles_menu_nonce' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Side Tiles Menu: Nonce verification failed' );
				error_log( 'Expected nonce field: side_tiles_nonce' );
				error_log( 'POST keys: ' . implode( ', ', array_keys( $_POST ) ) );
			}
			wp_send_json_error(
				array(
					'message' => __( 'Błąd weryfikacji bezpieczeństwa. Odśwież stronę i spróbuj ponownie.', 'side-tiles-menu' ),
					'debug'   => 'nonce_failed',
				)
			);
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Side Tiles Menu: Permission denied' );
			}
			wp_send_json_error(
				array(
					'message' => __( 'Brak uprawnień', 'side-tiles-menu' ),
					'debug'   => 'permission_denied',
				)
			);
		}

		// Validate required fields
		if ( empty( $_POST['title'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Tytuł kafelka jest wymagany', 'side-tiles-menu' ),
					'debug'   => 'title_required',
				)
			);
		}

		try {
			$tile_id = sanitize_text_field( $_POST['tile_id'] ?? '' );
			$tile    = array(
				'id'           => $tile_id ?: uniqid( 'tile_', true ),
				'title'        => sanitize_text_field( $_POST['title'] ?? '' ),
				'content_type' => in_array( $_POST['content_type'] ?? '', array( 'svg', 'image', 'text' ), true ) ? $_POST['content_type'] : 'svg',
				'svg_code'     => wp_kses_post( $_POST['svg_code'] ?? '' ),
				'image_url'    => esc_url_raw( $_POST['image_url'] ?? '' ),
				'text_content' => sanitize_text_field( $_POST['text_content'] ?? '' ),
				'link_url'     => esc_url_raw( $_POST['link_url'] ?? '' ),
				'link_target'  => in_array( $_POST['link_target'] ?? '', array( '_self', '_blank' ), true ) ? $_POST['link_target'] : '_self',
				'order'        => absint( $_POST['order'] ?? 0 ),
				'aria_label'   => sanitize_text_field( $_POST['aria_label'] ?? '' ),
			);

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Side Tiles Menu: Tile data prepared: ' . print_r( $tile, true ) );
			}

			$options = get_option( $this->option_name, array() );
			$tiles   = $options['tiles'] ?? array();

			// Update or add tile
			$found = false;
			foreach ( $tiles as $index => $existing_tile ) {
				if ( $existing_tile['id'] === $tile['id'] ) {
					$tiles[ $index ] = $tile;
					$found           = true;
					break;
				}
			}

			if ( ! $found ) {
				$tiles[] = $tile;
			}

			// Sort by order
			usort(
				$tiles,
				function ( $a, $b ) {
					return ( $a['order'] ?? 0 ) - ( $b['order'] ?? 0 );
				}
			);

			$options['tiles'] = $tiles;

			// Force update by deleting first (ensures fresh save)
			delete_option( $this->option_name );
			$result = add_option( $this->option_name, $options, '', 'no' );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Side Tiles Menu: Add option result: ' . ( $result ? 'success' : 'failed' ) );
				// Verify it was actually saved
				$verify = get_option( $this->option_name );
				error_log( 'Side Tiles Menu: Verification - tiles count: ' . count( $verify['tiles'] ?? array() ) );
			}

			// Always return success if we got here (even if update_option returns false,
			// it might have saved correctly - WP returns false if value didn't change)
			wp_send_json_success(
				array(
					'message'    => __( 'Kafelek zapisany pomyślnie', 'side-tiles-menu' ),
					'tile'       => $tile,
					'tiles_html' => $this->render_tiles_list( $tiles ),
					'debug'      => array(
						'tile_id'     => $tile['id'],
						'saved'       => $result,
						'tiles_count' => count( $tiles ),
					),
				)
			);
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Side Tiles Menu: Exception: ' . $e->getMessage() );
			}
			wp_send_json_error(
				array(
					'message' => __( 'Błąd podczas zapisywania kafelka: ', 'side-tiles-menu' ) . $e->getMessage(),
					'debug'   => 'exception',
				)
			);
		}
	}

	/**
	 * AJAX: Delete tile
	 */
	public function ajax_delete_tile(): void {
		check_ajax_referer( 'side_tiles_menu_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak uprawnień', 'side-tiles-menu' ) ) );
		}

		$tile_id = sanitize_text_field( $_POST['tile_id'] ?? '' );

		$options = get_option( $this->option_name, array() );
		$tiles   = $options['tiles'] ?? array();

		$tiles = array_filter(
			$tiles,
			function ( $tile ) use ( $tile_id ) {
				return $tile['id'] !== $tile_id;
			}
		);

		$options['tiles'] = array_values( $tiles );
		update_option( $this->option_name, $options );

		wp_send_json_success( array( 'message' => __( 'Kafelek usunięty pomyślnie', 'side-tiles-menu' ) ) );
	}

	/**
	 * AJAX: Export settings
	 */
	public function ajax_export_settings(): void {
		check_ajax_referer( 'side_tiles_menu_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak uprawnień', 'side-tiles-menu' ) ) );
		}

		$options = get_option( $this->option_name, array() );

		wp_send_json_success(
			array(
				'data'     => $options,
				'filename' => 'side-tiles-menu-settings-' . gmdate( 'Y-m-d' ) . '.json',
			)
		);
	}

	/**
	 * AJAX: Import settings
	 */
	public function ajax_import_settings(): void {
		check_ajax_referer( 'side_tiles_menu_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak uprawnień', 'side-tiles-menu' ) ) );
		}

		$json_data = sanitize_textarea_field( $_POST['json_data'] ?? '' );

		if ( empty( $json_data ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak danych do importu', 'side-tiles-menu' ) ) );
		}

		$data = json_decode( $json_data, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( array( 'message' => __( 'Nieprawidłowy format JSON', 'side-tiles-menu' ) ) );
		}

		$sanitized = $this->sanitize_settings( $data );
		update_option( $this->option_name, $sanitized );

		wp_send_json_success( array( 'message' => __( 'Ustawienia zaimportowane pomyślnie', 'side-tiles-menu' ) ) );
	}
}
