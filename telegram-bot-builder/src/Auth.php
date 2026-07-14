<?php

/**
 * Autenticação por email + senha.
 */
class Auth
{
    public function __construct(private PDO $db) {}

    /**
     * Cria um novo usuário. Retorna [ok, mensagem].
     */
    public function register(string $email, string $password): array
    {
        $email = trim(mb_strtolower($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Informe um email válido.'];
        }
        if (strlen($password) < 6) {
            return [false, 'A senha deve ter pelo menos 6 caracteres.'];
        }

        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return [false, 'Já existe uma conta com esse email.'];
        }

        $stmt = $this->db->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);

        return [true, 'Conta criada com sucesso.'];
    }

    /**
     * Autentica um usuário. Retorna [ok, mensagem].
     */
    public function login(string $email, string $password): array
    {
        $email = trim(mb_strtolower($email));

        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return [false, 'Email ou senha incorretos.'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = $user['email'];

        return [true, 'Bem-vindo de volta!'];
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
