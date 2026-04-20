<?php
/**
 * AI prompt builder and OpenAI caller via Katahdin AI Hub.
 */

if (!defined('ABSPATH')) {
    exit;
}

class FC_Helper_AI_Generator {

    const PLUGIN_ID = 'fc-helper';

    const MODEL = 'gpt-4o';

    const OPTION_THEMES = 'fc_helper_themes';

    /**
     * Keys required on every stored theme row.
     *
     * @return list<string>
     */
    private static function theme_data_keys() {
        return array(
            'label',
            'primary',
            'accent',
            'bg_dark',
            'bg_mid',
            'bg_gradient_end',
            'gold',
            'gold_dark',
            'text_light',
            'text_muted',
            'bg_cream',
            'bg_warm',
            'border_warm',
            'text_on_light',
        );
    }

    /**
     * Built-in theme pack (seed / reset defaults).
     *
     * @return array<string, array<string, string>>
     */
    public static function get_default_themes() {
        return array(
            'dark_gold'    => array(
                'label'           => __('Dark Gold', 'fc-helper'),
                'primary'         => '#1c1917',
                'accent'          => '#c9a846',
                'bg_dark'         => '#1c1917',
                'bg_mid'          => '#2d2418',
                'bg_gradient_end' => '#2a2218',
                'gold'            => '#c9a846',
                'gold_dark'       => '#3d2f0a',
                'text_light'      => '#c9bfa0',
                'text_muted'      => '#d4c49a',
                'bg_cream'        => '#faf9f7',
                'bg_warm'         => '#faf6ec',
                'border_warm'     => '#e8dfc8',
                'text_on_light'   => '#1c1917',
            ),
            'sherpa_blue'  => array(
                'label'           => __('Sherpa Blue', 'fc-helper'),
                'primary'         => '#004555',
                'accent'          => '#239b90',
                'bg_dark'         => '#004555',
                'bg_mid'          => '#003847',
                'bg_gradient_end' => '#002f3c',
                'gold'            => '#239b90',
                'gold_dark'       => '#156b63',
                'text_light'      => '#b8ded9',
                'text_muted'      => '#9bcfc9',
                'bg_cream'        => '#f2faf9',
                'bg_warm'         => '#eaf6f4',
                'border_warm'     => '#c8e0dc',
                'text_on_light'   => '#003038',
            ),
            'jungle_green' => array(
                'label'           => __('Jungle Green', 'fc-helper'),
                'primary'         => '#002a34',
                'accent'          => '#239b90',
                'bg_dark'         => '#002a34',
                'bg_mid'          => '#0d3a42',
                'bg_gradient_end' => '#001f28',
                'gold'            => '#239b90',
                'gold_dark'       => '#1a756c',
                'text_light'      => '#a8d6d1',
                'text_muted'      => '#8bc9c2',
                'bg_cream'        => '#f0faf9',
                'bg_warm'         => '#e5f5f3',
                'border_warm'     => '#bed9d5',
                'text_on_light'   => '#002229',
            ),
            'pomegranate'  => array(
                'label'           => __('Pomegranate', 'fc-helper'),
                'primary'         => '#1a0a08',
                'accent'          => '#f04e23',
                'bg_dark'         => '#1a0a08',
                'bg_mid'          => '#2a1512',
                'bg_gradient_end' => '#120605',
                'gold'            => '#f04e23',
                'gold_dark'       => '#b8381a',
                'text_light'      => '#e8c9c0',
                'text_muted'      => '#d4b0a5',
                'bg_cream'        => '#fdf8f6',
                'bg_warm'         => '#faf0ec',
                'border_warm'     => '#e8cfc4',
                'text_on_light'   => '#1a0a08',
            ),
            'ocean_green'  => array(
                'label'           => __('Ocean Green', 'fc-helper'),
                'primary'         => '#003040',
                'accent'          => '#459e90',
                'bg_dark'         => '#003040',
                'bg_mid'          => '#0d4650',
                'bg_gradient_end' => '#002530',
                'gold'            => '#459e90',
                'gold_dark'       => '#2d6b61',
                'text_light'      => '#b5ddd6',
                'text_muted'      => '#9acfc6',
                'bg_cream'        => '#f2faf9',
                'bg_warm'         => '#e9f5f3',
                'border_warm'     => '#c0dbd6',
                'text_on_light'   => '#002830',
            ),
            'daintree'     => array(
                'label'           => __('Daintree', 'fc-helper'),
                'primary'         => '#002a34',
                'accent'          => '#6b2b60',
                'bg_dark'         => '#002a34',
                'bg_mid'          => '#1a3040',
                'bg_gradient_end' => '#001e26',
                'gold'            => '#6b2b60',
                'gold_dark'       => '#4a1f42',
                'text_light'      => '#c9b8c6',
                'text_muted'      => '#b5a0b2',
                'bg_cream'        => '#faf8fa',
                'bg_warm'         => '#f4eef3',
                'border_warm'     => '#dfd0dc',
                'text_on_light'   => '#002229',
            ),
            'light_cream'  => array(
                'label'           => __('Light Cream', 'fc-helper'),
                'primary'         => '#f5f0e8',
                'accent'          => '#b8860b',
                'bg_dark'         => '#f5f0e8',
                'bg_mid'          => '#ede5d8',
                'bg_gradient_end' => '#e8dcc8',
                'gold'            => '#b8860b',
                'gold_dark'       => '#8b6914',
                'text_light'      => '#4a3f30',
                'text_muted'      => '#5c5042',
                'bg_cream'        => '#faf9f7',
                'bg_warm'         => '#faf6ec',
                'border_warm'     => '#e8dfc8',
                'text_on_light'   => '#1c1917',
            ),
        );
    }

