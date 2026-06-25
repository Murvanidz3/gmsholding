<?php
/**
 * Unit Explorer — PROJECTS level (Phase 6).
 * Manages $content['unit_explorer']['projects'][]. Floors/apartments are
 * nested and managed in unit_floors.php / unit_apartments.php.
 */
require_once __DIR__ . '/../includes/layout.php';
admin_require_login();

$SELF = admin_url('modules/units.php');
if (!function_exists('gms_uid')) {
    function gms_uid($prefix) { return $prefix . '-' . substr(bin2hex(random_bytes(4)), 0, 6); }
}

/* ---------------- POST (PRG) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        gms_flash('error', 'Security token expired.'); header('Location: ' . $SELF, true, 303); exit;
    }
    $content = gms_load_content();
    if (!isset($content['unit_explorer']) || !is_array($content['unit_explorer'])) $content['unit_explorer'] = ['projects' => []];
    $projects = array_values($content['unit_explorer']['projects'] ?? []);
    $action = $_POST['action'] ?? '';
    $index  = isset($_POST['index']) && $_POST['index'] !== '' ? (int) $_POST['index'] : null;

    switch ($action) {
        case 'save':
            $proj = ($index !== null && isset($projects[$index])) ? $projects[$index] : ['id' => gms_uid('prj'), 'floors' => []];
            $proj['name']           = trim($_POST['name'] ?? '');
            $proj['status']         = ($_POST['status'] ?? 'completed') === 'ongoing' ? 'ongoing' : 'completed';
            $proj['location']       = trim($_POST['location'] ?? '');
            $upErr = null; $up = gms_handle_upload('building_file', $upErr);
            if ($up === null && $upErr) { gms_flash('error', $upErr); header('Location: ' . $SELF, true, 303); exit; }
            $proj['building_image'] = $up ?? trim($_POST['existing_building'] ?? '');
            if (!isset($proj['floors']) || !is_array($proj['floors'])) $proj['floors'] = [];
            if ($proj['name'] === '') { gms_flash('error', 'Project name is required.'); header('Location: ' . $SELF, true, 303); exit; }
            if ($index === null) { $projects[] = $proj; gms_flash('success', 'Project added.'); }
            elseif (isset($projects[$index])) { $projects[$index] = $proj; gms_flash('success', 'Project updated.'); }
            break;
        case 'delete':
            if ($index !== null && isset($projects[$index])) { array_splice($projects, $index, 1); gms_flash('success', 'Project deleted.'); }
            break;
        case 'move':
            $swap = ($_POST['dir'] ?? '') === 'up' ? $index - 1 : $index + 1;
            if ($index !== null && isset($projects[$index], $projects[$swap])) { [$projects[$index], $projects[$swap]] = [$projects[$swap], $projects[$index]]; gms_flash('success', 'Order updated.'); }
            break;
    }
    $content['unit_explorer']['projects'] = array_values($projects);
    if (!gms_save_content($content)) gms_flash('error', 'Could not write to data file.');
    header('Location: ' . $SELF, true, 303); exit;
}

/* ---------------- GET ---------------- */
$content  = gms_load_content();
$projects = array_values($content['unit_explorer']['projects'] ?? []);
$csrf     = admin_csrf_token();

$editing = null;
if (isset($_GET['new'])) $editing = ['index' => null, 'p' => ['name' => '', 'status' => 'completed', 'location' => '', 'building_image' => '']];
elseif (isset($_GET['edit']) && isset($projects[(int)$_GET['edit']])) { $i = (int)$_GET['edit']; $editing = ['index' => $i, 'p' => $projects[$i]]; }

$label = "block font-heading uppercase text-xs tracking-wider2 text-muted mb-2";
$input = "w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors";

gms_admin_header('Unit Explorer', 'dashboard');
?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <a href="<?= admin_url('index.php') ?>" class="text-xs uppercase tracking-wider2 text-muted hover:text-primary transition-colors">&larr; Dashboard</a>
        <h1 class="text-section mt-2">Unit Explorer</h1>
        <p class="text-muted mt-1"><?= count($projects) ?> project<?= count($projects) === 1 ? '' : 's' ?> · Project → Floor → Apartment</p>
    </div>
    <?php if ($editing === null): ?><a href="<?= $SELF ?>?new" class="btn-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>Add Project</a><?php endif; ?>
</div>

