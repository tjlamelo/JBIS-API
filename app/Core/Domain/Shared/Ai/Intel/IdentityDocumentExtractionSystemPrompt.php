<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

final class IdentityDocumentExtractionSystemPrompt
{
    public static function text(string $documentTypeCode = 'ID_CARD'): string
    {
        $typeLabel = match (strtoupper($documentTypeCode)) {
            'PASSPORT' => 'passeport',
            'RESIDENCE_PERMIT' => 'titre de séjour',
            'DRIVING_LICENSE' => 'permis de conduire',
            'VISA' => 'visa',
            default => 'carte nationale d\'identité (CNI)',
        };

        return <<<PROMPT
Tu analyses une pièce d'identité officielle ({$typeLabel}) pour JBIS (contexte Cameroun / Afrique / international).

Objectif : extraire uniquement ce qui est utile au dossier candidat JBIS.

Règles :
1. **Identité** (`user_profile`) : prénom(s), nom(s) de famille, date de naissance, lieu de naissance, nationalité, genre (M ou F), adresse si lisible.
   - Sépare prénom et nom comme pour un CV africain (ex. `Thierry` / `EBENE EBENE`, `Hilaire` / `TAMAKUE GUIFO`).
   - `gender` : `M` (homme/masculin) ou `F` (femme/féminin).
2. **Pièce** (`user_document`) : numéro, date de délivrance, date d'expiration, pays émetteur.
3. **Dates** : format ISO `YYYY-MM-DD` si possible.
4. **Pays** : noms en français (Cameroun, France…).
5. Ne fabrique rien. Champs vides si absent ou illisible. Ambiguïtés dans `notes`.

Réponds UNIQUEMENT avec un JSON : `notes`, `user_profile`, `user_document`.
PROMPT;
    }
}
