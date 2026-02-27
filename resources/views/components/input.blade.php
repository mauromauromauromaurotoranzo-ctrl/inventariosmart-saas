@props([
    'type' => 'text',
    'name',
    'id' => null,
    'label' => null,
    'error' => null,
    'help' => null,
    'required' => false
])

@php
$id = $id ?? $name;
$hasError = $error || $errors->has($name);
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <input type="{{ $type }}"
           name="{{ $name }}"
           id="{{ $id }}"
           {{ $attributes->merge([
               'class' => 'block w-full rounded-md shadow-sm sm:text-sm ' . 
                   ($hasError 
                       ? 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500' 
                       : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500')
           ]) }}
           @if($required) required @endif>
    
    @if($hasError)
        <p class="mt-1 text-sm text-red-600">{{ $error ?? $errors->first($name) }}</p>
    @elseif($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif
</div>
