<?php

namespace App\Providers;

use App\Core\Domain\Communication\Contracts\MailboxProvisioner;
use App\Core\Domain\Communication\Contracts\SubdomainProvisioner;
use App\Core\Domain\Communication\Contracts\SmsProvider;
use App\Core\Domain\Communication\Events\MailCampaignDispatched;
use App\Core\Domain\Communication\Events\SmsCampaignDispatched;
use App\Core\Domain\Communication\Exceptions\SmsProviderException;
use App\Core\Domain\Communication\Listeners\RefreshMailCampaignStatsListener;
use App\Core\Domain\Communication\Listeners\RefreshSmsCampaignStatsListener;
use App\Core\Domain\Communication\Services\CpanelMailboxProvisionerService;
use App\Core\Domain\Communication\Services\CpanelSubdomainProvisionerService;
use App\Core\Domain\Workflow\Services\ProcessFlow\Contracts\ProcessFlowPdfRenderer;
use App\Core\Domain\Workflow\Services\ProcessFlow\ProcessFlowScreenshotPdfRenderer;
use App\Core\Domain\Communication\Support\JbisMailbox;
use App\Core\Domain\Identity\Models\User;
use App\Listeners\SendWelcomeEmailListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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
        $this->app->bind(SubdomainProvisioner::class, CpanelSubdomainProvisionerService::class);

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
            $frontUrl = (string) config('app.frontend_url', 'http://localhost:3000');
            $query = http_build_query([
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);

            return $frontUrl.'/reset-password?'.$query;
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $frontUrl = (string) config('app.frontend_url', 'http://localhost:3000');
            $email = method_exists($notifiable, 'getEmailForPasswordReset')
                ? (string) $notifiable->getEmailForPasswordReset()
                : (string) ($notifiable->email ?? '');
            $url = $frontUrl.'/reset-password?'.http_build_query([
                'token' => $token,
                'email' => $email,
            ]);

            $expire = (int) config(
                'auth.passwords.'.config('auth.defaults.passwords').'.expire',
                60,
            );

            return (new MailMessage)
                ->from(JbisMailbox::address('noreply'), JbisMailbox::name('noreply'))
                ->replyTo(JbisMailbox::address('contact'), JbisMailbox::name('contact'))
                ->subject(__('Réinitialisation de votre mot de passe JBIS'))
                ->line(__('Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.'))
                ->action(__('Réinitialiser le mot de passe'), $url)
                ->line(__('Ce lien expirera dans :count minutes.', ['count' => $expire]))
                ->line(__('Si vous n\'avez pas demandé de réinitialisation, aucune action n\'est requise.'));
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
