<?php

use App\Core\Application\Api\V1\Catalog\Controllers\Offer\AdminOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\ForceDeleteOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\OfferCategoryController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\OfferFilterController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\PublicOfferController;
use App\Core\Application\Api\V1\Catalog\Controllers\Offer\RestoreOfferController;
use Illuminate\Support\Facades\Route;
use App\Core\Application\Api\V1\Auth\Controllers\AuthTokenController;
use App\Core\Application\Api\V1\Catalog\Controllers\{
    BenefitController,
    CityController,
    CompanyController,
    ContractTypeController,
    CountryController,
    EducationLevelController,
    LanguageController,
    LanguageLevelController,
    OfferTypeController,
    SkillCategoryController,
    SkillController,
    RequiredDocumentController,
    PublicProgramController,
    ProgramFilterController,
    WorkScheduleController
};
use App\Core\Application\Api\V1\Mail\Controllers\MailCampaignController;
use App\Core\Application\Api\V1\Sms\Controllers\SmsCampaignController;

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

    // Vérification d'email
    Route::get('/email/verify/{id}/{hash}', [AuthTokenController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('api.verification.verify');

    // Catalogue Public (SEO : Utilise les Slugs)
Route::prefix('public')->group(function (): void {
    // 1. Les filtres globaux (D'abord !)
    Route::get('/filters', OfferFilterController::class);

    // 2. Les offres
    Route::get('/offers', [PublicOfferController::class, 'index']);
    // On ajoute une contrainte regex pour le slug afin qu'il ne capture pas "filters"
    Route::get('/offers/{slug}', [PublicOfferController::class, 'show'])
         ->where('slug', '[a-z0-9\-]+'); 

    // 3. Les programmes
    Route::prefix('programs')->group(function (): void {
        Route::get('/filters', ProgramFilterController::class); 
        Route::get('/', [PublicProgramController::class, 'index']);
        Route::get('/{slug}', [PublicProgramController::class, 'show']);
        Route::get('/{programId}/offers', [PublicOfferController::class, 'indexByProgram']);
    });
});
    /*
    |--------------------------------------------------------------------------
    | ROUTES PRIVÉES (Authentification Requise)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function (): void {

        // Mon Compte & Sécurité
        Route::get('/me', [AuthTokenController::class, 'me']);
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
            
            // --- GESTION ADMIN DES OFFRES (Préfixe admin ajouté) ---
            Route::prefix('admin/offers')->group(function (): void {
                Route::get('/', [AdminOfferController::class, 'index']);
                Route::post('/', [AdminOfferController::class, 'store']);
                Route::post('/upload-photo', [AdminOfferController::class, 'uploadPhoto']);
                Route::get('/{offer}', [AdminOfferController::class, 'show'])->withTrashed();
                Route::put('/{offer}', [AdminOfferController::class, 'update']);
                Route::delete('/{offer}', [AdminOfferController::class, 'destroy']);
                Route::post('/{offer}/restore', RestoreOfferController::class)->withTrashed();
                Route::delete('/{offer}/force', ForceDeleteOfferController::class)->withTrashed();
            });

            // Listes de sélection (Dropdowns / Catalogues de référence)
            Route::get('/categories', [OfferCategoryController::class, 'index']);
            Route::get('/companies', [CompanyController::class, 'index']);
            Route::get('/countries', [CountryController::class, 'index']);
            Route::get('/cities', [CityController::class, 'index']);
            Route::get('/benefits', [BenefitController::class, 'index']);
            Route::get('/contract-types', [ContractTypeController::class, 'index']);
            Route::get('/offer-types', [OfferTypeController::class, 'index']);
            Route::get('/work-schedules', [WorkScheduleController::class, 'index']);
            Route::get('/education-levels', [EducationLevelController::class, 'index']);
            Route::get('/languages', [LanguageController::class, 'index']);
            Route::get('/language-levels', [LanguageLevelController::class, 'index']);
            Route::get('/skill-categories', [SkillCategoryController::class, 'index']);
            Route::get('/skills', [SkillController::class, 'index']);
            Route::get('/required-documents', [RequiredDocumentController::class, 'index']);
            Route::get('/programs', [PublicProgramController::class, 'index']);
        });

        // Campagnes Marketing
        Route::prefix('mail-campaigns')->group(function (): void {
            Route::get('/', [MailCampaignController::class, 'index']);
            Route::post('/preview', [MailCampaignController::class, 'preview']);
            Route::post('/send', [MailCampaignController::class, 'send']);
            Route::get('/{campaign}', [MailCampaignController::class, 'show']);
            Route::post('/{campaign}/refresh-stats', [MailCampaignController::class, 'refreshStats']);
        });

        Route::prefix('sms-campaigns')->group(function (): void {
            Route::get('/', [SmsCampaignController::class, 'index']);
            Route::post('/preview', [SmsCampaignController::class, 'preview']);
            Route::post('/send', [SmsCampaignController::class, 'send']);
            Route::get('/credit', [SmsCampaignController::class, 'credit']);
            Route::get('/{campaign}', [SmsCampaignController::class, 'show']);
            Route::post('/{campaign}/refresh-stats', [SmsCampaignController::class, 'refreshStats']);
        });
    });
});