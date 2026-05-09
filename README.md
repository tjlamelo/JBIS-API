Architecture Laravel Orientée Domaine (DDD)
Ce document présente une architecture Laravel pragmatique, inspirée du Domain-Driven Design (DDD) et des principes du Clean Code. L'objectif est de construire des applications robustes, maintenables et évolutives, en particulier pour les projets de grande envergure avec une logique métier complexe.

Philosophie Générale
L'architecture par défaut de Laravel regroupe le code par rôle technique (Contrôleurs, Modèles, etc.). Bien que efficace pour démarrer, cette structure devient un frein lorsque le projet grandit. Un concept métier comme "l'inscription" se retrouve éparpillé, ce qui augmente la charge cognitive.

Notre approche vise à :

Regrouper par concept métier plutôt que par rôle technique.
Séparer la logique métier de la couche de présentation (HTTP, Console).
Rendre le code explicite, testable et facile à naviguer.
Structure des Dossiers
Le cœur de cette architecture est l'utilisation d'un dossier core/ à la racine du projet, qui remplace et étend le dossier app/ par défaut.

text

/
├── App/
│   ├── Core/           # Couche Application (Livraison)
│   │   ├── Api/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   └── Dashboard/
│   │       ├── Controllers/
│   │       └── ViewModels/
│   │
│   ├── Domain/        # Couche Domaine (Cœur métier)
│   │   ├── Courses/
│   │   │   ├── Actions/
│   │   │   ├── Models/
│   │   │   ├── Rules/
│   │   │   └── ...
│   │   └── Students/
│   │       ├── Actions/
│   │       └── ...
│   │
│   └── Support/       # Utilitaires partagés
│       ├── Traits/
│       └── Helpers/
│
├── app/               # Laisser pour la compatibilité Laravel (ou supprimer)
├── ...
└── composer.json
1. La Couche Domaine (core/Domain/)
C'est le cœur de votre application. Elle contient la logique métier pure, indépendante de toute considération technique (HTTP, Base de données, etc.). Elle répond à la question : "Que fait notre application ?"

Chaque domaine métier (ex: Courses, Students, Orders) regroupe tous ses composants :

Actions : Classes à but unique représentant une opération métier (ex: PublishCourseAction, CreateOrderAction). C'est l'équivalent des "use cases".
Models : Modèles Eloquent "maigres", définissant les données et les relations. La logique complexe est déléguée aux Actions ou autres classes du domaine.
Data Transfer Objects (DTOs) : Objets pour transporter des données typées et structurées entre les couches, évitant les tableaux associatifs ambigus.
Rules : Règles de validation métier spécifiques au domaine (ex: CourseHasMinimumLessonsRule).
Events & Listeners : Événements métier (ex: CoursePublished) et leurs réactions.
States & Transitions : Gestion des cycles de vie complexes des entités (ex: Draft -> Published) en utilisant le State Pattern.
Exceptions : Erreurs spécifiques au domaine (ex: InsufficientStockException).
Services : Outils ou intégrations externes (ex: PaymentGateway, TaxCalculator). Voir la distinction Actions vs. Services ci-dessous.
2. La Couche Application (core/App/)
Cette couche est le mécanisme de livraison. Elle expose la logique métier du Domaine aux utilisateurs finaux (via le web, une API, la console). Elle répond à la question : "Comment les utilisateurs interagissent-ils avec notre application ?"

Elle doit rester "maigre" et "ennuyeuse", sans logique métier.

Controllers : Reçoivent les requêtes HTTP, valident les entrées (via les Form Requests), appellent une Action du Domaine, et retournent une réponse.
Form Requests : Valident et autorisent les requêtes HTTP spécifiques. Elles transforment souvent les données en DTO pour les passer au Domaine.
API Resources / View Models : Transforment les objets du Domaine en réponses JSON structurées (API) ou en données pour les vues (Dashboard).
Middleware : Gèrent les aspects transversaux du transport HTTP (authentification, CORS, etc.).
Jobs : Gèrent l'infrastructure des files d'attente et délèguent la logique métier réelle aux Actions du Domaine.
3. Les Utilitaires Partagés (core/Support/)
Ce dossier contient du code générique et réutilisable qui n'est lié à aucun domaine métier spécifique.

