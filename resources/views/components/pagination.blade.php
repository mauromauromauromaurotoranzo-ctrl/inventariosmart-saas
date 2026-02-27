@props(['total' => 0, 'perPage' => 10, 'currentPage' => 1])

@php
$totalPages = ceil($total / $perPage);
$start = max(1, $currentPage - 2);
$end = min($totalPages, $currentPage + 2);
@endphp

@if($totalPages > 1)
    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
            <button @click="$dispatch('change-page', {{ $currentPage - 1 }})" 
                    :disabled="{{ $currentPage <= 1 ? 'true' : 'false' }}"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                Anterior
            </button>
            <button @click="$dispatch('change-page', {{ $currentPage + 1 }})"
                    :disabled="{{ $currentPage >= $totalPages ? 'true' : 'false' }}"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                Siguiente
            </button>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Mostrando <span class="font-medium">{{ (($currentPage - 1) * $perPage) + 1 }}</span>
                    a <span class="font-medium">{{ min($currentPage * $perPage, $total) }}</span>
                    de <span class="font-medium">{{ $total }}</span> resultados
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <button @click="$dispatch('change-page', {{ $currentPage - 1 }})"
                            :disabled="{{ $currentPage <= 1 ? 'true' : 'false' }}"
                            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50">
                        <span class="sr-only">Anterior</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    @for($i = $start; $i <= $end; $i++)
                        <button @click="$dispatch('change-page', {{ $i }})"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold {{ $i === $currentPage ? 'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0' }}">
                            {{ $i }}
                        </button>
                    @endfor
                    
                    <button @click="$dispatch('change-page', {{ $currentPage + 1 }})"
                            :disabled="{{ $currentPage >= $totalPages ? 'true' : 'false' }}"
                            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50">
                        <span class="sr-only">Siguiente</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
@endif
