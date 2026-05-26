<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index()
    {
        $payments = Payment::with(['order.user'])
            ->latest()
            ->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }
}
