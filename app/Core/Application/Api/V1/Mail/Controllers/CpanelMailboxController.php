<?php

namespace App\Core\Application\Api\V1\Mail\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Mail\Requests\CreateMailboxRequest;
use App\Core\Application\Api\V1\Mail\Requests\UpdateMailboxPasswordRequest;
use App\Core\Application\Api\V1\Mail\Requests\UpdateMailboxQuotaRequest;
use App\Core\Domain\Communication\Actions\CreateMailboxAction;
use App\Core\Domain\Communication\Actions\DeleteMailboxAction;
use App\Core\Domain\Communication\Actions\ListMailboxesAction;
use App\Core\Domain\Communication\Actions\SuspendMailboxAction;
use App\Core\Domain\Communication\Actions\UnsuspendMailboxAction;
use App\Core\Domain\Communication\Actions\UpdateMailboxPasswordAction;
use App\Core\Domain\Communication\Actions\UpdateMailboxQuotaAction;
use App\Core\Domain\Communication\DTOs\CreateMailboxDto;
use App\Core\Domain\Communication\DTOs\DeleteMailboxDto;
use App\Core\Domain\Communication\DTOs\UpdateMailboxPasswordDto;
use App\Core\Domain\Communication\DTOs\UpdateMailboxQuotaDto;
use App\Core\Domain\Communication\Exceptions\MailboxProvisioningException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CpanelMailboxController extends Controller
{
    public function __construct(
        private readonly CreateMailboxAction $createMailboxAction,
        private readonly DeleteMailboxAction $deleteMailboxAction,
        private readonly ListMailboxesAction $listMailboxesAction,
        private readonly UpdateMailboxPasswordAction $updateMailboxPasswordAction,
        private readonly UpdateMailboxQuotaAction $updateMailboxQuotaAction,
        private readonly SuspendMailboxAction $suspendMailboxAction,
        private readonly UnsuspendMailboxAction $unsuspendMailboxAction,
    ) {}

    public function index(): JsonResponse
    {
        try {
            $mailboxes = $this->listMailboxesAction->execute();
        } catch (MailboxProvisioningException $exception) {
            return BaseResponse::unprocessableEntity([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'mailboxes' => $mailboxes,
        ])->toJsonResponse();
    }

    public function store(CreateMailboxRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->createMailboxAction->execute(
                new CreateMailboxDto(
                    localPart: (string) $validated['local_part'],
                    password: (string) $validated['password'],
                    quotaMb: isset($validated['quota_mb']) ? (int) $validated['quota_mb'] : null,
                )
            );
        } catch (MailboxProvisioningException $exception) {
            return BaseResponse::unprocessableEntity([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        if (! $result->success) {
            return BaseResponse::unprocessableEntity([
                'message' => $result->message,
                'email' => $result->email,
                'error' => $result->rawError,
            ])->toJsonResponse();
        }

        return BaseResponse::created([
            'message' => $result->message,
            'email' => $result->email,
        ])->toJsonResponse();
    }

    public function destroy(string $localPart): JsonResponse
    {
        try {
            $result = $this->deleteMailboxAction->execute(
                new DeleteMailboxDto(localPart: $localPart)
            );
        } catch (MailboxProvisioningException $exception) {
            return BaseResponse::unprocessableEntity([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        if (! $result->success) {
            return BaseResponse::unprocessableEntity([
                'message' => $result->message,
                'email' => $result->email,
                'error' => $result->rawError,
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'message' => $result->message,
            'email' => $result->email,
        ])->toJsonResponse();
    }

    public function updatePassword(UpdateMailboxPasswordRequest $request, string $localPart): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->updateMailboxPasswordAction->execute(
                new UpdateMailboxPasswordDto(
                    localPart: $localPart,
                    password: (string) $validated['password'],
                )
            );
        } catch (MailboxProvisioningException $exception) {
            return BaseResponse::unprocessableEntity([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        if (! $result->success) {
            return BaseResponse::unprocessableEntity([
                'message' => $result->message,
                'email' => $result->email,
                'error' => $result->rawError,
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'message' => $result->message,
            'email' => $result->email,
        ])->toJsonResponse();
    }

    public function suspend(string $localPart): JsonResponse
    {
        return $this->handleSuspendState($localPart, true);
    }

    public function unsuspend(string $localPart): JsonResponse
    {
        return $this->handleSuspendState($localPart, false);
    }

    public function updateQuota(UpdateMailboxQuotaRequest $request, string $localPart): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->updateMailboxQuotaAction->execute(
                new UpdateMailboxQuotaDto(
                    localPart: $localPart,
                    quotaMb: (int) $validated['quota_mb'],
                )
            );
        } catch (MailboxProvisioningException $exception) {
            return BaseResponse::unprocessableEntity([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        if (! $result->success) {
            return BaseResponse::unprocessableEntity([
                'message' => $result->message,
                'email' => $result->email,
                'error' => $result->rawError,
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'message' => $result->message,
            'email' => $result->email,
        ])->toJsonResponse();
    }

    private function handleSuspendState(string $localPart, bool $suspend): JsonResponse
    {
        try {
            $result = $suspend
                ? $this->suspendMailboxAction->execute(new DeleteMailboxDto(localPart: $localPart))
                : $this->unsuspendMailboxAction->execute(new DeleteMailboxDto(localPart: $localPart));
        } catch (MailboxProvisioningException $exception) {
            return BaseResponse::unprocessableEntity([
                'message' => $exception->getMessage(),
            ])->toJsonResponse();
        }

        if (! $result->success) {
            return BaseResponse::unprocessableEntity([
                'message' => $result->message,
                'email' => $result->email,
                'error' => $result->rawError,
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'message' => $result->message,
            'email' => $result->email,
        ])->toJsonResponse();
    }
}
