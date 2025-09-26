<?php
/*
Template Name: Received Your Request
Template Post Type: page
*/
if (!defined('ABSPATH')) exit;

/* Hide theme chrome for clean landing page */
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
    --border-light: #e5e7eb;
    --success-green: #10b981;
    --warning-yellow: #f59e0b;
    --error-red: #ef4444;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: var(--text-primary);
    background: var(--bg-secondary);
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px 20px;
    min-height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 60px;
  }

  .success-card {
    background: var(--bg-primary);
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 48px 40px;
    text-align: center;
    border: 1px solid var(--border-light);
  }

  .success-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--success-green), #059669);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
  }

  .success-icon svg {
    width: 40px;
    height: 40px;
    color: white;
  }

  .success-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 16px;
    line-height: 1.2;
  }

  .success-message {
    font-size: 18px;
    color: var(--text-secondary);
    margin: 0 0 32px;
    line-height: 1.5;
  }


  .contact-info {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 24px;
    margin: 24px 0;
  }

  .contact-info h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 16px;
  }

  .contact-info p {
    margin: 8px 0;
    color: var(--text-secondary);
  }

  .contact-info a {
    color: var(--primary-blue);
    text-decoration: none;
    font-weight: 500;
  }

  .contact-info a:hover {
    text-decoration: underline;
  }

  .site-links {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 32px;
  }

  .site-link {
    display: block;
    background: var(--primary-blue);
    color: white;
    padding: 16px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 16px;
    text-align: center;
    transition: all 0.2s ease;
  }

  .site-link:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
  }

  .footer-note {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--border-light);
    font-size: 14px;
    color: var(--text-light);
  }

  @media (max-width: 640px) {
    .container {
      padding: 20px 16px;
    }
    
    .success-card {
      padding: 32px 24px;
    }
    
    .success-title {
      font-size: 24px;
    }
    
    .success-message {
      font-size: 16px;
    }
  }
</style>

<div class="container">
  <div class="success-card">
    <div class="success-icon">
      <svg fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
      </svg>
    </div>
    
    <h1 class="success-title">We've Received Your Request!</h1>
    
    <p class="success-message">
      Thank you for reaching out to us. We've successfully received your submission and our team will review it shortly.
    </p>
    
    
    <div class="contact-info">
      <h3>Need immediate assistance?</h3>
      <p>If you have urgent questions, please don't hesitate to contact us:</p>
      <p>
        <strong>Email:</strong> <a href="mailto:support@jazzedge.com">support@jazzedge.com</a>
      </p>
    </div>
    
    <div class="site-links">
      <a href="https://jazzedge.academy/" class="site-link" target="_blank">Jazzedge Academy</a>
      <a href="https://jazzedge.com/" class="site-link" target="_blank">Jazzedge</a>
      <a href="https://homeschoolpiano.com/" class="site-link" target="_blank">HomeSchool Piano</a>
      <a href="https://pianowithwillie.com/" class="site-link" target="_blank">Piano With Willie</a>
    </div>
    
    <div class="footer-note">
      <p>This confirmation page was generated automatically. Please keep this page for your records.</p>
    </div>
  </div>
</div>

<?php get_footer(); ?>
