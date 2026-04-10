<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Tag;
use App\Models\Business;
use App\Models\DeliveryAddress;
use App\Models\ServiceZone;
use Illuminate\Support\Facades\Storage;

new #[Title('Tag')] class extends Component {
    public Tag $tag;
    public $city = null;

    public function mount(Tag $tag)
    {
        $this->tag = $tag;
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
        return $this->tag->businesses()
            ->active()
            ->when($this->city, function ($query) {
                $query->where('city_id', $this->city->id);
            })
            ->get();
    }
};
?>

<div class="flex flex-col gap-4 pt-6">
    <div class="flex items-center gap-4 px-4">
        <a wire:navigate href="/" class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-800">
            <i class="bxf bx-arrow-left-stroke text-2xl text-gray-800/40"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $tag->name }}</h1>
    </div>

    <div class="flex w-full flex-col gap-2 px-4">
        <div class="flex flex-col gap-4">
            @forelse($this->businesses as $restaurant)
                <livewire:restaurant.card
                    :key="'res-'.$restaurant->id"
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
                <div class="flex flex-col items-center py-24 text-center">
                    <i class="bxf bx-search-alt text-4xl text-gray-200"></i>
                    <p class="mt-2 text-sm text-gray-400 text-balance">
                        No encontramos restaurantes con el tag "{{ $tag->name }}" en tu zona.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
