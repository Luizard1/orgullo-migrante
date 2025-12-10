// /api/solicitud.js
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

  const form = formidable({ multiples: true });

  form.parse(req, async (err, fields, files) => {
    if (err) {
      return res.status(400).json({ error: 'Error al procesar el formulario' });
    }

    const usuario_id = parseInt(fields.usuario_id, 10);
    const tipo_tramite = fields.tipo_tramite || 'registro_inicial';

    if (!usuario_id) {
      return res.status(401).json({ error: 'Usuario no autenticado' });
    }

    try {
      // 1. Crear nueva solicitud
      const { data: solicitud, error: solicitudError } = await supabase
        .from('solicitudes')
        .insert({
          usuario_id,
          estado_tramite: 'registro_inicial',
          fecha_creacion: new Date().toISOString(),
          fecha_ultimo_cambio: new Date().toISOString(),
          tipo_tramite
        })
        .select('id')
        .single();

      if (solicitudError) throw solicitudError;
      const solicitud_id = solicitud.id;

      // 2. Función para subir archivo y registrar documento
      async function guardarDocumento(file, tipoDoc) {
        if (!file) return;

        const extension = file.originalFilename.split('.').pop().toLowerCase();
        const permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!permitidas.includes(extension)) return;

        const nombre_archivo = `${tipoDoc}_${solicitud_id}_${Date.now()}.${extension}`;
        const fileBuffer = fs.readFileSync(file.filepath);

        const { error: uploadError } = await supabase.storage
          .from('documentos')
          .upload(nombre_archivo, fileBuffer, { contentType: file.mimetype });
        if (uploadError) throw uploadError;

        const { data: publicUrlData } = supabase.storage
          .from('documentos')
          .getPublicUrl(nombre_archivo);
        const publicUrl = publicUrlData.publicUrl;

        const { error: insertError } = await supabase
          .from('documentos_iniciales')
          .insert({
            solicitud_id,
            tipo_documento: tipoDoc,
            ruta_archivo: publicUrl
          });
        if (insertError) throw insertError;
      }

      // 3. Guardar cada archivo
      await guardarDocumento(files.archivo_curp, 'CURP');
      await guardarDocumento(files.archivo_rfc, 'RFC');
      await guardarDocumento(files.archivo_domicilio, 'ComprobanteDomicilio');
      await guardarDocumento(files.archivo_evidencia, 'EvidenciaAdicional');

      return res.status(200).json({
        message: `Solicitud enviada correctamente. Folio #${solicitud_id}`,
        solicitud_id
      });
    } catch (error) {
      return res.status(500).json({ error: 'Error en la base de datos: ' + error.message });
    }
  });
}
