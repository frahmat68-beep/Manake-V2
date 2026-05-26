<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Order Payment Status Constants
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_EXPIRED = 'expired';
    const PAYMENT_REFUNDED = 'refunded';

    // Order Rental Status Constants
    const RENTAL_WAITING_PAYMENT = 'waiting_payment';
    const RENTAL_PAID = 'paid';
    const RENTAL_PROCESSED = 'processed';
    const RENTAL_PICKED_UP = 'picked_up';
    const RENTAL_RETURNED = 'returned';
    const RENTAL_DAMAGED = 'damaged';
    const RENTAL_LOST = 'lost';
    const RENTAL_COMPLETED = 'completed';
    const RENTAL_CANCELLED = 'cancelled';
    const RENTAL_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'order_number',
        'rental_start_date',
        'rental_end_date',
        'duration_days',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'additional_fee',
        'grand_total',
        'payment_status',
        'rental_status',
        'midtrans_order_id',
        'reschedule_count',
        'paid_at',
        'expired_at',
        'notes',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'duration_days' => 'integer',
        'subtotal' => 'integer',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'integer',
        'total_amount' => 'integer',
        'additional_fee' => 'integer',
        'grand_total' => 'integer',
        'reschedule_count' => 'integer',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * Relationship: Order belongs to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Order has many OrderItems.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship: Order has one Payment.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Relationship: Order has many Status Logs.
     */
    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    /**
     * Helper check: check if the order has been paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Helper check: check if the order is eligible for rescheduling.
     * Booking allows maximum of 1 rescheduling.
     */
    public function canBeRescheduled(): bool
    {
        return $this->reschedule_count < 1 &&
            in_array($this->rental_status, [self::RENTAL_WAITING_PAYMENT, self::RENTAL_PAID]);
    }

    /**
     * Calculate rental duration days between start and end dates.
     * Returns absolute count of days (inclusive).
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return int
     */
    public static function calculateDurationDays($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Standard rental inclusive duration: end date minus start date plus 1 day
        // For example: 2026-05-26 to 2026-05-27 is 2 days.
        return max(1, $start->diffInDays($end) + 1);
    }
}
