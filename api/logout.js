// /api/logout.js
export default async function handler(req, res) {
  if (req.method !== 'POST' && req.method !== 'GET') {
    return res.status(405).json({ error: 'Método no permitido' });
  }

  // Aquí no hay sesiones de PHP, simplemente devolvemos un mensaje
  return res.status(200).json({ message: 'Sesión cerrada. Redirigir al login.' });
}
