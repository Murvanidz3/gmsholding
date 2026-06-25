<?php
/**
 * Site & Branding — single-form editor (Phase 5).
 * Controls global identity used by header, footer AND the contact section.
 * Writes:
 *   $content['site']  -> name, email, phone, address, hours, social{}
 *   $content['contact']['cards'] -> re-synced (pin/phone/mail/clock) so the
 *   contact section never drifts from the global values.
 */
require_once __DIR__ . '/../includes/layout.php';
admin_require_login();

$SELF = admin_url('modules/branding.php');

/* Social networks we expose globally (key => label). Order = render order. */
$SOCIALS = [
    'facebook'  => 'Facebook',
    'instagram' => 'Instagram',
    'linkedin'  => 'LinkedIn',
    'youtube'   => 'YouTube',
    'twitter'   => 'Twitter / X',
];

/* -------------------------------------------------------------------------
   POST (PRG)
------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        gms_flash('error', 'Security token expired. Please try again.');
        header('Location: ' . $SELF, true, 303); exit;
    }

    $content = gms_load_content();
    $site    = $content['site'] ?? [];

    $name    = trim($_POST['brand_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $hours   = trim($_POST['hours'] ?? '');

    if ($name === '') {
        gms_flash('error', 'Brand name cannot be empty.');
        header('Location: ' . $SELF, true, 303); exit;
    }

    // Social: keep only non-empty URLs, preserve defined order
    $social = [];
    foreach ($SOCIALS as $key => $_label) {
        $url = trim($_POST['social_' . $key] ?? '');
        if ($url !== '') $social[$key] = $url;
    }

    // --- Update global site ---
    $site['name']    = $name;
    $site['email']   = $email;
    $site['phone']   = $phone;
    $site['address'] = $address;
    $site['hours']   = $hours;
    $site['social']  = $social;
    $content['site'] = $site;

    // --- Re-sync contact section cards so they mirror the globals ---
    $tel = $phone !== '' ? 'tel:' . preg_replace('/\s+/', '', $phone) : '';
    $mail = $email !== '' ? 'mailto:' . $email : '';
    if (isset($content['contact']['cards']) && is_array($content['contact']['cards'])) {
        foreach ($content['contact']['cards'] as &$card) {
            switch ($card['icon'] ?? '') {
                case 'pin':   $card['value'] = $address; $card['href'] = ''; break;
                case 'phone': $card['value'] = $phone;   $card['href'] = $tel; break;
                case 'mail':  $card['value'] = $email;   $card['href'] = $mail; break;
                case 'clock': $card['value'] = $hours;   $card['href'] = ''; break;
            }
        }
        unset($card);
    }

    if (gms_save_content($content)) {
        gms_flash('success', 'Branding updated. Header, footer & contact are in sync.');
    } else {
        gms_flash('error', 'Could not write to data file. Check folder permissions.');
    }
    header('Location: ' . $SELF, true, 303); exit;
}

/* -------------------------------------------------------------------------
   GET render
------------------------------------------------------------------------- */
$content = gms_load_content();
$site    = $content['site'] ?? [];
$social  = $site['social'] ?? [];
$csrf    = admin_csrf_token();

// Working hours default: prefer site.hours, fall back to the contact clock card
$hours = $site['hours'] ?? '';
if ($hours === '' && !empty($content['contact']['cards'])) {
    foreach ($content['contact']['cards'] as $c) {
        if (($c['icon'] ?? '') === 'clock') { $hours = $c['value'] ?? ''; break; }
    }
}

$label = "block font-heading uppercase text-xs tracking-wider2 text-muted mb-2";
$input = "w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors";

gms_admin_header('Site & Branding', 'dashboard');
?>

<div class="mb-8">
    <a href="<?= admin_url('index.php') ?>" class="text-xs uppercase tracking-wider2 text-muted hover:text-primary transition-colors">&larr; Dashboard</a>
    <h1 class="text-section mt-2">Site &amp; Branding</h1>
    <p class="text-muted mt-1">Global identity used across the header, footer and contact section.</p>
</div>

<form method="post" action="<?= $SELF ?>" class="space-y-8 max-w-4xl">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

    <!-- CARD: General -->
    <section class="bg-dark-800 border border-line p-7 lg:p-8">
        <div class="flex items-center gap-3 mb-6">
            <span class="grid place-items-center w-9 h-9 bg-primary/10 text-primary"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10M4 17h7"/></svg></span>
            <h2 class="text-lg">General</h2>
        </div>
        <div>
            <label class="<?= $label ?>">Brand / Site Name</label>
            <input name="brand_name" type="text" required value="<?= e($site['name'] ?? '') ?>" placeholder="GMS" class="<?= $input ?>">
            <p class="text-muted text-xs mt-2">Appears in the logo, page titles and footer.</p>
        </div>
    </section>

    <!-- CARD: Contact -->
    <section class="bg-dark-800 border border-line p-7 lg:p-8">
        <div class="flex items-center gap-3 mb-6">
            <span class="grid place-items-center w-9 h-9 bg-primary/10 text-primary"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18v14H3zM3 7l9 6 9-6"/></svg></span>
            <h2 class="text-lg">Contact Info</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="<?= $label ?>">Email</label>
                <input name="email" type="email" value="<?= e($site['email'] ?? '') ?>" placeholder="hello@gms.com" class="<?= $input ?>">
            </div>
            <div>
                <label class="<?= $label ?>">Phone</label>
                <input name="phone" type="text" value="<?= e($site['phone'] ?? '') ?>" placeholder="+1 800 123 4567" class="<?= $input ?>">
            </div>
            <div class="sm:col-span-2">
                <label class="<?= $label ?>">Address</label>
                <input name="address" type="text" value="<?= e($site['address'] ?? '') ?>" placeholder="24 Industrial Ave, New York, USA" class="<?= $input ?>">
            </div>
            <div class="sm:col-span-2">
                <label class="<?= $label ?>">Working Hours</label>
                <input name="hours" type="text" value="<?= e($hours) ?>" placeholder="Mon – Fri: 8:00 – 18:00" class="<?= $input ?>">
            </div>
        </div>
        <p class="text-muted text-xs mt-4">These auto-update the contact cards (phone & email become clickable links).</p>
    </section>

    <!-- CARD: Socials -->
    <section class="bg-dark-800 border border-line p-7 lg:p-8">
        <div class="flex items-center gap-3 mb-6">
            <span class="grid place-items-center w-9 h-9 bg-primary/10 text-primary"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8a3 3 0 10-2.8-4M6 12a3 3 0 100 0m12 4a3 3 0 10-2.8 4M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg></span>
            <h2 class="text-lg">Social Links</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            <?php foreach ($SOCIALS as $key => $lbl): ?>
            <div>
                <label class="<?= $label ?>"><?= e($lbl) ?></label>
                <input name="social_<?= e($key) ?>" type="url" value="<?= e($social[$key] ?? '') ?>" placeholder="https://…" class="<?= $input ?>">
            </div>
            <?php endforeach; ?>
        </div>
        <p class="text-muted text-xs mt-4">Leave a field blank to hide that network from the header &amp; footer.</p>
    </section>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <button type="submit" class="btn-primary">Save Changes</button>
        <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn-outline">Preview Site ↗</a>
    </div>
</form>

<?php gms_admin_footer(); ?>
