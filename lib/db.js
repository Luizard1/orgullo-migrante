// lib/db.js
import { createClient } from '@supabase/supabase-js';

// Usa variables de entorno en Vercel para no exponer credenciales
const supabaseUrl = process.env.SUPABASE_URL;   // ejemplo: https://njfgquifcdhoakaeqrqz.supabase.co
const supabaseKey = process.env.SUPABASE_KEY;   // tu API key (service_role o anon)

if (!supabaseUrl || !supabaseKey) {
  throw new Error("Faltan variables de entorno SUPABASE_URL o SUPABASE_KEY");
}

// Crear cliente Supabase
export const supabase = createClient(supabaseUrl, supabaseKey);
