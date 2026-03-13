<?php
/*
Template Name: Play Blues Piano — Homepage
Template Post Type: page
*/
if (!defined('ABSPATH')) exit;

/* ------------ CONFIG ------------ */
$academy_url = 'https://jazzedgeacademy.com'; // <- update to live Academy URL

/* Hide sidebar; keep header + footer for navigation */
add_filter('generate_show_title',    '__return_false');
add_filter('generate_sidebar_layout', fn() => 'no-sidebar');

get_header(); ?>

<style>
  /* ─── Design Tokens ─────────────────────────────── */
  :root {
    --ink:        #0d1117;
    --ink-mid:    #2e3646;
    --ink-soft:   #5a6475;
    --gold:       #c8912a;
    --gold-light: #f0c354;
    --blue-deep:  #0e2a4a;
    --blue-mid:   #1a4a7a;
    --blue-bright:#2e7dd4;
    --cream:      #faf7f2;
    --cream-dark: #f0ead8;
    --white:      #ffffff;
    --danger:     #b91c1c;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.12);
    --shadow-md:  0 4px 12px rgba(0,0,0,.15);
    --shadow-lg:  0 10px 30px rgba(0,0,0,.20);
    --shadow-xl:  0 20px 50px rgba(0,0,0,.25);
  }

  /* ─── Global Resets ─────────────────────────────── */
  *, *::before, *::after { box-sizing: border-box; }

  .pbp-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.65;
    color: var(--ink);
    width: 100%;
  }

  /* Full-width override for GeneratePress content area */
  .pbp-page ~ * { display: none; }
  .entry-content  { padding: 0 !important; max-width: none !important; }
  .inside-article { padding: 0 !important; }

  /* ─── Layout Helpers ────────────────────────────── */
  .pbp-wrap {
    max-width: 1120px;
    margin: 0 auto;
    padding: 0 24px;
    width: 100%;
  }
  .pbp-wrap--narrow { max-width: 800px; }
  .pbp-wrap--wide   { max-width: 1280px; }

  section { width: 100%; }

  /* ─── Buttons ───────────────────────────────────── */
  .pbp-btn {
    display: inline-block;
    padding: 15px 32px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 17px;
    text-decoration: none;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    letter-spacing: .3px;
  }
  .pbp-btn:hover  { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
  .pbp-btn--gold  { background: var(--gold); color: var(--white); }
  .pbp-btn--gold:hover  { background: #b07d1e; color: var(--white); }
  .pbp-btn--white { background: var(--white); color: var(--blue-deep); }
  .pbp-btn--white:hover { background: var(--cream); color: var(--blue-deep); }
  .pbp-btn--lg    { padding: 18px 40px; font-size: 19px; }

  /* ─── 1. HERO ───────────────────────────────────── */
  .pbp-hero {
    background:
      linear-gradient(160deg, rgba(14,42,74,.94) 0%, rgba(9,20,40,.97) 100%),
      url('https://playbluespiano.com/wp-content/uploads/hero-piano-bg.jpg') center/cover no-repeat;
    color: var(--white);
    padding: 100px 0 80px;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .pbp-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 50% at 50% 0%, rgba(200,145,42,.12), transparent);
    pointer-events: none;
  }
  .pbp-hero__eyebrow {
    display: inline-block;
    background: rgba(200,145,42,.2);
    border: 1px solid var(--gold);
    color: var(--gold-light);
    padding: 6px 18px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 24px;
  }
  .pbp-hero__title {
    font-size: clamp(2.4rem, 5.5vw, 4.2rem);
    font-weight: 900;
    line-height: 1.1;
    margin: 0 0 16px;
    color: var(--white);
  }
  .pbp-hero__title span { color: var(--gold-light); }
  .pbp-hero__sub {
    font-size: clamp(1.1rem, 2.2vw, 1.3rem);
    color: rgba(255,255,255,.75);
    max-width: 640px;
    margin: 0 auto 36px;
  }
  .pbp-hero__cta-group {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
  }

  /* ─── 2. MIGRATION NOTICE ───────────────────────── */
  .pbp-notice {
    background: var(--blue-deep);
    color: var(--white);
    padding: 64px 0;
    text-align: center;
  }
  .pbp-notice__inner {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(200,145,42,.35);
    border-radius: 12px;
    padding: 48px 40px;
    max-width: 780px;
    margin: 0 auto;
  }
  .pbp-notice__badge {
    display: inline-block;
    background: var(--gold);
    color: var(--white);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 4px;
    margin-bottom: 20px;
  }
  .pbp-notice__title {
    font-size: clamp(1.7rem, 3.5vw, 2.5rem);
    font-weight: 800;
    margin: 0 0 16px;
    color: var(--white);
  }
  .pbp-notice__body {
    font-size: 1.1rem;
    color: rgba(255,255,255,.80);
    margin: 0 0 32px;
    line-height: 1.75;
  }

  /* ─── 3. INTRO QUOTE ────────────────────────────── */
  .pbp-quote-band {
    background: var(--cream);
    padding: 56px 0;
    text-align: center;
  }
  .pbp-quote-band blockquote {
    font-size: clamp(1.15rem, 2.5vw, 1.45rem);
    font-style: italic;
    color: var(--ink-mid);
    max-width: 700px;
    margin: 0 auto;
    padding: 0;
    border: none;
    line-height: 1.7;
  }
  .pbp-quote-band cite {
    display: block;
    margin-top: 16px;
    font-style: normal;
    font-weight: 700;
    font-size: .95rem;
    color: var(--gold);
    letter-spacing: .5px;
  }

  /* ─── 4. FEATURES ───────────────────────────────── */
  .pbp-features {
    background: var(--white);
    padding: 80px 0;
  }
  .pbp-features__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 28px;
    margin-top: 48px;
  }
  .pbp-feature-card {
    background: var(--cream);
    border-radius: 12px;
    padding: 32px 28px;
    border-top: 4px solid var(--gold);
    box-shadow: var(--shadow-sm);
    transition: box-shadow .25s ease, transform .25s ease;
  }
  .pbp-feature-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-4px);
  }
  .pbp-feature-card__icon {
    font-size: 2.2rem;
    margin-bottom: 16px;
    display: block;
  }
  .pbp-feature-card__title {
    font-size: 1.2rem;
    font-weight: 800;
    margin: 0 0 10px;
    color: var(--blue-deep);
  }
  .pbp-feature-card__body {
    font-size: .98rem;
    color: var(--ink-soft);
    margin: 0;
    line-height: 1.7;
  }

  /* ─── Section Heading (shared) ──────────────────── */
  .pbp-section-title {
    font-size: clamp(1.7rem, 3.5vw, 2.4rem);
    font-weight: 900;
    color: var(--blue-deep);
    margin: 0 0 8px;
    line-height: 1.2;
  }
  .pbp-section-sub {
    font-size: 1.05rem;
    color: var(--ink-soft);
    margin: 0;
  }
  .pbp-section-divider {
    width: 60px;
    height: 4px;
    background: var(--gold);
    border: none;
    margin: 16px 0 0;
    border-radius: 2px;
  }

  /* ─── 5. TESTIMONIALS ───────────────────────────── */
  .pbp-testimonials {
    background: var(--cream-dark);
    padding: 80px 0;
  }
  .pbp-testimonials__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 48px;
  }
  .pbp-testi-card {
    background: var(--white);
    border-radius: 12px;
    padding: 28px 24px;
    box-shadow: var(--shadow-sm);
    position: relative;
  }
  .pbp-testi-card::before {
    content: '\201C';
    position: absolute;
    top: 16px;
    left: 20px;
    font-size: 4rem;
    line-height: 1;
    color: var(--gold-light);
    font-family: Georgia, serif;
  }
  .pbp-testi-card__text {
    font-size: 1rem;
    font-style: italic;
    color: var(--ink-mid);
    margin: 28px 0 16px;
    line-height: 1.7;
  }
  .pbp-testi-card__author {
    font-weight: 700;
    font-size: .9rem;
    color: var(--blue-deep);
    margin: 0;
  }
  .pbp-testi-card__meta {
    font-size: .85rem;
    color: var(--ink-soft);
    margin: 2px 0 0;
  }

  /* ─── 6. LESSONS LIBRARY ────────────────────────── */
  .pbp-lessons {
    background: var(--blue-deep);
    color: var(--white);
    padding: 80px 0;
  }
  .pbp-lessons .pbp-section-title { color: var(--white); }
  .pbp-lessons .pbp-section-sub   { color: rgba(255,255,255,.65); }

  .pbp-courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    margin-top: 40px;
  }
  .pbp-course-tag {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(200,145,42,.3);
    border-radius: 8px;
    padding: 12px 16px;
    font-size: .88rem;
    font-weight: 600;
    color: rgba(255,255,255,.85);
    transition: background .2s, border-color .2s;
  }
  .pbp-course-tag:hover {
    background: rgba(200,145,42,.15);
    border-color: var(--gold);
    color: var(--white);
  }

  /* ─── 7. ACCESS / 24-7 SECTION ──────────────────── */
  .pbp-access {
    background: var(--white);
    padding: 80px 0;
  }
  .pbp-access__layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
  }
  .pbp-access__stat {
    font-size: clamp(3rem, 7vw, 5.5rem);
    font-weight: 900;
    color: var(--gold);
    line-height: 1;
    margin-bottom: 8px;
  }
  .pbp-access__stat-label {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--blue-deep);
    margin: 0 0 24px;
  }
  .pbp-access__body {
    font-size: 1.05rem;
    color: var(--ink-soft);
    line-height: 1.75;
    margin: 0 0 24px;
  }
  .pbp-access__checklist {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 12px;
  }
  .pbp-access__checklist li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: .98rem;
    color: var(--ink-mid);
    font-weight: 500;
  }
  .pbp-access__checklist li::before {
    content: '✔';
    color: var(--gold);
    font-weight: 900;
    flex-shrink: 0;
  }

  /* ─── 8. FAQ ────────────────────────────────────── */
  .pbp-faq {
    background: var(--cream);
    padding: 80px 0;
  }
  .pbp-faq__list {
    margin-top: 48px;
    display: grid;
    gap: 16px;
  }
  details.pbp-faq__item {
    background: var(--white);
    border-radius: 10px;
    border: 1px solid #e0d8cc;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
  }
  details.pbp-faq__item summary {
    padding: 20px 24px;
    font-weight: 700;
    font-size: 1.05rem;
    cursor: pointer;
    color: var(--blue-deep);
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
  }
  details.pbp-faq__item summary::after {
    content: '+';
    font-size: 1.5rem;
    font-weight: 300;
    color: var(--gold);
    transition: transform .2s;
    flex-shrink: 0;
    margin-left: 16px;
  }
  details.pbp-faq__item[open] summary::after { content: '−'; }
  details.pbp-faq__item .pbp-faq__answer {
    padding: 0 24px 20px;
    font-size: .98rem;
    color: var(--ink-soft);
    line-height: 1.75;
  }

  /* ─── 9. STUDENT SPOTLIGHT ──────────────────────── */
  .pbp-spotlight {
    background: var(--ink);
    color: var(--white);
    padding: 80px 0;
    text-align: center;
  }
  .pbp-spotlight .pbp-section-title { color: var(--white); }
  .pbp-spotlight .pbp-section-divider { margin: 16px auto 0; }
  .pbp-spotlight__intro {
    color: rgba(255,255,255,.72);
    max-width: 640px;
    margin: 24px auto 40px;
    font-size: 1.05rem;
    line-height: 1.75;
  }
  .pbp-video-wrap {
    max-width: 720px;
    margin: 0 auto;
    position: relative;
    padding-top: 56.25%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
  }
  .pbp-video-wrap iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
  }

  /* ─── 10. BENEFITS ──────────────────────────────── */
  .pbp-benefits {
    background: var(--white);
    padding: 80px 0;
  }
  .pbp-benefits__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: start;
    margin-top: 48px;
  }
  .pbp-benefit-item {
    display: flex;
    gap: 18px;
    align-items: flex-start;
  }
  .pbp-benefit-item__num {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--gold);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1rem;
    flex-shrink: 0;
    margin-top: 2px;
  }
  .pbp-benefit-item__title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--blue-deep);
    margin: 0 0 6px;
  }
  .pbp-benefit-item__body {
    font-size: .95rem;
    color: var(--ink-soft);
    margin: 0;
    line-height: 1.65;
  }

  /* ─── 11. FINAL CTA BAND ────────────────────────── */
  .pbp-cta-band {
    background:
      linear-gradient(160deg, rgba(14,42,74,.96) 0%, rgba(9,20,40,.98) 100%);
    color: var(--white);
    padding: 80px 0;
    text-align: center;
  }
  .pbp-cta-band__title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 900;
    margin: 0 0 16px;
    color: var(--white);
  }
  .pbp-cta-band__sub {
    font-size: 1.15rem;
    color: rgba(255,255,255,.72);
    max-width: 580px;
    margin: 0 auto 36px;
    line-height: 1.7;
  }
  .pbp-cta-band__fine {
    margin-top: 20px;
    font-size: .88rem;
    color: rgba(255,255,255,.45);
  }

  /* ─── Responsive ────────────────────────────────── */
  @media (max-width: 768px) {
    .pbp-access__layout,
    .pbp-benefits__grid { grid-template-columns: 1fr; gap: 40px; }

    .pbp-notice__inner { padding: 36px 24px; }

    .pbp-hero { padding: 70px 0 60px; }

    .pbp-hero__cta-group { flex-direction: column; align-items: center; }
    .pbp-btn { width: 100%; max-width: 340px; text-align: center; }
  }
  @media (max-width: 480px) {
    .pbp-wrap { padding: 0 16px; }
    .pbp-courses-grid { grid-template-columns: 1fr 1fr; }
  }
