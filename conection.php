<?php
// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "importaarquivo";

// Verifica se o arquivo foi enviado
if (isset($_FILES['xlsxFile'])) {
    $file = $_FILES['xlsxFile'];

    // Verifica se não houve erro no upload
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Verifica se o arquivo é um arquivo XLSX
        if ($fileType === 'xlsx') {
            $uploadDir = 'uploads/';
            $uploadPath = $uploadDir . basename($file['name']);

            // Move o arquivo para o diretório de upload
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Arquivo foi enviado e movido com sucesso, agora você pode processá-lo e inserir no banco de dados

                // Incluir a classe PHPExcel
                require 'PHPExcel/Classes/PHPExcel.php';

                // Criar um objeto PHPExcel
                $objPHPExcel = PHPExcel_IOFactory::load($uploadPath);

                // Conexão com o banco de dados
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Verifica a conexão
                if ($conn->connect_error) {
                    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
                }

                // Loop através das linhas da planilha
                foreach ($objPHPExcel->getActiveSheet()->getRowIterator() as $row) {
                    // Pula a primeira linha, que provavelmente contém cabeçalhos
                    if ($row->getRowIndex() === 1) {
                        continue;
                    }

                    // Obtém os valores das células
                    $rowData = $row->getCellIterator();
                    $data = [];
                    foreach ($rowData as $cell) {
                        $data[] = $cell->getValue();
                    }

                    // Insere os dados no banco de dados
                    $sql = "INSERT INTO cbcontrato (nome, contrato, carteira) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $data[0], $data[1], $data[2]);
                    $stmt->execute();
                }

                // Fecha a conexão com o banco de dados
                $conn->close();

                echo 'Arquivo enviado e inserido no banco de dados com sucesso.';
            } else {
                echo 'Erro ao mover o arquivo.';
            }
        } else {
            echo 'Por favor, envie um arquivo XLSX válido.';
        }
    } else {
        echo 'Erro no upload do arquivo: ' . $file['error'];
    }
} else {
    echo 'Nenhum arquivo foi enviado.';
}
?>
