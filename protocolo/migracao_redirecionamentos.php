<?php
require_once 'env/config.php';

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Modificar a parte de criação da foreign key
try {
    // Primeiro verificar se a foreign key existe
    $checkFK = $conn->query("
        SELECT COUNT(1) constraint_exists
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_NAME = 'fk_protocolo'
        AND TABLE_NAME = 'protocolo_redirecionamentos'
    ");

    $constraintExists = $checkFK->fetch_assoc()['constraint_exists'];

    // Se existir, remover
    if ($constraintExists) {
        $conn->query("ALTER TABLE protocolo_redirecionamentos DROP FOREIGN KEY fk_protocolo");
    }

    // Criar nova foreign key
    $alterTable = "ALTER TABLE protocolo_redirecionamentos 
                   ADD CONSTRAINT fk_protocolo 
                   FOREIGN KEY (protocolo_id) 
                   REFERENCES protocolos(id)";
    $conn->query($alterTable);
} catch (Exception $e) {
    echo "Aviso ao configurar foreign key: " . $e->getMessage() . "\n";
}

// Limpar tabela antes de migrar
$conn->query("TRUNCATE TABLE protocolo_redirecionamentos");

// Buscar todos os protocolos
$sql = "SELECT id, redirecionado, departamento_atual FROM protocolos WHERE redirecionado IS NOT NULL";
$result = $conn->query($sql);

$total = $result->num_rows;
$migrados = 0;
$erros = 0;
$log = fopen("migracao_log.txt", "w");
fwrite($log, "Iniciando migração...\n\n");

// Adicionar mais detalhes ao log inicial
fwrite($log, "=== Detalhes da Execução ===\n");
fwrite($log, "Data/Hora: " . date('Y-m-d H:i:s') . "\n");
fwrite($log, "Total de protocolos encontrados: {$total}\n\n");

// Adicionar contador de redirecionamentos por protocolo
$redirecionamentosPorProtocolo = [];

// Preparar statement para inserção
$insertStmt = $conn->prepare("INSERT INTO protocolo_redirecionamentos 
    (protocolo_id, departamento_origem, departamento_destino, data_hora) 
    VALUES (?, ?, ?, STR_TO_DATE(?, '%d/%m/%Y'))");

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $redirecionado = trim($row['redirecionado']);
    $redirecionamentosPorProtocolo[$id] = 0;

    fwrite($log, "\n=== Processando protocolo {$id} ===\n");
    fwrite($log, "String de redirecionamento: {$redirecionado}\n");

    // Se tem apenas um redirecionamento, considerar que veio da secretaria
    if (strpos($redirecionado, '>') === false) {
        if (preg_match('/(.+)\s*\((\d{2}\/\d{2}\/\d{4})\)/', $redirecionado, $matches)) {
            $depDestino = trim($matches[1]);
            $data = $matches[2];
            $origem = 'Secretaria'; // Criar variável para referência

            // Passar variáveis por referência usando bind_param
            $insertStmt->bind_param(
                "ssss",
                $id,
                $origem,
                $depDestino,
                $data
            );

            if ($insertStmt->execute()) {
                $migrados++;
                $redirecionamentosPorProtocolo[$id]++;
                fwrite($log, "Migrado: Secretaria -> {$depDestino} em {$data}\n");
            } else {
                $erros++;
                fwrite($log, "ERRO ao migrar redirecionamento único: " . $insertStmt->error . "\n");
            }
        }
        continue;
    }

    // Para múltiplos redirecionamentos
    $redirecionamentos = explode('>', $redirecionado);

    // Se o primeiro redirecionamento não tem origem explícita, assumir Secretaria
    $primeiro = trim($redirecionamentos[0]);
    if (preg_match('/(.+)\s*\((\d{2}\/\d{2}\/\d{4})\)/', $primeiro, $matches)) {
        array_unshift($redirecionamentos, "Secretaria (" . $matches[2] . ")");
    }

    for ($i = 0; $i < count($redirecionamentos) - 1; $i++) {
        $atual = trim($redirecionamentos[$i]);
        $proximo = trim($redirecionamentos[$i + 1]);

        if (
            preg_match('/(.+)\s*\((\d{2}\/\d{2}\/\d{4})\)/', $atual, $matchesAtual) &&
            preg_match('/(.+)\s*\((\d{2}\/\d{2}\/\d{4})\)/', $proximo, $matchesProximo)
        ) {

            $depOrigem = trim($matchesAtual[1]);
            $depDestino = trim($matchesProximo[1]);
            $dataRedirecionamento = $matchesProximo[2];

            // Passar variáveis por referência
            $insertStmt->bind_param(
                "ssss",
                $id,
                $depOrigem,
                $depDestino,
                $dataRedirecionamento
            );

            if ($insertStmt->execute()) {
                $migrados++;
                $redirecionamentosPorProtocolo[$id]++;
                fwrite($log, "Migrado: {$depOrigem} -> {$depDestino} em {$dataRedirecionamento}\n");
            } else {
                $erros++;
                fwrite($log, "ERRO ao migrar: " . $insertStmt->error . "\n");
            }
        }
    }
}

fwrite($log, "\nResumo da migração:\n");
fwrite($log, "Total de protocolos: {$total}\n");
fwrite($log, "Redirecionamentos migrados: {$migrados}\n");
fwrite($log, "Erros: {$erros}\n");

// Adicionar estatísticas detalhadas ao final do log
fwrite($log, "\n=== Estatísticas Detalhadas ===\n");
fwrite($log, "Total de protocolos processados: {$total}\n");
fwrite($log, "Total de redirecionamentos migrados: {$migrados}\n");
fwrite($log, "Média de redirecionamentos por protocolo: " . ($total > 0 ? round($migrados / $total, 2) : 0) . "\n\n");

fwrite($log, "Redirecionamentos por protocolo:\n");
foreach ($redirecionamentosPorProtocolo as $protocoloId => $quantidade) {
    if ($quantidade > 0) {
        fwrite($log, "Protocolo {$protocoloId}: {$quantidade} redirecionamentos\n");
    }
}

fclose($log);
$insertStmt->close();
$conn->close();

echo "Migração concluída! Total: {$total}, Migrados: {$migrados}, Erros: {$erros}. Verifique o arquivo migracao_log.txt para detalhes.";
