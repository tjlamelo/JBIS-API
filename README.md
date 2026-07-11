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

## Fonctionnalités et modules (état au 2026-07-06)

Légende : **Implémenté** · **Partiel** (API ou UI seule) · **Non implémenté**

Dépôt frontend associé : **jbis-next** (routes UI). Ce README décrit surtout la couche API.

### Rôles applicatifs

| Rôle | Périmètre |
|------|-----------|
| `candidate` | Dossier candidat, candidatures, documents |
| `staff` / `admin` | Back-office JBIS (catalogue, dossiers, recruteurs, candidatures…) |
| `recruiter` | Portail recruteur : offres, profils partagés, demandes de profils |
| `partner` | Établissements de formation — gestion des stages (en cours) |

> **Recruteur ≠ Partenaire** : le recruteur embauche / consulte des profils ; le partenaire soumet des étudiants en stage et suit leurs dossiers.

### Modules implémentés (API)

| Module | Besoin fonctionnel | API | UI (jbis-next) |
|--------|-------------------|-----|----------------|
| **Auth & identité** | Connexion, inscription, 2FA, profil candidat, validation staff | Implémenté | Implémenté |
| **Documents** | Dépôt, validation, téléchargement, types | Implémenté | Implémenté |
| **Catalogue** | Offres, programmes, référentiels, entreprises | Implémenté | Implémenté |
| **Candidatures** | Parcours `Application` + étapes workflow, entretiens, paiements | Implémenté | Implémenté |
| **Workflow** | Process flows configurables (étapes, documents, paiements…) | Implémenté | Implémenté |
| **Formations JBIS** | CRUD catalogue `Training` (admin staff) | Implémenté | Implémenté (`/admin/training`) |
| **Recruteurs — onboarding** | Candidature publique, validation staff, provisionnement portail | Implémenté | Implémenté |
| **Recruteurs — offres** | Soumission offre par recruteur, validation / publication staff | Implémenté | Implémenté |
| **Recruteurs — partage profils** | Staff assigne des candidats avec sections visibles / champs masqués | Implémenté | Implémenté |
| **Recruteurs — demandes de profils** | Recruteur soumet critères → staff matche automatiquement → transmission | Implémenté | Implémenté |
| **Recruteurs — retour profils** | Recruteur indique statut + note sur profils reçus | Implémenté | Implémenté |
| **Communication** | Campagnes mail / SMS, newsletter, contact | Implémenté | Partiel |
| **Export** | Excel / CSV / PDF (sources métier) | Implémenté | Partiel |
| **Partenaire — stages** | Cohortes, inscription étudiants, checklist docs, placements | **Partiel** (MVP) | **Partiel** (`/partner/cohorts`, `/admin/partners/cohorts`) |

### Détail — modules récents (juillet 2026)

#### Formations JBIS (`Training`)

- **Besoin** : référentiel des formations proposées par JBIS, géré par le staff.
- **API** : `GET/POST/PUT/DELETE /api/v1/catalog/admin/trainings`
- **Permissions** : `training.view`, `training.create`, `training.update`, `training.delete`
- **Modèle** : `App\Core\Domain\Catalog\Models\Training`

#### Demandes de profils recruteur (`RecruiterProfileRequest`)

- **Besoin** : le recruteur décrit un besoin (critères, quantité) ; le staff voit les candidats matchés et peut transmettre.
- **Flux** : `draft` → `submitted` → `matched` → `transmitted` (ou `rejected` / `needs_changes`)
- **API recruteur** : `/api/v1/recruiter/profile-requests` (CRUD + `submit`)
- **API admin** : `/api/v1/identity/admin/recruiter-profile-requests` (liste, match, transmit, review)
- **Matching** : réutilise les filtres de recherche profils admin (`AdminUserIdsFromFiltersQuery`)
- **Traçabilité** : `recruiter_profile_request_id` sur `RecruiterProfileAssignment`
- **Permissions** : `recruiterprofilerequest.*`

#### Retour recruteur sur profils assignés

- **Besoin** : le recruteur qualifie les profils reçus (gardé, à revoir, refusé…) avec une note libre.
- **API** : `PATCH /api/v1/recruiter/assignments/{id}/feedback`
- **Champs** : `feedback_status`, `feedback_note`, `feedback_updated_at`, `feedback_updated_by_user_id`
- **Migration** : `2026_07_06_153000_add_recruiter_feedback_to_assignments` + FK `rpa_feedback_updated_by_foreign`

