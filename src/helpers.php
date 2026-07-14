<?php

/** Escapa texto para saída HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Redireciona e encerra a execução. */
function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

/** Retorna (e cria, se necessário) o token CSRF da sessão. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo hidden com o token CSRF. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Valida o token CSRF recebido via POST. */
function csrf_check(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Sessão expirada. Recarregue a página e tente novamente.');
    }
}

/** Define uma mensagem flash para exibir na próxima requisição. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Consome e retorna as mensagens flash. */
function take_flash(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

/** ID do usuário logado, ou null. */
function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/** Garante que há um usuário logado. */
function require_login(): int
{
    $id = current_user_id();
    if ($id === null) {
        redirect('?page=login');
    }
    return $id;
}
