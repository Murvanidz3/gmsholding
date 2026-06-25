</main><!-- /#top -->

<!-- ============ FOOTER ============ -->
<footer id="site-footer" class="bg-dark-800 border-t border-line pt-20 pb-8">
    <div class="container grid gap-12 md:grid-cols-2 lg:grid-cols-4">
        <!-- Brand -->
        <div>
            <a href="index.php" class="flex items-center gap-2 font-heading text-2xl font-bold text-white uppercase mb-5">
                <span class="inline-block w-8 h-8 bg-primary skew-x-[-8deg]"></span><?= $site['name'] ?>
            </a>
            <p class="text-muted leading-relaxed mb-6 max-w-xs">
                Premium construction & engineering delivering precision and craftsmanship since 1998.
            </p>
            <div class="flex items-center gap-3">
                <?php foreach ($site['social'] as $name => $url): ?>
                    <a href="<?= $url ?>" aria-label="<?= $name ?>"
                       class="grid place-items-center w-10 h-10 border border-white/15 text-white/70 hover:bg-primary hover:text-dark hover:border-primary transition-all capitalize text-xs">
                        <?= strtoupper(substr($name, 0, 1)) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick links -->
        <div>
            <h4 class="text-lg mb-6">Company</h4>
            <ul class="space-y-3">
                <?php foreach ($nav as $item): ?>
                    <li><a href="<?= $item['url'] ?>" class="text-muted hover:text-primary transition-colors"><?= $item['label'] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Contact -->
        <div>
            <h4 class="text-lg mb-6">Get In Touch</h4>
            <ul class="space-y-3 text-muted">
                <li><?= $site['address'] ?></li>
                <li><a href="tel:<?= preg_replace('/\s+/', '', $site['phone']) ?>" class="hover:text-primary transition-colors"><?= $site['phone'] ?></a></li>
                <li><a href="mailto:<?= $site['email'] ?>" class="hover:text-primary transition-colors"><?= $site['email'] ?></a></li>
            </ul>
        </div>

        <!-- Newsletter -->
        <div>
            <h4 class="text-lg mb-6">Newsletter</h4>
            <p class="text-muted mb-4">Subscribe for project updates & insights.</p>
            <form class="flex" onsubmit="return false;">
                <input type="email" required placeholder="Email address"
                       class="flex-1 bg-dark-700 border border-line px-4 py-3 text-sm text-white placeholder:text-muted focus:outline-none focus:border-primary">
                <button class="bg-primary text-dark px-5 hover:bg-white transition-colors" aria-label="Subscribe">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>
        </div>
    </div>

    <div class="container mt-16 pt-6 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-muted">
        <p>&copy; <?= date('Y') ?> <?= $site['name'] ?>. All rights reserved.</p>
        <p>Crafted with precision.</p>
    </div>
</footer>

<!-- Back to top -->
<a href="#top" id="to-top" class="fixed bottom-6 right-6 z-50 grid place-items-center w-12 h-12 bg-primary text-dark opacity-0 translate-y-4 pointer-events-none transition-all duration-300" aria-label="Back to top">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 15l7-7 7 7"/></svg>
</a>

<!-- Swiper (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<!-- Site JS -->
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/unit-explorer.js') ?>"></script>
</body>
</ht