<?php

namespace App\Core\Domain\Communication\Contracts;

use App\Core\Domain\Communication\DTOs\MailboxCreationResultDto;

interface MailboxProvisioner
{
    public function createMailbox(string $localPart, string $password, ?int $quotaMb = null): MailboxCreationResultDto;

    /**
     * @return list<array<string, mixed>>
     */
    public function listMailboxes(): array;

    public function deleteMailbox(string $localPart): MailboxCreationResultDto;

    public function updateMailboxPassword(string $localPart, string $password): MailboxCreationResultDto;

    public function suspendMailbox(string $localPart): MailboxCreationResultDto;

    public function unsuspendMailbox(string $localPart): MailboxCreationResultDto;

    public function updateMailboxQuota(string $localPart, int $quotaMb): MailboxCreationResultDto;
}
