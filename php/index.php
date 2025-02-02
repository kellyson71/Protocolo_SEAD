<?php

header('Access-Control-Allow-Origin: *');
define('DBHOST', 'srv1844.hstgr.io');
define('DBNAME', 'u492577848_protocolo');
define('USER', 'u492577848_Proto_estagio');
define('DBPASSWORD', 'Kellys0n_123');

require __DIR__ . '/../vendor/autoload.php'; // Ajuste conforme necessário

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode($_POST['json'], true);

try {
    $conn = new mysqli(DBHOST, USER, DBPASSWORD, DBNAME);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    function protocoloExiste($conn, $nome, $assunto)
    {
        $sql = "SELECT 1 FROM protocolos WHERE nome = ? AND requerimento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nome, $assunto);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    $nome = $data['name'];
    $assunto = $data['assunto'];

    if (protocoloExiste($conn, $nome, $assunto)) {
        http_response_code(400);
        echo json_encode(array("message" => "Você já criou esse protocolo"));
        exit(0);
    }

    function obterProximoID($conn)
    {
        $sql = "SELECT MAX(ID) AS max_id FROM protocolos";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row["max_id"] + 1;
        } else {
            return 1;
        }
    }

    $numeroProtocolo = obterProximoID($conn);
    $dst = '../media/' . $numeroProtocolo . '/';

    // Função para excluir diretório e seu conteúdo
    function deleteDirectory($dir)
    {
        if (!file_exists($dir)) return;

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    // Se o diretório existir, exclui ele
    if (is_dir($dst)) {
        deleteDirectory($dst);
    }

    // Cria o novo diretório
    if (!mkdir($dst, 0755, true)) {
        throw new Exception("Não foi possível criar o diretório para os arquivos");
    }

    foreach ($_FILES as $f) {
        $filename = $f['name'];
        if (!move_uploaded_file($f['tmp_name'], $dst . $filename)) {
            throw new Exception("Erro ao fazer upload do arquivo: " . $filename);
        }
    }

    date_default_timezone_set('America/Sao_Paulo');
    $dataEntrada = date('d/m/Y');

    $documentos = isset($_POST['nome_documento']) ? $_POST['nome_documento'] : null;

    $sql = "INSERT INTO protocolos (
        id,
        nome,
        gmail,
        contexto,
        requerimento,
        departamento_atual,
        data,
        dataEntrada,
        numeroProtocolo,
        matricula,
        endereco,
        telefone,
        unidadeTrabalho,
        documentos,
        lotacao,
        vinculo
    ) VALUES (
        ?, ?, ?, ?, ?, 'Secretaria', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssssisssssss",
        $numeroProtocolo,
        $data['name'],
        $data['email'],
        $data['descricao'],
        $data['assunto'],
        $dataEntrada,
        $dataEntrada,
        $numeroProtocolo,
        $data['matricula'],
        $data['endereco'],
        $data['phone'],
        $documentos,
        $data['UT'],
        $data['lotacao'],
        $data['vinculo']
    );

    if ($stmt->execute()) {
        echo json_encode(array("message" => "Protocolo criado com sucesso! Você receberá um e-mail de confirmação."));

        // Envio de e-mail
        try {
            $token = bin2hex(random_bytes(16));

            // Armazenar o token no banco de dados
            $stmt = $conn->prepare("UPDATE protocolos SET token = ? WHERE id = ?;");
            $stmt->bind_param('si', $token, $numeroProtocolo);
            $stmt->execute();

            // Configuração do e-mail
            $mail = new PHPMailer(true); // true habilita exceções
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "ssl";
            $mail->Username = 'proto_sead@potocolo.estagiopaudosferros.com';
            $mail->setFrom('proto_sead@potocolo.estagiopaudosferros.com', 'Prefeitura de Pau dos Ferros');
            $mail->Password = 'Teste123!';
            $mail->Port = 465;

            $mail->addAddress($data['email'], $data['name']);
            $mail->Subject = '=?UTF-8?B?' . base64_encode('Protocolo registrado com sucesso') . '?=';
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
                <body style="font-family: Arial, sans-serif; line-height: 1.6;">
                    <p>Olá ' . $data['name'] . ',</p>
                    <p>Informamos que seu protocolo foi registrado com sucesso:</p>
                    <ul>
                        <li>Número do Protocolo: ' . $numeroProtocolo . '</li>
                        <li>Assunto: ' . $data['assunto'] . '</li>
                    </ul>
                    <p>Para confirmar a leitura deste e-mail, por favor, clique no link: <a href="https://protocolosead.com/confirmar_leitura.php?id=' . $numeroProtocolo . '&token=' . $token . '" class="link-confirm">Confirmar Leitura</a></p>
                    <p>Aguarde nosso contato para dar continuidade ao processo do seu requerimento.</p>
                    <hr>
                    <p><strong>Em caso de dúvidas:</strong></p>
                    <ul>
                        <li>E-mail: protocolopmpf@gmail.com</li>
                        <li>WhatsApp: (84) 99858-6712</li>
                    </ul>
                    <p><em>Esta é uma mensagem automática. Por favor, não responda este e-mail.</em></p>
                    <p>Atenciosamente,<br>SEAD - Secretaria de Administração<br>Prefeitura Municipal de Pau dos Ferros</p>
                </body>
                </html>
            ';
            $mail->isHTML(true);

            if (!$mail->send()) {
                error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
            }
        } catch (Exception $e) {
            error_log('Erro ao configurar o e-mail: ' . $e->getMessage());
        }
    } else {
        throw new Exception("Erro no cadastro: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array("message" => "Erro ao processar sua solicitação: " . $e->getMessage()));
}
