@extends('layouts.app')

@section('title', 'Dashboard - InventarioSmart')
@section('page-title', 'Dashboard')

@section('content')
<div x-data="dashboardApp()" x-init="init()">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Ventas Hoy</p>
                    <p class="text-2xl font-bold" x-text="stats.ventasHoy"></p>
                    <p class="text-xs" :class="stats.cambioVentas >= 0 ? 'text-green-600' : 'text-red-600'">
                        <span x-text="(stats.cambioVentas >= 0 ? '+' : '') + stats.cambioVentas + '%'"></span> vs ayer
                    </p>
                </div>
            </div>
        </x-card>
        
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Ingresos Hoy</p>
                    <p class="text-2xl font-bold" x-text="'$' + stats.ingresosHoy.toLocaleString()"></p>
                    <p class="text-xs" :class="stats.cambioIngresos >= 0 ? 'text-green-600' : 'text-red-600'">
                        <span x-text="(stats.cambioIngresos >= 0 ? '+' : '') + stats.cambioIngresos + '%'"></span> vs ayer
                    </p>
                </div>
            </div>
        </x-card>
        
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Productos Bajo Stock</p>
                    <p class="text-2xl font-bold" x-text="stats.stockBajo"></p>
                    <a href="{{ route('productos.index') }}?stock=bajo" class="text-xs text-blue-600 hover:underline">Ver productos</a>
                </div>
            </div>
        </x-card>
        
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Nuevos Clientes</p>
                    <p class="text-2xl font-bold" x-text="stats.nuevosClientes"></p>
                    <p class="text-xs text-gray-500">Este mes</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Ventas Chart -->
        <x-card title="Ventas de los últimos 7 días">
            <canvas id="ventasChart" height="250"></canvas>
        </x-card>
        
        <!-- Productos más vendidos -->
        <x-card title="Productos más vendidos">
            <div class="space-y-3">
                <template x-for="(prod, index) in topProductos" :key="index">
                    <div class="flex items-center">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold mr-3" x-text="index + 1"></span>
                        <div class="flex-1">
                            <p class="font-medium text-sm" x-text="prod.nombre"></p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${prod.porcentaje}%`"></div>
                            </div>
                        </div>
                        <span class="ml-3 font-bold text-sm" x-text="prod.cantidad + ' uds'"></span>
                    </div>
                </template>
            </div>
        </x-card>
    </div>

    <!-- Recent Activity & Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Últimas Ventas -->
        <x-card title="Últimas Ventas" class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="venta in ultimasVentas" :key="venta.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm" x-text="'#' + venta.id"></td>
                                <td class="px-4 py-2 text-sm" x-text="venta.cliente?.nombre || 'Consumidor Final'"></td>
                                <td class="px-4 py-2 text-sm" x-text="venta.items_count + ' items'"></td>
                                <td class="px-4 py-2 text-sm font-bold" x-text="'$' + venta.total.toFixed(2)"></td>
                                <td class="px-4 py-2 text-sm text-gray-500" x-text="new Date(venta.created_at).toLocaleTimeString()"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('ventas.index') }}" class="text-blue-600 hover:underline text-sm">Ver todas las ventas →</a>
            </div>
        </x-card>
        
        <!-- Alertas -->
        <x-card title="Alertas">
            <div class="space-y-3">
                <template x-if="alertas.length === 0">
                    <p class="text-gray-500 text-center py-4">No hay alertas pendientes</p>
                </template>
                
                <template x-for="alerta in alertas" :key="alerta.id">
                    <div :class="{'p-3 rounded-lg border-l-4': true, 'bg-red-50 border-red-400': alerta.tipo === 'error', 'bg-yellow-50 border-yellow-400': alerta.tipo === 'warning', 'bg-blue-50 border-blue-400': alerta.tipo === 'info'}">
                        <div class="flex items-start">
                            <div class="flex-1">
                                <p class="font-medium text-sm" :class="{'text-red-800': alerta.tipo === 'error', 'text-yellow-800': alerta.tipo === 'warning', 'text-blue-800': alerta.tipo === 'info'}" x-text="alerta.titulo"></p>
                                <p class="text-xs mt-1" :class="{'text-red-600': alerta.tipo === 'error', 'text-yellow-600': alerta.tipo === 'warning', 'text-blue-600': alerta.tipo === 'info'}" x-text="alerta.mensaje"></p>
                            </div>
                            <button @click="marcarLeida(alerta.id)" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </x-card>
    </div>

    <!-- Quick Actions -->
    <div class="fixed bottom-6 right-6 flex flex-col space-y-2">
        <a href="{{ route('ventas.pos') }}" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-lg transition flex items-center justify-center" title="Nueva Venta">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
    </div>
</div>

@push('scripts')
<script>
function dashboardApp() {
    return {
        stats: {
            ventasHoy: 0,
            ingresosHoy: 0,
            cambioVentas: 0,
            cambioIngresos: 0,
            stockBajo: 0,
            nuevosClientes: 0
        },
        topProductos: [],
        ultimasVentas: [],
        alertas: [],
        ventasChart: null,
        
        init() {
            this.fetchStats();
            this.fetchTopProductos();
            this.fetchUltimasVentas();
            this.fetchAlertas();
            this.initChart();
        },
        
        async fetchStats() {
            try {
                const response = await axios.get('/api/dashboard/stats');
                this.stats = response.data;
            } catch (error) {
                console.error('Error cargando stats:', error);
            }
        },
        
        async fetchTopProductos() {
            try {
                const response = await axios.get('/api/dashboard/top-productos');
                this.topProductos = response.data;
            } catch (error) {
                console.error('Error cargando top productos:', error);
            }
        },
        
        async fetchUltimasVentas() {
            try {
                const response = await axios.get('/api/ventas?limit=5');
                this.ultimasVentas = response.data.data || [];
            } catch (error) {
                console.error('Error cargando últimas ventas:', error);
            }
        },
        
        async fetchAlertas() {
            // Simulación de alertas
            this.alertas = [
                { id: 1, tipo: 'warning', titulo: 'Stock bajo', mensaje: '5 productos están por debajo del mínimo' },
                { id: 2, tipo: 'info', titulo: 'Pago pendiente', mensaje: 'Tu suscripción vence en 5 días' }
            ];
        },
        
        initChart() {
            const ctx = document.getElementById('ventasChart').getContext('2d');
            
            // Datos de ejemplo - reemplazar con datos reales de la API
            const labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            const data = [1200, 1900, 1500, 2200, 2800, 3500, 3100];
            
            this.ventasChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ventas ($)',
                        data: data,
                        borderColor: 'rgb(37, 99, 235)',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        },
        
        marcarLeida(id) {
            this.alertas = this.alertas.filter(a => a.id !== id);
        }
    }
}
</script>
@endpush
@endsection
