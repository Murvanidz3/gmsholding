<?php
/** STATS / FEATURES — parallax band w/ count-up. Reads $stats_band + $stats */
global $stats_band, $stats;
?>
<section class="relative py-28 lg:py-36 overflow-hidden">
    <!-- Parallax background (JS translates [data-parallax]) -->
    <div class="absolute inset-0 -top-24 -bottom-24 bg-cover bg-center will-change-transform"
         data-parallax data-parallax-speed="0.25"
         style="background-image:url('<?= $stats_band['bg'] ?>');"></div>
    <div class="absolute inset-0 bg-dark/85"></div>
    <!-- yellow accent corner -->
    <div class="absolute top-0 left-0 w-1.5 h-full bg-primary/0"></div>

    <div class="container relative z-10">
        <div class="max-w-2xl mb-14 reveal">
            <span class="eyebrow mb-5"><?= $stats_band['eyebrow'] ?></span>
            <h2 class="text-section"><?= $stats_band['title'] ?></h2>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line">
            <?php foreach ($stats as $s): ?>
            <div class="reveal bg-dark/60 backdrop-blur-sm p-8 lg:p-10 text-center sm:text-left">
                <div class="flex items-baseline justify-center sm:justify-start gap-1">
                    <span class="font-heading text-5xl lg:text-6xl font-bold text-primary counter" data-target="<?= $s['value'] ?>">0</span>
                    <?php if ($s['suffix']): ?>
                    <span class="font-heading text-4xl font-bold text-primary"><?= $s['suffix'] ?></span>
                    <?php endif; ?>
                </div>
                <span class="block mt-3 font-heading uppercase text-sm tracking-wider2 text-white/80"><?= $s['label'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
