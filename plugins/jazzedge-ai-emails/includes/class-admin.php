<?php
/**
 * Admin UI for Jazzedge AI Emails.
 */

if (!defined('ABSPATH')) {
    exit;
}

class JE_Emails_Admin {

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $hub_ok = function_exists('katahdin_ai_hub');
        ?>
        <div class="wrap je-ai-emails-wrap">
            <h1 class="je-ai-emails-title"><?php echo esc_html__('AI Emails', 'jazzedge-ai-emails'); ?></h1>
            <?php if (!$hub_ok) : ?>
                <div class="notice notice-error">
                    <p><?php echo esc_html__('Katahdin AI Hub must be installed and active for AI features.', 'jazzedge-ai-emails'); ?></p>
                </div>
            <?php endif; ?>

            <nav class="je-tabs" role="tablist">
                <button type="button" class="je-tab is-active" data-tab="create" role="tab" aria-selected="true">
                    <?php echo esc_html__('Create Email', 'jazzedge-ai-emails'); ?>
                </button>
                <button type="button" class="je-tab" data-tab="history" role="tab" aria-selected="false">
                    <?php echo esc_html__('Email History', 'jazzedge-ai-emails'); ?>
                </button>
                <button type="button" class="je-tab" data-tab="settings" role="tab" aria-selected="false">
                    <?php echo esc_html__('Settings', 'jazzedge-ai-emails'); ?>
                </button>
                <button type="button" id="je-btn-new-email" class="je-btn je-btn-ghost je-btn-sm je-new-email-btn" title="<?php echo esc_attr__('Clear everything and start a fresh email', 'jazzedge-ai-emails'); ?>">
                    <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                    <?php echo esc_html__('Start Over', 'jazzedge-ai-emails'); ?>
                </button>
            </nav>

            <div id="je-panel-create" class="je-panel is-active" role="tabpanel" data-panel="create">
                <div class="je-card">
                    <div class="je-prompt-sections">

                        <div class="je-prompt-section">
                            <div class="je-prompt-section-header">
                                <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                                <h3 class="je-prompt-section-title"><?php echo esc_html__('Important Points', 'jazzedge-ai-emails'); ?></h3>
                                <span class="je-prompt-section-hint"><?php echo esc_html__('Key facts the AI needs — dates, links, prices, offer details, etc.', 'jazzedge-ai-emails'); ?></span>
                            </div>
                            <div class="je-points-list" id="je-points-list">
                                <div class="je-point-row">
                                    <input type="text" class="je-input je-point-input" placeholder="<?php echo esc_attr__('e.g. Webinar is Thursday March 26th at 1pm Eastern', 'jazzedge-ai-emails'); ?>" />
                                    <button type="button" class="je-btn je-btn-ghost je-btn-icon je-remove-point" title="<?php echo esc_attr__('Remove', 'jazzedge-ai-emails'); ?>" tabindex="-1">
                                        <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <div class="je-point-row">
                                    <input type="text" class="je-input je-point-input" placeholder="<?php echo esc_attr__('e.g. Sale price is $147, ends March 30th', 'jazzedge-ai-emails'); ?>" />
                                    <button type="button" class="je-btn je-btn-ghost je-btn-icon je-remove-point" title="<?php echo esc_attr__('Remove', 'jazzedge-ai-emails'); ?>" tabindex="-1">
                                        <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <div class="je-point-row">
                                    <input type="text" class="je-input je-point-input" placeholder="<?php echo esc_attr__('e.g. Purchase link: https://jazzedge.academy/item/...', 'jazzedge-ai-emails'); ?>" />
                                    <button type="button" class="je-btn je-btn-ghost je-btn-icon je-remove-point" title="<?php echo esc_attr__('Remove', 'jazzedge-ai-emails'); ?>" tabindex="-1">
                                        <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="je-btn je-btn-ghost je-btn-sm" id="je-add-point">
                                <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                <?php echo esc_html__('Add another point', 'jazzedge-ai-emails'); ?>
                            </button>
                        </div>

