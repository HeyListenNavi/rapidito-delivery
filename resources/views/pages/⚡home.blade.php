<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\ServiceZone;
use App\Models\DeliveryAddress;
use App\Models\Tag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Enums\AddressSource;

new #[Title('Home')] class extends Component {
    public ?float $lat = null;
    public ?float $lng = null;

    public $city = null;
    public bool $noService = false;
    public bool $locationDenied = false;
    public bool $hasAddress = false;

    protected $listeners = [
        'locationDetected' => 'setLocation',
        'locationDenied' => 'handleDenied',
    ];

    public function mount()
    {
        $guestToken = request()->cookie('guest_token');

        if ($guestToken) {
            $address = DeliveryAddress::where('guest_token', $guestToken)->latest()->first();

            if ($address && $address->lat && $address->lng) {
                $this->lat = $address->lat;
                $this->lng = $address->lng;
                $this->hasAddress = true;
                $this->resolveServiceZone();
            }
        }
    }

    public function handleDenied()
    {
        $this->locationDenied = true;
        $this->noService = false;
        $this->city = null;
        $this->dispatch('addressUpdated');
    }

    public function setLocation($lat, $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->saveAddress();
        $this->resolveServiceZone();
        $this->hasAddress = true;
        $this->dispatch('addressUpdated');
    }

    protected function resolveServiceZone()
    {
        $zone = ServiceZone::active()
            ->with('city.businesses')
            ->get()
            ->first(fn ($zone) => $zone->contains($this->lat, $this->lng));

        if (!$zone) {
            $this->noService = true;
            $this->city = null;
        } else {
            $this->noService = false;
            $this->city = $zone->city;
        }
    }

    protected function saveAddress()
    {
        $guestToken = request()->cookie('guest_token');

        if (!$this->lat || !$this->lng || !$guestToken) return;

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => "{$this->lat},{$this->lng}",
            'key' => config('services.google_maps.key'),
            'language' => 'es'
        ]);

        $data = $response->json();

        if (!isset($data['results'][0])) {
            Log::error('Error API Google:', $data ?? []);
            return;
        }

        $result = $data['results'][0];
        $components = $result['address_components'];

        $calle = $this->extractComponent($components, ['route']);
        $numero = $this->extractComponent($components, ['street_number']);
        $colonia = $this->extractComponent($components, ['sublocality', 'sublocality_level_1', 'neighborhood']);
        $ciudad = $this->extractComponent($components, ['locality', 'administrative_area_level_2']);
        $estado = $this->extractComponent($components, ['administrative_area_level_1']);
        $codigoPostal = $this->extractComponent($components, ['postal_code']);
        $pais = $this->extractComponent($components, ['country']);

        DeliveryAddress::updateOrCreate(
            ['guest_token' => $guestToken],
            [
                'formatted_address' => $result['formatted_address'],
                'street' => $calle,
                'street_number' => $numero,
                'neighborhood' => $colonia,
                'city' => $ciudad,
                'state' => $estado,
                'postal_code' => $codigoPostal,
                'country' => $pais ?? 'México',
                'lat' => $this->lat,
                'lng' => $this->lng,
                "source" => AddressSource::GPS,
                'place_id' => $result['place_id'] ?? null,
            ]
        );
    }

    protected function extractComponent(array $components, array $types): ?string
    {
        foreach ($components as $component) {
            if (!empty(array_intersect($types, $component['types']))) {
                return $component['long_name'];
            }
        }
        return null;
    }

    #[Computed]
    public function tags()
    {
        return Tag::whereIn('name', [
            'Tacos', 'Hamburguesas', 'Pizza', 'Sushi', 'Mariscos',
            'Vegano', 'Postres', 'Café', 'Alitas', 'Desayunos'
        ])->get();
    }

    #[Computed]
    public function filteredBusinesses()
    {
        if (!$this->city) return collect();

        return $this->city->businesses()
            ->active()
            ->get();
    }
};
?>

