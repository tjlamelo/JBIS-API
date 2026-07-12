<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Shared\Ai\Support\AiScalarText;

/**
 * Corrige les inversions fréquentes poste ↔ établissement dans les brouillons IA.
 */
final class OrganizationNameDisambiguator
{
    /** @var list<string> */
    private const ORG_PATTERNS = [
        '/\b(universit[ée]|university|école|ecole|school|institut|institute|lyc[ée]e|college|facult[ée]|campus|acad[ée]mie)\b/ui',
        '/\b(sa|sarl|sas|gmbh|ltd|inc|corp|plc|group|groupe|minist[èe]re|h[ôo]pital|hospital|clinique|banque|bank)\b/ui',
        '/\b(ens|polytech|sup|camtel|mtn|orange|total|snec|cnps|feicom)\b/ui',
    ];

    /** @var list<string> */
    private const ROLE_PATTERNS = [
        '/\b(développeur|developer|développeuse|ingénieur|engineer|manager|stagiaire|intern|chef|directeur|directrice|analyste|consultant|consultante|technicien|technicienne|assistant|assistante|responsable|charg[ée]|agent|comptable|secr[ée]taire)\b/ui',
    ];

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function disambiguateExperience(array $row): array
    {
        $title = trim(AiScalarText::from($row['job_title'] ?? ''));
        $company = trim(AiScalarText::from($row['company_name'] ?? ''));

        if ($title === '' && $company !== '' && $this->looksLikeJobTitle($company)) {
            $row['job_title'] = $company;
            $row['company_name'] = '';

            return $row;
        }

        if ($company === '' && $title !== '' && $this->looksLikeOrganization($title)) {
            $row['company_name'] = $title;
            $row['job_title'] = '';

            return $row;
        }

        if ($title !== '' && $company !== '' && $this->shouldSwapRoleAndOrganization($title, $company)) {
            $row['job_title'] = $company;
            $row['company_name'] = $title;
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function disambiguateEducation(array $row): array
    {
        $degree = trim(AiScalarText::from($row['degree'] ?? ''));
        $institution = trim(AiScalarText::from($row['institution_name'] ?? ''));

        if ($institution === '' && $degree !== '' && $this->looksLikeOrganization($degree)) {
            $row['institution_name'] = $degree;
            $row['degree'] = '';

            return $row;
        }

        if ($degree !== '' && $institution !== '') {
            if ($this->looksLikeOrganization($degree) && ! $this->looksLikeOrganization($institution)) {
                $row['degree'] = $institution;
                $row['institution_name'] = $degree;
            } elseif ($this->looksLikeOrganization($institution) && ! $this->looksLikeOrganization($degree)) {
                // ordre déjà correct
            } elseif ($this->shouldSwapRoleAndOrganization($degree, $institution)) {
                $row['degree'] = $institution;
                $row['institution_name'] = $degree;
            }
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function disambiguateInternship(array $row): array
    {
        $title = trim(AiScalarText::from($row['title'] ?? ''));
        $organization = trim(AiScalarText::from($row['organization'] ?? ''));

        if ($title !== '' && $organization !== '' && $this->shouldSwapRoleAndOrganization($title, $organization)) {
            $row['title'] = $organization;
            $row['organization'] = $title;
        } elseif ($organization === '' && $title !== '' && $this->looksLikeOrganization($title)) {
            $row['organization'] = $title;
            $row['title'] = '';
        }

        return $row;
    }

    private function shouldSwapRoleAndOrganization(string $first, string $second): bool
    {
        $firstIsOrg = $this->looksLikeOrganization($first);
        $secondIsOrg = $this->looksLikeOrganization($second);
        $firstIsRole = $this->looksLikeJobTitle($first);
        $secondIsRole = $this->looksLikeJobTitle($second);

        if ($firstIsOrg && $secondIsRole) {
            return true;
        }

        if ($firstIsOrg && ! $secondIsOrg && ! $secondIsRole) {
            return false;
        }

        return $firstIsOrg && ! $secondIsOrg;
    }

    private function looksLikeOrganization(string $value): bool
    {
        foreach (self::ORG_PATTERNS as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeJobTitle(string $value): bool
    {
        foreach (self::ROLE_PATTERNS as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }
}
