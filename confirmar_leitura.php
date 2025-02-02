<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Leitura</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap">
    <style>
    /* Estilos Globais */
    body {
        background-image: url('./assets/background.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
        color: #023047;
    }

    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    /* Caixa de Mensagem */
    .form-container {
        background-color: #fff;
        border-radius: 7px;
        padding: 40px;
        box-shadow: 10px 10px 40px rgba(0, 0, 0, 0.4);
        text-align: center;
        max-width: 400px;
        width: 100%;
    }

    .form-container h1 {
        margin: 0 0 20px;
        font-weight: 500;
        font-size: 2.3em;
    }

    .form-container p {
        font-size: 14px;
        color: #666;
        margin-bottom: 25px;
    }

    .success-message {
        color: green;
        margin-top: 10px;
    }

    .error-message {
        color: red;
        margin-top: 10px;
    }

    /* Botão de Voltar */
    .back-button {
        background-color: #00b04b;
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
        text-decoration: none;
    }

    .back-button:hover {
        background-color: #007a33;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h1>Confirmação de Leitura</h1>
            <?php
            // Configurações do banco de dados
            require_once './protocolo/env/config.php';

            header('Content-Type: application/json');

            $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

            if ($conn->connect_error) {
                echo json_encode(array('success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error));
                exit();
            }

            // Conexão com o banco de dados
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Verifica se houve erro na conexão
            if ($conn->connect_error) {
                die("<p class='error-message'>Erro na conexão: " . $conn->connect_error . "</p>");
            }

            // Verifica se os parâmetros id e token estão presentes na URL
            if (isset($_GET['id']) && isset($_GET['token'])) {
                $id_protocolo = $_GET['id'];
                $token = $_GET['token'];

                // Verifica se o token corresponde ao ID
                $stmt = $conn->prepare("SELECT nome, requerimento, token, lido FROM protocolos WHERE numeroProtocolo = ? AND token = ?");
                if ($stmt === false) {
                    die("<p class='error-message'>Erro ao preparar a consulta: " . $conn->error . "</p>");
                }

                $stmt->bind_param('is', $id_protocolo, $token);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    // Token é válido, prossiga com a verificação do status de leitura
                    $protocolo = $result->fetch_assoc();
                    $nome_usuario = $protocolo['nome'];
                    $nome_protocolo = $protocolo['requerimento'];

                    if ($protocolo['lido']) {
                        // Protocolo já foi lido
                        echo "<p class='error-message'>Olá $nome_usuario, seu protocolo \"$nome_protocolo\" já foi confirmado como lido.</p>";
                    } else {
                        // Atualiza o valor de 'lido' para true
                        $sql = "UPDATE protocolos SET lido = TRUE WHERE numeroProtocolo = ?";
                        $stmt_update = $conn->prepare($sql);
                        if ($stmt_update === false) {
                            die("<p class='error-message'>Erro ao preparar a consulta de atualização: " . $conn->error . "</p>");
                        }

                        $stmt_update->bind_param('i', $id_protocolo);
                        $stmt_update->execute();

                        if ($stmt_update->affected_rows > 0) {
                            echo "<p class='success-message'>Olá $nome_usuario, seu protocolo \"$nome_protocolo\" foi confirmado como lido com sucesso!</p>
                    <p>Aguarde nosso contato para dar continuidade ao processo do seu requerimento. Estamos à disposição para qualquer dúvida ou assistência adicional.</p>
                    <p>Atenciosamente,<br>SEAD</p>";
                        } else {
                            echo "<p class='error-message'>Erro ao confirmar leitura: Nenhuma linha foi afetada. Verifique se o protocolo já foi marcado como lido ou se o ID está correto.</p>";
                        }
                    }
                } else {
                    // Token inválido ou link adulterado
                    echo "<p class='error-message'>Link inválido ou já utilizado.</p>";
                }
            } else {
                // Parâmetros id ou token ausentes
                echo "<p class='error-message'>Parâmetros inválidos. Certifique-se de acessar o link correto.</p>";
            }

            // Fecha a conexão
            $conn->close();
            ?>
            <button class="back-button" onclick="window.history.back()">Voltar</button>
        </div>
    </div>
</body>

</html>