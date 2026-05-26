<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'equipment_id',
        'rental_start_date',
        'rental_end_date',
        'qty',
        'price_per_day',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'qty' => 'integer',
        'price_per_day' => 'integer',
    ];

    /**
     * Relationship: CartItem belongs to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: CartItem belongs to Equipment.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
