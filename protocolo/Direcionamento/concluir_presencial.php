<?php
require_once '../env/config.php';

header('Content-Type: application/json');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(array('success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error));
    exit();
}

// Receber dados da solicitação AJAX
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
// Vamos ignorar o valor recebido de novoDepartamento e sempre usar 'Protocolo_Geral'
$novoDepartamento = "Protocolo_Geral";

if (!$id) {
    echo json_encode(array('success' => false, 'message' => 'ID inválido.'));
    exit();
}

// Atualizar o departamento no banco de dados
$sql = "UPDATE protocolos SET departamento_atual = '$novoDepartamento' WHERE id = '$id'";

if (!$conn->query($sql)) {
    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar departamento: ' . $conn->error));
    $conn->close();
    exit();
}

$dataAtual = date('d/m/Y');

// Atualizar o departamento e registrar o redirecionamento na coluna redirecionado
$sql = "UPDATE protocolos SET departamento_atual = '$novoDepartamento', redirecionado = CONCAT_WS(' > Concluido precencial com o', redirecionado, '$novoDepartamento ($dataAtual)') WHERE id = '$id'";
$sql = "UPDATE protocolos SET estado = 2 WHERE id = '$id'";

if (!$conn->query($sql)) {
    echo json_encode(array('success' => false, 'message' => 'Erro ao registrar redirecionamento: ' . $conn->error));
    $conn->close();
    exit();
}

$response = array(
    'success' => true,
    'message' => 'Atualizado com sucesso.'
);

// Enviar resposta JSON
echo json_encode($response);

$conn->close();
?>