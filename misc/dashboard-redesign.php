<?php
// Modern Dashboard Redesign for Jazzedge Academy
// Optimized for older user base with maximum engagement focus
?>

<style>
/* Modern Dashboard Styles */
.dashboard-modern {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
}

.dashboard-hero {
    background: linear-gradient(135deg, #002A34 0%, #004555 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 10px 25px rgba(0, 69, 85, 0.15);
    position: relative;
    overflow: hidden;
}

.dashboard-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(180deg); }
}

.hero-content {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
}

.hero-welcome {
    text-align: left;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 15px 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.hero-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin: 0;
    font-weight: 400;
    line-height: 1.6;
}

.hero-practice-hub {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.hero-action {
    display: flex;
    justify-content: flex-end;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.dashboard-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.dashboard-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
}

.card-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    gap: 12px;
}

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .card-icon svg {
            width: 24px;
            height: 24px;
        }

.icon-practice { background: linear-gradient(135deg, #239B90, #459E90); }
.icon-lessons { background: linear-gradient(135deg, #004555, #002A34); }
.icon-classes { background: linear-gradient(135deg, #F04E23, #d43d1a); }
.icon-coaching { background: linear-gradient(135deg, #459E90, #239B90); }

.card-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.card-subtitle {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 5px 0 0 0;
}

/* Hero Section Styles */
.practice-hub-info h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.practice-hub-icon {
    width: 32px;
    height: 32px;
}

.practice-hub-info p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0 0 20px 0;
    line-height: 1.6;
}

.practice-stats {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    display: block;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

        .btn-primary {
            background: white;
            color: #002A34;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary svg {
            width: 20px;
            height: 20px;
        }

.btn-primary:hover {
    background: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-secondary svg {
            width: 20px;
            height: 20px;
        }

.btn-secondary:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
}

/* Content Cards */
        .content-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .content-preview:hover {
            background: #f1f5f9;
            transform: translateX(5px);
            text-decoration: none;
            color: inherit;
        }

        .content-thumbnail {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            flex-shrink: 0;
        }
        
        .content-thumbnail svg {
            width: 24px;
            height: 24px;
        }
        
        .lesson-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .no-lesson-content {
            text-align: center;
            padding: 20px 0;
        }
        
        .no-lesson-content p {
            color: #6b7280;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .events-content {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .event-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .event-preview:hover {
            background: #f1f5f9;
            transform: translateX(5px);
            text-decoration: none;
            color: inherit;
        }
        
        .no-events-content {
            text-align: center;
            padding: 20px 0;
        }
        
        .no-events-content p {
            color: #6b7280;
            margin-bottom: 15px;
            line-height: 1.6;
        }

.content-info h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 5px 0;
}

.content-info p {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 0;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #239B90, #459E90);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 5px;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.quick-action {
    background: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.quick-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: #3b82f6;
}

        .quick-action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #004555, #002A34);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 15px;
        }
        
        .quick-action-icon svg {
            width: 24px;
            height: 24px;
        }

.quick-action h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 8px 0;
}

.quick-action p {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .hero-content {
        grid-template-columns: 1fr;
        gap: 30px;
        text-align: center;
    }
    
    .hero-welcome {
        text-align: center;
    }
    
    .practice-stats {
        justify-content: center;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-action {
        justify-content: center;
    }
    
    .dashboard-modern {
        padding: 15px;
    }
    
    .dashboard-hero {
        padding: 30px 20px;
    }
}

/* Accessibility Improvements */
.btn-primary:focus,
.btn-secondary:focus,
.quick-action:focus {
    outline: 3px solid #3b82f6;
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .dashboard-card {
        border: 2px solid #000;
    }
    
    .card-title {
        color: #000;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .dashboard-card,
    .btn-primary,
    .btn-secondary,
    .quick-action,
    .content-preview {
        transition: none;
    }
    
    .dashboard-header::before {
        animation: none;
    }
}
</style>

<div class="dashboard-modern">
    <!-- Combined Hero Section -->
    <div class="dashboard-hero">
        <div class="hero-content">
            <div class="hero-welcome">
                <h1 class="hero-title">Welcome back, Willie!</h1>
                <p class="hero-subtitle">Ready to continue your musical journey? Let's make today productive.</p>
            </div>
            
            <div class="hero-practice-hub">
                <div class="practice-hub-info">
                    <h3>
                        <svg class="practice-hub-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        Your Practice Hub
                    </h3>
                    <p>Track your progress, earn badges, and stay motivated with our gamified practice system. Every minute counts toward your musical growth!</p>
                    
                    <div class="practice-stats">
                        <?php
                        // Get real Practice Hub data
                        $current_user_id = get_current_user_id();
                        if ($current_user_id > 0) {
                            // Get user stats from Practice Hub
                            global $wpdb;
                            $user_stats = $wpdb->get_row($wpdb->prepare(
                                "SELECT * FROM {$wpdb->prefix}jph_user_stats WHERE user_id = %d",
                                $current_user_id
                            ), ARRAY_A);
                            
                            if ($user_stats) {
                                $current_streak = intval($user_stats['current_streak'] ?? 0);
                                $total_xp = intval($user_stats['total_xp'] ?? 0);
                                $badges_earned = intval($user_stats['badges_earned'] ?? 0);
                            } else {
                                // Default values if no stats found
                                $current_streak = 0;
                                $total_xp = 0;
                                $badges_earned = 0;
                            }
                        } else {
                            // Default values for non-logged-in users
                            $current_streak = 0;
                            $total_xp = 0;
                            $badges_earned = 0;
                        }
                        ?>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo number_format($current_streak); ?></span>
                            <span class="stat-label">Day Streak</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo number_format($total_xp); ?></span>
                            <span class="stat-label">Total XP</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo number_format($badges_earned); ?></span>
                            <span class="stat-label">Badges Earned</span>
                        </div>
                    </div>
                </div>
                
                <div class="hero-action">
                    <a href="#" class="btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Enter Practice Hub
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Spacing under Practice Hub -->
    <div style="margin-bottom: 40px;"></div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Recent Lessons -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon icon-lessons">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <h3 class="card-title">Continue Learning</h3>
                    <p class="card-subtitle">Pick up where you left off</p>
                </div>
            </div>
            
            <?php
            // Get current user ID
            $current_user_id = get_current_user_id();
            
            if ($current_user_id > 0) {
                $last_lesson = get_user_meta($current_user_id, 'last_viewed_lesson', true);
                
                if (!empty($last_lesson)) {
                    $last_lesson_data = maybe_unserialize($last_lesson);
                    
                    if (is_array($last_lesson_data) && !empty($last_lesson_data['title'])) {
                        $lesson_title = esc_html($last_lesson_data['title']);
                        $permalink = esc_url($last_lesson_data['permalink']);
                        ?>
                        <a href="<?php echo $permalink; ?>" class="content-preview">
                            <div class="content-thumbnail">
                                <img src="https://jazzedge.academy/wp-content/uploads/2024/01/square-piano-play-squashed.jpg" 
                                     alt="<?php echo esc_attr($lesson_title); ?>" 
                                     class="lesson-thumbnail" />
                            </div>
                            <div class="content-info">
                                <h4><?php echo $lesson_title; ?></h4>
                                <p>Last viewed lesson</p>
                            </div>
                        </a>
                        
                        <a href="<?php echo $permalink; ?>" class="btn-primary" style="width: 100%; margin-top: 15px;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Continue Lesson
                        </a>
                        <?php
                    } else {
                        // Fallback content when no valid lesson data
                        ?>
                        <div class="no-lesson-content">
                            <p>The next time you view and play a lesson, it will show up here for you to quickly get back to where you left off.</p>
                            <a href="/paths" class="btn-primary" style="width: 100%; margin-top: 15px;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Choose a Learning Path
                            </a>
                        </div>
                        <?php
                    }
                } else {
                    // No lesson data at all
                    ?>
                    <div class="no-lesson-content">
                        <p>The next time you view and play a lesson, it will show up here for you to quickly get back to where you left off.</p>
                        <a href="/paths" class="btn-primary" style="width: 100%; margin-top: 15px;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Choose a Learning Path
                        </a>
                    </div>
                    <?php
                }
            } else {
                // User not logged in
                ?>
                <div class="no-lesson-content">
                    <p>Please log in to see your recent lessons and continue learning.</p>
                    <a href="/wp-login.php" class="btn-primary" style="width: 100%; margin-top: 15px;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Log In
                    </a>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- Upcoming Events -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon icon-classes">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="card-title">Upcoming Events</h3>
                    <p class="card-subtitle">Join live sessions</p>
                </div>
            </div>
            
            <div class="events-content">
                <?php
                // Get upcoming events using the complete shortcode logic
                $current_user_id = get_current_user_id();
                if ($current_user_id > 0) {
                    // Get user's membership level for filtering
                    $user_level = '';
                    $user_meta = get_user_meta($current_user_id, 'membership_level', true);
                    if ($user_meta) {
                        $user_level = sanitize_title($user_meta);
                    }
                    
                    // Use the complete events shortcode logic
                    $count = 3; // Show 3 events in dashboard
                    $teacher = '';
                    $level = $user_level;
                    $etype = '';
                    
                    // Pull a wide window and then use je_ev_local_dt() to normalize times
                    $now_ts = current_time('timestamp');
                    
                    // Add some buffer time to catch events that might be in different timezone
                    $buffer_hours = 6; // Look back 6 hours to catch events that might have timezone issues
                    $now_with_buffer = $now_ts - ($buffer_hours * 3600);
                    
                    $start = new DateTimeImmutable('@' . $now_with_buffer);
                    $end = new DateTimeImmutable('@' . $now_ts);
                    $end = $end->modify('+365 days');
                    
                    // Get events using the actual function
                    if (function_exists('je_get_events_between')) {
                        $events = je_get_events_between($start, $end, [
                            'teacher' => $teacher,
                            'membership-level' => $level,
                        ]);
                        
                        // Optional event-type filter
                        if ($etype) {
                            $events = array_values(array_filter($events, function($ev) use ($etype) {
                                $slugs = wp_get_post_terms($ev->ID, 'event-type', ['fields' => 'slugs']);
                                if (is_wp_error($slugs) || empty($slugs)) $slugs = wp_get_post_terms($ev->ID, 'event_type', ['fields' => 'slugs']);
                                $slugs = is_wp_error($slugs) ? [] : array_map('sanitize_title', (array)$slugs);
                                return in_array($etype, $slugs, true);
                            }));
                        }
                        
                        // Normalize start times with je_ev_local_dt() (prevents Dec 31 epoch bug)
                        $items = [];
                        foreach ($events as $ev) {
                            $raw = get_post_meta($ev->ID, 'je_event_start', true);
                            $dt = je_ev_local_dt($raw);
                            if (!$dt) continue;
                            $ts = $dt->getTimestamp();
                            
                            // Get event end time, or default to 2 hours after start if no end time
                            $raw_end = get_post_meta($ev->ID, 'je_event_end', true);
                            $end_dt = $raw_end ? je_ev_local_dt($raw_end) : null;
                            $end_ts = $end_dt ? $end_dt->getTimestamp() : ($ts + (2 * 3600)); // Default 2 hours
                            
                            // Keep events visible for 6 hours after they end (increased buffer for timezone issues)
                            $visible_until = $end_ts + (6 * 3600); // 6 hours after end
                            
                            // More inclusive filtering - show events that are either upcoming or recently ended
                            if ($visible_until >= $now_ts) $items[] = ['id' => $ev->ID, 'ts' => $ts, 'end_ts' => $end_ts];
                        }
                        
                        usort($items, fn($a, $b) => $a['ts'] <=> $b['ts']);
                        $items = array_slice($items, 0, $count);
                        
                        if (!empty($items)) {
                            foreach ($items as $it) {
                                $pid = $it['id'];
                                $event_title = get_the_title($pid);
                                $event_permalink = get_permalink($pid);
                                $event_date = wp_date('D, M j • g:i a', $it['ts']);
                                ?>
                                <a href="<?php echo esc_url($event_permalink); ?>" class="event-preview">
                                    <div class="content-thumbnail">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="content-info">
                                        <h4><?php echo esc_html($event_title); ?></h4>
                                        <p><?php echo esc_html($event_date); ?></p>
                                    </div>
                                </a>
                                <?php
                            }
                        } else {
                            // No events found
                            ?>
                            <div class="no-events-content">
                                <p>No upcoming events scheduled. Check back soon for new live sessions!</p>
                            </div>
                            <?php
                        }
                    } else {
                        // Function not available
                        ?>
                        <div class="no-events-content">
                            <p>Events system not available. Please check back later.</p>
                        </div>
                        <?php
                    }
                } else {
                    // User not logged in
                    ?>
                    <div class="no-events-content">
                        <p>Please log in to see upcoming events and live sessions.</p>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <a href="/calendar" class="btn-primary" style="width: 100%; margin-top: 15px;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                View Full Calendar
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <h4>Book Private Lesson</h4>
            <p>Get personalized 1-on-1 coaching</p>
        </div>
        
        <div class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h4>Browse Lessons</h4>
            <p>Explore our lesson library</p>
        </div>
        
        <div class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h4>View Leaderboard</h4>
            <p>See how you rank</p>
        </div>
        
        <div class="quick-action">
            <div class="quick-action-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                </svg>
            </div>
            <h4>Join Community</h4>
            <p>Connect with other students</p>
        </div>
    </div>
</div>

<script>
// Add some interactive enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for quick actions
    const quickActions = document.querySelectorAll('.quick-action');
    quickActions.forEach(action => {
        action.addEventListener('click', function() {
            // Add a subtle click effect
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
    
    // Add hover effects for content previews
    const contentPreviews = document.querySelectorAll('.content-preview');
    contentPreviews.forEach(preview => {
        preview.addEventListener('click', function() {
            // Simulate navigation (replace with actual functionality)
            console.log('Navigate to:', this.querySelector('h4').textContent);
        });
    });
    
    // Animate progress bars on scroll
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressFill = entry.target.querySelector('.progress-fill');
                if (progressFill) {
                    const width = progressFill.style.width;
                    progressFill.style.width = '0%';
                    setTimeout(() => {
                        progressFill.style.width = width;
                    }, 100);
                }
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.progress-bar').forEach(bar => {
        observer.observe(bar);
    });
});
</script>
