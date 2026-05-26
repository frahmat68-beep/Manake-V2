<?php

namespace App\Services;

/**
 * Service to manage the rental order state machine.
 * Aligned with the "business process optimization" thesis focal points.
 */
class OrderStatusService
{
    /**
     * Transition an order to the "Paid" state.
     *
     * @param int $orderId
     * @return bool
     */
    public function transitionToPaid(int $orderId): bool
    {
        // TODO: Update order status to paid, trigger notification, adjust locked inventory.
        return true;
    }

    /**
     * Transition an order to "Picked Up" (Active Rental).
     *
     * @param int $orderId
     * @param string $officerName
     * @return bool
     */
    public function transitionToPickedUp(int $orderId, string $officerName): bool
    {
        // TODO: Mark items as physically handed over, record officer/admin name.
        return true;
    }

    /**
     * Transition an order to "Returned" and handle checks for damage/lateness.
     *
     * @param int $orderId
     * @param string $returnDate
     * @param array $conditionNotes
     * @return array Summary of charges (refunded deposit or late penalty)
     */
    public function transitionToReturned(int $orderId, string $returnDate, array $conditionNotes = []): array
    {
        // TODO: Compute if actual return date is past scheduled return date.
        // If yes, transition to late_penalty status and compute penalty fee per day.
        // If clean return, trigger deposit refund request.
        
        return [
            'status' => 'returned',
            'penalty_applied' => 0,
            'refund_deposit_amount' => 50000,
        ];
    }

    /**
     * Transition order to "Completed" (all debts settled, deposit processed).
     *
     * @param int $orderId
     * @return bool
     */
    public function transitionToCompleted(int $orderId): bool
    {
        // TODO: Mark order as complete and archive.
        return true;
    }
}
