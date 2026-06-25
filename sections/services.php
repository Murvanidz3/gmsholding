<?php
/** SERVICES — reads $services_intro + $services from config.php */
global $services_intro, $services;

/** Resolve an icon key to its inner SVG via the central registry (config.php). */
function gms_icon($key) {
    $icons = gms_service_icons();
    return $icons[$key] ?? $icons['building'];
}
?>
<section id="services" class="py-24 lg:py-32 bg-dark-800 border-y border-line">
    <div class="container">
        <!-- Heading -->
        <div class="max-w-2xl mb-16 reveal">
            <span class="eyebrow mb-5"><?= $services_intro['eyebrow'] ?></span>
            <h2 class="text-section mb-5"><?= $services_intro['title'] ?></h2>
            <p class="text-muted text-lg leading-relaxed"><?= $services_intro['text'] ?></p>
        </div>

        <!-- Grid -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-line">
            <?php foreach ($services as $i => $s): ?>
            <div class="reveal group relative bg-dark p-9 lg:p-10 hover:bg-primary transition-colors duration-500 ease-out-expo">
                <!-- index watermark -->
                <span class="absolute top-6 right-8 font-heading text-5xl font-bold text-white/5 group-hover:text-dark/10 transition-colors">
                    <?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?>
                </span>

                <!-- icon -->
                <span class="grid place-items-center w-16 h-16 border border-line text-primary group-hover:bg-dark group-hover:border-dark group-hover:text-primary transition-all duration-500 mb-7">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><?= gms_icon($s['icon']) ?></svg>
                </span>

                <h3 class="text-xl mb-3 group-hover:text-dark transition-colors"><?= $s['title'] ?></h3>
                <p class="text-muted leading-relaxed mb-6 group-hover:text-dark/80 transition-colors"><?= $s['text'] ?></p>

                <a href="#contact" class="inline-flex items-center gap-2 font-heading uppercase text-xs tracking-wider2 text-primary group-hover:text-dark transition-colors">
                    Read More
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
