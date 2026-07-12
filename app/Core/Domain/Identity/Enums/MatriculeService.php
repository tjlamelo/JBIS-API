<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Enums;

enum MatriculeService: string
{
    case PlacementInternational = 'placement_international';
    case PlacementNational = 'placement_national';
    case CertificationAmericaine = 'certification_americaine';
    case Consultations = 'consultations';
    case StageAcademique = 'stage_academique';
    case StageProfessionnel = 'stage_professionnel';
    case VisaEtudiant = 'visa_etudiant';
    case Partenaire = 'partenaire';
    case Permanent = 'permanent';
    case Candidat = 'candidat';
    case Staff = 'staff';
    case Recruteur = 'recruteur';

    public function code(): string
    {
        return match ($this) {
            self::PlacementInternational => 'INT',
            self::PlacementNational => 'NAT',
            self::CertificationAmericaine => 'CERT',
            self::Consultations => 'CONS',
            self::StageAcademique => 'STAC',
            self::StageProfessionnel => 'STPR',
            self::VisaEtudiant => 'VISE',
            self::Partenaire => 'PART',
            self::Permanent => 'PERM',
            self::Candidat => 'CAND',
            self::Staff => 'STAF',
            self::Recruteur => 'RECR',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PlacementInternational => 'Placement international',
            self::PlacementNational => 'Placement national',
            self::CertificationAmericaine => 'Certification américaine',
            self::Consultations => 'Consultations',
            self::StageAcademique => 'Stage académique',
            self::StageProfessionnel => 'Stage professionnel',
            self::VisaEtudiant => 'Visa étudiant',
            self::Partenaire => 'Partenaire',
            self::Permanent => 'Permanent',
            self::Candidat => 'Candidat',
            self::Staff => 'Staff',
            self::Recruteur => 'Recruteur',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * @return list<array{value: string, label: string, code: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
                'code' => $case->code(),
            ],
            self::cases(),
        );
    }
}
