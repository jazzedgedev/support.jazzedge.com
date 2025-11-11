Here's a comprehensive README for the AI Piano Assessment Plugin:

# AI Piano Assessment Plugin

## **Overview**
A WordPress plugin that creates an intelligent piano learning assessment system. Users fill out a form via Fluent Forms, and the plugin uses AI to analyze their responses and provide personalized recommendations for which of our piano learning sites they should explore.

## **Sites We Recommend**
- **Jazzedge Academy** - Live coaching with teachers, structured curriculum
- **Jazzedge** - Free-form exploration, intermediate/advanced jazz focus  
- **HomeSchoolPiano** - Homeschooling context, family learning, structured curriculum
- **PianoWithWillie** - One-time lifetime access purchases, specific songs/techniques

## **Assessment Questions**

### **Skill Level:**
- Years playing piano
- Current skill level (beginner/intermediate/advanced)
- Which "levers" need help: Rhythm & timing, Improvisation & soloing, Jazz standards & repertoire, Chord progressions & harmony, Sight reading, Technique & finger independence, Ear training, Theory & understanding
- Biggest challenge right now (open text)

### **Musical Preferences:**
- Favorite genres (Jazz, Classical, Pop, Blues, Rock, R&B, Latin, etc.)
- Learning preference: Just watch videos on my own, Live group classes with a teacher, I want to be given a list of lessons to practice
- Interest in live coaching with a teacher (Yes/No/Maybe)

### **Investment & Commitment:**
- Weekly time commitment (15min, 30min, 1hr, 2hr+)
- Budget range (Free, $10-25/month, $25-50/month, $50+/month)
- Interest in one-time lifetime access purchases instead of subscriptions (Yes/No)

### **Goals & Context:**
- Primary goal (Play for myself, Jam with others, Perform gigs, Teach others, etc.)

## **Technical Architecture**

### **Integration Method:**
- Uses Fluent Forms webhook system
- Form submission triggers webhook to plugin endpoint
- Plugin processes data with AI analysis
- Sends personalized email recommendations
- Tags user in FluentCRM for follow-up

### **Plugin Structure:**
```
jazzedge-piano-assessment/
├── jazzedge-piano-assessment.php (main plugin file)
├── includes/
│   ├── class-ai-analyzer.php
│   ├── class-email-generator.php
│   └── class-fluentcrm-integration.php
├── assets/
│   ├── admin.css
│   └── admin.js
└── templates/
    └── recommendation-email.php
```

### **Key Functions:**
- `process_assessment_submission()` - Webhook handler
- `run_ai_analysis()` - Analyzes form responses and generates recommendations
- `send_recommendation_email()` - Sends personalized email with site recommendations
- `tag_user_in_fluentcrm()` - Tags user based on recommended site

### **AI Analysis Logic:**
- Analyzes skill level, preferences, budget, and goals
- Matches user profile to appropriate site(s)
- Provides reasoning for recommendations
- Suggests specific courses/programs within recommended sites

### **Setup Requirements:**
1. Fluent Forms plugin installed
2. OpenAI API key configured
3. FluentCRM integration (optional)
4. Webhook URL: `/wp-json/jazzedge-assessment/v1/process`

### **Branding:**
- Katahdin AI branding throughout admin interface
- "Powered by Katahdin AI" in emails and admin pages
- Logo integration similar to other Katahdin AI plugins

## **Workflow:**
1. User fills out Fluent Forms assessment
2. Form submission triggers webhook to plugin
3. Plugin runs AI analysis on responses
4. Generates personalized recommendations
5. Sends email with site recommendations and reasoning
6. Tags user in FluentCRM for follow-up sequences

This plugin follows the same patterns as the existing MemberPress Subscription Stats and Brand Voice Creator plugins for consistency and maintainability.