### Module partenaire — stages (juillet 2026)

#### Implémenté (MVP)

| Besoin | Statut | Détail |
|--------|--------|--------|
| Organisation partenaire | OK | `PartnerOrganization` + pivot `partner_organization_user` |
| Cohortes / promotions | OK | CRUD brouillon, soumission, revue staff |
| Enrôlement étudiants | OK | `PartnerCohortStudent` (nom, e-mail, lien compte optionnel) |
| Checklist documentaire | OK | Template par cohorte + statut par étudiant (sync `user_documents`) |
| Vue récap partenaire | OK | Dashboard KPIs + fiche étudiant checklist |
| API admin | OK | `/identity/admin/partner-organizations`, `/partner-cohorts` |

#### Non implémenté (backlog)

| Besoin | Description |
|--------|-------------|
| Matching & placement | Recherche automatique de stages par JBIS |
| Convention & feedback | Workflow convention, retours fin de stage |
| Invitation compte étudiant | E-mail d'inscription automatique |
| Lien `UserInternship` | Association stage candidat ↔ placement partenaire |

**API partenaire** : `/api/v1/partner/me/organization`, `/dashboard`, `/cohorts`, `/cohorts/{id}/students`  
**Permissions** : `partnerorganization.*`, `partnercohort.*`, `partnercohortstudent.*`  
**Migration** : `2026_07_06_160000_create_partner_portal_tables.php`

### Migrations récentes (recruteur)

| Fichier | Objet |
|---------|-------|
| `2026_07_06_100000_create_recruiter_profile_requests_table` | Table demandes de profils |
| `2026_07_06_100001_add_recruiter_profile_request_id_to_assignments` | Lien demande ↔ assignation |
| `2026_07_06_153000_add_recruiter_feedback_to_assignments` | Colonnes feedback recruteur |
| `2026_07_06_153001_add_recruiter_feedback_updated_by_foreign` | FK courte (limite MySQL 64 car.) |

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

## Modélisation UML du domaine

Les modèles Eloquent vivent dans `app/Core/Domain/*/Models/`.  
Le tableau ci-dessous liste, pour chaque *bounded context*, les relations entre les classes (verbe, multiplicité, table pivot le cas échéant). Un diagramme Mermaid global suit en fin de section.

### Bounded contexts

| Contexte | Dossier | Classes clés |
|----------|---------|--------------|
| Identity | `Domain/Identity/Models/` | User, UserProfile, UserDocument, Education, Experience, Certification, UserSkill, UserTraining, UserPreferredCountry, UserVisaHistory… |
| Catalog | `Domain/Catalog/Models/` | Offer, Program, Company, Category, Trade, Skill, SkillCategory, EducationLevel, ContractType, OfferType, WorkSchedule, Benefit, Training, Agency… |
| Candidacy | `Domain/Candidacy/Models/` | Application, ApplicationStep, ApplicationDocument, Interview, Appointment, RequiredDocument, OfferLanguageCourseRequirement, CandidateLanguageCourse… |
| Workflow | `Domain/Workflow/Models/` | ProcessFlow, ProcessFlowSection, ProcessStep |
| Finance | `Domain/Finance/Models/` | Payment, PaymentSchedule, PaymentInstallment |
| Recruiter | `Domain/Recruiter/Models/` | RecruiterOrganization, RecruiterOfferSubmission, RecruiterProfileAssignment, RecruiterProfileRequest, RecruiterOnboardingApplication |
| Partner | `Domain/Partner/Models/` | PartnerOrganization, PartnerCohort, PartnerCohortStudent, PartnerCohortRequiredDocument, PartnerCohortStudentDocument |
| Location | `Domain/Location/Models/` | Country, Region, City, GeographicZone, Language, LanguageLevel |
| Communication | `Domain/Communication/Models/` | DiscoverySource, MailCampaign, MailDispatch, SmsCampaign, SmsDispatch, NewsletterSubscription, ContactRequest |

### Relations détaillées par contexte

Légende : `◆--` = appartenance (N-1), `1--*` = posséder plusieurs, `*--*` = many-to-many, `1--1` = posséder un. Les verbes sont à l'infinitif, conformément à la notation UML. Les classes marquées **ENUM** sont des tables de référence / énumérations du domaine.

