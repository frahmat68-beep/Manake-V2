<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Service to handle equipment availability and booking rules.
 * Aligned with optimal business processes for rental equipment.
 */
class AvailabilityService
{
    /**
     * Normalize and validate rental date range.
     * Dates are inclusive and must be valid.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array [Carbon $start, Carbon $end]
     * @throws ValidationException
     */
    public function normalizeDateRange($startDate, $endDate): array
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'rental_start_date' => 'Format tanggal sewa tidak valid.',
            ]);
        }

        $today = Carbon::today();

        if ($start->lt($today)) {
            throw ValidationException::withMessages([
                'rental_start_date' => 'Tanggal mulai sewa tidak boleh sebelum hari ini.',
            ]);
        }

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'rental_end_date' => 'Tanggal akhir sewa tidak boleh sebelum tanggal mulai.',
            ]);
        }

        return [$start, $end];
    }

    /**
     * Get the buffered range (minus 1 day from start, plus 1 day from end).
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    public function getBufferedRange(Carbon $start, Carbon $end): array
    {
        return [
            'buffer_start' => $start->copy()->subDay(),
            'buffer_end' => $end->copy()->addDay(),
        ];
    }

    /**
     * Count units currently reserved during the requested buffered date range.
     * Ignores inactive orders (failed, expired, refunded, completed, cancelled).
     *
     * @param Equipment $equipment
     * @param Carbon $start
     * @param Carbon $end
     * @param int|null $excludeOrderId
     * @return int
     */
    public function countReservedUnits(Equipment $equipment, Carbon $start, Carbon $end, ?int $excludeOrderId = null): int
    {
        $buffers = $this->getBufferedRange($start, $end);
        $bufferStart = $buffers['buffer_start']->toDateString();
        $bufferEnd = $buffers['buffer_end']->toDateString();

        // Query active orders overlapping with the buffered request range
        $query = \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.equipment_id', $equipment->id)
            // Exclude inactive payment statuses
            ->whereNotIn('orders.payment_status', [
                Order::PAYMENT_FAILED,
                Order::PAYMENT_EXPIRED,
                Order::PAYMENT_REFUNDED
            ])
            // Exclude inactive rental statuses
            ->whereNotIn('orders.rental_status', [
                Order::RENTAL_COMPLETED,
                Order::RENTAL_CANCELLED,
                Order::RENTAL_EXPIRED
            ])
            // Check overlaps: Order rental dates overlap with buffered range
            ->where('orders.rental_start_date', '<=', $bufferEnd)
            ->where('orders.rental_end_date', '>=', $bufferStart);

        if ($excludeOrderId !== null) {
            $query->where('orders.id', '!=', $excludeOrderId);
        }

        return (int) $query->sum('order_items.qty');
    }

    /**
     * Get available stock units count.
     *
     * @param Equipment $equipment
     * @param Carbon $start
     * @param Carbon $end
     * @param int|null $excludeOrderId
     * @return int
     */
    public function getAvailableUnits(Equipment $equipment, Carbon $start, Carbon $end, ?int $excludeOrderId = null): int
    {
        if (!$equipment->isReady()) {
            return 0;
        }

        $reserved = $this->countReservedUnits($equipment, $start, $end, $excludeOrderId);

        return max($equipment->stock - $reserved, 0);
    }

    /**
     * Check if requested quantity of equipment is available.
     *
     * @param Equipment $equipment
     * @param Carbon|string $start
     * @param Carbon|string $end
     * @param int $qty
     * @param int|null $excludeOrderId
     * @return bool
     */
    public function isAvailable(Equipment $equipment, $start, $end, int $qty = 1, ?int $excludeOrderId = null): bool
    {
        if ($qty <= 0) {
            return false;
        }

        if ($start instanceof Carbon && $end instanceof Carbon) {
            $normalizedStart = $start;
            $normalizedEnd = $end;
        } else {
            try {
                [$normalizedStart, $normalizedEnd] = $this->normalizeDateRange($start, $end);
            } catch (ValidationException $e) {
                return false;
            }
        }

        $available = $this->getAvailableUnits($equipment, $normalizedStart, $normalizedEnd, $excludeOrderId);

        return $available >= $qty;
    }

    /**
     * Assert equipment is available, throwing ValidationException if not.
     *
     * @param Equipment $equipment
     * @param Carbon|string $start
     * @param Carbon|string $end
     * @param int $qty
     * @param int|null $excludeOrderId
     * @return void
     * @throws ValidationException
     */
    public function assertAvailable(Equipment $equipment, $start, $end, int $qty = 1, ?int $excludeOrderId = null): void
    {
        [$normalizedStart, $normalizedEnd] = $this->normalizeDateRange($start, $end);

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah sewa alat tidak boleh kurang dari 1.',
            ]);
        }

        if (!$equipment->isReady()) {
            throw ValidationException::withMessages([
                'equipment' => "Alat media {$equipment->name} sedang tidak siap disewa (status: {$equipment->status}).",
            ]);
        }

        $available = $this->getAvailableUnits($equipment, $normalizedStart, $normalizedEnd, $excludeOrderId);

        if ($available < $qty) {
            throw ValidationException::withMessages([
                'qty' => "Alat sewa {$equipment->name} tidak mencukupi untuk tanggal tersebut. Sisa stok tersedia: {$available} unit (Anda meminta {$qty} unit).",
            ]);
        }
    }

    /**
     * Get summary details of equipment availability.
     *
     * @param Equipment $equipment
     * @param Carbon|string $start
     * @param Carbon|string $end
     * @param int $qty
     * @return array
     */
    public function getAvailabilitySummary(Equipment $equipment, $start, $end, int $qty = 1): array
    {
        try {
            [$normalizedStart, $normalizedEnd] = $this->normalizeDateRange($start, $end);
            $isValid = true;
            $validationMsg = '';
        } catch (ValidationException $e) {
            $normalizedStart = Carbon::parse($start)->startOfDay();
            $normalizedEnd = Carbon::parse($end)->startOfDay();
            $isValid = false;
            $validationMsg = $e->errors()['rental_start_date'][0] ?? ($e->errors()['rental_end_date'][0] ?? 'Tanggal tidak valid.');
        }

        $buffers = $this->getBufferedRange($normalizedStart, $normalizedEnd);
        $reserved = $isValid ? $this->countReservedUnits($equipment, $normalizedStart, $normalizedEnd) : 0;
        $available = $isValid ? $this->getAvailableUnits($equipment, $normalizedStart, $normalizedEnd) : 0;
        $ok = $isValid && ($available >= $qty) && $equipment->isReady() && ($qty > 0);

        if (!$isValid) {
            $msg = $validationMsg;
        } elseif (!$equipment->isReady()) {
            $msg = "Alat sewa sedang {$equipment->status}.";
        } elseif ($available < $qty) {
            $msg = "Stok tidak mencukupi. Sisa unit: {$available}.";
        } else {
            $msg = 'Alat sewa tersedia untuk dipesan.';
        }

        return [
            'ok' => $ok,
            'status' => $ok ? 'available' : 'not_available',
            'stock' => $equipment->stock,
            'reserved_units' => $reserved,
            'available_units' => $available,
            'requested_qty' => $qty,
            'start_date' => $normalizedStart->toDateString(),
            'end_date' => $normalizedEnd->toDateString(),
            'buffer_start_date' => $buffers['buffer_start']->toDateString(),
            'buffer_end_date' => $buffers['buffer_end']->toDateString(),
            'message' => $msg,
        ];
    }
}
