import { supabase } from './supabaseClient.js';

export async function verificarSesion() {
  const loginSection = document.getElementById('loginSection');
  const userInfoSection = document.getElementById('userInfoSection');
  const userNameDisplay = document.getElementById('userNameDisplay');
  const logoutButton = document.getElementById('logoutButton');

  const { data: { session }, error } = await supabase.auth.getSession();

  if (session) {
    const userId = session.user.id;

    const { data: userData, error: userError } = await supabase
      .from('usuarios')
      .select('usuario')
      .eq('id', userId)
      .single();

    if (userData && userData.usuario) {
      if (loginSection) loginSection.style.display = 'none';
      if (userInfoSection) userInfoSection.style.display = 'block';
      if (userNameDisplay) userNameDisplay.textContent = userData.usuario;

      if (logoutButton) {
        logoutButton.addEventListener('click', async () => {
          await supabase.auth.signOut();
          if (userInfoSection) userInfoSection.style.display = 'none';
          if (loginSection) loginSection.style.display = 'block';
        });
      }
    }
  }
}
verificarSesion(); // Esto hace que se ejecute automáticamente
