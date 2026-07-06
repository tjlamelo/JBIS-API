<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Catalog\Models\Benefit;
use App\Core\Domain\Catalog\Models\ContractType;
use App\Core\Domain\Catalog\Models\EducationLevel;
use App\Core\Domain\Catalog\Models\WorkSchedule;
use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Identity\Support\CountryNameResolver;
use App\Core\Domain\Identity\Support\LanguageCatalogResolver;
use App\Core\Domain\Identity\Support\SkillCatalogResolver;
use App\Core\Domain\Location\Models\City;
use Illuminate\Support\Str;

/**
 * Post-traitement du brouillon IA offre : normalisation fr/en, résolution catalogue, valeurs par défaut.
 */
final class OfferExtractionDraftEnricher
{
    /** @var array<string, list<string>> */
    private const REQUIRED_DOCUMENT_ALIASES = [
        'passeport-valide' => ['passport', 'passeport', 'valid passport'],
        'cv' => ['curriculum', 'resume', 'résumé', 'resumé'],
        'diplome-le-plus-eleve' => ['diploma', 'diplôme', 'diplome', 'degree'],
        'certificat-de-visite-medicale' => ['medical', 'visite médicale', 'medical examination', 'fitness', 'medical insurance certificate'],
        'casier-judiciaire' => ['criminal record', 'casier', 'antecedent'],
        'photo-identite' => ['photo', 'id photo', 'identity photo'],
        'extrait-de-naissance' => ['birth certificate', 'acte de naissance', 'naissance'],
        'lettre-motivation' => ['cover letter', 'motivation letter', 'lettre de motivation'],
        'test-de-langue-ieltstef' => ['ielts', 'tef', 'language test', 'test de langue'],
    ];

    /** @var list<string> */
    private const UNCERTAINTY_PATTERNS = [
        'a verifier', 'à vérifier', 'si applicable', 'peut-etre', 'peut-être', 'semble',
        'non precise', 'non précisé', 'non mentionne', 'non mentionné', 'a confirmer', 'à confirmer',
        'inferred', 'hint', 'catalogue', 'id:', 'uuid', 'json', 'champ ',
    ];

    /** @var array<string, int> */
    private const LOCALIZED_LIMITS = [
        'description' => 1000,
        'responsibilities' => 1800,
        'requirements' => 1800,
        'specific_documents' => 1200,
    ];

    /** @var array<string, list<string>> */
    private const BENEFIT_ALIASES = [
        'logement' => ['accommodation', 'housing', 'lodging', 'hébergement', 'hebergement'],
        'nutrition' => ['food', 'meals', 'repas', 'meal', 'board'],
        'assurance' => ['medical insurance', 'health insurance', 'insurance', 'mutuelle', 'medical'],
        'transport' => ['transportation', 'transport', 'navette', 'shuttle'],
        'uniform' => ['uniform', 'uniforme', 'tenue'],
        'visa' => ['visa', 'work permit', 'permis de travail'],
    ];

