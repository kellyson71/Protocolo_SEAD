<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../env/config.php');

$TEST_MODE = false;

function logEmail($protocolo_id, $email_destino, $assunto, $mensagem, $status, $erro = null)
{
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        error_log("Erro de conexão com banco: " . $conn->connect_error);
        return false;
    }

    // Pega o usuário da sessão
    session_start();
    $usuario = isset($_SESSION['username']) ? $_SESSION['username'] : 'Sistema';

    $sql = "INSERT INTO email_logs (protocolo_id, email_destino, assunto, mensagem, usuario_envio, status, erro) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssss",
        $protocolo_id,
        $email_destino,
        $assunto,
        $mensagem,
        $usuario,
        $status,
        $erro
    );

    $result = $stmt->execute();

    if (!$result) {
        error_log("Erro ao registrar log: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    return $result;
}

function sendMail($email, $nome, $assunto, $mensagem, $protocolo_id = null)
{
    error_log("Função sendMail chamada para: $email");
    global $TEST_MODE;

    if ($TEST_MODE) {
        error_log("=== EMAIL EM MODO DE TESTE ===");
        error_log("Para: " . $email);
        error_log("Nome: " . $nome);
        error_log("Assunto: " . $assunto);
        error_log("Mensagem: " . $mensagem);
        error_log("============================");
        return true;
    }

    try {
        error_log("Iniciando envio de email para: " . $email);

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->Username = 'naoresponder@protocolosead.com';
        $mail->Password = 'Kellys0n_123';
        $mail->Port = 465;

        $mail->setFrom('naoresponder@protocolosead.com', 'Prefeitura de Pau dos Ferros');
        $mail->addAddress($email, $nome);
        $mail->Subject = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
        $mail->isHTML(true);
        $mail->Body = mb_convert_encoding($mensagem, 'UTF-8', 'UTF-8');

        if (!$mail->send()) {
            $erro = $mail->ErrorInfo;
            error_log("Erro ao enviar email: " . $erro);
            if ($protocolo_id) {
                logEmail($protocolo_id, $email, $assunto, $mensagem, 'ERRO', $erro);
            }
            return false;
        } else {
            error_log("Email enviado com sucesso para: " . $email);
            if ($protocolo_id) {
                logEmail($protocolo_id, $email, $assunto, $mensagem, 'SUCESSO');
            }
            return true;
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
        error_log("Exceção ao enviar email: " . $erro);
        if ($protocolo_id) {
            logEmail($protocolo_id, $email, $assunto, $mensagem, 'ERRO', $erro);
        }
        return false;
    }
}