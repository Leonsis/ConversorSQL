# Conversor SQL
Conversor de Excel para SQL — ferramenta simples para gerar comandos `INSERT` a partir de planilhas (.xlsx / .xls / .csv).

**Resumo**
- O projeto lê uma planilha enviada via formulário, converte as linhas em comandos `INSERT INTO` e disponibiliza um arquivo `.sql` para download.
- Usa a biblioteca `PHPExcel-1.8` presente em `Classes/PHPExcel-1.8`.

**Pré-requisitos**
- PHP com suporte a uploads (verifique com `php -v`).
- Extensão DOM (usada por `DOMDocument`).
- A pasta `Classes/PHPExcel-1.8` deve estar presente no diretório `Classes`.

**Instalação / Preparação**
1. Coloque o projeto no diretório do servidor web (ex.: `C:/inetpub/wwwroot/Caio/ConversorSQL`).
2. Verifique se `Classes/PHPExcel-1.8` está intacta.
3. Ajuste limites de upload no `php.ini` se precisar suportar arquivos maiores (veja seção abaixo).

**Como usar**
1. Acesse a página principal (`index.php`) no navegador.
2. Selecione um arquivo Excel (.xlsx/.xls/.csv) e clique em `Import`.
3. Se o upload for bem-sucedido, o navegador iniciará o download de um arquivo `.sql` contendo os `INSERT` gerados.

**Ajustes importantes / Troubleshooting**
- Limites de upload: se o arquivo for maior que `upload_max_filesize`, o PHP rejeitará o upload. Verifique e ajuste `C:\PHP\v5.6\php.ini` (ou o `php.ini` carregado pelo seu PHP):

```powershell
notepad C:\PHP\v5.6\php.ini
# editar: upload_max_filesize = 10M
#          post_max_size = 20M
# depois reinicie o IIS:
iisreset
```

- Diretório temporário: PHP usa `upload_tmp_dir` (ex.: `C:\Windows\Temp`). Garanta permissões de escrita para o usuário do IIS/PHP ou altere `upload_tmp_dir` no `php.ini` para uma pasta do projeto com permissões adequadas.
- Logs de erro: confira o `error_log` configurado no `php.ini` (ex.: `C:\Windows\temp\PHP56_errors.log`) para mensagens de HTTP 500.
- Avisos do DOM ao ler HTML: já aplicamos uma correção em [Classes/PHPExcel-1.8/Classes/PHPExcel/Reader/HTML.php](Classes/PHPExcel-1.8/Classes/PHPExcel/Reader/HTML.php) para suprimir warnings de `DOMDocument::loadHTML`.
- Mensagens de depuração: temporariamente `index.php` ativa `display_errors`. Remova essas linhas em produção (procure `ini_set('display_errors'` em `index.php`).

**Notas sobre comportamento**
- O gerador usa a primeira linha como cabeçalho (nomes de colunas). Colunas vazias recebem nomes `col_1`, `col_2`, etc.
- O nome da tabela é gerado a partir do nome do arquivo enviado (sem extensão). Você pode alterar isso no script se desejar pedir o nome da tabela via formulário.

**Contribuições / Melhoria futura**
- Melhorar a validação de tipos (datas, números), tratar colunas acentuadas/UTF-8 e permitir salvar o `.sql` no servidor.
- Migrar para `PhpSpreadsheet` (recomendado) se atualizar a versão do PHP.

Se quiser, eu atualizo o `README` com instruções para um fluxo de deploy mais detalhado ou adiciono exemplos de planilha para teste.
