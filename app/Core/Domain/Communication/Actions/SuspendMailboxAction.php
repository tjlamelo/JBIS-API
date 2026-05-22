<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Contracts\MailboxProvisioner;
use App\Core\Domain\Communication\DTOs\DeleteMailboxDto;
use App\Core\Domain\Communication\DTOs\MailboxCreationResultDto;
use App\Core\Domain\Communication\Exceptions\MailboxProvisioningException;

class SuspendMailboxAction
{
    public function __construct(
        private readonly MailboxProvisioner $mailboxProvisioner,
    ) {}

    public function execute(DeleteMailboxDto $data): MailboxCreationResultDto
    {
        $localPart = trim($data->localPart);
        if ($localPart === '') {
            throw new MailboxProvisioningException('Le nom local de la boite mail est requis.');
        }

        if (! preg_match('/^[a-zA-Z0-9._%+-]+$/', $localPart)) {
            throw new MailboxProvisioningException('Le nom local de la boite mail contient des caracteres invalides.');
        }

        return $this->mailboxProvisioner->suspendMailbox($localPart);
    }
}
