<?php
require_once '../env/config.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão: ' . $conn->connect_error]);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    $dataAtual = date('d/m/Y');
    $novoDepartamento = "Protocolo_Geral";

    // Update departamento_atual and redirecionado
    $sql1 = "UPDATE protocolos SET 
             departamento_atual = ?, 
             redirecionado = CONCAT_WS(' > Concluido', redirecionado, ? )
             WHERE id = ?";
    
    $stmt1 = $conn->prepare($sql1);
    $redirectInfo = $novoDepartamento . ' (' . $dataAtual . ')';
    $stmt1->bind_param('ssi', $novoDepartamento, $redirectInfo, $id);
    $stmt1->execute();

    // Update estado
    $sql2 = "UPDATE protocolos SET estado = 1 WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param('i', $id);
    $stmt2->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Protocolo concluído com sucesso']);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>