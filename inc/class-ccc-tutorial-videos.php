<?php
/**
 * Tools → Tutorial Videos admin page.
 *
 * Lightweight CMS-side video library for client onboarding/training content.
 * Stores a flat list of { title, description, embed_code } in the
 * `ccc_tutorial_videos` option and renders each provider's pasted embed
 * code as-is. Legacy entries with `embed_url` fall back to wp_oembed_get().
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CCC_Tutorial_Videos {

	const OPTION_KEY = 'ccc_tutorial_videos';
	const PAGE_SLUG  = 'ccc-tutorial-videos';

	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_ccc_tv_add',    array( __CLASS__, 'handle_add' ) );
		add_action( 'admin_post_ccc_tv_delete', array( __CLASS__, 'handle_delete' ) );
	}

	public static function register_menu() {
		add_management_page(
			__( 'Tutorial Videos', 'classic-city-core' ),
			__( 'Tutorial Videos', 'classic-city-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	private static function get_videos() {
		$videos = get_option( self::OPTION_KEY, array() );
		return is_array( $videos ) ? $videos : array();
	}

	private static function save_videos( array $videos ) {
		update_option( self::OPTION_KEY, array_values( $videos ), false );
	}

	private static function redirect_back( $args = array() ) {
		$url = add_query_arg(
			array_merge( array( 'page' => self::PAGE_SLUG ), $args ),
			admin_url( 'tools.php' )
		);
		wp_safe_redirect( $url );
		exit;
	}

	public static function handle_add() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}
		check_admin_referer( 'ccc_tv_add' );

		$title       = isset( $_POST['title'] )       ? sanitize_text_field( wp_unslash( $_POST['title'] ) )           : '';
		$embed_code  = isset( $_POST['embed_code'] )  ? trim( wp_unslash( $_POST['embed_code'] ) )                    : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

		if ( $title === '' || $embed_code === '' ) {
			self::redirect_back( array( 'ccc_tv_error' => rawurlencode( __( 'Title and embed code are required.', 'classic-city-core' ) ) ) );
		}

		// Idempotency: if the same user POSTs an identical title+embed within
		// 10s, treat the second submission as a no-op. Guards against double-
		// clicks and browser resubmits before the PRG redirect lands.
		$dedupe_key = 'ccc_tv_add_' . md5( get_current_user_id() . '|' . $title . '|' . $embed_code );
		if ( get_transient( $dedupe_key ) ) {
			self::redirect_back( array( 'ccc_tv_added' => 1 ) );
		}
		set_transient( $dedupe_key, 1, 10 );

		$videos   = self::get_videos();
		$videos[] = array(
			'id'          => wp_generate_uuid4(),
			'title'       => $title,
			'embed_code'  => $embed_code,
			'description' => $description,
		);
		self::save_videos( $videos );

		self::redirect_back( array( 'ccc_tv_added' => 1 ) );
	}

	public static function handle_delete() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}
		$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
		check_admin_referer( 'ccc_tv_delete_' . $id );

		$videos = self::get_videos();
		$videos = array_values( array_filter( $videos, function ( $v ) use ( $id ) {
			return ( $v['id'] ?? '' ) !== $id;
		} ) );
		self::save_videos( $videos );

		self::redirect_back( array( 'ccc_tv_deleted' => 1 ) );
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'classic-city-core' ) );
		}

		$videos = self::get_videos();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Tutorial Videos', 'classic-city-core' ) . '</h1>';
		echo '<p style="margin:0 0 1.5em;color:#555;">' . esc_html__( 'Add training and how-to videos for site editors. Paste the embed code (the full <iframe>… or <script>… HTML) from YouTube, Vimeo, Loom, Wistia, or any other provider\'s "Share → Embed" dialog.', 'classic-city-core' ) . '</p>';

		if ( isset( $_GET['ccc_tv_added'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Video added.', 'classic-city-core' ) . '</p></div>';
		}
		if ( isset( $_GET['ccc_tv_deleted'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Video removed.', 'classic-city-core' ) . '</p></div>';
		}
		if ( isset( $_GET['ccc_tv_error'] ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( wp_unslash( $_GET['ccc_tv_error'] ) ) . '</p></div>';
		}

		self::styles();
		self::render_grid( $videos );
		self::render_add_form();

		echo '</div>';
	}

	private static function styles() {
		echo '<style>
			.ccc-tv-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:20px; margin:0 0 2em; }
			@media (max-width: 782px) { .ccc-tv-grid { grid-template-columns:1fr; } }
			.ccc-tv-card { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:16px; display:flex; flex-direction:column; gap:10px; }
			.ccc-tv-card .ccc-tv-embed { position:relative; padding-bottom:56.25%; height:0; overflow:hidden; background:#000; border-radius:3px; }
			.ccc-tv-card .ccc-tv-embed iframe,
			.ccc-tv-card .ccc-tv-embed object,
			.ccc-tv-card .ccc-tv-embed embed,
			.ccc-tv-card .ccc-tv-embed video { position:absolute; top:0; left:0; width:100%; height:100%; }
			.ccc-tv-card .ccc-tv-embed-fallback { padding:24px; color:#fff; text-align:center; }
			.ccc-tv-card h2 { font-size:1.05em; margin:4px 0 0; line-height:1.3; }
			.ccc-tv-card .ccc-tv-desc { color:#555; font-size:13px; margin:0; white-space:pre-wrap; }
			.ccc-tv-card .ccc-tv-actions { margin-top:auto; padding-top:6px; border-top:1px solid #f0f0f1; }
			.ccc-tv-card .ccc-tv-actions a { color:#b32d2e; text-decoration:none; font-size:12px; }
			.ccc-tv-card .ccc-tv-actions a:hover { text-decoration:underline; }
			.ccc-tv-empty { background:#fff; border:1px dashed #c3c4c7; border-radius:4px; padding:24px; text-align:center; color:#777; margin:0 0 2em; }
			.ccc-tv-form { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:20px; max-width:720px; }
			.ccc-tv-form h2 { margin-top:0; }
			.ccc-tv-form .form-row { margin:0 0 14px; }
			.ccc-tv-form label { display:block; font-weight:600; margin:0 0 4px; font-size:13px; }
			.ccc-tv-form input[type=text], .ccc-tv-form input[type=url], .ccc-tv-form textarea { width:100%; max-width:100%; }
			.ccc-tv-form textarea { min-height:80px; }
		</style>';
	}

	/**
	 * Fetch the oEmbed HTML without WP's `wp_filter_oembed_result()` sandbox
	 * wrapping (which strips cookie/storage access and breaks Loom/YouTube/
	 * Vimeo players inside this admin context). We trust admin-pasted URLs
	 * here, so the provider's own iframe is exactly what we want.
	 */
	private static function fetch_embed_html( $url ) {
		remove_filter( 'oembed_result', 'wp_filter_oembed_result', 10 );
		$html = wp_oembed_get( $url );
		add_filter( 'oembed_result', 'wp_filter_oembed_result', 10, 3 );
		return $html;
	}

	private static function render_grid( $videos ) {
		if ( empty( $videos ) ) {
			echo '<div class="ccc-tv-empty">' . esc_html__( 'No tutorial videos yet. Add one below.', 'classic-city-core' ) . '</div>';
			return;
		}

		echo '<div class="ccc-tv-grid">';
		foreach ( $videos as $v ) {
			$id          = $v['id']          ?? '';
			$title       = $v['title']       ?? '';
			$description = $v['description'] ?? '';
			$embed_code  = $v['embed_code']  ?? '';
			$embed_url   = $v['embed_url']   ?? ''; // Legacy field — render via oEmbed for back-compat.

			if ( $embed_code !== '' ) {
				$embed_html = $embed_code;
			} elseif ( $embed_url !== '' ) {
				$embed_html = self::fetch_embed_html( $embed_url );
				if ( ! $embed_html ) {
					$embed_html = '<div class="ccc-tv-embed-fallback">' . sprintf(
						/* translators: %s: video URL */
						esc_html__( 'Could not embed: %s', 'classic-city-core' ),
						'<a href="' . esc_url( $embed_url ) . '" style="color:#fff;text-decoration:underline;" target="_blank" rel="noopener">' . esc_html( $embed_url ) . '</a>'
					) . '</div>';
				}
			} else {
				$embed_html = '<div class="ccc-tv-embed-fallback">' . esc_html__( 'No embed code on this entry.', 'classic-city-core' ) . '</div>';
			}

			$delete_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'ccc_tv_delete',
						'id'     => $id,
					),
					admin_url( 'admin-post.php' )
				),
				'ccc_tv_delete_' . $id
			);

			echo '<div class="ccc-tv-card">';
			echo '<div class="ccc-tv-embed">' . $embed_html . '</div>'; // Trusted: pasted by an admin (manage_options).
			if ( $title !== '' ) {
				echo '<h2>' . esc_html( $title ) . '</h2>';
			}
			if ( $description !== '' ) {
				echo '<p class="ccc-tv-desc">' . esc_html( $description ) . '</p>';
			}
			echo '<div class="ccc-tv-actions">';
			echo '<a href="' . esc_url( $delete_url ) . '" onclick="return confirm(\'' . esc_js( __( 'Remove this video?', 'classic-city-core' ) ) . '\');">' . esc_html__( 'Remove', 'classic-city-core' ) . '</a>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	private static function render_add_form() {
		echo '<div class="ccc-tv-form">';
		echo '<h2>' . esc_html__( 'Add a video', 'classic-city-core' ) . '</h2>';
		// Disable submit on first click so a slow round-trip can't be turned
		// into a duplicate row by an impatient second click.
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="var s=this.querySelector(\'#submit\');if(s.disabled){return false;}s.disabled=true;s.value=\'Adding\\u2026\';">';
		echo '<input type="hidden" name="action" value="ccc_tv_add" />';
		wp_nonce_field( 'ccc_tv_add' );

		echo '<div class="form-row">';
		echo '<label for="ccc_tv_title">' . esc_html__( 'Title', 'classic-city-core' ) . '</label>';
		echo '<input type="text" id="ccc_tv_title" name="title" required />';
		echo '</div>';

		echo '<div class="form-row">';
		echo '<label for="ccc_tv_embed_code">' . esc_html__( 'Embed code', 'classic-city-core' ) . '</label>';
		echo '<textarea id="ccc_tv_embed_code" name="embed_code" rows="5" placeholder="' . esc_attr__( 'Paste the full embed code — e.g. <iframe src=\"https://www.loom.com/embed/...\"></iframe>', 'classic-city-core' ) . '" required></textarea>';
		echo '<p class="description" style="margin-top:4px;font-size:12px;color:#777;">' . esc_html__( 'On Loom: Share → Embed → Copy embed code. On YouTube: Share → Embed → Copy.', 'classic-city-core' ) . '</p>';
		echo '</div>';

		echo '<div class="form-row">';
		echo '<label for="ccc_tv_description">' . esc_html__( 'Description', 'classic-city-core' ) . '</label>';
		echo '<textarea id="ccc_tv_description" name="description"></textarea>';
		echo '</div>';

		submit_button( __( 'Add video', 'classic-city-core' ) );

		echo '</form>';
		echo '</div>';
	}
}

CCC_Tutorial_Videos::boot();
