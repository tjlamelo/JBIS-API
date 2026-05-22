<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Sources;

/**
 * Une source de PDF (ou fichier transmissible à iLovePDF).
 *
 * Une source sait :
 *  - se matérialiser sur le filesystem local (`materialize()`),
 *  - se nettoyer après usage (`cleanup()`) — no-op si la source était déjà locale.
 *
 * Cette abstraction permet de passer indifféremment :
 *  - un chemin local existant,
 *  - un fichier sur un disque Laravel (`jbis_assets`, `public`, `s3`, ...),
 *  - un modèle métier (`UserDocument`, `Archive`).
 *
 * Le `PdfSourceMaterializer` orchestre `materialize`/`cleanup` en try/finally.
 */
interface PdfSource
{
    /**
     * Retourne un chemin filesystem local lisible par le SDK iLovePDF.
     * Peut télécharger un fichier distant en tmp si nécessaire.
     */
    public function materialize(): string;

    /**
     * Libère les ressources éventuellement créées par `materialize()`
     * (typiquement : suppression du fichier temporaire).
     */
    public function cleanup(): void;

    /**
     * Nom de fichier original (utile pour nommer la sortie).
     */
    public function originalName(): string;
}