<?php if ($editing !== null): $p = $editing['p']; $isNew = $editing['index'] === null; ?>
<form method="post" action="<?= $SELF ?>" enctype="multipart/form-data" class="max-w-3xl bg-dark-800 border border-line p-7 lg:p-8 space-y-5">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="save">
    <?php if (!$isNew): ?><input type="hidden" name="index" value="<?= (int)$editing['index'] ?>"><?php endif; ?>
    <h2 class="text-xl"><?= $isNew ? 'Add Project' : 'Edit Project' ?></h2>
    <div class="grid sm:grid-cols-2 gap-5">
        <div><label class="<?= $label ?>">Project Name</label><input name="name" required value="<?= e($p['name']) ?>" class="<?= $input ?>" placeholder="Skyline Residences"></div>
        <div><label class="<?= $label ?>">Status</label>
            <select name="status" class="<?= $input ?>">
                <option value="completed" <?= ($p['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="ongoing" <?= ($p['status'] ?? '') === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
            </select>
        </div>
    </div>
    <div><label class="<?= $label ?>">Location</label><input name="location" value="<?= e($p['location'] ?? '') ?>" class="<?= $input ?>" placeholder="Downtown, New York"></div>
    <div>
            <label class="<?= $label ?>">Building Image (Stage 1)</label>
            <?php if (!empty($p['building_image'])): ?>
                <img src="<?= e($p['building_image']) ?>" alt="" class="w-full max-h-52 object-cover border border-line mb-3">
            <?php endif; ?>
            <input type="hidden" name="existing_building" value="<?= e($p['building_image'] ?? '') ?>">
            <input name="building_file" type="file" accept="image/png,image/jpeg,image/webp,image/gif"
                   class="w-full bg-dark-700 border border-line text-muted text-sm file:mr-4 file:border-0 file:bg-primary file:text-dark file:px-4 file:py-3 file:font-heading file:uppercase file:text-xs file:tracking-wider2 file:cursor-pointer">
            <p class="text-muted text-xs mt-2">Floor polygons are plotted on this image. Leave empty to keep current. JPG/PNG/WEBP/GIF, max 8 MB.</p>
        </div>
    <div class="flex items-center gap-3 pt-2">
        <button class="btn-primary"><?= $isNew ? 'Create Project' : 'Save Changes' ?></button>
        <a href="<?= $SELF ?>" class="btn-outline">Cancel</a>
    </div>
    <?php if (!$isNew): ?><p class="text-muted text-xs">Tip: after saving, use <span class="text-primary">Manage Floors</span> to map floors onto the building image.</p><?php endif; ?>
</form>

<?php else: ?>
<?php if (!$projects): ?>
    <div class="bg-dark-800 border border-line border-dashed p-14 text-center"><p class="text-muted mb-6">No projects yet.</p><a href="<?= $SELF ?>?new" class="btn-primary">Add Project</a></div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($projects as $i => $p): $floors = $p['floors'] ?? []; $on = ($p['status'] ?? '') === 'ongoing'; ?>
        <div class="bg-dark-800 border border-line hover:border-primary/40 transition-colors flex flex-col sm:flex-row">
            <div class="relative sm:w-48 shrink-0 aspect-video sm:aspect-auto overflow-hidden bg-dark-700">
                <?php if (!empty($p['building_image'])): ?><img src="<?= e($p['building_image']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover"><?php endif; ?>
            </div>
            <div class="flex-1 p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] uppercase tracking-wider2 px-2 py-0.5 <?= $on ? 'bg-amber-500/20 text-amber-300' : 'bg-green-500/20 text-green-300' ?>"><?= $on ? 'Ongoing' : 'Completed' ?></span>
                        <span class="text-muted text-xs"><?= count($floors) ?> floor<?= count($floors) === 1 ? '' : 's' ?></span>
                    </div>
                    <h3 class="text-lg text-white truncate"><?= e($p['name']) ?></h3>
                    <p class="text-muted text-xs truncate mt-0.5"><?= e($p['location'] ?? '') ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <a href="<?= admin_url('modules/unit_floors.php') ?>?project=<?= e($p['id']) ?>" class="btn-primary !px-4 !py-2 !text-xs">Manage Floors</a>
                    <form method="post" action="<?= $SELF ?>" class="inline"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="move"><input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="up"><button class="grid place-items-center w-8 h-8 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30" <?= $i === 0 ? 'disabled' : '' ?>>&uarr;</button></form>
                    <form method="post" action="<?= $SELF ?>" class="inline"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="move"><input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="down"><button class="grid place-items-center w-8 h-8 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30" <?= $i === count($projects) - 1 ? 'disabled' : '' ?>>&darr;</button></form>
                    <a href="<?= $SELF ?>?edit=<?= $i ?>" class="btn-outline !px-4 !py-2 !text-xs">Edit</a>
                    <form method="post" action="<?= $SELF ?>" class="inline" onsubmit="return confirm('Delete this project and all its floors/units?');"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="index" value="<?= $i ?>"><button class="grid place-items-center w-8 h-8 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white">&times;</button></form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php endif; ?>
<?php gms_admin_footer(); ?>
