<?php
/**
 * Style Guide Tokens admin page (read-only — Phase B3.2a).
 *
 * Registers: Appearance → Style Guide Tokens
 *
 * Reads the active child theme's theme.json and renders a diagnostic view of
 * every token the Style Guide cares about. No write paths yet — Phase B3.2b
 * will add palette/typography/spacing edits, B3.2c will add the custom-tokens,
 * B3.2d will add a custom.css editor.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CCC_Style_Guide_Admin {

	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_ccc_sg_save_palette',       array( __CLASS__, 'handle_save_palette' ) );
		add_action( 'admin_post_ccc_sg_delete_palette_pair', array( __CLASS__, 'handle_delete_palette_pair' ) );
		add_action( 'admin_post_ccc_sg_save_typography',    array( __CLASS__, 'handle_save_typography' ) );
		add_action( 'admin_post_ccc_sg_save_spacing',       array( __CLASS__, 'handle_save_spacing' ) );
		add_action( 'admin_post_ccc_sg_save_shadows',       array( __CLASS__, 'handle_save_shadows' ) );
		add_action( 'admin_post_ccc_sg_save_custom_tokens', array( __CLASS__, 'handle_save_custom_tokens' ) );
		add_action( 'admin_post_ccc_sg_save_custom_css',    array( __CLASS__, 'handle_save_custom_css' ) );
		add_action( 'admin_post_ccc_sg_save_fonts',         array( __CLASS__, 'handle_save_fonts' ) );
		add_action( 'admin_post_ccc_sg_save_texture',       array( __CLASS__, 'handle_save_texture' ) );
		add_action( 'admin_post_ccc_sg_delete_texture',     array( __CLASS__, 'handle_delete_texture' ) );
	}

	public static function register_menu() {
		$hook = add_theme_page(
			__( 'Style Guide Tokens', 'classic-city-core' ),
			__( 'Style Guide Tokens', 'classic-city-core' ),
			'manage_options',
			'style-guide-tokens',
			array( __CLASS__, 'render_page' )
		);
		// Enqueue media-library JS only on our page — needed by the Textures
		// "Choose image" picker.
		add_action( 'admin_print_scripts-' . $hook, array( __CLASS__, 'enqueue_media_picker' ) );
	}

	public static function enqueue_media_picker() {
		wp_enqueue_media();
	}

	/**
	 * Load the active stylesheet's theme.json. Returns the decoded array plus
	 * resolution metadata so the page can show a helpful error state if the
	 * active theme isn't an sg-{slug} child.
	 */
	private static function load_theme_json() {
		$path = get_stylesheet_directory() . '/theme.json';
		if ( ! file_exists( $path ) ) {
			return array( 'error' => "theme.json not found at {$path}" );
		}
		$raw = file_get_contents( $path );
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			return array( 'error' => "Invalid JSON at {$path}" );
		}
		return array(
			'data' => $data,
			'path' => $path,
		);
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}

		$loaded = self::load_theme_json();
		$theme  = wp_get_theme();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Style Guide Tokens', 'classic-city-core' ) . '</h1>';

		echo '<p style="margin:0 0 1.5em;color:#555;">';
		printf(
			/* translators: 1: theme name, 2: theme slug */
			esc_html__( 'Active theme: %1$s (%2$s). Palette is editable; other sections are read-only for now.', 'classic-city-core' ),
			'<strong>' . esc_html( $theme->get( 'Name' ) ) . '</strong>',
			'<code>' . esc_html( $theme->get_stylesheet() ) . '</code>'
		);
		echo '</p>';

		if ( isset( $_GET['ccc_sg_saved'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Palette saved.', 'classic-city-core' ) . '</p></div>';
		}
		if ( isset( $_GET['ccc_sg_error'] ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( wp_unslash( $_GET['ccc_sg_error'] ) ) . '</p></div>';
		}

		if ( isset( $loaded['error'] ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $loaded['error'] ) . '</p></div>';
			echo '</div>';
			return;
		}

		$data     = $loaded['data'];
		$settings = $data['settings'] ?? array();
		$custom   = $settings['custom'] ?? array();

		// Merge parent's custom.fs into the display set so heading tokens that
		// live only on the parent (e.g. h-N-min seeded by Option C fluid
		// typography) still appear as editable rows. Child values win; on save
		// the handler writes to the child theme.json and those edits override
		// the parent for the next page load.
		$parent_fs = array();
		$parent_theme_json = get_template_directory() . '/theme.json';
		if ( $parent_theme_json !== $loaded['path'] && file_exists( $parent_theme_json ) ) {
			$parent_data = json_decode( file_get_contents( $parent_theme_json ), true );
			if ( is_array( $parent_data ) ) {
				$parent_fs = $parent_data['settings']['custom']['fs'] ?? array();
			}
		}
		$merged_fs = array_merge( $parent_fs, $custom['fs'] ?? array() );

		self::styles();

		self::render_palette( $settings['color']['palette'] ?? array(), $custom['color'] ?? array() );
		self::render_typography(
			$settings['typography']['fontSizes'] ?? array(),
			$settings['typography']['fontFamilies'] ?? array(),
			$merged_fs
		);
		self::render_spacing( $settings['spacing']['spacingSizes'] ?? array() );
		self::render_shadows( $settings['shadow']['presets'] ?? array() );
		self::render_custom_tokens( $settings['layout'] ?? array(), $custom );
		self::render_textures( $custom['textures'] ?? array() );
		self::render_custom_css();

		echo '<p class="description">' . esc_html__( 'Source file:', 'classic-city-core' ) . ' <code>' . esc_html( str_replace( ABSPATH, '', $loaded['path'] ) ) . '</code></p>';
		echo '</div>';
	}

	private static function styles() {
		echo '<style>
			.sgt-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:12px; margin:0 0 1.5em; }
			.sgt-swatch { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:10px; display:flex; align-items:center; gap:10px; }
			.sgt-swatch .chip { width:40px; height:40px; border-radius:4px; border:1px solid rgba(0,0,0,.1); flex-shrink:0; }
			.sgt-swatch code { font-size:11px; color:#555; }
			.sgt-swatch .name { font-weight:600; }
			.sgt-swatch .meta { font-size:12px; color:#666; }
			.sgt-table { width:100%; background:#fff; border:1px solid #c3c4c7; border-collapse:collapse; margin:0 0 1.5em; }
			.sgt-table th, .sgt-table td { padding:8px 12px; text-align:left; border-bottom:1px solid #f0f0f1; font-size:13px; vertical-align:middle; }
			.sgt-table th { background:#f6f7f7; font-weight:600; }
			.sgt-table tr:last-child td { border-bottom:none; }
			.sgt-inline-swatch { display:flex; gap:8px; align-items:center; }
			.sgt-chip { width:24px; height:24px; border-radius:3px; border:1px solid rgba(0,0,0,.1); flex-shrink:0; }
			/* Editable chips are <input type="color"> — strip the native chrome
			 * and the inner swatch padding so they render as a clean color
			 * rectangle matching the read-only div chip. */
			input.sgt-chip {
				-webkit-appearance: none;
				-moz-appearance: none;
				appearance: none;
				padding: 0;
				margin: 0;
				background-color: transparent;
				cursor: pointer;
			}
			input.sgt-chip::-webkit-color-swatch-wrapper { padding: 0; }
			input.sgt-chip::-webkit-color-swatch { border: 0; border-radius: 2px; }
			input.sgt-chip::-moz-color-swatch { border: 0; border-radius: 2px; }
			.sgt-hex-input, .sgt-hex-readonly { font-family:monospace; font-size:12px; width:100px; padding:2px 6px; }
			.sgt-hex-readonly { background:#f6f7f7; color:#555; }
			.sgt-section { margin:0 0 2em; }
			.sgt-section h2 { font-size:1.1em; margin:0 0 .5em; }
			.sgt-empty { color:#999; font-style:italic; }
		</style>';
	}

	/**
	 * Group a flat palette into pairs keyed by base slug. For each non-`-alt`
	 * entry we look up its `{slug}-alt` partner. Orphan bases (no alt) and
	 * orphan alts (no base) both pass through — orphan bases get treated as
	 * pairs with auto-derived alts on save.
	 *
	 * @return array [ base_slug => [ 'base' => entry|null, 'alt' => entry|null ] ]
	 */
	private static function group_palette_into_pairs( $palette ) {
		$by_slug = array();
		foreach ( $palette as $c ) {
			$slug = $c['slug'] ?? '';
			if ( $slug === '' ) continue;
			$by_slug[ $slug ] = $c;
		}

		$pairs = array();
		foreach ( $by_slug as $slug => $c ) {
			if ( substr( $slug, -4 ) === '-alt' ) continue;
			$pairs[ $slug ] = array(
				'base' => $c,
				'alt'  => $by_slug[ $slug . '-alt' ] ?? null,
			);
		}
		// Surface orphan alts (alt without a base) so they're not silently dropped.
		foreach ( $by_slug as $slug => $c ) {
			if ( substr( $slug, -4 ) !== '-alt' ) continue;
			$base_slug = substr( $slug, 0, -4 );
			if ( ! isset( $pairs[ $base_slug ] ) ) {
				$pairs[ $base_slug ] = array( 'base' => null, 'alt' => $c );
			}
		}
		return $pairs;
	}

	private static function render_palette( $palette, $opposites ) {
		echo '<div class="sgt-section"><h2>' . esc_html__( 'Color palette', 'classic-city-core' ) . '</h2>';

		echo '<p class="description" style="margin:0 0 1em;">';
		echo esc_html__( 'Paired colors share a single text-opposite (base and -alt always resolve to the same opposite to keep button hovers stable). Leave alt on "auto" to re-derive it from the base hex.', 'classic-city-core' );
		echo '</p>';

		$pairs = self::group_palette_into_pairs( $palette );

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ccc_sg_save_palette">';
		wp_nonce_field( 'ccc_sg_save_palette', 'ccc_sg_nonce' );

		if ( $pairs ) {
			echo '<table class="sgt-table">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__( 'Name',      'classic-city-core' ) . '</th>';
			echo '<th>' . esc_html__( 'Base',      'classic-city-core' ) . '</th>';
			echo '<th>' . esc_html__( 'Alt',       'classic-city-core' ) . '</th>';
			echo '<th>' . esc_html__( 'Alt Auto',  'classic-city-core' ) . '</th>';
			echo '<th>' . esc_html__( 'Opposite',  'classic-city-core' ) . '</th>';
			echo '<th style="width:1%;">' . esc_html__( 'Delete', 'classic-city-core' ) . '</th>';
			echo '</tr></thead><tbody>';
			foreach ( $pairs as $base_slug => $pair ) {
				self::render_palette_pair_row( $base_slug, $pair, $opposites );
			}
			echo '</tbody></table>';
		} else {
			echo '<p class="sgt-empty">' . esc_html__( 'No palette entries yet. Add one below.', 'classic-city-core' ) . '</p>';
		}

		// Add-new-pair row (lives inside the save form so one click persists all edits + adds).
		echo '<div class="sgt-pair-add" style="margin-top:1.5em;padding:1em;border:1px dashed #c3c4c7;border-radius:4px;background:#fafafa;">';
		echo '<h3 style="margin:0 0 .5em;font-size:13px;text-transform:uppercase;letter-spacing:.04em;">' . esc_html__( 'Add new color', 'classic-city-core' ) . '</h3>';
		echo '<label style="display:inline-block;margin-right:1em;"><span style="font-size:12px;font-weight:600;">Slug</span><br><input type="text" name="new_pair_slug" value="" pattern="[a-z0-9][a-z0-9-]*" placeholder="accent" style="font-family:monospace;" /></label>';
		echo '<label style="display:inline-block;"><span style="font-size:12px;font-weight:600;">Base hex</span><br><input type="text" name="new_pair_base" value="" pattern="#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?" placeholder="#FF00AA" style="font-family:monospace;" /></label>';
		echo '<div style="color:#888;font-size:12px;margin-top:.5em;">The -alt variant is auto-derived on save; the opposite is auto-computed.</div>';
		echo '</div>';

		submit_button( __( 'Save palette', 'classic-city-core' ), 'primary', 'submit', false );
		echo '</form>';

		// Two-way sync between hex text input and color-picker chip. Typing
		// a hex updates the picker's swatch; picking a color writes the
		// #RRGGBB value back to the text input (which is what POSTs on save).
		// <input type="color"> only accepts expanded #RRGGBB — the helper
		// below expands 3-char hex to 6-char before setting the picker value.
		echo '<script>
			function sgtToHex6(v){
				v = (v || "").trim();
				if (!v) return "#000000";
				if (v.charAt(0) !== "#") v = "#" + v;
				if (/^#[0-9A-Fa-f]{3}$/.test(v)) {
					var c = v.slice(1);
					v = "#" + c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
				}
				return /^#[0-9A-Fa-f]{6}$/.test(v) ? v : "#000000";
			}
			// hex text input → color picker chip
			document.querySelectorAll(".sgt-hex-input").forEach(function(input){
				input.addEventListener("input", function(){
					var v = input.value.trim();
					if (/^#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/.test(v)) {
						var picker = document.getElementById(input.dataset.chip);
						if (picker) picker.value = sgtToHex6(v);
					}
				});
			});
			// color picker chip → hex text input
			document.querySelectorAll("input.sgt-chip").forEach(function(picker){
				picker.addEventListener("input", function(){
					var hexInput = document.querySelector(".sgt-hex-input[data-chip=\"" + picker.id + "\"]");
					if (hexInput) hexInput.value = picker.value;
				});
			});
			document.querySelectorAll(".sgt-alt-auto").forEach(function(cb){
				function sync(){
					var target = document.getElementById(cb.dataset.target);
					if (!target) return;
					target.disabled = cb.checked;
					target.style.opacity = cb.checked ? "0.5" : "1";
				}
				cb.addEventListener("change", sync);
				sync();
			});
		</script>';

		echo '</div>';
	}

	/**
	 * Normalize any hex string to the `#RRGGBB` form required by
	 * <input type="color">. Accepts `#abc`, `abc`, `#aabbcc`, `aabbcc`.
	 * Falls back to `#000000` on anything invalid or empty — the hex text
	 * input is still the source of truth, so the picker value is cosmetic.
	 */
	private static function to_hex6( $v ) {
		$v = trim( (string) $v );
		if ( $v === '' ) return '#000000';
		if ( $v[0] !== '#' ) $v = '#' . $v;
		if ( preg_match( '/^#([0-9A-Fa-f])([0-9A-Fa-f])([0-9A-Fa-f])$/', $v, $m ) ) {
			$v = '#' . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3];
		}
		return preg_match( '/^#[0-9A-Fa-f]{6}$/', $v ) ? strtolower( $v ) : '#000000';
	}

	/**
	 * Render one paired-color row as a <tr> inside the palette table.
	 * Columns: Name · Base · Alt · Alt Auto · Opposite (read-only) · Delete.
	 *
	 * Input element classes (`sgt-hex-input`, `sgt-alt-auto`) and POST field
	 * names (`pairs[{slug}][base|alt|alt_auto]`) are unchanged — the live
	 * chip preview JS and the save handler wire to those.
	 */
	private static function render_palette_pair_row( $base_slug, $pair, $opposites ) {
		$base_hex = $pair['base']['color']       ?? '';
		$alt_hex  = $pair['alt']['color']        ?? '';
		$opposite = $opposites[ $base_slug . '-opposite' ] ?? ( $opposites[ $base_slug . '-alt-opposite' ] ?? '' );

		$base_chip_id = 'sgt-pair-' . $base_slug . '-base-chip';
		$alt_chip_id  = 'sgt-pair-' . $base_slug . '-alt-chip';
		$alt_input_id = 'sgt-pair-' . $base_slug . '-alt-input';

		echo '<tr>';

		// 1. Name.
		echo '<td><strong>' . esc_html( $base_slug ) . '</strong></td>';

		// 2. Base. Chip is an <input type="color"> so the native picker opens
		//    on click. The hex text input still drives POST; the two sync via
		//    the JS below.
		echo '<td><div class="sgt-inline-swatch">';
		printf(
			'<input type="color" id="%1$s" class="sgt-chip" value="%2$s" aria-label="%3$s" />',
			esc_attr( $base_chip_id ),
			esc_attr( self::to_hex6( $base_hex ) ),
			esc_attr( sprintf( __( 'Pick %s base color', 'classic-city-core' ), $base_slug ) )
		);
		printf(
			'<input type="text" name="pairs[%1$s][base]" value="%2$s" class="sgt-hex-input" data-chip="%3$s" pattern="#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?" />',
			esc_attr( $base_slug ),
			esc_attr( $base_hex ),
			esc_attr( $base_chip_id )
		);
		echo '</div></td>';

		// 3. Alt.
		echo '<td><div class="sgt-inline-swatch">';
		printf(
			'<input type="color" id="%1$s" class="sgt-chip" value="%2$s" aria-label="%3$s" />',
			esc_attr( $alt_chip_id ),
			esc_attr( self::to_hex6( $alt_hex ) ),
			esc_attr( sprintf( __( 'Pick %s alt color', 'classic-city-core' ), $base_slug ) )
		);
		printf(
			'<input type="text" id="%4$s" name="pairs[%1$s][alt]" value="%2$s" class="sgt-hex-input" data-chip="%3$s" pattern="#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?" />',
			esc_attr( $base_slug ),
			esc_attr( $alt_hex ),
			esc_attr( $alt_chip_id ),
			esc_attr( $alt_input_id )
		);
		echo '</div></td>';

		// 4. Alt Auto toggle.
		$alt_is_current_auto = false; // Theme.json doesn't record whether the saved alt was auto-derived.
		echo '<td>';
		printf(
			'<label style="font-size:12px;"><input type="checkbox" class="sgt-alt-auto" name="pairs[%1$s][alt_auto]" value="1" data-target="%2$s"%3$s /> auto</label>',
			esc_attr( $base_slug ),
			esc_attr( $alt_input_id ),
			$alt_is_current_auto ? ' checked' : ''
		);
		echo '</td>';

		// 5. Opposite (display-only; no input name so nothing is POSTed).
		echo '<td><div class="sgt-inline-swatch">';
		if ( $opposite ) {
			printf( '<div class="sgt-chip" style="background:%s;"></div>', esc_attr( $opposite ) );
			printf( '<input type="text" value="%s" class="sgt-hex-readonly" disabled />', esc_attr( $opposite ) );
		} else {
			echo '<span style="color:#999;font-size:12px;font-style:italic;">—</span>';
		}
		echo '</div></td>';

		// 6. Delete — rendered as an <a> instead of a nested <form>. Nested
		//    forms are invalid HTML; browsers close the outer form early at
		//    the inner </form>, which historically caused this whole
		//    save-palette row to POST incorrectly (dropping pairs). The nonce
		//    travels as a query arg via wp_nonce_url and the delete handler
		//    reads from $_REQUEST so GET works.
		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'ccc_sg_delete_palette_pair',
					'slug'   => rawurlencode( $base_slug ),
				),
				admin_url( 'admin-post.php' )
			),
			'ccc_sg_delete_palette_pair_' . $base_slug,
			'ccc_sg_nonce'
		);
		echo '<td>';
		printf(
			'<a href="%1$s" class="button button-small button-link-delete" onclick="return confirm(\'%2$s\');">%3$s</a>',
			esc_url( $delete_url ),
			esc_js( sprintf( __( 'Delete the %s pair (both base and alt)?', 'classic-city-core' ), $base_slug ) ),
			esc_html__( 'Delete', 'classic-city-core' )
		);
		echo '</td>';

		echo '</tr>';
	}

	/**
	 * Save palette handler. POSTed to admin-post.php?action=ccc_sg_save_palette.
	 *
	 * Reads the current active child theme's theme.json, rebuilds the palette
	 * using user-submitted hexes, re-runs the color resolver (which enforces
	 * the -alt inherits-opposite-from-base rule), and writes the file back.
	 *
	 * Only the palette and the linked settings.custom.color block are touched —
	 * every other section (typography, spacing, custom tokens) is preserved
	 * verbatim.
	 */
	public static function handle_save_palette() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}
		check_admin_referer( 'ccc_sg_save_palette', 'ccc_sg_nonce' );

		$path = get_stylesheet_directory() . '/theme.json';
		if ( ! file_exists( $path ) ) {
			self::redirect_with_error( "theme.json not found at {$path}" );
		}

		$tj = json_decode( file_get_contents( $path ), true );
		if ( ! is_array( $tj ) ) {
			self::redirect_with_error( 'Could not parse theme.json' );
		}

		$pairs_in = isset( $_POST['pairs'] ) && is_array( $_POST['pairs'] ) ? wp_unslash( $_POST['pairs'] ) : array();

		// Build the flat colors array in the shape CCC_Client_Importer::resolve_palette
		// expects. For each submitted pair, emit a base entry and an alt entry.
		// If the alt_auto checkbox is set OR the alt hex field is blank, the
		// alt's hex is 'auto' so resolve_palette re-derives it.
		$colors_input = array();
		foreach ( $pairs_in as $base_slug => $data ) {
			$base_slug = sanitize_html_class( (string) $base_slug );
			if ( $base_slug === '' || substr( $base_slug, -4 ) === '-alt' ) continue;
			$base_hex = trim( (string) ( $data['base'] ?? '' ) );
			if ( $base_hex === '' ) continue; // blank base → treat as deleted

			$colors_input[] = array(
				'name' => $base_slug,
				'hex'  => $base_hex,
			);

			$alt_auto = ! empty( $data['alt_auto'] );
			$alt_hex  = trim( (string) ( $data['alt'] ?? '' ) );
			$colors_input[] = array(
				'name' => $base_slug . '-alt',
				'hex'  => ( $alt_auto || $alt_hex === '' ) ? 'auto' : $alt_hex,
			);
		}

		// Handle the "add new pair" fields if the user filled them.
		$new_slug = sanitize_html_class( trim( (string) wp_unslash( $_POST['new_pair_slug'] ?? '' ) ) );
		$new_hex  = trim( (string) wp_unslash( $_POST['new_pair_base'] ?? '' ) );
		if ( $new_slug !== '' && $new_hex !== '' ) {
			if ( ! preg_match( '/^[a-z0-9][a-z0-9-]*$/', $new_slug ) || substr( $new_slug, -4 ) === '-alt' ) {
				self::redirect_with_error( "Invalid new pair slug: '{$new_slug}' (lowercase letters/digits/dashes; may not end in -alt)" );
			}
			// Prevent clobbering an existing pair.
			foreach ( $colors_input as $c ) {
				if ( $c['name'] === $new_slug ) {
					self::redirect_with_error( "A pair with slug '{$new_slug}' already exists — edit it above instead of adding a new one." );
				}
			}
			$colors_input[] = array( 'name' => $new_slug,            'hex' => $new_hex );
			$colors_input[] = array( 'name' => $new_slug . '-alt',   'hex' => 'auto' );
		}

		if ( ! $colors_input ) {
			self::redirect_with_error( 'Palette would be empty — at least one pair is required.' );
		}

		$warnings = array();
		try {
			$resolved = CCC_Client_Importer::resolve_palette( $colors_input, $warnings );
		} catch ( Exception $e ) {
			self::redirect_with_error( 'Validation failed: ' . $e->getMessage() );
		}

		// Rebuild palette + opposites block.
		$new_palette = array();
		$opposites   = array();
		foreach ( $resolved as $c ) {
			$new_palette[] = array(
				'slug'  => $c['slug'],
				'name'  => $c['name'],
				'color' => $c['hex'],
			);
			$opposites[ $c['slug'] . '-opposite' ] = $c['opposite'];
		}

		$tj['settings']['color']['palette'] = $new_palette;
		// Preserve any non-color entries already in settings.custom.color
		// (future-proofing; today we only have *-opposite keys).
		$existing_custom_color = $tj['settings']['custom']['color'] ?? array();
		foreach ( $existing_custom_color as $k => $v ) {
			if ( ! preg_match( '/-opposite$/', $k ) ) {
				$opposites[ $k ] = $v;
			}
		}
		$tj['settings']['custom']['color'] = $opposites;

		$bytes = file_put_contents(
			$path,
			wp_json_encode( $tj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
		);
		if ( $bytes === false ) {
			self::redirect_with_error( 'Could not write theme.json — check file permissions.' );
		}

		wp_safe_redirect( add_query_arg( 'ccc_sg_saved', '1', admin_url( 'themes.php?page=style-guide-tokens' ) ) );
		exit;
	}

	/**
	 * Delete one paired color entry (both base and -alt) from the active
	 * child theme's palette. Per-row Delete buttons POST here.
	 */
	public static function handle_delete_palette_pair() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}
		// Read from $_REQUEST so this works when linked as a GET with nonce.
		$base_slug = sanitize_html_class( strtolower( wp_unslash( $_REQUEST['slug'] ?? '' ) ) );
		check_admin_referer( 'ccc_sg_delete_palette_pair_' . $base_slug, 'ccc_sg_nonce' );

		$path = get_stylesheet_directory() . '/theme.json';
		if ( ! file_exists( $path ) ) {
			self::redirect_with_error( 'theme.json not found' );
		}
		$tj = json_decode( file_get_contents( $path ), true );
		if ( ! is_array( $tj ) ) {
			self::redirect_with_error( 'Could not parse theme.json' );
		}

		// Drop the base + -alt palette entries.
		$palette = $tj['settings']['color']['palette'] ?? array();
		$palette = array_values( array_filter( $palette, function ( $c ) use ( $base_slug ) {
			$s = $c['slug'] ?? '';
			return $s !== $base_slug && $s !== $base_slug . '-alt';
		} ) );
		$tj['settings']['color']['palette'] = $palette;

		// Drop the linked opposite entries.
		if ( isset( $tj['settings']['custom']['color'] ) && is_array( $tj['settings']['custom']['color'] ) ) {
			unset( $tj['settings']['custom']['color'][ $base_slug . '-opposite' ] );
			unset( $tj['settings']['custom']['color'][ $base_slug . '-alt-opposite' ] );
		}

		file_put_contents( $path, wp_json_encode( $tj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

		wp_safe_redirect( add_query_arg( 'ccc_sg_saved', '1', admin_url( 'themes.php?page=style-guide-tokens' ) ) );
		exit;
	}

	private static function redirect_with_error( $msg ) {
		wp_safe_redirect( add_query_arg(
			'ccc_sg_error',
			rawurlencode( $msg ),
			admin_url( 'themes.php?page=style-guide-tokens' )
		) );
		exit;
	}

	private static function render_typography( $sizes, $families, $custom_fs ) {
		echo '<div class="sgt-section"><h2>' . esc_html__( 'Typography', 'classic-city-core' ) . '</h2>';

		// Font families — Google fonts are editable; custom local fonts (have
		// fontFace entries) are read-only because renaming them would require
		// moving font files on disk. Editable rows submit to a separate form
		// (ccc_sg_save_fonts) that rewrites the Google Fonts URL in the child
		// theme's functions.php.
		echo '<h3>' . esc_html__( 'Font families', 'classic-city-core' ) . '</h3>';
		if ( $families ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			echo '<input type="hidden" name="action" value="ccc_sg_save_fonts">';
			wp_nonce_field( 'ccc_sg_save_fonts', 'ccc_sg_nonce' );

			echo '<table class="sgt-table"><thead><tr><th>Role</th><th>Family</th><th>Source</th></tr></thead><tbody>';
			$any_editable = false;
			foreach ( $families as $f ) {
				$slug         = $f['slug'] ?? '';
				$family_str   = $f['fontFamily'] ?? '';
				$primary      = self::extract_primary_family( $family_str );
				$has_custom   = ! empty( $f['fontFace'] );
				$source_label = $has_custom ? count( $f['fontFace'] ) . ' local face(s)' : 'Google font';

				echo '<tr>';
				printf( '<td><strong>%s</strong><div style="color:#888;font-size:11px;font-family:monospace;">%s</div></td>', esc_html( $f['name'] ?? $slug ), esc_html( $slug ) );
				if ( $has_custom ) {
					printf(
						'<td><code>%s</code><div class="description" style="font-size:11px;">%s</div></td>',
						esc_html( $primary ),
						esc_html__( 'Read-only — custom local fonts are renamed on disk, not in the admin.', 'classic-city-core' )
					);
				} else {
					$any_editable = true;
					printf(
						'<td><input type="text" name="fontFamilies[%1$s]" value="%2$s" class="regular-text" style="font-family:monospace;max-width:240px;" pattern="[A-Za-z0-9][A-Za-z0-9 \-+_]{0,49}" title="Letters, digits, spaces, dashes, plus, underscore. Max 50 chars." /></td>',
						esc_attr( $slug ),
						esc_attr( $primary )
					);
				}
				printf( '<td>%s</td>', esc_html( $source_label ) );
				echo '</tr>';
			}
			echo '</tbody></table>';

			if ( $any_editable ) {
				echo '<p class="description" style="margin:.5em 0;">';
				echo esc_html__( 'Changing a Google font also rewrites the Google Fonts URL in the child theme\'s functions.php. Weights come from defaults.json.', 'classic-city-core' );
				echo '</p>';
				submit_button( __( 'Save font families', 'classic-city-core' ), 'primary', 'submit', false );
			}
			echo '</form>';
		} else {
			echo '<p class="sgt-empty">' . esc_html__( 'No font families.', 'classic-city-core' ) . '</p>';
		}

		// Body font sizes + heading sizes — both editable in one form.
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:1em;">';
		echo '<input type="hidden" name="action" value="ccc_sg_save_typography">';
		wp_nonce_field( 'ccc_sg_save_typography', 'ccc_sg_nonce' );

		echo '<h3>' . esc_html__( 'Body font sizes', 'classic-city-core' ) . '</h3>';
		if ( $sizes ) {
			echo '<table class="sgt-table"><thead><tr><th>Slug</th><th>Name</th><th>Size</th></tr></thead><tbody>';
			foreach ( $sizes as $s ) {
				$slug = $s['slug'] ?? '';
				printf(
					'<tr><td><code>%1$s</code></td><td>%2$s</td><td><input type="text" name="fontSizes[%1$s]" value="%3$s" class="regular-text" style="font-family:monospace;max-width:140px;" /></td></tr>',
					esc_attr( $slug ),
					esc_html( $s['name'] ?? '' ),
					esc_attr( $s['size'] ?? '' )
				);
			}
			echo '</tbody></table>';
		} else {
			echo '<p class="sgt-empty">' . esc_html__( 'No body font sizes.', 'classic-city-core' ) . '</p>';
		}

		echo '<h3>' . esc_html__( 'Heading sizes (custom.fs)', 'classic-city-core' ) . '</h3>';
		if ( $custom_fs ) {
			echo '<table class="sgt-table"><thead><tr><th>Heading</th><th>Size</th><th>CSS variable</th></tr></thead><tbody>';
			foreach ( $custom_fs as $key => $size ) {
				printf(
					'<tr><td><code>%1$s</code></td><td><input type="text" name="customFs[%1$s]" value="%2$s" class="regular-text" style="font-family:monospace;max-width:140px;" /></td><td><code>--wp--custom--fs--%1$s</code></td></tr>',
					esc_attr( $key ),
					esc_attr( $size )
				);
			}
			echo '</tbody></table>';
		} else {
			echo '<p class="sgt-empty">' . esc_html__( 'No heading sizes defined.', 'classic-city-core' ) . '</p>';
		}

		if ( $sizes || $custom_fs ) {
			submit_button( __( 'Save typography', 'classic-city-core' ), 'primary', 'submit', false );
		}
		echo '</form>';

		echo '</div>';
	}

	private static function render_spacing( $sizes ) {
		echo '<div class="sgt-section"><h2>' . esc_html__( 'Spacing', 'classic-city-core' ) . '</h2>';
		if ( ! $sizes ) {
			echo '<p class="sgt-empty">' . esc_html__( 'No spacing presets.', 'classic-city-core' ) . '</p></div>';
			return;
		}
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ccc_sg_save_spacing">';
		wp_nonce_field( 'ccc_sg_save_spacing', 'ccc_sg_nonce' );

		echo '<table class="sgt-table"><thead><tr><th>Slug</th><th>Size</th><th>CSS variable</th></tr></thead><tbody>';
		foreach ( $sizes as $s ) {
			$slug = $s['slug'] ?? '';
			printf(
				'<tr><td><code>%1$s</code></td><td><input type="text" name="spacing[%1$s]" value="%2$s" class="regular-text" style="font-family:monospace;max-width:140px;" /></td><td><code>--wp--preset--spacing--%1$s</code></td></tr>',
				esc_attr( $slug ),
				esc_attr( $s['size'] ?? '' )
			);
		}
		echo '</tbody></table>';

		submit_button( __( 'Save spacing', 'classic-city-core' ), 'primary', 'submit', false );
		echo '</form></div>';
	}

	private static function render_shadows( $shadows ) {
		echo '<div class="sgt-section"><h2>' . esc_html__( 'Shadows', 'classic-city-core' ) . '</h2>';
		if ( ! $shadows ) {
			echo '<p class="sgt-empty">' . esc_html__( 'No shadow presets.', 'classic-city-core' ) . '</p></div>';
			return;
		}
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ccc_sg_save_shadows">';
		wp_nonce_field( 'ccc_sg_save_shadows', 'ccc_sg_nonce' );

		echo '<table class="sgt-table"><thead><tr><th>Slug</th><th>Preview</th><th>Value</th></tr></thead><tbody>';
		foreach ( $shadows as $s ) {
			$slug = $s['slug'] ?? '';
			printf(
				'<tr><td><code>%1$s</code></td><td><div style="width:60px;height:30px;background:#fff;box-shadow:%2$s;border-radius:4px;"></div></td><td><input type="text" name="shadows[%1$s]" value="%3$s" class="regular-text" style="font-family:monospace;width:100%%;" /></td></tr>',
				esc_attr( $slug ),
				esc_attr( $s['shadow'] ?? '' ),
				esc_attr( $s['shadow'] ?? '' )
			);
		}
		echo '</tbody></table>';

		submit_button( __( 'Save shadows', 'classic-city-core' ), 'primary', 'submit', false );
		echo '</form></div>';
	}

	/**
	 * B3.2c — editable custom tokens. Covers both settings.layout (content/wide
	 * size) and settings.custom.* (button padding, radius, letter-spacing,
	 * base font sizes, body bg, narrow size, icons style). Submits to the
	 * ccc_sg_save_custom_tokens handler; fields are keyed by their theme.json
	 * path (dot-separated) so the handler can write them back without a
	 * per-field switch statement.
	 */
	private static function render_custom_tokens( $layout, $custom ) {
		echo '<div class="sgt-section"><h2>' . esc_html__( 'Custom tokens', 'classic-city-core' ) . '</h2>';
		echo '<p class="description" style="margin:0 0 1em;">' . esc_html__( 'These live under settings.custom.* / settings.layout in theme.json and drive the fluid type, button sizing, and layout widths.', 'classic-city-core' ) . '</p>';

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ccc_sg_save_custom_tokens">';
		wp_nonce_field( 'ccc_sg_save_custom_tokens', 'ccc_sg_nonce' );

		$icons_style = $custom['icons']['style'] ?? 'solid';

		// Field definitions grouped visually.
		$groups = array(
			__( 'Layout widths', 'classic-city-core' ) => array(
				array( 'layout.contentSize',       __( 'Content size', 'classic-city-core' ),      $layout['contentSize'] ?? '',              'size' ),
				array( 'layout.wideSize',          __( 'Wide size', 'classic-city-core' ),         $layout['wideSize'] ?? '',                 'size' ),
				array( 'custom.layout.narrow-size', __( 'Narrow size', 'classic-city-core' ),       $custom['layout']['narrow-size'] ?? '',    'size' ),
			),
			__( 'Buttons & radius', 'classic-city-core' ) => array(
				array( 'custom.btn.padding-y', __( 'Button padding (y)', 'classic-city-core' ),  $custom['btn']['padding-y'] ?? '', 'size' ),
				array( 'custom.btn.padding-x', __( 'Button padding (x)', 'classic-city-core' ),  $custom['btn']['padding-x'] ?? '', 'size' ),
				array( 'custom.radius.default', __( 'Radius', 'classic-city-core' ),              $custom['radius']['default'] ?? '', 'size' ),
			),
			__( 'Typography rhythm', 'classic-city-core' ) => array(
				array( 'custom.heading.letter-spacing', __( 'Heading letter-spacing', 'classic-city-core' ), $custom['heading']['letter-spacing'] ?? '', 'letter' ),
				array( 'custom.heading.base-font-size', __( 'Heading base size', 'classic-city-core' ),      $custom['heading']['base-font-size'] ?? '', 'size' ),
				array( 'custom.eyebrow.letter-spacing', __( 'Eyebrow letter-spacing', 'classic-city-core' ), $custom['eyebrow']['letter-spacing'] ?? '', 'letter' ),
				array( 'custom.body.base-font-size',    __( 'Body base size', 'classic-city-core' ),         $custom['body']['base-font-size'] ?? '',    'size' ),
			),
			__( 'Body background', 'classic-city-core' ) => array(
				array( 'custom.body.bg', __( 'Body background color', 'classic-city-core' ), $custom['body']['bg'] ?? '', 'color' ),
			),
		);

		foreach ( $groups as $label => $fields ) {
			printf( '<h3 style="margin-top:1em;">%s</h3>', esc_html( $label ) );
			echo '<table class="sgt-table"><thead><tr><th style="width:30%;">' . esc_html__( 'Token', 'classic-city-core' ) . '</th><th>' . esc_html__( 'Value', 'classic-city-core' ) . '</th></tr></thead><tbody>';
			foreach ( $fields as $row ) {
				list( $path, $label_text, $value, $type ) = $row;
				$name  = 'tokens[' . $path . ']';
				$ph    = $type === 'color' ? '#FFFFFF' : ( $type === 'letter' ? '-0.02em, 0, normal' : '1.2rem, 800px, 24px' );
				echo '<tr>';
				printf( '<td><strong>%s</strong><div style="color:#888;font-size:11px;font-family:monospace;">%s</div></td>', esc_html( $label_text ), esc_html( $path ) );
				if ( $type === 'color' ) {
					printf(
						'<td><input type="text" name="%1$s" value="%2$s" class="regular-text" style="font-family:monospace;max-width:140px;" placeholder="%3$s" /> <span style="display:inline-block;width:24px;height:24px;border:1px solid #ccc;border-radius:3px;vertical-align:middle;background:%2$s;margin-left:8px;"></span></td>',
						esc_attr( $name ),
						esc_attr( $value ),
						esc_attr( $ph )
					);
				} else {
					printf(
						'<td><input type="text" name="%1$s" value="%2$s" class="regular-text" style="font-family:monospace;max-width:200px;" placeholder="%3$s" /></td>',
						esc_attr( $name ),
						esc_attr( $value ),
						esc_attr( $ph )
					);
				}
				echo '</tr>';
			}
			echo '</tbody></table>';
		}

		// Icons style — select, not a free text.
		echo '<h3 style="margin-top:1em;">' . esc_html__( 'Icons', 'classic-city-core' ) . '</h3>';
		echo '<table class="sgt-table"><thead><tr><th style="width:30%;">' . esc_html__( 'Token', 'classic-city-core' ) . '</th><th>' . esc_html__( 'Value', 'classic-city-core' ) . '</th></tr></thead><tbody>';
		echo '<tr>';
		echo '<td><strong>' . esc_html__( 'FontAwesome style', 'classic-city-core' ) . '</strong><div style="color:#888;font-size:11px;font-family:monospace;">custom.icons.style</div></td>';
		echo '<td><select name="tokens[custom.icons.style]">';
		foreach ( array( 'solid', 'regular', 'light', 'sharp-light' ) as $opt ) {
			printf(
				'<option value="%1$s"%2$s>%1$s</option>',
				esc_attr( $opt ),
				selected( $icons_style, $opt, false )
			);
		}
		echo '</select></td>';
		echo '</tr>';
		echo '</tbody></table>';

		submit_button( __( 'Save custom tokens', 'classic-city-core' ), 'primary', 'submit', false );
		echo '</form>';

		echo '</div>';
	}

	/**
	 * B3.2d — custom.css textarea. Lives alongside theme.json in the child
	 * theme root. Reads whatever is on disk now; saving overwrites it.
	 * If the child theme's functions.php is missing the enqueue (i.e. the
	 * theme was CLI-created without a custom.css), the save handler injects
	 * the enqueue so the new CSS actually loads on the frontend.
	 */
	private static function render_custom_css() {
		$path = get_stylesheet_directory() . '/custom.css';
		$current = file_exists( $path ) ? file_get_contents( $path ) : '';

		echo '<div class="sgt-section"><h2>' . esc_html__( 'Custom CSS', 'classic-city-core' ) . '</h2>';
		echo '<p class="description" style="margin:0 0 1em;">';
		echo esc_html__( 'Raw CSS appended after blocks.css. Targets use the WP-native vars: var(--wp--preset--color--cta), var(--wp--preset--spacing--20), etc.', 'classic-city-core' );
		echo '</p>';

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="ccc_sg_save_custom_css">';
		wp_nonce_field( 'ccc_sg_save_custom_css', 'ccc_sg_nonce' );
		printf(
			'<textarea name="custom_css" rows="18" class="large-text code" style="font-family:monospace;font-size:12px;background:#fff;" spellcheck="false">%s</textarea>',
			esc_textarea( $current )
		);
		echo '<p class="description" style="margin-top:.5em;">';
		printf(
			/* translators: %s: path to custom.css */
			esc_html__( 'Writes to: %s', 'classic-city-core' ),
			'<code>' . esc_html( str_replace( ABSPATH, '', $path ) ) . '</code>'
		);
		echo '</p>';
		submit_button( __( 'Save custom CSS', 'classic-city-core' ), 'primary', 'submit', false );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Shared save pipeline: permission check, nonce check, load theme.json,
	 * hand off to the modifier callback, write back, redirect. The callback
	 * gets the parsed theme.json by reference and mutates only the section it
	 * owns — every other key is preserved on write.
	 *
	 * @param string   $nonce_action  The nonce action for this form.
	 * @param callable $modifier      fn( array &$tj ) — mutates theme.json.
	 */
	private static function run_save( $nonce_action, callable $modifier ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}
		check_admin_referer( $nonce_action, 'ccc_sg_nonce' );

		$path = get_stylesheet_directory() . '/theme.json';
		if ( ! file_exists( $path ) ) {
			self::redirect_with_error( "theme.json not found at {$path}" );
		}

		$tj = json_decode( file_get_contents( $path ), true );
		if ( ! is_array( $tj ) ) {
			self::redirect_with_error( 'Could not parse theme.json' );
		}

		try {
			$modifier( $tj );
		} catch ( Exception $e ) {
			self::redirect_with_error( 'Validation failed: ' . $e->getMessage() );
		}

		$bytes = file_put_contents(
			$path,
			wp_json_encode( $tj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
		);
		if ( $bytes === false ) {
			self::redirect_with_error( 'Could not write theme.json — check file permissions.' );
		}

		wp_safe_redirect( add_query_arg( 'ccc_sg_saved', '1', admin_url( 'themes.php?page=style-guide-tokens' ) ) );
		exit;
	}

	/**
	 * Validate a CSS size value. Accepts lengths (px/rem/em/%/vh/vw/ch/ex),
	 * calc() expressions, and unitless "0". Rejects empty + obviously-bogus
	 * input to catch fat-fingered saves before they land in theme.json.
	 */
	private static function validate_size( $v, $label ) {
		$v = trim( (string) $v );
		if ( $v === '' ) {
			throw new Exception( "Empty size for {$label}" );
		}
		if ( $v === '0' ) {
			return $v;
		}
		if ( preg_match( '/^calc\(.+\)$/i', $v ) ) {
			return $v;
		}
		if ( preg_match( '/^-?\d*\.?\d+(px|rem|em|%|vh|vw|ch|ex)$/i', $v ) ) {
			return $v;
		}
		throw new Exception( "Invalid size '{$v}' for {$label}" );
	}

	public static function handle_save_typography() {
		self::run_save( 'ccc_sg_save_typography', function ( array &$tj ) {
			$submitted_sizes = isset( $_POST['fontSizes'] ) && is_array( $_POST['fontSizes'] ) ? wp_unslash( $_POST['fontSizes'] ) : array();
			$submitted_fs    = isset( $_POST['customFs'] )  && is_array( $_POST['customFs'] )  ? wp_unslash( $_POST['customFs'] )  : array();

			// Body font sizes — preserve order + slug + name, only size changes.
			if ( ! empty( $tj['settings']['typography']['fontSizes'] ) ) {
				foreach ( $tj['settings']['typography']['fontSizes'] as &$entry ) {
					$slug = $entry['slug'] ?? '';
					if ( isset( $submitted_sizes[ $slug ] ) ) {
						$entry['size'] = self::validate_size( $submitted_sizes[ $slug ], "fontSize.{$slug}" );
					}
				}
				unset( $entry );
			}

			// Heading sizes under custom.fs. Iterate over the submitted keys
			// rather than the existing child set so entries inherited from the
			// parent theme.json (e.g. `h-N-min` tokens added by Option C fluid
			// typography) can be overridden even when the child didn't define
			// them yet.
			if ( $submitted_fs ) {
				if ( ! isset( $tj['settings']['custom']['fs'] ) || ! is_array( $tj['settings']['custom']['fs'] ) ) {
					$tj['settings']['custom']['fs'] = array();
				}
				foreach ( $submitted_fs as $key => $value ) {
					$key = sanitize_key( str_replace( '_', '-', (string) $key ) );
					if ( $key === '' ) continue;
					$tj['settings']['custom']['fs'][ $key ] = self::validate_size( $value, "custom.fs.{$key}" );
				}
			}
		} );
	}

	public static function handle_save_spacing() {
		self::run_save( 'ccc_sg_save_spacing', function ( array &$tj ) {
			$submitted = isset( $_POST['spacing'] ) && is_array( $_POST['spacing'] ) ? wp_unslash( $_POST['spacing'] ) : array();
			if ( empty( $tj['settings']['spacing']['spacingSizes'] ) ) {
				return;
			}
			foreach ( $tj['settings']['spacing']['spacingSizes'] as &$entry ) {
				$slug = $entry['slug'] ?? '';
				if ( isset( $submitted[ $slug ] ) ) {
					$entry['size'] = self::validate_size( $submitted[ $slug ], "spacing.{$slug}" );
				}
			}
			unset( $entry );
		} );
	}

	public static function handle_save_shadows() {
		self::run_save( 'ccc_sg_save_shadows', function ( array &$tj ) {
			$submitted = isset( $_POST['shadows'] ) && is_array( $_POST['shadows'] ) ? wp_unslash( $_POST['shadows'] ) : array();
			if ( empty( $tj['settings']['shadow']['presets'] ) ) {
				return;
			}
			foreach ( $tj['settings']['shadow']['presets'] as &$entry ) {
				$slug = $entry['slug'] ?? '';
				if ( isset( $submitted[ $slug ] ) ) {
					$val = trim( (string) $submitted[ $slug ] );
					if ( $val === '' ) {
						throw new Exception( "Empty shadow for {$slug}" );
					}
					// Minimal sanity: we intentionally don't deeply validate CSS
					// shadows — they have many legal shapes and users pasting
					// from design tools should be trusted.
					$entry['shadow'] = $val;
				}
			}
			unset( $entry );
		} );
	}

	/**
	 * Validate a letter-spacing value. Accepts the same shapes as validate_size
	 * plus "normal" (CSS keyword).
	 */
	private static function validate_letter_spacing( $v, $label ) {
		$v = trim( (string) $v );
		if ( $v === '' )                                return '';
		if ( strtolower( $v ) === 'normal' )            return 'normal';
		return self::validate_size( $v, $label );
	}

	/**
	 * Write a dotted-path value into a nested array by reference. E.g.
	 * `custom.btn.padding-y` becomes $arr['custom']['btn']['padding-y'] = $v.
	 */
	private static function set_dotted( array &$arr, $path, $value ) {
		$keys = explode( '.', $path );
		$cursor = &$arr;
		foreach ( $keys as $i => $k ) {
			if ( $i === count( $keys ) - 1 ) {
				$cursor[ $k ] = $value;
				return;
			}
			if ( ! isset( $cursor[ $k ] ) || ! is_array( $cursor[ $k ] ) ) {
				$cursor[ $k ] = array();
			}
			$cursor = &$cursor[ $k ];
		}
	}

	public static function handle_save_custom_tokens() {
		self::run_save( 'ccc_sg_save_custom_tokens', function ( array &$tj ) {
			$submitted = isset( $_POST['tokens'] ) && is_array( $_POST['tokens'] ) ? wp_unslash( $_POST['tokens'] ) : array();

			// Per-field: validator + theme.json path. Path is relative to the
			// theme.json root; `settings.` prefix is added when writing.
			$fields = array(
				'layout.contentSize'           => 'size',
				'layout.wideSize'              => 'size',
				'custom.layout.narrow-size'    => 'size',
				'custom.btn.padding-y'         => 'size',
				'custom.btn.padding-x'         => 'size',
				'custom.radius.default'        => 'size',
				'custom.heading.letter-spacing' => 'letter',
				'custom.heading.base-font-size' => 'size',
				'custom.eyebrow.letter-spacing' => 'letter',
				'custom.body.base-font-size'   => 'size',
				'custom.body.bg'               => 'color',
				'custom.icons.style'           => 'icons',
			);

			$allowed_icon_styles = array( 'solid', 'regular', 'light', 'sharp-light' );

			foreach ( $fields as $path => $type ) {
				if ( ! array_key_exists( $path, $submitted ) ) continue;
				$raw = $submitted[ $path ];
				if ( $type === 'size' ) {
					$v = self::validate_size( $raw, $path );
				} elseif ( $type === 'letter' ) {
					$v = self::validate_letter_spacing( $raw, $path );
					if ( $v === '' ) continue; // skip empty letter-spacing rather than wipe
				} elseif ( $type === 'color' ) {
					$v = CCC_Client_Importer::normalize_hex_public( $raw );
				} elseif ( $type === 'icons' ) {
					$raw = strtolower( trim( (string) $raw ) );
					if ( ! in_array( $raw, $allowed_icon_styles, true ) ) {
						throw new Exception( "Invalid icons.style: '{$raw}'" );
					}
					$v = $raw;
				} else {
					continue;
				}
				self::set_dotted( $tj['settings'], $path, $v );
			}
		} );
	}

	/**
	 * B4 textures — render the add/edit form + list of existing textures.
	 *
	 * @param array $registered The `settings.custom.textures` block from theme.json.
	 */
	private static function render_textures( $registered ) {
		$registered = is_array( $registered ) ? $registered : array();

		echo '<div class="sgt-section"><h2>' . esc_html__( 'Textures', 'classic-city-core' ) . '</h2>';
		echo '<p class="description" style="margin:0 0 1em;">';
		echo esc_html__( 'Background textures this child theme exposes. Each entry renders as a .has-bg-texture-{slug} rule that any block can opt into via its Additional CSS class.', 'classic-city-core' );
		echo '</p>';

		// Existing list.
		if ( $registered ) {
			echo '<table class="sgt-table" style="margin:0 0 1.5em;"><thead><tr><th>Slug</th><th>Image</th><th>Mode</th><th>Details</th><th style="width:200px;">Actions</th></tr></thead><tbody>';
			foreach ( $registered as $slug => $entry ) {
				if ( ! is_array( $entry ) ) continue;
				$img_id   = isset( $entry['image'] ) ? (int) $entry['image'] : 0;
				$img_url  = $img_id > 0 ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
				$mode     = $entry['mode'] ?? 'cover';
				$opacity  = $entry['opacity'] ?? 1;
				$blend    = $entry['blend-mode'] ?? 'normal';
				$detail   = $mode === 'cover'
					? sprintf( 'size: %s · angle: %s', $entry['size'] ?? '50% auto', $entry['angle'] ?? '0deg' )
					: sprintf( 'position: %s · size: %s · fade: %s', $entry['position'] ?? 'top-right', $entry['size'] ?? '400px', ! empty( $entry['fade'] ) ? 'yes' : 'no' );

				echo '<tr>';
				echo '<td><code>' . esc_html( $slug ) . '</code><div style="color:#888;font-size:11px;">opacity: ' . esc_html( (string) $opacity ) . ' · blend: ' . esc_html( $blend ) . '</div></td>';
				echo '<td>';
				if ( $img_url ) {
					printf( '<img src="%s" alt="" style="width:60px;height:60px;object-fit:cover;border:1px solid #ddd;border-radius:3px;" />', esc_url( $img_url ) );
				} else {
					echo '<em style="color:#999;">(missing)</em>';
				}
				echo '</td>';
				echo '<td>' . esc_html( $mode ) . '</td>';
				echo '<td style="font-size:12px;color:#666;">' . esc_html( $detail ) . '</td>';
				echo '<td>';
				// Delete form.
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline;" onsubmit="return confirm(\'Delete texture ' . esc_js( $slug ) . '?\');">';
				echo '<input type="hidden" name="action" value="ccc_sg_delete_texture">';
				echo '<input type="hidden" name="slug" value="' . esc_attr( $slug ) . '">';
				wp_nonce_field( 'ccc_sg_delete_texture_' . $slug, 'ccc_sg_nonce' );
				submit_button( __( 'Delete', 'classic-city-core' ), 'delete small', 'submit', false );
				echo '</form>';
				echo '</td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		} else {
			echo '<p class="sgt-empty">' . esc_html__( 'No textures registered yet. Add one below.', 'classic-city-core' ) . '</p>';
		}

		// Add form.
		echo '<h3>' . esc_html__( 'Add / update a texture', 'classic-city-core' ) . '</h3>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" id="ccc-sg-texture-form">';
		echo '<input type="hidden" name="action" value="ccc_sg_save_texture">';
		wp_nonce_field( 'ccc_sg_save_texture', 'ccc_sg_nonce' );

		echo '<table class="sgt-table"><tbody>';

		echo '<tr><td style="width:180px;"><strong>Slug</strong><div style="font-size:11px;color:#888;">letters/digits/dashes only</div></td><td><input type="text" name="slug" value="" class="regular-text" pattern="[a-z0-9][a-z0-9-]*" required placeholder="e.g. wood, paper, flourish" /> <span style="color:#888;font-size:12px;">saving an existing slug updates it</span></td></tr>';

		echo '<tr><td><strong>Image</strong></td><td>';
		echo '<input type="hidden" name="image" id="ccc-sg-texture-image" value="" />';
		echo '<div id="ccc-sg-texture-preview" style="display:inline-block;vertical-align:middle;margin-right:10px;"></div>';
		echo '<button type="button" class="button" id="ccc-sg-texture-pick">' . esc_html__( 'Choose image', 'classic-city-core' ) . '</button>';
		echo '<button type="button" class="button-link" id="ccc-sg-texture-clear" style="margin-left:8px;display:none;">remove</button>';
		echo '</td></tr>';

		echo '<tr><td><strong>Mode</strong></td><td>';
		echo '<label><input type="radio" name="mode" value="cover" checked class="ccc-sg-texture-mode" /> cover (fills block area)</label> &nbsp; ';
		echo '<label><input type="radio" name="mode" value="accent" class="ccc-sg-texture-mode" /> accent (positioned graphic)</label>';
		echo '</td></tr>';

		echo '<tr><td><strong>Opacity</strong></td><td><input type="number" name="opacity" min="0" max="1" step="0.05" value="1" style="width:80px;" /></td></tr>';

		echo '<tr><td><strong>Blend mode</strong></td><td><select name="blend-mode">';
		foreach ( array( 'normal', 'multiply', 'overlay', 'screen' ) as $opt ) {
			printf( '<option value="%s">%s</option>', esc_attr( $opt ), esc_html( $opt ) );
		}
		echo '</select></td></tr>';

		// Cover-only fields.
		echo '<tr class="ccc-sg-texture-mode-cover"><td><strong>Size (cover)</strong><div style="font-size:11px;color:#888;">background-size value</div></td><td><input type="text" name="size_cover" value="50% auto" class="regular-text" style="max-width:220px;" /></td></tr>';
		echo '<tr class="ccc-sg-texture-mode-cover"><td><strong>Angle (cover)</strong></td><td><input type="text" name="angle" value="0deg" class="regular-text" style="max-width:120px;" /></td></tr>';

		// Accent-only fields.
		echo '<tr class="ccc-sg-texture-mode-accent" style="display:none;"><td><strong>Position (accent)</strong></td><td><select name="position">';
		foreach ( array( 'top-right', 'top-left', 'bottom-right', 'bottom-left', 'center' ) as $p ) {
			printf( '<option value="%s">%s</option>', esc_attr( $p ), esc_html( $p ) );
		}
		echo '</select></td></tr>';
		echo '<tr class="ccc-sg-texture-mode-accent" style="display:none;"><td><strong>Size (accent)</strong><div style="font-size:11px;color:#888;">width, e.g. 400px</div></td><td><input type="text" name="size_accent" value="400px" class="regular-text" style="max-width:160px;" /></td></tr>';
		echo '<tr class="ccc-sg-texture-mode-accent" style="display:none;"><td><strong>Fade (accent)</strong></td><td><label><input type="checkbox" name="fade" value="1" /> fade to transparent at the edges</label></td></tr>';

		echo '</tbody></table>';

		submit_button( __( 'Save texture', 'classic-city-core' ), 'primary', 'submit', false );
		echo '</form>';

		// Media picker JS + mode-switch UI.
		echo '<script>
			jQuery(function($){
				var frame;
				$("#ccc-sg-texture-pick").on("click", function(e){
					e.preventDefault();
					if (frame) { frame.open(); return; }
					frame = wp.media({ title: "Choose texture image", button: { text: "Use image" }, multiple: false });
					frame.on("select", function(){
						var att = frame.state().get("selection").first().toJSON();
						$("#ccc-sg-texture-image").val(att.id);
						$("#ccc-sg-texture-preview").html("<img src=\"" + (att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url) + "\" style=\"width:60px;height:60px;object-fit:cover;border:1px solid #ddd;border-radius:3px;\" />");
						$("#ccc-sg-texture-clear").show();
					});
					frame.open();
				});
				$("#ccc-sg-texture-clear").on("click", function(e){
					e.preventDefault();
					$("#ccc-sg-texture-image").val("");
					$("#ccc-sg-texture-preview").empty();
					$(this).hide();
				});
				$(".ccc-sg-texture-mode").on("change", function(){
					var m = $("input[name=mode]:checked").val();
					$(".ccc-sg-texture-mode-cover").toggle(m === "cover");
					$(".ccc-sg-texture-mode-accent").toggle(m === "accent");
				});
			});
		</script>';

		echo '</div>';
	}

	public static function handle_save_texture() {
		self::run_save( 'ccc_sg_save_texture', function ( array &$tj ) {
			$raw_slug = wp_unslash( $_POST['slug'] ?? '' );
			$slug     = sanitize_html_class( strtolower( $raw_slug ) );
			if ( ! $slug || ! preg_match( '/^[a-z0-9][a-z0-9-]*$/', $slug ) ) {
				throw new Exception( "Invalid slug: '{$raw_slug}'" );
			}

			$image_id = (int) ( $_POST['image'] ?? 0 );
			if ( $image_id <= 0 ) {
				throw new Exception( 'Pick an image from the media library' );
			}

			$mode = in_array( $_POST['mode'] ?? '', array( 'cover', 'accent' ), true ) ? $_POST['mode'] : 'cover';
			$opacity = max( 0.0, min( 1.0, (float) ( $_POST['opacity'] ?? 1 ) ) );
			$blend   = in_array( $_POST['blend-mode'] ?? '', array( 'normal', 'multiply', 'overlay', 'screen' ), true )
				? $_POST['blend-mode']
				: 'normal';

			$entry = array(
				'image'      => $image_id,
				'mode'       => $mode,
				'opacity'    => $opacity,
				'blend-mode' => $blend,
			);

			if ( $mode === 'cover' ) {
				$entry['size']  = trim( wp_unslash( $_POST['size_cover'] ?? '50% auto' ) );
				$entry['angle'] = trim( wp_unslash( $_POST['angle'] ?? '0deg' ) );
			} else {
				$allowed_pos = array( 'top-right', 'top-left', 'bottom-right', 'bottom-left', 'center' );
				$entry['position'] = in_array( $_POST['position'] ?? '', $allowed_pos, true ) ? $_POST['position'] : 'top-right';
				$entry['size']     = trim( wp_unslash( $_POST['size_accent'] ?? '400px' ) );
				$entry['fade']     = ! empty( $_POST['fade'] );
			}

			if ( ! isset( $tj['settings']['custom']['textures'] ) || ! is_array( $tj['settings']['custom']['textures'] ) ) {
				$tj['settings']['custom']['textures'] = array();
			}
			$tj['settings']['custom']['textures'][ $slug ] = $entry;
		} );
	}

	public static function handle_delete_texture() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}
		$slug = sanitize_html_class( strtolower( wp_unslash( $_POST['slug'] ?? '' ) ) );
		check_admin_referer( 'ccc_sg_delete_texture_' . $slug, 'ccc_sg_nonce' );

		$path = get_stylesheet_directory() . '/theme.json';
		if ( ! file_exists( $path ) ) {
			self::redirect_with_error( 'theme.json not found' );
		}
		$tj = json_decode( file_get_contents( $path ), true );
		if ( ! is_array( $tj ) ) {
			self::redirect_with_error( 'Could not parse theme.json' );
		}
		if ( isset( $tj['settings']['custom']['textures'][ $slug ] ) ) {
			unset( $tj['settings']['custom']['textures'][ $slug ] );
			file_put_contents( $path, wp_json_encode( $tj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		}

		wp_safe_redirect( add_query_arg( 'ccc_sg_saved', '1', admin_url( 'themes.php?page=style-guide-tokens' ) ) );
		exit;
	}

	/**
	 * Extract the primary family name from a CSS font-family string.
	 * `'Barlow Semi Condensed', system-ui, -apple-system, sans-serif`
	 *   → `Barlow Semi Condensed`
	 * Unquoted first entries (e.g. `Inter, sans-serif`) also work.
	 */
	private static function extract_primary_family( $fontFamily ) {
		$s = trim( (string) $fontFamily );
		if ( $s === '' ) return '';
		// Quoted: 'Name' or "Name".
		if ( preg_match( "/^['\"]([^'\"]+)['\"]/", $s, $m ) ) {
			return trim( $m[1] );
		}
		// Unquoted: up to first comma.
		$parts = explode( ',', $s );
		return trim( $parts[0] );
	}

	/**
	 * Validate a font family name. Must match the same character set the UI
	 * pattern enforces (letters, digits, spaces, dashes, plus, underscore).
	 */
	private static function validate_family_name( $name ) {
		$name = trim( (string) $name );
		if ( $name === '' ) {
			throw new Exception( 'Empty font family name' );
		}
		if ( strlen( $name ) > 50 ) {
			throw new Exception( "Font family name too long: '{$name}'" );
		}
		if ( ! preg_match( '/^[A-Za-z0-9][A-Za-z0-9 \-+_]{0,49}$/', $name ) ) {
			throw new Exception( "Invalid font family name: '{$name}'" );
		}
		return $name;
	}

	/**
	 * Rebuild the Google Fonts enqueue block in the child theme's
	 * functions.php. Removes any existing `{slug}-google-fonts` enqueue block,
	 * then appends a fresh one if $families is non-empty. Leaves the
	 * custom.css enqueue block alone.
	 *
	 * @param string $fn_content Current functions.php content.
	 * @param string $slug       The child theme slug (used to build the handle).
	 * @param array  $families   Array of [ 'family' => 'Name', 'weights' => [400,700] ].
	 * @return string Updated content.
	 */
	private static function rewrite_google_fonts_enqueue( $fn_content, $slug, $families ) {
		$handle = "'" . $slug . "-google-fonts'";

		// Remove any existing google-fonts enqueue block. Match conservatively:
		// an add_action(...) block that contains the handle. Non-greedy body,
		// DOTALL so newlines inside the block don't break the match.
		if ( strpos( $fn_content, $handle ) !== false ) {
			$pattern = '#\n*add_action\s*\(\s*\'wp_enqueue_scripts\'\s*,\s*function\s*\(\s*\)\s*\{(?:[^{}]|\{[^{}]*\})*?' . preg_quote( $handle, '#' ) . '(?:[^{}]|\{[^{}]*\})*?\}\s*\)\s*;\s*\n?#s';
			$fn_content = preg_replace( $pattern, "\n", $fn_content );
		}

		if ( empty( $families ) ) {
			return rtrim( $fn_content ) . "\n";
		}

		$parts = array();
		foreach ( $families as $entry ) {
			$weights = $entry['weights'] ?? array( 400 );
			sort( $weights );
			$weights = array_values( array_unique( $weights ) );
			$parts[] = rawurlencode( $entry['family'] ) . ':wght@' . implode( ';', $weights );
		}
		$url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $parts ) . '&display=swap';

		$block  = "\nadd_action( 'wp_enqueue_scripts', function() {\n";
		$block .= "\twp_enqueue_style( {$handle}, " . var_export( $url, true ) . ", array(), null );\n";
		$block .= "} );\n";

		return rtrim( $fn_content ) . "\n" . $block;
	}

	public static function handle_save_fonts() {
		self::run_save( 'ccc_sg_save_fonts', function ( array &$tj ) {
			$submitted = isset( $_POST['fontFamilies'] ) && is_array( $_POST['fontFamilies'] ) ? wp_unslash( $_POST['fontFamilies'] ) : array();
			if ( empty( $tj['settings']['typography']['fontFamilies'] ) ) {
				return;
			}

			foreach ( $tj['settings']['typography']['fontFamilies'] as &$entry ) {
				$slug = $entry['slug'] ?? '';
				// Skip rows the user didn't submit (e.g. custom-face rows are read-only).
				if ( ! isset( $submitted[ $slug ] ) ) continue;
				// Custom-face rows: even if the UI submitted something, ignore — safer.
				if ( ! empty( $entry['fontFace'] ) ) continue;

				$name     = self::validate_family_name( $submitted[ $slug ] );
				$fallback = CCC_Client_Importer::font_fallback_public( $name );
				$entry['fontFamily'] = "'{$name}', {$fallback}";
			}
			unset( $entry );

			// Rewrite the child theme's functions.php Google Fonts enqueue.
			// Collect Google families (no fontFace) + default weights per role.
			$defaults_path = get_template_directory() . '/config/defaults.json';
			$defaults      = is_file( $defaults_path ) ? json_decode( file_get_contents( $defaults_path ), true ) : array();

			$google = array();
			foreach ( $tj['settings']['typography']['fontFamilies'] as $entry ) {
				if ( ! empty( $entry['fontFace'] ) ) continue;
				$slug    = $entry['slug'] ?? '';
				$primary = self::extract_primary_family( $entry['fontFamily'] ?? '' );
				if ( $primary === '' ) continue;
				$weights = $defaults['fonts'][ $slug ]['weights'] ?? array( 400 );
				$google[] = array( 'family' => $primary, 'weights' => $weights );
			}

			$theme    = wp_get_theme();
			$tslug    = $theme->get_stylesheet();
			$fn_path  = get_stylesheet_directory() . '/functions.php';
			if ( file_exists( $fn_path ) ) {
				$fn = file_get_contents( $fn_path );
				$fn = self::rewrite_google_fonts_enqueue( $fn, $tslug, $google );
				file_put_contents( $fn_path, $fn );
			}
		} );
	}

	/**
	 * B3.2d handler — writes the submitted CSS to the active child theme's
	 * custom.css. If the child theme's functions.php lacks the enqueue
	 * (CLI-created theme that never had a custom.css at import time), we
	 * append the standard enqueue block so the new CSS actually loads.
	 */
	public static function handle_save_custom_css() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}
		check_admin_referer( 'ccc_sg_save_custom_css', 'ccc_sg_nonce' );

		$css_path = get_stylesheet_directory() . '/custom.css';
		$fn_path  = get_stylesheet_directory() . '/functions.php';

		// No sanitization on the CSS body — admin users are trusted, and CSS
		// strings legitimately contain characters wp_kses would mangle.
		$content = isset( $_POST['custom_css'] ) ? wp_unslash( $_POST['custom_css'] ) : '';
		$content = (string) $content;

		if ( file_put_contents( $css_path, $content ) === false ) {
			self::redirect_with_error( 'Could not write custom.css — check file permissions.' );
		}

		// Make sure functions.php enqueues custom.css. Idempotent: detect via
		// the canonical pattern the importer emits and skip if present.
		if ( file_exists( $fn_path ) ) {
			$fn = file_get_contents( $fn_path );
			if ( strpos( $fn, "'/custom.css'" ) === false ) {
				$theme = wp_get_theme();
				$slug  = $theme->get_stylesheet();
				$block  = "\nadd_action( 'wp_enqueue_scripts', function() {\n";
				$block .= "\twp_enqueue_style(\n";
				$block .= "\t\t'{$slug}-custom',\n";
				$block .= "\t\tget_stylesheet_directory_uri() . '/custom.css',\n";
				$block .= "\t\tarray( 'ccc-blocks' ),\n";
				$block .= "\t\tfilemtime( get_stylesheet_directory() . '/custom.css' )\n";
				$block .= "\t);\n";
				$block .= "}, 20 );\n";
				file_put_contents( $fn_path, rtrim( $fn ) . "\n" . $block );
			}
		}

		wp_safe_redirect( add_query_arg( 'ccc_sg_saved', '1', admin_url( 'themes.php?page=style-guide-tokens' ) ) );
		exit;
	}
}

CCC_Style_Guide_Admin::boot();
