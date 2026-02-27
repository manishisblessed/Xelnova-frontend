@props(['category'])

<a {{ $attributes->merge(['href' => '#', 'class' => 'flex flex-col items-center group cursor-pointer']) }}>
    <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-blue-50 flex items-center justify-center mb-2 group-hover:bg-blue-100 transition overflow-hidden border border-transparent group-hover:border-xelnova-green-300">
        <img src="{{ $category['image'] ?? 'https://placehold.co/100x100?text=Cat' }}" 
             alt="{{ $category['name'] ?? 'Category' }}" 
             class="w-12 h-12 md:w-14 md:h-14 object-contain group-hover:scale-110 transition-transform duration-300">
    </div>
    <span class="text-xs md:text-sm font-medium text-gray-700 group-hover:text-xelnova-green-600 text-center">{{ $category['name'] ?? 'Category' }}</span>
</a>