### Candidacy


| Type | Classe | Relation | Cible | Multiplicité | Notes |
|------|--------|----------|-------|--------------|-------|
| - | `Application` | `user` — appartenir à | `User` | ◆-- |  |
| - | `Application` | `offer` — appartenir à | `Offer` | ◆-- |  |
| - | `Application` | `program` — appartenir à | `Program` | ◆-- |  |
| - | `Application` | `processFlow` — appartenir à | `ProcessFlow` | ◆-- |  |
| - | `Application` | `protocolDocument` — appartenir à | `UserDocument` | ◆-- | pivot `protocol_document_id` |
| - | `Application` | `currentStep` — appartenir à | `ApplicationStep` | ◆-- | pivot `current_application_step_id` |
| - | `Application` | `steps` — posséder plusieurs | `ApplicationStep` | 1--* |  |
| - | `Application` | `documents` — posséder plusieurs | `ApplicationDocument` | 1--* |  |
| - | `Application` | `events` — posséder plusieurs | `ApplicationStepEvent` | 1--* |  |
| - | `Application` | `interviews` — posséder plusieurs | `Interview` | 1--* |  |
| - | `Application` | `languageCourses` — posséder plusieurs | `CandidateLanguageCourse` | 1--* |  |
| - | `Application` | `payments` — posséder plusieurs | `Payment` | 1--* |  |
| - | `Application` | `paymentSchedule` — posséder un | `PaymentSchedule` | 1--1 |  |
| - | `ApplicationDocument` | `application` — appartenir à | `Application` | ◆-- |  |
| - | `ApplicationDocument` | `userDocument` — appartenir à | `UserDocument` | ◆-- |  |
| - | `ApplicationDocument` | `applicationStep` — appartenir à | `ApplicationStep` | ◆-- |  |
| - | `ApplicationDocument` | `reviewer` — appartenir à | `User` | ◆-- | pivot `reviewed_by` |
| - | `ApplicationStep` | `application` — appartenir à | `Application` | ◆-- |  |
| - | `ApplicationStep` | `processStep` — appartenir à | `ProcessStep` | ◆-- |  |
| - | `ApplicationStep` | `payments` — posséder plusieurs | `Payment` | 1--* |  |
| - | `ApplicationStep` | `installments` — posséder plusieurs | `PaymentInstallment` | 1--* |  |
| - | `ApplicationStep` | `applicationDocuments` — posséder plusieurs | `ApplicationDocument` | 1--* |  |
| - | `ApplicationStep` | `interview` — posséder un | `Interview` | 1--1 |  |
| - | `ApplicationStep` | `documentsValidatedBy` — appartenir à | `User` | ◆-- | pivot `documents_validated_by` |
| - | `ApplicationStep` | `interviewValidatedBy` — appartenir à | `User` | ◆-- | pivot `interview_validated_by` |
| - | `ApplicationStep` | `signedContract` — appartenir à | `UserDocument` | ◆-- | pivot `signed_contract_id` |
| - | `ApplicationStepEvent` | `application` — appartenir à | `Application` | ◆-- |  |
| - | `ApplicationStepEvent` | `applicationStep` — appartenir à | `ApplicationStep` | ◆-- |  |
| - | `ApplicationStepEvent` | `actor` — appartenir à | `User` | ◆-- | pivot `actor_user_id` |
| - | `Appointment` | `user` — appartenir à | `User` | ◆-- |  |
| - | `Appointment` | `agency` — appartenir à | `Agency` | ◆-- |  |
| - | `Appointment` | `discoverySource` — appartenir à | `DiscoverySource` | ◆-- |  |
| - | `CandidateLanguageCourse` | `user` — appartenir à | `User` | ◆-- |  |
| - | `CandidateLanguageCourse` | `offer` — appartenir à | `Offer` | ◆-- |  |
| - | `CandidateLanguageCourse` | `application` — appartenir à | `Application` | ◆-- |  |
| - | `CandidateLanguageCourse` | `language` — appartenir à | `Language` | ◆-- |  |
| - | `CandidateLanguageCourse` | `training` — appartenir à | `Training` | ◆-- |  |
| - | `CandidateLanguageCourse` | `userTraining` — appartenir à | `UserTraining` | ◆-- |  |
| - | `CandidateLanguageCourse` | `recordedBy` — appartenir à | `User` | ◆-- | pivot `recorded_by` |
| - | `Interview` | `application` — appartenir à | `Application` | ◆-- |  |
| - | `Interview` | `applicationStep` — appartenir à | `ApplicationStep` | ◆-- |  |
| - | `Interview` | `company` — appartenir à | `Company` | ◆-- |  |
| - | `Interview` | `reportDocument` — appartenir à | `UserDocument` | ◆-- | pivot `report_document_id` |
| - | `OfferLanguageCourseRequirement` | `offer` — appartenir à | `Offer` | ◆-- |  |
| - | `OfferLanguageCourseRequirement` | `language` — appartenir à | `Language` | ◆-- |  |
| - | `OfferLanguageCourseRequirement` | `training` — appartenir à | `Training` | ◆-- |  |
| - | `RequiredDocument` | `program` — appartenir à | `Program` | ◆-- |  |
| - | `RequiredDocument` | `offer` — appartenir à | `Offer` | ◆-- |  |

