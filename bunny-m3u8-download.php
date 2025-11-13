<?php
/**
 * Bunny m3u8 Download Proof of Concept
 * 
 * Downloads an m3u8 playlist from Bunny CDN and extracts the highest quality stream
 */

// Configuration
$m3u8_url = 'https://vz-0696d3da-4b7.b-cdn.net/016b994e-47f8-478c-ac69-36771a189bca/playlist.m3u8';
$script_dir = __DIR__;
$master_playlist_file = $script_dir . '/playlist.m3u8';
$stream_playlist_file = $script_dir . '/stream.m3u8';

echo "Downloading master m3u8 playlist from Bunny CDN...\n";
echo "URL: {$m3u8_url}\n\n";

// Function to download with headers
function download_with_headers($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Referer: https://support.jazzedge.com/',
        'Accept: */*',
        'Accept-Language: en-US,en;q=0.9'
    ));
    
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    return array(
        'content' => $content,
        'http_code' => $http_code,
        'error' => $curl_error
    );
}

// Download master playlist
$result = download_with_headers($m3u8_url);

if ($result['error']) {
    die("ERROR: Failed to download master playlist. cURL error: {$result['error']}\n");
}

if ($result['http_code'] !== 200) {
    echo "ERROR: Failed to download master playlist. HTTP code: {$result['http_code']}\n";
    if (!empty($result['content'])) {
        echo "Response body: " . substr($result['content'], 0, 500) . "\n";
    }
    echo "\nNote: 403 errors from Bunny CDN can be caused by:\n";
    echo "  - 'Block Direct URL File Access' setting requiring a valid referrer\n";
    echo "  - Token authentication being enabled\n";
    echo "  - Allowed domains restrictions\n";
    echo "  - 'Enable Direct Play' being disabled in Bunny Stream settings\n";
    exit(1);
}

if (empty($result['content'])) {
    die("ERROR: Downloaded master playlist is empty.\n");
}

// Save master playlist
if (file_put_contents($master_playlist_file, $result['content']) === false) {
    die("ERROR: Failed to save master playlist to {$master_playlist_file}\n");
}

echo "✓ Master playlist downloaded\n";
echo "  File size: " . number_format(strlen($result['content'])) . " bytes\n\n";

// Parse master playlist to find highest quality stream
echo "Parsing master playlist to find highest quality stream...\n";
$lines = explode("\n", $result['content']);
$streams = array();
$current_bandwidth = 0;
$current_url = '';

foreach ($lines as $line) {
    $line = trim($line);
    
    // Check for stream info line with bandwidth
    if (preg_match('/#EXT-X-STREAM-INF:.*BANDWIDTH=(\d+)/', $line, $matches)) {
        $current_bandwidth = intval($matches[1]);
    }
    // Check for URL line (not a comment)
    elseif (!empty($line) && !preg_match('/^#/', $line) && $current_bandwidth > 0) {
        $current_url = $line;
        $streams[] = array(
            'bandwidth' => $current_bandwidth,
            'url' => $current_url
        );
        $current_bandwidth = 0;
    }
}

if (empty($streams)) {
    die("ERROR: Could not find any streams in master playlist.\n");
}

// Sort by bandwidth (highest first)
usort($streams, function($a, $b) {
    return $b['bandwidth'] - $a['bandwidth'];
});

$highest_quality = $streams[0];
echo "  Found " . count($streams) . " quality streams\n";
echo "  Selected highest quality: {$highest_quality['bandwidth']} bps\n";
echo "  Stream URL: {$highest_quality['url']}\n\n";

// Construct full URL for the stream playlist
$base_url = dirname($m3u8_url) . '/';
$stream_url = $base_url . $highest_quality['url'];

echo "Downloading stream playlist...\n";
echo "URL: {$stream_url}\n\n";

// Download the stream playlist
$stream_result = download_with_headers($stream_url);

if ($stream_result['error']) {
    die("ERROR: Failed to download stream playlist. cURL error: {$stream_result['error']}\n");
}

