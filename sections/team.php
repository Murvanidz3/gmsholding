<?php
/** TEAM — reads $team_intro + $team. Social keys map to inline SVGs. */
global $team_intro, $team;

function gms_social_icon($key) {
    $icons = [
        'facebook' => '<path d="M14 9h3V6h-3c-1.7 0-3 1.3-3 3v2H9v3h2v6h3v-6h2.5l.5-3H14V9z"/>',
        'twitter'  => '<path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1A4 4 0 0012 8.8c0 .3 0 .6.1.9-3.3-.2-6.3-1.8-8.3-4.2-.4.6-.6 1.3-.6 2 0 1.4.7 2.6 1.8 3.3-.6 0-1.2-.2-1.8-.5v.1c0 1.9 1.4 3.5 3.2 3.9-.6.2-1.2.2-1.8.1.5 1.6 2 2.7 3.7 2.7A8 8 0 012 18.6 11.3 11.3 0 008.1 20c7.3 0 11.3-6 11.3-11.3v-.5c.8-.6 1.5-1.3 2-2.1z"/>',
        'linkedin' => '<path d="M6.5 8.5h-3v9h3v-9zM5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm5.5 1.5h-3v9h3v-4.8c0-1.3 1.7-1.4 1.7 0v4.8h3v-5.5c0-3.2-3.4-3.1-4.7-1.5V8.5z"/>',
        'instagram'=> '<path d="M12 8a4 4 0 100 8 4 4 0 000-8zm0 6.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5zM16.5 3h-9A4.5 4.5 0 003 7.5v9A4.5 4.5 0 007.5 21h9a4.5 4.5 0 004.5-4.5v-9A4.5 4.5 0 0016.5 3zm2.5 13.5A2.5 2.5 0 0116.5 19h-9A2.5 2.5 0 015 16.5v-9A2.5 2.5 0 017.5 5h9A2.5 2.5 0 0119 7.5v9zM17.5 7a1 1 0 11-2 0 1 1 0 012 0z"/>',
    ];
    return $icons[$key] ?? '';
}
?>
<section id="team" class="py-24 lg:py-32 bg-dark">
    <div class="container">
        <!-- Heading -->
        <div class="max-w-2xl mb-16 reveal">
            <span class="eyebrow mb-5"><?= $team_intro['eyebrow'] ?></span>
            <h2 class="text-section mb-5"><?= $team_intro['title'] ?></h2>
            <p class="text-muted text-lg leading-relaxed"><?= $team_intro['text'] ?></p>
        </div>

        <!-- Grid -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($team as $m): ?>
            <article class="reveal group relative overflow-hidden bg-dark-800">
                <!-- photo -->
                <div class="relative overflow-hidden aspect-[3/4]">
                    <img src="<?= $m['photo'] ?>" alt="<?= $m['name'] ?>"
                         class="w-full h-full object-cover transition-transform duration-700 ease-out-expo group-hover:scale-110" loading="lazy">
                    <span class="absolute inset-0 bg-gradient-to-t from-dark via-transparent to-transparent opacity-70"></span>

                    <!-- social reveal -->
                    <div class="absolute right-4 bottom-4 flex flex-col gap-2 translate-x-16 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-500 ease-out-expo">
                        <?php foreach ($m['social'] as $key => $url): ?>
                        <a href="<?= $url ?>" aria-label="<?= $key ?>"
                           class="grid place-items-center w-10 h-10 bg-primary text-dark hover:bg-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><?= gms_social_icon($key) ?></svg>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- name plate -->
                <div class="p-6 relative">
                    <span class="absolute left-0 top-0 h-full w-1 bg-primary scale-y-0 group-hover:scale-y-100 origin-top transition-transform duration-500"></span>
                    <h3 class="text-lg mb-1 group-hover:text-primary transition-colors"><?= $m['name'] ?></h3>
                    <p class="text-muted text-sm uppercase tracking-wider2"><?= $m['role'] ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
