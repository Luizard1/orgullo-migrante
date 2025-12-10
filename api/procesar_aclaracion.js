// /api/procesar_aclaracion.js
import { supabase } from '../../lib/db.js';
import formidable from 'formidable';
import fs from 'fs';

export const config = {
  api: {
    bodyParser: false, // necesario para manejar archivos
  },
};

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Método no permitido' });
  }

  const form = formidable({ multiples: false });

  form.parse(req, async (err, fields, files) => {
    if (err) {
      return res.status(400).json({ error: 'Error al procesar el formulario' });
    }

    const solicitud_id = parseInt(fields.solicitud_id, 10);
    if (!solicitud_id || solicitud_id <= 0) {
      return res.status(400).json({ error: 'Solicitud inválida' });
    }

    const archivo = files.archivo_aclaracion;
    if (!archivo) {
      return res.status(400).json({ error: 'Por favor selecciona un archivo válido' });
    }

    const extension = archivo.originalFilename.split('.').pop().toLowerCase();
    const permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!permitidas.includes(extension)) {
      return res.status(400).json({ error: 'Formato no permitido. Usa PDF, JPG, JPEG o PNG.' });
    }

    // Nombre único para el archivo
    const nombre_archivo = `aclaracion_${solicitud_id}_${Date.now()}.${extension}`;

    try {
      // Subir archivo a Supabase Storage (bucket "aclaraciones")
      const fileBuffer = fs.readFileSync(archivo.filepath);
      const { error: uploadError } = await supabase.storage
        .from('aclaraciones')
        .upload(nombre_archivo, fileBuffer, {
          contentType: archivo.mimetype,
        });

      if (uploadError) throw uploadError;

      // Obtener URL pública
      const { data: publicUrlData } = supabase.storage
        .from('aclaraciones')
        .getPublicUrl(nombre_archivo);
      const publicUrl = publicUrlData.publicUrl;

      // Actualizar la solicitud en la BD
      const { error: updateError } = await supabase
        .from('solicitudes')
        .update({
          documento_aclaracion: publicUrl,
          estado_tramite: 'registro_inicial',
          ultimos_comentarios: 'El ciudadano ha enviado la evidencia solicitada. Pendiente de nueva revisión.',
          fecha_ultimo_cambio: new Date().toISOString(),
        })
        .eq('id', solicitud_id);

      if (updateError) throw updateError;

      return res.status(200).json({
        message: '¡Archivo enviado correctamente! Tu solicitud ha vuelto a revisión.',
        url: publicUrl,
      });
    } catch (error) {
      return res.status(500).json({ error: 'Error en la base de datos: ' + error.message });
    }
  });
}
