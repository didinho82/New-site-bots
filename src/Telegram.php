<?php

/**
 * Cliente mínimo para a Bot API do Telegram.
 */
class Telegram
{
    private string $base;

    public function __construct(private string $token)
    {
        $this->base = 'https://api.telegram.org/bot' . $token . '/';
    }

    /**
     * Faz uma chamada à API e retorna o array decodificado.
     */
    public function call(string $method, array $params = []): array
    {
        $ch = curl_init($this->base . $method);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return ['ok' => false, 'description' => 'Erro de conexão: ' . $err];
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return ['ok' => false, 'description' => 'Resposta inválida da API do Telegram.'];
        }
        return $data;
    }

    /** Valida o token e retorna os dados do bot (getMe). */
    public function getMe(): array
    {
        return $this->call('getMe');
    }

    /** Registra o webhook para receber updates. */
    public function setWebhook(string $url, string $secret): array
    {
        return $this->call('setWebhook', [
            'url'             => $url,
            'secret_token'    => $secret,
            'allowed_updates' => json_encode(['message', 'edited_message', 'callback_query']),
            'drop_pending_updates' => 'true',
        ]);
    }

    /** Remove o webhook. */
    public function deleteWebhook(): array
    {
        return $this->call('deleteWebhook', ['drop_pending_updates' => 'true']);
    }

    /** Envia uma mensagem de texto. */
    public function sendMessage(int|string $chatId, string $text, array $extra = []): array
    {
        return $this->call('sendMessage', array_merge([
            'chat_id' => $chatId,
            'text'    => $text,
        ], $extra));
    }
}