    /**
     * Ensure a theme row has all keys; fill from $fallback (per-key) when missing or invalid.
     *
     * @param array<string, string> $data
     * @param array<string, string> $fallback
     * @return array<string, string>
     */
    public static function normalize_theme_row(array $data, array $fallback) {
        $defaults_all = self::get_default_themes();
        $base_fb      = $defaults_all['dark_gold'];
        $out          = array();
        foreach (self::theme_data_keys() as $key) {
            if ('label' === $key) {
                $raw = isset($data[ $key ]) ? (string) $data[ $key ] : '';
                $fb = isset($fallback[ $key ]) ? (string) $fallback[ $key ] : (string) $base_fb[ $key ];
                $out[ $key ] = '' !== trim($raw) ? sanitize_text_field($raw) : $fb;
                continue;
            }
            $raw = isset($data[ $key ]) ? (string) $data[ $key ] : '';
            $fb  = isset($fallback[ $key ]) ? (string) $fallback[ $key ] : (string) $base_fb[ $key ];
            $san = sanitize_hex_color($raw);
            if (!$san) {
                $san = sanitize_hex_color($fb);
            }
            $out[ $key ] = $san ? $san : '#000000';
        }
        return $out;
    }

    /**
     * Theme definitions for AI tokens and UI swatches (stored option or defaults).
     *
     * @return array<string, array<string, string>>
     */
    public static function get_themes() {
        $stored = get_option(self::OPTION_THEMES, null);
        if (!is_array($stored) || array() === $stored) {
            return self::get_default_themes();
        }
        $defaults = self::get_default_themes();
        $out      = array();
        foreach ($stored as $slug => $row) {
            $slug = sanitize_key((string) $slug);
            if ('' === $slug || !is_array($row)) {
                continue;
            }
            $fb = isset($defaults[ $slug ]) ? $defaults[ $slug ] : $defaults['dark_gold'];
            $out[ $slug ] = self::normalize_theme_row($row, $fb);
        }
        if (array() === $out) {
            return self::get_default_themes();
        }
        return $out;
    }

    /**
     * @param array<string, array<string, string>> $themes
     */
    public static function save_themes(array $themes) {
        $defaults = self::get_default_themes();
        $clean    = array();
        foreach ($themes as $slug => $row) {
            $slug = sanitize_key((string) $slug);
            if ('' === $slug || !is_array($row)) {
                continue;
            }
            $fb = isset($defaults[ $slug ]) ? $defaults[ $slug ] : $defaults['dark_gold'];
            $clean[ $slug ] = self::normalize_theme_row($row, $fb);
        }
        return (bool) update_option(self::OPTION_THEMES, $clean, false);
    }

    /**
     * @param string $theme_key
     * @return array<string, string>
     */
    public static function get_theme($theme_key) {
        $themes = self::get_themes();
        if (isset($themes[ $theme_key ])) {
            return $themes[ $theme_key ];
        }
        if (isset($themes['dark_gold'])) {
            return $themes['dark_gold'];
        }
        $first = reset($themes);
        return is_array($first) ? $first : self::get_default_themes()['dark_gold'];
    }

    /**
     * Keys returned by suggest_colors (matches theme palette minus label).
     *
     * @return list<string>
     */
    private static function suggest_color_palette_keys() {
        return array(
            'primary',
            'bg_mid',
            'bg_gradient_end',
            'accent',
            'gold',
            'gold_dark',
            'text_light',
            'text_muted',
            'bg_cream',
            'bg_warm',
            'border_warm',
            'text_on_light',
            'bg_dark',
        );
    }

