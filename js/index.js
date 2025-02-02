function showFeedback(message, type) {
    const feedbackDiv = document.getElementById('feedback')

    feedbackDiv.style.display = 'block'
    feedbackDiv.innerHTML = message

    if (type === 'success') {
        feedbackDiv.style.backgroundColor = "#00A759"
    } else {
        feedbackDiv.style.backgroundColor = "#ED4141"
    }

    setTimeout(() => {
        feedbackDiv.style.display = 'none'
    }, 3000)
}
function validateEmails() {
                console.log(1)
            var email = document.getElementById('email').value;
            var confirmEmail = document.getElementById('confirmEmail').value;

            if (email !== confirmEmail) {
                showFeedback('Os emails não coincidem', 'error');
                return false; // Impede o envio do formulário
            }
            return true; // Permite o envio do formulário
        }
function increaseFont() {
    const root = document.documentElement

    const currentFontSize = Number(root.style.fontSize.replace('px', '')) || 16
    root.style.setProperty('font-size', currentFontSize + 2 + "px")
}

function decreaseFont() {
    const root = document.documentElement

    const currentFontSize = Number(root.style.fontSize.replace('px', '')) || 16
    root.style.setProperty('font-size', currentFontSize - 2 + "px")
}




function updateCheckedOptions() {
    const element = document.getElementsByClassName('multi-select')[0].children[0]

    Array.from(element.children).forEach(element => {
        if (element.tagName !== 'LABEL')
            return

        const input = element.getElementsByTagName('input')[0]

        input.checked ? 
           element.classList.add('checked') : 
           element.classList.remove('checked')
    });
}

function sendSignUp(e) {
    e.preventDefault()

    const botao = document.getElementById('botao')
    const resultado = validateEmails();
    if (resultado !== true) {
        return;
    }
    botao.setAttribute('disabled', 'disabled')
    botao.innerHTML = '...'

    const data = getData()
    
    const formData = new FormData()

    const doc= document.getElementById('anexos')

    formData.set('name', data.name)
    formData.set('json', JSON.stringify(data))

    if (doc.files[0] !== null) {
        formData.set('anexos', doc.files[0])

        const nome_documento = document.getElementById('nome_documento')

        formData.set('nome_documento', nome_documento.value)
    }

    fetch('./php/index.php', { body: formData, method: 'POST' }).then((res) => {
        if (res.status === 200)
            showFeedback('Cadastro bem sucedido!', 'success')
        else if (!res.json) {
            showFeedback(res.message)
        } else {
            res.json().then(res => showFeedback(res.message))
        }
    }).catch(() => {
        if (!res.json) {
            showFeedback(res.message)
        } else {
            res.json().then(res => showFeedback(res.message))
        }
    // }).finally(() => {
    //     botao.removeAttribute('disabled')
    //     botao.innerHTML = 'ENVIAR'
    })
}

function getData() {
    const elements = document.getElementById('form').elements;

    const data = {
        name: elements.name.value,
        email: elements.email.value,
        matricula: elements.matricula.value,
        phone: elements.phone.value,
        endereco: elements.endereco.value,
        vinculo: elements.vinculo.value,
        lotacao: elements.lotacao.value,
        assunto: elements.assunto.value,
        descricao: elements.texto.value
    };

    return data;
}



const handlePhone = (event) => {
    let input = event.target
    input.value = phoneMask(input.value)
  }
  
const phoneMask = (value) => {
    if (!value) return ""
    value = value.replace(/\D/g,'')
    value = value.replace(/(\d{2})(\d)/,"($1) $2")
    value = value.replace(/(\d)(\d{4})$/,"$1-$2")
    return value
  }

function formatarCPF(cpf) {
    return cpf;
}

function toggleCPFInput() {
    if (cpfInput.style.display === 'none') {
        cpfInput.style.display = 'block';
        verificarButton.style.display = 'block'; // Mostra o botão junto com o input
    } else {
        cpfInput.style.display = 'none';
        verificarButton.style.display = 'none'; // Oculta o botão junto com o input
    }
}
function verificarCPF() {
    var cpf = $('#cpfInput').val();

    if (cpf.trim() === '') {
        showFeedback('Por favor, informe o CPF.', 'error');
        return;
}

$.ajax({
    type: 'GET',
    url: './php/consulta.php',
    data: { cpf: cpf },
    dataType: 'json',
    success: function (response) {
        showFeedback(response.message, 'success');
    },
    error: function (xhr, status, error) {
        console.error('Erro na requisição AJAX:', xhr.status, xhr.statusText);
        console.error('Detalhes do erro:', error);
        console.error('Resposta do servidor:', xhr.responseText);
        showFeedback('Erro na requisição AJAX. Consulte o console para mais informações.', 'error');
    }
});
}

window.addEventListener('load', () => {
    const form = document.getElementById('form')
    const phoneInput = document.getElementById('phone')    

    phoneInput.addEventListener('keyup', handlePhone)
    form.addEventListener('submit', sendSignUp)
    var simButton = document.getElementById('def-1');
var naoButton = document.getElementById('def-2');
var formFilesDiv = document.querySelector('.form-files');

// Adiciona o evento de clique aos botões
simButton.addEventListener('click', toggleDiv);
naoButton.addEventListener('click', toggleDiv);

formFilesDiv.style.display = 'none';
})      



function toggleDiv() {
    if (this.id === 'def-1') {
        // Se o botão "Sim" foi clicado, exibe a div
        formFilesDiv.style.display = 'block';
    } else {
        // Se o botão "Não" foi clicado, oculta a div
        formFilesDiv.style.display = 'none';
    }
}

