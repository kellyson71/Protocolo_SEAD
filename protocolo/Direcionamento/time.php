<?php
require_once '../env/config.php';

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Consulta para obter os protocolos
$sql = "SELECT * FROM protocolos";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Data do protocolo
        $data_protocolo = DateTime::createFromFormat('d/m/Y', $row['data']);

        if ($data_protocolo === false) {
            // Tratamento de erro se a data não puder ser interpretada
            echo "Erro ao processar a data do protocolo #" . $row['id'] . "<br>";
            continue;
        }

        // Prazo de 15 dias
        $prazo = new DateInterval('P15D');

        // Calcula a data de vencimento
        $data_vencimento = $data_protocolo->add($prazo);

        // Data atual
        $data_atual = new DateTime();

        // Calcula a diferença em dias
        $diferenca = $data_atual->diff($data_vencimento);

        // Exibe o tempo restante
        echo "Protocolo #" . $row['id'] . ": " . $diferenca->format('%a dias') . " restantes.<br>";

        // Adiciona a constante do prazo
        $const_prazo = $prazo->format('%a');
        echo "O prazo para o protocolo #" . $row['id'] . " é de " . $const_prazo . " dias.<br>";
    }
} else {
    echo "Nenhum protocolo encontrado.";
}

// Fecha a conexão
$conn->close();
?>