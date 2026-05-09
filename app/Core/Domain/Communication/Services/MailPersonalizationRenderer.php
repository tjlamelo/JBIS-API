<?php

namespace App\Core\Domain\Communication\Services;

use App\Core\Application\Mail\ViewModels\UserMailViewModel;
use App\Core\Domain\Communication\DTOs\MailTemplateContentDto;
use App\Core\Domain\Communication\DTOs\RenderedMailDto;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Arr;

class MailPersonalizationRenderer
{
    public function renderForUser(
        string $subject,
        ?string $body,
        ?MailTemplateContentDto $content,
        ?User $user,
    ): RenderedMailDto {
        $context = (new UserMailViewModel(
            user: $user,
            custom: $content?->variables() ?? [],
        ))->toContext();

        $renderedContent = $content === null
            ? null
            : new MailTemplateContentDto($this->replaceTokensInArray($content->toArray(), $context));

        return new RenderedMailDto(
            subject: $this->replaceTokens($subject, $context),
            body: $body === null ? null : $this->replaceTokens($body, $context),
            content: $renderedContent,
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function replaceTokensInArray(array $payload, array $context): array
    {
        $result = [];

        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $result[$key] = $this->replaceTokens($value, $context);
                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->replaceTokensInArray($value, $context);
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function replaceTokens(string $text, array $context): string
    {
        return (string) preg_replace_callback('/\{\{\s*([^}]+)\s*\}\}/', function (array $matches) use ($context): string {
            $expression = trim((string) ($matches[1] ?? ''));
            if ($expression === '') {
                return '';
            }

            [$path, $fallback] = array_pad(explode('|', $expression, 2), 2, null);
            $path = trim($path);
            $fallback = $fallback !== null ? trim($fallback) : null;
            $value = data_get($context, $path);

            if (is_scalar($value)) {
                return (string) $value;
            }

            if (is_array($value)) {
                return implode(', ', Arr::flatten($value));
            }

            return $fallback ?? '';
        }, $text);
    }
}
