// /api/dashboard_ciudadano.js
import { supabase } from '../lib/db.js';

export default async function handler(req, res) {
  if (req.method !== 'GET') {
    return res.status(405).json({ error: 'Método no permitido' });
  }

  // En PHP usabas $_SESSION; aquí deberías usar un token o ID enviado desde el frontend
  const usuario_id = req.query.usuario_id;
  const nombre_usuario = req.query.nombre || 'Usuario';

  if (!usuario_id) {
    return res.status(401).json({ error: 'Usuario no autenticado' });
  }

  try {
    // 1. Total de Trámites
    const { count: total_tramites, error: err1 } = await supabase
      .from('solicitudes')
      .select('*', { count: 'exact', head: true })
      .eq('usuario_id', usuario_id);
    if (err1) throw err1;

    // 2. Aprobados
    const { count: total_aprobados, error: err2 } = await supabase
      .from('solicitudes')
      .select('*', { count: 'exact', head: true })
      .eq('usuario_id', usuario_id)
      .in('estado_tramite', ['activo', 'listo_activacion']);
    if (err2) throw err2;

    // 3. En Revisión
    const { count: total_revision, error: err3 } = await supabase
      .from('solicitudes')
      .select('*', { count: 'exact', head: true })
      .eq('usuario_id', usuario_id)
      .eq('estado_tramite', 'registro_inicial');
    if (err3) throw err3;

    // 4. Pendientes
    const { count: total_pendientes, error: err4 } = await supabase
      .from('solicitudes')
      .select('*', { count: 'exact', head: true })
      .eq('usuario_id', usuario_id)
      .eq('estado_tramite', 'pendiente_aclaracion');
    if (err4) throw err4;

    return res.status(200).json({
      usuario: nombre_usuario,
      total_tramites,
      total_aprobados,
      total_revision,
      total_pendientes
    });

  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
}
