<?php
require_once '../env/config.php';

header('Content-Type: application/json');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(array('success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error));
    exit();
}
try {
    // Receber dados da solicitação AJAX
    $data = json_decode(file_get_contents("php://input"), true);

    $protocolo = $data['protocolo'];
    $contexto = $data['contexto'];
    $novoDepartamento = $data['novoDepartamento'];

    // Atualizar o departamento no banco de dados
    $sql = "UPDATE protocolos SET departamento_atual = '$novoDepartamento' WHERE protocolo = '$protocolo' AND contexto = '$contexto'";
    $result = $conn->query($sql);

    // Preparar resposta JSON
    $response = array(
        'success' => $result,
        'message' => $result ? '  com sucesso.' : 'Falha ao atualizar o departamento.'
    );

    // Enviar resposta JSON
    header('Content-Type: application/json');
    echo json_encode($response);

    $dataAtual = date('d/m/Y');

    // Atualizar o departamento e registrar o redirecionamento na coluna redirecionado
    $sql = "UPDATE protocolos SET departamento_atual = '$novoDepartamento', redirecionado = CONCAT_WS(' > ', redirecionado, '$novoDepartamento ($dataAtual)') WHERE protocolo = '$protocolo' AND contexto = '$contexto'";

    $result = $conn->query($sql);
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

// Fechar a conexão com o banco de dados
$conn->close();