@extends('layouts.app')

@section('title', 'Punto de Venta - InventarioSmart')
@section('page-title', 'Punto de Venta')

@section('content')
<div x-data="posApp()" x-init="init()" class="h-[calc(100vh-8rem)]">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">
        <!-- Panel Izquierdo: Productos -->
        <div class="lg:col-span-2 flex flex-col space-y-4">
            <!-- Búsqueda y Categorías -->
            <x-card>
                <div class="space-y-4">
                    <!-- Buscador -->
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="search"
                            @keydown.enter="buscarProducto()"
                            placeholder="Buscar producto por código o nombre..."
                            class="w-full pl-10 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            x-ref="searchInput"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button @click="buscarProducto()" class="absolute inset-y-0 right-0 px-4 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700">
                            Buscar
                        </button>
                    </div>
                    
                    <!-- Categorías rápidas -->
                    <div class="flex flex-wrap gap-2">
                        <button @click="categoriaActiva = ''" :class="{'bg-blue-600 text-white': categoriaActiva === '', 'bg-gray-200 text-gray-700': categoriaActiva !== ''}" class="px-3 py-1 rounded-full text-sm font-medium transition">
                            Todos
                        </button>
                        <template x-for="cat in categorias" :key="cat.id">
                            <button @click="categoriaActiva = cat.id" :class="{'bg-blue-600 text-white': categoriaActiva === cat.id, 'bg-gray-200 text-gray-700': categoriaActiva !== cat.id}" class="px-3 py-1 rounded-full text-sm font-medium transition" x-text="cat.nombre"></button>
                        </template>
                    </div>
                </div>
            </x-card>
            
            <!-- Grid de Productos -->
            <x-card class="flex-1 overflow-hidden">
                <div class="h-full overflow-y-auto">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 p-2">
                        <template x-if="loading">
                            <div class="col-span-full flex justify-center py-8">
                                <x-loading size="lg" />
                            </div>
                        </template>
                        
                        <template x-for="producto in productosFiltrados" :key="producto.id">
                            <div @click="agregarProducto(producto)" class="bg-white border rounded-lg p-3 cursor-pointer hover:shadow-md hover:border-blue-500 transition group">
                                <div class="aspect-square bg-gray-100 rounded-lg mb-2 flex items-center justify-center">
                                    <span class="text-4xl">📦</span>
                                </div>
                                <h4 class="font-medium text-gray-900 text-sm truncate" x-text="producto.nombre"></h4>
                                <p class="text-xs text-gray-500" x-text="producto.codigo"></p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="font-bold text-blue-600" x-text="'$' + producto.precio_venta"></span>
                                    <span :class="{'text-xs px-2 py-0.5 rounded-full': true, 'bg-green-100 text-green-800': producto.stock > 0, 'bg-red-100 text-red-800': producto.stock === 0}" x-text="producto.stock + ' disp'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </x-card>
        </div>
        
        <!-- Panel Derecho: Carrito -->
        <div class="flex flex-col space-y-4">
            <!-- Info Cliente -->
            <x-card>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-medium text-gray-900">Cliente</h3>
                    <button @click="buscarCliente()" class="text-blue-600 text-sm hover:underline">Cambiar</button>
                </div>
                <div x-show="!cliente" class="text-gray-500 text-sm">
                    Consumidor Final
                </div>
                <div x-show="cliente">
                    <p class="font-medium" x-text="cliente?.nombre"></p>
                    <p class="text-sm text-gray-500" x-text="cliente?.telefono"></p>
                </div>
            </x-card>
            
            <!-- Items del Carrito -->
            <x-card class="flex-1 overflow-hidden">
                <h3 class="font-medium text-gray-900 mb-3">Items (<span x-text="items.length"></span>)</h3>
                
                <div class="overflow-y-auto max-h-[calc(100vh-28rem)]">
                    <template x-if="items.length === 0">
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <p class="mt-2">Agregá productos al carrito</p>
                        </div>
                    </template>
                    
                    <div class="space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-sm" x-text="item.nombre"></h4>
                                        <p class="text-xs text-gray-500" x-text="'$' + item.precio + ' c/u'"></p>
                                    </div>
                                    <button @click="eliminarItem(index)" class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <div class="flex items-center space-x-2">
                                        <button @click="actualizarCantidad(index, -1)" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">-</button>
                                        <span class="font-medium w-8 text-center" x-text="item.cantidad"></span>
                                        <button @click="actualizarCantidad(index, 1)" class="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-sm">+</button>
                                    </div>
                                    <span class="font-bold" x-text="'$' + (item.precio * item.cantidad).toFixed(2)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </x-card>
            
            <!-- Totales y Pago -->
            <x-card>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span x-text="'$' + subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Descuento</span>
                        <div class="flex items-center">
                            <input type="number" x-model="descuento" class="w-20 text-right border rounded px-2 py-1 text-sm" min="0">
                            <span class="ml-1">%</span>
                        </div>
                    </div>
                    <div class="border-t pt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold">TOTAL</span>
                            <span class="text-2xl font-bold text-blue-600" x-text="'$' + total.toFixed(2)"></span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 space-y-2">
                    <x-button variant="success" size="lg" class="w-full" @click="procesarPago('efectivo')" :disabled="items.length === 0 || procesando">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Efectivo (F4)
                    </x-button>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <x-button variant="primary" @click="procesarPago('tarjeta')" :disabled="items.length === 0 || procesando">
                            Tarjeta
                        </x-button>
                        <x-button variant="secondary" @click="procesarPago('transferencia')" :disabled="items.length === 0 || procesando">
                            Transferencia
                        </x-button>
                    </div>
                    
                    <x-button variant="outline" class="w-full" @click="cancelarVenta()" :disabled="items.length === 0">
                        Cancelar (Esc)
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>
    
    <!-- Modal Pago Efectivo -->
    <x-modal id="pago-efectivo-modal" max-width="sm">
        <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Pago en Efectivo</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Total a pagar</label>
                    <p class="text-2xl font-bold text-blue-600" x-text="'$' + total.toFixed(2)"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Recibido</label>
                    <input type="number" x-model="pagoRecibido" @input="calcularCambio()" class="mt-1 block w-full border rounded-lg px-3 py-2 text-lg" autofocus>
                </div>
                
                <div class="bg-green-50 p-3 rounded-lg" x-show="cambio > 0">
                    <label class="block text-sm font-medium text-green-800">Cambio</label>
                    <p class="text-xl font-bold text-green-600" x-text="'$' + cambio.toFixed(2)"></p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <x-button variant="success" @click="confirmarPago()" :disabled="pagoRecibido < total || guardando">
                Confirmar Pago
            </x-button>
            <x-button variant="ghost" @click="$dispatch('close-modal', {id: 'pago-efectivo-modal'})" class="mr-3">
                Cancelar
            </x-button>
        </div>
    </x-modal>
    
    <!-- Modal Buscar Cliente -->
    <x-modal id="buscar-cliente-modal" max-width="md">
        <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Buscar Cliente</h3>
            
            <input type="text" x-model="clienteSearch" @input.debounce.300ms="buscarClientes()" placeholder="Nombre, teléfono o email..." class="w-full border rounded-lg px-3 py-2 mb-4">
            
            <div class="max-h-64 overflow-y-auto">
                <template x-for="c in clientesEncontrados" :key="c.id">
                    <div @click="seleccionarCliente(c)" class="p-3 hover:bg-gray-100 cursor-pointer border-b">
                        <p class="font-medium" x-text="c.nombre"></p>
                        <p class="text-sm text-gray-500" x-text="c.telefono + ' | ' + c.email"></p>
                    </div>
                </template>
            </div>
            
            <button @click="cliente = null; $dispatch('close-modal', {id: 'buscar-cliente-modal'})" class="mt-4 w-full py-2 text-center text-blue-600 hover:underline">
                Usar Consumidor Final
            </button>
        </div>
    </x-modal>
    
    <!-- Ticket Preview -->
    <x-modal id="ticket-modal" max-width="sm">
        <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
            <div class="text-center mb-4">
                <h3 class="text-lg font-bold">InventarioSmart</h3>
                <p class="text-sm text-gray-500">Ticket #<span x-text="ultimaVenta?.id"></span></p>
                <p class="text-xs text-gray-400" x-text="new Date().toLocaleString()"></p>
            </div>
            
            <div class="border-t border-b py-2 my-2 text-sm">
                <template x-for="item in ultimaVenta?.items" :key="item.id">
                    <div class="flex justify-between py-1">
                        <span x-text="item.cantidad + 'x ' + item.nombre.substring(0, 20)"></span>
                        <span x-text="'$' + (item.precio * item.cantidad).toFixed(2)"></span>
                    </div>
                </template>
            </div>
            
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span x-text="'$' + ultimaVenta?.subtotal?.toFixed(2)"></span>
                </div>
                <div class="flex justify-between" x-show="ultimaVenta?.descuento > 0">
                    <span>Descuento</span>
                    <span x-text="'- $' + ultimaVenta?.descuento?.toFixed(2)"></span>
                </div>
                <div class="flex justify-between font-bold text-lg border-t pt-2">
                    <span>TOTAL</span>
                    <span x-text="'$' + ultimaVenta?.total?.toFixed(2)"></span>
                </div>
            </div>
            
            <div class="mt-4 text-center text-xs text-gray-500">
                <p>¡Gracias por su compra!</p>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <x-button variant="primary" @click="imprimirTicket(); $dispatch('close-modal', {id: 'ticket-modal'})">
                Imprimir Ticket
            </x-button>
            <x-button variant="ghost" @click="nuevaVenta(); $dispatch('close-modal', {id: 'ticket-modal'})" class="mr-3">
                Nueva Venta
            </x-button>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
