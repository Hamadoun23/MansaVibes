<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderPdfController extends Controller
{
    public function show(Order $order): View|Response
    {
        return $this->renderPdf($order);
    }

    /**
     * Lien public signé (WhatsApp client) — sans authentification, expire avec l’URL.
     */
    public function showShared(Order $order): View|Response
    {
        return $this->renderPdf($order);
    }

    private function renderPdf(Order $order): View|Response
    {
        $order->load(['items.measurementTemplate', 'client', 'assignee', 'tenant']);

        if (request()->boolean('preview')) {
            return view('modules.orders.pdf', compact('order'));
        }

        $pdf = Pdf::loadView('modules.orders.pdf', compact('order'));

        $safeRef = preg_replace('/[^\w\-]/', '_', (string) $order->reference) ?: 'commande';

        return $pdf->stream('facture-'.$safeRef.'.pdf');
    }
}
