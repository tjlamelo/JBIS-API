# Audit de normalisation — jbis-api

Audit théorique du schéma relationnel (migrations `database/migrations/` + relations Eloquent `app/Core/Domain/**/Models`), appliquant la théorie des dépendances fonctionnelles (DF) et des formes normales 1FN → BCKFN (4FN/5FN évoquées quand pertinent).

## Méthodologie

- Extraction exhaustive de toutes les tables métier créées par `Schema::create`, avec PK, colonnes, `unique()`, `foreign()`.
- Les DF sont établies dans cet ordre de fiabilité : (1) contraintes `unique()` explicites, (2) FK (jamais traitées comme violation en soi), (3) sémantique du nom de colonne (marquée *DF implicite à vérifier* si non déclarée en base).
- Pour chaque table, le graphe des DF retenues (attributs = sommets, DF élémentaires = arcs) est vérifié explicitement pour détecter d'éventuels cycles nontriviaux (`X → Y` et `Y → X` avec `X ≠ Y`), classés soit **cas bénin** (clés candidates synonymes de la même entité, ex. `id ↔ slug`), soit **cas suspect** (deux colonnes non-clé qui s'auto-déterminent, symptôme d'un encodage redondant à fusionner).
- Pour chaque violation de forme normale identifiée, les quatre anomalies classiques du cours (redondance, insertion, suppression, mise à jour) sont documentées explicitement table par table — pas seulement dans le Top 5 final. Quand une case ne s'applique pas (ex. dénormalisation en écriture unique jamais resynchronisée), c'est dit explicitement avec justification, plutôt qu'omis.
- Tables techniques Laravel pures ignorées : `cache`, `jobs`, `failed_jobs`, `personal_access_tokens`, `password_reset_tokens` (mentionnée brièvement car triviale), `sessions` (idem).
- Tables vendor (`permissions`, `roles`, `model_has_*`, `role_has_permissions` de Spatie ; `audits` de owen-it/laravel-auditing) : schéma standard bien connu, traité brièvement.
- Tables de lookup à 2-4 colonnes évidentes (`id`, `name`/`code`, `slug`, `is_active`) : traitées collectivement en Partie A.
- Tables pivots pures sans attribut métier (juste 2 FK) : mentionnées seulement si elles posent un problème de clé.

**Remarque générale (à ne pas répéter table par table) :** de nombreuses colonnes `name`, `title`, `description`, `label` sont typées `json` et contiennent des traductions `{"fr": "...", "en": "..."}`. Ce n'est **pas** une violation de 1FN : c'est un choix de modélisation assumé pour l'i18n (value object), cohérent avec `spatie/laravel-translatable` et les colonnes générées `*_fr`/`*_en` (`STORED GENERATED`) qui servent à l'indexation FULLTEXT. Ces colonnes générées ne sont pas non plus des violations : elles sont mathématiquement déterminées par la colonne JSON source (DF triviale par construction), et créent parfois un cycle bénin `id ↔ slug_fr` (clés synonymes), noté au cas par cas.

---

## Partie A — Tables de référence (lookup)

`categories`, `contract_types`, `benefits`, `education_levels`, `work_schedules`, `offer_types`, `skill_categories`, `discovery_sources`, `language_levels`, `languages`, `geographic_zones`, `required_documents`, `document_types`, `trainings`, `certification_offers`, `countries`.

**Clé(s) candidate(s)** : `id` (surrogate) + une clé métier secondaire (`slug`, `code` ou `key`) déclarée `unique()`.
**DF élémentaires retenues** : `id → *` (toutes les colonnes) ; `slug/code/key → *` (DF certaine, contrainte unique en base).

**Graphe des DF (racines / cycles)** : deux racines par table — `id` et `slug`/`code`/`key` — qui se déterminent mutuellement (`id → slug` car `id` est clé, `slug → id` car `slug` est `unique()`). **Cycle bénin** : ce sont deux clés candidates synonymes de la même entité, pas une redondance suspecte. Aucune 3ᵉ racine, aucun cycle entre attributs non-clé.

- **1FN** : ✅ — colonnes atomiques (JSON = i18n assumé ; `document_types.allowed_extensions`/`allowed_mime_types` = listes de règles de validation techniques, cas limite toléré, aucune anomalie observée).
- **2FN** : ✅ trivial — clé primaire simple (`id`), aucune dépendance partielle possible.
- **3FN** : ✅ — aucun attribut non-clé ne détermine un autre attribut non-clé.
- **BCKFN** : ✅ — les seuls déterminants (`id`, `slug`/`code`/`key`) sont des clés candidates.
- **Recommandation** : RAS pour l'ensemble de ce groupe.

---

## Partie B — Identité & Utilisateurs

### Table : users

**Clé(s) candidate(s)** : `id` (PK) ; `email` (unique) ; `phone_number1` (unique, nullable).
**DF élémentaires retenues** : `id → name, email, phone_number1, password, active, auth_provider, two_factor_*` ; `email → id → (tout)` ; `phone_number1 → id → (tout)`.

