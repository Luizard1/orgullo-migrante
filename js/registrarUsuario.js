import { supabase } from './supabaseClient.js';
import bcrypt from 'bcryptjs';

export async function registrarUsuario(nombre, correo, contrasena, rol = "usuario") {
  try {
    // Encriptar contraseña en backend JS
    const salt = bcrypt.genSaltSync(10);
    const hash = bcrypt.hashSync(contrasena, salt);

    const { error } = await supabase.from("usuarios").insert({
      nombre,
      correo,
      contrasena: hash,
      rol
    });

    if (error) {
      console.error("Error al registrar:", error.message);
      return { ok: false, error: error.message };
    }
    return { ok: true, message: "Usuario registrado correctamente" };
  } catch (err) {
    console.error("Error inesperado:", err);
    return { ok: false, error: err.message };
  }
}
