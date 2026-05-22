<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Contracts\MailboxProvisioner;
use App\Core\Domain\Communication\DTOs\MailboxCreationResultDto;
use App\Core\Domain\Communication\DTOs\UpdateMailboxQuotaDto;
use App\Core\Domain\Communication\Exceptions\MailboxProvisioningException;

class UpdateMailboxQuotaAction
{
    public function __construct(
        private readonly MailboxProvisioner $mailboxProvisioner,
    ) {}

    public function execute(UpdateMailboxQuotaDto $data): MailboxCreationResultDto
    {
        $localPart = trim($data->localPart);
        if ($localPart === '') {
            throw new MailboxProvisioningException('Le nom local de la boite mail est requis.');
        }

        if (! preg_match('/^[a-zA-Z0-9._%+-]+$/', $localPart)) {
            throw new MailboxProvisioningException('Le nom local de la boite mail contient des caracteres invalides.');
        }

        if ($data->quotaMb < 0) {
            throw new MailboxProvisioningException('Le quota ne peut pas etre negatif.');
        }

        return $this->mailboxProvisioner->updateMailboxQuota($localPart, $data->quotaMb);
    }
}
