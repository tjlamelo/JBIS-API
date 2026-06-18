<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\Trade;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TradeSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIdsBySlug = Category::query()->pluck('id', 'slug');
        $rows = [];

        foreach ($this->definitions() as $categorySlug => $trades) {
            $categoryId = $categoryIdsBySlug[$categorySlug] ?? null;
            if ($categoryId === null) {
                continue;
            }

            foreach ($trades as $trade) {
                $slug = Str::slug($trade['en']);
                $rows[] = [
                    'category_id' => $categoryId,
                    'slug' => $slug,
                    'name' => json_encode(['fr' => $trade['fr'], 'en' => $trade['en']], JSON_UNESCAPED_UNICODE),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Trade::upsert($rows, ['category_id', 'slug'], ['name', 'is_active', 'updated_at']);
    }

    /**
     * @return array<string, list<array{fr: string, en: string}>>
     */
    private function definitions(): array
    {
        return [
            // ==================== BTP & INDUSTRIE ====================
            'construction-structural-work' => [
                ['fr' => 'Maçon', 'en' => 'Mason'],
                ['fr' => 'Coffreur-boiseur', 'en' => 'Formwork carpenter'],
                ['fr' => 'Ferrailleur', 'en' => 'Steel fixer'],
                ['fr' => 'Manœuvre BTP', 'en' => 'Construction laborer'],
                ['fr' => 'Chef de chantier', 'en' => 'Site supervisor'],
                ['fr' => 'Conducteur de travaux', 'en' => 'Works manager'],
                ['fr' => 'Géomètre', 'en' => 'Surveyor'],
                ['fr' => 'Bétonnier', 'en' => 'Concrete worker'],
                ['fr' => 'Ouvrier en voirie', 'en' => 'Road worker'],
                ['fr' => 'Poseur de canalisations', 'en' => 'Pipe layer'],
            ],
            'construction-finishing-work' => [
                ['fr' => 'Plombier', 'en' => 'Plumber'],
                ['fr' => 'Peintre en bâtiment', 'en' => 'Building painter'],
                ['fr' => 'Carreleur', 'en' => 'Tiler'],
                ['fr' => 'Plâtrier', 'en' => 'Plasterer'],
                ['fr' => 'Menuisier', 'en' => 'Carpenter'],
                ['fr' => 'Électricien de chantier', 'en' => 'Construction electrician'],
                ['fr' => 'Fabricant d’agencements', 'en' => 'Fit-out joiner'],
                ['fr' => 'Poseur de revêtements', 'en' => 'Flooring fitter'],
                ['fr' => 'Isolateur', 'en' => 'Insulation worker'],
                ['fr' => 'Menuisier PVC / aluminium', 'en' => 'PVC/aluminium joiner'],
            ],
            'heavy-machinery-operation' => [
                ['fr' => 'Conducteur d\'engins de chantier', 'en' => 'Construction equipment operator'],
                ['fr' => 'Grutier', 'en' => 'Crane operator'],
                ['fr' => 'Conducteur de pelleteuse', 'en' => 'Excavator operator'],
                ['fr' => 'Conducteur de bulldozer', 'en' => 'Bulldozer operator'],
                ['fr' => 'Conducteur de tractopelle', 'en' => 'Backhoe loader operator'],
                ['fr' => 'Conducteur de chargeuse', 'en' => 'Wheel loader operator'],
                ['fr' => 'Conducteur de niveleuse', 'en' => 'Grader operator'],
                ['fr' => 'Conducteur de compacteur', 'en' => 'Roller operator'],
                ['fr' => 'Conducteur de grue mobile', 'en' => 'Mobile crane operator'],
                ['fr' => 'Conducteur d\'engins de manutention', 'en' => 'Material handling equipment operator'],
            ],
            'industrial-maintenance' => [
                ['fr' => 'Technicien de maintenance industrielle', 'en' => 'Industrial maintenance technician'],
                ['fr' => 'Mécanicien industriel', 'en' => 'Industrial mechanic'],
                ['fr' => 'Électromécanicien', 'en' => 'Electromechanic'],
                ['fr' => 'Automaticien', 'en' => 'Automation technician'],
                ['fr' => 'Technicien de maintenance des machines-outils', 'en' => 'Machine tool maintenance technician'],
                ['fr' => 'Responsable maintenance', 'en' => 'Maintenance manager'],
                ['fr' => 'Technicien en CND (contrôle non destructif)', 'en' => 'NDT technician'],
                ['fr' => 'Roboticien', 'en' => 'Robotics technician'],
            ],
            'welding-metallurgy' => [
                ['fr' => 'Soudeur', 'en' => 'Welder'],
                ['fr' => 'Chaudronnier', 'en' => 'Boilermaker'],
                ['fr' => 'Serrurier-métallier', 'en' => 'Metalworker-locksmith'],
                ['fr' => 'Tuyauteur', 'en' => 'Pipe fitter'],
                ['fr' => 'Soudeur TIG', 'en' => 'TIG welder'],
                ['fr' => 'Soudeur MIG/MAG', 'en' => 'MIG/MAG welder'],
                ['fr' => 'Soudeur à l\'arc', 'en' => 'Arc welder'],
                ['fr' => 'Constructeur métallique', 'en' => 'Structural metal fabricator'],
                ['fr' => 'Traceur / découpeur', 'en' => 'Marker / cutter'],
            ],
            'manufacturing' => [
                ['fr' => 'Opérateur de production', 'en' => 'Production operator'],
                ['fr' => 'Technicien en automatismes', 'en' => 'Automation technician'],
                ['fr' => 'Électricien industriel', 'en' => 'Industrial electrician'],
                ['fr' => 'Contrôleur qualité', 'en' => 'Quality controller'],
                ['fr' => 'Responsable de production', 'en' => 'Production manager'],
                ['fr' => 'Opérateur de ligne d’emballage', 'en' => 'Packaging line operator'],
                ['fr' => 'Conducteur de machine de production', 'en' => 'Production machine operator'],
                ['fr' => 'Régleur', 'en' => 'Machine setter'],
                ['fr' => 'Technicien de process', 'en' => 'Process technician'],
                ['fr' => 'Ouvrier de fabrication', 'en' => 'Factory worker'],
            ],
            'skilled-labor' => [
                ['fr' => 'Ouvrier qualifié', 'en' => 'Skilled worker'],
                ['fr' => 'Technicien polyvalent', 'en' => 'Multiskilled technician'],
                ['fr' => 'Ouvrier de maintenance', 'en' => 'Maintenance worker'],
                ['fr' => 'Agent de fabrication', 'en' => 'Production agent'],
            ],
            'carpentry-woodwork' => [
                ['fr' => 'Menuisier', 'en' => 'Carpenter'],
                ['fr' => 'Ébéniste', 'en' => 'Cabinetmaker'],
                ['fr' => 'Charpentier', 'en' => 'Timber framer'],
                ['fr' => 'Agenceur', 'en' => 'Furniture installer'],
                ['fr' => 'Tourneur sur bois', 'en' => 'Wood turner'],
                ['fr' => 'Sculpteur sur bois', 'en' => 'Wood carver'],
                ['fr' => 'Marqueteur', 'en' => 'Marquetry worker'],
            ],

            // ==================== LOGISTIQUE & TRANSPORT ====================
            'warehouse-logistics' => [
                ['fr' => 'Préparateur de commandes', 'en' => 'Order picker'],
                ['fr' => 'Cariste', 'en' => 'Forklift operator'],
                ['fr' => 'Magasinier', 'en' => 'Warehouse clerk'],
                ['fr' => 'Agent de quai', 'en' => 'Dock worker'],
                ['fr' => 'Gestionnaire d\'entrepôt', 'en' => 'Warehouse manager'],
                ['fr' => 'Responsable logistique', 'en' => 'Logistics manager'],
                ['fr' => 'Agent de réception / expédition', 'en' => 'Receiving / shipping clerk'],
                ['fr' => 'Opérateur de chariot élévateur', 'en' => 'Forklift driver'],
                ['fr' => 'Technicien WMS', 'en' => 'WMS technician'],
                ['fr' => 'Coordinateur logistique', 'en' => 'Logistics coordinator'],
            ],
            'transport-delivery' => [
                ['fr' => 'Livreur', 'en' => 'Delivery driver'],
                ['fr' => 'Chauffeur-livreur', 'en' => 'Delivery courier'],
                ['fr' => 'Conducteur de camion', 'en' => 'Truck driver'],
                ['fr' => 'Routier', 'en' => 'Long-haul trucker'],
                ['fr' => 'Conducteur de camion frigorifique', 'en' => 'Refrigerated truck driver'],
                ['fr' => 'Conducteur de semi-remorque', 'en' => 'Semi-trailer driver'],
                ['fr' => 'Chauffeur de VUL', 'en' => 'Van driver'],
                ['fr' => 'Responsable de tournées', 'en' => 'Route planner'],
            ],
            'heavy-vehicle-taxi-driving' => [
                ['fr' => 'Chauffeur de taxi', 'en' => 'Taxi driver'],
                ['fr' => 'Chauffeur VTC', 'en' => 'Ride-hail driver'],
                ['fr' => 'Conducteur de bus', 'en' => 'Bus driver'],
                ['fr' => 'Conducteur de car', 'en' => 'Coach driver'],
                ['fr' => 'Conducteur de poids lourd', 'en' => 'Heavy goods vehicle driver'],
                ['fr' => 'Conducteur d\'autocar', 'en' => 'Motorcoach driver'],
            ],
            'couriers-motorcycle-bike' => [
                ['fr' => 'Livreur à moto', 'en' => 'Motorcycle courier'],
                ['fr' => 'Livreur à vélo', 'en' => 'Bike courier'],
                ['fr' => 'Coursier', 'en' => 'Messenger'],
                ['fr' => 'Livreur de repas (UberEats, Deliveroo)', 'en' => 'Food delivery rider'],
            ],
            'maritime-port-transport' => [
                ['fr' => 'Marin', 'en' => 'Sailor'],
                ['fr' => 'Matelot', 'en' => 'Deckhand'],
                ['fr' => 'Capitaine', 'en' => 'Captain'],
                ['fr' => 'Officier de pont', 'en' => 'Deck officer'],
                ['fr' => 'Docker', 'en' => 'Docker'],
                ['fr' => 'Grutier portuaire', 'en' => 'Port crane operator'],
                ['fr' => 'Agent de manutention', 'en' => 'Stevedore'],
                ['fr' => 'Technicien de maintenance portuaire', 'en' => 'Port maintenance technician'],
                ['fr' => 'Pilote maritime', 'en' => 'Maritime pilot'],
            ],
            'aviation-airport-services' => [
                ['fr' => 'Pilote de ligne', 'en' => 'Airline pilot'],
                ['fr' => 'Hôtesse de l\'air / Steward', 'en' => 'Flight attendant'],
                ['fr' => 'Agent d\'escale', 'en' => 'Ground handling agent'],
                ['fr' => 'Contrôleur aérien', 'en' => 'Air traffic controller'],
                ['fr' => 'Mécanicien aéronautique', 'en' => 'Aircraft mechanic'],
                ['fr' => 'Agent de piste', 'en' => 'Ramp agent'],
                ['fr' => 'Agent de sécurité aéroportuaire', 'en' => 'Airport security agent'],
                ['fr' => 'Technicien de maintenance aéronautique', 'en' => 'Aeronautical maintenance technician'],
            ],
            'transport-mobility' => [
                ['fr' => 'Agent de mobilité', 'en' => 'Mobility agent'],
                ['fr' => 'Planificateur de transport', 'en' => 'Transport planner'],
                ['fr' => 'Gestionnaire de flotte', 'en' => 'Fleet manager'],
            ],

            // ==================== SANTÉ & SOCIAL ====================
            'medical-paramedical' => [
                ['fr' => 'Infirmier(ère)', 'en' => 'Nurse'],
                ['fr' => 'Aide-soignant(e)', 'en' => 'Nursing assistant'],
                ['fr' => 'Technicien de laboratoire', 'en' => 'Laboratory technician'],
                ['fr' => 'Radiologue', 'en' => 'Radiologist'],
                ['fr' => 'Kinésithérapeute', 'en' => 'Physiotherapist'],
                ['fr' => 'Ergothérapeute', 'en' => 'Occupational therapist'],
                ['fr' => 'Manipulateur radio', 'en' => 'Radiologic technologist'],
                ['fr' => 'Médecin généraliste', 'en' => 'General practitioner'],
                ['fr' => 'Médecin spécialiste', 'en' => 'Medical specialist'],
                ['fr' => 'Chirurgien', 'en' => 'Surgeon'],
                ['fr' => 'Anesthésiste', 'en' => 'Anesthesiologist'],
                ['fr' => 'Pédiatre', 'en' => 'Pediatrician'],
                ['fr' => 'Gynécologue', 'en' => 'Gynecologist'],
                ['fr' => 'Cardiologue', 'en' => 'Cardiologist'],
                ['fr' => 'Ophtalmologiste', 'en' => 'Ophthalmologist'],
            ],
            'personal-care-assistance' => [
                ['fr' => 'Aide à domicile', 'en' => 'Home care aide'],
                ['fr' => 'Auxiliaire de vie', 'en' => 'Care assistant'],
                ['fr' => 'Accompagnant éducatif', 'en' => 'Educational care worker'],
                ['fr' => 'Aide ménagère', 'en' => 'Housekeeper'],
                ['fr' => 'Téléassistant(e)', 'en' => 'Telecare operator'],
                ['fr' => 'Assistant de soins en gérontologie', 'en' => 'Geriatric care assistant'],
            ],
            'mental-health-wellness' => [
                ['fr' => 'Psychologue', 'en' => 'Psychologist'],
                ['fr' => 'Conseiller en insertion', 'en' => 'Integration counselor'],
                ['fr' => 'Psychiatre', 'en' => 'Psychiatrist'],
                ['fr' => 'Psychothérapeute', 'en' => 'Psychotherapist'],
                ['fr' => 'Coach en développement personnel', 'en' => 'Personal development coach'],
                ['fr' => 'Médiateur social', 'en' => 'Social mediator'],
                ['fr' => 'Éducateur spécialisé', 'en' => 'Special educator'],
            ],
            'healthcare-medical' => [
                ['fr' => 'Médecin généraliste', 'en' => 'General practitioner'],
                ['fr' => 'Médecin spécialiste', 'en' => 'Specialist doctor'],
                ['fr' => 'Chirurgien', 'en' => 'Surgeon'],
                ['fr' => 'Sage-femme', 'en' => 'Midwife'],
                ['fr' => 'Pharmacien', 'en' => 'Pharmacist'],
                ['fr' => 'Dentiste', 'en' => 'Dentist'],
            ],
            'personal-care-home-services' => [
                ['fr' => 'Aide ménager(e)', 'en' => 'Domestic helper'],
                ['fr' => 'Employé de maison', 'en' => 'Domestic worker'],
                ['fr' => 'Garde d\'enfant', 'en' => 'Childminder'],
                ['fr' => 'Assistant familial', 'en' => 'Foster care worker'],
                ['fr' => 'Agent de services à la personne', 'en' => 'Personal service agent'],
            ],

            // ==================== HÔTELLERIE & RESTAURATION ====================
            'catering-culinary' => [
                ['fr' => 'Cuisinier(ère)', 'en' => 'Cook'],
                ['fr' => 'Commis de cuisine', 'en' => 'Kitchen assistant'],
                ['fr' => 'Chef de partie', 'en' => 'Line cook'],
                ['fr' => 'Chef cuisinier', 'en' => 'Head chef'],
                ['fr' => 'Pâtissier', 'en' => 'Pastry chef'],
                ['fr' => 'Boulanger', 'en' => 'Baker'],
                ['fr' => 'Pizzaïolo', 'en' => 'Pizza maker'],
                ['fr' => 'Cuisinier de collectivité', 'en' => 'Catering cook'],
                ['fr' => 'Sous-chef', 'en' => 'Sous-chef'],
            ],
            'waiting-bar-service' => [
                ['fr' => 'Serveur(se)', 'en' => 'Waiter/waitress'],
                ['fr' => 'Barman / Barmaid', 'en' => 'Bartender'],
                ['fr' => 'Sommelier(ère)', 'en' => 'Sommelier'],
                ['fr' => 'Chef de rang', 'en' => 'Head waiter'],
                ['fr' => 'Barmaid / Barman', 'en' => 'Bartender'],
                ['fr' => 'Caféier', 'en' => 'Barista'],
            ],
            'hotel-accommodation' => [
                ['fr' => 'Réceptionniste hôtelier', 'en' => 'Hotel receptionist'],
                ['fr' => 'Femme / Valet de chambre', 'en' => 'Housekeeper'],
                ['fr' => 'Gouvernant(e) d\'étages', 'en' => 'Floor supervisor'],
                ['fr' => 'Concierge', 'en' => 'Concierge'],
                ['fr' => 'Responsable d\'hébergement', 'en' => 'Accommodation manager'],
                ['fr' => 'Veilleur de nuit', 'en' => 'Night watchman'],
            ],
            'cleaning-maintenance' => [
                ['fr' => 'Agent d\'entretien', 'en' => 'Cleaning agent'],
                ['fr' => 'Agent de propreté', 'en' => 'Sanitation worker'],
                ['fr' => 'Technicien de maintenance', 'en' => 'Maintenance technician'],
                ['fr' => 'Agent de nettoyage industriel', 'en' => 'Industrial cleaner'],
                ['fr' => 'Responsable d\'entretien', 'en' => 'Cleaning supervisor'],
            ],
            'cleaning-hygiene' => [
                ['fr' => 'Agent de nettoyage', 'en' => 'Cleaner'],
                ['fr' => 'Technicien de surface', 'en' => 'Surface technician'],
                ['fr' => 'Employé d\'entretien', 'en' => 'Maintenance employee'],
                ['fr' => 'Agent d\'hygiène', 'en' => 'Hygiene agent'],
            ],
            'tourism-hospitality' => [
                ['fr' => 'Guide touristique', 'en' => 'Tour guide'],
                ['fr' => 'Animateur touristique', 'en' => 'Tourism animator'],
                ['fr' => 'Conseiller en séjour', 'en' => 'Travel advisor'],
                ['fr' => 'Agent de voyage', 'en' => 'Travel agent'],
                ['fr' => 'Responsable de site touristique', 'en' => 'Tourist site manager'],
                ['fr' => 'Interprète guide', 'en' => 'Guide interpreter'],
            ],
            'food-beverage' => [
                ['fr' => 'Serveur en restauration rapide', 'en' => 'Fast food server'],
                ['fr' => 'Cuisinier de fast-food', 'en' => 'Fast food cook'],
                ['fr' => 'Barista', 'en' => 'Barista'],
                ['fr' => 'Préparateur de commandes en restauration', 'en' => 'Food delivery prep'],
            ],

            // ==================== RELATION CLIENT & ADMINISTRATIF ====================
            'customer-service-call-center' => [
                ['fr' => 'Conseiller clientèle', 'en' => 'Customer service advisor'],
                ['fr' => 'Téléconseiller', 'en' => 'Call center agent'],
                ['fr' => 'Chargé de relation client', 'en' => 'Customer relationship manager'],
                ['fr' => 'Agent de centre d\'appels', 'en' => 'Call center operator'],
                ['fr' => 'Chargé de satisfaction client', 'en' => 'Customer satisfaction officer'],
                ['fr' => 'Superviseur de plateau', 'en' => 'Floor supervisor'],
            ],
            'administration-management' => [
                ['fr' => 'Assistant(e) administratif(ve)', 'en' => 'Administrative assistant'],
                ['fr' => 'Comptable', 'en' => 'Accountant'],
                ['fr' => 'Gestionnaire de paie', 'en' => 'Payroll manager'],
                ['fr' => 'Chargé(e) de projet', 'en' => 'Project officer'],
                ['fr' => 'Secrétaire', 'en' => 'Secretary'],
                ['fr' => 'Chef de projet', 'en' => 'Project manager'],
                ['fr' => 'Contrôleur de gestion', 'en' => 'Management controller'],
                ['fr' => 'Responsable administratif', 'en' => 'Administrative manager'],
            ],
            'human-resources' => [
                ['fr' => 'Chargé(e) de recrutement', 'en' => 'Recruitment officer'],
                ['fr' => 'Gestionnaire RH', 'en' => 'HR manager'],
                ['fr' => 'Responsable formation', 'en' => 'Training manager'],
                ['fr' => 'Chargé de paie', 'en' => 'Payroll administrator'],
                ['fr' => 'Chargé de relations sociales', 'en' => 'Employee relations officer'],
            ],
            'legal-services' => [
                ['fr' => 'Juriste', 'en' => 'Legal counsel'],
                ['fr' => 'Assistant(e) juridique', 'en' => 'Legal assistant'],
                ['fr' => 'Avocat', 'en' => 'Lawyer'],
                ['fr' => 'Notaire', 'en' => 'Notary'],
                ['fr' => 'Clerc de notaire', 'en' => 'Notary clerk'],
                ['fr' => 'Conseil juridique', 'en' => 'Legal advisor'],
            ],
            'banking-finance' => [
                ['fr' => 'Conseiller bancaire', 'en' => 'Bank advisor'],
                ['fr' => 'Analyste financier', 'en' => 'Financial analyst'],
                ['fr' => 'Chargé de clientèle bancaire', 'en' => 'Bank customer service officer'],
                ['fr' => 'Gestionnaire de comptes', 'en' => 'Account manager'],
                ['fr' => 'Trésorier', 'en' => 'Treasurer'],
                ['fr' => 'Auditeur financier', 'en' => 'Financial auditor'],
            ],
            'public-services-administration' => [
                ['fr' => 'Fonctionnaire', 'en' => 'Civil servant'],
                ['fr' => 'Agent administratif de mairie', 'en' => 'Municipal administrative officer'],
                ['fr' => 'Agent de service public', 'en' => 'Public service agent'],
                ['fr' => 'Secrétaire de mairie', 'en' => 'Town clerk'],
            ],
            'real-estate-property-management' => [
                ['fr' => 'Agent immobilier', 'en' => 'Real estate agent'],
                ['fr' => 'Gestionnaire de copropriété', 'en' => 'Property manager'],
                ['fr' => 'Syndic', 'en' => 'Trustee'],
                ['fr' => 'Chargé de location', 'en' => 'Rental manager'],
                ['fr' => 'Négociateur immobilier', 'en' => 'Real estate negotiator'],
            ],

            // ==================== MÉDIAS & COMMUNICATION ====================
            'media-journalism' => [
                ['fr' => 'Journaliste', 'en' => 'Journalist'],
                ['fr' => 'Rédacteur web', 'en' => 'Web writer'],
                ['fr' => 'Reporter', 'en' => 'Reporter'],
                ['fr' => 'Chef de rédaction', 'en' => 'Editor-in-chief'],
                ['fr' => 'Chargé de communication', 'en' => 'Communications officer'],
                ['fr' => 'Community manager', 'en' => 'Community manager'],
                ['fr' => 'Traffic manager', 'en' => 'Traffic manager'],
            ],
            'marketing-advertising' => [
                ['fr' => 'Chargé(e) de marketing', 'en' => 'Marketing officer'],
                ['fr' => 'Community manager', 'en' => 'Community manager'],
                ['fr' => 'Traffic manager', 'en' => 'Traffic manager'],
                ['fr' => 'Chef de produit', 'en' => 'Product manager'],
                ['fr' => 'Responsable marketing digital', 'en' => 'Digital marketing manager'],
                ['fr' => 'Growth hacker', 'en' => 'Growth hacker'],
                ['fr' => 'Brand manager', 'en' => 'Brand manager'],
            ],
            'design-creative' => [
                ['fr' => 'Graphiste', 'en' => 'Graphic designer'],
                ['fr' => 'Designer UI/UX', 'en' => 'UI/UX designer'],
                ['fr' => 'Illustrateur', 'en' => 'Illustrator'],
                ['fr' => 'Directeur artistique', 'en' => 'Art director'],
                ['fr' => 'Motion designer', 'en' => 'Motion designer'],
                ['fr' => 'Designer de produit', 'en' => 'Product designer'],
            ],
            'photography-av' => [
                ['fr' => 'Photographe', 'en' => 'Photographer'],
                ['fr' => 'Monteur vidéo', 'en' => 'Video editor'],
                ['fr' => 'Réalisateur', 'en' => 'Director'],
                ['fr' => 'Chef opérateur', 'en' => 'Camera operator'],
                ['fr' => 'Ingénieur du son', 'en' => 'Sound engineer'],
                ['fr' => 'Coloriste', 'en' => 'Colorist'],
            ],
            'events-entertainment' => [
                ['fr' => 'Organisateur d\'événements', 'en' => 'Event organizer'],
                ['fr' => 'Technicien événementiel', 'en' => 'Event technician'],
                ['fr' => 'Régisseur', 'en' => 'Stage manager'],
                ['fr' => 'Animateur d\'événements', 'en' => 'Event host'],
                ['fr' => 'Planner événementiel', 'en' => 'Event planner'],
            ],
            'entertainment-recreation' => [
                ['fr' => 'Animateur', 'en' => 'Entertainer'],
                ['fr' => 'Éducateur sportif', 'en' => 'Sports educator'],
                ['fr' => 'Guide d\'activités', 'en' => 'Activity guide'],
                ['fr' => 'Moniteur de sports', 'en' => 'Sports instructor'],
            ],

            // ==================== IT & NUMÉRIQUE ====================
            'it-technology' => [
                ['fr' => 'Développeur web', 'en' => 'Web developer'],
                ['fr' => 'Développeur mobile', 'en' => 'Mobile developer'],
                ['fr' => 'Technicien support informatique', 'en' => 'IT support technician'],
                ['fr' => 'Administrateur systèmes', 'en' => 'Systems administrator'],
                ['fr' => 'Ingénieur DevOps', 'en' => 'DevOps engineer'],
                ['fr' => 'Analyste cybersécurité', 'en' => 'Cybersecurity analyst'],
                ['fr' => 'Développeur full-stack', 'en' => 'Full-stack developer'],
                ['fr' => 'Développeur front-end', 'en' => 'Front-end developer'],
                ['fr' => 'Développeur back-end', 'en' => 'Back-end developer'],
                ['fr' => 'Database administrator', 'en' => 'Database administrator'],
            ],
            'digital-trades-data-cloud-cybersecurity' => [
                ['fr' => 'Administrateur systèmes', 'en' => 'Systems administrator'],
                ['fr' => 'Ingénieur DevOps', 'en' => 'DevOps engineer'],
                ['fr' => 'Analyste cybersécurité', 'en' => 'Cybersecurity analyst'],
                ['fr' => 'Data scientist', 'en' => 'Data scientist'],
                ['fr' => 'Ingénieur cloud', 'en' => 'Cloud engineer'],
                ['fr' => 'Architecte cloud', 'en' => 'Cloud architect'],
                ['fr' => 'RSSI (Responsable Sécurité SI)', 'en' => 'CISO'],
                ['fr' => 'Administrateur bases de données', 'en' => 'Database administrator'],
                ['fr' => 'Ingénieur données', 'en' => 'Data engineer'],
                ['fr' => 'Expert en protection des données', 'en' => 'Data protection expert'],
            ],
            'artificial-intelligence' => [
                ['fr' => 'Ingénieur machine learning', 'en' => 'Machine learning engineer'],
                ['fr' => 'Data scientist', 'en' => 'Data scientist'],
                ['fr' => 'Ingénieur NLP', 'en' => 'NLP engineer'],
                ['fr' => 'Ingénieur en vision par ordinateur', 'en' => 'Computer vision engineer'],
                ['fr' => 'Ingénieur en IA générative', 'en' => 'Generative AI engineer'],
                ['fr' => 'Chercheur en IA', 'en' => 'AI researcher'],
            ],
            'telecommunications' => [
                ['fr' => 'Technicien télécom', 'en' => 'Telecom technician'],
                ['fr' => 'Ingénieur réseau', 'en' => 'Network engineer'],
                ['fr' => 'Installateur fibre optique', 'en' => 'Fiber optic installer'],
                ['fr' => 'Technicien réseau', 'en' => 'Network technician'],
                ['fr' => 'Ingénieur en télécommunications', 'en' => 'Telecommunications engineer'],
            ],
            'cryptocurrency-blockchain' => [
                ['fr' => 'Développeur blockchain', 'en' => 'Blockchain developer'],
                ['fr' => 'Analyste cryptomonnaies', 'en' => 'Cryptocurrency analyst'],
                ['fr' => 'Ingénieur blockchain', 'en' => 'Blockchain engineer'],
                ['fr' => 'Smart contract developer', 'en' => 'Smart contract developer'],
                ['fr' => 'Responsable de communauté crypto', 'en' => 'Crypto community manager'],
            ],
            'vr-ar-technology' => [
                ['fr' => 'Développeur VR/AR', 'en' => 'VR/AR developer'],
                ['fr' => 'Designer 3D', 'en' => '3D designer'],
                ['fr' => 'Intégrateur Unity', 'en' => 'Unity developer'],
                ['fr' => 'Technicien en réalité virtuelle', 'en' => 'VR technician'],
                ['fr' => 'Product Owner VR/AR', 'en' => 'VR/AR product owner'],
            ],
            'business-intelligence-strategic-watch' => [
                ['fr' => 'Veilleur stratégique', 'en' => 'Strategic watcher'],
                ['fr' => 'Analyste BI', 'en' => 'BI analyst'],
                ['fr' => 'Data analyst', 'en' => 'Data analyst'],
                ['fr' => 'Consommateur d\'études', 'en' => 'Market researcher'],
            ],

            // ==================== AGRICULTURE & ENVIRONNEMENT ====================
            'agriculture-food' => [
                ['fr' => 'Agriculteur', 'en' => 'Farmer'],
                ['fr' => 'Éleveur', 'en' => 'Livestock farmer'],
                ['fr' => 'Ouvrier agricole', 'en' => 'Farm worker'],
                ['fr' => 'Jardinier paysagiste', 'en' => 'Landscaper gardener'],
                ['fr' => 'Maraîcher', 'en' => 'Market gardener'],
                ['fr' => 'Arboriculteur', 'en' => 'Orchardist'],
                ['fr' => 'Vigneron', 'en' => 'Winemaker'],
                ['fr' => 'Horticulteur', 'en' => 'Horticulturist'],
                ['fr' => 'Pépiniériste', 'en' => 'Nurseryman'],
                ['fr' => 'Apiculteur', 'en' => 'Beekeeper'],
            ],
            'food-processing-agribusiness' => [
                ['fr' => 'Technicien agroalimentaire', 'en' => 'Food processing technician'],
                ['fr' => 'Fromager', 'en' => 'Cheese maker'],
                ['fr' => 'Opérateur en transformation des viandes', 'en' => 'Meat processing operator'],
                ['fr' => 'Condimentaire', 'en' => 'Condiment maker'],
                ['fr' => 'Responsable qualité agroalimentaire', 'en' => 'Quality manager in agrifood'],
            ],
            'fishing-aquaculture' => [
                ['fr' => 'Pêcheur', 'en' => 'Fisherman'],
                ['fr' => 'Aquaculteur', 'en' => 'Aquaculture farmer'],
                ['fr' => 'Ostréiculteur', 'en' => 'Oyster farmer'],
                ['fr' => 'Marin pêcheur', 'en' => 'Fishing boat crew'],
                ['fr' => 'Technicien de pisciculture', 'en' => 'Fish farm technician'],
            ],
            'environment-sustainability' => [
                ['fr' => 'Ingénieur environnement', 'en' => 'Environmental engineer'],
                ['fr' => 'Écologue', 'en' => 'Ecologist'],
                ['fr' => 'Chargé de projet développement durable', 'en' => 'Sustainability project officer'],
                ['fr' => 'Énergéticien', 'en' => 'Energy specialist'],
                ['fr' => 'Conseil en RSE', 'en' => 'CSR consultant'],
            ],
            'renewable-energy' => [
                ['fr' => 'Technicien ENR', 'en' => 'Renewable energy technician'],
                ['fr' => 'Installateur de panneaux solaires', 'en' => 'Solar panel installer'],
                ['fr' => 'Technicien éolien', 'en' => 'Wind turbine technician'],
                ['fr' => 'Ingénieur en énergies renouvelables', 'en' => 'Renewable energy engineer'],
                ['fr' => 'Conseiller en efficacité énergétique', 'en' => 'Energy efficiency advisor'],
            ],
            'water-sanitation' => [
                ['fr' => 'Technicien de l\'eau', 'en' => 'Water technician'],
                ['fr' => 'Égoutier', 'en' => 'Sewer worker'],
                ['fr' => 'Agent de station d\'épuration', 'en' => 'Wastewater treatment plant operator'],
                ['fr' => 'Ingénieur en traitement des eaux', 'en' => 'Water treatment engineer'],
                ['fr' => 'Plombier sanitaire', 'en' => 'Sanitary plumber'],
            ],
            'waste-management-recycling' => [
                ['fr' => 'Éboueur', 'en' => 'Garbage collector'],
                ['fr' => 'Agent de tri', 'en' => 'Sorting agent'],
                ['fr' => 'Technicien en valorisation', 'en' => 'Recycling technician'],
                ['fr' => 'Conducteur de benne', 'en' => 'Bin lorry driver'],
                ['fr' => 'Responsable de déchèterie', 'en' => 'Waste disposal site manager'],
            ],

            // ==================== SÉCURITÉ, DÉFENSE & URGENCE ====================
            'security-safety' => [
                ['fr' => 'Agent de sécurité', 'en' => 'Security guard'],
                ['fr' => 'Agent de sûreté', 'en' => 'Safety officer'],
                ['fr' => 'Responsable sécurité', 'en' => 'Security manager'],
                ['fr' => 'Agent de sécurité incendie', 'en' => 'Fire safety officer'],
                ['fr' => 'Vigile', 'en' => 'Security officer'],
                ['fr' => 'Opérateur de vidéosurveillance', 'en' => 'CCTV operator'],
            ],
            'private-security-surveillance' => [
                ['fr' => 'Agent de sécurité privée', 'en' => 'Private security agent'],
                ['fr' => 'Garde particulier', 'en' => 'Security guard'],
                ['fr' => 'Agent de surveillance', 'en' => 'Surveillance officer'],
                ['fr' => 'Responsable de site de surveillance', 'en' => 'Site security supervisor'],
            ],
            'firefighters-emergency-services' => [
                ['fr' => 'Pompier', 'en' => 'Firefighter'],
                ['fr' => 'Ambulancier', 'en' => 'Ambulance driver'],
                ['fr' => 'Sapeur-pompier professionnel', 'en' => 'Professional firefighter'],
                ['fr' => 'Officier de sapeurs-pompiers', 'en' => 'Fire officer'],
                ['fr' => 'Secouriste', 'en' => 'First responder'],
            ],
            'aerospace-defense' => [
                ['fr' => 'Ingénieur aéronautique', 'en' => 'Aeronautical engineer'],
                ['fr' => 'Mécanicien aéronautique', 'en' => 'Aircraft mechanic'],
                ['fr' => 'Technicien de maintenance aéronautique', 'en' => 'Aircraft maintenance technician'],
                ['fr' => 'Ingénieur en défense', 'en' => 'Defense engineer'],
                ['fr' => 'Opérateur de simulation de vol', 'en' => 'Flight simulator operator'],
            ],

            // ==================== AUTRES SECTEURS ====================
            'automotive' => [
                ['fr' => 'Mécanicien automobile', 'en' => 'Auto mechanic'],
                ['fr' => 'Vendeur automobile', 'en' => 'Car salesperson'],
                ['fr' => 'Carrossier', 'en' => 'Auto body repairer'],
                ['fr' => 'Peintre automobile', 'en' => 'Auto painter'],
                ['fr' => 'Technicien de diagnostic', 'en' => 'Diagnostic technician'],
                ['fr' => 'Responsable après-vente', 'en' => 'After-sales manager'],
            ],
            'electrical-energy' => [
                ['fr' => 'Électricien bâtiment', 'en' => 'Building electrician'],
                ['fr' => 'Technicien ENR', 'en' => 'Renewable energy technician'],
                ['fr' => 'Électrotechnicien', 'en' => 'Electrotechnician'],
                ['fr' => 'Technicien en éclairage public', 'en' => 'Public lighting technician'],
                ['fr' => 'Installateur en câblage', 'en' => 'Cable installer'],
            ],
            'hair-beauty' => [
                ['fr' => 'Coiffeur(se)', 'en' => 'Hairdresser'],
                ['fr' => 'Esthéticien(ne)', 'en' => 'Beautician'],
                ['fr' => 'Maquilleur(se)', 'en' => 'Makeup artist'],
                ['fr' => 'Manucure', 'en' => 'Nail technician'],
                ['fr' => 'Barbier', 'en' => 'Barber'],
                ['fr' => 'Prothésiste ongulaire', 'en' => 'Nail artist'],
            ],
            'craftsmanship-traditional-trades' => [
                ['fr' => 'Artisan d\'art', 'en' => 'Craftsman'],
                ['fr' => 'Bijoutier', 'en' => 'Jeweler'],
                ['fr' => 'Céramiste', 'en' => 'Ceramist'],
                ['fr' => 'Verrerie', 'en' => 'Glassblower'],
                ['fr' => 'Tapissier', 'en' => 'Upholsterer'],
                ['fr' => 'Maroquinier', 'en' => 'Leather worker'],
                ['fr' => 'Luthier', 'en' => 'Luthier'],
                ['fr' => 'Émailleur', 'en' => 'Enameler'],
            ],
            'social-solidarity-economy' => [
                ['fr' => 'Responsable d\'association', 'en' => 'Association manager'],
                ['fr' => 'Coordinateur de projet solidaire', 'en' => 'Solidarity project coordinator'],
                ['fr' => 'Animateur de coopérative', 'en' => 'Cooperative animator'],
                ['fr' => 'Chargé de mission ESS', 'en' => 'Social economy mission officer'],
            ],
            'coaching-personal-development' => [
                ['fr' => 'Coach professionnel', 'en' => 'Professional coach'],
                ['fr' => 'Coach en développement personnel', 'en' => 'Personal development coach'],
                ['fr' => 'Formateur en soft skills', 'en' => 'Soft skills trainer'],
                ['fr' => 'Consultant en management', 'en' => 'Management consultant'],
                ['fr' => 'Médiateur', 'en' => 'Mediator'],
            ],
            'insurance-risk-management' => [
                ['fr' => 'Assureur', 'en' => 'Insurance agent'],
                ['fr' => 'Gestionnaire de sinistres', 'en' => 'Claims handler'],
                ['fr' => 'Expert en sinistres', 'en' => 'Claims adjuster'],
                ['fr' => 'Courtier en assurances', 'en' => 'Insurance broker'],
                ['fr' => 'Responsable risques', 'en' => 'Risk manager'],
            ],
            'microfinance-financial-inclusion' => [
                ['fr' => 'Agent de crédit', 'en' => 'Loan officer'],
                ['fr' => 'Conseiller microfinance', 'en' => 'Microfinance advisor'],
                ['fr' => 'Chargé d\'inclusion financière', 'en' => 'Financial inclusion officer'],
                ['fr' => 'Gestionnaire de portefeuille microfinance', 'en' => 'Microfinance portfolio manager'],
            ],
            'education-training' => [
                ['fr' => 'Formateur(trice)', 'en' => 'Trainer'],
                ['fr' => 'Enseignant(e)', 'en' => 'Teacher'],
                ['fr' => 'Professeur', 'en' => 'Professor'],
                ['fr' => 'Instructeur', 'en' => 'Instructor'],
                ['fr' => 'Tuteur', 'en' => 'Tutor'],
                ['fr' => 'Coordinateur pédagogique', 'en' => 'Educational coordinator'],
            ],
            'humanitarian-logistics-emergency-relief' => [
                ['fr' => 'Logisticien humanitaire', 'en' => 'Humanitarian logistician'],
                ['fr' => 'Coordinateur d\'urgence', 'en' => 'Emergency coordinator'],
                ['fr' => 'Responsable de mission ONG', 'en' => 'NGO mission manager'],
                ['fr' => 'Acheteur humanitaire', 'en' => 'Humanitarian buyer'],
                ['fr' => 'Référent sécurité terrain', 'en' => 'Field security officer'],
            ],
        ];
    }
}