### Catalog


| Type | Classe | Relation | Cible | Multiplicité | Notes |
|------|--------|----------|-------|--------------|-------|
| - | `Agency` | `manager` — appartenir à | `User` | ◆-- | pivot `manager_id` |
| - | `Agency` | `country` — appartenir à | `Country` | ◆-- |  |
| - | `Agency` | `city` — appartenir à | `City` | ◆-- |  |
| - | `Agency` | `profiles` — posséder plusieurs | `UserProfile` | 1--* | pivot `agency_id` |
| **ENUM** | `Benefit` | `offers` — être lié à plusieurs | `Offer` | *--* | pivot `benefit_offer` |
| **ENUM** | `Category` | `users` — être lié à plusieurs | `User` | *--* | pivot `user_sector` |
| **ENUM** | `Category` | `skills` — posséder plusieurs | `Skill` | 1--* | pivot `category_id` |
| **ENUM** | `Category` | `trades` — posséder plusieurs | `Trade` | 1--* | pivot `category_id` |
| - | `CertificationOffer` | `processFlow` — appartenir à | `ProcessFlow` | ◆-- |  |
| - | `Company` | `category` — appartenir à | `Category` | ◆-- | pivot `category_id` |
| - | `Company` | `country` — appartenir à | `Country` | ◆-- |  |
| - | `Company` | `city` — appartenir à | `City` | ◆-- |  |
| - | `Company` | `offers` — posséder plusieurs | `Offer` | 1--* | pivot `company_id` |
| **ENUM** | `ContractType` | `offers` — posséder plusieurs | `Offer` | 1--* | pivot `contract_type_id` |
| **ENUM** | `EducationLevel` | `offers` — posséder plusieurs | `Offer` | 1--* | pivot `education_level_id` |
| - | `Offer` | `languages` — être lié à plusieurs | `Language` | *--* | pivot `language_offer` |
| - | `Offer` | `city` — appartenir à | `City` | ◆-- |  |
| - | `Offer` | `country` — appartenir à | `Country` | ◆-- |  |
| - | `Offer` | `contractType` — appartenir à | `ContractType` | ◆-- |  |
| - | `Offer` | `offerType` — appartenir à | `OfferType` | ◆-- |  |
| - | `Offer` | `workSchedule` — appartenir à | `WorkSchedule` | ◆-- |  |
| - | `Offer` | `educationLevel` — appartenir à | `EducationLevel` | ◆-- |  |
| - | `Offer` | `company` — appartenir à | `Company` | ◆-- |  |
| - | `Offer` | `program` — appartenir à | `Program` | ◆-- |  |
| - | `Offer` | `category` — appartenir à | `Category` | ◆-- | pivot `category_id` |
| - | `Offer` | `trade` — appartenir à | `Trade` | ◆-- |  |
| - | `Offer` | `benefits` — être lié à plusieurs | `Benefit` | *--* |  |
| - | `Offer` | `skills` — être lié à plusieurs | `Skill` | *--* | pivot `offer_skill` |
| - | `Offer` | `requiredDocuments` — être lié à plusieurs | `RequiredDocument` | *--* | pivot `offer_required_document` |
| - | `Offer` | `languageCourseRequirements` — posséder plusieurs | `OfferLanguageCourseRequirement` | 1--* |  |
| - | `Offer` | `candidateLanguageCourses` — posséder plusieurs | `CandidateLanguageCourse` | 1--* |  |
| **ENUM** | `OfferType` | `offers` — posséder plusieurs | `Offer` | 1--* | pivot `offer_type_id` |
| - | `Program` | `languages` — être lié à plusieurs | `Language` | *--* | pivot `language_program` |
| - | `Program` | `offers` — posséder plusieurs | `Offer` | 1--* | pivot `program_id` |
| - | `Program` | `geographicZone` — appartenir à | `GeographicZone` | ◆-- | pivot `geographic_zone_id` |
| - | `Program` | `requiredDocuments` — être lié à plusieurs | `RequiredDocument` | *--* | pivot `program_required_document` |
| **ENUM** | `Skill` | `skillCategory` — appartenir à | `SkillCategory` | ◆-- | pivot `skill_category_id` |
| **ENUM** | `Skill` | `category` — appartenir à | `Category` | ◆-- | pivot `category_id` |
| **ENUM** | `Skill` | `offers` — être lié à plusieurs | `Offer` | *--* | pivot `offer_skill` |
| **ENUM** | `SkillCategory` | `skills` — posséder plusieurs | `Skill` | 1--* | pivot `skill_category_id` |
| **ENUM** | `Trade` | `category` — appartenir à | `Category` | ◆-- | pivot `category_id` |
| **ENUM** | `Trade` | `offers` — posséder plusieurs | `Offer` | 1--* |  |
| **ENUM** | `Training` | `userTrainings` — posséder plusieurs | `UserTraining` | 1--* | pivot `training_id` |
| **ENUM** | `WorkSchedule` | `offers` — posséder plusieurs | `Offer` | 1--* | pivot `work_schedule_id` |

