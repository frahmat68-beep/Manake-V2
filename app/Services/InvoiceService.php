<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Service to manage invoice generations using DomPDF.
 * Creates clean, academic-compliant PDFs for rental transactions.
 */
class InvoiceService
{
    /**
     * Generate PDF Invoice for an order.
     *
     * @param array $orderData
     * @return \Illuminate\Http\Response
     */
    public function downloadInvoicePdf(array $orderData)
    {
        // Render a Blade view into PDF.
        // We will make sure the PDF has correct CSS styles.
        $pdf = Pdf::loadView('invoices.rental_template', compact('orderData'));
        
        return $pdf->download('invoice-' . ($orderData['order_id'] ?? 'unknown') . '.pdf');
    }

    /**
     * Stream PDF Invoice for inside browser viewing.
     *
     * @param array $orderData
     * @return \Illuminate\Http\Response
     */
    public function streamInvoicePdf(array $orderData)
    {
        $pdf = Pdf::loadView('invoices.rental_template', compact('orderData'));
        
        return $pdf->stream();
    }
}
