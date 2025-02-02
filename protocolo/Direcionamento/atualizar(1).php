<?php
require_once '../env/config.php';

// Conexão com o banco de dados
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica se foram recebidos os dados do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os valores do formulário
    $column = $_POST['column'];
    $id = $_POST['id'];
    $new_value = 1; // Modificando o novo valor para 1

    // Verifica se os campos foram preenchidos
    if (!empty($column) && !empty($id)) {
        // Consulta SQL para atualizar o valor da coluna "estado" para 1
        $sql = "UPDATE protocolos SET estado = 1 WHERE id = $id";

        // Executa a consulta
        if ($conn->query($sql) === TRUE) {
            echo "Registro atualizado com sucesso.";
        } else {
            echo "Erro ao atualizar registro: " . $conn->error;
        }
    } else {
        echo "Por favor, selecione uma coluna e um ID.";
    }
}

// Fecha a conexão
$conn->close();
?>