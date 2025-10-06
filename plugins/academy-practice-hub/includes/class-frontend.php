<?php
/**
 * Frontend display for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Frontend {
    
    private $database;
    
    public function __construct() {
        $this->database = new JPH_Database();
        add_shortcode('jph_dashboard', array($this, 'render_dashboard'));
    }
    
    /**
     * Render the student dashboard
     */
    public function render_dashboard($atts) {
        // Only show to logged-in users
        if (!is_user_logged_in()) {
            return '<div class="jph-login-required">Please log in to access your practice dashboard.</div>';
        }
        
        $user_id = get_current_user_id();
        
        // Get user's practice items
        $practice_items = $this->database->get_user_practice_items($user_id);
        
        // Get user's lesson favorites for matching URLs
        $lesson_favorites = $this->database->get_lesson_favorites($user_id);
        
        // Create a lookup array for practice item names to lesson URLs
        $lesson_urls = array();
        foreach ($lesson_favorites as $favorite) {
            if (!empty($favorite['url'])) {
                $lesson_urls[$favorite['title']] = $favorite['url'];
            }
        }
        
        // Get user stats using gamification system
        $gamification = new APH_Gamification();
        $user_stats = $gamification->get_user_stats($user_id);
        
        // Enqueue scripts and styles
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        
        ob_start();
        ?>
        <div class="jph-student-dashboard">
            
            <!-- Success/Error Messages -->
            <div id="jph-messages" class="jph-messages" style="display: none;">
                <div class="jph-message-content">
                    <span class="jph-message-close"><i class="fa-solid fa-circle-xmark"></i></span>
                    <div class="jph-message-text"></div>
                </div>
            </div>
            
            <div class="jph-header">
                <div class="header-top">
                    <h2>🎹 Your Practice Dashboard</h2>
                    <!-- Stats Explanation Button - Top Right -->
                    <button id="jph-stats-explanation-btn" type="button" class="jph-btn jph-btn-secondary jph-stats-help-btn">
                        <span class="btn-icon">📊</span>
                        How do these stats work?
                    </button>
                </div>
                <div class="jph-stats">
                    <div class="stat">
                        <span class="stat-value">⭐<?php echo esc_html($user_stats['current_level']); ?></span>
                        <span class="stat-label">Level</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">⚡<?php echo esc_html($user_stats['total_xp']); ?></span>
                        <span class="stat-label">XP</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">🔥<?php echo esc_html($user_stats['current_streak']); ?></span>
                        <span class="stat-label">Streak</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">💎 <?php echo esc_html($user_stats['gems_balance']); ?></span>
                        <span class="stat-label">GEMS</span>
                    </div>
                </div>
                
                <!-- Neuroscience Practice Tip -->
                <div class="pro-tip-box">
                    <div class="pro-tip-content">
                        <span class="tip-icon">💡</span>
                        <span class="tip-label">Pro Tip:</span>
                        <span class="tip-text" id="neuro-tip-text">Loading practice insight...</span>
                    </div>
                </div>
            </div>
            
            <!-- Shield Protection Section - Moved outside hero section -->
            <div class="jph-shield-protection">
                <div class="shield-accordion-header">
                    <h3>🛡️ Shield Protection</h3>
                    <span class="shield-toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
                </div>
                
                <div class="shield-accordion-content" id="shield-accordion-content" style="display: none;">
                    <!-- Shield Stats and Actions -->
                    <div class="jph-protection-stats">
                        <div class="protection-item">
                            <span class="protection-icon">🛡️</span>
                            <span class="protection-label">Shields:</span>
                            <span class="protection-value" id="shield-count"><?php echo esc_html($user_stats['streak_shield_count'] ?? 0); ?></span>
                        </div>
                        <div class="protection-actions">
                            <?php 
                            $shield_count = $user_stats['streak_shield_count'] ?? 0;
                            $gem_balance = $user_stats['gems_balance'] ?? 0;
                            $shield_cost = 50;
                            $has_enough_gems = $gem_balance >= $shield_cost;
                            ?>
                            
                            <?php if ($shield_count < 3): ?>
                                <?php if ($has_enough_gems): ?>
                                <button type="button" class="button button-secondary" id="purchase-shield-btn" 
                                        data-cost="50" data-nonce="<?php echo wp_create_nonce('jph_purchase_streak_shield'); ?>">
                                    Buy Shield (50 💎)
                                </button>
                                <?php else: ?>
                                <button type="button" class="button button-secondary" id="purchase-shield-btn-insufficient" 
                                        data-cost="50" data-gem-balance="<?php echo $gem_balance; ?>" data-nonce="<?php echo wp_create_nonce('jph_purchase_streak_shield'); ?>">
                                    Buy Shield (50 💎)
                                </button>
                                <?php endif; ?>
                            <?php else: ?>
                            <button type="button" class="button button-secondary" disabled>
                                Max Shields (3)
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Shield Explanation -->
                    <div class="shield-explanation-section">
                        <h4>🛡️ How Shield Protection Works</h4>
                        <p>Shields automatically protect your practice streak when you miss practice days. Think of them as insurance for your streak!</p>
                        
                        <div class="shield-info-grid">
                            <div class="shield-info-item">
                                <h5>⚡ How It Works</h5>
                                <p>Shield protection follows a simple 3-step process:</p>
                                <ul>
                                    <li><strong>Step 1:</strong> You miss a practice day</li>
                                    <li><strong>Step 2:</strong> System checks for available shields</li>
                                    <li><strong>Step 3:</strong> Shield activates automatically</li>
                                </ul>
                            </div>
                            
                            <div class="shield-info-item">
                                <h5>💰 Cost & Limits</h5>
                                <p>Shields have clear pricing and usage limits:</p>
                                <ul>
                                    <li><strong>Cost:</strong> 50 💎 gems per shield</li>
                                    <li><strong>Limit:</strong> Maximum 3 shields at once</li>
                                    <li><strong>Activation:</strong> Completely automatic</li>
                                </ul>
                            </div>
                            
                            <div class="shield-info-item">
                                <h5>💡 Pro Tips</h5>
                                <p>Get the most out of your shield protection:</p>
                                <ul>
                                    <li>Keep 1-2 shields active for peace of mind</li>
                                    <li>Practice regularly to minimize shield usage</li>
                                    <li>Balance shield purchases with other gem priorities</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (($user_stats['current_streak'] ?? 0) === 0 && ($user_stats['longest_streak'] ?? 0) > 0 && ($user_stats['total_sessions'] ?? 0) > 0): ?>
                <div class="jph-streak-recovery">
                    <h4>🔧 Streak Recovery Available</h4>
                    <p>Your streak is broken. You can repair it using gems!</p>
                    <div class="recovery-options">
                        <button type="button" class="button button-primary" id="repair-1-day" data-days="1" data-cost="25">
                            Repair 1 Day (25 💎)
                        </button>
                        <button type="button" class="button button-primary" id="repair-3-days" data-days="3" data-cost="75">
                            Repair 3 Days (75 💎)
                        </button>
                        <button type="button" class="button button-primary" id="repair-7-days" data-days="7" data-cost="175">
                            Repair 7 Days (175 💎)
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Practice Items Section -->
            <div class="jph-practice-items">
                <h3>Your Practice Items 
                    <span class="item-count">(<?php echo count($practice_items); ?>/6)</span>
                </h3>
                <div class="jph-items-grid" id="sortable-practice-items">
                    <?php 
                    // Always show 6 cards
                    for ($i = 0; $i < 6; $i++): 
                        if (isset($practice_items[$i])):
                            $item = $practice_items[$i];
                            
                            // Get last practice date for this item
                            $last_practice = $this->database->get_last_practice_session($user_id, $item['id']);
                            $last_practice_date = $last_practice ? $last_practice['created_at'] : null;
                            
                            // Format the date for display
                            $practice_date_display = '';
                            if ($last_practice_date) {
                                $db_timestamp = strtotime($last_practice_date . ' UTC');
                                $current_utc_timestamp = current_time('timestamp', true);
                                $time_ago = human_time_diff($db_timestamp, $current_utc_timestamp);
                                
                                // Shorten time units for better space usage
                                $time_ago = str_replace('hours', 'hrs', $time_ago);
                                $time_ago = str_replace('minutes', 'min', $time_ago);
                                $time_ago = str_replace('seconds', 'sec', $time_ago);
                                $practice_date_display = $time_ago . " ago";
                            } else {
                                $practice_date_display = "Never practiced";
                            }
                    ?>
                        <div class="jph-item sortable-practice-item" data-item-id="<?php echo esc_attr($item['id']); ?>" draggable="true">
                            <!-- Drag Handle -->
                            <div class="drag-handle" title="Drag to reorder">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <circle cx="4" cy="4" r="1" fill="#666"/>
                                    <circle cx="8" cy="4" r="1" fill="#666"/>
                                    <circle cx="12" cy="4" r="1" fill="#666"/>
                                    <circle cx="4" cy="8" r="1" fill="#666"/>
                                    <circle cx="8" cy="8" r="1" fill="#666"/>
                                    <circle cx="12" cy="8" r="1" fill="#666"/>
                                    <circle cx="4" cy="12" r="1" fill="#666"/>
                                    <circle cx="8" cy="12" r="1" fill="#666"/>
                                    <circle cx="12" cy="12" r="1" fill="#666"/>
                                </svg>
                            </div>
                            <!-- Card Header -->
                            <div class="item-card-header">
                                <h4><?php echo esc_html($item['name']); ?></h4>
                            </div>
                            
                            <!-- Last Practiced Date -->
                            <div class="item-last-practiced">
                                Last practiced: <?php echo esc_html($practice_date_display); ?>
                            </div>
                            
                            <!-- Description -->
                            <div class="item-description">
                                <p><?php echo esc_html($item['description']); ?></p>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="item-actions">
                                <button class="jph-log-practice-btn" data-item-id="<?php echo esc_attr($item['id']); ?>">
                                    Log Practice
                                </button>
                                <div class="item-controls">
                                    <button class="jph-edit-item-btn icon-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-name="<?php echo esc_attr($item['name']); ?>" data-category="<?php echo esc_attr($item['category']); ?>" data-description="<?php echo esc_attr($item['description']); ?>" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                                            <path d="M100.4 417.2C104.5 402.6 112.2 389.3 123 378.5L304.2 197.3L338.1 163.4C354.7 180 389.4 214.7 442.1 267.4L476 301.3L442.1 335.2L260.9 516.4C250.2 527.1 236.8 534.9 222.2 539L94.4 574.6C86.1 576.9 77.1 574.6 71 568.4C64.9 562.2 62.6 553.3 64.9 545L100.4 417.2zM156 413.5C151.6 418.2 148.4 423.9 146.7 430.1L122.6 517L209.5 492.9C215.9 491.1 221.7 487.8 226.5 483.2L155.9 413.5zM510 267.4C493.4 250.8 458.7 216.1 406 163.4L372 129.5C398.5 103 413.4 88.1 416.9 84.6C430.4 71 448.8 63.4 468 63.4C487.2 63.4 505.6 71 519.1 84.6L554.8 120.3C568.4 133.9 576 152.3 576 171.4C576 190.5 568.4 209 554.8 222.5C551.3 226 536.4 240.9 509.9 267.4z"/>
                                        </svg>
                                    </button>
                                    <button class="jph-delete-item-btn icon-btn" data-item-id="<?php echo esc_attr($item['id']); ?>" data-name="<?php echo esc_attr($item['name']); ?>" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                                            <path d="M232.7 69.9L224 96L128 96C110.3 96 96 110.3 96 128C96 145.7 110.3 160 128 160L512 160C529.7 160 544 145.7 544 128C544 110.3 529.7 96 512 96L416 96L407.3 69.9C402.9 56.8 390.7 48 376.9 48L263.1 48C249.3 48 237.1 56.8 232.7 69.9zM512 208L128 208L149.1 531.1C150.7 556.4 171.7 576 197 576L443 576C468.3 576 489.3 556.4 490.9 531.1L512 208z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="jph-item jph-empty-item sortable-empty-slot">
                            <div class="drag-handle disabled" title="Empty slot - not draggable">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <circle cx="4" cy="4" r="1" fill="#ccc"/>
                                    <circle cx="8" cy="4" r="1" fill="#ccc"/>
                                    <circle cx="12" cy="4" r="1" fill="#ccc"/>
                                    <circle cx="4" cy="8" r="1" fill="#ccc"/>
                                    <circle cx="8" cy="8" r="1" fill="#ccc"/>
                                    <circle cx="12" cy="8" r="1" fill="#ccc"/>
                                    <circle cx="4" cy="12" r="1" fill="#ccc"/>
                                    <circle cx="8" cy="12" r="1" fill="#ccc"/>
                                    <circle cx="12" cy="12" r="1" fill="#ccc"/>
                                </svg>
                            </div>
                            <div class="item-info">
                                <h4>Empty Slot</h4>
                                <p>Add a new practice item to get started!</p>
                            </div>
                            <div class="item-actions">
                                <button class="jph-btn jph-btn-primary jph-add-item-btn" type="button">
                                    Add Practice Item
                                </button>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endfor; 
                    ?>
                </div>
            </div>
            
            <!-- Badges Section -->
            <div class="jph-badges-section">
                <h2>🏆 Your Badges</h2>
                <div class="jph-badges-grid">
                    <?php
                    $badges = $this->database->get_badges();
                    $user_badges = $this->database->get_user_badges($user_id);
                    $earned_badge_keys = array_column($user_badges, 'badge_key');
                    
                    foreach ($badges as $badge):
                        $is_earned = in_array($badge['badge_key'], $earned_badge_keys);
                    ?>
                    <div class="jph-badge-card <?php echo $is_earned ? 'earned' : 'locked'; ?>">
                        <div class="jph-badge-image">
                            <?php if (!empty($badge['image_url'])): ?>
                                <img src="<?php echo esc_url($badge['image_url']); ?>" alt="<?php echo esc_attr($badge['name']); ?>">
                            <?php else: ?>
                                <span class="jph-badge-icon"><?php echo $badge['icon'] ?? '🏆'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="jph-badge-content">
                            <h4><?php echo esc_html($badge['name']); ?></h4>
                            <p><?php echo esc_html($badge['description']); ?></p>
                            <div class="jph-badge-rewards">
                                <?php if ($badge['xp_reward'] > 0): ?>
                                    <span class="jph-reward">+<?php echo $badge['xp_reward']; ?> XP</span>
                                <?php endif; ?>
                                <?php if ($badge['gem_reward'] > 0): ?>
                                    <span class="jph-reward">+<?php echo $badge['gem_reward']; ?> 💎</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Full Width Practice History -->
            <div class="jph-practice-history-full">
                <div class="practice-history-header-section">
                    <h3>📊 Your Practice History</h3>
                    <div class="practice-history-controls">
                        <button id="export-history-btn" class="jph-btn jph-btn-secondary">
                            <span class="btn-icon">📥</span>
                            Export CSV
                        </button>
                        <button id="view-all-sessions-btn" class="jph-btn jph-btn-secondary">
                            <span class="btn-icon">👁️</span>
                            View All
                        </button>
                        <button id="load-more-sessions-btn" class="jph-btn jph-btn-secondary">
                            <span class="btn-icon">📈</span>
                            Load More Sessions
                        </button>
                    </div>
                </div>
                <div class="practice-history-header">
                    <div class="practice-history-header-item">Item</div>
                    <div class="practice-history-header-item">Duration</div>
                    <div class="practice-history-header-item">How it felt</div>
                    <div class="practice-history-header-item">Improvement</div>
                    <div class="practice-history-header-item">Date</div>
                    <div class="practice-history-header-item">Actions</div>
                </div>
                <div class="practice-history-list" id="practice-history-list">
                    <div class="loading-message">Loading practice history...</div>
                </div>
                <div id="load-more-container" style="text-align: center; margin-top: 20px; display: none;">
                    <button id="load-more-sessions-bottom" class="jph-btn jph-btn-secondary">
                        <span class="btn-icon">📈</span>
                        Load More Sessions
                    </button>
                </div>
            </div>
            
            <!-- Debug Information (for logged in users) -->
            <?php if (current_user_can('manage_options')): ?>
            <div class="jph-debug-section">
                <h2>🔍 Debug Information</h2>
                <div class="jph-debug-content">
                    <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
                    <p><strong>Is Admin:</strong> <?php echo current_user_can('manage_options') ? 'Yes' : 'No'; ?></p>
                    <div class="jph-debug-actions">
                        <button id="view-all-data-btn" class="jph-btn jph-btn-secondary">All Table Data</button>
                        <button id="view-user-stats-btn" class="jph-btn jph-btn-secondary">User Stats</button>
                        <button id="view-user-badges-btn" class="jph-btn jph-btn-secondary">User Badges</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Practice Session Modal -->
        <div id="practice-session-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2 id="practice-session-title">Logging practice for: <span id="log-practice-item-name"></span></h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <form id="practice-session-form">
                        <input type="hidden" id="practice-item-id" name="item_id">
                        <input type="hidden" id="practice-item-name" name="item_name">
                        
                        <div class="jph-form-group">
                            <label for="practice-duration">Duration (minutes)</label>
                            <input type="number" id="practice-duration" name="duration" min="1" max="300" required>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="practice-notes">Notes (optional)</label>
                            <textarea id="practice-notes" name="notes" rows="3" placeholder="How did your practice session go?"></textarea>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="practice-sentiment">How did it feel?</label>
                            <select id="practice-sentiment" name="sentiment">
                                <option value="">Select...</option>
                                <option value="excellent">😊 Excellent</option>
                                <option value="good">😌 Good</option>
                                <option value="okay">😐 Okay</option>
                                <option value="challenging">😅 Challenging</option>
                                <option value="frustrating">😤 Frustrating</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="practice-improvement">Improvement level</label>
                            <select id="practice-improvement" name="improvement">
                                <option value="">Select...</option>
                                <option value="significant">📈 Significant</option>
                                <option value="moderate">📊 Moderate</option>
                                <option value="slight">📉 Slight</option>
                                <option value="none">➡️ No change</option>
                            </select>
                        </div>
                        
                        <div class="jph-modal-footer">
                            <button type="submit" class="jph-btn jph-btn-primary">Log Practice Session</button>
                            <button type="button" class="jph-btn jph-btn-secondary jph-modal-close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Shield Purchase Modal -->
        <div id="shield-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>🛡️ Purchase Streak Shield</h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <p>A Streak Shield protects your current streak from being broken if you miss a day of practice.</p>
                    <p><strong>Cost:</strong> 50 💎</p>
                    <p><strong>Current Shields:</strong> <span id="shield-count"><?php echo $user_stats['streak_shield_count'] ?? 0; ?></span></p>
                    <p><strong>Maximum:</strong> 3 shields</p>
                    
                    <div class="jph-modal-footer">
                        <button id="purchase-shield-btn" class="jph-btn jph-btn-primary">Purchase Shield (50 💎)</button>
                        <button type="button" class="jph-btn jph-btn-secondary jph-modal-close">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Explanation Modal -->
        <div id="stats-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>📊 Stats Explanation</h2>
                    <span class="jph-modal-close"><i class="fa-solid fa-circle-xmark"></i></span>
                </div>
                <div class="jph-modal-body">
                    <div class="jph-stats-explanation">
                        <h3>Practice Time</h3>
                        <p>Total time spent practicing across all sessions.</p>
                        
                        <h3>Practice Sessions</h3>
                        <p>Number of individual practice sessions logged.</p>
                        
                        <h3>Day Streak</h3>
                        <p>Consecutive days of practice. Miss a day and your streak resets!</p>
                        
                        <h3>Gems</h3>
                        <p>Virtual currency earned through practice. Use gems to purchase streak shields.</p>
                        
                        <h3>Level</h3>
                        <p>Your practice level based on total XP earned.</p>
                        
                        <h3>Badges</h3>
                        <p>Achievements earned for reaching practice milestones.</p>
                    </div>
                    
                    <div class="jph-modal-footer">
                        <button type="button" class="jph-btn jph-btn-secondary jph-modal-close">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .jph-student-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fffe 0%, #f0f8f7 100%);
            min-height: 100vh;
        }
        
        .jph-header {
            margin-bottom: 40px;
            padding: 40px 30px;
            background: linear-gradient(135deg, #004555 0%, #002A34 100%);
            color: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 69, 85, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header-top h2 {
            margin: 0;
            flex: 1;
            font-size: 1.8em;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .jph-stats-help-btn {
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(10px);
            padding: 10px 16px !important;
            font-size: 14px !important;
            white-space: nowrap;
        }
        
        .jph-stats-help-btn:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            transform: translateY(-1px);
        }
        
        /* Modern Pro Tip Styling */
        .pro-tip-box {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 4px solid #f39c12;
            border-radius: 12px;
            padding: 20px 24px;
            margin: 30px 0 20px 0;
            box-shadow: 0 4px 20px rgba(243, 156, 18, 0.15);
            border: 1px solid rgba(243, 156, 18, 0.2);
        }
        
        .pro-tip-content {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #8b4513;
        }
        
        .tip-icon {
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .tip-label {
            font-weight: 700;
            font-size: 14px;
            color: #d35400;
        }
        
        .tip-text {
            font-size: 15px;
            font-weight: 500;
            line-height: 1.5;
            letter-spacing: 0.25px;
        }
        
        .jph-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat {
            background: white;
            padding: 30px 25px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #239B90, #459E90);
        }
        
        .stat:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 69, 85, 0.2);
            border-color: #239B90;
        }
        
        .stat-value {
            display: block;
            font-size: 2.5em;
            font-weight: 800;
            color: #004555;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 1.1em;
            font-weight: 600;
            color: #666;
        }
        
        .jph-btn-secondary {
            background: linear-gradient(135deg, #f8fffe 0%, #e8f5f4 100%);
            color: #004555;
            border: 1px solid #00A8A8;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .jph-btn-secondary:hover {
            background: linear-gradient(135deg, #e8f5f4 0%, #d1e7e4 100%);
            color: #004555;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,168,168,0.2);
        }
        
        .btn-icon {
            font-size: 18px;
        }
        
        .jph-badges-section {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            padding: 30px;
            margin: 30px 0;
        }
        
        .jph-badges-section h3 {
            margin-bottom: 20px;
            color: #004555;
            font-size: 1.4em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge-count {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #004555;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }
        
        .jph-badges-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        
        @media (max-width: 1200px) {
            .jph-badges-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .jph-badges-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .jph-badges-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .jph-badge-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid #e8f5f4;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .jph-badge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 69, 85, 0.15);
            border-color: #004555;
        }
        
        .jph-badge-card.earned {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-color: #ffd700;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.2);
        }
        
        .jph-badge-card.earned:hover {
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.3);
        }
        
        .jph-badge-image {
            width: 64px;
            height: 64px;
            margin: 0 auto 15px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            transition: all 0.3s ease;
        }
        
        .jph-badge-card.earned .jph-badge-image {
            background: transparent;
        }
        
        .jph-badge-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .jph-badge-name {
            font-weight: 600;
            color: #004555;
            font-size: 0.9em;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        
        .jph-badge-description {
            font-size: 0.8em;
            color: #666;
            line-height: 1.3;
            margin-bottom: 10px;
        }
        
        .jph-badge-category {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-badge-category-achievement { background: #e3f2fd; color: #1976d2; }
        .jph-badge-category-milestone { background: #f3e5f5; color: #7b1fa2; }
        .jph-badge-category-special { background: #fff3e0; color: #f57c00; }
        .jph-badge-category-streak { background: #ffebee; color: #d32f2f; }
        .jph-badge-category-level { background: #e8f5e8; color: #388e3c; }
        .jph-badge-category-practice { background: #e0f2f1; color: #00796b; }
        .jph-badge-category-improvement { background: #fce4ec; color: #c2185b; }
        
        .jph-badge-rewards {
            font-size: 0.8em;
            color: #666;
            margin-top: 8px;
            font-weight: 500;
        }
        
        .jph-badge-earned-date {
            font-size: 0.7em;
            color: #28a745;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .no-badges-message {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 1.1em;
        }
        
        .no-badges-message .emoji {
            font-size: 2em;
            display: block;
            margin-bottom: 10px;
        }
        
        .jph-shield-protection {
            background: white;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .shield-accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            cursor: pointer;
            border-bottom: 1px solid #dee2e6;
            transition: background 0.2s ease;
        }
        
        .shield-accordion-header:hover {
            background: linear-gradient(135deg, #e9ecef, #dee2e6);
        }
        
        .shield-accordion-header h3 {
            color: #333;
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .shield-toggle-icon {
            font-size: 16px;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .shield-toggle-icon i {
            font-size: inherit;
        }
        
        .shield-accordion-content {
            padding: 20px;
            background: white;
        }
        
        .shield-explanation-section {
            margin-top: 25px;
            padding: 20px;
            background: #f8fffe;
            border-radius: 12px;
            border-left: 4px solid #00A8A8;
        }
        
        .shield-explanation-section h4 {
            margin: 0 0 10px 0;
            color: #004555;
            font-size: 18px;
            font-weight: 600;
        }
        
        .shield-explanation-section p {
            margin: 0 0 20px 0;
            color: #555;
            line-height: 1.5;
        }
        
        .shield-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        .shield-info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .shield-info-item h5 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }
        
        .shield-info-item p {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .shield-info-item ul {
            margin: 0;
            padding-left: 15px;
            color: #666;
            font-size: 12px;
        }
        
        .shield-info-item li {
            margin-bottom: 3px;
            line-height: 1.3;
        }
        
        @media (max-width: 768px) {
            .shield-info-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .jph-protection-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 25px;
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            border-radius: 16px;
            border: 2px solid #e3f2fd;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        
        .protection-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            border: 2px solid #e1f5fe;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .protection-icon {
            font-size: 24px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .protection-label {
            font-weight: 600;
            color: #2A3940;
            font-size: 16px;
        }
        
        .protection-value {
            background: linear-gradient(45deg, #239B90, #004555);
            color: white;
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 3px 8px rgba(35, 155, 144, 0.4);
        }
        
        .protection-label {
            font-weight: 500;
            color: #666;
        }
        
        .protection-value {
            background: #007cba;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .protection-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .protection-actions .button {
            padding: 12px 20px !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            border: none !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
            box-shadow: 0 3px 8px rgba(0,0,0,0.15) !important;
            opacity: 1 !important;
        }
        
        .protection-actions .button-secondary {
            background: linear-gradient(135deg, #FF6B35, #f04e23) !important;
            color: white !important;
            border: none !important;
        }
        
        .protection-actions .button-secondary:hover {
            background: linear-gradient(135deg, #e05a2b, #d63e1c) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4) !important;
        }
        
        .protection-actions .button-secondary:disabled {
            background: #ccc !important;
            color: #666 !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        /* Practice Items Styles */
        .jph-practice-items {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            padding: 30px;
            margin: 30px 0;
        }
        
        .jph-practice-items h3 {
            margin-bottom: 20px;
            color: #004555;
            font-size: 1.4em;
        }
        
        .jph-items-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 1024px) {
            .jph-items-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .jph-items-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .jph-item-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid #e8f5f4;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .jph-item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 69, 85, 0.15);
            border-color: #004555;
        }
        
        .jph-item-name {
            font-size: 1.1em;
            font-weight: 600;
            color: #004555;
            margin-bottom: 15px;
        }
        
        .log-practice-btn {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .log-practice-btn:hover {
            background: linear-gradient(135deg, #006666, #008888);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 69, 85, 0.3);
        }
        
        /* Practice History Styles */
        .jph-practice-history-full {
            background: white;
            border-radius: 16px;
            border: 2px solid #e8f5f4;
            box-shadow: 0 8px 25px rgba(0, 69, 85, 0.1);
            padding: 30px;
            margin: 30px 0;
        }
        
        .practice-history-header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .practice-history-header-section h3 {
            margin: 0;
            color: #004555;
            font-size: 1.4em;
        }
        
        .practice-history-controls {
            display: flex;
            gap: 10px;
        }
        
        .practice-history-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 2px solid #e8f5f4;
            font-weight: 600;
            color: #004555;
        }
        
        .practice-history-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
        }
        
        .practice-history-item:hover {
            background: #f8f9fa;
        }
        
        .practice-history-item-content {
            font-size: 0.9em;
            color: #333;
        }
        
        .delete-session-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .delete-session-btn:hover {
            background: #c82333;
        }
        
        .loading-message {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 1.1em;
        }
        
        .no-sessions {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 1.1em;
        }
        
        .error-message {
            text-align: center;
            padding: 40px 20px;
            color: #dc3545;
            font-size: 1.1em;
        }
        
        /* Modal Styles */
        .jph-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .jph-modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .jph-modal-header {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .jph-modal-header h2 {
            margin: 0;
            font-size: 1.4em;
        }
        
        .jph-modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .jph-modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .jph-modal-body {
            padding: 30px;
        }
        
        .jph-form-group {
            margin-bottom: 20px;
        }
        
        .jph-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #004555;
        }
        
        .jph-form-group input,
        .jph-form-group select,
        .jph-form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e8f5f4;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .jph-form-group input:focus,
        .jph-form-group select:focus,
        .jph-form-group textarea:focus {
            outline: none;
            border-color: #004555;
            box-shadow: 0 0 0 3px rgba(0, 69, 85, 0.1);
        }
        
        .jph-modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e8f5f4;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        .jph-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .jph-btn-primary {
            background: linear-gradient(135deg, #004555, #006666);
            color: white;
        }
        
        .jph-btn-primary:hover {
            background: linear-gradient(135deg, #006666, #008888);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 69, 85, 0.3);
        }
        
        .jph-btn-secondary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            color: #004555;
            border: 2px solid #e8f5f4;
        }
        
        .jph-btn-secondary:hover {
            background: linear-gradient(135deg, #e9ecef, #dee2e6);
            transform: translateY(-2px);
        }
        
        /* Practice Items Drag and Drop Styles */
        .jph-item {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 2px solid #e8f5f4;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            cursor: move;
        }
        
        .jph-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 69, 85, 0.15);
            border-color: #004555;
        }
        
        .jph-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            z-index: 1000;
        }
        
        .jph-item.drag-over {
            border-color: #004555;
            background: #f0f8ff;
        }
        
        .drag-handle {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: grab;
            opacity: 0.6;
            transition: opacity 0.3s ease;
        }
        
        .drag-handle:hover {
            opacity: 1;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .drag-handle.disabled {
            cursor: default;
            opacity: 0.3;
        }
        
        .item-card-header h4 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
            font-weight: 600;
            color: #004555;
        }
        
        .item-last-practiced {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
        }
        
        .item-description p {
            margin: 0 0 15px 0;
            font-size: 0.9em;
            color: #555;
            line-height: 1.4;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
        
        .item-controls {
            display: flex;
            gap: 5px;
        }
        
        .icon-btn {
            background: none;
            border: none;
            padding: 5px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
            color: #666;
        }
        
        .icon-btn:hover {
            background: #f0f0f0;
            color: #004555;
        }
        
        .jph-empty-item {
            background: linear-gradient(135deg, #f8f9fa, #f0f0f0);
            border: 2px dashed #ccc;
            opacity: 0.7;
        }
        
        .jph-empty-item:hover {
            opacity: 1;
            border-color: #004555;
        }
        
        .item-count {
            font-size: 0.8em;
            color: #666;
            font-weight: normal;
        }
        
        /* Delete Session Button */
        .jph-delete-session-btn {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
            color: #dc3545;
        }
        
        .jph-delete-session-btn:hover {
            background: #ffebee;
            transform: scale(1.1);
            color: #c82333;
        }
        
        .jph-delete-session-btn i {
            font-size: 16px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            
            // Initialize clean neuroscience tips
            initNeuroscienceTips();
            
            // Load practice history
            loadPracticeHistory();
            
            // Load badges
            loadBadges();
            
            // Load lesson favorites
            loadLessonFavorites();
            
            // Initialize other functionality
            initModalHandlers();
            initPracticeSessionHandlers();
            initShieldHandlers();
            initStatsHandlers();
            
            // Clean Neuroscience Tips (Adult-oriented)
            function initNeuroscienceTips() {
                const practiceTips = [
                    "Memory consolidation occurs during sleep — practice 4 hours before bedtime for optimal retention.",
                    "Break practice into 20-25 minute sessions with 5-minute breaks to maximize focus and learning.",
                    "Sleep deprivation reduces motor learning efficiency by 40% — prioritize rest for better progress.",
                    "Practice at 85% difficulty level — challenging but achievable for maximum skill development.",
                    "Slow, perfect repetitions build neural pathways faster than rushed practice — quality over speed.",
                    "Morning practice shows 23% better retention — optimize for your brain's peak learning hours.",
                    "Mental practice activates identical brain areas as physical practice — visualization accelerates learning.",
                    "10 minutes of meditation before practice can improve focus by up to 67% during sessions.",
                    "Scale practice primes neural pathways for complex pieces — warm up cognitively, not just physically.",
                    "Playing simple pieces while multitasking engages implicit learning systems — passive absorption works.",
                    "More than 4 hours of daily practice can decrease accuracy by 25% — avoid diminishing returns.",
                    "Mental rehearsal triggers the same neural patterns as physical practice — utilize downtime effectively."
                ];
                
                // Show random tip on page load only
                const randomIndex = Math.floor(Math.random() * practiceTips.length);
                const tipElement = document.getElementById('neuro-tip-text');
                
                if (tipElement) {
                    tipElement.textContent = practiceTips[randomIndex];
                    
                    // Add subtle fade-in effect
                    tipElement.style.opacity = '0';
                    setTimeout(() => {
                        tipElement.style.transition = 'opacity 0.6s ease-in-out';
                        tipElement.style.opacity = '1';
                    }, 200);
                }
            }
            
            // Load practice history
            function loadPracticeHistory() {
                $.ajax({
                    url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    data: { limit: 10 },
                    success: function(response) {
                        if (response.success && response.sessions) {
                            displayPracticeHistory(response.sessions);
                        } else {
                            $('#practice-history-list').html('<div class="no-sessions">No practice sessions found.</div>');
                        }
                    },
                    error: function() {
                        $('#practice-history-list').html('<div class="error-message">Error loading practice history.</div>');
                    }
                });
            }
            
            // Display practice history
            function displayPracticeHistory(sessions) {
                let html = '';
                if (sessions.length === 0) {
                    html = '<div class="no-sessions">No practice sessions found.</div>';
                } else {
                    sessions.forEach(session => {
                        const date = new Date(session.created_at).toLocaleDateString();
                        const sentiment = session.sentiment || 'Not specified';
                        const improvement = session.improvement_detected ? 'Yes' : 'No';
                        
                        html += `
                            <div class="practice-history-item">
                                <div class="practice-history-item-content">${session.item_name}</div>
                                <div class="practice-history-item-content">${session.duration_minutes} min</div>
                                <div class="practice-history-item-content">${sentiment}</div>
                                <div class="practice-history-item-content">${improvement}</div>
                                <div class="practice-history-item-content">${date}</div>
                                <div class="practice-history-item-content">
                                    <button class="jph-delete-session-btn" data-session-id="${session.id}" data-item-name="${session.item_name || 'Unknown Item'}" title="Delete this practice session"><i class="fa-solid fa-circle-xmark"></i></button>
                                </div>
                            </div>
                        `;
                    });
                }
                $('#practice-history-list').html(html);
            }
            
            // Load badges
            function loadBadges() {
                console.log('Loading badges...');
                $.ajax({
                    url: '<?php echo rest_url('jph/v1/badges'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        console.log('Badges API response:', response);
                        if (response.success) {
                            console.log('Success! Displaying badges:', response.badges);
                            displayBadges(response.badges);
                        } else {
                            console.log('API returned success: false');
                            $('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading badges:', error);
                        console.error('XHR response:', xhr.responseText);
                        $('#jph-badges-grid').html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges earned yet</div>');
                    }
                });
            }
            
            // Display badges
            function displayBadges(badges) {
                var $container = $('#jph-badges-grid');
                
                if (!badges || badges.length === 0) {
                    $container.html('<div class="no-badges-message"><span class="emoji">🏆</span>No badges available yet. Keep practicing to earn your first badge!</div>');
                    return;
                }
                
                var html = '';
                var earnedCount = 0;
                badges.forEach(function(badge, index) {
                    var earnedClass = badge.is_earned ? 'earned' : 'locked';
                    if (badge.is_earned) earnedCount++;
                    
                    var badgeImage = badge.image_url && badge.image_url.startsWith('http') ? 
                        '<img src="' + badge.image_url + '" alt="' + badge.name + '">' : 
                        '<span class="badge-emoji">' + (badge.icon || 'BADGE') + '</span>';
                    
                    var earnedDate = badge.is_earned && badge.earned_at ? 
                        '<div class="jph-badge-earned-date">Earned: ' + formatDate(badge.earned_at) + '</div>' : '';
                    
                    html += '<div class="jph-badge-card ' + earnedClass + '">';
                    html += '    <div class="jph-badge-image">' + badgeImage + '</div>';
                    html += '    <div class="jph-badge-name">' + escapeHtml(badge.name) + '</div>';
                    html += '    <div class="jph-badge-description">' + escapeHtml(badge.description || '') + '</div>';
                    html += '    <div class="jph-badge-rewards">+' + (badge.xp_reward || 0) + ' XP +' + (badge.gem_reward || 0) + ' 💎</div>';
                    html += earnedDate;
                    html += '</div>';
                });
                
                $container.html(html);
                
                // Update badge count if element exists
                if ($('#badge-count').length) {
                    $('#badge-count').text(earnedCount);
                }
            }
            
            // Load lesson favorites
            function loadLessonFavorites() {
                console.log('Loading lesson favorites...');
                $.ajax({
                    url: '<?php echo rest_url('jph/v1/lesson-favorites'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var select = $('#lesson-favorite-select');
                            select.empty();
                            
                            if (response.favorites.length === 0) {
                                select.append('<option value="">No lesson favorites found</option>');
                            } else {
                                select.append('<option value="">Select a lesson favorite...</option>');
                                $.each(response.favorites, function(index, favorite) {
                                    select.append('<option value="' + favorite.id + '" data-title="' + escapeHtml(favorite.title) + '" data-category="' + escapeHtml(favorite.category) + '" data-description="' + escapeHtml(favorite.description || '') + '">' + escapeHtml(favorite.title) + '</option>');
                                });
                            }
                        } else {
                            $('#lesson-favorite-select').html('<option value="">Error loading favorites</option>');
                        }
                    },
                    error: function() {
                        $('#lesson-favorite-select').html('<option value="">Error loading favorites</option>');
                    }
                });
            }
            
            // Helper function to format dates
            function formatDate(dateString) {
                if (!dateString) return '';
                var date = new Date(dateString);
                return date.toLocaleDateString();
            }
            
            // Helper function to escape HTML
            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }
            
            // Initialize modal handlers
            function initModalHandlers() {
                // Close modal handlers
                $('.jph-modal-close').on('click', function() {
                    $(this).closest('.jph-modal').hide();
                });
                
                // Click outside to close
                $('.jph-modal').on('click', function(e) {
                    if (e.target === this) {
                        $(this).hide();
                    }
                });
            }
            
            // Initialize practice session handlers
            function initPracticeSessionHandlers() {
                // Log practice session button handlers
                $('.log-practice-btn').on('click', function() {
                    const itemName = $(this).data('item-name');
                    $('#practice-item-name').val(itemName);
                    $('#practice-session-modal').show();
                });
                
                // Practice session form submission
                $('#practice-session-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = {
                        item_name: formData.get('item_name'),
                        duration_minutes: parseInt(formData.get('duration_minutes')),
                        sentiment: formData.get('sentiment'),
                        improvement_detected: formData.get('improvement') === 'significant' || formData.get('improvement') === 'moderate',
                        notes: formData.get('notes')
                    };
                    
                    $.ajax({
                        url: '<?php echo rest_url('jph/v1/practice-sessions'); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: data,
                        success: function(response) {
                            if (response.success) {
                                $('#practice-session-modal').hide();
                                location.reload(); // Refresh to show updated stats
                            } else {
                                alert('Error logging practice session: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function() {
                            alert('Error logging practice session');
                        }
                    });
                });
            }
            
            // Initialize shield handlers
            function initShieldHandlers() {
                // Shield accordion toggle
                jQuery(document).on('click', '.shield-accordion-header', function(e) {
                    e.preventDefault();
                    console.log('Shield accordion clicked');
                    
                    const content = jQuery('#shield-accordion-content');
                    const header = jQuery(this);
                    const icon = header.find('.shield-toggle-icon');
                    
                    console.log('Content found:', content.length);
                    console.log('Header found:', header.length);
                    console.log('Icon found:', icon.length);
                    
                    if (content.is(':visible')) {
                        content.slideUp(300);
                        header.removeClass('active');
                        icon.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    } else {
                        content.slideDown(300);
                        header.addClass('active');
                        icon.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    }
                });
                
                // Purchase shield button
                jQuery(document).on('click', '#purchase-shield-btn', function() {
                    const cost = jQuery(this).data('cost');
                    const nonce = jQuery(this).data('nonce');
                    
                    // Check current shield count from the display
                    const shieldCountText = jQuery('#shield-count').text();
                    const currentShieldCount = parseInt(shieldCountText) || 0;
                    
                    console.log('Current shield count from display:', currentShieldCount);
                    console.log('Button data:', { cost, nonce });
                    
                    // Prevent purchase if already at max shields
                    if (currentShieldCount >= 3) {
                        alert('You already have the maximum number of shields (3). You cannot purchase more shields.');
                        return;
                    }
                    
                    if (!confirm(`Purchase Streak Shield for ${cost} gems?`)) {
                        return;
                    }
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('jph/v1/purchase-shield'); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: {
                            cost: cost,
                            nonce: nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update shield count display
                                jQuery('#shield-count').text(response.data.new_shield_count);
                                
                                // Update gem balance in stats
                                jQuery('.stat-value').each(function() {
                                    if (jQuery(this).text().includes('💎')) {
                                        jQuery(this).text(response.data.new_gem_balance + ' 💎');
                                    }
                                });
                                
                                // Update button state if at max shields
                                if (response.data.new_shield_count >= 3) {
                                    jQuery('#purchase-shield-btn').prop('disabled', true).text('Max Shields (3)');
                                }
                                
                                alert('Shield purchased successfully!');
                            } else {
                                alert('Error purchasing shield: ' + (response.message || 'Unknown error'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('JPH: Purchase Shield error:', error);
                            console.error('JPH: XHR response:', xhr.responseText);
                            console.error('JPH: Status:', status);
                            
                            // Try to parse the error response
                            let errorMessage = 'Network error. Please try again.';
                            
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    const errorData = JSON.parse(xhr.responseText);
                                    if (errorData.message) {
                                        errorMessage = errorData.message;
                                    }
                                } catch (e) {
                                    // If it's not JSON, it might be an HTML error page
                                    if (xhr.responseText.includes('critical error')) {
                                        errorMessage = 'Server error occurred. Please try again later.';
                                    }
                                }
                            }
                            
                            alert(errorMessage);
                        }
                    });
                });
                
                // Insufficient gems button
                jQuery(document).on('click', '#purchase-shield-btn-insufficient', function() {
                    const cost = jQuery(this).data('cost');
                    const gemBalance = jQuery(this).data('gem-balance');
                    const needed = cost - gemBalance;
                    
                    alert(`Insufficient gems! You have ${gemBalance} 💎 but need ${cost} 💎. You need ${needed} more gems to purchase a shield.`);
                });
            }
            
            // Initialize stats handlers
            function initStatsHandlers() {
                // Stats explanation button
                $('#jph-stats-explanation-btn').on('click', function() {
                    $('#stats-modal').show();
                });
            }
            
            // Initialize drag and drop for practice items
            function initDragAndDrop() {
                let draggedElement = null;
                
                // Remove any existing drag classes first
                $('.sortable-practice-item').removeClass('dragging drag-over');
                
                // Make practice items draggable
                $('.sortable-practice-item').on('dragstart', function(e) {
                    draggedElement = this;
                    $(this).addClass('dragging');
                    e.originalEvent.dataTransfer.effectAllowed = 'move';
                });
                
                $('.sortable-practice-item').on('dragend', function() {
                    // Remove dragging class from all elements
                    $('.sortable-practice-item').removeClass('dragging drag-over');
                    draggedElement = null;
                });
                
                // Handle drop zones
                $('.sortable-practice-item, .sortable-empty-slot').on('dragover', function(e) {
                    e.preventDefault();
                    e.originalEvent.dataTransfer.dropEffect = 'move';
                    
                    // Add visual feedback for drop target
                    if (this !== draggedElement) {
                        $(this).addClass('drag-over');
                    }
                });
                
                $('.sortable-practice-item, .sortable-empty-slot').on('dragleave', function(e) {
                    // Remove drag-over class when leaving
                    $(this).removeClass('drag-over');
                });
                
                $('.sortable-practice-item, .sortable-empty-slot').on('drop', function(e) {
                    e.preventDefault();
                    
                    // Remove all drag classes
                    $('.sortable-practice-item').removeClass('dragging drag-over');
                    
                    if (draggedElement && draggedElement !== this) {
                        // Swap the elements
                        const draggedHTML = draggedElement.outerHTML;
                        const targetHTML = this.outerHTML;
                        
                        $(draggedElement).replaceWith(targetHTML);
                        $(this).replaceWith(draggedHTML);
                        
                        // Re-initialize drag and drop for new elements
                        setTimeout(function() {
                            initDragAndDrop();
                        }, 100);
                        
                        // Update order in database
                        updatePracticeItemOrder();
                    }
                    
                    draggedElement = null;
                });
            }
            
            // Update practice item order
            function updatePracticeItemOrder() {
                const itemIds = [];
                $('.sortable-practice-item').each(function() {
                    const itemId = $(this).data('item-id');
                    if (itemId) {
                        itemIds.push(itemId);
                    }
                });
                
                // Send to server to update order
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'jph_update_practice_item_order',
                        item_ids: itemIds,
                        nonce: '<?php echo wp_create_nonce('jph_update_practice_item_order'); ?>'
                    },
                    success: function(response) {
                        if (!response.success) {
                            console.error('Failed to update practice item order');
                        }
                    },
                    error: function() {
                        console.error('Error updating practice item order');
                    }
                });
            }
            
            // Initialize delete session handlers
            function initDeleteSessionHandlers() {
                $(document).on('click', '.jph-delete-session-btn', function() {
                    const sessionId = $(this).data('session-id');
                    const itemName = $(this).data('item-name');
                    
                    if (confirm('Are you sure you want to delete this practice session for "' + itemName + '"? This action cannot be undone.')) {
                        $.ajax({
                            url: '<?php echo rest_url('jph/v1/practice-sessions/'); ?>' + sessionId,
                            method: 'DELETE',
                            headers: {
                                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Remove the session row from the table
                                    $(this).closest('.practice-history-item').fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                    
                                    // Reload the page to update stats
                                    setTimeout(function() {
                                        location.reload();
                                    }, 500);
                                } else {
                                    alert('Failed to delete practice session: ' + (response.message || 'Unknown error'));
                                }
                            }.bind(this),
                            error: function() {
                                alert('Error deleting practice session');
                            }
                        });
                    }
                });
            }
            
            // Initialize all functionality
            initDragAndDrop();
            initDeleteSessionHandlers();
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
}
