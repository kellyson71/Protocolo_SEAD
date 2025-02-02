<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Protocolo</title>
    <style>
    #concluirProtocoloBtn {
        background-color: #4CAF50;
        /* Cor de fundo verde */
        color: white;
        /* Cor do texto branco */
        border: none;
        /* Sem borda */
        padding: 10px 20px;
        /* Preenchimento interno do botão */
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin-top: 20px;
        cursor: pointer;
        border-radius: 5px;
        /* Borda arredondada */
        transition: background-color 0.3s ease;
        /* Transição suave da cor de fundo */
    }

    #concluirProtocoloBtn:hover {
        background-color: #45a049;
        /* Cor de fundo verde mais escura ao passar o mouse */
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 20px;
        padding: 20px;
        background-color: #f9f9f9;
        color: #333;
    }

    h2 {
        color: #013d86;
        border-bottom: 2px solid #013d86;
        padding-bottom: 5px;
    }

    p {
        margin: 10px 0;
    }

    strong {
        font-weight: bold;
        color: #013d86;
    }
    </style>
</head>

<body>

    <?php
    // Obter os parâmetros da URL
    $nome = urldecode($_GET['nome']);
    $gmail = urldecode($_GET['gmail']);
    $protocolo = urldecode($_GET['protocolo']);
    $contexto = urldecode($_GET['contexto']);
    $requerimento = urldecode($_GET['requerimento']);
    $departamento = urldecode($_GET['departamento']);
    $redirecionado = urldecode($_GET['redirecionado']);
    // Novas informações
    $dataEntrada = urldecode($_GET['dataEntrada']);
    $numeroProtocolo = urldecode($_GET['numeroProtocolo']);
    $matricula = urldecode($_GET['matricula']);
    $endereco = urldecode($_GET['endereco']);
    $telefone = urldecode($_GET['telefone']);
    $cargo = urldecode($_GET['cargo']);
    $unidadeTrabalho = urldecode($_GET['unidadeTrabalho']);
    $lotacao = urldecode($_GET['lotacao']);
    $vinculo = urldecode($_GET['vinculo']);
    $dataAdmissao = urldecode($_GET['dataAdmissao']);
    ?>

    <h2>Detalhes do Protocolo</h2>
    <p><strong>Nome:</strong> <?php echo $nome; ?></p>
    <p><strong>Gmail:</strong> <?php echo $gmail; ?></p>
    <p><strong>Protocolo:</strong> <?php echo $protocolo; ?></p>
    <p><strong>Contexto:</strong> <?php echo $contexto; ?></p>
    <p><strong>Requerimento:</strong> <?php echo $requerimento; ?></p>
    <p><strong>Departamento Atual:</strong> <?php echo $departamento; ?></p>
    <p><strong>Redirecionado:</strong> <?php echo $redirecionado; ?></p>
    <!-- Novas informações -->
    <p><strong>Data de Entrada:</strong> <?php echo $dataEntrada; ?></p>
    <p><strong>Número do Protocolo:</strong> <?php echo $numeroProtocolo; ?></p>
    <p><strong>Matrícula:</strong> <?php echo $matricula; ?></p>
    <p><strong>Endereço:</strong> <?php echo $endereco; ?></p>
    <p><strong>Telefone:</strong> <?php echo $telefone; ?></p>
    <p><strong>Cargo:</strong> <?php echo $cargo; ?></p>
    <p><strong>Unidade de Trabalho:</strong> <?php echo $unidadeTrabalho; ?></p>
    <p><strong>Lotação:</strong> <?php echo $lotacao; ?></p>
    <p><strong>Vínculo:</strong> <?php echo $vinculo; ?></p>
    <p><strong>Data de Admissão:</strong> <?php echo $dataAdmissao; ?></p>

    <form method="post">
        <button type="submit" id="concluirProtocoloBtn" name="concluirProtocolo"> Protocolo</button>
    </form>

</body>

</html>
<?php
// Se o botão "Concluir Protocolo" for pressionado
if (isset($_POST['concluirProtocolo'])) {
    // Conecte-se ao banco de dados (substitua os valores conforme necessário)
    require_once '../env/config.php';

    header('Content-Type: application/json');

    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        echo json_encode(array('success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error));
        exit();
    }
    // Atualize o estado para 1
    $sql = "UPDATE protocolo SET estado = 1 WHERE protocolo = '$protocolo'";

    if ($conn->query($sql) === TRUE) {
        echo "Estado atualizado com sucesso.";

        // Envie um e-mail se o estado for alterado para 1
        if ($_GET['estado'] == 0) {
            $to = $gmail;
            $subject = "Seu protocolo foi concluído";
            $message = "Caro $nome,\n\nSeu protocolo foi concluído com sucesso.\n\nAtenciosamente,\nSua Empresa";
            $headers = "From: seu_email@dominio.com"; // Substitua com o endereço de e-mail do remetente

            // Envie o e-mail
            mail($to, $subject, $message, $headers);
        }
    } else {
        echo "Erro ao atualizar o estado: " . $conn->error;
    }
    $conn->close();
}
?>