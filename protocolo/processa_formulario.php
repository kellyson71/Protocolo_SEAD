<?php
// Configurações do banco de dados
require_once './env/config.php';

header('Content-Type: application/json');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(array('success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error));
    exit();
}

// Obter os dados do formulário
$dataEntrada = $_POST['dataEntrada'];
$numeroProtocolo = $_POST['numeroProtocolo'];
$nome = $_POST['nome'];
$matricula = $_POST['matricula'];
$endereco = $_POST['endereco'];
$telefone = $_POST['telefone'];
$cargo = $_POST['cargo'];
$unidadeTrabalho = $_POST['unidadeTrabalho'];
$lotacao = $_POST['lotacao'];
$vinculo = $_POST['vinculo'];
$dataAdmissao = $_POST['dataAdmissao'];
$objetoRequerimento = $_POST['objetoRequerimento'];

// Verificar a última ID na tabela
$result = $conn->query("SELECT MAX(id) AS max_id FROM protocolos");
$row = $result->fetch_assoc();
$new_id = $row['max_id'] + 1;

// Preparar e executar a consulta SQL para inserir os dados
$sql = "INSERT INTO protocolos (id, dataEntrada, numeroProtocolo, nome, matricula, endereco, telefone, cargo, unidadeTrabalho, lotacao, vinculo, dataAdmissao, requerimento) 
        VALUES ($new_id, '$dataEntrada', '$numeroProtocolo', '$nome', '$matricula', '$endereco', '$telefone', '$cargo', '$unidadeTrabalho', '$lotacao', '$vinculo', '$dataAdmissao', '$objetoRequerimento')";

if ($conn->query($sql) === TRUE) {
    echo "Protocolo adicionado com sucesso!";
} else {
    echo "Erro ao adicionar protocolo: " . $conn->error;
}

// Fechar a conexão com o banco de dados
$conn->close();