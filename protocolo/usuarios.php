<?php
// Dados de conexão com o banco de dados
$conn = new mysqli("srv1844.hstgr.io", "u492577848_Proto_estagio", "Kellys0n_123", "u492577848_protocolo");

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Array de usuários e senhas

$usuarios = array(
    "kellyson" => "kellyson123",

    "Secretaria" => "Secretaria123",
    "Assessoria_Técnica" => "Assessoria_Técnica123",
    "Assessoria_Jurídica" => "Assessoria_Jurídica123",
    "Patrimônio" => "Patrimônio123",
    "Junta_Médica" => "Junta_Médica123",
    "Cipa" => "Cipa123",
    "Arquivo_Central" => "Arquivo_Central123",
    "Recursos_Humanos" => "Recursos_Humanos123",
    "Tecnologia_de_Informação" => "Tecnologia_de_Informação123",
    "Comunicação" => "Comunicação123",
    "Folha_de_Pagamento" => "Folha_de_Pagamento123",
    "Almoxarifado" => "Almoxarifado123",
    "Segurança_no_Trabalho" => "Segurança_no_Trabalho123",
    "Protocolo_Geral" => "Protocolo_Geral123"
);




// Preparação da consulta SQL para inserir usuários no banco de dados
$stmt = $conn->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");

// Percorrer o array de usuários e senhas, inserindo cada um no banco de dados
foreach ($usuarios as $username => $password) {
    // Gerar o hash da senha
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Bind dos parâmetros e execução da consulta
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
}

// Fechar a conexão com o banco de dados
$stmt->close();
$conn->close();

echo "Usuários criados com sucesso!";
