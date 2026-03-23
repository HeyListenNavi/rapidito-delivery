<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tag;
use App\Models\Category;
use App\Models\Product;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        "phone",
        "email",
        "web_site",
        'status',
        'category_id',
        'address',
        'city_id',
        'state',
        'postal_code',
        'country',
        'lat',
        'lng',
        'google_maps_url',
        'logo_path',
        'banner_path',
        'reference_image',
        'is_open',
        'accepts_delivery',
        'accepts_pickup',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'accepts_delivery' => 'boolean',
        'accepts_pickup' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, "business_id");
    }

    public function category()
    {
        return $this->belongsTo(Category::class, "category_id");
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, "business_tag", "business_id", "tag_id")
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, "business_user", "business_id", "user_id");
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOpen($query)
    {
        return $query->where('is_open', true);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
