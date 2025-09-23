<?php
/**
 * Template Name: 5 Pillars Landing Page
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<style>
/* General layout */
.landing-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 60px 20px;
}

/* Headline */
.landing-container h1 {
    font-size: 2.2rem;
    margin-bottom: 20px;
    text-align: center;
}

/* Subhead */
.landing-container p.subhead {
    font-size: 1.2rem;
    text-align: center;
    margin-bottom: 40px;
}

/* Willie headshot */
.landing-headshot {
    text-align: center;
    margin-bottom: 30px;
}
.landing-headshot img {
    max-width: 180px;
    border-radius: 50%;
}

/* Form section */
.landing-form {
    background: #f04e23; /* Jazzedge orange */
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    margin-top: 40px;
}
.landing-form h2 {
    color: #fff;
    margin-bottom: 20px;
}
.landing-form p {
    color: #fff;
    margin-bottom: 20px;
}
.landing-form .ff-el-form {
    background: transparent !important;
}

/* Button override */
.landing-form .ff-btn {
    background: #004555 !important;
    color: #fff !important;
    border-radius: 6px !important;
    padding: 12px 28px !important;
    font-size: 1.1rem !important;
    border: none !important;
    transition: background 0.3s ease;
}
.landing-form .ff-btn:hover {
    background: #003344 !important;
    color: #fff !important;
}
</style>

<div class="landing-container">

    <div class="landing-headshot">
        <img src="https://support.jazzedge.com/wp-content/uploads/2025/08/academy-headshot-willie-smaller.png" alt="Willie Myette">
    </div>

    <h1>The 5 Pillars of Improvisation Mastery</h1>
    <p class="subhead">Live Session: Friday, August 29th at 1pm Eastern</p>

    <p>Unlock the secrets to improvisation with a proven framework. In this session, I’ll guide you through the five essential pillars that form the foundation of every great solo. Whether you’re looking to add more expression, strengthen your rhythm, or truly connect with your audience, this session will give you practical steps to start transforming your playing immediately.</p>

    <p>You’ll learn what makes improvisation feel natural and confident—without relying on endless licks or memorization. Plus, you’ll discover how to take what you already know and elevate it into creative, authentic music.</p>

    <div class="landing-form">
        <h2>Get the Link & Replay</h2>
        <p>Register below to reserve your spot and receive the replay after the session.</p>
        <?php echo do_shortcode('[fluentform id="1"]'); ?>
    </div>

</div>

<?php get_footer(); ?>