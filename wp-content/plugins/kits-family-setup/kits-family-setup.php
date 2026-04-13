<?php
/**
 * Plugin Name:  Kits for Family Times — Site Setup
 * Plugin URI:   https://github.com/NicolasChoo/Final-Exam-take-4
 * Description:  Creates all content (posts, pages, categories) for the Kits for Family Times website. Activate once after installing the child theme.
 * Version:      1.0.0
 * Author:       Nicolas Choo
 * License:      GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ───────────────────────────────────────────────────────────────
//  ACTIVATION HOOK — runs all setup tasks
// ───────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, 'kits_setup_run' );

// Also run on init in case direct activation isn't triggered (Playground / WP-WASM)
add_action( 'init', 'kits_setup_maybe_run', 20 );
function kits_setup_maybe_run() {
    if ( ! get_option( 'kits_setup_done' ) ) {
        kits_setup_run();
    }
}

function kits_setup_run() {
    if ( get_option( 'kits_setup_done' ) ) return; // idempotent

    kits_create_categories();
    kits_create_editorial_posts();
    kits_create_product_posts();
    kits_create_pages();
    kits_set_homepage();
    kits_activate_child_theme();

    update_option( 'kits_setup_done', '1' );
}

// ───────────────────────────────────────────────────────────────
//  1. CATEGORIES
// ───────────────────────────────────────────────────────────────
function kits_create_categories() {
    wp_insert_term( 'Stress Relief & Wellness', 'category', [
        'slug'        => 'stress-relief',
        'description' => 'How family kits help reduce stress and promote wellbeing.',
    ] );
    wp_insert_term( 'Kit Products', 'category', [
        'slug'        => 'kit-products',
        'description' => 'Sample activity kits available for families.',
    ] );
    wp_insert_term( 'Family Activities', 'category', [
        'slug'        => 'family-activities',
        'description' => 'Ideas and inspiration for family time at home.',
    ] );
}

// ───────────────────────────────────────────────────────────────
//  HELPER — get or create image attachment from URL
// ───────────────────────────────────────────────────────────────
function kits_sideload_image( $url, $post_id, $alt ) {
    if ( ! function_exists( 'media_sideload_image' ) ) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
    $attach_id = media_sideload_image( $url, $post_id, $alt, 'id' );
    if ( ! is_wp_error( $attach_id ) ) {
        set_post_thumbnail( $post_id, $attach_id );
    }
}

// ───────────────────────────────────────────────────────────────
//  2. FIVE EDITORIAL POSTS (stress-relief angle, SEO landing pages)
// ───────────────────────────────────────────────────────────────
function kits_create_editorial_posts() {
    $cat = get_cat_ID( 'Stress Relief & Wellness' );

    $editorial_posts = [

        [
            'title'   => 'How Family Kit Night Can Melt Away the Week\'s Stress',
            'slug'    => 'family-kit-night-stress-relief',
            'excerpt' => 'Discover why setting aside one evening a week for a shared activity kit can dramatically lower household stress — for kids and parents alike.',
            'content' => kits_post_content_1(),
            'image'   => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?w=800&q=80',
            'alt'     => 'Happy family doing a craft activity together at a kitchen table',
        ],

        [
            'title'   => 'The Science Behind Why Making Things Together Reduces Anxiety',
            'slug'    => 'science-making-together-reduces-anxiety',
            'excerpt' => 'Research confirms that hands-on creative activity lowers cortisol levels. Here\'s how family kits put that science to work in your living room.',
            'content' => kits_post_content_2(),
            'image'   => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=800&q=80',
            'alt'     => 'Parent and child working on a craft project together, smiling',
        ],

        [
            'title'   => '5 Ways Activity Kits Bring Families Closer and Calm Anxious Minds',
            'slug'    => '5-ways-activity-kits-calm-families',
            'excerpt' => 'From focused attention to shared pride in a finished project, activity kits deliver five powerful stress-busting benefits your whole family will feel.',
            'content' => kits_post_content_3(),
            'image'   => 'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=800&q=80',
            'alt'     => 'Family sitting together at a table working on a colorful craft kit',
        ],

        [
            'title'   => 'From Screen Time to Family Time: Kits That Help Everyone Unwind',
            'slug'    => 'screen-time-to-family-kit-time',
            'excerpt' => 'Swapping even 45 minutes of scrolling for a hands-on kit can reset the family\'s nervous system. Here\'s how to make the transition fun, not forced.',
            'content' => kits_post_content_4(),
            'image'   => 'https://images.unsplash.com/photo-1560807707-8cc77767d783?w=800&q=80',
            'alt'     => 'Children building a project away from screens, looking engaged and happy',
        ],

        [
            'title'   => 'Weekend Reset: How a Simple Activity Kit Transforms Family Stress',
            'slug'    => 'weekend-reset-activity-kit-family-stress',
            'excerpt' => 'Monday worries fade faster than you\'d expect when Friday evening includes a family kit. We explore the weekend-reset ritual that therapists are recommending.',
            'content' => kits_post_content_5(),
            'image'   => 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?w=800&q=80',
            'alt'     => 'Family laughing together over a finished craft project on a weekend afternoon',
        ],

    ];

    foreach ( $editorial_posts as $data ) {
        // Skip if slug already exists
        if ( get_page_by_path( $data['slug'], OBJECT, 'post' ) ) continue;

        $post_id = wp_insert_post( [
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_content' => $data['content'],
            'post_excerpt' => $data['excerpt'],
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_category'=> [ $cat ],
        ] );

        if ( $post_id && ! is_wp_error( $post_id ) ) {
            wp_set_post_tags( $post_id, [ 'stress relief', 'family kits', 'activity kits', 'family wellness' ] );
            kits_sideload_image( $data['image'], $post_id, $data['alt'] );
        }
    }
}

// ── Editorial post content ─────────────────────────────────────

function kits_post_content_1() {
    return <<<HTML
<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large">
  <img src="https://images.unsplash.com/photo-1516627145497-ae6968895b74?w=800&q=80" alt="Happy family crafting together" class="wp-image-auto"/>
  <figcaption class="wp-element-caption">A shared kit project can become the highlight of the week.</figcaption>
</figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"stress-banner"} -->
<div class="stress-banner">
  Did you know that <strong>87% of parents</strong> report lower stress levels after a shared creative activity with their children? A family kit is one of the easiest ways to make that happen tonight.
</div>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why One Evening a Week Changes Everything</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Monday through Friday the household machine runs at full speed — school lunches, work deadlines, homework battles, and the relentless ping of notifications. By the time Friday arrives, everyone's cortisol tank is full. The antidote isn't complicated: it's a table, a box of materials, and 60 minutes of making something together.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Activity kits remove the hardest part of starting a creative project: the planning. When everything arrives pre-selected and ready to use, the family can skip straight to the good part — actually doing it. Research from the American Art Therapy Association links hands-on creative work to measurable reductions in stress hormones, improved mood, and stronger feelings of connection between participants.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The Ritual That Sticks</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Consistency is key. Families who schedule their kit night at the same time each week report that children begin to anticipate it positively — sometimes reminding parents when the day arrives. That anticipation itself is a stress buffer: having something pleasant to look forward to is a well-documented coping mechanism in positive psychology.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Start small. Even a 30-minute candle painting kit or a simple mosaic activity can break the anxiety loop. The goal isn't a museum-worthy finished product; it's 30 minutes where everyone is focused on the same thing, side by side, phone face-down on the counter.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Getting Started This Week</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Browse our growing collection of family kits — sorted by age range, activity type, and time needed. Every kit comes with clear instructions so adults don't need prior experience. Find your first kit tonight and schedule your first kit night for this Friday.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
  <div class="wp-block-button"><a class="wp-block-button__link" href="/shop/">Browse Family Kits</a></div>
</div>
<!-- /wp:buttons -->
HTML;
}

function kits_post_content_2() {
    return <<<HTML
<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large">
  <img src="https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=800&q=80" alt="Parent and child crafting together" class="wp-image-auto"/>
  <figcaption class="wp-element-caption">Making something together creates a biochemical environment for calm.</figcaption>
</figure>
<!-- /wp:image -->

<!-- wp:heading -->
<h2>What Happens in the Brain When We Make Things Together</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Neuroscience has given us a clearer picture of why crafting works so well as a stress reliever. When we engage in repetitive, purposeful physical actions — cutting, gluing, painting, folding — the brain shifts from its default mode network (linked to rumination and anxiety) toward task-positive networks associated with focus and reward. The result is what psychologists call a "flow state": absorbed attention that crowds out worry.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>A 2016 study published in <em>Art Therapy: Journal of the American Art Therapy Association</em> found that just 45 minutes of creative activity significantly lowered cortisol levels — regardless of the participant's artistic skill level. You don't have to be crafty for the biology to work.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The Social Amplifier</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The stress-reducing effects are amplified when the creative activity is shared. Co-regulation — the process by which a calm person's nervous system helps regulate a stressed person's — is a cornerstone of family mental health. When a parent is calm and focused on a project, children literally "catch" that calm through shared attention and mirroring behavior.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Activity kits provide the structure that makes co-regulation easy. Instead of navigating the open-ended anxiety of "let's be creative," a kit gives everyone clear, achievable steps. Each small success — a completed section, a chosen color combination, a piece that fits just right — triggers a small dopamine release that keeps motivation alive and worry at bay.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why Kits Work Better Than Free Play for Stressed Families</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Free-form creative play is wonderful — but when everyone is already depleted, the blank canvas can feel like one more decision to make. Kits lower the decision burden to near zero. The materials are chosen, the steps are outlined, and the end goal is visible. This "scaffolded creativity" preserves the therapeutic benefits of making while removing the cognitive overhead that exhausted families struggle with most.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
  <div class="wp-block-button"><a class="wp-block-button__link" href="/shop/">Find Your Kit</a></div>
</div>
<!-- /wp:buttons -->
HTML;
}

function kits_post_content_3() {
    return <<<HTML
<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large">
  <img src="https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=800&q=80" alt="Family working on a colorful craft kit" class="wp-image-auto"/>
</figure>
<!-- /wp:image -->

<!-- wp:heading -->
<h2>1. Shared Focus Quiets Individual Anxiety</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>When the whole family looks at the same task, individual worries recede. The brain can only hold so much conscious attention; filling it with "where does this piece go?" leaves no room for "what if things go wrong tomorrow?" A kit's clear instructions give everyone a shared focal point that acts as a collective mindfulness exercise.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. Accomplishment Builds Resilience</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Completing a kit — even a simple one — produces a tangible result. Holding up a finished project and saying "we made that" delivers a shot of self-efficacy at a time when many families feel like life is happening <em>to</em> them rather than <em>with</em> them. That small victory resets the internal narrative from helpless to capable, a shift psychologists associate with higher stress resilience.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3. Screen-Free Time Lowers Baseline Cortisol</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Screens trigger micro-stressors: notifications, comparison, news, rapid content switching. Even 45 minutes away from screens during an evening kit session gives the sympathetic nervous system a genuine rest. Studies from the University of Pennsylvania link decreased social media use to significant improvements in anxiety and depression within one week.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>4. Conversation Flows More Naturally</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Side-by-side activity creates what researchers call "parallel play" for adults — an environment where conversation happens organically without the pressure of face-to-face interrogation. Parents often report that children share things during a kit session that they never would at the dinner table. The activity provides emotional cover that makes honesty easier.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>5. The Ritual Signals Safety</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Predictable, positive rituals tell a child's (and adult's) nervous system: <em>this is a safe time</em>. Safety is the opposite of stress. When kit night becomes a recurring family institution, its approach alone begins to dial down anxiety in the days beforehand — the anticipation of calm is itself calming.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
  <div class="wp-block-button"><a class="wp-block-button__link" href="/shop/">Shop Kits by Activity</a></div>
</div>
<!-- /wp:buttons -->
HTML;
}

function kits_post_content_4() {
    return <<<HTML
<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large">
  <img src="https://images.unsplash.com/photo-1560807707-8cc77767d783?w=800&q=80" alt="Children engaged with a hands-on project, not screens" class="wp-image-auto"/>
  <figcaption class="wp-element-caption">Engagement with a physical project is a natural counterbalance to screen fatigue.</figcaption>
</figure>
<!-- /wp:image -->

<!-- wp:heading -->
<h2>The Screen Fatigue Problem</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>The average American family logs more than 11 hours of combined screen time per day. For children, the number is rising year over year. Screen fatigue — the mental exhaustion that comes from too much passive or reactive digital consumption — manifests as irritability, shortened attention spans, disrupted sleep, and heightened anxiety. Sound familiar?</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why Telling Kids to "Put the Phone Down" Doesn't Work</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>The device is filling a need: stimulation, social connection, entertainment, or numbing. Simply removing it creates a vacuum that children experience as deprivation — which is stressful in itself. The replacement has to be genuinely appealing. That's where kits change the game. A well-chosen kit offers novelty, hands-on stimulation, and the social reward of a parent's undivided attention — all things screens provide, but without the cortisol-spiking downsides.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The 45-Minute Transition Formula</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><strong>Step 1 — Announce, don't surprise.</strong> Give 10 minutes' notice before kit time. Abrupt transitions cause resistance. "Kit night in 10!" feels exciting; yanked devices feel punitive.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><strong>Step 2 — Set up together.</strong> Let kids help unbox the kit. The tactile unpacking — feeling the materials, reading the instructions — activates curiosity and begins the cognitive shift from passive consumption to active creation.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><strong>Step 3 — Leave your own phone in another room.</strong> Children respond to modeling more than instruction. Your phone-free presence signals that this time is genuinely valued.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><strong>Step 4 — Don't aim for perfection.</strong> The project doesn't need to be Instagram-worthy. A lopsided candle or a slightly uneven mosaic is a family memory, not a failure. Laughing at mistakes together is worth more than a flawless finished product.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
  <div class="wp-block-button"><a class="wp-block-button__link" href="/shop/">Find a Kit for Tonight</a></div>
</div>
<!-- /wp:buttons -->
HTML;
}

function kits_post_content_5() {
    return <<<HTML
<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large">
  <img src="https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?w=800&q=80" alt="Family laughing over a finished craft project" class="wp-image-auto"/>
  <figcaption class="wp-element-caption">The weekend reset isn't a big event — it's a small, consistent ritual.</figcaption>
</figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"stress-banner"} -->
<div class="stress-banner">
  Therapists increasingly recommend <strong>structured weekend rituals</strong> as one of the most effective tools for managing accumulated weekday stress. A shared family kit is one of the simplest forms this ritual can take.
</div>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Why the Weekend Reset Matters</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Without intentional decompression, weekday stress bleeds into the weekend — and then into the following week. Many families report that by Sunday evening they feel as wound-up as they did on Friday, despite two days of "time off." The reason is that unstructured rest isn't the same as restorative rest. True recovery requires active engagement in something meaningful and low-stakes.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>What Therapists Are Recommending</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Family therapists and child psychologists increasingly prescribe structured, screen-free family activities as "stress inoculation" — building the family's collective capacity to handle stress before it becomes a crisis. The prescription is specific: short (30–90 minutes), physical, creative, and completed together. Activity kits check every box.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The Monday Difference</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Families who practice a weekly kit ritual consistently report a measurable "Monday difference" — children are less resistant to the school morning routine, parents feel more patient, and the household starts the week from a calmer baseline. The mechanism is simple: a positive shared experience at the end of the week primes the relational system for cooperation rather than friction.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Building Your Family Reset Ritual</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Pick a kit that interests everyone — not just the easiest or cheapest option, but something that genuinely sparks curiosity. Schedule it at the same time each week. Protect it like a calendar appointment. Within three weeks, the ritual will begin to run itself. Within a month, you'll notice the Monday difference.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
  <div class="wp-block-button"><a class="wp-block-button__link" href="/shop/">Start Your Reset Ritual</a></div>
</div>
<!-- /wp:buttons -->
HTML;
}

// ───────────────────────────────────────────────────────────────
//  3. FIVE SAMPLE KIT PRODUCT POSTS
// ───────────────────────────────────────────────────────────────
function kits_create_product_posts() {
    $cat = get_cat_ID( 'Kit Products' );

    $products = [

        [
            'title'   => 'Garden Fairy House Kit — Build a Magical World Together',
            'slug'    => 'garden-fairy-house-kit',
            'excerpt' => 'Craft a whimsical miniature fairy house from natural materials. Perfect for ages 5–12 — no glue gun required.',
            'content' => kits_product_content( [
                'name'        => 'Garden Fairy House Kit',
                'tagline'     => 'Build a tiny world with big imagination.',
                'description' => 'Everything you need to create a hand-painted miniature fairy cottage — including a pre-cut wooden house form, natural moss, pebbles, dried flowers, non-toxic paint, and a step-by-step illustrated guide. Best completed over two 30-minute sessions so the paint can dry between coats.',
                'ages'        => '5–12 years (adult supervision for younger children)',
                'time'        => '60–90 minutes total',
                'includes'    => [ 'Pre-cut wooden house form', 'Assorted non-toxic paints (6 colors)', 'Natural moss pad', 'River pebbles bag', 'Dried flower mix', 'Craft glue', 'Illustrated instruction booklet' ],
                'stress_note' => 'The meditative act of painting tiny details is remarkably calming for children and adults alike. Perfect for rainy Saturday afternoons.',
                'image'       => 'https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=800&q=80',
                'alt'         => 'Miniature fairy house made from natural materials and wood',
            ] ),
            'image'   => 'https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=800&q=80',
            'alt'     => 'Miniature fairy house craft project',
        ],

        [
            'title'   => 'Family Candle Making Kit — Create Calm Scents at Home',
            'slug'    => 'family-candle-making-kit',
            'excerpt' => 'Pour, scent, and decorate your own soy candles as a family. A sensory experience that doubles as a stress-relief ritual.',
            'content' => kits_product_content( [
                'name'        => 'Family Candle Making Kit',
                'tagline'     => 'Pour calm into every room.',
                'description' => 'A complete soy candle making set designed for families. Choose from three calming fragrance blends — Lavender Grove, Citrus Burst, or Vanilla Hearth — then pour, cool, and decorate with included dried botanicals and decorative labels you personalize together.',
                'ages'        => '8+ years (adult pours the hot wax)',
                'time'        => '45–60 minutes + 2 hours cooling',
                'includes'    => [ '2 lbs pre-measured soy wax', '3 fragrance oils', 'Pre-tabbed cotton wicks (6)', '4 glass candle jars', 'Dried botanical mix', 'Personalized label sheets', 'Wooden craft sticks for stirring', 'Step-by-step guide' ],
                'stress_note' => 'Scent is one of the most direct pathways to the brain\'s calm center. Making candles while choosing fragrances together is an act of shared intentional calm.',
                'image'       => 'https://images.unsplash.com/photo-1608181831718-f34d1ea10cae?w=800&q=80',
                'alt'         => 'Family making soy candles together, pouring wax into glass jars',
            ] ),
            'image'   => 'https://images.unsplash.com/photo-1608181831718-f34d1ea10cae?w=800&q=80',
            'alt'     => 'Soy candle making family kit',
        ],

        [
            'title'   => 'Tie-Dye Family Fun Kit — Colorful Creativity for All Ages',
            'slug'    => 'tie-dye-family-fun-kit',
            'excerpt' => 'Six vibrant dye colors, four white garments, and unlimited creativity — the classic stress-releasing activity that never gets old.',
            'content' => kits_product_content( [
                'name'        => 'Tie-Dye Family Fun Kit',
                'tagline'     => 'Wear what you made together.',
                'description' => 'The perennial family favorite, perfected. Our kit includes pre-measured fiber-reactive dye in six vivid colors, rubber bands, gloves, and four blank garments (two adult tees and two child tees in your chosen sizes). The instruction booklet teaches six classic folding patterns from beginner spiral to advanced bullseye.',
                'ages'        => 'All ages (adults handle dye mixing)',
                'time'        => '45 minutes active + overnight setting',
                'includes'    => [ '6 fiber-reactive dye bottles', '2 adult blank tees', '2 child blank tees', 'Rubber bands (30)', 'Latex-free gloves (4 pairs)', 'Plastic squeeze bottles', 'Drop cloth', 'Pattern instruction booklet' ],
                'stress_note' => 'The unpredictability of tie-dye is part of the therapy — learning to delight in unexpected results rather than needing control is a powerful stress-management lesson.',
                'image'       => 'https://images.unsplash.com/photo-1596552185290-426cb7ceeda0?w=800&q=80',
                'alt'         => 'Bright colorful tie-dye shirts made by a family',
            ] ),
            'image'   => 'https://images.unsplash.com/photo-1596552185290-426cb7ceeda0?w=800&q=80',
            'alt'     => 'Tie-dye family craft kit with colorful results',
        ],

        [
            'title'   => 'Family Mosaic Art Kit — Piece Together Beautiful Memories',
            'slug'    => 'family-mosaic-art-kit',
            'excerpt' => 'Create a stunning mosaic picture frame or stepping stone together. A mindful, focused activity that produces a keepsake you\'ll display for years.',
            'content' => kits_product_content( [
                'name'        => 'Family Mosaic Art Kit',
                'tagline'     => 'Every piece matters.',
                'description' => 'Choose between two project options: a decorative picture frame (great for indoor display) or a garden stepping stone (for outdoor use). Both projects use glass tile pieces, a grout pen, and our non-toxic mosaic adhesive on the included form. The kit contains enough materials for all four family members to contribute sections.',
                'ages'        => '6+ years',
                'time'        => '90 minutes active + 3 hours dry time',
                'includes'    => [ 'Glass mosaic tiles (500 pieces, 8 colors)', 'Pre-formed frame OR stepping stone base', 'Non-toxic mosaic adhesive', 'Grout pen', 'Tile nippers (child-safe design)', 'Soft-grip tweezers', 'Design template sheet', 'Care & display guide' ],
                'stress_note' => 'The rhythmic, focused act of placing small tiles one at a time is a natural mindfulness practice. Many families report deep, unforced conversation emerging during mosaic sessions.',
                'image'       => 'https://images.unsplash.com/photo-1579033461380-adb8b73a88db?w=800&q=80',
                'alt'         => 'Colorful mosaic art project made by a family',
            ] ),
            'image'   => 'https://images.unsplash.com/photo-1579033461380-adb8b73a88db?w=800&q=80',
            'alt'     => 'Family mosaic art kit with colorful glass tiles',
        ],

        [
            'title'   => 'Home Sourdough Starter Kit — The Ultimate Family Baking Adventure',
            'slug'    => 'home-sourdough-starter-kit',
            'excerpt' => 'Feed your starter, bake your first loaf, and discover why slow food is the best family stress reliever. Includes a live starter culture ready to activate.',
            'content' => kits_product_content( [
                'name'        => 'Home Sourdough Starter Kit',
                'tagline'     => 'Patience, flour, and family — a recipe for calm.',
                'description' => 'Sourdough is patience made edible. Our starter kit includes a live, ready-to-activate sourdough culture, a glazed ceramic crock, artisan flour blend, and a 40-page illustrated family guide with recipes scaled for baking together. The multi-day process teaches children about biology, time, and the reward of waiting — skills that directly counter anxiety.',
                'ages'        => 'All ages (oven use is adult only)',
                'time'        => 'Day 1: 20 min setup; Day 3–5: 30 min daily feeding; Day 5–7: first bake (2 hours)',
                'includes'    => [ 'Live sourdough starter culture', 'Glazed ceramic crock with lid', 'Artisan flour blend (5 lbs)', '40-page illustrated family recipe guide', 'Dough scraper', 'Bread scoring lame', 'Kitchen thermometer', 'Linen proofing cloth' ],
                'stress_note' => 'Caring for a living starter gives children (and adults) a daily purpose ritual. The smell of baking bread is one of the most universally calming sensory experiences humans know.',
                'image'       => 'https://images.unsplash.com/photo-1585478751688-9b5d2a40f2e4?w=800&q=80',
                'alt'         => 'Family baking sourdough bread together, flour on hands and smiles',
            ] ),
            'image'   => 'https://images.unsplash.com/photo-1585478751688-9b5d2a40f2e4?w=800&q=80',
            'alt'     => 'Sourdough bread baking family kit',
        ],

    ];

    foreach ( $products as $data ) {
        if ( get_page_by_path( $data['slug'], OBJECT, 'post' ) ) continue;

        $post_id = wp_insert_post( [
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_content' => $data['content'],
            'post_excerpt' => $data['excerpt'],
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_category'=> [ $cat ],
        ] );

        if ( $post_id && ! is_wp_error( $post_id ) ) {
            wp_set_post_tags( $post_id, [ 'family kits', 'activity kits', 'craft kits', 'DIY family' ] );
            kits_sideload_image( $data['image'], $post_id, $data['alt'] );
        }
    }
}

function kits_product_content( $p ) {
    $includes_html = '';
    foreach ( $p['includes'] as $item ) {
        $includes_html .= '<!-- wp:list-item --><li>' . esc_html( $item ) . '</li><!-- /wp:list-item -->' . "\n";
    }

    return <<<HTML
<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large">
  <img src="{$p['image']}" alt="{$p['alt']}" class="wp-image-auto"/>
</figure>
<!-- /wp:image -->

<!-- wp:heading {"level":2} -->
<h2>{$p['tagline']}</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>{$p['description']}</p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns">
  <!-- wp:column -->
  <div class="wp-block-column">
    <!-- wp:heading {"level":3} -->
    <h3>Age Range</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p>{$p['ages']}</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:column -->
  <!-- wp:column -->
  <div class="wp-block-column">
    <!-- wp:heading {"level":3} -->
    <h3>Time Required</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph -->
    <p>{$p['time']}</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:heading {"level":3} -->
<h3>What's in the Box</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
{$includes_html}
</ul>
<!-- /wp:list -->

<!-- wp:paragraph {"className":"stress-banner"} -->
<div class="stress-banner">
  <strong>Stress Relief Spotlight:</strong> {$p['stress_note']}
</div>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
  <div class="wp-block-button"><a class="wp-block-button__link" href="/shop/">View All Kits</a></div>
</div>
<!-- /wp:buttons -->
HTML;
}

// ───────────────────────────────────────────────────────────────
//  4. PAGES
// ───────────────────────────────────────────────────────────────
function kits_create_pages() {
    kits_create_home_page();
    kits_create_shop_page();
    kits_create_registration_page();
}

function kits_create_home_page() {
    if ( get_page_by_path( 'home' ) ) return;
    wp_insert_post( [
        'post_title'   => 'Home',
        'post_name'    => 'home',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => <<<HTML
<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">Kits for at Home Family Times</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textAlign":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Creative activity kits that bring families together, reduce stress, and make memories — one project at a time.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[kit_chooser]
<!-- /wp:shortcode -->

<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity"/>
<!-- /wp:separator -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Why Families Love Kit Nights</h2>
<!-- /wp:heading -->

<!-- wp:columns {"isStackedOnMobile":true} -->
<div class="wp-block-columns">
  <!-- wp:column -->
  <div class="wp-block-column" style="text-align:center;padding:1.5rem">
    <p style="font-size:2.5rem">&#127774;</p>
    <h3>Reduce Stress</h3>
    <p>Hands-on creativity lowers cortisol and quiets anxious minds for the whole family.</p>
  </div>
  <!-- /wp:column -->
  <!-- wp:column -->
  <div class="wp-block-column" style="text-align:center;padding:1.5rem">
    <p style="font-size:2.5rem">&#10084;&#65039;</p>
    <h3>Connect Deeply</h3>
    <p>Side-by-side making creates conversation and closeness that screens can't provide.</p>
  </div>
  <!-- /wp:column -->
  <!-- wp:column -->
  <div class="wp-block-column" style="text-align:center;padding:1.5rem">
    <p style="font-size:2.5rem">&#127775;</p>
    <h3>Make Memories</h3>
    <p>Every finished project is a keepsake — a physical reminder of time well spent together.</p>
  </div>
  <!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
  <div class="wp-block-button"><a class="wp-block-button__link" href="/shop/">Browse All Kits</a></div>
  <div class="wp-block-button is-style-outline"><a class="wp-block-button__link" href="/kit-developer-registration/">Become a Kit Developer</a></div>
</div>
<!-- /wp:buttons -->
HTML,
    ] );
}

function kits_create_shop_page() {
    if ( get_page_by_path( 'shop' ) ) return;
    wp_insert_post( [
        'post_title'   => 'Shop Kits',
        'post_name'    => 'shop',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => <<<HTML
<!-- wp:heading {"textAlign":"center"} -->
<h1 class="wp-block-heading has-text-align-center">Find Your Perfect Family Kit</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textAlign":"center"} -->
<p class="has-text-align-center">Browse our curated collection of hands-on activity kits — designed to bring families together and melt the week's stress away.</p>
<!-- /wp:paragraph -->

<!-- wp:query {"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","categoryIds":[],"tagIds":[],"order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}} -->
<div class="wp-block-query">
  <!-- wp:post-template -->
    <!-- wp:post-featured-image {"isLink":true} /-->
    <!-- wp:post-title {"isLink":true} /-->
    <!-- wp:post-excerpt {"moreText":"Read More →"} /-->
  <!-- /wp:post-template -->
  <!-- wp:query-pagination -->
    <!-- wp:query-pagination-previous /-->
    <!-- wp:query-pagination-numbers /-->
    <!-- wp:query-pagination-next /-->
  <!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
HTML,
    ] );
}

function kits_create_registration_page() {
    if ( get_page_by_path( 'kit-developer-registration' ) ) return;
    wp_insert_post( [
        'post_title'   => 'Kit Developer Registration',
        'post_name'    => 'kit-developer-registration',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => <<<HTML
<!-- wp:heading {"textAlign":"center"} -->
<h1 class="wp-block-heading has-text-align-center">Become a Kit Developer</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textAlign":"center","fontSize":"medium"} -->
<p class="has-text-align-center has-medium-font-size">Do you design creative activities, curate craft supplies, or develop educational experiences? We want to hear from you. Tell us in 100 words why you'd make a great kit developer for our community.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[kit_registration_form]
<!-- /wp:shortcode -->
HTML,
    ] );
}

// ───────────────────────────────────────────────────────────────
//  5. SET FRONT PAGE
// ───────────────────────────────────────────────────────────────
function kits_set_homepage() {
    $home = get_page_by_path( 'home' );
    if ( $home ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $home->ID );
    }
}

// ───────────────────────────────────────────────────────────────
//  6. ACTIVATE CHILD THEME
// ───────────────────────────────────────────────────────────────
function kits_activate_child_theme() {
    $current = get_option( 'stylesheet' );
    if ( $current !== 'kits-family-child' ) {
        switch_theme( 'kits-family-child' );
    }
}

// ───────────────────────────────────────────────────────────────
//  REGISTRATION FORM SHORTCODE
// ───────────────────────────────────────────────────────────────
add_shortcode( 'kit_registration_form', 'kits_registration_form' );
function kits_registration_form( $atts ) {
    $notice  = '';
    $success = false;

    if ( isset( $_POST['kit_reg_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kit_reg_nonce'] ) ), 'kit_registration' ) ) {
        $name        = sanitize_text_field( wp_unslash( $_POST['kit_name'] ?? '' ) );
        $email       = sanitize_email( wp_unslash( $_POST['kit_email'] ?? '' ) );
        $business    = sanitize_text_field( wp_unslash( $_POST['kit_business'] ?? '' ) );
        $summary_raw = wp_unslash( $_POST['kit_summary'] ?? '' );
        $summary     = sanitize_textarea_field( $summary_raw );
        $word_count  = str_word_count( $summary );
        $errors      = [];

        if ( empty( $name ) )                   $errors[] = 'Please enter your name.';
        if ( ! is_email( $email ) )             $errors[] = 'Please enter a valid email address.';
        if ( empty( $summary ) )                $errors[] = 'Please enter your 100-word summary.';
        if ( $word_count < 80 )                 $errors[] = "Your summary is only {$word_count} words. Please write at least 80 words.";
        if ( $word_count > 120 )                $errors[] = "Your summary is {$word_count} words. Please keep it to 100–120 words maximum.";

        if ( empty( $errors ) ) {
            // Save as a draft post for admin review
            $post_id = wp_insert_post( [
                'post_title'   => 'Kit Developer Application: ' . $name,
                'post_content' => $summary,
                'post_status'  => 'draft',
                'post_type'    => 'post',
                'meta_input'   => [
                    '_kit_applicant_name'     => $name,
                    '_kit_applicant_email'    => $email,
                    '_kit_applicant_business' => $business,
                    '_kit_word_count'         => $word_count,
                ],
            ] );

            // Email notification to admin
            wp_mail(
                get_option( 'admin_email' ),
                'New Kit Developer Application: ' . $name,
                "Name: {$name}\nEmail: {$email}\nBusiness: {$business}\n\nSummary ({$word_count} words):\n{$summary}",
                [ 'Content-Type: text/plain; charset=UTF-8' ]
            );

            $success = true;
            $notice  = '<div class="kit-notice kit-notice--success">Thank you, ' . esc_html( $name ) . '! Your application has been received. We review all submissions within 5 business days and will be in touch at ' . esc_html( $email ) . '.</div>';
        } else {
            $notice = '<div class="kit-notice kit-notice--error"><strong>Please fix the following:</strong><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $errors ) ) . '</li></ul></div>';
        }
    }

    ob_start();
    ?>
    <div class="kit-registration-wrap">

        <?php echo $notice; ?>

        <?php if ( ! $success ) : ?>
        <form method="post" action="<?php echo esc_url( get_permalink() ); ?>" novalidate>
            <?php wp_nonce_field( 'kit_registration', 'kit_reg_nonce' ); ?>

            <div class="form-group">
                <label for="kit_name">Full Name <span aria-hidden="true">*</span></label>
                <input type="text" id="kit_name" name="kit_name"
                       value="<?php echo esc_attr( $_POST['kit_name'] ?? '' ); ?>"
                       required autocomplete="name" placeholder="Your full name"/>
            </div>

            <div class="form-group">
                <label for="kit_email">Email Address <span aria-hidden="true">*</span></label>
                <input type="email" id="kit_email" name="kit_email"
                       value="<?php echo esc_attr( $_POST['kit_email'] ?? '' ); ?>"
                       required autocomplete="email" placeholder="you@example.com"/>
            </div>

            <div class="form-group">
                <label for="kit_business">Business or Studio Name <span aria-hidden="true">(optional)</span></label>
                <input type="text" id="kit_business" name="kit_business"
                       value="<?php echo esc_attr( $_POST['kit_business'] ?? '' ); ?>"
                       autocomplete="organization" placeholder="Your creative studio or brand name"/>
            </div>

            <div class="form-group">
                <label for="kit_summary">
                    Why Would You Make a Great Kit Developer?
                    <span aria-hidden="true">* <small>(100 words)</small></span>
                </label>
                <textarea id="kit_summary" name="kit_summary"
                          required
                          aria-describedby="word-count-info"
                          placeholder="Tell us about your background, the kinds of kits you'd create, and why families would love making them. Write approximately 100 words."><?php echo esc_textarea( $_POST['kit_summary'] ?? '' ); ?></textarea>
                <div class="word-count-bar" id="word-count-info" aria-live="polite">
                    Word count: <span class="count" id="kit-word-count">0</span> / 100
                </div>
            </div>

            <button type="submit" class="kit-btn kit-submit">
                Submit My Application &rarr;
            </button>
        </form>
        <?php endif; ?>

    </div>

    <script>
    (function () {
        const ta = document.getElementById('kit_summary');
        const counter = document.getElementById('kit-word-count');
        if (!ta || !counter) return;

        function countWords(str) {
            return str.trim() === '' ? 0 : str.trim().split(/\s+/).length;
        }

        function updateCount() {
            const n = countWords(ta.value);
            counter.textContent = n;
            counter.classList.toggle('over', n > 120);
            counter.parentElement.querySelector('.word-count-bar') &&
                (counter.style.color = n >= 80 && n <= 120 ? 'var(--color-primary)' : n > 120 ? '#e63946' : '');
        }

        ta.addEventListener('input', updateCount);
        updateCount(); // run on page load (pre-filled on error)
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ───────────────────────────────────────────────────────────────
//  ADMIN NOTICE — remind admin to set theme if needed
// ───────────────────────────────────────────────────────────────
add_action( 'admin_notices', 'kits_admin_notice' );
function kits_admin_notice() {
    if ( get_option( 'stylesheet' ) !== 'kits-family-child' ) {
        echo '<div class="notice notice-info is-dismissible"><p><strong>Kits for Family Times:</strong> Please activate the <em>Kits for Family Times</em> child theme under <a href="' . esc_url( admin_url( 'themes.php' ) ) . '">Appearance → Themes</a> to complete setup.</p></div>';
    }
}
