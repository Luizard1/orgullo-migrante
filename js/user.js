// js/user.js
document.addEventListener("DOMContentLoaded", () => {
  const userBtn      = document.getElementById("userButton");
  const userOverlay  = document.getElementById("userOverlay");
  const closeUser    = document.getElementById("closeUser");

  const loginSection     = document.getElementById("loginSection");
  const registerSection  = document.getElementById("registerSection");
  const forgotSection    = document.getElementById("forgotSection");

  const loginForm     = document.getElementById("loginForm");
  const registerForm  = document.getElementById("registerForm");
  const forgotForm    = document.getElementById("forgotForm");

  const registerLink  = document.getElementById("registerLink");
  const forgotLink    = document.getElementById("forgotLink");
  const backToLogin   = document.getElementById("backToLogin");
  const backToLogin2  = document.getElementById("backToLogin2");

  // Abrir/Cerrar overlay
  userBtn.addEventListener("click", () => userOverlay.style.display = "block");
  closeUser.addEventListener("click", () => userOverlay.style.display = "none");
  window.addEventListener("click", e => {
    if (e.target === userOverlay) userOverlay.style.display = "none";
  });

  // Mostrar formularios
  registerLink.addEventListener("click", e => {
    e.preventDefault();
    loginSection.style.display    = "none";
    registerSection.style.display = "block";
  });
  backToLogin.addEventListener("click", e => {
    e.preventDefault();
    registerSection.style.display = "none";
    loginSection.style.display    = "block";
  });
  forgotLink.addEventListener("click", e => {
    e.preventDefault();
    loginSection.style.display    = "none";
    forgotSection.style.display   = "block";
  });
  backToLogin2.addEventListener("click", e => {
    e.preventDefault();
    forgotSection.style.display   = "none";
    loginSection.style.display    = "block";
  });

  /* Simular base de datos con localStorage
  const getUsers = () => JSON.parse(localStorage.getItem("users")) || {};
  const setUsers = u => localStorage.setItem("users", JSON.stringify(u));

  // 1) LOGIN
  loginForm.addEventListener("submit", e => {
    e.preventDefault();
    const { username, password } = Object.fromEntries(new FormData(loginForm));
    const users = getUsers();
    if (users[username] === password) {
      alert(`✅ ¡Bienvenido, ${username}!`);
      userOverlay.style.display = "none";
    } else {
      alert("❌ Usuario o contraseña incorrectos.");
    }
  });

     2) REGISTER
    registerForm.addEventListener("submit", e => {
        e.preventDefault();
        const form = new FormData(registerForm);
        const email           = form.get("email").trim();
        const username        = form.get("username").trim();
        const lastName        = form.get("lastName").trim();
        const password        = form.get("password");
        const confirmPassword = form.get("confirmPassword");
        const phone           = form.get("phone").trim();
        const shoeSize        = form.get("shoeSize").trim();

        // Validar campos obligatorios
        if (!email || !username || !password || !confirmPassword || !shoeSize) {
            return alert("❌ Completa todos los campos obligatorios (*).");
        }
        // Validar contraseñas
        if (password !== confirmPassword) {
            return alert("❌ Las contraseñas no coinciden.");
        }
        const users = getUsers();
        if (users[username]) {
            return alert("❌ Este usuario ya existe.");
        }

        // Guardar nuevo usuario
        users[username] = {
            email,
            lastName,
            password,
            phone,
            shoeSize
        };
        setUsers(users);

        alert("✅ Registro exitoso. Ahora inicia sesión.");
        registerSection.style.display = "none";
        loginSection.style.display    = "block";
        registerForm.reset();
    });
*/

  // 3) FORGOT PASSWORD
  forgotForm.addEventListener("submit", e => {
    e.preventDefault();
    const username = new FormData(forgotForm).get("username");
    const users = getUsers();
    if (!users[username]) {
      return alert("❌ Usuario no encontrado.");
    }
    const resetCode = "RST-" + Math.random().toString(36).substr(2, 6).toUpperCase();
    alert(`📧 Se ha enviado un código de recuperación a ${username}: ${resetCode}`);
    forgotSection.style.display = "none";
    loginSection.style.display  = "block";
  });
});
