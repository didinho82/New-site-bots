# API de Veículos 🚗

API Node.js com Express para buscar informações de veículos pela placa.

## Instalação

```bash
cd api_veiculos
npm install
```

## Desenvolvimento Local

```bash
npm run dev
```

A API estará disponível em: `http://localhost:5000`

## Produção

```bash
npm start
```

## Deploy no Vercel

### 1. Faça push para o GitHub
```bash
git add .
git commit -m "API Node.js para Vercel"
git push origin main
```

### 2. Conecte no Vercel
- Acesse https://vercel.com
- Clique em "New Project"
- Selecione seu repositório `New-site-bots`
- Selecione a pasta raiz como `/api_veiculos`
- Clique em "Deploy"

### 3. Pronto! 🚀
Sua API estará em: `https://seu-projeto.vercel.app`

---

## Endpoints

### 1. Verificar status
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

### 2. Buscar veículo pela placa
```
GET /api/veiculos/buscar/<placa>
```

**Exemplo:**
```
GET /api/veiculos/buscar/ABC1234
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
    "cor": "Prata",
    "created_at": "2024-01-15 10:30:00"
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

### 3. Listar todos os veículos
```
GET /api/veiculos/listar
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
      "cor": "Prata",
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

### 4. Adicionar novo veículo
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

**Resposta de erro - placa duplicada (409):**
```json
{
  "sucesso": false,
  "mensagem": "Já existe um veículo com a placa ABC1234"
}
```

---

### 5. Atualizar veículo
```
PUT /api/veiculos/atualizar/<placa>
```

**Body (JSON):**
```json
{
  "marca": "Toyota",
  "modelo": "Corolla",
  "ano": 2024,
  "cor": "Preto"
}
```

**Resposta:**
```json
{
  "sucesso": true,
  "mensagem": "Veículo ABC1234 atualizado com sucesso!"
}
```

---

### 6. Deletar veículo
```
DELETE /api/veiculos/deletar/<placa>
```

**Exemplo:**
```
DELETE /api/veiculos/deletar/ABC1234
```

**Resposta:**
```json
{
  "sucesso": true,
  "mensagem": "Veículo ABC1234 deletado com sucesso!"
}
```

---

## Banco de Dados

O banco SQLite é criado automaticamente na primeira execução com veículos de exemplo.

**Arquivo:** `veiculos.db` (local) ou `/tmp/veiculos.db` (Vercel)

### Estrutura da Tabela

```sql
CREATE TABLE veiculos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  placa TEXT UNIQUE NOT NULL,
  marca TEXT NOT NULL,
  modelo TEXT NOT NULL,
  ano INTEGER NOT NULL,
  cor TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## Usando no seu Bot do Telegram

### Exemplo com Node.js (telegraf):

```javascript
const axios = require('axios');

const API_URL = 'https://seu-projeto.vercel.app';

async function buscarVeiculo(placa) {
  try {
    const response = await axios.get(`${API_URL}/api/veiculos/buscar/${placa}`);
    const dados = response.data.dados;
    
    return `🚗 **Informações do Veículo**
📋 Placa: ${dados.placa}
🏭 Marca: ${dados.marca}
🚙 Modelo: ${dados.modelo}
📅 Ano: ${dados.ano}
🎨 Cor: ${dados.cor}`;
  } catch (error) {
    return '❌ Veículo não encontrado!';
  }
}

// No seu handler do bot:
bot.command('buscar', (ctx) => {
  ctx.reply('Digite a placa do veículo:');
  ctx.session.waitingForPlate = true;
});

bot.on('text', async (ctx) => {
  if (ctx.session.waitingForPlate) {
    const resultado = await buscarVeiculo(ctx.message.text);
    ctx.reply(resultado);
    ctx.session.waitingForPlate = false;
  }
});
```

---

## Variáveis de Ambiente

Se precisar configurar variáveis:

```
PORT=5000
NODE_ENV=development
```

---

**API pronta para usar! 🚀**
