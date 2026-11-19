document.addEventListener("DOMContentLoaded", () => {
  // Mostrar u ocultar contraseña en la pantalla de acceso.
  document.querySelectorAll("[data-toggle-password]").forEach((button) => {
    button.addEventListener("click", () => {
      const input = document.getElementById(button.dataset.togglePassword);
      if (!input) return;
      const showing = input.type === "text";
      input.type = showing ? "password" : "text";
      button.textContent = showing ? "Ver" : "Ocultar";
      button.setAttribute("aria-label", showing ? "Mostrar contraseña" : "Ocultar contraseña");
    });
  });

  // Validación visual de confirmación de contraseña.
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", (event) => {
      const password = document.getElementById("password").value;
      const confirmation = document.getElementById("confirmPassword").value;
      const message = document.getElementById("registerMessage");
      if (password !== confirmation) {
        event.preventDefault();
        message.textContent = "Las contraseñas no coinciden. Verifica la información.";
        document.getElementById("confirmPassword").focus();
        return;
      }
    });
  }

  // La fecha de nacimiento no puede ser futura.
  const fechaNac = document.getElementById("fechaNac");
  if (fechaNac) {
    const t = new Date();
    fechaNac.max = `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, "0")}-${String(t.getDate()).padStart(2, "0")}`;
  }

  // Fecha del día en el menú principal.
  const dateElement = document.getElementById("currentDate");
  if (dateElement) {
    dateElement.textContent = new Intl.DateTimeFormat("es-MX", {
      day: "2-digit", month: "long", year: "numeric"
    }).format(new Date());
  }

  // Aviso para los módulos que todavía no tienen una página o backend asignado.
  document.querySelectorAll("[data-module]").forEach((link) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();
      const moduleNames = {
        entrada: "Registrar entrada",
        salida: "Registrar salida",
        consulta: "Consultar registros"
      };
      alert(`${moduleNames[link.dataset.module]}: este módulo se conectará cuando se defina el backend.`);
    });
  });


  // Fecha y hora locales para las pantallas de entrada y salida.
  const pad = (number) => String(number).padStart(2, "0");
  const now = new Date();
  const localDate = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
  const localTime = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
  ["entryDate", "exitDate"].forEach((id) => {
    const field = document.getElementById(id);
    if (field) field.value = localDate;
  });
  ["entryTime", "exitTime"].forEach((id) => {
    const field = document.getElementById(id);
    if (field) field.value = localTime;
  });

  // Confirmación de interfaz: no guarda ni transmite información.
  [
    { formId: "entryForm", messageId: "entryMessage", confirmationId: "entryConfirmation" },
    { formId: "exitForm", messageId: "exitMessage", confirmationId: "exitConfirmation" }
  ].forEach(({ formId, messageId, confirmationId }) => {
    const form = document.getElementById(formId);
    if (!form) return;
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      const message = document.getElementById(messageId);
      const confirmation = document.getElementById(confirmationId);
      if (!form.reportValidity()) return;
      message.textContent = "";
      confirmation.hidden = false;
      confirmation.scrollIntoView({ behavior: "smooth", block: "nearest" });
    });
  });
});
