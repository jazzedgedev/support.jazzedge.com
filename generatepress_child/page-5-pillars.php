<?php
/*
Template Name: JE Landing — Modern Marketing Page (GeneratePress)
Template Post Type: page
*/
if (!defined('ABSPATH')) exit;

/* ------------ CONFIG ------------ */
$zoom_url  = '#'; // <- put your Zoom join link here
$headshot  = 'https://support.jazzedge.com/wp-content/uploads/2025/08/academy-headshot-willie-smaller.png';

/* Hide theme chrome (true landing) */
add_filter('generate_show_title', '__return_false');
add_filter('generate_sidebar_layout', fn()=> 'no-sidebar');
add_filter('generate_show_site_header', '__return_false');
add_filter('generate_navigation_position', fn()=> 'none');
add_filter('generate_show_footer', '__return_false');
add_filter('generate_footer_widget_areas', fn()=> 0);

get_header(); ?>

<style>
  :root {
    --primary-blue: #2563eb;
    --primary-orange: #f97316;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --text-light: #9ca3af;
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-accent: #eff6ff;
    --border-light: #e5e7eb;
    --border-medium: #d1d5db;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  }

  /* Reset and base styles */
  * { box-sizing: border-box; }
  
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: var(--text-primary);
    margin: 0;
    padding: 0;
    background: var(--bg-secondary);
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
  .marketing-landing {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--bg-accent) 0%, var(--bg-secondary) 100%);
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

  /* Hero Section */
  .hero-section {
    padding: 80px 0 60px;
    text-align: center;
    background: 
      radial-gradient(circle at 20% 20%, rgba(37, 99, 235, 0.1) 0%, transparent 50%),
      radial-gradient(circle at 80% 80%, rgba(249, 115, 22, 0.1) 0%, transparent 50%),
      linear-gradient(135deg, #ffffff 0%, var(--bg-secondary) 100%);
  }

  .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary-blue);
    color: white;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
    box-shadow: var(--shadow-md);
  }

  .hero-badge::before {
    content: "🎯";
    font-size: 16px;
  }

  .hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    line-height: 1.1;
    margin: 0 0 20px;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-orange) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .hero-subtitle {
    font-size: clamp(1.125rem, 2.5vw, 1.375rem);
    color: var(--text-secondary);
    margin: 0 0 32px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
  }

  .hero-date {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: var(--text-primary);
    color: white;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    margin-bottom: 32px;
    box-shadow: var(--shadow-lg);
  }

  .hero-date::before {
    content: "📅";
    font-size: 18px;
  }

  .hero-cta {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    font-size: 16px;
    cursor: pointer;
  }

  .btn-primary {
    background: var(--primary-orange);
    color: white;
    box-shadow: var(--shadow-md);
  }

  .btn-primary:hover {
    background: #ea580c;
    transform: translateY(-2px);
    box-shadow: var(--shadow-xl);
    color: white;
  }

  .btn-secondary {
    background: white;
    color: var(--primary-blue);
    border-color: var(--primary-blue);
    box-shadow: var(--shadow-sm);
  }

  .btn-secondary:hover {
    background: var(--primary-blue);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }

  /* Main Content */
  .main-content {
    padding: 60px 0 80px;
  }

  .content-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 40px;
    align-items: start;
  }

  .card {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-light);
  }

  .card-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 16px;
    color: var(--text-primary);
  }

  .card-lead {
    font-size: 1.125rem;
    color: var(--text-secondary);
    margin: 0 0 24px;
    line-height: 1.7;
  }

  /* Learning List */
  .learning-list {
    display: grid;
    gap: 16px;
    margin: 24px 0;
    padding: 0;
    list-style: none;
  }

  .learning-item {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    background: var(--bg-accent);
    padding: 20px;
    border-radius: 16px;
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
  }

  .learning-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }

  .learning-tag {
    background: var(--primary-blue);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    min-width: 140px;
    text-align: center;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .learning-description {
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0;
  }

  .note {
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: 12px;
    padding: 16px;
    font-size: 14px;
    color: #92400e;
    margin-top: 24px;
  }

  /* Bio Section */
  .bio-section {
    display: flex;
    gap: 20px;
    align-items: center;
    margin-top: 32px;
    padding: 24px;
    background: var(--bg-secondary);
    border-radius: 16px;
    border: 1px solid var(--border-light);
  }

  .bio-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: var(--shadow-md);
    flex-shrink: 0;
  }

  .bio-text {
    margin: 0;
    color: var(--text-secondary);
    line-height: 1.6;
  }

  .bio-text strong {
    color: var(--text-primary);
  }

  /* Testimonials */
  .testimonials {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 24px;
  }

  .testimonial {
    background: white;
    padding: 16px;
    border-radius: 12px;
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-sm);
  }

  .testimonial-text {
    margin: 0 0 8px;
    color: var(--text-secondary);
    font-size: 14px;
    font-style: italic;
  }

  .testimonial-author {
    font-weight: 600;
    color: var(--primary-blue);
    font-size: 13px;
    margin: 0;
  }

  /* Registration Form */
  .registration-card {
    position: sticky;
    top: 20px;
  }

  .form-note {
    text-align: center;
    font-size: 14px;
    color: var(--text-light);
    margin-top: 16px;
  }

  /* Fluent Forms Styling */
  .fluentform .ff-el-group input,
  .fluentform .ff-el-group select,
  .fluentform .ff-el-group textarea {
    border-radius: 12px !important;
    border: 2px solid var(--border-light) !important;
    padding: 12px 16px !important;
    font-size: 16px !important;
    transition: all 0.3s ease !important;
  }

  .fluentform .ff-el-group input:focus,
  .fluentform .ff-el-group textarea:focus {
    outline: none !important;
    border-color: var(--primary-blue) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
  }

  .fluentform .ff-btn {
    background: var(--primary-orange) !important;
    color: white !important;
    border-radius: 12px !important;
    padding: 14px 28px !important;
    font-weight: 600 !important;
    border: none !important;
    font-size: 16px !important;
    transition: all 0.3s ease !important;
    width: 100% !important;
  }

  .fluentform .ff-btn:hover {
    background: #ea580c !important;
    transform: translateY(-2px) !important;
    box-shadow: var(--shadow-lg) !important;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .content-grid {
      grid-template-columns: 1fr;
      gap: 32px;
    }
    
    .registration-card {
      position: relative;
      top: auto;
    }
    
    .hero-cta {
      flex-direction: column;
      align-items: center;
    }
    
    .btn {
      width: 100%;
      max-width: 300px;
      justify-content: center;
    }
    
    .bio-section {
      flex-direction: column;
      text-align: center;
    }
    
    .learning-item {
      flex-direction: column;
      gap: 12px;
    }
    
    .learning-tag {
      align-self: flex-start;
    }
  }

  @media (max-width: 480px) {
    .container {
      padding: 0 16px;
    }
    
    .card {
      padding: 24px;
    }
    
    .hero-section {
      padding: 60px 0 40px;
    }
  }
