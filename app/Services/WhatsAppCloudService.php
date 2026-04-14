<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudService
{
    public function isConfigured(): bool
    {
        $c = config('whatsapp.cloud');

        return $c['enabled']
            && is_string($c['access_token']) && $c['access_token'] !== ''
            && is_string($c['phone_number_id']) && $c['phone_number_id'] !== '';
    }

    public function sendPdfTo(string $toDigits, string $pdfBinary, string $filename, ?string $caption = null): void
    {
        $mediaId = $this->uploadPdfBinary($pdfBinary, $filename);
        $this->sendDocumentMessage($toDigits, $mediaId, $filename, $caption);
    }

    private function uploadPdfBinary(string $binary, string $filename): string
    {
        $c = config('whatsapp.cloud');
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/media',
            $c['api_version'],
            $c['phone_number_id']
        );

        $response = Http::withToken($c['access_token'])
            ->timeout(120)
            ->attach('file', $binary, $filename, ['Content-Type' => 'application/pdf'])
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'type' => 'application/pdf',
            ]);

        return $this->extractMediaId($response);
    }

    private function sendDocumentMessage(string $toDigits, string $mediaId, string $filename, ?string $caption): void
    {
        $c = config('whatsapp.cloud');
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $c['api_version'],
            $c['phone_number_id']
        );

        $document = [
            'id' => $mediaId,
            'filename' => $filename,
        ];
        if ($caption !== null && $caption !== '') {
            $document['caption'] = $caption;
        }

        $response = Http::withToken($c['access_token'])
            ->timeout(60)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $toDigits,
                'type' => 'document',
                'document' => $document,
            ]);

        if (! $response->successful()) {
            Log::warning('whatsapp.cloud.send_failed', ['body' => $response->body()]);
            throw new \RuntimeException($this->formatApiError($response));
        }
    }

    private function extractMediaId(Response $response): string
    {
        if (! $response->successful()) {
            Log::warning('whatsapp.cloud.media_upload_failed', ['body' => $response->body()]);
            throw new \RuntimeException($this->formatApiError($response));
        }

        $id = $response->json('id');
        if (! is_string($id) || $id === '') {
            throw new \RuntimeException('Réponse Meta invalide : id du média manquant.');
        }

        return $id;
    }

    private function formatApiError(Response $response): string
    {
        $json = $response->json();
        $msg = $json['error']['message'] ?? null;
        if (is_string($msg) && $msg !== '') {
            return $msg;
        }

        $body = $response->body();

        return is_string($body) && $body !== ''
            ? $body
            : 'Erreur API WhatsApp Cloud ('.$response->status().').';
    }
}
