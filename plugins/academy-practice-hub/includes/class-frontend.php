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
        $gamification = new JPH_Gamification();
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
                            $gems_balance = $user_stats['gems_balance'] ?? 0;
                            $shield_cost = 100;
                            ?>
                            <button id="jph-buy-shield-btn" class="jph-btn jph-btn-primary" 
                                    <?php echo ($shield_count >= 3 || $gems_balance < $shield_cost) ? 'disabled' : ''; ?>>
                                <span class="btn-icon">🛡️</span>
                                Buy Shield (<?php echo $shield_cost; ?> 💎)
                            </button>
                        </div>
                    </div>
                    
                    <div class="shield-info">
                        <p><strong>What are Streak Shields?</strong></p>
                        <p>Shields protect your practice streak from being broken if you miss a day. You can have up to 3 shields at once.</p>
                        <p><strong>How it works:</strong></p>
                        <ul>
                            <li>If you miss a practice day, a shield is automatically used</li>
                            <li>Your streak continues without interruption</li>
                            <li>Shields cost 100 gems each</li>
                            <li>Maximum 3 shields allowed</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Practice Items Section -->
            <div class="jph-practice-items">
                <h2>🎵 Practice Items</h2>
                <div class="jph-items-grid">
                    <?php foreach ($practice_items as $item): ?>
                    <div class="jph-item-card" data-item-id="<?php echo $item['id']; ?>">
                        <div class="jph-item-header">
                            <h3><?php echo esc_html($item['name']); ?></h3>
                            <span class="jph-item-category"><?php echo esc_html($item['category']); ?></span>
                        </div>
                        <div class="jph-item-content">
                            <?php if (!empty($item['description'])): ?>
                                <p><?php echo esc_html($item['description']); ?></p>
                            <?php endif; ?>
                            
                            <!-- Lesson URL if available -->
                            <?php if (isset($lesson_urls[$item['name']])): ?>
                                <div class="jph-lesson-link">
                                    <a href="<?php echo esc_url($lesson_urls[$item['name']]); ?>" target="_blank" class="jph-btn jph-btn-secondary">
                                        <span class="btn-icon">📚</span>
                                        View Lesson
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="jph-item-actions">
                            <button class="jph-btn jph-btn-primary log-practice-btn" 
                                    data-item-id="<?php echo $item['id']; ?>" 
                                    data-item-name="<?php echo esc_attr($item['name']); ?>">
                                Log Practice
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
                    <p><strong>Cost:</strong> 100 💎</p>
                    <p><strong>Current Shields:</strong> <span id="shield-count"><?php echo $user_stats['streak_shield_count'] ?? 0; ?></span></p>
                    <p><strong>Maximum:</strong> 3 shields</p>
                    
                    <div class="jph-modal-footer">
                        <button id="purchase-shield-btn" class="jph-btn jph-btn-primary">Purchase Shield (100 💎)</button>
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
        <?php
        
        return ob_get_clean();
    }
}
