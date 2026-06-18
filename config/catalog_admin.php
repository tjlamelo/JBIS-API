<?php

use App\Core\Domain\Catalog\Models\Agency;
use App\Core\Domain\Catalog\Models\Benefit;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\ContractType;
use App\Core\Domain\Catalog\Models\EducationLevel;
use App\Core\Domain\Catalog\Models\OfferType;
use App\Core\Domain\Catalog\Models\Skill;
use App\Core\Domain\Catalog\Models\SkillCategory;
use App\Core\Domain\Catalog\Models\Trade;
use App\Core\Domain\Catalog\Models\WorkSchedule;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Location\Models\GeographicZone;
use App\Core\Domain\Location\Models\Language;
use App\Core\Domain\Location\Models\LanguageLevel;
use App\Core\Domain\Location\Models\Region;

return [
    'resources' => [
        'categories' => [
            'label' => 'Catégories (secteurs)',
            'model' => Category::class,
            'translatable' => ['name', 'description'],
            'fillable' => ['name', 'slug', 'description', 'is_active'],
            'casts' => ['is_active' => 'boolean'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'name.en' => ['nullable', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
                'description' => ['nullable', 'array'],
                'is_active' => ['nullable', 'boolean'],
            ],
        ],
        'countries' => [
            'label' => 'Pays',
            'model' => Country::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'code', 'phone_code', 'is_active'],
            'casts' => ['is_active' => 'boolean'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'name.en' => ['nullable', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:3'],
                'phone_code' => ['nullable', 'string', 'max:16'],
                'is_active' => ['nullable', 'boolean'],
            ],
        ],
        'geographic_zones' => [
            'label' => 'Zones géographiques',
            'model' => GeographicZone::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug', 'sort_order', 'is_active'],
            'casts' => ['is_active' => 'boolean', 'sort_order' => 'integer'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['nullable', 'boolean'],
            ],
        ],
        'regions' => [
            'label' => 'Régions',
            'model' => Region::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug', 'country_id'],
            'with' => ['country:id,code,name'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
                'country_id' => ['required', 'integer', 'exists:countries,id'],
            ],
        ],
        'cities' => [
            'label' => 'Villes',
            'model' => City::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug', 'region_id', 'zip_code'],
            'with' => ['region:id,name,country_id'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
                'region_id' => ['required', 'integer', 'exists:regions,id'],
                'zip_code' => ['nullable', 'string', 'max:20'],
            ],
        ],
        'contract_types' => [
            'label' => 'Types de contrat',
            'model' => ContractType::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
            ],
        ],
        'benefits' => [
            'label' => 'Avantages',
            'model' => Benefit::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
            ],
        ],
        'education_levels' => [
            'label' => 'Niveaux de formation',
            'model' => EducationLevel::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
            ],
        ],
        'agencies' => [
            'label' => 'Agences',
            'model' => Agency::class,
            'translatable' => ['name', 'description'],
            'fillable' => [
                'name', 'slug', 'description', 'country_id', 'city_id', 'address',
                'latitude', 'longitude', 'phones', 'whatsapp_numbers', 'email',
                'manager_id', 'number_of_employees', 'opening_hours', 'image_url', 'is_active',
            ],
            'casts' => [
                'phones' => 'array',
                'whatsapp_numbers' => 'array',
                'opening_hours' => 'array',
                'is_active' => 'boolean',
            ],
            'soft_deletes' => true,
            'with' => ['country:id,code,name', 'city:id,name'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:255'],
                'country_id' => ['nullable', 'integer', 'exists:countries,id'],
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
                'is_active' => ['nullable', 'boolean'],
            ],
        ],
        'document_types' => [
            'label' => 'Types de documents',
            'model' => DocumentType::class,
            'fillable' => [
                'code', 'label', 'storage_slug', 'unique_per_user', 'requires_expiry_date',
                'requires_document_number', 'max_file_size_kb', 'allowed_extensions',
                'allowed_mime_types', 'sort_order', 'is_active', 'visible_to_candidates',
            ],
            'casts' => [
                'label' => 'array',
                'unique_per_user' => 'boolean',
                'requires_expiry_date' => 'boolean',
                'requires_document_number' => 'boolean',
                'allowed_extensions' => 'array',
                'allowed_mime_types' => 'array',
                'is_active' => 'boolean',
                'visible_to_candidates' => 'boolean',
            ],
            'rules' => [
                'code' => ['required', 'string', 'max:40'],
                'label' => ['required', 'array'],
                'label.fr' => ['required', 'string', 'max:255'],
                'storage_slug' => ['required', 'string', 'max:80'],
                'is_active' => ['nullable', 'boolean'],
                'visible_to_candidates' => ['nullable', 'boolean'],
            ],
        ],
        'languages' => [
            'label' => 'Langues',
            'model' => Language::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'code'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:10'],
            ],
        ],
        'language_levels' => [
            'label' => 'Niveaux de langue',
            'model' => LanguageLevel::class,
            'translatable' => ['name'],
            'fillable' => ['code', 'name', 'sort_order', 'is_active'],
            'casts' => ['is_active' => 'boolean', 'sort_order' => 'integer'],
            'rules' => [
                'code' => ['required', 'string', 'max:100'],
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['nullable', 'boolean'],
            ],
        ],
        'offer_types' => [
            'label' => "Types d'offre",
            'model' => OfferType::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
            ],
        ],
        'work_schedules' => [
            'label' => 'Horaires de travail',
            'model' => WorkSchedule::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
            ],
        ],
        'skill_categories' => [
            'label' => 'Catégories de compétences',
            'model' => SkillCategory::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug'],
            'soft_deletes' => true,
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
            ],
        ],
        'skills' => [
            'label' => 'Compétences',
            'model' => Skill::class,
            'translatable' => ['name'],
            'fillable' => ['name', 'slug', 'skill_category_id', 'category_id'],
            'soft_deletes' => true,
            'with' => ['skillCategory:id,name,slug', 'category:id,name,slug'],
            'rules' => [
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
                'skill_category_id' => ['nullable', 'integer', 'exists:skill_categories,id'],
                'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            ],
        ],
        'trades' => [
            'label' => 'Métiers',
            'model' => Trade::class,
            'translatable' => ['name'],
            'fillable' => ['category_id', 'name', 'slug', 'is_active'],
            'casts' => ['is_active' => 'boolean'],
            'with' => ['category:id,name,slug'],
            'rules' => [
                'category_id' => ['required', 'integer', 'exists:categories,id'],
                'name' => ['required', 'array'],
                'name.fr' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:120'],
                'is_active' => ['nullable', 'boolean'],
            ],
        ],
    ],
];
