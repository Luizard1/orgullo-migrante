// /api/revision.js
import { supabase } from '../../lib/db.js';

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Método no permitido' });
  }

  const { solicitud_id, accion, notas } = req.body;

  if (!solicitud_id || solicitud_id <= 0) {
    return res.status(400).json({ error: 'Solicitud inválida' });
  }

  let nuevo_estado = '';
  let mensaje_alerta = '';

  switch (accion) {
    case 'aprobar':
      nuevo_estado = 'listo_activacion';
      mensaje_alerta = 'Solicitud APROBADA correctamente.';
      break;

    case 'aclaracion':
      if (!notas || notas.trim() === '') {
        return res.status(400).json({ error: 'Debes escribir una nota explicando qué aclaración necesitas.' });
      }
      nuevo_estado = 'pendiente_aclaracion';
      mensaje_alerta = 'Se ha solicitado una aclaración al ciudadano.';
      break;

    case 'devolver':
      if (!notas || notas.trim() === '') {
        return res.status(400).json({ error: 'Para devolver un trámite debes explicar la razón en las notas.' });
      }
      nuevo_estado = 'devuelto';
      mensaje_alerta = 'El trámite ha sido marcado como DEVUELTO.';
      break;

    default:
      return res.status(400).json({ error: 'Acción no reconocida.' });
  }

  try {
    const { error: updateError, count } = await supabase
      .from('solicitudes')
      .update({
        estado_tramite: nuevo_estado,
        fecha_ultimo_cambio: new Date().toISOString(),
        ultimos_comentarios: notas || ''
      })
      .eq('id', solicitud_id);

    if (updateError) throw updateError;

    return res.status(200).json({ message: mensaje_alerta });
  } catch (error) {
    return res.status(500).json({ error: 'Error al actualizar: ' + error.message });
  }
}
