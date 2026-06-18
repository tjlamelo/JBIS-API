<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\SkillCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $skillTypes = [
            'technique' => ['fr' => 'Technique', 'en' => 'Technical'],
            'soft-skill' => ['fr' => 'Compétence comportementale', 'en' => 'Soft skill'],
        ];

        foreach ($skillTypes as $slug => $name) {
            SkillCategory::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        $skillTypeIds = SkillCategory::query()
            ->whereIn('slug', array_keys($skillTypes))
            ->get(['id', 'slug'])
            ->pluck('id', 'slug');

        $categoryIdsBySlug = Category::query()->pluck('id', 'slug');

        $skills = array_merge(
            // Administration, finance, gestion
            $this->categorySkills('administration-management', [
                ['fr' => 'Comptabilité générale et analytique', 'en' => 'General and management accounting', 'category' => 'technique'],
                ['fr' => 'Gestion de la paie et des déclarations sociales', 'en' => 'Payroll and social declarations management', 'category' => 'technique'],
                ['fr' => 'Élaboration de budgets et prévisions financières', 'en' => 'Budgeting and financial forecasting', 'category' => 'technique'],
                ['fr' => 'Gestion de projet (méthodes Agile, cycle en V)', 'en' => 'Project management (Agile, V-cycle)', 'category' => 'technique'],
                ['fr' => 'Systèmes d’information de gestion (ERP type SAP)', 'en' => 'Management information systems (SAP ERP)', 'category' => 'technique'],
                ['fr' => 'Tableaux de bord et indicateurs de performance (KPI)', 'en' => 'Dashboards and KPIs', 'category' => 'technique'],
                ['fr' => 'Gestion des achats et approvisionnements', 'en' => 'Procurement and supply management', 'category' => 'technique'],
                ['fr' => 'Rigueur et exactitude', 'en' => 'Rigor and accuracy', 'category' => 'soft-skill'],
                ['fr' => 'Organisation et planification', 'en' => 'Organization and planning', 'category' => 'soft-skill'],
                ['fr' => 'Analyse critique et synthèse', 'en' => 'Critical analysis and synthesis', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('legal-services', [
                ['fr' => 'Droit des contrats et des sociétés', 'en' => 'Contract and corporate law', 'category' => 'technique'],
                ['fr' => 'Droit du travail et gestion des RH', 'en' => 'Labor law and HR management', 'category' => 'technique'],
                ['fr' => 'Discrétion et confidentialité', 'en' => 'Discretion and confidentiality', 'category' => 'soft-skill'],
                ['fr' => 'Négociation et médiation', 'en' => 'Negotiation and mediation', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('banking-finance', [
                ['fr' => 'Fiscalité (TVA, impôts sur les sociétés)', 'en' => 'Taxation (VAT, corporate tax)', 'category' => 'technique'],
            ]),

            // Informatique & numérique
            $this->categorySkills('it-technology', [
                ['fr' => 'Langages de programmation (Python, Java, C++)', 'en' => 'Programming languages (Python, Java, C++)', 'category' => 'technique'],
                ['fr' => 'Développement Web (HTML, CSS, JavaScript, React)', 'en' => 'Web development (HTML, CSS, JS, React)', 'category' => 'technique'],
                ['fr' => 'Bases de données (SQL, MongoDB, PostgreSQL)', 'en' => 'Databases (SQL, MongoDB, PostgreSQL)', 'category' => 'technique'],
                ['fr' => 'Conception assistée par ordinateur (CAO/DAO)', 'en' => 'Computer-aided design (CAD/CAM)', 'category' => 'technique'],
                ['fr' => 'Électronique embarquée et systèmes temps réel', 'en' => 'Embedded electronics and real-time systems', 'category' => 'technique'],
                ['fr' => 'Résolution de problèmes complexes', 'en' => 'Complex problem solving', 'category' => 'soft-skill'],
                ['fr' => 'Esprit d’analyse et de synthèse', 'en' => 'Analytical and synthetic thinking', 'category' => 'soft-skill'],
                ['fr' => 'Adaptabilité aux évolutions technologiques', 'en' => 'Adaptability to technological changes', 'category' => 'soft-skill'],
                ['fr' => 'Travail en équipe agile', 'en' => 'Agile teamwork', 'category' => 'soft-skill'],
                ['fr' => 'Curiosité et veille technologique', 'en' => 'Curiosity and technological watch', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('digital-trades-data-cloud-cybersecurity', [
                ['fr' => 'Réseaux et sécurité (TCP/IP, pare-feu, VPN)', 'en' => 'Networking and security (TCP/IP, firewall, VPN)', 'category' => 'technique'],
                ['fr' => 'Cloud computing (AWS, Azure, GCP)', 'en' => 'Cloud computing (AWS, Azure, GCP)', 'category' => 'technique'],
                ['fr' => 'Cybersécurité (pentesting, GDPR, ISO 27001)', 'en' => 'Cybersecurity (penetration testing, GDPR, ISO 27001)', 'category' => 'technique'],
                ['fr' => 'DevOps et CI/CD (Docker, Kubernetes, Jenkins)', 'en' => 'DevOps and CI/CD (Docker, Kubernetes, Jenkins)', 'category' => 'technique'],
            ]),
            $this->categorySkills('artificial-intelligence', [
                ['fr' => 'Intelligence artificielle et machine learning', 'en' => 'Artificial intelligence and machine learning', 'category' => 'technique'],
            ]),

            // Commerce & marketing
            $this->categorySkills('marketing-advertising', [
                ['fr' => 'Stratégie marketing (4P, SWOT, segmentation)', 'en' => 'Marketing strategy (4Ps, SWOT, segmentation)', 'category' => 'technique'],
                ['fr' => 'Marketing digital (SEO, SEA, social media)', 'en' => 'Digital marketing (SEO, SEA, social media)', 'category' => 'technique'],
                ['fr' => 'Création de contenu (rédaction, vidéo, podcasts)', 'en' => 'Content creation (writing, video, podcasts)', 'category' => 'technique'],
                ['fr' => 'Gestion de la relation client (CRM, Salesforce)', 'en' => 'Customer relationship management (CRM, Salesforce)', 'category' => 'technique'],
                ['fr' => 'Études de marché et analyse de données', 'en' => 'Market research and data analysis', 'category' => 'technique'],
                ['fr' => 'Gestion des réseaux sociaux et community management', 'en' => 'Social media and community management', 'category' => 'technique'],
                ['fr' => 'Relations publiques et communication d’influence', 'en' => 'Public relations and influencer communication', 'category' => 'technique'],
                ['fr' => 'Marketing automation (HubSpot, Mailchimp)', 'en' => 'Marketing automation (HubSpot, Mailchimp)', 'category' => 'technique'],
                ['fr' => 'E-commerce (gestion de boutique en ligne, UX/UI)', 'en' => 'E-commerce (online store management, UX/UI)', 'category' => 'technique'],
                ['fr' => 'Persuasion et influence', 'en' => 'Persuasion and influence', 'category' => 'soft-skill'],
                ['fr' => 'Créativité et innovation', 'en' => 'Creativity and innovation', 'category' => 'soft-skill'],
                ['fr' => 'Empathie client et écoute active', 'en' => 'Customer empathy and active listening', 'category' => 'soft-skill'],
                ['fr' => 'Prise de parole en public', 'en' => 'Public speaking', 'category' => 'soft-skill'],
                ['fr' => 'Gestion du temps et des priorités', 'en' => 'Time and priority management', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('sales-distribution', [
                ['fr' => 'Techniques de vente et closing', 'en' => 'Sales techniques and closing', 'category' => 'technique'],
            ]),

            // Santé & social
            $this->categorySkills('medical-paramedical', [
                ['fr' => 'Soins infirmiers de base (pose de perfusion, pansements)', 'en' => 'Basic nursing care (IV, dressings)', 'category' => 'technique'],
                ['fr' => 'Surveillance des paramètres vitaux', 'en' => 'Vital signs monitoring', 'category' => 'technique'],
                ['fr' => 'Réanimation cardio-pulmonaire (RCP) et premiers secours', 'en' => 'CPR and first aid', 'category' => 'technique'],
                ['fr' => 'Hygiène et asepsie (bloc opératoire, stérilisation)', 'en' => 'Hygiene and asepsis (operating room, sterilization)', 'category' => 'technique'],
                ['fr' => 'Gestion des dossiers médicaux (DMP, logiciels métier)', 'en' => 'Medical records management (DMP, EMR)', 'category' => 'technique'],
                ['fr' => 'Patience et résilience', 'en' => 'Patience and resilience', 'category' => 'soft-skill'],
                ['fr' => 'Travail en équipe pluridisciplinaire', 'en' => 'Multidisciplinary teamwork', 'category' => 'soft-skill'],
                ['fr' => 'Sens de l’observation et de l’anticipation', 'en' => 'Observation and anticipation', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('personal-care-assistance', [
                ['fr' => 'Aide à la mobilité et transfert de patients', 'en' => 'Patient mobility and transfer assistance', 'category' => 'technique'],
                ['fr' => 'Médico-social : accompagnement des personnes âgées ou handicapées', 'en' => 'Social support for elderly or disabled persons', 'category' => 'technique'],
                ['fr' => 'Empathie et bienveillance', 'en' => 'Empathy and kindness', 'category' => 'soft-skill'],
                ['fr' => 'Discrétion et respect de la vie privée', 'en' => 'Discretion and privacy respect', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('mental-health-wellness', [
                ['fr' => 'Éducation thérapeutique et suivi médical', 'en' => 'Therapeutic education and medical follow-up', 'category' => 'technique'],
                ['fr' => 'Psychologie et techniques d’entretien', 'en' => 'Psychology and interview techniques', 'category' => 'technique'],
                ['fr' => 'Médiation familiale et sociale', 'en' => 'Family and social mediation', 'category' => 'technique'],
            ]),

            // Création & médias
            $this->categorySkills('design-creative', [
                ['fr' => 'Dessin et illustration (croquis, infographie)', 'en' => 'Drawing and illustration (sketch, graphic design)', 'category' => 'technique'],
                ['fr' => 'Conception graphique (Adobe Photoshop, Illustrator)', 'en' => 'Graphic design (Adobe Photoshop, Illustrator)', 'category' => 'technique'],
                ['fr' => 'Créativité et imagination', 'en' => 'Creativity and imagination', 'category' => 'soft-skill'],
                ['fr' => 'Sensibilité esthétique et culturelle', 'en' => 'Aesthetic and cultural sensitivity', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('photography-av', [
                ['fr' => 'Photographie et post-production (Lightroom, Capture One)', 'en' => 'Photography and post-production (Lightroom, Capture One)', 'category' => 'technique'],
                ['fr' => 'Montage vidéo et effets spéciaux (Premiere Pro, After Effects)', 'en' => 'Video editing and VFX (Premiere Pro, After Effects)', 'category' => 'technique'],
                ['fr' => 'Animation 2D/3D (Blender, Maya)', 'en' => '2D/3D animation (Blender, Maya)', 'category' => 'technique'],
                ['fr' => 'Création musicale et sound design (Ableton, Pro Tools)', 'en' => 'Music production and sound design (Ableton, Pro Tools)', 'category' => 'technique'],
                ['fr' => 'Techniques d’impression (sérigraphie, lithographie)', 'en' => 'Print techniques (serigraphy, lithography)', 'category' => 'technique'],
            ]),
            $this->categorySkills('media-journalism', [
                ['fr' => 'Écriture créative (roman, scénario, poésie)', 'en' => 'Creative writing (novel, script, poetry)', 'category' => 'technique'],
                ['fr' => 'Médiation culturelle et animation d’ateliers', 'en' => 'Cultural mediation and workshop facilitation', 'category' => 'technique'],
                ['fr' => 'Gestion de projet culturel et événementiel', 'en' => 'Cultural and event project management', 'category' => 'technique'],
                ['fr' => 'Autonomie et auto-discipline', 'en' => 'Autonomy and self-discipline', 'category' => 'soft-skill'],
                ['fr' => 'Ouverture d’esprit et curiosité', 'en' => 'Open-mindedness and curiosity', 'category' => 'soft-skill'],
                ['fr' => 'Capacité à recevoir et donner des feedbacks', 'en' => 'Ability to give and receive feedback', 'category' => 'soft-skill'],
            ]),

            // BTP & industrie
            $this->categorySkills('construction-structural-work', [
                ['fr' => 'Lecture de plans et schémas techniques', 'en' => 'Blueprint and technical drawing reading', 'category' => 'technique'],
                ['fr' => 'Charpente et ossature bois', 'en' => 'Timber framing and wooden structures', 'category' => 'technique'],
            ]),
            $this->categorySkills('construction-finishing-work', [
                ['fr' => 'Plomberie, chauffage et ventilation (CVAC)', 'en' => 'Plumbing, heating and ventilation (HVAC)', 'category' => 'technique'],
            ]),
            $this->categorySkills('welding-metallurgy', [
                ['fr' => 'Soudure (MIG, TIG, arc) et assemblage métallique', 'en' => 'Welding (MIG, TIG, arc) and metal assembly', 'category' => 'technique'],
                ['fr' => 'Métallerie et serrurerie', 'en' => 'Metalwork and locksmithing', 'category' => 'technique'],
            ]),
            $this->categorySkills('heavy-machinery-operation', [
                ['fr' => 'Conduite d’engins de chantier (grue, pelleteuse, nacelle)', 'en' => 'Construction machinery operation (crane, excavator, boom)', 'category' => 'technique'],
            ]),
            $this->categorySkills('industrial-maintenance', [
                ['fr' => 'Maintenance mécanique et hydraulique', 'en' => 'Mechanical and hydraulic maintenance', 'category' => 'technique'],
            ]),
            $this->categorySkills('manufacturing', [
                ['fr' => 'Électricité et électronique industrielle', 'en' => 'Industrial electricity and electronics', 'category' => 'technique'],
                ['fr' => 'Robotique industrielle et automatisme', 'en' => 'Industrial robotics and automation', 'category' => 'technique'],
                ['fr' => 'Gestion de la production (lean, Six Sigma)', 'en' => 'Production management (lean, Six Sigma)', 'category' => 'technique'],
                ['fr' => 'Rigueur et sécurité au travail', 'en' => 'Rigor and workplace safety', 'category' => 'soft-skill'],
                ['fr' => 'Polyvalence et dextérité', 'en' => 'Versatility and manual dexterity', 'category' => 'soft-skill'],
                ['fr' => 'Travail en équipe et coordination', 'en' => 'Teamwork and coordination', 'category' => 'soft-skill'],
                ['fr' => 'Esprit d’initiative et réactivité', 'en' => 'Initiative and responsiveness', 'category' => 'soft-skill'],
                ['fr' => 'Résistance physique et endurance', 'en' => 'Physical stamina and endurance', 'category' => 'soft-skill'],
            ]),

            // Agriculture
            $this->categorySkills('agriculture-food', [
                ['fr' => 'Conduite de tracteurs et engins agricoles', 'en' => 'Tractor and agricultural machinery operation', 'category' => 'technique'],
                ['fr' => 'Techniques de semis, plantation et récolte', 'en' => 'Sowing, planting and harvesting techniques', 'category' => 'technique'],
                ['fr' => 'Gestion des cultures (irrigation, fertilisation)', 'en' => 'Crop management (irrigation, fertilization)', 'category' => 'technique'],
                ['fr' => 'Élevage (bovin, ovin, porcin, avicole)', 'en' => 'Livestock farming (cattle, sheep, pigs, poultry)', 'category' => 'technique'],
                ['fr' => 'Médecine vétérinaire de base', 'en' => 'Basic veterinary care', 'category' => 'technique'],
                ['fr' => 'Agroforesterie et permaculture', 'en' => 'Agroforestry and permaculture', 'category' => 'technique'],
                ['fr' => 'Transformation agroalimentaire (fromagerie, conserverie)', 'en' => 'Agri-food processing (cheese-making, canning)', 'category' => 'technique'],
                ['fr' => 'Entretien des espaces verts et paysagisme', 'en' => 'Green space maintenance and landscaping', 'category' => 'technique'],
                ['fr' => 'Gestion de l’eau et des déchets agricoles', 'en' => 'Water and agricultural waste management', 'category' => 'technique'],
                ['fr' => 'Agriculture biologique et certification', 'en' => 'Organic farming and certification', 'category' => 'technique'],
                ['fr' => 'Patience et observation de la nature', 'en' => 'Patience and nature observation', 'category' => 'soft-skill'],
                ['fr' => 'Adaptabilité aux aléas climatiques', 'en' => 'Adaptability to weather hazards', 'category' => 'soft-skill'],
                ['fr' => 'Esprit d’entreprise et gestion autonome', 'en' => 'Entrepreneurial spirit and autonomous management', 'category' => 'soft-skill'],
                ['fr' => 'Respect de l’environnement et éthique', 'en' => 'Environmental respect and ethics', 'category' => 'soft-skill'],
                ['fr' => 'Travail en équipe saisonnière', 'en' => 'Seasonal teamwork', 'category' => 'soft-skill'],
            ]),

            // Hôtellerie, restauration & tourisme
            $this->categorySkills('hotel-accommodation', [
                ['fr' => 'Gestion des réservations (PMS, Channel Manager)', 'en' => 'Reservation management (PMS, Channel Manager)', 'category' => 'technique'],
                ['fr' => 'Accueil et service client en hôtellerie', 'en' => 'Reception and customer service in hospitality', 'category' => 'technique'],
            ]),
            $this->categorySkills('waiting-bar-service', [
                ['fr' => 'Techniques de sommellerie et service en salle', 'en' => 'Sommelier techniques and table service', 'category' => 'technique'],
            ]),
            $this->categorySkills('catering-culinary', [
                ['fr' => 'Cuisine (préparation, cuisson, dressage)', 'en' => 'Cooking (preparation, cooking, plating)', 'category' => 'technique'],
                ['fr' => 'Gestion des stocks et approvisionnement en restauration', 'en' => 'Stock and supply management in catering', 'category' => 'technique'],
            ]),
            $this->categorySkills('tourism-hospitality', [
                ['fr' => 'Animation touristique et guidage', 'en' => 'Tourist animation and guiding', 'category' => 'technique'],
                ['fr' => 'Vente et promotion de prestations touristiques', 'en' => 'Sales and promotion of tourist services', 'category' => 'technique'],
                ['fr' => 'Langage de spécialité (vocabulaire métier)', 'en' => 'Technical vocabulary', 'category' => 'technique'],
                ['fr' => 'Sens du service et de l’accueil', 'en' => 'Service orientation and hospitality', 'category' => 'soft-skill'],
                ['fr' => 'Aisance relationnelle et présentation', 'en' => 'Interpersonal skills and presentation', 'category' => 'soft-skill'],
                ['fr' => 'Adaptabilité à des publics variés', 'en' => 'Adaptability to diverse audiences', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('events-entertainment', [
                ['fr' => 'Organisation d’événements et banquets', 'en' => 'Event and banquet organization', 'category' => 'technique'],
            ]),
            $this->categorySkills('cleaning-maintenance', [
                ['fr' => 'Nettoyage et entretien des locaux (méthodes professionnelles)', 'en' => 'Professional cleaning and maintenance', 'category' => 'technique'],
                ['fr' => 'Gestion du stress en période de rush', 'en' => 'Stress management during peak periods', 'category' => 'soft-skill'],
            ]),
        );

        $uniqueSkills = [];
        foreach ($skills as $skill) {
            $slug = Str::slug($skill['en']);
            if (! isset($uniqueSkills[$slug])) {
                $uniqueSkills[$slug] = $skill;
            }
        }

        foreach ($uniqueSkills as $slug => $skill) {
            $categoryId = $categoryIdsBySlug[$skill['category_slug']] ?? null;
            if ($categoryId === null) {
                continue;
            }

            DB::table('skills')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => json_encode(
                        ['fr' => $skill['fr'], 'en' => $skill['en']],
                        JSON_UNESCAPED_UNICODE
                    ),
                    'skill_category_id' => $skillTypeIds[$skill['type']] ?? null,
                    'category_id' => $categoryId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    /**
     * @param  array<int, array{fr: string, en: string, category: string}>  $skills
     * @return array<int, array{fr: string, en: string, type: string, category_slug: string}>
     */
    private function categorySkills(string $categorySlug, array $skills): array
    {
        return array_map(
            fn (array $skill) => [
                'fr' => $skill['fr'],
                'en' => $skill['en'],
                'type' => $skill['category'],
                'category_slug' => $categorySlug,
            ],
            $skills
        );
    }
}
