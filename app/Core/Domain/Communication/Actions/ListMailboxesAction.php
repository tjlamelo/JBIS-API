<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Contracts\MailboxProvisioner;

class ListMailboxesAction
{
    public function __construct(
        private readonly MailboxProvisioner $mailboxProvisioner,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(): array
    {
        return $this->mailboxProvisioner->listMailboxes();
    }
}
