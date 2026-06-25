<?php
/** Unit Explorer — FLOORS level. ?project=ID. Maps floor polygons onto the building image. */
require_once __DIR__ . '/../includes/layout.php';
admin_require_login();

if (!function_exists('gms_uid')) { function gms_uid($p){ return $p.'-'.substr(bin2hex(random_bytes(4)),0,6); } }
function gms_find_idx(array $arr, string $id): int { foreach ($arr as $i => $x) if (($x['id'] ?? '') === $id) return $i; return -1; }
function gms_poly_in($raw): array { $d = json_decode((string)$raw, true); return is_array($d) ? $d : []; }

$projectId = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['project'] ?? '') : ($_GET['project'] ?? '');
$UNITS = admin_url('modules/units.php');
$SELF  = admin_url('modules/unit_floors.php') . '?project=' . urlencode($projectId);

/* ---------------- POST (PRG) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf'] ?? null)) { gms_flash('error', 'Security token expired.'); header('Location: ' . $SELF, true, 303); exit; }
    $content  = gms_load_content();
    $projects = array_values($content['unit_explorer']['projects'] ?? []);
    $pi = gms_find_idx($projects, $projectId);
    if ($pi === -1) { gms_flash('error', 'Project not found.'); header('Location: ' . $UNITS, true, 303); exit; }
    $floors = array_values($projects[$pi]['floors'] ?? []);
    $action = $_POST['action'] ?? '';
    $index  = isset($_POST['index']) && $_POST['index'] !== '' ? (int) $_POST['index'] : null;

    switch ($action) {
        case 'save':
            $floor = ($index !== null && isset($floors[$index])) ? $floors[$index] : ['id' => gms_uid('flr'), 'apartments' => []];
            $floor['name']       = trim($_POST['name'] ?? '');
            $floor['label']      = trim($_POST['label'] ?? '');
            $upErr = null; $up = gms_handle_upload('plan_file', $upErr);
            if ($up === null && $upErr) { gms_flash('error', $upErr); header('Location: ' . $SELF, true, 303); exit; }
            $floor['plan_image'] = $up ?? trim($_POST['existing_plan'] ?? '');
            $floor['polygon']    = gms_poly_in($_POST['polygon'] ?? '[]');
            if (!isset($floor['apartments']) || !is_array($floor['apartments'])) $floor['apartments'] = [];
            if ($floor['name'] === '') { gms_flash('error', 'Floor name is required.'); header('Location: ' . $SELF, true, 303); exit; }
            if ($index === null) { $floors[] = $floor; gms_flash('success', 'Floor added.'); }
            elseif (isset($floors[$index])) { $floors[$index] = $floor; gms_flash('success', 'Floor updated.'); }
            break;
        case 'delete':
            if ($index !== null && isset($floors[$index])) { array_splice($floors, $index, 1); gms_flash('success', 'Floor deleted.'); }
            break;
        case 'move':
            $swap = ($_POST['dir'] ?? '') === 'up' ? $index - 1 : $index + 1;
            if ($index !== null && isset($floors[$index], $floors[$swap])) { [$floors[$index], $floors[$swap]] = [$floors[$swap], $floors[$index]]; gms_flash('success', 'Order updated.'); }
            break;
    }
    $projects[$pi]['floors'] = array_values($floors);
    $content['unit_explorer']['projects'] = array_values($projects);
    if (!gms_save_content($content)) gms_flash('error', 'Could not write to data file.');
    header('Location: ' . $SELF, true, 303); exit;
}

/* ---------------- GET ---------------- */
$content  = gms_load_content();
$projects = array_values($content['unit_explorer']['projects'] ?? []);
$pi = gms_find_idx($projects, $projectId);
if ($pi === -1) { gms_flash('error', 'Project not found.'); header('Location: ' . $UNITS, true, 303); exit; }
$project = $projects[$pi];
$floors  = array_values($project['floors'] ?? []);
$csrf    = admin_csrf_token();

$editing = null;
if (isset($_GET['new'])) $editing = ['index' => null, 'f' => ['name' => '', 'label' => '', 'plan_image' => '', 'polygon' => []]];
elseif (isset($_GET['edit']) && isset($floors[(int)$_GET['edit']])) { $i = (int)$_GET['edit']; $editing = ['index' => $i, 'f' => $floors[$i]]; }

$label = "block font-heading uppercase text-xs tracking-wider2 text-muted mb-2";
$input = "w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors";

gms_admin_header('Floors — ' . $project['name'], 'dashboard');
?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <a href="<?= $UNITS ?>" class="text-xs uppercase tracking-wider2 text-muted hover:text-primary transition-colors">&larr; Projects</a>
        <h1 class="text-section mt-2"><?= e($project['name']) ?> · Floors</h1>
        <p class="text-muted mt-1"><?= count($floors) ?> floor<?= count($floors) === 1 ? '' : 's' ?> mapped on the building</p>
    </div>
    <?php if ($editing === null): ?><a href="<?= $SELF ?>&new" class="btn-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>Add Floor</a><?php endif; ?>
</div>

