<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Business;
use App\Models\Tag;
use App\Models\DeliveryAddress;
use App\Models\ServiceZone;
use Illuminate\Support\Facades\Storage;

new #[Title('Search Product')] class extends Component {
    public string $search = '';
    public $city = null;

    public function mount()
    {
        $guestToken = request()->cookie('guest_token');

        if ($guestToken) {
            $address = DeliveryAddress::where('guest_token', $guestToken)->latest()->first();

            if ($address && $address->lat && $address->lng) {
                $this->resolveServiceZone($address->lat, $address->lng);
            }
        }
    }

    protected function resolveServiceZone($lat, $lng)
    {
        $zone = ServiceZone::active()
            ->with('city.businesses')
            ->get()
            ->first(fn ($zone) => $zone->contains($lat, $lng));

        if ($zone) {
            $this->city = $zone->city;
        }
    }

    #[Computed]
    public function businesses()
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        return Business::active()
            ->where('name', 'like', '%' . $this->search . '%')
            ->when($this->city, function ($query) {
                $query->where('city_id', $this->city->id);
            })
            ->get();
    }

    #[Computed]
    public function topTags()
    {
        return Tag::whereIn('name', ['Hamburguesas', 'Vegano', 'Café', 'Postres'])->get();
    }
};
?>

<div class="flex flex-col gap-6 p-4">
    <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white pl-4 shadow-sm">
        <i class="bxf bx-search text-lg text-red-400"></i>
        <input wire:model.live.debounce.300ms="search" type="text"
            class="w-full border-none py-4 pr-4 text-sm font-medium text-gray-800 focus:ring-0"
            placeholder="¿Qué se te antoja hoy?">
    </div>

    <div class="flex w-full flex-col gap-3">
        <h2 class="font-bold text-gray-800">Top Categorías</h2>

        <div class="grid grid-cols-2 gap-4">
            @if($tag = $this->topTags->firstWhere('name', 'Hamburguesas'))
            <a wire:navigate href="/tag/{{ $tag->id }}"
                class="rounded-4xl relative flex h-28 flex-col justify-center overflow-hidden border border-orange-200 bg-orange-100 p-5 transition-transform active:scale-90">
                <span class="relative z-10 text-lg font-bold leading-tight text-orange-900">Comida<br>Rápida</span>
                <i class="bxf bx-burger absolute -bottom-3 -right-3 text-7xl text-orange-300"></i>
            </a>
            @endif

            @if($tag = $this->topTags->firstWhere('name', 'Vegano'))
            <a wire:navigate href="/tag/{{ $tag->id }}"
                class="rounded-4xl relative flex h-28 flex-col justify-center overflow-hidden border border-emerald-200 bg-emerald-50 p-5 transition-transform active:scale-90">
                <span class="relative z-10 text-lg font-bold leading-tight text-emerald-900">Saludable<br>& Fit</span>
                <i class="bxf bx-carrot absolute -bottom-3 -right-3 text-7xl text-emerald-300"></i>
            </a>
            @endif

            @if($tag = $this->topTags->firstWhere('name', 'Café'))
            <a wire:navigate href="/tag/{{ $tag->id }}"
                class="rounded-4xl relative flex h-28 flex-col justify-center overflow-hidden border border-stone-200 bg-stone-100 p-5 transition-transform active:scale-90">
                <span class="relative z-10 text-lg font-bold leading-tight text-stone-900">Bebidas<br>& Café</span>
                <i class="bxf bx-cup-hot absolute -bottom-3 -right-3 text-7xl text-stone-300"></i>
            </a>
            @endif

            @if($tag = $this->topTags->firstWhere('name', 'Postres'))
            <a wire:navigate href="/tag/{{ $tag->id }}"
                class="rounded-4xl relative flex h-28 flex-col justify-center overflow-hidden border border-rose-200 bg-rose-100 p-5 transition-transform active:scale-90">
                <span class="relative z-10 text-lg font-bold leading-tight text-rose-900">Postres<br>& Dulces</span>
                <i class="bxf bx-icecream absolute -bottom-3 -right-3 text-7xl text-rose-300"></i>
            </a>
            @endif
        </div>
    </div>

    <div wire:loading wire:target="search" class="w-full py-4 relative">
        <i class="bxf bx-loader-lines-alt animate-spin absolute left-1/2 -translate-1/2 text-4xl text-red-500"></i>
    </div>

    @if(strlen($search) >= 2)
        <div wire:loading.remove wire:target="search" class="flex w-full flex-col gap-4">
            <h2 class="font-bold text-gray-800">Resultados para "{{ $search }}"</h2>
            <div class="flex flex-col gap-4">
                @forelse($this->businesses as $restaurant)
                    <livewire:restaurant.card
                        :key="'search-res-'.$restaurant->id"
                        :business="$restaurant"
                        :name="$restaurant->name"
                        :type="$restaurant->category?->name ?? 'General'"
                        :stars="4.0"
                        time="30-40min"
                        :image="$restaurant->banner_path
                                ? Storage::temporaryUrl($restaurant->banner_path, now()->addMinutes(10))
                                : 'https://picsum.photos/300/200'"
                    />
                @empty
                    <div class="flex flex-col items-center py-10 text-center">
                        <i class="bxf bx-search-alt text-4xl text-gray-200"></i>
                        <p class="mt-2 text-sm text-gray-400">No encontramos resultados para tu búsqueda.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
