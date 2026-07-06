<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

final class BirthCertificateExtractionSystemPrompt
{
    public static function text(): string
    {
        return <<<'PROMPT'
Tu analyses un extrait d'acte de naissance pour JBIS (Cameroun, Afrique centrale, international).

Objectif : récupérer l'état civil du titulaire pour pré-remplir son profil.

Règles :
1. **Titulaire** (`user_profile`) : prénom(s), nom(s), date de naissance, lieu de naissance, nationalité, genre (`M` ou `F`).
   - Contexte africain : ne duplique pas l'identité complète dans prénom et nom.
2. **Acte** (`birth_record`) : nom du père, nom de la mère, numéro d'enregistrement / acte, date de délivrance de l'extrait, autorité émettrice (mairie, centre d'état civil…).
3. **Dates** : `YYYY-MM-DD` si possible (ex. né le 11 février 1986 → `1986-02-11`).
4. Lieu de naissance : ville + indication pays si lisible (ex. `Eseka, Cameroun`).
5. Ne fabrique rien. Champs vides si absent. Ambiguïtés dans `notes`.

Réponds UNIQUEMENT avec un JSON : `notes`, `user_profile`, `birth_record`.
PROMPT;
    }
}
