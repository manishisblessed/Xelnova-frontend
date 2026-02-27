<x-marketplace.layout>
    @section('title', $page->meta_title ?: $page->title)

    <div class="bg-gray-50 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $page->title }}</h1>
                <p class="text-sm text-gray-500 mb-8">
                    Last updated: {{ $page->updated_at->format('d M Y') }}
                </p>

                <div class="cms-content">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
