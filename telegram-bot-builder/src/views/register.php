<?php /** @var string $appName */ ?>
<div class="auth-card">
    <div class="auth-logo"><span class="logo">◆</span> <?= e($appName) ?></div>
    <h1>Criar conta</h1>
    <p class="muted">Comece a criar bots do Telegram em minutos.</p>
    <form method="post" action="?page=register">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="register">
        <label>Email
            <input type="email" name="email" required autofocus placeholder="voce@email.com">
        </label>
        <label>Senha
            <input type="password" name="password" required minlength="6" placeholder="mínimo 6 caracteres">
        </label>
        <button type="submit" class="btn-primary btn-block">Criar conta</button>
    </form>
    <p class="auth-switch">Já tem conta? <a href="?page=login">Entrar</a></p>
</div>
