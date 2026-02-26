<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tu prueba expira pronto</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f59e0b; color: white; padding: 30px; text-align: center; }
        .content { background: #f9fafb; padding: 30px; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .urgent { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Tu prueba gratuita expira pronto</h1>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $tenantName }}</strong>,</p>
            
            <p class="urgent">Tu período de prueba gratuita expira en {{ $daysRemaining }} días.</p>
            
            <p>No pierdas el acceso a tu inventario y todas las funcionalidades que estás usando. Actualiza a un plan pago y sigue gestionando tu negocio sin interrupciones.</p>
            
            <h3>✨ Beneficios de actualizar:</h3>
            <ul>
                <li>Acceso ilimitado a todas las funcionalidades</li>
                <li>Soporte técnico prioritario</li>
                <li>Actualizaciones constantes</li>
                <li>Tus datos siempre seguros</li>
            </ul>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $upgradeUrl }}" class="button">Ver planes y actualizar</a>
            </p>
            
            <p>¿Tienes preguntas? Responde a este email y te ayudamos.</p>
            
            <p>Saludos,<br>
            El equipo de InventarioSmart</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} InventarioSmart. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
