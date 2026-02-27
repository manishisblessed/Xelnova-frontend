@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'fullWidth' => false,
    'disabled' => false
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variants = [
        'primary' => 'bg-xelnova-green-500 hover:bg-xelnova-green-600 text-white focus:ring-xelnova-green-500',
        'secondary' => 'bg-xelnova-gold-400 hover:bg-xelnova-gold-500 text-gray-900 focus:ring-xelnova-gold-400',
        'outline' => 'border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 focus:ring-xelnova-green-500',
        'ghost' => 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:ring-gray-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    
    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size] . ($fullWidth ? ' w-full' : '');
@endphp

<button {{ $attributes->merge(['type' => $type, 'class' => $classes, 'disabled' => $disabled]) }}>
    {{ $slot }}
</button>
