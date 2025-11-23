import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm';

const supabaseUrl = 'https://wsrfrkhlqtlocnnssdsf.supabase.co';
const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6IndzcmZya2hscXRsb2NubnNzZHNmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc3MTY3NDQsImV4cCI6MjA2MzI5Mjc0NH0.DQ6NAA9PakEc4Ln_RpUOk3Llfdyde-gqzQNHQuYT1Qc'; // Copia esto desde el panel de Supabase -> Settings -> API

export const supabase = createClient(supabaseUrl, supabaseKey);
