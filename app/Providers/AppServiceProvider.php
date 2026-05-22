<?php

namespace App\Providers;

use App\Core\Domain\Communication\Contracts\MailboxProvisioner;
use App\Core\Domain\Communication\Contracts\SmsProvider;
use App\Core\Domain\Communication\Events\MailCampaignDispatched;
use App\Core\Domain\Communication\Events\SmsCampaignDispatched;
use App\Core\Domain\Communication\Exceptions\SmsProviderException;
use App\Core\Domain\Communication\Listeners\RefreshMailCampaignStatsListener;
use App\Core\Domain\Communication\Listeners\RefreshSmsCampaignStatsListener;
use App\Core\Domain\Communication\Services\CpanelMailboxProvisionerService;
use App\Core\Domain\Workflow\Services\ProcessFlow\Contracts\ProcessFlowPdfRenderer;
use App\Core\Domain\Workflow\Services\ProcessFlow\ProcessFlowScreenshotPdfRenderer;
use App\Core\Domain\Identity\Models\User;
use App\Listeners\SendWelcomeEmailListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MailboxProvisioner::class, CpanelMailboxProvisionerService::class);

        $this->app->bind(ProcessFlowPdfRenderer::class, ProcessFlowScreenshotPdfRenderer::class);

        $this->app->bind(SmsProvider::class, function ($app): SmsProvider {
            $provider = (string) config('sms.provider', 'queen_sms');
            $providerClass = config("sms.providers.{$provider}");
            if (! is_string($providerClass) || ! class_exists($providerClass)) {
                throw new SmsProviderException("Provider SMS non supporte: {$provider}");
            }

            $service = $app->make($providerClass);
            if (! $service instanceof SmsProvider) {
                throw new SmsProviderException("Provider SMS invalide: {$provider}");
            }

            return $service;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Registered::class, SendWelcomeEmailListener::class);
        Event::listen(MailCampaignDispatched::class, RefreshMailCampaignStatsListener::class);
        Event::listen(SmsCampaignDispatched::class, RefreshSmsCampaignStatsListener::class);

        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            $frontUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');
            $query = http_build_query([
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);

            return $frontUrl.'/reset-password?'.$query;
        });

        VerifyEmail::createUrlUsing(function (User $user): string {
            return URL::temporarySignedRoute(
                'api.verification.verify',
                Carbon::now()->addMinutes(60),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );
        });
    }
}
