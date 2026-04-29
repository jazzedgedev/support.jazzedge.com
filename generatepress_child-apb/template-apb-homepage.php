<?php
/**
 * Template Name: APB Homepage
 *
 * @package GeneratePress_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	// Editor content omitted; layout is fully custom below.
	endwhile;
?>

<div class="apb-homepage">
	<section class="apb-hero" aria-label="<?php echo esc_attr__( 'Hero', 'generatepress' ); ?>">
		<div class="apb-hero__inner apb-container">
			<h1 class="apb-hero__title"><?php echo esc_html( 'Elevate Your Craft Anytime, Anywhere with Expert-Led Online Workshops at ActorPlaybook.com' ); ?></h1>
			<p class="apb-hero__sub"><?php echo esc_html( 'Access a range of comprehensive resources, guided exercises, and expert-led workshops designed to help you grow as an actor.' ); ?></p>
			<p class="apb-hero__cta">
				<a class="apb-btn apb-btn--primary" href="<?php echo esc_url( home_url( '/join-the-actorplaybook-community/' ) ); ?>"><?php echo esc_html( 'Enroll Today' ); ?></a>
			</p>
		</div>
	</section>

	<section class="apb-section apb-why" aria-labelledby="apb-why-heading">
		<div class="apb-container">
			<h2 id="apb-why-heading" class="apb-section-title"><?php echo esc_html( 'Why Choose ActorPlaybook?' ); ?></h2>
			<div class="apb-feature-grid">
				<div class="apb-feature-card">
					<h3 class="apb-feature-card__title"><?php echo esc_html( 'Convenience & Flexibility' ); ?></h3>
					<p class="apb-feature-card__text"><?php echo esc_html( 'Learn from anywhere 24/7. Perfect for actors balancing auditions, day jobs, and other commitments. Access our self-guided library of 12+ courses, 100+ lessons, industry interviews, and more.' ); ?></p>
				</div>
				<div class="apb-feature-card">
					<h3 class="apb-feature-card__title"><?php echo esc_html( 'Expert Guidance' ); ?></h3>
					<p class="apb-feature-card__text"><?php echo esc_html( 'Classes led by experienced industry professionals and coaches. Practical tips and insider knowledge to help you stand out in auditions and performances.' ); ?></p>
				</div>
				<div class="apb-feature-card">
					<h3 class="apb-feature-card__title"><?php echo esc_html( 'Supportive Community' ); ?></h3>
					<p class="apb-feature-card__text"><?php echo esc_html( 'Connect with fellow actors and coaches for collaboration and networking. A platform designed to inspire and uplift actors on their unique journeys.' ); ?></p>
				</div>
				<div class="apb-feature-card">
					<h3 class="apb-feature-card__title"><?php echo esc_html( 'Comprehensive Training' ); ?></h3>
					<p class="apb-feature-card__text"><?php echo esc_html( 'Workshops covering acting techniques, character development, audition prep, and more. Options for beginners, intermediates, and seasoned professionals.' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="apb-section apb-provide" aria-labelledby="apb-provide-heading">
		<div class="apb-container apb-provide__grid">
			<div class="apb-provide__copy">
				<p class="apb-eyebrow"><?php echo esc_html( 'What we provide' ); ?></p>
				<h2 id="apb-provide-heading" class="apb-heading"><?php echo esc_html( 'A creative home, valuable resources and training.' ); ?></h2>
				<p class="apb-body"><?php echo esc_html( 'ActorPlaybook.com equips actors with the tools, skills, and confidence needed to succeed in the competitive entertainment industry.' ); ?></p>
			</div>
			<div class="apb-provide__media" role="presentation">
				<div class="apb-img-placeholder"><?php echo esc_html( 'Image' ); ?></div>
			</div>
		</div>
	</section>

	<section class="apb-section apb-services" aria-labelledby="apb-services-heading">
		<div class="apb-container">
			<h2 id="apb-services-heading" class="apb-section-title"><?php echo esc_html( 'Lessons / Pro-Training Replays' ); ?></h2>
			<div class="apb-icon-grid">
				<div class="apb-icon-card">
					<span class="apb-icon-card__icon" aria-hidden="true">&#9632;</span>
					<h3 class="apb-icon-card__title"><?php echo esc_html( 'Lessons' ); ?></h3>
					<p class="apb-icon-card__text"><?php echo esc_html( 'Structured online lessons available on your schedule' ); ?></p>
				</div>
				<div class="apb-icon-card">
					<span class="apb-icon-card__icon" aria-hidden="true">&#9632;</span>
					<h3 class="apb-icon-card__title"><?php echo esc_html( 'Pro-Training Replays' ); ?></h3>
					<p class="apb-icon-card__text"><?php echo esc_html( 'Full replay library of past pro-training sessions' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<section class="apb-cta-banner" aria-labelledby="apb-cta-banner-heading">
		<div class="apb-container apb-cta-banner__inner">
			<h2 id="apb-cta-banner-heading" class="apb-cta-banner__title"><?php echo esc_html( 'Ready to reenergize your career?' ); ?></h2>
			<a class="apb-btn apb-btn--outline" href="<?php echo esc_url( home_url( '/join-the-actorplaybook-community/' ) ); ?>"><?php echo esc_html( 'Learn More' ); ?></a>
		</div>
	</section>

	<section class="apb-section apb-testimonials" aria-labelledby="apb-testimonials-heading">
		<div class="apb-container">
			<div class="apb-testimonials__head">
				<h2 id="apb-testimonials-heading" class="apb-section-title"><?php echo esc_html( 'Real actors. Real stories.' ); ?></h2>
				<p class="apb-testimonials__review">
					<a href="<?php echo esc_url( 'https://g.page/r/CWdUGl9kvJBGEAI/review' ); ?>"><?php echo esc_html( 'Leave a Review' ); ?></a>
				</p>
			</div>
			<div class="apb-testimonial-grid">
				<?php
				$apb_testimonials = array(
					array(
						'name'  => 'Krystian Bester',
						'role'  => 'Studio Member',
						'quote' => 'I am thrilled to share my positive experience with ActorPlaybook. In addition to the exceptional resources...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/KrystianBester-101523-012-scaled_jpg.jpg',
					),
					array(
						'name'  => 'Callie Beaulieu',
						'role'  => 'Studio Member',
						'quote' => 'In the seven months I have been a member of ActorPlaybook I have grown tremendously as an actor. Jami...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/callie.jpg',
					),
					array(
						'name'  => 'Marvin Novogrodski',
						'role'  => 'Studio Member',
						'quote' => 'The camaraderie and learning is non-stop. ActorPlaybook is a place of warmth and family.',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/marv.jpg',
					),
					array(
						'name'  => 'Olia Neiman',
						'role'  => 'Studio Member',
						'quote' => 'I\'m incredibly grateful for the privilege of being part of the ActorPlaybook family that brightens life...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/olia.jpg',
					),
					array(
						'name'  => 'Sarah Dunn',
						'role'  => 'Studio Member',
						'quote' => 'At ActorPlaybook Jami Tennille has created an amazing group full of resources and fabulous opportunities...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/sarah.jpg',
					),
					array(
						'name'  => 'Phil Harris',
						'role'  => 'Actor',
						'quote' => 'ActorPlaybook isn\'t just for the new actor trying to learn the business — it\'s for the seasoned actor who wants to sharpen their skills every day.',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/philH.jpeg',
					),
					array(
						'name'  => 'Carol Drewes',
						'role'  => 'Studio Member',
						'quote' => 'Thanks again for all you do. I already feel, after two months\' involvement with Actors Playbook, transformed...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/Carol_E__Drewes___Photos.jpg',
					),
					array(
						'name'  => 'Jaci Kjernander',
						'role'  => 'Studio Member',
						'quote' => 'Working with Jami and ActorPlaybook has been life changing for me. The workshops and ongoing training...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/Jaci-Kjernander.jpg',
					),
					array(
						'name'  => 'Amanda Mooney',
						'role'  => 'Studio Member',
						'quote' => 'I genuinely believe that joining up with the ActorPlaybook community is one of the best moves I could...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/amandamooney.jpeg',
					),
					array(
						'name'  => 'Tana Eriksen',
						'role'  => 'Studio Member',
						'quote' => 'I absolutely love being a member of Actor Playbook. The community is so supportive and welcoming. Jami...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/tana-1.jpg',
					),
					array(
						'name'  => 'Taiye Ojeikere',
						'role'  => 'Studio Member',
						'quote' => 'Working with Jami and the APB family has truly been a blessing for me! This community is truly so positive...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/taiye.jpg',
					),
					array(
						'name'  => 'Stella Ryan-Lozon',
						'role'  => 'Studio Member',
						'quote' => 'Quick note to say thank you! For those of us who aren\'t on every call, the replays are invaluable...',
						'photo' => 'https://actorplaybook.com/wp-content/uploads/2026/04/stella-1.jpg',
					),
				);
				foreach ( $apb_testimonials as $apb_t ) :
					?>
					<blockquote class="apb-testimonial-card">
						<div class="apb-testimonial-card__head">
							<span class="apb-testimonial-card__photo-wrap">
								<img
									class="apb-testimonial-card__photo"
									src="<?php echo esc_url( $apb_t['photo'] ); ?>"
									alt="<?php echo esc_attr( $apb_t['name'] ); ?>"
									width="80"
									height="80"
									loading="lazy"
									decoding="async"
								/>
							</span>
							<div class="apb-testimonial-card__who">
								<cite class="apb-testimonial-card__name"><?php echo esc_html( $apb_t['name'] ); ?></cite>
								<span class="apb-testimonial-card__role"><?php echo esc_html( $apb_t['role'] ); ?></span>
							</div>
						</div>
						<p class="apb-testimonial-card__quote"><?php echo esc_html( $apb_t['quote'] ); ?></p>
					</blockquote>
					<?php
				endforeach;
				?>
			</div>
		</div>
	</section>

	<section class="apb-featured-quote" aria-labelledby="apb-featured-quote-heading">
		<div class="apb-container apb-featured-quote__inner">
			<p class="apb-featured-quote__mark" aria-hidden="true">"</p>
			<blockquote class="apb-featured-quote__body">
				<p id="apb-featured-quote-heading"><?php echo esc_html( 'ActorPlaybook isn\'t just for the new actor trying to learn the business — it\'s for the seasoned actor who wants to sharpen their skills.' ); ?></p>
				<footer class="apb-featured-quote__attr">
					<?php
					$apb_phil_headshot_url = 'https://actorplaybook.com/wp-content/uploads/2026/04/philH.jpeg';
					?>
					<img class="apb-quote-headshot" src="<?php echo esc_url( $apb_phil_headshot_url ); ?>" alt="<?php echo esc_attr( 'Phil Harris' ); ?>" width="80" height="80" loading="lazy" />
					<div>
						<strong><?php echo esc_html( 'Phil Harris — Actor' ); ?></strong>
					</div>
				</footer>
			</blockquote>
		</div>
	</section>

	<section class="apb-section apb-lead" aria-labelledby="apb-lead-heading">
		<div class="apb-container apb-lead__grid">
			<div class="apb-lead__media">
				<div class="apb-kenneth-img apb-img-placeholder" role="img" aria-label="<?php echo esc_attr__( 'Kenneth Lonergan', 'generatepress' ); ?>"></div>
			</div>
			<div class="apb-lead__form-wrap">
				<h2 id="apb-lead-heading"><?php echo esc_html( 'Get Audition Advice From Kenneth Lonergan' ); ?></h2>
				<p class="apb-body"><?php echo esc_html( 'Empower yourself with insider access to audition tips and advice from Academy Award Winner, Kenneth Lonergan.' ); ?></p>

				<?php
				// Optional shortcode replacement for CRM/marketing embeds: [apb_lonergan_optin]
				// echo do_shortcode( '[apb_lonergan_optin]' );
				?>
				<!-- Shortcode alternative: [apb_lonergan_optin] -->

				<?php if ( isset( $_GET['apb_optin'] ) && 'thanks' === sanitize_key( wp_unslash( $_GET['apb_optin'] ) ) ) : ?>
					<p class="apb-form-notice apb-form-notice--success" role="status"><?php echo esc_html( 'Thank you! Check your email.' ); ?></p>
				<?php elseif ( isset( $_GET['apb_optin'] ) && 'invalid' === sanitize_key( wp_unslash( $_GET['apb_optin'] ) ) ) : ?>
					<p class="apb-form-notice apb-form-notice--error" role="alert"><?php echo esc_html( 'Please enter a valid name and email.' ); ?></p>
				<?php endif; ?>

				<form class="apb-lead-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="apb_lonergan_optin" />
					<?php wp_nonce_field( 'apb_lonergan_optin', 'apb_lonergan_nonce' ); ?>
					<p class="apb-field">
						<label for="apb-email"><?php echo esc_html( 'Email' ); ?> <span class="apb-required">*</span></label>
						<input id="apb-email" name="apb_email" type="email" required autocomplete="email" />
					</p>
					<p class="apb-field">
						<label for="apb-name"><?php echo esc_html( 'Name' ); ?> <span class="apb-required">*</span></label>
						<input id="apb-name" name="apb_name" type="text" required autocomplete="name" />
					</p>
					<p class="apb-field apb-field--submit">
						<button type="submit" class="apb-btn apb-btn--primary"><?php echo esc_html( 'Yes, send it to me!' ); ?></button>
					</p>
				</form>
			</div>
		</div>
	</section>

	<section class="apb-section apb-blog" aria-labelledby="apb-blog-heading">
		<div class="apb-container">
			<h2 id="apb-blog-heading" class="apb-section-title"><?php echo esc_html( 'Visit Our Blog' ); ?></h2>
			<?php
			$apb_blog = new WP_Query(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => 4,
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				)
			);
			if ( $apb_blog->have_posts() ) :
				?>
				<div class="apb-blog-grid">
					<?php
					while ( $apb_blog->have_posts() ) :
						$apb_blog->the_post();
						?>
						<article <?php post_class( 'apb-blog-card' ); ?>>
							<a class="apb-blog-card__thumb" href="<?php echo esc_url( get_permalink() ); ?>">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail(
										'medium_large',
										array(
											'class' => 'apb-blog-card__img',
											'alt'   => esc_attr( get_the_title() ),
										)
									);
								} else {
									echo '<span class="apb-blog-card__img apb-blog-card__img--placeholder" aria-hidden="true"></span>';
								}
								?>
							</a>
							<h3 class="apb-blog-card__title">
								<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
							</h3>
							<div class="apb-blog-card__excerpt"><?php echo wp_kses_post( get_the_excerpt() ); ?></div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<?php
			endif;
			?>
		</div>
	</section>

	<section class="apb-policy-footer" aria-label="<?php echo esc_attr__( 'Policies', 'generatepress' ); ?>">
		<div class="apb-container apb-policy-footer__inner">
			<nav class="apb-policy-nav" aria-label="<?php echo esc_attr__( 'Legal links', 'generatepress' ); ?>">
				<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php echo esc_html( 'FAQ' ); ?></a>
				<span class="apb-policy-sep" aria-hidden="true">|</span>
				<a href="<?php echo esc_url( home_url( '/terms-conditions/' ) ); ?>"><?php echo esc_html( 'Terms & Conditions' ); ?></a>
				<span class="apb-policy-sep" aria-hidden="true">|</span>
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php echo esc_html( 'Privacy Policy' ); ?></a>
				<span class="apb-policy-sep" aria-hidden="true">|</span>
				<span class="apb-policy-cancel"><strong><?php echo esc_html( 'Cancellation & Refund Policy' ); ?></strong> — <?php echo esc_html( 'We offer a 14-day money-back guarantee. Monthly members may cancel at any time.' ); ?></span>
			</nav>
		</div>
	</section>
</div>

<?php
get_footer();
