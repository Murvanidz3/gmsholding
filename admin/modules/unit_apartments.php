<?php
/** Unit Explorer — APARTMENTS level. ?project=ID&floor=ID. Maps unit polygons on the floor plan. */
require_once __DIR__ . '/../includes/layout.php';
admin_require_login();

if (!function_exists('gms_uid')) { function gms_uid($p){ return $p.'-'.substr(bin2hex(random_bytes(4)),0,6); } }
if (!function_exists('gms_find_idx')) { function gms_find_idx(array $a, string $id): int { foreach ($a as $i => $x) if (($x['id'] ?? '') === $id) return $i; return -1; } }
if (!function_exists('gms_poly_in')) { function gms_poly_in($raw): array { $d = json_decode((string)$raw, true); return is_array($d) ? $d : []; } }

$isPost    = $_SERVER['REQUEST_METHOD'] === 'POST';
$projectId = $isPost ? ($_POST['project'] ?? '') : ($_GET['project'] ?? '');
$floorId   = $isPost ? ($_POST['floor'] ?? '')   : ($_GET['floor'] ?? '');
$UNITS  = admin_url('modules/units.php');
$FLOORS = admin_url('modules/unit_floors.php') . '?project=' . urlencode($projectId);
$SELF   = admin_url('modules/unit_apartments.php') . '?project=' . urlencode($projectId) . '&floor=' . urlencode($floorId);
$STATUSES = gms_unit_statuses();

