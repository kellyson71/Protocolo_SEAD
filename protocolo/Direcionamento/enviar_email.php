<?php
session_start();
require_once '../env/config.php';
require_once '../../Util/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Método não permitido']));
}

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    exit(json_encode(['success' => false, 'message' => 'ID inválido']));
}

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception('Erro de conexão: ' . $conn->connect_error);
    }

    // Inicia a transação
    $conn->begin_transaction();

    try {
        // Primeiro atualiza o estado do protocolo
        $stmt = $conn->prepare("UPDATE protocolos SET estado = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar estado do protocolo');
        }

        // Busca informações do protocolo para o email
        $stmt = $conn->prepare("SELECT gmail as email, nome, numeroProtocolo as numero, requerimento FROM protocolos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Protocolo não encontrado');
        }

        $protocolo = $result->fetch_assoc();

        // Prepara o email
        $assunto = "Protocolo {$protocolo['numero']} - Concluído";
        $mensagem = "
            <html>
            <body>
                <p>Olá {$protocolo['nome']}, gostaríamos de informar que o protocolo referente ao requerimento {$protocolo['requerimento']} com o id {$protocolo['numero']} que você enviou foi concluído.</p>
                <p>Para completar o processo, pedimos que compareça à Prefeitura de Pau dos Ferros de segunda a sexta-feira, no horário das 07h às 13h. Estamos à disposição para qualquer dúvida ou assistência adicional.</p>
                <p>Atenciosamente,<br>SEAD</p>
            </body>
            </html>";

        // Envia o email usando a função genérica
        if (!sendMail($protocolo['email'], $protocolo['nome'], $assunto, $mensagem)) {
            throw new Exception('Erro ao enviar email');
        }

        // Se chegou até aqui, confirma as alterações
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Protocolo atualizado e email enviado com sucesso']);
    } catch (Exception $e) {
        // Se algo deu errado, desfaz as alterações
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro no processamento: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
