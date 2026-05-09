<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\OfferCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Secteurs Formels
            [
                'fr' => 'Informatique & Technologie',
                'en' => 'IT & Technology',
                'icon' => 'computer-chip'
            ],
            [
                'fr' => 'Banque & Finance',
                'en' => 'Banking & Finance',
                'icon' => 'landmark'
            ],
            [
                'fr' => 'Santé & Médecine',
                'en' => 'Healthcare & Medical',
                'icon' => 'stethoscope'
            ],
            [
                'fr' => 'BTP & Construction',
                'en' => 'Construction & Civil Engineering',
                'icon' => 'building'
            ],
            [
                'fr' => 'Ingénierie',
                'en' => 'Engineering',
                'icon' => 'cogs'
            ],
            [
                'fr' => 'Automobiles',
                'en' => 'Automotive',
                'icon' => 'car'
            ],
            [
                'fr' => 'Électricité & Énergies',
                'en' => 'Electrical & Energy',
                'icon' => 'bolt'
            ],
            [
                'fr' => 'Industrie Manufacturière',
                'en' => 'Manufacturing',
                'icon' => 'industry'
            ],
            [
                'fr' => 'Juridique & Droit',
                'en' => 'Legal Services',
                'icon' => 'gavel'
            ],
            [
                'fr' => 'Ressources Humaines',
                'en' => 'Human Resources',
                'icon' => 'user-tie'
            ],
            
            // Secteurs Informels & Services
            [
                'fr' => 'Menuiserie & Ébénisterie',
                'en' => 'Carpentry & Woodwork',
                'icon' => 'saw'
            ],
            [
                'fr' => 'Ouvriers & Métiers',
                'en' => 'Skilled Labor',
                'icon' => 'tool-box'
            ],
            [
                'fr' => 'Entrepôt & Logistique',
                'en' => 'Warehouse & Logistics',
                'icon' => 'warehouse'
            ],
            [
                'fr' => 'Vente & Distribution',
                'en' => 'Sales & Distribution',
                'icon' => 'shopping-cart'
            ],
            [
                'fr' => 'Marketing & Communication',
                'en' => 'Marketing & Advertising',
                'icon' => 'megaphone'
            ],
            [
                'fr' => 'Médias & Communication',
                'en' => 'Media & Broadcasting',
                'icon' => 'newspaper'
            ],
            
            // Secteurs Spécialisés
            [
                'fr' => 'Agriculture & Agroalimentaire',
                'en' => 'Agriculture & Food',
                'icon' => 'seedling'
            ],
            [
                'fr' => 'Tourisme & Hôtellerie',
                'en' => 'Tourism & Hospitality',
                'icon' => 'hotel'
            ],
            [
                'fr' => 'Éducation & Formation',
                'en' => 'Education & Training',
                'icon' => 'graduation-cap'
            ],
            [
                'fr' => 'Santé Mentale & Bien-être',
                'en' => 'Mental Health & Wellness',
                'icon' => 'heart'
            ],
            [
                'fr' => 'Design & Créativité',
                'en' => 'Design & Creative',
                'icon' => 'palette'
            ],
            [
                'fr' => 'Environnement & Développement Durable',
                'en' => 'Environment & Sustainability',
                'icon' => 'leaf'
            ],
            
            // Secteurs Émergents
            [
                'fr' => 'Cryptomonnaies & Blockchain',
                'en' => 'Cryptocurrency & Blockchain',
                'icon' => 'bitcoin'
            ],
            [
                'fr' => 'Intelligence Artificielle',
                'en' => 'Artificial Intelligence',
                'icon' => 'robot'
            ],
            [
                'fr' => 'Réalité Virtuelle/Augmentée',
                'en' => 'VR/AR Technology',
                'icon' => 'vr-cardboard'
            ],
        ];

        foreach ($categories as $category) {
            $slug = Str::slug($category['en']);

            OfferCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => [
                        'fr' => $category['fr'],
                        'en' => $category['en'],
                    ],
                    'icon' => $category['icon'],
                    'is_active' => true,
                ]
            );
        }
    }
}