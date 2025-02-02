<?php
// Dados de conexão
$host = "srv1844.hstgr.io";
$user = "u492577848_Proto_estagio";
$password = "Kellys0n_123";
$database = "u492577848_protocolo";

// Tenta estabelecer a conexão
try {
    $conn = new mysqli($host, $user, $password, $database);

    // Verifica se houve erro na conexão
    if ($conn->connect_error) {
        echo "❌ Falha na conexão: " . $conn->connect_error . "\n";
        echo "Código do erro: " . $conn->connect_errno . "\n";

        // Informações adicionais para debug
        echo "\nDetalhes da tentativa de conexão:\n";
        echo "Host: " . $host . "\n";
        echo "Usuário: " . $user . "\n";
        echo "Banco de dados: " . $database . "\n";
        echo "IP do cliente: " . $_SERVER['REMOTE_ADDR'] . "\n";
    } else {
        echo "✅ Conexão estabelecida com sucesso!\n";
        echo "Versão do servidor: " . $conn->server_info . "\n";
        echo "Versão do protocolo: " . $conn->protocol_version . "\n";

        // Tenta listar as tabelas para verificar as permissões
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "\nTabelas disponíveis:\n";
            while ($row = $result->fetch_array()) {
                echo "- " . $row[0] . "\n";
            }
        }

        $conn->close();
        echo "\nConexão fechada com sucesso.";
    }
} catch (Exception $e) {
    echo "❌ Erro capturado: " . $e->getMessage();
}