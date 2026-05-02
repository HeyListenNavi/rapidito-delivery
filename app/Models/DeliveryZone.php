<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Location\Coordinate;
use Location\Polygon as GeoPolygon;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_zone_id',
        'name',
        'polygon_json',
        'delivery_price',
        'active',
        'bbox_min_lat',
        'bbox_max_lat',
        'bbox_min_lng',
        'bbox_max_lng',
    ];

    protected $casts = [
        'polygon_json' => 'array',
        'delivery_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot: calcular BBOX automáticamente
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::saving(function ($zone) {

            $coordinates = [];

            // Extraer las coordenadas validando la estructura GeoJSON (FeatureCollection)
            if (isset($zone->polygon_json['features'][0]['geometry']['type']) && $zone->polygon_json['features'][0]['geometry']['type'] === 'Polygon') {
                $coordinates = $zone->polygon_json['features'][0]['geometry']['coordinates'][0] ?? [];
            }

            $lats = [];
            $lngs = [];

            foreach ($coordinates as $point) {
                $lngs[] = $point[0];
                $lats[] = $point[1];
            }

            // Validación defensiva para evitar el error de min() vacío
            if (!empty($lats) && !empty($lngs)) {
                $zone->bbox_min_lat = min($lats);
                $zone->bbox_max_lat = max($lats);
                $zone->bbox_min_lng = min($lngs);
                $zone->bbox_max_lng = max($lngs);
            } else {
                // Valores por defecto seguros si el polígono viene mal formado
                $zone->bbox_min_lat = 0;
                $zone->bbox_max_lat = 0;
                $zone->bbox_min_lng = 0;
                $zone->bbox_max_lng = 0;
            }
        });
    }

    public function serviceZone()
    {
        return $this->belongsTo(ServiceZone::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeWhereBboxContains($query, float $lat, float $lng)
    {
        return $query
            ->where('bbox_min_lat', '<=', $lat)
            ->where('bbox_max_lat', '>=', $lat)
            ->where('bbox_min_lng', '<=', $lng)
            ->where('bbox_max_lng', '>=', $lng);
    }

    public function toPhpGeoPolygon(): GeoPolygon
    {
        $polygon = new GeoPolygon();

        $coordinates = [];

        // Misma lógica de extracción robusta para cuando necesites instanciar el polígono
        if (isset($this->polygon_json['features'][0]['geometry']['type']) && $this->polygon_json['features'][0]['geometry']['type'] === 'Polygon') {
            $coordinates = $this->polygon_json['features'][0]['geometry']['coordinates'][0] ?? [];
        }

        foreach ($coordinates as $point) {
            $polygon->addPoint(new Coordinate($point[1], $point[0])); // lat, lng
        }

        return $polygon;
    }

    public function contains(float $lat, float $lng): bool
    {
        if (!$this->active) {
            return false;
        }

        // Primero verificamos el BBOX (Caja delimitadora) para descartar rápido y ahorrar CPU
        if (
            $lat < $this->bbox_min_lat ||
            $lat > $this->bbox_max_lat ||
            $lng < $this->bbox_min_lng ||
            $lng > $this->bbox_max_lng
        ) {
            return false;
        }

        // Si está dentro de la caja, hacemos la comprobación matemática exacta del polígono
        $coordinate = new Coordinate($lat, $lng);

        return $this->toPhpGeoPolygon()->contains($coordinate);
    }

    public function outgoingFares()
    {
        return $this->hasMany(DeliveryZoneFare::class, 'from_zone_id');
    }

    public function incomingFares()
    {
        return $this->hasMany(DeliveryZoneFare::class, 'to_zone_id');
    }
}
