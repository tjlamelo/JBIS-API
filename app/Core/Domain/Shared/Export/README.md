# Module Export

Module **abstrait, modulaire et réutilisable** d'export de données pour l'API JBIS.

> Objectif : permettre d'exporter en Excel / CSV / PDF n'importe quel agrégat
> de données (utilisateurs, candidatures, offres, programmes, documents…),
> avec une sélection **fine et libre** des colonnes, y compris des champs
> issus de relations.

---

## Vue d'ensemble

```
Contracts/   → ExportSourceInterface, ExportDriverInterface, ResolvedSheet
DTOs/        → Definition / Sheet / Field / FieldSchema / SourceSchema / Result
Enums/       → ExportFormat (xlsx|csv|pdf), ExportFieldType
Exceptions/  → Hiérarchie d'erreurs métier
Sources/     → AbstractEloquentExportSource + sources concrètes
Drivers/     → CsvExportDriver, XlsxExportDriver, PdfExportDriver
Registry/    → ExportSourceRegistry, ExportDriverRegistry
Services/    → ExportService (orchestration), ExportSchemaService (UI)
Support/     → FieldPathResolver, ValueFormatter, FilenameBuilder
Providers/   → ExportServiceProvider (DI + vues)
Templates/   → Templates Blade PDF (namespace "exports::")
```

## Endpoints HTTP

| Méthode | URL                       | Rôle |
|---------|---------------------------|------|
| GET     | `/api/v1/exports/schema`  | Liste les sources, leurs champs et les formats disponibles |
| POST    | `/api/v1/exports`         | Exécute un export et renvoie le fichier en téléchargement |

## Exemple de requête

```jsonc
POST /api/v1/exports
{
  "format": "xlsx",
  "file_name": "candidats-actifs",
  "meta": { "title": "Candidats actifs", "subtitle": "Période : Mai 2026" },
  "sheets": [
    {
      "name": "Utilisateurs",
      "source": "users",
      "filters": { "active": true, "created_after": "2025-01-01" },
      "with": ["documents"],
      "fields": [
        { "key": "email" },
        { "key": "profile.first_name", "label": "Prénom" },
        { "key": "profile.last_name",  "label": "Nom" },
        { "key": "documents_count" },
        { "key": "applications_count" }
      ]
    },
    {
      "name": "Candidatures",
      "source": "applications",
      "filters": { "status": ["PENDING", "IN_PROGRESS"] },
      "fields": [
        { "key": "application_number" },
        { "key": "status" },
        { "key": "user.email" },
        { "key": "offer.title" },
        { "key": "program.name" },
        { "key": "created_at" }
      ]
    }
  ]
}
```

- `format` = `xlsx` | `csv` | `pdf`
- `sheets[*].source` = clé déclarée par une `ExportSourceInterface`
- `sheets[*].fields[*].key` = clé issue de `GET /exports/schema` *ou* dot-path libre
  (auquel cas `path` est obligatoire — ex. champ calculé non catalogué)
- `chunk_size` (par feuille) contrôle le streaming Eloquent (10–5000)

## Ajouter une nouvelle source

```php
namespace App\Core\Domain\Shared\Export\Sources;

use App\Core\Domain\Shared\Export\Enums\ExportFieldType;

final class AgencyExportSource extends AbstractEloquentExportSource
{
    public function key(): string { return 'agencies'; }
    public function label(): string { return 'Agences'; }
    public function modelClass(): string { return \App\Core\Domain\Catalog\Models\Agency::class; }

    protected function fields(): array
    {
        return [
            $this->field('id', 'ID', type: ExportFieldType::Integer),
            $this->field('name', 'Nom'),
            // …
        ];
    }
}
```

Puis enregistrer dans `ExportServiceProvider::register()` :

```php
$registry->register(new AgencyExportSource());
```

## Ajouter un nouveau format

