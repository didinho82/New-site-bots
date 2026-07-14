<?php

declare(strict_types=1);

session_start();

/** @var array $config @var PDO $pdo @var Repo $repo @var Auth $auth */
[$config, $pdo, $repo, $auth] = require dirname(__DIR__) . '/src/bootstrap.php';

$page   = $_GET['page'] ?? 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];

/* --------------------------------------------------------------------------
 * Ações (POST)
 * ------------------------------------------------------------------------ */
if ($method === 'POST' && $page !== 'test_command') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'register': {
            [$ok, $msg] = $auth->register($_POST['email'] ?? '', $_POST['password'] ?? '');
            flash($ok ? 'success' : 'error', $msg);
            if ($ok) {
                $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
                redirect('?page=dashboard');
            }
            redirect('?page=register');
        }

        case 'login': {
            [$ok, $msg] = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
            flash($ok ? 'success' : 'error', $msg);
            redirect($ok ? '?page=dashboard' : '?page=login');
        }

        case 'create_bot': {
            $userId = require_login();
            $token  = trim($_POST['token'] ?? '');
            if ($token === '') {
                flash('error', 'Cole o token do bot.');
                redirect('?page=dashboard');
            }
            $me = (new Telegram($token))->getMe();
            if (empty($me['ok'])) {
                flash('error', 'Token inválido: ' . ($me['description'] ?? 'não foi possível validar.'));
                redirect('?page=dashboard');
            }
            $botId = $repo->createBot($userId, $token, $me['result']);
            $repo->createCommand($botId, '/start');
            flash('success', 'Bot "' . ($me['result']['first_name'] ?? '') . '" (@' . ($me['result']['username'] ?? '') . ') criado com sucesso!');
            redirect('?page=commands&bot=' . $botId);
        }

        case 'delete_bot': {
            $userId = require_login();
            $bot = $repo->findBot((int) ($_POST['bot_id'] ?? 0), $userId);
            if ($bot) {
                if ($bot['webhook_set']) {
                    (new Telegram($bot['token']))->deleteWebhook();
                }
                $repo->deleteBot((int) $bot['id'], $userId);
                flash('success', 'Bot removido.');
            }
            redirect('?page=dashboard');
        }

        case 'toggle_webhook': {
            $userId = require_login();
            $bot = $repo->findBot((int) ($_POST['bot_id'] ?? 0), $userId);
            if (!$bot) {
                redirect('?page=dashboard');
            }
            $tg = new Telegram($bot['token']);
            if ($bot['webhook_set']) {
                $tg->deleteWebhook();
                $repo->setWebhookFlag((int) $bot['id'], false);
                flash('success', 'Webhook desativado.');
            } else {
                $url = rtrim($config['app_url'], '/') . '/webhook.php?bot=' . $bot['id'];
                $res = $tg->setWebhook($url, $bot['webhook_secret']);
                if (!empty($res['ok'])) {
                    $repo->setWebhookFlag((int) $bot['id'], true);
                    flash('success', 'Webhook ativado! O bot já recebe mensagens.');
                } else {
                    flash('error', 'Falha ao ativar webhook: ' . ($res['description'] ?? '') . ' (precisa de HTTPS público)');
                }
            }
            redirect('?page=commands&bot=' . $bot['id']);
        }

        case 'create_command': {
            $userId = require_login();
            $bot = $repo->findBot((int) ($_POST['bot_id'] ?? 0), $userId);
            if ($bot) {
                $cmdId = $repo->createCommand((int) $bot['id'], '/start');
                flash('success', 'Comando criado. Edite o nome e o script.');
                redirect('?page=commands&bot=' . $bot['id'] . '&cmd=' . $cmdId);
            }
            redirect('?page=dashboard');
        }

        case 'save_command': {
            $userId = require_login();
            $bot = $repo->findBot((int) ($_POST['bot_id'] ?? 0), $userId);
            $cmd = $bot ? $repo->findCommand((int) ($_POST['command_id'] ?? 0), (int) $bot['id']) : null;
            if ($cmd) {
                $name = trim($_POST['name'] ?? '');
                if ($name === '') {
                    $name = $cmd['name'];
                }
                $repo->updateCommand(
                    (int) $cmd['id'],
                    (int) $bot['id'],
                    $name,
                    $_POST['script'] ?? '',
                    isset($_POST['enabled'])
                );
                flash('success', 'Comando salvo.');
                redirect('?page=commands&bot=' . $bot['id'] . '&cmd=' . $cmd['id']);
            }
            redirect('?page=dashboard');
        }

        case 'delete_command': {
            $userId = require_login();
            $bot = $repo->findBot((int) ($_POST['bot_id'] ?? 0), $userId);
            if ($bot) {
                $repo->deleteCommand((int) ($_POST['command_id'] ?? 0), (int) $bot['id']);
                flash('success', 'Comando removido.');
            }
            redirect('?page=commands&bot=' . ($bot['id'] ?? ''));
        }
    }

    redirect('?page=dashboard');
}

