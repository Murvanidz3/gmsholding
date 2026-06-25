<?php
/**
 * Services — full CRUD editor (Phase 5).
 * Data: $content['services'] in /data/site_content.json
 * PRG + CSRF + atomic save, same pattern as hero.php.
 */
require_once __DIR__ . '/../includes/layout.php';
admin_require_login();

$SELF = admin_url('modules/services.php');

$ICONS = gms_service_icons(); // single source of truth (config.php)
function gms_admin_icon(array $icons, string $key): string {
    return '<svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' . ($icons[$key] ?? '') . '</svg>';
}

/* -------------------------------------------------------------------------
   POST (PRG)
------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        gms_flash('error', 'Security token expired. Please try again.');
        header('Location: ' . $SELF, true, 303); exit;
    }

    $content  = gms_load_content();
    $services = array_values($content['services'] ?? []);
    $action   = $_POST['action'] ?? '';
    $index    = isset($_POST['index']) && $_POST['index'] !== '' ? (int) $_POST['index'] : null;

    switch ($action) {

        case 'save':
            $icon = $_POST['icon'] ?? '';
            if (!isset($ICONS[$icon])) $icon = array_key_first($ICONS); // guard against bad keys
            $service = [
                'icon'  => $icon,
                'title' => trim($_POST['title'] ?? ''),
                'text'  => trim($_POST['text'] ?? ''),
            ];
            if ($service['title'] === '') {
                gms_flash('error', 'Service title is required.');
                header('Location: ' . $SELF, true, 303); exit;
            }
            if ($index === null) {
                $services[] = $service;
                gms_flash('success', 'Service added.');
            } elseif (isset($services[$index])) {
                $services[$index] = $service;
                gms_flash('success', 'Service updated.');
            } else {
                gms_flash('error', 'Service not found.');
            }
            break;

        case 'delete':
            if ($index !== null && isset($services[$index])) {
                array_splice($services, $index, 1);
                gms_flash('success', 'Service deleted.');
            } else {
                gms_flash('error', 'Service not found.');
            }
            break;

        case 'move':
            $swap = ($_POST['dir'] ?? '') === 'up' ? $index - 1 : $index + 1;
            if ($index !== null && isset($services[$index], $services[$swap])) {
                [$services[$index], $services[$swap]] = [$services[$swap], $services[$index]];
                gms_flash('success', 'Order updated.');
            }
            break;
    }

    $content['services'] = array_values($services);
    if (!gms_save_content($content)) {
        gms_flash('error', 'Could not write to data file. Check folder permissions.');
    }
    header('Location: ' . $SELF, true, 303); exit;
}

/* -------------------------------------------------------------------------
   GET render
------------------------------------------------------------------------- */
$content  = gms_load_content();
$services = array_values($content['services'] ?? []);
$csrf     = admin_csrf_token();

$editing = null;
if (isset($_GET['new'])) {
    $editing = ['index' => null, 'service' => ['icon' => array_key_first($ICONS), 'title' => '', 'text' => '']];
} elseif (isset($_GET['edit']) && isset($services[(int)$_GET['edit']])) {
    $i = (int) $_GET['edit'];
    $editing = ['index' => $i, 'service' => $services[$i]];
}

$label = "block font-heading uppercase text-xs tracking-wider2 text-muted mb-2";
$input = "w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors";

gms_admin_header('Services', 'dashboard');
?>

<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <a href="<?= admin_url('index.php') ?>" class="text-xs uppercase tracking-wider2 text-muted hover:text-primary transition-colors">&larr; Dashboard</a>
        <h1 class="text-section mt-2">Services</h1>
        <p class="text-muted mt-1"><?= count($services) ?> service<?= count($services) === 1 ? '' : 's' ?> shown in the homepage grid</p>
    </div>
    <?php if ($editing === null): ?>
    <a href="<?= $SELF ?>?new" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Add Service
    </a>
    <?php endif; ?>
</div>

<?php if ($editing !== null): /* ============ FORM VIEW ============ */
    $s = $editing['service'];
    $isNew = $editing['index'] === null;
