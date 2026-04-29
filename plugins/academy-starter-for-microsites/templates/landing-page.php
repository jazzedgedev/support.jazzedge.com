<?php
/**
 * Landing page template.
 *
 * @package Academy_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$display_name    = isset( $data['display_name'] ) ? $data['display_name'] : __( 'Jazz Piano', 'academy-starter' );
$keyword_phrases = isset( $data['keyword_phrases'] ) && is_array( $data['keyword_phrases'] ) ? $data['keyword_phrases'] : array();
$primary_keyword = ! empty( $keyword_phrases[0] ) ? $keyword_phrases[0] : strtolower( $display_name );
$paid_url        = ! empty( $data['paid_url'] ) ? $data['paid_url'] : AcademyStarterAdmin::DEFAULT_PAID_URL;
$free_form_id    = ! empty( $data['free_form_id'] ) ? absint( $data['free_form_id'] ) : 0;
$schema          = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'WebPage',
	'name'        => $data['hero_headline'],
	'description' => $data['hero_subheadline'],
	'keywords'    => $keyword_phrases,
);

$faq_schema_items = array(
	array(
		'q' => __( 'What is included in the Academy Starter program?', 'academy-starter' ),
		'a' => __( 'The free plan includes the full 30-Day Piano Playbook — over 3.5 hours of structured lessons. Starter Plus adds the Blues Piano Blueprint, Rock Piano Blueprint, and Super Simple Standards for 6+ hours total. Every lesson includes downloadable resources: sheet music PDFs, backing tracks, and iReal Pro files — all included and yours to keep forever.', 'academy-starter' ),
	),
	array(
		'q' => __( 'Is Academy Starter good for complete beginners?', 'academy-starter' ),
		'a' => __( 'Yes. The program starts with absolute fundamentals — rhythm, technique, reading, and chords — and builds gradually so there is no guesswork about what to practice.', 'academy-starter' ),
	),
	array(
		'q' => sprintf( __( 'Will this help me learn %s specifically?', 'academy-starter' ), $display_name ),
		'a' => sprintf( __( 'Yes. The Starter program builds the core skills essential before diving deeper into %s-specific lessons, songs, and techniques available inside JazzEdge Academy.', 'academy-starter' ), $display_name ),
	),
	array(
		'q' => __( 'How long do I have access?', 'academy-starter' ),
		'a' => __( 'Starter Plus includes 90 days of full access from the date of purchase. After that you can upgrade to a full JazzEdge Academy membership or repurchase Academy Starter anytime.', 'academy-starter' ),
	),
	array(
		'q' => __( 'Is the $7 program a subscription?', 'academy-starter' ),
		'a' => __( 'No. Starter Plus is a single one-time payment of $7. There are no recurring charges, auto-renewals, or hidden fees.', 'academy-starter' ),
	),
	array(
		'q' => __( 'Can I access lessons on my phone or tablet?', 'academy-starter' ),
		'a' => __( 'Yes. All lessons are accessible on desktop, laptop, tablet, and smartphone through your JazzEdge Academy account.', 'academy-starter' ),
	),
	array(
		'q' => __( 'Where do I log in to access my lessons?', 'academy-starter' ),
		'a' => __( 'After signing up, log in at jazzedge.academy. Your lessons and progress will be waiting for you there.', 'academy-starter' ),
	),
	array(
		'q' => __( 'What downloadable resources are included?', 'academy-starter' ),
		'a' => __( 'Every lesson comes with sheet music (PDF), backing tracks (MP3), and iReal Pro chord chart files. These are yours to download and keep forever — even after your 90-day access period ends.', 'academy-starter' ),
	),
	array(
		'q' => __( 'What if I want more than the Starter program right now?', 'academy-starter' ),
		'a' => __( 'You can skip the Starter and go straight to a full program at the JazzEdge Shop. You will find individual courses, bundles, and full academy memberships to match any budget or learning goal.', 'academy-starter' ),
	),
	array(
		'q' => sprintf( __( 'Do you have individual %s courses I can purchase?', 'academy-starter' ), $display_name ),
		'a' => __( 'Yes. The JazzEdge Shop has individual courses, lesson packs, and bundles available for one-time purchase.', 'academy-starter' ),
	),
	array(
		'q' => __( 'Can I upgrade to a full membership later?', 'academy-starter' ),
		'a' => __( 'Yes. You can upgrade to Essentials, Studio, or Premier at any time from JazzEdge Academy. Your Starter progress carries forward seamlessly.', 'academy-starter' ),
	),
);

if ( ! empty( $data['style_faqs'] ) && is_array( $data['style_faqs'] ) ) {
	foreach ( $data['style_faqs'] as $sfaq ) {
		$faq_schema_items[] = array(
			'q' => $sfaq['question'],
			'a' => $sfaq['answer'],
		);
	}
}

$faq_schema = array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => array_map(
		static function ( $item ) {
			return array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['a'],
				),
			);
		},
		$faq_schema_items
	),
);
?>

<div class="academy-starter" data-style="<?php echo esc_attr( $data['style_key'] ); ?>">
	<script type="application/ld+json"><?php echo wp_json_encode( $schema ); ?></script>
	<script type="application/ld+json"><?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES ); ?></script>

	<div class="academy-starter-promo-banner">
		<p><?php esc_html_e( '⚡ Limited Time Offer — Save $18 (72% OFF) Today!', 'academy-starter' ); ?></p>
	</div>

	<section class="academy-starter-hero">
		<div class="academy-starter-container academy-starter-hero-grid">
			<div class="academy-starter-hero-copy">
				<p class="academy-starter-kicker"><?php echo esc_html( sprintf( __( 'Academy Starter for %s', 'academy-starter' ), $display_name ) ); ?></p>
				<h1><?php echo esc_html( $data['hero_headline'] ); ?></h1>
				<p class="academy-starter-subheadline"><?php echo esc_html( $data['hero_subheadline'] ); ?></p>
				<p class="academy-starter-hero-note">
					<?php esc_html_e( 'Follow a clear 90-day path designed for students who want real results at the piano without guessing what to practice next.', 'academy-starter' ); ?>
				</p>
				<div class="academy-starter-cta-row">
					<button type="button" class="academy-starter-button academy-starter-button-primary academy-starter-free-cta" data-click-type="free">
						<?php esc_html_e( 'Start Free', 'academy-starter' ); ?>
					</button>
					<a class="academy-starter-button academy-starter-button-secondary academy-starter-paid-cta" data-click-type="paid" href="<?php echo esc_url( $paid_url ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Get Full Access for $7', 'academy-starter' ); ?>
					</a>
				</div>
				<div class="academy-starter-trust-row">
					<span><?php esc_html_e( 'No credit card for free access', 'academy-starter' ); ?></span>
					<span><?php esc_html_e( 'Structured beginner-friendly lessons', 'academy-starter' ); ?></span>
					<span><?php esc_html_e( '90 days of guided practice', 'academy-starter' ); ?></span>
				</div>
			</div>
			<div class="academy-starter-hero-panel" aria-label="<?php esc_attr_e( 'Program highlights', 'academy-starter' ); ?>">
				<div class="academy-starter-badge"><?php esc_html_e( 'Piano in 90 Days', 'academy-starter' ); ?></div>
				<h2><?php esc_html_e( 'What You Get', 'academy-starter' ); ?></h2>
				<ul>
					<li><?php esc_html_e( '30-Day Piano Playbook foundation', 'academy-starter' ); ?></li>
					<li><?php esc_html_e( 'Style-focused practice direction', 'academy-starter' ); ?></li>
					<li><?php esc_html_e( 'Bonus blueprints with Starter Plus', 'academy-starter' ); ?></li>
					<li><?php esc_html_e( '90 days of access', 'academy-starter' ); ?></li>
					<li><?php esc_html_e( 'Simple next steps for every practice session', 'academy-starter' ); ?></li>
				</ul>
				<p class="academy-starter-content-hours">
					<?php esc_html_e( 'Free: 3h 34min of lessons · Starter Plus: 6+ hours total', 'academy-starter' ); ?>
				</p>
				<p class="academy-starter-panel-login">
					<?php esc_html_e( 'Already have a Starter account?', 'academy-starter' ); ?>
					<a href="https://jazzedge.academy/login" target="_blank" rel="noopener">
						<?php esc_html_e( 'Log in at JazzEdge Academy →', 'academy-starter' ); ?>
					</a>
				</p>
			</div>
		</div>
	</section>

	<section class="academy-starter-section academy-starter-learn">
		<div class="academy-starter-container">
			<div class="academy-starter-section-heading">
				<p class="academy-starter-kicker"><?php esc_html_e( 'What You\'ll Learn', 'academy-starter' ); ?></p>
				<h2><?php echo esc_html( sprintf( __( 'Build Real Skills for %s Step by Step', 'academy-starter' ), $display_name ) ); ?></h2>
			</div>
			<div class="academy-starter-card-grid">
				<div class="academy-starter-benefit-card">
					<span>01</span>
					<h3><?php esc_html_e( 'Foundation', 'academy-starter' ); ?></h3>
					<p><?php echo esc_html( $data['benefit_1'] ); ?></p>
				</div>
				<div class="academy-starter-benefit-card">
					<span>02</span>
					<h3><?php esc_html_e( 'Practice Plan', 'academy-starter' ); ?></h3>
					<p><?php echo esc_html( $data['benefit_2'] ); ?></p>
				</div>
				<div class="academy-starter-benefit-card">
					<span>03</span>
					<h3><?php esc_html_e( 'Confidence', 'academy-starter' ); ?></h3>
					<p><?php echo esc_html( $data['benefit_3'] ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="academy-starter-section academy-starter-videos">
		<div class="academy-starter-container">
			<div class="academy-starter-section-heading">
				<p class="academy-starter-kicker"><?php esc_html_e( 'See What You\'ll Learn', 'academy-starter' ); ?></p>
				<h2><?php echo esc_html( sprintf( __( 'Sample Lessons From The %s Starter Program', 'academy-starter' ), $display_name ) ); ?></h2>
			</div>
			<div class="academy-starter-video-grid">
				<?php
				$videos = array(
					array(
						'title'       => __( 'Playbook Days 1–10', 'academy-starter' ),
						'description' => __( 'Start from zero. Build rhythm, hand position, and your first chords.', 'academy-starter' ),
						'badge'       => __( 'Free', 'academy-starter' ),
						'badge_class' => 'academy-starter-badge-free',
						'video_url'   => 'https://vz-0696d3da-4b7.b-cdn.net/54051011-d53c-4d62-862b-c53e442e7454/playlist.m3u8',
						'poster_url'  => 'https://jazzedge.academy/wp-content/uploads/2026/03/piano-playbook-1-10.webp',
					),
					array(
						'title'       => __( 'Playbook Days 11–20', 'academy-starter' ),
						'description' => __( 'Keep the momentum going. Expand your vocabulary and daily practice habits.', 'academy-starter' ),
						'badge'       => __( 'Free', 'academy-starter' ),
						'badge_class' => 'academy-starter-badge-free',
						'video_url'   => 'https://vz-0696d3da-4b7.b-cdn.net/b9c7e684-23bf-43ec-a2c9-3397a8ab95be/playlist.m3u8',
						'poster_url'  => 'https://jazzedge.academy/wp-content/uploads/2026/03/piano-playbook-11-20.webp',
					),
					array(
						'title'       => __( 'Playbook Days 21–30', 'academy-starter' ),
						'description' => __( 'More advanced concepts to identify your current level and what to focus on next.', 'academy-starter' ),
						'badge'       => __( 'Free', 'academy-starter' ),
						'badge_class' => 'academy-starter-badge-free',
						'video_url'   => 'https://vz-0696d3da-4b7.b-cdn.net/242c1de5-0aa8-46e0-a9d2-4ae46bb38eba/playlist.m3u8',
						'poster_url'  => 'https://jazzedge.academy/wp-content/uploads/2026/03/piano-playbook-21-30.webp',
					),
					array(
						'title'       => __( 'Blues Piano Blueprint', 'academy-starter' ),
						'description' => __( 'Shuffle, swing, stride, accompaniment, and improvisation. 54 minutes.', 'academy-starter' ),
						'badge'       => __( 'Starter Plus', 'academy-starter' ),
						'badge_class' => 'academy-starter-badge-paid',
						'video_url'   => 'https://vz-0696d3da-4b7.b-cdn.net/232eba77-666f-4af6-be64-288c0d64cb13/playlist.m3u8',
						'poster_url'  => 'https://jazzedge.academy/wp-content/uploads/2026/03/blues-blueprint.webp',
					),
					array(
						'title'       => __( 'Rock Piano Blueprint', 'academy-starter' ),
						'description' => __( 'Techniques for melody, accompaniment, and rock keyboard playing. 40 minutes.', 'academy-starter' ),
						'badge'       => __( 'Starter Plus', 'academy-starter' ),
						'badge_class' => 'academy-starter-badge-paid',
						'video_url'   => 'https://vz-0696d3da-4b7.b-cdn.net/a4b147ff-49ba-47e9-82be-5fd0635baec4/playlist.m3u8',
						'poster_url'  => 'https://jazzedge.academy/wp-content/uploads/2026/03/rock-blueprint.webp',
					),
					array(
						'title'       => __( 'Super Simple Standards', 'academy-starter' ),
						'description' => __( 'Simplified jazz and pop standards using chord shells. 1 hour 16 minutes.', 'academy-starter' ),
						'badge'       => __( 'Starter Plus', 'academy-starter' ),
						'badge_class' => 'academy-starter-badge-paid',
						'video_url'   => 'https://vz-0696d3da-4b7.b-cdn.net/d19a62a1-3389-4334-9cd5-d14b6ccebf48/playlist.m3u8',
						'poster_url'  => 'https://jazzedge.academy/wp-content/uploads/2026/03/jazz-blueprint.webp',
					),
				);
				?>

				<?php foreach ( $videos as $video ) : ?>
					<div class="academy-starter-video-card">
						<div class="academy-starter-video-embed">
							<?php if ( ! empty( $video['video_url'] ) ) : ?>
								<video class="academy-starter-video-element" controls preload="metadata" playsinline webkit-playsinline poster="<?php echo esc_url( $video['poster_url'] ); ?>" data-hls-src="<?php echo esc_url( $video['video_url'] ); ?>">
									<source src="<?php echo esc_url( $video['video_url'] ); ?>" type="application/x-mpegURL">
									<?php esc_html_e( 'Your browser does not support the video tag.', 'academy-starter' ); ?>
								</video>
							<?php else : ?>
								<div class="academy-starter-video-placeholder">
									<span><?php esc_html_e( 'Video Preview', 'academy-starter' ); ?></span>
								</div>
							<?php endif; ?>
						</div>
						<div class="academy-starter-video-meta">
							<span class="academy-starter-video-badge <?php echo esc_attr( $video['badge_class'] ); ?>">
								<?php echo esc_html( $video['badge'] ); ?>
							</span>
							<h3><?php echo esc_html( $video['title'] ); ?></h3>
							<p><?php echo esc_html( $video['description'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="academy-starter-videos-cta-note">
				<?php esc_html_e( 'Starter Plus includes all 6 lesson series — over 6 hours of structured content.', 'academy-starter' ); ?>
			</p>
		</div>
	</section>

	<section class="academy-starter-section academy-starter-about">
		<div class="academy-starter-container academy-starter-narrow">
			<h2><?php echo esc_html( sprintf( __( 'A Better First Step Into %s', 'academy-starter' ), $display_name ) ); ?></h2>
			<div class="academy-starter-about-columns">
				<?php
				$about_text = get_option( AcademyStarterAdmin::OPTION_ABOUT_TEXT, '' );
				if ( ! empty( $about_text ) ) {
					echo wp_kses_post( wpautop( $about_text ) );
				} else {
					$about = $data['about_paragraph'];
					if ( is_array( $about ) ) {
						foreach ( $about as $para ) {
							echo '<p>' . esc_html( $para ) . '</p>';
						}
					} else {
						echo '<p>' . esc_html( $about ) . '</p>';
					}
					echo '<p>' . esc_html( sprintf( __( 'Start with the essentials, then use those skills as your bridge into %s and the deeper techniques this style demands.', 'academy-starter' ), $display_name ) ) . '</p>';
				}
				?>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $data['practice_topics'] ) ) : ?>
		<section class="academy-starter-section academy-starter-practice">
			<div class="academy-starter-container">
				<div class="academy-starter-section-heading">
					<p class="academy-starter-kicker"><?php esc_html_e( 'Inside The Program', 'academy-starter' ); ?></p>
					<h2><?php echo esc_html( sprintf( __( 'What You\'ll Practice in Your %s Lessons', 'academy-starter' ), $display_name ) ); ?></h2>
				</div>
				<ul class="academy-starter-practice-list">
					<?php foreach ( $data['practice_topics'] as $topic ) : ?>
						<li><?php echo esc_html( $topic ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<section class="academy-starter-section academy-starter-resources">
		<div class="academy-starter-container">
			<div class="academy-starter-resources-inner">
				<div class="academy-starter-resources-icon" aria-hidden="true">📦</div>
				<div class="academy-starter-resources-copy">
					<h2><?php esc_html_e( 'Everything You Need — Yours to Keep', 'academy-starter' ); ?></h2>
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: display name e.g. "Cocktail Piano" */
								__( 'Every lesson in the Academy Starter program comes with downloadable resources so you can practice away from the screen. Your %s journey includes sheet music, backing tracks, and iReal Pro files — all included at no extra cost and yours to download and keep forever.', 'academy-starter' ),
								$display_name
							)
						);
						?>
					</p>
					<ul class="academy-starter-resources-list">
						<li>
							<strong><?php esc_html_e( 'Sheet Music', 'academy-starter' ); ?></strong>
							<?php esc_html_e( 'PDF lead sheets and notation for every lesson', 'academy-starter' ); ?>
						</li>
						<li>
							<strong><?php esc_html_e( 'Backing Tracks', 'academy-starter' ); ?></strong>
							<?php esc_html_e( 'Play along with professional rhythm section recordings', 'academy-starter' ); ?>
						</li>
						<li>
							<strong><?php esc_html_e( 'iReal Pro Files', 'academy-starter' ); ?></strong>
							<?php esc_html_e( 'Import chord charts directly into iReal Pro for practice anywhere', 'academy-starter' ); ?>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<section class="academy-starter-section academy-starter-offer">
		<div class="academy-starter-container">
			<div class="academy-starter-section-heading">
				<p class="academy-starter-kicker"><?php esc_html_e( 'Choose Your Program', 'academy-starter' ); ?></p>
				<h2><?php esc_html_e( 'Start Free or Unlock the Full Starter Program', 'academy-starter' ); ?></h2>
			</div>

			<div class="academy-starter-access-banner">
				<span class="academy-starter-access-icon">📅</span>
				<div>
					<strong><?php esc_html_e( 'Full 3 Months of Access — Go At Your Own Pace', 'academy-starter' ); ?></strong>
					<p><?php esc_html_e( 'Both programs give you 90 days of access. No rushing. Practice when it works for you and come back to lessons as many times as you need.', 'academy-starter' ); ?></p>
				</div>
			</div>

			<div class="academy-starter-offer-grid">
				<div class="academy-starter-offer-card">
					<div class="academy-starter-offer-badge-spacer"></div>
					<div class="academy-starter-offer-header">
						<h3><?php esc_html_e( 'Free Starter Access', 'academy-starter' ); ?></h3>
						<div class="academy-starter-offer-price">
							<strong><?php esc_html_e( 'Free', 'academy-starter' ); ?></strong>
							<span><?php esc_html_e( 'No credit card or payment needed', 'academy-starter' ); ?></span>
						</div>
						<p><?php esc_html_e( 'Try the structured 30-Day Piano Playbook and begin building your foundation today.', 'academy-starter' ); ?></p>
					</div>
					<ul>
						<li><?php esc_html_e( '30-Day Piano Playbook (3h 34min)', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( 'Step-by-step daily practice plan', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( 'Accessible on any device', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( 'Sheet music, backing tracks & iReal Pro files', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( '90 days of access', 'academy-starter' ); ?></li>
					</ul>
					<button type="button" class="academy-starter-button academy-starter-button-primary academy-starter-free-cta" data-click-type="free">
						<?php esc_html_e( 'Get Free Access', 'academy-starter' ); ?>
					</button>
					<p class="academy-starter-offer-footnote"><?php esc_html_e( 'No credit card required. Instant access.', 'academy-starter' ); ?></p>
				</div>
				<div class="academy-starter-offer-card academy-starter-offer-featured">
					<div class="academy-starter-offer-badge-row">
						<div class="academy-starter-badge"><?php esc_html_e( 'Most Popular', 'academy-starter' ); ?></div>
					</div>
					<div class="academy-starter-offer-header">
						<h3><?php esc_html_e( 'Starter Plus', 'academy-starter' ); ?></h3>
						<div class="academy-starter-offer-price">
							<strong><s>$25</s> $7</strong>
							<span><?php esc_html_e( 'One-time payment · No subscription', 'academy-starter' ); ?></span>
						</div>
						<p><?php esc_html_e( 'Unlock the full playbook experience and bonus blueprint lessons for a low one-time price.', 'academy-starter' ); ?></p>
					</div>
					<ul>
						<li><?php esc_html_e( '30-Day Piano Playbook (3h 34min)', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( 'Blues Piano Blueprint (54 min)', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( 'Rock Piano Blueprint (40 min)', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( 'Super Simple Standards (1h 16min)', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( 'Sheet music, backing tracks & iReal Pro files', 'academy-starter' ); ?></li>
						<li><?php esc_html_e( '90 days of access', 'academy-starter' ); ?></li>
					</ul>
					<a class="academy-starter-button academy-starter-button-secondary academy-starter-paid-cta" data-click-type="paid" href="<?php echo esc_url( $paid_url ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Get Full Access for $7', 'academy-starter' ); ?>
					</a>
					<p class="academy-starter-offer-footnote"><?php esc_html_e( 'Save 72% today. One-time charge, no recurring fees.', 'academy-starter' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="academy-starter-section academy-starter-testimonials">
		<div class="academy-starter-container">
			<div class="academy-starter-section-heading">
				<p class="academy-starter-kicker"><?php esc_html_e( 'Student Notes', 'academy-starter' ); ?></p>
				<h2><?php esc_html_e( 'A Clearer Way To Start', 'academy-starter' ); ?></h2>
			</div>
			<div class="academy-starter-testimonial-grid">
				<figure>
					<blockquote><?php echo esc_html( $data['unique_testimonial'] ?? __( '"I finally knew what to practice each day. The lessons were organized and easy to follow."', 'academy-starter' ) ); ?></blockquote>
					<figcaption>
						&mdash;
						<?php
						$raw_name   = $data['unique_testimonial_name'] ?? $data['testimonial_attribution'];
						$first_name = explode( ' ', trim( $raw_name ) );
						echo esc_html( $first_name[0] );
						?>
					</figcaption>
				</figure>
				<figure>
					<blockquote><?php esc_html_e( '"The starter plan helped me stop jumping between random videos and actually build momentum. Having a daily plan made all the difference."', 'academy-starter' ); ?></blockquote>
					<figcaption>&mdash; Michael</figcaption>
				</figure>
				<figure>
					<blockquote><?php echo esc_html( sprintf( __( '"I wanted a practical entry point into %s and this gave me exactly that. Clear, structured, and actually fun to follow."', 'academy-starter' ), $display_name ) ); ?></blockquote>
					<figcaption>&mdash; Sarah</figcaption>
				</figure>
			</div>
		</div>
	</section>

	<section class="academy-starter-section academy-starter-faq">
		<div class="academy-starter-container academy-starter-narrow">
			<div class="academy-starter-section-heading">
				<p class="academy-starter-kicker"><?php esc_html_e( 'FAQ', 'academy-starter' ); ?></p>
				<h2><?php esc_html_e( 'Questions Before You Start?', 'academy-starter' ); ?></h2>
			</div>
			<details>
				<summary><?php esc_html_e( 'What is included in the Academy Starter program?', 'academy-starter' ); ?></summary>
				<p>
					<?php esc_html_e( 'The free plan includes the full 30-Day Piano Playbook — over 3.5 hours of structured lessons. Starter Plus adds the Blues Piano Blueprint, Rock Piano Blueprint, and Super Simple Standards for 6+ hours total. Every lesson includes downloadable resources: sheet music PDFs, backing tracks, and iReal Pro files — all included and yours to keep forever.', 'academy-starter' ); ?>
				</p>
			</details>
			<details>
				<summary><?php esc_html_e( 'Is Academy Starter good for complete beginners?', 'academy-starter' ); ?></summary>
				<p><?php esc_html_e( 'Yes. The program starts with absolute fundamentals — rhythm, technique, reading, and chords — and builds gradually so there is no guesswork about what to practice.', 'academy-starter' ); ?></p>
			</details>
			<details>
				<summary><?php esc_html_e( 'I\'m more advanced — are these lessons for me?', 'academy-starter' ); ?></summary>
				<p>
					<?php
					printf(
						esc_html__( 'Yes. While the 30-Day Playbook is built for beginners, the Starter Plus program is genuinely valuable for intermediate and advanced players. The Blues Piano Blueprint, Rock Piano Blueprint, and Super Simple Standards are focused courses that challenge players at every level. Many experienced students use the Starter program to reset their practice habits, close gaps accumulated from years of self-teaching, and rebuild a more reliable technical foundation. If you already have some experience, Starter Plus gives you a low-cost way to identify what is missing before moving into deeper material inside %1$sJazzEdge Academy%2$s.', 'academy-starter' ),
						'<a href="https://jazzedge.academy/join" target="_blank" rel="noopener">',
						'</a>'
					);
					?>
				</p>
			</details>
			<details>
				<summary><?php echo esc_html( sprintf( __( 'Will this help me learn %s specifically?', 'academy-starter' ), $display_name ) ); ?></summary>
				<p>
					<?php
					printf(
						esc_html__( 'Yes. The Starter program builds the core skills essential before diving deeper into %1$s-specific lessons, songs, and techniques available inside %2$sJazzEdge Academy%3$s.', 'academy-starter' ),
						esc_html( $display_name ),
						'<a href="https://jazzedge.academy/" target="_blank" rel="noopener">',
						'</a>'
					);
					?>
				</p>
			</details>
			<details>
				<summary><?php esc_html_e( 'How long do I have access?', 'academy-starter' ); ?></summary>
				<p>
					<?php
					printf(
						/* translators: 1: opening link tag, 2: closing link tag */
						esc_html__( 'Starter Plus includes 90 days of full access from the date of purchase. After that you can upgrade to a %1$sfull JazzEdge Academy membership%2$s or repurchase Academy Starter anytime.', 'academy-starter' ),
						'<a href="https://jazzedge.academy/join" target="_blank" rel="noopener">',
						'</a>'
					);
					?>
				</p>
			</details>
			<details>
				<summary><?php esc_html_e( 'Is the $7 program a subscription?', 'academy-starter' ); ?></summary>
				<p><?php esc_html_e( 'No. Starter Plus is a single one-time payment of $7. There are no recurring charges, auto-renewals, or hidden fees.', 'academy-starter' ); ?></p>
			</details>
			<details>
				<summary><?php esc_html_e( 'Can I access lessons on my phone or tablet?', 'academy-starter' ); ?></summary>
				<p><?php esc_html_e( 'Yes. All lessons are accessible on desktop, laptop, tablet, and smartphone through your JazzEdge Academy account.', 'academy-starter' ); ?></p>
			</details>
			<details>
				<summary><?php esc_html_e( 'Where do I log in to access my lessons?', 'academy-starter' ); ?></summary>
				<p>
					<?php esc_html_e( 'After signing up, log in at', 'academy-starter' ); ?>
					<a href="https://jazzedge.academy/login" target="_blank" rel="noopener">jazzedge.academy/login</a>.
					<?php esc_html_e( 'Your lessons and progress will be waiting for you there.', 'academy-starter' ); ?>
				</p>
			</details>
			<details>
				<summary><?php esc_html_e( 'What downloadable resources are included?', 'academy-starter' ); ?></summary>
				<p><?php esc_html_e( 'Every lesson comes with downloadable resources, including sheet music PDFs. Some lessons also include additional resources like backing tracks, MP3 files, and iReal Pro chord charts. These resources are yours to download and keep forever — even after your 90-day access period ends.', 'academy-starter' ); ?></p>
			</details>
			<details>
				<summary><?php esc_html_e( 'What if I want more than the Starter program right now?', 'academy-starter' ); ?></summary>
				<p>
					<?php
					printf(
						esc_html__( 'No problem. You can skip the Starter and go straight to a full program. %1$sBrowse all courses and memberships at the JazzEdge Shop →%2$s. You will find individual courses, bundles, and full academy memberships to match any budget or learning goal.', 'academy-starter' ),
						'<a href="https://shop.jazzedge.com/" target="_blank" rel="noopener">',
						'</a>'
					);
					?>
				</p>
			</details>
			<details>
				<summary><?php echo esc_html( sprintf( __( 'Do you have individual %s courses I can purchase?', 'academy-starter' ), $display_name ) ); ?></summary>
				<p>
					<?php
					printf(
						esc_html__( 'Yes. %1$sThe JazzEdge Shop%2$s has individual courses, lesson packs, and bundles available for one-time purchase. If you know what you want and are ready to go deeper, the shop is the fastest way to get started with a specific program.', 'academy-starter' ),
						'<a href="https://shop.jazzedge.com/" target="_blank" rel="noopener">',
						'</a>'
					);
					?>
				</p>
			</details>
			<?php if ( ! empty( $data['style_faqs'] ) && is_array( $data['style_faqs'] ) ) : ?>
				<?php foreach ( $data['style_faqs'] as $faq ) : ?>
					<details>
						<summary><?php echo esc_html( $faq['question'] ); ?></summary>
						<p><?php echo esc_html( $faq['answer'] ); ?></p>
					</details>
				<?php endforeach; ?>
			<?php endif; ?>
			<details>
				<summary><?php esc_html_e( 'Can I upgrade to a full membership later?', 'academy-starter' ); ?></summary>
				<p>
					<?php
					printf(
						esc_html__( 'Yes. You can upgrade to Essentials, Studio, or Premier at any time from %1$sJazzEdge Academy%2$s. Your Starter progress carries forward seamlessly.', 'academy-starter' ),
						'<a href="https://jazzedge.academy/join" target="_blank" rel="noopener">',
						'</a>'
					);
					?>
				</p>
			</details>
		</div>
	</section>

	<section class="academy-starter-next-step-strip">
		<div class="academy-starter-container">
			<p class="academy-starter-next-step-label">
				<?php esc_html_e( 'Ready to go further?', 'academy-starter' ); ?>
			</p>
			<div class="academy-starter-next-step-links">
				<a href="https://jazzedge.academy/join" target="_blank" rel="noopener" class="academy-starter-next-step-card">
					<strong><?php esc_html_e( 'JazzEdge Academy', 'academy-starter' ); ?></strong>
					<span><?php esc_html_e( 'Full memberships with unlimited access to every course, lesson, and instructor.', 'academy-starter' ); ?></span>
					<em><?php esc_html_e( 'Explore memberships →', 'academy-starter' ); ?></em>
				</a>
				<a href="https://shop.jazzedge.com/" target="_blank" rel="noopener" class="academy-starter-next-step-card">
					<strong><?php esc_html_e( 'JazzEdge Shop', 'academy-starter' ); ?></strong>
					<span><?php echo esc_html( sprintf( __( 'Individual %s courses and bundles available for one-time purchase.', 'academy-starter' ), $display_name ) ); ?></span>
					<em><?php esc_html_e( 'Browse the shop →', 'academy-starter' ); ?></em>
				</a>
			</div>
		</div>
	</section>

	<section class="academy-starter-footer-cta">
		<div class="academy-starter-container">
			<h2><?php echo esc_html( $data['hero_headline'] ); ?></h2>
			<p><?php esc_html_e( 'Choose the free starter path or unlock full access today and start practicing with a plan.', 'academy-starter' ); ?></p>
			<div class="academy-starter-cta-row">
				<button type="button" class="academy-starter-button academy-starter-button-primary academy-starter-free-cta" data-click-type="free">
					<?php esc_html_e( 'Start Free', 'academy-starter' ); ?>
				</button>
				<a class="academy-starter-button academy-starter-button-secondary academy-starter-paid-cta" data-click-type="paid" href="<?php echo esc_url( $paid_url ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'Get Full Access for $7', 'academy-starter' ); ?>
				</a>
			</div>
			<div class="academy-starter-footer-links">
				<a href="<?php echo esc_url( $data['academy_search_url'] ?? 'https://shop.jazzedge.com/' ); ?>" target="_blank" rel="noopener">
					<?php echo esc_html( sprintf( __( 'Browse %s courses in the Academy', 'academy-starter' ), $display_name ) ); ?> →
				</a>
				<a href="https://jazzedge.academy/join" target="_blank" rel="noopener">
					<?php esc_html_e( 'View full Academy memberships', 'academy-starter' ); ?> →
				</a>
				<a href="https://jazzedge.academy/login" target="_blank" rel="noopener">
					<?php esc_html_e( 'Already a Starter member? Log in at JazzEdge Academy', 'academy-starter' ); ?> →
				</a>
			</div>
		</div>
	</section>

	<div class="academy-starter-modal-overlay" data-academy-starter-modal aria-hidden="true">
		<div class="academy-starter-modal" role="dialog" aria-modal="true" aria-labelledby="academy-starter-modal-title">
			<button type="button" class="academy-starter-modal-close" aria-label="<?php esc_attr_e( 'Close free signup form', 'academy-starter' ); ?>">&times;</button>
			<div class="academy-starter-modal-header">
				<span class="academy-starter-badge"><?php esc_html_e( 'Free Starter', 'academy-starter' ); ?></span>
				<h2 id="academy-starter-modal-title"><?php esc_html_e( 'Get Started Free Today', 'academy-starter' ); ?></h2>
				<p><?php esc_html_e( 'Get instant access to the free starter path. No credit card required.', 'academy-starter' ); ?></p>
			</div>
			<div class="academy-starter-modal-body">
				<?php if ( $free_form_id ) : ?>
					<?php echo do_shortcode( '[fluentform id="' . absint( $free_form_id ) . '"]' ); ?>
				<?php else : ?>
					<p class="academy-starter-form-missing"><?php esc_html_e( 'Please select a Fluent Form in Settings > Academy Starter.', 'academy-starter' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="academy-starter-sticky-bar" id="academy-starter-sticky-bar" aria-hidden="true">
		<div class="academy-starter-sticky-bar-inner">
			<div class="academy-starter-sticky-bar-copy">
				<strong><?php esc_html_e( 'Start Playing Piano the Right Way.', 'academy-starter' ); ?></strong>
				<span><?php echo esc_html( sprintf( __( 'Follow the 90-day Academy Starter plan for %s.', 'academy-starter' ), $display_name ) ); ?></span>
			</div>
			<div class="academy-starter-sticky-bar-actions">
				<button
					type="button"
					class="academy-starter-button academy-starter-button-primary academy-starter-free-cta academy-starter-sticky-free-btn"
					data-click-type="free"
				>
					<?php esc_html_e( 'Start Free', 'academy-starter' ); ?>
				</button>
				<a
					class="academy-starter-button academy-starter-button-secondary academy-starter-paid-cta"
					data-click-type="paid"
					href="<?php echo esc_url( $paid_url ); ?>"
					target="_blank"
					rel="noopener"
				>
					<?php esc_html_e( 'Get Full Access — $7', 'academy-starter' ); ?>
				</a>
			</div>
			<button type="button" class="academy-starter-sticky-dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'academy-starter' ); ?>">
				&times;
			</button>
		</div>
	</div>

	<div class="academy-starter-member-bar">
		<p>
		<?php
		printf(
			/* translators: %s: site name */
			esc_html__( 'Already a member of %s?', 'academy-starter' ),
			esc_html( get_bloginfo( 'name' ) )
		);
		?>
			<a href="<?php echo esc_url( wp_login_url() ); ?>">
				<?php esc_html_e( 'Log in here', 'academy-starter' ); ?>
			</a>
		</p>
	</div>
</div>
