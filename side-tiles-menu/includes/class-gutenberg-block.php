<?php
/**
 * Gutenberg Block class
 *
 * @package SideTilesMenu
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Side Tiles Menu Gutenberg Block class
 */
class Side_Tiles_Menu_Gutenberg_Block {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register Gutenberg block
	 */
	public function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'side-tiles-menu/tiles',
			array(
				'editor_script'   => 'side-tiles-menu-block',
				'editor_style'    => 'side-tiles-menu-block-editor',
				'style'           => 'side-tiles-menu-frontend',
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'tileId' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);

		wp_register_script(
			'side-tiles-menu-block',
			SIDE_TILES_MENU_PLUGIN_URL . 'admin/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			SIDE_TILES_MENU_VERSION,
			true
		);

		wp_register_style(
			'side-tiles-menu-block-editor',
			SIDE_TILES_MENU_PLUGIN_URL . 'admin/css/block-editor.css',
			array( 'wp-edit-blocks' ),
			SIDE_TILES_MENU_VERSION
		);

		// Pass tiles data to block editor
		$options = get_option( 'side_tiles_menu_options', array() );
		$tiles   = $options['tiles'] ?? array();

		wp_localize_script(
			'side-tiles-menu-block',
			'sideTilesMenuBlock',
			array(
				'tiles' => $tiles,
			)
		);
	}

	/**
	 * Render block callback
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ): string {
		$tile_id = $attributes['tileId'] ?? '';

		if ( empty( $tile_id ) ) {
			return side_tiles_menu()->frontend->render();
		}

		return side_tiles_menu()->frontend->render_single_tile( $tile_id );
	}
}
