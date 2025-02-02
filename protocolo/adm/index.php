<?php
// session_start();

// // Verificar se o usuário está autenticado e se a sessão está configurada
// if (!isset($_SESSION["user_id"]) || !isset($_SESSION["departamento"])) {
//     header("Location: caminho_para_pagina_de_login.php");
//     exit();
// }

// // Verificar se o usuário tem permissão para acessar esta página
// $allowed_departamento = $_GET["dep"];
// if ($_SESSION["departamento"] !== $allowed_departamento) {
//     header("Location: caminho_para_pagina_de_permissao_negada.php");
//     exit();
// }

// // Se o botão de logout for clicado
// if (isset($_POST["logout"])) {
//     // Destruir a sessão e redirecionar para a página de login
//     session_destroy();
//     header("Location: caminho_para_pagina_de_login.php");
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua Página RH</title>
    <style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #013d86;
        /* Cor de fundo azul */
        display: flex;
    }

    .sidebar {
        width: 250px;
        background-color: #009640;
        /* Cor verde ajustada */
        padding-top: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: fixed;
        height: 100%;
    }

    .logo {
        width: 70px;
        /* Largura ajustada conforme sua preferência */
        height: auto;
        margin-bottom: 20px;
    }

    .sidebar a {
        padding: 15px;
        text-decoration: none;
        font-size: 18px;
        color: white;
        transition: 0.3s;
    }

    .sidebar a:hover {
        background-color: #007e34;
        /* Cor de fundo verde mais escura ao passar o mouse */
    }

    .content {
        margin-left: 250px;
        padding: 20px;
        flex: 1;
    }

    .line {
        width: 100%;
        height: 0px;
        background: #008748;
        /* Cor verde da linha (substitua pela cor desejada) */
        margin: 0 auto;
    }

    .protocol-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        /* Agora os protocolos ficarão um abaixo do outro */
        margin-top: 40px;
    }

    .protocol-box {
        width: 800px;
        /* Aumentei a largura para 700px */
        height: 98px;
        margin: 10px;
        border-radius: 10px;
        background: #FFF;
        /* Cor de fundo branca (substitua pela cor desejada) */
        position: relative;
        text-align: left;
        padding: 10px;
        display: flex;
        align-items: center;
    }

    .view-details-button {
        background-color: #009640;
        color: #fff;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .view-details-button:hover {
        background-color: #007e34;
        /* Cor de fundo verde mais escura ao passar o mouse */
    }

    .protocol-icon {
        width: 50px;
        height: auto;
        margin-right: 10px;
        /* Adicionei uma margem à direita para separar o ícone do texto */
    }

    .protocol-title {
        color: #000;
        /* Cor do texto */
        font-family: 'Siemreap', sans-serif;
        /* Fonte especificada */
        font-size: 18px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px;
    }

    .protocol-subtitle {
        color: rgba(0, 0, 0, 0.7);
        /* Cor do subtítulo com opacidade */
        font-family: 'Siemreap', sans-serif;
        /* Fonte especificada */
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 16px;
    }

    .select-container {
        margin-top: 5px;
        /* Ajustei a margem para separar o select do quadrado */
        margin-left: auto;
        /* Afastei a seleção para o canto esquerdo */
    }

    select {
        padding: 5px;
        /* Ajustei o padding do select */
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    .redirect-button {
        background-color: #009640;
        color: #fff;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
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
    </style>
</head>

<body>

    <div class="sidebar">
        <img class="logo" src="../assets/prefeitura-logo.png" alt="Logo da empresa">
        <form method="post" action="">
            <input type="submit" name="logout" value="Logout">
        </form>
        <a href="?dep=">Inicio</a>
        <a href="?dep=Secretária">Secretária</a>
        <a href="?dep=Assessoria_Técnica">Assessoria Técnica</a>
        <a href="?dep=Assessoria_Jurídica">Assessoria Jurídica</a>
        <a href="?dep=Patrimônio">Patrimônio</a>
        <a href="?dep=Junta_Médica">Junta Médica</a>
        <a href="?dep=Cipa">Cipa</a>
        <a href="?dep=Arquivo_Central">Arquivo Central</a>
        <a href="?dep=Recursos_Humanos">Recursos Humanos</a>
        <a href="?dep=Tecnologia_de_Informação">Tecnologia de Informação</a>
        <a href="?dep=Comunicação">Comunicação</a>
        <a href="?dep=Folha_de_Pagamento">Folha de Pagamento</a>
        <a href="?dep=Almoxarifado">Almoxarifado</a>
        <a href="?dep=Segurança_no_Trabalho">Segurança no Trabalho</a>
        <a href="?dep=Protocolo_Geral">Protocolo Geral</a>

    </div>

    <div class="content">
        <div class="line"></div>

        <div class="protocol-container" id="protocol-container">
            <!-- Protocolo será adicionado dinamicamente pelo script -->
        </div>

        <div id="feedback"></div>
    </div>

    <script>
    function populateSelect(select) {
        const departments = [
            "Secretária",
            "Assessoria Técnica",
            "Assessoria Jurídica",
            "Patrimônio",
            "Junta Médica",
            "Cipa",
            "Arquivo Central",
            "Recursos Humanos",
            "Tecnologia de Informação",
            "Comunicação",
            "Folha de Pagamento",
            "Almoxarifado",
            "Segurança no Trabalho",
            "Protocolo Geral"
        ];

        departments.forEach(department => {
            const option = document.createElement("option");
            option.value = department.replace(/\s/g, '_'); // Remover espaços e substituir por _
            option.text = department;
            select.add(option);
        });
    }
    // Função para adicionar dinamicamente os blocos de protocolo
    function addProtocolBox(nome, gmail, protocolo, contexto, requerimento, departamento_atual, redirecionado, data,
        dataEntrada, numeroProtocolo, matricula, endereco, telefone, cargo, unidadeTrabalho, lotacao, vinculo,
        dataAdmissao) {
        var partesData = data.split('/');
        var dataCriacao = new Date(partesData[2], partesData[1] - 1, partesData[0]);

        // Adicionar 15 dias ao objeto Date (prazo)
        var prazo = new Date(dataCriacao);
        prazo.setDate(prazo.getDate() + 15);

        // Dias da semana em português
        var diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira',
            'Sábado'
        ];

        // Dia da semana do prazo
        var diaSemanaPrazo = diasSemana[prazo.getDay()];

        // Formatar a data do prazo
        var diaPrazo = prazo.getDate();
        var mesPrazo = prazo.getMonth() + 1; // Os meses começam do zero
        var anoPrazo = prazo.getFullYear();

        // Formatando a data no padrão DD/MM/YYYY
        var dataFormatada = `${diaPrazo}/${mesPrazo}/${anoPrazo}`;
        const container = document.getElementById('protocol-container');

        const protocolBox = document.createElement('div');
        protocolBox.classList.add('protocol-box');

        const protocolIcon = document.createElement('img');
        protocolIcon.classList.add('protocol-icon');
        protocolIcon.src = '../assets/image9.png'; // Altere conforme necessário
        protocolIcon.alt = 'Ícone';

        const protocolInfo = document.createElement('div');

        const protocolTitle = document.createElement('div');
        protocolTitle.classList.add('protocol-title');
        protocolTitle.textContent = protocolo;

        const protocolSubtitle = document.createElement('div');
        protocolSubtitle.classList.add('protocol-subtitle');
        protocolSubtitle.textContent = contexto;

        // Elemento para o departamento atual
        const departamentoAtualElement = document.createElement('div');
        departamentoAtualElement.classList.add('protocol-subtitle');
        departamentoAtualElement.textContent = 'Departamento Atual: ' + departamento_atual + " - prazo: " +
            diaSemanaPrazo + "," + dataFormatada;

        protocolInfo.appendChild(protocolTitle);
        protocolInfo.appendChild(protocolSubtitle);
        protocolInfo.appendChild(departamentoAtualElement); // Adiciona o departamento atual

        const selectContainer = document.createElement('div');
        selectContainer.classList.add('select-container');

        const selectLabel = document.createElement('label');
        selectLabel.htmlFor = 'department-select-' + container.children.length;
        selectLabel.textContent = 'Departamento:';

        const select = document.createElement('select');
        select.id = 'department-select-' + container.children.length;
        select.classList.add('department-select');
        populateSelect(select);

        selectContainer.appendChild(selectLabel);
        selectContainer.appendChild(select);

        const redirectButton = document.createElement('button');
        redirectButton.classList.add('redirect-button');
        redirectButton.textContent = 'Redirecionar';
        redirectButton.addEventListener('click', function() {
            const selectedDepartment = select.value;
            updateDatabase(protocolo, contexto, selectedDepartment); // Chamada da função updateDatabase
            showFeedback(`Redirecionado com sucesso para ${selectedDepartment}`, 'success');
            console.log('Valor de protocolo antes da requisição:', protocolo); // Adicione esta linha
            updateDatabase(protocolo, contexto, selectedDepartment);
            showFeedback(`Redirecionado com sucesso para ${selectedDepartment}`, 'success');
            // protocolBox.style.display = 'none'; // Faz o protocolo desaparecer
        });

        const viewDetailsButton = document.createElement('button');
        viewDetailsButton.classList.add('view-details-button');
        viewDetailsButton.textContent = 'Ver';

        viewDetailsButton.addEventListener('click', function() {
            // Obter os valores diretamente do protocoloBox
            const nome = this.dataset.nome;
            const gmail = this.dataset.gmail;
            const protocolo = this.dataset.protocolo;
            const contexto = this.dataset.contexto;
            const requerimento = this.dataset.requerimento;
            const departamento_atual = this.dataset.departamento_atual;
            const redirecionado = this.dataset.redirecionado;

            // Adicionar constantes para as variáveis adicionais
            const data = this.dataset.data;
            const dataEntrada = this.dataset.dataEntrada;
            const numeroProtocolo = this.dataset.numeroProtocolo;
            const matricula = this.dataset.matricula;
            const endereco = this.dataset.endereco;
            const telefone = this.dataset.telefone;
            const cargo = this.dataset.cargo;
            const unidadeTrabalho = this.dataset.unidadeTrabalho;
            const lotacao = this.dataset.lotacao;
            const vinculo = this.dataset.vinculo;
            const dataAdmissao = this.dataset.dataAdmissao;

            // Redirecionar para a página de detalhes com os parâmetros
            window.location.href =
                `detalhes_protocolo.php?nome=${nome}&gmail=${gmail}&protocolo=${protocolo}&contexto=${contexto}&requerimento=${requerimento}&departamento=${departamento_atual}&redirecionado=${redirecionado}&data=${data}&dataEntrada=${dataEntrada}&numeroProtocolo=${numeroProtocolo}&matricula=${matricula}&endereco=${endereco}&telefone=${telefone}&cargo=${cargo}&unidadeTrabalho=${unidadeTrabalho}&lotacao=${lotacao}&vinculo=${vinculo}&dataAdmissao=${dataAdmissao}`;
        });

        viewDetailsButton.dataset.nome = nome;
        viewDetailsButton.dataset.gmail = gmail;
        viewDetailsButton.dataset.protocolo = protocolo;
        viewDetailsButton.dataset.contexto = contexto;
        viewDetailsButton.dataset.requerimento = requerimento;
        viewDetailsButton.dataset.departamento_atual = departamento_atual;
        viewDetailsButton.dataset.redirecionado = redirecionado;
        viewDetailsButton.dataset.data = data;
        viewDetailsButton.dataset.dataEntrada = dataEntrada;
        viewDetailsButton.dataset.numeroProtocolo = numeroProtocolo;
        viewDetailsButton.dataset.matricula = matricula;
        viewDetailsButton.dataset.endereco = endereco;
        viewDetailsButton.dataset.telefone = telefone;
        viewDetailsButton.dataset.cargo = cargo;
        viewDetailsButton.dataset.unidadeTrabalho = unidadeTrabalho;
        viewDetailsButton.dataset.lotacao = lotacao;
        viewDetailsButton.dataset.vinculo = vinculo;
        viewDetailsButton.dataset.dataAdmissao = dataAdmissao;

        protocolBox.appendChild(protocolIcon);
        protocolBox.appendChild(protocolInfo);
        protocolBox.appendChild(selectContainer);
        protocolBox.appendChild(redirectButton);
        protocolBox.appendChild(viewDetailsButton);

        container.appendChild(protocolBox);
    }


    function updateDatabase(protocolo, contexto, novoDepartamento) {
        // Realizar uma solicitação AJAX usando fetch ou XMLHttpRequest
        // Substitua a URL e o método conforme necessário
        fetch('atualizar_departamento.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    protocolo: protocolo,
                    contexto: contexto,
                    novoDepartamento: novoDepartamento,
                }),
            })
            .then(response => response.json())
            .then(data => {
                // Exibir feedback para o usuário
                showFeedback(data.message, data.success ? 'success' : 'error');
            })
            .catch(error => {
                console.error('Erro na atualização do banco de dados:', error);
            });
    }


    // Função para mostrar o feedback
    function showFeedback(message, type) {
        const feedbackDiv = document.getElementById('feedback');

        feedbackDiv.style.display = 'block';
        feedbackDiv.innerHTML = message;

        if (type === 'success') {
            feedbackDiv.style.backgroundColor = "#00A759";
        } else {
            feedbackDiv.style.backgroundColor = "#ED4141";
        }

        setTimeout(() => {
            feedbackDiv.style.display = 'none';
        }, 3000);
    }

    function redirectToDetailsPage(nome, gmail, protocolo, contexto, requerimento, departamento, redirecionado, data,
        dataEntrada, numeroProtocolo, matricula, endereco, telefone, cargo, unidadeTrabalho, lotacao, vinculo,
        dataAdmissao) {
        // Codificar as informações do protocolo para passar para a nova página
        const encodedNome = encodeURIComponent(nome);
        const encodedGmail = encodeURIComponent(gmail);
        const encodedProtocolo = encodeURIComponent(protocolo);
        const encodedContexto = encodeURIComponent(contexto);
        const encodedRequerimento = encodeURIComponent(requerimento);
        const encodedDepartamento = encodeURIComponent(departamento);
        const encodedRedirecionado = encodeURIComponent(redirecionado);
        const encodedData = encodeURIComponent(data);
        const encodedDataEntrada = encodeURIComponent(dataEntrada);
        const encodedNumeroProtocolo = encodeURIComponent(numeroProtocolo);
        const encodedMatricula = encodeURIComponent(matricula);
        const encodedEndereco = encodeURIComponent(endereco);
        const encodedTelefone = encodeURIComponent(telefone);
        const encodedCargo = encodeURIComponent(cargo);
        const encodedUnidadeTrabalho = encodeURIComponent(unidadeTrabalho);
        const encodedLotacao = encodeURIComponent(lotacao);
        const encodedVinculo = encodeURIComponent(vinculo);
        const encodedDataAdmissao = encodeURIComponent(dataAdmissao);

        // Construir a URL da página de detalhes com os parâmetros
        const detailsPageURL =
            `detalhes_protocolo.php?nome=${encodedNome}&gmail=${encodedGmail}&protocolo=${encodedProtocolo}&contexto=${encodedContexto}&requerimento=${encodedRequerimento}&departamento=${encodedDepartamento}&redirecionado=${encodedRedirecionado}&data=${encodedData}&dataEntrada=${encodedDataEntrada}&numeroProtocolo=${encodedNumeroProtocolo}&matricula=${encodedMatricula}&endereco=${encodedEndereco}&telefone=${encodedTelefone}&cargo=${encodedCargo}&unidadeTrabalho=${encodedUnidadeTrabalho}&lotacao=${encodedLotacao}&vinculo=${encodedVinculo}&dataAdmissao=${encodedDataAdmissao}`;

        // Redirecionar para a nova página
        window.location.href = detailsPageURL;
    }
    </script>

    <!-- Conteúdo da sua página aqui -->

