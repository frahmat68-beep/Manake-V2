<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    // Use plural name matching migrations
    protected $table = 'equipments';

    // Equipment Status Constants
    const STATUS_READY = 'ready';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_UNAVAILABLE = 'unavailable';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'specifications',
        'stock',
        'price_per_day',
        'status',
        'image_path',
    ];

    protected $casts = [
        'specifications' => 'array',
        'stock' => 'integer',
        'price_per_day' => 'integer',
    ];

    /**
     * Relationship: Equipment belongs to Category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship: Equipment has many Images.
     */
    public function images()
    {
        return $this->hasMany(EquipmentImage::class);
    }

    /**
     * Relationship: Equipment has many CartItems.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relationship: Equipment has many OrderItems.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Accessor: Get available units count (placeholder, integrates with AvailabilityService later).
     */
    public function getAvailableUnitsAttribute(): int
    {
        return $this->stock;
    }

    /**
     * Check if the equipment status is ready.
     */
    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    /**
     * Check if the equipment is rentable (ready and stock > 0).
     */
    public function isRentable(): bool
    {
        return $this->isReady() && $this->stock > 0;
    }
}
