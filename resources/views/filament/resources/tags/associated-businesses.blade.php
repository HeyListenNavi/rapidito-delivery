<div class="flex flex-wrap gap-2 mt-2">
    @forelse ($businesses as $business)
        @php
            $colorClass = $business['status'] === 'active'
                ? 'bg-primary-500/10 text-primary-700 ring-primary-500/20 hover:bg-primary-500/20'
                : 'bg-gray-500/10 text-gray-700 ring-gray-500/20 hover:bg-gray-500/20';
            $dotColor = $business['status'] === 'active' ? 'bg-primary-500' : 'bg-gray-500';
        @endphp

        <a
            href="{{ $business['url'] }}"
            class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset transition {{ $colorClass }}"
        >
            <svg class="h-1.5 w-1.5 fill-primary-700 {{ $dotColor }} rounded-full" viewBox="0 0 6 6" aria-hidden="true">
                <circle cx="3" cy="3" r="3" />
            </svg>
            {{ $business['name'] }}
        </a>
    @empty
        <p class="text-sm text-gray-500 italic">No hay negocios vinculados a esta etiqueta.</p>
    @endforelse
</div>
