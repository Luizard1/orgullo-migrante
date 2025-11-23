// Asegúrate de enlazar este archivo como <script type="module" src="enviarCorreo.js"></script>

//import emailjs from 'https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.esm.min.js';


// Espera a que el DOM cargue
document.addEventListener('DOMContentLoaded', () => {
  // Inicializa EmailJS con tu User ID (reemplaza por el tuyo real)
  emailjs.init('T7c70_XmqKy2oBRD6'); // <-- reemplaza con tu User ID de EmailJS

  const form = document.querySelector('.footer-form');
  const emailInput = document.getElementById('email');

  if (!form || !emailInput) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = emailInput.value.trim();
    if (!email) {
      alert('Por favor, introduce una dirección de correo válida.');
      return;
    }

    try {
      const response = await emailjs.send('service_a3s30j6', 'template_7t8t9be', {
        to_email: email,
        name: 'Suscriptor Draco',
        time: new Date().toLocaleString(),
        message: 'Gracias por suscribirte a nuestro boletín de DRACO.',
      });

      console.log('Correo enviado con éxito', response);
      alert('¡Gracias por suscribirte! Hemos enviado un correo de confirmación.');
      form.reset();
    } catch (error) {
      console.error('Error al enviar el correo:', error);
      alert('Hubo un problema al enviar el correo. Inténtalo de nuevo más tarde.');
    }
  });
});
