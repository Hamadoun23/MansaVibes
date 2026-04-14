<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Services\WhatsAppCloudService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
class OrderWhatsAppController extends Controller
{
    public function sendInvoice(Order $order, WhatsAppCloudService $whatsapp): RedirectResponse
    {
        return $this->sendPdfMessage(
            $order,
            $whatsapp,
            'Facture '.$order->reference.' — '.number_format($order->total_cents / 100, 0, ',', ' ').' FCFA',
            'Facture PDF envoyée par WhatsApp (pièce jointe).'
        );
    }

    public function sendReceipt(Order $order, WhatsAppCloudService $whatsapp): RedirectResponse
    {
        $paid = number_format($order->advance_payment_cents / 100, 0, ',', ' ').' FCFA';
        $mode = $order->paymentMethodLabel() ?? '—';

        return $this->sendPdfMessage(
            $order,
            $whatsapp,
            'Accusé de paiement — '.$order->reference.'. Versé : '.$paid.' ('.$mode.').',
            'Reçu / facture PDF envoyé par WhatsApp (pièce jointe).'
        );
    }

    private function sendPdfMessage(
        Order $order,
        WhatsAppCloudService $whatsapp,
        string $caption,
        string $successMessage,
    ): RedirectResponse {
        if (! $whatsapp->isConfigured()) {
            return redirect()
                ->back()
                ->withErrors(['whatsapp_cloud' => 'API WhatsApp Cloud non configurée (voir .env : WHATSAPP_CLOUD_*).']);
        }

        $order->load(['items.measurementTemplate', 'client', 'assignee', 'tenant']);
        $digits = $order->clientWhatsAppDigits();
        if ($digits === null) {
            return redirect()
                ->back()
                ->withErrors(['whatsapp_cloud' => 'Numéro de téléphone du client manquant ou invalide.']);
        }

        $pdf = Pdf::loadView('modules.orders.pdf', ['order' => $order]);
        $binary = $pdf->output();
        $safeRef = preg_replace('/[^\w\-]/', '_', (string) $order->reference) ?: 'commande';
        $filename = 'facture-'.$safeRef.'.pdf';

        try {
            $whatsapp->sendPdfTo($digits, $binary, $filename, $caption);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withErrors(['whatsapp_cloud' => $e->getMessage()]);
        }

        NotificationLog::query()->create([
            'channel' => 'whatsapp',
            'recipient' => $digits,
            'body' => $caption,
            'status' => 'sent',
            'meta' => [
                'order_id' => $order->id,
                'type' => 'pdf_document',
                'filename' => $filename,
            ],
        ]);

        return redirect()->back()->with('status', $successMessage);
    }
}
