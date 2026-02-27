<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'InventarioSmart')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Print styles for tickets */
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100">
    @auth
        <div class="min-h-screen flex" x-data="{ 
            sidebarOpen: window.innerWidth >= 1024,
            userMenuOpen: false,
            notificationsOpen: false 
        }" x-init="window.addEventListener('resize', () => { 
            if (window.innerWidth >= 1024) sidebarOpen = true; 
            else sidebarOpen = false; 
        })">
            <!-- Sidebar -->
            <aside 
                x-show="sidebarOpen" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex flex-col shadow-xl"
                x-cloak
            >
                <!-- Logo -->
                <div class="flex items-center h-16 px-6 border-b border-slate-800">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold">InventarioSmart</span>
                    </div>
                    <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4 space-y-1">
                    <div class="px-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Principal</div>
                    
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('productos.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('productos.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Productos
                    </a>
                    
                    <a href="{{ route('categorias.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('categorias.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Categorías
                    </a>
                    
                    <div class="px-4 mt-6 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Ventas</div>
                    
                    <a href="{{ route('ventas.pos') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('ventas.pos') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Punto de Venta
                        <span class="ml-auto bg-green-500 text-white text-xs px-2 py-0.5 rounded-full">POS</span>
                    </a>
                    
                    <a href="{{ route('ventas.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('ventas.index') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Historial de Ventas
                    </a>
                    
                    <a href="{{ route('cajas.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('cajas.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Cajas
                    </a>
                    
                    <div class="px-4 mt-6 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Contactos</div>
                    
                    <a href="{{ route('clientes.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('clientes.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Clientes
                    </a>
                    
                    <a href="{{ route('proveedores.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('proveedores.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                        Proveedores
                    </a>
                    
                    <div class="px-4 mt-6 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Finanzas</div>
                    
                    <a href="{{ route('cuentas-corrientes.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('cuentas-corrientes.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Cuentas Corrientes
                    </a>
                    
                    <a href="{{ route('cheques.index') }}" class="flex items-center px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('cheques.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Cheques
                    </a>
                </nav>
                
                <!-- User Section -->
                <div class="border-t border-slate-800 p-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center w-full text-left">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-lg shadow-lg py-1 z-50">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mi Perfil</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Configuración</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Cerrar Sesión</button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Overlay para móvil -->
            <div x-show="sidebarOpen" 
                 @click="sidebarOpen = false"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden"
                 x-cloak>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Header -->
                <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-30">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Quick Actions -->
                        <a href="{{ route('ventas.pos') }}" class="hidden sm:flex items-center px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nueva Venta
                        </a>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                    <!-- Alerts -->
                    @if(session('success'))
                        <x-alert type="success" dismissible class="mb-4">
                            {{ session('success') }}
                        </x-alert>
                    @endif

                    @if(session('error'))
                        <x-alert type="error" dismissible class="mb-4">
                            {{ session('error') }}
                        </x-alert>
                    @endif

                    @if(session('warning'))
                        <x-alert type="warning" dismissible class="mb-4">
                            {{ session('warning') }}
                        </x-alert>
                    @endif

                    @if ($errors->any())
                        <x-alert type="error" class="mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
        
        <!-- Toast Notifications -->
        <x-toast position="top-right" />
    @else
        @yield('content')
    @endauth

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configurar Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.withCredentials = true;
        
        // Interceptor para manejar errores globalmente
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    window.location.href = '/login';
                }
                return Promise.reject(error);
            }
        );
    </script>
    
    @stack('scripts')
</body>
</html>