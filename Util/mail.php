<?php

use PHPMailer\PHPMailer\PHPMailer;

// Corrigir o caminho do autoload
require_once(__DIR__ . '/../vendor/autoload.php');

// Configuração do modo de teste
$TEST_MODE = true; // Mude para false quando quiser enviar emails reais

error_log("chamado ");

function sendMail($email, $nome, $assunto, $mensagem)
{
    error_log("Função sendMail chamada para: $email");
    global $TEST_MODE;

    if ($TEST_MODE) {
        // Log das informações do email em modo de teste
        error_log("=== EMAIL EM MODO DE TESTE ===");
        error_log("Para: " . $email);
        error_log("Nome: " . $nome);
        error_log("Assunto: " . $assunto);
        error_log("Mensagem: " . $mensagem);
        error_log("============================");
        return true; // Simula envio bem-sucedido
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

        // Adicionar imagem embutida
        // $mail->AddEmbeddedImage('./assets/icon.png', 'logo_demutran');

        $mail->Username = 'demutran@demutranpaudosferros.com.br';
        $mail->setFrom('demutran@demutranpaudosferros.com.br', 'demutran de Pau dos Ferros');
        $mail->Password = 'WRVGAxCbrJ8wdM$';
        $mail->Port = 465;

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