<div class="flex flex-col gap-8 pt-4">

    {{-- Estado de ubicación denegada --}}
    @if($locationDenied)
    <div class="flex flex-col items-center justify-center px-6 py-24 gap-8 text-center">
        <span class="text-6xl text-red-500">
            <i class="bxf bx-lock-alt"></i>
        </span>
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
            Necesitamos acceso a tu ubicación
        </h2>
        <p class="max-w-xs text-sm leading-relaxed text-gray-500">
            No pudimos acceder a tu ubicación. Necesitamos saber dónde estás para mostrarte los mejores sabores a tu alrededor.
        </p>
        <div class="flex flex-col gap-4">
            <p class="text-sm font-medium text-gray-400">
                ¿Prefieres hacerlo tú mismo?
            </p>
            <a wire:navigate href="/location"
                class="block w-full text-center bg-red-500 text-white font-bold p-4 rounded-2xl hover:bg-red-600 active:scale-[0.98] transition-all disabled:opacity-50 disabled:active:scale-100 shadow-md shadow-red-200">
                Seleccionar ubicación manualmente
            </a>
            <p class="text-sm text-gray-400 italic">
                Prometemos no seguirte hasta tu cocina (solo hasta la puerta)
            </p>
        </div>
    </div>

    {{-- Hay ciudad y cobertura --}}
    @elseif($city)
    <div class="flex w-full flex-col gap-2">
        <div class="flex items-center justify-between px-4">
            <h2 class="font-bold text-gray-800">Explorar por categorías</h2>
        </div>

        <div class="no-scrollbar flex gap-8 overflow-x-auto px-4">
            @foreach($this->tags as $tag)
            <a wire:navigate href="/tag/{{ $tag->id }}" class="flex flex-col items-center gap-1 shrink-0">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-200 text-2xl">
                    @php
                        $icons = [
                            'Tacos' => '🌮',
                            'Hamburguesas' => '🍔',
                            'Pizza' => '🍕',
                            'Sushi' => '🍣',
                            'Mariscos' => '🍤',
                            'Vegano' => '🥗',
                            'Postres' => '🍰',
                            'Café' => '☕',
                            'Alitas' => '🍗',
                            'Desayunos' => '🍳',
                        ];
                    @endphp
                    {{ $icons[$tag->name] ?? '🍴' }}
                </div>
                <span class="text-xs font-medium text-gray-600">{{ $tag->name }}</span>
            </a>
            @endforeach
        </div>
    </div>

    <div class="flex w-full flex-col gap-2 px-4">
        <h2 class="font-bold text-gray-800">
            Restaurantes cerca
        </h2>

        <div class="flex flex-col gap-4">
            @forelse($this->filteredBusinesses as $restaurant)
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
                <div class="flex flex-col items-center py-10 text-center">
                    <i class="bxf bx-search-alt text-4xl text-gray-200"></i>
                    <p class="mt-2 text-sm text-gray-400 text-balance">
                        No encontramos restaurantes en tu zona actualmente.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- No hay cobertura --}}
    @elseif($noService)
    <div class="flex flex-col items-center justify-center px-6 py-24 gap-8 text-center">
        <span class="text-6xl text-red-500">:(</span>
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
            ¡Vaya! Aún no llegamos ahí
        </h2>
        <p class="max-w-xs text-sm leading-relaxed text-gray-500">
            Lo sentimos mucho, pero aún no tenemos cobertura en tu ubicación actual.
        </p>
        <div class="flex flex-col gap-4">
            <p class="text-sm font-medium text-gray-400">
                ¿Crees que es un error?
            </p>
            <a wire:navigate href="/location"
                class="block w-full text-center bg-red-500 text-white font-bold p-4 rounded-2xl hover:bg-red-600 active:scale-[0.98] transition-all disabled:opacity-50 disabled:active:scale-100 shadow-md shadow-red-200">
                Seleccionar ubicación manualmente
            </a>
            <p class="text-sm text-gray-400 italic">
                A veces el GPS tiene hambre y se confunde un poco
            </p>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:navigated', () => {
            // Evaluamos la propiedad del componente directamente de forma limpia, sin usar Eloquent aquí
            const alreadyHasAddress = {{ $hasAddress ? 'true' : 'false' }};

            if (alreadyHasAddress) {
                return;
            }

            if (!navigator.geolocation) return;

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    Livewire.dispatch('locationDetected', {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                },
                function (error) {
                    console.log('Geolocation error:', error);
                    Livewire.dispatch('locationDenied');
                }
            );
        });
    </script>
</div>
