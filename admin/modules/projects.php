<?php
/**
 * Projects — full CRUD editor (Phase 5).
 * Data: $content['projects'] in /data/site_content.json
 * PRG + CSRF + atomic save, same pattern as hero.php / services.php.
 */
require_once __DIR__ . '/../includes/layout.php';
admin_require_login();

$SELF = admin_url('modules/projects.php');

/* Grid-span options (key => label). 'lg' spans 2x2 in the homepage masonry. */
$SIZES = [
    'lg' => 'Large — spans 2×2',
    'sm' => 'Standard',
];

/* -------------------------------------------------------------------------
   POST (PRG)
------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        gms_flash('error', 'Security token expired. Please try again.');
        header('Location: ' . $SELF, true, 303); exit;
    }

    $content  = gms_load_content();
    $projects = array_values($content['projects'] ?? []);
    $action   = $_POST['action'] ?? '';
    $index    = isset($_POST['index']) && $_POST['index'] !== '' ? (int) $_POST['index'] : null;

    switch ($action) {

        case 'save':
            $size = $_POST['size'] ?? 'sm';
            if (!isset($SIZES[$size])) $size = 'sm';
            $project = [
                'image'    => trim($_POST['image'] ?? ''),
                'title'    => trim($_POST['title'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'url'      => trim($_POST['url'] ?? '') ?: '#',
                'size'     => $size,
            ];
            if ($project['title'] === '') {
                gms_flash('error', 'Project title is required.');
                header('Location: ' . $SELF, true, 303); exit;
            }
            if ($index === null) {
                $projects[] = $project;
                gms_flash('success', 'Project added.');
            } elseif (isset($projects[$index])) {
                $projects[$index] = $project;
                gms_flash('success', 'Project updated.');
            } else {
                gms_flash('error', 'Project not found.');
            }
            break;

        case 'delete':
            if ($index !== null && isset($projects[$index])) {
                array_splice($projects, $index, 1);
                gms_flash('success', 'Project deleted.');
            } else {
                gms_flash('error', 'Project not found.');
            }
            break;

        case 'move':
            $swap = ($_POST['dir'] ?? '') === 'up' ? $index - 1 : $index + 1;
            if ($index !== null && isset($projects[$index], $projects[$swap])) {
                [$projects[$index], $projects[$swap]] = [$projects[$swap], $projects[$index]];
                gms_flash('success', 'Order updated.');
            }
            break;
    }

    $content['projects'] = array_values($projects);
    if (!gms_save_content($content)) {
        gms_flash('error', 'Could not write to data file. Check folder permissions.');
    }
    header('Location: ' . $SELF, true, 303); exit;
}

/* -------------------------------------------------------------------------
   GET render
------------------------------------------------------------------------- */
$content  = gms_load_content();
$projects = array_values($content['projects'] ?? []);
$csrf     = admin_csrf_token();

$editing = null;
if (isset($_GET['new'])) {
    $editing = ['index' => null, 'project' => ['image' => '', 'title' => '', 'category' => '', 'url' => '#', 'size' => 'sm']];
} elseif (isset($_GET['edit']) && isset($projects[(int)$_GET['edit']])) {
    $i = (int) $_GET['edit'];
    $editing = ['index' => $i, 'project' => $projects[$i]];
}

$label = "block font-heading uppercase text-xs tracking-wider2 text-muted mb-2";
$input = "w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors";

gms_admin_header('Projects', 'dashboard');
?>

<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <a href="<?= admin_url('index.php') ?>" class="text-xs uppercase tracking-wider2 text-muted hover:text-primary transition-colors">&larr; Dashboard</a>
        <h1 class="text-section mt-2">Projects</h1>
        <p class="text-muted mt-1"><?= count($projects) ?> project<?= count($projects) === 1 ? '' : 's' ?> in the portfolio grid</p>
    </div>
    <?php if ($editing === null): ?>
    <a href="<?= $SELF ?>?new" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Add Project
    </a>
    <?php endif; ?>
</div>

<?php if ($editing !== null): /* ============ FORM VIEW ============ */
    $p = $editing['project'];
    $isNew = $editing['index'] === null;
