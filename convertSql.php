<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once 'Classes/PHPExcel-1.8/Classes/PHPExcel.php';
    require_once 'Classes/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

    if (isset($_FILES['file'])) {
        if (!isset($_FILES['file']['error'])) {
            echo 'Upload inválido.';
        } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $code = $_FILES['file']['error'];
            $map = [
                UPLOAD_ERR_INI_SIZE => 'O arquivo excede upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o limite definido no formulário',
                UPLOAD_ERR_PARTIAL => 'O upload foi feito parcialmente',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente no servidor',
                UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar o arquivo no disco',
                UPLOAD_ERR_EXTENSION => 'Upload interrompido por extensão'
            ];
            $msg = isset($map[$code]) ? $map[$code] : ('Erro no upload: ' . $code);
            $msg .= ' (upload_max_filesize=' . ini_get('upload_max_filesize') . ', post_max_size=' . ini_get('post_max_size') . ')';
            echo $msg;
        } else {
            $tmp = $_FILES['file']['tmp_name'];

            try {
            
            $inputFileType = PHPExcel_IOFactory::identify($tmp);
            $reader = PHPExcel_IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load($tmp);
            $sheet = $spreadsheet->getActiveSheet();

            
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

            
            $headers = array_shift($rows);
            
            $columns = [];
            foreach ($headers as $i => $h) {
                $h = trim((string)$h);
                if ($h === '') {
                    $h = 'col_' . ($i + 1);
                }
                
                $columns[] = preg_replace('/[^A-Za-z0-9_]/', '_', $h);
            }

            
            $originalName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            $tableName = preg_replace('/[^A-Za-z0-9_]/', '_', $originalName ?: 'my_table');

            $columnsList = implode(', ', array_map(function ($c) { return "`" . $c . "`"; }, $columns));

            $valuesSql = [];
            foreach ($rows as $r) {
                
                $allEmpty = true;
                $escaped = [];
                for ($i = 0; $i < count($columns); $i++) {
                    $val = isset($r[$i]) ? $r[$i] : '';
                    if ($val !== null && $val !== '') {
                        $allEmpty = false;
                    }
                    
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
    }