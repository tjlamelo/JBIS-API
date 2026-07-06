<?php

declare(strict_types=1);

namespace Tests\Unit\Identity\Support;

use App\Core\Domain\Identity\Support\OrganizationNameDisambiguator;
use Tests\TestCase;

final class OrganizationNameDisambiguatorTest extends TestCase
{
    public function test_swaps_inverted_job_title_and_company(): void
    {
        $disambiguator = new OrganizationNameDisambiguator;

        $row = $disambiguator->disambiguateExperience([
            'job_title' => 'Université de Yaoundé I',
            'company_name' => 'Stagiaire développeur',
        ]);

        self::assertSame('Stagiaire développeur', $row['job_title']);
        self::assertSame('Université de Yaoundé I', $row['company_name']);
    }

    public function test_moves_degree_into_institution_when_misplaced(): void
    {
        $disambiguator = new OrganizationNameDisambiguator;

        $row = $disambiguator->disambiguateEducation([
            'degree' => 'École Nationale Supérieure Polytechnique',
            'institution_name' => 'Master Génie Logiciel',
        ]);

        self::assertSame('Master Génie Logiciel', $row['degree']);
        self::assertSame('École Nationale Supérieure Polytechnique', $row['institution_name']);
    }
}
