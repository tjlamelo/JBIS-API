<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Toutes les catégories avec leur mini description (fr/en)
        $categories = [
            // === PÔLE 1 : BTP & INDUSTRIE ===
            [
                'fr' => 'BTP & Construction - Gros Œuvre',
                'en' => 'Construction - Structural Work',
                'description_fr' => 'Travaux de structure : maçonnerie, ferraillage, coulage du béton, etc.',
                'description_en' => 'Structural work: masonry, rebar, concrete pouring, etc.',
            ],
            [
                'fr' => 'BTP & Construction - Second Œuvre',
                'en' => 'Construction - Finishing Work',
                'description_fr' => 'Finition : plâtrerie, peinture, carrelage, menuiserie intérieure, etc.',
                'description_en' => 'Finishing: plastering, painting, tiling, interior carpentry, etc.',
            ],
            [
                'fr' => 'Conduite d\'engins lourds',
                'en' => 'Heavy Machinery Operation',
                'description_fr' => 'Pilotage de grues, excavatrices, bulldozers, tractopelles et chargeuses.',
                'description_en' => 'Operation of cranes, excavators, bulldozers, backhoe loaders and wheel loaders.',
            ],
            [
                'fr' => 'Industrie & Maintenance',
                'en' => 'Industrial Maintenance',
                'description_fr' => 'Maintenance préventive et curative des équipements industriels.',
                'description_en' => 'Preventive and corrective maintenance of industrial equipment.',
            ],
            [
                'fr' => 'Soudure & Métallurgie',
                'en' => 'Welding & Metallurgy',
                'description_fr' => 'Soudage TIG/MIG/Arc, assemblage métallique et chaudronnerie.',
                'description_en' => 'TIG/MIG/Arc welding, metal assembly and boiler making.',
            ],

            // === PÔLE 2 : LOGISTIQUE, TRANSPORT & LIVRAISON ===
            [
                'fr' => 'Logistique & Entrepôt',
                'en' => 'Warehouse & Logistics',
                'description_fr' => 'Gestion d\'entrepôt, préparation de commandes, chargement/déchargement.',
                'description_en' => 'Warehouse management, order picking, loading/unloading.',
            ],
            [
                'fr' => 'Transport & Distribution',
                'en' => 'Transport & Delivery',
                'description_fr' => 'Livraison de marchandises, transport de fret, tournées locales et nationales.',
                'description_en' => 'Goods delivery, freight transport, local and national rounds.',
            ],
            [
                'fr' => 'Conduite poids lourds & Taxi',
                'en' => 'Heavy Vehicle & Taxi Driving',
                'description_fr' => 'Transport de personnes ou de marchandises avec véhicules de grande capacité.',
                'description_en' => 'Passenger or goods transport with high-capacity vehicles.',
            ],
            [
                'fr' => 'Livreurs (moto, vélo)',
                'en' => 'Couriers (motorcycle, bike)',
                'description_fr' => 'Livraison rapide en milieu urbain avec deux-roues.',
                'description_en' => 'Fast delivery in urban areas with two-wheelers.',
            ],

            // === PÔLE 3 : SANTÉ, SOINS & MÉDICO‑SOCIAL ===
            [
                'fr' => 'Médical & Paramédical',
                'en' => 'Medical & Paramedical',
                'description_fr' => 'Soins infirmiers, réanimation, suivi médical et techniques diagnostiques.',
                'description_en' => 'Nursing care, resuscitation, medical monitoring and diagnostic techniques.',
            ],
            [
                'fr' => 'Aide à la personne & Soins',
                'en' => 'Personal Care & Assistance',
                'description_fr' => 'Accompagnement des personnes âgées, handicapées ou dépendantes.',
                'description_en' => 'Support for elderly, disabled or dependent persons.',
            ],
            [
                'fr' => 'Santé mentale & Bien-être',
                'en' => 'Mental Health & Wellness',
                'description_fr' => 'Psychologie, thérapies, soutien psychologique et bien-être global.',
                'description_en' => 'Psychology, therapies, psychological support and overall well-being.',
            ],

            // === PÔLE 4 : HÔTELLERIE, RESTAURATION & SERVICES ===
            [
                'fr' => 'Restauration & Cuisine',
                'en' => 'Catering & Culinary',
                'description_fr' => 'Cuisine traditionnelle ou gastronomique, gestion des approvisionnements.',
                'description_en' => 'Traditional or gourmet cuisine, supply management.',
            ],
            [
                'fr' => 'Service en salle & Bar',
                'en' => 'Waiting & Bar Service',
                'description_fr' => 'Accueil des clients, service à table, préparation de boissons.',
                'description_en' => 'Customer reception, table service, beverage preparation.',
            ],
            [
                'fr' => 'Hôtellerie & Hébergement',
                'en' => 'Hotel & Accommodation',
                'description_fr' => 'Réception, gestion des réservations, entretien des chambres.',
                'description_en' => 'Reception, booking management, room maintenance.',
            ],
            [
                'fr' => 'Nettoyage & Entretien',
                'en' => 'Cleaning & Maintenance',
                'description_fr' => 'Nettoyage professionnel, entretien des locaux et espaces de vie.',
                'description_en' => 'Professional cleaning, maintenance of premises and living spaces.',
            ],

            // === PÔLE 5 : RELATION CLIENT, ADMINISTRATIF & MÉDIAS ===
            [
                'fr' => 'Relation Client & Téléconseil',
                'en' => 'Customer Service & Call Center',
                'description_fr' => 'Accueil téléphonique, gestion des réclamations, fidélisation client.',
                'description_en' => 'Telephone reception, complaint handling, customer loyalty.',
            ],
            [
                'fr' => 'Administration & Gestion',
                'en' => 'Administration & Management',
                'description_fr' => 'Comptabilité, RH, gestion de projets, secrétariat.',
                'description_en' => 'Accounting, HR, project management, secretarial work.',
            ],
            [
                'fr' => 'Médias & Journalisme',
                'en' => 'Media & Journalism',
                'description_fr' => 'Rédaction, reportage, production audiovisuelle, communication.',
                'description_en' => 'Writing, reporting, audiovisual production, communication.',
            ],

            // === ANCIENNES CATÉGORIES (conservées) ===
            [
                'fr' => 'Informatique & Technologie',
                'en' => 'IT & Technology',
                'description_fr' => 'Développement, réseaux, cybersécurité, support technique.',
                'description_en' => 'Development, networking, cybersecurity, technical support.',
            ],
            [
                'fr' => 'Banque & Finance',
                'en' => 'Banking & Finance',
                'description_fr' => 'Conseil financier, analyse de risques, opérations bancaires.',
                'description_en' => 'Financial advice, risk analysis, banking operations.',
            ],
            [
                'fr' => 'Santé & Médecine',
                'en' => 'Healthcare & Medical',
                'description_fr' => 'Médecine générale, spécialisée, soins hospitaliers.',
                'description_en' => 'General and specialized medicine, hospital care.',
            ],
            [
                'fr' => 'BTP & Construction',
                'en' => 'Construction & Civil Engineering',
                'description_fr' => 'Génie civil, construction de bâtiments et infrastructures.',
                'description_en' => 'Civil engineering, building and infrastructure construction.',
            ],
            [
                'fr' => 'Ingénierie',
                'en' => 'Engineering',
                'description_fr' => 'Conception, études techniques, supervision de projets.',
                'description_en' => 'Design, technical studies, project supervision.',
            ],
            [
                'fr' => 'Automobiles',
                'en' => 'Automotive',
                'description_fr' => 'Mécanique, vente de véhicules, réparation automobile.',
                'description_en' => 'Mechanics, vehicle sales, car repair.',
            ],
            [
                'fr' => 'Électricité & Énergies',
                'en' => 'Electrical & Energy',
                'description_fr' => 'Installation électrique, énergies renouvelables, maintenance.',
                'description_en' => 'Electrical installation, renewable energy, maintenance.',
            ],
            [
                'fr' => 'Industrie Manufacturière',
                'en' => 'Manufacturing',
                'description_fr' => 'Production de biens, chaînes d’assemblage, contrôle qualité.',
                'description_en' => 'Production of goods, assembly lines, quality control.',
            ],
            [
                'fr' => 'Juridique & Droit',
                'en' => 'Legal Services',
                'description_fr' => 'Droit civil, pénal, des sociétés, conseil juridique.',
                'description_en' => 'Civil, criminal and corporate law, legal advice.',
            ],
            [
                'fr' => 'Ressources Humaines',
                'en' => 'Human Resources',
                'description_fr' => 'Recrutement, gestion des carrières, formation, paie.',
                'description_en' => 'Recruitment, career management, training, payroll.',
            ],
            [
                'fr' => 'Menuiserie & Ébénisterie',
                'en' => 'Carpentry & Woodwork',
                'description_fr' => 'Création d’éléments en bois, fabrication de meubles, agencement.',
                'description_en' => 'Creation of wood elements, furniture making, fitting.',
            ],
            [
                'fr' => 'Ouvriers & Métiers',
                'en' => 'Skilled Labor',
                'description_fr' => 'Métiers manuels, travaux de maintenance, assistance technique.',
                'description_en' => 'Manual trades, maintenance work, technical assistance.',
            ],
            [
                'fr' => 'Vente & Distribution',
                'en' => 'Sales & Distribution',
                'description_fr' => 'Vente de produits, force de vente, commerce de détail.',
                'description_en' => 'Product sales, sales force, retail.',
            ],
            [
                'fr' => 'Marketing & Communication',
                'en' => 'Marketing & Advertising',
                'description_fr' => 'Études de marché, campagnes publicitaires, branding.',
                'description_en' => 'Market research, advertising campaigns, branding.',
            ],
            [
                'fr' => 'Agriculture & Agroalimentaire',
                'en' => 'Agriculture & Food',
                'description_fr' => 'Culture, élevage, transformation des produits agricoles.',
                'description_en' => 'Farming, livestock, processing of agricultural products.',
            ],
            [
                'fr' => 'Tourisme & Hôtellerie',
                'en' => 'Tourism & Hospitality',
                'description_fr' => 'Accueil des touristes, gestion des séjours, animation.',
                'description_en' => 'Tourist reception, stay management, entertainment.',
            ],
            [
                'fr' => 'Éducation & Formation',
                'en' => 'Education & Training',
                'description_fr' => 'Enseignement, développement de programmes, formation professionnelle.',
                'description_en' => 'Teaching, program development, vocational training.',
            ],
            [
                'fr' => 'Design & Créativité',
                'en' => 'Design & Creative',
                'description_fr' => 'Design graphique, illustration, conception de produits.',
                'description_en' => 'Graphic design, illustration, product design.',
            ],
            [
                'fr' => 'Environnement & Développement Durable',
                'en' => 'Environment & Sustainability',
                'description_fr' => 'Gestion des ressources, écologie, projets durables.',
                'description_en' => 'Resource management, ecology, sustainable projects.',
            ],
            [
                'fr' => 'Cryptomonnaies & Blockchain',
                'en' => 'Cryptocurrency & Blockchain',
                'description_fr' => 'Transactions sécurisées, développement de smart contracts.',
                'description_en' => 'Secure transactions, smart contract development.',
            ],
            [
                'fr' => 'Intelligence Artificielle',
                'en' => 'Artificial Intelligence',
                'description_fr' => 'Machine learning, traitement du langage, vision par ordinateur.',
                'description_en' => 'Machine learning, language processing, computer vision.',
            ],
            [
                'fr' => 'Réalité Virtuelle/Augmentée',
                'en' => 'VR/AR Technology',
                'description_fr' => 'Création d’expériences immersives, simulation 3D.',
                'description_en' => 'Creation of immersive experiences, 3D simulation.',
            ],
            [
                'fr' => 'Aéronautique & Défense',
                'en' => 'Aerospace & Defense',
                'description_fr' => 'Construction aéronautique, systèmes de défense, maintenance.',
                'description_en' => 'Aeronautical construction, defense systems, maintenance.',
            ],
            [
                'fr' => 'Sécurité & Sûreté',
                'en' => 'Security & Safety',
                'description_fr' => 'Surveillance, sécurité des biens et des personnes, prévention.',
                'description_en' => 'Surveillance, security of goods and people, prevention.',
            ],
            [
                'fr' => 'Immobilier & Gestion locative',
                'en' => 'Real Estate & Property Management',
                'description_fr' => 'Transaction immobilière, gestion de biens, syndic.',
                'description_en' => 'Real estate transactions, property management, syndic.',
            ],
            [
                'fr' => 'Télécommunications',
                'en' => 'Telecommunications',
                'description_fr' => 'Réseaux, téléphonie, transmission de données.',
                'description_en' => 'Networks, telephony, data transmission.',
            ],
            [
                'fr' => 'Services à la personne',
                'en' => 'Personal Care & Home Services',
                'description_fr' => 'Aide domestique, soins à domicile, assistance quotidienne.',
                'description_en' => 'Domestic help, home care, daily assistance.',
            ],
            [
                'fr' => 'Nettoyage & Hygiène',
                'en' => 'Cleaning & Hygiene',
                'description_fr' => 'Nettoyage industriel, entretien des espaces publics.',
                'description_en' => 'Industrial cleaning, maintenance of public spaces.',
            ],
            [
                'fr' => 'Restauration & Alimentation',
                'en' => 'Food & Beverage',
                'description_fr' => 'Préparation des repas, services de restauration collective.',
                'description_en' => 'Meal preparation, catering services.',
            ],
            [
                'fr' => 'Événementiel & Loisirs',
                'en' => 'Events & Entertainment',
                'description_fr' => 'Organisation de spectacles, festivals, activités culturelles.',
                'description_en' => 'Organization of shows, festivals, cultural activities.',
            ],
            [
                'fr' => 'Photographie & Audiovisuel',
                'en' => 'Photography & AV',
                'description_fr' => 'Prise de vue, montage vidéo, post-production.',
                'description_en' => 'Shooting, video editing, post-production.',
            ],
            [
                'fr' => 'Coiffure & Esthétique',
                'en' => 'Hair & Beauty',
                'description_fr' => 'Coiffure, soins esthétiques, maquillage.',
                'description_en' => 'Hairdressing, aesthetic care, makeup.',
            ],
            [
                'fr' => 'Transport & Mobilité',
                'en' => 'Transport & Mobility',
                'description_fr' => 'Déplacements de personnes, logistique de mobilité.',
                'description_en' => 'People transportation, mobility logistics.',
            ],
            [
                'fr' => 'Services publics & Administration',
                'en' => 'Public Services & Administration',
                'description_fr' => 'Gestion administrative, fonctions publiques.',
                'description_en' => 'Administrative management, public functions.',
            ],

            // === NOUVEAUX SECTEURS (avec descriptions) ===
            [
                'fr' => 'Sécurité privée & Surveillance',
                'en' => 'Private Security & Surveillance',
                'description_fr' => 'Vigilance, contrôle d’accès, protection des personnes et des biens.',
                'description_en' => 'Vigilance, access control, protection of people and property.',
            ],
            [
                'fr' => 'Pompiers & Secours d\'urgence',
                'en' => 'Firefighters & Emergency Services',
                'description_fr' => 'Intervention en cas d’incendie, accidents, catastrophes naturelles.',
                'description_en' => 'Intervention in fires, accidents, natural disasters.',
            ],
            [
                'fr' => 'Transport maritime & Portuaire',
                'en' => 'Maritime & Port Transport',
                'description_fr' => 'Navigation, manutention portuaire, logistique maritime.',
                'description_en' => 'Navigation, port handling, maritime logistics.',
            ],
            [
                'fr' => 'Transport aérien & Aéroportuaire',
                'en' => 'Aviation & Airport Services',
                'description_fr' => 'Personnel navigant, services au sol, gestion des vols.',
                'description_en' => 'Flight crew, ground services, flight management.',
            ],
            [
                'fr' => 'Microfinance & Inclusion financière',
                'en' => 'Microfinance & Financial Inclusion',
                'description_fr' => 'Accès au crédit, épargne, services financiers aux exclus.',
                'description_en' => 'Access to credit, savings, financial services for the excluded.',
            ],
            [
                'fr' => 'Assurances & Gestion des risques',
                'en' => 'Insurance & Risk Management',
                'description_fr' => 'Évaluation des risques, souscription de contrats, gestion des sinistres.',
                'description_en' => 'Risk assessment, contract underwriting, claims management.',
            ],
            [
                'fr' => 'Artisanat d\'art & Métiers traditionnels',
                'en' => 'Craftsmanship & Traditional Trades',
                'description_fr' => 'Travail du bois, du métal, de la terre, techniques patrimoniales.',
                'description_en' => 'Wood, metal, clay work, heritage techniques.',
            ],
            [
                'fr' => 'Gestion des déchets & Recyclage',
                'en' => 'Waste Management & Recycling',
                'description_fr' => 'Collecte, tri, valorisation et traitement des déchets.',
                'description_en' => 'Collection, sorting, recovery and waste treatment.',
            ],
            [
                'fr' => 'Énergies renouvelables',
                'en' => 'Renewable Energy',
                'description_fr' => 'Installation et maintenance de panneaux solaires, éoliennes, etc.',
                'description_en' => 'Installation and maintenance of solar panels, wind turbines, etc.',
            ],
            [
                'fr' => 'Eau & Assainissement',
                'en' => 'Water & Sanitation',
                'description_fr' => 'Gestion de l’eau potable, assainissement, traitement des eaux usées.',
                'description_en' => 'Drinking water management, sanitation, wastewater treatment.',
            ],
            [
                'fr' => 'Agroalimentaire & Transformation',
                'en' => 'Food Processing & Agribusiness',
                'description_fr' => 'Transformation des matières premières en produits alimentaires.',
                'description_en' => 'Transformation of raw materials into food products.',
            ],
            [
                'fr' => 'Pêche & Aquaculture',
                'en' => 'Fishing & Aquaculture',
                'description_fr' => 'Pêche en mer ou en eau douce, élevage de poissons et crustacés.',
                'description_en' => 'Sea or freshwater fishing, fish and shellfish farming.',
            ],
            [
                'fr' => 'Animation & Loisirs',
                'en' => 'Entertainment & Recreation',
                'description_fr' => 'Organisation d’activités ludiques, sportives ou culturelles.',
                'description_en' => 'Organisation of fun, sports or cultural activities.',
            ],
            [
                'fr' => 'Coaching & Développement personnel',
                'en' => 'Coaching & Personal Development',
                'description_fr' => 'Accompagnement individuel ou collectif pour le développement des compétences.',
                'description_en' => 'Individual or group coaching for skill development.',
            ],
            [
                'fr' => 'Métiers du numérique (Data, Cloud, Cybersécurité)',
                'en' => 'Digital Trades (Data, Cloud, Cybersecurity)',
                'description_fr' => 'Data science, infrastructure cloud, protection des systèmes.',
                'description_en' => 'Data science, cloud infrastructure, system protection.',
            ],
            [
                'fr' => 'Intelligence économique & Veille stratégique',
                'en' => 'Business Intelligence & Strategic Watch',
                'description_fr' => 'Collecte et analyse d’informations pour la prise de décision.',
                'description_en' => 'Information collection and analysis for decision making.',
            ],
            [
                'fr' => 'Logistique humanitaire & Urgence',
                'en' => 'Humanitarian Logistics & Emergency Relief',
                'description_fr' => 'Acheminement de l’aide lors de crises ou catastrophes.',
                'description_en' => 'Delivery of aid during crises or disasters.',
            ],
            [
                'fr' => 'Économie sociale & Solidaire',
                'en' => 'Social & Solidarity Economy',
                'description_fr' => 'Projets coopératifs, associations, commerce équitable.',
                'description_en' => 'Cooperative projects, associations, fair trade.',
            ],
        ];

        // Suppression des doublons (basée sur le slug anglais)
        $unique = [];
        foreach ($categories as $cat) {
            $slug = Str::slug($cat['en']);
            if (!isset($unique[$slug])) {
                $unique[$slug] = $cat;
            }
        }

        $data = [];
        foreach ($unique as $slug => $cat) {
            $data[] = [
                'slug' => $slug,
                'name' => json_encode(['fr' => $cat['fr'], 'en' => $cat['en']], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'fr' => $cat['description_fr'],
                    'en' => $cat['description_en'],
                ], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Mise à jour : on remplace 'icon' par 'description' dans la colonne cible
        Category::upsert($data, ['slug'], ['name', 'description', 'is_active', 'updated_at']);
    }
}