<?php

namespace App\Services;

use App\Models\User;
use App\Support\Format;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class WhatsAppCloudApi
{
    public function isConfigured(): bool
    {
        return filled(config('services.whatsapp.token'))
            && filled(config('services.whatsapp.phone_number_id'))
            && filled(config('services.whatsapp.template'))
            && filled(config('services.whatsapp.graph_version'));
    }

    /**
     * @param Collection<int, User> $recipients
     * @return array{sent: int, failed: int}
     */
    public function broadcast(Collection $recipients, string $message): array
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'share' => 'Konfigurasi Meta WhatsApp Cloud API belum lengkap. Periksa environment server.',
            ]);
        }

        $sent = 0;
        $failed = 0;
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            config('services.whatsapp.graph_version'),
            config('services.whatsapp.phone_number_id'),
        );

        foreach ($recipients as $recipient) {
            try {
                $response = Http::withToken(config('services.whatsapp.token'))
                    ->acceptJson()
                    ->timeout(15)
                    ->post($url, [
                        'messaging_product' => 'whatsapp',
                        'to' => Format::waNumber($recipient->whatsapp_number),
                        'type' => 'template',
                        'template' => [
                            'name' => config('services.whatsapp.template'),
                            'language' => ['code' => config('services.whatsapp.template_language')],
                            'components' => [[
                                'type' => 'body',
                                'parameters' => [['type' => 'text', 'text' => $message]],
                            ]],
                        ],
                    ]);

                if (! $response->successful()) {
                    $failed++;
                    Log::warning('Meta WhatsApp API rejected a message.', [
                        'user_id' => $recipient->id,
                        'status' => $response->status(),
                        'error_code' => $response->json('error.code'),
                        'error_type' => $response->json('error.type'),
                    ]);

                    continue;
                }

                $sent++;
            } catch (ConnectionException $exception) {
                $failed++;
                Log::warning('Meta WhatsApp API connection failed.', [
                    'user_id' => $recipient->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
