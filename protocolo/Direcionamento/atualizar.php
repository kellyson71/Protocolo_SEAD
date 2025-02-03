<?php
header('Content-Type: application/json');
require __DIR__ . '/../vendor/autoload.php';
require_once '../env/config.php';

use PHPMailer\PHPMailer\PHPMailer;

try {
    // Recebe os dados JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['column']) || !isset($data['new_value'])) {
        throw new Exception('Dados incompletos');
    }

    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Erro na conexão: " . $conn->connect_error);
    }

    $id = $data['id'];
    $column = $data['column'];
    $new_value = $data['new_value'];

    // Atualiza o valor no banco de dados
    $sql = "UPDATE protocolos SET $column = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $new_value, $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar registro");
    }

    // Se for atualização de estado, envia e-mail
    if ($column == "estado" && $new_value == "1") {
        $stmt = $conn->prepare("SELECT nome, requerimento, gmail, numeroProtocolo FROM protocolos WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $proto = $result->fetch_assoc();

        if ($proto) {
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
            $mail->isHTML(true);
            
            $mail->Body = '
                <html>
                <body>
                    <p>Olá ' . $proto['nome'] . ', gostaríamos de informar que o protocolo referente ao requerimento ' . $proto['requerimento'] . ' com o id ' . $proto['numeroProtocolo'] . ' que você enviou foi concluído.</p>
                    <p>Para completar o processo, pedimos que compareça à Prefeitura de Pau dos Ferros de segunda a sexta-feira, no horário das 07h às 13h. Estamos à disposição para qualquer dúvida ou assistência adicional.</p>
                    <p>Atenciosamente,<br>SEAD</p>
                </body>
                </html>';

            if (!$mail->send()) {
                throw new Exception("Erro ao enviar e-mail: " . $mail->ErrorInfo);
            }
        }
    }

    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Operação realizada com sucesso'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}