</style>

<div class="pbp-page">

  <!-- ── 1. HERO ──────────────────────────────────── -->
  <section class="pbp-hero">
    <div class="pbp-wrap">
      <div class="pbp-hero__eyebrow">Blues Piano with Willie Myette</div>
      <h1 class="pbp-hero__title">
        Take Your Blues Piano Playing<br>
        To The Next Level&hellip;<span>Guaranteed.</span>
      </h1>
      <p class="pbp-hero__sub">
        Real blues piano lessons from professional jazz educator Willie Myette.
        Learn the chords, grooves, licks, and improvisation techniques that make
        blues piano sound <em>authentic</em>.
      </p>
      <div class="pbp-hero__cta-group">
        <a href="<?php echo esc_url($academy_url); ?>" class="pbp-btn pbp-btn--gold pbp-btn--lg">
          Setup My FREE Academy Account &rarr;
        </a>
        <a href="#pbp-lessons" class="pbp-btn pbp-btn--white">
          Browse Lessons
        </a>
      </div>
    </div>
  </section>

  <!-- ── 2. MIGRATION NOTICE ──────────────────────── -->
  <section class="pbp-notice">
    <div class="pbp-wrap">
      <div class="pbp-notice__inner">
        <span class="pbp-notice__badge">Important Update</span>
        <h2 class="pbp-notice__title">We&rsquo;ve Moved to Jazzedge Academy</h2>
        <p class="pbp-notice__body">
          The content from this site is now found on <strong>Jazzedge Academy</strong>,
          the new flagship site from Jazzedge. With a free trial to Jazzedge Academy,
          you get access to every lesson chapter&mdash;hours of free lesson content to
          explore. Click below to get started.
        </p>
        <a href="<?php echo esc_url($academy_url); ?>" class="pbp-btn pbp-btn--gold pbp-btn--lg">
          Setup My FREE Academy Account &rarr;
        </a>
      </div>
    </div>
  </section>

  <!-- ── 3. INTRO QUOTE ───────────────────────────── -->
  <section class="pbp-quote-band">
    <div class="pbp-wrap pbp-wrap--narrow">
      <blockquote>
        &ldquo;Your lessons have not only taken me to the next level, but given me
        a much clearer path on where to go next.&rdquo;
        <cite>&mdash; Billy McCafferty, Denver, CO</cite>
      </blockquote>
    </div>
  </section>

  <!-- ── 4. FEATURES ──────────────────────────────── -->
  <section class="pbp-features">
    <div class="pbp-wrap">
      <h2 class="pbp-section-title">Everything You Need to Play Blues Piano</h2>
      <p class="pbp-section-sub">Every membership includes all three pillars of a complete learning experience.</p>
      <hr class="pbp-section-divider">
      <div class="pbp-features__grid">

        <div class="pbp-feature-card">
          <span class="pbp-feature-card__icon">🎬</span>
          <h3 class="pbp-feature-card__title">Full 24 / 7 Access</h3>
          <p class="pbp-feature-card__body">
            Watch lessons as many times as you like, 365 days a year. Stream
            to any internet-enabled device&mdash;phone, tablet, or computer.
            Pick up right where you left off.
          </p>
        </div>

        <div class="pbp-feature-card">
          <span class="pbp-feature-card__icon">🎼</span>
          <h3 class="pbp-feature-card__title">Detailed Sheet Music</h3>
          <p class="pbp-feature-card__body">
            Comprehensive sheet music comes with every lesson. Don&rsquo;t read
            music? Follow along with the virtual keyboard overlay and see every
            note as the instructor plays.
          </p>
        </div>

        <div class="pbp-feature-card">
          <span class="pbp-feature-card__icon">🤝</span>
          <h3 class="pbp-feature-card__title">Student Community</h3>
          <p class="pbp-feature-card__body">
            Learning with others keeps you motivated. Our student community
            lets you ask questions, share goals, and cheer on fellow pianists
            at every stage.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- ── 5. TESTIMONIALS ──────────────────────────── -->
  <section class="pbp-testimonials">
    <div class="pbp-wrap">
      <h2 class="pbp-section-title">What Students Are Saying</h2>
      <hr class="pbp-section-divider">
      <div class="pbp-testimonials__grid">

        <div class="pbp-testi-card">
          <p class="pbp-testi-card__text">
            &ldquo;There is actually no one else in web music education at the level
            of Willie Myette and I have hired them all. If you are at all interested
            in understanding, learning, playing or improving your play at the piano,
            you must investigate this superb pianist and teacher.&rdquo;
          </p>
          <p class="pbp-testi-card__author">Lonnie Moseley</p>
        </div>

        <div class="pbp-testi-card">
          <p class="pbp-testi-card__text">
            &ldquo;Willie has a great passion for teaching. As a former teacher myself
            I can appreciate his passion&mdash;that passion is contagious. It rubs off
            on me and I know I&rsquo;m passionate about it now, primarily because I
            found PianoWithWillie.&rdquo;
          </p>
          <p class="pbp-testi-card__author">Ron Guarascio</p>
          <p class="pbp-testi-card__meta">Student since &rsquo;14</p>
        </div>

        <div class="pbp-testi-card">
          <p class="pbp-testi-card__text">
            &ldquo;I&rsquo;ve been doing lessons for 5 years now, but I&rsquo;ve been
            playing the piano for most of my life and have had many teachers. I can
            sincerely say that my experience here has been my best musical educational
            experience.&rdquo;
          </p>
          <p class="pbp-testi-card__author">Kerry Beaumont</p>
          <p class="pbp-testi-card__meta">Student since &rsquo;11</p>
        </div>

      </div>
    </div>
  </section>

  <!-- ── 6. LESSON LIBRARY ─────────────────────────── -->
  <section class="pbp-lessons" id="pbp-lessons">
    <div class="pbp-wrap">
      <h2 class="pbp-section-title">Lessons Available on Play Blues Piano</h2>
      <p class="pbp-section-sub">A growing library of step-by-step courses covering every aspect of blues and jazz piano.</p>
      <hr class="pbp-section-divider" style="background:var(--gold);">
      <div class="pbp-courses-grid">
        <?php
        $courses = [
          'Jazz & Blues Piano Part 7',
          'Jazz & Blues Piano Part 6',
          'Jazz & Blues Piano Part 5',
          'Jazz & Blues Piano Part 4',
          'Jazz & Blues Piano Part 3',
          'Jazz & Blues Piano Part 2',
          'Jazz & Blues Piano Part 1',
          'Tritone Substitutions',
          'Alternate Changes',
          'Walking Bassline Exercises',
          'Rootless Voicings for Blues',
          'Left Hand Shell Voicings',
          'Classic Blues Endings',
          'Creating Intros',
          'Comping Techniques',
          'Two-Handed Grooves',
          'Dominant 7th Chords',
          'Shuffle & Rock Grooves',
          'Blues Scale Techniques',
          'Intros and Endings',
          'Advanced Turnarounds',
          'Practice Habits & Speed',
          'Right Hand Riffs',
          'Bassline Development',
          'Improvisation Ideas',
        ];
        foreach ($courses as $course) :
        ?>
          <div class="pbp-course-tag"><?php echo esc_html($course); ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── 7. 24/7 ACCESS ───────────────────────────── -->
  <section class="pbp-access">
    <div class="pbp-wrap">
      <div class="pbp-access__layout">
        <div>
          <div class="pbp-access__stat">24 / 7</div>
          <div class="pbp-access__stat-label">Unlimited Streaming Access</div>
          <p class="pbp-access__body">
            Need to watch a lesson over and over to nail every note? Not a problem.
            All memberships include unlimited streaming to every piano lesson in the
            library&mdash;with no restrictions, on any device.
          </p>
          <ul class="pbp-access__checklist">
            <li>Watch on your phone, tablet, or computer</li>
            <li>Pick up exactly where you left off</li>
            <li>Repeat any section as many times as you need</li>
            <li>New lessons added regularly</li>
          </ul>
        </div>
        <div>
          <h2 class="pbp-section-title">Benefits of Online Lessons</h2>
          <hr class="pbp-section-divider">
          <p style="margin:24px 0 0; font-size:1.05rem; color:var(--ink-soft); line-height:1.75;">
            Learn on <strong>your schedule</strong>&mdash;6 AM or 11 PM, it doesn&rsquo;t matter.
            Private lessons from a quality teacher run $60 or more per session, and costs add
            up fast. With a membership to Play Blues Piano you get unlimited 24-hour access to
            the full lesson library at a fraction of the cost.
          </p>
          <a href="<?php echo esc_url($academy_url); ?>"
             class="pbp-btn pbp-btn--gold"
             style="margin-top:28px; display:inline-block;">
            Start Your Free Trial &rarr;
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ── 8. FAQ ────────────────────────────────────── -->
  <section class="pbp-faq">
    <div class="pbp-wrap pbp-wrap--narrow">
      <h2 class="pbp-section-title">Frequently Asked Questions</h2>
      <hr class="pbp-section-divider">
      <div class="pbp-faq__list">

        <details class="pbp-faq__item">
          <summary>How do online lessons work?</summary>
          <div class="pbp-faq__answer">
            Once you become a member you have immediate access to the full lesson library.
            Start from the beginning and work through each lesson in order, or jump straight
            to the topic you want to learn. These lessons are designed for intermediate-level
            players&mdash;you&rsquo;ll need a basic knowledge of chords and rhythms to get the
            most from them, but exercises along the way help you fill any gaps.
          </div>
        </details>

        <details class="pbp-faq__item">
          <summary>Are these the same lessons found on PianoWithWillie?</summary>
          <div class="pbp-faq__answer">
            No. The lessons at Play Blues Piano are unique to this site and are not found
            anywhere else on the Internet.
          </div>
        </details>

        <details class="pbp-faq__item">
          <summary>Where can I access this content now?</summary>
          <div class="pbp-faq__answer">
            All Play Blues Piano content has been migrated to
            <a href="<?php echo esc_url($academy_url); ?>">Jazzedge Academy</a>&mdash;the new
            flagship learning platform from Jazzedge. You can access every lesson chapter with
            a free trial. No credit card required to get started.
          </div>
        </details>

        <details class="pbp-faq__item">
          <summary>What level are these lessons designed for?</summary>
          <div class="pbp-faq__answer">
            The courses are built with intermediate-level players in mind. You should be
            comfortable playing simple chords and have a basic sense of rhythm. Exercises
            within each course help you solidify foundational skills as you progress
            through more advanced material.
          </div>
        </details>

        <details class="pbp-faq__item">
          <summary>Can I watch lessons on my phone or tablet?</summary>
          <div class="pbp-faq__answer">
            Yes. All lessons stream to any internet-enabled device. Watch on your phone on
            the go, then continue on your tablet or desktop at home. Your progress is synced
            across devices automatically.
          </div>
        </details>

      </div>
    </div>
  </section>

  <!-- ── 9. STUDENT SPOTLIGHT ─────────────────────── -->
  <section class="pbp-spotlight">
    <div class="pbp-wrap">
      <h2 class="pbp-section-title">Student Spotlight</h2>
      <hr class="pbp-section-divider">
      <p class="pbp-spotlight__intro">
        Thierry has been a student since 2009 and is from Belgium. Here is his
        rendition of Dave Brubeck&rsquo;s classic&mdash;<em>Take 5</em>.
      </p>
      <!-- Replace with your Vimeo or YouTube embed URL -->
      <div class="pbp-video-wrap">
        <iframe
          src="https://www.youtube.com/embed/dQw4w9WgXcQ"
          title="Student Spotlight – Thierry plays Take 5"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen>
        </iframe>
      </div>
    </div>
  </section>

  <!-- ── 10. BENEFITS DETAILS ──────────────────────── -->
  <section class="pbp-benefits">
    <div class="pbp-wrap">
      <h2 class="pbp-section-title">Why Online Lessons Work</h2>
      <hr class="pbp-section-divider">
      <div class="pbp-benefits__grid">

        <div class="pbp-benefit-item">
          <div class="pbp-benefit-item__num">1</div>
          <div>
            <p class="pbp-benefit-item__title">Learn on Your Schedule</p>
            <p class="pbp-benefit-item__body">
              No commute, no fixed appointment. Practice at 6 AM before work
              or midnight when the house is quiet&mdash;lessons are always waiting.
            </p>
          </div>
        </div>

        <div class="pbp-benefit-item">
          <div class="pbp-benefit-item__num">2</div>
          <div>
            <p class="pbp-benefit-item__title">Affordable Compared to Private Lessons</p>
            <p class="pbp-benefit-item__body">
              Private lessons average $60+ per session. A membership gives you
              the entire library for a fraction of that&mdash;unlimited replays included.
            </p>
          </div>
        </div>

        <div class="pbp-benefit-item">
          <div class="pbp-benefit-item__num">3</div>
          <div>
            <p class="pbp-benefit-item__title">Repeat Until You&rsquo;ve Got It</p>
            <p class="pbp-benefit-item__body">
              Struggling with a tricky lick? Watch the same segment ten times
              in a row. There&rsquo;s no awkwardness&mdash;the lesson is always patient.
            </p>
          </div>
        </div>

        <div class="pbp-benefit-item">
          <div class="pbp-benefit-item__num">4</div>
          <div>
            <p class="pbp-benefit-item__title">Expert Instruction from Day One</p>
            <p class="pbp-benefit-item__body">
              Every lesson is taught by Willie Myette&mdash;professional jazz pianist
              and educator with thousands of students worldwide.
            </p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ── 11. FINAL CTA ─────────────────────────────── -->
  <section class="pbp-cta-band">
    <div class="pbp-wrap">
      <h2 class="pbp-cta-band__title">Ready to Start Playing Blues Piano?</h2>
      <p class="pbp-cta-band__sub">
        All Play Blues Piano content is now on Jazzedge Academy. Start your free
        trial today and unlock every lesson chapter&mdash;no credit card required.
      </p>
      <a href="<?php echo esc_url($academy_url); ?>" class="pbp-btn pbp-btn--gold pbp-btn--lg">
        Setup My FREE Academy Account &rarr;
      </a>
      <p class="pbp-cta-band__fine">Free trial &bull; Instant access &bull; Cancel any time</p>
    </div>
  </section>

</div><!-- .pbp-page -->

<?php get_footer(); ?>
