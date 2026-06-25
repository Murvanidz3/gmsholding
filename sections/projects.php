<?php
/** PROJECTS — reads $projects_intro + $projects from config.php */
global $projects_intro, $projects;
?>
<section id="projects" class="py-24 lg:py-32 bg-dark">
    <div class="container">
        <!-- Heading + CTA -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-16 reveal">
            <div class="max-w-2xl">
                <span class="eyebrow mb-5"><?= $projects_intro['eyebrow'] ?></span>
                <h2 class="text-section mb-5"><?= $projects_intro['title'] ?></h2>
                <p class="text-muted text-lg leading-relaxed"><?= $projects_intro['text'] ?></p>
            </div>
            <a href="#contact" class="btn-outline shrink-0">All Projects</a>
        </div>

        <!-- Masonry-style grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 auto-rows-[260px] gap-4">
            <?php foreach ($projects as $p):
                $span = $p['size'] === 'lg'
                    ? 'col-span-2 row-span-2'
                    : 'col-span-2 sm:col-span-1 lg:col-span-2';
            ?>
            <a href="<?= $p['url'] ?>" class="reveal group relative overflow-hidden <?= $span ?>">
                <img src="<?= $p['image'] ?>" alt="<?= $p['title'] ?>"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 ease-out-expo group-hover:scale-110" loading="lazy">

                <!-- gradient -->
                <span class="absolute inset-0 bg-gradient-to-t from-dark via-dark/20 to-transparent opacity-80 group-hover:opacity-95 transition-opacity"></span>

                <!-- caption -->
                <div class="absolute inset-x-0 bottom-0 p-7 translate-y-2 group-hover:translate-y-0 transition-transform duration-500 ease-out-expo">
                    <span class="inline-block bg-primary text-dark px-3 py-1 font-heading uppercase text-[11px] tracking-wider2 mb-3"><?= $p['category'] ?></span>
                    <h3 class="text-xl lg:text-2xl text-white"><?= $p['title'] ?></h3>
                </div>

                <!-- corner plus -->
                <span class="absolute top-5 right-5 grid place-items-center w-11 h-11 bg-primary text-dark opacity-0 -translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 17L17 7M17 7H9m8 0v8"/></svg>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
