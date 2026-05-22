<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Identity\Actions\Legal\PublishLegalDocumentAction;
use App\Core\Domain\Identity\Support\ConsentType;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $publisher = app(PublishLegalDocumentAction::class);

        $publisher->execute(
            type: ConsentType::TERMS,
            version: '2026-01',
            title: [
                'fr' => 'Conditions générales d\'utilisation',
                'en' => 'Terms of Service',
            ],
            content: [
                'fr' => '<p>Conditions générales JBIS — version 2026-01. En utilisant la plateforme, vous acceptez ces conditions.</p>',
                'en' => '<p>JBIS Terms of Service — version 2026-01.</p>',
            ],
            summary: 'CGU JBIS v2026-01',
            requiresReacceptance: true,
        );

        $publisher->execute(
            type: ConsentType::PRIVACY,
            version: '2026-01',
            title: [
                'fr' => 'Politique de confidentialité',
                'en' => 'Privacy Policy',
            ],
            content: [
                'fr' => '<p>Politique de confidentialité JBIS — version 2026-01.</p>',
                'en' => '<p>JBIS Privacy Policy — version 2026-01.</p>',
            ],
            summary: 'Confidentialité JBIS v2026-01',
            requiresReacceptance: true,
        );

        $publisher->execute(
            type: ConsentType::COOKIES,
            version: '2026-01',
            title: [
                'fr' => 'Politique des cookies',
                'en' => 'Cookie Policy',
            ],
            content: [
                'fr' => '<p>Gestion des cookies et traceurs — version 2026-01.</p>',
                'en' => '<p>Cookie policy — version 2026-01.</p>',
            ],
            summary: 'Cookies JBIS v2026-01',
            requiresReacceptance: false,
        );
    }
}
