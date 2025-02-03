<?php
// ===== Configurações Globais =====
define('FEEDBACK_TIMEOUT', 3000);
define('PROTOCOLS_PER_PAGE', 10);
define('MAX_PAGINATION_LINKS', 5);

// Adicionar novas configurações de feedback
$feedbackConfig = [
    'success' => [
        'bgColor' => '#00A759',
        'icon' => '✓'
    ],
    'error' => [
        'bgColor' => '#ED4141',
        'icon' => '✕'
    ],
    'warning' => [
        'bgColor' => '#FFA500',
        'icon' => '⚠'
    ],
    'loading' => [
        'bgColor' => '#008435',
        'icon' => '↻'
    ]
];

// ===== Imports/Requires =====
session_start();
require_once '../env/config.php';

// ===== Configurações de Estado =====
$protocolStates = [
    0 => 'pendentes',
    1 => 'concluídos',
    2 => 'a concluir'
];

// ===== Handlers de Sessão =====
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = array();
    session_destroy();
    header("Location: ../");
    exit();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["dep"] !== $_GET["dep"]) {
    header("Location: ../");
    exit();
}

// ===== Conexão com Banco de Dados =====
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro de conexão: ' . $conn->connect_error
    ]));
}

// ===== Funções Utilitárias =====
function isUserAdmin($conn, $username)
{
    $stmt = $conn->prepare("SELECT is_admin FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($row = $result->fetch_assoc()) ? $row['is_admin'] == 1 : false;
}

// ===== Carregar Departamentos =====
$departments = [];
$sql = "SELECT DISTINCT username FROM usuarios";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row["username"];
    }
} else {
    die("Nenhum departamento encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Protocolos</title>
</head>

<body>
    <script src="https://cdn.tailwindcss.com"></script>



    <script>
        function randomDelay() {
            return 0;
        }
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.querySelector('.content').style.display = 'block';
                document.getElementById('skeleton-placeholder').style.display = 'none';
            }, randomDelay());
        });
    </script>


    </head>

    <body>
        <!-- Modificar a navbar -->
        <div class="bg-[#009640] shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center gap-4">
                        <img class="h-10 w-auto" src="../assets/prefeitura-logo.png" alt="Logo prefeitura">
                        <h1 class="text-xl font-semibold text-white hide-on-mobile">ProtocoloSEAD</h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <h2 class="text-lg text-white">
                            <?php
                            $depName = $_GET["dep"] ?? "Todos";
                            echo htmlspecialchars(ucfirst(str_replace('_', ' ', $depName)));
                            ?>
                        </h2>

                        <?php if (isUserAdmin($conn, $_GET["dep"])): ?>
                            <button
                                onclick="window.location.href = '../register/index.html?dep=<?php echo $_GET['dep']; ?>';"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-[#009640] bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009640] transition-colors duration-200">
                                Gerenciar Usuários
                            </button>
                        <?php endif; ?>

                        <button onclick="window.location.href = '?action=logout';"
                            class="inline-flex items-center px-4 py-2 border-2 border-white text-sm font-medium rounded-md text-white hover:bg-[#008435] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                            Sair
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: center;">

            <div class="max-w-lg mx-auto mt-4">
                <div class="flex">
                    <select id="searchField"
                        class="flex-shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-gray-900 bg-gray-100 border border-gray-300 rounded-s-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100">
                        <option value="" disabled selected>Tipo de pesquisa</option>
                        <option value="protocol-title">Título</option>
                        <option value="protocol-subtitle">ID</option>
                        <option value="protocol-name">Nome</option>
                        <option value="protocol-gmail">Gmail</option>
                        <option value="protocol-date">Data</option>
                    </select>

                    <div class="relative w-full">
                        <input type="search" id="searchInput"
                            class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-e-lg border-s-gray-50 border-s-2 border border-gray-300 focus:ring-green-500 focus:border-green-500"
                            placeholder="Pesquisar protocolos..." />
                        <button onclick="searchProtocols()"
                            class="absolute top-0 end-0 p-2.5 text-sm font-medium h-full text-white bg-green-600 rounded-e-lg border border-green-700 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                            <span class="sr-only">Search</span>
                        </button>
                    </div>
                </div>
                <div id="searchAlert" class="mt-2 text-red-500 text-sm hidden"></div>
            </div>

            <div class="filter-buttons">

                <button class="filter-button" onclick="filterProtocols(0)">Pendentes</button>
                <button class="filter-button" onclick="filterProtocols(1)">Concluídos</button>
                <?php if (isUserAdmin($conn, $_GET["dep"])): ?>
                    <button class="filter-button" onclick="filterProtocols(2)">A Concluir</button>
                <?php endif; ?>
                <button class="filter-button" onclick="filterProtocols()">Mostrar Todos</button>


            </div>

            <div class="skeleton-container">
                <div id="skeleton-placeholder" class="protocol-container" id="protocol-container">
                    <!-- Skeleton loader -->
                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>

                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>

                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>
                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>
                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>
                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>
                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>
                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>
                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>
                    <div class="protocol-box animate-pulse">
                        <div class="protocol-icon bg-gray-300 rounded-full w-12 h-12"></div>
                        <div class="ml-4 space-y-3">
                            <div class="h-5 bg-gray-300 rounded w-1/2"></div>
                            <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="searchAlert" class="mt-2 text-red-500 hidden"></div>
        </div>
        <div class="content">
            <div class="protocol-container" id="protocol-container">
                <div id="no-protocols-message" style="display:none; text-align:center; font-size: 18px; color: red;">
                </div>

            </div>
            <div id="feedback"></div>

        </div>

        <!-- Adicionar elementos de feedback no HTML -->
        <div id="feedbackToast" class="feedback-toast"></div>
        <div id="loadingOverlay" class="loading-overlay">
            <div class="loading-spinner"></div>
        </div>
        <div id="modalBackdrop" class="modal-backdrop"></div>
        <div id="confirmationModal" class="confirmation-modal">
            <h3 class="text-xl mb-4">Confirmar Ação</h3>
            <p id="confirmationMessage" class="mb-4"></p>
            <div class="flex justify-end gap-3">
                <button id="cancelAction" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
                <button id="confirmAction"
                    class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded">Confirmar</button>
            </div>
        </div>

        <script>
            const FEEDBACK_TIMEOUT = 3000;
            const PROTOCOLS_PER_PAGE = 10;

            function searchProtocols() {
                const searchText = document.getElementById('searchInput').value.toLowerCase();
                const searchFieldElement = document.getElementById('searchField');
                const searchField = searchFieldElement.value;

                if (!searchField) {
                    showSearchAlert("Por favor, selecione um tipo de pesquisa.");
                    return;
                } else {
                    hideSearchAlert();
                }

                // Add search params to URL
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('search', searchText);
                urlParams.set('searchField', searchField);
                urlParams.delete('page'); // Reset to first page when searching

                // Update the URL correctly
                window.location.href = '?' + urlParams.toString();
            }

            function showSearchAlert(message) {
                const alertDiv = document.getElementById('searchAlert');
                alertDiv.textContent = message;
                alertDiv.classList.remove('hidden');
            }

            function hideSearchAlert() {
                const alertDiv = document.getElementById('searchAlert');
                alertDiv.textContent = '';
                alertDiv.classList.add('hidden');
            }

            // Change 'keydown' to 'keyup' in the event listener
            document.getElementById('searchInput').addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    searchProtocols();
                }
            });

            document.getElementById('searchField').addEventListener('change', function() {
                const searchInput = document.getElementById('searchInput');
                if (this.value === 'protocol-date') {
                    // Inicializar Flatpickr para datas
                    flatpickr(searchInput, {
                        dateFormat: "d/m/Y",
                        locale: {
                            firstDayOfWeek: 0,
                            weekdays: {
                                shorthand: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                                longhand: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta',
                                    'Sábado'
                                ],
                            },
                            months: {
                                shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
                                    'Out', 'Nov', 'Dez'
                                ],
                                longhand: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                                    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                                ],
                            },
                        },
                        placeholder: "Selecione uma data..."
                    });
                    searchInput.setAttribute('placeholder', 'Selecione uma data...');
                } else {
                    // Destruir Flatpickr se existir
                    if (searchInput._flatpickr) {
                        searchInput._flatpickr.destroy();
                    }
                    searchInput.setAttribute('placeholder', 'Pesquisar protocolos...');
                }
            });

            function populateSelect(select, departamentoAtual) {
                const departments = <?php echo json_encode($departments); ?>;
                let optionExists = false;

                departments.forEach(department => {
                    const departmentLower = department.toLowerCase();
                    if (departmentLower !== 'kellyson' && departmentLower !== 'k') { // Ignorar "kellyson" e "k"
                        const option = document.createElement("option");
                        option.value = department;
                        option.text = department;
                        select.add(option);
                        if (department.toLowerCase() === departamentoAtual.toLowerCase()) {
                            optionExists = true;
                        }
                    }
                });

                // Se o departamento atual não estiver na lista, adicioná-lo como a primeira opção
                if (!optionExists) {
                    const currentOption = document.createElement("option");
                    currentOption.value = departamentoAtual;
                    currentOption.text = departamentoAtual;
                    select.add(currentOption, select.options[0]);
                }

                select.value = departamentoAtual;
            }

            function addProtocolBox(nome, gmail, id, requerimento, departamentoAtual, redirecionado, data, estado) {
                var partesData = data.split('/');
                var dataCriacao = new Date(partesData[2], partesData[1] - 1, partesData[0]);
                var prazo = new Date(dataCriacao);
                prazo.setDate(prazo.getDate() + 15);
                var diasSemana = ['Domingo ', 'Segunda-feira ', 'Terça-feira ', 'Quarta-feira ', 'Quinta-feira ',
                    'Sexta-feira ', 'Sábado '
                ];
                var diaSemanaPrazo = diasSemana[prazo.getDay()];
                var diaPrazo = prazo.getDate();
                var mesPrazo = prazo.getMonth() + 1;
                var anoPrazo = prazo.getFullYear();
                var dataFormatada = `${diaPrazo}/${mesPrazo}/${anoPrazo}`;

                const container = document.getElementById('protocol-container');
                const protocolBox = document.createElement('div');
                protocolBox.id = `protocolo-${id}`;
                protocolBox.classList.add('protocol-box');
                protocolBox.dataset.estado = estado; // Adiciona o atributo data-estado
                protocolBox.dataset.data = dataCriacao
                protocolBox.style.cursor = 'pointer'; // Add cursor pointer to show it's clickable

                // Add click event to the entire box
                protocolBox.addEventListener('click', function(e) {
                    // Prevent click if clicking on dropdown or view button
                    if (e.target.closest('.redirect-dropdown') || e.target.closest('.view-details-button')) {
                        return;
                    }
                    var dep = (new URLSearchParams(window.location.search)).get('dep');
                    var url = `detalhes_protocolo.php?id=${id}&dep=${dep}`;
                    window.location.href = url;
                });

                const protocolIcon = document.createElement('img');
                protocolIcon.classList.add('protocol-icon');
                protocolIcon.src = '../assets/image9.png';
                protocolIcon.alt = 'Ícone';

                const protocolInfo = document.createElement('div');
                const protocolTitle = document.createElement('div');
                protocolTitle.classList.add('protocol-title');
                protocolTitle.textContent = requerimento;

                const protocolSubtitle = document.createElement('div');
                protocolSubtitle.classList.add('protocol-subtitle');
                protocolSubtitle.textContent = 'Nº de protocolo: ' + id;

                const departamentoAtualElement = document.createElement('div');
                departamentoAtualElement.classList.add('protocol-subtitle');
                const formattedDepartamento = formatText(departamentoAtual);
                departamentoAtualElement.innerHTML = 'Departamento Atual: ' + formattedDepartamento + "<br>" + "prazo: " +
                    diaSemanaPrazo + dataFormatada;

                if (prazo < new Date()) {
                    departamentoAtualElement.style.color = 'red';
                }
                if (estado == 2) {
                    departamentoAtualElement.style.color = 'blue';
                    departamentoAtualElement.textContent = "A concluir";
                }

                protocolInfo.appendChild(protocolTitle);
                protocolInfo.appendChild(protocolSubtitle);
                protocolInfo.appendChild(departamentoAtualElement);

                const redirectDropdown = document.createElement('select');
                redirectDropdown.classList.add('redirect-dropdown');

                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Redirecionar para...';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                redirectDropdown.appendChild(defaultOption);

                // Populate departments
                const departments = <?php echo json_encode($departments); ?>;
                departments.forEach(department => {
                    const departmentLower = department.toLowerCase();
                    if (departmentLower !== 'kellyson' && departmentLower !== 'k' && department !==
                        departamentoAtual) {
                        const option = document.createElement('option');
                        option.value = department;
                        option.textContent = formatText(department);
                        redirectDropdown.appendChild(option);
                    }
                });

                // Add change event listener
                redirectDropdown.addEventListener('change', function() {
                    const selectedDepartment = this.value;
                    if (selectedDepartment) {
                        updateProtocol(id, selectedDepartment);
                    }
                });

                const protocolActions = document.createElement('div');
                protocolActions.classList.add('protocol-actions');

                if (estado !== "1") {
                    protocolActions.appendChild(redirectDropdown);
                }

                // Keep the view details button
                const viewDetailsButton = document.createElement('button');
                viewDetailsButton.classList.add('view-details-button');
                viewDetailsButton.textContent = 'ver';
                viewDetailsButton.addEventListener('click', function() {
                    const nome = this.dataset.nome;
                    const gmail = this.dataset.gmail;
                    const id = this.dataset.id;
                    const requerimento = this.dataset.requerimento;
                    const departamentoAtual = this.dataset.departamentoAtual;
                    const redirecionado = this.dataset.redirecionado;
                    var dep = (new URLSearchParams(window.location.search)).get('dep');
                    var url = `detalhes_protocolo.php?id=${id}&dep=${dep}`;
                    window.location.href = url;
                });

                viewDetailsButton.dataset.nome = nome;
                viewDetailsButton.dataset.gmail = gmail;
                viewDetailsButton.dataset.id = id;
                viewDetailsButton.dataset.requerimento = requerimento;
                viewDetailsButton.dataset.departamentoAtual = departamentoAtual;
                viewDetailsButton.dataset.redirecionado = redirecionado;

                protocolActions.appendChild(viewDetailsButton);

                const infoContainer = document.createElement('div');
                infoContainer.classList.add('protocol-info-container');
                infoContainer.appendChild(protocolIcon);
                infoContainer.appendChild(protocolInfo);

                protocolBox.appendChild(infoContainer);
                protocolBox.appendChild(protocolActions);

                if (estado === "1") {
                    // Add styling to show completed protocols
                    protocolBox.style.backgroundColor = '#e8f5e9';
                    protocolBox.style.borderLeft = '4px solid #4CAF50';
                }

                container.appendChild(protocolBox);
            }

            function updateProtocol(protocolId, newDepartment) {
                confirmAction(`Deseja realmente redirecionar este protocolo para ${formatText(newDepartment)}?`, () => {
                    showLoading(true);

                    fetch('atualizar_departamento.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: protocolId,
                                novoDepartamento: newDepartment
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            showLoading(false);
                            handleUpdateResponse(data, protocolId, newDepartment);
                        })
                        .catch(error => {
                            showLoading(false);
                            showFeedback('Erro ao processar requisição', 'error');

                            // Resetar o dropdown para a opção padrão em caso de erro
                            const protocolBox = document.getElementById(`protocolo-${protocolId}`);
                            if (protocolBox) {
                                const redirectDropdown = protocolBox.querySelector('.redirect-dropdown');
                                if (redirectDropdown) {
                                    redirectDropdown.value = '';
                                }
                            }
                        });
                }, () => {
                    // Callback para quando o usuário cancela a ação
                    const protocolBox = document.getElementById(`protocolo-${protocolId}`);
                    if (protocolBox) {
                        const redirectDropdown = protocolBox.querySelector('.redirect-dropdown');
                        if (redirectDropdown) {
                            redirectDropdown.value = '';
                        }
                    }
                });
            }

            function handleUpdateResponse(data, protocolId, newDepartment) {
                if (data.success) {
                    // Atualizar o departamento atual visualmente
                    const protocolBox = document.getElementById(`protocolo-${protocolId}`);
                    if (protocolBox) {
                        const departamentoElement = protocolBox.querySelector('.protocol-subtitle:last-child');
                        if (departamentoElement) {
                            const prazoText = departamentoElement.innerHTML.split("<br>")[1] || '';
                            departamentoElement.innerHTML = 'Departamento Atual: ' + formatText(newDepartment) +
                                "<br>" + prazoText;
                        }

                        // Reset dropdown to default option
                        const redirectDropdown = protocolBox.querySelector('.redirect-dropdown');
                        if (redirectDropdown) {
                            redirectDropdown.value = '';
                        }
                    }

                    showFeedback('Protocolo redirecionado com sucesso!', 'success');
                } else {
                    showFeedback(data.message || 'Erro ao redirecionar', 'error');
                }
            }

            function showFeedback(message, type) {
                const toast = document.getElementById('feedbackToast');
                const config = <?php echo json_encode($feedbackConfig); ?>[type];

                toast.innerHTML = `
                    <span class="feedback-icon">${config.icon}</span>
                    <span>${message}</span>
                `;
                toast.style.backgroundColor = config.bgColor;
                toast.classList.add('show');

                setTimeout(() => {
                    toast.classList.remove('show');
                }, FEEDBACK_TIMEOUT);
            }

            function showLoading(show = true) {
                const overlay = document.getElementById('loadingOverlay');
                if (show) {
                    overlay.classList.add('show');
                } else {
                    overlay.classList.remove('show');
                }
            }

            function confirmAction(message, onConfirm) {
                const modal = document.getElementById('confirmationModal');
                const backdrop = document.getElementById('modalBackdrop');
                const messageEl = document.getElementById('confirmationMessage');
                const confirmBtn = document.getElementById('confirmAction');
                const cancelBtn = document.getElementById('cancelAction');

                messageEl.textContent = message;
                modal.classList.add('show');
                backdrop.classList.add('show');

                const handleConfirm = () => {
                    modal.classList.remove('show');
                    backdrop.classList.remove('show');
                    onConfirm();
                    cleanup();
                };

                const handleCancel = () => {
                    modal.classList.remove('show');
                    backdrop.classList.remove('show');
                    cleanup();
                };

                const cleanup = () => {
                    confirmBtn.removeEventListener('click', handleConfirm);
                    cancelBtn.removeEventListener('click', handleCancel);
                };

                confirmBtn.addEventListener('click', handleConfirm);
                cancelBtn.addEventListener('click', handleCancel);

                // Fechar modal ao clicar no backdrop
                backdrop.addEventListener('click', handleCancel);
            }

            function filterProtocols(state) {
                const urlParams = new URLSearchParams(window.location.search);

                // Clear search parameters when changing filters
                urlParams.delete('search');
                urlParams.delete('searchField');
                urlParams.delete('page');

                // Set or remove state parameter
                if (state !== undefined) {
                    urlParams.set('state', state);
                } else {
                    urlParams.delete('state');
                }

                // Keep only the department parameter
                const dep = urlParams.get('dep');
                urlParams.delete('dep');
                if (dep) {
                    urlParams.set('dep', dep);
                }

                window.location.search = urlParams.toString();
            }

            function formatText(text) {
                // Replace underscores with spaces and capitalize first letter of each word
                return text.split('_')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            }
        </script>
    </body>

