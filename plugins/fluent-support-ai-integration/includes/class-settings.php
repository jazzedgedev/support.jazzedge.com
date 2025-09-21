<?php
/**
 * Settings class for Fluent Support AI Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class FluentSupportAI_Settings {
    
    /**
     * Render settings page
     */
    public function render() {
        $api_key = get_option('fluent_support_ai_openai_key', '');
        $business_name = get_option('fluent_support_ai_business_name', '');
        $business_website = get_option('fluent_support_ai_business_website', '');
        $business_industry = get_option('fluent_support_ai_business_industry', '');
        $business_description = get_option('fluent_support_ai_business_description', '');
        $support_tone = get_option('fluent_support_ai_support_tone', 'professional');
        $support_style = get_option('fluent_support_ai_support_style', '');
        
        $prompt_manager = new FluentSupportAI_Prompt_Manager();
        $prompts = $prompt_manager->get_prompts();
        
        ?>
        <style>
            .fs-ai-settings {
                max-width: 1200px;
                margin: 20px 0;
            }
            .fs-ai-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                border-radius: 12px;
                margin-bottom: 30px;
                text-align: center;
            }
            .fs-ai-header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 600;
            }
            .fs-ai-header p {
                margin: 10px 0 0 0;
                opacity: 0.9;
                font-size: 16px;
            }
            .fs-ai-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-bottom: 30px;
            }
            .fs-ai-card {
                background: white;
                border: 1px solid #e1e5e9;
                border-radius: 12px;
                padding: 25px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                transition: box-shadow 0.3s ease;
            }
            .fs-ai-card:hover {
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            .fs-ai-card h2 {
                margin: 0 0 20px 0;
                color: #2c3e50;
                font-size: 20px;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .fs-ai-card .icon {
                font-size: 24px;
            }
            .fs-ai-form-group {
                margin-bottom: 20px;
            }
            .fs-ai-form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #2c3e50;
            }
            .fs-ai-form-group input,
            .fs-ai-form-group select,
            .fs-ai-form-group textarea {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 8px;
                font-size: 14px;
                transition: border-color 0.3s ease;
            }
            .fs-ai-form-group input:focus,
            .fs-ai-form-group select:focus,
            .fs-ai-form-group textarea:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .fs-ai-form-group .description {
                margin-top: 5px;
                font-size: 13px;
                color: #6c757d;
            }
            .fs-ai-button {
                background: #28a745;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                transition: background 0.3s ease;
                margin-right: 10px;
            }
            .fs-ai-button:hover {
                background: #218838;
            }
            .fs-ai-button-secondary {
                background: #6c757d;
            }
            .fs-ai-button-secondary:hover {
                background: #5a6268;
            }
            .fs-ai-button-test {
                background: #007bff;
            }
            .fs-ai-button-test:hover {
                background: #0056b3;
            }
            .fs-ai-full-width {
                grid-column: 1 / -1;
            }
            .fs-ai-prompt-item {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 15px;
            }
            .fs-ai-prompt-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            .fs-ai-prompt-name {
                font-weight: 600;
                color: #2c3e50;
                margin: 0;
            }
            .fs-ai-prompt-actions {
                display: flex;
                gap: 10px;
            }
            .fs-ai-prompt-content {
                background: white;
                padding: 15px;
                border-radius: 6px;
                border: 1px solid #dee2e6;
                font-family: monospace;
                font-size: 13px;
                white-space: pre-wrap;
                max-height: 150px;
                overflow-y: auto;
            }
            .fs-ai-branding {
                text-align: center;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
                margin-top: 20px;
            }
            .fs-ai-branding img {
                max-height: 50px;
                margin: 10px 0;
            }
            @media (max-width: 768px) {
                .fs-ai-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        
        <div class="wrap">
            <div class="fs-ai-settings">
                <div class="fs-ai-header">
                    <h1>🤖 Fluent Support AI Integration</h1>
                    <p>Configure your AI-powered customer support system</p>
                </div>
                
                <div class="fs-ai-grid">
                    <!-- API Configuration -->
                    <div class="fs-ai-card">
                        <h2><span class="icon">🔑</span> API Configuration</h2>
                        <form id="api-settings-form" method="post" action="">
                            <?php wp_nonce_field('fluent_support_ai_save_settings', 'fluent_support_ai_settings_nonce'); ?>
                            <input type="hidden" name="action" value="save_api_key">
                            
                            <div class="fs-ai-form-group">
                                <label for="openai_api_key">OpenAI API Key</label>
                                <input type="password" id="openai_api_key" name="openai_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       placeholder="sk-...">
                                <div class="description">
                                    Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" name="save_openai_key" class="fs-ai-button">
                                    💾 Save API Key
                                </button>
                                <button type="submit" name="test_openai_key" class="fs-ai-button fs-ai-button-test">
                                    🧪 Test API Key
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Business Context -->
                    <div class="fs-ai-card">
                        <h2><span class="icon">🏢</span> Business Context</h2>
                        <form id="business-settings-form" method="post" action="">
                            <?php wp_nonce_field('fluent_support_ai_save_settings', 'fluent_support_ai_settings_nonce'); ?>
                            <input type="hidden" name="action" value="save_business_settings">
                            
                            <div class="fs-ai-form-group">
                                <label for="business_name">Business Name</label>
                                <input type="text" id="business_name" name="business_name" 
                                       value="<?php echo esc_attr($business_name); ?>" 
                                       placeholder="Your Company Name">
                                <div class="description">The name of your business for personalized responses</div>
                            </div>
                            
                            <div class="fs-ai-form-group">
                                <label for="business_website">Website</label>
                                <input type="url" id="business_website" name="business_website" 
                                       value="<?php echo esc_attr($business_website); ?>" 
                                       placeholder="https://yourcompany.com">
                                <div class="description">Your business website URL</div>
                            </div>
                            
                            <div class="fs-ai-form-group">
                                <label for="business_industry">Industry</label>
                                <select id="business_industry" name="business_industry">
                                    <option value="">Select Industry</option>
                                    <option value="technology" <?php selected($business_industry, 'technology'); ?>>Technology</option>
                                    <option value="ecommerce" <?php selected($business_industry, 'ecommerce'); ?>>E-commerce</option>
                                    <option value="saas" <?php selected($business_industry, 'saas'); ?>>SaaS</option>
                                    <option value="education" <?php selected($business_industry, 'education'); ?>>Education</option>
                                    <option value="healthcare" <?php selected($business_industry, 'healthcare'); ?>>Healthcare</option>
                                    <option value="finance" <?php selected($business_industry, 'finance'); ?>>Finance</option>
                                    <option value="consulting" <?php selected($business_industry, 'consulting'); ?>>Consulting</option>
                                    <option value="other" <?php selected($business_industry, 'other'); ?>>Other</option>
                                </select>
                                <div class="description">Your business industry for context-aware responses</div>
                            </div>
                            
                            <div class="fs-ai-form-group">
                                <label for="business_description">Business Description</label>
                                <textarea id="business_description" name="business_description" 
                                          rows="4" placeholder="Describe your business, products, and services..."><?php echo esc_textarea($business_description); ?></textarea>
                                <div class="description">Additional context about your business for better AI responses</div>
                            </div>
                            
                            <button type="submit" class="fs-ai-button">
                                💾 Save Business Settings
                            </button>
                        </form>
                    </div>
                    
                    <!-- Support Style -->
                    <div class="fs-ai-card">
                        <h2><span class="icon">🎨</span> Support Style</h2>
                        <form id="style-settings-form" method="post" action="">
                            <?php wp_nonce_field('fluent_support_ai_save_settings', 'fluent_support_ai_settings_nonce'); ?>
                            <input type="hidden" name="action" value="save_style_settings">
                            
                            <div class="fs-ai-form-group">
                                <label for="support_tone">Response Tone</label>
                                <select id="support_tone" name="support_tone">
                                    <option value="professional" <?php selected($support_tone, 'professional'); ?>>Professional</option>
                                    <option value="friendly" <?php selected($support_tone, 'friendly'); ?>>Friendly</option>
                                    <option value="casual" <?php selected($support_tone, 'casual'); ?>>Casual</option>
                                    <option value="technical" <?php selected($support_tone, 'technical'); ?>>Technical</option>
                                    <option value="empathetic" <?php selected($support_tone, 'empathetic'); ?>>Empathetic</option>
                                </select>
                                <div class="description">The tone for AI-generated responses</div>
                            </div>
                            
                            <div class="fs-ai-form-group">
                                <label for="support_style">Custom Style Guidelines</label>
                                <textarea id="support_style" name="support_style" 
                                          rows="3" placeholder="e.g., Always use first names, mention our 24/7 support, include relevant links..."><?php echo esc_textarea($support_style); ?></textarea>
                                <div class="description">Specific guidelines for how responses should be formatted</div>
                            </div>
                            
                            <button type="submit" class="fs-ai-button">
                                💾 Save Style Settings
                            </button>
                        </form>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="fs-ai-card">
                        <h2><span class="icon">📊</span> Quick Stats</h2>
                        <div style="text-align: center; padding: 20px 0;">
                            <div style="font-size: 32px; font-weight: bold; color: #28a745; margin-bottom: 10px;">
                                <?php echo count($prompts); ?>
                            </div>
                            <div style="color: #6c757d; margin-bottom: 20px;">Active Prompts</div>
                            
                            <div style="font-size: 24px; font-weight: bold; color: #007bff; margin-bottom: 10px;">
                                <?php echo !empty($api_key) ? '✅' : '❌'; ?>
                            </div>
                            <div style="color: #6c757d;">API Key Status</div>
                        </div>
                    </div>
                </div>
                
                <!-- Prompt Management -->
                <div class="fs-ai-card fs-ai-full-width">
                    <h2><span class="icon">📝</span> AI Prompts Management</h2>
                    
                    <!-- Add New Prompt Form -->
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                        <h3 style="margin: 0 0 20px 0; color: #2c3e50;">➕ Add New Prompt</h3>
                        <form id="add-prompt-form">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <div class="fs-ai-form-group">
                                    <label for="prompt_name">Prompt Name</label>
                                    <input type="text" id="prompt_name" name="prompt_name" 
                                           placeholder="e.g., Friendly Response" required>
                                    <div class="description">A descriptive name for this prompt</div>
                                </div>
                                <div class="fs-ai-form-group">
                                    <label for="prompt_description">Description</label>
                                    <input type="text" id="prompt_description" name="prompt_description" 
                                           placeholder="e.g., Use for general customer inquiries">
                                    <div class="description">Brief description of when to use this prompt</div>
                                </div>
                            </div>
                            
                            <div class="fs-ai-form-group">
                                <label for="prompt_content">Prompt Content</label>
                                <div style="position: relative;">
                                    <textarea id="prompt_content" name="prompt_content" 
                                              rows="4" placeholder="Write a friendly response to this customer inquiry: {ticket_content}" required></textarea>
                                    <button type="button" class="fs-ai-button" style="position: absolute; top: 5px; right: 5px; padding: 5px 10px; font-size: 12px;" onclick="insertTicketContent()">
                                        📝 Insert {ticket_content}
                                    </button>
                                </div>
                                <div class="description">
                                    The AI prompt template. Use {ticket_content} to include the ticket content, {business_name} for your business name, and {agent_name} for the agent's name.
                                </div>
                            </div>
                            
                            <button type="submit" class="fs-ai-button">
                                ➕ Add Prompt
                            </button>
                        </form>
                    </div>
                    
                    <!-- Existing Prompts -->
                    <div>
                        <h3 style="margin: 0 0 20px 0; color: #2c3e50;">📋 Existing Prompts</h3>
                        <?php if (empty($prompts)): ?>
                            <div style="text-align: center; padding: 40px; color: #6c757d; background: #f8f9fa; border-radius: 8px;">
                                <div style="font-size: 48px; margin-bottom: 15px;">📝</div>
                                <p style="margin: 0; font-size: 16px;">No prompts found. Add your first prompt above to get started!</p>
                            </div>
                        <?php else: ?>
                            <div style="display: grid; gap: 15px;">
                                <?php foreach ($prompts as $prompt): ?>
                                    <div class="fs-ai-prompt-item">
                                        <div class="fs-ai-prompt-header">
                                            <h4 class="fs-ai-prompt-name"><?php echo esc_html($prompt['name']); ?></h4>
                                            <div class="fs-ai-prompt-actions">
                                                <button type="button" class="fs-ai-button fs-ai-button-secondary edit-prompt" 
                                                        data-prompt-id="<?php echo esc_attr($prompt['id']); ?>">
                                                    ✏️ Edit
                                                </button>
                                                <button type="button" class="fs-ai-button" style="background: #dc3545;" onclick="deletePrompt(<?php echo esc_attr($prompt['id']); ?>)">
                                                    🗑️ Delete
                                                </button>
                                            </div>
                                        </div>
                                        <?php if (!empty($prompt['description'])): ?>
                                            <p style="margin: 10px 0; color: #6c757d; font-style: italic;">
                                                <?php echo esc_html($prompt['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="fs-ai-prompt-content"><?php echo esc_html($prompt['prompt']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Katahdin AI Branding -->
                <div class="fs-ai-branding">
                    <p style="margin: 0 0 15px 0; color: #6c757d;">
                        This AI integration is powered by Katahdin AI, providing advanced AI solutions for WordPress applications.
                    </p>
                    <a href="https://katahdin.ai/" target="_blank" rel="noopener">
                        <img src="https://katahdin.ai/wp-content/uploads/2025/09/cropped-Katahdin-AI-Logo-dark-with-tag.png" 
                             alt="Katahdin AI" 
                             style="max-height: 50px; margin: 10px 0;">
                    </a>
                    <p style="margin: 10px 0 0 0;">
                        <a href="https://katahdin.ai/" target="_blank" rel="noopener" style="color: #667eea; text-decoration: none;">
                            Visit Katahdin AI →
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <script>
        // Handle form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // API Settings Form
            document.getElementById('api-settings-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                // Check if it's a test button click
                const isTest = e.submitter && e.submitter.name === 'test_openai_key';
                
                if (isTest) {
                    // For test, we need to submit to the current page
                    formData.append('test_openai_key', '1');
                    formData.append('fluent_support_ai_settings_nonce', '<?php echo wp_create_nonce('fluent_support_ai_save_settings'); ?>');
                    
                    // Submit to current page
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.includes('OpenAI API key is valid')) {
                            alert('✅ API key is valid and working!');
                        } else if (data.includes('API key test failed')) {
                            alert('❌ API key test failed. Please check your key.');
                        } else {
                            alert('❌ Error testing API key. Please try again.');
                        }
                    })
                    .catch(error => {
                        alert('❌ Network error. Please try again.');
                    });
                } else {
                    // For save, submit normally
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.includes('success')) {
                            alert('✅ API key saved successfully!');
                            location.reload();
                        } else {
                            alert('❌ Error saving API key. Please try again.');
                        }
                    })
                    .catch(error => {
                        alert('❌ Network error. Please try again.');
                    });
                }
            });
            
            // Business Settings Form
            document.getElementById('business-settings-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('fluent_support_ai_settings_nonce', '<?php echo wp_create_nonce('fluent_support_ai_save_settings'); ?>');
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('Business settings saved')) {
                        alert('✅ Business settings saved successfully!');
                        location.reload();
                    } else {
                        alert('❌ Error saving settings. Please try again.');
                    }
                })
                .catch(error => {
                    alert('❌ Network error. Please try again.');
                });
            });
            
            // Style Settings Form
            document.getElementById('style-settings-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('fluent_support_ai_settings_nonce', '<?php echo wp_create_nonce('fluent_support_ai_save_settings'); ?>');
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('Style settings saved')) {
                        alert('✅ Style settings saved successfully!');
                        location.reload();
                    } else {
                        alert('❌ Error saving settings. Please try again.');
                    }
                })
                .catch(error => {
                    alert('❌ Network error. Please try again.');
                });
            });
            
            // Add Prompt Form
            document.getElementById('add-prompt-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'fluent_support_ai_save_prompt');
                formData.append('nonce', '<?php echo wp_create_nonce('fluent_support_ai_nonce'); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Prompt added successfully!');
                        location.reload();
                    } else {
                        alert('❌ Error adding prompt: ' + (data.data || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('❌ Network error. Please try again.');
                });
            });
        });
        
        function deletePrompt(promptId) {
            if (confirm('Are you sure you want to delete this prompt?')) {
                const formData = new FormData();
                formData.append('action', 'fluent_support_ai_delete_prompt');
                formData.append('prompt_id', promptId);
                formData.append('nonce', '<?php echo wp_create_nonce('fluent_support_ai_nonce'); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Prompt deleted successfully!');
                        location.reload();
                    } else {
                        alert('❌ Error deleting prompt: ' + (data.data || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('❌ Network error. Please try again.');
                });
            }
        }
        
        function insertTicketContent() {
            const textarea = document.getElementById('prompt_content');
            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(textarea.selectionEnd);
            
            textarea.value = textBefore + '{ticket_content}' + textAfter;
            textarea.focus();
            textarea.setSelectionRange(cursorPos + '{ticket_content}'.length, cursorPos + '{ticket_content}'.length);
        }
        </script>
        <?php
    }
}
