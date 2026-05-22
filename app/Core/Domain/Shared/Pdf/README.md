# Module PDF (iLovePDF)

Abstraction interne autour du SDK [`ilovepdf/ilovepdf-php`](https://github.com/ilovepdf/ilovepdf-php) pour exposer un **contrat stable** côté domaine sans coupler le reste du code à iLovePDF.

```
Shared/Pdf/
├── Contracts/PdfProcessorInterface.php   ← contrat bas-niveau (paths filesystem)
├── DTOs/
│   ├── PdfTaskResult.php                 ← résultat brut d'une tâche
│   └── PublishedPdfResult.php            ← résultat publié sur un disque Laravel
├── Enums/
│   ├── PdfTool.php
│   └── CompressLevel.php
├── Exceptions/
│   ├── PdfException.php                  ← racine
│   ├── PdfConfigurationException.php
│   └── PdfProcessingException.php
├── Sources/                              ← abstraction des sources de fichiers
│   ├── PdfSource.php                     ← interface (materialize/cleanup)
│   ├── StorageDiskSource.php             ← générique : tout disque Laravel
│   ├── ArchiveSource.php                 ← wrapper sémantique sur Archive
│   └── UserDocumentSource.php            ← wrapper sémantique sur UserDocument
├── Support/
│   └── PdfSourceMaterializer.php         ← matérialise N sources + cleanup auto
├── Providers/PdfServiceProvider.php
└── Services/
    ├── IlovepdfProcessor.php             ← implémentation bas-niveau
    └── PdfDocumentService.php            ← façade haut-niveau (process + publish)
```

## Configuration

`config/ilovepdf.php` lit ces variables :

```env
ILOVEPDF_PUBLIC_KEY=project_public_xxx
ILOVEPDF_SECRET_KEY=secret_key_xxx
ILOVEPDF_TIMEOUT=60
ILOVEPDF_COMPRESSION_LEVEL=recommended
```

Si `ILOVEPDF_PUBLIC_KEY` ou `ILOVEPDF_SECRET_KEY` est vide au moment de l'appel, le service lève une `PdfConfigurationException` (jamais au boot).

## Usage

Inject the contract anywhere via DI :

```php
use App\Core\Domain\Shared\Pdf\Contracts\PdfProcessorInterface;
use App\Core\Domain\Shared\Pdf\Enums\CompressLevel;

public function __construct(private readonly PdfProcessorInterface $pdf) {}

public function example(): void
{
    $result = $this->pdf->compress(
        files: [storage_path('app/source.pdf')],
        outputDir: storage_path('app/output'),
        level: CompressLevel::Extreme,
        outputFilename: 'invoice-compressed',
    );

    // $result->path     : /abs/path/invoice-compressed.pdf
    // $result->mimeType : application/pdf
    // $result->size     : 12345
}
```

## Méthodes disponibles

| Méthode         | Entrée                       | Outil iLovePDF |
| --------------- | ---------------------------- | -------------- |
| `compress`      | `string[]`                   | `compress`     |
| `merge`         | `string[]` (≥ 2)             | `merge`        |
| `split`         | `string` + ranges (`"1-3,5"`)| `split`        |
| `protect`       | `string` + password          | `protect`      |
| `unlock`        | `string` + password          | `unlock`       |
| `watermark`     | `string[]` + text            | `watermark`    |
| `imagesToPdf`   | `string[]` (images)          | `imagepdf`     |
| `pdfToImages`   | `string` (pdf)               | `pdfjpg`       |
| `officeToPdf`   | `string` (docx/xlsx/pptx)    | `officepdf`    |
| `ocr`           | `string[]` (pdf)             | `pdfocr`       |

Chaque méthode retourne un `PdfTaskResult` qui contient :

- `tool`        : identifiant de l'outil utilisé
- `taskId`      : id iLovePDF de la tâche (debug / logs)
- `path`        : chemin absolu du fichier de sortie (pdf ou zip)
- `filename`    : nom de fichier basename
- `mimeType`    : `application/pdf` ou `application/zip`
- `size`        : taille en octets
- `isArchive`   : `true` si la sortie est un ZIP (cas multi-fichiers)

## Gestion des erreurs

Toutes les erreurs SDK sont enveloppées :

```php
try {
    $this->pdf->merge($files, $outputDir);
} catch (PdfConfigurationException $e) {
    // ILOVEPDF_PUBLIC_KEY / ILOVEPDF_SECRET_KEY manquants
} catch (PdfProcessingException $e) {
    // fichier introuvable, échec d'upload / exécution / download
} catch (PdfException $e) {
    // racine commune
}
```

## Étendre

Pour brancher un autre fournisseur (e.g. PDFTron, Adobe Document Cloud) :

1. Créer un service implémentant `PdfProcessorInterface`.
2. Le binder dans un service provider à la place de `IlovepdfProcessor`.

Aucune autre partie du code n'a à être modifiée.

---

## Niveau 2 : documents de la plateforme (`PdfDocumentService`)

Pour traiter les **documents utilisateurs** (`UserDocument`) et **archives entreprise** (`Archive`), n'utilise pas `PdfProcessorInterface` directement : utilise `PdfDocumentService`. Il s'occupe de :

1. Matérialiser la source (path filesystem local, en zéro-copie quand le disque est local — ce qui est le cas de `jbis_assets`).
2. Lancer le traitement iLovePDF dans un dossier tmp isolé.
3. **Publier** le résultat sur `jbis_assets` sous `documents/processed/YYYY/MM/...`.
4. Nettoyer tous les fichiers temporaires (try/finally garanti, même en cas d'exception).

> Cloudinary n'intervient **jamais** dans ce pipeline. La source de vérité I/O est `jbis_assets`. Cloudinary reste un CDN d'affichage géré ailleurs.

### Sources disponibles

| Source                       | Usage                                                      |
| ---------------------------- | ---------------------------------------------------------- |
| `StorageDiskSource::on(...)` | `(disk, relativePath)` — générique                         |
| `ArchiveSource::of($a)`      | document d'archive entreprise (`Archive.disk + stored_name`) |
| `UserDocumentSource::of($doc)` | pièce d'identité / diplôme utilisateur (`UserDocument.file_path` sur `jbis_assets/Identity/`) |

Toutes implémentent le même contrat `PdfSource` → tu peux ajouter ta propre source en 30 lignes.

### Configuration

```php
// config/ilovepdf.php
'documents' => [
    'disk'   => env('ILOVEPDF_DOCUMENTS_DISK', 'jbis_assets'),
    'folder' => env('ILOVEPDF_DOCUMENTS_FOLDER', 'documents/processed'),
],
```

### Exemples

```php
use App\Core\Domain\Shared\Pdf\Services\PdfDocumentService;
use App\Core\Domain\Shared\Pdf\Sources\ArchiveSource;
use App\Core\Domain\Shared\Pdf\Sources\UserDocumentSource;
use App\Core\Domain\Shared\Pdf\Enums\CompressLevel;

public function __construct(private readonly PdfDocumentService $pdfDocs) {}

// 1. Compresser une archive entreprise
$result = $this->pdfDocs->compress(
    source: ArchiveSource::of($archive),
    level:  CompressLevel::Extreme,
);
// $result->relativePath → "documents/processed/2026/05/devis-compressed-aXb3c2.pdf"
// $result->publicUrl    → "https://assets.jbis.cm/documents/processed/2026/05/..."
// $result->task->size   → taille après compression

// 2. Compresser une pièce d'identité utilisateur
$compressed = $this->pdfDocs->compress(
    source: UserDocumentSource::of($userDocument),
    level:  CompressLevel::Recommended,
);

// 3. Protéger un PDF par mot de passe
$secured = $this->pdfDocs->protect(
    source: ArchiveSource::of($archive),
    password: 'super-secret',
    folder: 'documents/secured',
);

// 4. Attacher le résultat à un modèle (au choix de l'appelant — on n'écrit
//    rien automatiquement, c'est volontaire pour rester modulaire)
$archive->update([
    'stored_name' => $secured->relativePath,
    'disk'        => $secured->disk,
    'size'        => $secured->task->size,
    'mime_type'   => $secured->task->mimeType,
]);
```

### Méthodes haut-niveau

`compress`, `merge`, `protect`, `unlock`, `watermark`, `split`, `officeToPdf`, `pdfToImages`, `ocr` — mêmes paramètres que `PdfProcessorInterface` mais avec :

- `PdfSource` (ou `list<PdfSource>` pour `merge`) en entrée à la place d'un path
- `?string $folder` / `?string $filename` / `?string $disk` optionnels pour surcharger la cible de publication

### Garanties

- ✅ Pas de fichier orphelin : tmp toujours nettoyé (try/finally)
- ✅ Pas de couplage à Cloudinary
- ✅ Sources extensibles via `PdfSource` (interface 3 méthodes)
- ✅ Compatible avec un disque local OU distant (s3/ftp) — détection automatique
- ✅ Le model d'origine n'est **pas** modifié implicitement : c'est au caller d'updater `Archive`/`UserDocument` avec le `PublishedPdfResult`
