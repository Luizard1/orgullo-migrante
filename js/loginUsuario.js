import { supabase } from './supabaseClient.js';

document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  const loginSection = document.getElementById('loginSection');
  const userInfoSection = document.getElementById('userInfoSection');
  const userNameDisplay = document.getElementById('userNameDisplay');
  const logoutButton = document.getElementById('logoutButton');

  // Función para verificar sesión activa y mostrar nombre
  async function verificarSesion() {
    const { data: { session }, error: sessionError } = await supabase.auth.getSession();

    if (session) {
      const userId = session.user.id;
      const { data, error } = await supabase
        .from('usuarios')
        .select('usuario')
        .eq('id', userId)
        .single();

      if (data && data.usuario) {
        loginSection.style.display = 'none';
        userInfoSection.style.display = 'block';
        userNameDisplay.textContent = data.usuario;
      } else {
        console.error('Error obteniendo nombre de usuario:', error);
      }
    } else if (sessionError) {
      console.error('Error obteniendo sesión:', sessionError.message);
    }
  }

  verificarSesion(); // Verifica al cargar la página

  // Evento de login
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = loginForm.username.value;
    const password = loginForm.password.value;

    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password,
    });

    if (error) {
      console.error('Error al iniciar sesión:', error.message);
      alert('Credenciales inválidas');
    } else {
      verificarSesion();
      // Redireccionar o recargar
      window.location.reload();
    }
  });

  // Evento cerrar sesión
  logoutButton.addEventListener('click', async () => {
    await supabase.auth.signOut();
    userInfoSection.style.display = 'none';
    loginSection.style.display = 'block';

    // Vaciar carrito
    localStorage.removeItem("cart");

    // Redireccionar o recargar
    window.location.reload();

  });
});
