<?php

/**
 * Executa o script Python associado a um comando.
 *
 * O script recebe o contexto da mensagem através de variáveis de ambiente e
 * de um JSON no stdin. Tudo que o script imprime no stdout é enviado de volta
 * como resposta ao usuário do Telegram.
 */
class ScriptRunner
{
    public function __construct(
        private string $pythonBin,
        private int $timeout,
        private string $scriptsDir
    ) {
        if (!is_dir($this->scriptsDir)) {
            mkdir($this->scriptsDir, 0775, true);
        }
    }

    /**
     * @return array{ok:bool, output:string, error:string}
     */
    public function run(string $script, array $context): array
    {
        $file = tempnam($this->scriptsDir, 'cmd_') . '.py';
        file_put_contents($file, $script);

        $env = array_merge($_ENV, [
            'BOT_TOKEN'    => (string) ($context['bot_token'] ?? ''),
            'CHAT_ID'      => (string) ($context['chat_id'] ?? ''),
            'USER_ID'      => (string) ($context['user_id'] ?? ''),
            'FIRST_NAME'   => (string) ($context['first_name'] ?? ''),
            'USERNAME'     => (string) ($context['username'] ?? ''),
            'MESSAGE_TEXT' => (string) ($context['text'] ?? ''),
            'COMMAND'      => (string) ($context['command'] ?? ''),
            'ARGS'         => (string) ($context['args'] ?? ''),
            'UPDATE_JSON'  => json_encode($context['update'] ?? [], JSON_UNESCAPED_UNICODE),
            'PYTHONUNBUFFERED' => '1',
        ]);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            [$this->pythonBin, $file],
            $descriptors,
            $pipes,
            $this->scriptsDir,
            $env
        );

        if (!is_resource($process)) {
            @unlink($file);
            return ['ok' => false, 'output' => '', 'error' => 'Não foi possível iniciar o interpretador Python.'];
        }

        // Envia o contexto completo também no stdin.
        fwrite($pipes[0], json_encode($context['update'] ?? [], JSON_UNESCAPED_UNICODE));
        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $error  = '';
        $start  = microtime(true);

        while (true) {
            $status = proc_get_status($process);
            $output .= stream_get_contents($pipes[1]);
            $error  .= stream_get_contents($pipes[2]);

            if (!$status['running']) {
                break;
            }
            if (microtime(true) - $start > $this->timeout) {
                proc_terminate($process, 9);
                $error .= "\n[timeout] O script excedeu {$this->timeout}s e foi encerrado.";
                break;
            }
            usleep(20000);
        }

        // Drena qualquer resto.
        $output .= stream_get_contents($pipes[1]);
        $error  .= stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        @unlink($file);

        return [
            'ok'     => trim($error) === '',
            'output' => trim($output),
            'error'  => trim($error),
        ];
    }
}
