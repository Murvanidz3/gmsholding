<?php
/**
 * Global site config — Phase 4 (flat-file).
 * Content now lives in /data/site_content.json (edited by the admin panel).
 * This file loads that JSON and exposes the SAME PHP variables the sections
 * already consume, so no section markup needs to change.
 */

if (!defined('BASE_URL')) {
    // Adjust to match your local server path, e.g. '' for php -S localhost:8000
    define('BASE_URL', '');
}
if (!defined('DATA_FILE')) {
    define('DATA_FILE', dirname(__DIR__) . '/data/site_content.json');
}

function asset($path) { return BASE_URL . '/assets/' . ltrim($path, '/'); }

/**
 * Read + decode the content store. Returns an associative array.
 * On any failure returns [] so the front-end degrades gracefully
 * (sections simply render nothing) instead of fataling.
 */
function gms_load_content() {
    static $cache = null;
    if ($cache !== null) return $cache;

    // Self-seed: on a fresh server the live store (data/) isn't deployed, so
    // create it from the bundled includes/seed.json. If data/ isn't writable,
    // fall back to reading the seed directly so the front-end still renders.
    if (!is_readable(DATA_FILE)) {
        $seed = __DIR__ . '/seed.json';
        if (is_readable($seed)) {
            $dir = dirname(DATA_FILE);
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            @copy($seed, DATA_FILE);
            if (!is_readable(DATA_FILE)) {
                $raw  = file_get_contents($seed);
                $data = json_decode($raw, true);
                return $cache = (json_last_error() === JSON_ERROR_NONE && is_array($data)) ? $data : [];
            }
        }
    }

    if (!is_readable(DATA_FILE)) { return $cache = []; }
    $raw = file_get_contents(DATA_FILE);
    $data = json_decode($raw, true);
    return $cache = (json_last_error() === JSON_ERROR_NONE && is_array($data)) ? $data : [];
}

/**
 * Persist the content store (used by the admin panel in Phase 5).
 * Writes atomically via a temp file + rename to avoid partial writes.
 */
function gms_save_content(array $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) return false;
    $tmp = DATA_FILE . '.tmp';
    if (file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    return rename($tmp, DATA_FILE);
}

/**
 * Secure image upload handler (admin only).
 * Validates the file is a real image (by content, not extension), enforces a
 * size cap, generates a safe unique name, moves it into /assets/img/uploads/,
 * and returns a root-relative web path (works from front-end and /admin/).
 * Returns null when no file was submitted or validation failed.
 */
function gms_upload_dir(): string { return dirname(__DIR__) . '/assets/img/uploads'; }

function gms_handle_upload(string $key, ?string &$error = null): ?string {
    if (empty($_FILES[$key]) || !is_array($_FILES[$key])) { return null; }
    $f = $_FILES[$key];
    $err = $f['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($err === UPLOAD_ERR_NO_FILE) { return null; }                  // nothing chosen
    if ($err !== UPLOAD_ERR_OK)      { $error = 'Upload failed (code ' . $err . ').'; return null; }
    if (($f['size'] ?? 0) <= 0 || $f['size'] > 8 * 1024 * 1024) { $error = 'Image must be 1 byte–8 MB.'; return null; }
    if (!is_uploaded_file($f['tmp_name'])) { $error = 'Invalid upload.'; return null; }

    // Validate by actual content (rejects disguised scripts).
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $info = @getimagesize($f['tmp_name']);
    if ($info === false || !isset($allowed[$info['mime']])) { $error = 'Only JPG, PNG, WEBP or GIF images are allowed.'; return null; }
    $ext = $allowed[$info['mime']];

    $dir = gms_upload_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) { $error = 'Upload folder is not writable.'; return null; }

    $name = 'img_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 6) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) { $error = 'Could not save the uploaded file.'; return null; }

    return BASE_URL . '/assets/img/uploads/' . $name;
}

/**
 * Service icon registry — SINGLE SOURCE OF TRUTH.
 * Inner SVG markup (paths only) keyed by name. Consumed by:
 *   - sections/services.php  (front-end render via gms_icon())
 *   - admin/modules/services.php (icon picker + list previews)
 * Add a new icon here and it is instantly available in both places.
 */
function gms_service_icons(): array {
    return [
        'building'  => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h2m-2 4h2m-2 4h2m2-8h2m-2 4h2m-2 4h2"/>',
        'ruler'     => '<path d="M3 17L17 3l4 4L7 21l-4-4z"/><path d="M7.5 12.5l2 2M11 9l2 2M14.5 5.5l2 2"/>',
        'crane'     => '<path d="M5 21h14M6 21V8m0 0L3 5m3 3h12l-3-3M9 8V4m0 0h6"/><path d="M9 4l8 4"/>',
        'helmet'    => '<path d="M3 16h18M5 16a7 7 0 0114 0M9 16V8a3 3 0 016 0v8M12 5V3"/>',
        'blueprint' => '<rect x="3" y="4" width="18" height="16" rx="1"/><path d="M3 9h6v11M9 4v5m6 0h6m-6 0v11m0-6h6"/>',
        'shield'    => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
    ];
}

/**
 * Unit-explorer status registry — SINGLE SOURCE OF TRUTH for availability
 * states. Colors are hex (applied inline) so Tailwind's purge never strips
 * them. Used by the front-end map fills and the admin status dropdowns.
 */
function gms_unit_statuses(): array {
    return [
        'available' => ['label' => 'Available', 'color' => '#22C55E'],
        'reserved'  => ['label' => 'Reserved',  'color' => '#F59E0B'],
        'sold'      => ['label' => 'Sold',      'color' => '#EF4444'],
    ];
}

/* ------------------------------------------------------------------
   Hydrate the variables the sections expect.
   Each falls back to [] if the key is missing from the JSON.
------------------------------------------------------------------ */
$content = gms_load_content();

$site               = $content['site']               ?? [];
$nav                = $content['nav']                 ?? [];
$hero_slides        = $content['hero_slides']         ?? [];
$about              = $content['about']               ?? [];
$services_intro     = $content['services_intro']      ?? [];
$services           = $content['services']            ?? [];
$projects_intro     = $content['projects_intro']      ?? [];
$projects           = $content['projects']            ?? [];
$stats_band         = $content['stats_band']          ?? [];
$stats              = $content['stats']               ?? [];
$testimonials_intro = $content['testimonials_intro']  ?? [];
$testimonials       = $content['testimonials']        ?? [];
$team_intro         = $content['team_intro']          ?? [];
$team               = $content['team']                ?? [];
$contact            = $content['contact']             ?? [];
$unit_explorer      = $content['unit_explorer']       ?? [];
