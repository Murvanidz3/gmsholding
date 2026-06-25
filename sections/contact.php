<?php
/** CONTACT — reads $contact. Form is HTML-only, ready for PHP handler (Phase 2 admin). */
global $contact;

function gms_contact_icon($key) {
    $icons = [
        'pin'   => '<path d="M12 21s-7-6.2-7-11a7 7 0 1114 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone' => '<path d="M6.6 10.8a15 15 0 006.6 6.6l2.2-2.2a1 1 0 011-.2 11 11 0 003.5.6 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11 11 0 00.6 3.5 1 1 0 01-.2 1l-2.3 2.3z"/>',
        'mail'  => '<rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="M4 7l8 5 8-5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    ];
    return $icons[$key] ?? '';
}
?>
<section id="contact" class="py-24 lg:py-32 bg-dark-800 border-t border-line">
    <div class="container">
        <!-- Heading -->
        <div class="max-w-2xl mb-16 reveal">
            <span class="eyebrow mb-5"><?= $contact['eyebrow'] ?></span>
            <h2 class="text-section mb-5"><?= $contact['title'] ?></h2>
            <p class="text-muted text-lg leading-relaxed"><?= $contact['text'] ?></p>
        </div>

        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            <!-- LEFT: info cards + map -->
            <div class="reveal space-y-8">
                <div class="grid sm:grid-cols-2 gap-px bg-line border border-line">
                    <?php foreach ($contact['cards'] as $card): ?>
                    <div class="bg-dark p-7 group">
                        <span class="grid place-items-center w-12 h-12 bg-primary/10 text-primary group-hover:bg-primary group-hover:text-dark transition-colors mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><?= gms_contact_icon($card['icon']) ?></svg>
                        </span>
                        <span class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-1"><?= $card['label'] ?></span>
                        <?php if (!empty($card['href'])): ?>
                            <a href="<?= $card['href'] ?>" class="text-white hover:text-primary transition-colors"><?= $card['value'] ?></a>
                        <?php else: ?>
                            <span class="text-white"><?= $card['value'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- map -->
                <div class="relative h-72 border border-line overflow-hidden grayscale contrast-125 hover:grayscale-0 transition-all duration-700">
                    <iframe src="<?= $contact['map_embed'] ?>" title="Office location"
                            class="absolute inset-0 w-full h-full" style="border:0;"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <!-- RIGHT: form (HTML only, ready for PHP) -->
            <div class="reveal bg-dark border border-line p-8 lg:p-10">
                <form action="#" method="post" class="space-y-5" novalidate>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Full Name</label>
                            <input id="name" name="name" type="text" required placeholder="John Doe"
                                   class="w-full bg-dark-700 border border-line px-4 py-3.5 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors">
                        </div>
                        <div>
                            <label for="email" class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Email</label>
                            <input id="email" name="email" type="email" required placeholder="you@email.com"
                                   class="w-full bg-dark-700 border border-line px-4 py-3.5 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="phone" class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Phone</label>
                            <input id="phone" name="phone" type="tel" placeholder="+1 800 000 0000"
                                   class="w-full bg-dark-700 border border-line px-4 py-3.5 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors">
                        </div>
                        <div>
                            <label for="subject" class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Subject</label>
                            <select id="subject" name="subject"
                                    class="w-full bg-dark-700 border border-line px-4 py-3.5 text-white focus:outline-none focus:border-primary transition-colors">
                                <?php foreach ($contact['subjects'] as $subj): ?>
                                <option value="<?= htmlspecialchars($subj) ?>"><?= $subj ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Message</label>
                        <textarea id="message" name="message" rows="5" required placeholder="Tell us about your project..."
                                  class="w-full bg-dark-700 border border-line px-4 py-3.5 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors resize-none"></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full sm:w-auto">
                        Send Message
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