/* --------------------------------------------------------------------------
 * API (POST via fetch) — teste de comando sem delay
 * ------------------------------------------------------------------------ */
if ($page === 'test_command') {
    header('Content-Type: application/json');
    $userId = current_user_id();
    if ($userId === null) {
        echo json_encode(['ok' => false, 'error' => 'Não autenticado.']);
        exit;
    }
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    if (!hash_equals($_SESSION['csrf'] ?? '', $body['csrf'] ?? '')) {
        echo json_encode(['ok' => false, 'error' => 'CSRF inválido.']);
        exit;
    }
    $bot = $repo->findBot((int) ($body['bot_id'] ?? 0), $userId);
    $cmd = $bot ? $repo->findCommand((int) ($body['command_id'] ?? 0), (int) $bot['id']) : null;
    if (!$cmd) {
        echo json_encode(['ok' => false, 'error' => 'Comando não encontrado.']);
        exit;
    }
    $text = (string) ($body['text'] ?? $cmd['name']);
    $script = isset($body['script']) && $body['script'] !== '' ? (string) $body['script'] : $cmd['script'];
    $runner = new ScriptRunner($config['python_bin'], (int) $config['script_timeout'], APP_ROOT . '/data/scripts');
    $started = microtime(true);
    $result = $runner->run($script, [
        'bot_token' => $bot['token'],
        'chat_id'   => '123456789',
        'user_id'   => '123456789',
        'first_name'=> 'Teste',
        'username'  => 'usuario_teste',
        'text'      => $text,
        'command'   => $cmd['name'],
        'args'      => trim(substr($text, strlen($cmd['name']))),
        'update'    => ['message' => ['text' => $text, 'chat' => ['id' => 123456789], 'from' => ['id' => 123456789, 'first_name' => 'Teste']]],
    ]);
    $result['ms'] = (int) round((microtime(true) - $started) * 1000);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

/* --------------------------------------------------------------------------
 * Páginas (GET)
 * ------------------------------------------------------------------------ */
if ($page === 'logout') {
    $auth->logout();
    redirect('?page=login');
}

$viewsDir = APP_ROOT . '/src/views';

if (in_array($page, ['login', 'register'], true)) {
    if (current_user_id() !== null) {
        redirect('?page=dashboard');
    }
    render($viewsDir, $page, $config, ['page' => $page]);
    exit;
}

// A partir daqui exige login.
$userId = require_login();

switch ($page) {
    case 'commands': {
        $bot = $repo->findBot((int) ($_GET['bot'] ?? 0), $userId);
        if (!$bot) {
            flash('error', 'Bot não encontrado.');
            redirect('?page=dashboard');
        }
        $commands = $repo->commandsForBot((int) $bot['id']);
        $selectedId = (int) ($_GET['cmd'] ?? ($commands[0]['id'] ?? 0));
        $selected = null;
        foreach ($commands as $c) {
            if ((int) $c['id'] === $selectedId) {
                $selected = $c;
            }
        }
        render($viewsDir, 'commands', $config, [
            'page' => 'commands',
            'bot' => $bot,
            'commands' => $commands,
            'selected' => $selected,
        ]);
        break;
    }

    case 'dashboard':
    default: {
        $bots = $repo->botsForUser($userId);
        render($viewsDir, 'dashboard', $config, [
            'page' => 'dashboard',
            'bots' => $bots,
        ]);
        break;
    }
}

/** Renderiza uma view dentro do layout. */
function render(string $viewsDir, string $view, array $config, array $data = []): void
{
    extract($data);
    $appName = $config['app_name'];
    $flashes = take_flash();
    ob_start();
    require $viewsDir . '/' . $view . '.php';
    $content = ob_get_clean();
    require $viewsDir . '/layout.php';
}
