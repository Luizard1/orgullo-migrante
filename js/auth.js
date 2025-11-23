import { supabase } from "./supabaseClient.js";

async function verificarUsuario() {
  const { data: { user }, error } = await supabase.auth.getUser();

  if (error) {
    console.error("Error obteniendo usuario:", error.message);
    return;
  }

  if (!user) {
    console.log("No hay usuario logueado");
    return;
  }

  const userId = user.id;
  console.log("Usuario logueado con ID:", userId);

  const { data: perfil, error: perfilError } = await supabase
    .from('usuarios') // ❌ Estaba mal escrito con espacio extra: ' usuarios'
    .select('*')
    .eq('id', userId)
    .single();

  if (perfilError) {
    console.error("Error obteniendo perfil:", perfilError.message);
    return;
  }

  console.log("Perfil del usuario:", perfil);
}

verificarUsuario();
