<?php
header('Content-Type: application/json');

function sendResponse($success, $message)
{
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

$conn = new mysqli("srv1844.hstgr.io", "u492577848_Proto_estagio", "Kellys0n_123", "u492577848_protocolo");

if ($conn->connect_error) {
    sendResponse(false, "Falha na conexão com o banco de dados: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        $username = $_POST["username"];
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            sendResponse(true, "Usuário registrado com sucesso!");
        } else {
            sendResponse(false, "Erro no registro: " . $stmt->error);
        }

        $stmt->close();
    } elseif (isset($_POST['change_password'])) {
        $username = $_POST["username"];
        $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $new_password, $username);

        if ($stmt->execute()) {
            sendResponse(true, "Senha alterada com sucesso!");
        } else {
            sendResponse(false, "Erro ao alterar a senha: " . $stmt->error);
        }

        $stmt->close();
    } elseif (isset($_POST['manage_user'])) {
        $username = $_POST["username"];
        $action = $_POST["action"];

        switch ($action) {
            case 'make_admin':
                $stmt = $conn->prepare("UPDATE usuarios SET is_admin = 1 WHERE username = ?");
                $stmt->bind_param("s", $username);
                break;

            case 'remove_admin':
                $stmt = $conn->prepare("UPDATE usuarios SET is_admin = 0 WHERE username = ?");
                $stmt->bind_param("s", $username);
                break;

            case 'change_username':
                $new_username = $_POST["new_username"];
                $stmt = $conn->prepare("UPDATE usuarios SET username = ? WHERE username = ?");
                $stmt->bind_param("ss", $new_username, $username);
                break;

            case 'delete_user':
                $stmt = $conn->prepare("DELETE FROM usuarios WHERE username = ?");
                $stmt->bind_param("s", $username);
                break;

            default:
                sendResponse(false, "Ação inválida");
                break;
        }

        if ($stmt->execute()) {
            $actionMessages = [
                'make_admin' => 'Usuário promovido a administrador',
                'remove_admin' => 'Privilégios de administrador removidos',
                'change_username' => 'Nome de usuário alterado',
                'delete_user' => 'Usuário excluído'
            ];
            sendResponse(true, $actionMessages[$action]);
        } else {
            sendResponse(false, "Erro ao executar ação: " . $stmt->error);
        }

        $stmt->close();
    }
}

$conn->close();
sendResponse(false, "Requisição inválida");