### Communication


| Type | Classe | Relation | Cible | Multiplicité | Notes |
|------|--------|----------|-------|--------------|-------|
| - | `ContactRequest` | `discoverySource` — appartenir à | `DiscoverySource` | ◆-- |  |
| - | `MailCampaign` | `creator` — appartenir à | `User` | ◆-- | pivot `created_by` |
| - | `MailCampaign` | `dispatches` — posséder plusieurs | `MailDispatch` | 1--* |  |
| - | `MailDispatch` | `campaign` — appartenir à | `MailCampaign` | ◆-- | pivot `mail_campaign_id` |
| - | `MailDispatch` | `user` — appartenir à | `User` | ◆-- |  |
| - | `NewsletterSubscription` | `user` — appartenir à | `User` | ◆-- |  |
| - | `SmsCampaign` | `creator` — appartenir à | `User` | ◆-- | pivot `created_by` |
| - | `SmsCampaign` | `dispatches` — posséder plusieurs | `SmsDispatch` | 1--* |  |
| - | `SmsDispatch` | `campaign` — appartenir à | `SmsCampaign` | ◆-- | pivot `sms_campaign_id` |
| - | `SmsDispatch` | `user` — appartenir à | `User` | ◆-- |  |

### Finance


| Type | Classe | Relation | Cible | Multiplicité | Notes |
|------|--------|----------|-------|--------------|-------|
| - | `Payment` | `application` — appartenir à | `Application` | ◆-- |  |
| - | `Payment` | `applicationStep` — appartenir à | `ApplicationStep` | ◆-- |  |
| - | `Payment` | `user` — appartenir à | `User` | ◆-- |  |
| - | `PaymentInstallment` | `application` — appartenir à | `Application` | ◆-- |  |
| - | `PaymentInstallment` | `applicationStep` — appartenir à | `ApplicationStep` | ◆-- |  |
| - | `PaymentSchedule` | `application` — appartenir à | `Application` | ◆-- |  |

### Identity


