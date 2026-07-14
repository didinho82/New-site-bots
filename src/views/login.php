<?php /** @var string $appName */ ?>
<div class="auth-card">
    <div class="auth-logo"><span class="logo">◆</span> <?= e($appName) ?></div>
    <h1>Entrar</h1>
    <p class="muted">Acesse o painel para criar e gerenciar seus bots do Telegram.</p>
    <form method="post" action="?page=login">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="login">
        <label>Email
            <input type="email" name="email" required autofocus placeholder="voce@email.com">
        </label>
        <label>Senha
            <input type="password" name="password" required placeholder="••••••••">
        </label>
        <button type="submit" class="btn-primary btn-block">Entrar</button>
    </form>
    <p class="auth-switch">Não tem conta? <a href="?page=register">Criar conta</a></p>
</div>
