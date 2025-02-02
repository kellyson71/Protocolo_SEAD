<?php
session_start();
require_once '../env/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die(json_encode(['success' => false, 'message' => 'Não autorizado']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['field']) || !isset($data['value'])) {
    die(json_encode(['success' => false, 'message' => 'Dados inválidos']));
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro na conexão']));
}

$id = intval($data['id']);
$field = $conn->real_escape_string($data['field']);
$value = $conn->real_escape_string($data['value']);

$sql = "UPDATE protocolos SET `$field` = '$value' WHERE id = $id";
$result = $conn->query($sql);

$response = [
    'success' => $result === TRUE,
    'message' => $result === TRUE ? 'Atualizado com sucesso' : 'Erro ao atualizar'
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();