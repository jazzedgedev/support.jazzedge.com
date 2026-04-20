<?php
/**
 * Katahdin AI Hub integration for email generation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class JE_Emails_AI_Handler {

    const OPTION_CUSTOM_PROMPT = 'je_emails_system_prompt';

    /**
     * Base + custom instructions for the model.
     */
    public static function build_full_system_prompt($custom_system_prompt) {
        $custom = is_string($custom_system_prompt) ? trim($custom_system_prompt) : '';
        $base = <<<'PROMPT'
You are an expert email copywriter for Jazzedge, an online piano education company. You write warm, conversational marketing emails for piano students.

IMPORTANT — Format the email body as WordPress Gutenberg block markup exactly like this FluentCRM example:
<!-- wp:paragraph -->
<p>Hey {{contact.first_name}},</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Your paragraph here.</p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>Item</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

Rules:
- Always start body with Hey {{contact.first_name}},
- Use <!-- wp:paragraph --><p>...</p><!-- /wp:paragraph --> for every paragraph
- Use <!-- wp:list --> with nested <!-- wp:list-item --> for bullet lists
- For highlighted/callout paragraphs use: <!-- wp:paragraph {"style":{"color":{"background":"#ff6a003d"}},"paddingTop":15,"paddingRight":15,"paddingBottom":15,"paddingLeft":15} --><p class="has-background" style="...">...</p><!-- /wp:paragraph -->
- End with Talk soon,\nWillie
- Return ONLY the block markup for the body, no extra explanation
- For subject lines, return ONLY the subject text, no quotes or explanation

[CUSTOM INSTRUCTIONS FROM SETTINGS]:
PROMPT;
        return $base . "\n" . ( $custom !== '' ? $custom : '(none)' );
    }

    public static function get_custom_system_prompt() {
        $v = get_option(self::OPTION_CUSTOM_PROMPT, '');
        return is_string($v) ? $v : '';
    }

    /**
     * @param string|null $json
     * @return array<int, array{role: string, content: string}>
     */
    public static function decode_thread($json) {
        if (!is_string($json) || $json === '') {
            return array();
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return array();
        }
        $out = array();
        foreach ($decoded as $msg) {
            if (!is_array($msg) || empty($msg['role']) || !isset($msg['content'])) {
                continue;
            }
            $role = $msg['role'] === 'assistant' ? 'assistant' : 'user';
            $out[] = array(
                'role' => $role,
                'content' => (string) $msg['content'],
            );
        }
        return $out;
    }

    /**
     * Run chat completion via hub.
     *
     * @param array<int, array{role: string, content: string}> $messages After system message.
     * @return string|WP_Error Assistant text.
     */
    private function chat($messages) {
        try {
            if (!function_exists('katahdin_ai_hub')) {
                return new WP_Error('no_hub', __('Katahdin AI Hub is not available.', 'jazzedge-ai-emails'));
            }
            $hub = katahdin_ai_hub();
            if (!$hub || !method_exists($hub, 'make_api_call')) {
                return new WP_Error('no_hub', __('Katahdin AI Hub is not initialized.', 'jazzedge-ai-emails'));
            }
            $response = $hub->make_api_call(
                JAZZEDGE_AI_EMAILS_PLUGIN_ID,
                'chat/completions',
                array(
                    'model' => 'gpt-4o',
                    'messages' => $messages,
                    'max_tokens' => 2000,
                    'temperature' => 0.7,
                )
            );
            if (is_wp_error($response)) {
                return $response;
            }
            if (!empty($response['choices'][0]['message']['content'])) {
                return trim((string) $response['choices'][0]['message']['content']);
            }
            return new WP_Error('bad_response', __('Unexpected response from AI.', 'jazzedge-ai-emails'));
        } catch (Exception $e) {
            return new WP_Error('ai_exception', $e->getMessage());
        }
    }

    /**
     * @param string $prompt User request or revision feedback.
     * @param array  $thread Prior {role, content} pairs (no system).
     * @param string $custom_system_prompt From settings.
     * @param bool   $is_revision Whether this continues an existing thread.
     * @return array{content: string, thread: array, messages_sent: array<int, array{role: string, content: string}>}|WP_Error
     */
    public function generate_subject($prompt, $thread, $custom_system_prompt, $is_revision = false) {
        $system = self::build_full_system_prompt($custom_system_prompt);
        $messages = array(
            array('role' => 'system', 'content' => $system),
        );
        foreach ($thread as $m) {
            $messages[] = array(
                'role' => $m['role'],
                'content' => $m['content'],
            );
        }
        if ($is_revision) {
            $user_msg = 'Revise the email subject line based on this feedback. Return ONLY the new subject text, no quotes or explanation:\n\n' . $prompt;
        } else {
            $user_msg_tmpl = "Create a compelling email subject line for a marketing email to piano students. Return ONLY the subject text.\n\nBrief / goals:\n%s";
            $user_msg = sprintf($user_msg_tmpl, $prompt);
        }
        $messages[] = array('role' => 'user', 'content' => $user_msg);
        $content = $this->chat($messages);
        if (is_wp_error($content)) {
            return $content;
        }
        $content = $this->strip_subject_wrappers($content);
        $thread_out = $thread;
        $thread_out[] = array('role' => 'user', 'content' => $user_msg);
        $thread_out[] = array('role' => 'assistant', 'content' => $content);
        return array(
            'content' => $content,
            'thread' => $thread_out,
            'messages_sent' => $messages,
        );
    }

    /**
     * @param string $prompt User request or revision feedback.
     * @param array  $thread Prior messages.
     * @param string $custom_system_prompt
     * @param string $subject_hint Current subject for context on first gen.
     * @param bool   $is_revision
     * @return array{content: string, thread: array, messages_sent: array<int, array{role: string, content: string}>}|WP_Error
     */
    public function generate_body($prompt, $thread, $custom_system_prompt, $subject_hint = '', $is_revision = false) {
        $system = self::build_full_system_prompt($custom_system_prompt);
        $messages = array(
            array('role' => 'system', 'content' => $system),
        );
        foreach ($thread as $m) {
            $messages[] = array(
                'role' => $m['role'],
                'content' => $m['content'],
            );
        }
        if ($is_revision) {
            $user_msg = "Revise the email body (keep valid Gutenberg block markup). Return ONLY the block markup, no explanation.\n\nFeedback:\n" . $prompt;
        } else {
            $sub = $subject_hint !== '' ? "Subject line: {$subject_hint}\n\n" : '';
            $user_msg = "{$sub}Write a BRAND NEW email body using FluentCRM Gutenberg block markup as specified in your instructions.\n\nThe following prompt contains your content directives and any style reference. Follow the instructions in it carefully — the IMPORTANT POINTS are your content source. Any reference email included is for voice/tone matching ONLY and must not be rewritten or copied from.\n\n" . $prompt;
        }
        $messages[] = array('role' => 'user', 'content' => $user_msg);
        $content = $this->chat($messages);
        if (is_wp_error($content)) {
            return $content;
        }
        $thread_out = $thread;
        $thread_out[] = array('role' => 'user', 'content' => $user_msg);
        $thread_out[] = array('role' => 'assistant', 'content' => $content);
        return array(
            'content' => $content,
            'thread' => $thread_out,
            'messages_sent' => $messages,
        );
    }

    private function strip_subject_wrappers($text) {
        $text = trim($text);
        $text = preg_replace('/^["\']|["\']$/u', '', $text);
        return trim($text);
    }

    /**
     * @return array{success: bool, message?: string, error?: string}
     */
    public function test_connection() {
        if (!function_exists('katahdin_ai_hub')) {
            return array(
                'success' => false,
                'error' => __('Katahdin AI Hub is not available.', 'jazzedge-ai-emails'),
            );
        }
        $hub = katahdin_ai_hub();
        if (!$hub || !$hub->api_manager) {
            return array(
                'success' => false,
                'error' => __('API manager not available.', 'jazzedge-ai-emails'),
            );
        }
        $ping = $this->chat(
            array(
                array('role' => 'system', 'content' => 'Reply with the single word pong.'),
                array('role' => 'user', 'content' => 'ping'),
            )
        );
        if (is_wp_error($ping)) {
            return array(
                'success' => false,
                'error' => $ping->get_error_message(),
            );
        }
        return array(
            'success' => true,
            'message' => __('Connection OK.', 'jazzedge-ai-emails'),
        );
    }
}
