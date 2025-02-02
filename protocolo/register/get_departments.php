<?php
// get_departments.php

require_once '../env/config.php';

// Estabelece conexão com o banco de dados
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro de conexão: ' . $conn->connect_error
    ]));
}

$sql = "SELECT username FROM usuarios";
$result = $conn->query($sql);

$options = "";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departamento = htmlspecialchars($row['username']);
        $options .= "<option value=\"$departamento\">$departamento</option>";
    }
} else {
    $options = "<option value=\"\">Nenhum departamento encontrado</option>";
}

$conn->close();
echo $options;