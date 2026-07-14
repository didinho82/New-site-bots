<?php /** @var array $bot @var array $commands @var array|null $selected @var array $config */ ?>
<div class="page-head">
    <div>
        <a class="back" href="?page=dashboard">← Meus bots</a>
        <h1><?= e($bot['first_name']) ?> <span class="muted">@<?= e($bot['username']) ?></span></h1>
    </div>
    <form method="post" action="?page=dashboard" class="webhook-toggle">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="toggle_webhook">
        <input type="hidden" name="bot_id" value="<?= (int) $bot['id'] ?>">
        <span class="badge <?= $bot['webhook_set'] ? 'badge-on' : 'badge-off' ?>">
            <?= $bot['webhook_set'] ? 'recebendo mensagens' : 'webhook desativado' ?>
        </span>
        <button type="submit" class="<?= $bot['webhook_set'] ? 'btn-danger' : 'btn-primary' ?>">
            <?= $bot['webhook_set'] ? 'Desativar' : 'Ativar bot' ?>
        </button>
    </form>
</div>

<div class="cmd-layout">
    <aside class="cmd-sidebar">
        <form method="post" action="?page=dashboard">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_command">
            <input type="hidden" name="bot_id" value="<?= (int) $bot['id'] ?>">
            <button type="submit" class="btn-primary btn-block">+ Criar comando</button>
        </form>
        <ul class="cmd-list">
            <?php foreach ($commands as $c): ?>
                <li>
                    <a href="?page=commands&bot=<?= (int) $bot['id'] ?>&cmd=<?= (int) $c['id'] ?>"
                       class="<?= ($selected && (int) $selected['id'] === (int) $c['id']) ? 'active' : '' ?>">
                        <span class="cmd-dot <?= $c['enabled'] ? 'on' : 'off' ?>"></span>
                        <?= e($c['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (!$commands): ?>
                <li class="muted small">Nenhum comando ainda.</li>
            <?php endif; ?>
        </ul>
    </aside>

    <section class="cmd-editor">
        <?php if (!$selected): ?>
            <div class="empty">Selecione ou crie um comando para editar o script.</div>
        <?php else: ?>
            <form method="post" action="?page=dashboard" id="cmd-form"
                  data-test-url="?page=test_command"
                  data-bot-id="<?= (int) $bot['id'] ?>"
                  data-command-id="<?= (int) $selected['id'] ?>"
                  data-csrf="<?= e(csrf_token()) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_command">
                <input type="hidden" name="bot_id" value="<?= (int) $bot['id'] ?>">
                <input type="hidden" name="command_id" value="<?= (int) $selected['id'] ?>">

                <div class="editor-head">
                    <label class="cmd-name-field">
                        <span>Comando</span>
                        <input type="text" name="name" value="<?= e($selected['name']) ?>" pattern="/.*" placeholder="/start">
                    </label>
                    <label class="switch">
                        <input type="checkbox" name="enabled" <?= $selected['enabled'] ? 'checked' : '' ?>>
                        <span>Ativo</span>
                    </label>
                </div>

                <label class="script-label">Script Python
                    <span class="hint">Use <code>print()</code> para responder. Variáveis: <code>MESSAGE_TEXT</code>, <code>CHAT_ID</code>, <code>FIRST_NAME</code>, <code>BOT_TOKEN</code>.</span>
                </label>
                <textarea name="script" class="code" spellcheck="false" rows="18"><?= e($selected['script']) ?></textarea>

                <div class="editor-actions">
                    <button type="submit" class="btn-primary">Salvar</button>
                    <button type="button" class="btn-secondary" id="btn-test">▶ Testar sem delay</button>
                    <button type="submit" class="btn-danger inline" form="del-form"
                            onclick="return confirm('Excluir este comando?');">Excluir</button>
                </div>
            </form>

            <form method="post" action="?page=dashboard" id="del-form" hidden>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_command">
                <input type="hidden" name="bot_id" value="<?= (int) $bot['id'] ?>">
                <input type="hidden" name="command_id" value="<?= (int) $selected['id'] ?>">
            </form>

            <div class="test-panel" id="test-panel" hidden>
                <div class="test-head">
                    <strong>Simulador</strong>
                    <input type="text" id="test-input" placeholder="<?= e($selected['name']) ?>" value="<?= e($selected['name']) ?>">
                    <button type="button" class="btn-secondary" id="btn-run">Enviar</button>
                </div>
                <pre id="test-output" class="test-output">A resposta do bot aparece aqui…</pre>
            </div>
        <?php endif; ?>
    </section>
</div>
