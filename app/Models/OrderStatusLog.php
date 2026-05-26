<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'actor_type',
        'from_status',
        'to_status',
        'note',
        'additional_fee',
        'metadata',
    ];

    protected $casts = [
        'additional_fee' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Relationship: Log belongs to Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: Log belongs to User (actor).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