                        <!-- Section 1b: Create Email About -->
                        <div class="je-prompt-section">
                            <div class="je-prompt-section-header">
                                <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3z" /></svg>
                                <h3 class="je-prompt-section-title"><?php echo esc_html__('Create Email About', 'jazzedge-ai-emails'); ?></h3>
                                <span class="je-prompt-section-hint"><?php echo esc_html__("Optional. Describe what this email is about if you don't have a reference email below.", 'jazzedge-ai-emails'); ?></span>
                            </div>
                            <textarea id="je-email-about" class="je-textarea" rows="4" placeholder="<?php echo esc_attr__("e.g. This is a promotional email for the Rock Piano Intensive course. It's a pre-recorded course students can download and keep forever. The goal is to get students to purchase before the sale ends.", 'jazzedge-ai-emails'); ?>"></textarea>
                        </div>

                        <!-- Section 2: Content to base off -->
                        <div class="je-prompt-section">
                            <div class="je-prompt-section-header">
                                <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                                <h3 class="je-prompt-section-title"><?php echo esc_html__('Content to Base Off', 'jazzedge-ai-emails'); ?></h3>
                                <span class="je-prompt-section-hint"><?php echo esc_html__('Optional. Paste any content — an email, article, outline, or notes — as a style/tone guide. The AI will not rewrite it, only draw inspiration from it.', 'jazzedge-ai-emails'); ?></span>
                            </div>
                            <textarea id="je-base-email" class="je-textarea je-textarea-code" rows="8" placeholder="<?php echo esc_attr__('Paste any content here — a previous email, article, outline, or notes…', 'jazzedge-ai-emails'); ?>"></textarea>
                        </div>

                        <!-- Section: Important URLs -->
                        <div class="je-prompt-section">
                            <div class="je-prompt-section-header">
                                <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                                <h3 class="je-prompt-section-title"><?php echo esc_html__('Important URL(s)', 'jazzedge-ai-emails'); ?></h3>
                                <span class="je-prompt-section-hint"><?php echo esc_html__('Links that must appear in the email. Add the URL and the text to link — the URL will not be shown raw.', 'jazzedge-ai-emails'); ?></span>
                            </div>
                            <div class="je-urls-list">
                                <div class="je-url-row">
                                    <input type="url" class="je-input je-url-input" id="je-url-1" placeholder="<?php echo esc_attr__('https://jazzedge.academy/item/...', 'jazzedge-ai-emails'); ?>" />
                                    <input type="text" class="je-input je-url-text-input" id="je-url-text-1" placeholder="<?php echo esc_attr__('Link text e.g. Get the course here', 'jazzedge-ai-emails'); ?>" />
                                </div>
                                <div class="je-url-row">
                                    <input type="url" class="je-input je-url-input" id="je-url-2" placeholder="<?php echo esc_attr__('https://...', 'jazzedge-ai-emails'); ?>" />
                                    <input type="text" class="je-input je-url-text-input" id="je-url-text-2" placeholder="<?php echo esc_attr__('Link text e.g. Join the webinar here', 'jazzedge-ai-emails'); ?>" />
                                </div>
                            </div>
                        </div>

                        <div class="je-prompt-section">
                            <div class="je-prompt-section-header">
                                <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" /></svg>
                                <h3 class="je-prompt-section-title"><?php echo esc_html__('Design Notes', 'jazzedge-ai-emails'); ?></h3>
                                <span class="je-prompt-section-hint"><?php echo esc_html__('Optional. Any formatting requests — highlighted callout blocks, bullet list style, emphasis, etc.', 'jazzedge-ai-emails'); ?></span>
                            </div>
                            <textarea id="je-design-notes" class="je-textarea" rows="4" placeholder="<?php echo esc_attr__('e.g. Add a highlighted orange callout block around the Premier members note. Use bullet points for the what\'s included section.', 'jazzedge-ai-emails'); ?>"></textarea>
                        </div>

                    </div>

                    <div class="je-actions-row">
                        <button type="button" id="je-btn-generate" class="je-btn je-btn-primary">
                            <?php echo self::icon_sparkles(); ?>
                            <span><?php echo esc_html__('Generate Email', 'jazzedge-ai-emails'); ?></span>
                        </button>
                        <span class="je-spinner je-spinner-inline" id="je-spinner-generate" aria-hidden="true"></span>
                    </div>
                </div>

