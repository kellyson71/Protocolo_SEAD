<?php

header('Access-Control-Allow-Origin: *');
define('DBHOST', 'srv1844.hstgr.io');
define('DBNAME', 'u492577848_protocolo');
define('USER', 'u492577848_Proto_estagio');
define('DBPASSWORD', 'Kellys0n_123');

require __DIR__ . '/../vendor/autoload.php'; // Ajuste conforme necessário
require_once(__DIR__ . '/../Util/mail.php');

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
        // Gerar o token e atualizar no banco
        $token = bin2hex(random_bytes(16));
        $stmt = $conn->prepare("UPDATE protocolos SET token = ? WHERE id = ?;");
        $stmt->bind_param('si', $token, $numeroProtocolo);
        $stmt->execute();

        // Preparar o corpo do email
        $emailBody = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #009640; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { background-color: #f4f4f4; padding: 20px; text-align: center; }
                    .info { margin: 20px 0; }
                    .contact { margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Protocolo Registrado com Sucesso</h2>
                    </div>
                    <div class='content'>
                        <p>Olá {$data['name']},</p>
                        <div class='info'>
                            <p>Seu protocolo foi registrado com sucesso:</p>
                            <ul>
                                <li>Número do Protocolo: {$numeroProtocolo}</li>
                                <li>Assunto: {$data['assunto']}</li>
                            </ul>
                        </div>
                        <p>Para confirmar a leitura deste e-mail, acesse: 
                            <a href='https://protocolosead.com/confirmar_leitura.php?id={$numeroProtocolo}&token={$token}'>
                                Confirmar Leitura
                            </a>
                        </p>
                        <p>Aguarde nosso contato para dar continuidade ao processo do seu requerimento.</p>
                        <div class='contact'>
                            <p><strong>Em caso de dúvidas:</strong></p>
                            <ul>
                                <li>E-mail: protocolopmpf@gmail.com</li>
                                <li>WhatsApp: (84) 99858-6712</li>
                            </ul>
                        </div>
                    </div>
                    <div class='footer'>
                        <p>Esta é uma mensagem automática. Por favor, não responda este e-mail.</p>
                        <p>SEAD - Secretaria de Administração<br>Prefeitura Municipal de Pau dos Ferros</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        // Enviar email usando o mail.php
        $enviado = sendMail(
            $data['email'],
            $data['name'],
            'Protocolo registrado com sucesso',
            $emailBody
        );

        if (!$enviado) {
            error_log("Erro ao enviar email para: " . $data['email']);
        }

        echo json_encode(array("message" => "Protocolo criado com sucesso! Você receberá um e-mail de confirmação."));
    } else {
        throw new Exception("Erro no cadastro: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array("message" => "Erro ao processar sua solicitação: " . $e->getMessage()));
}
