<?php

namespace App\Core\Domain\Communication\Services;

use App\Core\Domain\Communication\Contracts\MailboxProvisioner;
use App\Core\Domain\Communication\DTOs\MailboxCreationResultDto;
use App\Core\Domain\Communication\Exceptions\MailboxProvisioningException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CpanelMailboxProvisionerService implements MailboxProvisioner
{
    public function createMailbox(string $localPart, string $password, ?int $quotaMb = null): MailboxCreationResultDto
    {
        $config = $this->resolveConfig();
        $quota = $quotaMb ?? 1024;

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => sprintf('cpanel %s:%s', $config['username'], $config['token']),
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'].'/Email/add_pop', [
                'email' => $localPart,
                'password' => $password,
                'domain' => $config['domain'],
                'quota' => max($quota, 0),
            ]);

        if (! $response->successful()) {
            $body = (string) $response->body();

            return new MailboxCreationResultDto(
                success: false,
                email: sprintf('%s@%s', $localPart, $config['domain']),
                message: sprintf('Erreur HTTP cPanel: %s', (string) $response->status()),
                rawError: $body !== '' ? $body : null,
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            return new MailboxCreationResultDto(
                success: false,
                email: sprintf('%s@%s', $localPart, $config['domain']),
                message: 'Reponse cPanel invalide.',
                rawError: (string) $response->body(),
            );
        }

        $status = (int) data_get($payload, 'status', 0);
        $errors = data_get($payload, 'errors');
        $errorMessage = is_array($errors) && isset($errors[0]) ? (string) $errors[0] : null;

        if ($status !== 1) {
            return new MailboxCreationResultDto(
                success: false,
                email: sprintf('%s@%s', $localPart, $config['domain']),
                message: $errorMessage ?: 'La creation de la boite mail a echoue.',
                rawError: $errorMessage,
            );
        }

        return new MailboxCreationResultDto(
            success: true,
            email: sprintf('%s@%s', $localPart, $config['domain']),
            message: 'Boite mail creee avec succes.',
            rawError: null,
        );
    }

    public function deleteMailbox(string $localPart): MailboxCreationResultDto
    {
        $config = $this->resolveConfig();

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => sprintf('cpanel %s:%s', $config['username'], $config['token']),
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'].'/Email/delete_pop', [
                'email' => $localPart,
                'domain' => $config['domain'],
            ]);

        if (! $response->successful()) {
            return new MailboxCreationResultDto(
                success: false,
                email: sprintf('%s@%s', $localPart, $config['domain']),
                message: sprintf('Erreur HTTP cPanel: %s', (string) $response->status()),
                rawError: (string) $response->body(),
            );
        }

        $payload = $response->json();
        $status = (int) data_get($payload, 'status', 0);
        $errors = data_get($payload, 'errors');
        $errorMessage = is_array($errors) && isset($errors[0]) ? (string) $errors[0] : null;

        if ($status !== 1) {
            return new MailboxCreationResultDto(
                success: false,
                email: sprintf('%s@%s', $localPart, $config['domain']),
                message: $errorMessage ?: 'La suppression de la boite mail a echoue.',
                rawError: $errorMessage,
            );
        }

        return new MailboxCreationResultDto(
            success: true,
            email: sprintf('%s@%s', $localPart, $config['domain']),
            message: 'Boite mail supprimee avec succes.',
            rawError: null,
        );
    }

    public function listMailboxes(): array
    {
        $config = $this->resolveConfig();

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => sprintf('cpanel %s:%s', $config['username'], $config['token']),
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'].'/Email/list_pops', [
                'domain' => $config['domain'],
            ]);

        if (! $response->successful()) {
            throw new MailboxProvisioningException(sprintf('Erreur HTTP cPanel: %s', (string) $response->status()));
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new MailboxProvisioningException('Reponse cPanel invalide.');
        }

        $status = (int) data_get($payload, 'status', 0);
        if ($status !== 1) {
            $errors = data_get($payload, 'errors');
            $errorMessage = is_array($errors) && isset($errors[0]) ? (string) $errors[0] : 'Le listage des boites mail a echoue.';
            throw new MailboxProvisioningException($errorMessage);
        }

        $rows = data_get($payload, 'data', []);
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_map(function (mixed $row): array {
            if (! is_array($row)) {
                return [];
            }

            return [
                'email' => (string) ($row['email'] ?? ''),
                'login' => (string) ($row['login'] ?? ''),
                'domain' => (string) ($row['domain'] ?? ''),
                'suspended' => (bool) ($row['suspended_login'] ?? false),
                'disk_used' => (string) ($row['diskused'] ?? ''),
                'disk_quota' => (string) ($row['diskquota'] ?? ''),
            ];
        }, $rows));
    }

    public function updateMailboxPassword(string $localPart, string $password): MailboxCreationResultDto
    {
        $config = $this->resolveConfig();

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => sprintf('cpanel %s:%s', $config['username'], $config['token']),
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'].'/Email/passwd_pop', [
                'email' => $localPart,
                'domain' => $config['domain'],
                'password' => $password,
            ]);

        return $this->mapMutatingMailboxResponse(
            $response,
            $localPart,
            $config['domain'],
            'La mise a jour du mot de passe a echoue.',
            'Mot de passe de la boite mail mis a jour avec succes.'
        );
    }

    public function suspendMailbox(string $localPart): MailboxCreationResultDto
    {
        $config = $this->resolveConfig();

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => sprintf('cpanel %s:%s', $config['username'], $config['token']),
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'].'/Email/suspend_login', [
                'email' => $localPart,
                'domain' => $config['domain'],
            ]);

        return $this->mapMutatingMailboxResponse(
            $response,
            $localPart,
            $config['domain'],
            'La suspension de la boite mail a echoue.',
            'Boite mail suspendue avec succes.'
        );
    }

    public function unsuspendMailbox(string $localPart): MailboxCreationResultDto
    {
        $config = $this->resolveConfig();

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => sprintf('cpanel %s:%s', $config['username'], $config['token']),
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'].'/Email/unsuspend_login', [
                'email' => $localPart,
                'domain' => $config['domain'],
            ]);

        return $this->mapMutatingMailboxResponse(
            $response,
            $localPart,
            $config['domain'],
            'La reactivation de la boite mail a echoue.',
            'Boite mail reactivee avec succes.'
        );
    }

    public function updateMailboxQuota(string $localPart, int $quotaMb): MailboxCreationResultDto
    {
        $config = $this->resolveConfig();

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => sprintf('cpanel %s:%s', $config['username'], $config['token']),
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'].'/Email/edit_pop_quota', [
                'email' => $localPart,
                'domain' => $config['domain'],
                'quota' => max($quotaMb, 0),
            ]);

        return $this->mapMutatingMailboxResponse(
            $response,
            $localPart,
            $config['domain'],
            'La mise a jour du quota a echoue.',
            'Quota de la boite mail mis a jour avec succes.'
        );
    }

    /**
     * @return array{base_url: string, username: string, token: string, domain: string, timeout: int}
     */
    private function resolveConfig(): array
    {
        $config = config('services.cpanel');
        $host = trim((string) ($config['host'] ?? ''));
        $username = trim((string) ($config['username'] ?? ''));
        $token = trim((string) ($config['token'] ?? ''));
        $domain = trim((string) ($config['primary_domain'] ?? ''));
        $timeout = (int) ($config['timeout'] ?? 15);

        if ($host === '' || $username === '' || $token === '' || $domain === '') {
            throw new MailboxProvisioningException('Configuration cPanel incomplete.');
        }

        return [
            'base_url' => 'https://'.$host.':2083/execute',
            'username' => $username,
            'token' => $token,
            'domain' => $domain,
            'timeout' => $timeout,
        ];
    }

    private function mapMutatingMailboxResponse(
        Response $response,
        string $localPart,
        string $domain,
        string $fallbackError,
        string $successMessage
    ): MailboxCreationResultDto {
        if (! $response->successful()) {
            return new MailboxCreationResultDto(
                success: false,
                email: sprintf('%s@%s', $localPart, $domain),
                message: sprintf('Erreur HTTP cPanel: %s', (string) $response->status()),
                rawError: (string) $response->body(),
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            return new MailboxCreationResultDto(
                success: false,
                email: sprintf('%s@%s', $localPart, $domain),
                message: 'Reponse cPanel invalide.',
                rawError: (string) $response->body(),
            );
        }

        $status = (int) data_get($payload, 'status', 0);
        $errors = data_get($payload, 'errors');
        $errorMessage = is_array($errors) && isset($errors[0]) ? (string) $errors[0] : null;

        if ($status !== 1) {
            return new MailboxCreationResultDto(
                success: false,
                email: sprintf('%s@%s', $localPart, $domain),
                message: $errorMessage ?: $fallbackError,
                rawError: $errorMessage,
            );
        }

        return new MailboxCreationResultDto(
            success: true,
            email: sprintf('%s@%s', $localPart, $domain),
            message: $successMessage,
            rawError: null,
        );
    }
}
