@extends('layouts.app')

@section('title', 'Categorías - InventarioSmart')
@section('page-title', 'Categorías')

@section('content')
<div x-data="categoriasApp()" x-init="init()">
    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
        <div class="text-sm text-gray-500">
            Total: <span class="font-medium" x-text="categorias.length"></span> categorías
        </div>
        <x-button variant="primary" @click="abrirModal()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Categoría
        </x-button>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <template x-for="cat in categorias" :key="cat.id">
            <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition p-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-xl">
                            📁
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900" x-text="cat.nombre"></h3>
                            <p class="text-xs text-gray-500" x-text="cat.productos_count + ' productos'"></p>
                        </div>
                    </div>
                    <div class="flex space-x-1">
                        <button @click="editar(cat)" class="p-1.5 text-gray-400 hover:text-blue-600 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button @click="confirmarEliminar(cat)" class="p-1.5 text-gray-400 hover:text-red-600 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <p x-show="cat.descripcion" class="mt-2 text-sm text-gray-600 line-clamp-2" x-text="cat.descripcion"></p>
                
                <div class="mt-3 pt-3 border-t flex items-center justify-between text-xs">
                    <span class="text-gray-500" x-text="'Stock: ' + cat.stock_total"></span>
                    <span class="font-medium text-green-600" x-text="'$' + cat.valor_total?.toLocaleString()"></span>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="categorias.length === 0 && !loading" class="text-center py-12">
        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900">No hay categorías</h3>
        <p class="text-gray-500 mt-1">Creá tu primera categoría para organizar los productos</p>
    </div>

    <!-- Modal Form -->
    <x-modal id="categoria-modal" max-width="md">
        <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="modoEdicion ? 'Editar Categoría' : 'Nueva Categoría'"></h3>
            
            <form @submit.prevent="guardar()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text" x-model="form.nombre" required
                           class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea x-model="form.descripcion" rows="3"
                              class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </form>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <x-button variant="primary" @click="guardar()" :loading="guardando">
                Guardar
            </x-button>
            <x-button variant="ghost" @click="$dispatch('close-modal', {id: 'categoria-modal'})" class="mr-3">
                Cancelar
            </x-button>
        </div>
    </x-modal>

    <!-- Confirm Delete -->
    <x-confirm-dialog 
        id="eliminar-categoria"
        title="¿Eliminar categoría?"
        message="Esta acción no se puede deshacer. Los productos asociados quedarán sin categoría."
        confirm-text="Eliminar"
        cancel-text="Cancelar"
        on-confirm="eliminar()"
    />
</div>

@push('scripts')
<script>
function categoriasApp() {
    return {
        categorias: [],
        loading: false,
        guardando: false,
        modoEdicion: false,
        categoriaActual: null,
        form: {
            nombre: '',
            descripcion: ''
        },
        
        init() {
            this.fetchCategorias();
        },
        
        async fetchCategorias() {
            this.loading = true;
            try {
                const response = await axios.get('/api/categorias');
                this.categorias = response.data.data || [];
            } catch (error) {
                this.$dispatch('toast', { message: 'Error al cargar categorías', type: 'error' });
            } finally {
                this.loading = false;
            }
        },
        
        abrirModal() {
            this.modoEdicion = false;
            this.categoriaActual = null;
            this.form = { nombre: '', descripcion: '' };
            this.$dispatch('open-modal', { id: 'categoria-modal' });
        },
        
        editar(categoria) {
            this.modoEdicion = true;
            this.categoriaActual = categoria;
            this.form = {
                nombre: categoria.nombre,
                descripcion: categoria.descripcion || ''
            };
            this.$dispatch('open-modal', { id: 'categoria-modal' });
        },
        
        async guardar() {
            if (!this.form.nombre.trim()) {
                this.$dispatch('toast', { message: 'El nombre es requerido', type: 'warning' });
                return;
            }
            
            this.guardando = true;
            
            try {
                if (this.modoEdicion) {
                    await axios.put(`/api/categorias/${this.categoriaActual.id}`, this.form);
                    this.$dispatch('toast', { message: 'Categoría actualizada', type: 'success' });
                } else {
                    await axios.post('/api/categorias', this.form);
                    this.$dispatch('toast', { message: 'Categoría creada', type: 'success' });
                }
                
                this.$dispatch('close-modal', { id: 'categoria-modal' });
                this.fetchCategorias();
            } catch (error) {
                const msg = error.response?.data?.message || 'Error al guardar';
                this.$dispatch('toast', { message: msg, type: 'error' });
            } finally {
                this.guardando = false;
            }
        },
        
        confirmarEliminar(categoria) {
            this.categoriaActual = categoria;
            this.$dispatch('open-confirm', { id: 'eliminar-categoria' });
        },
        
        async eliminar() {
            try {
                await axios.delete(`/api/categorias/${this.categoriaActual.id}`);
                this.$dispatch('toast', { message: 'Categoría eliminada', type: 'success' });
                this.fetchCategorias();
            } catch (error) {
                const msg = error.response?.data?.message || 'Error al eliminar';
                this.$dispatch('toast', { message: msg, type: 'error' });
            }
        }
    }
}
</script>
@endpush
@endsection
