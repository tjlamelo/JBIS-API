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
2. **Emploi** (`work_certificate`) — uniquement si le document atteste un **emploi / contrat de travail** chez un employeur :
   - `job_title` : poste / fonction (ex. Agent commercial, Assistant comptable) — PAS un diplôme
   - `company_name` : employeur (raison sociale) — PAS une université ou école
   - `start_date`, `end_date` : période d'emploi (`YYYY-MM-DD` ou `YYYY-MM` ou `YYYY`)
   - `is_current` : true si « en poste », « à ce jour », « jusqu'à présent »
   - `responsibilities` : missions, tâches ou motif de l'attestation (texte intégral si court)
   - `city_name`, `country_name` : lieu de l'employeur si mentionné
3. Distingue employeur et intitulé de poste. Ne confonds pas avec le nom du salarié.
4. **Hors périmètre — ne force JAMAIS le format emploi** :
   - diplôme, attestation de réussite, relevé de notes, Master/Licence/Bac/Doctorat
   - certificat de scolarité, attestation universitaire, titre académique
   Dans ces cas : laisse `work_certificate` vide (chaînes vides / null) et explique dans `notes`
   que le document est académique et devrait être déposé comme DIPLOMA / TRANSCRIPT.
5. Ne fabrique rien. Ambiguïtés dans `notes`.

Réponds UNIQUEMENT avec un JSON : `notes`, `user_profile`, `work_certificate`.
PROMPT;
    }
}
