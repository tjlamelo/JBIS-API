<?php

namespace App\Core\Domain\Communication\Services;

use App\Core\Domain\Communication\Contracts\SmsProvider;
use App\Core\Domain\Communication\DTOs\SmsDispatchResultDto;
use App\Core\Domain\Communication\Exceptions\SmsProviderException;
use Illuminate\Support\Facades\Http;

class QueenSmsService implements SmsProvider
{
    public function send(string $phoneNumber, string $message, ?string $senderId = null): SmsDispatchResultDto
    {
        $config = config('services.queensms');
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $apiKey = (string) ($config['api_key'] ?? '');

        // Doc Queen SMS:
        // - Endpoint: {baseUrl}/sms.php
        // - Payload (x-www-form-urlencoded):
        //   api_key, senderid (<= 11 chars), sms, mobiles (comma-separated)
        $effectiveSenderId = $senderId ?: (string) ($config['sender_id'] ?? '');
        if ($baseUrl === '' || $apiKey === '' || $effectiveSenderId === '') {
            throw new SmsProviderException('Configuration Queen SMS manquante.');
        }

        $endpoint = str_ends_with($baseUrl, '/sms.php') ? $baseUrl : $baseUrl.'/sms.php';

        // Queen SMS attend "mobiles" sous forme d'une liste séparée par des virgules.
        // On envoie un seul destinataire ici, mais on nettoie le format (pas de '+', espaces, etc.)
        // pour maximiser la compatibilite.
        $mobiles = preg_replace('/\D+/', '', $phoneNumber) ?? '';

        if ($mobiles === '') {
            return new SmsDispatchResultDto(
                status: 'failed',
                errorMessage: 'Numero de telephone invalide.',
            );
        }

        $response = Http::timeout((int) ($config['timeout'] ?? 10))
            ->asForm()
            ->acceptJson()
            ->post($endpoint, [
                'api_key' => $apiKey,
                'senderid' => $effectiveSenderId,
                'sms' => $message,
                'mobiles' => $mobiles,
            ]);

        if (! $response->successful()) {
            $body = (string) $response->body();
            return new SmsDispatchResultDto(
                status: 'failed',
                errorMessage: sprintf(
                    'Queen SMS HTTP %s: %s',
                    (string) $response->status(),
                    $body
                ),
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            return new SmsDispatchResultDto(
                status: 'failed',
                errorMessage: (string) $response->body(),
            );
        }

        $responseCode = $payload['responsecode'] ?? null;

        if ((string) $responseCode !== '1') {
            $errorCode = $payload['errorcode'] ?? null;
            $errorDescription = $payload['errordescription'] ?? null;
            $responseMessage = $payload['responsemessage'] ?? null;
            $responseDescription = $payload['responsedescription'] ?? null;

            return new SmsDispatchResultDto(
                status: 'failed',
                errorMessage: sprintf(
                    'Queen SMS error (responsecode=%s, responsedescription=%s, responsemessage=%s, errorcode=%s, errordescription=%s)',
                    (string) ($responseCode ?? 'null'),
                    (string) ($responseDescription ?? 'null'),
                    (string) ($responseMessage ?? 'null'),
                    (string) ($errorCode ?? 'null'),
                    (string) ($errorDescription ?? 'null'),
                ),
            );
        }

        $messageId = '';
        if (isset($payload['sms'][0]['messageid'])) {
            $messageId = (string) $payload['sms'][0]['messageid'];
        } elseif (isset($payload['messageid'])) {
            $messageId = (string) $payload['messageid'];
        }

        return new SmsDispatchResultDto(
            status: 'sent',
            providerMessageId: $messageId !== '' ? $messageId : null,
            errorMessage: null,
        );
    }

    public function getCredit(): int
    {
        $config = config('services.queensms');
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $apiKey = (string) ($config['api_key'] ?? '');

        if ($baseUrl === '' || $apiKey === '') {
            throw new SmsProviderException('Configuration Queen SMS manquante.');
        }

        $endpoint = str_ends_with($baseUrl, '/smscredit.php') ? $baseUrl : $baseUrl.'/smscredit.php';

        $response = Http::timeout((int) ($config['timeout'] ?? 10))
            ->asForm()
            ->acceptJson()
            ->post($endpoint, [
                'api_key' => $apiKey,
            ]);

        if (! $response->successful()) {
            throw new SmsProviderException('Erreur HTTP Queen SMS lors de la recuperation du credit.');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new SmsProviderException('Reponse Queen SMS invalide pour le credit.');
        }

        $responseCode = (string) ($payload['responsecode'] ?? '');
        // Si Queen SMS renvoie un responsecode != 1, on ne veut pas faire planter
        // l'API interne (sinon la vue affiche 500). On retourne un credit a 0.
        if ($responseCode !== '1') {
            $credit = $payload['credit'] ?? 0;
            if (is_string($credit) && trim($credit) !== '') {
                return (int) $credit;
            }

            return 0;
        }

        $credit = $payload['credit'] ?? 0;
        if (is_string($credit) && trim($credit) !== '') {
            return (int) $credit;
        }

        return (int) $credit;
    }
}
