<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'equipment_id',
        'equipment_name',
        'equipment_slug',
        'qty',
        'price_per_day',
        'item_subtotal',
        'rental_start_date',
        'rental_end_date',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price_per_day' => 'integer',
        'item_subtotal' => 'integer',
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
    ];

    /**
     * Relationship: OrderItem belongs to Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: OrderItem belongs to Equipment.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Accessor: Calculate inclusive duration days.
     */
    public function getDurationDaysAttribute(): int
    {
        if (!$this->rental_start_date || !$this->rental_end_date) {
            return 1;
        }

        return Order::calculateDurationDays($this->rental_start_date, $this->rental_end_date);
    }

    /**
     * Accessor: Computed subtotal.
     */
    public function getComputedSubtotalAttribute(): int
    {
        return $this->qty * $this->price_per_day * $this->duration_days;
    }
}
