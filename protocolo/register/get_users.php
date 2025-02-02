<?php
header('Content-Type: application/json');

$conn = new mysqli("srv1844.hstgr.io", "u492577848_Proto_estagio", "Kellys0n_123", "u492577848_protocolo");

if ($conn->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

$sql = "SELECT username, is_admin FROM usuarios ORDER BY username";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'username' => $row['username'],
            // Cast to boolean for proper JSON response
            'is_admin' => (bool)$row['is_admin']
        ];
    }
}

$conn->close();
echo json_encode(['users' => $users]);
