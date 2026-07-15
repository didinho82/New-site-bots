# API de Consulta CPF 🔍

API Node.js/Express para consultar informações de CPF usando BigDataCorp.

## Instalação

```bash
cd api_cpf
npm install
```

## Desenvolvimento Local

```bash
npm run dev
```

A API estará disponível em: `http://localhost:3333`

## Produção

```bash
npm start
```

## Configuração de Variáveis de Ambiente

1. Copie o arquivo `.env.example` para `.env`:
```bash
cp .env.example .env
```

2. Configure suas credenciais BigDataCorp:
```
base_url=bigboost.bigdatacorp.com.br
user=seu_usuario
pass=sua_senha
```

## Deploy no Vercel

### 1. Faça push para o GitHub
```bash
git add .
git commit -m "Add search-document-cpf API"
git push origin main
```

### 2. No Vercel Dashboard
- Acesse https://vercel.com/dashboard
- Clique em "New Project"
- Selecione seu repositório `New-site-bots`
- Em "Root Directory", coloque: `api_cpf`
- Clique em "Environment Variables" e adicione:
  - `base_url` = `bigboost.bigdatacorp.com.br`
  - `user` = seu usuário
  - `pass` = sua senha
- Clique em "Deploy"

### 3. Pronto! 🚀
Sua API estará em: `https://seu-projeto.vercel.app`

---

## Endpoints

### Status da API
```
GET /
```

**Resposta:**
```json
{
  "mensagem": "API de Consulta CPF 🔍",
  "versao": "1.0.0",
  "endpoint": "GET /?cpf=XXXXXXXXXXX"
}
```

### Consultar CPF
```
GET /api/cpf?cpf=XXXXXXXXXXX
```

**Parâmetro:**
- `cpf` (obrigatório) - CPF a ser consultado (ex: 12345678901)

**Resposta de Sucesso (200):**
```json
{
  "sucesso": true,
  "dados": {
    "IdNumber": "37923039XXX",
    "Name": "NOME DA PESSOA",
    "Birthdate": "1988-09-XX",
    "Gender": "F",
    "WorkingClass": []
  }
}
```

**Resposta de Erro (400):**
```json
{
  "sucesso": false,
  "mensagem": "CPF é obrigatório"
}
```

**Resposta de Erro (500):**
```json
{
  "sucesso": false,
  "mensagem": "Erro ao consultar CPF",
  "erro": "mensagem de erro"
}
```

---

## Exemplo com cURL

```bash
curl "http://localhost:3333/api/cpf?cpf=12345678901"
```

## Exemplo com JavaScript/Node.js

```javascript
const axios = require('axios');

async function consultarCPF(cpf) {
  try {
    const response = await axios.get(`https://seu-projeto.vercel.app/api/cpf?cpf=${cpf}`);
    console.log(response.data);
  } catch (error) {
    console.error('Erro:', error.message);
  }
}

consultarCPF('12345678901');
```

---

**API pronta para usar! 🚀**
