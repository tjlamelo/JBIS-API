<?php
namespace App\Core\Domain\Shared\Media\Actions;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadMediaAction
{
    public function execute(string $localPath, ?string $displayName = null): StreamedResponse
    {
        $disk = Storage::disk('jbis_assets');

        // 1. Vérification d'existence
        if (!$disk->exists($localPath)) {
            // Dans JBIS, on pourrait logguer ici pour savoir si le miroir est cassé
            throw new \Exception("Le fichier est introuvable sur le miroir local.");
        }

        // 2. Utilisation de la façade pour générer le téléchargement
        // Cela résout l'erreur de méthode inconnue
        return $disk->download($localPath, $displayName);
    }
}