                <div id="je-editor-panels" class="je-editor-panels" hidden>
                    <div class="je-panel-grid">
                        <div class="je-subpanel je-card" id="je-subject-panel" data-field="subject">
                            <div class="je-subpanel-header">
                                <h2><?php echo esc_html__('Subject', 'jazzedge-ai-emails'); ?></h2>
                                <span class="je-badge" id="je-subject-revisions">0 <?php echo esc_html__('revisions', 'jazzedge-ai-emails'); ?></span>
                            </div>
                            <input type="text" id="je-subject-input" class="je-input" />
                            <label class="je-label" for="je-subject-feedback"><?php echo esc_html__('Revision', 'jazzedge-ai-emails'); ?></label>
                            <textarea id="je-subject-feedback" class="je-textarea je-textarea-sm" placeholder="<?php echo esc_attr__('Tweak the subject…', 'jazzedge-ai-emails'); ?>" rows="3"></textarea>
                            <div class="je-actions-row">
                                <button type="button" id="je-btn-revise-subject" class="je-btn je-btn-secondary"><?php echo esc_html__('Revise Subject', 'jazzedge-ai-emails'); ?></button>
                                <button type="button" id="je-btn-approve-subject" class="je-btn je-btn-success">
                                    <?php echo self::icon_check(); ?>
                                    <span><?php echo esc_html__('Approve Subject', 'jazzedge-ai-emails'); ?></span>
                                </button>
                            </div>
                            <div class="je-lock-overlay" id="je-subject-lock" hidden><?php echo self::icon_lock(); ?></div>
                        </div>

                        <div class="je-subpanel je-card" id="je-body-panel" data-field="body">
                            <div class="je-subpanel-header">
                                <h2><?php echo esc_html__('Body', 'jazzedge-ai-emails'); ?></h2>
                                <span class="je-badge" id="je-body-revisions">0 <?php echo esc_html__('revisions', 'jazzedge-ai-emails'); ?></span>
                            </div>
                            <div class="je-body-view-toggle">
                                <button type="button" class="je-btn je-btn-ghost je-toggle is-active" data-view="raw"><?php echo esc_html__('Raw', 'jazzedge-ai-emails'); ?></button>
                                <button type="button" class="je-btn je-btn-ghost je-toggle" data-view="preview"><?php echo esc_html__('Preview', 'jazzedge-ai-emails'); ?></button>
                            </div>
                            <textarea id="je-body-raw" class="je-textarea je-textarea-code" readonly rows="14" aria-readonly="true"></textarea>
                            <div id="je-body-preview" class="je-body-preview" hidden></div>
                            <label class="je-label" for="je-body-feedback"><?php echo esc_html__('Revision', 'jazzedge-ai-emails'); ?></label>
                            <textarea id="je-body-feedback" class="je-textarea je-textarea-sm" placeholder="<?php echo esc_attr__('Tweak the body…', 'jazzedge-ai-emails'); ?>" rows="4"></textarea>
                            <div class="je-actions-row">
                                <button type="button" id="je-btn-revise-body" class="je-btn je-btn-secondary"><?php echo esc_html__('Revise Body', 'jazzedge-ai-emails'); ?></button>
                                <button type="button" id="je-btn-approve-body" class="je-btn je-btn-success">
                                    <?php echo self::icon_check(); ?>
                                    <span><?php echo esc_html__('Approve Body', 'jazzedge-ai-emails'); ?></span>
                                </button>
                            </div>
                            <div class="je-lock-overlay" id="je-body-lock" hidden><?php echo self::icon_lock(); ?></div>
                        </div>
                    </div>
                    <div class="je-save-bar" id="je-save-bar" hidden>
                        <button type="button" id="je-btn-save-history" class="je-btn je-btn-primary je-btn-lg">
                            <?php echo self::icon_bookmark(); ?>
                            <span><?php echo esc_html__('Save to History', 'jazzedge-ai-emails'); ?></span>
                        </button>
                    </div>
                </div>
            </div>

