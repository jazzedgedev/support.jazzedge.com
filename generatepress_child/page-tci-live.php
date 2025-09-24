<?php
/**
 * Template Name: TCI Live Sales Page
 * Description: High-converting landing page for The Confident Improviser™ LIVE (Beginner/Advanced).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* Hide theme chrome (true landing) */
add_filter('generate_show_title', '__return_false');
add_filter('generate_sidebar_layout', fn()=> 'no-sidebar');
add_filter('generate_show_site_header', '__return_false');
add_filter('generate_navigation_position', fn()=> 'none');
add_filter('generate_show_footer', '__return_false');
add_filter('generate_footer_widget_areas', fn()=> 0);

get_header();
?>

<!-- HLS.js for HLS streaming support -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<style>
/* ===== Brand Palette ===== */
:root{
  --sherpa:#004555;     /* Sherpa Blue */
  --jungle:#006B5C;     /* Jungle Green */
  --pomegranate:#E74C3C; /* Pomegranate Red */
  --daintree:#2A3940;   /* Daintree Dark */
  --ocean:#459E90;      /* Soft teal */
  --fog:#F4F7F8;
  --dark-teal:#2C5F5A;  /* Dark teal */
}

/* Reset and base styles */
* { box-sizing: border-box; }

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  line-height: 1.6;
  color: var(--daintree);
  margin: 0;
  padding: 0;
  background: var(--fog);
}

/* Force full width and hide theme elements */
.site-content, .inside-article, .entry-content, .content-area, .grid-container {
  max-width: none !important;
  width: 100% !important;
  padding: 0 !important;
  margin: 0 !important;
}

.site-header, .main-navigation, .site-footer, .footer-widgets {
  display: none !important;
}

/* Main container */
.tci-landing {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--fog) 0%, #ffffff 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  width: 100%;
}

/* ===== Hero Section ===== */
.tci-hero {
  background: linear-gradient(135deg, var(--dark-teal), var(--sherpa));
  color: white;
  padding: 80px 0 60px;
  text-align: center;
  position: relative;
  overflow: hidden;
  width: 100%;
}

.tci-hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.3;
}

.tci-hero-content {
  position: relative;
  z-index: 2;
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
  width: 100%;
}

.tci-logo {
  font-size: 18px;
  font-weight: 700;
  color: var(--pomegranate);
  background: rgba(255, 255, 255, 0.1);
  padding: 8px 16px;
  border-radius: 20px;
  display: inline-block;
  margin-bottom: 20px;
  backdrop-filter: blur(10px);
}

.tci-eyebrow {
  font-size: 16px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.tci-title {
  font-size: clamp(36px, 6vw, 64px);
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 24px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.tci-title .highlight {
  color: var(--pomegranate);
}

.tci-description {
  font-size: clamp(18px, 2.5vw, 22px);
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 40px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.tci-cta-buttons {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
}

.tci-btn-primary,
.tci-btn-secondary {
  padding: 16px 32px;
  border-radius: 50px;
  font-weight: 700;
  font-size: 18px;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
  min-width: 200px;
  text-align: center;
}

.tci-btn-primary {
  background: var(--pomegranate);
  color: white;
  box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
}

.tci-btn-primary:hover {
  background: #c0392b;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(231, 76, 60, 0.6);
  color: white;
  text-decoration: none;
}

.tci-btn-secondary {
  background: transparent;
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
}

.tci-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.6);
  color: white;
  text-decoration: none;
  transform: translateY(-2px);
}

/* ===== Video Section ===== */
.tci-video-section {
  background: #f8f9fa;
  padding: 80px 0;
  border-top: 6px solid var(--jungle);
  width: 100%;
}

.tci-video-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 20px;
  width: 100%;
}

.tci-video-wrapper {
  position: relative;
  padding-top: 56.25%;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  background: #000;
}

.tci-video-wrapper iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
  border-radius: 20px;
}

/* ===== Content Sections ===== */
.tci-section {
  padding: 80px 0;
  width: 100%;
}

.tci-section .container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.tci-section-title {
  font-size: clamp(28px, 4vw, 40px);
  font-weight: 800;
  color: var(--daintree);
  text-align: center;
  margin-bottom: 20px;
  line-height: 1.2;
}

.tci-section-text {
  font-size: 18px;
  line-height: 1.7;
  color: #2A3940;
  text-align: center;
  margin-bottom: 40px;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}

/* ===== Quotes Section ===== */
.tci-quotes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
  margin-top: 40px;
}

.tci-quote {
  background: white;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  border-left: 4px solid var(--pomegranate);
  position: relative;
}

.tci-quote::before {
  content: '"';
  position: absolute;
  top: -10px;
  left: 20px;
  font-size: 60px;
  color: var(--pomegranate);
  font-family: serif;
  line-height: 1;
}