Implémentez `ExportDriverInterface` et enregistrez-le dans le provider.
Tout le pipeline (résolution des sources, paths, formats de champs) est
réutilisé : votre driver ne s'occupe que de **l'écriture du fichier**.

## Dépendances optionnelles

| Format | Paquet | Activation |
|--------|--------|------------|
| CSV   | — (toujours actif) | — |
| XLSX  | `phpoffice/phpspreadsheet` | `composer require phpoffice/phpspreadsheet` |
| PDF   | `barryvdh/laravel-dompdf`  | `composer require barryvdh/laravel-dompdf` |

Si un paquet est manquant, le driver correspondant **n'est pas enregistré** :
le format est exclu de `/exports/schema` *et* refusé par la validation,
au lieu de lever une erreur 500 silencieuse.

## Templates PDF — 3 modes

| Mode | Clé `meta` | Quand l'utiliser |
|------|-----------|------------------|
| **Front-driven** | `template_html` (string HTML) | Le front possède la maquette (logo, polices, signatures). Le module n'injecte que les données via `{{ ... }}`. |
| **Blade nommé**  | `template` (ex. `"exports::pdf.default"` ou `"exports.pdf.candidat"`) | Template versionné côté API. |
| **Par défaut**   | aucune des deux | Utilise le template intégré `exports::pdf.default`. |

### Syntaxe `template_html` (envoyé par le front)

```html
<h1>{{ title }}</h1>
<p>Généré le {{ generated_at }} — {{ sheet_count }} feuille(s)</p>

{{ sheets }}                  <!-- toutes les feuilles, en tables stylées -->
{{ sheet:Utilisateurs }}      <!-- une feuille précise par son nom -->
{{ row_count:Utilisateurs }}  <!-- nombre de lignes d'une feuille -->
{{ sheet_data:Utilisateurs }} <!-- JSON brut des lignes (échappé) -->

{{ meta.client.name }}        <!-- dot-path libre dans `meta` -->
{{ app_name }} {{ subtitle }} <!-- raccourcis -->
```

Toutes les valeurs scalaires sont passées par `htmlspecialchars` (échappement HTML strict). Aucune exécution PHP/Blade n'est faite sur le template du front.

## Localisation (champs `translatable`)

La locale active est résolue par le middleware `SetApiLocale` dans l'ordre :
`X-Locale` → `?lang=` → header `Accept-Language` du navigateur.

Les champs typés `translatable` (ex. `offer.title`, `program.name`, slugs Spatie) sont automatiquement rendus dans cette locale. Pour forcer une locale différente pour un champ donné, ajoutez `"locale": "fr"` (ou `"en"`) dans la définition du champ.

## Pourquoi le dossier `Sources/` ?

Une **Source** = un agrégat exportable (un modèle racine Eloquent + son catalogue de champs + ses filtres).

C'est l'**unique** endroit où :
1. on déclare la clé publique (`"users"`, `"offers"`, …) qu'utilisera le client,
2. on liste **tous** les champs exportables (avec leur label, type, `requiresWith`),
3. on encapsule les filtres autorisés (`status`, `program_id`, `created_after`, etc.).

Ce découpage rend le module ouvert à l'extension, fermé à la modification : pour exposer un nouveau modèle, on ajoute **une seule classe** ; aucun autre fichier du module ne change.

## Robustesse intégrée

- **Streaming** : `lazyById()` → pas de table chargée en mémoire (gros volumes).
- **Validation stricte** : sources/formats inconnus → 422 explicite.
- **Champs libres** acceptés si `path` est fourni (zéro friction pour des cas custom).
- **Résolution non stricte** : un segment manquant renvoie `null` → ligne préservée.
- **CSV multi-feuilles** automatiquement zippé.
- **Filenames sûrs** : slugifiés + horodatés + suffixe aléatoire.
- **Cleanup** : le fichier généré est supprimé après envoi (`deleteFileAfterSend`).
