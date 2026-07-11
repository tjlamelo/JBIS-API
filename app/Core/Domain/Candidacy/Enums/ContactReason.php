<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Enums;

enum ContactReason: string
{
    case General = 'GENERAL';
    case Recruitment = 'RECRUITMENT';
    case Training = 'TRAINING';
    case Internship = 'INTERNSHIP';
    case Appointment = 'APPOINTMENT';
    case Partnership = 'PARTNERSHIP';
    case Other = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::General => 'Question générale',
            self::Recruitment => 'Recrutement / emploi à l\'étranger',
            self::Training => 'Formation ou certification AMCA',
            self::Internship => 'Stage ou alternance',
            self::Appointment => 'Demande de rendez-vous',
            self::Partnership => 'Partenariat entreprise / école',
            self::Other => 'Autre demande',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