    /**
     * @param string $raw
     * @return array<string, mixed>|null
     */
    private static function parse_suggest_colors_json($raw) {
        $t = trim((string) $raw);
        if (preg_match('/^```(?:json)?\s*\R?(.*?)\R?```\s*$/is', $t, $m)) {
            $t = trim($m[1]);
        }
        $decoded = json_decode($t, true);
        if (!is_array($decoded)) {
            $start = strpos($t, '{');
            $end   = strrpos($t, '}');
            if (false !== $start && false !== $end && $end > $start) {
                $decoded = json_decode(substr($t, $start, $end - $start + 1), true);
            }
        }
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Ask the AI for a full palette from one master color.
     *
     * @param string $master_color Hex color, e.g. #1c1917.
     * @param string $theme_name   Optional hint for naming / mood.
     * @return array<string, string>|WP_Error
     */
    public static function suggest_colors($master_color, $theme_name) {
        if (!fc_helper_is_hub_active()) {
            return new WP_Error(
                'no_hub',
                __('Katahdin AI Hub is not available.', 'fc-helper')
            );
        }
        $master_color = sanitize_hex_color((string) $master_color);
        if (!$master_color) {
            return new WP_Error('bad_color', __('Invalid master color.', 'fc-helper'));
        }
        $theme_name = sanitize_text_field((string) $theme_name);
        $hint       = '' !== $theme_name ? $theme_name : '(none)';

        $user_msg = "Generate a premium color theme for an online piano course product page. Master color: {$master_color}. Theme name hint: {$hint}.\n\n"
            . 'Return ONLY a valid JSON object with exactly these 13 keys and hex color values:'
            . "\n{\n"
            . '  "primary": dark background color derived from or harmonious with the master color,' . "\n"
            . '  "bg_mid": slightly lighter variant of primary (5-8% lighter),' . "\n"
            . '  "bg_gradient_end": slightly darker variant of primary (3-5% darker),' . "\n"
            . '  "accent": a vibrant, high-contrast accent color that complements the master color — this is used for buttons, badges, and highlights,' . "\n"
            . '  "gold": same value as accent,' . "\n"
            . '  "gold_dark": darker version of accent (15-20% darker), used for subtle accent text,' . "\n"
            . '  "text_light": a very light color (85-95% lightness) readable on the dark primary background,' . "\n"
            . '  "text_muted": slightly less bright than text_light, same hue family,' . "\n"
            . '  "bg_cream": near-white with a very subtle warm or cool tint matching the theme palette,' . "\n"
            . '  "bg_warm": slightly more saturated/tinted than bg_cream,' . "\n"
            . '  "border_warm": a soft border color between bg_warm and a mid tone,' . "\n"
            . '  "text_on_light": a dark readable color for text on light/cream backgrounds,' . "\n"
            . '  "bg_dark": same value as primary' . "\n"
            . "}\n\n"
            . "Rules: primary/bg_dark must be dark (luminance < 0.15). accent must be vivid and have contrast ratio >= 3.5:1 against primary. "
            . "All values must be valid 6-digit hex colors starting with #. Return JSON only — no markdown, no explanation.";

        $messages = array(
            array(
                'role'    => 'system',
                'content' => 'You are a color designer. Output only valid JSON.',
            ),
            array(
                'role'    => 'user',
                'content' => $user_msg,
            ),
        );

        $chat = self::execute_chat(
            $messages,
            array(
                'max_tokens'  => 300,
                'temperature' => 0.7,
                'raw_content' => true,
            )
        );
        if (is_wp_error($chat)) {
            return $chat;
        }
        $content = isset($chat['content']) ? (string) $chat['content'] : '';
        $decoded = self::parse_suggest_colors_json($content);
        if (null === $decoded) {
            return new WP_Error(
                'parse_json',
                __('Could not parse color palette from the AI response.', 'fc-helper')
            );
        }
        $out = array();
        foreach (self::suggest_color_palette_keys() as $key) {
            if (!isset($decoded[ $key ]) || !is_string($decoded[ $key ])) {
                return new WP_Error(
                    'missing_key',
                    sprintf(
                        /* translators: %s: color key name */
                        __('AI response missing or invalid color key: %s', 'fc-helper'),
                        $key
                    )
                );
            }
            $san = sanitize_hex_color($decoded[ $key ]);
            if (!$san) {
                return new WP_Error(
                    'bad_hex',
                    sprintf(
                        /* translators: %s: color key name */
                        __('Invalid hex color for key: %s', 'fc-helper'),
                        $key
                    )
                );
            }
            $out[ $key ] = $san;
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $raw
     * @return array<string, mixed>|WP_Error
     */
    public static function generate($raw) {
        if (!fc_helper_is_hub_active()) {
            return new WP_Error(
                'no_hub',
                __('Katahdin AI Hub is not available.', 'fc-helper'),
                array(
                    'prompt_sent'   => array(),
                    'response_time' => null,
                    'http_status'   => null,
                    'model'         => self::MODEL,
                    'usage'         => array(),
                )
            );
        }
        $theme    = self::get_theme(isset($raw['theme']) ? (string) $raw['theme'] : 'dark_gold');
        $messages = array(
            array(
                'role'    => 'system',
                'content' => self::build_system_prompt($theme),
            ),
            array(
                'role'    => 'user',
                'content' => self::build_user_prompt($raw, $theme),
            ),
        );
        return self::execute_chat($messages);
    }

    /**
     * @param string               $html
     * @param string               $revision_request
     * @param array<string, string> $theme
     * @return array<string, mixed>|WP_Error
     */
    public static function revise($html, $revision_request, $theme) {
        if (!fc_helper_is_hub_active()) {
            return new WP_Error(
                'no_hub',
                __('Katahdin AI Hub is not available.', 'fc-helper'),
                array(
                    'prompt_sent'   => array(),
                    'response_time' => null,
                    'http_status'   => null,
                    'model'         => self::MODEL,
                    'usage'         => array(),
                )
            );
        }
        $revision_request = trim((string) $revision_request);
        if ($revision_request === '') {
            return new WP_Error(
                'empty_revision',
                __('Please describe what to revise.', 'fc-helper'),
                array(
                    'prompt_sent'   => array(),
                    'response_time' => null,
                    'http_status'   => null,
                    'model'         => self::MODEL,
                    'usage'         => array(),
                )
            );
        }

        $messages = array(
            array(
                'role'    => 'system',
                'content' => self::build_system_prompt($theme) . "\n\nWhen revising: apply the user's request precisely. Return the complete updated HTML document only — every section still in full, never truncated or summarized. Preserve structure unless the user asks to change it. Output only raw HTML starting with <div and ending with </div>.",
            ),
            array(
                'role'    => 'assistant',
                'content' => $html,
            ),
            array(
                'role'    => 'user',
                'content' => "Apply this revision to the HTML above. Return the full updated HTML only (no markdown, no code fences):\n\n" . $revision_request,
            ),
        );
        return self::execute_chat($messages);
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed>                             $options Optional: max_tokens (int), temperature (float), raw_content (bool).
     * @return array<string, mixed>|WP_Error
     */
    private static function execute_chat($messages, $options = array()) {
        $hub = katahdin_ai_hub();
        $api = $hub->api_manager;

        $max_tokens  = isset($options['max_tokens']) ? (int) $options['max_tokens'] : 16000;
        $temperature = isset($options['temperature']) ? (float) $options['temperature'] : 0.65;

        $data = array(
            'model'       => self::MODEL,
            'messages'    => $messages,
            'max_tokens'  => $max_tokens,
            'temperature' => $temperature,
        );

        $t0       = microtime(true);
        $response = $api->make_call(
            self::PLUGIN_ID,
            'chat/completions',
            $data
        );
        $elapsed_ms = (int) round((microtime(true) - $t0) * 1000);

        if (is_wp_error($response)) {
            $edata  = $response->get_error_data();
            $status = is_array($edata) && isset($edata['status']) ? (int) $edata['status'] : 0;
            return new WP_Error(
                $response->get_error_code(),
                $response->get_error_message(),
                array(
                    'prompt_sent'   => $messages,
                    'response_time' => $elapsed_ms,
                    'http_status'   => $status,
                    'model'         => self::MODEL,
                    'usage'         => array(),
                )
            );
        }

        $usage   = isset($response['usage']) && is_array($response['usage']) ? $response['usage'] : array();
        $content = $response['choices'][0]['message']['content'] ?? '';
        if (!is_string($content) || $content === '') {
            return new WP_Error(
                'empty_response',
                __('The AI returned an empty response.', 'fc-helper'),
                array(
                    'prompt_sent'   => $messages,
                    'response_time' => $elapsed_ms,
                    'http_status'   => 200,
                    'model'         => self::MODEL,
                    'usage'         => $usage,
                )
            );
        }

        $base = array(
            'messages'         => $messages,
            'model'            => self::MODEL,
            'usage'            => $usage,
            'response_time_ms' => $elapsed_ms,
            'http_status'      => 200,
        );
        if (!empty($options['raw_content'])) {
            $base['content'] = trim($content);
            return $base;
        }
        $base['html'] = self::strip_wrappers($content);
        return $base;
    }

    /**
     * @param string $content
     * @return string
     */
    public static function strip_wrappers($content) {
        $content = trim($content);
        if (preg_match('/^```(?:html)?\s*\R(.*?)\R```\s*$/is', $content, $m)) {
            return trim($m[1]);
        }
        if (strpos($content, '```') === 0) {
            $content = preg_replace('/^```(?:html)?\s*\R?/i', '', $content);
            $content = preg_replace('/\R```\s*$/', '', $content);
        }
        return trim($content);
    }

    /**
     * Accessible label color for text on a solid hex background (matches admin swatch logic).
     *
     * @param string $bg_hex Background color with or without #.
     * @return string
     */
    private static function contrast_label_for_bg($bg_hex) {
        $hex = ltrim((string) $bg_hex, '#');
        if (strlen($hex) !== 6) {
            return '#ffffff';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $l = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
        return $l > 0.55 ? '#1c1917' : '#ffffff';
    }

    /**
     * Whether accent is bright enough to use as text on dark (primary) backgrounds.
     *
     * @param string $hex Color with or without #.
     * @return bool
     */
    private static function accent_is_readable_on_dark(string $hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return true;
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
        return $luminance > 0.25;
    }

    /**
     * @param array<string, string> $theme
     */
    private static function build_system_prompt($theme) {
        $primary   = $theme['primary'] ?? $theme['bg_dark'] ?? '';
        $mid       = $theme['bg_mid'] ?? '';
        $grad_end  = $theme['bg_gradient_end'] ?? '';
        $accent    = $theme['accent'] ?? $theme['gold'] ?? '';
        $text_on_accent = self::contrast_label_for_bg($accent);
        $gold_dark = $theme['gold_dark'] ?? '';
        $text_light = $theme['text_light'] ?? '';
        $accent_on_dark = self::accent_is_readable_on_dark($accent) ? $accent : $text_light;
        $text_muted = $theme['text_muted'] ?? '';
        $bg_cream   = $theme['bg_cream'] ?? '';
        $bg_warm    = $theme['bg_warm'] ?? '';
        $border_warm = $theme['border_warm'] ?? '';

        $prompt = <<<'FC_HELPER_SYSTEM'
You are an expert direct-response copywriter and senior front-end developer. Your job is to produce complete, beautifully styled, high-converting HTML product description pages for online piano courses.

CRITICAL OUTPUT RULES:
- Output ONLY raw HTML. No markdown. No code fences. No explanation. No comments before or after the HTML.
- Every style must be inline. No <style> tags. No class names. No external CSS.
- The output must be a single continuous block of HTML starting with <div and ending with </div>.
- You must never truncate, summarize, abbreviate, or partially deliver any section. Write every required section in full with complete, final copy — no shortcuts, no "TBD", no ellipses standing in for text, no phrases like "continued below" or "[rest omitted]".
- Do not skip or collapse multi-paragraph sections; each paragraph and list item must be written out entirely in one response. If a section calls for three paragraphs or eight bullets, output all of them in full.
- Do not truncate. Do not summarize sections. The page must be complete and publish-ready in a single output.

COPY WRITING RULES:
- Write in a direct, confident, no-hype tone. No exclamation marks.
- Lead with the student's transformation, not the product features.
- Each paragraph must be 2-4 complete sentences. No one-liners.
- The "What You'll Learn" section must have exactly 8 items, written as outcomes the student will achieve, not feature descriptions.
- Write copy that is specific to the product title and description provided. Do not use generic filler phrases.
- If "Additional Notes / AI Instructions" appear in the user message, follow them faithfully without contradicting the product details.
- When the user message includes chapter transcripts, ground your marketing copy in them; do not state teaching details that contradict those transcripts.

DESIGN SYSTEM — follow this exactly:

OUTER WRAPPER: The outermost <div> must have: style="font-family:Georgia,serif; background:#ffffff;"

SECTION ORDER — always output sections 1, 3, 4, 5, 5b, and 6. Output section 2 only when the user provided a Sample Video URL; if they did not, omit section 2 entirely. Output section 4b (COURSE CHAPTERS) only when the user message contains a block beginning with ---LESSON DATA FROM ACADEMY LESSON MANAGER---; if no lesson data is present, omit section 4b entirely.

1. HERO BANNER
   Background: linear-gradient(135deg, {primary} 0%, {mid} 50%, {primary} 100%)
   Padding: 60px 40px, text-align center
   - Gold pill badge: display inline-block, background {accent}, color {text_on_accent}, font-family Arial, font-size 12px, font-weight bold, letter-spacing 3px, text-transform uppercase, padding 6px 16px, border-radius 20px, margin-bottom 20px — text derived from product (e.g. "Piano Course") without inventing duration or stats not in the user message.
   - H1: Georgia, #ffffff, 42px, line-height 1.2, margin 0 0 16px, font-weight bold — must use the exact product title from the user message.
   - Subtitle P: color {accent_on_dark}, Arial, 20px, font-weight 300, margin 0 0 12px, letter-spacing 1px.
   - Description P: color {text_muted}, Arial, 16px, line-height 1.6, max-width 620px, display block, margin 0 auto, text-align center — based on the user description.

2. SAMPLE VIDEO (only if a sample video URL is provided):
   - Output EXACTLY this block, substituting {video_url} with the provided URL verbatim:

     <div data-sample-video="{video_url}" style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden; background:#000; margin:0 0 24px;"></div>

   - Do NOT detect the URL type. Do NOT emit <video>, <iframe>, <source>, <script>, or <noscript> tags. Do NOT add data-hls, src, or poster attributes. The shop site detects the URL type at runtime and renders the correct player.
   - Accepted URL formats include: youtube.com/watch?v=..., youtu.be/..., youtube.com/shorts/..., vimeo.com/..., player.vimeo.com/video/..., *.b-cdn.net/*.m3u8, and any .m3u8/.mp4/.webm URL.
   - If no sample video URL is provided, omit this section entirely.

3. INTRO / HOOK
   Background #ffffff, padding 50px 40px
   H2: Georgia 30px {primary}, transformation-focused opening
   3 paragraphs: Arial 16px line-height 1.8 color #444

4. WHAT YOU'LL LEARN
   Background #ffffff, padding 50px 40px
   H2: Georgia 28px {primary}, text-align center, margin-bottom 30px
   2-column grid: display:grid; grid-template-columns:1fr 1fr; gap:16px. Each of 8 items: display:flex; align-items:center; gap:12px. Badge: background:{accent}; color:{text_on_accent}; width:22px; height:22px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-family:Arial; font-size:13px; font-weight:bold; flex-shrink:0. Text: font-family:Arial; font-size:15px; color:#444.

4b. COURSE CHAPTERS (only when LESSON DATA is present in user message)
   Background: {bg_warm}, padding 50px 40px
   H2: Georgia 28px {primary}, text-align center, margin-bottom 8px — use "Course Chapters" as the heading
   Subheading P: Arial 14px #666, text-align center, margin-bottom 32px — e.g. "Here is exactly what you will learn in each chapter:"
   Each chapter row wrapper: display:flex; align-items:flex-start; gap:16px; padding:14px 0; border-bottom:1px solid {border_warm}. Last row has no border-bottom.
     - Timestamp badge: display:inline-block; background:{accent}; color:{text_on_accent}; font-family:Arial; font-size:11px; font-weight:bold; padding:3px 10px; border-radius:12px; white-space:nowrap; min-width:72px; text-align:center
     - Chapter title: font-family:Georgia; font-size:16px; color:{primary}; font-weight:bold; flex:1
     - Duration: font-family:Arial; font-size:13px; color:#888; white-space:nowrap
   Total row: display:flex; align-items:center; gap:16px; padding:14px 0; margin-top:4px; border-top:2px solid {accent}.
     - Left: display:inline-block; font-family:Arial; font-size:11px; font-weight:bold; color:{accent}; text-transform:uppercase; letter-spacing:1px — text: TOTAL
     - Right: font-family:Georgia; font-size:16px; font-weight:bold; color:{primary}; flex:1; text-align:right — calculated total time
   Use the exact chapter titles, start times, and durations from the LESSON DATA block. Do not invent or alter them. Calculate the total by summing all duration values from the LESSON DATA chapters. Show it in the format that drops leading zeros (e.g. '55:22' for under an hour, '1:32:23' for over an hour).

5. EVERYTHING THAT'S INCLUDED
   Background {primary}, padding 50px 40px
   H2: Georgia 28px #ffffff, text-align center, margin-bottom 34px
   2-column grid: display:grid; grid-template-columns:1fr 1fr; max-width:720px; margin:0 auto 40px; gap:20px. Each of 4 cards — no background, no border-radius, no padding wrapper:
     - Icon: inline SVG, 44x44, stroke currentColor, color {accent_on_dark}, margin-bottom:10px, display:block. Use the exact SVG strings below — do not alter paths, viewBox, stroke-width, or attributes. Output each SVG directly (no wrapping <span>).
     - Title div: font-family:Arial; font-size:16px; font-weight:bold; color:#ffffff; margin-bottom:6px
     - Body div: font-family:Arial; font-size:14px; line-height:1.75; color:{text_light}
   Use these four cards in this order, with the exact icon + title pairings below. For each card, output the SVG first (with style="color:{accent_on_dark}; margin-bottom:10px; display:block;"), then the Title div, then the Body div.

   Card 1 — Structured Lessons:
   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="44" height="44" style="color:{accent_on_dark}; margin-bottom:10px; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>

   Card 2 — Practice Material:
   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="44" height="44" style="color:{accent_on_dark}; margin-bottom:10px; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>

   Card 3 — Notation & Resources:
   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="44" height="44" style="color:{accent_on_dark}; margin-bottom:10px; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" /></svg>

   Card 4 — Download Access:
   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="44" height="44" style="color:{accent_on_dark}; margin-bottom:10px; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>

   The Title div and Body div specs (Arial 16px bold #ffffff / Arial 14px {text_light}) remain unchanged — only the icon has changed from an emoji to an SVG.

   After the items grid, download note (max-width:680px; margin:0 auto): label div Arial 12px {accent_on_dark} bold uppercase letter-spacing:2px text "Download Note"; P Arial 14px {text_light} line-height:1.75 with the standard download ownership copy: "After purchase, you'll have 1 full year to download all videos to your device. Once downloaded, you own them outright — no ongoing membership, no content that expires or disappears."

5b. HOW IT WORKS
   Background: {bg_warm}, padding 50px 40px
   H2: Georgia 28px {primary}, text-align center, margin-bottom 8px — use "How It Works" as the heading
   Subheading P: Arial 14px #666, text-align center, margin-bottom 32px — use "Three simple steps from purchase to practice"
   3-column grid: display:grid; grid-template-columns:repeat(3, 1fr); gap:24px; max-width:960px; margin:0 auto. Each card has no background, no border:
     - Number badge: width:48px; height:48px; border-radius:50%; background:{accent}; color:{text_on_accent}; font-family:Georgia; font-size:22px; font-weight:bold; display:flex; align-items:center; justify-content:center; margin-bottom:16px
     - Title div: font-family:Arial; font-size:16px; font-weight:bold; color:{primary}; margin-bottom:8px
     - Body div: font-family:Arial; font-size:14px; line-height:1.75; color:#444
   Output exactly these three steps in this order with these exact numbers, titles, and copy (do not reword):
     Step 1 — badge "1" — Title: "Buy the Program" — Body: "Complete your purchase and get instant access. No waiting, no account setup headaches — you're in immediately."
     Step 2 — badge "2" — Title: "Download the Zip File" — Body: "You'll receive immediate download access to a single zip file containing everything included in this course."
     Step 3 — badge "3" — Title: "Open and Start Learning" — Body: "Inside the zip you'll find all video lessons, sheet music, and practice resources — organized and ready to use on your device."

6. BOTTOM CTA
   Background: linear-gradient(135deg, {mid} 0%, {primary} 100%)
   Padding: 60px 40px, text-align center
   Small pill badge like hero (background {accent}, text {text_on_accent})
   H2: Georgia 34px #ffffff
   P: Arial 16px {text_light}, max-width 560px margin 0 auto 30px
   Trust line Arial 13px {accent_on_dark}: "✓ Instant access after purchase · ✓ 1-year download window · ✓ Lifetime ownership of all files"
   After the trust line, output exactly this shortcode on its own line (no wrapping div, no styling, no link tag):
   [fc_buy_buttons]
   This shortcode is rendered server-side and outputs the Add to Cart and Buy Now buttons with full functionality. Do not wrap it in any other element. Do not replace it with a plain <a href="#"> link.

Forbidden in output: stats bar, level/weeks/counts, about-the-song section, practice-actions hero, bonus section, sheet-music image blocks, testimonials, YouTube embeds, unless Additional Notes explicitly require them.

COLOR TOKEN SUBSTITUTION — use these hex values from the user message in all themed UI:
{primary}, {mid}, {gradient_end}, {accent}, {accent_on_dark}, {text_on_accent}, {gold_dark}, {text_light}, {text_muted}, {bg_cream}, {bg_warm}, {border_warm}

FC_HELPER_SYSTEM;

        $repl = array(
            '{primary}'          => $primary,
            '{mid}'              => $mid,
            '{gradient_end}'     => $grad_end,
            '{accent}'           => $accent,
            '{accent_on_dark}'   => $accent_on_dark,
            '{text_on_accent}'   => $text_on_accent,
            '{gold_dark}'        => $gold_dark,
            '{text_light}'       => $text_light,
            '{text_muted}'       => $text_muted,
            '{bg_cream}'         => $bg_cream,
            '{bg_warm}'          => $bg_warm,
            '{border_warm}'      => $border_warm,
        );

        return str_replace(array_keys($repl), array_values($repl), $prompt);
    }

    /**
     * @param int $sec
     * @return string
     */
    private static function format_duration_for_prompt($sec) {
        $sec = max(0, (int) $sec);
        $h   = (int) floor($sec / 3600);
        $m   = (int) floor(($sec % 3600) / 60);
        $s   = (int) ($sec % 60);
        if ($h > 0) {
            return sprintf('%d:%02d:%02d', $h, $m, $s);
        }
        return sprintf('%d:%02d', $m, $s);
    }

    /**
     * @param array<string, mixed>|null $lesson_data
     */
    private static function build_lesson_data_user_block($lesson_data) {
        if (!is_array($lesson_data) || empty($lesson_data['lesson']) || !is_array($lesson_data['lesson'])) {
            return '';
        }
        $lesson   = $lesson_data['lesson'];
        $chapters = isset($lesson_data['chapters']) && is_array($lesson_data['chapters']) ? $lesson_data['chapters'] : array();
        $title    = isset($lesson['title']) ? (string) $lesson['title'] : '';
        $desc     = isset($lesson['description']) ? (string) $lesson['description'] : '';
        $count    = count($chapters);

        $lines   = array();
        $lines[] = '---LESSON DATA FROM ACADEMY LESSON MANAGER---';
        $lines[] = '';
        $lines[] = 'Lesson Title: ' . $title;
        $lines[] = 'Lesson Description: ' . $desc;
        $lines[] = '';
        $lines[] = 'Chapters (' . $count . ' total):';

        foreach ($chapters as $ch ) {
            if (!is_array($ch)) {
                continue;
            }
            $st  = isset($ch['start_time']) ? (string) $ch['start_time'] : '00:00:00';
            $ct  = isset($ch['title']) ? (string) $ch['title'] : '';
            $dur = isset($ch['duration_seconds']) ? (int) $ch['duration_seconds'] : 0;
            $lines[] = '[' . $st . '] ' . $ct . ' (duration: ' . self::format_duration_for_prompt($dur) . ')';
        }

        $lines[] = '';
        $lines[] = 'Chapter Transcripts:';

        $idx = 0;
        foreach ($chapters as $ch ) {
            if (!is_array($ch)) {
                continue;
            }
            ++$idx;
            $ct = isset($ch['title']) ? (string) $ch['title'] : '';
            $lines[] = '--- Chapter ' . $idx . ': ' . $ct . ' ---';
            $trans     = $ch['transcript'] ?? null;
            $lines[] = (is_string($trans) && $trans !== '') ? $trans : '(no transcript available)';
            $lines[] = '';
        }

        $lines[] = '---END LESSON DATA---';
        return implode("\n", $lines);
    }

    private static function build_user_prompt($raw, $theme) {
        $t = function ( $key, $default = '(not provided)' ) use ( $raw ) {
            if (!isset($raw[ $key ])) {
                return $default;
            }
            $v = $raw[ $key ];
            if (is_int($v) || is_float($v)) {
                return (string) $v;
            }
            if (!is_string($v) || trim($v) === '') {
                return $default;
            }
            return trim($v);
        };

        $primary      = $theme['primary'] ?? $theme['bg_dark'] ?? '';
        $mid          = $theme['bg_mid'] ?? '';
        $gradient_end = $theme['bg_gradient_end'] ?? '';
        $accent          = $theme['accent'] ?? $theme['gold'] ?? '';
        $text_on_accent  = self::contrast_label_for_bg($accent);
        $gold_dark       = $theme['gold_dark'] ?? '';
        $text_light      = $theme['text_light'] ?? '';
        $accent_on_dark  = self::accent_is_readable_on_dark($accent) ? $accent : $text_light;
        $text_muted   = $theme['text_muted'] ?? '';
        $bg_cream     = $theme['bg_cream'] ?? '';
        $bg_warm      = $theme['bg_warm'] ?? '';
        $border_warm  = $theme['border_warm'] ?? '';

        $lines   = array();
        $lines[] = 'Generate a complete product description page HTML for the following course.';
        $lines[] = 'Follow the design system and section order exactly as specified in your instructions.';
        $lines[] = 'Write full, specific copy — do not use placeholder text.';
        $lines[] = '';
        $lines[] = 'PRODUCT DETAILS:';
        $lines[] = 'Title: ' . $t('product_title');
        $lines[] = 'Description: ' . $t('description');
        $lines[] = 'Sample Video URL: ' . $t('sample_video_url');
        $lines[] = 'Sample Video Splash Image URL: ' . $t('sample_video_splash_url');
        $lines[] = 'Additional Notes / AI Instructions: ' . $t('additional_notes');
        $lines[] = '';
        $lesson_block = self::build_lesson_data_user_block(isset($raw['lesson_data']) ? $raw['lesson_data'] : null);
        if ($lesson_block !== '') {
            $lines[] = $lesson_block;
            $lines[] = '';
        }
        $lines[] = 'THEME COLORS:';
        $lines[] = 'Primary background: ' . $primary;
        $lines[] = 'Mid background: ' . $mid;
        $lines[] = 'Gradient end: ' . $gradient_end;
        $lines[] = 'Accent/Gold: ' . $accent;
        $lines[] = 'Accent on Dark Text: ' . $accent_on_dark;
        $lines[] = 'Use this color (not Accent/Gold) when the accent is used as text color on dark primary-colored backgrounds.';
        $lines[] = 'Text on Accent: ' . $text_on_accent;
        $lines[] = 'Gold Dark: ' . $gold_dark;
        $lines[] = 'Text Light: ' . $text_light;
        $lines[] = 'Text Muted: ' . $text_muted;
        $lines[] = 'Cream Background: ' . $bg_cream;
        $lines[] = 'Warm Background: ' . $bg_warm;
        $lines[] = 'Warm Border: ' . $border_warm;
        $lines[] = '';
        $lines[] = 'Text on Accent: use this color for ALL text or icons that sit directly on an accent-colored ({accent}) background.';
        $lines[] = '';
        $lines[] = 'Replace all theme placeholders in the HTML with these exact hex codes.';
        $lines[] = 'Output only the HTML. Start immediately with <div.';

        return implode("\n", $lines);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function extract_youtube_id($url) {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        $patterns = array(
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
        );
        foreach ($patterns as $p) {
            if (preg_match($p, $url, $m)) {
                return $m[1];
            }
        }
        return '';
    }
}
