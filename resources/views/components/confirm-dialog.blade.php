@props(['id', 'title' => 'Confirmar acción', 'message' => '¿Estás seguro?', 'confirmText' => 'Confirmar', 'cancelText' => 'Cancelar', 'variant' => 'danger'])

@php
$buttonVariants = [
    'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    'info' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
];
@endphp

<x-modal :id="$id" maxWidth="sm">
    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full {{ $variant === 'danger' ? 'bg-red-100' : ($variant === 'warning' ? 'bg-yellow-100' : 'bg-blue-100') }} sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 {{ $variant === 'danger' ? 'text-red-600' : ($variant === 'warning' ? 'text-yellow-600' : 'text-blue-600') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">{{ $title }}</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">{{ $message }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
        <button type="button" 
                @click="$dispatch('confirmed-{{ $id }}'); show = false"
                class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm {{ $buttonVariants[$variant] ?? $buttonVariants['danger'] }} sm:ml-3 sm:w-auto">
            {{ $confirmText }}
        </button>
        <button type="button" 
                @click="show = false"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
            {{ $cancelText }}
        </button>
    </div>
</x-modal>
