<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso - InventarioSmart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">¡Pago Exitoso!</h1>
            <p class="text-gray-600 mb-6">
                Tu suscripción ha sido activada correctamente. Ya podés empezar a usar todas las funciones de InventarioSmart.
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>Próximo paso:</strong> Completá la configuración inicial de tu negocio.
                </p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('onboarding.wizard') }}" class="block w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition">
                    Ir al Onboarding
                </a>
                <a href="{{ route('dashboard') }}" class="block w-full px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
                    Ir al Dashboard
                </a>
            </div>

            <p class="mt-6 text-sm text-gray-500">
                ¿Tenés preguntas? Contactanos en <a href="mailto:soporte@inventariosmart.app" class="text-blue-600 hover:underline">soporte@inventariosmart.app</a>
            </p>
        </div>
    </div>
</body>
</html>
