<?php /** @var string $content @var string $appName @var array $flashes @var string $page */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($appName) ?> — Criador de Bots do Telegram</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= (isset($page) && in_array($page, ['login','register'], true)) ? 'auth-body' : '' ?>">
<?php $loggedIn = current_user_id() !== null; ?>
<?php if ($loggedIn && !in_array($page ?? '', ['login','register'], true)): ?>
    <header class="topbar">
        <a class="brand" href="?page=dashboard">
            <span class="logo">◆</span> <?= e($appName) ?>
        </a>
        <nav class="topnav">
            <a href="?page=dashboard" class="<?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>">Meus Bots</a>
            <span class="user"><?= e($_SESSION['user_email'] ?? '') ?></span>
            <a class="btn-ghost" href="?page=logout">Sair</a>
        </nav>
    </header>
<?php endif; ?>

<main class="<?= (isset($page) && in_array($page, ['login','register'], true)) ? 'auth-main' : 'app-main' ?>">
    <?php foreach ($flashes as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
</main>

<script src="assets/app.js"></script>
</body>
</html>
