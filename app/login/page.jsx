// app/login/page.jsx
'use client';
import { supabase } from '@/lib/supabaseClient';
import { useState } from 'react';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const signIn = async () => {
    const { error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) alert(error.message);
    else window.location.href = '/';
  };

  const signUp = async () => {
    const { error } = await supabase.auth.signUp({ email, password });
    if (error) alert(error.message);
    else alert('Revisa tu correo para confirmar.');
  };

  return (
    <div style={{ padding: 24 }}>
      <h2>Acceso</h2>
      <input
        type="email"
        placeholder="Correo"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
      />
      <br />
      <input
        type="password"
        placeholder="Contraseña"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
      />
      <br />
      <button onClick={signIn}>Entrar</button>
      <button onClick={signUp} style={{ marginLeft: 12 }}>Registrarse</button>
    </div>
  );
}
