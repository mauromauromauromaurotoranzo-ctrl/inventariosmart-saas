@extends('layouts.app')

@section('title', 'Productos - InventarioSmart')
@section('page-title', 'Gestión de Productos')

@section('content')
<div x-data="productosApp()" x-init="init()">
    <!-- Header Actions -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1 max-w-lg">
            <div class="relative">
                <input 
                    type="text" 
                    x-model="search"
                    @input.debounce.300ms="fetchProductos()"
                    placeholder="Buscar productos..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="flex gap-2">
            <x-button variant="outline" @click="showFilters = !showFilters">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtros
            </x-button>
            
            <x-button variant="primary" @click="openModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Producto
            </x-button>
        </div>
    </div>

    <!-- Filters Panel -->
    <div x-show="showFilters" x-transition class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <select x-model="filters.categoria" @change="fetchProductos()" class="border rounded-lg px-3 py-2">
                <option value="">Todas las categorías</option>
                <template x-for="cat in categorias" :key="cat.id">
                    <option :value="cat.id" x-text="cat.nombre"></option>
                </template>
            </select>
            
            <select x-model="filters.stock" @change="fetchProductos()" class="border rounded-lg px-3 py-2">
                <option value="">Stock</option>
                <option value="bajo">Stock bajo</option>
                <option value="sin">Sin stock</option>
                <option value="disponible">Disponible</option>
            </select>
            
            <select x-model="filters.estado" @change="fetchProductos()" class="border rounded-lg px-3 py-2">
                <option value="">Estado</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
            
            <button @click="resetFilters()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Limpiar filtros
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Productos</p>
                    <p class="text-2xl font-bold" x-text="stats.total"></p>
                </div>
            </div>
        </x-card>
        
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Stock Bajo</p>
                    <p class="text-2xl font-bold" x-text="stats.stockBajo"></p>
                </div>
            </div>
        </x-card>
        
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Sin Stock</p>
                    <p class="text-2xl font-bold" x-text="stats.sinStock"></p>
                </div>
            </div>
        </x-card>
        
        <x-card>
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Valor Total</p>
                    <p class="text-2xl font-bold" x-text="'$' + stats.valorTotal.toLocaleString()"></p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Productos Table -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center">
                                <x-loading size="lg" />
                            </td>
                        </tr>
                    </template>
                    
                    <template x-if="!loading && productos.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No se encontraron productos
                            </td>
                        </tr>
                    </template>
                    
                    <template x-for="producto in productos" :key="producto.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="producto.codigo"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-lg">📦</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="producto.nombre"></div>
                                        <div class="text-sm text-gray-500" x-text="producto.descripcion?.substring(0, 50) + '...'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="producto.categoria?.nombre || 'Sin categoría'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" x-text="'$' + producto.precio_venta"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                    'bg-green-100 text-green-800': producto.stock > producto.stock_minimo,
                                    'bg-yellow-100 text-yellow-800': producto.stock <= producto.stock_minimo && producto.stock > 0,
                                    'bg-red-100 text-red-800': producto.stock === 0
                                }" x-text="producto.stock + ' unidades'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :variant="producto.activo ? 'green' : 'gray'" x-text="producto.activo ? 'Activo' : 'Inactivo'"></x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="editProducto(producto)" class="text-blue-600 hover:text-blue-900 mr-3">
                                    Editar
                                </button>
                                <button @click="confirmDelete(producto)" class="text-red-600 hover:text-red-900">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            <x-pagination 
                :total="100" 
                :per-page="10" 
                :current-page="1"
                @change-page="changePage($event.detail)"
            />
        </div>
    </x-card>

    <!-- Modal Form -->
    <x-modal id="producto-modal" max-width="2xl">
        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="editing ? 'Editar Producto' : 'Nuevo Producto'"></h3>
            
            <form @submit.prevent="saveProducto()" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="codigo" label="Código" x-model="form.codigo" required />
                    <x-input name="nombre" label="Nombre" x-model="form.nombre" required />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea x-model="form.descripcion" rows="3" class="w-full border rounded-lg px-3 py-2"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-select name="categoria_id" label="Categoría" x-model="form.categoria_id" :options="[]">
                        <template x-for="cat in categorias" :key="cat.id">
                            <option :value="cat.id" x-text="cat.nombre"></option>
                        </template>
                    </x-select>
                    
                    <x-input name="precio_costo" label="Precio Costo" type="number" step="0.01" x-model="form.precio_costo" />
                    <x-input name="precio_venta" label="Precio Venta" type="number" step="0.01" x-model="form.precio_venta" required />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-input name="stock" label="Stock Actual" type="number" x-model="form.stock" required />
                    <x-input name="stock_minimo" label="Stock Mínimo" type="number" x-model="form.stock_minimo" />
                    <x-input name="stock_maximo" label="Stock Máximo" type="number" x-model="form.stock_maximo" />
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" x-model="form.activo" id="activo" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="activo" class="ml-2 text-sm text-gray-700">Producto activo</label>
                </div>
            </form>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <x-button variant="primary" @click="saveProducto()" :loading="saving">
                Guardar
            </x-button>
            <x-button variant="ghost" @click="closeModal()" class="mr-3">
                Cancelar
            </x-button>
        </div>
    </x-modal>

    <!-- Confirm Delete -->
    <x-confirm-dialog 
        id="delete-confirm" 
        title="Eliminar Producto"
        message="¿Estás seguro de eliminar este producto? Esta acción no se puede deshacer."
        confirm-text="Eliminar"
        variant="danger"
        @confirmed-delete-confirm="deleteProducto()"
    />
