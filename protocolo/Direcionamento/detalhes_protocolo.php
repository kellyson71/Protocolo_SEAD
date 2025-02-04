<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../");
    exit;
}

// Adiciona verificação de segurança para hideAlert
if (isset($_POST['hideAlert'])) {
    $alertType = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['hideAlert']); // Sanitiza a entrada
    $_SESSION['hide_' . $alertType . '_alert'] = true;
    exit(json_encode(['success' => true]));
}

require_once '../env/config.php';

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro de conexão: ' . $conn->connect_error
    ]));
}
// Obter o ID do protocolo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar se o ID é válido
if ($id > 0) {
    // Melhora a segurança da consulta SQL usando prepared statement
    $sql = "SELECT * FROM protocolos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $estado = $row['estado'];

        // Consulta dos redirecionamentos
        $sqlRedir = "SELECT 
            departamento_origem,
            departamento_destino,
            DATE_FORMAT(data_hora, '%d/%m/%Y') as data
            FROM protocolo_redirecionamentos 
            WHERE protocolo_id = ?
            ORDER BY data_hora ASC";

        $stmtRedir = $conn->prepare($sqlRedir);
        $stmtRedir->bind_param("s", $id);
        $stmtRedir->execute();
        $redirecionamentos = $stmtRedir->get_result()->fetch_all(MYSQLI_ASSOC);

        // Formatar histórico de redirecionamentos
        $historicoHtml = '';
        if (!empty($redirecionamentos)) {
            $historicoHtml = '<li><span>Histórico de Redirecionamentos:</span><div class="timeline">';
            foreach ($redirecionamentos as $r) {
                $historicoHtml .= sprintf(
                    '<div class="timeline-item">
                        <div class="timeline-date">%s</div>
                        <div class="timeline-content">
                            <span class="origin">%s</span>
                            <i class="fas fa-arrow-right"></i>
                            <span class="destination">%s</span>
                        </div>
                    </div>',
                    $r['data'],
                    formatDepartamento($r['departamento_origem']),
                    formatDepartamento($r['departamento_destino'])
                );
            }
            $historicoHtml .= '</div></li>';
        }
    }
    $stmt->close();
} else {
    $error_message = "ID inválido.";
}

$conn->close();

// Função auxiliar para validar diretório
function validateDirectory($path)
{
    if (!is_dir($path)) {
        if (!mkdir($path, 0777, true)) {
            throw new Exception('Não foi possível criar o diretório');
        }
    }
    return $path;
}

// Melhorar o tratamento de upload de arquivos
if (isset($_POST['submit']) && isset($_FILES['file'])) {
    try {
        $upload_dir = validateDirectory('./media/' . $id . '/');

        $file = $_FILES['file'];
        $file_name = basename($file['name']);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validaç��es em constantes para melhor manutenção
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $max_size = 5000000; // 5MB

        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception("Tipo de arquivo não permitido. Use: " . implode(', ', $allowed_types));
        }

        if ($file['size'] > $max_size) {
            throw new Exception("Arquivo muito grande. Tamanho máximo: 5MB");
        }

        $target_file = $upload_dir . $file_name;
        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            throw new Exception("Erro ao enviar arquivo");
        }

        $upload_message = ['type' => 'success', 'message' => 'Arquivo enviado com sucesso!'];
    } catch (Exception $e) {
        $upload_message = ['type' => 'danger', 'message' => $e->getMessage()];
    }
}

// Melhorar a listagem de arquivos com tratamento de erros
function listFiles($directory)
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = scandir($directory);
    return array_filter($files, function ($file) {
        return !in_array($file, ['.', '..']);
    });
}