    public function __construct(
        private readonly CountryNameResolver $countryResolver,
        private readonly LanguageCatalogResolver $languageResolver,
        private readonly SkillCatalogResolver $skillResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $draft
     * @param  array<string, mixed>  $formContext
     * @return array<string, mixed>
     */
    public function enrich(array $draft, array $formContext = [], string $scope = 'full'): array
    {
        $draft = $this->normalizeLocalizedFields($draft);
        $draft = $this->humanizeLocalizedDraft($draft, $formContext);

        if ($scope === 'editorial') {
            return $this->finalizeLocalizedDraft([
                'description' => $draft['description'] ?? ['fr' => '', 'en' => ''],
                'responsibilities' => $draft['responsibilities'] ?? ['fr' => '', 'en' => ''],
                'requirements' => $draft['requirements'] ?? ['fr' => '', 'en' => ''],
                'specific_documents' => $draft['specific_documents'] ?? ['fr' => '', 'en' => ''],
                'notes' => '',
            ]);
        }

        $draft = $this->applyDefaults($draft, $formContext);
        $draft = $this->supplementFromRawText($draft, (string) ($formContext['raw_text'] ?? ''));

        $unmatched = [
            'benefits' => [],
            'skills' => [],
            'languages' => [],
        ];

        $benefitIds = $this->resolveBenefitIds($draft, $unmatched);
        $skillRequirements = $this->resolveSkillRequirements($draft, $unmatched);
        $languageRequirements = $this->resolveLanguageRequirements($draft, $unmatched);
        $requiredDocuments = $this->resolveRequiredDocuments($draft);

        $countryId = $formContext['country_id'] ?? null;
        if (! $countryId) {
            $countryId = $this->resolveCountryId($draft);
        }

        $cityId = $formContext['city_id'] ?? null;
        if (! $cityId && is_string($draft['city_hint'] ?? null) && $draft['city_hint'] !== '') {
            $cityId = $this->resolveCityId((string) $draft['city_hint'], $countryId);
        }

        return $this->finalizeLocalizedDraft([
            'description' => $draft['description'] ?? ['fr' => '', 'en' => ''],
            'responsibilities' => $draft['responsibilities'] ?? ['fr' => '', 'en' => ''],
            'requirements' => $draft['requirements'] ?? ['fr' => '', 'en' => ''],
            'specific_documents' => $draft['specific_documents'] ?? ['fr' => '', 'en' => ''],
            'work_mode' => $this->normalizeWorkMode($draft['work_mode'] ?? null),
            'salary_min' => $this->nullableNumber($draft['salary_min'] ?? null),
            'salary_max' => $this->nullableNumber($draft['salary_max'] ?? null),
            'currency' => is_string($draft['currency'] ?? null) && $draft['currency'] !== ''
                ? strtoupper(trim($draft['currency']))
                : ($formContext['currency'] ?? 'XAF'),
            'is_salary_public' => $this->resolveSalaryPublic($draft),
            'is_company_public' => $this->resolveCompanyPublic($formContext),
            'available_positions' => max(1, (int) ($draft['available_positions'] ?? 1)),
            'address' => is_string($draft['address'] ?? null) ? trim($draft['address']) : '',
            'country_id' => $countryId,
            'city_id' => $cityId,
            'contract_type_id' => $this->resolveContractTypeId($draft['contract_type_hint'] ?? null),
            'work_schedule_id' => $this->resolveWorkScheduleId(
                $draft['work_schedule_hint'] ?? $this->localizedTextBlob($draft, 'responsibilities'),
            ),
            'education_level_id' => $this->resolveEducationLevelId(
                $draft['education_level_hint'] ?? $this->localizedTextBlob($draft, 'requirements'),
            ),
            'benefit_ids' => $benefitIds,
            'language_requirements' => $languageRequirements,
            'skill_requirements' => $skillRequirements,
            'required_documents' => $requiredDocuments,
            'notes' => '',
            'unmatched' => $unmatched,
        ]);
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function normalizeLocalizedFields(array $draft): array
    {
        foreach (['description', 'responsibilities', 'requirements', 'specific_documents', 'expectations', 'specifications'] as $key) {
            if (! isset($draft[$key]) || ! is_array($draft[$key])) {
                $draft[$key] = ['fr' => '', 'en' => ''];

                continue;
            }

            $fr = trim((string) ($draft[$key]['fr'] ?? ''));
            $en = trim((string) ($draft[$key]['en'] ?? ''));

            if (mb_strlen($fr) > ($limit = self::LOCALIZED_LIMITS[$key] ?? 1800)) {
                $fr = mb_substr($fr, 0, $limit);
            }
            if (mb_strlen($en) > ($limit = self::LOCALIZED_LIMITS[$key] ?? 1800)) {
                $en = mb_substr($en, 0, $limit);
            }

            if ($fr === '' && $en !== '') {
                $fr = $en;
            }
            if ($en === '' && $fr !== '') {
                $en = $fr;
            }

            $draft[$key] = ['fr' => $fr, 'en' => $en];
        }

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  array<string, mixed>  $formContext
     * @return array<string, mixed>
     */
    private function humanizeLocalizedDraft(array $draft, array $formContext): array
    {
        $draft = $this->redistributeLegacyBlocks($draft);

        foreach (['description', 'responsibilities', 'requirements', 'specific_documents'] as $key) {
            if (! isset($draft[$key]) || ! is_array($draft[$key])) {
                $draft[$key] = ['fr' => '', 'en' => ''];

                continue;
            }

            foreach (['fr', 'en'] as $locale) {
                $sanitized = $this->sanitizeHumanText((string) ($draft[$key][$locale] ?? ''));
                $draft[$key][$locale] = $this->formatEditorialText($sanitized, $key);
            }
        }

        return $this->ensureEditorialSectionsFilled($draft, $formContext);
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function redistributeLegacyBlocks(array $draft): array
    {
        foreach (['expectations' => 'responsibilities', 'specifications' => 'requirements'] as $source => $target) {
            $block = $draft[$source] ?? null;
            if (! is_array($block)) {
                continue;
            }

            foreach (['fr', 'en'] as $locale) {
                $extra = trim((string) ($block[$locale] ?? ''));
                if ($extra === '') {
                    continue;
                }

                $current = trim((string) ($draft[$target][$locale] ?? ''));
                $draft[$target][$locale] = $current === '' ? $extra : $current."\n\n".$extra;
            }
        }

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  array<string, mixed>  $formContext
     * @return array<string, mixed>
     */
    private function ensureEditorialSectionsFilled(array $draft, array $formContext): array
    {
        $tradeNames = is_array($formContext['trade_name'] ?? null) ? $formContext['trade_name'] : [];

        foreach (['fr', 'en'] as $locale) {
            $trade = trim((string) ($tradeNames[$locale] ?? $tradeNames['en'] ?? $tradeNames['fr'] ?? ''));
            if ($trade === '') {
                continue;
            }

            if (trim((string) ($draft['responsibilities'][$locale] ?? '')) === '') {
                $draft['responsibilities'][$locale] = $locale === 'fr'
                    ? "1. Assurer les missions liées au poste de {$trade}\n2. Respecter les consignes de sécurité et les horaires convenus"
                    : "1. Carry out duties related to the {$trade} role\n2. Follow safety instructions and agreed working hours";
            }

            if (trim((string) ($draft['requirements'][$locale] ?? '')) === '') {
                $draft['requirements'][$locale] = $locale === 'fr'
                    ? "1. Profil adapté au poste de {$trade}\n2. Motivation, sérieux et respect des consignes"
                    : "1. Profile suited to the {$trade} position\n2. Motivated, reliable, and compliant with instructions";
            }
        }

        return $draft;
    }

    private function sanitizeHumanText(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\b[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\b/i', '', $text) ?? $text;
        $text = preg_replace('/\b(id|uuid|slug|trade_id|company_id|benefit_id)\s*[:=]\s*\S+/i', '', $text) ?? $text;

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $kept = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                if ($kept !== [] && end($kept) !== '') {
                    $kept[] = '';
                }

                continue;
            }

            $normalizedLine = $this->normalize($line);
            $skip = false;
            foreach (self::UNCERTAINTY_PATTERNS as $pattern) {
                if (str_contains($normalizedLine, $this->normalize($pattern))) {
                    $skip = true;
                    break;
                }
            }

            if (! $skip) {
                $kept[] = $line;
            }
        }

        $text = trim(implode("\n", $kept));
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function formatEditorialText(string $text, string $section): string
    {
        if ($text === '') {
            return '';
        }

        return match ($section) {
            'description' => $this->formatDescriptionText($text),
            'responsibilities', 'requirements' => $this->formatNumberedList($text),
            'specific_documents' => $this->formatBulletList($text),
            default => $text,
        };
    }

    private function formatDescriptionText(string $text): string
    {
        $lines = $this->extractContentLines($text);
        if ($lines === []) {
            return '';
        }

        if (count($lines) === 1) {
            return $this->normalizeSentence($lines[0]);
        }

        $paragraphs = [];
        $current = '';

        foreach ($lines as $line) {
            $sentence = $this->normalizeSentence($line);
            if ($current === '') {
                $current = $sentence;

                continue;
            }

            if (mb_strlen($current.' '.$sentence) > 420) {
                $paragraphs[] = $current;
                $current = $sentence;
            } else {
                $current .= ' '.$sentence;
            }
        }

        if ($current !== '') {
            $paragraphs[] = $current;
        }

        return implode("\n\n", array_slice($paragraphs, 0, 3));
    }

    private function formatNumberedList(string $text): string
    {
        $lines = $this->extractContentLines($text);
        if ($lines === []) {
            return '';
        }

        $formatted = [];
        $index = 1;

        foreach ($lines as $line) {
            $line = $this->normalizeSentence($this->stripListMarker($line));
            if ($line === '') {
                continue;
            }

            $formatted[] = $index.'. '.$line;
            $index++;
        }

        return implode("\n", $formatted);
    }

    private function formatBulletList(string $text): string
    {
        $lines = $this->extractContentLines($text);
        if ($lines === []) {
            return '';
        }

        $formatted = [];

        foreach ($lines as $line) {
            $line = $this->normalizeSentence($this->stripListMarker($line));
            if ($line === '') {
                continue;
            }

            $formatted[] = '• '.$line;
        }

        return implode("\n", $formatted);
    }

    /**
     * @return list<string>
     */
    private function extractContentLines(string $text): array
    {
        $rawLines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $items = [];
        $buffer = '';

        foreach ($rawLines as $line) {
            $line = trim($line);
            if ($line === '') {
                if ($buffer !== '') {
                    $items[] = $this->stripListMarker($buffer);
                    $buffer = '';
                }

                continue;
            }

            if (preg_match('/^([\d]+[\.\)]|[-•*])\s+/u', $line) === 1) {
                if ($buffer !== '') {
                    $items[] = $this->stripListMarker($buffer);
                    $buffer = '';
                }
                $items[] = $this->stripListMarker($line);

                continue;
            }

            $buffer = $buffer === '' ? $line : $buffer.' '.$line;
        }

        if ($buffer !== '') {
            $items[] = $this->stripListMarker($buffer);
        }

        return array_values(array_filter(
            array_map(static fn (string $item): string => trim($item), $items),
            static fn (string $item): bool => $item !== '',
        ));
    }

    private function stripListMarker(string $line): string
    {
        $line = trim($line);

        return trim(preg_replace('/^([\d]+[\.\)]|[-•*])\s*/u', '', $line) ?? $line);
    }

    private function normalizeSentence(string $text): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($text === '') {
            return '';
        }

        $first = mb_strtoupper(mb_substr($text, 0, 1));

        return $first.mb_substr($text, 1);
    }

    private function translatableLabel(mixed $value, string $locale): string
    {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[$locale] ?? $value['fr'] ?? $value['en'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return list<array{required_document_id: int, is_mandatory: bool, sort_order: int}>
     */
    private function resolveRequiredDocuments(array $draft): array
    {
        $hints = is_array($draft['inferred_required_documents'] ?? null) ? $draft['inferred_required_documents'] : [];
        $specificBlob = $this->localizedTextBlob($draft, 'specific_documents');
        if ($specificBlob !== '') {
            $hints[] = $specificBlob;
        }

        if ($hints === []) {
            return [];
        }

        $documents = RequiredDocument::query()->get(['id', 'name', 'slug']);
        $resolved = [];
        $sortOrder = 0;

        foreach ($hints as $hint) {
            if (! is_string($hint) || trim($hint) === '') {
                continue;
            }

            $id = $this->matchRequiredDocumentId($hint, $documents);
            if ($id === null) {
                continue;
            }

            if (! isset($resolved[$id])) {
                $resolved[$id] = [
                    'required_document_id' => $id,
                    'is_mandatory' => true,
                    'sort_order' => $sortOrder++,
                ];
            }
        }

        return array_values($resolved);
    }

    /**
     * @param  iterable<RequiredDocument>  $documents
     */
    private function matchRequiredDocumentId(string $hint, iterable $documents): ?int
    {
        $needle = $this->normalize($hint);

        foreach ($documents as $document) {
            $slug = $this->normalize((string) $document->slug);
            if ($slug !== '' && (str_contains($needle, $slug) || str_contains($slug, $needle))) {
                return (int) $document->id;
            }

            foreach (['fr', 'en'] as $locale) {
                $label = $this->normalize($this->translatableLabel($document->name, $locale));
                if ($label !== '' && (str_contains($label, $needle) || str_contains($needle, $label))) {
                    return (int) $document->id;
                }
            }
        }

        foreach (self::REQUIRED_DOCUMENT_ALIASES as $slug => $aliases) {
            $slugNorm = $this->normalize($slug);
            if (! str_contains($needle, $slugNorm)) {
                $matchedAlias = false;
                foreach ($aliases as $alias) {
                    if (str_contains($needle, $this->normalize($alias))) {
                        $matchedAlias = true;
                        break;
                    }
                }
                if (! $matchedAlias) {
                    continue;
                }
            }

            foreach ($documents as $document) {
                if ($this->normalize((string) $document->slug) === $slugNorm) {
                    return (int) $document->id;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function resolveSalaryPublic(array $draft): bool
    {
        $hasSalary = $this->nullableNumber($draft['salary_min'] ?? null) !== null
            || $this->nullableNumber($draft['salary_max'] ?? null) !== null;

        if (! $hasSalary) {
            return false;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $formContext
     */
    private function resolveCompanyPublic(array $formContext): bool
    {
        if (! empty($formContext['company_id'])) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  array<string, mixed>  $formContext
     * @return array<string, mixed>
     */
    private function applyDefaults(array $draft, array $formContext): array
    {
        if (empty($draft['work_mode'])) {
            $draft['work_mode'] = 'on-site';
        }

        if (empty($draft['available_positions'])) {
            $draft['available_positions'] = 1;
        }

        if (empty($draft['address']) && is_string($draft['city_hint'] ?? null) && $draft['city_hint'] !== '') {
            $draft['address'] = trim((string) $draft['city_hint']);
        }

        if (empty($draft['country_hint']) && ! empty($formContext['country_name'])) {
            $draft['country_hint'] = (string) $formContext['country_name'];
        }

        return $draft;
    }

    /**
     * Complète le brouillon IA avec des signaux détectés dans le texte source (salaire, pays, avantages…).
     *
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function supplementFromRawText(array $draft, string $rawText): array
    {
        $rawText = trim($rawText);
        if ($rawText === '') {
            return $draft;
        }

        if ($this->nullableNumber($draft['salary_min'] ?? null) === null) {
            if (preg_match('/salary[:\s]+(\d+(?:[.,]\d+)?)\s*([A-Z]{3})?/iu', $rawText, $matches) === 1) {
                $amount = (float) str_replace(',', '.', $matches[1]);
                $draft['salary_min'] = $amount;
                $draft['salary_max'] = $amount;
                if (! empty($matches[2])) {
                    $draft['currency'] = strtoupper($matches[2]);
                }
            } elseif (preg_match('/(\d+(?:[.,]\d+)?)\s*(AED|USD|EUR|XAF|GBP|CAD)\b/iu', $rawText, $matches) === 1) {
                $amount = (float) str_replace(',', '.', $matches[1]);
                $draft['salary_min'] = $amount;
                $draft['salary_max'] = $amount;
                $draft['currency'] = strtoupper($matches[2]);
            }
        } elseif ($this->nullableNumber($draft['salary_max'] ?? null) === null) {
            $draft['salary_max'] = $this->nullableNumber($draft['salary_min']);
        }

        if (empty($draft['currency']) && preg_match('/\b(AED|USD|EUR|XAF|GBP|CAD)\b/i', $rawText, $matches) === 1) {
            $draft['currency'] = strtoupper($matches[1]);
        }

        $normalized = $this->normalize($rawText);

        if (empty($draft['country_hint'])) {
            if (str_contains($normalized, 'uae') || str_contains($normalized, 'emirats') || str_contains($normalized, 'emirates')) {
                $draft['country_hint'] = 'UAE';
            }
        }

        if (empty($draft['contract_type_hint']) && preg_match('/contract[:\s]+(\d+)\s*years?/iu', $rawText, $matches) === 1) {
            $draft['contract_type_hint'] = $matches[1].' years';
        }

        if (empty($draft['work_schedule_hint']) && preg_match('/(\d+)\s*hours?\s*per\s*day/iu', $rawText, $matches) === 1) {
            $draft['work_schedule_hint'] = $matches[1].' hours per day';
        }

        $benefitHints = $this->detectBenefitHintsFromText($rawText);
        $existingBenefits = is_array($draft['inferred_benefits'] ?? null) ? $draft['inferred_benefits'] : [];
        $draft['inferred_benefits'] = array_values(array_unique(array_merge($existingBenefits, $benefitHints)));

        if (empty($draft['language_requirements'])) {
            $draft['language_requirements'] = $this->detectLanguageRequirementsFromText($rawText);
        }

        return $draft;
    }

    /**
     * @return list<string>
     */
    private function detectBenefitHintsFromText(string $text): array
    {
        $normalized = $this->normalize($text);
        $hints = [];

        foreach (self::BENEFIT_ALIASES as $keyword => $aliases) {
            $terms = array_merge([$keyword], $aliases);

            foreach ($terms as $term) {
                $termNorm = $this->normalize($term);
                if ($termNorm !== '' && str_contains($normalized, $termNorm)) {
                    $hints[] = (string) $term;
                    break;
                }
            }
        }

        return array_values(array_unique($hints));
    }

    /**
     * @return list<array{language_hint: string, level_hint: string}>
     */
    private function detectLanguageRequirementsFromText(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $rows = [];
        $patterns = [
            ['pattern' => '/\b(basic|elementary)\s+english\b/i', 'language' => 'English', 'level' => 'basic'],
            ['pattern' => '/\b(fluent|advanced)\s+english\b/i', 'language' => 'English', 'level' => 'fluent'],
            ['pattern' => '/\benglish\b/i', 'language' => 'English', 'level' => ''],
            ['pattern' => '/\b(basic|elementary)\s+anglais\b/i', 'language' => 'Anglais', 'level' => 'basic'],
            ['pattern' => '/\banglais\b/i', 'language' => 'Anglais', 'level' => ''],
            ['pattern' => '/\bfrench\b/i', 'language' => 'French', 'level' => ''],
            ['pattern' => '/\bfrançais\b/i', 'language' => 'Français', 'level' => ''],
            ['pattern' => '/\barabic\b/i', 'language' => 'Arabic', 'level' => ''],
            ['pattern' => '/\barabe\b/i', 'language' => 'Arabe', 'level' => ''],
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern['pattern'], $text) !== 1) {
                continue;
            }

            $rows[] = [
                'language_hint' => $pattern['language'],
                'level_hint' => $pattern['level'],
            ];
        }

        $unique = [];
        foreach ($rows as $row) {
            $unique[$row['language_hint']] = $row;
        }

        return array_values($unique);
    }

    private function normalizeWorkMode(mixed $value): string
    {
        $mode = strtolower(trim((string) $value));

        return match ($mode) {
            'hybrid', 'remote', 'on-site' => $mode,
            'onsite', 'on site', 'présentiel', 'presentiel' => 'on-site',
            'télétravail', 'teletravail', 'remote work' => 'remote',
            default => 'on-site',
        };
    }

    private function nullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  array{benefits: list<string>, skills: list<string>, languages: list<string>}  $unmatched
     * @return list<int>
     */
    private function resolveBenefitIds(array $draft, array &$unmatched): array
    {
        $hints = is_array($draft['inferred_benefits'] ?? null) ? $draft['inferred_benefits'] : [];
        $textBlob = $this->localizedTextBlob($draft, 'description').' '
            .$this->localizedTextBlob($draft, 'responsibilities');
        $hints = array_values(array_unique(array_merge($hints, $this->detectBenefitHintsFromText($textBlob))));
        $benefits = Benefit::query()->get(['id', 'name', 'slug']);
        $resolved = [];

        foreach ($hints as $hint) {
            if (! is_string($hint) || trim($hint) === '') {
                continue;
            }

            $id = $this->matchBenefitId($hint, $benefits);
            if ($id !== null) {
                $resolved[] = $id;

                continue;
            }

            $unmatched['benefits'][] = $hint;
        }

        return array_values(array_unique($resolved));
    }

    /**
     * @param  iterable<Benefit>  $benefits
     */
    private function matchBenefitId(string $hint, iterable $benefits): ?int
    {
        $needle = $this->normalize($hint);

        foreach ($benefits as $benefit) {
            foreach (['fr', 'en'] as $locale) {
                $label = $this->normalize((string) $benefit->getTranslation('name', $locale, false));
                if ($label !== '' && (str_contains($label, $needle) || str_contains($needle, $label))) {
                    return (int) $benefit->id;
                }
            }
        }

        foreach (self::BENEFIT_ALIASES as $keyword => $aliases) {
            $terms = array_merge([$keyword], $aliases);
            $groupMatches = str_contains($needle, $keyword) || in_array($needle, $aliases, true);

            if (! $groupMatches) {
                foreach ($aliases as $alias) {
                    if (str_contains($needle, $this->normalize($alias))) {
                        $groupMatches = true;
                        break;
                    }
                }
            }

            if (! $groupMatches) {
                continue;
            }

            foreach ($benefits as $benefit) {
                $slug = $this->normalize(str_replace('-', ' ', (string) $benefit->slug));

                foreach (['fr', 'en'] as $locale) {
                    $label = $this->normalize((string) $benefit->getTranslation('name', $locale, false));

                    foreach ($terms as $term) {
                        $termNorm = $this->normalize($term);
                        if ($termNorm === '') {
                            continue;
                        }

                        if (
                            str_contains($label, $termNorm)
                            || str_contains($termNorm, $label)
                            || str_contains($slug, $termNorm)
                            || str_contains($termNorm, $slug)
                        ) {
                            return (int) $benefit->id;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  array{benefits: list<string>, skills: list<string>, languages: list<string>}  $unmatched
     * @return list<array{skill_id: int, level: string|null}>
     */
    private function resolveSkillRequirements(array $draft, array &$unmatched): array
    {
        $hints = is_array($draft['inferred_skills'] ?? null) ? $draft['inferred_skills'] : [];
        $resolved = [];

        foreach ($hints as $hint) {
            if (! is_string($hint) || trim($hint) === '') {
                continue;
            }

            $id = $this->skillResolver->resolveId($hint);
            if ($id !== null) {
                $resolved[] = ['skill_id' => $id, 'level' => null];

                continue;
            }

            $unmatched['skills'][] = $hint;
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  array{benefits: list<string>, skills: list<string>, languages: list<string>}  $unmatched
     * @return list<array{language_id: int, language_level_id: int|null, required_level: string|null}>
     */
    private function resolveLanguageRequirements(array $draft, array &$unmatched): array
    {
        $rows = is_array($draft['language_requirements'] ?? null) ? $draft['language_requirements'] : [];

        if ($rows === []) {
            $rows = $this->detectLanguageRequirementsFromText(
                $this->localizedTextBlob($draft, 'requirements'),
            );
        }

        $resolved = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $languageHint = trim((string) ($row['language_hint'] ?? ''));
            if ($languageHint === '') {
                continue;
            }

            $languageId = $this->languageResolver->resolveId($languageHint);
            if ($languageId === null) {
                $unmatched['languages'][] = $languageHint;

                continue;
            }

            $levelHint = trim((string) ($row['level_hint'] ?? ''));
            $resolved[] = [
                'language_id' => $languageId,
                'language_level_id' => $this->languageResolver->resolveLevelId($levelHint !== '' ? $levelHint : null),
                'required_level' => $levelHint !== '' ? $levelHint : null,
            ];
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function resolveCountryId(array $draft): ?int
    {
        $hint = trim((string) ($draft['country_hint'] ?? ''));
        if ($hint === '') {
            $cityHint = trim((string) ($draft['city_hint'] ?? ''));
            $inferred = $this->countryResolver->resolveNameFromCity($cityHint);
            if ($inferred !== null) {
                return $this->countryResolver->resolveId($inferred);
            }

            return null;
        }

        return $this->countryResolver->resolveId($hint);
    }

    private function resolveCityId(string $cityHint, ?int $countryId): ?int
    {
        $query = City::query()->where(function ($inner) use ($cityHint): void {
            $inner->where('name->fr', 'like', '%'.$cityHint.'%')
                ->orWhere('name->en', 'like', '%'.$cityHint.'%');
        });

        if ($countryId !== null) {
            $query->where('country_id', $countryId);
        }

        $id = $query->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function resolveContractTypeId(mixed $hint): ?int
    {
        $text = strtolower(trim((string) $hint));
        if ($text === '') {
            return null;
        }

        if (preg_match('/\b(\d+)\s*(an|ans|year|years|mois|month)\b/u', $text) === 1
            || str_contains($text, 'cdd')
            || str_contains($text, 'fixed')
            || str_contains($text, 'determin')
            || str_contains($text, 'temporary')) {
            return $this->contractTypeIdBySlug('fixed-term') ?? $this->contractTypeIdBySlug('cdd');
        }

        if (str_contains($text, 'cdi') || str_contains($text, 'full') || str_contains($text, 'permanent')) {
            return $this->contractTypeIdBySlug('full-time');
        }

        if (str_contains($text, 'stage') || str_contains($text, 'intern')) {
            return $this->contractTypeIdBySlug('internship');
        }

        if (str_contains($text, 'freelance')) {
            return $this->contractTypeIdBySlug('freelance');
        }

        return null;
    }

    private function contractTypeIdBySlug(string $slug): ?int
    {
        $id = ContractType::query()->where('slug', $slug)->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function resolveWorkScheduleId(mixed $hint): ?int
    {
        $text = strtolower(trim((string) $hint));
        if ($text === '') {
            return null;
        }

        if (str_contains($text, 'night') || str_contains($text, 'nuit')) {
            return $this->workScheduleIdBySlug('night');
        }

        if (str_contains($text, 'rotat') || preg_match('/\b1[02]\s*h/u', $text) === 1) {
            return $this->workScheduleIdBySlug('rotating');
        }

        if (str_contains($text, 'flex')) {
            return $this->workScheduleIdBySlug('flexible');
        }

        return $this->workScheduleIdBySlug('day');
    }

    private function workScheduleIdBySlug(string $slug): ?int
    {
        $id = WorkSchedule::query()->where('slug', $slug)->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function resolveEducationLevelId(mixed $hint): ?int
    {
        $text = trim((string) $hint);
        if ($text === '') {
            return null;
        }

        $normalized = $this->normalize($text);
        $levels = EducationLevel::query()->get(['id', 'name']);

        foreach ($levels as $level) {
            foreach (['fr', 'en'] as $locale) {
                $label = $this->normalize((string) $level->getTranslation('name', $locale, false));
                if ($label !== '' && (str_contains($label, $normalized) || str_contains($normalized, $label))) {
                    return (int) $level->id;
                }
            }
        }

        return null;
    }

    private function normalize(string $value): string
    {
        $value = Str::ascii(mb_strtolower(trim($value)));

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function finalizeLocalizedDraft(array $draft): array
    {
        foreach (['description', 'responsibilities', 'requirements', 'specific_documents'] as $key) {
            if (! isset($draft[$key]) || ! is_array($draft[$key])) {
                continue;
            }

            foreach (['fr', 'en'] as $locale) {
                $value = (string) ($draft[$key][$locale] ?? '');
                $limit = self::LOCALIZED_LIMITS[$key] ?? 1800;
                if (mb_strlen($value) > $limit) {
                    $draft[$key][$locale] = mb_substr($value, 0, $limit);
                }
            }
        }

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function localizedTextBlob(array $draft, string $key): string
    {
        $block = $draft[$key] ?? null;
        if (! is_array($block)) {
            return '';
        }

        return trim(((string) ($block['fr'] ?? '')).' '.((string) ($block['en'] ?? '')));
    }
}