</html>
<?php
require_once '../env/config.php';

// Estabelece conexão com o banco de dados
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Erro de conexão: ' . $conn->connect_error
    ]));
}
$sql = "SELECT id, departamento_atual, requerimento, redirecionado, gmail, nome, data, estado FROM protocolos WHERE estado != 3";

// Add search conditions to SQL query
if (isset($_GET['search']) && isset($_GET['searchField'])) {
    $searchText = $conn->real_escape_string($_GET['search']);
    $searchField = $_GET['searchField'];

    switch ($searchField) {
        case 'protocol-title':
            $sql .= " AND requerimento LIKE '%$searchText%'";
            break;
        case 'protocol-subtitle':
            $sql .= " AND id LIKE '%$searchText%'";
            break;
        case 'protocol-name':
            $sql .= " AND nome LIKE '%$searchText%'";
            break;
        case 'protocol-gmail':
            $sql .= " AND gmail LIKE '%$searchText%'";
            break;
        case 'protocol-date':
            $sql .= " AND data LIKE '%$searchText%'";
            break;
    }
}

if (isset($_GET["dep"]) && !empty($_GET["dep"])) {
    $departamentoFiltrado = $_GET["dep"];
    if (isUserAdmin($conn, $departamentoFiltrado)) {
        // Don't filter by department for admins
    } else {
        $sql .= " AND departamento_atual = '$departamentoFiltrado'";
    }
}