<?php if ($editing !== null): $f = $editing['f']; $isNew = $editing['index'] === null; ?>
<form method="post" action="<?= admin_url('modules/unit_floors.php') ?>" enctype="multipart/form-data" class="grid lg:grid-cols-2 gap-8">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="project" value="<?= e($projectId) ?>">
    <?php if (!$isNew): ?><input type="hidden" name="index" value="<?= (int)$editing['index'] ?>"><?php endif; ?>
    <div class="bg-dark-800 border border-line p-7 lg:p-8 space-y-5">
        <h2 class="text-xl"><?= $isNew ? 'Add Floor' : 'Edit Floor' ?></h2>
        <div class="grid sm:grid-cols-2 gap-5">
            <div><label class="<?= $label ?>">Floor Name</label><input name="name" required value="<?= e($f['name']) ?>" class="<?= $input ?>" placeholder="Floor 8"></div>
            <div><label class="<?= $label ?>">Short Label</label><input name="label" value="<?= e($f['label'] ?? '') ?>" class="<?= $input ?>" placeholder="08"></div>
        </div>
        <div>
            <label class="<?= $label ?>">Floor 2D Render (Stage 2)</label>
            <?php if (!empty($f['plan_image'])): ?>
                <img src="<?= e($f['plan_image']) ?>" alt="" class="w-full max-h-52 object-cover border border-line mb-3">
            <?php endif; ?>
            <input type="hidden" name="existing_plan" value="<?= e($f['plan_image'] ?? '') ?>">
            <input name="plan_file" type="file" accept="image/png,image/jpeg,image/webp,image/gif"
                   class="w-full bg-dark-700 border border-line text-muted text-sm file:mr-4 file:border-0 file:bg-primary file:text-dark file:px-4 file:py-3 file:font-heading file:uppercase file:text-xs file:tracking-wider2 file:cursor-pointer">
            <p class="text-muted text-xs mt-2">The full floor plan where all units are visible. Unit polygons are plotted on this. Max 8 MB.</p>
        </div>
        <input type="hidden" name="polygon" id="floor_poly" value='<?= e(json_encode($f['polygon'] ?? [])) ?>'>
        <div class="flex items-center gap-3 pt-2">
            <button class="btn-primary"><?= $isNew ? 'Create Floor' : 'Save Changes' ?></button>
            <a href="<?= $SELF ?>" class="btn-outline">Cancel</a>
        </div>
    </div>
    <div>
        <label class="<?= $label ?>">Map this floor on the building</label>
        <?php if (!empty($project['building_image'])): ?>
            <div class="gms-mapper" data-image="<?= e($project['building_image']) ?>" data-target="#floor_poly"></div>
        <?php else: ?>
            <p class="text-muted text-sm border border-line border-dashed p-6">Add a Building Image to the project first to enable mapping.</p>
        <?php endif; ?>
    </div>
</form>

<?php else: ?>
<?php if (!$floors): ?>
    <div class="bg-dark-800 border border-line border-dashed p-14 text-center"><p class="text-muted mb-6">No floors yet.</p><a href="<?= $SELF ?>&new" class="btn-primary">Add Floor</a></div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($floors as $i => $f): $apts = $f['apartments'] ?? []; $mapped = !empty($f['polygon']); ?>
        <div class="bg-dark-800 border border-line hover:border-primary/40 transition-colors p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
            <span class="grid place-items-center w-12 h-12 shrink-0 bg-primary text-dark font-heading"><?= e($f['label'] ?: '–') ?></span>
            <div class="min-w-0 flex-1">
                <h3 class="text-lg text-white truncate"><?= e($f['name']) ?></h3>
                <p class="text-muted text-xs mt-1">
                    <?= count($apts) ?> unit<?= count($apts) === 1 ? '' : 's' ?> ·
                    <span class="<?= $mapped ? 'text-green-400' : 'text-amber-400' ?>"><?= $mapped ? 'mapped' : 'not mapped' ?></span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="<?= admin_url('modules/unit_apartments.php') ?>?project=<?= e($projectId) ?>&floor=<?= e($f['id']) ?>" class="btn-primary !px-4 !py-2 !text-xs">Manage Units</a>
                <form method="post" action="<?= admin_url('modules/unit_floors.php') ?>" class="inline"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="project" value="<?= e($projectId) ?>"><input type="hidden" name="action" value="move"><input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="up"><button class="grid place-items-center w-8 h-8 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30" <?= $i === 0 ? 'disabled' : '' ?>>&uarr;</button></form>
                <form method="post" action="<?= admin_url('modules/unit_floors.php') ?>" class="inline"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="project" value="<?= e($projectId) ?>"><input type="hidden" name="action" value="move"><input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="down"><button class="grid place-items-center w-8 h-8 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30" <?= $i === count($floors) - 1 ? 'disabled' : '' ?>>&darr;</button></form>
                <a href="<?= $SELF ?>&edit=<?= $i ?>" class="btn-outline !px-4 !py-2 !text-xs">Edit</a>
                <form method="post" action="<?= admin_url('modules/unit_floors.php') ?>" class="inline" onsubmit="return confirm('Delete this floor and its units?');"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="project" value="<?= e($projectId) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="index" value="<?= $i ?>"><button class="grid place-items-center w-8 h-8 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white">&times;</button></form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php endif; ?>
<?php gms_admin_footer(); ?>
