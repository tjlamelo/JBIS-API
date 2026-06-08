<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class StoreRecruiterOnboardingDocumentAction
{
    private const DISK = 'local';

    public function execute(
        RecruiterOnboardingApplication $application,
        UploadedFile $file,
        string $documentType,
    ): RecruiterOnboardingDocument {
        $path = sprintf(
            'recruiter-onboarding/%d/%s_%s',
            $application->id,
            Str::uuid()->toString(),
            $file->getClientOriginalName(),
        );

        Storage::disk(self::DISK)->put($path, $file->get());

        return RecruiterOnboardingDocument::query()->create([
            'recruiter_onboarding_application_id' => $application->id,
            'document_type' => $documentType,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);
    }
}
