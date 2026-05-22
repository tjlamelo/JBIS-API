<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\DTOs;

use App\Core\Domain\Identity\Support\LoginRiskLevel;

final readonly class LoginRiskAssessmentDto
{
    /**
     * @param  list<string>  $signals
     * @param  array<string, int>  $signal_scores
     */
    public function __construct(
        public int $score,
        public LoginRiskLevel $level,
        public array $signals,
        public array $signal_scores,
        public bool $isNewDevice,
        public bool $shouldBlock,
        public bool $shouldChallenge,
        public bool $shouldNotify,
        public bool $trustDevice,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'level' => $this->level->value,
            'signals' => $this->signals,
            'signal_scores' => $this->signal_scores,
            'is_new_device' => $this->isNewDevice,
            'should_block' => $this->shouldBlock,
            'should_challenge' => $this->shouldChallenge,
            'should_notify' => $this->shouldNotify,
            'trust_device' => $this->trustDevice,
        ];
    }

    public function userMessage(): string
    {
        if ($this->shouldBlock) {
            return __('Connexion bloquée pour votre sécurité. Vérifiez votre e-mail ou contactez le support.');
        }

        if ($this->shouldChallenge) {
            return __('Connexion à risque élevé. Une vérification supplémentaire est requise.');
        }

        return __('Connexion inhabituelle détectée.');
    }
}
