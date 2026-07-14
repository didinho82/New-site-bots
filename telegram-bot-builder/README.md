# BotForge — Criador de Bots do Telegram (PHP)

Sistema completo em PHP para criar e gerenciar bots do Telegram por token, com
login, dashboard, criação de comandos e execução de scripts em Python. As
mensagens são recebidas e respondidas via **webhook**, sem delay.

## Recursos

- **Login** com email + senha (senhas com hash `bcrypt`, proteção CSRF).
- **Dashboard** com resumo e lista de bots.
- **Criar bot**: cole o token do [@BotFather](https://t.me/BotFather), clique em
  *Validar e criar* → o nome do bot é buscado via `getMe`.
- **Comandos**: botão *Criar comando* (começa em `/start`), edite o nome para
  `/menu`, `/status`, etc. e cole o **script Python** de cada comando.
- **Simulador** (*Testar sem delay*) que roda o script no navegador e mostra a
  resposta e o tempo de execução, sem precisar publicar o bot.
- **Webhook** que executa o script Python do comando e envia o `stdout` como
  resposta ao usuário.
- Comando coringa opcional `*` que recebe qualquer mensagem.

## Como funciona o script de um comando

O script recebe o contexto da mensagem por variáveis de ambiente e por um JSON
no `stdin`. **Tudo que o script imprimir com `print()` é enviado como resposta.**

Variáveis disponíveis:

| Variável        | Descrição                                  |
|-----------------|--------------------------------------------|
| `MESSAGE_TEXT`  | Texto completo da mensagem                  |
| `COMMAND`       | Nome do comando (ex.: `/start`)             |
| `ARGS`          | Texto após o comando                        |
| `CHAT_ID`       | ID do chat                                  |
| `USER_ID`       | ID do usuário                               |
| `FIRST_NAME`    | Primeiro nome do usuário                    |
| `USERNAME`      | @username do usuário                        |
| `BOT_TOKEN`     | Token do bot (para chamadas próprias à API) |
| `UPDATE_JSON`   | Update completo do Telegram (JSON)          |

Exemplo:

```python
import os
nome = os.environ.get('FIRST_NAME', '')
print(f'Olá {nome}! Bem-vindo 👋')
```

## Requisitos

- PHP 8.1+ com `pdo_sqlite` (ou `pdo_mysql`) e `curl`.
- Python 3 disponível no servidor (`python3`).

## Instalação

```bash
cp config.php.example config.php
# ajuste app_url (HTTPS público), banco e python_bin em config.php
php -S 0.0.0.0:8000 -t public   # desenvolvimento
```

Em produção, aponte o servidor web (Apache/Nginx) para a pasta `public/` e
defina `app_url` com o domínio HTTPS. Depois, no painel de cada bot, clique em
**Ativar bot** para registrar o webhook.

## Configuração

Edite `config.php`:

- `app_url` — URL pública HTTPS (necessária para o webhook do Telegram).
- `db_dsn` — DSN PDO. Padrão: SQLite em `data/app.sqlite`.
- `python_bin` — caminho do Python (padrão `python3`).
- `script_timeout` — tempo máximo de execução de um script (segundos).

## Segurança

Os scripts dos comandos rodam Python no servidor. Trate o painel como uma
ferramenta administrativa (apenas usuários confiáveis devem ter acesso) e, de
preferência, rode a aplicação com um usuário de sistema restrito.
