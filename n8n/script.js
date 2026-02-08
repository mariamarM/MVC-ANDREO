// Validar token de seguridad
const receivedToken = $request.headers['x-shared-token'];
const expectedToken = '5ba0f659-d18b-4edd-82b6-ed115eafa3c9'; // Mismo que en tu PHP

if (!receivedToken || receivedToken !== expectedToken) {
  // Crear respuesta de error
  const errorItem = {
    json: {
      success: false,
      error: 'Token inválido',
      statusCode: 401
    },
    pairedItem: {
      item: 0
    }
  };
  
  // Lanzar error para que n8n lo maneje
  throw new Error('Token de seguridad inválido');
}

// Obtener datos del webhook
const webhookData = $input.first().json;

// Verificar estructura de datos
if (!webhookData || !webhookData.data) {
  throw new Error('Datos del webhook inválidos');
}

const eventData = webhookData.data;

// Crear contenido para el email
const emailContent = `
🎉 ¡Nueva Publicación Creada! 🎉

- **Canción:** ${eventData.song_title}
- **ID de Review:** ${eventData.review_id}
- **Calificación:** ⭐ ${eventData.rating}/5
- **Autor:** ${eventData.user_email}
- **Fecha:** ${eventData.timestamp}

📝 **Contenido:**
${eventData.content || 'Sin contenido'}

🔗 **Enlace:** ${eventData.url}

---

Gracias por contribuir a nuestra comunidad! ✨

*Este es un mensaje automático, por favor no responder.*
`;

// También puedes crear un HTML más bonito
const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px; }
        .content { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
        .button { display: inline-block; background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Nueva Publicación! 🎉</h1>
        </div>
        <div class="content">
            <h2>${eventData.title}</h2>
            <p><strong>Autor:</strong> ${eventData.user_email}</p>
            <p><strong>Fecha:</strong> ${eventData.timestamp}</p>
            <p><strong>ID de publicación:</strong> ${eventData.post_id}</p>
            
            <h3>Contenido:</h3>
            <p>${eventData.content || 'Sin contenido'}</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="${eventData.url}" class="button">Ver Publicación</a>
            </div>
        </div>
        <div class="footer">
            <p>Este es un mensaje automático. Por favor, no responder.</p>
            <p>© ${new Date().getFullYear()} Tu Aplicación MVC</p>
        </div>
    </div>
</body>
</html>
`;

// Retornar datos para el siguiente nodo
return [
  {
    json: {
      // Datos originales
      ...eventData,
      
      // Contenidos para email
      email_subject: `Nueva publicación: ${eventData.title}`,
      email_to: eventData.user_email, // O un email fijo como 'admin@tudominio.com'
      email_text: emailContent,
      email_html: htmlContent,
      
      // Metadatos
      processed_at: new Date().toISOString(),
      workflow: 'post_created_notification'
    }
  }
];