function showModal() {
  const modal = document.getElementById("successModal");
  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  const modal = document.getElementById("successModal");
  modal.classList.add("hidden");
  document.body.style.overflow = "auto";
}

function showReenvioModal() {
  const modal = document.getElementById("reenvioModal");
  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function closeReenvioModal() {
  const modal = document.getElementById("reenvioModal");
  modal.classList.add("hidden");
  document.body.style.overflow = "auto";
}

function reiniciarFormulario() {
  window.location.reload();
}

// Modifique a função showFeedback
function showFeedback(message, type) {
  if (type === "success") {
    showModal();
  } else if (message.includes("já criou esse protocolo")) {
    showReenvioModal();
  } else {
    const feedbackDiv = document.getElementById("feedback");
    feedbackDiv.style.display = "block";
    feedbackDiv.innerHTML = message;
    feedbackDiv.style.backgroundColor = "#ED4141";

    setTimeout(() => {
      feedbackDiv.style.display = "none";
    }, 3000);
  }
}

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function validateEmails() {
  const emailInput = document.getElementById("email");
  const confirmEmailInput = document.getElementById("confirmEmail");
  const email = emailInput.value;
  const confirmEmail = confirmEmailInput.value;

  let isValid = true;

  // Limpa estados anteriores
  emailInput.classList.remove("error");
  confirmEmailInput.classList.remove("error");
  emailInput.parentElement.querySelector(".error-message").style.display =
    "none";
  confirmEmailInput.parentElement.querySelector(
    ".error-message"
  ).style.display = "none";

  // Valida formato do email
  if (!validateEmail(email)) {
    emailInput.classList.add("error");
    emailInput.parentElement.querySelector(".error-message").textContent =
      "Email inválido";
    emailInput.parentElement.querySelector(".error-message").style.display =
      "block";
    isValid = false;
  }

  // Valida se os emails são iguais
  if (email !== confirmEmail) {
    confirmEmailInput.classList.add("error");
    confirmEmailInput.parentElement.querySelector(
      ".error-message"
    ).textContent = "Os emails não coincidem";
    confirmEmailInput.parentElement.querySelector(
      ".error-message"
    ).style.display = "block";
    isValid = false;
  }

  return isValid;
}

function increaseFont() {
  const root = document.documentElement;

  const currentFontSize = Number(root.style.fontSize.replace("px", "")) || 16;
  root.style.setProperty("font-size", currentFontSize + 2 + "px");
}

function decreaseFont() {
  const root = document.documentElement;

  const currentFontSize = Number(root.style.fontSize.replace("px", "")) || 16;
  root.style.setProperty("font-size", currentFontSize - 2 + "px");
}

function updateCheckedOptions() {
  const element =
    document.getElementsByClassName("multi-select")[0].children[0];

  Array.from(element.children).forEach((element) => {
    if (element.tagName !== "LABEL") return;

    const input = element.getElementsByTagName("input")[0];

    input.checked
      ? element.classList.add("checked")
      : element.classList.remove("checked");
  });
}

function sendSignUp(e) {
  e.preventDefault();

  const botao = document.getElementById("botao");
  const buttonText = botao.querySelector(".button-text");
  const spinner = botao.querySelector("#loading-spinner");

  const resultado = validateEmails();
  if (resultado !== true) {
    return;
  }

  // Desabilita o botão e mostra o spinner
  botao.setAttribute("disabled", "disabled");
  buttonText.classList.add("opacity-50");
  spinner.classList.remove("hidden");

  const data = getData();
  const formData = new FormData();

  const doc = document.getElementById("anexos");

  formData.set("name", data.name);
  formData.set("json", JSON.stringify(data));

  if (doc.files[0] !== null) {
    formData.set("anexos", doc.files[0]);

    const nome_documento = document.getElementById("nome_documento");

    formData.set("nome_documento", nome_documento.value);
  }

  fetch("./php/index.php", { body: formData, method: "POST" })
    .then((res) => {
      if (res.status === 200) showFeedback("Cadastro bem sucedido!", "success");
      else if (!res.json) {
        showFeedback(res.message);
      } else {
        res.json().then((res) => showFeedback(res.message));
      }
    })
    .catch(() => {
      if (!res.json) {
        showFeedback(res.message);
      } else {
        res.json().then((res) => showFeedback(res.message));
      }
    })
    .finally(() => {
      // Reativa o botão e esconde o spinner
      botao.removeAttribute("disabled");
      buttonText.classList.remove("opacity-50");
      spinner.classList.add("hidden");
    });
}

function getData() {
  const elements = document.getElementById("form").elements;

  const data = {
    name: elements.name.value,
    email: elements.email.value,
    matricula: elements.matricula.value,
    phone: elements.phone.value,
    endereco: elements.endereco.value,
    vinculo: elements.vinculo.value,
    lotacao: elements.lotacao.value,
    assunto: elements.assunto.value,
    descricao: elements.texto.value,
  };

  return data;
}

const handlePhone = (event) => {
  let input = event.target;
  input.value = phoneMask(input.value);
};

const phoneMask = (value) => {
  if (!value) return "";
  value = value.replace(/\D/g, "");
  value = value.replace(/(\d{2})(\d)/, "($1) $2");
  value = value.replace(/(\d)(\d{4})$/, "$1-$2");
  return value;
};

function formatarCPF(cpf) {
  return cpf;
}

function toggleCPFInput() {
  if (cpfInput.style.display === "none") {
    cpfInput.style.display = "block";
    verificarButton.style.display = "block"; // Mostra o botão junto com o input
  } else {
    cpfInput.style.display = "none";
    verificarButton.style.display = "none"; // Oculta o botão junto com o input
  }
}
function verificarCPF() {
  var cpf = $("#cpfInput").val();

  if (cpf.trim() === "") {
    showFeedback("Por favor, informe o CPF.", "error");
    return;
  }

  $.ajax({
    type: "GET",
    url: "./php/consulta.php",
    data: { cpf: cpf },
    dataType: "json",
    success: function (response) {
      showFeedback(response.message, "success");
    },
    error: function (xhr, status, error) {
      console.error("Erro na requisição AJAX:", xhr.status, xhr.statusText);
      console.error("Detalhes do erro:", error);
      console.error("Resposta do servidor:", xhr.responseText);
      showFeedback(
        "Erro na requisição AJAX. Consulte o console para mais informações.",
        "error"
      );
    },
  });
}

window.addEventListener("DOMContentLoaded", () => {
  // Event listeners são agora configurados no script inline do index.html
});
