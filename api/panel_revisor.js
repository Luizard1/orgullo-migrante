// /api/panel_revisor.js
import { supabase } from '../lib/db.js';

export default async function handler(req, res) {
  if (req.method !== 'GET') {
    return res.status(405).json({ error: 'Método no permitido' });
  }

  const nombre_revisor = req.query.nombre || 'Revisor';

  try {
    // Totales
    const { count: total_solicitudes, error: err1 } = await supabase
      .from('solicitudes')
      .select('*', { count: 'exact', head: true });
    if (err1) throw err1;

    // En Revisión
    const { count: total_revision, error: err2 } = await supabase
      .from('solicitudes')
      .select('*', { count: 'exact', head: true })
      .eq('estado_tramite', 'registro_inicial');
    if (err2) throw err2;

    // Pendientes (Aclaraciones)
    const { count: total_pendientes, error: err3 } = await supabase
      .from('solicitudes')
      .select('*', { count: 'exact', head: true })
      .eq('estado_tramite', 'pendiente_aclaracion');
    if (err3) throw err3;

    // Revisadas Hoy
    const hoy = new Date().toISOString().split('T')[0];
    const { count: total_hoy, error: err4 } = await supabase
      .from('solicitudes')
      .select('*', { count: 'exact', head: true })
      .neq('estado_tramite', 'registro_inicial')
      .filter('fecha_ultimo_cambio', 'eq', hoy);
    if (err4) throw err4;

    // Solicitudes pendientes
    const { data: solicitudes_pendientes, error: err5 } = await supabase
      .from('solicitudes')
      .select('id, fecha_creacion, estado_tramite, usuarios(nombre_completo)')
      .in('estado_tramite', ['registro_inicial', 'pendiente_aclaracion'])
      .order('fecha_creacion', { ascending: false });
    if (err5) throw err5;

    return res.status(200).json({
      revisor: nombre_revisor,
      total_solicitudes,
      total_revision,
      total_pendientes,
      total_hoy,
      solicitudes_pendientes
    });

  } catch (error) {
    return res.status(500).json({ error: error.message });
  }
}
