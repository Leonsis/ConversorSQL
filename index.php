<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Excel to MySQL</title>
        <link rel="stylesheet" href="src/main.css">
        <script src="src/main.js"></script>
        <!-- Bootstrap -->
        <link href="src/css/bootstrap.min.css" rel="stylesheet">        
        <script src="src/js/bootstrap.bundle.min.js"></script>
        
    </head>
    <body style="background-color: #303030;">
        
        <div class="container mt-5">
            <h1 class="text-center mb-4 text-white">Conversor Excel para MySQL</h1>
            <div class="card mx-auto"  style="width: 580px;">
                <div class="card-body">
                    <form action="import.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="file" class="form-label text-white">Selecione um arquivo Excel</label>
                            <input type="file" class="form-control text-white p-0" style="border: none; background-color: var(--background-color);" id="file" name="file" accept=".xlsx, .xls" required>
                        </div>

                        <div class="mb-3">

                            <button class="btn btn-dark text-white" id="toggleButton" >
                                Como funciona?
                            </button>

                            <div id="texto" class="text-white mt-3" style="display: none;">
                                <p>
                                    O sistema converte automaticamente arquivos Excel (.xlsx, .xls e .csv) em comandos SQL prontos para importação em bancos de dados como MySQL, SQL Server e PostgreSQL.
                                </p>

                                <p>
                                    Basta enviar sua planilha organizada em linhas e colunas, e o software irá ler os dados, gerar os comandos SQL automaticamente e disponibilizar um arquivo .sql para download.
                                </p>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Import</button>
                    </form>
                </div>
            </div>
        </div>
        
    </body>
</html>
