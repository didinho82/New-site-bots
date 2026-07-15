const express = require('express');
const cors = require('cors');
const Database = require('better-sqlite3');
const path = require('path');

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// Inicializar banco de dados
const dbPath = process.env.NODE_ENV === 'production' ? '/tmp/veiculos.db' : path.join(__dirname, 'veiculos.db');
const db = new Database(dbPath);

// Criar tabela se não existir
function initDB() {
  db.exec(`
    CREATE TABLE IF NOT EXISTS veiculos (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      placa TEXT UNIQUE NOT NULL,
      marca TEXT NOT NULL,
      modelo TEXT NOT NULL,
      ano INTEGER NOT NULL,
      cor TEXT NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Inserir dados de exemplo se tabela estiver vazia
  const count = db.prepare('SELECT COUNT(*) as count FROM veiculos').get();
  if (count.count === 0) {
    const insert = db.prepare(`
      INSERT INTO veiculos (placa, marca, modelo, ano, cor) 
      VALUES (?, ?, ?, ?, ?)
    `);

    const veiculos = [
      ['ABC1234', 'Toyota', 'Corolla', 2023, 'Prata'],
      ['XYZ5678', 'Honda', 'Civic', 2022, 'Preto'],
      ['DEF9012', 'Volkswagen', 'Gol', 2021, 'Vermelho'],
      ['GHI3456', 'Fiat', 'Palio', 2020, 'Branco'],
      ['JKL7890', 'Hyundai', 'HB20', 2023, 'Azul']
    ];

    veiculos.forEach(veiculo => insert.run(...veiculo));
    console.log('✅ Banco de dados inicializado com dados de exemplo!');
  }
}

initDB();

// ========== ENDPOINTS ==========

// Verificar status da API
app.get('/api/saude', (req, res) => {
  res.json({
    status: 'online',
    mensagem: 'API de veículos está funcionando!'
  });
});

// Buscar veículo pela placa
app.get('/api/veiculos/buscar/:placa', (req, res) => {
  try {
    const placa = req.params.placa.toUpperCase();
    const stmt = db.prepare('SELECT * FROM veiculos WHERE placa = ?');
    const veiculo = stmt.get(placa);

    if (veiculo) {
      res.json({
        sucesso: true,
        dados: veiculo
      });
    } else {
      res.status(404).json({
        sucesso: false,
        mensagem: `Nenhum veículo encontrado com a placa ${placa}`
      });
    }
  } catch (erro) {
    res.status(500).json({
      sucesso: false,
      erro: erro.message
    });
  }
});

// Listar todos os veículos
app.get('/api/veiculos/listar', (req, res) => {
  try {
    const stmt = db.prepare('SELECT * FROM veiculos ORDER BY created_at DESC');
    const veiculos = stmt.all();

    res.json({
      sucesso: true,
      total: veiculos.length,
      dados: veiculos
    });
  } catch (erro) {
    res.status(500).json({
      sucesso: false,
      erro: erro.message
    });
  }
});

// Adicionar novo veículo
app.post('/api/veiculos/adicionar', (req, res) => {
  try {
    const { placa, marca, modelo, ano, cor } = req.body;

    // Validar campos obrigatórios
    if (!placa || !marca || !modelo || !ano || !cor) {
      return res.status(400).json({
        sucesso: false,
        mensagem: 'Campos obrigatórios: placa, marca, modelo, ano, cor'
      });
    }

    const stmt = db.prepare(`
      INSERT INTO veiculos (placa, marca, modelo, ano, cor) 
      VALUES (?, ?, ?, ?, ?)
    `);

    stmt.run(placa.toUpperCase(), marca, modelo, ano, cor);

    res.status(201).json({
      sucesso: true,
      mensagem: `Veículo ${placa.toUpperCase()} adicionado com sucesso!`
    });
  } catch (erro) {
    if (erro.message.includes('UNIQUE')) {
      res.status(409).json({
        sucesso: false,
        mensagem: `Já existe um veículo com a placa ${req.body.placa}`
      });
    } else {
      res.status(500).json({
        sucesso: false,
        erro: erro.message
      });
    }
  }
});

// Atualizar veículo
app.put('/api/veiculos/atualizar/:placa', (req, res) => {
  try {
    const placaOriginal = req.params.placa.toUpperCase();
    const { marca, modelo, ano, cor } = req.body;

    const stmt = db.prepare(`
      UPDATE veiculos 
      SET marca = ?, modelo = ?, ano = ?, cor = ?
      WHERE placa = ?
    `);

    const info = stmt.run(marca, modelo, ano, cor, placaOriginal);

    if (info.changes > 0) {
      res.json({
        sucesso: true,
        mensagem: `Veículo ${placaOriginal} atualizado com sucesso!`
      });
    } else {
      res.status(404).json({
        sucesso: false,
        mensagem: `Nenhum veículo encontrado com a placa ${placaOriginal}`
      });
    }
  } catch (erro) {
    res.status(500).json({
      sucesso: false,
      erro: erro.message
    });
  }
});

// Deletar veículo
app.delete('/api/veiculos/deletar/:placa', (req, res) => {
  try {
    const placa = req.params.placa.toUpperCase();
    const stmt = db.prepare('DELETE FROM veiculos WHERE placa = ?');
    const info = stmt.run(placa);

    if (info.changes > 0) {
      res.json({
        sucesso: true,
        mensagem: `Veículo ${placa} deletado com sucesso!`
      });
    } else {
      res.status(404).json({
        sucesso: false,
        mensagem: `Nenhum veículo encontrado com a placa ${placa}`
      });
    }
  } catch (erro) {
    res.status(500).json({
      sucesso: false,
      erro: erro.message
    });
  }
});

// Rota raiz
app.get('/', (req, res) => {
  res.json({
    mensagem: 'API de Veículos 🚗',
    versao: '1.0.0',
    endpoints: [
      'GET /api/saude',
      'GET /api/veiculos/listar',
      'GET /api/veiculos/buscar/:placa',
      'POST /api/veiculos/adicionar',
      'PUT /api/veiculos/atualizar/:placa',
      'DELETE /api/veiculos/deletar/:placa'
    ]
  });
});

// Tratamento de erros 404
app.use((req, res) => {
  res.status(404).json({
    sucesso: false,
    mensagem: 'Rota não encontrada',
    path: req.path
  });
});

// Iniciar servidor
const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`✅ API rodando em http://localhost:${PORT}`);
});

// Graceful shutdown
process.on('SIGINT', () => {
  db.close();
  process.exit(0);
});
