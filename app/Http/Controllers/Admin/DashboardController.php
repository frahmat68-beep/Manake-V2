<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Order;

class DashboardController extends Controller
{
    /**
     * Display the admin panel landing dashboard.
     */
    public function index()
    {
        $totalEquipment = Equipment::count();
        $readyEquipment = Equipment::where('status', 'ready')->count();
        $maintenanceEquipment = Equipment::where('status', 'maintenance')->count();
        
        $pendingPayments = Order::where('payment_status', 'pending')->count();
        $paidOrdersCount = Order::where('payment_status', 'paid')->count();
        $revenuePaidTotal = Order::where('payment_status', 'paid')->sum('grand_total');
        
        $ordersWaitingAction = Order::whereIn('rental_status', ['waiting_payment', 'paid', 'processed'])->count();
        
        $latestOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalEquipment',
            'readyEquipment',
            'maintenanceEquipment',
            'pendingPayments',
            'paidOrdersCount',
            'revenuePaidTotal',
            'ordersWaitingAction',
            'latestOrders'
        ));
    }
}
