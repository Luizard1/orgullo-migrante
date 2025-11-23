import { supabase } from './supabaseClient.js';
import bcrypt from 'bcryptjs';

export async function loginUsuario(correo, contrasena) {
  try {
    const { data: user, error } = await supabase
      .from("usuarios")
      .select("*")
      .eq("correo", correo)
      .single();

    if (error || !user) {
      return { ok: false, error: "Usuario no encontrado" };
    }

    const valido = bcrypt.compareSync(contrasena, user.contrasena);
    if (!valido) {
      return { ok: false, error: "Contraseña incorrecta" };
    }

    return { ok: true, usuario: user.nombre, rol: user.rol };
  } catch (err) {
    console.error("Error inesperado:", err);
    return { ok: false, error: err.message };
  }
}
