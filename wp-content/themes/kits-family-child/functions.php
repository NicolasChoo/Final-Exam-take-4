<?php
/**
 * Kits for Family Times — Child Theme Functions
 * Parent: Twenty Twenty-Five
 */

// ── 1. Enqueue parent + child styles and Google Fonts ──────────────────────
add_action( 'wp_enqueue_scripts', 'kits_child_enqueue_styles' );
function kits_child_enqueue_styles() {
    // Google Fonts: Playfair Display (headings) + Nunito (body)
    wp_enqueue_style(
        'kits-google-fonts',
        'https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@400;700;900&display=swap',
        [],
        null
    );

    // Parent theme stylesheet
    wp_enqueue_style(
        'twentytwentyfive-style',
        get_template_directory_uri() . '/style.css',
        [ 'kits-google-fonts' ]
    );

    // Child theme overrides
    wp_enqueue_style(
        'kits-child-style',
        get_stylesheet_uri(),
        [ 'twentytwentyfive-style' ],
        wp_get_theme()->get( 'Version' )
    );
}

// ── 2. Favicon / site icon ─────────────────────────────────────────────────
add_action( 'wp_head', 'kits_child_favicon' );
function kits_child_favicon() {
    $favicon_url = get_stylesheet_directory_uri() . '/favicon.svg';
    echo '<link rel="icon" type="image/svg+xml" href="' . esc_url( $favicon_url ) . '">' . "\n";
    echo '<link rel="alternate icon" href="' . esc_url( $favicon_url ) . '">' . "\n";
}

// ── 3. Theme setup ─────────────────────────────────────────────────────────
add_action( 'after_setup_theme', 'kits_child_setup' );
function kits_child_setup() {
    add_theme_support( 'custom-logo', [
        'height'      => 80,
        'width'       => 220,
        'flex-height' => true,
        'flex-width'  => true,
    ] );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption' ] );
}

// ── 4. Register Kit Chooser shortcode ──────────────────────────────────────
// Usage: [kit_chooser]  (place on front page via block / shortcode block)
add_shortcode( 'kit_chooser', 'kits_render_chooser' );
function kits_render_chooser( $atts ) {
    $atts = shortcode_atts( [
        'shop_url' => home_url( '/shop/' ),
        'dev_url'  => home_url( '/kit-developer-registration/' ),
    ], $atts, 'kit_chooser' );

    ob_start(); ?>
    <div class="kit-chooser" aria-label="Find your path">

        <div class="kit-chooser__card kit-chooser__card--maker">
            <span class="kit-chooser__icon" aria-hidden="true">&#127984;</span>
            <h3>I Want to Make a Kit</h3>
            <p>Browse our curated collection of family activity kits — everything you need to unwind, create, and connect at home.</p>
            <a class="kit-btn" href="<?php echo esc_url( $atts['shop_url'] ); ?>">
                Shop Kits &rarr;
            </a>
        </div>

        <div class="kit-chooser__card kit-chooser__card--developer">
            <span class="kit-chooser__icon" aria-hidden="true">&#128736;&#65039;</span>
            <h3>I Want to Develop Kits</h3>
            <p>Are you a maker, educator, or creative business? Tell us why you'd create amazing kits for families — apply to become a kit developer.</p>
            <a class="kit-btn" href="<?php echo esc_url( $atts['dev_url'] ); ?>">
                Apply as Developer &rarr;
            </a>
        </div>

    </div>
    <?php
    return ob_get_clean();
}

// ── 5. Activate the setup plugin automatically ─────────────────────────────
add_action( 'after_setup_theme', 'kits_auto_activate_setup_plugin' );
function kits_auto_activate_setup_plugin() {
    if ( ! is_admin() ) return;
    $plugin = 'kits-family-setup/kits-family-setup.php';
    if ( ! is_plugin_active( $plugin ) ) {
        activate_plugin( $plugin );
    }
}
