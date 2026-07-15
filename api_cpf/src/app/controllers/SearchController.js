const { get } = require("axios");

module.exports = {
  async show(req, res) {
    try {
      const { user, pass, base_url } = require("../../config");
      const { cpf } = req.query;

      if (!cpf) {
        return res.status(400).json({
          sucesso: false,
          mensagem: "CPF é obrigatório"
        });
      }

      if (!user || !pass || !base_url) {
        return res.status(500).json({
          sucesso: false,
          mensagem: "Variáveis de ambiente não configuradas"
        });
      }

      const { data } = await get(
        `https://${base_url}/API/Query?USERNAME=${user}&PASSWORD=${pass}&SOURCE=BOOKPF&SEARCHKEY=OP=CPF|DOC=${cpf}`
      );

      const result = JSON.parse(data.OperationResult);
      const { Entities } = result;

      res.json({
        sucesso: true,
        dados: Entities[0]
      });
    } catch (erro) {
      res.status(500).json({
        sucesso: false,
        mensagem: "Erro ao consultar CPF",
        erro: erro.message
      });
    }
  },
};
