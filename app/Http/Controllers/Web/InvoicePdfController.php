<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoicePdfController extends Controller
{
    public function show(Invoice $invoice): View|Response
    {
        $invoice->load(['client', 'items']);

        if (request()->boolean('preview')) {
            return view('modules.invoices.pdf', compact('invoice'));
        }

        $pdf = Pdf::loadView('modules.invoices.pdf', compact('invoice'));

        return $pdf->stream('facture-'.$invoice->number.'.pdf');
    }
}
