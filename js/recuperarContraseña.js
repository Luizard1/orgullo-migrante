import { supabase } from './supabaseClient.js';
import bcrypt from 'bcryptjs';

export async function cambiarContrasena(correo, contrasenaActual, contrasenaNueva) {
  try {
    // 1. Buscar usuario
    const { data: user, error } = await supabase
      .from("usuarios")
      .select("*")
      .eq("correo", correo)
      .single();

    if (error || !user) {
      return { ok: false, error: "Usuario no encontrado" };
    }

    // 2. Verificar contraseña actual
    const valido = bcrypt.compareSync(contrasenaActual, user.contrasena);
    if (!valido) {
      return { ok: false, error: "La contraseña actual es incorrecta" };
    }

    // 3. Encriptar nueva contraseña
    const salt = bcrypt.genSaltSync(10);
    const hashNueva = bcrypt.hashSync(contrasenaNueva, salt);

    // 4. Actualizar en la base
    const { error: updateError } = await supabase
      .from("usuarios")
      .update({ contrasena: hashNueva })
      .eq("correo", correo);

    if (updateError) {
      return { ok: false, error: updateError.message };
    }

    return { ok: true, message: "Contraseña actualizada correctamente" };
  } catch (err) {
    return { ok: false, error: err.message };
  }
}