| Type | Classe | Relation | Cible | Multiplicité | Notes |
|------|--------|----------|-------|--------------|-------|
| - | `Archive` | `user` — appartenir à | `User` | ◆-- |  |
| - | `Certification` | `document` — appartenir à | `UserDocument` | ◆-- | pivot `document_id` |
| - | `Certification` | `user` — appartenir à | `User` | ◆-- |  |
| - | `Certification` | `approver` — appartenir à | `User` | ◆-- | pivot `approved_by` |
| **ENUM** | `DocumentType` | `userDocuments` — posséder plusieurs | `UserDocument` | 1--* | pivot `document_type_id` |
| - | `Education` | `document` — appartenir à | `UserDocument` | ◆-- | pivot `document_id` |
| - | `Education` | `user` — appartenir à | `User` | ◆-- |  |
| - | `Education` | `level` — appartenir à | `EducationLevel` | ◆-- | pivot `education_level_id` |
| - | `Education` | `country` — appartenir à | `Country` | ◆-- |  |
| - | `Experience` | `document` — appartenir à | `UserDocument` | ◆-- | pivot `document_id` |
| - | `Experience` | `user` — appartenir à | `User` | ◆-- |  |
| - | `Experience` | `contractType` — appartenir à | `ContractType` | ◆-- |  |
| - | `Experience` | `country` — appartenir à | `Country` | ◆-- |  |
| - | `InterestAndHobby` | `user` — appartenir à | `User` | ◆-- |  |
| - | `Language` | `user` — appartenir à | `User` | ◆-- |  |
| - | `Language` | `language` — appartenir à | `CatalogLanguage` | ◆-- | pivot `language_id` |
| - | `Language` | `languageLevel` — appartenir à | `LanguageLevel` | ◆-- | pivot `language_level_id` |
| - | `LegalDocument` | `publisher` — appartenir à | `User` | ◆-- | pivot `published_by` |
| - | `User` | `profile` — posséder un | `UserProfile` | 1--1 |  |
| - | `User` | `sectors` — être lié à plusieurs | `Category` | *--* | pivot `user_sector` |
| - | `User` | `trades` — être lié à plusieurs | `Trade` | *--* | pivot `user_trade` |
| - | `User` | `documents` — posséder plusieurs | `UserDocument` | 1--* |  |
| - | `User` | `experiences` — posséder plusieurs | `Experience` | 1--* |  |
| - | `User` | `languages` — posséder plusieurs | `Language` | 1--* |  |
| - | `User` | `userSkills` — posséder plusieurs | `UserSkill` | 1--* |  |
| - | `User` | `educations` — posséder plusieurs | `Education` | 1--* |  |
| - | `User` | `certifications` — posséder plusieurs | `Certification` | 1--* |  |
| - | `User` | `Offers` — posséder plusieurs | `Offer` | 1--* | pivot `user_id` |
| - | `User` | `applications` — posséder plusieurs | `Application` | 1--* |  |
| - | `User` | `devices` — posséder plusieurs | `UserDevice` | 1--* |  |
| - | `User` | `settings` — posséder un | `UserSetting` | 1--1 |  |
| - | `User` | `consents` — posséder plusieurs | `UserConsent` | 1--* |  |
| - | `User` | `preferredCountries` — posséder plusieurs | `UserPreferredCountry` | 1--* |  |
| - | `User` | `visaHistories` — posséder plusieurs | `UserVisaHistory` | 1--* |  |
| - | `User` | `trainings` — posséder plusieurs | `UserTraining` | 1--* |  |
| - | `User` | `languageCourses` — posséder plusieurs | `CandidateLanguageCourse` | 1--* |  |
| - | `User` | `internships` — posséder plusieurs | `UserInternship` | 1--* |  |
| - | `User` | `interests` — posséder plusieurs | `InterestAndHobby` | 1--* |  |
| - | `User` | `staffNotes` — posséder plusieurs | `UserNote` | 1--* | pivot `user_id` |
| - | `User` | `authoredStaffNotes` — posséder plusieurs | `UserNote` | 1--* | pivot `author_id` |
| - | `User` | `recruiterOrganizations` — être lié à plusieurs | `RecruiterOrganization` | *--* | pivot `recruiter_organization_user` |
| - | `UserConsent` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserDevice` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserDocument` | `documentType` — appartenir à | `DocumentType` | ◆-- | pivot `document_type_id` |
| - | `UserDocument` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserDocument` | `uploader` — appartenir à | `User` | ◆-- | pivot `uploaded_by` |
| - | `UserDocument` | `validator` — appartenir à | `User` | ◆-- | pivot `validated_by` |
| - | `UserDocument` | `issuingCountry` — appartenir à | `Country` | ◆-- | pivot `issuing_country_id` |
| - | `UserDocument` | `linkedVisaHistories` — posséder plusieurs | `UserVisaHistory` | 1--* | pivot `document_id` |
| - | `UserDocument` | `extractions` — posséder plusieurs | `UserDocumentExtraction` | 1--* | pivot `user_document_id` |
| - | `UserDocumentExtraction` | `userDocument` — appartenir à | `UserDocument` | ◆-- | pivot `user_document_id` |
| - | `UserDocumentExtraction` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserDocumentExtraction` | `reviewer` — appartenir à | `User` | ◆-- | pivot `reviewed_by` |
| - | `UserInternship` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserInternship` | `certificateDocument` — appartenir à | `UserDocument` | ◆-- | pivot `certificate_document_id` |
| - | `UserNote` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserNote` | `author` — appartenir à | `User` | ◆-- | pivot `author_id` |
| - | `UserPermissionOverride` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserPreferredCountry` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserPreferredCountry` | `country` — appartenir à | `Country` | ◆-- |  |
| - | `UserProfile` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserProfile` | `discoverySource` — appartenir à | `DiscoverySource` | ◆-- |  |
| - | `UserProfile` | `nationality` — appartenir à | `Country` | ◆-- | pivot `nationality_country_id` |
| - | `UserProfile` | `agency` — appartenir à | `Agency` | ◆-- | pivot `agency_id` |
| - | `UserProfile` | `highestEducationLevel` — appartenir à | `EducationLevel` | ◆-- | pivot `highest_education_level_id` |
| - | `UserProfile` | `approver` — appartenir à | `User` | ◆-- | pivot `approved_by` |
| - | `UserProfile` | `recruiterOrganization` — appartenir à | `RecruiterOrganization` | ◆-- |  |
| - | `UserSecurityEvent` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserSecurityEvent` | `device` — appartenir à | `UserDevice` | ◆-- | pivot `user_device_id` |
| - | `UserSetting` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserSkill` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserSkill` | `skill` — appartenir à | `Skill` | ◆-- |  |
| - | `UserTraining` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserTraining` | `training` — appartenir à | `Training` | ◆-- |  |
| - | `UserVisaHistory` | `user` — appartenir à | `User` | ◆-- |  |
| - | `UserVisaHistory` | `country` — appartenir à | `Country` | ◆-- |  |
| - | `UserVisaHistory` | `document` — appartenir à | `UserDocument` | ◆-- | pivot `document_id` |

### Location


| Type | Classe | Relation | Cible | Multiplicité | Notes |
|------|--------|----------|-------|--------------|-------|
| **ENUM** | `City` | `region` — appartenir à | `Region` | ◆-- |  |
| **ENUM** | `City` | `offers` — posséder plusieurs | `Offer` | 1--* |  |
| **ENUM** | `Country` | `userPreferredCountries` — posséder plusieurs | `UserPreferredCountry` | 1--* |  |
| **ENUM** | `Country` | `userVisaHistories` — posséder plusieurs | `UserVisaHistory` | 1--* |  |
| **ENUM** | `GeographicZone` | `programs` — posséder plusieurs | `Program` | 1--* | pivot `geographic_zone_id` |
| **ENUM** | `Language` | `programs` — être lié à plusieurs | `Program` | *--* | pivot `language_program` |
| **ENUM** | `Language` | `offers` — être lié à plusieurs | `Offer` | *--* | pivot `language_offer` |
| **ENUM** | `Region` | `country` — appartenir à | `Country` | ◆-- |  |
| **ENUM** | `Region` | `cities` — posséder plusieurs | `City` | 1--* |  |

### Recruiter


| Type | Classe | Relation | Cible | Multiplicité | Notes |
|------|--------|----------|-------|--------------|-------|
| - | `RecruiterOfferSubmission` | `organization` — appartenir à | `RecruiterOrganization` | ◆-- | pivot `recruiter_organization_id` |
| - | `RecruiterOfferSubmission` | `submittedBy` — appartenir à | `User` | ◆-- | pivot `submitted_by_user_id` |
| - | `RecruiterOfferSubmission` | `offer` — appartenir à | `Offer` | ◆-- |  |
| - | `RecruiterOfferSubmission` | `reviewer` — appartenir à | `User` | ◆-- | pivot `reviewed_by` |
| - | `RecruiterOnboardingApplication` | `applicant` — appartenir à | `User` | ◆-- | pivot `applicant_user_id` |
| - | `RecruiterOnboardingApplication` | `organization` — appartenir à | `RecruiterOrganization` | ◆-- | pivot `recruiter_organization_id` |
| - | `RecruiterOnboardingApplication` | `reviewer` — appartenir à | `User` | ◆-- | pivot `reviewed_by` |
| - | `RecruiterOnboardingApplication` | `documents` — posséder plusieurs | `RecruiterOnboardingDocument` | 1--* |  |
| - | `RecruiterOnboardingDocument` | `application` — appartenir à | `RecruiterOnboardingApplication` | ◆-- | pivot `recruiter_onboarding_application_id` |
| - | `RecruiterOrganization` | `company` — appartenir à | `Company` | ◆-- |  |
| - | `RecruiterOrganization` | `members` — être lié à plusieurs | `User` | *--* | pivot `recruiter_organization_user` |
| - | `RecruiterOrganization` | `assignments` — posséder plusieurs | `RecruiterProfileAssignment` | 1--* |  |
| - | `RecruiterOrganization` | `offerSubmissions` — posséder plusieurs | `RecruiterOfferSubmission` | 1--* |  |
| - | `RecruiterOrganization` | `onboardingApplication` — posséder un | `RecruiterOnboardingApplication` | 1--1 | pivot `recruiter_organization_id` |
| - | `RecruiterProfileAssignment` | `organization` — appartenir à | `RecruiterOrganization` | ◆-- | pivot `recruiter_organization_id` |
| - | `RecruiterProfileAssignment` | `candidate` — appartenir à | `User` | ◆-- | pivot `candidate_user_id` |
| - | `RecruiterProfileAssignment` | `assignedBy` — appartenir à | `User` | ◆-- | pivot `assigned_by_user_id` |

### Workflow


| Type | Classe | Relation | Cible | Multiplicité | Notes |
|------|--------|----------|-------|--------------|-------|
| - | `ProcessFlow` | `sections` — posséder plusieurs | `ProcessFlowSection` | 1--* |  |
| - | `ProcessFlow` | `steps` — posséder plusieurs | `ProcessStep` | 1--* |  |
| - | `ProcessFlow` | `program` — appartenir à | `Program` | ◆-- |  |
| - | `ProcessFlow` | `offer` — appartenir à | `Offer` | ◆-- |  |
| - | `ProcessFlow` | `country` — appartenir à | `Country` | ◆-- |  |
| - | `ProcessFlowSection` | `processFlow` — appartenir à | `ProcessFlow` | ◆-- |  |
| - | `ProcessFlowSection` | `steps` — posséder plusieurs | `ProcessStep` | 1--* |  |
| - | `ProcessStep` | `processFlow` — appartenir à | `ProcessFlow` | ◆-- |  |
| - | `ProcessStep` | `section` — appartenir à | `ProcessFlowSection` | ◆-- | pivot `process_flow_section_id` |

### Autres énumérations sans relation directe

Ces classes sont des tables de référence / énumérations du domaine mais n'apparaissent pas dans les relations ci-dessus car elles sont uniquement référencées par clé étrangère sans relation inverse déclarée.

| Package | Classe | Table |
|---------|--------|-------|
| Location | `LanguageLevel` | `language_levels` |
 
## API v1 — aperçu

Préfixe : **`/api/v1`**. Routes publiques (auth, catalogue public) puis groupe **`auth:sanctum`**.

| Module | Préfixe / exemples | Rôle |
|--------|-------------------|------|
| Auth | `POST /login`, `/register`, `/me`, 2FA | Sessions token |
| Identity (candidat) | `PATCH /me/profile/steps/{step}` | Wizard profil (`personal`, `contact`, `professional`, `documents`) |
| Identity (admin) | `GET/POST /identity/admin/users`, `PATCH …/profile/approval` | Gestion utilisateurs & validation profil |
| Documents | `GET/POST /documents`, `POST …/validate` | Pièces justificatives |
| Catalog | `/catalog/admin/offers`, `/public/offers` | Offres & programmes |
| Catalog (trainings) | `/catalog/admin/trainings` | Formations JBIS (staff) |
| Recruiter | `/recruiter/profile-requests`, `/recruiter/assignments/…/feedback` | Portail recruteur |
| Recruiter (admin) | `/identity/admin/recruiter-profile-requests` | Demandes de profils (staff) |
| Partner | `/partner/cohorts`, `/partner/dashboard` | Portail établissement (stages) |
| Partner (admin) | `/identity/admin/partner-cohorts` | Cohortes partenaires (staff) |
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
