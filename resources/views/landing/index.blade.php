<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InventarioSmart - Control de Inventario Inteligente</title>
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
                    <span class="text-2xl font-bold text-indigo-600">InventarioSmart</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#features" class="text-gray-600 hover:text-gray-900">Características</a>
                    <a href="#rubros" class="text-gray-600 hover:text-gray-900">Rubros</a>
                    <a href="{{ route('landing.pricing') }}" class="text-gray-600 hover:text-gray-900">Precios</a>
                    <a href="{{ route('landing.register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Crear cuenta gratis</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block xl:inline">Controla tu inventario</span>
                            <span class="block text-indigo-600 xl:inline">como un profesional</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            Sistema de gestión de inventario adaptado a tu rubro. Prueba gratuita de 14 días. Sin tarjeta de crédito.
                        </p>
                        <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                            <div class="rounded-md shadow">
                                <a href="{{ route('landing.register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                                    Empezar gratis
                                </a>
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-3">
                                <a href="#demo" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10">
                                    Ver demo
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-indigo-50 flex items-center justify-center">
            <div class="text-9xl">📦</div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-base text-indigo-600 font-semibold tracking-wide uppercase">Características</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Todo lo que necesitas para tu negocio
                </p>
            </div>

            <div class="mt-10">
                <dl class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white text-xl">📊</div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Control de stock en tiempo real</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Seguimiento preciso de tu inventario con alertas de stock bajo y reportes detallados.
                        </dd>
                    </div>

                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white text-xl">💰</div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Ventas y caja integrada</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Gestiona ventas, múltiples formas de pago y el flujo de caja desde un solo lugar.
                        </dd>
                    </div>

                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white text-xl">👥</div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Clientes y proveedores</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Administra cuentas corrientes, deudas y el historial completo de tus contactos.
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <!-- Rubros Section -->
    <section id="rubros" class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">Adaptado a tu rubro</h2>
                <p class="mt-4 text-lg text-gray-500">Funcionalidades específicas para cada tipo de negocio</p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="text-4xl">🏪</div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">Retail</h3>
                                <p class="text-sm text-gray-500">Escáner, promociones, multi-sucursal</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="text-4xl">💊</div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">Farmacia</h3>
                                <p class="text-sm text-gray-500">Lotes, vencimientos, obras sociales</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="text-4xl">🍽️</div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">Restaurante</h3>
                                <p class="text-sm text-gray-500">Recetas, mermas, insumos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="text-4xl">🔧</div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">Ferretería</h3>
                                <p class="text-sm text-gray-500">Categorías profundas, equivalentes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-indigo-700">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
            <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                <span class="block">¿Listo para empezar?</span>
                <span class="block text-indigo-200">Prueba gratuita de 14 días.</span>
            </h2>
            <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                <div class="inline-flex rounded-md shadow">
                    <a href="{{ route('landing.register') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50">
                        Crear cuenta gratis
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-400">
                &copy; {{ date('Y') }} InventarioSmart. Todos los derechos reservados.
            </p>
        </div>
    </footer>
</body>
</html>
