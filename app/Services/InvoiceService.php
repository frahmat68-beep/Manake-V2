<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Service to manage invoice generations using DomPDF.
 * Creates clean, academic-compliant PDFs for rental transactions.
 */
class InvoiceService
{
    /**
     * Render the invoice as direct HTML string for browser previews.
     *
     * @param Order $order
     * @return string
     */
    public function renderHtml(Order $order): string
    {
        return view('invoices.rental_template', ['order' => $order])->render();
    }

    /**
     * Generate PDF Invoice for downloading.
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(Order $order)
    {
        $pdf = Pdf::loadView('invoices.rental_template', ['order' => $order]);
        
        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }
}
