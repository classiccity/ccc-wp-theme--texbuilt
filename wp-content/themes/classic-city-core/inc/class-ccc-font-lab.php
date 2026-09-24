<?php
/**
 * Font Lab — a development-only overlay for trying typefaces on the live site.
 *
 * A floating button opens a panel that swaps the three font-family ROLE
 * variables (`heading`, `body`, `accent`) against the shared font library at
 * classiccity/ccc-font-library. Because every element rule in the generated
 * global stylesheet resolves its family through those variables — `h1 {
 * font-family: var(--wp--preset--font-family--heading) }` — reassigning one
 * variable restyles the entire site with no page reload and no content change.
 *
 * OFF BY DEFAULT. A child theme opts in:
 *
 *     add_theme_support( 'ccc-font-lab', array(
 *         'library'          => 'https://font-library-five.vercel.app',
 *         'key'              => '',     // only if FONT_KEY is set on Vercel
 *         'allow_production' => true,   // WPE reports 'production' on staging
 *     ) );
 *
 * GUARDS (see config()). Theme support, plus a non-production environment,
 * plus the `edit_theme_options` capability. The environment check is
 * overridable per child via `allow_production` — WP Engine installs report
 * 'production' even when they are staging boxes, and keeping the whole on/off
 * decision in the child's functions.php beats splitting it across wp-config.
 * The capability check is NOT overridable, so a logged-out visitor never sees
 * the lab on any environment. When the override is active the toggle renders
 * in an amber warning state, so a lab left on is visible rather than silent.
 *
 * WHAT IT NEVER DOES: it does not register these fonts with `theme.json`, and
 * it does not write to the database. The editor's font picker is untouched, so
 * no author can apply a lab font to a block and bake it into post content that
 * would silently fall back once the lab is removed. The only thing this feature
 * mutates is three CSS custom properties on the <html> element of the browser
 * doing the previewing.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CCC_Font_Lab {

	const HANDLE  = 'ccc-font-lab';
	const STORAGE = 'cccFontLab';

	/**
	 * The three role variables the panel drives. Keys are the theme.json font
	 * family slugs; the CSS custom property name is derived from the slug.
	 */
	const ROLES = array(
		'heading' => 'Heading',
		'body'    => 'Body',
		'accent'  => 'Accent',
	);

	public static function boot() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_footer', array( __CLASS__, 'render' ) );
	}

	/**
	 * Every condition must hold. Ordered cheapest-first.
	 *
	 * `wp_get_environment_type()` returns 'production' unless WP_ENVIRONMENT_TYPE
	 * says otherwise, so the default answer is the safe one — but a child theme
	 * can override that single check with `allow_production` (see below). The
	 * capability check that follows it cannot be overridden by anything.
	 *
	 * @return array|false Config array when the lab should load, else false.
	 */
	public static function config() {
		if ( ! current_theme_supports( 'ccc-font-lab' ) ) {
			return false;
		}

		$support = get_theme_support( 'ccc-font-lab' );
		$args    = ( is_array( $support ) && isset( $support[0] ) && is_array( $support[0] ) )
			? $support[0]
			: array();

		/*
		 * Environment gate, overridable FROM THE CHILD THEME.
		 *
		 * WP Engine installs report `production` even when they are staging
		 * boxes, so on a staging-use install the environment check would
		 * otherwise make the lab impossible to use where it is most wanted.
		 * `allow_production => true` is the deliberate, greppable opt-out; it
		 * lives in the child's functions.php so the whole on/off decision sits
		 * in one file rather than being split across wp-config.
		 *
		 * The trade is explicit: this weakens "a forgotten flag cannot reach
		 * production" down to "a forgotten flag is only ever visible to logged-in
		 * administrators". The capability check below is NOT overridable and is
		 * what keeps a client's visitors from ever seeing it.
		 */
		$allow_production = ! empty( $args['allow_production'] );
		$is_production    = ( 'production' === wp_get_environment_type() );
		if ( $is_production && ! $allow_production ) {
			return false;
		}

		// Never overridable. A logged-out visitor must never see this, on any
		// environment, under any configuration.
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) ) {
			return false;
		}

		$library = isset( $args['library'] ) ? untrailingslashit( esc_url_raw( $args['library'] ) ) : '';
		if ( ! $library ) {
			return false;
		}

		return array(
			'library'  => $library,
			'key'      => isset( $args['key'] ) ? (string) $args['key'] : '',
			'roles'    => self::ROLES,
			'storage'  => self::STORAGE,
			// Drives the amber warning state on the toggle, so a lab left on
			// against a production-typed environment is visually obvious rather
			// than something you have to remember.
			'flagged'  => ( $is_production && $allow_production ),
		);
	}

	public static function enqueue() {
		$config = self::config();
		if ( ! $config ) {
			return;
		}

		$dir = get_template_directory();
		$uri = get_template_directory_uri();

		wp_enqueue_style(
			self::HANDLE,
			$uri . '/assets/font-lab.css',
			array(),
			(string) filemtime( $dir . '/assets/font-lab.css' )
		);
		wp_enqueue_script(
			self::HANDLE,
			$uri . '/assets/font-lab.js',
			array(),
			(string) filemtime( $dir . '/assets/font-lab.js' ),
			true
		);
		wp_add_inline_script(
			self::HANDLE,
			'window.cccFontLabConfig = ' . wp_json_encode( $config ) . ';',
			'before'
		);

		/*
		 * Re-apply the stored selection in <head>, synchronously, BEFORE first
		 * paint. The panel script runs in the footer, so without this the page
		 * would paint in the theme's own fonts and then visibly reflow into the
		 * chosen ones on every navigation — which makes it impossible to judge
		 * a typeface. Only the custom properties are set here; the @font-face
		 * rules are injected by the panel script once it loads.
		 */
		add_action( 'wp_head', array( __CLASS__, 'early_restore' ), 1 );
	}

	public static function early_restore() {
		if ( ! self::config() ) {
			return;
		}
		$roles = wp_json_encode( array_keys( self::ROLES ) );
		?>
<script id="ccc-font-lab-restore">
(function(){try{
	var s = JSON.parse(localStorage.getItem(<?php echo wp_json_encode( self::STORAGE ); ?>) || '{}');
	var roles = <?php echo $roles; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_json_encode output. ?>;
	roles.forEach(function(role){
		if (s[role] && s[role].stack) {
			document.documentElement.style.setProperty('--wp--preset--font-family--' + role, s[role].stack);
		}
	});
}catch(e){}})();
</script>
		<?php
	}

	public static function render() {
		$config = self::config();
		if ( ! $config ) {
			return;
		}
		?>
<div id="ccc-font-lab" class="ccc-font-lab" hidden>
	<button type="button" class="ccc-font-lab__toggle<?php echo $config['flagged'] ? ' is-flagged' : ''; ?>"
		aria-expanded="false" aria-controls="ccc-font-lab-panel"
		<?php if ( $config['flagged'] ) : ?>title="<?php esc_attr_e( 'Font Lab is running on a production-typed environment (allow_production). Remove the flag before launch.', 'classic-city-core' ); ?>"<?php endif; ?>>
		<span class="ccc-font-lab__toggle-glyph" aria-hidden="true">Aa</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Open font lab', 'classic-city-core' ); ?></span>
	</button>

	<section id="ccc-font-lab-panel" class="ccc-font-lab__panel" aria-label="<?php esc_attr_e( 'Font lab', 'classic-city-core' ); ?>">
		<header class="ccc-font-lab__head">
			<strong><?php esc_html_e( 'Font Lab', 'classic-city-core' ); ?></strong>
			<?php if ( $config['flagged'] ) : ?>
			<span class="ccc-font-lab__warn" title="<?php esc_attr_e( 'allow_production is set in the child theme', 'classic-city-core' ); ?>"><?php esc_html_e( 'prod', 'classic-city-core' ); ?></span>
			<?php endif; ?>
			<span class="ccc-font-lab__count" data-ccc-font-lab-count></span>
			<button type="button" class="ccc-font-lab__close" aria-label="<?php esc_attr_e( 'Close', 'classic-city-core' ); ?>">&times;</button>
		</header>

		<nav class="ccc-font-lab__roles" role="tablist">
			<?php foreach ( self::ROLES as $slug => $label ) : ?>
			<button type="button" role="tab" class="ccc-font-lab__role" data-role="<?php echo esc_attr( $slug ); ?>">
				<?php echo esc_html( $label ); ?>
			</button>
			<?php endforeach; ?>
		</nav>

		<div class="ccc-font-lab__controls">
			<input type="search" class="ccc-font-lab__search" data-ccc-font-lab-search
				placeholder="<?php esc_attr_e( 'Search name or mood…', 'classic-city-core' ); ?>"
				aria-label="<?php esc_attr_e( 'Search fonts', 'classic-city-core' ); ?>" />
			<label class="ccc-font-lab__filter">
				<input type="checkbox" data-ccc-font-lab-bodysafe />
				<?php esc_html_e( 'Body-safe only', 'classic-city-core' ); ?>
			</label>
			<label class="ccc-font-lab__filter">
				<input type="checkbox" data-ccc-font-lab-accents />
				<?php esc_html_e( 'Has accents', 'classic-city-core' ); ?>
			</label>
		</div>

		<ul class="ccc-font-lab__list" data-ccc-font-lab-list></ul>

		<footer class="ccc-font-lab__foot">
			<button type="button" class="ccc-font-lab__btn" data-ccc-font-lab-reset>
				<?php esc_html_e( 'Reset', 'classic-city-core' ); ?>
			</button>
			<button type="button" class="ccc-font-lab__btn ccc-font-lab__btn--primary" data-ccc-font-lab-export>
				<?php esc_html_e( 'Copy theme.json', 'classic-city-core' ); ?>
			</button>
		</footer>
		<pre class="ccc-font-lab__export" data-ccc-font-lab-output hidden></pre>
	</section>
</div>
		<?php
	}
}

CCC_Font_Lab::boot();
