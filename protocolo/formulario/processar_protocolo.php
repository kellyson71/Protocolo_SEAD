<?php
// Configurações do banco de dados
require_once '../env/config.php';

// Estabelece conexão com o banco de dados
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro de conexão: ' . $conn->connect_error
    ]));
}

// Obter os dados do formulário
$nome = $_POST['nome'];
$gmail = $_POST['gmail'];
$protocolo = $_POST['protocolo'];
$contexto = $_POST['contexto'];

// Verificar a última ID na tabela
$result = $conn->query("SELECT MAX(id) AS max_id FROM protocolo");
$row = $result->fetch_assoc();
$new_id = $row['max_id'] + 1;

// Preparar e executar a consulta SQL para inserir os dados
$sql = "INSERT INTO protocolo (id, nome, gmail, protocolo, contexto) VALUES ($new_id, '$nome', '$gmail', '$protocolo', '$contexto')";

if ($conn->query($sql) === TRUE) {
    echo "Protocolo adicionado com sucesso!";
} else {
    echo "Erro ao adicionar protocolo: " . $conn->error;
}

// Fechar a conexão com o banco de dados
$conn->close();