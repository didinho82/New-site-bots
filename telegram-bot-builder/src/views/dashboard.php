<?php /** @var array $bots */ ?>
<div class="page-head">
    <div>
        <h1>Meus Bots</h1>
        <p class="muted">Crie um bot colando o token gerado pelo <a href="https://t.me/BotFather" target="_blank" rel="noopener">@BotFather</a>.</p>
    </div>
</div>

<div class="grid">
    <section class="card create-bot">
        <h2>Criar novo bot</h2>
        <form method="post" action="?page=dashboard">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_bot">
            <label>Token do bot
                <input type="text" name="token" required placeholder="123456789:ABCdef..." autocomplete="off">
            </label>
            <button type="submit" class="btn-primary">Validar e criar</button>
        </form>
        <p class="hint">Ao validar, buscamos o nome do bot na API do Telegram (getMe).</p>
    </section>

    <section class="card stats">
        <h2>Resumo</h2>
        <div class="stat-row">
            <div class="stat"><span class="stat-num"><?= count($bots) ?></span><span class="stat-label">Bots</span></div>
            <div class="stat">
                <span class="stat-num"><?= count(array_filter($bots, fn($b) => $b['webhook_set'])) ?></span>
                <span class="stat-label">Ativos</span>
            </div>
        </div>
    </section>
</div>

<h2 class="section-title">Bots cadastrados</h2>
<?php if (!$bots): ?>
    <div class="empty">Nenhum bot ainda. Crie o primeiro acima.</div>
<?php else: ?>
    <div class="bot-list">
        <?php foreach ($bots as $b): ?>
            <div class="bot-card">
                <div class="bot-avatar"><?= e(mb_strtoupper(mb_substr($b['first_name'] ?: '?', 0, 1))) ?></div>
                <div class="bot-info">
                    <div class="bot-name"><?= e($b['first_name']) ?>
                        <span class="badge <?= $b['webhook_set'] ? 'badge-on' : 'badge-off' ?>">
                            <?= $b['webhook_set'] ? 'ativo' : 'inativo' ?>
                        </span>
                    </div>
                    <div class="bot-username">@<?= e($b['username']) ?></div>
                </div>
                <div class="bot-actions">
                    <a class="btn-primary" href="?page=commands&bot=<?= (int) $b['id'] ?>">Gerenciar</a>
                    <form method="post" action="?page=dashboard" onsubmit="return confirm('Remover este bot e seus comandos?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_bot">
                        <input type="hidden" name="bot_id" value="<?= (int) $b['id'] ?>">
                        <button type="submit" class="btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
