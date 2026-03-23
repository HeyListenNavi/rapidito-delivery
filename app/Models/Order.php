<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\OrderStatus;
use App\Enums\DeliveryStatus;
use App\Enums\OrderLifecycleStatus;
use App\Enums\PaymentStatus;
use App\Enums\RestaurantDecisionStatus;


class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'driver_id',

        'lifecycle_status',
        'business_decision_status',
        'delivery_status',
        'payment_status',

        "special_instructions",

        'subtotal',
        'delivery_fee',
        'total',
        "payment_method",
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',

        'lifecycle_status' => OrderLifecycleStatus::class,
        'business_decision_status' => RestaurantDecisionStatus::class,
        'delivery_status' => DeliveryStatus::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function dropoffLocations()
    {
        return $this->hasMany(OrderDropoffLocation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Business::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');

        $this->subtotal = $subtotal;
        $this->total = $subtotal + $this->delivery_fee;

        $this->save();
    }
}