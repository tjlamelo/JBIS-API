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
                ['fr' => 'Secrétariat et gestion administrative courante', 'en' => 'General secretarial and office administration', 'category' => 'technique'],
                ['fr' => 'Microsoft Word', 'en' => 'Microsoft Word', 'category' => 'technique'],
                ['fr' => 'Microsoft Excel', 'en' => 'Microsoft Excel', 'category' => 'technique'],
                ['fr' => 'Microsoft PowerPoint', 'en' => 'Microsoft PowerPoint', 'category' => 'technique'],
                ['fr' => 'Microsoft Outlook', 'en' => 'Microsoft Outlook', 'category' => 'technique'],
                ['fr' => 'Google Docs', 'en' => 'Google Docs', 'category' => 'technique'],
                ['fr' => 'Google Sheets', 'en' => 'Google Sheets', 'category' => 'technique'],
                ['fr' => 'LibreOffice Writer', 'en' => 'LibreOffice Writer', 'category' => 'technique'],
                ['fr' => 'LibreOffice Calc', 'en' => 'LibreOffice Calc', 'category' => 'technique'],
                ['fr' => 'Rigueur et exactitude', 'en' => 'Rigor and accuracy', 'category' => 'soft-skill'],
                ['fr' => 'Organisation et planification', 'en' => 'Organization and planning', 'category' => 'soft-skill'],
                ['fr' => 'Analyse critique et synthèse', 'en' => 'Critical analysis and synthesis', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('legal-services', [
                ['fr' => 'Droit des contrats et des sociétés', 'en' => 'Contract and corporate law', 'category' => 'technique'],
                ['fr' => 'Droit du travail et gestion des RH', 'en' => 'Labor law and HR management', 'category' => 'technique'],
                ['fr' => 'Rédaction d’actes juridiques et notariés', 'en' => 'Drafting legal and notarial deeds', 'category' => 'technique'],
                ['fr' => 'Plaidoirie et représentation en justice', 'en' => 'Pleading and courtroom representation', 'category' => 'technique'],
                ['fr' => 'Veille juridique et réglementaire', 'en' => 'Legal and regulatory watch', 'category' => 'technique'],
                ['fr' => 'Gestion de dossiers contentieux', 'en' => 'Litigation case management', 'category' => 'technique'],
                ['fr' => 'Discrétion et confidentialité', 'en' => 'Discretion and confidentiality', 'category' => 'soft-skill'],
                ['fr' => 'Négociation et médiation', 'en' => 'Negotiation and mediation', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('banking-finance', [
                ['fr' => 'Fiscalité (TVA, impôts sur les sociétés)', 'en' => 'Taxation (VAT, corporate tax)', 'category' => 'technique'],
                ['fr' => 'Analyse financière et interprétation des bilans', 'en' => 'Financial analysis and balance sheet interpretation', 'category' => 'technique'],
                ['fr' => 'Gestion de trésorerie et cash management', 'en' => 'Treasury management and cash management', 'category' => 'technique'],
                ['fr' => 'Crédit, scoring et gestion du risque client', 'en' => 'Credit, scoring and customer risk management', 'category' => 'technique'],
                ['fr' => 'Conformité et lutte anti-blanchiment (AML/CFT)', 'en' => 'Compliance and anti-money laundering (AML/CFT)', 'category' => 'technique'],
                ['fr' => 'Produits financiers et marchés (actions, obligations)', 'en' => 'Financial products and markets (equities, bonds)', 'category' => 'technique'],
                ['fr' => 'Modélisation et prévision financière', 'en' => 'Financial modelling and forecasting', 'category' => 'technique'],
                ['fr' => 'Reporting réglementaire et conformité bancaire', 'en' => 'Regulatory reporting and banking compliance', 'category' => 'technique'],
                ['fr' => 'Relation client bancaire et conseil patrimonial', 'en' => 'Bank client relations and wealth advisory', 'category' => 'technique'],
                ['fr' => 'Rigueur et sens de l’éthique professionnelle', 'en' => 'Rigor and professional ethics', 'category' => 'soft-skill'],
                ['fr' => 'Précision et souci du détail', 'en' => 'Precision and attention to detail', 'category' => 'soft-skill'],
            ]),

            // Informatique & numérique
            $this->categorySkills('it-technology', [
                // Langages
                ['fr' => 'Python', 'en' => 'Python', 'category' => 'technique'],
                ['fr' => 'Java', 'en' => 'Java', 'category' => 'technique'],
                ['fr' => 'C', 'en' => 'C', 'category' => 'technique'],
                ['fr' => 'C++', 'en' => 'C++', 'category' => 'technique'],
                ['fr' => 'C#', 'en' => 'C#', 'category' => 'technique'],
                ['fr' => 'PHP', 'en' => 'PHP', 'category' => 'technique'],
                ['fr' => 'JavaScript', 'en' => 'JavaScript', 'category' => 'technique'],
                ['fr' => 'TypeScript', 'en' => 'TypeScript', 'category' => 'technique'],
                ['fr' => 'Go', 'en' => 'Go', 'category' => 'technique'],
                ['fr' => 'Rust', 'en' => 'Rust', 'category' => 'technique'],
                ['fr' => 'Ruby', 'en' => 'Ruby', 'category' => 'technique'],
                ['fr' => 'Kotlin', 'en' => 'Kotlin', 'category' => 'technique'],
                ['fr' => 'Swift', 'en' => 'Swift', 'category' => 'technique'],
                ['fr' => 'Dart', 'en' => 'Dart', 'category' => 'technique'],
                // Web
                ['fr' => 'HTML', 'en' => 'HTML', 'category' => 'technique'],
                ['fr' => 'CSS', 'en' => 'CSS', 'category' => 'technique'],
                ['fr' => 'React', 'en' => 'React', 'category' => 'technique'],
                ['fr' => 'Vue.js', 'en' => 'Vue.js', 'category' => 'technique'],
                ['fr' => 'Angular', 'en' => 'Angular', 'category' => 'technique'],
                ['fr' => 'Next.js', 'en' => 'Next.js', 'category' => 'technique'],
                ['fr' => 'Node.js', 'en' => 'Node.js', 'category' => 'technique'],
                ['fr' => 'Laravel', 'en' => 'Laravel', 'category' => 'technique'],
                ['fr' => 'Symfony', 'en' => 'Symfony', 'category' => 'technique'],
                ['fr' => 'Django', 'en' => 'Django', 'category' => 'technique'],
                ['fr' => 'Spring Boot', 'en' => 'Spring Boot', 'category' => 'technique'],
                // Bases de données
                ['fr' => 'SQL', 'en' => 'SQL', 'category' => 'technique'],
                ['fr' => 'MySQL', 'en' => 'MySQL', 'category' => 'technique'],
                ['fr' => 'PostgreSQL', 'en' => 'PostgreSQL', 'category' => 'technique'],
                ['fr' => 'MongoDB', 'en' => 'MongoDB', 'category' => 'technique'],
                ['fr' => 'Redis', 'en' => 'Redis', 'category' => 'technique'],
                // CAO / DAO
                ['fr' => 'AutoCAD', 'en' => 'AutoCAD', 'category' => 'technique'],
                ['fr' => 'SolidWorks', 'en' => 'SolidWorks', 'category' => 'technique'],
                ['fr' => 'Revit', 'en' => 'Revit', 'category' => 'technique'],
                ['fr' => 'SketchUp', 'en' => 'SketchUp', 'category' => 'technique'],
                ['fr' => 'Catia', 'en' => 'Catia', 'category' => 'technique'],
                // Mobile & systèmes
                ['fr' => 'Flutter', 'en' => 'Flutter', 'category' => 'technique'],
                ['fr' => 'React Native', 'en' => 'React Native', 'category' => 'technique'],
                ['fr' => 'Android (natif)', 'en' => 'Android (native)', 'category' => 'technique'],
                ['fr' => 'iOS (natif)', 'en' => 'iOS (native)', 'category' => 'technique'],
                ['fr' => 'Électronique embarquée et systèmes temps réel', 'en' => 'Embedded electronics and real-time systems', 'category' => 'technique'],
                ['fr' => 'Support utilisateur et helpdesk', 'en' => 'User support and helpdesk', 'category' => 'technique'],
                ['fr' => 'Architecture logicielle et API REST', 'en' => 'Software architecture and REST APIs', 'category' => 'technique'],
                ['fr' => 'Tests et assurance qualité logicielle', 'en' => 'Software testing and QA', 'category' => 'technique'],
                ['fr' => 'Git', 'en' => 'Git', 'category' => 'technique'],
                ['fr' => 'Résolution de problèmes complexes', 'en' => 'Complex problem solving', 'category' => 'soft-skill'],
                ['fr' => 'Esprit d’analyse et de synthèse', 'en' => 'Analytical and synthetic thinking', 'category' => 'soft-skill'],
                ['fr' => 'Adaptabilité aux évolutions technologiques', 'en' => 'Adaptability to technological changes', 'category' => 'soft-skill'],
                ['fr' => 'Travail en équipe agile', 'en' => 'Agile teamwork', 'category' => 'soft-skill'],
                ['fr' => 'Curiosité et veille technologique', 'en' => 'Curiosity and technological watch', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('digital-trades-data-cloud-cybersecurity', [
                ['fr' => 'TCP/IP', 'en' => 'TCP/IP', 'category' => 'technique'],
                ['fr' => 'Pare-feu / Firewall', 'en' => 'Firewall administration', 'category' => 'technique'],
                ['fr' => 'VPN', 'en' => 'VPN', 'category' => 'technique'],
                ['fr' => 'AWS', 'en' => 'AWS', 'category' => 'technique'],
                ['fr' => 'Microsoft Azure', 'en' => 'Microsoft Azure', 'category' => 'technique'],
                ['fr' => 'Google Cloud Platform (GCP)', 'en' => 'Google Cloud Platform (GCP)', 'category' => 'technique'],
                ['fr' => 'Pentesting', 'en' => 'Penetration testing', 'category' => 'technique'],
                ['fr' => 'RGPD / GDPR', 'en' => 'GDPR compliance', 'category' => 'technique'],
                ['fr' => 'ISO 27001', 'en' => 'ISO 27001', 'category' => 'technique'],
                ['fr' => 'Docker', 'en' => 'Docker', 'category' => 'technique'],
                ['fr' => 'Kubernetes', 'en' => 'Kubernetes', 'category' => 'technique'],
                ['fr' => 'Jenkins', 'en' => 'Jenkins', 'category' => 'technique'],
                ['fr' => 'GitHub Actions', 'en' => 'GitHub Actions', 'category' => 'technique'],
                ['fr' => 'GitLab CI', 'en' => 'GitLab CI', 'category' => 'technique'],
                ['fr' => 'Terraform', 'en' => 'Terraform', 'category' => 'technique'],
                ['fr' => 'Ansible', 'en' => 'Ansible', 'category' => 'technique'],
                ['fr' => 'Ingénierie des données et pipelines ETL', 'en' => 'Data engineering and ETL pipelines', 'category' => 'technique'],
                ['fr' => 'Gouvernance des données et protection (RGPD)', 'en' => 'Data governance and protection (GDPR)', 'category' => 'technique'],
                ['fr' => 'Administration de bases de données', 'en' => 'Database administration', 'category' => 'technique'],
                ['fr' => 'Gestion de la sécurité des systèmes d’information', 'en' => 'Information systems security management', 'category' => 'technique'],
                ['fr' => 'Architecture cloud multi-tenant', 'en' => 'Multi-tenant cloud architecture', 'category' => 'technique'],
            ]),
            $this->categorySkills('artificial-intelligence', [
                ['fr' => 'Intelligence artificielle et machine learning', 'en' => 'Artificial intelligence and machine learning', 'category' => 'technique'],
                ['fr' => 'TensorFlow', 'en' => 'TensorFlow', 'category' => 'technique'],
                ['fr' => 'PyTorch', 'en' => 'PyTorch', 'category' => 'technique'],
                ['fr' => 'scikit-learn', 'en' => 'scikit-learn', 'category' => 'technique'],
                ['fr' => 'Traitement du langage naturel (NLP)', 'en' => 'Natural Language Processing (NLP)', 'category' => 'technique'],
                ['fr' => 'Vision par ordinateur (Computer Vision)', 'en' => 'Computer Vision', 'category' => 'technique'],
                ['fr' => 'Pré-traitement des données et feature engineering', 'en' => 'Data preprocessing and feature engineering', 'category' => 'technique'],
                ['fr' => 'MLOps et déploiement de modèles', 'en' => 'MLOps and model deployment', 'category' => 'technique'],
                ['fr' => 'Statistiques appliquées et probabilités', 'en' => 'Applied statistics and probability', 'category' => 'technique'],
                ['fr' => 'Évaluation des modèles et métriques (AUC, F1)', 'en' => 'Model evaluation and metrics (AUC, F1)', 'category' => 'technique'],
                ['fr' => 'Explicabilité et biais algorithmique', 'en' => 'Explainability and algorithmic bias', 'category' => 'technique'],
                ['fr' => 'Curiosité scientifique et rigueur expérimentale', 'en' => 'Scientific curiosity and experimental rigor', 'category' => 'soft-skill'],
            ]),

            // Commerce & marketing
            $this->categorySkills('marketing-advertising', [
                ['fr' => 'Stratégie marketing (4P, SWOT, segmentation)', 'en' => 'Marketing strategy (4Ps, SWOT, segmentation)', 'category' => 'technique'],
                ['fr' => 'SEO', 'en' => 'SEO', 'category' => 'technique'],
                ['fr' => 'SEA / Google Ads', 'en' => 'SEA / Google Ads', 'category' => 'technique'],
                ['fr' => 'Meta Ads', 'en' => 'Meta Ads', 'category' => 'technique'],
                ['fr' => 'Création de contenu rédactionnel', 'en' => 'Written content creation', 'category' => 'technique'],
                ['fr' => 'Création de contenu vidéo', 'en' => 'Video content creation', 'category' => 'technique'],
                ['fr' => 'Salesforce', 'en' => 'Salesforce', 'category' => 'technique'],
                ['fr' => 'HubSpot', 'en' => 'HubSpot', 'category' => 'technique'],
                ['fr' => 'Mailchimp', 'en' => 'Mailchimp', 'category' => 'technique'],
                ['fr' => 'Études de marché et analyse de données', 'en' => 'Market research and data analysis', 'category' => 'technique'],
                ['fr' => 'Gestion des réseaux sociaux et community management', 'en' => 'Social media and community management', 'category' => 'technique'],
                ['fr' => 'Relations publiques et communication d’influence', 'en' => 'Public relations and influencer communication', 'category' => 'technique'],
                ['fr' => 'E-commerce (gestion de boutique en ligne)', 'en' => 'E-commerce (online store management)', 'category' => 'technique'],
                ['fr' => 'Persuasion et influence', 'en' => 'Persuasion and influence', 'category' => 'soft-skill'],
                ['fr' => 'Créativité et innovation', 'en' => 'Creativity and innovation', 'category' => 'soft-skill'],
                ['fr' => 'Empathie client et écoute active', 'en' => 'Customer empathy and active listening', 'category' => 'soft-skill'],
                ['fr' => 'Prise de parole en public', 'en' => 'Public speaking', 'category' => 'soft-skill'],
                ['fr' => 'Gestion du temps et des priorités', 'en' => 'Time and priority management', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('sales-distribution', [
                ['fr' => 'Techniques de vente et closing', 'en' => 'Sales techniques and closing', 'category' => 'technique'],
                ['fr' => 'Prospection commerciale (phones, emailing, social selling)', 'en' => 'Commercial prospecting (phone, email, social selling)', 'category' => 'technique'],
                ['fr' => 'Gestion de comptes clés (Key Account Management)', 'en' => 'Key Account Management', 'category' => 'technique'],
                ['fr' => 'Négociation commerciale et argumentation', 'en' => 'Commercial negotiation and persuasion', 'category' => 'technique'],
                ['fr' => 'Prévision des ventes et pipeline forecasting', 'en' => 'Sales forecasting and pipeline management', 'category' => 'technique'],
                ['fr' => 'Gestion des canaux de distribution et merchandising', 'en' => 'Channel management and merchandising', 'category' => 'technique'],
                ['fr' => 'Service après-vente et fidélisation client', 'en' => 'After-sales service and customer retention', 'category' => 'technique'],
                ['fr' => 'Salesforce', 'en' => 'Salesforce', 'category' => 'technique'],
                ['fr' => 'HubSpot', 'en' => 'HubSpot', 'category' => 'technique'],
                ['fr' => 'Orientation résultat et ténacité', 'en' => 'Result orientation and perseverance', 'category' => 'soft-skill'],
                ['fr' => 'Empathie commerciale et écoute active', 'en' => 'Commercial empathy and active listening', 'category' => 'soft-skill'],
            ]),

            // Santé & social
            $this->categorySkills('medical-paramedical', [
                ['fr' => 'Soins infirmiers de base (pose de perfusion, pansements)', 'en' => 'Basic nursing care (IV, dressings)', 'category' => 'technique'],
                ['fr' => 'Surveillance des paramètres vitaux', 'en' => 'Vital signs monitoring', 'category' => 'technique'],
                ['fr' => 'Réanimation cardio-pulmonaire (RCP) et premiers secours', 'en' => 'CPR and first aid', 'category' => 'technique'],
                ['fr' => 'Hygiène et asepsie (bloc opératoire, stérilisation)', 'en' => 'Hygiene and asepsis (operating room, sterilization)', 'category' => 'technique'],
                ['fr' => 'Gestion des dossiers médicaux (DMP, logiciels métier)', 'en' => 'Medical records management (DMP, EMR)', 'category' => 'technique'],
                ['fr' => 'Diagnostic médical et examen clinique', 'en' => 'Medical diagnosis and clinical examination', 'category' => 'technique'],
                ['fr' => 'Actes chirurgicaux et interventions', 'en' => 'Surgical procedures and operations', 'category' => 'technique'],
                ['fr' => 'Anesthésie et réanimation peropératoire', 'en' => 'Anesthesia and perioperative care', 'category' => 'technique'],
                ['fr' => 'Suivi de grossesse et gynécologie', 'en' => 'Pregnancy monitoring and gynecology', 'category' => 'technique'],
                ['fr' => 'Pédiatrie et suivi de la croissance de l’enfant', 'en' => 'Pediatrics and child growth monitoring', 'category' => 'technique'],
                ['fr' => 'Cardiologie et exploration cardiovasculaire', 'en' => 'Cardiology and cardiovascular examination', 'category' => 'technique'],
                ['fr' => 'Ophtalmologie et examens de la vue', 'en' => 'Ophthalmology and eye examinations', 'category' => 'technique'],
                ['fr' => 'Imagerie médicale et manipulation radio', 'en' => 'Medical imaging and radiologic manipulation', 'category' => 'technique'],
                ['fr' => 'Analyses de laboratoire et biologie médicale', 'en' => 'Laboratory analysis and medical biology', 'category' => 'technique'],
                ['fr' => 'Rééducation fonctionnelle et kinésithérapie', 'en' => 'Functional rehabilitation and physiotherapy', 'category' => 'technique'],
                ['fr' => 'Ergothérapie et adaptation du quotidien', 'en' => 'Occupational therapy and daily living adaptation', 'category' => 'technique'],
                ['fr' => 'Patience et résilience', 'en' => 'Patience and resilience', 'category' => 'soft-skill'],
                ['fr' => 'Travail en équipe pluridisciplinaire', 'en' => 'Multidisciplinary teamwork', 'category' => 'soft-skill'],
                ['fr' => 'Sens de l’observation et de l’anticipation', 'en' => 'Observation and anticipation', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('personal-care-assistance', [
                ['fr' => 'Aide à la mobilité et transfert de patients', 'en' => 'Patient mobility and transfer assistance', 'category' => 'technique'],
                ['fr' => 'Médico-social : accompagnement des personnes âgées ou handicapées', 'en' => 'Social support for elderly or disabled persons', 'category' => 'technique'],
                ['fr' => 'Aide à la toilette et aux actes essentiels', 'en' => 'Assistance with hygiene and essential daily acts', 'category' => 'technique'],
                ['fr' => 'Téléassistance et suivi à distance', 'en' => 'Telecare and remote monitoring', 'category' => 'technique'],
                ['fr' => 'Accompagnement éducatif spécialisé', 'en' => 'Specialized educational support', 'category' => 'technique'],
                ['fr' => 'Entretien du cadre de vie', 'en' => 'Living environment upkeep', 'category' => 'technique'],
                ['fr' => 'Empathie et bienveillance', 'en' => 'Empathy and kindness', 'category' => 'soft-skill'],
                ['fr' => 'Discrétion et respect de la vie privée', 'en' => 'Discretion and privacy respect', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('mental-health-wellness', [
                ['fr' => 'Éducation thérapeutique et suivi médical', 'en' => 'Therapeutic education and medical follow-up', 'category' => 'technique'],
                ['fr' => 'Psychologie et techniques d’entretien', 'en' => 'Psychology and interview techniques', 'category' => 'technique'],
                ['fr' => 'Médiation familiale et sociale', 'en' => 'Family and social mediation', 'category' => 'technique'],
                ['fr' => 'Diagnostic et suivi psychiatrique', 'en' => 'Psychiatric diagnosis and follow-up', 'category' => 'technique'],
                ['fr' => 'Techniques psychothérapeutiques (TCC, etc.)', 'en' => 'Psychotherapeutic techniques (CBT, etc.)', 'category' => 'technique'],
                ['fr' => 'Accompagnement à l’insertion socioprofessionnelle', 'en' => 'Socio-professional integration support', 'category' => 'technique'],
                ['fr' => 'Éducation spécialisée et accompagnement éducatif', 'en' => 'Special education and educational support', 'category' => 'technique'],
                ['fr' => 'Écoute active et non-jugement', 'en' => 'Active listening and non-judgment', 'category' => 'soft-skill'],
            ]),

            // Création & médias
            $this->categorySkills('design-creative', [
                ['fr' => 'Dessin et illustration (croquis, infographie)', 'en' => 'Drawing and illustration (sketch, graphic design)', 'category' => 'technique'],
                ['fr' => 'Adobe Photoshop', 'en' => 'Adobe Photoshop', 'category' => 'technique'],
                ['fr' => 'Adobe Illustrator', 'en' => 'Adobe Illustrator', 'category' => 'technique'],
                ['fr' => 'Adobe InDesign', 'en' => 'Adobe InDesign', 'category' => 'technique'],
                ['fr' => 'Figma', 'en' => 'Figma', 'category' => 'technique'],
                ['fr' => 'Adobe XD', 'en' => 'Adobe XD', 'category' => 'technique'],
                ['fr' => 'Canva', 'en' => 'Canva', 'category' => 'technique'],
                ['fr' => 'Direction artistique et identité visuelle', 'en' => 'Art direction and visual identity', 'category' => 'technique'],
                ['fr' => 'Motion design et animation', 'en' => 'Motion design and animation', 'category' => 'technique'],
                ['fr' => 'Design produit et industrialisation', 'en' => 'Product design and industrialization', 'category' => 'technique'],
                ['fr' => 'Créativité et imagination', 'en' => 'Creativity and imagination', 'category' => 'soft-skill'],
                ['fr' => 'Sensibilité esthétique et culturelle', 'en' => 'Aesthetic and cultural sensitivity', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('photography-av', [
                ['fr' => 'Adobe Lightroom', 'en' => 'Adobe Lightroom', 'category' => 'technique'],
                ['fr' => 'Capture One', 'en' => 'Capture One', 'category' => 'technique'],
                ['fr' => 'Adobe Premiere Pro', 'en' => 'Adobe Premiere Pro', 'category' => 'technique'],
                ['fr' => 'Adobe After Effects', 'en' => 'Adobe After Effects', 'category' => 'technique'],
                ['fr' => 'DaVinci Resolve', 'en' => 'DaVinci Resolve', 'category' => 'technique'],
                ['fr' => 'Blender', 'en' => 'Blender', 'category' => 'technique'],
                ['fr' => 'Autodesk Maya', 'en' => 'Autodesk Maya', 'category' => 'technique'],
                ['fr' => 'Ableton Live', 'en' => 'Ableton Live', 'category' => 'technique'],
                ['fr' => 'Pro Tools', 'en' => 'Pro Tools', 'category' => 'technique'],
                ['fr' => 'Techniques d’impression (sérigraphie, lithographie)', 'en' => 'Print techniques (serigraphy, lithography)', 'category' => 'technique'],
                ['fr' => 'Prise de vue et cadrage', 'en' => 'Shooting and framing', 'category' => 'technique'],
                ['fr' => 'Réalisation et direction de tournage', 'en' => 'Directing and film production', 'category' => 'technique'],
                ['fr' => 'Ingénierie du son et mixage', 'en' => 'Sound engineering and mixing', 'category' => 'technique'],
            ]),
            $this->categorySkills('media-journalism', [
                ['fr' => 'Écriture créative (roman, scénario, poésie)', 'en' => 'Creative writing (novel, script, poetry)', 'category' => 'technique'],
                ['fr' => 'Médiation culturelle et animation d’ateliers', 'en' => 'Cultural mediation and workshop facilitation', 'category' => 'technique'],
                ['fr' => 'Gestion de projet culturel et événementiel', 'en' => 'Cultural and event project management', 'category' => 'technique'],
                ['fr' => 'Rédaction journalistique et enquête', 'en' => 'Journalistic writing and investigation', 'category' => 'technique'],
                ['fr' => 'Gestion de rédaction et ligne éditoriale', 'en' => 'Editorial management and content strategy', 'category' => 'technique'],
                ['fr' => 'Communication institutionnelle et relations presse', 'en' => 'Institutional communication and press relations', 'category' => 'technique'],
                ['fr' => 'Gestion de campagnes publicitaires (traffic management)', 'en' => 'Ad campaign traffic management', 'category' => 'technique'],
                ['fr' => 'Autonomie et auto-discipline', 'en' => 'Autonomy and self-discipline', 'category' => 'soft-skill'],
                ['fr' => 'Ouverture d’esprit et curiosité', 'en' => 'Open-mindedness and curiosity', 'category' => 'soft-skill'],
                ['fr' => 'Capacité à recevoir et donner des feedbacks', 'en' => 'Ability to give and receive feedback', 'category' => 'soft-skill'],
            ]),

            // BTP & industrie
            $this->categorySkills('construction-structural-work', [
                ['fr' => 'Lecture de plans et schémas techniques', 'en' => 'Blueprint and technical drawing reading', 'category' => 'technique'],
                ['fr' => 'Charpente et ossature bois', 'en' => 'Timber framing and wooden structures', 'category' => 'technique'],
                ['fr' => 'Maçonnerie et travaux de gros œuvre', 'en' => 'Masonry and structural work', 'category' => 'technique'],
                ['fr' => 'Ferraillage et pose d’armatures', 'en' => 'Rebar fixing and reinforcement', 'category' => 'technique'],
                ['fr' => 'Coffrage et coulage du béton', 'en' => 'Formwork and concrete pouring', 'category' => 'technique'],
                ['fr' => 'Topographie et implantation de chantier', 'en' => 'Surveying and site layout', 'category' => 'technique'],
                ['fr' => 'Pilotage de chantier et coordination', 'en' => 'Site management and coordination', 'category' => 'technique'],
                ['fr' => 'Terrassement et travaux de voirie', 'en' => 'Earthworks and road works', 'category' => 'technique'],
                ['fr' => 'Pose de canalisations et réseaux enterrés', 'en' => 'Pipe laying and underground networks', 'category' => 'technique'],
                ['fr' => 'Sécurité sur chantier BTP', 'en' => 'Construction site safety', 'category' => 'soft-skill'],
                ['fr' => 'Résistance physique et travail en extérieur', 'en' => 'Physical endurance and outdoor work', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('construction-finishing-work', [
                ['fr' => 'Plomberie, chauffage et ventilation (CVAC)', 'en' => 'Plumbing, heating and ventilation (HVAC)', 'category' => 'technique'],
                ['fr' => 'Carrelage et pose de revêtements muraux', 'en' => 'Tiling and wall covering installation', 'category' => 'technique'],
                ['fr' => 'Peinture et finitions décoratives', 'en' => 'Painting and decorative finishes', 'category' => 'technique'],
                ['fr' => 'Plâtrerie et isolation intérieure', 'en' => 'Plastering and interior insulation', 'category' => 'technique'],
                ['fr' => 'Menuiserie intérieure et agencement', 'en' => 'Interior carpentry and fittings', 'category' => 'technique'],
                ['fr' => 'Pose de revêtements de sol (parquet, PVC)', 'en' => 'Floor coverings installation (parquet, PVC)', 'category' => 'technique'],
                ['fr' => 'Électricité de chantier et câblage', 'en' => 'Construction electrical wiring', 'category' => 'technique'],
                ['fr' => 'Fabrication et pose d’agencements sur-mesure', 'en' => 'Custom fit-out fabrication and installation', 'category' => 'technique'],
                ['fr' => 'Pose de menuiseries PVC et aluminium', 'en' => 'PVC and aluminium joinery installation', 'category' => 'technique'],
                ['fr' => 'Finitions et sens du détail', 'en' => 'Finishing and attention to detail', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('welding-metallurgy', [
                ['fr' => 'Soudure (MIG, TIG, arc) et assemblage métallique', 'en' => 'Welding (MIG, TIG, arc) and metal assembly', 'category' => 'technique'],
                ['fr' => 'Métallerie et serrurerie', 'en' => 'Metalwork and locksmithing', 'category' => 'technique'],
                ['fr' => 'Soudage TIG haute précision', 'en' => 'High-precision TIG welding', 'category' => 'technique'],
                ['fr' => 'Soudage MIG/MAG', 'en' => 'MIG/MAG welding', 'category' => 'technique'],
                ['fr' => 'Soudage à l’arc', 'en' => 'Arc welding', 'category' => 'technique'],
                ['fr' => 'Chaudronnerie et façonnage de tôles', 'en' => 'Boilermaking and sheet metal forming', 'category' => 'technique'],
                ['fr' => 'Tuyauterie industrielle', 'en' => 'Industrial pipefitting', 'category' => 'technique'],
                ['fr' => 'Traçage et découpe de métaux', 'en' => 'Metal marking and cutting', 'category' => 'technique'],
                ['fr' => 'Lecture de plans de fabrication métallique', 'en' => 'Metal fabrication drawing reading', 'category' => 'technique'],
                ['fr' => 'Sécurité et précision au travail', 'en' => 'Safety and precision at work', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('heavy-machinery-operation', [
                ['fr' => 'Conduite d’engins de chantier (grue, pelleteuse, nacelle)', 'en' => 'Construction machinery operation (crane, excavator, boom)', 'category' => 'technique'],
                ['fr' => 'Sécurité et réglementation des chantiers', 'en' => 'Site safety and regulations', 'category' => 'technique'],
                ['fr' => 'Maintenance de premier niveau des engins', 'en' => 'Basic machinery maintenance', 'category' => 'technique'],
                ['fr' => 'Préparation et balisage de zones de chantier', 'en' => 'Site preparation and marking', 'category' => 'technique'],
                ['fr' => 'Conduite de grue mobile', 'en' => 'Mobile crane operation', 'category' => 'technique'],
                ['fr' => 'Conduite de niveleuse et compacteur', 'en' => 'Grader and roller operation', 'category' => 'technique'],
                ['fr' => 'Manutention lourde et levage', 'en' => 'Heavy handling and lifting', 'category' => 'technique'],
                ['fr' => 'Habilitations CACES', 'en' => 'CACES certifications', 'category' => 'technique'],
                ['fr' => 'Vigilance et précision de conduite', 'en' => 'Vigilance and driving precision', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('industrial-maintenance', [
                ['fr' => 'Maintenance mécanique et hydraulique', 'en' => 'Mechanical and hydraulic maintenance', 'category' => 'technique'],
                ['fr' => 'Maintenance préventive et curative', 'en' => 'Preventive and corrective maintenance', 'category' => 'technique'],
                ['fr' => 'Diagnostic de pannes et dépannage', 'en' => 'Fault diagnosis and troubleshooting', 'category' => 'technique'],
                ['fr' => 'Automates programmables (PLC) et supervision', 'en' => 'Programmable logic controllers (PLC) and SCADA', 'category' => 'technique'],
                ['fr' => 'Maintenance prédictive et capteurs IIoT', 'en' => 'Predictive maintenance and IIoT sensors', 'category' => 'technique'],
                ['fr' => 'Lecture de schémas électriques et pneumatiques', 'en' => 'Reading electrical and pneumatic diagrams', 'category' => 'technique'],
                ['fr' => 'Contrôle non destructif (CND)', 'en' => 'Non-destructive testing (NDT)', 'category' => 'technique'],
                ['fr' => 'Robotique industrielle et maintenance', 'en' => 'Industrial robotics maintenance', 'category' => 'technique'],
            ]),
            $this->categorySkills('manufacturing', [
                ['fr' => 'Électricité et électronique industrielle', 'en' => 'Industrial electricity and electronics', 'category' => 'technique'],
                ['fr' => 'Robotique industrielle et automatisme', 'en' => 'Industrial robotics and automation', 'category' => 'technique'],
                ['fr' => 'Gestion de la production (lean, Six Sigma)', 'en' => 'Production management (lean, Six Sigma)', 'category' => 'technique'],
                ['fr' => 'Conduite de ligne de production', 'en' => 'Production line operation', 'category' => 'technique'],
                ['fr' => 'Contrôle qualité et métrologie', 'en' => 'Quality control and metrology', 'category' => 'technique'],
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
                ['fr' => 'Entretien et nettoyage des chambres', 'en' => 'Room cleaning and housekeeping', 'category' => 'technique'],
                ['fr' => 'Conciergerie et service personnalisé', 'en' => 'Concierge and personalized service', 'category' => 'technique'],
                ['fr' => 'Gestion d’équipe d’étage', 'en' => 'Floor team management', 'category' => 'technique'],
                ['fr' => 'Surveillance de nuit et sécurité', 'en' => 'Night surveillance and security', 'category' => 'technique'],
                ['fr' => 'Sens de l’accueil et discrétion', 'en' => 'Hospitality and discretion', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('waiting-bar-service', [
                ['fr' => 'Techniques de sommellerie et service en salle', 'en' => 'Sommelier techniques and table service', 'category' => 'technique'],
                ['fr' => 'Mixologie et préparation de cocktails', 'en' => 'Mixology and cocktail preparation', 'category' => 'technique'],
                ['fr' => 'Hygiène alimentaire et HACCP', 'en' => 'Food hygiene and HACCP', 'category' => 'technique'],
                ['fr' => 'Gestion des stocks et commandes boissons', 'en' => 'Beverage stock and ordering management', 'category' => 'technique'],
                ['fr' => 'Encaissement et gestion de caisse', 'en' => 'Cashiering and till management', 'category' => 'technique'],
                ['fr' => 'Accueil et relation client au bar', 'en' => 'Bar reception and customer relations', 'category' => 'technique'],
                ['fr' => 'Rapidité d’exécution et gestion du rush', 'en' => 'Speed of execution and rush management', 'category' => 'soft-skill'],
                ['fr' => 'Connaissance des vins et alcools', 'en' => 'Knowledge of wines and spirits', 'category' => 'technique'],
            ]),
            $this->categorySkills('catering-culinary', [
                ['fr' => 'Cuisine (préparation, cuisson, dressage)', 'en' => 'Cooking (preparation, cooking, plating)', 'category' => 'technique'],
                ['fr' => 'Gestion des stocks et approvisionnement en restauration', 'en' => 'Stock and supply management in catering', 'category' => 'technique'],
                ['fr' => 'Pâtisserie et techniques sucrées', 'en' => 'Pastry and sweet techniques', 'category' => 'technique'],
                ['fr' => 'Boulangerie et panification', 'en' => 'Bread making and baking', 'category' => 'technique'],
                ['fr' => 'Élaboration de menus et création culinaire', 'en' => 'Menu creation and culinary innovation', 'category' => 'technique'],
                ['fr' => 'Gestion de brigade et organisation de cuisine', 'en' => 'Kitchen brigade management', 'category' => 'technique'],
                ['fr' => 'Hygiène alimentaire et HACCP', 'en' => 'Food hygiene and HACCP', 'category' => 'technique'],
                ['fr' => 'Cuisine de collectivité et grande production', 'en' => 'Collective catering and large-scale cooking', 'category' => 'technique'],
                ['fr' => 'Rigueur et rapidité en cuisine', 'en' => 'Rigor and speed in the kitchen', 'category' => 'soft-skill'],
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
                ['fr' => 'Coordination technique (son, lumière, scène)', 'en' => 'Technical coordination (sound, lighting, stage)', 'category' => 'technique'],
                ['fr' => 'Logistique et gestion des flux', 'en' => 'Logistics and crowd/flow management', 'category' => 'technique'],
                ['fr' => 'Sécurité événementielle et conformité', 'en' => 'Event security and compliance', 'category' => 'technique'],
                ['fr' => 'Scénographie et mise en espace', 'en' => 'Scenography and spatial design', 'category' => 'technique'],
                ['fr' => 'Booking d’artistes et relations agences', 'en' => 'Artist booking and agency relations', 'category' => 'technique'],
                ['fr' => 'Gestion des prestataires et budgets', 'en' => 'Vendor management and budgeting', 'category' => 'technique'],
                ['fr' => 'Réactivité et gestion des imprévus', 'en' => 'Reactivity and handling unforeseen events', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('cleaning-maintenance', [
                ['fr' => 'Nettoyage et entretien des locaux (méthodes professionnelles)', 'en' => 'Professional cleaning and maintenance', 'category' => 'technique'],
                ['fr' => 'Entretien technique des bâtiments', 'en' => 'Building technical maintenance', 'category' => 'technique'],
                ['fr' => 'Nettoyage industriel et normes d’hygiène', 'en' => 'Industrial cleaning and hygiene standards', 'category' => 'technique'],
                ['fr' => 'Utilisation d’équipements de nettoyage professionnels', 'en' => 'Professional cleaning equipment usage', 'category' => 'technique'],
                ['fr' => 'Encadrement d’équipe d’entretien', 'en' => 'Cleaning team supervision', 'category' => 'technique'],
                ['fr' => 'Autonomie et sens du service', 'en' => 'Autonomy and service orientation', 'category' => 'soft-skill'],
                ['fr' => 'Gestion du stress en période de rush', 'en' => 'Stress management during peak periods', 'category' => 'soft-skill'],
            ]),
            // Commerce, informel, mode, réparation
            $this->categorySkills('retail-commerce', [
                ['fr' => 'Vente au détail et service client en magasin', 'en' => 'Retail sales and in-store customer service', 'category' => 'technique'],
                ['fr' => 'Merchandising et gestion de vitrine', 'en' => 'Merchandising and window display', 'category' => 'technique'],
                ['fr' => 'Gestion de caisse et encaissement', 'en' => 'Cash handling and point-of-sale', 'category' => 'technique'],
                ['fr' => 'Gestion des stocks et réassort', 'en' => 'Stock management and replenishment', 'category' => 'technique'],
                ['fr' => 'E-commerce et gestion de boutique en ligne', 'en' => 'E-commerce and online store management', 'category' => 'technique'],
                ['fr' => 'Relation client et fidélisation', 'en' => 'Customer relations and retention', 'category' => 'soft-skill'],
                ['fr' => 'Techniques de vente et négociation', 'en' => 'Sales techniques and negotiation', 'category' => 'technique'],
                ['fr' => 'Gestion des retours et SAV', 'en' => 'Returns and after-sales service', 'category' => 'technique'],
                ['fr' => 'Gestion d’équipe en magasin', 'en' => 'In-store team management', 'category' => 'technique'],
            ]),
            $this->categorySkills('informal-economy-street-trading', [
                ['fr' => 'Vente ambulante et gestion de stand', 'en' => 'Street vending and stall management', 'category' => 'technique'],
                ['fr' => 'Préparation et hygiène alimentaire (street food)', 'en' => 'Street food preparation and hygiene', 'category' => 'technique'],
                ['fr' => 'Collecte et tri de recyclables', 'en' => 'Recyclables collection and sorting', 'category' => 'technique'],
                ['fr' => 'Réparation rapide et dépannage de proximité', 'en' => 'Quick repair and local troubleshooting', 'category' => 'technique'],
                ['fr' => 'Gestion de petite trésorerie', 'en' => 'Small cash management', 'category' => 'technique'],
                ['fr' => 'Adaptabilité et débrouillardise', 'en' => 'Adaptability and resourcefulness', 'category' => 'soft-skill'],
                ['fr' => 'Négociation commerciale de rue', 'en' => 'Street negotiation skills', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('textile-fashion', [
                ['fr' => 'Couture et confection', 'en' => 'Sewing and garment making', 'category' => 'technique'],
                ['fr' => 'Patronage et modélisme', 'en' => 'Pattern making and modelling', 'category' => 'technique'],
                ['fr' => 'Retouches et ajustements', 'en' => 'Alterations and fittings', 'category' => 'technique'],
                ['fr' => 'Connaissance des tissus et matériaux', 'en' => 'Fabric and material knowledge', 'category' => 'technique'],
                ['fr' => 'Design de mode et stylisme', 'en' => 'Fashion design and styling', 'category' => 'technique'],
                ['fr' => 'Gestion d\'atelier et production textile', 'en' => 'Workshop management and textile production', 'category' => 'technique'],
                ['fr' => 'Broderie et finitions textiles', 'en' => 'Embroidery and textile finishing', 'category' => 'technique'],
            ]),
            $this->categorySkills('repair-maintenance-services', [
                ['fr' => 'Dépannage électroménager', 'en' => 'Appliance troubleshooting and repair', 'category' => 'technique'],
                ['fr' => 'Réparation de téléphones et petits appareils', 'en' => 'Mobile phone and small device repair', 'category' => 'technique'],
                ['fr' => 'Réparation et entretien de vélos', 'en' => 'Bicycle repair and maintenance', 'category' => 'technique'],
                ['fr' => 'Diagnostic matériel et remplacement de pièces', 'en' => 'Equipment diagnostics and parts replacement', 'category' => 'technique'],
                ['fr' => 'Service après-vente et relation client', 'en' => 'After-sales service and customer relations', 'category' => 'soft-skill'],
                ['fr' => 'Sécurité électrique de base', 'en' => 'Basic electrical safety', 'category' => 'technique'],
            ]),

            // Ressources humaines & ingénierie
            $this->categorySkills('human-resources', [
                ['fr' => 'Recrutement et sourcing de candidats', 'en' => 'Recruitment and candidate sourcing', 'category' => 'technique'],
                ['fr' => 'Conduite d’entretiens et évaluation', 'en' => 'Interview management and assessment', 'category' => 'technique'],
                ['fr' => 'Droit du travail appliqué aux RH', 'en' => 'Labor law applied to HR', 'category' => 'technique'],
                ['fr' => 'Gestion de la paie', 'en' => 'Payroll management', 'category' => 'technique'],
                ['fr' => 'Écoute active et empathie', 'en' => 'Active listening and empathy', 'category' => 'soft-skill'],
                ['fr' => 'Discrétion et confidentialité', 'en' => 'Discretion and confidentiality', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('construction-civil-engineering', [
                ['fr' => 'Génie civil et calcul de structures', 'en' => 'Civil engineering and structural calculations', 'category' => 'technique'],
                ['fr' => 'Gestion de projets de construction', 'en' => 'Construction project management', 'category' => 'technique'],
                ['fr' => 'Normes de construction et réglementation', 'en' => 'Construction standards and regulations', 'category' => 'technique'],
                ['fr' => 'Calcul de structures (béton armé, charpente métallique)', 'en' => 'Structural calculations (reinforced concrete, steel framing)', 'category' => 'technique'],
                ['fr' => 'Métrage et chiffrage de travaux', 'en' => 'Quantity surveying and cost estimating', 'category' => 'technique'],
                ['fr' => 'Étude géotechnique des sols', 'en' => 'Soil geotechnical study', 'category' => 'technique'],
                ['fr' => 'Dessin technique et modélisation BIM', 'en' => 'Technical drawing and BIM modelling', 'category' => 'technique'],
                ['fr' => 'Rigueur technique', 'en' => 'Technical rigor', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('engineering', [
                ['fr' => 'AutoCAD', 'en' => 'AutoCAD', 'category' => 'technique'],
                ['fr' => 'SolidWorks', 'en' => 'SolidWorks', 'category' => 'technique'],
                ['fr' => 'Inventor', 'en' => 'Inventor', 'category' => 'technique'],
                ['fr' => 'Catia', 'en' => 'Catia', 'category' => 'technique'],
                ['fr' => 'Gestion de projets d’ingénierie', 'en' => 'Engineering project management', 'category' => 'technique'],
                ['fr' => 'Résolution de problèmes techniques complexes', 'en' => 'Complex technical problem solving', 'category' => 'technique'],
                ['fr' => 'Conception mécanique et simulation', 'en' => 'Mechanical design and simulation', 'category' => 'technique'],
                ['fr' => 'Automatisme et électrotechnique', 'en' => 'Automation and electrical engineering', 'category' => 'technique'],
                ['fr' => 'Amélioration continue et Lean management', 'en' => 'Continuous improvement and Lean management', 'category' => 'technique'],
                ['fr' => 'Pilotage de projets techniques multi-métiers', 'en' => 'Cross-disciplinary technical project management', 'category' => 'technique'],
                ['fr' => 'Esprit d’analyse', 'en' => 'Analytical mindset', 'category' => 'soft-skill'],
            ]),

            // Métiers manuels & artisanat
            $this->categorySkills('carpentry-woodwork', [
                ['fr' => 'Menuiserie et travail du bois', 'en' => 'Woodworking and carpentry', 'category' => 'technique'],
                ['fr' => 'Ébénisterie et fabrication de meubles', 'en' => 'Cabinetmaking and furniture making', 'category' => 'technique'],
                ['fr' => 'Lecture de plans techniques', 'en' => 'Technical drawing reading', 'category' => 'technique'],
                ['fr' => 'Charpente traditionnelle et ossature bois', 'en' => 'Traditional timber framing', 'category' => 'technique'],
                ['fr' => 'Tournage et sculpture sur bois', 'en' => 'Wood turning and carving', 'category' => 'technique'],
                ['fr' => 'Marqueterie et finitions décoratives', 'en' => 'Marquetry and decorative finishes', 'category' => 'technique'],
                ['fr' => 'Précision et minutie', 'en' => 'Precision and attention to detail', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('skilled-labor', [
                ['fr' => 'Polyvalence technique', 'en' => 'Technical versatility', 'category' => 'technique'],
                ['fr' => 'Maintenance générale', 'en' => 'General maintenance', 'category' => 'technique'],
                ['fr' => 'Utilisation d’outillage manuel et électroportatif', 'en' => 'Hand and power tool usage', 'category' => 'technique'],
                ['fr' => 'Adaptabilité et rigueur', 'en' => 'Adaptability and rigor', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('craftsmanship-traditional-trades', [
                ['fr' => 'Travail artisanal du bois, métal, terre', 'en' => 'Artisanal work of wood, metal, clay', 'category' => 'technique'],
                ['fr' => 'Techniques patrimoniales et savoir-faire traditionnel', 'en' => 'Heritage techniques and traditional know-how', 'category' => 'technique'],
                ['fr' => 'Bijouterie et travail des métaux précieux', 'en' => 'Jewelry making and precious metal work', 'category' => 'technique'],
                ['fr' => 'Céramique et poterie', 'en' => 'Ceramics and pottery', 'category' => 'technique'],
                ['fr' => 'Travail du verre et soufflage', 'en' => 'Glassblowing and glass work', 'category' => 'technique'],
                ['fr' => 'Tapisserie et rembourrage', 'en' => 'Upholstery', 'category' => 'technique'],
                ['fr' => 'Maroquinerie et travail du cuir', 'en' => 'Leatherworking', 'category' => 'technique'],
                ['fr' => 'Minutie et patience', 'en' => 'Meticulousness and patience', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('hair-beauty', [
                ['fr' => 'Techniques de coiffure', 'en' => 'Hairdressing techniques', 'category' => 'technique'],
                ['fr' => 'Soins esthétiques du visage et du corps', 'en' => 'Facial and body aesthetic care', 'category' => 'technique'],
                ['fr' => 'Maquillage professionnel', 'en' => 'Professional makeup', 'category' => 'technique'],
                ['fr' => 'Sens artistique et créativité', 'en' => 'Artistic sense and creativity', 'category' => 'soft-skill'],
                ['fr' => 'Relation client et écoute', 'en' => 'Customer relations and listening', 'category' => 'soft-skill'],
            ]),

            // Logistique & transport
            $this->categorySkills('warehouse-logistics', [
                ['fr' => 'Gestion d’entrepôt (WMS)', 'en' => 'Warehouse management (WMS)', 'category' => 'technique'],
                ['fr' => 'Préparation de commandes', 'en' => 'Order picking', 'category' => 'technique'],
                ['fr' => 'Conduite de chariot élévateur (CACES)', 'en' => 'Forklift operation (CACES)', 'category' => 'technique'],
                ['fr' => 'Gestion des stocks et inventaire', 'en' => 'Stock and inventory management', 'category' => 'technique'],
                ['fr' => 'Réception et expédition de marchandises', 'en' => 'Goods receiving and shipping', 'category' => 'technique'],
                ['fr' => 'Utilisation de systèmes WMS', 'en' => 'WMS systems usage', 'category' => 'technique'],
                ['fr' => 'Coordination logistique multi-sites', 'en' => 'Multi-site logistics coordination', 'category' => 'technique'],
                ['fr' => 'Rigueur et sens de l’organisation', 'en' => 'Rigor and organization', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('transport-delivery', [
                ['fr' => 'Conduite professionnelle et sécurité routière', 'en' => 'Professional driving and road safety', 'category' => 'technique'],
                ['fr' => 'Planification d’itinéraires', 'en' => 'Route planning', 'category' => 'technique'],
                ['fr' => 'Gestion documentaire de transport (CMR)', 'en' => 'Transport documentation management (CMR)', 'category' => 'technique'],
                ['fr' => 'Conduite de poids lourd et semi-remorque', 'en' => 'Heavy truck and semi-trailer driving', 'category' => 'technique'],
                ['fr' => 'Transport sous température dirigée', 'en' => 'Temperature-controlled transport', 'category' => 'technique'],
                ['fr' => 'Gestion de tournées de livraison', 'en' => 'Delivery route management', 'category' => 'technique'],
                ['fr' => 'Ponctualité et fiabilité', 'en' => 'Punctuality and reliability', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('heavy-vehicle-taxi-driving', [
                ['fr' => 'Permis poids lourd / super lourd', 'en' => 'Heavy / super-heavy vehicle license', 'category' => 'technique'],
                ['fr' => 'Conduite défensive', 'en' => 'Defensive driving', 'category' => 'technique'],
                ['fr' => 'Conduite de transport en commun (bus, car)', 'en' => 'Public transport driving (bus, coach)', 'category' => 'technique'],
                ['fr' => 'Gestion des titres de transport et billetterie', 'en' => 'Fare and ticketing management', 'category' => 'technique'],
                ['fr' => 'Relation client au volant', 'en' => 'Customer relations while driving', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('couriers-motorcycle-bike', [
                ['fr' => 'Conduite deux-roues en milieu urbain', 'en' => 'Urban two-wheeler riding', 'category' => 'technique'],
                ['fr' => 'Orientation et gestion GPS', 'en' => 'Navigation and GPS management', 'category' => 'technique'],
                ['fr' => 'Gestion des livraisons express', 'en' => 'Express delivery management', 'category' => 'technique'],
                ['fr' => 'Résistance physique et rapidité', 'en' => 'Physical stamina and speed', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('maritime-port-transport', [
                ['fr' => 'Navigation maritime', 'en' => 'Maritime navigation', 'category' => 'technique'],
                ['fr' => 'Manutention portuaire', 'en' => 'Port cargo handling', 'category' => 'technique'],
                ['fr' => 'Réglementation maritime internationale (SOLAS)', 'en' => 'International maritime regulations (SOLAS)', 'category' => 'technique'],
                ['fr' => 'Commandement et navigation de navire', 'en' => 'Ship command and navigation', 'category' => 'technique'],
                ['fr' => 'Manutention de conteneurs et grutage portuaire', 'en' => 'Container handling and port crane operation', 'category' => 'technique'],
                ['fr' => 'Maintenance des infrastructures portuaires', 'en' => 'Port infrastructure maintenance', 'category' => 'technique'],
                ['fr' => 'Pilotage et accostage de navires', 'en' => 'Ship piloting and berthing', 'category' => 'technique'],
                ['fr' => 'Vigilance et sang-froid', 'en' => 'Vigilance and composure', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('aviation-airport-services', [
                ['fr' => 'Procédures de sûreté aéroportuaire', 'en' => 'Airport security procedures', 'category' => 'technique'],
                ['fr' => 'Gestion au sol des aéronefs', 'en' => 'Aircraft ground handling', 'category' => 'technique'],
                ['fr' => 'Anglais aéronautique', 'en' => 'Aviation English', 'category' => 'technique'],
                ['fr' => 'Pilotage d’aéronef', 'en' => 'Aircraft piloting', 'category' => 'technique'],
                ['fr' => 'Contrôle de la circulation aérienne', 'en' => 'Air traffic control', 'category' => 'technique'],
                ['fr' => 'Service et sécurité à bord', 'en' => 'In-flight service and safety', 'category' => 'technique'],
                ['fr' => 'Maintenance et inspection d’aéronefs', 'en' => 'Aircraft maintenance and inspection', 'category' => 'technique'],
                ['fr' => 'Rigueur et respect des procédures', 'en' => 'Rigor and procedure compliance', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('transport-mobility', [
                ['fr' => 'Planification de flotte', 'en' => 'Fleet planning', 'category' => 'technique'],
                ['fr' => 'Optimisation des déplacements', 'en' => 'Mobility optimization', 'category' => 'technique'],
                ['fr' => 'Sens de l’organisation', 'en' => 'Organizational skills', 'category' => 'soft-skill'],
            ]),

            // Santé, social & services à la personne
            $this->categorySkills('healthcare-medical', [
                ['fr' => 'Diagnostic médical', 'en' => 'Medical diagnosis', 'category' => 'technique'],
                ['fr' => 'Prescription et suivi thérapeutique', 'en' => 'Prescription and treatment follow-up', 'category' => 'technique'],
                ['fr' => 'Actes chirurgicaux', 'en' => 'Surgical procedures', 'category' => 'technique'],
                ['fr' => 'Suivi de grossesse et accouchement', 'en' => 'Pregnancy monitoring and childbirth', 'category' => 'technique'],
                ['fr' => 'Dispensation et conseil pharmaceutique', 'en' => 'Pharmaceutical dispensing and advice', 'category' => 'technique'],
                ['fr' => 'Soins dentaires et chirurgie bucco-dentaire', 'en' => 'Dental care and oral surgery', 'category' => 'technique'],
                ['fr' => 'Empathie et écoute du patient', 'en' => 'Patient empathy and listening', 'category' => 'soft-skill'],
                ['fr' => 'Gestion du stress en urgence', 'en' => 'Stress management in emergencies', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('personal-care-home-services', [
                ['fr' => 'Garde d’enfants', 'en' => 'Childcare', 'category' => 'technique'],
                ['fr' => 'Entretien du domicile', 'en' => 'Home upkeep', 'category' => 'technique'],
                ['fr' => 'Assistance familiale et protection de l’enfance', 'en' => 'Foster care and child protection', 'category' => 'technique'],
                ['fr' => 'Gestion du foyer et tâches ménagères', 'en' => 'Household management and chores', 'category' => 'technique'],
                ['fr' => 'Patience et bienveillance', 'en' => 'Patience and kindness', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('coaching-personal-development', [
                ['fr' => 'Techniques de coaching individuel', 'en' => 'Individual coaching techniques', 'category' => 'technique'],
                ['fr' => 'Animation d’ateliers de développement personnel', 'en' => 'Personal development workshop facilitation', 'category' => 'technique'],
                ['fr' => 'Formation en soft skills', 'en' => 'Soft skills training', 'category' => 'technique'],
                ['fr' => 'Conseil en management et organisation', 'en' => 'Management and organizational consulting', 'category' => 'technique'],
                ['fr' => 'Écoute active et bienveillance', 'en' => 'Active listening and kindness', 'category' => 'soft-skill'],
            ]),

            // Nettoyage, restauration rapide & relation client
            $this->categorySkills('cleaning-hygiene', [
                ['fr' => 'Techniques de nettoyage industriel', 'en' => 'Industrial cleaning techniques', 'category' => 'technique'],
                ['fr' => 'Utilisation de produits et normes d’hygiène', 'en' => 'Use of cleaning products and hygiene standards', 'category' => 'technique'],
                ['fr' => 'Nettoyage des espaces publics', 'en' => 'Public space cleaning', 'category' => 'technique'],
                ['fr' => 'Rigueur et sens du détail', 'en' => 'Rigor and attention to detail', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('food-beverage', [
                ['fr' => 'Préparation rapide en restauration rapide', 'en' => 'Fast food preparation', 'category' => 'technique'],
                ['fr' => 'Respect des normes HACCP', 'en' => 'HACCP compliance', 'category' => 'technique'],
                ['fr' => 'Service au comptoir et prise de commande', 'en' => 'Counter service and order taking', 'category' => 'technique'],
                ['fr' => 'Rapidité d’exécution', 'en' => 'Speed of execution', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('customer-service-call-center', [
                ['fr' => 'Techniques d’accueil téléphonique', 'en' => 'Telephone reception techniques', 'category' => 'technique'],
                ['fr' => 'Gestion des réclamations', 'en' => 'Complaint handling', 'category' => 'technique'],
                ['fr' => 'Utilisation de logiciels CRM', 'en' => 'CRM software usage', 'category' => 'technique'],
                ['fr' => 'Patience et courtoisie', 'en' => 'Patience and courtesy', 'category' => 'soft-skill'],
                ['fr' => 'Gestion du stress', 'en' => 'Stress management', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('public-services-administration', [
                ['fr' => 'Procédures administratives publiques', 'en' => 'Public administrative procedures', 'category' => 'technique'],
                ['fr' => 'Accueil du public', 'en' => 'Public reception', 'category' => 'technique'],
                ['fr' => 'Rédaction administrative et actes officiels', 'en' => 'Administrative writing and official records', 'category' => 'technique'],
                ['fr' => 'Neutralité et sens du service public', 'en' => 'Neutrality and public service ethics', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('real-estate-property-management', [
                ['fr' => 'Estimation immobilière', 'en' => 'Real estate appraisal', 'category' => 'technique'],
                ['fr' => 'Gestion locative et baux', 'en' => 'Rental and lease management', 'category' => 'technique'],
                ['fr' => 'Négociation immobilière', 'en' => 'Real estate negotiation', 'category' => 'technique'],
                ['fr' => 'Sens du relationnel et de la confiance', 'en' => 'Interpersonal skills and trustworthiness', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('entertainment-recreation', [
                ['fr' => 'Animation de groupe', 'en' => 'Group facilitation', 'category' => 'technique'],
                ['fr' => 'Organisation d’activités ludiques', 'en' => 'Recreational activity organization', 'category' => 'technique'],
                ['fr' => 'Encadrement sportif et activités physiques', 'en' => 'Sports coaching and physical activities', 'category' => 'technique'],
                ['fr' => 'Dynamisme et créativité', 'en' => 'Dynamism and creativity', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('education-training', [
                ['fr' => 'Conception de programmes pédagogiques', 'en' => 'Educational program design', 'category' => 'technique'],
                ['fr' => 'Animation de formation', 'en' => 'Training facilitation', 'category' => 'technique'],
                ['fr' => 'Évaluation des acquis', 'en' => 'Learning assessment', 'category' => 'technique'],
                ['fr' => 'Enseignement disciplinaire et transmission du savoir', 'en' => 'Subject teaching and knowledge transfer', 'category' => 'technique'],
                ['fr' => 'Tutorat individualisé', 'en' => 'Individualized tutoring', 'category' => 'technique'],
                ['fr' => 'Pédagogie et patience', 'en' => 'Pedagogy and patience', 'category' => 'soft-skill'],
            ]),

            // Numérique, télécoms & tech émergentes
            $this->categorySkills('telecommunications', [
                ['fr' => 'Installation et maintenance réseau', 'en' => 'Network installation and maintenance', 'category' => 'technique'],
                ['fr' => 'Fibre optique et raccordement', 'en' => 'Fiber optic connection', 'category' => 'technique'],
                ['fr' => 'Diagnostic de pannes télécoms', 'en' => 'Telecom fault diagnosis', 'category' => 'technique'],
                ['fr' => 'Rigueur technique', 'en' => 'Technical rigor', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('cryptocurrency-blockchain', [
                ['fr' => 'Développement de smart contracts', 'en' => 'Smart contract development', 'category' => 'technique'],
                ['fr' => 'Cryptographie et sécurité blockchain', 'en' => 'Cryptography and blockchain security', 'category' => 'technique'],
                ['fr' => 'Analyse de marché crypto', 'en' => 'Crypto market analysis', 'category' => 'technique'],
                ['fr' => 'Veille technologique', 'en' => 'Technology watch', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('vr-ar-technology', [
                ['fr' => 'Unity', 'en' => 'Unity', 'category' => 'technique'],
                ['fr' => 'Unreal Engine', 'en' => 'Unreal Engine', 'category' => 'technique'],
                ['fr' => 'Modélisation et design immersif', 'en' => 'Immersive design and modelling', 'category' => 'technique'],
                ['fr' => 'Créativité technique', 'en' => 'Technical creativity', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('business-intelligence-strategic-watch', [
                ['fr' => 'Analyse de données et reporting', 'en' => 'Data analysis and reporting', 'category' => 'technique'],
                ['fr' => 'Microsoft Power BI', 'en' => 'Microsoft Power BI', 'category' => 'technique'],
                ['fr' => 'Tableau', 'en' => 'Tableau', 'category' => 'technique'],
                ['fr' => 'Looker Studio', 'en' => 'Looker Studio', 'category' => 'technique'],
                ['fr' => 'Veille concurrentielle', 'en' => 'Competitive intelligence', 'category' => 'technique'],
                ['fr' => 'Esprit de synthèse', 'en' => 'Synthesis skills', 'category' => 'soft-skill'],
            ]),

            // Agriculture, environnement & énergie
            $this->categorySkills('food-processing-agribusiness', [
                ['fr' => 'Transformation agroalimentaire', 'en' => 'Food processing', 'category' => 'technique'],
                ['fr' => 'Contrôle qualité alimentaire', 'en' => 'Food quality control', 'category' => 'technique'],
                ['fr' => 'Hygiène et sécurité alimentaire (HACCP)', 'en' => 'Food hygiene and safety (HACCP)', 'category' => 'technique'],
                ['fr' => 'Rigueur sanitaire', 'en' => 'Sanitary rigor', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('fishing-aquaculture', [
                ['fr' => 'Techniques de pêche', 'en' => 'Fishing techniques', 'category' => 'technique'],
                ['fr' => 'Élevage aquacole', 'en' => 'Aquaculture farming', 'category' => 'technique'],
                ['fr' => 'Entretien des équipements marins', 'en' => 'Marine equipment maintenance', 'category' => 'technique'],
                ['fr' => 'Endurance physique', 'en' => 'Physical endurance', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('environment-sustainability', [
                ['fr' => 'Évaluation d’impact environnemental', 'en' => 'Environmental impact assessment', 'category' => 'technique'],
                ['fr' => 'Réglementation environnementale', 'en' => 'Environmental regulations', 'category' => 'technique'],
                ['fr' => 'Gestion de projets durables', 'en' => 'Sustainable project management', 'category' => 'technique'],
                ['fr' => 'Sensibilité écologique', 'en' => 'Ecological awareness', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('renewable-energy', [
                ['fr' => 'Installation de panneaux solaires', 'en' => 'Solar panel installation', 'category' => 'technique'],
                ['fr' => 'Maintenance d’éoliennes', 'en' => 'Wind turbine maintenance', 'category' => 'technique'],
                ['fr' => 'Dimensionnement de systèmes énergétiques', 'en' => 'Energy system sizing', 'category' => 'technique'],
                ['fr' => 'Rigueur et sécurité électrique', 'en' => 'Rigor and electrical safety', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('water-sanitation', [
                ['fr' => 'Traitement des eaux usées', 'en' => 'Wastewater treatment', 'category' => 'technique'],
                ['fr' => 'Maintenance de réseaux d’assainissement', 'en' => 'Sanitation network maintenance', 'category' => 'technique'],
                ['fr' => 'Analyse de la qualité de l’eau', 'en' => 'Water quality analysis', 'category' => 'technique'],
                ['fr' => 'Rigueur sanitaire', 'en' => 'Sanitary rigor', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('waste-management-recycling', [
                ['fr' => 'Collecte et tri des déchets', 'en' => 'Waste collection and sorting', 'category' => 'technique'],
                ['fr' => 'Valorisation des matières recyclables', 'en' => 'Recyclable material recovery', 'category' => 'technique'],
                ['fr' => 'Conduite d’engins de collecte', 'en' => 'Collection vehicle operation', 'category' => 'technique'],
                ['fr' => 'Sens civique et rigueur', 'en' => 'Civic sense and rigor', 'category' => 'soft-skill'],
            ]),

            // Sécurité, défense & urgence
            $this->categorySkills('security-safety', [
                ['fr' => 'Surveillance et contrôle d’accès', 'en' => 'Surveillance and access control', 'category' => 'technique'],
                ['fr' => 'Gestion des situations d’urgence', 'en' => 'Emergency situation management', 'category' => 'technique'],
                ['fr' => 'Réglementation de sécurité incendie', 'en' => 'Fire safety regulations', 'category' => 'technique'],
                ['fr' => 'Contrôle d’accès et gestion des badges', 'en' => 'Access control and badge management', 'category' => 'technique'],
                ['fr' => 'Sang-froid et vigilance', 'en' => 'Composure and vigilance', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('private-security-surveillance', [
                ['fr' => 'Rondes de surveillance', 'en' => 'Security patrols', 'category' => 'technique'],
                ['fr' => 'Utilisation de systèmes de vidéosurveillance', 'en' => 'CCTV systems usage', 'category' => 'technique'],
                ['fr' => 'Réactivité et discrétion', 'en' => 'Responsiveness and discretion', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('firefighters-emergency-services', [
                ['fr' => 'Intervention incendie', 'en' => 'Fire intervention', 'category' => 'technique'],
                ['fr' => 'Premiers secours et réanimation', 'en' => 'First aid and resuscitation', 'category' => 'technique'],
                ['fr' => 'Utilisation de matériel de secours', 'en' => 'Rescue equipment usage', 'category' => 'technique'],
                ['fr' => 'Sang-froid sous pression', 'en' => 'Composure under pressure', 'category' => 'soft-skill'],
                ['fr' => 'Courage et esprit d’équipe', 'en' => 'Courage and team spirit', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('aerospace-defense', [
                ['fr' => 'Maintenance aéronautique', 'en' => 'Aeronautical maintenance', 'category' => 'technique'],
                ['fr' => 'Normes de sécurité aérienne', 'en' => 'Aviation safety standards', 'category' => 'technique'],
                ['fr' => 'Conception de systèmes de défense', 'en' => 'Defense systems design', 'category' => 'technique'],
                ['fr' => 'Rigueur extrême et précision', 'en' => 'Extreme rigor and precision', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('humanitarian-logistics-emergency-relief', [
                ['fr' => 'Logistique de crise', 'en' => 'Crisis logistics', 'category' => 'technique'],
                ['fr' => 'Coordination des secours d’urgence', 'en' => 'Emergency relief coordination', 'category' => 'technique'],
                ['fr' => 'Gestion des approvisionnements humanitaires', 'en' => 'Humanitarian supply management', 'category' => 'technique'],
                ['fr' => 'Résilience et adaptabilité', 'en' => 'Resilience and adaptability', 'category' => 'soft-skill'],
            ]),

            // Automobile & électricité
            $this->categorySkills('automotive', [
                ['fr' => 'Mécanique automobile', 'en' => 'Auto mechanics', 'category' => 'technique'],
                ['fr' => 'Diagnostic électronique embarqué', 'en' => 'Onboard electronic diagnostics', 'category' => 'technique'],
                ['fr' => 'Carrosserie et peinture', 'en' => 'Bodywork and painting', 'category' => 'technique'],
                ['fr' => 'Vente et conseil automobile', 'en' => 'Automotive sales and advisory', 'category' => 'technique'],
                ['fr' => 'Rigueur technique et sécurité', 'en' => 'Technical rigor and safety', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('electrical-energy', [
                ['fr' => 'Installation électrique bâtiment', 'en' => 'Building electrical installation', 'category' => 'technique'],
                ['fr' => 'Normes électriques (NF C 15-100)', 'en' => 'Electrical standards (NF C 15-100)', 'category' => 'technique'],
                ['fr' => 'Diagnostic de pannes électriques', 'en' => 'Electrical fault diagnosis', 'category' => 'technique'],
                ['fr' => 'Rigueur et sécurité électrique', 'en' => 'Rigor and electrical safety', 'category' => 'soft-skill'],
            ]),

            // Finance solidaire, assurance & économie sociale
            $this->categorySkills('insurance-risk-management', [
                ['fr' => 'Évaluation et souscription des risques', 'en' => 'Risk assessment and underwriting', 'category' => 'technique'],
                ['fr' => 'Gestion des sinistres', 'en' => 'Claims management', 'category' => 'technique'],
                ['fr' => 'Réglementation des assurances', 'en' => 'Insurance regulations', 'category' => 'technique'],
                ['fr' => 'Rigueur analytique', 'en' => 'Analytical rigor', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('microfinance-financial-inclusion', [
                ['fr' => 'Analyse de crédit', 'en' => 'Credit analysis', 'category' => 'technique'],
                ['fr' => 'Accompagnement à l’épargne', 'en' => 'Savings support', 'category' => 'technique'],
                ['fr' => 'Éducation financière', 'en' => 'Financial literacy education', 'category' => 'technique'],
                ['fr' => 'Pédagogie et patience', 'en' => 'Pedagogy and patience', 'category' => 'soft-skill'],
            ]),
            $this->categorySkills('social-solidarity-economy', [
                ['fr' => 'Gestion associative', 'en' => 'Nonprofit management', 'category' => 'technique'],
                ['fr' => 'Montage de projets solidaires', 'en' => 'Solidarity project development', 'category' => 'technique'],
                ['fr' => 'Esprit d’engagement et coopération', 'en' => 'Commitment and cooperation', 'category' => 'soft-skill'],
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
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        // Anciennes compétences trop génériques (outils regroupés) — masquées du catalogue.
        $obsoleteSlugs = [
            'office-software-proficiency-word-excel-outlook',
            'programming-languages-python-java-c',
            'web-development-html-css-js-react',
            'databases-sql-mongodb-postgresql',
            'computer-aided-design-cadcam',
            'mobile-development-swift-kotlin-flutter',
            'networking-and-security-tcpip-firewall-vpn',
            'cloud-computing-aws-azure-gcp',
            'cybersecurity-penetration-testing-gdpr-iso-27001',
            'devops-and-cicd-docker-kubernetes-jenkins',
            'infrastructure-automation-iac-terraform',
            'deep-learning-tensorflow-pytorch',
            'digital-marketing-seo-sea-social-media',
            'content-creation-writing-video-podcasts',
            'customer-relationship-management-crm-salesforce',
            'marketing-automation-hubspot-mailchimp',
            'e-commerce-online-store-management-uxui',
            'crm-usage-salesforce-hubspot',
            'graphic-design-adobe-photoshop-illustrator',
            'uiux-design-and-prototyping-figma',
            'photography-and-post-production-lightroom-capture-one',
            'video-editing-and-vfx-premiere-pro-after-effects',
            '2d3d-animation-blender-maya',
            'music-production-and-sound-design-ableton-pro-tools',
            'technical-design-and-modelling-cad',
            'real-time-3d-development-unity-unreal',
            'unityunreal-engine-integration',
            'bi-tools-power-bi-tableau',
        ];

        DB::table('skills')
            ->whereIn('slug', $obsoleteSlugs)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => $now,
                'updated_at' => $now,
            ]);
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
