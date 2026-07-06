<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\Appointment;
use App\Core\Domain\Candidacy\Policies\ApplicationPolicy;
use App\Core\Domain\Candidacy\Policies\AppointmentPolicy;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\Models\Training;
use App\Core\Domain\Catalog\Policies\Program\ProgramPolicy;
use App\Core\Domain\Catalog\Policies\Training\TrainingPolicy;
use App\Core\Domain\Identity\Models\Archive;
use App\Core\Domain\Identity\Models\Certification;
use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Identity\Models\InterestAndHobby;
use App\Core\Domain\Identity\Models\Language;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\UserInternship;
use App\Core\Domain\Identity\Models\UserNote;
use App\Core\Domain\Identity\Models\UserPreferredCountry;
use App\Core\Domain\Identity\Models\UserSkill;
use App\Core\Domain\Identity\Models\UserTraining;
use App\Core\Domain\Identity\Models\UserVisaHistory;
use App\Core\Domain\Identity\Policies\ArchivePolicy;
use App\Core\Domain\Identity\Policies\CertificationPolicy;
use App\Core\Domain\Identity\Policies\EducationPolicy;
use App\Core\Domain\Identity\Policies\ExperiencePolicy;
use App\Core\Domain\Identity\Policies\InterestAndHobbyPolicy;
use App\Core\Domain\Identity\Policies\PermissionManagementPolicy;
use App\Core\Domain\Identity\Policies\UserDocumentPolicy;
use App\Core\Domain\Identity\Policies\UserInternshipPolicy;
use App\Core\Domain\Identity\Policies\UserLanguagePolicy;
use App\Core\Domain\Identity\Policies\UserNotePolicy;
use App\Core\Domain\Identity\Policies\UserPolicy;
use App\Core\Domain\Identity\Policies\UserPreferredCountryPolicy;
use App\Core\Domain\Identity\Policies\UserSkillPolicy;
use App\Core\Domain\Identity\Policies\UserTrainingPolicy;
use App\Core\Domain\Identity\Policies\UserVisaHistoryPolicy;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Identity\Support\PermissionManagement;
use App\Core\Domain\Recruiter\Models\RecruiterOfferSubmission;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use App\Core\Domain\Recruiter\Policies\RecruiterOfferSubmissionPolicy;
use App\Core\Domain\Recruiter\Policies\RecruiterOnboardingApplicationPolicy;
use App\Core\Domain\Recruiter\Policies\RecruiterOrganizationPolicy;
use App\Core\Domain\Recruiter\Policies\RecruiterProfileAssignmentPolicy;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\Policies\ProcessFlowPolicy;
use App\Core\Domain\Workflow\Policies\ProcessStepPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Application::class => ApplicationPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        Program::class => ProgramPolicy::class,
        Training::class => TrainingPolicy::class,
        ProcessFlow::class => ProcessFlowPolicy::class,
        ProcessStep::class => ProcessStepPolicy::class,
        Experience::class => ExperiencePolicy::class,
        Education::class => EducationPolicy::class,
        Certification::class => CertificationPolicy::class,
        Language::class => UserLanguagePolicy::class,
        UserInternship::class => UserInternshipPolicy::class,
        InterestAndHobby::class => InterestAndHobbyPolicy::class,
        UserSkill::class => UserSkillPolicy::class,
        UserTraining::class => UserTrainingPolicy::class,
        UserNote::class => UserNotePolicy::class,
        UserVisaHistory::class => UserVisaHistoryPolicy::class,
        UserPreferredCountry::class => UserPreferredCountryPolicy::class,
        Archive::class => ArchivePolicy::class,
        UserDocument::class => UserDocumentPolicy::class,
        User::class => UserPolicy::class,
        PermissionManagement::class => PermissionManagementPolicy::class,
        RecruiterOrganization::class => RecruiterOrganizationPolicy::class,
        RecruiterOnboardingApplication::class => RecruiterOnboardingApplicationPolicy::class,
        RecruiterOfferSubmission::class => RecruiterOfferSubmissionPolicy::class,
        RecruiterProfileAssignment::class => RecruiterProfileAssignmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-dashboard', static function ($user): bool {
            if ($user === null) {
                return false;
            }

            return $user->hasAnyRole([
                ApplicationRole::SUPERADMIN,
                ApplicationRole::ADMIN,
                ApplicationRole::STAFF,
                ApplicationRole::RECRUITER,
                ApplicationRole::CANDIDATE,
            ]);
        });
    }
}
