<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!function_exists('e')) {
    function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
function admin_url(string $path = ''): string { return BASE_URL . '/admin/' . ltrim($path, '/'); }

/** Flash message helpers (survive the PRG redirect via session). */
function gms_flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function gms_take_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function gms_admin_header(string $title, string $active = 'dashboard'): void {
    $user = e($_SESSION['admin_user'] ?? 'admin');
    $nav = [
        'dashboard' => ['label' => 'Dashboard', 'url' => admin_url('index.php')],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> — GMS Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="bg-dark text-white/80 font-body min-h-screen flex flex-col">
    <header class="border-b border-line bg-dark-800 sticky top-0 z-40">
        <div class="max-w-8xl mx-auto px-5 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="<?= admin_url('index.php') ?>" class="flex items-center gap-2">
                    <span class="inline-block w-7 h-7 bg-primary skew-x-[-8deg]"></span>
                    <span class="font-heading text-xl font-bold text-white uppercase">GMS</span>
                    <span class="font-heading text-[11px] uppercase tracking-wider2 text-muted self-end mb-1">Admin</span>
                </a>
                <nav class="hidden sm:flex items-center gap-5 text-sm">
                    <?php foreach ($nav as $key => $item): ?>
                        <a href="<?= $item['url'] ?>" class="font-heading uppercase tracking-wider2 text-xs <?= $active === $key ? 'text-primary' : 'text-muted hover:text-white' ?> transition-colors"><?= e($item['label']) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="flex items-center gap-5 text-sm">
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="text-muted hover:text-primary transition-colors">View Site ↗</a>
                <span class="text-line">|</span>
                <span class="text-muted hidden sm:inline">Hi, <?= $user ?></span>
                <a href="<?= admin_url('logout.php') ?>" class="btn-outline !px-4 !py-2 !text-xs">Logout</a>
            </div>
        </div>
    </header>
    <main class="flex-1 max-w-8xl w-full mx-auto px-5 lg:px-8 py-10">
    <?php
    if ($f = gms_take_flash()):
        $ok = $f['type'] === 'success';
        $cls = $ok ? 'bg-primary/10 border-primary/40 text-primary' : 'bg-red-500/10 border-red-500/30 text-red-300';
    ?>
        <div class="flex items-center gap-3 border <?= $cls ?> px-4 py-3 mb-8 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><?php if ($ok): ?><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/><?php else: ?><path stroke-linecap="round" d="M12 9v4m0 4h.01"/><?php endif; ?></svg>
            <?= e($f['msg']) ?>
        </div>
    <?php endif;
}

function gms_admin_footer(): void {
    ?>
    </main>
    <footer class="border-t border-line bg-dark-800 py-5">
        <div class="max-w-8xl mx-auto px-5 lg:px-8 text-xs text-muted flex justify-between">
            <span>&copy; <?= date('Y') ?> GMS — Admin Panel</span>
            <span>Flat-file CMS</span>
        </div>
    </footer>
    <!-- Reusable polygon mapper (no-ops on pages without .gms-mapper) -->
    <script src="<?= asset('js/admin-mapper.js') ?>"></script>
</body>
</html>
    <?php
}
