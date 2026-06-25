<?php
require_once __DIR__ . '/includes/auth.php';
if (admin_is_logged_in()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Session expired — please try again.';
    } elseif (admin_attempt_login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: index.php'); exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
$csrf = admin_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — GMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-dark text-white/80 font-body min-h-screen grid place-items-center p-5">

    <div class="w-full max-w-md">
        <!-- Brand -->
        <div class="flex items-center justify-center gap-2 mb-8">
            <span class="inline-block w-8 h-8 bg-primary skew-x-[-8deg]"></span>
            <span class="font-heading text-2xl font-bold text-white uppercase tracking-tightest">GMS</span>
            <span class="font-heading text-xs uppercase tracking-wider2 text-muted self-end mb-1">Admin</span>
        </div>

        <div class="bg-dark-800 border border-line shadow-card p-8 lg:p-10">
            <h1 class="text-2xl mb-1">Sign In</h1>
            <p class="text-muted text-sm mb-8">Enter your credentials to manage site content.</p>

            <?php if ($error): ?>
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-300 text-sm px-4 py-3 mb-6">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="post" action="login.php" class="space-y-5" novalidate>
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div>
                    <label for="username" class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Username</label>
                    <input id="username" name="username" type="text" required autofocus autocomplete="username"
                           class="w-full bg-dark-700 border border-line px-4 py-3.5 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors"
                           placeholder="admin">
                </div>

                <div>
                    <label for="password" class="block font-heading uppercase text-xs tracking-wider2 text-muted mb-2">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full bg-dark-700 border border-line px-4 py-3.5 text-white placeholder:text-muted focus:outline-none focus:border-primary transition-colors"
                           placeholder="••••••••">
                </div>

                <button type="submit" class="btn-primary w-full">
                    Sign In
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>
        </div>

        <p class="text-center text-muted text-xs mt-6">&copy; <?= date('Y') ?> GMS — Admin Panel</p>
    </div>
</body>
</html>
