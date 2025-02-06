<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once(__DIR__ . '/../vendor/autoload.php');

$TEST_MODE = true; // Mude para false quando quiser enviar emails reais

error_log("chamado ");

function sendMail($email, $nome, $assunto, $mensagem)
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

        $mail->Username = 'proto_sead@potocolo.estagiopaudosferros.com';
        $mail->Password = 'Teste123!';
        $mail->Port = 465;

        $mail->setFrom('proto_sead@potocolo.estagiopaudosferros.com', 'Prefeitura de Pau dos Ferros');
        $mail->addAddress($email, $nome);
        $mail->Subject = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
        $mail->isHTML(true);
        $mail->Body = mb_convert_encoding($mensagem, 'UTF-8', 'UTF-8');

        if (!$mail->send()) {
            error_log("Erro ao enviar email: " . $mail->ErrorInfo);
            return false;
        } else {
            error_log("Email enviado com sucesso para: " . $email);
            return true;
        }
    } catch (Exception $e) {
        error_log("Exceção ao enviar email: " . $e->getMessage());
        return false;
    }
}