Traits : Comportements réutilisables (ex: HasUuid, Sluggable).
Helpers : Fonctions utilitaires générales.
Classes d'infrastructure : Tout ce qui pourrait presque être un package Composer indépendant.
Guides Pratiques et Bonnes Pratiques
Actions vs. Services : La Distinction Cruciale
C'est le point le plus important pour éviter les "God Classes".

Concept
Rôle
Question Clé
Exemple
Action	Représente ce que l'application fait.	"Est-ce une opération métier complète ?"	PlaceOrderAction
Service	Représente ce que l'application utilise.	"Est-ce un outil technique ou une intégration ?"	StripePaymentService

Règle d'or : Les Actions appellent les Services, jamais l'inverse. Ne créez pas un OrderService pour orchestrer des actions, mais plutôt une PlaceOrderAction qui injecte et utilise des services.

Implémenter une Action
Une Action doit être simple et focalisée.

php

// core/Domain/Orders/Actions/PlaceOrderAction.php

class PlaceOrderAction
{
    public function __construct(
        private TaxCalculator $taxCalculator,
        private PaymentGateway $paymentGateway
    ) {}

    public function execute(PlaceOrderData $data): Order
    {
        // 1. Calculer la taxe via un Service
        $taxAmount = $this->taxCalculator->calculate($data->items);

        // 2. Créer la commande
        $order = Order::create([...]);

        // 3. Débiter via un Service
        $this->paymentGateway->charge($data->paymentMethod, $order->total);

        // 4. Déclencher un événement
        event(new OrderPlaced($order));

        return $order;
    }
}
Convention : Nommez avec le suffixe Action.
Méthode : Utilisez une méthode publique execute(). Évitez __invoke() ou handle() pour la clarté.
Entrées : Acceptez des DTOs ou des objets typés, jamais l'objet Request directement.
Sorties : Retournez un objet du Domaine ou void, mais jamais une réponse HTTP.
Placement des Composants
Pour savoir où placer votre code, posez-vous la question : "Est-ce une règle métier ou un mécanisme de livraison ?"

Policies : Domaine. core/Domain/{Domain}/Policies/. Elles contiennent la logique d'autorisation métier.
Middleware : Application. core/App/{Module}/Middleware/. Ils gèrent le transport HTTP.
Form Requests : Application. core/App/{Module}/Requests/. Elles valident la requête HTTP.
Règles de validation (Rules) : Domaine. core/Domain/{Domain}/Rules/. Si la règle est purement métier.
Traits :
Métier (ex: HasSubscription) : Domaine. core/Domain/{Domain}/Traits/.
Technique (ex: HasUuid) : Support. core/Support/Traits/.
Les Règles de Validation
Règles personnalisées : Créez des classes de règles pour la logique réutilisable (ex: NoDisposableEmail).
Package Spatie : Utilisez spatie/laravel-validation-rules pour des cas courants (Delimited, ModelsExist, Authorized).
Logique complexe : Pour les validations dépendant de plusieurs champs, utilisez les hooks after() des Form Requests.
Mise en Œuvre Technique
Pour que Laravel reconnaisse cette nouvelle structure, deux modifications sont nécessaires :

 
Ensuite, exécutez composer dump-autoload.
bootstrap/app.php : Indiquez à Laravel où se trouve le chemin de l'application.
php

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Ajoutez cette ligne
$app->useAppPath($app->basePath('core/App'));

// ...
Cela permet aux commandes Artisan (make:controller, etc.) de fonctionner correctement avec la nouvelle structure.

En Résumé : Les Principes Clés
Simplicité avant tout : Le code simple et lisible est préférable au code "astucieux".
Convention over Configuration : Suivez les standards de Laravel et de l'équipe pour réduire la charge cognitive.
Responsabilité Unique : Chaque classe doit avoir une seule et unique raison de changer.
Le Domaine est Roi : Toute la logique métier précieuse et complexe doit résider dans la couche Domaine.
L'Application est un Pont : Elle connecte le monde extérieur (HTTP) à votre logique métier sans la polluer.
Testez tout : Les tests sont le filet de sécurité qui vous permet de refactoriser en toute confiance. Privilégiez les tests de fonctionnalité (Feature Tests).

