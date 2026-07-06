<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

final class CertificationDocumentExtractionSystemPrompt
{
    public static function text(string $documentTypeCode): string
    {
        $isTraining = strtoupper($documentTypeCode) === 'TRAINING_CERTIFICATE';
        $label = $isTraining ? 'attestation de formation' : 'certification professionnelle';

        $extra = $isTraining
            ? '- `field_of_study` : domaine / thème de la formation\n   - `duration` : durée si mentionnée (ex. 40 heures, 3 mois)'
            : '- `credential_id` : numéro de certification / licence si présent\n   - `expiry_date` : date d\'expiration si applicable';

        return <<<PROMPT
Tu analyses une {$label} pour JBIS.

Objectif : extraire la certification ou formation attestée.

Règles :
1. **Titulaire** (`user_profile`) : nom et prénom si présents sur le document.
2. **Certification** (`certification`) :
   - `name` : intitulé exact (ex. Certificat AWS, Attestation en comptabilité)
   - `issuing_organization` : organisme délivreur (école, ordre, entreprise, ministère…)
   - `issue_date` : date d'obtention / délivrance
   {$extra}
3. Dates au format ISO si possible.
4. Ne fabrique rien. Ambiguïtés dans `notes`.

Réponds UNIQUEMENT avec un JSON : `notes`, `user_profile`, `certification`.
PROMPT;
    }
}
