// /api/login.js
import { supabase } from '../lib/db.js';
import bcrypt from 'bcryptjs';

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Método no permitido' });
  }

  const { email, password } = req.body;

  if (!email || !password) {
    return res.status(400).json({ error: 'Email y contraseña son obligatorios' });
  }

  try {
    // Buscar usuario por email
    const { data: usuarios, error } = await supabase
      .from('usuarios')
      .select('id, nombre_completo, password_hash, rol')
      .eq('email', email)
      .limit(1);

    if (error) throw error;

    const usuario = usuarios && usuarios.length > 0 ? usuarios[0] : null;

    if (!usuario) {
      return res.status(404).json({ error: 'El usuario no existe' });
    }

    // Verificar contraseña
    const validPassword = await bcrypt.compare(password, usuario.password_hash);

    if (!validPassword) {
      return res.status(401).json({ error: 'Contraseña incorrecta' });
    }

    // Devolver datos del usuario (el frontend decide la redirección)
    return res.status(200).json({
      usuario_id: usuario.id,
      nombre: usuario.nombre_completo,
      rol: usuario.rol
    });

  } catch (err) {
    return res.status(500).json({ error: 'Error en la consulta: ' + err.message });
  }
}
