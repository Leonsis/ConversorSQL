from flask import Flask, request, jsonify
from flask_cors import CORS  # Importando CORS
import pandas as pd

app = Flask(__name__)
CORS(app)  # Habilita CORS para todas as origens

@app.route('/upload', methods=['POST'])
def upload_file():
    # Pega os dados enviados no formulário

    table_name = request.form.get('table')
    file = request.files.get('file')

    # Verifica se todos os campos foram preenchidos
    if not all([table_name, file]):
        return jsonify({"message": "All fields are required."}), 400

    try:
        df = pd.read_excel(file)

        # Gera a query para criar a tabela
        columns = df.columns
        column_definitions = []
        
        # Aqui, modificamos para garantir que qualquer nome de coluna seja tratado corretamente
        for column in columns:
            # Verifica o nome da coluna para garantir que é um nome válido no SQL (evitar espaços e caracteres especiais)
            sanitized_column_name = column.replace(' ', '_').replace('-', '_').replace('?', '').replace('!', '')
            column_definitions.append(f"`{sanitized_column_name}` VARCHAR(255)")  # Define o tipo como VARCHAR para todas as colunas (você pode personalizar conforme necessário)

        create_table_query = f"CREATE TABLE {table_name} ({', '.join(column_definitions)});"
        
        # Gera a query de inserção baseada nos dados
        columns = ', '.join([f"`{col.replace(' ', '_').replace('-', '_').replace('?', '').replace('!', '')}`" for col in df.columns])  # Garantir que o nome da coluna seja seguro
        values = ', '.join(
            ["(" + ", ".join([f"'{str(value).replace('\'', '')}'" for value in row]) + ")" for row in df.values]
        )
        query = f"INSERT INTO {table_name} ({columns}) VALUES {values};"

        return jsonify({"message": "Query generated successfully.", "query": query})
    except Exception as e:
        return jsonify({"message": f"Error processing file: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)  # Alterado para ouvir em todas as interfaces de rede
