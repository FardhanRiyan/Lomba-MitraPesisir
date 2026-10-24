<?php

namespace App\Services;

use App\Models\User;
use App\Support\Format;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class WhatsAppCloudApi
{
    public function __construct(private readonly Client $client)
    {
    }

    public function isConfigured(): bool
    {
        return filled(config('services.whatsapp.token'))
            && preg_match('/^\d+$/', (string) config('services.whatsapp.phone_number_id')) === 1
            && preg_match('/^v\d+\.\d+$/', (string) config('services.whatsapp.graph_version')) === 1
            && $this->hasTrustedApiUrl()
            && filled(config('services.whatsapp.template'))
            && filled(config('services.whatsapp.template_language'));
    }

    /**
     * @return array{sent: int, failed: int}
     */
    public function broadcast(Builder $recipients, string $message): array
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'share' => 'Konfigurasi Meta WhatsApp Cloud API belum lengkap. Periksa environment server.',
            ]);
        }

        $sent = 0;
        $failed = 0;
        $recipients
            ->select(['id', 'whatsapp_number'])
            ->chunkById(100, function ($users) use ($message, &$sent, &$failed): void {
                $requests = function () use ($users, $message, &$failed) {
                    foreach ($users as $recipient) {
                        $phoneNumber = Format::waNumber($recipient->whatsapp_number);
                        if (preg_match('/^\d{8,15}$/', $phoneNumber) !== 1) {
                            $failed++;
                            Log::warning('WhatsApp broadcast skipped because the recipient number is invalid.', [
                                'user_id' => $recipient->id,
                            ]);

                            continue;
                        }

                        yield $recipient->id => new Request(
                            'POST',
                            $this->messagesUrl(),
                            [
                                'Authorization' => 'Bearer '.config('services.whatsapp.token'),
                                'Accept' => 'application/json',
                                'Content-Type' => 'application/json',
                            ],
                            json_encode($this->templatePayload($phoneNumber, $message), JSON_THROW_ON_ERROR),
                        );
                    }
                };

                $pool = new Pool($this->client, $requests(), [
                    'concurrency' => 10,
                    'options' => [
                        'connect_timeout' => 5,
                        'timeout' => 15,
                        'http_errors' => false,
                    ],
                    'fulfilled' => function (ResponseInterface $response, int|string $userId) use (&$sent, &$failed): void {
                        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                            $sent++;

                            return;
                        }

                        $failed++;
                        $this->logApiRejection($userId, $response);
                    },
                    'rejected' => function (mixed $reason, int|string $userId) use (&$failed): void {
                        $failed++;
                        Log::warning('Meta WhatsApp broadcast request failed.', [
                            'user_id' => $userId,
                            'exception' => $reason instanceof Throwable ? $reason::class : get_debug_type($reason),
                        ]);
                    },
                ]);

                $pool->promise()->wait();
            });

        return ['sent' => $sent, 'failed' => $failed];
    }

    public function sendTemplate(User $recipient, string $message): bool
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'share' => 'Konfigurasi Meta WhatsApp Cloud API belum lengkap atau tidak valid.',
            ]);
        }

        $phoneNumber = Format::waNumber($recipient->whatsapp_number);
        if (preg_match('/^\d{8,15}$/', $phoneNumber) !== 1) {
            Log::warning('WhatsApp message skipped because the recipient number is invalid.', [
                'user_id' => $recipient->id,
            ]);

            return false;
        }

        try {
            $response = $this->client->post($this->messagesUrl(), [
                'headers' => [
                    'Authorization' => 'Bearer '.config('services.whatsapp.token'),
                    'Accept' => 'application/json',
                ],
                'connect_timeout' => 5,
                'timeout' => 15,
                'http_errors' => false,
                'json' => $this->templatePayload($phoneNumber, $message),
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return true;
            }

            $this->logApiRejection($recipient->id, $response);
        } catch (GuzzleException $exception) {
            Log::warning('Meta WhatsApp API request failed.', [
                'user_id' => $recipient->id,
                'exception' => $exception::class,
            ]);
        }

        return false;
    }

    private function messagesUrl(): string
    {
        return sprintf(
            '%s/%s/%s/messages',
            rtrim((string) config('services.whatsapp.api_url'), '/'),
            config('services.whatsapp.graph_version'),
            config('services.whatsapp.phone_number_id'),
        );
    }

    private function templatePayload(string $phoneNumber, string $message): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => [
                'name' => config('services.whatsapp.template'),
                'language' => ['code' => config('services.whatsapp.template_language')],
                'components' => [[
                    'type' => 'body',
                    'parameters' => [['type' => 'text', 'text' => $message]],
                ]],
            ],
        ];
    }

    private function logApiRejection(int|string $userId, ResponseInterface $response): void
    {
        $responseBody = json_decode((string) $response->getBody(), true);
        $error = is_array($responseBody) && is_array($responseBody['error'] ?? null)
            ? $responseBody['error']
            : [];

        Log::warning('Meta WhatsApp API rejected a message.', [
            'user_id' => $userId,
            'status' => $response->getStatusCode(),
            'error_code' => $error['code'] ?? null,
            'error_type' => $error['type'] ?? null,
        ]);
    }

    private function hasTrustedApiUrl(): bool
    {
        $url = (string) config('services.whatsapp.api_url');
        $parts = parse_url($url);

        return is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && ($parts['host'] ?? null) === 'graph.facebook.com'
            && ! isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment'])
            && (! isset($parts['port']) || $parts['port'] === 443)
            && in_array($parts['path'] ?? '', ['', '/'], true);
    }
}