// Adicionar função auxiliar para formatar nome do departamento
function formatDepartamento($dep)
{
    return ucwords(str_replace('_', ' ', $dep));
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Protocolo</title>
    <link rel="icon" href="../assets/prefeitura-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/detalhes.css">



    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .confirm-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .confirm-popup p {
            margin-bottom: 10px;
        }

        .confirm-popup button {
            padding: 10px 20px;
            background-color: #009640;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .confirm-popup button:hover {
            background-color: #007a33;
        }

        .btn-voltar {
            margin-top: 0px;
            /* Ajuste a margem conforme necessário */
            background-color: #006a33;

        }

        #feedback {
            display: none;
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            text-align: center;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            h1 {
                font-size: 1.5em;
                /* Ajuste o tamanho da fonte conforme necessário */
            }
        }
    </style>

    <script>
        const MESSAGES = {
            UPDATE_SUCCESS: 'Campo atualizado com sucesso!',
            UPDATE_ERROR: 'Erro ao atualizar o campo',
            DELETE_ERROR: 'Erro ao excluir o protocolo',
            CONCLUDE_SUCCESS: 'Protocolo marcado como concluído com sucesso!',
            CONCLUDE_ERROR: 'Erro ao concluir protocolo',
            EMAIL_SUCCESS: 'Protocolo concluído e e-mail enviado com sucesso!',
            EMAIL_ERROR: 'Erro ao processar a solicitação'
        };

        function confirmarConclusao() {
            if (confirm("Você tem certeza que deseja concluir este protocolo?")) {
                document.getElementById("form_concluir_protocolo").submit();
            }
        }
        var Secretaria = "Secretaria";

        function showFeedback(message, type, timeout = 3000) {
            const feedbackDiv = document.getElementById('feedback');
            if (!feedbackDiv) return;

            feedbackDiv.style.display = 'block';
            feedbackDiv.innerHTML = message;
            feedbackDiv.style.backgroundColor = type === 'success' ? "#00A759" : "#ED4141";

            setTimeout(() => {
                feedbackDiv.style.display = 'none';
            }, timeout);
        }

        function updateDatabase(id) {
            fetch('concluir_presencial.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        novoDepartamento: "Secretaria",
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showFeedback(data.message, 'success');
                        const cardProtocolo = document.getElementById(`protocolo-${id}`);
                        if (cardProtocolo) {
                            cardProtocolo.parentNode.removeChild(cardProtocolo);
                        }
                    } else {
                        showFeedback(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro na atualização do banco de dados:', error);
                });
        }

        function voltar() {
            var urlParams = new URLSearchParams(window.location.search);
            var dep = urlParams.get('dep');
            window.location.href = dep ? './index.php?dep=' + dep : '../';
        }

        function makeEditable(element, field) {
            if (element.classList.contains('editing')) return;

            const currentValue = element.textContent;
            element.classList.add('editing');

            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentValue;
            input.className = 'editable-input';

            input.onblur = function() {
                saveInlineEdit(element, field, input.value);
            };

            input.onkeydown = function(e) {
                if (e.key === 'Enter') {
                    input.blur();
                } else if (e.key === 'Escape') {
                    element.classList.remove('editing');
                    element.textContent = currentValue;
                }
            };

            element.textContent = '';
            element.appendChild(input);
            input.focus();
        }

        function saveInlineEdit(element, field, value) {
            const id = <?php echo $id; ?>;

            fetch('update_field.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        field: field,
                        value: value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    element.classList.remove('editing');
                    if (data.success) {
                        element.textContent = value;
                        showFeedback(MESSAGES.UPDATE_SUCCESS, 'success');
                    } else {
                        element.textContent = element.getAttribute('data-original');
                        showFeedback(MESSAGES.UPDATE_ERROR, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    element.classList.remove('editing');
                    element.textContent = element.getAttribute('data-original');
                    showFeedback(MESSAGES.UPDATE_ERROR, 'error');
                });
        }

        function confirmarExclusao(id) {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'block';
            window.protocoloId = id; // Store the ID for later use
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'none';
        }

        function executeDelete() {
            const id = window.protocoloId;

            fetch('ocultar_protocolo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showFeedback(data.message, 'success');
                        setTimeout(() => {
                            const urlParams = new URLSearchParams(window.location.search);
                            const dep = urlParams.get('dep');
                            window.location.href = dep ? './index.php?dep=' + dep : '../';
                        }, 2000);
                    } else {
                        showFeedback(MESSAGES.DELETE_ERROR, 'error');
                    }
                    closeDeleteModal();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showFeedback(MESSAGES.DELETE_ERROR, 'error');
                    closeDeleteModal();
                });
        }

        // Add this to close modal when clicking outside
        window.onclick = function(event) {
            const deleteModal = document.getElementById('deleteModal');
            const concludeModal = document.getElementById('concludeModal');
            const emailModal = document.getElementById('emailModal');

            if (event.target == deleteModal) {
                closeDeleteModal();
            }
            if (event.target == concludeModal) {
                closeConcludeModal();
            }
            if (event.target == emailModal) {
                closeEmailModal();
            }
        }

        function confirmarConcluido(id) {
            const modal = document.getElementById('concludeModal');
            modal.style.display = 'block';
            window.protocoloId = id;
        }

        function closeConcludeModal() {
            const modal = document.getElementById('concludeModal');
            modal.style.display = 'none';
        }

        function executeConclude() {
            const id = window.protocoloId;
            fetch('marcar_concluido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showFeedback(MESSAGES.CONCLUDE_SUCCESS, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showFeedback(data.message || MESSAGES.CONCLUDE_ERROR, 'error');
                    }
                    closeConcludeModal();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showFeedback(MESSAGES.CONCLUDE_ERROR, 'error');
                    closeConcludeModal();
                });
        }

        function confirmarEmail() {
            const modal = document.getElementById('emailModal');
            modal.style.display = 'block';
        }

        function closeEmailModal() {
            const modal = document.getElementById('emailModal');
            modal.style.display = 'none';
        }

        function executeEmail() {
            console.log('Iniciando processo...'); // Debug

            const button = document.querySelector('.modal-buttons .button');
            const spinnerContainer = button.querySelector('.spinner-container');
            const spinner = button.querySelector('.spinner');
            const icon = button.querySelector('.fa-envelope');
            const text = button.querySelector('.button-text');

            // Desabilita o botão e mostra o spinner
            button.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (icon) icon.style.display = 'none';
            if (text) text.textContent = 'Enviando...';

            const id = <?php echo $id; ?>;

            // Envia diretamente para enviar_email.php que vai fazer tudo
            fetch('enviar_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showFeedback(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Erro ao processar solicitação');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showFeedback(error.message || 'Erro ao processar a solicitação', 'error');
                    // Restaura o botão
                    button.disabled = false;
                    if (spinner) spinner.style.display = 'none';
                    if (icon) icon.style.display = 'inline-block';
                    if (text) text.textContent = 'Confirmar';
                })
                .finally(() => {
                    closeEmailModal();
                });
        }

        function hideAlert(type) {
            const alert = document.getElementById(type + '-alert');
            if (alert) {
                alert.style.display = 'none';

                // Save the preference to server
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'hideAlert=' + type
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    </script>

</head>

<body></body>
<div class="navbar">
    <button onclick="voltar()" class="button btn-voltar tooltip-button" data-tooltip="Voltar para a página anterior">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </button>
    <h1>protocolo nº <?php echo htmlspecialchars($_GET['id'] ?? 'Todos'); ?></h1>
    <img class="logo" src="../assets/prefeitura-logo.png" alt="Logo da empresa">
</div>
<div id="feedback"></div>

<div class="container <?php echo ($estado == 3) ? 'excluded' : ''; ?>">
    <?php if ($estado == 3): ?>
        <div class="excluded-protocol-banner">
            <i class="fas fa-trash-alt"></i>
            <div>
                <strong>Protocolo Excluído</strong>
                <p style="margin: 0;">Este protocolo foi marcado como excluído e não pode mais ser modificado.</p>
            </div>
        </div>
    <?php endif; ?>
    <h1>Detalhes do Protocolo</h1>
    <ul class="protocol-details">
        <?php
        if (isset($row)) {
            foreach ($row as $key => $value) {
                if (!in_array($key, ['id', 'estado', 'dataEntrada', 'lido'])) {
                    $formatted_key = ucfirst($key);
                    $display_value = isset($value) ? ucfirst(htmlspecialchars($value)) : '';
                    echo "<li>
                                <span>{$formatted_key}:</span> 
                                <span class='editable-field' onclick='makeEditable(this, \"{$key}\")' id='value-{$key}' title='Clique para editar'>{$display_value}</span>
                              </li>";
                }
            }
            // Adicionar histórico de redirecionamentos se existir
            if (!empty($historicoHtml)) {
                echo $historicoHtml;
            }
        } else {
            echo "<li>{$error_message}</li>";
        }
        ?>
    </ul>
    <h2>Arquivos na Pasta</h2>
    <ul class="file-list">
        <?php
        error_reporting(0); // Desabilita avisos de erro
        $directory = '../../media/' . $id;
        $path_to_files = '../../media/' . $id . '/';

        if (is_dir($directory)) {
            $files = scandir($directory);
            if (count($files) > 2) { // mais que . e ..
                foreach ($files as $file) {
                    if ($file != "." && $file != "..") {
                        $file_path = $path_to_files . $file;
                        echo "<li class='file-item'>";
                        if (file_exists($file_path)) {
                            echo "<a class='file-link' href='" . htmlspecialchars($path_to_files . rawurlencode($file)) . "' target='_blank'>
                                    <i class='fas fa-file file-icon'></i>
                                    " . htmlspecialchars($file) . "
                                </a>";
                        } else {
                            echo "<span class='file-unavailable'>
                                    <i class='fas fa-file-excel file-icon'></i>
                                    " . htmlspecialchars($file) . "
                                    <span class='file-error'>Arquivo não encontrado</span>
                                </span>";
                        }
                        echo "</li>";
                    }
                }
            } else {
                echo "<li>Sem documentos</li>";
            }
        } else {
            echo "<li>Sem documentos</li>";
        }
        ?>
    </ul>
</div>

<?php if ($estado != 3): ?>
    <div class="container">
        <h1>Alterar Protocolos</h1>
        <?php if (!isset($_SESSION['hide_edit_alert'])): ?>
            <div class="alert alert-info" role="alert" id="edit-alert">
                <i class="fas fa-info-circle"></i>
                Para editar qualquer informação do protocolo, basta clicar sobre o campo desejado e fazer a alteração.
                <span class="close-btn" onclick="hideAlert('edit')">&times;</span>
            </div>
        <?php endif; ?>
        <hr>
        <div class="button-group">
            <a href="form.php?id=<?php echo $id; ?>" target="_blank" class="button tooltip-button"
                data-tooltip="Gerar documento para impressão"
                onclick="window.open(this.href, '_blank', 'width=800,height=600'); return false;">
                <i class="fas fa-print"></i>
                Imprimir Doc
            </a>
            <?php if ($estado == 0): ?>
                <button type="button" onclick="confirmarConcluido(<?php echo $id; ?>)" class="button tooltip-button"
                    data-tooltip="Marcar este protocolo como finalizado">
                    <i class="fas fa-check-circle"></i>
                    Marcar como concluído
                </button>
                <button type="button" onclick="confirmarEmail()" class="button tooltip-button"
                    data-tooltip="Finalizar e enviar notificação por e-mail">
                    <i class="fas fa-envelope"></i>
                    Concluir via E-mail
                </button>
            <?php endif; ?>
        </div>

        <div>
            <button type="button" onclick="confirmarExclusao(<?php echo $id; ?>)"
                class="buttonprecensial delete-button tooltip-button" data-tooltip="Remover permanentemente este protocolo">
                <i class="fas fa-trash-alt"></i>
                Excluir
            </button>
        </div>
    </div>
<?php endif; ?>

<?php if ($estado != 3): ?>
    <div class="container">
        <div class="upload-section">
            <h2 class="upload-title">Upload de Arquivos</h2>
            <?php if (!isset($_SESSION['hide_upload_alert'])): ?>
                <div class="alert alert-info" role="alert" id="upload-alert">
                    <i class="fas fa-info-circle"></i>
                    Os arquivos enviados aqui são de uso interno do sistema e ficarão disponíveis apenas para os administradores
                    que têm acesso a este protocolo. O requerente não terá acesso a estes documentos.
                    <span class="close-btn" onclick="hideAlert('upload')">&times;</span>
                </div>
            <?php endif; ?>
            <?php
            if (isset($_POST['submit']) && isset($_FILES['file'])) {
                $upload_dir = './media/' . $id . '/';

                // Cria o diretório se não existir
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file = $_FILES['file'];
                $file_name = basename($file['name']);
                $target_file = $upload_dir . $file_name;

                // Verifica se é um arquivo válido
                $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_extension, $allowed_types)) {
                    echo "<div class='alert alert-danger'>Tipo de arquivo não permitido. Use: PDF, DOC, DOCX, JPG, JPEG ou PNG.</div>";
                } else if ($file['size'] > 5000000) { // 5MB limit
                    echo "<div class='alert alert-danger'>Arquivo muito grande. Tamanho máximo: 5MB.</div>";
                } else if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    echo "<div class='alert alert-success'>Arquivo enviado com sucesso!</div>";
                } else {
                    echo "<div class='alert alert-danger'>Erro ao enviar arquivo. Tente novamente.</div>";
                }
            }
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="file-input-wrapper">
                    <label class="file-input-label" for="file">
                        <i class="fas fa-cloud-upload-alt fa-lg" style="color: #009640;"></i>
                        <span>Selecione um arquivo ou arraste aqui</span>
                        <input type="file" name="file" id="file" class="file-input" required>
                    </label>
                </div>
                <button type="submit" name="submit" class="upload-button">
                    <i class="fas fa-upload"></i>
                    Enviar Arquivo
                </button>
            </form>

            <?php if (!empty($id)): ?>
                <h2 class="upload-title">Arquivos do protocolo Nº<?php echo $id; ?></h2>
                <div class="file-list">
                    <?php
                    $directory = './media/' . $id;
                    $web_directory = './media/' . $id . '/';

                    if (is_dir($directory)) {
                        $files = scandir($directory);
                        if (count($files) > 2) { // mais que . e ..
                            foreach ($files as $file) {
                                if ($file != "." && $file != "..") {
                                    $file_url = $web_directory . rawurlencode($file);
                                    echo "<div class='file-list-item'>
                                        <i class='fas fa-file file-icon'></i>
                                        <a href='{$file_url}' target='_blank' class='file-link'>" . htmlspecialchars($file) . "</a>
                                      </div>";
                                }
                            }
                        } else {
                            echo "<p>Sem documentos</p>";
                        }
                    } else {
                        echo "<p>Sem documentos</p>";
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2>Confirmar Exclusão</h2>
        <p>Tem certeza que deseja excluir este protocolo? Esta ação não pode ser desfeita.</p>
        <div class="modal-buttons">
            <button onclick="executeDelete()" class="button">Confirmar</button>
            <button onclick="closeDeleteModal()" class="buttonprecensial">Cancelar</button>
        </div>
    </div>
</div>

<div id="concludeModal" class="modal">
    <div class="modal-content">
        <h2>Confirmar Conclusão</h2>
        <p>Tem certeza que deseja marcar este protocolo como concluído?</p>
        <p class="warning-text"><i class="fas fa-exclamation-triangle"></i> Observação: O usuário não receberá
            notificação por e-mail desta conclusão.</p>
        <div class="modal-buttons"></div>
        <button onclick="executeConclude()" class="button">Confirmar</button>
        <button onclick="closeConcludeModal()" class="buttonprecensial">Cancelar</button>
    </div>
</div>
</div>

<div id="emailModal" class="modal">
    <div class="modal-content">
        <h2>Confirmar Envio de E-mail</h2>
        <p>Tem certeza que deseja concluir este protocolo e enviar notificação por e-mail?</p>
        <div class="modal-buttons">
            <button onclick="executeEmail()" class="button">
                <span class="spinner-container">
                    <div class="spinner"></div>
                    <i class="fas fa-envelope"></i>
                    <span class="button-text">Confirmar</span>
                </span>
            </button>
            <button onclick="closeEmailModal()" class="buttonprecensial">Cancelar</button>
        </div>
    </div>
</div>
</body>

</html>
</div>