<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Identity\Models\User;

/**
 * Construit un texte de synthèse du candidat pour `OfferProfileMatchingService` (ou autres prompts).
 */
final class CandidateProfileSnapshotTextBuilder
{
    public static function build(User $user): string
    {
        $user->loadMissing(['profile', 'educations', 'experiences', 'languages.language', 'certifications']);

        $lines = [];

        $p = $user->profile;
        if ($p !== null) {
            $lines[] = '--- Profil ---';
            $lines[] = trim(implode(' ', array_filter([
                $p->first_name,
                $p->last_name,
            ])));
            foreach (['bio', 'address', 'place_of_birth', 'gender', 'marital_status', 'phone_number2', 'email_institutional'] as $field) {
                $v = $p->{$field} ?? null;
                if (is_string($v) && $v !== '') {
                    $lines[] = $field.': '.$v;
                }
            }
        }

        if ($user->educations->isNotEmpty()) {
            $lines[] = '--- Formations (education) ---';
            foreach ($user->educations as $e) {
                $lines[] = sprintf(
                    '- %s @ %s (%s → %s) %s',
                    $e->degree,
                    $e->institution_name,
                    $e->start_date?->format('Y-m-d') ?? '',
                    $e->end_date?->format('Y-m-d') ?? '',
                    $e->field_of_study ?? ''
                );
            }
        }

        if ($user->experiences->isNotEmpty()) {
            $lines[] = '--- Expériences ---';
            foreach ($user->experiences as $x) {
                $lines[] = sprintf(
                    '- %s chez %s (%s → %s)',
                    $x->job_title,
                    $x->company_name,
                    $x->start_date?->format('Y-m-d') ?? '',
                    $x->is_current ? 'présent' : ($x->end_date?->format('Y-m-d') ?? '')
                );
                if (is_string($x->responsibilities) && $x->responsibilities !== '') {
                    $lines[] = '  '.$x->responsibilities;
                }
            }
        }

        if ($user->languages->isNotEmpty()) {
            $lines[] = '--- Langues ---';
            foreach ($user->languages as $ul) {
                $name = '';
                if ($ul->language !== null) {
                    $name = $ul->language->getTranslation('name', 'fr')
                        ?: $ul->language->getTranslation('name', 'en')
                        ?: '';
                }
                if ($name === '') {
                    $name = 'langue #'.$ul->language_id;
                }
                $lines[] = '- '.$name.' : '.($ul->proficiency_level ?? '');
            }
        }

        $certs = $user->certifications;
        if ($certs->isNotEmpty()) {
            $lines[] = '--- Certifications ---';
            foreach ($certs as $c) {
                $lines[] = sprintf('- %s (%s)', $c->name, $c->issuing_organization);
            }
        }

        return implode("\n", $lines) ?: 'Profil vide.';
    }
}
