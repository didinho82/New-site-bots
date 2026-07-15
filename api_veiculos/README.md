# API de Veículos 🚗

API desenvolvida em Python com Flask para buscar informações de veículos pela placa.

## Instalação

```bash
cd api_veiculos
pip install -r requirements.txt
```

## Executar a API

```bash
python app.py
```

A API estará disponível em: `http://localhost:5000`

## Endpoints

### 1. Buscar veículo pela placa
```
GET /api/veiculos/buscar/<placa>
```

**Exemplo:**
```
GET http://localhost:5000/api/veiculos/buscar/ABC1234
```

**Resposta de sucesso (200):**
```json
{
  "sucesso": true,
  "dados": {
    "id": 1,
    "placa": "ABC1234",
    "marca": "Toyota",
    "modelo": "Corolla",
    "ano": 2023,
    "cor": "Prata"
  }
}
```

**Resposta de erro (404):**
```json
{
  "sucesso": false,
  "mensagem": "Nenhum veículo encontrado com a placa ABC1234"
}
```

---

### 2. Listar todos os veículos
```
GET /api/veiculos/listar
```

**Exemplo:**
```
GET http://localhost:5000/api/veiculos/listar
```

**Resposta:**
```json
{
  "sucesso": true,
  "total": 5,
  "dados": [
    {
      "id": 1,
      "placa": "ABC1234",
      "marca": "Toyota",
      "modelo": "Corolla",
      "ano": 2023,
      "cor": "Prata"
    }
  ]
}
```

---

### 3. Adicionar novo veículo
```
POST /api/veiculos/adicionar
```

**Body (JSON):**
```json
{
  "placa": "ABC1234",
  "marca": "Toyota",
  "modelo": "Corolla",
  "ano": 2023,
  "cor": "Prata"
}
```

**Resposta de sucesso (201):**
```json
{
  "sucesso": true,
  "mensagem": "Veículo ABC1234 adicionado com sucesso!"
}
```

---

### 4. Deletar veículo
```
DELETE /api/veiculos/deletar/<placa>
```

**Exemplo:**
```
DELETE http://localhost:5000/api/veiculos/deletar/ABC1234
```

**Resposta:**
```json
{
  "sucesso": true,
  "mensagem": "Veículo ABC1234 deletado com sucesso!"
}
```

---

### 5. Verificar status da API
```
GET /api/saude
```

**Resposta:**
```json
{
  "status": "online",
  "mensagem": "API de veículos está funcionando!"
}
```

---

## Usando no seu Bot do Telegram

### Exemplo com Python (python-telegram-bot):

```python
import requests

def buscar_veiculo(placa):
    url = f'http://localhost:5000/api/veiculos/buscar/{placa}'
    try:
        response = requests.get(url)
        if response.status_code == 200:
            dados = response.json()['dados']
            return f"""
🚗 **Informações do Veículo**
📋 Placa: {dados['placa']}
🏭 Marca: {dados['marca']}
🚙 Modelo: {dados['modelo']}
📅 Ano: {dados['ano']}
🎨 Cor: {dados['cor']}
            """
        else:
            return "❌ Veículo não encontrado!"
    except Exception as e:
        return f"❌ Erro: {str(e)}"

# No seu handler do bot:
@bot.message_handler(commands=['buscar_veiculo'])
def handle_buscar(message):
    msg = bot.send_message(message.chat.id, "Digite a placa do veículo:")
    bot.register_next_step_handler(msg, lambda m: bot.send_message(
        message.chat.id, 
        buscar_veiculo(m.text)
    ))
```

---

## Banco de Dados

O banco de dados SQLite é criado automaticamente na primeira execução com alguns veículos de exemplo.

Arquivo: `veiculos.db`

---

## Estrutura da Tabela

```sql
CREATE TABLE veiculos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    placa TEXT UNIQUE NOT NULL,
    marca TEXT NOT NULL,
    modelo TEXT NOT NULL,
    ano INTEGER NOT NULL,
    cor TEXT NOT NULL
);
```

---

## Deploy na Nuvem

Para deploy gratuito, você pode usar:
- **Render.com** (grátis)
- **Heroku** (necessário cartão de crédito)
- **PythonAnywhere** (grátis com limitações)
- **Replit** (grátis)

Depois é só usar a URL da sua API hospedada no lugar de `http://localhost:5000`!

---

**API pronta para usar! 🚀**
