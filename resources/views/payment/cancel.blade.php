<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Cancelado - InventarioSmart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Cancel Icon -->
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Pago Cancelado</h1>
            <p class="text-gray-600 mb-6">
                No te preocupes, no se realizó ningún cargo. Podés intentarlo de nuevo cuando quieras.
            </p>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-700">
                    Recordá que tenés <strong>14 días de prueba gratuita</strong> para probar todas las funciones.
                </p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('landing.pricing') }}" class="block w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition">
                    Ver Planes Disponibles
                </a>
                <a href="{{ route('dashboard') }}" class="block w-full px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
                    Continuar con Prueba Gratuita
                </a>
            </div>

            <p class="mt-6 text-sm text-gray-500">
                ¿Necesitás ayuda? Contactanos en <a href="mailto:soporte@inventariosmart.app" class="text-blue-600 hover:underline">soporte@inventariosmart.app</a>
            </p>
        </div>
    </div>
</body>
</html>
