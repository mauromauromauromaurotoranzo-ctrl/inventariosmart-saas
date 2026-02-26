<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - InventarioSmart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">¡Bienvenido a InventarioSmart!</h1>
            <p class="text-gray-600 mb-6">Tu cuenta ha sido creada exitosamente. Vamos a configurar tu negocio en unos simples pasos.</p>
            
            <div class="space-y-4">
                <div class="flex items-center justify-center space-x-2">
                    <span class="w-3 h-3 bg-indigo-600 rounded-full"></span>
                    <span class="w-3 h-3 bg-gray-300 rounded-full"></span>
                    <span class="w-3 h-3 bg-gray-300 rounded-full"></span>
                    <span class="w-3 h-3 bg-gray-300 rounded-full"></span>
                </div>
                <p class="text-sm text-gray-500">Paso 1 de 4</p>
            </div>

            <a href="{{ route('onboarding.step', ['tenant' => $tenantSlug, 'step' => 1]) }}" 
               class="mt-6 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Comenzar configuración →
            </a>
        </div>
    </div>
</body>
</html>
