<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

final class WorkCertificateExtractionSystemPrompt
{
    public static function text(): string
    {
        return <<<'PROMPT'
Tu analyses une attestation / certificat de travail pour JBIS.

Objectif : extraire l'expérience professionnelle attestée pour pré-remplir le dossier candidat.

Règles :
1. **Salarié** (`user_profile`) : nom et prénom si mentionnés sur l'attestation (optionnel).
2. **Emploi** (`work_certificate`) :
   - `job_title` : poste / fonction (ex. Agent commercial, Assistant comptable)
   - `company_name` : employeur (raison sociale complète)
   - `start_date`, `end_date` : période d'emploi (`YYYY-MM-DD` ou `YYYY-MM` ou `YYYY`)
   - `is_current` : true si « en poste », « à ce jour », « jusqu'à présent »
   - `responsibilities` : missions, tâches ou motif de l'attestation (texte intégral si court)
   - `city_name`, `country_name` : lieu de l'employeur si mentionné
3. Distingue employeur et intitulé de poste. Ne confonds pas avec le nom du salarié.
4. Ne fabrique rien. Ambiguïtés dans `notes`.

Réponds UNIQUEMENT avec un JSON : `notes`, `user_profile`, `work_certificate`.
PROMPT;
    }
}
