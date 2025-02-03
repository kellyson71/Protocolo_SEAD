<?php
header('Content-Type: application/json');
require_once '../env/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = $data['id'];

    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Erro na conexão: ' . $conn->connect_error]);
        exit;
    }

    $sql = "DELETE FROM protocolos WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        // Remove the directories
        $directory1 = $_SERVER['DOCUMENT_ROOT'] . '/media/' . $id;
        $directory2 = $_SERVER['DOCUMENT_ROOT'] . '/protocolo/Direcionamento/media/' . $id;

        function deleteDirectory($dir)
        {
            if (!file_exists($dir)) {
                return true;
            }

            if (!is_dir($dir)) {
                return unlink($dir);
            }

            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }

                if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                    return false;
                }
            }

            return rmdir($dir);
        }

        $success1 = deleteDirectory($directory1);
        $success2 = deleteDirectory($directory2);

        if ($success1 && $success2) {
            echo json_encode(['success' => true, 'message' => 'Protocolo e diretórios excluídos com sucesso.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Protocolo excluído, mas erro ao excluir um ou mais diretórios.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o protocolo: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID do protocolo não fornecido.']);
}
?>