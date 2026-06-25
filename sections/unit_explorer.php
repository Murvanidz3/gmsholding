<?php
/** UNIT EXPLORER — side-by-side projects (Completed + Ongoing). Reads $unit_explorer + gms_unit_statuses(). */
global $unit_explorer;
$ue_projects = $unit_explorer['projects'] ?? [];
$ue_status   = gms_unit_statuses();
if (!$ue_projects) return;
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<section id="units" class="py-24 lg:py-32 bg-dark">
    <div class="container">
        <!-- Heading + legend -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12 reveal">
            <div class="max-w-2xl">
                <span class="eyebrow mb-5"><?= $h($unit_explorer['eyebrow'] ?? '') ?></span>
                <h2 class="text-section mb-5"><?= $h($unit_explorer['title'] ?? '') ?></h2>
                <p class="text-muted text-lg leading-relaxed"><?= $h($unit_explorer['text'] ?? '') ?></p>
            </div>
            <div class="flex flex-wrap items-center gap-4 shrink-0">
                <?php foreach ($ue_status as $st): ?>
                <span class="flex items-center gap-2 text-xs uppercase tracking-wider2 text-muted">
                    <span class="inline-block w-3 h-3" style="background:<?= $h($st['color']) ?>"></span><?= $h($st['label']) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Side-by-side: 1 col mobile, 2 cols desktop -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            <?php foreach ($ue_projects as $p): $on = ($p['status'] ?? '') === 'ongoing'; ?>
            <div class="ue-instance reveal border border-line bg-dark-800 overflow-hidden flex flex-col">
                <!-- header -->
                <div class="flex items-center justify-between gap-3 p-4 lg:p-5 border-b border-line">
                    <div class="min-w-0">
                        <span class="block font-heading text-white uppercase text-base lg:text-lg truncate"><?= $h($p['name']) ?></span>
                        <span class="block text-muted text-xs truncate"><?= $h($p['location'] ?? '') ?></span>
                    </div>
                    <span class="shrink-0 font-heading uppercase text-[10px] tracking-wider2 px-3 py-1.5 <?= $on ? 'bg-amber-500/20 text-amber-300' : 'bg-green-500/20 text-green-300' ?>">
                        <?= $on ? 'Ongoing' : 'Completed' ?>
                    </span>
                </div>
                <!-- interactive stage (JS renders) -->
                <div class="ue-stage flex-1 min-h-[260px]"></div>
                <div class="px-4 lg:px-5 py-3 border-t border-line text-muted text-xs">
                    Hover floors &middot; click a floor for its plan &middot; click a unit for details
                </div>
                <!-- per-instance data payload -->
                <script type="application/json" class="ue-data"><?= json_encode($p, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script type="application/json" id="ue-statuses"><?= json_encode($ue_status, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</section>