</style>

<main class="marketing-landing">
  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-badge">Live Training + Replay Available</div>
      <h1 class="hero-title">The 5 Pillars of Improvisation Mastery</h1>
      <p class="hero-subtitle">A focused, practical session to help you improvise with confidence, groove, and expression—without getting lost in theory.</p>
      <div class="hero-date">Wednesday, Sep 3 • 1:00 PM Eastern</div>
      <div class="hero-cta">
        <a class="btn btn-primary" href="#register">
          🎯 Get Zoom Link + Replay Access
        </a>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <section class="main-content">
    <div class="container">
      <div class="content-grid">
        <!-- Learning Content -->
        <div class="card">
          <h2 class="card-title">What You'll Master (No Fluff, Just Results)</h2>
          <p class="card-lead">We'll unpack the five essentials that make great solos possible—then show you how to practice them so they stick.</p>

          <ul class="learning-list">
            <li class="learning-item">
              <span class="learning-tag">Accompaniment</span>
              <p class="learning-description">Build a steady, musical foundation so your right hand is free to create.</p>
            </li>
            <li class="learning-item">
              <span class="learning-tag">Rhythm</span>
              <p class="learning-description">Turn simple ideas into grooves that move—feel, time, and syncopation.</p>
            </li>
            <li class="learning-item">
              <span class="learning-tag">Technique</span>
              <p class="learning-description">Play what you hear with ease—articulation, touch, and control.</p>
            </li>
            <li class="learning-item">
              <span class="learning-tag">Scales</span>
              <p class="learning-description">Use the right sounds the right way—tone colors, shapes, and constraints that spark ideas.</p>
            </li>
            <li class="learning-item">
              <span class="learning-tag">Expression</span>
              <p class="learning-description">Phrasing, space, and storytelling so your solos actually say something.</p>
            </li>
          </ul>

          <div class="note">
            💡 <strong>Take Away:</strong> Leave with a simple practice framework and a mini-checklist to keep your progress focused.
          </div>

          <!-- Bio Section -->
          <div class="bio-section">
            <img src="<?php echo esc_url($headshot); ?>" alt="Willie Myette" class="bio-image">
            <p class="bio-text">
              <strong>About Willie Myette.</strong> Jazz pianist, educator, and founder of JazzEdge. Willie has taught thousands of students worldwide through JazzEdge, JazzPianoLessons, HomeSchoolPiano, and Jazzedge Academy—known for turning complex concepts into practical, musical results.
            </p>
          </div>

          <!-- Testimonials -->
          <div class="testimonials">
            <div class="testimonial">
              <p class="testimonial-text">"Willie's approach finally made improvisation click for me."</p>
              <p class="testimonial-author">— Sarah M.</p>
            </div>
            <div class="testimonial">
              <p class="testimonial-text">"Clear, musical, and motivating. I practiced more this week than all month."</p>
              <p class="testimonial-author">— Dan K.</p>
            </div>
            <div class="testimonial">
              <p class="testimonial-text">"No fluff—just what to work on and how."</p>
              <p class="testimonial-author">— Priya R.</p>
            </div>
          </div>
        </div>

        <!-- Registration Form -->
        <div class="card registration-card" id="register">
          <h2 class="card-title">🎯 Get Your Access</h2>
          <p class="card-lead">Register now to get the Zoom link for the live session plus automatic replay access.</p>
          
          <?php echo do_shortcode('[fluentform id="1"]'); ?>
          
          <p class="form-note">
            🔒 You'll receive the Zoom link + replay automatically. No spam, ever.
          </p>
        </div>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>