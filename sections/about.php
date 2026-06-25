<?php
/** ABOUT — reads $about from config.php */
global $about;
?>
<section id="about" class="py-24 lg:py-32 bg-dark overflow-hidden">
    <div class="container grid lg:grid-cols-2 gap-14 lg:gap-20 items-center">

        <!-- Images -->
        <div class="relative reveal">
            <div class="relative overflow-hidden">
                <img src="<?= $about['image'] ?>" alt="About GMS"
                     class="w-full h-[480px] object-cover" loading="lazy">
            </div>
            <!-- Secondary floating image -->
            <div class="absolute -bottom-10 -right-4 lg:right-10 w-48 h-56 border-4 border-dark hidden sm:block overflow-hidden shadow-card">
                <img src="<?= $about['image_alt'] ?>" alt="" class="w-full h-full object-cover" loading="lazy">
            </div>
            <!-- Experience badge -->
            <div class="absolute -top-6 -left-2 lg:left-6 bg-primary text-dark px-7 py-6 shadow-glow">
                <span class="block font-heading text-5xl font-bold leading-none counter" data-target="<?= $about['badge_num'] ?>">0</span>
                <span class="block font-heading uppercase text-xs tracking-wider2 mt-1 max-w-[7rem] leading-tight"><?= $about['badge_txt'] ?></span>
            </div>
        </div>

        <!-- Content -->
        <div class="reveal">
            <span class="eyebrow mb-5"><?= $about['eyebrow'] ?></span>
            <h2 class="text-section mb-6 max-w-xl"><?= $about['title'] ?></h2>
            <p class="text-muted text-lg leading-relaxed mb-8 max-w-xl"><?= $about['text'] ?></p>

            <!-- Bullet points -->
            <ul class="space-y-4 mb-10">
                <?php foreach ($about['points'] as $point): ?>
                <li class="flex items-start gap-3">
                    <span class="mt-1 grid place-items-center w-5 h-5 bg-primary text-dark shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-white/85"><?= $point ?></span>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Mini counters -->
            <div class="flex flex-wrap gap-10 mb-10">
                <?php foreach ($about['counters'] as $c): ?>
                <div>
                    <div class="flex items-baseline gap-1">
                        <span class="font-heading text-4xl font-bold text-primary counter" data-target="<?= $c['value'] ?>">0</span>
                        <span class="font-heading text-3xl font-bold text-primary"><?= $c['suffix'] ?></span>
                    </div>
                    <span class="text-muted text-sm uppercase tracking-wider2 mt-1 block"><?= $c['label'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- CTA + signature -->
            <div class="flex flex-wrap items-center gap-8">
                <a href="<?= $about['cta_url'] ?>" class="btn-primary"><?= $about['cta_text'] ?></a>
                <div class="leading-tight">
                    <span class="block font-heading text-xl text-white italic"><?= $about['signature'] ?></span>
                    <span class="block text-muted text-sm uppercase tracking-wider2"><?= $about['role'] ?></span>
                </div>
            </div>
        </div>
    </div>
</section>
