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
    } else {
        $error_message = "Nenhum resultado encontrado.";
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
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Protocolo</title>
    <link rel="icon" href="../assets/prefeitura-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
    body {
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
        background-image: url('./assets/background.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: #023047;
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        color: #333;
        font-size: 2em;
        margin-bottom: 20px;
    }

    ul {
        padding: 0;
    }

    li {
        list-style-type: none;
        padding: 10px 0;
        border-bottom: 1px solid #ccc;
    }

    li:last-child {
        border-bottom: none;
    }

    span {
        font-weight: bold;
        color: #666;
    }



    .button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #009640;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        margin-top: 20px;
        font-family: 'Inter', sans-serif;
        /* Mesma fonte para todos os botões */
        font-size: 14px;
        /* Mesmo tamanho de fonte para todos os botões */
        border: none;
        cursor: pointer;

    }

    .buttonprecensial {
        display: inline-block;
        padding: 10px 20px;
        background-color: red;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        margin-top: 20px;
        font-family: 'Inter', sans-serif;
        /* Mesma fonte para todos os botões */
        font-size: 14px;
        /* Mesmo tamanho de fonte para todos os botões */
        border: none;
        cursor: pointer;

    }

    .button:hover {
        background-color: #007a33;
    }

    .navbar {
        background-color: #009640;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        width: 70px;
        height: auto;
    }

    .navbar h1 {
        color: white;
        margin: 0;
    }


    select {
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        margin-right: 10px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        outline: none;
    }

    .tooltip {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: 200px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }

    .edit-icon {
        cursor: pointer;
        margin-left: 10px;
        color: #009640;
    }

    .editable-field {
        display: inline-block;
        padding: 2px 5px;
        border: 1px solid transparent;
        border-radius: 3px;
        transition: all 0.3s;
        position: relative;
    }

    .editable-field:hover::after {
        content: '\f044';
        font-family: 'Font Awesome 5 Free';
        position: absolute;
        right: -20px;
        top: 50%;
        transform: translateY(-50%);
        color: #009640;
        font-size: 14px;
        opacity: 0.7;
    }

    .editable-field:hover {
        background-color: #f0f0f0;
        border: 1px dashed #009640;
        padding-right: 25px;
        cursor: text;
    }

    .editable-field.editing {
        border: 1px solid #009640;
        background-color: #fff;
    }

    .editable-input {
        border: none;
        padding: 0;
        font-family: inherit;
        font-size: inherit;
        background: transparent;
        width: auto;
        min-width: 50px;
    }

    .editable-input:focus {
        outline: none;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 500px;
        text-align: center;
    }

    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }

    .close {
        float: right;
        cursor: pointer;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover {
        color: #009640;
    }

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
        margin: 0;
        background-color: #009640;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-voltar:hover {
        background-color: #007a33;
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

    .button-group {
        display: inline-flex;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
    }

    .button-group .button {
        margin-top: 0;
        margin-right: -1px;
        border-radius: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .button-group .button:first-child {
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }

    .button-group .button:last-child {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
        margin-right: 0;
    }

    .delete-button {
        display: inline-block;
        width: auto;
        margin-top: 1rem;
        margin-left: 0;
        /* Changed from auto to 0 */
    }

    .alert {
        padding: 12px 20px;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 4px;
        position: relative;
        padding-right: 35px;
        /* Space for close button */
    }

    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }

    .alert i {
        margin-right: 8px;
    }

    .tooltip-button {
        position: relative;
        display: inline-flex;
    }

    .tooltip-button:before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 5px 10px;
        background: #333;
        color: white;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 10;
    }

    .tooltip-button:hover:before {
        visibility: visible;
        opacity: 1;
    }

    .warning-text {
        color: #856404;
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
        font-size: 14px;
    }

    .warning-text i {
        margin-right: 8px;
        color: #856404;
    }

    /* Adicionar estes estilos para o spinner */
    .spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 8px;
        display: none;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .button:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Atualizar estilos do spinner */
    .spinner-container {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .spinner {
        width: 12px;
        height: 12px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        display: none;
        animation: spin 0.8s linear infinite;
        flex-shrink: 0;
        /* Impede que o spinner encolha */
    }

    .button[data-action="send-email"] {
        min-width: 120px;
        /* Garante largura mínima para evitar redimensionamento */
        color: #ffffff;
    }

    .button[data-action="send-email"] .button-text {
        color: #ffffff;
    }

    .button[data-action="send-email"] .fa-envelope {
        color: #ffffff;
    }

    /* Garante que o container mantenha o tamanho mesmo quando o spinner está visível */
    .button[data-action="send-email"][disabled] .spinner-container {
        min-width: 90px;
        /* Ajuste este valor conforme necessário */
        justify-content: center;
    }

    /* Estilos para o Upload de Arquivos */
    .upload-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .upload-title {
        color: #333;
        font-size: 1.5em;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #009640;
    }

    .file-input-wrapper {
        position: relative;
        margin-bottom: 20px;
    }

    .file-input-label {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: #fff;
        border: 2px dashed #009640;
        border-radius: 6px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-input-label:hover {
        background-color: #f0f9f4;
    }

    .file-input {
        display: none;
    }

    .upload-button {
        background-color: #009640;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .upload-button:hover {
        background-color: #007a33;
    }

    .file-list {
        margin-top: 20px;
    }

    .file-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px;
        border-bottom: 1px solid #eee;
    }

    .file-list-item:last-child {
        border-bottom: none;
    }

    .file-icon {
        color: #009640;
    }

    .file-link {
        color: #333;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .file-link:hover {
        color: #009640;
    }

    .alert .close-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        opacity: 0.5;
        transition: opacity 0.2s;
    }

    .alert .close-btn:hover {
        opacity: 1;
    }

    /* Adicionar estilos para protocolos excluídos */
    .excluded-protocol-banner {
        background-color: #dc3545;
        color: white;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .excluded-protocol-banner i {
        font-size: 24px;
    }

    .container.excluded {
        position: relative;
        opacity: 0.8;
    }

    .container.excluded::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(220, 53, 69, 0.05);
        pointer-events: none;
        border-radius: 8px;
    }

    .excluded .protocol-details li {
        color: #6c757d;
    }

    .excluded .button {
        background-color: #6c757d;
    }

    .excluded .button:hover {
        background-color: #5a6268;
    }

    .excluded .editable-field {
        pointer-events: none;
        color: #6c757d;
    }
    </style>

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
        const button = document.querySelector('[data-action="send-email"]');
        const spinnerContainer = button.querySelector('.spinner-container');
        const spinner = button.querySelector('.spinner');
        const icon = button.querySelector('.fa-envelope');
        const text = button.querySelector('.button-text');

        // Desabilita o botão e mostra o spinner
        button.disabled = true;
        spinner.style.display = 'inline-block';
        icon.style.display = 'none';
        text.textContent = 'Enviando...';

        const id = <?php echo $id; ?>;
        fetch('atualizar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    column: 'estado',
                    new_value: '1'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFeedback(MESSAGES.EMAIL_SUCCESS, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showFeedback(data.message || MESSAGES.EMAIL_ERROR, 'error');
                    // Restaura o botão ao estado original em caso de erro
                    button.disabled = false;
                    spinner.style.display = 'none';
                    icon.style.display = 'inline-block';
                    text.textContent = 'Concluir via E-mail';
                }
                closeEmailModal();
            })
            .catch(error => {
                console.error('Erro:', error);
                showFeedback(MESSAGES.EMAIL_ERROR, 'error');
                closeEmailModal();
                // Restaura o botão ao estado original em caso de erro
                button.disabled = false;
                spinner.style.display = 'none';
                icon.style.display = 'inline-block';
                text.textContent = 'Concluir via E-mail';
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
        } else {
            echo "<li>{$error_message}</li>";
        }
        ?>
    </ul>
    <h2>Arquivos na Pasta</h2>
    <ul class="file-list">
        <?php

        $directory = '../../media/' . $id;
        $path_to_files = '../../media/' . $id . '/';

        if (is_dir($directory) && !empty(scandir($directory))) {
            $files = scandir($directory);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    echo "<li><a class='file-link' href='$path_to_files/$file'>$file</a></li>";
                }
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
            <button onclick="executeEmail()" class="button" data-action="send-email"></button>
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