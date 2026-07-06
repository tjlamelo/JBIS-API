<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

final class DiplomaDocumentExtractionSystemPrompt
{
    public static function text(): string
    {
        return <<<'PROMPT'
Tu analyses un diplôme ou relevé de notes pour JBIS.

Objectif : extraire la formation attestée pour pré-remplir le parcours académique.

Règles :
1. **Titulaire** (`user_profile`) : nom et prénom du diplômé si présents.
2. **Formation** (`education`) :
   - `degree` : intitulé du diplôme (Licence, Baccalauréat, Certificat…)
   - `institution_name` : établissement délivreur (université, collège, centre de formation…)
   - `field_of_study` : filière / spécialité si distincte du diplôme
   - `start_date`, `end_date` : période de cursus
   - `grade` : mention, moyenne ou résultat si indiqué
   - `country_name`, `city_name` : localisation de l'établissement
3. Distingue clairement diplôme et établissement (ne les inverse pas).
4. Ne fabrique rien. Ambiguïtés dans `notes`.

Réponds UNIQUEMENT avec un JSON : `notes`, `user_profile`, `education`.
PROMPT;
    }
}
