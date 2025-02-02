function randomDelay() {
    return Math.random() * 1000; 
  }
  document.addEventListener("DOMContentLoaded", function(event) {
    document.querySelector('.lds-ring').style.display = 'block';
  });
  window.addEventListener('load', function() {
    setTimeout(function() {
      document.querySelector('.content').style.display = 'block';
      document.querySelector('.lds-ring').style.display = 'none';
    }, randomDelay());
  });

function searchProtocols() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const protocols = document.querySelectorAll('.protocol-box');
    if (searchText === '') {
        protocols.forEach(protocol => {
            protocol.style.display = ''; 
        });
        return; 
    }
    protocols.forEach(protocol => {
        const protocolTitle = protocol.querySelector('.protocol-title').textContent.toLowerCase();
        if (protocolTitle.includes(searchText)) {
            protocol.style.display = ''; 
        } else {
            protocol.style.display = 'none'; 
        }
    });
}
        function populateSelect(select) {
    const departments = [
            "Secretaria",
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
            "Folha de pagamento"

    ];
    departments.forEach(department => {
        const option = document.createElement("option");
        option.value = department.replace(/\s/g, '_'); 
        option.text = department;
        select.add(option);
    });
}
function addProtocolBox(nome, gmail, id, requerimento, departamento_atual, redirecionado, data,estado) {
var partesData = data.split('/');
var dataCriacao = new Date(partesData[2], partesData[1] - 1, partesData[0]);
var prazo = new Date(dataCriacao);
prazo.setDate(prazo.getDate() + 15);
var diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
var diaSemanaPrazo = diasSemana[prazo.getDay()];
var diaPrazo = prazo.getDate();
var mesPrazo = prazo.getMonth() + 1; 
var anoPrazo = prazo.getFullYear();
var dataFormatada = `${diaPrazo}/${mesPrazo}/${anoPrazo}`;
    const container = document.getElementById('protocol-container');
    const protocolBox = document.createElement('div');
    protocolBox.id = `protocolo-${id}`
    protocolBox.classList.add('protocol-box');
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
    departamentoAtualElement.innerHTML = 'Departamento Atual: ' + departamento_atual + "<br>"+ "prazo: " + diaSemanaPrazo  + dataFormatada;
    if (prazo < new Date()) {
        departamentoAtualElement.style.color = 'red'; 
    }
    protocolInfo.appendChild(protocolTitle);
    protocolInfo.appendChild(protocolSubtitle);
    protocolInfo.appendChild(departamentoAtualElement); 
    const selectContainer = document.createElement('div');
    selectContainer.classList.add('select-container');
    const selectLabel = document.createElement('label');
    selectLabel.htmlFor = 'department-select-' + container.children.length;
    selectLabel.textContent = '';
    const select = document.createElement('select');
    select.id = 'department-select-' + container.children.length;
    select.classList.add('department-select');
    populateSelect(select);
    select.value = departamento_atual
    selectContainer.appendChild(selectLabel);
    selectContainer.appendChild(select);
    const redirectButton = document.createElement('button');
    redirectButton.classList.add('redirect-button');
    redirectButton.textContent = 'Redirecionar';
    redirectButton.addEventListener('click', function() {
        const selectedDepartment = select.value;
        updateDatabase(id, selectedDepartment); 
        showFeedback(`Redirecionado com sucesso para ${selectedDepartment}`, 'success');
    });
    const viewDetailsButton = document.createElement('button');
    viewDetailsButton.classList.add('view-details-button');
    viewDetailsButton.textContent = 'ver';
    viewDetailsButton.addEventListener('click', function() {
        const nome = this.dataset.nome;
        const gmail = this.dataset.gmail;
        const id = this.dataset.id;
        const requerimento = this.dataset.requerimento;
        const departamento_atual = this.dataset.departamento_atual;
        const redirecionado = this.dataset.redirecionado;
        var dep = (new URLSearchParams(window.location.search)).get('dep');
        var url = `detalhes_protocolo.php?id=${id}&dep=${dep}`;
        window.location.href = url;
    });
    viewDetailsButton.dataset.nome = nome;
    viewDetailsButton.dataset.gmail = gmail;
    viewDetailsButton.dataset.id = id;
    viewDetailsButton.dataset.requerimento = requerimento;
    viewDetailsButton.dataset.departamento_atual = departamento_atual;
    viewDetailsButton.dataset.redirecionado = redirecionado;
    protocolBox.appendChild(protocolIcon);
    protocolBox.appendChild(protocolInfo);
    protocolBox.appendChild(selectContainer);
    protocolBox.appendChild(redirectButton);
    protocolBox.appendChild(viewDetailsButton);
if (estado === "1") {
    protocolBox.style.opacity = "0.9";
    departamentoAtualElement.textContent = "Concluído";
    redirectButton.style.display = "none";
    viewDetailsButton.style.marginLeft = "15px";
    selectContainer.style.display = "none";
    container.appendChild(protocolBox);
} else {
    const protocols = container.getElementsByClassName('protocol-box');
    let indexToInsert = 0;
    let insertBeforeNode = null;
    for (let i = 0; i < protocols.length; i++) {
        const currentProtocol = protocols[i];
        const currentProtocolEstado = currentProtocol.dataset.estado;
        const currentProtocolData = new Date(currentProtocol.dataset.data);
        if (estado === currentProtocolEstado) {
            if (estado === "0" && currentProtocolData < dataCriacao) {
                insertBeforeNode = currentProtocol;
                break;
            } else if (estado === "1" && currentProtocolData > dataCriacao) {
                insertBeforeNode = currentProtocol;
                break;
            }
        }
    }
    container.insertBefore(protocolBox, protocols[0]);
}
}
function updateDatabase(id, novoDepartamento) {
                    console.log(novoDepartamento);
    fetch('atualizar_departamento.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: id,
            novoDepartamento: novoDepartamento,
        }),
    })
    .then(response => response.json())
    .then(data => {
        showFeedback(data.message, data.success ? 'success' : 'error');
        const cardProtocolo = document.getElementById(`protocolo-${id}`)
        cardProtocolo.parentNode.removeChild(cardProtocolo)
    })
    .catch(error => {
        console.error('Erro na atualização do banco de dados:', error);
    });
}
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

$(document).ready(function() {
  $('select').select2();
});