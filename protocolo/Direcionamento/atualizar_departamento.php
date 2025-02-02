<?php
require_once '../env/config.php';

// Estabelece conexão com o banco de dados
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro de conexão: ' . $conn->connect_error
    ]));
}

// Recebe e decodifica os dados JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['novoDepartamento'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Dados incompletos'
    ]);
    exit;
}

$id = $conn->real_escape_string($data['id']);
$novoDepartamento = $conn->real_escape_string($data['novoDepartamento']);
$dataAtual = date('d/m/Y');

// Primeiro, verifica se o protocolo existe
$checkSql = "SELECT departamento_atual, redirecionado FROM protocolos WHERE id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Protocolo não encontrado'
    ]);
    exit;
}

$row = $result->fetch_assoc();
$departamentoAtual = $row['departamento_atual'];
$redirecionadoAtual = $row['redirecionado'];

// Atualiza o departamento e o histórico de redirecionamentos
$updateSql = "UPDATE protocolos SET 
              departamento_atual = ?,
              redirecionado = ? 
              WHERE id = ?";

// Prepara o novo valor para redirecionado
$novoRedirecionado = empty($redirecionadoAtual) ? 
    "$novoDepartamento ($dataAtual)" : 
    "$redirecionadoAtual > $novoDepartamento ($dataAtual)";

$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("sss", 
    $novoDepartamento,
    $novoRedirecionado,
    $id
);

$success = $updateStmt->execute();

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Protocolo redirecionado com sucesso' : 'Erro ao redirecionar protocolo'
]);

$checkStmt->close();
$updateStmt->close();
$conn->close();
?>