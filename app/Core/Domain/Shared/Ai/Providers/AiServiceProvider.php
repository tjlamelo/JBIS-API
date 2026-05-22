<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Providers;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelConfigurationException;
use App\Core\Domain\Shared\Ai\Intel\CvProfileFromDocumentsTextService;
use App\Core\Domain\Shared\Ai\Intel\CvSectionStructuredGenerationService;
use App\Core\Domain\Shared\Ai\Intel\JobOfferFromPlainTextService;
use App\Core\Domain\Shared\Ai\Intel\OfferProfileMatchingService;
use App\Core\Domain\Shared\Ai\Services\FakeLanguageModelClient;
use App\Core\Domain\Shared\Ai\Services\GeminiLanguageModelClient;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;

/**
 * Module IA : contrat `LanguageModelClientInterface` branché selon `AI_DRIVER`.
 */
final class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            base_path('config/ai.php'),
            'ai'
        );

        $this->app->singleton(GeminiLanguageModelClient::class, function ($app): GeminiLanguageModelClient {
            /** @var Config $config */
            $config = $app->make('config');
            $gemini = (array) $config->get('ai.gemini', []);

            return new GeminiLanguageModelClient(
                apiKey: (string) ($gemini['api_key'] ?? ''),
                model: (string) ($gemini['model'] ?? 'gemini-2.0-flash'),
                baseUrl: (string) ($gemini['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta'),
                timeout: (int) ($gemini['timeout'] ?? 60),
            );
        });

        $this->app->singleton(FakeLanguageModelClient::class, function ($app): FakeLanguageModelClient {
            /** @var Config $config */
            $config = $app->make('config');
            $fake = (array) $config->get('ai.fake', []);
            $stub = $fake['structured_stub'] ?? null;

            return new FakeLanguageModelClient(
                responseText: (string) ($fake['response'] ?? ''),
                structuredJsonStub: is_array($stub) ? $stub : null,
            );
        });

        $this->app->singleton(LanguageModelClientInterface::class, function ($app): LanguageModelClientInterface {
            /** @var Config $config */
            $config = $app->make('config');

            $driver = (string) $config->get('ai.driver', 'gemini');
            /** @var array<string, class-string> $providers */
            $providers = (array) $config->get('ai.providers', []);
            $class = $providers[$driver] ?? null;

            if (! is_string($class) || ! class_exists($class)) {
                throw new LanguageModelConfigurationException(
                    sprintf('Driver IA inconnu ou classe invalide : %s', $driver)
                );
            }

            $service = $app->make($class);
            if (! $service instanceof LanguageModelClientInterface) {
                throw new LanguageModelConfigurationException(
                    sprintf('Le driver IA %s ne respecte pas LanguageModelClientInterface.', $driver)
                );
            }

            return $service;
        });

        $this->app->alias(LanguageModelClientInterface::class, 'ai.language_model');

        $this->registerIntelServices();
    }

    private function registerIntelServices(): void
    {
        $this->app->singleton(CvProfileFromDocumentsTextService::class);
        $this->app->singleton(JobOfferFromPlainTextService::class);
        $this->app->singleton(OfferProfileMatchingService::class);
        $this->app->singleton(CvSectionStructuredGenerationService::class);
    }

    public function boot(): void
    {
        $this->publishes([
            base_path('config/ai.php') => config_path('ai.php'),
        ], 'ai-config');
    }

    public function provides(): array
    {
        return [
            LanguageModelClientInterface::class,
            GeminiLanguageModelClient::class,
            FakeLanguageModelClient::class,
            'ai.language_model',
            CvProfileFromDocumentsTextService::class,
            JobOfferFromPlainTextService::class,
            OfferProfileMatchingService::class,
            CvSectionStructuredGenerationService::class,
        ];
    }
}
