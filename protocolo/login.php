<?php
session_start();

// Verifique se estas são as credenciais corretas
$host = "srv1844.hstgr.io";
$user = "u492577848_Proto_estagio";
$password = "Kellys0n_123"; // Confirme se esta senha está correta
$database = "u492577848_protocolo";

try {
    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        throw new Exception("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Erro de conexão: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, username, password FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION['loggedin'] = true;
        $_SESSION['dep'] = strtolower($user["username"]);

        $dep_param = urlencode($user["username"]);
        $dep_param = strtolower($dep_param);
        // Enviando um aviso de login para a página index.php
        header("Location: ./Direcionamento/index.php?dep=$dep_param");
        exit();
    } else {
        echo '<script>
                alert("Senha ou e-mail incorretos. Verifique suas credenciais.");
                window.location.href = "index.html"; 
              </script>';
    }

    $stmt->close();
}

$conn->close();