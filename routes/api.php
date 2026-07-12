<?php

use App\Core\Application\Api\V1\Analytics\Controllers\Ga4AcquisitionController;
use App\Core\Application\Api\V1\Analytics\Controllers\Ga4OverviewController;
use App\Core\Application\Api\V1\Analytics\Controllers\Ga4PagesController;
use App\Core\Application\Api\V1\Auth\Controllers\AuthTokenController;
use App\Core\Application\Api\V1\Candidacy\Controllers\AdminApplicationController;
use App\Core\Application\Api\V1\Candidacy\Controllers\AdminApplicationStepController;
use App\Core\Application\Api\V1\Candidacy\Controllers\ApplicationStepController;
use App\Core\Application\Api\V1\Candidacy\Controllers\AdminAppointmentController;
use App\Core\Application\Api\V1\Candidacy\Controllers\ApplicationController;
use App\Core\Application\Api\V1\Candidacy\Controllers\ApplicationDocumentController;
use App\Core\Application\Api\V1\Candidacy\Controllers\OfferApplicationReadinessController;
use App\Core\Application\Api\V1\Catalog\Controllers\AdminCatalogController;
use App\Core\Application\Api\V1\Catalog\Controllers\BenefitController;
use App\Core\Application\Api\V1\Catalog\Controllers\CityController;
use App\Core\Application\Api\V1\Catalog\Controllers\CompanyController;
use App\Core\Application\Api\V1\Catalog\Controllers\ContractTypeController;
use App\Core\Application\Api\V1\Catalog\Controllers\CountryController;
use App\Core\Application\Api\V1\Catalog\Controllers\EducationLevelController;
use App\Core\Application\Api\V1\Catalog\Controllers\LanguageController;
use App\Core\Application\Api\V1\Catalog\Controllers\LanguageLevelController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\AdminOfferExtractionController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\AdminOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\ForceDeleteOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\CategoryController;
use App\Core\Application\Api\V1\Catalog\Controllers\TradeController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\OfferFacetController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\OfferFilterController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\PublicOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\RestoreOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\OfferTypeController;
use App\Core\Application\Api\V1\Catalog\Controllers\Program\AdminProgramController;
use App\Core\Application\Api\V1\Catalog\Controllers\Program\ProgramFacetController;
use App\Core\Application\Api\V1\Catalog\Controllers\Program\ProgramFilterController;
use App\Core\Application\Api\V1\Catalog\Controllers\Program\PublicProgramController;
use App\Core\Application\Api\V1\Catalog\Controllers\GeographicZoneController;
use App\Core\Application\Api\V1\Catalog\Controllers\RegionController;
use App\Core\Application\Api\V1\Catalog\Controllers\RequiredDocumentController;
use App\Core\Application\Api\V1\Catalog\Controllers\SkillCategoryController;
use App\Core\Application\Api\V1\Catalog\Controllers\SkillController;
use App\Core\Application\Api\V1\Catalog\Controllers\Training\AdminTrainingController;
use App\Core\Application\Api\V1\Catalog\Controllers\TrainingController;
use App\Core\Application\Api\V1\Catalog\Controllers\CertificationOffer\AdminCertificationOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\CertificationOffer\CertificationOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\UserSkillController;
use App\Core\Application\Api\V1\Catalog\Controllers\UserTrainingController;
use App\Core\Application\Api\V1\Catalog\Controllers\WorkScheduleController;
use App\Core\Application\Api\V1\Dashboard\Controllers\DashboardController;
use App\Core\Application\Api\V1\Shared\Controllers\MetaEnumController;
use App\Core\Application\Api\V1\Document\Controllers\UserDocumentController;
use App\Core\Application\Api\V1\Document\Controllers\UserDocumentExtractionController;
use App\Core\Application\Api\V1\Export\Controllers\ExportController;
use App\Core\Application\Api\V1\Export\Controllers\ExportSchemaController;
use App\Core\Application\Api\V1\Identity\Controllers\AdminLegalDocumentController;
use App\Core\Application\Api\V1\Identity\Controllers\AdminUserDossierController;
use App\Core\Application\Api\V1\Identity\Controllers\AdminUserController;
use App\Core\Application\Api\V1\Identity\Controllers\AdminUserImportController;
use App\Core\Application\Api\V1\Identity\Controllers\AdminUserSearchFiltersController;
use App\Core\Application\Api\V1\Identity\Controllers\ArchiveController;
use App\Core\Application\Api\V1\Identity\Controllers\UserSecurityEventController;
use App\Core\Application\Api\V1\Operations\Controllers\AssignedTaskController;
use App\Core\Application\Api\V1\Operations\Controllers\DailyTaskController;
use App\Core\Application\Api\V1\Operations\Controllers\MeetingController;
use App\Core\Application\Api\V1\Identity\Controllers\CertificationController;
use App\Core\Application\Api\V1\Identity\Controllers\EducationController;
use App\Core\Application\Api\V1\Identity\Controllers\ExperienceController;
use App\Core\Application\Api\V1\Identity\Controllers\InterestAndHobbyController;
use App\Core\Application\Api\V1\Identity\Controllers\LegalDocumentController;
use App\Core\Application\Api\V1\Identity\Controllers\MyConsentController;
use App\Core\Application\Api\V1\Identity\Controllers\MyDeviceController;
use App\Core\Application\Api\V1\Identity\Controllers\MyDiscoverySourceController;
use App\Core\Application\Api\V1\Identity\Controllers\MyProfileController;
use App\Core\Application\Api\V1\Identity\Controllers\MySettingsController;
use App\Core\Application\Api\V1\Identity\Controllers\PermissionController;
use App\Core\Application\Api\V1\Identity\Controllers\RolePermissionController;
use App\Core\Application\Api\V1\Identity\Controllers\UserInternshipController;
use App\Core\Application\Api\V1\Identity\Controllers\UserLanguageController;
use App\Core\Application\Api\V1\Identity\Controllers\UserNoteController;
use App\Core\Application\Api\V1\Identity\Controllers\UserPermissionOverrideController;
use App\Core\Application\Api\V1\Identity\Controllers\UserPreferredCountryController;
use App\Core\Application\Api\V1\Identity\Controllers\UserVisaHistoryController;
use App\Core\Application\Api\V1\Communication\Controllers\AdminNewsletterController;
use App\Core\Application\Api\V1\Communication\Controllers\MyNotificationController;
use App\Core\Application\Api\V1\Communication\Controllers\NewsletterSubscriptionController;
use App\Core\Application\Api\V1\Mail\Controllers\CpanelMailboxController;
use App\Core\Application\Api\V1\Mail\Controllers\MailCampaignController;
use App\Core\Application\Api\V1\Recruiter\Controllers\AdminRecruiterAssignmentController;
use App\Core\Application\Api\V1\Recruiter\Controllers\AdminRecruiterProfileRequestController;
use App\Core\Application\Api\V1\Recruiter\Controllers\AdminRecruiterOfferController;
use App\Core\Application\Api\V1\Recruiter\Controllers\AdminRecruiterOnboardingController;
use App\Core\Application\Api\V1\Recruiter\Controllers\AdminRecruiterOrganizationController;
use App\Core\Application\Api\V1\Recruiter\Controllers\RecruiterAssignmentController;
use App\Core\Application\Api\V1\Recruiter\Controllers\RecruiterOfferController;
use App\Core\Application\Api\V1\Recruiter\Controllers\RecruiterProfileRequestController;
use App\Core\Application\Api\V1\Recruiter\Controllers\RecruiterOnboardingController;
use App\Core\Application\Api\V1\Recruiter\Controllers\RecruiterOrganizationController;
use App\Core\Application\Api\V1\Partner\Controllers\AdminPartnerCohortController;
use App\Core\Application\Api\V1\Partner\Controllers\AdminPartnerOrganizationController;
use App\Core\Application\Api\V1\Partner\Controllers\PartnerCohortController;
use App\Core\Application\Api\V1\Partner\Controllers\PartnerCohortStudentController;
use App\Core\Application\Api\V1\Partner\Controllers\PartnerDashboardController;
use App\Core\Application\Api\V1\Partner\Controllers\PartnerOrganizationController;
use App\Core\Application\Api\V1\Public\Controllers\AgencyPublicController;
use App\Core\Application\Api\V1\Public\Controllers\AppointmentPublicController;
use App\Core\Application\Api\V1\Public\Controllers\DiscoverySourceController;
use App\Core\Application\Api\V1\Public\Controllers\PublicMailboxController;
use App\Core\Application\Api\V1\Public\Controllers\NewsletterPublicController;
use App\Core\Application\Api\V1\Public\Controllers\RecruiterOnboardingPublicController;
use App\Core\Application\Api\V1\Sms\Controllers\SmsCampaignController;
use App\Core\Application\Api\V1\Workflow\Controllers\ProcessFlow\AdminProcessFlowController;
use App\Core\Application\Api\V1\Workflow\Controllers\ProcessFlow\ProcessFlowImportController;
use App\Core\Application\Api\V1\Workflow\Controllers\ProcessFlow\ProcessFlowPdfController;
use App\Core\Application\Api\V1\Workflow\Controllers\ProcessFlow\ProcessFlowSectionKeyController;
use App\Core\Application\Api\V1\Workflow\Controllers\ProcessStep\AdminProcessStepController;
use Illuminate\Support\Facades\Route;