.tci-quote p {
  font-style: italic;
  font-size: 16px;
  line-height: 1.6;
  color: var(--daintree);
  margin: 0;
}

/* ===== Benefits Grid ===== */
.tci-benefits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
  margin-top: 40px;
}

.tci-benefit {
  background: white;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.tci-benefit:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.tci-benefit-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--daintree);
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.tci-benefit-text {
  font-size: 16px;
  line-height: 1.6;
  color: #2A3940;
  margin: 0;
}

/* ===== Final CTA ===== */
.tci-final-cta {
  background: linear-gradient(135deg, var(--jungle), var(--ocean));
  color: white;
  padding: 80px 20px;
  text-align: center;
  border-radius: 20px;
  margin: 40px 0;
  border: 3px solid var(--pomegranate);
}

.tci-final-title {
  font-size: clamp(28px, 4vw, 36px);
  font-weight: 800;
  margin-bottom: 20px;
  color: white;
}

.tci-final-text {
  font-size: 18px;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 40px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

/* ===== Responsive Design ===== */
@media (max-width: 768px) {
  .tci-hero {
    padding: 80px 0;
    min-height: 70vh;
  }
  
  .tci-section {
    padding: 60px 0;
  }
  
  .tci-video-section {
    padding: 60px 0;
  }
  
  .tci-benefits-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .tci-cta-buttons {
    flex-direction: column;
    align-items: center;
  }
  
  .tci-btn-primary,
  .tci-btn-secondary {
    width: 100%;
    max-width: 300px;
  }
}

@media (max-width: 480px) {
  .tci-hero {
    padding: 60px 0;
    min-height: 60vh;
  }
  
  .tci-section {
    padding: 40px 0;
  }
  
  .tci-video-section {
    padding: 40px 0;
  }
  
  .tci-benefit {
    padding: 20px;
  }
  
  .tci-final-cta {
    padding: 40px 20px;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const video = document.getElementById('bunny-video');
  const videoSrc = 'https://vz-0696d3da-4b7.b-cdn.net/70c5d7a6-49f5-4d00-a2b1-8904e2877115/playlist.m3u8';
  
  if (video.canPlayType('application/vnd.apple.mpegurl')) {
    // Safari supports HLS natively
    video.src = videoSrc;
  } else if (Hls.isSupported()) {
    // Use HLS.js for other browsers
    const hls = new Hls();
    hls.loadSource(videoSrc);
    hls.attachMedia(video);
  } else {
    console.error('HLS is not supported in this browser');
  }
});
</script>

<main class="tci-landing">
  <!-- Hero Section -->
  <section class="tci-hero">
    <div class="container">
      <div class="tci-hero-content">
    <div class="tci-logo">JAZZ EDGE</div>
    <div class="tci-eyebrow">The Confident Improviser™ LIVE</div>
    <h1 class="tci-title">Master Jazz Piano in <span class="highlight">Live Classes</span></h1>
    <p class="tci-description">Join beginner and advanced live classes designed by Willie Myette, founder of Jazzedge Academy. Learn the 5 pillars of improvisation with neuroscience-backed teaching methods.</p>
      <div class="tci-cta-buttons">
        <a href="https://jazzedge.academy/join" class="tci-btn-primary">Join Live Classes</a>
        <a href="#video" class="tci-btn-secondary">Watch Sample Class</a>
      </div>
      </div>
    </div>
  </section>

  <!-- Video Section -->
  <section class="tci-video-section" id="video">
    <div class="container">
      <div class="tci-video-container">
    <h2 class="tci-section-title">Watch a Sample Class</h2>
    <p class="tci-section-text">See exactly what you'll learn in The Confident Improviser™ LIVE classes</p>
        <div class="tci-video-wrapper">
          <video 
            id="bunny-video" 
            controls 
            preload="metadata" 
            width="100%" 
            height="100%" 
            style="position:absolute;top:0;left:0;width:100%;height:100%;">
            <source src="https://vz-0696d3da-4b7.b-cdn.net/70c5d7a6-49f5-4d00-a2b1-8904e2877115/playlist.m3u8" type="application/x-mpegURL">
            <p>Your browser does not support the video tag.</p>
          </video>
        </div>
      </div>
    </div>
  </section>

  <!-- Why Join Section -->
  <section class="tci-section">
    <div class="container">
      <h2 class="tci-section-title">Why Join These Live Classes?</h2>
      <p class="tci-section-text">These aren't random jam sessions. Each lesson is carefully structured to reduce overwhelm, build confidence, and give you quick wins at the piano.</p>
      
      <div class="tci-quotes">
        <div class="tci-quote">
          <p>If you don't have a system, you're just hoping. The Confident Improviser™ gives you a system.</p>
        </div>
        <div class="tci-quote">
          <p>I've been playing for 20 years and never understood jazz until now. This system is revolutionary.</p>
        </div>
        <div class="tci-quote">
          <p>The breathing technique was immediate gratification. I was sitting there going, 'Oh my gosh, this is working!'</p>
        </div>
      </div>
    </div>
  </section>

  <!-- What You'll Learn Section -->
  <section class="tci-section" style="background: #f8f9fa;">
    <div class="container">
      <h2 class="tci-section-title">What You'll Learn</h2>
      <p class="tci-section-text">Based on 35+ years of teaching experience and neuroscience research, you'll master the 5 pillars of improvisation:</p>
      
      <div class="tci-benefits-grid">
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🎼</span> Accompaniment
          </div>
          <p class="tci-benefit-text">Build a rock-solid left hand foundation. Learn to play steady bass lines while carrying on a conversation - that's when you know you've got it down.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🎵</span> Melody
          </div>
          <p class="tci-benefit-text">Create beautiful, singable melodies that flow naturally. Learn to think in phrases, not just notes.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🎹</span> Harmony
          </div>
          <p class="tci-benefit-text">Understand chord progressions and how to use them creatively. Master the language of jazz harmony.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🎶</span> Rhythm
          </div>
          <p class="tci-benefit-text">Develop a strong sense of time and groove. Learn to swing and feel the music in your bones.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🎤</span> Expression
          </div>
          <p class="tci-benefit-text">Add emotion and personality to your playing. Learn to tell a story through your music.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Science Section -->
  <section class="tci-section">
    <div class="container">
      <h2 class="tci-section-title">The Science Behind Success</h2>
      <p class="tci-section-text">This isn't just another piano course. It's based on cutting-edge neuroscience research that shows exactly how the brain learns music.</p>
      
      <div class="tci-benefits-grid">
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🧠</span> Dopamine Hits
          </div>
          <p class="tci-benefit-text">Every time you master a simple pattern, your brain releases dopamine - the feel-good chemical that keeps you motivated and coming back for more.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>⚡</span> Neural Pathways
          </div>
          <p class="tci-benefit-text">Repetition builds strong neural pathways. Our system uses the optimal practice intervals to maximize learning efficiency.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🎯</span> Focused Attention
          </div>
          <p class="tci-benefit-text">Short, focused practice sessions are more effective than long, unfocused ones. We teach you how to practice smart, not hard.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Class Structure Section -->
  <section class="tci-section" style="background: #f8f9fa;">
    <div class="container">
      <h2 class="tci-section-title">How The Classes Work</h2>
      <p class="tci-section-text">Two levels designed to meet you where you are and take you where you want to go.</p>
      
      <div class="tci-benefits-grid">
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🌱</span> Beginner Classes
          </div>
          <p class="tci-benefit-text">Perfect for Studio members. Start with simple bass lines and build confidence. Each class includes specific practice items you can work on for 10-15 minutes daily.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>🚀</span> Advanced Classes
          </div>
          <p class="tci-benefit-text">For Academy members ready to dive deeper. Explore complex harmonies, advanced techniques, and sophisticated improvisation concepts.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>📹</span> Class Recordings
          </div>
          <p class="tci-benefit-text">Every live class is recorded and available for review. Practice at your own pace and never miss a lesson.</p>
        </div>
        
        <div class="tci-benefit">
          <div class="tci-benefit-title">
            <span>❓</span> Q&A Sessions
          </div>
          <p class="tci-benefit-text">Get personalized feedback and answers to your questions. Willie and the team are there to support your journey.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Success Stories Section -->
  <section class="tci-section">
    <div class="container">
      <h2 class="tci-section-title">What Students Are Saying</h2>
      <p class="tci-section-text">Real results from students who've transformed their playing.</p>
      
      <div class="tci-quotes">
        <div class="tci-quote">
          <p>The breathing technique was immediate gratification. I was sitting there going, 'Oh my gosh, this is working!'</p>
        </div>
        <div class="tci-quote">
          <p>I've been playing for 20 years and never understood jazz until now. This system is revolutionary.</p>
        </div>
        <div class="tci-quote">
          <p>If you don't have a system, you're just hoping. The Confident Improviser™ gives you a system.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Final CTA Section -->
  <section class="tci-section">
    <div class="container">
      <div class="tci-final-cta">
        <h2 class="tci-final-title">Ready to Play With Confidence?</h2>
        <p class="tci-final-text">Join Jazzedge Academy today and get access to The Confident Improviser™ LIVE beginner and advanced classes, plus a full library of lessons, technique training, and personalized coaching.</p>
        <a href="https://jazzedge.academy/join" class="tci-btn-primary">Join Jazzedge Academy</a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