</body>

</html>
<?php
// Configurações do banco de dados
require_once '../env/config.php';

header('Content-Type: application/json');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(array('success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error));
    exit();
}
// Verificar se há um parâmetro "dep" na URL
if (isset($_GET['dep']) && !empty($_GET['dep'])) {
    $departamentoFiltrado = $_GET['dep'];
    $sql = "SELECT protocolo, contexto, departamento_atual, requerimento, redirecionado, gmail, nome, data, 
                    numeroProtocolo, matricula, endereco, telefone, cargo, unidadeTrabalho, lotacao, vinculo, dataAdmissao 
            FROM protocolos 
            WHERE departamento_atual = '$departamentoFiltrado'";
} else {
    $sql = "SELECT protocolo, contexto, departamento_atual, requerimento, redirecionado, gmail, nome, data, 
                    numeroProtocolo, matricula, endereco, telefone, cargo, unidadeTrabalho, lotacao, vinculo, dataAdmissao 
            FROM protocolos";
}
$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

if ($result->num_rows > 0) {
    // Saída de dados de cada linha
    while ($row = $result->fetch_assoc()) {
        $protocolo = $row["protocolo"];
        $contexto = $row["contexto"];
        $departamento = $row["departamento_atual"];
        $requerimento = $row["requerimento"];
        $redirecionado = $row["redirecionado"];
        $gmail = $row["gmail"];
        $nome = $row["nome"];
        $data = $row["data"];
        $dataEntrada = $row["dataEntrada"];
        $numeroProtocolo = $row["numeroProtocolo"];
        $matricula = $row["matricula"];
        $endereco = $row["endereco"];
        $telefone = $row["telefone"];
        $cargo = $row["cargo"];
        $unidadeTrabalho = $row["unidadeTrabalho"];
        $lotacao = $row["lotacao"];
        $vinculo = $row["vinculo"];
        $dataAdmissao = $row["dataAdmissao"];

        // Adicionar o protocolo na página usando JavaScript
        echo "<script>addProtocolBox('$nome', '$gmail', '$protocolo', '$contexto', '$requerimento', '$departamento', '$redirecionado', '$data', '$dataEntrada', '$numeroProtocolo', '$matricula', '$endereco', '$telefone', '$cargo', '$unidadeTrabalho', '$lotacao', '$vinculo', '$dataAdmissao');</script>";
    }
} else {
    echo "0 resultados";
}

// Fechar a conexão com o banco de dados
$conn->close();
?>


<!-- Incorporar os dados no script JavaScript -->
<script>
var protocolosArray = <?php echo $protocolos_json; ?>;

protocolosArray.forEach(function(protocolo) {
    addProtocolBox(
        protocolo.protocolo,
        protocolo.contexto,
        protocolo.departamento,
        protocolo.requerimento,
        protocolo.redirecionado,
        protocolo.gmail,
        protocolo.nome,
        protocolo.data,
        protocolo.dataEntrada,
        protocolo.numeroProtocolo,
        protocolo.matricula,
        protocolo.endereco,
        protocolo.telefone,
        protocolo.cargo,
        protocolo.unidadeTrabalho,
        protocolo.lotacao,
        protocolo.vinculo,
        protocolo.dataAdmissao
    );
});
</script>