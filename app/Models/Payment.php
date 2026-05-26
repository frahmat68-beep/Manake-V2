<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'snap_token',
        'snap_redirect_url',
        'payment_type',
        'transaction_status',
        'fraud_status',
        'status',
        'gross_amount',
        'paid_at',
        'raw_payload',
    ];

    protected $casts = [
        'gross_amount' => 'integer',
        'paid_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    /**
     * Relationship: Payment belongs to Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
