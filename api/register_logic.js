// /api/register.js
import { supabase } from '../lib/db.js';
import bcrypt from 'bcryptjs';

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Método no permitido' });
  }

  const { fullname, email, password } = req.body;

  // Validar datos básicos
  if (!fullname || !email || !password) {
    return res.status(400).json({ error: 'Todos los campos son obligatorios.' });
  }

  try {
    // Encriptar contraseña
    const password_hash = await bcrypt.hash(password, 10);
    
    // Insertar usuario en Supabase
    const { error } = await supabase
      .from('usuarios')
      .insert({
        nombre_completo: fullname,
        email,
        password_hash,
        rol: 'ciudadano',
        fecha_registro: new Date().toISOString()
      });

    if (error) {
      // Manejo de errores (ejemplo: email duplicado)
      if (error.message.includes('usuarios_email_key')) {
        return res.status(400).json({ error: 'El correo ya está registrado. Usa otro.' });
      }
      return res.status(500).json({ error: 'Error en la base de datos: ' + error.message });
    }

    return res.status(200).json({ message: 'Registro exitoso. Ahora inicia sesión.' });
  } catch (err) {
    return res.status(500).json({ error: 'Error interno: ' + err.message });
  }
}
