<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Registro</title>
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

    /* Formulário */
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

    .form-container input[type="text"] {
        padding: 15px;
        font-size: 14px;
        border: 1px solid #ccc;
        margin-bottom: 20px;
        margin-top: 5px;
        border-radius: 4px;
        transition: all linear 160ms;
        outline: none;
        width: calc(100% - 30px);
    }

    .form-container input[type="text"]:focus {
        border: 1px solid #024287;
    }

    .form-container input[type="submit"] {
        background-color: #024287;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        border: none !important;
        transition: all linear 160ms;
        cursor: pointer;
        margin: 0 !important;
        padding: 15px 30px;
        border-radius: 4px;
    }

    .form-container input[type="submit"]:hover {
        transform: scale(1.05);
        background-color: #0061c8;
    }

    .error-message {
        color: red;
        margin-top: 10px;
    }

    .success-message {
        color: green;
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
            <h1>Atualizar Registro</h1>
            <?php

            use PHPMailer\PHPMailer\PHPMailer;

            require __DIR__ . '/../../vendor/autoload.php';

            // Configurações do banco de dados
            // faço depois

            // Conexão com o banco de dados
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Verifica se houve erro na conexão
            if ($conn->connect_error) {
                die("Erro na conexão: " . $conn->connect_error);
            }

            // Verifica se foram recebidos os dados do formulário
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Recebe os valores do formulário
                $id = $_POST['id'];
                $column = $_POST['column'];
                $new_value = $_POST['new_value'];

                // Verifica se os campos foram preenchidos
                if (!empty($id) && !empty($column) && !empty($new_value)) {
                    // Atualiza o valor no banco de dados
                    $sql = "UPDATE protocolos SET $column = '$new_value' WHERE id = $id";

                    if ($conn->query($sql) === TRUE) {
                        echo "<p class='success-message'>Registro atualizado com sucesso.</p>";
                    } else {
                        echo "<p class='error-message'>Erro ao atualizar o registro: " . $conn->error . "</p>";
                    }
                } else {
                    echo "<p class='error-message'>Por favor, preencha todos os campos.</p>";
                }

                try {

                    if ($column == "estado") {
                        $stmt = $conn->prepare("SELECT nome, requerimento, gmail, numeroProtocolo FROM protocolos WHERE id = ?;");
                        $stmt->bind_param('i', $id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $proto = $result->fetch_assoc();

                        if ($proto == null) {
                            exit;
                        }

                        // Gerar um token seguro
                        $token = bin2hex(random_bytes(16));

                        // Armazenar o token no banco de dados
                        $stmt = $conn->prepare("UPDATE protocolos SET token = ? WHERE id = ?;");
                        $stmt->bind_param('si', $token, $id);
                        $stmt->execute();

                        // Configuração do e-mail
                        $mail = new PHPMailer;
                        $mail->isSMTP();
                        $mail->Host = 'smtp.hostinger.com';
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = "ssl";

                        $mail->Username = 'test@potocolo.estagiopaudosferros.com';
                        $mail->setFrom('test@potocolo.estagiopaudosferros.com', 'Prefeitura de Pau dos Ferros');
                        $mail->Password = 'Teste123!';
                        $mail->Port = 465;

                        $mail->addAddress($proto['gmail'], $proto['nome']);
                        $mail->Subject = '=?UTF-8?B?' . base64_encode('Protocolo concluído') . '?=';
                        $mail->Body    = '
        <html>
        <head>
            <style>
                .link-confirm {
                    color: #007bff;
                    font-weight: bold;
                    text-decoration: none;
                    transition: color 0.3s ease;
                }
                .link-confirm:hover {
                    color: #0056b3;
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <p>Olá ' . $proto['nome'] . ', gostaríamos de informar que o protocolo referente ao requerimento ' . $proto['requerimento'] . ' com o id ' . $proto['numeroProtocolo'] . ' que você enviou foi concluído.</p>
            <p>Para confirmar a leitura deste e-mail, por favor, clique no link: <a href="https://protocolosead.com/confirmar_leitura.php?id=' . $proto['numeroProtocolo'] . '&token=' . $token . '" class="link-confirm">Confirmar Leitura</a></p>
            <p>Para completar o processo, pedimos que compareça à Prefeitura de Pau dos Ferros de segunda a sexta-feira, no horário das 07h às 13h. Estamos à disposição para qualquer dúvida ou assistência adicional.</p>
            <p>Atenciosamente,<br>SEAD</p>
        </body>
        </html>
    ';
                        $mail->isHTML(true);

                        if (!$mail->send()) {
                            echo '<p>O e-mail não foi enviado.</p>';
                            echo '<p>Erro: ' . $mail->ErrorInfo . "</p>";
                        } else {
                            echo '<p>O e-mail foi enviado com sucesso.</p>';
                        }
                    }
                } catch (Exception $e) {
                    echo "<p>" . $e->getMessage() . "</p>";
                    exit;
                }
            }

            // Fecha a conexão
            $conn->close();
            ?>
            <a class="back-button"
                href="/protocolo/Direcionamento/detalhes_protocolo.php?id=<?php echo $id; ?>">Voltar</a>
        </div>
    </div>
</body>

</html>