Conventions d'Implémentation (Projet JBIS)
Ces conventions complètent les principes ci-dessus et s'appliquent au code courant du projet.

1) Nommage des Objets de Données
- Utilisez le suffixe DTO pour tous les objets de transfert.
- Exemple : MailCampaignDto, MailAudienceDto, DeviceContextDto.
- N'utilisez pas le suffixe Data pour les objets métier transportés entre couches.

2) Pattern Action obligatoire pour les cas métier
- Toute opération métier complète doit être une classe *Action avec une méthode publique execute().
- Exemple : DispatchMailCampaignAction, ResolveMailAudienceAction, LoginUserAction.
- Les Controllers ne doivent pas contenir la logique métier ; ils valident, appellent une Action, et retournent la réponse.

3) Query métier via Builder dédié
- Les requêtes SQL métier complexes ne doivent pas être dispersées dans les Services/Actions.
- Créez un Builder dédié sur le Model concerné et exposez des méthodes sémantiques.
- Exemple sur UserBuilder : withValidEmail(), forRoles(), forAgencies(), uniqueByEmail().

4) Effets de bord via Events/Listeners
- Les effets secondaires (stats, notifications techniques, etc.) doivent être déclenchés par des événements de domaine.
- Exemple : MailCampaignDispatched -> RefreshMailCampaignStatsListener.
- Évitez les appels directs "en cascade" dans une Action principale quand un Event est plus adapté.

5) États et transitions métier explicites
- Les statuts métier ne doivent pas être manipulés en "string magique" partout.
- Définissez un enum d'état et des transitions autorisées.
- Exemple : MailCampaignStatus + transitionTo() sur l'agrégat/model.

6) Exceptions de domaine ciblées
- Évitez RuntimeException générique pour les erreurs métier.
- Créez des exceptions explicites.
- Exemple : EmptyAudienceException, InvalidMailCampaignTransitionException.

7) Domaine indépendant du transport HTTP
- Le Domaine ne doit pas dépendre de Request ou d'objets HTTP.
- Si nécessaire, mappez la requête vers un DTO en couche Application.
- Exemple : DeviceContextDto créé dans le Controller puis passé aux Actions/Services domaine.

8) Réponses API unifiées
- Tous les Controllers API doivent retourner l'enveloppe standard BaseResponse.
- Utilisez exclusivement les helpers : BaseResponse::ok(), ::created(), ::unprocessableEntity(), etc.
- Les retours JsonResponse "bruts" (response()->json(...)) sont à éviter dans les controllers API.

9) Contrat d'Action : DTO en entrée et DTO en sortie
- Une Action domaine ne reçoit pas de tableau associatif non typé.
- Utilisez des DTOs d'entrée/sortie explicites.
- Exemple : LoginUserAction(LoginCredentialsDto) -> AuthenticationResultDto.

10) Logique de recherche métier via Builder
- Les règles de recherche d'entité (ex: email OU téléphone) doivent vivre dans un Builder dédié.
- Exemple : UserBuilder::findByLogin(string $login).
- L'Action ne doit pas reconstruire des requêtes SQL/regex complexes inline.

11) Placement des ViewModels
- Un ViewModel est une mécanique de livraison/préparation de sortie.
- Il doit être placé en couche Application, y compris pour une API pure.
- Exemple : App\Core\Application\Mail\ViewModels\UserMailViewModel.



Le principe de Storage Abstraction (abstraction du stockage) est une approche architecturale qui consiste à découpler la logique métier de ton application des détails techniques liés au stockage physique des fichiers.

Dans le cadre de notre projet Laravel, cette abstraction est la clé de voûte de notre système de miroir hybride entre ton serveur local (assets.jbis.cm) et le cloud (Cloudinary).

1. Le concept : "Le Quoi" avant "Le Comment"
L'idée centrale est que tes domaines métiers (Identity, Catalog, etc.) ne doivent jamais savoir où ni comment un fichier est stocké. Ils demandent simplement à "sauvegarder un média".

Sans abstraction : Ton contrôleur appelle directement l'API de Cloudinary. Si tu changes de fournisseur, tu dois modifier tous tes contrôleurs.

Avec l'abstraction : Ton contrôleur appelle une Action de Domaine (ex: StoreMediaAction). Que le fichier finisse sur un VPS à Yaoundé, sur S3 ou sur Cloudinary, le code métier reste identique.

