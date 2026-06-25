<?php
require_once __DIR__ . '/includes/layout.php';
admin_require_login();

$content = gms_load_content();
$count = fn($k) => count($content[$k] ?? []);

// Section registry. 'url' set => editor live; null => coming in a later phase.
$sections = [
    ['key'=>'site',         'label'=>'Site & Branding', 'desc'=>'Name, contact, social links',        'url'=>admin_url('modules/branding.php')],
    ['key'=>'hero_slides',  'label'=>'Hero Slider',     'desc'=>$count('hero_slides').' slides',       'url'=>admin_url('modules/hero.php')],
    ['key'=>'about',        'label'=>'About',           'desc'=>'Intro, badge, counters',              'url'=>null],
    ['key'=>'services',     'label'=>'Services',        'desc'=>$count('services').' services',        'url'=>admin_url('modules/services.php')],
    ['key'=>'projects',     'label'=>'Projects',        'desc'=>$count('projects').' projects',        'url'=>admin_url('modules/projects.php')],
    ['key'=>'unit_explorer', 'label'=>'Unit Explorer', 'desc'=>count($content['unit_explorer']['projects']??[]).' projects (floors & units)', 'url'=>admin_url('modules/units.php')],
    ['key'=>'stats',        'label'=>'Stats',           'desc'=>$count('stats').' figures',            'url'=>null],
    ['key'=>'testimonials', 'label'=>'Testimonials',    'desc'=>$count('testimonials').' reviews',     'url'=>null],
    ['key'=>'team',         'label'=>'Team',            'desc'=>$count('team').' members',             'url'=>null],
    ['key'=>'contact',      'label'=>'Contact',         'desc'=>'Details, map, form subjects',         'url'=>null],
];

gms_admin_header('Dashboard', 'dashboard');
?>
<h1 class="text-section mb-2">Dashboard</h1>
<p class="text-muted mb-10">Manage homepage content. More editors are added each phase.</p>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($sections as $s): $live = !empty($s['url']); ?>
    <?= $live ? '<a href="'.e($s['url']).'"' : '<div' ?> class="group bg-dark-800 border border-line p-6 <?= $live ? 'hover:border-primary/60' : '' ?> transition-colors block">
        <div class="flex items-start justify-between mb-4">
            <h3 class="text-lg <?= $live ? 'group-hover:text-primary' : '' ?> transition-colors"><?= e($s['label']) ?></h3>
            <?php if ($live): ?>
                <span class="font-heading text-xs uppercase tracking-wider2 text-dark bg-primary px-2 py-1">Live</span>
            <?php else: ?>
                <span class="font-heading text-xs uppercase tracking-wider2 text-muted border border-line px-2 py-1">Soon</span>
            <?php endif; ?>
        </div>
        <p class="text-muted text-sm mb-6"><?= e($s['desc']) ?></p>
        <span class="inline-flex items-center gap-2 font-heading uppercase text-xs tracking-wider2 <?= $live ? 'text-primary' : 'text-muted' ?>">
            <?= $live ? 'Manage' : 'Coming soon' ?>
            <?php if ($live): ?><svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg><?php endif; ?>
        </span>
    <?= $live ? '</a>' : '</div>' ?>
    <?php endforeach; ?>
</div>

<?php gms_admin_footer(); ?>