?>
<div class="grid lg:grid-cols-3 gap-8">
    <form method="post" action="<?= $SELF ?>" class="lg:col-span-2 bg-dark-800 border border-line p-7 lg:p-8 space-y-5">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="save">
        <?php if (!$isNew): ?><input type="hidden" name="index" value="<?= (int)$editing['index'] ?>"><?php endif; ?>

        <h2 class="text-xl mb-1"><?= $isNew ? 'Add New Project' : 'Edit Project #' . ((int)$editing['index'] + 1) ?></h2>

        <div>
            <label class="<?= $label ?>">Image URL</label>
            <input name="image" type="url" value="<?= e($p['image']) ?>" placeholder="https://…/project.jpg" class="<?= $input ?>">
        </div>
        <div>
            <label class="<?= $label ?>">Title (Project Name)</label>
            <input name="title" type="text" required value="<?= e($p['title']) ?>" placeholder="Skyline Corporate Tower" class="<?= $input ?>">
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="<?= $label ?>">Category</label>
                <input name="category" type="text" value="<?= e($p['category']) ?>" placeholder="Commercial" class="<?= $input ?>">
            </div>
            <div>
                <label class="<?= $label ?>">Size / Grid Span</label>
                <select name="size" class="<?= $input ?>">
                    <?php foreach ($SIZES as $key => $lbl): ?>
                    <option value="<?= e($key) ?>" <?= ($p['size'] ?? 'sm') === $key ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div>
            <label class="<?= $label ?>">Link URL <span class="text-muted/60 normal-case tracking-normal">(optional — defaults to #)</span></label>
            <input name="url" type="text" value="<?= e($p['url'] ?? '') ?>" placeholder="#" class="<?= $input ?>">
        </div>

        <div class="flex items-center gap-3 pt-3">
            <button type="submit" class="btn-primary"><?= $isNew ? 'Create Project' : 'Save Changes' ?></button>
            <a href="<?= $SELF ?>" class="btn-outline">Cancel</a>
        </div>
    </form>

    <!-- Preview -->
    <div class="lg:col-span-1">
        <span class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-3">Preview</span>
        <div class="relative aspect-[4/3] border border-line overflow-hidden bg-dark-700 group">
            <?php if (!empty($p['image'])): ?>
                <img src="<?= e($p['image']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
            <?php endif; ?>
            <span class="absolute inset-0 bg-gradient-to-t from-dark via-dark/20 to-transparent opacity-80"></span>
            <div class="absolute inset-x-0 bottom-0 p-5">
                <?php if (!empty($p['category'])): ?><span class="inline-block bg-primary text-dark px-3 py-1 font-heading uppercase text-[11px] tracking-wider2 mb-2"><?= e($p['category']) ?></span><?php endif; ?>
                <h3 class="text-xl text-white"><?= $p['title'] !== '' ? e($p['title']) : 'Project name' ?></h3>
            </div>
        </div>
        <p class="text-muted text-xs mt-3">Span: <span class="text-white uppercase"><?= e($p['size'] ?? 'sm') ?></span></p>
    </div>
</div>

<?php else: /* ============ LIST VIEW ============ */ ?>

<?php if (!$projects): ?>
    <div class="bg-dark-800 border border-line border-dashed p-14 text-center">
        <p class="text-muted mb-6">No projects yet. Add your first portfolio item.</p>
        <a href="<?= $SELF ?>?new" class="btn-primary">Add Project</a>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($projects as $i => $p): ?>
        <div class="group bg-dark-800 border border-line hover:border-primary/40 transition-colors flex flex-col sm:flex-row">
            <div class="relative sm:w-48 shrink-0 aspect-video sm:aspect-auto overflow-hidden bg-dark-700">
                <?php if (!empty($p['image'])): ?>
                    <img src="<?= e($p['image']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                <?php endif; ?>
                <span class="absolute top-2 left-2 bg-primary text-dark font-heading text-xs px-2 py-1"><?= $i + 1 ?></span>
            </div>
            <div class="flex-1 p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <?php if (!empty($p['category'])): ?><span class="text-primary text-xs uppercase tracking-wider2"><?= e($p['category']) ?></span><?php endif; ?>
                        <span class="text-[10px] uppercase tracking-wider2 border border-line text-muted px-1.5 py-0.5"><?= e($p['size'] ?? 'sm') ?></span>
                    </div>
                    <h3 class="text-lg text-white truncate"><?= e($p['title'] ?? '') ?: '<span class="text-muted">Untitled</span>' ?></h3>
                    <p class="text-muted text-xs truncate mt-1"><?= e($p['url'] ?? '#') ?></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form method="post" action="<?= $SELF ?>" class="inline">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="move">
                        <input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="up">
                        <button class="grid place-items-center w-9 h-9 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30 transition-colors" <?= $i === 0 ? 'disabled' : '' ?> title="Move up">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 15l7-7 7 7"/></svg>
                        </button>
                    </form>
                    <form method="post" action="<?= $SELF ?>" class="inline">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="move">
                        <input type="hidden" name="index" value="<?= $i ?>"><input type="hidden" name="dir" value="down">
                        <button class="grid place-items-center w-9 h-9 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30 transition-colors" <?= $i === count($projects) - 1 ? 'disabled' : '' ?> title="Move down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </form>
                    <a href="<?= $SELF ?>?edit=<?= $i ?>" class="btn-outline !px-4 !py-2 !text-xs">Edit</a>
                    <form method="post" action="<?= $SELF ?>" class="inline" onsubmit="return confirm('Delete this project?');">
                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button class="grid place-items-center w-9 h-9 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-colors" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 7h12M9 7V5h6v2m-1 0v12H10V7M5 7l1 14h12l1-14"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php endif; ?>

<?php gms_admin_footer(); ?>
