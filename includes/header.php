<?php
require_once __DIR__ . '/config.php';
$page_title = $page_title ?? ($site['name'] . ' — ' . $site['tagline']);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($site['tagline']) ?> — premium construction & engineering.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Swiper (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <!-- Compiled Tailwind -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="bg-dark text-white/80 font-body">

<!-- ============ TOP BAR ============ -->
<div class="hidden lg:block border-b border-line bg-dark-800/60">
    <div class="container flex items-center justify-between h-11 text-xs tracking-wide text-muted">
        <div class="flex items-center gap-6">
            <a href="mailto:<?= $site['email'] ?>" class="hover:text-primary transition-colors"><?= $site['email'] ?></a>
            <span class="text-line">|</span>
            <span><?= $site['address'] ?></span>
        </div>
        <div class="flex items-center gap-4">
            <?php foreach ($site['social'] as $name => $url): ?>
                <a href="<?= $url ?>" aria-label="<?= $name ?>" class="hover:text-primary transition-colors capitalize"><?= ucfirst($name) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ============ HEADER / NAV ============ -->
<header id="site-header" class="absolute top-11 left-0 right-0 z-50 transition-all duration-500 ease-out-expo">
    <div class="container flex items-center justify-between h-20 lg:h-24">
        <!-- Logo -->
        <a href="index.php" class="flex items-center gap-2 font-heading text-2xl font-700 tracking-tightest text-white">
            <span class="inline-block w-8 h-8 bg-primary skew-x-[-8deg]"></span>
            <span class="uppercase font-bold"><?= $site['name'] ?></span>
        </a>

        <!-- Desktop nav -->
        <nav class="hidden lg:flex items-center gap-9">
            <?php foreach ($nav as $item): ?>
                <a href="<?= $item['url'] ?>"
                   class="font-heading uppercase text-sm tracking-wider2 text-white/85 hover:text-primary transition-colors">
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Right actions -->
        <div class="hidden lg:flex items-center gap-5">
            <a href="tel:<?= preg_replace('/\s+/', '', $site['phone']) ?>" class="flex items-center gap-3 group">
                <span class="grid place-items-center w-11 h-11 rounded-full border border-white/20 text-primary group-hover:bg-primary group-hover:text-dark transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.05-.24 11.36 11.36 0 003.55.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.55 1 1 0 01-.24 1.05l-2.2 2.19z"/></svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-[11px] uppercase tracking-wider2 text-muted">Call Anytime</span>
                    <span class="block font-heading text-white font-medium"><?= $site['phone'] ?></span>
                </span>
            </a>
            <a href="#contact" class="btn-primary">Get A Quote</a>
        </div>

        <!-- Mobile toggle -->
        <button id="nav-toggle" class="lg:hidden grid place-items-center w-11 h-11 text-white" aria-label="Open menu">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
</header>

<!-- ============ MOBILE DRAWER ============ -->
<div id="mobile-menu" class="fixed inset-0 z-[60] lg:hidden invisible opacity-0 transition-all duration-300">
    <div class="absolute inset-0 bg-black/60" data-close></div>
    <div class="absolute right-0 top-0 h-full w-80 max-w-[85%] bg-dark-800 translate-x-full transition-transform duration-500 ease-out-expo p-8 flex flex-col"
         id="mobile-panel">
        <div class="flex items-center justify-between mb-10">
            <span class="font-heading text-xl font-bold text-white uppercase"><?= $site['name'] ?></span>
            <button data-close class="text-white" aria-label="Close menu">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <nav class="flex flex-col gap-1">
            <?php foreach ($nav as $item): ?>
                <a href="<?= $item['url'] ?>" data-close
                   class="font-heading uppercase tracking-wider2 text-white/85 hover:text-primary py-3 border-b border-line transition-colors">
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <a href="#contact" data-close class="btn-primary mt-8">Get A Quote</a>
        <div class="mt-auto pt-8 text-sm text-muted">
            <p class="text-primary font-heading"><?= $site['phone'] ?></p>
            <p><?= $site['email'] ?></p>
        </div>
    </div>
</div>

<!-- Page content starts -->
<main id="top">
