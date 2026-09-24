# Tutorial Videos — standalone drop-in

Self-contained "Tools → Tutorial Videos" admin page for any WordPress
theme, including older ones that aren't built on the Classic City Core
parent theme. Zero plugin dependencies, zero parent-theme coupling.

What it gives the site:

- **Tools → Tutorial Videos** admin page (capability: `manage_options`).
- 2-column responsive card grid of training videos, each with embed,
  title, and description.
- Add form below the grid — paste any provider's full embed code
  (YouTube, Vimeo, Loom, Wistia, etc.). No oEmbed gymnastics.
- Per-card "Remove" link.
- Idempotent add (transient-keyed) so a double-click doesn't dupe rows.

Data is stored in a single WordPress option (`{prefix}_tutorial_videos`)
as a flat array of `{ id, title, embed_code, description }`. No custom
post type, no custom tables.

---

## Install (3 minutes)

### 1. Drop the file in

Save the [PHP code](#the-code) below into the theme at:

```
{theme-root}/inc/class-tutorial-videos.php
```

If the theme has no `inc/` directory, put it wherever existing PHP
includes live (often just the theme root). Path doesn't matter — the
loader just `require_once`s it.

### 2. Wire it into `functions.php`

Add this single line near the bottom of `functions.php`:

```php
if ( is_admin() ) {
    require_once get_stylesheet_directory() . '/inc/class-tutorial-videos.php';
}
```

Adjust the path if you put the file elsewhere. Wrapping in
`is_admin()` keeps the class out of frontend requests (it's
admin-only code).

### 3. Done

Refresh wp-admin. New menu item appears at **Tools → Tutorial
Videos**.

---

## Rename to avoid collisions (recommended)

Before installing in a theme, find-and-replace these tokens so the
class, option, page slug, and form hooks are unique to that theme.
This matters mainly if the site might one day install another copy
(e.g., from a plugin) — but it's cheap insurance.

| Token in the code below | Replace with something like |
|---|---|
| `Theme_Tutorial_Videos` (class name) | `Acme_Tutorial_Videos` |
| `theme_tutorial_videos` (option key) | `acme_tutorial_videos` |
| `theme-tutorial-videos` (page slug) | `acme-tutorial-videos` |
| `theme_tv_add` / `theme_tv_delete` (action names) | `acme_tv_add` / `acme_tv_delete` |
| `'your-theme'` (text domain) | the theme's actual text domain, or just drop the `__()` wrapper |

A single search-and-replace on the file handles it.

---

## The code

Save everything in this fenced block to `inc/class-tutorial-videos.php`:

```php
<?php
/**
 * Tools → Tutorial Videos admin page (standalone, plugin-free).
 *
 * Stores a flat list of { title, embed_code, description } in the
 * `theme_tutorial_videos` option and renders each provider's pasted
 * embed code as-is. Capability-gated to `manage_options`.
 *
 * @package YourTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Theme_Tutorial_Videos {

    const OPTION_KEY = 'theme_tutorial_videos';
    const PAGE_SLUG  = 'theme-tutorial-videos';

    public static function boot() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_post_theme_tv_add',    array( __CLASS__, 'handle_add' ) );
        add_action( 'admin_post_theme_tv_delete', array( __CLASS__, 'handle_delete' ) );
    }

    public static function register_menu() {
        add_management_page(
            __( 'Tutorial Videos', 'your-theme' ),
            __( 'Tutorial Videos', 'your-theme' ),
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
            wp_die( esc_html__( 'Insufficient permissions.', 'your-theme' ) );
        }
        check_admin_referer( 'theme_tv_add' );

        $title       = isset( $_POST['title'] )       ? sanitize_text_field( wp_unslash( $_POST['title'] ) )           : '';
        $embed_code  = isset( $_POST['embed_code'] )  ? trim( wp_unslash( $_POST['embed_code'] ) )                    : '';
        $description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

        if ( $title === '' || $embed_code === '' ) {
            self::redirect_back( array( 'tv_error' => rawurlencode( __( 'Title and embed code are required.', 'your-theme' ) ) ) );
        }

        // Idempotency: identical title+embed POSTed by same user within 10s
        // is treated as a no-op. Guards against double-clicks and resubmits.
        $dedupe_key = 'theme_tv_add_' . md5( get_current_user_id() . '|' . $title . '|' . $embed_code );
        if ( get_transient( $dedupe_key ) ) {
            self::redirect_back( array( 'tv_added' => 1 ) );
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

        self::redirect_back( array( 'tv_added' => 1 ) );
    }

    public static function handle_delete() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'your-theme' ) );
        }
        $id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
        check_admin_referer( 'theme_tv_delete_' . $id );

        $videos = self::get_videos();
        $videos = array_values( array_filter( $videos, function ( $v ) use ( $id ) {
            return ( $v['id'] ?? '' ) !== $id;
        } ) );
        self::save_videos( $videos );

        self::redirect_back( array( 'tv_deleted' => 1 ) );
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient permissions.', 'your-theme' ) );
        }

        $videos = self::get_videos();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Tutorial Videos', 'your-theme' ) . '</h1>';
        echo '<p style="margin:0 0 1.5em;color:#555;">' . esc_html__( 'Add training and how-to videos for site editors. Paste the embed code (the full <iframe>… or <script>… HTML) from YouTube, Vimeo, Loom, Wistia, or any other provider\'s "Share → Embed" dialog.', 'your-theme' ) . '</p>';

        if ( isset( $_GET['tv_added'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Video added.', 'your-theme' ) . '</p></div>';
        }
        if ( isset( $_GET['tv_deleted'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Video removed.', 'your-theme' ) . '</p></div>';
        }
        if ( isset( $_GET['tv_error'] ) ) {
            echo '<div class="notice notice-error"><p>' . esc_html( wp_unslash( $_GET['tv_error'] ) ) . '</p></div>';
        }

        self::styles();
        self::render_grid( $videos );
        self::render_add_form();

        echo '</div>';
    }

    private static function styles() {
        echo '<style>
            .ttv-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:20px; margin:0 0 2em; }
            @media (max-width: 782px) { .ttv-grid { grid-template-columns:1fr; } }
            .ttv-card { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:16px; display:flex; flex-direction:column; gap:10px; }
            .ttv-card .ttv-embed { position:relative; padding-bottom:56.25%; height:0; overflow:hidden; background:#000; border-radius:3px; }
            .ttv-card .ttv-embed iframe,
            .ttv-card .ttv-embed object,
            .ttv-card .ttv-embed embed,
            .ttv-card .ttv-embed video { position:absolute; top:0; left:0; width:100%; height:100%; }
            .ttv-card .ttv-embed-fallback { padding:24px; color:#fff; text-align:center; }
            .ttv-card h2 { font-size:1.05em; margin:4px 0 0; line-height:1.3; }
            .ttv-card .ttv-desc { color:#555; font-size:13px; margin:0; white-space:pre-wrap; }
            .ttv-card .ttv-actions { margin-top:auto; padding-top:6px; border-top:1px solid #f0f0f1; }
            .ttv-card .ttv-actions a { color:#b32d2e; text-decoration:none; font-size:12px; }
            .ttv-card .ttv-actions a:hover { text-decoration:underline; }
            .ttv-empty { background:#fff; border:1px dashed #c3c4c7; border-radius:4px; padding:24px; text-align:center; color:#777; margin:0 0 2em; }
            .ttv-form { background:#fff; border:1px solid #c3c4c7; border-radius:4px; padding:20px; max-width:720px; }
            .ttv-form h2 { margin-top:0; }
            .ttv-form .form-row { margin:0 0 14px; }
            .ttv-form label { display:block; font-weight:600; margin:0 0 4px; font-size:13px; }
            .ttv-form input[type=text], .ttv-form textarea { width:100%; max-width:100%; }
            .ttv-form textarea { min-height:80px; }
        </style>';
    }

    private static function render_grid( $videos ) {
        if ( empty( $videos ) ) {
            echo '<div class="ttv-empty">' . esc_html__( 'No tutorial videos yet. Add one below.', 'your-theme' ) . '</div>';
            return;
        }

        echo '<div class="ttv-grid">';
        foreach ( $videos as $v ) {
            $id          = $v['id']          ?? '';
            $title       = $v['title']       ?? '';
            $description = $v['description'] ?? '';
            $embed_code  = $v['embed_code']  ?? '';

            $embed_html = $embed_code !== '' ? $embed_code : '<div class="ttv-embed-fallback">' . esc_html__( 'No embed code on this entry.', 'your-theme' ) . '</div>';

            $delete_url = wp_nonce_url(
                add_query_arg(
                    array(
                        'action' => 'theme_tv_delete',
                        'id'     => $id,
                    ),
                    admin_url( 'admin-post.php' )
                ),
                'theme_tv_delete_' . $id
            );

            echo '<div class="ttv-card">';
            echo '<div class="ttv-embed">' . $embed_html . '</div>'; // Trusted: pasted by an admin (manage_options).
            if ( $title !== '' ) {
                echo '<h2>' . esc_html( $title ) . '</h2>';
            }
            if ( $description !== '' ) {
                echo '<p class="ttv-desc">' . esc_html( $description ) . '</p>';
            }
            echo '<div class="ttv-actions">';
            echo '<a href="' . esc_url( $delete_url ) . '" onclick="return confirm(\'' . esc_js( __( 'Remove this video?', 'your-theme' ) ) . '\');">' . esc_html__( 'Remove', 'your-theme' ) . '</a>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    private static function render_add_form() {
        echo '<div class="ttv-form">';
        echo '<h2>' . esc_html__( 'Add a video', 'your-theme' ) . '</h2>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="var s=this.querySelector(\'#submit\');if(s.disabled){return false;}s.disabled=true;s.value=\'Adding\\u2026\';">';
        echo '<input type="hidden" name="action" value="theme_tv_add" />';
        wp_nonce_field( 'theme_tv_add' );

        echo '<div class="form-row">';
        echo '<label for="ttv_title">' . esc_html__( 'Title', 'your-theme' ) . '</label>';
        echo '<input type="text" id="ttv_title" name="title" required />';
        echo '</div>';

        echo '<div class="form-row">';
        echo '<label for="ttv_embed_code">' . esc_html__( 'Embed code', 'your-theme' ) . '</label>';
        echo '<textarea id="ttv_embed_code" name="embed_code" rows="5" placeholder="' . esc_attr__( 'Paste the full embed code — e.g. <iframe src=\"https://www.loom.com/embed/...\"></iframe>', 'your-theme' ) . '" required></textarea>';
        echo '<p class="description" style="margin-top:4px;font-size:12px;color:#777;">' . esc_html__( 'On Loom: Share → Embed → Copy embed code. On YouTube: Share → Embed → Copy.', 'your-theme' ) . '</p>';
        echo '</div>';

        echo '<div class="form-row">';
        echo '<label for="ttv_description">' . esc_html__( 'Description', 'your-theme' ) . '</label>';
        echo '<textarea id="ttv_description" name="description"></textarea>';
        echo '</div>';

        submit_button( __( 'Add video', 'your-theme' ) );

        echo '</form>';
        echo '</div>';
    }
}

Theme_Tutorial_Videos::boot();
```

---

## How editors use it

1. Open the provider (Loom, YouTube, Vimeo, etc.).
2. **Share → Embed → Copy embed code.**
3. In wp-admin, go to **Tools → Tutorial Videos**.
4. Paste the embed code into the textarea, fill in Title and
   Description, click **Add video**.
5. Card appears in the grid above. Use the **Remove** link to delete.

---

## Security notes

- Page is capability-gated to `manage_options` (admins only on
  single-site; super-admins on multisite). Same trust model as
  the Custom HTML widget and the Theme/Plugin file editors —
  admins can already execute arbitrary PHP, so accepting arbitrary
  embed HTML from them is not an escalation.
- Form is nonce-protected (`wp_nonce_field` + `check_admin_referer`).
- Delete actions are individually nonce-protected per-row.
- All non-embed user input (title, description) is sanitized.
- Embed code is rendered verbatim — that's the whole point. Do not
  loosen the capability check.

---

## Why pasted embed code instead of oEmbed?

WordPress oEmbed wraps non-trusted-provider iframes with
`sandbox="allow-scripts" security="restricted"`, which strips cookies
and storage from the embedded player. Loom (and others) renders as a
black box because the player can't initialize without those.
Removing the filter works for some providers, but not all. Asking
editors to paste the provider's own embed code sidesteps the whole
oEmbed pipeline and Just Works — providers give exactly the HTML they
expect to render.

---

## Requirements

- WordPress 5.2+ (uses `wp_generate_uuid4()`, available since 4.7).
- PHP 7.4+ (uses null-coalescing assignment, short array syntax).
- No plugins.
- No build step.
