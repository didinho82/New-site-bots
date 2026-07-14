<?php

/**
 * Consultas de bots e comandos.
 */
class Repo
{
    public function __construct(private PDO $db) {}

    /* ---------------- Bots ---------------- */

    public function botsForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM bots WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findBot(int $botId, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM bots WHERE id = ? AND user_id = ?');
        $stmt->execute([$botId, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function findBotById(int $botId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM bots WHERE id = ?');
        $stmt->execute([$botId]);
        return $stmt->fetch() ?: null;
    }

    public function createBot(int $userId, string $token, array $me): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO bots (user_id, token, telegram_id, username, first_name, webhook_secret)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $token,
            (string) ($me['id'] ?? ''),
            (string) ($me['username'] ?? ''),
            (string) ($me['first_name'] ?? ''),
            bin2hex(random_bytes(24)),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function setWebhookFlag(int $botId, bool $set): void
    {
        $stmt = $this->db->prepare('UPDATE bots SET webhook_set = ? WHERE id = ?');
        $stmt->execute([$set ? 1 : 0, $botId]);
    }

    public function deleteBot(int $botId, int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM bots WHERE id = ? AND user_id = ?');
        $stmt->execute([$botId, $userId]);
        $stmt = $this->db->prepare('DELETE FROM commands WHERE bot_id = ?');
        $stmt->execute([$botId]);
    }

    /* ---------------- Comandos ---------------- */

    public function commandsForBot(int $botId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM commands WHERE bot_id = ? ORDER BY id ASC');
        $stmt->execute([$botId]);
        return $stmt->fetchAll();
    }

    public function findCommand(int $commandId, int $botId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM commands WHERE id = ? AND bot_id = ?');
        $stmt->execute([$commandId, $botId]);
        return $stmt->fetch() ?: null;
    }

    public function findCommandByName(int $botId, string $name): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM commands WHERE bot_id = ? AND name = ? AND enabled = 1');
        $stmt->execute([$botId, $name]);
        return $stmt->fetch() ?: null;
    }

    public function createCommand(int $botId, string $name): int
    {
        $default = "# Script Python do comando \"$name\".\n"
            . "# Variáveis disponíveis: os.environ['CHAT_ID'], ['MESSAGE_TEXT'], ['FIRST_NAME'], ['BOT_TOKEN'] ...\n"
            . "# Tudo que voce imprimir com print() sera enviado como resposta.\n\n"
            . "import os\n\n"
            . "nome = os.environ.get('FIRST_NAME', '')\n"
            . "print(f'Ola {nome}! Voce usou o comando $name.')\n";

        $stmt = $this->db->prepare('INSERT INTO commands (bot_id, name, script) VALUES (?, ?, ?)');
        $stmt->execute([$botId, $name, $default]);
        return (int) $this->db->lastInsertId();
    }

    public function updateCommand(int $commandId, int $botId, string $name, string $script, bool $enabled): void
    {
        $stmt = $this->db->prepare(
            'UPDATE commands SET name = ?, script = ?, enabled = ?, updated_at = CURRENT_TIMESTAMP
             WHERE id = ? AND bot_id = ?'
        );
        $stmt->execute([$name, $script, $enabled ? 1 : 0, $commandId, $botId]);
    }

    public function deleteCommand(int $commandId, int $botId): void
    {
        $stmt = $this->db->prepare('DELETE FROM commands WHERE id = ? AND bot_id = ?');
        $stmt->execute([$commandId, $botId]);
    }
}