// Remove the default 'estado' filter, so all protocols are included when 'state' is not set
if (isset($_GET["state"]) && is_numeric($_GET["state"])) {
    $estadoFiltrado = intval($_GET["state"]);
    $sql .= " AND estado = $estadoFiltrado";
}

$sql .= " ORDER BY estado ASC, STR_TO_DATE(data, '%d/%m/%Y') DESC";

$protocolosPorPagina = 10;
$paginaAtual =
    isset($_GET["page"]) && is_numeric($_GET["page"])
    ? intval($_GET["page"])
    : 1;
$indiceInicio = ($paginaAtual - 1) * $protocolosPorPagina;
$sql .= " LIMIT $indiceInicio, $protocolosPorPagina";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row["id"];
        $departamento = $row["departamento_atual"];
        $requerimento = $row["requerimento"];
        $redirecionado = $row["redirecionado"];
        $gmail = $row["gmail"];
        $nome = $row["nome"];
        $data = $row["data"];
        $estado = $row["estado"];
        echo "<script>
        addProtocolBox('$nome', '$gmail', '$id', '$requerimento', '$departamento', '$redirecionado', '$data', '$estado' );
        </script>";
    }
} else {
    echo '<div style="background-color: #f2f2f2; padding: 20px; border-radius: 10px; text-align: center; margin: 20px auto; max-width: 600px;">';

    // Se houver pesquisa ativa
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        echo '<p style="font-size: 18px; color: #555;">Nenhum resultado encontrado</p>';
        echo '<p style="font-size: 14px; color: #777;">A pesquisa por "' .
            htmlspecialchars($_GET['search']) .
            '" não retornou resultados.</p>';
    }
    // Se houver filtro de estado
    else if (isset($_GET["state"])) {
        $estadoTexto = [
            "0" => "pendentes",
            "1" => "concluídos",
            "2" => "a concluir"
        ];
        echo '<p style="font-size: 18px; color: #555;">Sem protocolos ' .
            $estadoTexto[$_GET["state"]] . '</p>';
        echo '<p style="font-size: 14px; color: #777;">Não há protocolos com este status no momento.</p>';
    }
    // Caso padrão para departamento
    else {
        $depName = $_GET["dep"] ?? "geral";
        $depName = str_replace('_', ' ', ucfirst($depName));

        echo '<p style="font-size: 18px; color: #555;">Departamento sem protocolos</p>';
        echo '<p style="font-size: 14px; color: #777;">O departamento ' .
            htmlspecialchars($depName) .
            ' não possui protocolos ativos no momento.</p>';
    }

    echo '</div>';
}

