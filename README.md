# JBIS API

API REST Laravel pour la plateforme JBIS (recrutement, candidats, catalogue offres/programmes).  
Frontend associé : dépôt **jbis-next** (`NEXT_PUBLIC_API_BASE_URL` → cette API).

Authentification : **Laravel Sanctum** (tokens Bearer). Réponses JSON via l’enveloppe **`BaseResponse`**.

---

## Démarrage rapide

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Variables utiles (voir `.env.example`) :

| Variable | Rôle |
|----------|------|
| `APP_URL` | URL publique de l’API |
| `FRONTEND_URL` | Origine du front (CORS, liens e-mail) |
| `DB_*` | Base de données |
| `FILESYSTEM_DISK` / disques médias | Stockage fichiers |
| Clés Cloudinary | Upload cloud (miroir local + cloud) |

En local avec **jbis-next** : `APP_URL=http://127.0.0.1:8000`, front sur `http://localhost:3000`.

Qualité :

```bash
./vendor/bin/pint
php artisan test
```

---

## Structure du projet

Le code métier n’est pas dans un dossier `core/` à la racine : tout vit sous **`app/`**, organisé en DDD pragmatique.

```
app/
├── Core/
│   ├── Application/          # Livraison (HTTP API v1, requêtes, resources)
│   │   └── Api/
│   │       ├── Responses/    # BaseResponse
│   │       └── V1/
│   │           ├── Auth/
│   │           ├── Identity/     # users, profil candidat, admin users
│   │           ├── Document/     # pièces justificatives (user_documents)
│   │           ├── Catalog/        # offres, programmes, référentiels
│   │           ├── Candidacy/
│   │           ├── Communication/  # mail, SMS
│   │           ├── Export/
│   │           ├── Analytics/
│   │           └── Public/
│   └── Domain/               # Logique métier (Actions, Models, DTOs, Rules…)
│       ├── Identity/
│       ├── Catalog/
│       ├── Candidacy/
│       ├── Communication/
│       └── Shared/             # Media, Export, Pdf, Ai…
├── Http/                     # Kernel Laravel, middleware globaux
└── Actions/Fortify/          # Inscription Fortify
routes/
├── api.php                   # Toutes les routes API (/api/v1/…)
database/
├── migrations/
└── seeders/                  # Rôles, types de documents, etc.
```

**Règle** : les contrôleurs API restent minces ; la logique métier est dans `Core/Domain/*/Actions/*`.

---

## API v1 — aperçu

Préfixe : **`/api/v1`**. Routes publiques (auth, catalogue public) puis groupe **`auth:sanctum`**.

| Module | Préfixe / exemples | Rôle |
|--------|-------------------|------|
| Auth | `POST /login`, `/register`, `/me`, 2FA | Sessions token |
| Identity (candidat) | `PATCH /me/profile/steps/{step}` | Wizard profil (`personal`, `contact`, `professional`, `documents`) |
| Identity (admin) | `GET/POST /identity/admin/users`, `PATCH …/profile/approval` | Gestion utilisateurs & validation profil |
| Documents | `GET/POST /documents`, `POST …/validate` | Pièces justificatives |
| Catalog | `/catalog/admin/offers`, `/public/offers` | Offres & programmes |
| Mail / SMS | `/mail-campaigns`, `/sms-campaigns` | Campagnes |
| Export | `POST /exports` | Excel / CSV / PDF |

Fichier de référence : `routes/api.php`.

### Enveloppe de réponse

Tous les contrôleurs API utilisent `App\Core\Application\Api\Responses\BaseResponse` :

- `BaseResponse::ok([...])`
- `BaseResponse::created([...])`
- `BaseResponse::unprocessableEntity([...])`

Éviter `response()->json()` direct dans les controllers API.

---

## Profil candidat & dossier (intégration front)

### Candidat (espace `/candidate` côté jbis-next)

| Méthode | Route | Description |
|---------|-------|-------------|
| `PATCH` | `/api/v1/me/profile/steps/{step}` | Mise à jour d’une étape du wizard |
| `POST` | `/api/v1/me/profile/pictures` | Photos d’identité |
| `GET` | `/api/v1/documents` | Liste des documents de l’utilisateur connecté |
| `POST` | `/api/v1/documents` | Dépôt d’un document |
| `GET` | `/api/v1/documents/types` | Catalogue des types (candidat) |
| `GET` | `/api/v1/documents/{id}/download` | Téléchargement (nom de fichier normalisé) |

Domaine : `app/Core/Domain/Identity/Actions/Profile/`, modèle `UserProfile`.

### Staff — vue dossier admin (`/admin/user/[id]` côté jbis-next)

| Méthode | Route | Permission typique |
|---------|-------|------------------|
| `GET` | `/api/v1/identity/admin/users/{id}` | `user.view` |
| `PATCH` | `/api/v1/identity/admin/users/{id}/profile/approval` | `user.update` — `is_approved` |
| `GET` | `/api/v1/documents?user_id={id}` | `userdocument.view` |
| `POST` | `/api/v1/documents` (+ `user_id`) | `userdocument.create` — dépôt pour le candidat |
| `POST` | `/api/v1/documents/{id}/validate` | `userdocument.update` — `status`: `APPROVED` / `REJECTED`, `rejection_reason` optionnel |
| `DELETE` | `/api/v1/documents/{id}` | `userdocument.delete` |

