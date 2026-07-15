const express = require('express');
const cors = require('cors');

const app = express();

app.use(cors());
app.use(express.json());

// Banco de dados em memória
let veiculos = [
  { id: 1, placa: 'ABC1234', marca: 'Toyota', modelo: 'Corolla', ano: 2023, cor: 'Prata' },
  { id: 2, placa: 'XYZ5678', marca: 'Honda', modelo: 'Civic', ano: 2022, cor: 'Preto' },
  { id: 3, placa: 'DEF9012', marca: 'Volkswagen', modelo: 'Gol', ano: 2021, cor: 'Vermelho' },
  { id: 4, placa: 'GHI3456', marca: 'Fiat', modelo: 'Palio', ano: 2020, cor: 'Branco' },
  { id: 5, placa: 'JKL7890', marca: 'Hyundai', modelo: 'HB20', ano: 2023, cor: 'Azul' }
];

let nextId = 6;

// Rota raiz
app.get('/', (req, res) => {
  res.json({
    mensagem: 'API de Veículos 🚗',
    versao: '1.0.0',
    status: 'online',
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

// Verificar status
app.get('/api/saude', (req, res) => {
  res.json({
    status: 'online',
    mensagem: 'API de veículos está funcionando!'
  });
});

// Listar todos
app.get('/api/veiculos/listar', (req, res) => {
  res.json({
    sucesso: true,
    total: veiculos.length,
    dados: veiculos
  });
});

// Buscar por placa
app.get('/api/veiculos/buscar/:placa', (req, res) => {
  const placa = req.params.placa.toUpperCase();
  const veiculo = veiculos.find(v => v.placa === placa);

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
});

// Adicionar veículo
app.post('/api/veiculos/adicionar', (req, res) => {
  const { placa, marca, modelo, ano, cor } = req.body;

  if (!placa || !marca || !modelo || !ano || !cor) {
    return res.status(400).json({
      sucesso: false,
      mensagem: 'Campos obrigatórios: placa, marca, modelo, ano, cor'
    });
  }

  const placaUpper = placa.toUpperCase();
  if (veiculos.find(v => v.placa === placaUpper)) {
    return res.status(409).json({
      sucesso: false,
      mensagem: `Já existe um veículo com a placa ${placaUpper}`
    });
  }

  const novoVeiculo = {
    id: nextId++,
    placa: placaUpper,
    marca,
    modelo,
    ano,
    cor
  };

  veiculos.push(novoVeiculo);

  res.status(201).json({
    sucesso: true,
    mensagem: `Veículo ${placaUpper} adicionado com sucesso!`,
    dados: novoVeiculo
  });
});

// Atualizar veículo
app.put('/api/veiculos/atualizar/:placa', (req, res) => {
  const placaOriginal = req.params.placa.toUpperCase();
  const { marca, modelo, ano, cor } = req.body;

  const veiculo = veiculos.find(v => v.placa === placaOriginal);

  if (!veiculo) {
    return res.status(404).json({
      sucesso: false,
      mensagem: `Nenhum veículo encontrado com a placa ${placaOriginal}`
    });
  }

  veiculo.marca = marca || veiculo.marca;
  veiculo.modelo = modelo || veiculo.modelo;
  veiculo.ano = ano || veiculo.ano;
  veiculo.cor = cor || veiculo.cor;

  res.json({
    sucesso: true,
    mensagem: `Veículo ${placaOriginal} atualizado com sucesso!`,
    dados: veiculo
  });
});

// Deletar veículo
app.delete('/api/veiculos/deletar/:placa', (req, res) => {
  const placa = req.params.placa.toUpperCase();
  const index = veiculos.findIndex(v => v.placa === placa);

  if (index === -1) {
    return res.status(404).json({
      sucesso: false,
      mensagem: `Nenhum veículo encontrado com a placa ${placa}`
    });
  }

  veiculos.splice(index, 1);

  res.json({
    sucesso: true,
    mensagem: `Veículo ${placa} deletado com sucesso!`
  });
});

// 404
app.use((req, res) => {
  res.status(404).json({
    sucesso: false,
    mensagem: 'Rota não encontrada',
    path: req.path
  });
});

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`✅ API rodando em http://localhost:${PORT}`);
});