</div>

@push('scripts')
<script>
function productosApp() {
    return {
        productos: [],
        categorias: [],
        loading: false,
        saving: false,
        showFilters: false,
        editing: false,
        search: '',
        filters: {
            categoria: '',
            stock: '',
            estado: ''
        },
        stats: {
            total: 0,
            stockBajo: 0,
            sinStock: 0,
            valorTotal: 0
        },
        form: {
            id: null,
            codigo: '',
            nombre: '',
            descripcion: '',
            categoria_id: '',
            precio_costo: 0,
            precio_venta: 0,
            stock: 0,
            stock_minimo: 5,
            stock_maximo: 100,
            activo: true
        },
        productoToDelete: null,
        
        init() {
            this.fetchProductos();
            this.fetchCategorias();
            this.fetchStats();
        },
        
        async fetchProductos() {
            this.loading = true;
            try {
                const response = await axios.get('/api/productos', {
                    params: {
                        search: this.search,
                        ...this.filters
                    }
                });
                this.productos = response.data.data || [];
            } catch (error) {
                this.$dispatch('toast', { message: 'Error al cargar productos', type: 'error' });
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
        
        async fetchStats() {
            try {
                const response = await axios.get('/api/productos/stats');
                this.stats = response.data;
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
            }
        },
        
        openModal() {
            this.editing = false;
            this.resetForm();
            this.$dispatch('open-modal', { id: 'producto-modal' });
        },
        
        editProducto(producto) {
            this.editing = true;
            this.form = { ...producto };
            this.$dispatch('open-modal', { id: 'producto-modal' });
        },
        
        closeModal() {
            this.$dispatch('close-modal', { id: 'producto-modal' });
        },
        
        async saveProducto() {
            this.saving = true;
            try {
                const url = this.editing ? `/api/productos/${this.form.id}` : '/api/productos';
                const method = this.editing ? 'put' : 'post';
                
                await axios[method](url, this.form);
                
                this.$dispatch('toast', { 
                    message: this.editing ? 'Producto actualizado' : 'Producto creado', 
                    type: 'success' 
                });
                
                this.closeModal();
                this.fetchProductos();
                this.fetchStats();
            } catch (error) {
                this.$dispatch('toast', { 
                    message: error.response?.data?.message || 'Error al guardar', 
                    type: 'error' 
                });
            } finally {
                this.saving = false;
            }
        },
        
        confirmDelete(producto) {
            this.productoToDelete = producto;
            this.$dispatch('open-modal', { id: 'delete-confirm' });
        },
        
        async deleteProducto() {
            if (!this.productoToDelete) return;
            
            try {
                await axios.delete(`/api/productos/${this.productoToDelete.id}`);
                this.$dispatch('toast', { message: 'Producto eliminado', type: 'success' });
                this.fetchProductos();
                this.fetchStats();
            } catch (error) {
                this.$dispatch('toast', { message: 'Error al eliminar', type: 'error' });
            }
            
            this.productoToDelete = null;
        },
        
        resetForm() {
            this.form = {
                id: null,
                codigo: '',
                nombre: '',
                descripcion: '',
                categoria_id: '',
                precio_costo: 0,
                precio_venta: 0,
                stock: 0,
                stock_minimo: 5,
                stock_maximo: 100,
                activo: true
            };
        },
        
        resetFilters() {
            this.filters = { categoria: '', stock: '', estado: '' };
            this.fetchProductos();
        },
        
        changePage(page) {
            // Implementar paginación
            console.log('Cambiar a página:', page);
        }
    }
}
</script>
@endpush
@endsection