Actions domaine documents : `app/Core/Domain/Identity/Actions/Document/`  
États : `UserDocumentStatus` (`PENDING`, `APPROVED`, `REJECTED`, `EXPIRED`).  
Types : table `document_types`, seeder `DocumentTypeSeeder`.

Approbation profil : `ModerateUserProfileAction` → met à jour `user_profiles.is_approved` et `approved_by`.

---

## Architecture DDD (rappel)

Ce projet applique une architecture **orientée domaine**, inspirée du DDD et du Clean Code.

### Philosophie

- Regrouper par **concept métier** (Identity, Catalog…), pas uniquement par rôle technique.
- Séparer la logique métier de la couche HTTP.
- Rendre le code explicite, testable et navigable.

### Couche Domaine (`app/Core/Domain/`)

Répond à : *« Que fait l’application ? »*

Par domaine : **Actions**, **Models** (Eloquent légers), **DTOs**, **Rules**, **Events**, **Exceptions**, **Services** (intégrations techniques).

### Couche Application (`app/Core/Application/`)

Répond à : *« Comment expose-t-on le métier ? »*

**Controllers**, **Form Requests**, **API Resources** — sans logique métier lourde.

### Actions vs Services

| | Action | Service |
|---|--------|---------|
| Rôle | Opération métier complète | Outil / intégration |
| Question | « Que fait l’app ? » | « Qu’est-ce qu’elle utilise ? » |
| Exemple | `StoreUserDocumentAction` | `DocumentStorageService` |

Les **Actions** appellent les **Services**, pas l’inverse.

### Implémenter une Action

```php
// app/Core/Domain/Identity/Actions/Document/StoreUserDocumentAction.php

final class StoreUserDocumentAction
{
    public function execute(UserDocumentDto $dto): UserDocument
    {
        // logique métier…
    }
}
```

- Suffixe **`Action`**, méthode publique **`execute()`**.
- Entrée : **DTO** typé (pas de `Request` HTTP).
- Sortie : modèle / DTO domaine (pas de `JsonResponse`).

---

## Conventions d’implémentation (projet JBIS)

1. **DTO** — suffixe `Dto` pour les objets de transfert (`UserDocumentDto`, `AdminUserWriteDto`).
2. **Actions** — toute opération métier complète = classe `*Action` + `execute()`.
3. **Builders** — requêtes SQL métier complexes sur des `*Builder` de modèle (`UserBuilder`, `ProgramBuilder`).
4. **Events / Listeners** — effets de bord (stats, notifications) plutôt que cascades dans une Action.
5. **Enums d’état** — pas de chaînes magiques (`UserDocumentStatus`, `MailCampaignStatus`, etc.).
6. **Exceptions domaine** — explicites (`ProfileLockedException`, `DocumentStorageException`).
7. **Domaine sans HTTP** — mapper `Request` → DTO en Application.
8. **BaseResponse** — obligatoire sur l’API v1.
9. **ViewModels** — couche Application si transformation de sortie dédiée.

---

## Stockage médias (abstraction)

La logique métier ne dépend pas du détail d’hébergement (local vs Cloudinary).

**Module** : `app/Core/Domain/Shared/Media/`

- Contrat : `MediaStorageDriverInterface`
- Drivers : `LocalMirrorStorageDriver`, `CloudinaryStorageDriver`
- Orchestrateur : `StoreMediaAction`
- DTO : `UploadedMediaDto`
- Chemins : `MediaPathBuilder` (`{folder}/{YYYY}/{MM}/raw|optimized/…`)

Flux : écriture locale RAW + WebP optimisé → tentative cloud → URL publique cloud avec **fallback** local.

Exemple upload offre admin : `POST /api/v1/catalog/admin/offers/upload-photo`.

Documents candidats : `DocumentStorageService` (domaine Identity), politique fichiers `UserDocumentFilePolicy`.

---

## CI/CD (GitHub Actions → o2switch)

Workflow : `.github/workflows/deploy.yml`

- Déclencheur : push `main` / `master` ou manuel.
- Déploiement FTP ([SamKirkland/FTP-Deploy-Action](https://github.com/SamKirkland/FTP-Deploy-Action)).
- **`vendor/` exclu** du sync : exécuter sur le serveur après déploiement :
  `composer install --no-dev --optimize-autoloader`
- Secrets : `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`
- **`.env`** non écrasé par le déploiement.

Après déploiement : droits `storage/` et `bootstrap/cache/`, `php artisan migrate --force` si besoin, caches config/route selon procédure.

---

## Principes clés (synthèse)

- **Simplicité** et conventions d’équipe avant la sur-abstraction.
- **Responsabilité unique** par classe.
- **Le domaine est roi** — la logique précieuse vit dans `Core/Domain`.
- **L’application est un pont** HTTP vers le domaine.
- **Tests** — Feature tests sur parcours critiques (auth, documents, profil).

Pour l’organisation du front dossier (admin uniquement) et le pattern *Client Components at the Leaves*, voir le README de **jbis-next**.
