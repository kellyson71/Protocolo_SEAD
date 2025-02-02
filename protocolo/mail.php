<?php
// Inclui o arquivo do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['proto'] === null) {
        echo 'BAD REQUEST';
        exit;
    }

    $protocolo = (int) $_POST['proto'];

    function sendMail($protoId)
    {
        try {
            $conn = new mysqli("srv1844.hstgr.io", "u492577848_Proto_estagio", "Kellys0n_123", "u492577848_protocolo");

            if ($conn->connect_error) {
                die("Falha na conexão com o banco de dados: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("SELECT nome, gmail, requerimento FROM protocolos WHERE id = ?;");
            $bind = $stmt->bind_param('i', $protoId);
            $stmt->execute();
            $result = $stmt->get_result();

            $proto = $result->fetch_assoc();

            if ($proto == null) {
                echo 'Protocolo não encontrado';
                exit;
            }

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

            $mail->Subject = 'Protocolo deferido';
            $mail->Body    = 'Olá, gostaríamos de informar que o protocolo referente ao requerimento número ' . $proto['requerimento'] . ' que você enviou foi concluido.

Para completar o processo, pedimos que compareça à Prefeitura de Pau dos Ferros de segunda a sexta-feira, no horário das 07h às 13h. Estamos à disposição para qualquer dúvida ou assistência adicional.

Atenciosamente,
SEAD
';

            if (!$mail->send()) {
                echo 'O e-mail não foi enviado.';
                echo 'Erro: ' . $mail->ErrorInfo;
            } else {
                echo 'O e-mail foi enviado com sucesso.';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    sendMail($protocolo);
}
?>
<html>

<head>
    <title>Terminar protocolo</title>
</head>

<body>
    <?php
    if ($_GET['proto'] !== null) {
        echo '<form method="POST" action="">
                        <div>
                            <label>Protocolo de id ' . $_GET['proto'] . '</label>
                        </div>
                        <div>
                            <input id="proto" name="proto" value="' . $_GET['proto'] . '" hidden>
                        </div>
                        <button>Deferir protocolo</button>
                    </form>
                    ';
    } else {
        echo 'Nenhum protocolo selecionado';
    }
    ?>
</body>

</html>