2. Les piliers de notre implémentation
A. L'unicité de l'interface (DTO)
Pour que l'abstraction soit robuste, la communication entre les couches se fait via un UploadedMediaDto. Ce petit objet standardisé transporte toutes les informations nécessaires (chemins locaux, IDs cloud, URLs) de manière uniforme. Peu importe la complexité du stockage en arrière-plan, la réponse reçue par ton application est toujours structurée de la même façon.

B. Le Miroir Total (Redondance invisible)
Notre abstraction ne se contente pas de choisir un emplacement ; elle orchestre une double écriture.

Elle génère une version optimisée (WebP) pour la performance.

Elle conserve l'original (RAW) pour la sécurité.
Cette complexité est totalement invisible pour le reste du projet. Pour un développeur travaillant sur le module "Profil Utilisateur", c'est une simple opération de sauvegarde.

C. Le mécanisme de Fallback (Résilience)
L'abstraction permet de mettre en place une intelligence de récupération. Si Cloudinary est inaccessible, le système bascule automatiquement sur le miroir local. L'utilisateur final ne voit jamais de lien brisé, car l'abstraction gère dynamiquement la résolution des URLs en fonction de la disponibilité des supports.

3. Avantages pour le projet JBIS
Indépendance technologique : Nous ne sommes pas "prisonniers" de Cloudinary. Si nous décidons demain de migrer vers un stockage 100% local ou vers un autre fournisseur, il suffira de modifier une seule Action (StoreMediaAction) sans toucher au reste du code.

Testabilité : Grâce à cette couche d'abstraction, nous pouvons tester l'upload de fichiers dans nos tests unitaires sans réellement envoyer de données sur internet. On remplace simplement l'implémentation réelle par une "fausse" (Mock) qui simule un succès.

Maintenance simplifiée : Toute la logique de compression, de redimensionnement et de nommage des fichiers est centralisée. Une modification ici profite instantanément à tout l'écosystème du projet.

4. Implémentation actuelle dans JBIS (module Shared/Media)

Le module de stockage média de JBIS est désormais structuré autour d'une orchestration par drivers:

- Contrat: `App\Core\Domain\Shared\Media\Contracts\MediaStorageDriverInterface`
- Driver local (miroir): `App\Core\Domain\Shared\Media\Drivers\LocalMirrorStorageDriver`
- Driver cloud: `App\Core\Domain\Shared\Media\Drivers\CloudinaryStorageDriver`
- Orchestrateur métier: `App\Core\Domain\Shared\Media\Actions\StoreMediaAction`
- DTO standard: `App\Core\Domain\Shared\Media\DTOs\UploadedMediaDto`
- Builder de chemin: `App\Core\Domain\Shared\Media\Support\MediaPathBuilder`

Le flux d'écriture est:

1. génération d'un chemin standardisé avec date (`Y/m`) via `MediaPathBuilder`,
2. écriture locale RAW + Optimized (WebP),
3. tentative d'upload Cloudinary,
4. exposition d'une URL publique prioritaire cloud, avec fallback local.

5. Convention de stockage

Le dossier cible est fourni par le domaine appelant (ex: `app/catalog/offers/gallery`), puis enrichi automatiquement:

- local (`jbis_assets`):
  - `{folder}/{YYYY}/{MM}/raw/{file}.{ext}`
  - `{folder}/{YYYY}/{MM}/optimized/{file}.webp`
- cloud (Cloudinary):
  - `jbis.cm/{folder}/{YYYY}/{MM}/{public_id}`

Exemple photo d'offre:

- local raw: `app/catalog/offers/gallery/2026/05/raw/...`
- local optimized: `app/catalog/offers/gallery/2026/05/optimized/...`
- cloud: `jbis.cm/app/catalog/offers/gallery/2026/05/...`

6. Upload photo d'offre (API admin)

Un endpoint dédié existe pour l'upload image offre:

- `POST /api/v1/catalog/admin/offers/upload-photo`
- Contrôleur: `AdminOfferController::uploadPhoto()`
- payload: champ fichier `photo`
- réponse: `photo_url` + métadonnées `media`

Le champ `photo` est ensuite persistant sur l'entité `offers` et exposé par `OfferResource`.