function posApp() {
    return {
        productos: [],
        productosFiltrados: [],
        categorias: [],
        items: [],
        cliente: null,
        search: '',
        categoriaActiva: '',
        loading: false,
        procesando: false,
        guardando: false,
        descuento: 0,
        pagoRecibido: 0,
        cambio: 0,
        metodoPago: '',
        clienteSearch: '',
        clientesEncontrados: [],
        ultimaVenta: null,
        
        init() {
            this.fetchProductos();
            this.fetchCategorias();
            this.setupKeyboardShortcuts();
            this.$refs.searchInput.focus();
        },
        
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'F4') {
                    e.preventDefault();
                    if (this.items.length > 0) this.procesarPago('efectivo');
                }
                if (e.key === 'Escape') {
                    if (this.items.length > 0) this.cancelarVenta();
                }
            });
        },
        
        async fetchProductos() {
            this.loading = true;
            try {
                const response = await axios.get('/api/productos', {
                    params: { disponibles: true, per_page: 100 }
                });
                this.productos = response.data.data || [];
                this.filtrarProductos();
            } catch (error) {
                console.error('Error cargando productos:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async fetchCategorias() {
            try {
                const response = await axios.get('/api/categorias');
                this.categorias = response.data.data || [];
            } catch (error) {
                console.error('Error cargando categorías:', error);
            }
        },
        
        filtrarProductos() {
            let filtrados = this.productos;
            
            if (this.categoriaActiva) {
                filtrados = filtrados.filter(p => p.categoria_id === this.categoriaActiva);
            }
            
            if (this.search) {
                const term = this.search.toLowerCase();
                filtrados = filtrados.filter(p => 
                    p.nombre.toLowerCase().includes(term) || 
                    p.codigo.toLowerCase().includes(term)
                );
            }
            
            this.productosFiltrados = filtrados.slice(0, 20);
        },
        
        buscarProducto() {
            this.filtrarProductos();
            if (this.productosFiltrados.length === 1) {
                this.agregarProducto(this.productosFiltrados[0]);
                this.search = '';
                this.filtrarProductos();
            }
        },
        
        agregarProducto(producto) {
            if (producto.stock <= 0) {
                this.$dispatch('toast', { message: 'Producto sin stock', type: 'error' });
                return;
            }
            
            const existente = this.items.find(i => i.producto_id === producto.id);
            
            if (existente) {
                if (existente.cantidad >= producto.stock) {
                    this.$dispatch('toast', { message: 'Stock insuficiente', type: 'warning' });
                    return;
                }
                existente.cantidad++;
            } else {
                this.items.push({
                    producto_id: producto.id,
                    nombre: producto.nombre,
                    precio: parseFloat(producto.precio_venta),
                    cantidad: 1,
                    stock: producto.stock
                });
            }
            
            this.$dispatch('toast', { message: 'Producto agregado', type: 'success' });
        },
        
        actualizarCantidad(index, delta) {
            const item = this.items[index];
            const nuevaCantidad = item.cantidad + delta;
            
            if (nuevaCantidad <= 0) {
                this.eliminarItem(index);
                return;
            }
            
            if (nuevaCantidad > item.stock) {
                this.$dispatch('toast', { message: 'Stock insuficiente', type: 'warning' });
                return;
            }
            
            item.cantidad = nuevaCantidad;
        },
        
        eliminarItem(index) {
            this.items.splice(index, 1);
        },
        
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        },
        
        get total() {
            const descuentoMonto = this.subtotal * (this.descuento / 100);
            return this.subtotal - descuentoMonto;
        },
        
        procesarPago(metodo) {
            this.metodoPago = metodo;
            
            if (metodo === 'efectivo') {
                this.pagoRecibido = this.total;
                this.calcularCambio();
                this.$dispatch('open-modal', { id: 'pago-efectivo-modal' });
            } else {
                this.confirmarPago();
            }
        },
        
        calcularCambio() {
            this.cambio = Math.max(0, this.pagoRecibido - this.total);
        },
        
        async confirmarPago() {
            this.guardando = true;
            
            try {
                const ventaData = {
                    cliente_id: this.cliente?.id,
                    items: this.items.map(i => ({
                        producto_id: i.producto_id,
                        cantidad: i.cantidad,
                        precio_unitario: i.precio
                    })),
                    subtotal: this.subtotal,
                    descuento_porcentaje: this.descuento,
                    descuento_monto: this.subtotal * (this.descuento / 100),
                    total: this.total,
                    metodo_pago: this.metodoPago,
                    pago_recibido: this.metodoPago === 'efectivo' ? this.pagoRecibido : this.total,
                    cambio: this.cambio
                };
                
                const response = await axios.post('/api/ventas', ventaData);
                this.ultimaVenta = response.data.data;
                
                this.$dispatch('close-modal', { id: 'pago-efectivo-modal' });
                this.$dispatch('open-modal', { id: 'ticket-modal' });
                this.$dispatch('toast', { message: 'Venta realizada con éxito', type: 'success' });
                
            } catch (error) {
                this.$dispatch('toast', { message: 'Error al procesar la venta', type: 'error' });
            } finally {
                this.guardando = false;
            }
        },
        
        cancelarVenta() {
            if (confirm('¿Cancelar la venta actual?')) {
                this.items = [];
                this.cliente = null;
                this.descuento = 0;
                this.$dispatch('toast', { message: 'Venta cancelada', type: 'info' });
            }
        },
        
        nuevaVenta() {
            this.items = [];
            this.cliente = null;
            this.descuento = 0;
            this.pagoRecibido = 0;
            this.cambio = 0;
            this.$refs.searchInput.focus();
        },
        
        buscarCliente() {
            this.clienteSearch = '';
            this.clientesEncontrados = [];
            this.$dispatch('open-modal', { id: 'buscar-cliente-modal' });
        },
        
        async buscarClientes() {
            if (this.clienteSearch.length < 2) return;
            
            try {
                const response = await axios.get('/api/clientes', {
                    params: { search: this.clienteSearch }
                });
                this.clientesEncontrados = response.data.data || [];
            } catch (error) {
                console.error('Error buscando clientes:', error);
            }
        },
        
        seleccionarCliente(c) {
            this.cliente = c;
            this.$dispatch('close-modal', { id: 'buscar-cliente-modal' });
        },
        
        imprimirTicket() {
            window.print();
        }
    }
}
</script>
@endpush
@endsection
