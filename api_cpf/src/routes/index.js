const express = require("express");
const cors = require("cors");
const SearchController = require("../app/controllers/SearchController");

const app = express();

app.use(cors());
app.use(express.json());

// Rota raiz
app.get("/", (req, res) => {
  res.json({
    mensagem: "API de Consulta CPF 🔍",
    versao: "1.0.0",
    endpoint: "GET /?cpf=XXXXXXXXXXX"
  });
});

// Rota de consulta
app.get("/api/cpf", SearchController.show);

// 404
app.use((req, res) => {
  res.status(404).json({
    sucesso: false,
    mensagem: "Rota não encontrada"
  });
});

module.exports = app;
