<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\Contracts\MailboxProvisioner;
use App\Core\Domain\Communication\DTOs\CreateMailboxDto;
use App\Core\Domain\Communication\DTOs\MailboxCreationResultDto;
use App\Core\Domain\Communication\Exceptions\MailboxProvisioningException;

class CreateMailboxAction
{
    public function __construct(
        private readonly MailboxProvisioner $mailboxProvisioner,
    ) {}

    public function execute(CreateMailboxDto $data): MailboxCreationResultDto
    {
        $localPart = trim($data->localPart);
        if ($localPart === '') {
            throw new MailboxProvisioningException('Le nom local de la boite mail est requis.');
        }

        if (! preg_match('/^[a-zA-Z0-9._%+-]+$/', $localPart)) {
            throw new MailboxProvisioningException('Le nom local de la boite mail contient des caracteres invalides.');
        }

        if (mb_strlen($data->password) < 8) {
            throw new MailboxProvisioningException('Le mot de passe doit contenir au moins 8 caracteres.');
        }

        return $this->mailboxProvisioner->createMailbox($localPart, $data->password, $data->quotaMb);
    }
}
