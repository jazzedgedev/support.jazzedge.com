/**
 * Academy AI Assistant - Frontend JavaScript
 * 
 * Handles chat interface, API calls, and UI updates
 */

(function($) {
    'use strict';
    
    var AAAAssistant = {
        // Configuration
        config: {
            restUrl: '',
            nonce: '',
            currentUserId: 0,
            sessionId: 0,
            location: 'main'
        },
        
        // DOM elements
        elements: {
            container: null,
            messagesContainer: null,
            messageInput: null,
            sendButton: null,
            typingIndicator: null,
            errorMessage: null,
            sidebar: null,
            sessionsList: null,
            newChatBtn: null
        },
        
        /**
         * Initialize
         */
        init: function() {
            console.log('Academy AI Assistant: Initializing...');
            
            // Get config from localized script
            if (typeof aaaAssistant !== 'undefined') {
                this.config.restUrl = aaaAssistant.restUrl || '';
                this.config.nonce = aaaAssistant.nonce || '';
                this.config.currentUserId = aaaAssistant.currentUserId || 0;
                this.config.location = aaaAssistant.location || 'main';
                this.config.userAvatar = aaaAssistant.userAvatar || '';
                this.config.timezone = aaaAssistant.timezone || null;
                this.config.gmtOffset = aaaAssistant.gmtOffset || 0;
                console.log('Academy AI Assistant: Config loaded', {
                    restUrl: this.config.restUrl,
                    hasNonce: !!this.config.nonce,
                    userId: this.config.currentUserId,
                    location: this.config.location
                });
            } else {
                console.error('Academy AI Assistant: aaaAssistant object not found!');
                return;
            }
            
            // Cache DOM elements
            this.elements.container = $('#aaa-chat-container');
            if (this.elements.container.length === 0) {
                console.log('Academy AI Assistant: Chat container not found on page');
                return; // Shortcode not on this page
            }
            
            this.elements.messagesContainer = $('#aaa-chat-messages');
            this.elements.messageInput = $('#aaa-message-input');
            this.elements.sendButton = $('#aaa-send-button');
            this.elements.errorMessage = $('#aaa-error-message');
            this.typingIndicatorId = 'aaa-typing-indicator-message';
            this.elements.sidebar = $('#aaa-chat-sidebar');
            this.elements.sessionsList = $('#aaa-sessions-list');
            this.elements.newChatBtn = $('#aaa-new-chat-btn');
            
            // Check if all required elements found
            var missingElements = [];
            if (this.elements.messagesContainer.length === 0) missingElements.push('messagesContainer');
            if (this.elements.messageInput.length === 0) missingElements.push('messageInput');
            if (this.elements.sendButton.length === 0) missingElements.push('sendButton');
            
            if (missingElements.length > 0) {
                console.error('Academy AI Assistant: Missing DOM elements:', missingElements);
                return;
            }
            
            console.log('Academy AI Assistant: Initialized successfully');
            
            // Bind events
            this.bindEvents();
            
            // Load sessions list for sidebar
            this.loadSessionsList();
            
            // Load conversation history if session exists
            this.loadConversationHistory();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;
            
            // Send button click
            this.elements.sendButton.on('click', function() {
                self.sendMessage();
            });
            
            // Enter key to send (Shift+Enter for new line)
            this.elements.messageInput.on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    // Don't send if we're currently sending from a chip
                    if (self._sendingFromChip) {
                        e.preventDefault();
                        return false;
                    }
                    e.preventDefault();
                    self.sendMessage();
                }
            });
            
            // Auto-resize textarea
            this.elements.messageInput.on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            
            // Question starter chips - open modal
            $(document).on('click', '.aaa-starter-chip', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $chip = $(this);
                var template = $chip.data('template');
                var fieldsData = $chip.data('fields');
                var chipId = $chip.data('chip-id');
                
                if (!template) {
                    console.error('Academy AI Assistant: No template found for chip');
                    return;
                }
                
                // Open modal with form
                self.openChipModal(template, fieldsData || [], chipId);
            });
            
            // Close modal handlers
            $(document).on('click', '#aaa-chip-modal-close, #aaa-chip-modal-cancel, .aaa-chip-modal-overlay', function() {
                self.closeChipModal();
            });
            
            // Submit chip form
            $(document).on('submit', '#aaa-chip-form', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                self.submitChipForm();
                return false;
            });
            
            // Also handle submit button click directly (in case form submit doesn't fire)
            $(document).on('click', '#aaa-chip-modal-submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                // Trigger form submit (which will be caught by the handler above)
                $('#aaa-chip-form').submit();
                return false;
            });
            
            // Link modal handlers
            $(document).on('click', '#aaa-link-modal-close, .aaa-link-modal-overlay', function() {
                self.closeLinkModal();
            });
            
            // Visit button handler is set in openLinkModal() to ensure it has the correct URL
            
            // Close modal button (for modal context)
            $(document).on('click', '#aaa-close-modal-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close the parent modal if it exists
                var $modal = $('#jph-ai-assistant-modal');
                if ($modal.length > 0) {
                    $modal.fadeOut(300);
                    $('body').css('overflow', '');
                }
            });
            
            $(document).on('click', '#aaa-link-modal-favorite', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.handleAddFavorite();
            });
            
            // New chat button - prevent double clicks
            this.elements.newChatBtn.on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                
                // Prevent multiple clicks
                if ($btn.hasClass('creating')) {
                    return false;
                }
                
                $btn.addClass('creating');
                self.createNewChat();
                
                // Remove creating class after a delay
                setTimeout(function() {
                    $btn.removeClass('creating');
                }, 2000);
                
                return false;
            });
            
            // Session item click
            $(document).on('click', '.aaa-session-item:not(.aaa-renaming)', function() {
                var sessionId = $(this).data('session-id');
                if (sessionId) {
                    self.loadSession(sessionId);
                }
            });
            
            // Rename button
            $(document).on('click', '.aaa-rename-btn', function(e) {
                e.stopPropagation();
                var sessionId = $(this).closest('.aaa-session-item').data('session-id');
                self.startRenamingSession(sessionId);
            });
            
            // Delete button
            $(document).on('click', '.aaa-delete-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                var $btn = $(this);
                var $item = $btn.closest('.aaa-session-item');
                var sessionId = $item.data('session-id');
                
                // Prevent multiple clicks
                if ($btn.hasClass('deleting')) {
                    return false;
                }
                
                if (confirm('Are you sure you want to delete this chat? This cannot be undone.')) {
                    $btn.addClass('deleting');
                    self.deleteSession(sessionId);
                }
                
                return false;
            });
            
            // Rename input handlers
            $(document).on('keydown', '.aaa-session-rename-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var sessionId = $(this).data('session-id');
                    var newName = $(this).val().trim();
                    self.finishRenamingSession(sessionId, newName);
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    self.cancelRenamingSession();
                }
            });
            
            $(document).on('blur', '.aaa-session-rename-input', function() {
                var sessionId = $(this).data('session-id');
                var newName = $(this).val().trim();
                self.finishRenamingSession(sessionId, newName);
            });
            
            // Load sessions list
            this.loadSessionsList();
            this.loadTokenUsage();
            
            // Attach favorite handlers
            this.attachLessonLinkHandlers();
            
            // Download transcript button
            $('#aaa-download-transcript-btn').on('click', function(e) {
                e.preventDefault();
                self.downloadTranscript();
            });
        },
        
        /**
         * Send message
         */
        sendMessage: function(message, chipId) {
            // Prevent duplicate sends
            if (this._sendingMessage) {
                console.log('Academy AI Assistant: Message already sending, ignoring duplicate');
                return;
            }
            
            // If message is not provided, get it from input
            if (!message) {
                message = this.elements.messageInput.val().trim();
            }
            
            if (!message) {
                console.log('Academy AI Assistant: Empty message, not sending');
                return;
            }
            
            // Mark as sending
            this._sendingMessage = true;
            
            console.log('Academy AI Assistant: Sending message:', message, chipId ? '(chip: ' + chipId + ')' : '');
            
            // Disable input while sending
            this.setInputEnabled(false);
            this.hideError();
            
            // Add user message to UI immediately
            this.addMessage('user', message);
            
            // Show typing indicator AFTER user message is added
            this.showTyping();
            
            // Keep question starters visible - users can use them anytime
            
            // Clear input
            this.elements.messageInput.val('');
            this.elements.messageInput.css('height', 'auto');
            
            // Make API call
            var self = this;
            this.makeChatRequest(message, chipId).always(function() {
                // Reset sending flag after request completes (success or error)
                self._sendingMessage = false;
            });
        },
        
        /**
         * Make chat API request
         */
        makeChatRequest: function(message, chipId) {
            var self = this;
            var data = {
                message: message,
                session_id: this.config.sessionId,
                location: this.config.location,
                use_context: true,
                use_embeddings: true
            };
            
            // Add chip_id if provided
            if (chipId) {
                data.chip_id = chipId;
            }
            
            // Ensure restUrl doesn't have trailing slash
            var restUrl = this.config.restUrl;
            if (restUrl && restUrl.endsWith('/')) {
                restUrl = restUrl.slice(0, -1);
            }
            var url = restUrl + '/chat';
            console.log('Academy AI Assistant: Making API request to:', url);
            console.log('Academy AI Assistant: Request data:', data);
            
            if (!this.config.restUrl) {
                console.error('Academy AI Assistant: REST URL is empty!');
                this.handleChatError(null, 'error', 'REST URL not configured');
                // Return a rejected promise so .always() still works
                return $.Deferred().reject().promise();
            }
            
            if (!this.config.nonce) {
                console.error('Academy AI Assistant: Nonce is empty!');
                this.handleChatError(null, 'error', 'Security nonce not configured');
                // Return a rejected promise so .always() still works
                return $.Deferred().reject().promise();
            }
            
            return $.ajax({
                url: url,
                method: 'POST',
                xhrFields: {
                    withCredentials: true  // Include cookies for authentication
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    console.log('Academy AI Assistant: API response received:', response);
                    self.handleChatResponse(response);
                },
                error: function(xhr, status, error) {
                    console.error('Academy AI Assistant: API error:', {
                        status: status,
                        error: error,
                        response: xhr.responseJSON,
                        statusCode: xhr.status
                    });
                    self.handleChatError(xhr, status, error);
                }
            });
        },
        
        /**
         * Handle successful chat response
         */
        handleChatResponse: function(response) {
            this.hideTyping();
            this.setInputEnabled(true);
            
            // Re-attach lesson link handlers for new messages
            this.attachLessonLinkHandlers();
            
            // Update session ID - always update to ensure we have the correct session
            if (response.session_id) {
                this.config.sessionId = response.session_id;
                console.log('Academy AI Assistant: Session ID updated to:', this.config.sessionId);
            }
            
            // Update token usage if provided
            if (response.usage) {
                this.updateTokenUsageDisplay(response.usage);
            }
            
            // Add AI response to UI
            if (response.response) {
                this.addMessage('assistant', response.response);
            }
            
            // Scroll to bottom
            this.scrollToBottom();
            
            // Refresh sessions list to update message count and date
            // Use a small delay to ensure database has been updated
            var self = this;
            setTimeout(function() {
                self.loadSessionsList();
            }, 300);
        },
        
        /**
         * Handle chat error
         */
        handleChatError: function(xhr, status, error) {
            this.hideTyping();
            this.setInputEnabled(true);
            
            var errorMsg = 'An error occurred. Please try again.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.status === 403) {
                errorMsg = 'Access denied. Please refresh the page.';
            } else if (xhr.status === 429) {
                errorMsg = 'Rate limit exceeded. Please wait a moment.';
            } else if (xhr.status === 503) {
                errorMsg = 'AI service is temporarily unavailable. Please try again later.';
            }
            
            this.showError(errorMsg);
            
            // Remove the user message that failed
            this.elements.messagesContainer.find('.aaa-message.user:last').remove();
        },
        
        /**
         * Add message to chat
         */
        addMessage: function(role, content) {
            var messageClass = role === 'user' ? 'user' : 'assistant';
            
            var messageHtml = '<div class="aaa-message ' + messageClass + '">';
            
            if (role === 'assistant') {
                messageHtml += '<div class="aaa-avatar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aaa-sparkles-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg></div>';
            }
            
            messageHtml += '<div class="aaa-message-content">';
            messageHtml += '<div class="aaa-message-text">' + this.formatMessage(content) + '</div>';
            messageHtml += '<div class="aaa-message-time">' + this.getCurrentTime() + '</div>';
            messageHtml += '</div>';
            
            if (role === 'user') {
                // Use gravatar if available, otherwise fall back to emoji
                var userAvatar = this.config.userAvatar || '';
                if (userAvatar) {
                    messageHtml += '<div class="aaa-avatar user-avatar"><img src="' + this.escapeHtml(userAvatar) + '" alt="User" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" /></div>';
                } else {
                    messageHtml += '<div class="aaa-avatar user-avatar">👤</div>';
                }
            }
            
            messageHtml += '</div>';
            
            this.elements.messagesContainer.append(messageHtml);
            
            // POST-PROCESS: Find and convert any jazzedge.academy links that weren't caught by formatMessage
            // This handles cases where HTML is already in the DOM
            // Note: /course/ links are collections, /collection/ links are collections, /lesson/ links are lessons
            var $message = this.elements.messagesContainer.find('.aaa-message').last();
            $message.find('a[href*="jazzedge.academy/lesson"], a[href*="jazzedge.academy/collection"], a[href*="jazzedge.academy/course"]').each(function() {
                var $link = $(this);
                var url = $link.attr('href');
                var title = $link.text().trim();
                
                // Determine category: /course/ and /collection/ are collections, /lesson/ are lessons
                var isCollection = url.indexOf('/collection/') !== -1 || url.indexOf('/course/') !== -1;
                var category = isCollection ? 'collection' : 'lesson';
                
                console.log('Academy AI Assistant: Post-processing link found in DOM', { url: url, title: title, category: category });
                
                // Replace the link
                $link.attr('href', '#')
                     .addClass('aaa-lesson-link')
                     .attr('data-url', url)
                     .attr('data-title', title)
                     .attr('data-category', category)
                     .css('cursor', 'pointer')
                     .removeAttr('target');
            });
            
            // Re-attach lesson link handlers for newly added messages
            this.attachLessonLinkHandlers();
            
            this.scrollToBottom();
        },
        
        /**
         * Format message content (basic markdown-like formatting)
         */
        formatMessage: function(content) {
            var self = this;
            
            // Check if content already contains HTML (like <a> tags)
            var hasHtml = /<[a-z][\s\S]*>/i.test(content);
            
            if (hasHtml) {
                // Content already has HTML - process HTML links FIRST before any escaping
                console.log('Academy AI Assistant: Content contains HTML, processing HTML links first');
                
                // Convert existing HTML links to jazzedge.academy lessons/collections
                // More robust regex that handles any attribute order
                // NOTE: /course/ links are collections, /collection/ links are collections, /lesson/ links are lessons
                content = content.replace(/<a\s+[^>]*href=["'](https?:\/\/[^"']+jazzedge\.academy\/(?:lesson|collection|course)\/[^"']+)["'][^>]*>([^<]+)<\/a>/gi, function(match, url, title) {
                    // Determine category: /course/ and /collection/ are collections, /lesson/ are lessons
                    var isCollection = url.indexOf('/collection/') !== -1 || url.indexOf('/course/') !== -1;
                    var category = isCollection ? 'collection' : 'lesson';
                    var escapedUrl = self.escapeHtml(url);
                    var escapedTitle = self.escapeHtml(title.trim());
                // Use # for href to prevent navigation, store real URL in data attribute
                var link = '<a href="#" class="aaa-lesson-link" data-url="' + escapedUrl + '" data-title="' + escapedTitle + '" data-category="' + self.escapeHtml(category) + '" style="cursor: pointer;">' + escapedTitle + '</a>';
                    console.log('Academy AI Assistant: Converted existing HTML link', { 
                        url: url, 
                        title: escapedTitle, 
                        category: category,
                        originalMatch: match.substring(0, 80)
                    });
                    return link;
                });
                
                // Don't escape HTML if it's already HTML - just return it
                // But we still need to process markdown if there's any mixed content
                // Convert markdown links [text](url) to HTML links (in case of mixed content)
                content = content.replace(/\[([^\]]+)\]\((https?:\/\/[^\)]+jazzedge\.academy\/(?:lesson|collection|course)\/[^\)]+)\)/g, function(match, title, url) {
                    // Determine category: /course/ and /collection/ are collections, /lesson/ are lessons
                    var isCollection = url.indexOf('/collection/') !== -1 || url.indexOf('/course/') !== -1;
                    var category = isCollection ? 'collection' : 'lesson';
                    var escapedUrl = self.escapeHtml(url);
                    var escapedTitle = self.escapeHtml(title);
                    var link = '<a href="#" class="aaa-lesson-link" data-url="' + escapedUrl + '" data-title="' + escapedTitle + '" data-category="' + self.escapeHtml(category) + '" style="cursor: pointer;">' + escapedTitle + '</a>';
                    console.log('Academy AI Assistant: Created lesson link from markdown (mixed content)', { 
                        url: url, 
                        title: title, 
                        category: category
                    });
                    return link;
                });
                
                // Convert other markdown links (non-jazzedge.academy) in mixed content
                content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
                
                // Process markdown headers even in HTML content
                content = content.replace(/^######\s+(.+)$/gm, '<h6>$1</h6>');
                content = content.replace(/^#####\s+(.+)$/gm, '<h5>$1</h5>');
                content = content.replace(/^####\s+(.+)$/gm, '<h4>$1</h4>');
                content = content.replace(/^###\s+(.+)$/gm, '<h3>$1</h3>');
                content = content.replace(/^##\s+(.+)$/gm, '<h2>$1</h2>');
                content = content.replace(/^#\s+(.+)$/gm, '<h1>$1</h1>');
            } else {
                // Content is plain text/markdown - escape HTML first
                content = $('<div>').text(content).html();
                
                // Convert markdown headers (process from h6 to h1 to avoid conflicts)
                content = content.replace(/^######\s+(.+)$/gm, '<h6>$1</h6>');
                content = content.replace(/^#####\s+(.+)$/gm, '<h5>$1</h5>');
                content = content.replace(/^####\s+(.+)$/gm, '<h4>$1</h4>');
                content = content.replace(/^###\s+(.+)$/gm, '<h3>$1</h3>');
                content = content.replace(/^##\s+(.+)$/gm, '<h2>$1</h2>');
                content = content.replace(/^#\s+(.+)$/gm, '<h1>$1</h1>');
                
                // Convert markdown links [text](url) to HTML links
                // NOTE: /course/ links are collections
                content = content.replace(/\[([^\]]+)\]\((https?:\/\/[^\)]+jazzedge\.academy\/(?:lesson|collection|course)\/[^\)]+)\)/g, function(match, title, url) {
                    // Determine category: /course/ and /collection/ are collections, /lesson/ are lessons
                    var isCollection = url.indexOf('/collection/') !== -1 || url.indexOf('/course/') !== -1;
                    var category = isCollection ? 'collection' : 'lesson';
                    var escapedUrl = self.escapeHtml(url);
                    var escapedTitle = self.escapeHtml(title);
                    var link = '<a href="#" class="aaa-lesson-link" data-url="' + escapedUrl + '" data-title="' + escapedTitle + '" data-category="' + self.escapeHtml(category) + '" style="cursor: pointer;">' + escapedTitle + '</a>';
                    console.log('Academy AI Assistant: Created lesson link from markdown', { 
                        url: url, 
                        title: title, 
                        category: category
                    });
                    return link;
                });
                
                // Convert other markdown links (non-jazzedge.academy)
                content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
            }
            
            // Convert line breaks, but remove any that come immediately after closing header tags
            content = content.replace(/\n/g, '<br>');
            content = content.replace(/(<\/h[1-6]>)<br>/gi, '$1');
            
            // Convert **bold**
            content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Convert *italic*
            content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
            
            return content;
        },
        
        /**
         * Get current time string
         */
        getCurrentTime: function() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            return hours + ':' + minutes + ' ' + ampm;
        },
        
        /**
         * Load conversation history (optimized - single API call)
         */
        loadConversationHistory: function() {
            var self = this;
            
            // Show loading state
            this.showLoadingState();
            
            console.log('Academy AI Assistant: Loading conversation history...');
            console.log('Academy AI Assistant: REST URL:', this.config.restUrl);
            console.log('Academy AI Assistant: Nonce:', this.config.nonce ? 'present' : 'missing');
            
            // Use optimized endpoint that returns session + conversations in one call
            $.ajax({
                url: this.config.restUrl + 'session/recent',
                method: 'GET',
                xhrFields: {
                    withCredentials: true  // Include cookies for authentication
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                data: {
                    conversation_limit: 50
                },
                success: function(response) {
                    console.log('Academy AI Assistant: Conversation history response:', response);
                    self.hideLoadingState();
                    
                    // Check if we have a session (even if no conversations yet)
                    if (response.session) {
                        console.log('Academy AI Assistant: Found session:', response.session.id);
                        // Set session info
                        self.config.sessionId = response.session.id;
                        self.config.location = response.session.location || 'main';
                        
                        // If we have conversations, load them
                        if (response.conversations && response.conversations.length > 0) {
                            console.log('Academy AI Assistant: Found', response.conversations.length, 'conversations');
                            
                            // Clear welcome message
                            self.elements.messagesContainer.find('.aaa-welcome-message').remove();
                            
                            // Keep question starters visible - users can use them anytime
                            
                            // Add all conversations
                            response.conversations.forEach(function(conv) {
                                if (conv.message) {
                                    self.addMessage('user', conv.message);
                                }
                                if (conv.response) {
                                    self.addMessage('assistant', conv.response);
                                }
                            });
                            
                            self.scrollToBottom();
                        } else {
                            console.log('Academy AI Assistant: Session exists but no conversations yet');
                        }
                        
                        // Refresh sessions list to update active state (always do this if we have a session)
                        self.loadSessionsList();
                    } else {
                        // No session - welcome message will show, question starters visible
                        console.log('Academy AI Assistant: No session found', {
                            response: response
                        });
                    }
                },
                error: function(xhr, status, error) {
                    self.hideLoadingState();
                    console.error('Academy AI Assistant: Failed to load conversation history:', {
                        status: status,
                        error: error,
                        statusCode: xhr.status,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });
                    // Silently fail - user can still start new conversation
                }
            });
        },
        
        /**
         * Add system message
         */
        addSystemMessage: function(message) {
            var messageHtml = '<div class="aaa-system-message">' + this.formatMessage(message) + '</div>';
            this.elements.messagesContainer.append(messageHtml);
            this.scrollToBottom();
        },
        
        /**
         * Show typing indicator in chat messages
         */
        showTyping: function() {
            // Remove any existing typing indicator
            this.hideTyping();
            
            // Create typing indicator as a message-like element
            var typingHtml = '<div class="aaa-message assistant" id="' + this.typingIndicatorId + '">';
            typingHtml += '<div class="aaa-avatar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aaa-sparkles-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg></div>';
            typingHtml += '<div class="aaa-message-content">';
            typingHtml += '<div class="aaa-message-text aaa-typing-indicator">';
            typingHtml += '<span class="aaa-typing-dots">';
            typingHtml += '<span></span><span></span><span></span>';
            typingHtml += '</span>';
            typingHtml += 'Jazzedge AI is thinking...';
            typingHtml += '</div>';
            typingHtml += '</div>';
            typingHtml += '</div>';
            
            this.elements.messagesContainer.append(typingHtml);
            this.scrollToBottom();
        },
        
        /**
         * Hide typing indicator
         */
        hideTyping: function() {
            $('#' + this.typingIndicatorId).remove();
        },
        
        /**
         * Show error message
         */
        showError: function(message) {
            // Hide any existing error first to prevent duplicates
            this.hideError();
            this.elements.errorMessage.text(message).fadeIn(200);
            setTimeout(function() {
                $('#aaa-error-message').fadeOut();
            }, 5000);
        },
        
        /**
         * Hide error message
         */
        hideError: function() {
            this.elements.errorMessage.stop().fadeOut(100);
        },
        
        /**
         * Enable/disable input
         */
        setInputEnabled: function(enabled) {
            this.elements.messageInput.prop('disabled', !enabled);
            this.elements.sendButton.prop('disabled', !enabled);
            
            if (enabled) {
                this.elements.messageInput.focus();
            }
        },
        
        /**
         * Scroll to bottom of messages
         */
        scrollToBottom: function() {
            var container = this.elements.messagesContainer[0];
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        
        /**
         * Show loading state while fetching history
         */
        showLoadingState: function() {
            // Add a subtle loading indicator
            if (this.elements.messagesContainer.find('.aaa-loading-history').length === 0) {
                var loadingHtml = '<div class="aaa-loading-history" style="text-align: center; padding: 20px; color: #666; font-size: 14px;">Loading conversation history...</div>';
                this.elements.messagesContainer.append(loadingHtml);
            }
        },
        
        /**
         * Hide loading state
         */
        hideLoadingState: function() {
            this.elements.messagesContainer.find('.aaa-loading-history').remove();
        },
        
        /**
         * Load sessions list
         */
        loadSessionsList: function() {
            var self = this;
            
            // Ensure sessions list element exists
            if (!this.elements.sessionsList || this.elements.sessionsList.length === 0) {
                console.error('Academy AI Assistant: Sessions list element not found');
                console.error('Academy AI Assistant: Available elements:', {
                    container: this.elements.container ? this.elements.container.length : 0,
                    messagesContainer: this.elements.messagesContainer ? this.elements.messagesContainer.length : 0,
                    sessionsList: this.elements.sessionsList ? this.elements.sessionsList.length : 0
                });
                return;
            }
            
            console.log('Academy AI Assistant: Loading sessions list...');
            console.log('Academy AI Assistant: REST URL:', this.config.restUrl);
            console.log('Academy AI Assistant: Nonce:', this.config.nonce ? 'present (' + this.config.nonce.substring(0, 10) + '...)' : 'MISSING');
            console.log('Academy AI Assistant: User ID:', this.config.currentUserId);
            
            // Ensure restUrl doesn't have trailing slash, and add sessions
            var restUrl = this.config.restUrl;
            if (restUrl && restUrl.endsWith('/')) {
                restUrl = restUrl.slice(0, -1);
            }
            var requestUrl = restUrl + '/sessions';
            console.log('Academy AI Assistant: Request URL:', requestUrl);
            console.log('Academy AI Assistant: Base REST URL:', this.config.restUrl);
            
            $.ajax({
                url: requestUrl,
                method: 'GET',
                data: {
                    per_page: 50
                },
                xhrFields: {
                    withCredentials: true  // Include cookies for authentication
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                    console.log('Academy AI Assistant: Sending request with nonce header');
                },
                success: function(response) {
                    console.log('Academy AI Assistant: Sessions API success:', response);
                    console.log('Academy AI Assistant: Sessions count:', response.sessions ? response.sessions.length : 0);
                    if (response.sessions && response.sessions.length > 0) {
                        console.log('Academy AI Assistant: First session:', response.sessions[0]);
                    }
                    self.renderSessionsList(response.sessions || []);
                },
                error: function(xhr, status, error) {
                    console.error('Academy AI Assistant: Failed to load sessions:', {
                        status: status,
                        error: error,
                        statusCode: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON,
                        readyState: xhr.readyState
                    });
                    
                    var errorMessage = 'Error loading chats';
                    if (xhr.status === 0) {
                        errorMessage = 'Network error - check console';
                    } else if (xhr.status === 401 || xhr.status === 403) {
                        errorMessage = 'Authentication error';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Endpoint not found';
                    } else if (xhr.status >= 500) {
                        errorMessage = 'Server error';
                    }
                    
                    // Try to parse response if available
                    var responseData = null;
                    if (xhr.responseText) {
                        try {
                            responseData = JSON.parse(xhr.responseText);
                            console.error('Academy AI Assistant: Parsed error response:', responseData);
                            if (responseData && responseData.message) {
                                errorMessage += ': ' + responseData.message;
                            }
                        } catch (e) {
                            console.error('Academy AI Assistant: Could not parse error response:', e);
                        }
                    }
                    
                    self.elements.sessionsList.html('<div class="aaa-sessions-loading" style="color: #d63638;">' + errorMessage + ' (Status: ' + xhr.status + ')</div>');
                }
            });
        },
        
        /**
         * Render sessions list
         */
        renderSessionsList: function(sessions) {
            var self = this;
            var html = '';
            
            console.log('Academy AI Assistant: Rendering sessions list, count:', sessions ? sessions.length : 'null/undefined');
            console.log('Academy AI Assistant: Sessions data:', sessions);
            console.log('Academy AI Assistant: Current session ID:', this.config.sessionId);
            
            // Ensure sessions is an array
            if (!Array.isArray(sessions)) {
                console.error('Academy AI Assistant: Sessions is not an array:', typeof sessions, sessions);
                sessions = [];
            }
            
            if (sessions.length === 0) {
                console.log('Academy AI Assistant: No sessions to render');
                html = '<div class="aaa-sessions-loading">No previous chats</div>';
            } else {
                console.log('Academy AI Assistant: Rendering', sessions.length, 'sessions');
                sessions.forEach(function(session) {
                    var sessionName = session.session_name || 'New Chat';
                    var messageCount = session.message_count || 0;
                    // Use strict comparison and ensure both are numbers
                    var isActive = parseInt(session.id) === parseInt(self.config.sessionId) && self.config.sessionId > 0;
                    var activeClass = isActive ? 'active' : '';
                    
                    // Format date - use updated_at for display (when session was last used)
                    // Fall back to created_at if updated_at is not available
                    // Convert date string to Date object for formatSessionDate
                    var dateToUse = session.updated_at || session.created_at;
                    var dateObj = null;
                    
                    // Debug logging
                    console.log('Academy AI Assistant: Formatting date for session', session.id, {
                        updated_at: session.updated_at,
                        created_at: session.created_at,
                        dateToUse: dateToUse,
                        messageCount: messageCount,
                        currentTime: new Date().toISOString(),
                        wpGmtOffset: self.config.gmtOffset,
                        wpTimezone: self.config.timezone
                    });
                    
                    if (dateToUse) {
                        try {
                            // Parse the date string (format: "2025-12-19 15:19:09" from MySQL)
                            // MySQL dates are stored in UTC, so we need to parse them as UTC
                            if (typeof dateToUse === 'string') {
                                // Replace space with 'T' and add 'Z' to indicate UTC
                                // Format: "2025-12-19 15:19:09" -> "2025-12-19T15:19:09Z"
                                var dateStr = dateToUse.replace(' ', 'T');
                                // Check if it already has timezone info (Z, +, or - after the time)
                                if (!dateStr.endsWith('Z') && !dateStr.match(/[+-]\d{2}:\d{2}$/)) {
                                    dateStr += 'Z'; // Add Z to indicate UTC
                                }
                                dateObj = new Date(dateStr);
                                // Validate the date was parsed correctly
                                if (isNaN(dateObj.getTime())) {
                                    console.error('Academy AI Assistant: Invalid date string:', dateToUse, 'parsed as:', dateStr);
                                    dateObj = null;
                                } else {
                                    console.log('Academy AI Assistant: Parsed date successfully', {
                                        original: dateToUse,
                                        parsed: dateStr,
                                        dateObj: dateObj.toISOString(),
                                        dateObjLocal: dateObj.toString()
                                    });
                                }
                            } else if (dateToUse instanceof Date) {
                                dateObj = dateToUse;
                            }
                        } catch (e) {
                            console.error('Academy AI Assistant: Error parsing date:', dateToUse, e);
                            dateObj = null;
                        }
                    }
                    // Only call formatSessionDate if we have a valid date object
                    // If dateObj is null or invalid, formatSessionDate will return 'now'
                    // Add extra validation here to prevent errors
                    var dateStr = 'now';
                    if (dateObj && dateObj instanceof Date && typeof dateObj.getTime === 'function') {
                        try {
                            dateStr = self.formatSessionDate(dateObj, messageCount);
                        } catch (e) {
                            console.error('Academy AI Assistant: Error in formatSessionDate:', e, 'dateObj:', dateObj);
                            dateStr = 'now';
                        }
                    } else {
                        console.warn('Academy AI Assistant: Invalid dateObj passed to formatSessionDate:', dateObj, 'type:', typeof dateObj);
                    }
                    
                    html += '<div class="aaa-session-item ' + activeClass + '" data-session-id="' + session.id + '">';
                    html += '<div class="aaa-session-header">';
                    html += '<div class="aaa-session-name">' + self.escapeHtml(sessionName) + '</div>';
                    html += '<div class="aaa-session-actions">';
                    html += '<button type="button" class="aaa-session-action-btn aaa-rename-btn" title="Rename">';
                    html += '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>';
                    html += '</button>';
                    html += '<button type="button" class="aaa-session-action-btn aaa-delete-btn" title="Delete">';
                    html += '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>';
                    html += '</button>';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="aaa-session-meta">' + messageCount + ' messages • ' + dateStr + '</div>';
                    html += '</div>';
                });
            }
            
            this.elements.sessionsList.html(html);
        },
        
        /**
         * Format session date
         */
        formatSessionDate: function(date, messageCount) {
            // Check if date is valid - handle both Date objects and strings
            if (!date) {
                return 'now';
            }
            
            // Convert string to Date if needed
            if (typeof date === 'string') {
                try {
                    // Try to parse as ISO string with UTC
                    var dateStr = date.replace(' ', 'T');
                    if (!dateStr.endsWith('Z') && !dateStr.match(/[+-]\d{2}:\d{2}$/)) {
                        dateStr += 'Z';
                    }
                    date = new Date(dateStr);
                } catch (e) {
                    console.error('Academy AI Assistant: Error converting date string:', date, e);
                    return 'now';
                }
            }
            
            // Check if date is valid Date object - MUST be a Date instance
            if (!(date instanceof Date)) {
                console.error('Academy AI Assistant: date is not a Date object:', typeof date, date);
                return 'now';
            }
            
            // Check if date has getTime method (defensive check)
            if (typeof date.getTime !== 'function') {
                console.error('Academy AI Assistant: date.getTime is not a function:', date);
                return 'now';
            }
            
            // Check if date is valid (not NaN)
            var timeValue = date.getTime();
            if (isNaN(timeValue)) {
                console.error('Academy AI Assistant: Invalid date object (NaN):', date);
                return 'now';
            }
            
            // Get WordPress timezone offset (hours from UTC)
            // Positive = ahead of UTC, Negative = behind UTC
            var wpGmtOffset = this.config.gmtOffset || 0;
            
            // Database stores dates in UTC, but JavaScript Date parses MySQL datetime strings as local time
            // We need to treat the date as UTC and convert to WordPress timezone
            // If date is a string (MySQL format), we need to parse it as UTC
            var dateObj;
            if (typeof date === 'string') {
                // MySQL datetime format: "2025-12-19 15:19:09" - treat as UTC
                // Convert to ISO format with Z (UTC indicator)
                var isoString = date.replace(' ', 'T') + 'Z';
                dateObj = new Date(isoString);
            } else {
                // Already a Date object - assume it's UTC
                dateObj = date;
            }
            
            // Debug: Log date parsing
            console.log('Academy AI Assistant: formatSessionDate - Date calculation', {
                originalDate: date,
                dateObjUTC: dateObj.toISOString(),
                dateObjLocal: dateObj.toString(),
                wpGmtOffset: wpGmtOffset,
                messageCount: messageCount
            });
            
            // IMPORTANT: Don't convert to WordPress timezone for comparison
            // Compare dates directly in UTC, then format the display
            // The database stores in UTC, JavaScript Date with 'Z' is UTC
            var nowUTC = new Date();
            var diff = nowUTC.getTime() - dateObj.getTime();
            var minutes = Math.floor(diff / (1000 * 60));
            var hours = Math.floor(diff / (1000 * 60 * 60));
            var days = Math.floor(diff / (1000 * 60 * 60 * 24));
            
            // Debug: Log time difference
            console.log('Academy AI Assistant: formatSessionDate - Time difference', {
                diffMs: diff,
                minutes: minutes,
                hours: hours,
                days: days,
                nowUTC: nowUTC.toISOString(),
                dateUTC: dateObj.toISOString()
            });
            
            // For comparison, we need to check calendar days in UTC
            // Get UTC date components (not local)
            var dateUTCYear = dateObj.getUTCFullYear();
            var dateUTCMonth = dateObj.getUTCMonth();
            var dateUTCDay = dateObj.getUTCDate();
            
            var nowUTCYear = nowUTC.getUTCFullYear();
            var nowUTCMonth = nowUTC.getUTCMonth();
            var nowUTCDay = nowUTC.getUTCDate();
            
            // Create date objects for day comparison (midnight UTC)
            var dateStartUTC = new Date(Date.UTC(dateUTCYear, dateUTCMonth, dateUTCDay));
            var nowStartUTC = new Date(Date.UTC(nowUTCYear, nowUTCMonth, nowUTCDay));
            var daysDiff = Math.floor((nowStartUTC - dateStartUTC) / (1000 * 60 * 60 * 24));
            
            console.log('Academy AI Assistant: formatSessionDate - Day comparison', {
                dateStartUTC: dateStartUTC.toISOString(),
                nowStartUTC: nowStartUTC.toISOString(),
                daysDiff: daysDiff
            });
            
            // For new chats (0 messages), always show "now" regardless of time difference
            // This handles timezone issues and ensures new chats always show as "now"
            if (messageCount === 0) {
                console.log('Academy AI Assistant: formatSessionDate - Returning "now" for new chat (0 messages)');
                return 'now';
            }
            
            // Handle negative time (date in future or timezone issues)
            if (diff < 0) {
                console.log('Academy AI Assistant: formatSessionDate - Negative diff, returning "now"');
                return 'now';
            }
            
            // For very recent chats (within last 5 minutes), show "now"
            if (minutes < 5) {
                console.log('Academy AI Assistant: formatSessionDate - Very recent (' + minutes + ' minutes), returning "now"');
                return 'now';
            }
            
            // Less than a minute
            if (minutes < 1) {
                console.log('Academy AI Assistant: formatSessionDate - Less than 1 minute, returning "now"');
                return 'now';
            }
            // Less than an hour
            else if (hours < 1) {
                console.log('Academy AI Assistant: formatSessionDate - Less than 1 hour, returning "' + minutes + ' minutes ago"');
                return minutes + ' minutes ago';
            }
            // Same calendar day (in UTC)
            else if (daysDiff === 0) {
                console.log('Academy AI Assistant: formatSessionDate - Same day, returning "Today"');
                return 'Today';
            }
            // Yesterday (previous calendar day in UTC)
            else if (daysDiff === 1) {
                console.log('Academy AI Assistant: formatSessionDate - Yesterday, returning "Yesterday"');
                return 'Yesterday';
            }
            // Within a week
            else if (daysDiff < 7) {
                return daysDiff + ' days ago';
            }
            // Older than a week - show actual date (in WordPress timezone for display)
            else {
                return dateInWpTimezone.toLocaleDateString();
            }
        },
        
        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        /**
         * Attach handlers for "Add to Favorites" links
         */
        attachLessonLinkHandlers: function() {
            var self = this;
            
            console.log('Academy AI Assistant: Attaching lesson link handlers');
            console.log('Academy AI Assistant: Messages container:', this.elements.messagesContainer.length);
            
            // Remove existing handlers to avoid duplicates
            // Use messagesContainer as the delegate target for better performance and reliability
            this.elements.messagesContainer.off('click', '.aaa-lesson-link');
            
            // Check if any lesson links exist
            var existingLinks = this.elements.messagesContainer.find('.aaa-lesson-link');
            console.log('Academy AI Assistant: Found', existingLinks.length, 'existing lesson links');
            
            // Attach click handler for lesson links using event delegation on messages container
            this.elements.messagesContainer.on('click', '.aaa-lesson-link', function(e) {
                console.log('Academy AI Assistant: Lesson link clicked!', {
                    href: $(this).attr('href'),
                    class: $(this).attr('class'),
                    dataUrl: $(this).data('url'),
                    dataTitle: $(this).data('title')
                });
                
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                var $link = $(this);
                var url = $link.data('url') || $link.attr('href');
                var title = $link.data('title') || $link.text().trim();
                var category = $link.data('category') || 'lesson';
                
                // Detect category from URL if not set
                // /course/ and /collection/ are collections, /lesson/ are lessons
                if (!category || category === 'lesson') {
                    if (url && (url.indexOf('/collection/') !== -1 || url.indexOf('/course/') !== -1)) {
                        category = 'collection';
                    }
                }
                
                // Fallback: try to detect category from URL if not set
                if (!url || !title) {
                    console.error('Academy AI Assistant: Missing URL or title for lesson link', {
                        url: url,
                        title: title,
                        href: $link.attr('href'),
                        text: $link.text(),
                        dataUrl: $link.data('url'),
                        dataTitle: $link.data('title')
                    });
                    // If we have href, just navigate
                    if ($link.attr('href') && $link.attr('href') !== '#') {
                        window.open($link.attr('href'), '_blank');
                    }
                    return false;
                }
                
                console.log('Academy AI Assistant: Opening link modal', { url: url, title: title, category: category });
                
                // Open the modal
                self.openLinkModal(url, title, category);
                
                return false;
            });
            
            console.log('Academy AI Assistant: Lesson link handlers attached');
        },
        
        /**
         * Open link modal with lesson/collection options
         */
        openLinkModal: function(url, title, category) {
            var self = this;
            var $modal = $('#aaa-link-modal');
            
            if (!$modal.length) {
                console.error('Academy AI Assistant: Link modal not found in DOM');
                // Fallback: just navigate to the URL
                window.open(url, '_blank');
                return;
            }
            
            var $title = $('#aaa-link-modal-title');
            var $description = $('#aaa-link-modal-description');
            var $visitBtn = $('#aaa-link-modal-visit');
            var $favoriteBtn = $('#aaa-link-modal-favorite');
            
            if (!$title.length || !$description.length || !$visitBtn.length || !$favoriteBtn.length) {
                console.error('Academy AI Assistant: Modal elements not found', {
                    title: $title.length,
                    description: $description.length,
                    visitBtn: $visitBtn.length,
                    favoriteBtn: $favoriteBtn.length
                });
                // Fallback: just navigate
                window.open(url, '_blank');
                return;
            }
            
            // Update modal content
            $title.text(category === 'collection' ? 'Collection Options' : 'Lesson Options');
            $description.text('What would you like to do with "' + self.escapeHtml(title) + '"?');
            
            // Store data in modal
            $modal.data('url', url);
            $modal.data('title', title);
            $modal.data('category', category);
            
            // Update visit button - make it open in new tab
            $visitBtn.off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.open(url, '_blank', 'noopener,noreferrer');
                self.closeLinkModal();
                return false;
            });
            
            // Check if already favorited (from localStorage or modal data)
            var userId = self.config.currentUserId || 0;
            var favoritesKey = 'aaa_favorites_' + userId;
            var favorites = JSON.parse(localStorage.getItem(favoritesKey) || '[]');
            var isFavorited = favorites.indexOf(url) !== -1 || $modal.data('isFavorited') === true;
            
            // Set favorite button state based on favorite status
            if (isFavorited) {
                $favoriteBtn.prop('disabled', true)
                    .text('✓ Already in Favorites')
                    .css('opacity', '0.7')
                    .css('cursor', 'default');
                // Store in modal data for this session
                $modal.data('isFavorited', true);
            } else {
                $favoriteBtn.prop('disabled', false)
                    .text('★ Add to Favorites')
                    .css('opacity', '1')
                    .css('cursor', 'pointer');
                // Clear favorited state in modal data
                $modal.data('isFavorited', false);
            }
            
            console.log('Academy AI Assistant: Showing link modal', { url: url, title: title, category: category });
            
            // Show modal with flex display
            $modal.css('display', 'flex').css('opacity', '0').animate({ opacity: 1 }, 200);
        },
        
        /**
         * Close link modal
         */
        closeLinkModal: function() {
            var $modal = $('#aaa-link-modal');
            $modal.animate({ opacity: 0 }, 200, function() {
                $(this).css('display', 'none');
            });
        },
        
        /**
         * Handle adding to favorites from modal
         */
        handleAddFavorite: function() {
            var self = this;
            var $modal = $('#aaa-link-modal');
            var $favoriteBtn = $('#aaa-link-modal-favorite');
            
            var url = $modal.data('url');
            var title = $modal.data('title');
            var category = $modal.data('category') || 'lesson';
            
            if (!url || !title) {
                alert('Error: Missing lesson information');
                return;
            }
            
            // Disable button during request
            $favoriteBtn.prop('disabled', true).text('Adding...');
            
            // Make AJAX request
            var favoritesUrl = self.config.restUrl;
            if (favoritesUrl && !favoritesUrl.endsWith('/')) {
                favoritesUrl += '/';
            }
            favoritesUrl += 'favorites';
            
            $.ajax({
                url: favoritesUrl,
                method: 'POST',
                data: {
                    url: url,
                    title: title,
                    category: category
                },
                xhrFields: {
                    withCredentials: true
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        // Store in localStorage to persist state
                        var userId = self.config.currentUserId || 0;
                        var favoritesKey = 'aaa_favorites_' + userId;
                        var favorites = JSON.parse(localStorage.getItem(favoritesKey) || '[]');
                        if (favorites.indexOf(url) === -1) {
                            favorites.push(url);
                            localStorage.setItem(favoritesKey, JSON.stringify(favorites));
                        }
                        
                        // Update button to show persistent confirmation
                        $favoriteBtn.prop('disabled', true)
                            .text('✓ Added to Favorites')
                            .css('opacity', '0.7')
                            .css('cursor', 'default');
                        
                        // Store state in modal data so it persists if modal is reopened
                        $modal.data('isFavorited', true);
                        
                        // Show success message briefly, but keep button state
                        var $description = $('#aaa-link-modal-description');
                        var originalText = $description.text();
                        $description.text('✓ Successfully added to favorites!');
                        setTimeout(function() {
                            $description.text(originalText);
                        }, 2000);
                    } else {
                        alert(response.message || 'Failed to add to favorites');
                        $favoriteBtn.prop('disabled', false).text('★ Add to Favorites').css('opacity', '1');
                    }
                },
                error: function(xhr) {
                    var message = 'Failed to add to favorites';
                    if (xhr.status === 409) {
                        // Already in favorites - store in localStorage and show persistent state
                        var userId = self.config.currentUserId || 0;
                        var favoritesKey = 'aaa_favorites_' + userId;
                        var favorites = JSON.parse(localStorage.getItem(favoritesKey) || '[]');
                        if (favorites.indexOf(url) === -1) {
                            favorites.push(url);
                            localStorage.setItem(favoritesKey, JSON.stringify(favorites));
                        }
                        
                        // Update button to show persistent confirmation
                        $favoriteBtn.prop('disabled', true)
                            .text('✓ Already in Favorites')
                            .css('opacity', '0.7')
                            .css('cursor', 'default');
                        
                        // Store state in modal data
                        $modal.data('isFavorited', true);
                        
                        // Show message briefly
                        var $description = $('#aaa-link-modal-description');
                        var originalText = $description.text();
                        $description.text('This item is already in your favorites.');
                        setTimeout(function() {
                            $description.text(originalText);
                        }, 2000);
                    } else {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        alert(message);
                        $favoriteBtn.prop('disabled', false).text('★ Add to Favorites').css('opacity', '1');
                    }
                }
            });
        },
        
        /**
         * Old favorite handlers (removed - replaced with modal)
         */
        attachFavoriteHandlers: function() {
            // This method is deprecated - use attachLessonLinkHandlers instead
            // Kept for backward compatibility
        },
        
        /**
         * Download chat transcript
         */
        downloadTranscript: function() {
            var self = this;
            var sessionId = this.config.sessionId;
            
            if (!sessionId || sessionId === 0) {
                alert('No active chat session to download.');
                return;
            }
            
            // Disable button during download
            var $btn = $('#aaa-download-transcript-btn');
            var originalText = $btn.find('span').text();
            $btn.prop('disabled', true).find('span').text('Downloading...');
            
            // Create download URL
            var restUrl = this.config.restUrl;
            if (restUrl && restUrl.endsWith('/')) {
                restUrl = restUrl.slice(0, -1);
            }
            var downloadUrl = restUrl + '/transcript/' + sessionId + '?format=txt';
            
            // Use AJAX to download the file
            $.ajax({
                url: downloadUrl,
                method: 'GET',
                xhrFields: {
                    withCredentials: true,
                    responseType: 'blob' // Important for file downloads
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                success: function(data, status, xhr) {
                    // Get filename from Content-Disposition header or use default
                    var filename = 'chat-transcript-' + sessionId + '.txt';
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        var filenameMatch = disposition.match(/filename="?([^"]+)"?/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }
                    
                    // Create blob URL and trigger download
                    var blob = new Blob([data], { type: 'text/plain;charset=utf-8' });
                    var url = window.URL.createObjectURL(blob);
                    var link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                    
                    // Re-enable button
                    $btn.prop('disabled', false).find('span').text(originalText);
                },
                error: function(xhr, status, error) {
                    console.error('Academy AI Assistant: Failed to download transcript:', error);
                    alert('Failed to download transcript. Please try again.');
                    $btn.prop('disabled', false).find('span').text(originalText);
                }
            });
        },
        
        /**
         * Load token usage stats
         */
        loadTokenUsage: function() {
            var self = this;
            var $usageContainer = $('#aaa-token-usage');
            
            if (!$usageContainer.length) {
                return;
            }
            
            var restUrl = this.config.restUrl;
            if (restUrl && restUrl.endsWith('/')) {
                restUrl = restUrl.slice(0, -1);
            }
            
            $.ajax({
                url: restUrl + '/usage',
                method: 'GET',
                xhrFields: {
                    withCredentials: true
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                success: function(usage) {
                    self.updateTokenUsageDisplay(usage);
                },
                error: function(xhr, status, error) {
                    console.error('Academy AI Assistant: Failed to load token usage:', {
                        status: status,
                        error: error,
                        statusCode: xhr.status,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });
                    
                    // Show more helpful error message
                    var errorMsg = 'Unable to load usage';
                    if (xhr.status === 401) {
                        errorMsg = 'Please log in to view usage';
                    } else if (xhr.status === 403) {
                        errorMsg = 'Access denied';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    $usageContainer.html('<div class="aaa-token-usage-error">' + self.escapeHtml(errorMsg) + '</div>');
                }
            });
        },
        
        /**
         * Update token usage display
         */
        updateTokenUsageDisplay: function(usage) {
            var $usageContainer = $('#aaa-token-usage');
            
            if (!$usageContainer.length || !usage) {
                return;
            }
            
            var html = '<div class="aaa-token-usage-header">';
            html += '<h4>Token Usage</h4>';
            html += '<span class="aaa-token-membership">' + this.escapeHtml(usage.membership_level || 'free').charAt(0).toUpperCase() + (usage.membership_level || 'free').slice(1) + '</span>';
            html += '</div>';
            
            // Daily usage
            html += '<div class="aaa-token-usage-item">';
            html += '<div class="aaa-token-usage-label">Today</div>';
            if (usage.daily_limit > 0) {
                var dailyPercent = Math.min(100, (usage.daily_usage / usage.daily_limit) * 100);
                html += '<div class="aaa-token-usage-bar">';
                html += '<div class="aaa-token-usage-bar-fill" style="width: ' + dailyPercent + '%;"></div>';
                html += '</div>';
                html += '<div class="aaa-token-usage-text">';
                html += this.formatNumber(usage.daily_usage) + ' / ' + this.formatNumber(usage.daily_limit);
                html += ' <span class="aaa-token-usage-remaining">(' + this.formatNumber(usage.daily_remaining) + ' remaining)</span>';
                html += '</div>';
            } else {
                html += '<div class="aaa-token-usage-text">';
                html += this.formatNumber(usage.daily_usage) + ' <span class="aaa-token-usage-unlimited">(unlimited)</span>';
                html += '</div>';
            }
            html += '</div>';
            
            // Monthly usage
            html += '<div class="aaa-token-usage-item">';
            html += '<div class="aaa-token-usage-label">This Month</div>';
            if (usage.monthly_limit > 0) {
                var monthlyPercent = Math.min(100, (usage.monthly_usage / usage.monthly_limit) * 100);
                html += '<div class="aaa-token-usage-bar">';
                html += '<div class="aaa-token-usage-bar-fill" style="width: ' + monthlyPercent + '%;"></div>';
                html += '</div>';
                html += '<div class="aaa-token-usage-text">';
                html += this.formatNumber(usage.monthly_usage) + ' / ' + this.formatNumber(usage.monthly_limit);
                html += ' <span class="aaa-token-usage-remaining">(' + this.formatNumber(usage.monthly_remaining) + ' remaining)</span>';
                html += '</div>';
            } else {
                html += '<div class="aaa-token-usage-text">';
                html += this.formatNumber(usage.monthly_usage) + ' <span class="aaa-token-usage-unlimited">(unlimited)</span>';
                html += '</div>';
            }
            html += '</div>';
            
            $usageContainer.html(html);
        },
        
        /**
         * Format number with commas
         */
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        /**
         * Create new chat
         */
        createNewChat: function() {
            var self = this;
            
            $.ajax({
                url: this.config.restUrl + 'sessions',
                method: 'POST',
                xhrFields: {
                    withCredentials: true  // Include cookies for authentication
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                data: JSON.stringify({
                    location: this.config.location,
                    session_name: ''
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.session_id) {
                        self.config.sessionId = response.session_id;
                        // Load session (it will refresh the list at the end)
                        self.loadSession(response.session_id);
                    } else if (response.session && response.session.id) {
                        // Alternative response format
                        self.config.sessionId = response.session.id;
                        self.loadSession(response.session.id);
                    }
                },
                complete: function() {
                    // Remove creating class after completion
                    $('#aaa-new-chat-btn').removeClass('creating');
                },
                error: function(xhr, status, error) {
                    console.error('Academy AI Assistant: Failed to create new session:', error);
                    self.showError('Failed to create new chat. Please try again.');
                }
            });
        },
        
        /**
         * Load a specific session
         */
        loadSession: function(sessionId) {
            var self = this;
            
            // Update active session
            this.config.sessionId = sessionId;
            
            // Clear current messages
            this.elements.messagesContainer.empty();
            
            // Show loading
            this.showLoadingState();
            
            // Load conversation history
            $.ajax({
                url: this.config.restUrl + 'session/recent',
                method: 'GET',
                xhrFields: {
                    withCredentials: true  // Include cookies for authentication
                },
                data: {
                    session_id: sessionId,
                    conversation_limit: 50
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                success: function(response) {
                    self.hideLoadingState();
                    
                    if (response.session && response.conversations && response.conversations.length > 0) {
                        self.config.location = response.session.location || 'main';
                        
                        response.conversations.forEach(function(conv) {
                            self.addMessage('user', conv.message);
                            self.addMessage('assistant', conv.response);
                        });
                        
                        // Keep question starters visible - users can use them anytime
                    } else {
                        // New session - show welcome message (already in HTML, just show question starters)
                        // Welcome message is already in the HTML from the shortcode
                        $('#aaa-question-starters').show();
                    }
                    
                    // Refresh sessions list to update active state
                    self.loadSessionsList();
                    self.scrollToBottom();
                },
                error: function(xhr, status, error) {
                    self.hideLoadingState();
                    console.error('Academy AI Assistant: Failed to load session:', error);
                    self.showError('Failed to load chat. Please try again.');
                }
            });
        },
        
        /**
         * Start renaming a session
         */
        startRenamingSession: function(sessionId) {
            var $item = this.elements.sessionsList.find('.aaa-session-item[data-session-id="' + sessionId + '"]');
            var $name = $item.find('.aaa-session-name');
            var currentName = $name.text();
            
            $item.addClass('aaa-renaming');
            $name.html('<input type="text" class="aaa-session-rename-input" data-session-id="' + sessionId + '" value="' + this.escapeHtml(currentName) + '">');
            $item.find('.aaa-session-rename-input').focus().select();
        },
        
        /**
         * Finish renaming a session
         */
        finishRenamingSession: function(sessionId, newName) {
            var self = this;
            var $item = this.elements.sessionsList.find('.aaa-session-item[data-session-id="' + sessionId + '"]');
            
            $.ajax({
                url: this.config.restUrl + 'sessions/' + sessionId + '/name',
                method: 'POST',
                xhrFields: {
                    withCredentials: true  // Include cookies for authentication
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                data: JSON.stringify({
                    session_name: newName
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    $item.removeClass('aaa-renaming');
                    var displayName = newName || 'New Chat';
                    $item.find('.aaa-session-name').text(displayName);
                },
                error: function(xhr, status, error) {
                    console.error('Academy AI Assistant: Failed to rename session:', error);
                    self.cancelRenamingSession();
                    self.showError('Failed to rename chat. Please try again.');
                }
            });
        },
        
        /**
         * Cancel renaming
         */
        cancelRenamingSession: function() {
            this.elements.sessionsList.find('.aaa-session-item.aaa-renaming').each(function() {
                var $item = $(this);
                var sessionId = $item.data('session-id');
                var $input = $item.find('.aaa-session-rename-input');
                var originalName = $input.data('original-name') || 'New Chat';
                $item.removeClass('aaa-renaming');
                $item.find('.aaa-session-name').text(originalName);
            });
        },
        
        /**
         * Delete a session
         */
        deleteSession: function(sessionId) {
            var self = this;
            
            // Validate sessionId
            sessionId = parseInt(sessionId);
            if (!sessionId || sessionId <= 0) {
                console.error('Academy AI Assistant: Invalid session ID for deletion');
                return;
            }
            
            var $sessionItem = this.elements.sessionsList.find('.aaa-session-item[data-session-id="' + sessionId + '"]');
            var wasActive = parseInt(sessionId) === parseInt(self.config.sessionId);
            
            // Optimistic UI: Immediately fade out and remove the item
            if ($sessionItem.length > 0) {
                $sessionItem.fadeOut(200, function() {
                    $sessionItem.remove();
                    
                    // Remove deleting class from button
                    $sessionItem.find('.aaa-delete-btn').removeClass('deleting');
                    
                    // If no sessions left, show empty state
                    if (self.elements.sessionsList.find('.aaa-session-item').length === 0) {
                        self.elements.sessionsList.html('<div class="aaa-sessions-loading">No chats yet</div>');
                    }
                });
            }
            
            // If deleted session was active, create new one immediately
            if (wasActive) {
                self.config.sessionId = 0;
                self.elements.messagesContainer.html('<div class="aaa-welcome-message"><div class="aaa-avatar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aaa-sparkles-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg></div><div class="aaa-message-content"><p>Hi! I\'m Jazzedge AI, your knowledgeable and friendly music teacher. I\'m here to help you learn jazz piano, music theory, and piano technique.</p><p>How can I help you today?</p></div></div>');
                $('#aaa-question-starters').show();
            }
            
            // Perform actual deletion in background
            $.ajax({
                url: this.config.restUrl + 'session/' + sessionId,
                method: 'DELETE',
                xhrFields: {
                    withCredentials: true  // Include cookies for authentication
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                success: function(response) {
                    console.log('Academy AI Assistant: Session deleted successfully', response);
                    
                    // Remove deleting class
                    $sessionItem.find('.aaa-delete-btn').removeClass('deleting');
                    
                    // If deleted session was active, create new session
                    if (wasActive) {
                        self.createNewChat();
                    } else {
                        // Refresh list to ensure consistency
                        self.loadSessionsList();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Academy AI Assistant: Failed to delete session:', {
                        xhr: xhr,
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });
                    
                    // Remove deleting class
                    $sessionItem.find('.aaa-delete-btn').removeClass('deleting');
                    
                    // Reload list to restore the item
                    self.loadSessionsList();
                    self.showError('Failed to delete chat. Please try again.');
                }
            });
        }
    };
    
    /**
     * Open chip modal with form fields
     */
    AAAAssistant.openChipModal = function(template, fields, chipId) {
        var self = this;
        var $modal = $('#aaa-chip-modal');
        var $formFields = $('#aaa-chip-form-fields');
        
        // Store template and chip_id for later use
        $modal.data('template', template);
        $modal.data('chip-id', chipId || null);
        
        // Clear previous fields
        $formFields.empty();
        
        // Build form fields
        fields.forEach(function(field, index) {
            var $fieldWrapper = $('<div class="aaa-chip-field-wrapper"></div>');
            var $label = $('<label for="aaa-chip-field-' + index + '">' + field.label + '</label>');
            $fieldWrapper.append($label);
            
            if (field.type === 'select') {
                var $select = $('<select id="aaa-chip-field-' + index + '" name="' + field.name + '" class="aaa-chip-field" required></select>');
                $select.append('<option value="">Select...</option>');
                
                // Check for saved preference if this is the skill level field
                var savedValue = null;
                if (field.name === 'level') {
                    savedValue = localStorage.getItem('aaa_skill_level');
                }
                
                if (field.options) {
                    field.options.forEach(function(option) {
                        var optionText = option.charAt(0).toUpperCase() + option.slice(1);
                        var $option = $('<option value="' + option + '">' + optionText + '</option>');
                        // Pre-select saved value if it matches
                        if (savedValue && option.toLowerCase() === savedValue.toLowerCase()) {
                            $option.attr('selected', 'selected');
                        }
                        $select.append($option);
                    });
                }
                $fieldWrapper.append($select);
            } else {
                var $input = $('<input type="text" id="aaa-chip-field-' + index + '" name="' + field.name + '" class="aaa-chip-field" placeholder="' + (field.placeholder || '') + '" required>');
                $fieldWrapper.append($input);
            }
            
            $formFields.append($fieldWrapper);
        });
        
        // Show modal
        $modal.fadeIn(200);
        
        // Focus first field
        setTimeout(function() {
            $formFields.find('.aaa-chip-field').first().focus();
        }, 250);
    };
    
    /**
     * Close chip modal
     */
    AAAAssistant.closeChipModal = function() {
        var $modal = $('#aaa-chip-modal');
        var $form = $('#aaa-chip-form');
        var $submitBtn = $('#aaa-chip-modal-submit');
        $modal.fadeOut(200);
        $form[0].reset();
        // Reset submitting flag when closing
        $form.data('submitting', false);
        // Re-enable submit button
        $submitBtn.prop('disabled', false);
    };
    
    /**
     * Submit chip form and populate message input
     */
    AAAAssistant.submitChipForm = function() {
        var self = this;
        var $modal = $('#aaa-chip-modal');
        var $form = $('#aaa-chip-form');
        var template = $modal.data('template');
        
        // Prevent double submission
        if ($form.data('submitting')) {
            console.log('Academy AI Assistant: Chip form already submitting, ignoring duplicate');
            return;
        }
        
        // Mark as submitting
        $form.data('submitting', true);
        
        // Disable submit button to prevent double clicks
        var $submitBtn = $('#aaa-chip-modal-submit');
        $submitBtn.prop('disabled', true);
        
        // Get form values
        var formData = {};
        $form.find('.aaa-chip-field').each(function() {
            var $field = $(this);
            formData[$field.attr('name')] = $field.val().trim();
        });
        
        // Validate all fields are filled
        var allFilled = true;
        $form.find('.aaa-chip-field').each(function() {
            if (!$(this).val().trim()) {
                allFilled = false;
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (!allFilled) {
            // Reset submitting flag if validation failed
            $form.data('submitting', false);
            $submitBtn.prop('disabled', false);
            return;
        }
        
        // Replace placeholders in template
        var message = template;
        Object.keys(formData).forEach(function(key) {
            var value = formData[key];
            // Capitalize if it's a skill level
            if (key === 'level') {
                value = value.charAt(0).toUpperCase() + value.slice(1);
                // Save skill level preference to localStorage
                localStorage.setItem('aaa_skill_level', formData[key].toLowerCase());
            }
            message = message.replace('{' + key + '}', value);
        });
        
        // Populate message input
        if (self.elements.messageInput.length > 0) {
            // Mark that we're sending from a chip (prevent Enter key handler from firing)
            self._sendingFromChip = true;
            
            self.elements.messageInput.val(message);
            // Don't trigger 'input' event - it might cause issues
            // self.elements.messageInput.trigger('input');
            
            // Get chip_id from modal
            var chipId = $modal.data('chip-id');
            
            // Close modal
            self.closeChipModal();
            
            // Automatically send the message to JAI with chip_id
            // Use a slightly longer delay to ensure modal is closed
            setTimeout(function() {
                self.sendMessage(message, chipId);
                // Reset flags after sending
                $form.data('submitting', false);
                // Clear chip sending flag after a short delay
                setTimeout(function() {
                    self._sendingFromChip = false;
                }, 500);
            }, 150);
        } else {
            // Reset submitting flag if input element not found
            $form.data('submitting', false);
            $submitBtn.prop('disabled', false);
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        AAAAssistant.init();
    });
    
})(jQuery);
