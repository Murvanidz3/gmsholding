<?php
/**
 * Hero Slider — full CRUD editor (Phase 5).
 * Data: $content['hero_slides'] in /data/site_content.json
 * Pattern: POST mutation -> save JSON -> 303 redirect (PRG) -> GET render.
 */
require_once __DIR__ . '/../includes/layout.php';
admin_require_login();

$SELF = admin_url('modules/hero.php');

/* -------------------------------------------------------------------------
   POST handlers (mutations) — always end in a redirect (PRG)
------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        gms_flash('error', 'Security token expired. Please try again.');
        header('Location: ' . $SELF, true, 303); exit;
    }

    $content = gms_load_content();
    $slides  = array_values($content['hero_slides'] ?? []);
    $action  = $_POST['action'] ?? '';
    $index   = isset($_POST['index']) && $_POST['index'] !== '' ? (int) $_POST['index'] : null;

    switch ($action) {

        case 'save':
            $upErr = null; $up = gms_handle_upload('bg_file', $upErr);
            if ($up === null && $upErr) { gms_flash('error', $upErr); header('Location: ' . $SELF, true, 303); exit; }
            $slide = [
                'bg'       => $up ?? trim($_POST['existing_bg'] ?? ''),
                'eyebrow'  => trim($_POST['eyebrow'] ?? ''),
                'title'    => trim($_POST['title'] ?? ''),
                'text'     => trim($_POST['text'] ?? ''),
                'cta_text' => trim($_POST['cta_text'] ?? ''),
                'cta_url'  => trim($_POST['cta_url'] ?? ''),
            ];

            if ($slide['title'] === '' && $slide['bg'] === '') {
                gms_flash('error', 'A slide needs at least a title or an image.');
                header('Location: ' . $SELF, true, 303); exit;
            }

            if ($index === null) {                 // CREATE
                $slides[] = $slide;
                gms_flash('success', 'Slide added.');
            } elseif (isset($slides[$index])) {     // UPDATE
                $slides[$index] = $slide;
                gms_flash('success', 'Slide updated.');
            } else {
                gms_flash('error', 'Slide not found.');
            }
            break;

        case 'delete':
            if ($index !== null && isset($slides[$index])) {
                array_splice($slides, $index, 1);
                gms_flash('success', 'Slide deleted.');
            } else {
                gms_flash('error', 'Slide not found.');
            }
            break;

        case 'move':
            $dir = $_POST['dir'] ?? '';
            $swap = $dir === 'up' ? $index - 1 : $index + 1;
            if ($index !== null && isset($slides[$index], $slides[$swap])) {
                [$slides[$index], $slides[$swap]] = [$slides[$swap], $slides[$index]];
                gms_flash('success', 'Order updated.');
            }
            break;
    }

    $content['hero_slides'] = array_values($slides);
    if (!gms_save_content($content)) {
        gms_flash('error', 'Could not write to data file. Check folder permissions.');
    }
    header('Location: ' . $SELF, true, 303); exit;
}

/* -------------------------------------------------------------------------
   GET render
------------------------------------------------------------------------- */
$content = gms_load_content();
$slides  = array_values($content['hero_slides'] ?? []);
$csrf    = admin_csrf_token();

// Determine view: list (default), new, or edit
$editing = null;            // null = list view
if (isset($_GET['new'])) {
    $editing = ['index' => null, 'slide' => ['bg'=>'','eyebrow'=>'','title'=>'','text'=>'','cta_text'=>'','cta_url'=>'']];
} elseif (isset($_GET['edit']) && isset($slides[(int)$_GET['edit']])) {
    $i = (int) $_GET['edit'];
    $editing = ['index' => $i, 'slide' => $slides[$i]];
}

gms_admin_header('Hero Slider', 'dashboard');
?>

<!-- Breadcrumb + heading -->
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <a href="<?= admin_url('index.php') ?>" class="text-xs uppercase tracking-wider2 text-muted hover:text-primary transition-colors">&larr; Dashboard</a>
        <h1 class="text-section mt-2">Hero Slider</h1>
        <p class="text-muted mt-1"><?= count($slides) ?> slide<?= count($slides) === 1 ? '' : 's' ?> · shown in the homepage hero carousel</p>
    </div>
    <?php if ($editing === null): ?>
    <a href="<?= $SELF ?>?new" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Add Slide
    </a>
    <?php endif; ?>
</div>

<?php if ($editing !== null): /* ============ FORM VIEW ============ */
    $s = $editing['slide'];
    $isNew = $editing['index'] === null;
