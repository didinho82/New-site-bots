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

### 2. Listar todos os veículos
```
GET /api/veiculos/listar
```

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

### 4. Deletar veículo
```
DELETE /api/veiculos/deletar/<placa>
```

### 5. Verificar status
```
GET /api/saude
```