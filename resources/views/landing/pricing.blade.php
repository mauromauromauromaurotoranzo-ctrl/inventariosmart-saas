<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Precios - InventarioSmart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('landing.index') }}" class="text-2xl font-bold text-indigo-600">InventarioSmart</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('landing.register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Crear cuenta gratis</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Pricing Section -->
    <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-base font-semibold text-indigo-600 tracking-wide uppercase">Precios</h2>
            <p class="mt-1 text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
                Planes para cada etapa de tu negocio
            </p>
            <p class="max-w-xl mt-5 mx-auto text-xl text-gray-500">
                Prueba gratuita de 14 días en todos los planes. Sin compromiso.
            </p>
        </div>

        <div class="mt-12 space-y-4 sm:mt-16 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-6 lg:max-w-4xl lg:mx-auto xl:max-w-none xl:grid-cols-3">
            <!-- Basic Plan -->
            <div class="border border-gray-200 rounded-lg shadow-sm divide-y divide-gray-200 bg-white">
                <div class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Básico</h3>
                    <p class="mt-4 text-sm text-gray-500">Perfecto para empezar</p>
                    <p class="mt-8">
                        <span class="text-4xl font-extrabold text-gray-900">$29</span>
                        <span class="text-base font-medium text-gray-500">/mes</span>
                    </p>
                    <a href="{{ route('landing.register') }}?plan=basic" class="mt-8 block w-full bg-indigo-50 border border-indigo-200 rounded-md py-2 text-sm font-semibold text-indigo-700 text-center hover:bg-indigo-100">
                        Empezar prueba gratis
                    </a>
                </div>
                <div class="pt-6 pb-8 px-6">
                    <h4 class="text-sm font-medium text-gray-900 tracking-wide uppercase">Incluye</h4>
                    <ul class="mt-6 space-y-4">
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Hasta 1,000 productos</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">1 sucursal</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">2 usuarios</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Soporte por email</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Pro Plan -->
            <div class="border-2 border-indigo-500 rounded-lg shadow-sm divide-y divide-gray-200 bg-white relative">
                <div class="absolute top-0 right-0 -mt-3 mr-3 bg-indigo-500 text-white px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide">
                    Más popular
                </div>
                <div class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Profesional</h3>
                    <p class="mt-4 text-sm text-gray-500">Para negocios en crecimiento</p>
                    <p class="mt-8">
                        <span class="text-4xl font-extrabold text-gray-900">$79</span>
                        <span class="text-base font-medium text-gray-500">/mes</span>
                    </p>
                    <a href="{{ route('landing.register') }}?plan=pro" class="mt-8 block w-full bg-indigo-600 border border-transparent rounded-md py-2 text-sm font-semibold text-white text-center hover:bg-indigo-700">
                        Empezar prueba gratis
                    </a>
                </div>
                <div class="pt-6 pb-8 px-6">
                    <h4 class="text-sm font-medium text-gray-900 tracking-wide uppercase">Incluye</h4>
                    <ul class="mt-6 space-y-4">
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Hasta 10,000 productos</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Hasta 5 sucursales</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">10 usuarios</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Soporte prioritario</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">API access</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Enterprise Plan -->
            <div class="border border-gray-200 rounded-lg shadow-sm divide-y divide-gray-200 bg-white">
                <div class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Empresarial</h3>
                    <p class="mt-4 text-sm text-gray-500">Para grandes operaciones</p>
                    <p class="mt-8">
                        <span class="text-4xl font-extrabold text-gray-900">$199</span>
                        <span class="text-base font-medium text-gray-500">/mes</span>
                    </p>
                    <a href="{{ route('landing.register') }}?plan=enterprise" class="mt-8 block w-full bg-indigo-50 border border-indigo-200 rounded-md py-2 text-sm font-semibold text-indigo-700 text-center hover:bg-indigo-100">
                        Contactar ventas
                    </a>
                </div>
                <div class="pt-6 pb-8 px-6">
                    <h4 class="text-sm font-medium text-gray-900 tracking-wide uppercase">Incluye</h4>
                    <ul class="mt-6 space-y-4">
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Productos ilimitados</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Sucursales ilimitadas</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Usuarios ilimitados</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">Soporte 24/7 dedicado</span>
                        </li>
                        <li class="flex">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="ml-3 text-sm text-gray-500">On-premise opcional</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
