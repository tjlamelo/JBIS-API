<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\UpdateMyProfileWizardStepRequest;
use App\Core\Application\Api\V1\Identity\Support\ProfileResponseMapper;
use App\Core\Domain\Identity\Actions\Profile\UpdateMyProfileWizardStepAction;
use App\Core\Domain\Identity\Exceptions\ProfileLockedException;
use App\Core\Domain\Shared\Media\Actions\StoreMediaAction;
use App\Core\Domain\Shared\Media\Support\MediaUrlResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MyProfileController
{
    public function __construct(
        private readonly UpdateMyProfileWizardStepAction $updateWizardStep,
        private readonly StoreMediaAction $storeMediaAction,
        private readonly MediaUrlResolver $mediaUrlResolver,
        private readonly ProfileResponseMapper $profileResponseMapper,
    ) {}

    public function updateStep(UpdateMyProfileWizardStepRequest $request, string $step): JsonResponse
    {
        try {
            $profile = $this->updateWizardStep->execute(
                $request->user(),
                $step,
                $request->validated(),
            );
        } catch (ProfileLockedException $exception) {
            return BaseResponse::forbidden(['message' => $exception->getMessage()])->toJsonResponse();
        } catch (\InvalidArgumentException $exception) {
            return BaseResponse::unprocessableEntity(['message' => $exception->getMessage()])->toJsonResponse();
        }

        return BaseResponse::ok([
            'profile' => $this->profileResponseMapper->toArray($profile),
        ])->toJsonResponse();
    }

    public function uploadPicture(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required_without:photo', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'photo' => ['required_without:image', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $file = $validated['image'] ?? $validated['photo'];

        $uploaded = $this->storeMediaAction->execute(
            $file,
            'identity/profile-pictures',
        );

        $media = $uploaded->toArray();
        $urls = $this->mediaUrlResolver->all($media);

        return BaseResponse::ok([
            'message' => __('Photo telechargee avec succes.'),
            'image' => $urls,
            'media' => $media,
        ])->toJsonResponse();
    }
}
