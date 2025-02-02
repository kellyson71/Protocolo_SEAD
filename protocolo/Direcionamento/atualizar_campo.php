<?php
require_once '../env/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['column']) || !isset($data['new_value'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão']);
    exit;
}

$id = $conn->real_escape_string($data['id']);
$column = $conn->real_escape_string($data['column']);
$new_value = $conn->real_escape_string($data['new_value']);

$sql = "UPDATE protocolos SET `$column` = '$new_value' WHERE id = '$id'";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Atualizado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
}

$conn->close();