?>
<form method="post" action="<?= $SELF ?>" class="max-w-3xl bg-dark-800 border border-line p-7 lg:p-8 space-y-6">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save">
    <?php if (!$isNew): ?><input type="hidden" name="index" value="<?= (int)$editing['index'] ?>"><?php endif; ?>

    <h2 class="text-xl"><?= $isNew ? 'Add New Service' : 'Edit Service #' . ((int)$editing['index'] + 1) ?></h2>

    <div>
        <label class="<?= $label ?>">Title</label>
        <input name="title" type="text" required value="<?= e($s['title']) ?>" placeholder="Building Construction" class="<?= $input ?>">
    </div>

    <div>
        <label class="<?= $label ?>">Description</label>
        <textarea name="text" rows="3" placeholder="Short description of the service…" class="<?= $input ?> resize-none"><?= e($s['text']) ?></textarea>
    </div>

    <!-- Icon picker (radio grid, peer styling) -->
    <div>
        <label class="<?= $label ?>">Icon</label>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            <?php foreach ($ICONS as $key => $svg): ?>
            <label class="cursor-pointer">
                <input type="radio" name="icon" value="<?= e($key) ?>" class="peer sr-only" <?= $s['icon'] === $key ? 'checked' : '' ?>>
                <span class="flex flex-col items-center gap-2 py-4 border border-line text-muted hover:text-white peer-checked:border-primary peer-checked:text-primary peer-checked:bg-primary/5 transition-colors">
                    <?= gms_admin_icon($ICONS, $key) ?>
                    <span class="text-[10px] uppercase tracking-wider"><?= e($key) ?></span>
                </span>
            </label>
            <?php endforeach; ?>
        </div>
        <p class="text-muted text-xs mt-2">Icon keys map to <code class="text-primary">gms_icon()</code> on the front-end.</p>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="btn-primary"><?= $isNew ? 'Create Service' : 'Save Changes' ?></button>
        <a href="<?= $SELF ?>" class="btn-outline">Cancel</a>
    </div>
</form>

<?php else: /* ============ LIST VIEW ============ */ ?>

<?php if (!$services): ?>
    <div class="bg-dark-800 border border-line border-dashed p-14 text-center">
        <p class="text-muted mb-6">No services yet. Add your first one to populate the homepage grid.</p>
        <a href="<?= $SELF ?>?new" class="btn-primary">Add Service</a>
    </div>
<?php else: ?>
    <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach ($services as $i => $s): ?>
        <div class="group bg-dark-800 border border-line hover:border-primary/40 transition-colors p-6 flex gap-5">
            <span class="grid place-items-center w-14 h-14 shrink-0 border border-line text-primary"><?= gms_admin_icon($ICONS, $s['icon'] ?? '') ?></span>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="text-lg text-white truncate"><?= e($s['title'] ?? '') ?: '<span class="text-muted">Untitled</span>' ?></h3>
                    <span class="font-heading text-xs text-muted shrink-0"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                </div>
                <p class="text-muted text-sm line-clamp-2 mt-1 mb-4"><?= e($s['text'] ?? '') ?></p>
                <div class="flex items-center gap-2">
                    <form method="post" action="<?= $SELF ?>" class="inline">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="move">
                        <input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="up">
                        <button class="grid place-items-center w-8 h-8 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30 transition-colors" <?= $i === 0 ? 'disabled' : '' ?> title="Move up">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 15l7-7 7 7"/></svg>
                        </button>
                    </form>
                    <form method="post" action="<?= $SELF ?>" class="inline">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="move">
                        <input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="down">
                        <button class="grid place-items-center w-8 h-8 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30 transition-colors" <?= $i === count($services) - 1 ? 'disabled' : '' ?> title="Move down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </form>
                    <a href="<?= $SELF ?>?edit=<?= $i ?>" class="btn-outline !px-4 !py-2 !text-xs">Edit</a>
                    <form method="post" action="<?= $SELF ?>" class="inline" onsubmit="return confirm('Delete this service?');">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button class="grid place-items-center w-8 h-8 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-colors" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 7h12M9 7V5h6v2m-1 0v12H10V7M5 7l1 14h12l1-14"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php endif; /* end view switch */ ?>

<?php gms_admin_footer(); ?>