**Graphe des DF (racines / cycles)** : **3 racines indépendantes** — `id`, `email`, `phone_number1` — qui se déterminent toutes mutuellement deux à deux (`id↔email`, `id↔phone_number1`, `email↔phone_number1`). **Cycle bénin multi-racines** : ce sont trois clés candidates synonymes de la même entité *utilisateur*, pas une erreur. À documenter (plusieurs points d'entrée possibles pour identifier un utilisateur), mais pas une violation.

#### 1FN
✅ — toutes les colonnes sont atomiques et monovaluées.

#### 2FN
✅ trivial — clé primaire simple (`id`), pas de dépendance partielle possible.

#### 3FN
✅ — aucun attribut non-clé ne détermine un autre attribut non-clé (`auth_provider` ne détermine rien d'autre).

#### BCKFN
✅ — déterminants = `id`, `email`, `phone_number1`, tous clés candidates.

#### Recommandation
RAS sur cette table isolée. **Mais voir Partie K.1 — "Groupe répétitif des numéros de téléphone"** : `users.phone_number1` fait partie d'un pattern de répétition avec `user_profiles.phone_number2`/`phone_number3` qui viole 1FN à l'échelle du concept "numéros de téléphone d'un utilisateur" (anomalies détaillées en Partie K.1).

---

### Table : user_profiles

**Clé(s) candidate(s)** : `id` (PK) ; `user_id` (DF implicite forte, **non protégée par `unique()`**) ; `phone_number2` (unique, nullable) ; `phone_number3` (unique, nullable).
**DF élémentaires retenues** : `id → tout le reste` ; `user_id → tout le reste` (DF implicite à vérifier).

**Graphe des DF (racines / cycles)** : **4 racines candidates** — `id`, `user_id` (non enforced), `phone_number2`, `phone_number3` — toutes synonymes en théorie (déterminent chacune tout le reste). **Cycle bénin sous réserve** : le cycle `id ↔ user_id` n'est pas garanti par le SGBD tant que `unique('user_id')` n'est pas ajouté (cf. recommandation ci-dessous) — c'est une DF *voulue* mais non *prouvée* par une contrainte, à distinguer d'un cycle bénin pleinement établi comme `id ↔ phone_number2`.

#### 1FN
❌ — `pictures` est une colonne `json` contenant une **liste** de photos (commentaire migration : "max 3 entrées"). Attribut multivalué stocké dans une seule colonne → violation classique de 1FN.

#### 2FN
✅ trivial — clé primaire simple (`id`), pas de dépendance partielle possible.

#### 3FN
✅ — `nationality_country_id`, `highest_education_level_id`, `agency_id` sont des FK indépendantes, aucune DF transitive interne détectée.

#### BCKFN
✅ sous réserve de la 1FN.

#### Anomalies concrètes induites par cette violation (`pictures` JSON list)
- **Redondance** : pas de duplication inter-lignes classique ; le symptôme ici est la concaténation de plusieurs faits indépendants (une photo = un fichier avec son propre ordre d'affichage) dans une seule valeur atomique, sans clé propre par photo.
- **Anomalie d'insertion** : impossible d'ajouter une 4ᵉ photo via un `INSERT` ciblé — il faut lire-modifier-réécrire tout le tableau JSON applicativement ; rien n'empêche non plus d'insérer plus de 3 entrées malgré la règle métier commentée (aucune contrainte SQL ne la fait respecter).
- **Anomalie de suppression** : supprimer une photo précise nécessite de réécrire l'intégralité du JSON (pas de `DELETE` ciblé sur un élément), avec un risque d'erreur applicative pouvant effacer d'autres photos par erreur.
- **Anomalie de mise à jour** : **ne s'applique pas** au sens classique — chaque ligne `user_profiles` porte sa propre copie unique de `pictures`, il n'y a pas de copie dupliquée ailleurs à resynchroniser. Le problème est structurel (atomicité), pas un problème de synchronisation multi-copies.

#### Recommandation
1. Décomposer en `user_profile_pictures(id, user_profile_id, path, sort_order)`. Décomposition **sans perte** : `user_profile_id` est FK vers `user_profiles.id` (clé de `user_profiles`), la jointure restitue l'union sans duplication ni perte. **Préserve les DF** : aucune DF de `user_profiles` n'est perdue, `pictures` n'en portait aucune.
2. Ajouter `unique('user_id')` en base pour transformer le cycle `id ↔ user_id` de "voulu" en "garanti".

---

### Table : user_settings

**Clé(s) candidate(s)** : `id` (PK) ; `user_id` (unique).
**DF élémentaires retenues** : `user_id → language, theme, timezone, notifications, privacy, marketing`.

**Graphe des DF** : 2 racines synonymes `id ↔ user_id` — cycle bénin garanti par `unique()`. Pas de 3ᵉ racine.

#### 1FN
✅ — `notifications`/`privacy`/`marketing` sont des objets de préférence structurés (clé/valeur fixe), value object assumé, pas une violation.

#### 2FN / 3FN / BCKFN
✅ trivial — clé simple, pas de DF interne entre `language`, `theme`, `timezone`.

#### Recommandation
RAS.

---

### Table : user_documents

**Clé(s) candidate(s)** : `id` (PK) — seule racine, aucune autre colonne `unique()`.
**DF élémentaires retenues** : `id → user_id, document_type_id, file_path, issue_date, expiry_date, status, ...` ; `expiry_date → is_expired` (colonne générée `VIRTUAL`, DF triviale par construction).

**Graphe des DF** : racine unique `id`, pas de cycle. Arc dérivé non-cyclique `expiry_date → is_expired` (un seul sens, `is_expired` ne détermine pas `expiry_date`).

#### 1FN / 2FN / 3FN / BCKFN
✅ — atomique, clé simple, `document_number`/`issue_date`/`expiry_date` dépendent du document lui-même, pas de DF transitive via `document_type_id`.

#### Recommandation
RAS. Point hors-normalisation : `issuing_country_id` sans `onDelete` explicite.

---

### Table : experiences / education

**Clé(s) candidate(s)** : `id` (PK) — seule racine pour chacune.
**DF élémentaires retenues** : `id → user_id, job_title/degree, company_name/institution_name, country_id, start_date, end_date, status/is_approved`.

**Graphe des DF** : racine unique `id` pour les deux tables, pas de cycle.

#### 1FN / 2FN / 3FN / BCKFN
✅ — `city_name`/`residence_city` sont des chaînes libres indépendantes de `country_id`, pas de DF transitive (pas de FK vers `cities`).

#### Recommandation
RAS.

---

### Table : user_skills

**Clé(s) candidate(s)** : `id` (PK) uniquement. **Aucune `unique()` sur `(user_id, skill_id)`**.
**DF élémentaires retenues** : `(user_id, skill_id) → level, years_of_experience` — DF voulue, non enforced.

**Graphe des DF** : racine unique formelle `id`. La paire `(user_id, skill_id)` n'est **pas** une racine démontrable tant qu'elle n'est pas déclarée `unique()` — pas de cycle observable pour l'instant, mais c'est précisément le symptôme du problème : le graphe ne peut pas prouver que `(user_id, skill_id) → level` tient dans toutes les extensions de la relation.

#### 1FN
✅ — atomique.

#### 2FN
✅ trivial sur la clé déclarée `id`. **Mais** la clé candidate *voulue* `(user_id, skill_id)` n'est pas déclarée → rien n'empêche `MG=(user_id,skill_id) → MD=level` d'être violée par des doublons contradictoires.

#### 3FN / BCKFN
Non applicable formellement (seule clé déclarée = `id`), mais la DF `(user_id, skill_id) → level` n'est **pas garantie par le schéma**.

#### Anomalies concrètes induites par cette violation
- **Redondance** : la paire `(user_id, skill_id)` peut être physiquement dupliquée sur plusieurs lignes avec des valeurs `level` différentes et contradictoires.
- **Anomalie d'insertion** : aucune insertion n'est *bloquée* — c'est justement le problème : le SGBD accepte silencieusement `(user=7, skill=3, level=BEGINNER)` puis `(user=7, skill=3, level=EXPERT)` sans erreur.
- **Anomalie de suppression** : supprimer une des lignes dupliquées ne résout pas l'ambiguïté si une autre ligne contradictoire subsiste — aucune information *indépendante* n'est perdue, mais l'incohérence n'est pas non plus corrigée.
- **Anomalie de mise à jour** : corriger le niveau d'un utilisateur pour une compétence nécessite de savoir combien de lignes dupliquées existent et de toutes les mettre à jour ; en oublier une laisse deux niveaux contradictoires coexister pour la même paire.

#### Recommandation
Ajouter `unique(['user_id','skill_id'])`. Sans perte, préserve toutes les DF existantes, rend la clé candidate réelle démontrable (élimine l'anomalie).

---

### Table : offer_skill

Même diagnostic que `user_skills` : **aucune `unique()` sur `(offer_id, skill_id)`**.

**Graphe des DF** : racine unique formelle `id`, même situation dégénérée que `user_skills` pour la paire `(offer_id, skill_id)`.

#### Anomalies concrètes induites par cette violation
Identiques à `user_skills` (redondance de la paire avec `level` contradictoire, insertion non bloquée, suppression ne résolvant pas l'ambiguïté, mise à jour risquant l'oubli d'une ligne dupliquée).

#### Recommandation
Ajouter `unique(['offer_id','skill_id'])`.

---

### Table : user_languages

**Clé(s) candidate(s) déclarée(s)** : `unique(['user_id','language_id','language_level_id'])`.
**DF élémentaires retenues** : `(user_id, language_id) → language_level_id` — DF métier attendue mais **non celle réellement enforced**.

**Graphe des DF** : 2 racines formelles `id ↔ (user_id, language_id, language_level_id)` — cycle bénin sur la clé *déclarée*. **Mais** ce n'est pas la clé *voulue* : en incluant `language_level_id` dans la clé plutôt que de le traiter comme un attribut déterminé par `(user_id, language_id)`, le graphe masque l'absence de l'arc `(user_id, language_id) → language_level_id` qui devrait exister.

#### 1FN
✅.

#### 2FN
Sur la clé *déclarée* `(user_id, language_id, language_level_id)`, pas de DF partielle (`is_approved`, `approved_by`, `reviewed_at` dépendent bien de la clé complète).

#### 3FN / BCKFN
✅ formellement sur la clé déclarée.

#### ⚠️ Défaut de modélisation (clé candidate incorrecte, pas une violation de forme normale au sens strict)
Le schéma autorise deux lignes `(user=7, FR, B2)` et `(user=7, FR, C1)` **simultanément**, sans mécanisme pour distinguer laquelle est "active".

#### Anomalies concrètes induites par cette violation
- **Redondance** : `(user_id, language_id)` réapparaît sur plusieurs lignes avec des `language_level_id` différents, chaque ligne réaffirmant "cet utilisateur parle cette langue" avec un niveau distinct, sans distinction current/historique.
- **Anomalie d'insertion** : pas d'insertion techniquement bloquée, mais il n'existe pas d'opération "mettre à jour le niveau" propre — on ne peut qu'ajouter une ligne de plus, créant directement l'ambiguïté.
- **Anomalie de suppression** : supprimer l'ancienne ligne (ancien niveau) pour ne garder que le niveau actuel est une opération manuelle non garantie par le schéma — si elle est oubliée, les deux niveaux cohabitent indéfiniment.
- **Anomalie de mise à jour** : ce qui devrait être un `UPDATE` (changer le niveau d'un utilisateur pour une langue) devient un `INSERT` + oubli potentiel du `DELETE` de l'ancienne ligne — signe direct que la DF `(user_id, language_id) → language_level_id` n'est pas modélisée comme telle.

#### Recommandation
Remplacer `unique(['user_id','language_id','language_level_id'])` par `unique(['user_id','language_id'])` si un seul niveau courant est voulu. Décomposition **sans perte**, **préserve les DF**. Si l'historique est un besoin réel, ajouter `is_current`/`recorded_at` et documenter `(user_id, language_id, is_current=true)` comme clé fonctionnelle métier.

---

### Table : user_trade / user_sector / user_preferred_countries / user_permission_overrides / user_devices

Déclarent correctement leur clé composite via `unique()` : `(user_id, trade_id)`, `(user_id, category_id)`, `(user_id, country_id)`, `(user_id, permission_name)`, `(user_id, device_key)`.

**Graphe des DF** : pour chacune, 2 racines synonymes `id ↔ (composite)` — cycle bénin garanti par `unique()`.

#### 1FN / 2FN / 3FN / BCKFN
✅ pour les cinq tables — pas de dépendance partielle (`priority`, `years_of_experience`, etc. dépendent bien de la paire complète).

#### Point de vigilance spécifique à `user_devices` — pas un cycle, mais un déterminant hors-clé à documenter
`risk_score` (0-100) et `risk_level` (`low`/`medium`/`high`) : `risk_score → risk_level` est plausible (bucketing par seuils), mais **`risk_level → risk_score` ne tient pas** (un niveau correspond à une plage de scores, pas à une valeur unique) → **pas de cycle**, seulement un arc à sens unique entre deux attributs non-clé. Formellement, `MG=risk_score → MD=risk_level` avec `MG ≠ clé` : un déterminant non-clé, symptôme théorique d'un écart à la BCKFN, mais **traité comme dénormalisation assumée (cache de classification)** plutôt qu'une violation à corriger — évite de recalculer le seuillage à chaque requête de filtrage (`WHERE risk_level = 'high'`). Risque documenté : si la logique de seuillage change un jour, les lignes existantes garderont un `risk_level` obsolète tant qu'un recalcul explicite n'est pas exécuté (anomalie de mise à jour potentielle, non vérifiable dans le périmètre audité).

#### Recommandation
RAS pour la normalisation — ces cinq tables sont un bon contre-exemple montrant que l'oubli sur `user_skills`/`offer_skill`/`user_languages` est localisé, pas systémique. Documenter le mécanisme de recalcul de `risk_level` s'il existe.

---

### Table : user_consents

**Clé(s) candidate(s)** : `id` (PK) seulement. Index `(user_id, type, version)` **non unique**.
**DF élémentaires retenues** : `(user_id, type, version) → accepted_at, ip_address, user_agent` — DF implicite plausible mais non enforced.

**Graphe des DF** : racine unique `id`, pas de cycle (la combinaison `(user_id, type, version)` n'est pas prouvée comme clé).

#### 2FN / 3FN / BCKFN
Non violées au sens strict, mais l'absence de contrainte unique autorise des doublons (double consentement identique enregistré deux fois).

#### Anomalies concrètes
- **Redondance** : un même triplet `(user_id, type, version)` peut apparaître deux fois avec le même `accepted_at` — bruit, pas d'incohérence de fait puisque chaque ligne représente un *événement* horodaté distinct par nature (modèle "log"), pas un état.
- **Insertion / Suppression / Mise à jour** : **ne s'appliquent pas** au sens classique — un log d'événements n'a pas vocation à être mis à jour ni à représenter un état unique par clé ; le modèle "log" est cohérent ici, contrairement à `user_languages` qui modélise un état.

#### Recommandation
RAS sur la normalisation.

---

### Table : user_visa_histories / user_notes / user_security_events / user_document_extractions / user_certifications / user_trainings / internships / archives / interests_and_hobbies / appointments / legal_documents

**Clé(s) candidate(s)** : `id` (PK) pour toutes. `legal_documents` a en plus `unique(['type','version'])`. `appointments` a `unique(['agency_id','scheduled_at'])`.

**Graphe des DF** : racine unique `id` pour la plupart (pas de cycle) ; **2 racines synonymes** `id ↔ (type,version)` pour `legal_documents` et `id ↔ (agency_id,scheduled_at)` pour `appointments` (cycles bénins garantis par `unique()`).

#### 1FN / 2FN / 3FN / BCKFN
✅ pour l'ensemble — clés simples, pas de dépendance partielle ni transitive interne détectée.

#### Table : user_document_extractions — point de vigilance factuel (transitif, pas un cycle)
`document_type_code` (string) duplique une information atteignable via `user_document_id → user_documents.document_type_id → document_types.code`, sans FK. Un seul sens (`user_document_id → document_type_code`), pas de retour possible → pas de cycle, DF transitive assumée comme snapshot.

##### Anomalies concrètes induites (dénormalisation assumée)
- **Redondance** : `document_type_code` recopie une valeur atteignable par 2 jointures.
- **Anomalie d'insertion** : aucune — valeur copiée une seule fois à la création de l'extraction OCR.
- **Anomalie de suppression** : aucune — indépendant du cycle de vie de `document_types`.
- **Anomalie de mise à jour** : **risque réel** — si `document_types.code` est renommé après coup (ex. correction d'une typo), `user_document_extractions.document_type_code` **ne sera pas mis à jour automatiquement** (aucune FK, aucun trigger trouvé) → dérive silencieuse possible. Contrairement aux snapshots de `applications`/`application_steps` (Partie E), ce champ `code` a une probabilité réaliste d'être corrigé a posteriori, donc ce risque est **plus concret**, pas seulement théorique.

#### Recommandation
RAS pour la normalisation stricte du reste du groupe. Documenter l'intention snapshot de `document_type_code` ou le retirer au profit d'un `JOIN`.

---

## Partie C — Catalogue (offres, entreprises, compétences, programmes)

### Table : companies

**Clé(s) candidate(s)** : `id` (PK) ; `slug` (unique) ; `email` (unique, nullable).
**DF élémentaires retenues** : `slug → name, category_id, country_id, city_id, type, status, ...`.

**Graphe des DF** : **3 racines synonymes** `id ↔ slug ↔ email` — cycle bénin (trois clés candidates de la même entité Company), pas de redondance suspecte entre elles (ce ne sont pas deux encodages de la même info, mais trois identifiants indépendants légitimes).

#### 1FN / 2FN
✅.

#### 3FN
✅ — **vérifié explicitement** : `city_id` et `country_id` sont deux FK indépendantes, sans DF interne déclarée entre elles (pas de `CHECK` garantissant `city_id.region.country_id = country_id`). Ce n'est **pas** l'anti-pattern "pays dupliqué via city→region→country" : ce sont deux attributs métier indépendants du point de vue du schéma.

#### BCKFN
✅ — déterminants = `id`, `slug`, `email`.

#### Recommandation
RAS pour la normalisation. Hors-sujet : envisager un `CHECK`/validation applicative pour la cohérence géographique si c'est un invariant métier voulu.

---

### Table : offers

**Clé(s) candidate(s)** : `id` (PK) ; `slug_fr` (unique, colonne générée).
**DF élémentaires retenues** : `id → tout le reste` ; `slug_fr → id → tout le reste`.

**Graphe des DF** : 2 racines synonymes `id ↔ slug_fr` — cycle bénin (colonne générée mais unique, donc réellement déterminante). Pas de 3ᵉ racine.

#### 1FN
✅ — `responsibilities`, `requirements`, `photo_media` = value objects de contenu éditorial, pas des FK déguisées (contrairement à `document_type_ids`, Partie E).

#### 2FN / BCKFN
✅.

#### 3FN
✅ — **vérifié explicitement**, même raisonnement que `companies` : `city_id`/`country_id` indépendants de `company_id`, légitime (poste localisé différemment du siège). Pas de DF transitive `company_id → country_id` imposée (deux offres de la même entreprise peuvent avoir des `country_id` différents).

#### Recommandation
RAS.

---

### Table : trades

**Clé(s) candidate(s)** : `id` (PK) ; `(category_id, slug)` composite (unique — `slug` seul n'est **pas** unique globalement).
**DF élémentaires retenues** : `(category_id, slug) → name, is_active`.

**Graphe des DF** : 2 racines synonymes `id ↔ (category_id, slug)` — cycle bénin.

#### 1FN
✅.

#### 2FN
Clé primaire réelle `id` (simple) → ✅ trivial. Sur la clé candidate composite : aucun attribut ne dépend uniquement de `category_id` seul ni de `slug` seul → pas de DF partielle.

#### 3FN / BCKFN
✅.

#### Recommandation
RAS — le scoping de `slug` par `category_id` est un choix assumé, pas une violation.

---

### Table : programs

**Clé(s) candidate(s)** : `id` (PK) seul (pas de `unique()` sur `slug`, contrairement à `offers.slug_fr`).
**DF élémentaires retenues** : `id → tout le reste`.

**Graphe des DF** : racine unique `id`, pas de cycle — moins de racines que `offers`/`trades` malgré une structure similaire, incohérence de modélisation à signaler (hors-sujet normalisation stricte).

#### 1FN / 2FN / 3FN / BCKFN
✅.

#### Recommandation
RAS pour la normalisation stricte. Point de cohérence : ajouter `unique()` sur la colonne générée `slug_fr` par cohérence avec `offers`.

---

### Table : skills

**Clé(s) candidate(s)** : `id` (PK) ; `slug` (unique).
**DF élémentaires retenues** : `slug → name, skill_category_id`.

**Graphe des DF** : 2 racines synonymes `id ↔ slug` — cycle bénin. **Pas de cycle** entre `skill_category_id` et `category_id` : ce sont deux FK indépendantes vers deux tables cibles différentes (`skill_categories` et `categories`), aucune des deux ne détermine l'autre — pure ambiguïté sémantique de classification, pas une DF suspecte au sens de l'étape 3bis (il n'existe aucun arc démontrable entre elles dans un sens ou dans l'autre).

#### 1FN / 2FN / 3FN / BCKFN
✅.

#### Recommandation
RAS pour la normalisation. Vérifier si `category_id` est un champ legacy à supprimer (hors théorie des DF).

---

### Table : agencies

**Clé(s) candidate(s)** : `id` (PK) ; `slug` (unique) ; `email` (unique).
**DF élémentaires retenues** : `slug → tout le reste`.

**Graphe des DF** : 3 racines synonymes `id ↔ slug ↔ email` — cycle bénin.

#### 1FN
❌ — `phones`/`whatsapp_numbers` = colonnes `json` contenant des **listes** de numéros. Attribut multivalué dans une seule colonne → violation 1FN.

#### 2FN / 3FN / BCKFN
✅ par ailleurs.

#### Anomalies concrètes induites par cette violation
- **Redondance** : ne s'applique pas au sens inter-lignes (une seule valeur JSON par agence) — le problème est l'agrégation de plusieurs faits indépendants (chaque numéro) dans une valeur atomique unique.
- **Anomalie d'insertion** : ajouter un numéro nécessite une réécriture complète du tableau JSON, et rien n'empêche d'en insérer un nombre illimité (pas de contrainte).
- **Anomalie de suppression** : retirer un numéro précis nécessite de réécrire tout le JSON — risque d'erreur applicative.
- **Anomalie de mise à jour** : **ne s'applique pas** — pas de copies multiples à resynchroniser, chaque agence a sa propre valeur unique.

#### Recommandation
Décomposer en `agency_phones(id, agency_id, phone_number, type)`. Sans perte (`agency_id` FK vers clé de `agencies`), préserve les DF.

---

### Table : offer_required_document / program_required_document

**Clé(s) candidate(s)** : `(offer_id, required_document_id)` / `(program_id, required_document_id)`, déclarées `unique()`, + `id`.
**DF élémentaires retenues** : `(offer_id, required_document_id) → is_mandatory, sort_order`.

**Graphe des DF** : 2 racines synonymes par table — cycle bénin.

#### 1FN / 2FN / 3FN / BCKFN
✅ — clé composite complète, `sort_order` dépend bien de la paire complète.

#### Recommandation
RAS.

---

### Table : benefit_offer

Pivot pur, **sans `unique()` ni PK propre déclarée**.

**Graphe des DF** : situation dégénérée — aucune clé fonctionnelle démontrable avec le schéma actuel (absence totale de contrainte d'unicité), donc aucun graphe de DF exploitable tant que la contrainte n'est pas ajoutée.

#### Anomalies concrètes
- **Redondance** : la paire `(offer_id, benefit_id)` peut être insérée plusieurs fois de façon strictement identique — redondance pure sans même valeur contradictoire possible (pas d'attribut sur ce pivot).
- **Anomalie d'insertion** : aucune bloquée — un doublon exact peut être inséré sans erreur.
- **Anomalie de suppression** : supprimer un doublon ne supprime pas l'autre.
- **Anomalie de mise à jour** : **ne s'applique pas** — aucun attribut à mettre à jour sur ce pivot pur.

#### Recommandation
`unique(['offer_id','benefit_id'])` ou `primary(['offer_id','benefit_id'])`. Priorité basse (pas de perte d'information, juste du bruit).

---

## Partie D — Localisation

### Tables : countries → regions → cities → geographic_zones

**Clé(s) candidate(s)** : `id` (PK) + `code`/`slug` unique à chaque niveau.
**DF élémentaires retenues** : `regions.slug → country_id, name` ; `cities.slug → region_id, name, zip_code`.

**Graphe des DF** : 2 racines synonymes `id ↔ slug`/`code` par table — cycle bénin. Pas de cycle entre `country_id`/`region_id`/`city_id` de tables différentes (relation hiérarchique à sens unique, chaque enfant ne référence que son parent immédiat).

#### 1FN / 2FN / 3FN / BCKFN
✅ pour les quatre tables — hiérarchie correctement normalisée : chaque niveau ne stocke que sa propre FK vers le parent immédiat, **aucune redondance transitive** (contrairement à l'anti-pattern `city_id → region_id → country_id` stocké en clair cité dans la consigne).

#### Recommandation
RAS — modélisation exemplaire.

---

## Partie E — Candidature / Workflow

### Table : process_flows

**Clé(s) candidate(s)** : `id` (PK) ; `(flow_group_id, version)` composite (unique).
**DF élémentaires retenues** : `(flow_group_id, version) → status, name, program_id, offer_id, country_id, ...`.

**Graphe des DF** : 2 racines synonymes — cycle bénin.

#### 1FN / 2FN / 3FN / BCKFN
✅.

#### Recommandation
RAS.

---

### Table : process_steps

**Clé(s) candidate(s)** : `id` (PK) — seule racine.
**DF élémentaires retenues** : `id → step_type, payment_type, title, ...`.

**Graphe des DF** : racine unique `id`, pas de cycle.

#### 1FN
❌ — `document_type_ids` = colonne `json` contenant une **liste d'identifiants de FK** vers `document_types`. Violation 1FN la plus nette du schéma : attribut multivalué **et** pseudo-FK non déclarée.

#### 2FN / 3FN / BCKFN
✅ par ailleurs sur `id`.

#### Anomalies concrètes induites par cette violation
- **Redondance** : chaque `document_type_id` référencé est dupliqué "en dur" dans le JSON de chaque étape qui l'exige, sans point central (pas de `ON UPDATE CASCADE` possible).
- **Anomalie d'insertion** : aucune insertion bloquée même avec un ID de `document_type` inexistant (pas de FK) — données invalides silencieusement acceptées.
- **Anomalie de suppression** : supprimer un `document_type` référencé dans `document_type_ids` ne déclenche ni `CASCADE` ni `RESTRICT` ni `SET NULL` — l'ID devient orphelin, invisible pour l'intégrité référentielle.
- **Anomalie de mise à jour** : retirer un type de document requis de toutes les étapes concernées nécessite de parcourir/réécrire le JSON de **chaque ligne individuellement** (pas d'`UPDATE` ensembliste possible), avec risque d'en oublier une.

#### Recommandation
Décomposer en `process_step_document_type(process_step_id, document_type_id)`. Sans perte (`process_step_id` FK vers clé de `process_steps`), préserve les DF, et permet enfin une vraie contrainte `foreign()` vers `document_types.id`.

---

### Table : application_steps

**Clé(s) candidate(s)** : `id` (PK) ; `(application_id, step_order)` (unique).

**Graphe des DF** : 2 racines synonymes `id ↔ (application_id, step_order)` — cycle bénin. Arc transitif à sens unique `application_step_id → process_step_id → {step_type, title, ...}` (pas un cycle, pas de retour).

#### 1FN
❌ — même violation `document_type_ids` que `process_steps`.

#### Anomalies concrètes induites (`document_type_ids`)
Identiques à `process_steps` (redondance sans point central, insertion non contrôlée, suppression sans intégrité référentielle, mise à jour nécessitant un parcours ligne par ligne du JSON).

#### 3FN — point de vigilance factuel, pas une violation à corriger
`application_steps` duplique de nombreuses colonnes de `process_steps` (`step_type`, `title`, `description`, `is_blocking`, `is_required`, `requires_documents`). Formellement transitif : `application_id → process_step_id → step_type`, avec `process_step_id ∉ clé`. **Traité comme dénormalisation assumée (snapshot versionné)** : `process_flows` supporte plusieurs `version`, et `application_steps` doit rester figé sur l'état du parcours **au moment où le candidat l'a suivi**.

##### Anomalies concrètes induites par cette dénormalisation assumée
- **Redondance** : les colonnes de `process_steps` sont recopiées dans chaque `application_step` correspondante.
- **Anomalie d'insertion** : aucune — recopie unique à la création de l'`application_step`.
- **Anomalie de suppression** : aucune — l'objectif même de la dénormalisation est de protéger l'historique contre une suppression/modification future de `process_steps`.
- **Anomalie de mise à jour** : **ne s'applique pas par design** — ces colonnes ne sont pas censées être resynchronisées avec `process_steps` après création (instantané volontaire). Réserve : aucune preuve trouvée dans le code exploré d'un mécanisme empêchant une réécriture accidentelle a posteriori ; l'absence d'anomalie repose sur une convention applicative, pas sur une garantie SGBD.

#### Recommandation
Corriger uniquement `document_type_ids` (1FN). Documenter explicitement l'intention snapshot pour le reste.

---

### Table : applications

**Clé(s) candidate(s)** : `id` (PK) ; `application_number` (unique).
**DF élémentaires retenues** : `application_number → user_id, offer_id, process_flow_id, status, ...`.

**Graphe des DF** : 2 racines synonymes `id ↔ application_number` — cycle bénin. Arc transitif à sens unique `process_flow_id → {flow_group_id, process_flow_version}` (pas de retour, pas un cycle).

#### 1FN / 2FN
✅.

#### 3FN
❌ (cas limite, à documenter) — `flow_group_id`/`process_flow_version` transitivement déterminés par `process_flow_id` (FK vers `process_flows.id`, où `id → flow_group_id, version`). Formellement : X=`id` (clé), Y=`process_flow_id` (non-clé), Z=`{flow_group_id, process_flow_version}`. X → Y et Y → Z ⟹ X → Z, avec **Y ∉ clé**.

#### BCKFN
Même remarque.

#### Anomalies concrètes induites par cette dénormalisation assumée
- **Redondance** : `flow_group_id`/`process_flow_version` recopiés depuis `process_flows`, atteignable via `process_flow_id`.
- **Anomalie d'insertion** : aucune — copie unique à la création de la candidature.
- **Anomalie de suppression** : aucune — supprimer une candidature ne fait perdre aucune information indépendante sur `process_flows` (la dépendance va dans l'autre sens).
- **Anomalie de mise à jour** : **ne s'applique pas** — ces colonnes ne sont jamais réécrites après création (le workflow d'une candidature reste figé sur sa version d'origine par design), donc pas de risque de désynchronisation. Réserve identique à `application_steps` : garantie de convention applicative, pas de contrainte SGBD.

#### Recommandation
Dénormalisation assumée (cache anti-jointure), pas de correction urgente. Si audit strict voulu : supprimer ces deux colonnes et faire un `JOIN` — décomposition **sans perte** (`process_flow_id` déjà FK vers la clé de `process_flows`), **préserve les DF**.

---

### Table : payment_schedules / applications.total_due,total_paid / application_steps.amount_due,amount_paid

**Dénormalisation multi-niveaux assumée (cache d'agrégat)**, pas une violation de 3FN en soi.

#### Anomalies concrètes induites
- **Redondance** : le même agrégat "montant payé/dû" existe potentiellement à 3 niveaux (schedule / application / step), chacun recalculable depuis `payments`/`payment_installments`.
- **Anomalie d'insertion** : aucune — chaque total est initialisé à 0 et incrémenté par le code métier lors de chaque paiement.
- **Anomalie de suppression** : si un `payment` est supprimé/annulé, les 3 niveaux doivent être décrémentés en cascade — **risque réel non écarté** : aucune preuve trouvée dans le périmètre audité d'un recalcul cascadé automatique unique.
- **Anomalie de mise à jour** : à chaque paiement, potentiellement 3 lignes doivent être mises à jour de façon cohérente ; si le code n'écrit que 2 des 3 niveaux, une dérive silencieuse apparaît. **Signalé comme risque réel à vérifier côté code** (Actions/Observers), pas comme un fait confirmé.

#### Recommandation
Documenter et centraliser le point d'écriture unique si ce n'est pas déjà le cas.

---

### Table : application_documents

**Clé(s) candidate(s)** : `id` (PK) ; `(application_id, user_document_id)` (unique).

**Graphe des DF** : 2 racines synonymes — cycle bénin.

#### 1FN / 2FN / 3FN / BCKFN
✅ — `status` dépend bien de la paire complète (même document, statut différent selon la candidature).

#### Recommandation
RAS.

---

### Table : interviews

**Clé(s) candidate(s)** : `id` (PK) — seule racine.

**Graphe des DF** : racine unique, pas de cycle.

#### Point de vigilance factuel — pas une violation
`company_id` pourrait sembler transitivement dérivable via `application_id → offer_id → company_id`. **Vérifié comme non-violation** : un entretien peut être conduit par une entreprise partenaire différente de l'employeur officiel de l'offre — `company_id` n'est pas fonctionnellement déterminé par `application_id` dans ce contexte métier.

#### 1FN / 2FN / 3FN / BCKFN
✅.

#### Recommandation
RAS.

---

### Table : offer_language_course_requirements / candidate_language_courses

**Clé(s) candidate(s)** : `(offer_id, language_id)` (unique) pour la première ; `id` seul pour la seconde (pas d'unique sur `(user_id, offer_id, language_id)`, cohérent avec un modèle "log de suivi" où un candidat peut suivre plusieurs sessions).

**Graphe des DF** : 2 racines synonymes pour la première, racine unique pour la seconde.

#### 1FN / 2FN / 3FN / BCKFN
✅ pour les deux tables.

#### Recommandation
RAS.

---

## Partie F — Finance

### Table : payments / payment_installments

**Clé(s) candidate(s)** : `id` (PK) seul pour les deux — seule racine.

**Graphe des DF** : racine unique, pas de cycle. `user_id` sur `payments` est redondant avec `application.user_id` mais via une **DF inter-tables par FK**, pas une violation (conforme à la consigne : ne pas traiter les FK comme violation en soi).

#### 1FN / 2FN / 3FN / BCKFN
✅ pour les deux.

#### Recommandation
RAS.

---

## Partie G — Recruteurs (portail)

### Table : recruiter_organizations

**Clé(s) candidate(s)** : `id` (PK) ; `slug` (unique).

**Graphe des DF** : 2 racines synonymes — cycle bénin.

#### 1FN / 2FN / 3FN / BCKFN
✅.

#### Recommandation
RAS.

---

### Table : recruiter_organization_user

**Clé(s) candidate(s)** : `(recruiter_organization_id, user_id)` (unique) + `id`.

**Graphe des DF** : 2 racines synonymes — cycle bénin.

#### 1FN / 2FN / 3FN / BCKFN
✅ — `is_owner` dépend bien de la paire complète.

#### Recommandation
RAS.

---

### Table : recruiter_profile_assignments

**Clé(s) candidate(s)** : `id` (PK) seul. **Aucune `unique()`** sur `(recruiter_organization_id, candidate_user_id)`.

**Graphe des DF** : racine unique `id`, pas de cycle démontrable pour la paire.

#### Point de vigilance factuel — probable non-violation
Cohérent avec un modèle "historique d'assignations" (`assigned_at`/`revoked_at` suggèrent une réassignation possible dans le temps) : plusieurs lignes `(recruiter_organization_id, candidate_user_id)` sont légitimes, contrairement à `user_skills`/`offer_skill`.

#### 1FN / 2FN / 3FN / BCKFN
✅.

#### Recommandation
RAS pour la normalisation. Si "1 assignation active à la fois" est voulu : index partiel `unique(...) WHERE revoked_at IS NULL` (hors périmètre pur DF).

---

### Table : recruiter_onboarding_applications / recruiter_onboarding_documents / recruiter_offer_submissions

**Clé(s) candidate(s)** : `id` (PK) pour les trois — seule racine chacune.

**Graphe des DF** : racine unique, pas de cycle, pour les trois.

#### 1FN / 2FN / 3FN / BCKFN
✅ pour les trois.

#### Recommandation
RAS.

---

## Partie H — Communication

### Table : mail_campaigns / sms_campaigns

**Clé(s) candidate(s)** : `id` (PK) seul — seule racine.

**Graphe des DF** : racine unique, pas de cycle.

#### 1FN / 2FN / 3FN
✅.

#### Dénormalisation assumée (cache d'agrégat) — anomalies concrètes
- **Redondance** : `recipients_count`/`sent_count`/`failed_count` recalculables depuis `mail_dispatches`/`sms_dispatches` par `COUNT(*)`.
- **Anomalie d'insertion** : aucune — compteurs initialisés à 0, incrémentés au fil de l'envoi.
- **Anomalie de suppression** : si une ligne `mail_dispatches` est supprimée (rare, pas de soft-delete déclaré), le compteur ne serait pas automatiquement décrémenté — risque théorique, cas hors flux métier normal.
- **Anomalie de mise à jour** : chaque envoi individuel doit incrémenter le bon compteur de façon fiable — **signalé comme "à vérifier"** (mécanisme de recalcul non exploré dans ce périmètre), pas confirmé comme défaillant.

#### Recommandation
RAS pour la normalisation.

---

### Table : mail_dispatches / sms_dispatches

**Clé(s) candidate(s)** : `(mail_campaign_id, email)` / `(sms_campaign_id, phone_number)` (unique) + `id`.

**Graphe des DF** : 2 racines synonymes par table — cycle bénin.

#### 1FN / 2FN / 3FN / BCKFN
✅.

#### Recommandation
RAS.

---

### Table : newsletter_subscriptions

**Clé(s) candidate(s)** : `id` (PK) ; `email` (unique) ; `unsubscribe_token` (unique).

**Graphe des DF** : **3 racines synonymes** `id ↔ email ↔ unsubscribe_token` — cycle bénin.

#### 1FN / 2FN / 3FN / BCKFN
✅.

#### Recommandation
RAS. Remarque mineure hors-normalisation : `email` a un `index()` inline redondant avec sa contrainte `unique()`.

---

## Partie I — Réunions & tâches internes

### Table : meetings / assigned_tasks / daily_tasks

**Clé(s) candidate(s)** : `id` (PK) pour les trois — seule racine chacune.

**Graphe des DF** : racine unique, pas de cycle, pour les trois.

#### 1FN / 2FN / 3FN / BCKFN
✅ pour les trois.

#### Recommandation
RAS.

---

### Table : meeting_user / task_user

**Clé(s) candidate(s)** : `id` (PK) seul. **Aucune `unique()`** sur `(meeting_id, user_id)` / `(assigned_task_id, user_id)`.

**Graphe des DF** : racine unique formelle `id`, situation dégénérée pour la paire voulue, identique à `user_skills`/`offer_skill`.

#### 1FN
✅.

#### 2FN
Non pertinent formellement (clé déclarée = `id`), mais même symptôme que `user_skills`/`offer_skill` : la clé candidate réelle n'est pas protégée.

#### Anomalies concrètes induites par cette violation
- **Redondance** : la paire peut être dupliquée avec des valeurs `is_present`/`excuse_reason` contradictoires.
- **Anomalie d'insertion** : aucune bloquée — on peut enregistrer deux fois la présence du même utilisateur à la même réunion avec des valeurs différentes.
- **Anomalie de suppression** : supprimer une ligne dupliquée ne résout pas l'ambiguïté si une autre subsiste.
- **Anomalie de mise à jour** : corriger la présence d'un utilisateur nécessite de mettre à jour toutes les lignes dupliquées ; un oubli laisse une incohérence.

#### Recommandation
Ajouter `unique(['meeting_id','user_id'])` et `unique(['assigned_task_id','user_id'])`. Sans perte, préserve les DF.

---

## Partie J — Vendor (permissions, audit) & Analytics

### Spatie laravel-permission

Schéma vendor standard, conforme BCKFN par construction (clés composites explicites, `unique(name, guard_name)`). RAS, hors périmètre custom.

### `audits` (owen-it/laravel-auditing)

Table technique de journalisation polymorphique, assimilable aux tables techniques exclues. RAS.

### `ga4_daily_overview` / `ga4_daily_pages` / `ga4_daily_acquisition`

**Clé(s) candidate(s)** : `date` seul / `(date, page_path)` / `(date, source, medium, campaign)` — toutes `unique()`, + `id`.

**Graphe des DF** : 2 racines synonymes par table (`id ↔` clé métier) — cycle bénin.

#### 1FN / 2FN / 3FN / BCKFN
✅ pour les trois — pas de dépendance partielle sur les clés composites (`page_views` dépend bien de la paire complète).

#### Recommandation
RAS.

---

## Partie K — Cas particuliers transverses (synthèse)

### K.1 — Groupe répétitif : numéros de téléphone (violation 1FN conceptuelle)

`users.phone_number1` + `user_profiles.phone_number2` + `user_profiles.phone_number3` : trois colonnes de même nature réparties sur deux tables au lieu de `user_phones(user_id, phone_number, position/type)`.

#### Anomalies concrètes induites
- **Redondance** : le concept "numéro de téléphone" est dupliqué structurellement en 3 colonnes sur 2 tables, chaque colonne réservant de l'espace même si `NULL`.
- **Anomalie d'insertion** : impossible d'insérer un 4ᵉ numéro sans `ALTER TABLE` (ajout de `phone_number4`) — anomalie d'insertion structurelle classique.
- **Anomalie de suppression** : vider `phone_number2` pour un utilisateur qui avait aussi `phone_number3` pose la question de "décaler" `phone_number3` vers `phone_number2` — aucune règle SQL ne le fait, risque de perdre l'ordre de priorité.
- **Anomalie de mise à jour** : **ne s'applique pas** au sens "copies multiples à resynchroniser" (chaque table ne porte qu'une copie de chaque numéro) — le problème est structurel (insertion), pas un problème de synchronisation.

**Recommandation** : décomposer en `user_phones(id, user_id, phone_number, is_primary)`. Sans perte, préserve les DF.

### K.2 — Liste de FK en JSON (violation 1FN + intégrité référentielle cassée)

`process_steps.document_type_ids`, `application_steps.document_type_ids` — voir Partie E pour le détail des 4 anomalies. Cas le plus sévère de l'audit.

### K.3 — Listes de valeurs en JSON (violation 1FN, moindre gravité)

`agencies.phones`, `agencies.whatsapp_numbers`, `user_profiles.pictures` — voir Parties B/C pour le détail des 4 anomalies.

### K.4 — Clés candidates composites non protégées (DF voulue non enforced)

`user_skills`, `offer_skill`, `user_languages` (clé mal choisie), `meeting_user`, `task_user` — voir Parties B/I pour le détail des 4 anomalies de chacune.

### K.5 — Dépendances transitives via FK dupliquée (dénormalisations assumées, arcs à sens unique — pas des cycles)

`applications.flow_group_id`/`process_flow_version`, duplication `application_steps`↔`process_steps`, `user_document_extractions.document_type_code` — voir Parties B/E pour le détail des 4 anomalies de chacune. Aucune ne nécessite de correction urgente ; `document_type_code` est la plus à risque (probabilité réaliste de dérive si `document_types.code` est renommé).

### K.6 — Dénormalisation d'agrégats (cache assumé, à surveiller)

`programs.views_count`, `offers.views_count`, compteurs `mail_campaigns`/`sms_campaigns`, cumuls de paiement à 3 niveaux — voir Parties E/H. Seul point réellement à risque : la triplication du cumul de paiement (schedule/application/step).

### K.7 — Déterminant non-clé à sens unique (pas un cycle) : `risk_score → risk_level`

`user_devices` et `user_security_events` partagent le pattern `risk_score`(0-100)`→ risk_level`(catégorie). Un seul sens démontrable (bucketing), donc **pas de cycle** au sens de l'étape 3bis — traité comme dénormalisation assumée (cache de classification), avec un risque de dérive si le seuillage change sans recalcul rétroactif (voir Partie B).

### K.8 — Aucune dépendance multivaluée (4FN) ni dépendance de jointure évitable (5FN) détectée

Aucune table de configuration/preferences en JSON ne masque une vraie relation ternaire décomposable (`notifications`/`privacy`/`marketing`/`settings`/`targeting` = value objects à structure fixe). Les tables à 3+ FK examinées (`candidate_language_courses`, `offer_language_course_requirements`, `application_documents`) ont toutes une DF claire entre leurs colonnes — pas de variation indépendante de deux ensembles de valeurs pour une même clé, donc pas de violation 4FN identifiée.

### K.9 — Bilan des cycles de graphe de DF observés

Sur l'ensemble du schéma audité, **tous les cycles identifiés sont bénins** (clés candidates synonymes de la même entité : `id↔slug`, `id↔code`, `id↔email`, `id↔`clé composite `unique()`). **Aucun cycle suspect** (deux colonnes non-clé se déterminant mutuellement, symptôme d'un double encodage à fusionner) n'a été trouvé. Le cas le plus proche d'un tel pattern (`user_devices.risk_score`/`risk_level`) s'est révélé être un arc à sens unique, pas un cycle — documenté en K.7. Plusieurs tables ont plus d'une racine indépendante (`users` : 3 racines `id`/`email`/`phone_number1` ; `companies`/`agencies` : 3 racines `id`/`slug`/`email` ; `newsletter_subscriptions` : 3 racines `id`/`email`/`unsubscribe_token`) — documenté à chaque table concernée, non problématique en soi.

---

## Partie L — Synthèse finale

### Tableau récapitulatif

| Table | Forme normale atteinte | Violation principale | Priorité |
|---|---|---|---|
| `process_steps` | 1FN❌ | `document_type_ids` JSON = liste de FK | **Haute** |
| `application_steps` | 1FN❌ | `document_type_ids` JSON = liste de FK | **Haute** |
| `user_skills` | clé candidate non protégée | `unique(user_id, skill_id)` absente | **Haute** |
| `offer_skill` | clé candidate non protégée | `unique(offer_id, skill_id)` absente | **Haute** |
| `user_languages` | clé candidate mal choisie | `language_level_id` inclus dans la clé unique | **Haute** |
| `users` + `user_profiles` | 1FN❌ (conceptuel) | groupe répétitif `phone_number1/2/3` | Moyenne |
| `agencies` | 1FN❌ | `phones`, `whatsapp_numbers` en JSON | Moyenne |
| `user_profiles` | 1FN❌ | `pictures` liste en JSON | Moyenne |
| `meeting_user` | clé candidate non protégée | `unique(meeting_id, user_id)` absente | Moyenne |
| `task_user` | clé candidate non protégée | `unique(assigned_task_id, user_id)` absente | Moyenne |
| `user_profiles` | — | `unique(user_id)` absente malgré relation 1-1 | Moyenne |
| `user_devices`/`user_security_events` | déterminant hors-clé (arc unique) | `risk_score → risk_level` non-clé | Basse (assumé) |
| `benefit_offer` | — | aucune contrainte unique sur le pivot | Basse |
| `applications` | 3FN❌ (assumé) | `flow_group_id`/`process_flow_version` transitifs via `process_flow_id` | Basse (documenté) |
| `application_steps` | 3FN❌ (assumé) | duplication des champs `process_steps` (snapshot) | Basse (documenté) |
| `user_document_extractions` | 3FN❌ (assumé) | `document_type_code` transitif via `user_document_id` | Basse (risque réel de dérive) |
| `payment_schedules`/`applications`/`application_steps` | — | cumuls de paiement dupliqués sur 3 niveaux | Basse (à surveiller) |
| `mail_campaigns`/`sms_campaigns` | — | compteurs agrégats (cache) | Aucune (assumé) |
| `skills` | — | double FK `skill_category_id`/`category_id` (confusion possible) | Basse |
| `companies`, `offers` | 3FN✅ (vérifié) | géo indépendante — non-violation | Aucune |
| `interviews` | 3FN✅ (vérifié) | `company_id` indépendant — non-violation | Aucune |
| `countries`/`regions`/`cities`/`geographic_zones` | BCKFN✅ | — | Aucune |
| Tables de lookup (Partie A) | BCKFN✅ | — | Aucune |
| `user_trade`/`user_sector`/`user_preferred_countries`/`user_permission_overrides`/`user_devices` (clé) | BCKFN✅ | — | Aucune |
| `offer_required_document`/`program_required_document` | BCKFN✅ | — | Aucune |
| Reste du schéma (≈40 tables) | BCKFN✅ | — | Aucune |

### Top 5 des violations les plus impactantes

**1. `process_steps.document_type_ids` / `application_steps.document_type_ids` (liste de FK en JSON — violation 1FN)**
*Anomalie de suppression* : supprimer une ligne de `document_types` ne déclenche aucune contrainte référentielle → des ID orphelins restent silencieusement référencés dans `document_type_ids`.
*Anomalie de consultation* : impossible d'écrire "quelles étapes exigent le type de document X ?" en SQL simple sans parser du JSON.

**2. `user_skills` / `offer_skill` sans `unique(user_id, skill_id)` / `unique(offer_id, skill_id)`**
*Anomalie d'insertion et de mise à jour* : on peut insérer deux fois `(user_id=7, skill_id=3)` avec `level='BEGINNER'` puis `level='EXPERT'` ; corriger le niveau exige de mettre à jour toutes les lignes dupliquées, avec risque d'en oublier une.

**3. `user_languages` avec `unique(user_id, language_id, language_level_id)` au lieu de `unique(user_id, language_id)`**
*Anomalie de mise à jour/consultation* : un utilisateur peut avoir simultanément `(user=7, FR, B2)` et `(user=7, FR, C1)` — exactement le type d'anomalie illustré par l'exemple `propriétaire_voiture` du cours (clé mal choisie autorisant des faits contradictoires).

**4. Groupe répétitif `users.phone_number1` + `user_profiles.phone_number2`/`phone_number3` (violation 1FN)**
*Anomalie d'insertion* : un 4ᵉ numéro ne peut être enregistré sans modification du schéma — définition manuelle du problème de groupe répétitif enseigné en cours.

**5. `agencies.phones`/`whatsapp_numbers` et `user_profiles.pictures` (listes en JSON — violation 1FN)**
*Anomalie de contrainte* : la règle métier commentée "`pictures` max 3 entrées" n'est imposable par aucune contrainte SQL.
*Anomalie de recherche* : impossible d'indexer/rechercher un numéro whatsapp précis sans parser le JSON applicativement.

### Ce qui est déjà bien normalisé

- **La hiérarchie géographique** `countries → regions → cities` : chaque niveau ne stocke que sa FK vers le parent immédiat, aucune redondance en cascade.
- **Les tables de référence/lookup** (≈15 tables) : clé simple, `slug`/`code` unique cohérent, cycles bénins uniquement, aucune dépendance partielle ni transitive.
- **Les tables pivots bien conçues** (`user_trade`, `user_sector`, `user_preferred_countries`, `user_permission_overrides`, `user_devices`, `offer_required_document`, `program_required_document`, `recruiter_organization_user`) déclarent systématiquement la bonne contrainte `unique()` composite — la preuve que le défaut sur `user_skills`/`offer_skill`/`meeting_user`/`task_user` est un oubli localisé, pas une pratique généralisée.
- **`companies` et `offers`** ont été vérifiées explicitement pour une suspicion de redondance géographique transitive et se révèlent **conformes**.
- **Aucun cycle suspect** (deux colonnes non-clé qui s'auto-déterminent) n'a été trouvé sur l'ensemble du schéma — tous les cycles observés sont des clés candidates synonymes bénignes.
- **Les colonnes JSON multilingues** et leurs colonnes générées `STORED` associées sont un choix d'architecture assumé et cohérent, à ne pas confondre avec les vraies violations 1FN identifiées.
- **Les modules Finance et Recruiter** ne présentent aucune violation détectée.
