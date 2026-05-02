<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Location\Coordinate;
use Location\Polygon as GeoPolygon;

class ServiceZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'polygon',
        'active',
        'debug',
        'bbox_min_lat',
        'bbox_max_lat',
        'bbox_min_lng',
        'bbox_max_lng',
    ];

    protected $casts = [
        'polygon' => 'array',
        'active' => 'boolean',
        'debug' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($zone) {
            $coordinates = [];

            $features = $zone->polygon['features'] ?? [];
            foreach ($features as $feature) {
                if (($feature['geometry']['type'] ?? null) === 'Polygon') {
                    $coordinates = $feature['geometry']['coordinates'][0] ?? [];
                    break;
                }
            }

            if (empty($coordinates)) {
                return;
            }

            $lats = [];
            $lngs = [];

            foreach ($coordinates as $point) {
                if (isset($point[0], $point[1])) {
                    $lngs[] = $point[0];
                    $lats[] = $point[1];
                }
            }

            if (! empty($lats) && ! empty($lngs)) {
                $zone->bbox_min_lat = min($lats);
                $zone->bbox_max_lat = max($lats);
                $zone->bbox_min_lng = min($lngs);
                $zone->bbox_max_lng = max($lngs);
            }
        });
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function toPhpGeoPolygon(): GeoPolygon
    {
        $polygon = new GeoPolygon;
        $coordinates = [];

        $features = $this->polygon['features'] ?? [];
        foreach ($features as $feature) {
            if (($feature['geometry']['type'] ?? null) === 'Polygon') {
                $coordinates = $feature['geometry']['coordinates'][0] ?? [];
                break;
            }
        }

        foreach ($coordinates as $point) {
            $polygon->addPoint(new Coordinate($point[1], $point[0]));
        }

        return $polygon;
    }

    public function contains(float $lat, float $lng): bool
    {
        if (! $this->active) {
            return false;
        }

        if (
            $lat < $this->bbox_min_lat ||
            $lat > $this->bbox_max_lat ||
            $lng < $this->bbox_min_lng ||
            $lng > $this->bbox_max_lng
        ) {
            return false;
        }

        $coordinate = new Coordinate($lat, $lng);

        return $this->toPhpGeoPolygon()->contains($coordinate);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeDebug($query)
    {
        return $query->where('debug', true);
    }
}
