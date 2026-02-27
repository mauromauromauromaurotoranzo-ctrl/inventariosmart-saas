@props([
    'type' => 'info',
    'dismissible' => false
])

@php
$styles = [
    'info' => 'bg-blue-50 border-blue-400 text-blue-800',
    'success' => 'bg-green-50 border-green-400 text-green-800',
    'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
    'error' => 'bg-red-50 border-red-400 text-red-800'
];

$icons = [
    'info' => '<svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'success' => '<svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'warning' => '<svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    'error' => '<svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
];
@endphp

<div x-data="{ show: true }" 
     x-show="show"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="rounded-md p-4 border {{ $styles[$type] ?? $styles['info'] }}"
     role="alert">
    <div class="flex">
        <div class="flex-shrink-0">
            {!! $icons[$type] ?? $icons['info'] !!}
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm">{{ $slot }}</p>
        </div>
        @if($dismissible)
            <div class="ml-auto pl-3">
                <button @click="show = false" class="inline-flex rounded-md p-1.5 hover:bg-black hover:bg-opacity-10 focus:outline-none">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>