// Backward-compatible social auth aliases (legacy clients/configs without /v1).
Route::get('/auth/google/redirect', [AuthTokenController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthTokenController::class, 'handleGoogleCallback']);

Route::prefix('v1')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | ROUTES PUBLIQUES
    |--------------------------------------------------------------------------
    */

    // Auth & Social
    Route::post('/register', [AuthTokenController::class, 'register']);
    Route::post('/login', [AuthTokenController::class, 'login']);
    Route::post('/login/two-factor', [AuthTokenController::class, 'completeTwoFactorLogin']);
    Route::post('/forgot-password', [AuthTokenController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthTokenController::class, 'resetPassword']);
    Route::get('/auth/google/redirect', [AuthTokenController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AuthTokenController::class, 'handleGoogleCallback']);

    // VÃ©rification d'email
    Route::get('/email/verify/{id}/{hash}', [AuthTokenController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('api.verification.verify');

    // Catalogue Public (SEO : Utilise les Slugs)
    Route::prefix('public')->group(function (): void {
        // 1. Les filtres globaux (D'abord !)
        Route::get('/filters', OfferFilterController::class);
        Route::get('/filters/facets', OfferFacetController::class);

        // 2. Les offres
        Route::get('/offers', [PublicOfferController::class, 'index']);
        // On ajoute une contrainte regex pour le slug afin qu'il ne capture pas "filters"
        Route::get('/offers/{slug}', [PublicOfferController::class, 'show'])
            ->where('slug', '[a-zA-Z0-9\-]+');

        // 3. Les programmes
        Route::prefix('programs')->group(function (): void {
            Route::get('/filters', ProgramFilterController::class);
            Route::get('/filters/facets', ProgramFacetController::class);
            Route::get('/', [PublicProgramController::class, 'index']);
            Route::get('/{slug}', [PublicProgramController::class, 'show']);
            Route::get('/{programId}/offers', [PublicOfferController::class, 'indexByProgram']);
        });

        Route::get('/discovery-sources', DiscoverySourceController::class);
        Route::get('/mail-addresses', PublicMailboxController::class);
        Route::get('/agencies', AgencyPublicController::class);
        Route::post('/appointments', AppointmentPublicController::class);
        Route::post('/recruiter-onboarding', [RecruiterOnboardingPublicController::class, 'store']);
        Route::post('/newsletter/subscribe', [NewsletterPublicController::class, 'subscribe']);
        Route::post('/newsletter/unsubscribe', [NewsletterPublicController::class, 'unsubscribe']);
        Route::get('/newsletter/unsubscribe', [NewsletterPublicController::class, 'showByToken']);
        Route::get('/certification-offers', [CertificationOfferController::class, 'index']);
    });

    Route::prefix('legal')->group(function (): void {
        Route::get('/current', [LegalDocumentController::class, 'current']);
        Route::get('/{type}/{version}', [LegalDocumentController::class, 'show']);
    });
    /*
    |--------------------------------------------------------------------------
    | ROUTES PRIVÃ‰ES (Authentification Requise)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function (): void {

        // Mon Compte & SÃ©curitÃ©
        Route::get('/me', [AuthTokenController::class, 'me']);
        Route::post('/me/discovery-source', [MyDiscoverySourceController::class, 'store']);
        Route::get('/dashboard', DashboardController::class);
        Route::get('/meta/enums', [MetaEnumController::class, 'index']);
        Route::get('/me/settings', [MySettingsController::class, 'show']);
        Route::patch('/me/settings', [MySettingsController::class, 'update']);
        Route::get('/me/notifications', [MyNotificationController::class, 'index']);
        Route::get('/me/notifications/unread-count', [MyNotificationController::class, 'unreadCount']);
        Route::post('/me/notifications/read-all', [MyNotificationController::class, 'markAllRead']);
        Route::post('/me/notifications/{notification}/read', [MyNotificationController::class, 'markRead']);
        Route::get('/me/consents/status', [MyConsentController::class, 'status']);
        Route::get('/me/consents', [MyConsentController::class, 'history']);
        Route::post('/me/consents', [MyConsentController::class, 'store']);
        Route::get('/me/devices', [MyDeviceController::class, 'index']);
        Route::delete('/me/devices/{device}', [MyDeviceController::class, 'destroy']);
        Route::post('/me/profile/pictures', [MyProfileController::class, 'uploadPicture']);
        Route::patch('/me/profile/steps/{step}', [MyProfileController::class, 'updateStep'])
            ->whereIn('step', ['personal', 'contact', 'professional', 'documents']);

        Route::get('identity/permissions', [PermissionController::class, 'index']);
        Route::get('identity/roles/permissions', [RolePermissionController::class, 'index']);
        Route::get('identity/roles/{role}/permissions', [RolePermissionController::class, 'show']);
        Route::put('identity/roles/{role}/permissions', [RolePermissionController::class, 'sync']);
        Route::post('identity/roles/{role}/permissions/grant', [RolePermissionController::class, 'grant']);
        Route::post('identity/roles/{role}/permissions/revoke', [RolePermissionController::class, 'revoke']);
        Route::get('identity/users/{user}/permission-overrides', [UserPermissionOverrideController::class, 'index']);
        Route::post('identity/users/{user}/permission-overrides', [UserPermissionOverrideController::class, 'store']);
        Route::delete('identity/users/{user}/permission-overrides', [UserPermissionOverrideController::class, 'destroy']);

        Route::prefix('identity/admin/legal-documents')->group(function (): void {
            Route::get('/', [AdminLegalDocumentController::class, 'index']);
            Route::post('/', [AdminLegalDocumentController::class, 'store']);
        });

        Route::prefix('identity/admin/recruiter-organizations')->group(function (): void {
            Route::get('/', [AdminRecruiterOrganizationController::class, 'index']);
            Route::post('/', [AdminRecruiterOrganizationController::class, 'store']);
            Route::get('/{recruiterOrganization}', [AdminRecruiterOrganizationController::class, 'show']);
            Route::patch('/{recruiterOrganization}', [AdminRecruiterOrganizationController::class, 'update']);
            Route::post('/{recruiterOrganization}/provision', [AdminRecruiterOrganizationController::class, 'provision']);
        });

        Route::prefix('identity/admin/recruiter-assignments')->group(function (): void {
            Route::get('/', [AdminRecruiterAssignmentController::class, 'index']);
            Route::post('/bulk', [AdminRecruiterAssignmentController::class, 'bulkStore']);
            Route::post('/', [AdminRecruiterAssignmentController::class, 'store']);
            Route::delete('/{recruiterAssignment}', [AdminRecruiterAssignmentController::class, 'destroy']);
        });

        Route::prefix('identity/admin/recruiter-onboarding')->group(function (): void {
            Route::get('/', [AdminRecruiterOnboardingController::class, 'index']);
            Route::get('/{recruiterOnboardingApplication}', [AdminRecruiterOnboardingController::class, 'show']);
            Route::patch('/{recruiterOnboardingApplication}/review', [AdminRecruiterOnboardingController::class, 'review']);
        });

        Route::prefix('identity/admin/recruiter-offers')->group(function (): void {
            Route::get('/', [AdminRecruiterOfferController::class, 'index']);
            Route::get('/{recruiterOfferSubmission}', [AdminRecruiterOfferController::class, 'show']);
            Route::patch('/{recruiterOfferSubmission}/review', [AdminRecruiterOfferController::class, 'review']);
        });

        Route::prefix('identity/admin/recruiter-profile-requests')->group(function (): void {
            Route::get('/', [AdminRecruiterProfileRequestController::class, 'index']);
            Route::get('/{profileRequest}', [AdminRecruiterProfileRequestController::class, 'show']);
            Route::post('/{profileRequest}/match', [AdminRecruiterProfileRequestController::class, 'match']);
            Route::post('/{profileRequest}/transmit', [AdminRecruiterProfileRequestController::class, 'transmit']);
            Route::patch('/{profileRequest}/review', [AdminRecruiterProfileRequestController::class, 'review']);
        });

        Route::prefix('identity/admin/partner-organizations')->group(function (): void {
            Route::get('/', [AdminPartnerOrganizationController::class, 'index']);
            Route::post('/', [AdminPartnerOrganizationController::class, 'store']);
            Route::get('/{partnerOrganization}', [AdminPartnerOrganizationController::class, 'show']);
        });

        Route::prefix('identity/admin/partner-cohorts')->group(function (): void {
            Route::get('/', [AdminPartnerCohortController::class, 'index']);
            Route::get('/{partnerCohort}', [AdminPartnerCohortController::class, 'show']);
            Route::patch('/{partnerCohort}/review', [AdminPartnerCohortController::class, 'review']);
        });

        Route::prefix('identity/admin/users')->group(function (): void {
            Route::get('/search/filters', AdminUserSearchFiltersController::class);
            Route::get('/stats', [AdminUserController::class, 'stats']);
            Route::get('/import/template', [AdminUserImportController::class, 'template']);
            Route::post('/import', [AdminUserImportController::class, 'import']);
            Route::get('/matricule-services', [AdminUserController::class, 'matriculeServices']);
            Route::get('/', [AdminUserController::class, 'index']);
            Route::post('/', [AdminUserController::class, 'store']);
            Route::get('/{user}/consents', [AdminUserDossierController::class, 'consents']);
            Route::get('/{user}/settings', [AdminUserDossierController::class, 'settings']);
            Route::get('/{user}', [AdminUserController::class, 'show']);
            Route::put('/{user}', [AdminUserController::class, 'update']);
            Route::patch('/{user}/active', [AdminUserController::class, 'updateActive']);
            Route::patch('/{user}/profile/approval', [AdminUserController::class, 'updateProfileApproval']);
            Route::patch('/{user}/profile/steps/{step}', [AdminUserController::class, 'updateProfileStep'])
                ->whereIn('step', ['personal', 'contact', 'professional', 'documents']);
            Route::post('/{user}/reset-password', [AdminUserController::class, 'sendPasswordReset']);
            Route::post('/{user}/matricule', [AdminUserController::class, 'assignMatricule']);
        });
        Route::post('/logout', [AuthTokenController::class, 'logout']);
        Route::post('/logout-all', [AuthTokenController::class, 'logoutAll']);
        Route::post('/email/verification-notification', [AuthTokenController::class, 'resendEmailVerification']);

        Route::prefix('security/two-factor')->group(function (): void {
            Route::post('/enable', [AuthTokenController::class, 'enableTwoFactor']);
            Route::post('/confirm', [AuthTokenController::class, 'confirmTwoFactor']);
            Route::delete('/disable', [AuthTokenController::class, 'disableTwoFactor']);
            Route::get('/qr-code', [AuthTokenController::class, 'twoFactorQrCode']);
            Route::get('/recovery-codes', [AuthTokenController::class, 'twoFactorRecoveryCodes']);
            Route::post('/recovery-codes/regenerate', [AuthTokenController::class, 'regenerateTwoFactorRecoveryCodes']);
        });

        // Administration du Catalogue
        Route::prefix('catalog')->group(function (): void {

            // --- GESTION ADMIN DES OFFRES (PrÃ©fixe admin ajoutÃ©) ---
            Route::prefix('admin/offers')->group(function (): void {
                Route::get('/', [AdminOfferController::class, 'index']);
                Route::post('/', [AdminOfferController::class, 'store']);
                Route::post('/extract-from-text', AdminOfferExtractionController::class);
                Route::post('/upload-photo', [AdminOfferController::class, 'uploadPhoto']);
                Route::get('/{offer}', [AdminOfferController::class, 'show'])->withTrashed();
                Route::put('/{offer}', [AdminOfferController::class, 'update']);
                Route::delete('/{offer}', [AdminOfferController::class, 'destroy']);
                Route::post('/{offer}/restore', RestoreOfferController::class)->withTrashed();
                Route::delete('/{offer}/force', ForceDeleteOfferController::class)->withTrashed();
            });

            Route::prefix('admin/trainings')->group(function (): void {
                Route::get('/', [AdminTrainingController::class, 'index']);
                Route::post('/', [AdminTrainingController::class, 'store']);
                Route::get('/{training}', [AdminTrainingController::class, 'show']);
                Route::put('/{training}', [AdminTrainingController::class, 'update']);
                Route::delete('/{training}', [AdminTrainingController::class, 'destroy']);
            });

            Route::prefix('admin/certification-offers')->group(function (): void {
                Route::get('/', [AdminCertificationOfferController::class, 'index']);
                Route::post('/', [AdminCertificationOfferController::class, 'store']);
                Route::get('/{certificationOffer}', [AdminCertificationOfferController::class, 'show']);
                Route::put('/{certificationOffer}', [AdminCertificationOfferController::class, 'update']);
                Route::delete('/{certificationOffer}', [AdminCertificationOfferController::class, 'destroy']);
            });

            Route::prefix('admin/programs')->group(function (): void {
                Route::get('/', [AdminProgramController::class, 'index']);
                Route::post('/', [AdminProgramController::class, 'store']);
                Route::post('/upload-image', [AdminProgramController::class, 'uploadImage']);
                Route::get('/{program}', [AdminProgramController::class, 'show'])->withTrashed();
                Route::put('/{program}', [AdminProgramController::class, 'update'])->withTrashed();
                Route::delete('/{program}', [AdminProgramController::class, 'destroy'])->withTrashed();
            });

            Route::prefix('admin/process-flows')->group(function (): void {
                Route::get('/', [AdminProcessFlowController::class, 'index']);
                Route::post('/', [AdminProcessFlowController::class, 'store']);
                Route::post('/import', [ProcessFlowImportController::class, 'import']);
                Route::get('/import/template', [ProcessFlowImportController::class, 'template']);
                Route::get('/{processFlow}', [AdminProcessFlowController::class, 'show']);
                Route::get('/{processFlow}/pdf', ProcessFlowPdfController::class);
                Route::put('/{processFlow}', [AdminProcessFlowController::class, 'update']);
                Route::delete('/{processFlow}', [AdminProcessFlowController::class, 'destroy']);
                Route::post('/{processFlow}/publish', [AdminProcessFlowController::class, 'publish']);
            });

            Route::prefix('admin/process-steps')->group(function (): void {
                Route::post('/', [AdminProcessStepController::class, 'store']);
                Route::put('/{processStep}', [AdminProcessStepController::class, 'update']);
                Route::delete('/{processStep}', [AdminProcessStepController::class, 'destroy']);
            });

            Route::prefix('admin/referentials')->middleware('can:admin.access')->group(function (): void {
                Route::get('/', [AdminCatalogController::class, 'resources']);
                Route::get('/{resource}', [AdminCatalogController::class, 'index']);
                Route::post('/{resource}', [AdminCatalogController::class, 'store']);
                Route::get('/{resource}/{id}', [AdminCatalogController::class, 'show'])->whereNumber('id');
                Route::put('/{resource}/{id}', [AdminCatalogController::class, 'update'])->whereNumber('id');
                Route::delete('/{resource}/{id}', [AdminCatalogController::class, 'destroy'])->whereNumber('id');
            });

            // Listes de sÃ©lection (Dropdowns / Catalogues de rÃ©fÃ©rence)
            Route::get('/categories', [CategoryController::class, 'index']);
            Route::get('/trades', [TradeController::class, 'index']);
            Route::get('/companies', [CompanyController::class, 'index']);
            Route::get('/countries', [CountryController::class, 'index']);
            Route::get('/geographic-zones', [GeographicZoneController::class, 'index']);
            Route::get('/regions', [RegionController::class, 'index']);
            Route::get('/cities', [CityController::class, 'index']);
            Route::get('/benefits', [BenefitController::class, 'index']);
            Route::get('/contract-types', [ContractTypeController::class, 'index']);
            Route::get('/offer-types', [OfferTypeController::class, 'index']);
            Route::get('/work-schedules', [WorkScheduleController::class, 'index']);
            Route::get('/process-flow-section-keys', [ProcessFlowSectionKeyController::class, 'index']);
            Route::get('/education-levels', [EducationLevelController::class, 'index']);
            Route::get('/languages', [LanguageController::class, 'index']);
            Route::get('/language-levels', [LanguageLevelController::class, 'index']);
            Route::get('/skill-categories', [SkillCategoryController::class, 'index']);
            Route::get('/skills', [SkillController::class, 'index']);
            Route::get('/trainings', [TrainingController::class, 'index']);
            Route::get('/trainings/{training}', [TrainingController::class, 'show']);
            Route::get('/required-documents', [RequiredDocumentController::class, 'index']);
            Route::get('/programs', [PublicProgramController::class, 'index']);
        });

        // Campagnes Marketing
        Route::prefix('newsletter')->group(function (): void {
            Route::get('/me', [NewsletterSubscriptionController::class, 'me']);
            Route::patch('/me', [NewsletterSubscriptionController::class, 'update']);
            Route::delete('/me', [NewsletterSubscriptionController::class, 'destroy']);
        });

        Route::prefix('mail-campaigns')->group(function (): void {
            Route::post('/newsletter/offers', [AdminNewsletterController::class, 'sendOffers'])
                ->middleware('can:admin.access');
            Route::get('/', [MailCampaignController::class, 'index']);
            Route::post('/preview', [MailCampaignController::class, 'preview']);
            Route::post('/send', [MailCampaignController::class, 'send']);
            Route::get('/{campaign}', [MailCampaignController::class, 'show']);
            Route::post('/{campaign}/refresh-stats', [MailCampaignController::class, 'refreshStats']);
        });

        Route::prefix('recruiter')->group(function (): void {
            Route::get('/me/organization', [RecruiterOrganizationController::class, 'me']);
            Route::get('/onboarding/me', [RecruiterOnboardingController::class, 'me']);
            Route::get('/offers', [RecruiterOfferController::class, 'index']);
            Route::post('/offers', [RecruiterOfferController::class, 'store']);
            Route::get('/offers/{submission}', [RecruiterOfferController::class, 'show']);
            Route::patch('/offers/{submission}', [RecruiterOfferController::class, 'update']);
            Route::post('/offers/{submission}/submit', [RecruiterOfferController::class, 'submit']);
            Route::get('/profile-requests', [RecruiterProfileRequestController::class, 'index']);
            Route::post('/profile-requests', [RecruiterProfileRequestController::class, 'store']);
            Route::get('/profile-requests/{profileRequest}', [RecruiterProfileRequestController::class, 'show']);
            Route::patch('/profile-requests/{profileRequest}', [RecruiterProfileRequestController::class, 'update']);
            Route::post('/profile-requests/{profileRequest}/submit', [RecruiterProfileRequestController::class, 'submit']);
            Route::get('/assignments', [RecruiterAssignmentController::class, 'index']);
            Route::get('/assignments/{candidateUser}', [RecruiterAssignmentController::class, 'show']);
            Route::patch('/assignments/{assignment}/feedback', [RecruiterAssignmentController::class, 'updateFeedback']);
        });

        Route::prefix('partner')->group(function (): void {
            Route::get('/me/organization', [PartnerOrganizationController::class, 'me']);
            Route::get('/dashboard', [PartnerDashboardController::class, 'index']);
            Route::get('/cohorts', [PartnerCohortController::class, 'index']);
            Route::post('/cohorts', [PartnerCohortController::class, 'store']);
            Route::get('/cohorts/{partnerCohort}', [PartnerCohortController::class, 'show']);
            Route::patch('/cohorts/{partnerCohort}', [PartnerCohortController::class, 'update']);
            Route::post('/cohorts/{partnerCohort}/submit', [PartnerCohortController::class, 'submit']);
            Route::get('/cohorts/{partnerCohort}/students', [PartnerCohortStudentController::class, 'index']);
            Route::post('/cohorts/{partnerCohort}/students', [PartnerCohortStudentController::class, 'store']);
            Route::get('/cohorts/{partnerCohort}/students/{partnerCohortStudent}', [PartnerCohortStudentController::class, 'show']);
            Route::post('/cohorts/{partnerCohort}/students/{partnerCohortStudent}/refresh-documents', [PartnerCohortStudentController::class, 'refreshDocuments']);
        });

        Route::prefix('cpanel')->middleware('can:admin.access')->group(function (): void {
            Route::get('/mailboxes', [CpanelMailboxController::class, 'index']);
            Route::post('/mailboxes', [CpanelMailboxController::class, 'store']);
            Route::delete('/mailboxes/{localPart}', [CpanelMailboxController::class, 'destroy'])
                ->where('localPart', '[a-zA-Z0-9._%+-]+');
            Route::put('/mailboxes/{localPart}/password', [CpanelMailboxController::class, 'updatePassword'])
                ->where('localPart', '[a-zA-Z0-9._%+-]+');
            Route::put('/mailboxes/{localPart}/quota', [CpanelMailboxController::class, 'updateQuota'])
                ->where('localPart', '[a-zA-Z0-9._%+-]+');
            Route::post('/mailboxes/{localPart}/suspend', [CpanelMailboxController::class, 'suspend'])
                ->where('localPart', '[a-zA-Z0-9._%+-]+');
            Route::post('/mailboxes/{localPart}/unsuspend', [CpanelMailboxController::class, 'unsuspend'])
                ->where('localPart', '[a-zA-Z0-9._%+-]+');
        });

        Route::prefix('sms-campaigns')->group(function (): void {
            Route::get('/', [SmsCampaignController::class, 'index']);
            Route::post('/preview', [SmsCampaignController::class, 'preview']);
            Route::post('/send', [SmsCampaignController::class, 'send']);
            Route::get('/credit', [SmsCampaignController::class, 'credit']);
            Route::get('/{campaign}', [SmsCampaignController::class, 'show']);
            Route::post('/{campaign}/refresh-stats', [SmsCampaignController::class, 'refreshStats']);
        });

        Route::prefix('analytics/ga4')->group(function (): void {
            Route::get('/overview', Ga4OverviewController::class);
            Route::get('/pages', Ga4PagesController::class);
            Route::get('/acquisition', Ga4AcquisitionController::class);
        });

        Route::prefix('candidacy')->group(function (): void {
            Route::get('/appointments', [AdminAppointmentController::class, 'index']);
            Route::patch('/appointments/{appointment}', [AdminAppointmentController::class, 'update']);
            Route::delete('/appointments/{appointment}', [AdminAppointmentController::class, 'destroy']);
            Route::get('/offers/{offer}/readiness', [OfferApplicationReadinessController::class, 'show']);
            Route::get('/applications', [ApplicationController::class, 'index']);
            Route::post('/applications', [ApplicationController::class, 'store']);
            Route::get('/admin/applications', [AdminApplicationController::class, 'index']);
            Route::post('/admin/applications', [AdminApplicationController::class, 'store']);
            Route::get('/admin/applications/{application}', [AdminApplicationController::class, 'show']);
            Route::get('/applications/{application}', [ApplicationController::class, 'show']);
            Route::post('/applications/{application}/cancel', [ApplicationController::class, 'cancel']);
            Route::post('/applications/{application}/accept-protocol', [ApplicationController::class, 'acceptProtocol']);
            Route::post('/admin/applications/{application}/reject', [AdminApplicationController::class, 'reject']);
            Route::post('/admin/applications/{application}/resume', [AdminApplicationController::class, 'resume']);
            Route::post('/admin/applications/{application}/cancel', [AdminApplicationController::class, 'cancel']);
            Route::post('/applications/{application}/steps/{step}/documents', [ApplicationDocumentController::class, 'attach']);
            Route::patch('/applications/{application}/documents/{applicationDocument}', [ApplicationDocumentController::class, 'review']);
            Route::patch('/applications/{application}/steps/{step}/validation', [AdminApplicationStepController::class, 'validateStep']);
            Route::put('/applications/{application}/steps/{step}/interview', [AdminApplicationStepController::class, 'upsertInterview']);
            Route::post('/applications/{application}/steps/{step}/payments/declare', [ApplicationStepController::class, 'declarePayment']);
            Route::post('/applications/{application}/steps/{step}/payments', [AdminApplicationStepController::class, 'recordPayment']);
            Route::post('/applications/{application}/steps/{step}/payments/waive', [AdminApplicationStepController::class, 'waivePayment']);
            Route::post('/applications/{application}/payments/{payment}/confirm', [AdminApplicationStepController::class, 'confirmPayment']);
            Route::post('/applications/{application}/steps/{step}/advance', [AdminApplicationStepController::class, 'advance']);
            Route::post('/applications/{application}/steps/{step}/reopen', [AdminApplicationStepController::class, 'reopen']);
        });

        Route::prefix('documents')->group(function (): void {
            Route::get('/types', [UserDocumentController::class, 'types']);
            Route::get('/', [UserDocumentController::class, 'index']);
            Route::post('/download', [UserDocumentController::class, 'downloadBulk']);
            Route::post('/', [UserDocumentController::class, 'store']);
            Route::get('/{userDocument}/download', [UserDocumentController::class, 'download']);
            Route::get('/{userDocument}', [UserDocumentController::class, 'show']);
            Route::match(['put', 'patch'], '/{userDocument}', [UserDocumentController::class, 'update']);
            Route::delete('/{userDocument}', [UserDocumentController::class, 'destroy']);
            Route::post('/{userDocument}/validate', [UserDocumentController::class, 'validateDocument']);
            Route::get('/{userDocument}/extraction', [UserDocumentExtractionController::class, 'show']);
            Route::post('/{userDocument}/extraction/approve', [UserDocumentExtractionController::class, 'approve']);
            Route::post('/{userDocument}/extraction/reject', [UserDocumentExtractionController::class, 'reject']);
        });

        Route::prefix('identity')->group(function (): void {
            Route::get('experiences', [ExperienceController::class, 'index']);
            Route::post('experiences', [ExperienceController::class, 'store']);
            Route::get('experiences/{experience}', [ExperienceController::class, 'show']);
            Route::match(['put', 'patch'], 'experiences/{experience}', [ExperienceController::class, 'update']);
            Route::delete('experiences/{experience}', [ExperienceController::class, 'destroy']);
            Route::post('experiences/{experience}/validate', [ExperienceController::class, 'validateItem']);

            Route::get('education', [EducationController::class, 'index']);
            Route::post('education', [EducationController::class, 'store']);
            Route::get('education/{education}', [EducationController::class, 'show']);
            Route::match(['put', 'patch'], 'education/{education}', [EducationController::class, 'update']);
            Route::delete('education/{education}', [EducationController::class, 'destroy']);
            Route::post('education/{education}/approve', [EducationController::class, 'approve']);

            Route::get('certifications', [CertificationController::class, 'index']);
            Route::post('certifications', [CertificationController::class, 'store']);
            Route::get('certifications/{certification}', [CertificationController::class, 'show']);
            Route::match(['put', 'patch'], 'certifications/{certification}', [CertificationController::class, 'update']);
            Route::delete('certifications/{certification}', [CertificationController::class, 'destroy']);
            Route::post('certifications/{certification}/validate', [CertificationController::class, 'validateItem']);

            Route::get('languages', [UserLanguageController::class, 'index']);
            Route::post('languages', [UserLanguageController::class, 'store']);
            Route::get('languages/{userLanguage}', [UserLanguageController::class, 'show']);
            Route::match(['put', 'patch'], 'languages/{userLanguage}', [UserLanguageController::class, 'update']);
            Route::delete('languages/{userLanguage}', [UserLanguageController::class, 'destroy']);
            Route::post('languages/{userLanguage}/approve', [UserLanguageController::class, 'approve']);

            Route::get('internships', [UserInternshipController::class, 'index']);
            Route::post('internships', [UserInternshipController::class, 'store']);
            Route::get('internships/{userInternship}', [UserInternshipController::class, 'show']);
            Route::match(['put', 'patch'], 'internships/{userInternship}', [UserInternshipController::class, 'update']);
            Route::delete('internships/{userInternship}', [UserInternshipController::class, 'destroy']);

            Route::get('interests', [InterestAndHobbyController::class, 'index']);
            Route::post('interests', [InterestAndHobbyController::class, 'store']);
            Route::get('interests/{interestAndHobby}', [InterestAndHobbyController::class, 'show']);
            Route::match(['put', 'patch'], 'interests/{interestAndHobby}', [InterestAndHobbyController::class, 'update']);
            Route::delete('interests/{interestAndHobby}', [InterestAndHobbyController::class, 'destroy']);

            Route::get('visa-histories', [UserVisaHistoryController::class, 'index']);
            Route::post('visa-histories', [UserVisaHistoryController::class, 'store']);
            Route::match(['put', 'patch'], 'visa-histories/{userVisaHistory}', [UserVisaHistoryController::class, 'update']);
            Route::delete('visa-histories/{userVisaHistory}', [UserVisaHistoryController::class, 'destroy']);

            Route::get('preferred-countries', [UserPreferredCountryController::class, 'index']);
            Route::post('preferred-countries', [UserPreferredCountryController::class, 'store']);
            Route::match(['put', 'patch'], 'preferred-countries/{userPreferredCountry}', [UserPreferredCountryController::class, 'update']);
            Route::delete('preferred-countries/{userPreferredCountry}', [UserPreferredCountryController::class, 'destroy']);

            Route::get('notes', [UserNoteController::class, 'index']);
            Route::post('notes', [UserNoteController::class, 'store']);
            Route::match(['put', 'patch'], 'notes/{userNote}', [UserNoteController::class, 'update']);
            Route::delete('notes/{userNote}', [UserNoteController::class, 'destroy']);

            Route::get('archives', [ArchiveController::class, 'index']);
            Route::post('archives', [ArchiveController::class, 'store']);
            Route::match(['put', 'patch'], 'archives/{archive}', [ArchiveController::class, 'update']);
            Route::get('archives/{archive}/download', [ArchiveController::class, 'download']);
            Route::delete('archives/{archive}', [ArchiveController::class, 'destroy']);

            Route::get('security-events', [UserSecurityEventController::class, 'index']);
            Route::get('security-events/{userSecurityEvent}', [UserSecurityEventController::class, 'show']);
        });

        Route::prefix('operations')->group(function (): void {
            Route::get('staff-users', [MeetingController::class, 'staffUsers']);
            Route::get('week-board', [MeetingController::class, 'weekBoard']);

            Route::get('meetings', [MeetingController::class, 'index']);
            Route::post('meetings', [MeetingController::class, 'store']);
            Route::get('meetings/{meeting}', [MeetingController::class, 'show']);
            Route::match(['put', 'patch'], 'meetings/{meeting}', [MeetingController::class, 'update']);
            Route::delete('meetings/{meeting}', [MeetingController::class, 'destroy']);

            Route::get('assigned-tasks', [AssignedTaskController::class, 'index']);
            Route::post('assigned-tasks', [AssignedTaskController::class, 'store']);
            Route::post('assigned-tasks/{assignedTask}/renew', [AssignedTaskController::class, 'renew']);
            Route::get('assigned-tasks/{assignedTask}', [AssignedTaskController::class, 'show']);
            Route::match(['put', 'patch'], 'assigned-tasks/{assignedTask}', [AssignedTaskController::class, 'update']);
            Route::delete('assigned-tasks/{assignedTask}', [AssignedTaskController::class, 'destroy']);

            Route::get('daily-tasks', [DailyTaskController::class, 'index']);
            Route::post('daily-tasks', [DailyTaskController::class, 'store']);
            Route::get('daily-tasks/{dailyTask}', [DailyTaskController::class, 'show']);
            Route::match(['put', 'patch'], 'daily-tasks/{dailyTask}', [DailyTaskController::class, 'update']);
            Route::delete('daily-tasks/{dailyTask}', [DailyTaskController::class, 'destroy']);
        });

        Route::prefix('catalog')->group(function (): void {
            Route::get('user-skills', [UserSkillController::class, 'index']);
            Route::post('user-skills', [UserSkillController::class, 'store']);
            Route::get('user-skills/{userSkill}', [UserSkillController::class, 'show']);
            Route::match(['put', 'patch'], 'user-skills/{userSkill}', [UserSkillController::class, 'update']);
            Route::delete('user-skills/{userSkill}', [UserSkillController::class, 'destroy']);

            Route::get('user-trainings', [UserTrainingController::class, 'index']);
            Route::post('user-trainings', [UserTrainingController::class, 'store']);
            Route::get('user-trainings/{userTraining}', [UserTrainingController::class, 'show']);
            Route::match(['put', 'patch'], 'user-trainings/{userTraining}', [UserTrainingController::class, 'update']);
            Route::delete('user-trainings/{userTraining}', [UserTrainingController::class, 'destroy']);
        });

        // Export modulaire (Excel / CSV / PDF)
        Route::prefix('exports')->group(function (): void {
            Route::get('/schema', ExportSchemaController::class);
            Route::post('/', ExportController::class);
        });
    });
});
