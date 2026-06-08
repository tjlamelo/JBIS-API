<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Services;

use App\Core\Domain\Communication\Contracts\SubdomainProvisioner;
use App\Core\Domain\Communication\DTOs\SubdomainCreationResultDto;
use App\Core\Domain\Communication\Exceptions\MailboxProvisioningException;
use Illuminate\Support\Facades\Http;

final class CpanelSubdomainProvisionerService implements SubdomainProvisioner
{
    public function createSubdomain(string $subdomain, string $rootDomain, string $documentRoot): SubdomainCreationResultDto
    {
        $config = $this->resolveConfig();
        $fqdn = strtolower(trim($subdomain)).'.'.strtolower(trim($rootDomain));

        $response = Http::timeout($config['timeout'])
            ->withHeaders([
                'Authorization' => sprintf('cpanel %s:%s', $config['username'], $config['token']),
                'Accept' => 'application/json',
            ])
            ->get($config['base_url'].'/SubDomain/addsubdomain', [
                'domain' => $subdomain,
                'rootdomain' => $rootDomain,
                'dir' => $documentRoot,
            ]);

        if (! $response->successful()) {
            return new SubdomainCreationResultDto(
                success: false,
                fqdn: $fqdn,
                message: sprintf('Erreur HTTP cPanel: %s', (string) $response->status()),
                rawError: (string) $response->body(),
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            return new SubdomainCreationResultDto(
                success: false,
                fqdn: $fqdn,
                message: 'Réponse cPanel invalide.',
                rawError: (string) $response->body(),
            );
        }

        $status = (int) data_get($payload, 'status', 0);
        $errors = data_get($payload, 'errors');
        $errorMessage = is_array($errors) && isset($errors[0]) ? (string) $errors[0] : null;

        if ($status !== 1) {
            return new SubdomainCreationResultDto(
                success: false,
                fqdn: $fqdn,
                message: $errorMessage ?: 'La création du sous-domaine a échoué.',
                rawError: $errorMessage,
            );
        }

        return new SubdomainCreationResultDto(
            success: true,
            fqdn: $fqdn,
            message: 'Sous-domaine créé avec succès.',
            rawError: null,
        );
    }

    /**
     * @return array{base_url: string, username: string, token: string, timeout: int}
     */
    private function resolveConfig(): array
    {
        $config = config('services.cpanel');
        $host = trim((string) ($config['host'] ?? ''));
        $username = trim((string) ($config['username'] ?? ''));
        $token = trim((string) ($config['token'] ?? ''));
        $timeout = (int) ($config['timeout'] ?? 15);

        if ($host === '' || $username === '' || $token === '') {
            throw new MailboxProvisioningException('Configuration cPanel incomplète.');
        }

        return [
            'base_url' => 'https://'.$host.':2083/execute',
            'username' => $username,
            'token' => $token,
            'timeout' => $timeout,
        ];
    }
}
