<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * L'ordre est crucial pour respecter les contraintes de clés étrangères.
     */
    public function run(): void
    {
        // --- 1. PERMISSIONS & RÔLES ---
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            RecruiterOrganizationSeeder::class,
            LegalDocumentSeeder::class,
        ]);

        // --- 2. RÉFÉRENTIELS DE BASE (Niveau 0) ---
        // Ces tables ne dépendent d'aucune autre.
        $this->call([
            DocumentTypeSeeder::class,
            OfferDependenciesSeeder::class, // Assure les FK minimales pour offers
            GeographicZoneSeeder::class, // Zones mondiales (Nécessaire pour Programs)
            CategorySeeder::class,  // Catégories de métiers (Nécessaire pour Offers)
            TradeSeeder::class,       // Métiers rattachés aux catégories
            ContractTypeSeeder::class,   // Types de contrats (Nécessaire pour Offers)
            OfferTypeSeeder::class,      // Types d'offre (FK offers.offer_type_id)
            WorkScheduleSeeder::class,   // Horaires de travail (FK offers.work_schedule_id)
            EducationLevelSeeder::class, // Niveaux d'étude (FK offers.education_level_id)
            LanguageLevelSeeder::class,  // Niveaux de langue (FK language_offer.language_level_id)
            LanguageSeeder::class,       // Langues référentielles (FK/pivots language_*)
            SkillSeeder::class,          // Compétences (table skills)
            BenefitSeeder::class,        // Avantages sociaux (Relation Many-to-Many)
            DiscoverySourceSeeder::class, // Provenance (Comment avez-vous connu JBIS ?)
            TrainingSeeder::class,       // Catalogue formations (ex. cours d'anglais)
        ]);

        // --- 3. GÉOGRAPHIE & PARTENAIRES (Niveau 1) ---
        // LocationSeeder s'occupe de Pays -> Régions -> Villes
        $this->call([
            CountrySeeder::class,        // Liste des pays
            LocationSeeder::class,       // Régions et Villes liées aux pays
            CompanySeeder::class,        // Partenaires (Aman Taxi, etc.)
            AgencySeeder::class,         // Agences JBIS
        ]);

        // --- 4. CŒUR DU CATALOGUE (Niveau 2) ---
        // Les programmes dépendent des Zones Géo et de l'Admin.
        $this->call([
            ProgramSeeder::class,        // Canada, Dubaï, Albanie...
        ]);

        // --- 5. OFFRES D'EMPLOI (Niveau 3) ---
        // Les offres sont le point final car elles dépendent de :
        // Program, Category, ContractType, City, Country, Company et User.
        $this->call([
            OfferSeeder::class,
            RequiredDocumentSeeder::class,
        ]);

        // --- 6. PARCOURS PROCÉDURAUX (templates abstraits, sans FK programme/offre/pays) ---
        $this->call([
            ProcessFlowSeeder::class,
        ]);
    }
}
