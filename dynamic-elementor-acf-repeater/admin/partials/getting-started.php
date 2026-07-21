<?php
/**
 * Feature guide for the plugin admin page.
 *
 * @package DynamicElementorAcfRepeater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_pro          = function_exists( 'earluna_can_use_premium_code' ) && earluna_can_use_premium_code();
$edition_label   = $is_pro ? __( 'Pro active', 'dynamic-elementor-acf-repeater' ) : __( 'Free edition', 'dynamic-elementor-acf-repeater' );
$templates_url   = admin_url( 'edit.php?post_type=elementor_library&tabs_group=library' );
$docs_url        = 'https://calculabs.github.io/elementor-acf-repeater-docs/';
$support_url     = 'https://wordpress.org/support/plugin/dynamic-elementor-acf-repeater/';
$feature_version = defined( 'DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION' ) ? DYNAMIC_ELEMENTOR_ACF_REPEATER_VERSION : '';

$features = array(
	array(
		'eyebrow'  => __( 'Discover rows', 'dynamic-elementor-acf-repeater' ),
		'title'    => __( 'Search, sort, and narrow', 'dynamic-elementor-acf-repeater' ),
		'copy'     => __( 'Offer keyword search, stable ordering, number or date ranges, and Flexible Content layout selection before final pagination.', 'dynamic-elementor-acf-repeater' ),
		'location' => __( 'Loop Grid or Loop Carousel → Content → Row Search & Sorting', 'dynamic-elementor-acf-repeater' ),
		'badge'    => __( 'New in 2.3', 'dynamic-elementor-acf-repeater' ),
		'class'    => 'rose',
	),
	array(
		'eyebrow'  => __( 'Filter rows', 'dynamic-elementor-acf-repeater' ),
		'title'    => __( 'Use Elementor’s native filter', 'dynamic-elementor-acf-repeater' ),
		'copy'     => __( 'Connect Elementor Pro’s Taxonomy Filter to repeater-row values while preserving native deep links, Load More, and numbered pagination.', 'dynamic-elementor-acf-repeater' ),
		'location' => __( 'Taxonomy Filter → Selected Element; Loop widget → Repeater taxonomy source', 'dynamic-elementor-acf-repeater' ),
		'badge'    => __( 'Opt in', 'dynamic-elementor-acf-repeater' ),
		'class'    => 'mint',
	),
	array(
		'eyebrow'  => __( 'Open details', 'dynamic-elementor-acf-repeater' ),
		'title'    => __( 'Open repeater rows in a lightbox', 'dynamic-elementor-acf-repeater' ),
		'copy'     => __( 'Reuse the same Loop Item design for the selected repeater row, then choose only the overlay, close, navigation, sizing, and visibility controls you want.', 'dynamic-elementor-acf-repeater' ),
		'location' => __( 'Loop Grid or Loop Carousel → Content → Repeater Lightbox', 'dynamic-elementor-acf-repeater' ),
		'badge'    => __( 'Opt in', 'dynamic-elementor-acf-repeater' ),
		'class'    => 'violet',
	),
	array(
		'eyebrow'  => __( 'Keep it native', 'dynamic-elementor-acf-repeater' ),
		'title'    => __( 'Render rows in Loop Carousel', 'dynamic-elementor-acf-repeater' ),
		'copy'     => __( 'Keep Elementor’s arrows, bullets, breakpoints, drag, swipe, and familiar editor while every logical slide comes from a repeater row.', 'dynamic-elementor-acf-repeater' ),
		'location' => __( 'Loop Carousel → Query → Use ACF Rows', 'dynamic-elementor-acf-repeater' ),
		'badge'    => __( 'Pro', 'dynamic-elementor-acf-repeater' ),
		'class'    => 'blue',
	),
	array(
		'eyebrow'  => __( 'Model deeply', 'dynamic-elementor-acf-repeater' ),
		'title'    => __( 'Groups, Flexible Content, and nested Repeaters', 'dynamic-elementor-acf-repeater' ),
		'copy'     => __( 'Select stable field-key paths, map layouts to different Loop Items, and flatten nested rows without moving content into a replacement widget.', 'dynamic-elementor-acf-repeater' ),
		'location' => __( 'Loop Item Page Settings → ACF Row Schema; Loop widget → ACF Row Source', 'dynamic-elementor-acf-repeater' ),
		'badge'    => __( 'Pro', 'dynamic-elementor-acf-repeater' ),
		'class'    => 'amber',
	),
	array(
		'eyebrow'  => __( 'Curate posts', 'dynamic-elementor-acf-repeater' ),
		'title'    => __( 'Query Relationship and Post Object paths', 'dynamic-elementor-acf-repeater' ),
		'copy'     => __( 'Follow top-level or nested Relationship/Post Object selections, preserve editorial order, and hand the real selected posts to Elementor.', 'dynamic-elementor-acf-repeater' ),
		'location' => __( 'Loop Grid or Loop Carousel → Query → Query Relationship Posts', 'dynamic-elementor-acf-repeater' ),
		'badge'    => __( 'Pro', 'dynamic-elementor-acf-repeater' ),
		'class'    => 'plum',
	),
);
?>

<main class="ear-admin">
	<section class="ear-admin__hero">
		<div class="ear-admin__hero-copy">
			<div class="ear-admin__chips">
				<span class="ear-admin__chip ear-admin__chip--edition"><?php echo esc_html( $edition_label ); ?></span>
				<span class="ear-admin__chip"><?php echo esc_html( sprintf( __( 'Version %s', 'dynamic-elementor-acf-repeater' ), $feature_version ) ); ?></span>
				<span class="ear-admin__chip"><?php esc_html_e( 'Everything advanced is opt in', 'dynamic-elementor-acf-repeater' ); ?></span>
			</div>
			<p class="ear-admin__kicker"><?php esc_html_e( 'Dynamic ACF Repeater for Elementor', 'dynamic-elementor-acf-repeater' ); ?></p>
			<h1><?php esc_html_e( 'Your fields stay structured. Your design stays Elementor.', 'dynamic-elementor-acf-repeater' ); ?></h1>
			<p class="ear-admin__lede"><?php esc_html_e( 'Build one native Loop Item, then let ACF Pro or Secure Custom Fields rows supply the content. Add richer behavior only where a project needs it.', 'dynamic-elementor-acf-repeater' ); ?></p>
			<div class="ear-admin__actions">
				<a class="ear-admin__button ear-admin__button--primary" href="<?php echo esc_url( $templates_url ); ?>"><?php esc_html_e( 'Open Elementor Templates', 'dynamic-elementor-acf-repeater' ); ?></a>
				<a class="ear-admin__button" href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read the documentation ↗', 'dynamic-elementor-acf-repeater' ); ?></a>
			</div>
		</div>
		<div class="ear-admin__hero-model" aria-hidden="true">
			<div class="ear-admin__model-source"><small>ACF / SCF</small><strong>Repeater rows</strong><span>Structured content</span></div>
			<i></i>
			<div class="ear-admin__model-template"><small>Elementor</small><strong>One Loop Item</strong><span>Your design</span></div>
			<i></i>
			<div class="ear-admin__model-output"><small>Native</small><strong>Grid or Carousel</strong><span>Real interactions</span></div>
		</div>
	</section>

	<section class="ear-admin__promise" aria-label="Compatibility promise">
		<div><span class="dashicons dashicons-lock"></span><strong><?php esc_html_e( 'Nothing changes by surprise.', 'dynamic-elementor-acf-repeater' ); ?></strong></div>
		<p><?php esc_html_e( 'Existing widgets keep their saved behavior. Advanced controls are disabled until you enable them on that specific Loop Grid or Loop Carousel. No global migration and no manufactured card markup.', 'dynamic-elementor-acf-repeater' ); ?></p>
	</section>

	<section class="ear-admin__section">
		<div class="ear-admin__section-heading">
			<p><?php esc_html_e( 'The three-part workflow', 'dynamic-elementor-acf-repeater' ); ?></p>
			<h2><?php esc_html_e( 'Model once. Design once. Publish anywhere.', 'dynamic-elementor-acf-repeater' ); ?></h2>
		</div>
		<div class="ear-admin__steps">
			<article><span>01</span><h3><?php esc_html_e( 'Choose the row schema', 'dynamic-elementor-acf-repeater' ); ?></h3><p><?php esc_html_e( 'Create the Repeater, Flexible Content, nested, or Relationship structure your editors actually need.', 'dynamic-elementor-acf-repeater' ); ?></p></article>
			<article><span>02</span><h3><?php esc_html_e( 'Build the Loop Item', 'dynamic-elementor-acf-repeater' ); ?></h3><p><?php esc_html_e( 'Select that schema in Page Settings, then bind ordinary Elementor widgets with repeater dynamic tags.', 'dynamic-elementor-acf-repeater' ); ?></p></article>
			<article><span>03</span><h3><?php esc_html_e( 'Enable rows on the widget', 'dynamic-elementor-acf-repeater' ); ?></h3><p><?php esc_html_e( 'Use Elementor’s Loop Grid or Loop Carousel and turn on only the enhancements the page needs.', 'dynamic-elementor-acf-repeater' ); ?></p></article>
		</div>
	</section>

	<section class="ear-admin__section ear-admin__section--features">
		<div class="ear-admin__section-heading">
			<p><?php esc_html_e( 'Pro feature guide', 'dynamic-elementor-acf-repeater' ); ?></p>
			<h2><?php esc_html_e( 'Every enhancement has a clear switch.', 'dynamic-elementor-acf-repeater' ); ?></h2>
			<span><?php esc_html_e( 'The location line tells you exactly where to find it in Elementor.', 'dynamic-elementor-acf-repeater' ); ?></span>
		</div>
		<div class="ear-admin__feature-grid">
			<?php foreach ( $features as $feature ) : ?>
				<article class="ear-admin__feature ear-admin__feature--<?php echo esc_attr( $feature['class'] ); ?>">
					<div class="ear-admin__feature-top"><span><?php echo esc_html( $feature['eyebrow'] ); ?></span><b><?php echo esc_html( $feature['badge'] ); ?></b></div>
					<h3><?php echo esc_html( $feature['title'] ); ?></h3>
					<p><?php echo esc_html( $feature['copy'] ); ?></p>
					<div class="ear-admin__location"><span class="dashicons dashicons-admin-settings"></span><span><?php echo esc_html( $feature['location'] ); ?></span></div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="ear-admin__compatibility">
		<div>
			<p><?php esc_html_e( 'Built for the stack you already use', 'dynamic-elementor-acf-repeater' ); ?></p>
			<h2><?php esc_html_e( 'ACF Pro or Secure Custom Fields. Elementor Pro’s native Loop widgets.', 'dynamic-elementor-acf-repeater' ); ?></h2>
		</div>
		<ul>
			<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'No replacement grid or carousel widget', 'dynamic-elementor-acf-repeater' ); ?></li>
			<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'No shortcode-driven design workflow', 'dynamic-elementor-acf-repeater' ); ?></li>
			<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Per-widget opt-in behavior', 'dynamic-elementor-acf-repeater' ); ?></li>
			<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Responsive editor and frontend previews', 'dynamic-elementor-acf-repeater' ); ?></li>
		</ul>
	</section>

	<footer class="ear-admin__footer">
		<div><strong><?php esc_html_e( 'Need a hand?', 'dynamic-elementor-acf-repeater' ); ?></strong><span><?php esc_html_e( 'Start with the docs, then bring a reproducible example to support.', 'dynamic-elementor-acf-repeater' ); ?></span></div>
		<div><a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'dynamic-elementor-acf-repeater' ); ?></a><a href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support forum', 'dynamic-elementor-acf-repeater' ); ?></a></div>
	</footer>
</main>