if ($stream_result['http_code'] !== 200) {
    die("ERROR: Failed to download stream playlist. HTTP code: {$stream_result['http_code']}\n");
}

if (empty($stream_result['content'])) {
    die("ERROR: Downloaded stream playlist is empty.\n");
}

// Save stream playlist
if (file_put_contents($stream_playlist_file, $stream_result['content']) === false) {
    die("ERROR: Failed to save stream playlist to {$stream_playlist_file}\n");
}

echo "✓ Stream playlist downloaded and saved to: {$stream_playlist_file}\n";
echo "  File size: " . number_format(strlen($stream_result['content'])) . " bytes\n\n";

// Convert to MP4 using ffmpeg
$output_file = $script_dir . '/output.mp4';
echo "Converting HLS stream to MP4 using ffmpeg...\n";
echo "This may take a while depending on video length...\n";
echo "Using stream URL: {$stream_url}\n\n";

// Find ffmpeg executable (common macOS locations)
$ffmpeg_paths = array(
    '/opt/homebrew/bin/ffmpeg',  // Apple Silicon Homebrew (most common)
    '/usr/local/bin/ffmpeg',      // Intel Mac Homebrew
    '/usr/bin/ffmpeg',
    'ffmpeg' // fallback to PATH
);

$ffmpeg = null;
foreach ($ffmpeg_paths as $path) {
    // For absolute paths, check if file exists
    if (strpos($path, '/') === 0) {
        if (file_exists($path)) {
            // Try to run it to verify it works
            $test_cmd = escapeshellarg($path) . " -version 2>&1";
            $test_result = @shell_exec($test_cmd);
            
            if (!empty($test_result) && (strpos($test_result, 'ffmpeg') !== false || strpos($test_result, 'version') !== false)) {
                $ffmpeg = $path;
                break;
            }
        }
    } else {
        // For PATH-based, try which command
        $which_result = @shell_exec("which " . escapeshellarg($path) . " 2>&1");
        if (!empty($which_result) && strpos($which_result, '/') !== false) {
            $ffmpeg = trim($which_result);
            break;
        }
    }
}

if ($ffmpeg === null) {
    // Last resort: try the known path directly
    $known_path = '/opt/homebrew/bin/ffmpeg';
    if (file_exists($known_path)) {
        $ffmpeg = $known_path;
    } else {
        die("ERROR: ffmpeg not found. Please install ffmpeg with: brew install ffmpeg\n");
    }
}

echo "Using ffmpeg at: {$ffmpeg}\n\n";

// Use the original stream URL (not local file) so ffmpeg can resolve relative segment paths
// -headers: add HTTP headers for authentication (Referer required for Bunny CDN)
// -i: input URL
// -c copy: copy codecs without re-encoding (faster, preserves quality)
// -y: overwrite output file if it exists
// 2>&1: capture both stdout and stderr
$headers = "Referer: https://support.jazzedge.com/\r\nUser-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36";
$ffmpeg_cmd = $ffmpeg . " -headers " . escapeshellarg($headers) . " -i " . escapeshellarg($stream_url) . " -c copy -y " . escapeshellarg($output_file) . " 2>&1";

echo "Running: ffmpeg -i [stream_url] -c copy -y output.mp4\n";
$ffmpeg_output = shell_exec($ffmpeg_cmd);

// Check if output file was created
if (!file_exists($output_file)) {
    echo "\nERROR: ffmpeg conversion failed. Output file was not created.\n";
    if (!empty($ffmpeg_output)) {
        echo "ffmpeg output:\n{$ffmpeg_output}\n";
    }
    exit(1);
}

$output_size = filesize($output_file);
echo "\n✓ Conversion complete!\n";
echo "  Output file: {$output_file}\n";
echo "  File size: " . number_format($output_size) . " bytes (" . number_format($output_size / 1024 / 1024, 2) . " MB)\n\n";

echo "Done! The MP4 file is ready for transcription.\n";

