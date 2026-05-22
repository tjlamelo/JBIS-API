<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Document;

use App\Core\Domain\Identity\Exceptions\Document\DocumentStorageException;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Support\Document\DocumentDownloadNameBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

final class DownloadUserDocumentsZipAction
{
    public function __construct(
        private readonly DocumentDownloadNameBuilder $downloadNames,
    ) {}

    /**
     * @param  Collection<int, UserDocument>  $documents
     */
    public function execute(Collection $documents): BinaryFileResponse
    {
        if ($documents->isEmpty()) {
            throw new DocumentStorageException(__('Aucun document à télécharger.'));
        }

        $userIds = $documents->pluck('user_id')->unique()->values();
        if ($userIds->count() > 1) {
            throw new DocumentStorageException(__('Les documents doivent appartenir au même utilisateur.'));
        }

        $documents->loadMissing(['user.profile', 'documentType']);
        /** @var User $owner */
        $owner = $documents->first()->user ?? User::query()->with('profile')->findOrFail($userIds->first());

        $disk = Storage::disk(UserDocument::STORAGE_DISK);
        $tempZip = tempnam(sys_get_temp_dir(), 'jbis_docs_');

        if ($tempZip === false) {
            throw new DocumentStorageException(__('Impossible de créer l\'archive ZIP.'));
        }

        $zipPath = $tempZip.'.zip';
        @unlink($tempZip);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new DocumentStorageException(__('Impossible de créer l\'archive ZIP.'));
        }

        $usedNames = [];
        $indexByType = [];

        foreach ($documents as $document) {
            $path = (string) $document->file_path;

            if ($path === '' || ! $disk->exists($path)) {
                $zip->close();
                @unlink($zipPath);

                throw new DocumentStorageException(__('Fichier document introuvable.'));
            }

            $typeCode = $document->documentType?->code ?? 'OTHER';

            $indexByType[$typeCode] = ($indexByType[$typeCode] ?? 0) + 1;
            $entryName = $this->downloadNames->forDocumentInZip($document, $indexByType[$typeCode]);
            $entryName = $this->ensureUniqueZipEntry($entryName, $usedNames);
            $usedNames[] = $entryName;

            $absolutePath = $disk->path($path);
            $zip->addFile($absolutePath, $entryName);
        }

        $zip->close();

        $archiveName = $this->downloadNames->forZipArchive($owner, $documents->count());

        return response()
            ->download($zipPath, $archiveName, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /**
     * @param  list<string>  $usedNames
     */
    private function ensureUniqueZipEntry(string $name, array $usedNames): string
    {
        if (! in_array($name, $usedNames, true)) {
            return $name;
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);
        $counter = 2;

        do {
            $candidate = $extension !== '' ? "{$base}_{$counter}.{$extension}" : "{$base}_{$counter}";
            $counter++;
        } while (in_array($candidate, $usedNames, true));

        return $candidate;
    }
}