// Update count query to include search conditions
$sqlCount = str_replace("SELECT id, departamento_atual, requerimento, redirecionado, gmail, nome, data, estado", "SELECT COUNT(*) as countRows", $sql);
$sqlCount = preg_replace("/ORDER BY.*$/", "", $sqlCount); // Remove ORDER BY clause from count query

// Get total filtered records
$countResult = $conn->query($sqlCount);
$totalRecords = $countResult->fetch_assoc()['countRows'];
$totalPaginas = ceil($totalRecords / $protocolosPorPagina);

?>

<!-- Pagination with original green -->
<div class="flex justify-center mt-6 mb-52">
    <nav class="inline-flex items-center space-x-2">
        <?php
        $currentParams = $_GET;
        if ($paginaAtual > 1):
            $currentParams['page'] = $paginaAtual - 1;
        ?>
            <a href="?<?php echo http_build_query($currentParams); ?>"
                class="px-3 py-1 bg-white border border-[#009640] rounded-md hover:bg-green-50 text-[#000000FF]">
                Anterior
            </a>
        <?php endif; ?>

        <?php
        $maxLinks = 5; // Maximum number of page links to display
        $start = max(1, $paginaAtual - floor($maxLinks / 2));
        $end = min($totalPaginas, $start + $maxLinks - 1);

        if ($start > 1) {
            echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="px-3 py-1 bg-white border border-[#009640] rounded-md hover:bg-green-50 text-[#009640]">1</a>';
            if ($start > 2) {
                echo '<span class="px-3 py-1">...</span>';
            }
        }

        for ($i = $start; $i <= $end; $i++):
            if ($i == $paginaAtual): ?>
                <span class="px-3 py-1 bg-[#009640] text-white border border-[#009640] rounded-md">
                    <?php echo $i; ?>
                </span>
            <?php else: ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                    class="px-3 py-1 bg-white border border-[#009640] rounded-md hover:bg-green-50 text-[#000000FF]">
                    <?php echo $i; ?>
                </a>
        <?php endif;
        endfor;

        if ($end < $totalPaginas) {
            if ($end < $totalPaginas - 1) {
                echo '<span class="px-3 py-1">...</span>';
            }
            echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $totalPaginas])) . '" class="px-3 py-1 bg-white border border-[#009640] rounded-md hover:bg-green-50 text-[#009640]">' . $totalPaginas . '</a>';
        }
        ?>

        <?php if ($paginaAtual < $totalPaginas): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $paginaAtual + 1])); ?>"
                class="px-3 py-1 bg-white border border-[#009640] rounded-md hover:bg-green-50 text-[#000000FF]">
                Próxima
            </a>
        <?php else: ?>
            <span class="px-3 py-1 bg-gray-100 border border-gray-300 rounded-md text-gray-400 cursor-not-allowed">
                Próxima
            </span>
        <?php endif; ?>
    </nav>
</div>

<?php

$conn->close();
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<div class="content">
    </footer>
</div>

<footer id="customFooter">
    <a href="../documento.pdf" class="footerButton" target="_blank">Manual do Sistema (PDF)</a>
    <a href="https://wa.me/558496914693" class="footerLink">Suporte e Dúvidas (WhatsApp)</a>
    <div class="footerText">
        <p>Versão 2.3.4</p>
        <p>Desenvolvido por Kellyson R. Medeiros da S.</p>
    </div>
</footer>