<?php
/** TESTIMONIALS — Swiper. Reads $testimonials_intro + $testimonials */
global $testimonials_intro, $testimonials;
?>
<section class="py-24 lg:py-32 bg-dark-800 border-y border-line">
    <div class="container">
        <!-- Heading -->
        <div class="max-w-2xl mb-16 reveal">
            <span class="eyebrow mb-5"><?= $testimonials_intro['eyebrow'] ?></span>
            <h2 class="text-section mb-5"><?= $testimonials_intro['title'] ?></h2>
            <p class="text-muted text-lg leading-relaxed"><?= $testimonials_intro['text'] ?></p>
        </div>

        <!-- Slider -->
        <div class="swiper testimonials-swiper reveal">
            <div class="swiper-wrapper">
                <?php foreach ($testimonials as $t): ?>
                <div class="swiper-slide h-auto">
                    <figure class="relative h-full bg-dark border border-line p-9 lg:p-10 flex flex-col">
                        <!-- quote mark -->
                        <svg class="w-12 h-12 text-primary/30 mb-6" fill="currentColor" viewBox="0 0 24 24"><path d="M9.5 6C6.5 7.5 5 10 5 13.5V18h5v-5H7.5c0-2 .8-3.3 2.8-4.2L9.5 6zm9 0C15.5 7.5 14 10 14 13.5V18h5v-5h-2.5c0-2 .8-3.3 2.8-4.2L18.5 6z"/></svg>

                        <!-- rating -->
                        <div class="flex gap-1 mb-5">
                            <?php for ($i = 0; $i < $t['rating']; $i++): ?>
                            <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <?php endfor; ?>
                        </div>

                        <blockquote class="text-white/85 text-lg leading-relaxed mb-8 flex-1">“<?= $t['text'] ?>”</blockquote>

                        <figcaption class="flex items-center gap-4 pt-6 border-t border-line">
                            <img src="<?= $t['avatar'] ?>" alt="<?= $t['name'] ?>" class="w-14 h-14 object-cover rounded-full" loading="lazy">
                            <div>
                                <span class="block font-heading text-white uppercase tracking-wide"><?= $t['name'] ?></span>
                                <span class="block text-primary text-sm"><?= $t['role'] ?></span>
                            </div>
                        </figcaption>
                    </figure>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="swiper-pagination testimonials-pagination !relative !mt-12"></div>
        </div>
    </div>
</section>
