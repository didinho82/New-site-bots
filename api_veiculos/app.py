from flask import Flask, jsonify, request
from flask_cors import CORS
import sqlite3
import os

app = Flask(__name__)
CORS(app)

# Caminho do banco de dados
DB_PATH = 'veiculos.db'

def get_db_connection():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn

def init_db():
    """Inicializa o banco de dados com tabela de veículos"""
    if not os.path.exists(DB_PATH):
        conn = get_db_connection()
        cursor = conn.cursor()
        
        cursor.execute('''
            CREATE TABLE veiculos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                placa TEXT UNIQUE NOT NULL,
                marca TEXT NOT NULL,
                modelo TEXT NOT NULL,
                ano INTEGER NOT NULL,
                cor TEXT NOT NULL
            )
        ''')
        
        # Adicionar alguns veículos de exemplo
        veiculos_exemplo = [
            ('ABC1234', 'Toyota', 'Corolla', 2023, 'Prata'),
            ('XYZ5678', 'Honda', 'Civic', 2022, 'Preto'),
            ('DEF9012', 'Volkswagen', 'Gol', 2021, 'Vermelho'),
            ('GHI3456', 'Fiat', 'Palio', 2020, 'Branco'),
            ('JKL7890', 'Hyundai', 'HB20', 2023, 'Azul'),
        ]
        
        cursor.executemany(
            'INSERT INTO veiculos (placa, marca, modelo, ano, cor) VALUES (?, ?, ?, ?, ?)',
            veiculos_exemplo
        )
        
        conn.commit()
        conn.close()
        print("✅ Banco de dados criado com sucesso!")

@app.route('/api/veiculos/buscar/<placa>', methods=['GET'])
def buscar_veiculo(placa):
    """
    Busca um veículo pela placa
    Exemplo: /api/veiculos/buscar/ABC1234
    """
    try:
        placa = placa.upper()  # Converter para maiúscula
        conn = get_db_connection()
        veiculo = conn.execute(
            'SELECT * FROM veiculos WHERE placa = ?',
            (placa,)
        ).fetchone()
        conn.close()
        
        if veiculo:
            return jsonify({
                'sucesso': True,
                'dados': {
                    'id': veiculo['id'],
                    'placa': veiculo['placa'],
                    'marca': veiculo['marca'],
                    'modelo': veiculo['modelo'],
                    'ano': veiculo['ano'],
                    'cor': veiculo['cor']
                }
            }), 200
        else:
            return jsonify({
                'sucesso': False,
                'mensagem': f'Nenhum veículo encontrado com a placa {placa}'
            }), 404
    except Exception as e:
        return jsonify({
            'sucesso': False,
            'erro': str(e)
        }), 500

@app.route('/api/veiculos/listar', methods=['GET'])
def listar_veiculos():
    """Lista todos os veículos"""
    try:
        conn = get_db_connection()
        veiculos = conn.execute('SELECT * FROM veiculos').fetchall()
        conn.close()
        
        return jsonify({
            'sucesso': True,
            'total': len(veiculos),
            'dados': [dict(v) for v in veiculos]
        }), 200
    except Exception as e:
        return jsonify({
            'sucesso': False,
            'erro': str(e)
        }), 500

@app.route('/api/veiculos/adicionar', methods=['POST'])
def adicionar_veiculo():
    """
    Adiciona um novo veículo
    Body JSON:
    {
        "placa": "ABC1234",
        "marca": "Toyota",
        "modelo": "Corolla",
        "ano": 2023,
        "cor": "Prata"
    }
    """
    try:
        dados = request.get_json()
        
        if not all(k in dados for k in ['placa', 'marca', 'modelo', 'ano', 'cor']):
            return jsonify({
                'sucesso': False,
                'mensagem': 'Campos obrigatórios: placa, marca, modelo, ano, cor'
            }), 400
        
        conn = get_db_connection()
        try:
            conn.execute(
                'INSERT INTO veiculos (placa, marca, modelo, ano, cor) VALUES (?, ?, ?, ?, ?)',
                (dados['placa'].upper(), dados['marca'], dados['modelo'], dados['ano'], dados['cor'])
            )
            conn.commit()
            conn.close()
            
            return jsonify({
                'sucesso': True,
                'mensagem': f'Veículo {dados["placa"]} adicionado com sucesso!'
            }), 201
        except sqlite3.IntegrityError:
            conn.close()
            return jsonify({
                'sucesso': False,
                'mensagem': f'Já existe um veículo com a placa {dados["placa"]}'
            }), 409
    except Exception as e:
        return jsonify({
            'sucesso': False,
            'erro': str(e)
        }), 500

@app.route('/api/veiculos/deletar/<placa>', methods=['DELETE'])
def deletar_veiculo(placa):
    """Deleta um veículo pela placa"""
    try:
        placa = placa.upper()
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('DELETE FROM veiculos WHERE placa = ?', (placa,))
        conn.commit()
        
        if cursor.rowcount > 0:
            conn.close()
            return jsonify({
                'sucesso': True,
                'mensagem': f'Veículo {placa} deletado com sucesso!'
            }), 200
        else:
            conn.close()
            return jsonify({
                'sucesso': False,
                'mensagem': f'Nenhum veículo encontrado com a placa {placa}'
            }), 404
    except Exception as e:
        return jsonify({
            'sucesso': False,
            'erro': str(e)
        }), 500

@app.route('/api/saude', methods=['GET'])
def saude():
    """Verifica se a API está funcionando"""
    return jsonify({
        'status': 'online',
        'mensagem': 'API de veículos está funcionando!'
    }), 200

if __name__ == '__main__':
    init_db()
    app.run(debug=True, host='0.0.0.0', port=5000)