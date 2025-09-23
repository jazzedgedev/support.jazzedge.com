<?php
/**
 * Template Name: TCI Live Sales Page
 * Description: High-converting landing page for The Confident Improviser™ LIVE (Beginner/Advanced) — GeneratePress child theme template.
 */

if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<style>
/* ===== Brand Palette ===== */
:root{
  --sherpa:#004555;   /* Sherpa Blue */
  --daintree:#002A34; /* Deep navy */
  --pomegranate:#F04E23; /* Accent/CTA */
  --jungle:#239B90;   /* Teal */
  --ocean:#459E90;    /* Soft teal */
  --fog:#F4F7F8;
}

/* ===== Layout ===== */
.tci-hero{background:linear-gradient(180deg,var(--daintree),var(--sherpa));color:#fff;padding:68px 0 36px;text-align:center;}
.tci-wrap{max-width:1120px;margin:0 auto;padding:0 24px;}
.tci-eyebrow{letter-spacing:.12em;text-transform:uppercase;font-weight:600;color:#B8E6DD;}
.tci-title{font-size:clamp(32px,5vw,56px);line-height:1.05;margin:.25em 0 .35em;font-weight:800;}
.tci-sub{font-size:clamp(16px,2.6vw,22px);color:#E7FAF6;max-width:860px;margin:0 auto;}
.tci-cta{margin-top:26px;display:flex;gap:14px;flex-wrap:wrap;justify-content:center;}
.tci-btn{background:var(--pomegranate);color:#fff;padding:14px 22px;border-radius:12px;font-weight:700;text-decoration:none;}
.tci-btn:hover{filter:brightness(.95)}
.tci-btn--ghost{border:2px solid #9FE3D7;color:#E7FAF6;padding:14px 22px;border-radius:12px;font-weight:700;text-decoration:none;}
.tci-btn--ghost:hover{background:#9FE3D7;color:#002A34}

/* ===== Video ===== */
.tci-video{background:var(--fog);padding:34px 0;border-top:6px solid var(--jungle)}
.tci-video .frame{position:relative;padding-top:56.25%;border-radius:14px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.2);max-width:960px;margin:0 auto;}
.tci-video iframe{position:absolute;inset:0;width:100%;height:100%;border:0}

/* ===== Sections ===== */
.tci-section{padding:56px 0}
.tci-section--alt{background:#fff}
.tci-section--tint{background:linear-gradient(180deg,#F8FCFB,#EFF8F6)}
.tci-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:26px}
.tci-card{grid-column:span 12;background:#fff;border:1px solid #E6EEF0;border-radius:16px;padding:22px}
@media(min-width:900px){.tci-card{grid-column:span 6}}
.tci-h2{font-size:clamp(24px,3.6vw,34px);margin:0 0 10px;color:var(--daintree);font-weight:800;text-align:center;}
.tci-h3{font-size:20px;margin:0 0 6px;color:var(--sherpa);font-weight:800}
.tci-lead{font-size:18px;color:#2A3940;max-width:900px;margin:0 auto;text-align:center;}

/* ===== Pull Quotes ===== */
.tci-quotes{display:grid;gap:18px;max-width:960px;margin:0 auto;}
.tci-quote{background:#fff;border-left:6px solid var(--pomegranate);padding:18px 22px;border-radius:12px;font-size:18px;font-style:italic;color:#2A2A2A;box-shadow:0 4px 14px rgba(0,0,0,.05);}
</style>

<!-- Hero -->
<section class="tci-hero">
  <div class="tci-wrap">
    <div class="tci-eyebrow">Live Beginner & Advanced Classes</div>
    <h1 class="tci-title">The Confident Improviser™ LIVE</h1>
    <p class="tci-sub">Step-by-step live classes that break improvisation into simple, neuroscience-backed steps so you can finally play with confidence at the piano.</p>
    <div class="tci-cta">
      <a href="https://jazzedge.academy/join" class="tci-btn">Join Jazzedge Academy</a>
      <a href="#video" class="tci-btn--ghost">Watch Sample Class</a>
    </div>
  </div>
</section>

<!-- Video -->
<section id="video" class="tci-video">
  <div class="tci-wrap">
    <div class="frame">
      <iframe src="https://player.vimeo.com/video/1120982019?h=8d5f8d7045" allow="autoplay; fullscreen" allowfullscreen></iframe>
    </div>
  </div>
</section>

<!-- Why Join -->
<section class="tci-section tci-section--alt">
  <div class="tci-wrap">
    <h2 class="tci-h2">Why Join These Live Classes?</h2>
    <p class="tci-lead">These aren’t random jam sessions. Each lesson is carefully structured to reduce overwhelm, build confidence, and give you quick wins at the piano.</p>
    <div class="tci-quotes">
      <div class="tci-quote">“If you don’t believe you can improvise, you won’t. These classes give you the mindset and process to succeed.”</div>
      <div class="tci-quote">“Improvisation is just composition sped up. Everyone has a unique voice worth hearing.”</div>
      <div class="tci-quote">“Making mistakes is how you grow. If you’re not making mistakes in practice, you’re not really learning.”</div>
    </div>
  </div>
</section>

<!-- Benefits -->
<section class="tci-section tci-section--tint">
  <div class="tci-wrap">
    <h2 class="tci-h2">What You’ll Get</h2>
    <div class="tci-grid">
      <div class="tci-card">
        <h3 class="tci-h3">Beginner-Friendly</h3>
        <p>Start with simple, clear steps. Build a rock-solid left hand so improvising with your right hand feels natural.</p>
      </div>
      <div class="tci-card">
        <h3 class="tci-h3">Advanced Growth</h3>
        <p>Premier members can join advanced sessions that expand beginner material into jazz, blues, and rock contexts.</p>
      </div>
      <div class="tci-card">
        <h3 class="tci-h3">Recorded Lessons</h3>
        <p>Miss a class? No problem. All sessions are recorded so you can review at your own pace, anytime.</p>
      </div>
      <div class="tci-card">
        <h3 class="tci-h3">Neuroscience-Based</h3>
        <p>Chunking, breathing, and dopamine hits—all backed by science—keep you motivated and on track.</p>
      </div>
    </div>
  </div>
</section>

<!-- Final CTA -->
<section class="tci-section tci-section--alt">
  <div class="tci-wrap" style="text-align:center;">
    <h2 class="tci-h2">Ready to Play With Confidence?</h2>
    <p class="tci-lead">Join Jazzedge Academy today and get access to The Confident Improviser™ LIVE beginner and advanced classes, plus a full library of lessons to take your playing further.</p>
    <div class="tci-cta" style="justify-content:center;">
      <a href="https://jazzedge.academy/join" class="tci-btn">Join Jazzedge Academy</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>