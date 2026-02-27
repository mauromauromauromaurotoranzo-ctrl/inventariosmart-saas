@extends('layouts.app')

@section('title', 'Clientes - InventarioSmart')
@section('page-title', 'Clientes')

@section('content')
<div x-data="clientesApp()" x-init="init()">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-card>
            <p class="text-sm text-gray-500">Total Clientes</p>
            <p class="text-2xl font-bold" x-text="stats.total"></p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500">Activos</p>
            <p class="text-2xl font-bold text-green-600" x-text="stats.activos"></p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500">Con Deuda</p>
            <p class="text-2xl font-bold text-red-600" x-text="stats.con_deuda"></p>
        </x-card>
        <x-card>
            <p class="text-sm text-gray-500">Nuevos este mes</p>
            <p class="text-2xl font-bold text-blue-600" x-text="stats.nuevos_mes"></p>
        </x-card>
    </div>

    <!-- Filters & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="relative w-full sm:w-96">
            <input type="text" x-model="search" @input.debounce.300ms="fetchClientes()"
                   placeholder="Buscar por nombre, email o teléfono..."
                   class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        
        <div class="flex space-x-2">
            <select x-model="filtroTipo" @change="fetchClientes()" class="border rounded-lg px-3 py-2">
                <option value="">Todos los tipos</option>
                <option value="consumidor_final">Consumidor Final</option>
                <option value="responsable_inscripto">Responsable Inscripto</option>
                <option value="monotributista">Monotributista</option>
                <option value="exento">Exento</option>
            </select>
            
            <x-button variant="primary" @click="abrirModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Cliente
            </x-button>
        </div>
    </div>

    <!-- Table -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo CC</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="cliente in clientes" :key="cliente.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-600">
                                        <span x-text="cliente.nombre.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900" x-text="cliente.nombre"></p>
                                        <p x-show="cliente.cuit_cuil" class="text-xs text-gray-500" x-text="'CUIT: ' + cliente.cuit_cuil"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-900" x-text="cliente.email || '-'"></p>
                                <p class="text-xs text-gray-500" x-text="cliente.telefono || '-'"></p>
                            </td>
                            <td class="px-4 py-3">
                                <x-badge variant="info" x-text="formatTipo(cliente.tipo)"></x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="{'font-medium': true, 'text-red-600': cliente.saldo_cc > 0}" x-text="'$' + (cliente.saldo_cc || 0).toFixed(2)"></span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="verDetalle(cliente)" class="text-blue-600 hover:text-blue-800 mr-2">Ver</button>
                                <button @click="editar(cliente)" class="text-gray-600 hover:text-gray-800 mr-2">Editar</button>
                                <button @click="confirmarEliminar(cliente)" class="text-red-600 hover:text-red-800">Eliminar</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            <x-pagination 
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                @page-change="cambiarPagina($event)"
            />
        </div>
    </x-card>

    <!-- Modal Form -->
    <x-modal id="cliente-modal" max-width="lg">
        <div class="bg-white px-4 pb-4 pt-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="modoEdicion ? 'Editar Cliente' : 'Nuevo Cliente'"></h3>
            
            <form @submit.prevent="guardar()" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nombre completo *</label>
                        <input type="text" x-model="form.nombre" required
                               class="mt-1 block w-full border rounded-lg px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" x-model="form.email"
                               class="mt-1 block w-full border rounded-lg px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="tel" x-model="form.telefono"
                               class="mt-1 block w-full border rounded-lg px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo *</label>
                        <select x-model="form.tipo" required class="mt-1 block w-full border rounded-lg px-3 py-2">
                            <option value="consumidor_final">Consumidor Final</option>
                            <option value="responsable_inscripto">Responsable Inscripto</option>
                            <option value="monotributista">Monotributista</option>
                            <option value="exento">Exento</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CUIT/CUIL</label>
                        <input type="text" x-model="form.cuit_cuil"
                               class="mt-1 block w-full border rounded-lg px-3 py-2">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Dirección</label>
                        <textarea x-model="form.direccion" rows="2"
                                  class="mt-1 block w-full border rounded-lg px-3 py-2"></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Notas</label>
                        <textarea x-model="form.notas" rows="2"
                                  class="mt-1 block w-full border rounded-lg px-3 py-2"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <x-button variant="primary" @click="guardar()" :loading="guardando">
                Guardar
            </x-button>
            <x-button variant="ghost" @click="$dispatch('close-modal', {id: 'cliente-modal'})" class="mr-3">
                Cancelar
            </x-button>
        </div>
    </x-modal>

    <!-- Confirm Delete -->
    <x-confirm-dialog 
        id="eliminar-cliente"
        title="¿Eliminar cliente?"
        message="Esta acción no se puede deshacer. Si el cliente tiene ventas asociadas, no podrá ser eliminado."
        confirm-text="Eliminar"
        cancel-text="Cancelar"
        on-confirm="eliminar()"
    />
