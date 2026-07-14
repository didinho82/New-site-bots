<?php

declare(strict_types=1);

/**
 * Endpoint que recebe os updates do Telegram (webhook).
 * URL: /webhook.php?bot={id}
 *
 * Fluxo: valida o secret -> encontra o comando -> executa o script Python ->
 * envia a saída (stdout) de volta como resposta, sem delay.
 */

/** @var array $config @var PDO $pdo @var Repo $repo */
[$config, $pdo, $repo] = require dirname(__DIR__) . '/src/bootstrap.php';

// Responde 200 sempre para o Telegram não reenfileirar.
register_shutdown_function(function () {
    if (!headers_sent()) {
        http_response_code(200);
    }
});

$botId = (int) ($_GET['bot'] ?? 0);
$bot = $repo->findBotById($botId);
if (!$bot) {
    http_response_code(404);
    exit;
}

// Valida o secret_token enviado pelo Telegram no header.
$header = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
if (!hash_equals($bot['webhook_secret'], $header)) {
    http_response_code(403);
    exit;
}

$raw = file_get_contents('php://input');
$update = json_decode($raw, true);
if (!is_array($update)) {
    exit;
}

$message = $update['message'] ?? $update['edited_message'] ?? null;
if (!$message || !isset($message['text'])) {
    exit; // Só tratamos mensagens de texto neste fluxo.
}

$chatId = $message['chat']['id'] ?? null;
$text   = trim((string) $message['text']);
if ($chatId === null || $text === '') {
    exit;
}

// Determina o comando: primeira palavra, removendo @nomedobot.
$firstWord = explode(' ', $text)[0];
$firstWord = explode('@', $firstWord)[0];

$cmd = $repo->findCommandByName($botId, $firstWord);
if (!$cmd) {
    // Comando coringa opcional: "*" recebe qualquer mensagem.
    $cmd = $repo->findCommandByName($botId, '*');
}
if (!$cmd) {
    exit;
}

$runner = new ScriptRunner($config['python_bin'], (int) $config['script_timeout'], dirname(__DIR__) . '/data/scripts');
$result = $runner->run($cmd['script'], [
    'bot_token'  => $bot['token'],
    'chat_id'    => $chatId,
    'user_id'    => $message['from']['id'] ?? '',
    'first_name' => $message['from']['first_name'] ?? '',
    'username'   => $message['from']['username'] ?? '',
    'text'       => $text,
    'command'    => $cmd['name'],
    'args'       => trim(substr($text, strlen($firstWord))),
    'update'     => $update,
]);

$reply = $result['output'];
if ($reply !== '') {
    (new Telegram($bot['token']))->sendMessage($chatId, $reply);
}

http_response_code(200);