/* ---------------- POST (PRG) ---------------- */
if ($isPost) {
    if (!admin_csrf_check($_POST['csrf'] ?? null)) { gms_flash('error', 'Security token expired.'); header('Location: ' . $SELF, true, 303); exit; }
    $content  = gms_load_content();
    $projects = array_values($content['unit_explorer']['projects'] ?? []);
    $pi = gms_find_idx($projects, $projectId);
    if ($pi === -1) { gms_flash('error', 'Project not found.'); header('Location: ' . $UNITS, true, 303); exit; }
    $floors = array_values($projects[$pi]['floors'] ?? []);
    $fi = gms_find_idx($floors, $floorId);
    if ($fi === -1) { gms_flash('error', 'Floor not found.'); header('Location: ' . $FLOORS, true, 303); exit; }
    $apts = array_values($floors[$fi]['apartments'] ?? []);
    $action = $_POST['action'] ?? '';
    $index  = isset($_POST['index']) && $_POST['index'] !== '' ? (int) $_POST['index'] : null;

    switch ($action) {
        case 'save':
            $status = $_POST['status'] ?? 'available';
            if (!isset($STATUSES[$status])) $status = 'available';
            $apt = ($index !== null && isset($apts[$index])) ? $apts[$index] : ['id' => gms_uid('apt')];
            $apt['code']    = trim($_POST['code'] ?? '');
            $upErr = null; $up = gms_handle_upload('render_file', $upErr);
            if ($up === null && $upErr) { gms_flash('error', $upErr); header('Location: ' . $SELF, true, 303); exit; }
            $apt['render'] = $up ?? trim($_POST['existing_render'] ?? '');
            $apt['rooms']   = (int) ($_POST['rooms'] ?? 0);
            $apt['area']    = (float) ($_POST['area'] ?? 0);
            $apt['price']   = trim($_POST['price'] ?? '');
            $apt['status']  = $status;
            $apt['polygon'] = gms_poly_in($_POST['polygon'] ?? '[]');
            if ($apt['code'] === '') { gms_flash('error', 'Unit code is required.'); header('Location: ' . $SELF, true, 303); exit; }
            if ($index === null) { $apts[] = $apt; gms_flash('success', 'Unit added.'); }
            elseif (isset($apts[$index])) { $apts[$index] = $apt; gms_flash('success', 'Unit updated.'); }
            break;
        case 'delete':
            if ($index !== null && isset($apts[$index])) { array_splice($apts, $index, 1); gms_flash('success', 'Unit deleted.'); }
            break;
        case 'move':
            $swap = ($_POST['dir'] ?? '') === 'up' ? $index - 1 : $index + 1;
            if ($index !== null && isset($apts[$index], $apts[$swap])) { [$apts[$index], $apts[$swap]] = [$apts[$swap], $apts[$index]]; gms_flash('success', 'Order updated.'); }
            break;
    }
    $floors[$fi]['apartments'] = array_values($apts);
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
$fi = gms_find_idx($floors, $floorId);
if ($fi === -1) { gms_flash('error', 'Floor not found.'); header('Location: ' . $FLOORS, true, 303); exit; }
$floor = $floors[$fi];
$apts  = array_values($floor['apartments'] ?? []);
$csrf  = admin_csrf_token();

$editing = null;
if (isset($_GET['new'])) $editing = ['index' => null, 'a' => ['code' => '', 'render' => '', 'rooms' => 0, 'area' => 0, 'price' => '', 'status' => 'available', 'polygon' => []]];
elseif (isset($_GET['edit']) && isset($apts[(int)$_GET['edit']])) { $i = (int)$_GET['edit']; $editing = ['index' => $i, 'a' => $apts[$i]]; }

$label = "block font-heading uppercase text-xs tracking-wider2 text-muted mb-2";
$input = "w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors";

gms_admin_header('Units — ' . $floor['name'], 'dashboard');
?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <a href="<?= $FLOORS ?>" class="text-xs uppercase tracking-wider2 text-muted hover:text-primary transition-colors">&larr; <?= e($project['name']) ?> · Floors</a>
        <h1 class="text-section mt-2"><?= e($floor['name']) ?> · Units</h1>
        <p class="text-muted mt-1"><?= count($apts) ?> unit<?= count($apts) === 1 ? '' : 's' ?> on this floor</p>
    </div>
    <?php if ($editing === null): ?><a href="<?= $SELF ?>&new" class="btn-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>Add Unit</a><?php endif; ?>
</div>

<?php if ($editing !== null): $a = $editing['a']; $isNew = $editing['index'] === null; ?>
<form method="post" action="<?= admin_url('modules/unit_apartments.php') ?>" enctype="multipart/form-data" class="grid lg:grid-cols-2 gap-8">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="save">
    <input type="hidden" name="project" value="<?= e($projectId) ?>"><input type="hidden" name="floor" value="<?= e($floorId) ?>">
    <?php if (!$isNew): ?><input type="hidden" name="index" value="<?= (int)$editing['index'] ?>"><?php endif; ?>
    <div class="bg-dark-800 border border-line p-7 lg:p-8 space-y-5">
        <h2 class="text-xl"><?= $isNew ? 'Add Unit' : 'Edit Unit' ?></h2>
        <div class="grid sm:grid-cols-2 gap-5">
            <div><label class="<?= $label ?>">Unit Code</label><input name="code" required value="<?= e($a['code']) ?>" class="<?= $input ?>" placeholder="8A"></div>
            <div><label class="<?= $label ?>">Status</label>
                <select name="status" class="<?= $input ?>">
                    <?php foreach ($STATUSES as $k => $st): ?><option value="<?= e($k) ?>" <?= ($a['status'] ?? '') === $k ? 'selected' : '' ?>><?= e($st['label']) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="grid sm:grid-cols-3 gap-5">
            <div><label class="<?= $label ?>">Rooms</label><input name="rooms" type="number" min="0" value="<?= e($a['rooms'] ?? 0) ?>" class="<?= $input ?>"></div>
            <div><label class="<?= $label ?>">Area (m²)</label><input name="area" type="number" step="0.1" min="0" value="<?= e($a['area'] ?? 0) ?>" class="<?= $input ?>"></div>
            <div><label class="<?= $label ?>">Price</label><input name="price" value="<?= e($a['price'] ?? '') ?>" class="<?= $input ?>" placeholder="$540,000"></div>
        </div>
        <div>
            <label class="<?= $label ?>">Unit 2D Render (Stage 3)</label>
            <?php if (!empty($a['render'])): ?>
                <img src="<?= e($a['render']) ?>" alt="" class="w-full max-h-52 object-cover border border-line mb-3">
            <?php endif; ?>
            <input type="hidden" name="existing_render" value="<?= e($a['render'] ?? '') ?>">
            <input name="render_file" type="file" accept="image/png,image/jpeg,image/webp,image/gif"
                   class="w-full bg-dark-700 border border-line text-muted text-sm file:mr-4 file:border-0 file:bg-primary file:text-dark file:px-4 file:py-3 file:font-heading file:uppercase file:text-xs file:tracking-wider2 file:cursor-pointer">
            <p class="text-muted text-xs mt-2">Shown in the front-end modal with price & status. Max 8 MB.</p>
        </div>
        <input type="hidden" name="polygon" id="apt_poly" value='<?= e(json_encode($a['polygon'] ?? [])) ?>'>
        <div class="flex items-center gap-3 pt-2">
            <button class="btn-primary"><?= $isNew ? 'Create Unit' : 'Save Changes' ?></button>
            <a href="<?= $SELF ?>" class="btn-outline">Cancel</a>
        </div>
    </div>
    <div>
        <label class="<?= $label ?>">Map this unit on the floor plan</label>
        <?php if (!empty($floor['plan_image'])): ?>
            <div class="gms-mapper" data-image="<?= e($floor['plan_image']) ?>" data-target="#apt_poly"></div>
        <?php else: ?>
            <p class="text-muted text-sm border border-line border-dashed p-6">Add a Floor-plan Image to this floor first to enable unit mapping.</p>
        <?php endif; ?>
    </div>
</form>

<?php else: ?>
<?php if (!$apts): ?>
    <div class="bg-dark-800 border border-line border-dashed p-14 text-center"><p class="text-muted mb-6">No units yet.</p><a href="<?= $SELF ?>&new" class="btn-primary">Add Unit</a></div>
<?php else: ?>
    <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach ($apts as $i => $a): $st = $STATUSES[$a['status'] ?? 'available'] ?? ['label' => '?', 'color' => '#888']; ?>
        <div class="bg-dark-800 border border-line hover:border-primary/40 transition-colors p-5 flex gap-4">
            <div class="relative w-24 shrink-0 aspect-square overflow-hidden bg-dark-700">
                <?php if (!empty($a['render'])): ?><img src="<?= e($a['render']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover"><?php endif; ?>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-lg text-white truncate"><?= e($a['code']) ?></h3>
                    <span class="font-heading uppercase text-[10px] tracking-wider2 px-2 py-1 text-dark" style="background:<?= e($st['color']) ?>"><?= e($st['label']) ?></span>
                </div>
                <p class="text-muted text-xs mt-1"><?= (int)($a['rooms'] ?? 0) ?> rooms · <?= e($a['area'] ?? 0) ?> m² · <?= e($a['price'] ?? '') ?></p>
                <div class="flex items-center gap-2 mt-3">
                    <a href="<?= $SELF ?>&edit=<?= $i ?>" class="btn-outline !px-3 !py-1.5 !text-xs">Edit</a>
                    <form method="post" action="<?= admin_url('modules/unit_apartments.php') ?>" class="inline"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="project" value="<?= e($projectId) ?>"><input type="hidden" name="floor" value="<?= e($floorId) ?>"><input type="hidden" name="action" value="move"><input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="up"><button class="grid place-items-center w-8 h-8 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30" <?= $i === 0 ? 'disabled' : '' ?>>&uarr;</button></form>
                    <form method="post" action="<?= admin_url('modules/unit_apartments.php') ?>" class="inline"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="project" value="<?= e($projectId) ?>"><input type="hidden" name="floor" value="<?= e($floorId) ?>"><input type="hidden" name="action" value="move"><input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="down"><button class="grid place-items-center w-8 h-8 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30" <?= $i === count($apts) - 1 ? 'disabled' : '' ?>>&darr;</button></form>
                    <form method="post" action="<?= admin_url('modules/unit_apartments.php') ?>" class="inline" onsubmit="return confirm('Delete this unit?');"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="project" value="<?= e($projectId) ?>"><input type="hidden" name="floor" value="<?= e($floorId) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="index" value="<?= $i ?>"><button class="grid place-items-center w-8 h-8 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white">&times;</button></form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php endif; ?>
<?php gms_admin_footer(); ?>