?>
<div class="grid lg:grid-cols-3 gap-8">
    <!-- Form -->
    <form method="post" action="<?= $SELF ?>" enctype="multipart/form-data" class="lg:col-span-2 bg-dark-800 border border-line p-7 lg:p-8 space-y-5">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="save">
        <?php if (!$isNew): ?><input type="hidden" name="index" value="<?= (int)$editing['index'] ?>"><?php endif; ?>

        <h2 class="text-xl mb-1"><?= $isNew ? 'Add New Slide' : 'Edit Slide #' . ((int)$editing['index'] + 1) ?></h2>

        <div>
            <label class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Background Image</label>
            <?php if (!empty($s['bg'])): ?>
                <img src="<?= e($s['bg']) ?>" alt="" class="w-full max-h-52 object-cover border border-line mb-3">
            <?php endif; ?>
            <input type="hidden" name="existing_bg" value="<?= e($s['bg']) ?>">
            <input name="bg_file" type="file" accept="image/png,image/jpeg,image/webp,image/gif"
                   class="w-full bg-dark-700 border border-line text-muted text-sm file:mr-4 file:border-0 file:bg-primary file:text-dark file:px-4 file:py-3 file:font-heading file:uppercase file:text-xs file:tracking-wider2 file:cursor-pointer">
            <p class="text-muted text-xs mt-2">Leave empty to keep the current image. JPG/PNG/WEBP/GIF, max 8 MB.</p>
        </div>

        <div>
            <label class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Title <span class="text-muted/60 normal-case tracking-normal">(use &lt;br&gt; for line breaks)</span></label>
            <input name="title" type="text" value="<?= e($s['title']) ?>" placeholder="We Build Your&lt;br&gt;Dreams Into Reality"
                   class="w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors">
        </div>

        <div>
            <label class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Subtitle <span class="text-muted/60 normal-case tracking-normal">(small label above title)</span></label>
            <input name="eyebrow" type="text" value="<?= e($s['eyebrow']) ?>" placeholder="Construction & Engineering"
                   class="w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors">
        </div>

        <div>
            <label class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Description</label>
            <textarea name="text" rows="3" placeholder="Short supporting paragraph…"
                      class="w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors resize-none"><?= e($s['text']) ?></textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Button Text</label>
                <input name="cta_text" type="text" value="<?= e($s['cta_text']) ?>" placeholder="Discover More"
                       class="w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors">
            </div>
            <div>
                <label class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Button Link</label>
                <input name="cta_url" type="text" value="<?= e($s['cta_url']) ?>" placeholder="#about"
                       class="w-full bg-dark-700 border border-line px-4 py-3 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-3">
            <button type="submit" class="btn-primary"><?= $isNew ? 'Create Slide' : 'Save Changes' ?></button>
            <a href="<?= $SELF ?>" class="btn-outline">Cancel</a>
        </div>
    </form>

    <!-- Live-ish preview -->
    <div class="lg:col-span-1">
        <span class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-3">Preview</span>
        <div class="relative aspect-[4/5] border border-line overflow-hidden bg-dark-700">
            <?php if (!empty($s['bg'])): ?>
                <img src="<?= e($s['bg']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
            <?php endif; ?>
            <div class="hero-overlay absolute inset-0"></div>
            <div class="relative z-10 p-6 h-full flex flex-col justify-center">
                <?php if (!empty($s['eyebrow'])): ?><span class="eyebrow mb-3 text-xs"><?= e($s['eyebrow']) ?></span><?php endif; ?>
                <h3 class="font-heading text-2xl text-white uppercase leading-tight mb-3"><?= $s['title'] !== '' ? $s['title'] : 'Slide title' ?></h3>
                <?php if (!empty($s['text'])): ?><p class="text-white/70 text-sm mb-4 line-clamp-3"><?= e($s['text']) ?></p><?php endif; ?>
                <?php if (!empty($s['cta_text'])): ?><span class="btn-primary !px-4 !py-2 !text-xs self-start"><?= e($s['cta_text']) ?></span><?php endif; ?>
            </div>
        </div>
        <p class="text-muted text-xs mt-3">Title renders raw HTML (so &lt;br&gt; works on the live site).</p>
    </div>
</div>

<?php else: /* ============ LIST VIEW ============ */ ?>

<?php if (!$slides): ?>
    <div class="bg-dark-800 border border-line border-dashed p-14 text-center">
        <p class="text-muted mb-6">No slides yet. Add your first hero slide to get started.</p>
        <a href="<?= $SELF ?>?new" class="btn-primary">Add Slide</a>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($slides as $i => $s): ?>
        <div class="group bg-dark-800 border border-line hover:border-primary/40 transition-colors flex flex-col sm:flex-row">
            <!-- thumb -->
            <div class="relative sm:w-56 shrink-0 aspect-video sm:aspect-auto overflow-hidden bg-dark-700">
                <?php if (!empty($s['bg'])): ?>
                    <img src="<?= e($s['bg']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                <?php endif; ?>
                <span class="absolute top-2 left-2 bg-primary text-dark font-heading text-xs px-2 py-1"><?= $i + 1 ?></span>
            </div>
            <!-- body -->
            <div class="flex-1 p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="min-w-0 flex-1">
                    <?php if (!empty($s['eyebrow'])): ?><span class="text-primary text-xs uppercase tracking-wider2"><?= e($s['eyebrow']) ?></span><?php endif; ?>
                    <h3 class="text-lg text-white truncate"><?= strip_tags($s['title']) !== '' ? e(strip_tags($s['title'])) : '<span class="text-muted">Untitled</span>' ?></h3>
                    <p class="text-muted text-sm line-clamp-1 mt-1"><?= e($s['text'] ?? '') ?></p>
                    <?php if (!empty($s['cta_text'])): ?>
                        <span class="inline-block mt-2 text-xs text-muted">Button: <span class="text-white"><?= e($s['cta_text']) ?></span> → <span class="text-white"><?= e($s['cta_url']) ?></span></span>
                    <?php endif; ?>
                </div>
                <!-- actions -->
                <div class="flex items-center gap-2 shrink-0">
                    <!-- reorder -->
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
                        <button class="grid place-items-center w-9 h-9 border border-line text-muted hover:text-primary hover:border-primary disabled:opacity-30 transition-colors" <?= $i === count($slides) - 1 ? 'disabled' : '' ?> title="Move down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </form>
                    <a href="<?= $SELF ?>?edit=<?= $i ?>" class="btn-outline !px-4 !py-2 !text-xs">Edit</a>
                    <form method="post" action="<?= $SELF ?>" class="inline" onsubmit="return confirm('Delete this slide? This cannot be undone.');">
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

<?php endif; /* end view switch */ ?>

<?php gms_admin_footer(); ?>