</div>

@push('scripts')
<script>
function clientesApp() {
    return {
        clientes: [],
        stats: {},
        loading: false,
        guardando: false,
        modoEdicion: false,
        clienteActual: null,
        search: '',
        filtroTipo: '',
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 15
        },
        form: {
            nombre: '',
            email: '',
            telefono: '',
            tipo: 'consumidor_final',
            cuit_cuil: '',
            direccion: '',
            notas: ''
        },
        
        init() {
            this.fetchClientes();
            this.fetchStats();
        },
        
        async fetchClientes() {
            this.loading = true;
            try {
                const params = {
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    search: this.search,
                    tipo: this.filtroTipo
                };
                
                const response = await axios.get('/api/clientes', { params });
                this.clientes = response.data.data || [];
                this.pagination = response.data.meta || this.pagination;
            } catch (error) {
                this.$dispatch('toast', { message: 'Error al cargar clientes', type: 'error' });
            } finally {
                this.loading = false;
            }
        },
        
        async fetchStats() {
            try {
                const response = await axios.get('/api/clientes/stats/resumen');
                this.stats = response.data;
            } catch (error) {
                console.error('Error cargando stats:', error);
            }
        },
        
        abrirModal() {
            this.modoEdicion = false;
            this.clienteActual = null;
            this.form = {
                nombre: '',
                email: '',
                telefono: '',
                tipo: 'consumidor_final',
                cuit_cuil: '',
                direccion: '',
                notas: ''
            };
            this.$dispatch('open-modal', { id: 'cliente-modal' });
        },
        
        editar(cliente) {
            this.modoEdicion = true;
            this.clienteActual = cliente;
            this.form = { ...cliente };
            this.$dispatch('open-modal', { id: 'cliente-modal' });
        },
        
        async guardar() {
            if (!this.form.nombre.trim()) {
                this.$dispatch('toast', { message: 'El nombre es requerido', type: 'warning' });
                return;
            }
            
            this.guardando = true;
            
            try {
                if (this.modoEdicion) {
                    await axios.put(`/api/clientes/${this.clienteActual.id}`, this.form);
                    this.$dispatch('toast', { message: 'Cliente actualizado', type: 'success' });
                } else {
                    await axios.post('/api/clientes', this.form);
                    this.$dispatch('toast', { message: 'Cliente creado', type: 'success' });
                }
                
                this.$dispatch('close-modal', { id: 'cliente-modal' });
                this.fetchClientes();
                this.fetchStats();
            } catch (error) {
                const msg = error.response?.data?.message || 'Error al guardar';
                this.$dispatch('toast', { message: msg, type: 'error' });
            } finally {
                this.guardando = false;
            }
        },
        
        confirmarEliminar(cliente) {
            this.clienteActual = cliente;
            this.$dispatch('open-confirm', { id: 'eliminar-cliente' });
        },
        
        async eliminar() {
            try {
                await axios.delete(`/api/clientes/${this.clienteActual.id}`);
                this.$dispatch('toast', { message: 'Cliente eliminado', type: 'success' });
                this.fetchClientes();
                this.fetchStats();
            } catch (error) {
                const msg = error.response?.data?.message || 'Error al eliminar';
                this.$dispatch('toast', { message: msg, type: 'error' });
            }
        },
        
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.fetchClientes();
        },
        
        verDetalle(cliente) {
            window.location.href = `/clientes/${cliente.id}`;
        },
        
        formatTipo(tipo) {
            const tipos = {
                'consumidor_final': 'Consumidor Final',
                'responsable_inscripto': 'Resp. Inscripto',
                'monotributista': 'Monotributista',
                'exento': 'Exento'
            };
            return tipos[tipo] || tipo;
        }
    }
}
</script>
@endpush
@endsection