            <div id="je-panel-history" class="je-panel" role="tabpanel" data-panel="history" hidden>
                <div class="je-card je-card-pad-none">
                    <div id="je-history-loading" class="je-history-loading" hidden>
                        <span class="je-spinner"></span>
                    </div>
                    <div id="je-history-empty" class="je-empty-state" hidden>
                        <?php echo self::icon_envelope(); ?>
                        <p><?php echo esc_html__('No saved emails yet. Approve a subject and body, then save from the Create tab.', 'jazzedge-ai-emails'); ?></p>
                    </div>
                    <table class="je-history-table" id="je-history-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Date', 'jazzedge-ai-emails'); ?></th>
                                <th><?php echo esc_html__('Subject', 'jazzedge-ai-emails'); ?></th>
                                <th><?php echo esc_html__('Actions', 'jazzedge-ai-emails'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="je-history-tbody"></tbody>
                    </table>
                </div>
            </div>

            <div id="je-panel-settings" class="je-panel" role="tabpanel" data-panel="settings" hidden>
                <div class="je-card">
                    <h2 class="je-card-title"><?php echo esc_html__('AI System Prompt', 'jazzedge-ai-emails'); ?></h2>
                    <label class="je-label" for="je-custom-prompt"><?php echo esc_html__('Custom Email Instructions', 'jazzedge-ai-emails'); ?></label>
                    <textarea id="je-custom-prompt" class="je-textarea" rows="8" placeholder="<?php echo esc_attr__('Add any additional instructions for the AI — your brand voice, products to reference, things to avoid, etc.', 'jazzedge-ai-emails'); ?>"></textarea>
                    <div class="je-actions-row">
                        <button type="button" id="je-btn-save-settings" class="je-btn je-btn-primary"><?php echo esc_html__('Save Settings', 'jazzedge-ai-emails'); ?></button>
                        <span class="je-spinner je-spinner-inline" id="je-spinner-settings"></span>
                    </div>
                    <p class="je-help" id="je-settings-msg" hidden></p>
                </div>

                <div class="je-card">
                    <h2 class="je-card-title"><?php echo esc_html__('AI Connection Test', 'jazzedge-ai-emails'); ?></h2>
                    <button type="button" id="je-btn-test-ai" class="je-btn je-btn-secondary">
                        <?php echo self::icon_bolt(); ?>
                        <span><?php echo esc_html__('Test AI Connection', 'jazzedge-ai-emails'); ?></span>
                    </button>
                    <span class="je-spinner je-spinner-inline" id="je-spinner-test"></span>
                    <p class="je-test-result" id="je-test-result" hidden></p>
                </div>
            </div>

            <div class="je-debug-area" id="je-debug-area">
                <button type="button" class="je-debug-toggle" id="je-debug-toggle" aria-expanded="false">
                    <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" /></svg>
                    <span><?php echo esc_html__('Debug: Last AI Prompt', 'jazzedge-ai-emails'); ?></span>
                    <svg class="je-debug-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div class="je-debug-body" id="je-debug-body" hidden>
                    <p class="je-debug-empty" id="je-debug-empty"><?php echo esc_html__('No prompt sent yet. Generate or revise an email to see the full message array here.', 'jazzedge-ai-emails'); ?></p>
                    <div id="je-debug-messages" class="je-debug-messages" hidden></div>
                    <div class="je-debug-actions" id="je-debug-actions" hidden>
                        <button type="button" class="je-btn je-btn-ghost" id="je-debug-copy">
                            <svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" /></svg>
                            <?php echo esc_html__('Copy JSON', 'jazzedge-ai-emails'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private static function icon_sparkles() {
        return '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.089 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.089 3.09zM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.898 20.198 15.75 21l-1.148-.802a3.75 3.75 0 00-2.633-1.038l-1.147-.062 1.147-.062a3.75 3.75 0 002.633-1.038L15.75 15l1.148.802a3.75 3.75 0 002.633 1.038l1.147.062-1.147.062a3.75 3.75 0 00-2.633 1.038z" /></svg>';
    }

    private static function icon_check() {
        return '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    }

    private static function icon_bookmark() {
        return '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" /></svg>';
    }

    private static function icon_bolt() {
        return '<svg class="je-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>';
    }

    private static function icon_envelope() {
        return '<svg class="je-icon je-icon-xl" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>';
    }

    private static function icon_lock() {
        return '<svg class="je-icon je-icon-lock" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>';
    }
}
