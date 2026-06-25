<?php
$page_title = 'GMS — Premium Construction & Engineering';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     HERO SLIDER
============================================================ -->
<section class="relative h-screen min-h-[640px] w-full overflow-hidden bg-dark">
    <div class="swiper hero-swiper h-full w-full">
        <div class="swiper-wrapper">
            <?php foreach ($hero_slides as $slide): ?>
            <div class="swiper-slide relative">
                <!-- Background (ken-burns on active) -->
                <div class="hero-bg absolute inset-0 bg-cover bg-center"
                     style="background-image:url('<?= $slide['bg'] ?>');"></div>
                <div class="hero-overlay absolute inset-0"></div>

                <!-- Content -->
                <div class="relative z-10 h-full">
                    <div class="container h-full flex items-center">
                        <div class="hero-anim max-w-2xl pt-24">
                            <span class="eyebrow mb-6"><?= $slide['eyebrow'] ?></span>
                            <h1 class="text-display mb-6"><?= $slide['title'] ?></h1>
                            <p class="text-lg text-white/70 max-w-xl mb-9 leading-relaxed"><?= $slide['text'] ?></p>
                            <div class="flex flex-wrap items-center gap-4">
                                <a href="<?= $slide['cta_url'] ?>" class="btn-primary"><?= $slide['cta_text'] ?></a>
                                <a href="#projects" class="btn-outline">View Projects</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Controls -->
        <div class="swiper-button-prev !left-6 !hidden lg:!flex"></div>
        <div class="swiper-button-next !right-6 !hidden lg:!flex"></div>
        <div class="swiper-pagination !bottom-8"></div>
    </div>

    <!-- Vertical "scroll" hint -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 hidden md:flex flex-col items-center gap-2 text-muted">
        <span class="text-[11px] uppercase tracking-wider2 [writing-mode:vertical-rl] rotate-180">Scroll</span>
        <span class="w-px h-10 bg-gradient-to-b from-primary to-transparent"></span>
    </div>
</section>

<!-- ============================================================
     FEATURE STRIP (under hero) — modular placeholder
============================================================ -->
<section class="relative z-20 -mt-20 lg:-mt-16">
    <div class="container">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 bg-dark-800 shadow-card border border-line">
            <?php
            $features = [
                ['no' => '01', 'title' => 'Quality Materials', 'text' => 'Only certified, durable materials on every build.'],
                ['no' => '02', 'title' => 'Expert Engineers',  'text' => '25+ years of structural engineering mastery.'],
                ['no' => '03', 'title' => 'On-Time Delivery',  'text' => 'Disciplined project management, zero delays.'],
            ];
            foreach ($features as $f): ?>
            <div class="reveal group p-9 lg:p-10 border-b sm:border-b-0 sm:border-r border-line last:border-r-0 hover:bg-dark-700 transition-colors">
                <span class="font-heading text-5xl text-primary/30 group-hover:text-primary transition-colors"><?= $f['no'] ?></span>
                <h3 class="text-xl mt-4 mb-2"><?= $f['title'] ?></h3>
                <p class="text-muted leading-relaxed"><?= $f['text'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     MODULAR SECTIONS (order matters — matches GMS reference)
============================================================ -->
<?php
require __DIR__ . '/sections/unit_explorer.php';   // interactive apartment selector
require __DIR__ . '/sections/about.php';
require __DIR__ . '/sections/services.php';
require __DIR__ . '/sections/features.php';   // stats / parallax band
require __DIR__ . '/sections/projects.php';
require __DIR__ . '/sections/testimonials.php';
require __DIR__ . '/sections/team.php';
require __DIR__ . '/sections/contact.php';
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
