@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false
])

@php
$variants = [
    'primary' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800 focus:ring-gray-500',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
    'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
    'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-500',
    'outline' => 'bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
    'ghost' => 'bg-transparent text-gray-600 hover:bg-gray-100 focus:ring-gray-500'
];

$sizes = [
    'xs' => 'px-2.5 py-1.5 text-xs',
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
    'xl' => 'px-8 py-4 text-lg'
];

$classes = implode(' ', [
    'inline-flex items-center justify-center font-medium rounded-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
    $variants[$variant] ?? $variants['primary'],
    $sizes[$size] ?? $sizes['md']
]);
@endphp

<button type="{{ $type }}" 
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif>
    
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    
    {{ $slot }}
</button>
