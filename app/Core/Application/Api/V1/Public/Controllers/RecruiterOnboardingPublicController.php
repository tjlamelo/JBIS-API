<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Recruiter\Requests\StoreRecruiterOnboardingApplicationRequest;
use App\Core\Application\Api\V1\Recruiter\Resources\RecruiterOnboardingApplicationResource;
use App\Core\Application\Mail\Mailable\RecruiterOnboardingSubmittedStaffMail;
use App\Core\Domain\Recruiter\Actions\StoreRecruiterOnboardingDocumentAction;
use App\Core\Domain\Recruiter\Actions\SubmitRecruiterOnboardingApplicationAction;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

final class RecruiterOnboardingPublicController extends Controller
{
    public function __construct(
        private readonly SubmitRecruiterOnboardingApplicationAction $submitApplication,
        private readonly StoreRecruiterOnboardingDocumentAction $storeDocument,
    ) {}

    public function store(StoreRecruiterOnboardingApplicationRequest $request): JsonResponse
    {
        $this->authorize('create', RecruiterOnboardingApplication::class);

        $data = $request->validated();
        if ($request->user()) {
            $data['contact_email'] = $request->user()->email;
            $data['contact_name'] = $data['contact_name'] ?? $request->user()->name;
        }

        $application = $this->submitApplication->execute($data, $request->user());

        if ($request->hasFile('documents')) {
            $types = $request->input('document_types', []);
            foreach ($request->file('documents') as $index => $file) {
                $type = is_array($types) ? (string) ($types[$index] ?? 'other') : 'other';
                $this->storeDocument->execute($application, $file, $type);
            }
        }

        $application->load(['documents', 'applicant']);

        $notifyEmail = config('services.recruiter.notify_email');
        if (is_string($notifyEmail) && $notifyEmail !== '') {
            Mail::to($notifyEmail)->send(new RecruiterOnboardingSubmittedStaffMail($application));
        }

        return BaseResponse::created([
            'application' => new RecruiterOnboardingApplicationResource($application),
        ])->toJsonResponse();
    }
}
