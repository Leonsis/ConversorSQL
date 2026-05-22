<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once 'Classes/PHPExcel-1.8/Classes/PHPExcel.php';
    require_once 'Classes/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

    if (isset($_FILES['file'])) {
        $tmp = $_FILES['file']['tmp_name'];

        try {
            // Identify file type and create appropriate reader
            $inputFileType = PHPExcel_IOFactory::identify($tmp);
            $reader = PHPExcel_IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load($tmp);
            $sheet = $spreadsheet->getActiveSheet();

            // Read all rows into an array
            $rows = [];
            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $rows[] = $rowData;
            }

            if (count($rows) < 1) {
                throw new \Exception('Planilha vazia');
            }

            // Use first row as header
            $headers = array_shift($rows);
            // Normalize headers and fallback to column letters when empty
            $columns = [];
            foreach ($headers as $i => $h) {
                $h = trim((string)$h);
                if ($h === '') {
                    $h = 'col_' . ($i + 1);
                }
                // sanitize column name
                $columns[] = preg_replace('/[^A-Za-z0-9_]/', '_', $h);
            }

            // Table name from uploaded filename (without extension)
            $originalName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            $tableName = preg_replace('/[^A-Za-z0-9_]/', '_', $originalName ?: 'my_table');

            $columnsList = implode(', ', array_map(function ($c) { return "`" . $c . "`"; }, $columns));

            $valuesSql = [];
            foreach ($rows as $r) {
                // skip completely empty rows
                $allEmpty = true;
                $escaped = [];
                for ($i = 0; $i < count($columns); $i++) {
                    $val = isset($r[$i]) ? $r[$i] : '';
                    if ($val !== null && $val !== '') {
                        $allEmpty = false;
                    }
                    // Convert to string and escape single quotes for SQL
                    $s = str_replace("'", "''", (string)$val);
                    $escaped[] = "'" . $s . "'";
                }
                if ($allEmpty) continue;
                $valuesSql[] = '(' . implode(', ', $escaped) . ')';
            }

            if (empty($valuesSql)) {
                throw new \Exception('Nenhuma linha de dados encontrada para gerar SQL');
            }

            $sql = "INSERT INTO `{$tableName}` ({$columnsList}) VALUES\n" . implode(",\n", $valuesSql) . ";\n";

            // Create temporary file and send as download
            $filename = $tableName . '_' . date('YmdHis') . '.sql';
            $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($tmpFile, $sql);

            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tmpFile));
            readfile($tmpFile);
            @unlink($tmpFile);
            exit;

        } catch (\Exception $e) {
            echo "Erro ao processar arquivo: " . $e->getMessage();
        }
    }
?>

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
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="file" class="form-label text-white">Selecione um arquivo Excel</label>
                            <input type="file" class="form-control text-white p-0" style="border: none; background-color: var(--background-color);" id="file" name="file" accept=".xlsx, .xls" required>
                        </div>

                        <div class="mb-3">

                            <a class="btn btn-dark text-white" id="toggleButton" >
                                Como funciona?
                            </a>

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
