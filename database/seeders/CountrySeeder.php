<?php

namespace Database\Seeders;

use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => ['fr' => 'Afrique du Sud', 'en' => 'South Africa'], 'code' => 'ZA', 'phone_code' => '+27', 'flag' => '🇿🇦'],
            ['name' => ['fr' => 'Albanie', 'en' => 'Albania'], 'code' => 'AL', 'phone_code' => '+355', 'flag' => '🇦🇱'],
            ['name' => ['fr' => 'Algérie', 'en' => 'Algeria'], 'code' => 'DZ', 'phone_code' => '+213', 'flag' => '🇩🇿'],
            ['name' => ['fr' => 'Allemagne', 'en' => 'Germany'], 'code' => 'DE', 'phone_code' => '+49', 'flag' => '🇩🇪'],
            ['name' => ['fr' => 'Arabie saoudite', 'en' => 'Saudi Arabia'], 'code' => 'SA', 'phone_code' => '+966', 'flag' => '🇸🇦'],
            ['name' => ['fr' => 'Argentine', 'en' => 'Argentina'], 'code' => 'AR', 'phone_code' => '+54', 'flag' => '🇦🇷'],
            ['name' => ['fr' => 'Australie', 'en' => 'Australia'], 'code' => 'AU', 'phone_code' => '+61', 'flag' => '🇦🇺'],
            ['name' => ['fr' => 'Autriche', 'en' => 'Austria'], 'code' => 'AT', 'phone_code' => '+43', 'flag' => '🇦🇹'],
            ['name' => ['fr' => 'Belgique', 'en' => 'Belgium'], 'code' => 'BE', 'phone_code' => '+32', 'flag' => '🇧🇪'],
            ['name' => ['fr' => 'Bénin', 'en' => 'Benin'], 'code' => 'BJ', 'phone_code' => '+229', 'flag' => '🇧🇯'],
            ['name' => ['fr' => 'Botswana', 'en' => 'Botswana'], 'code' => 'BW', 'phone_code' => '+267', 'flag' => '🇧🇼'],
            ['name' => ['fr' => 'Brésil', 'en' => 'Brazil'], 'code' => 'BR', 'phone_code' => '+55', 'flag' => '🇧🇷'],
            ['name' => ['fr' => 'Bulgarie', 'en' => 'Bulgaria'], 'code' => 'BG', 'phone_code' => '+359', 'flag' => '🇧🇬'],
            ['name' => ['fr' => 'Burkina Faso', 'en' => 'Burkina Faso'], 'code' => 'BF', 'phone_code' => '+226', 'flag' => '🇧🇫'],
            ['name' => ['fr' => 'Burundi', 'en' => 'Burundi'], 'code' => 'BI', 'phone_code' => '+257', 'flag' => '🇧🇮'],
            ['name' => ['fr' => 'Cameroun', 'en' => 'Cameroon'], 'code' => 'CM', 'phone_code' => '+237', 'flag' => '🇨🇲'],
            ['name' => ['fr' => 'Canada', 'en' => 'Canada'], 'code' => 'CA', 'phone_code' => '+1', 'flag' => '🇨🇦'],
            ['name' => ['fr' => 'Cap-Vert', 'en' => 'Cape Verde'], 'code' => 'CV', 'phone_code' => '+238', 'flag' => '🇨🇻'],
            ['name' => ['fr' => 'République centrafricaine', 'en' => 'Central African Republic'], 'code' => 'CF', 'phone_code' => '+236', 'flag' => '🇨🇫'],
            ['name' => ['fr' => 'République du Congo', 'en' => 'Republic of the Congo'], 'code' => 'CG', 'phone_code' => '+242', 'flag' => '🇨🇬'],
            ['name' => ['fr' => 'République démocratique du Congo', 'en' => 'DR Congo'], 'code' => 'CD', 'phone_code' => '+243', 'flag' => '🇨🇩'],
            ['name' => ['fr' => 'Côte d’Ivoire', 'en' => 'Ivory Coast'], 'code' => 'CI', 'phone_code' => '+225', 'flag' => '🇨🇮'],
            ['name' => ['fr' => 'Croatie', 'en' => 'Croatia'], 'code' => 'HR', 'phone_code' => '+385', 'flag' => '🇭🇷'],
            ['name' => ['fr' => 'Égypte', 'en' => 'Egypt'], 'code' => 'EG', 'phone_code' => '+20', 'flag' => '🇪🇬'],
            ['name' => ['fr' => 'Émirats arabes unis', 'en' => 'United Arab Emirates'], 'code' => 'AE', 'phone_code' => '+971', 'flag' => '🇦🇪'],
            ['name' => ['fr' => 'Espagne', 'en' => 'Spain'], 'code' => 'ES', 'phone_code' => '+34', 'flag' => '🇪🇸'],
            ['name' => ['fr' => 'Estonie', 'en' => 'Estonia'], 'code' => 'EE', 'phone_code' => '+372', 'flag' => '🇪🇪'],
            ['name' => ['fr' => 'Finlande', 'en' => 'Finland'], 'code' => 'FI', 'phone_code' => '+358', 'flag' => '🇫🇮'],
            ['name' => ['fr' => 'Gabon', 'en' => 'Gabon'], 'code' => 'GA', 'phone_code' => '+241', 'flag' => '🇬🇦'],
            ['name' => ['fr' => 'Gambie', 'en' => 'Gambia'], 'code' => 'GM', 'phone_code' => '+220', 'flag' => '🇬🇲'],
            ['name' => ['fr' => 'Ghana', 'en' => 'Ghana'], 'code' => 'GH', 'phone_code' => '+233', 'flag' => '🇬🇭'],
            ['name' => ['fr' => 'Grèce', 'en' => 'Greece'], 'code' => 'GR', 'phone_code' => '+30', 'flag' => '🇬🇷'],
            ['name' => ['fr' => 'Grenade', 'en' => 'Grenada'], 'code' => 'GD', 'phone_code' => '+1473', 'flag' => '🇬🇩'],
            ['name' => ['fr' => 'Guinée', 'en' => 'Guinea'], 'code' => 'GN', 'phone_code' => '+224', 'flag' => '🇬🇳'],
            ['name' => ['fr' => 'Guinée-Bissau', 'en' => 'Guinea-Bissau'], 'code' => 'GW', 'phone_code' => '+245', 'flag' => '🇬🇼'],
            ['name' => ['fr' => 'Guinée équatoriale', 'en' => 'Equatorial Guinea'], 'code' => 'GQ', 'phone_code' => '+240', 'flag' => '🇬🇶'],
            ['name' => ['fr' => 'Irlande', 'en' => 'Ireland'], 'code' => 'IE', 'phone_code' => '+353', 'flag' => '🇮🇪'],
            ['name' => ['fr' => 'Islande', 'en' => 'Iceland'], 'code' => 'IS', 'phone_code' => '+354', 'flag' => '🇮🇸'],
            ['name' => ['fr' => 'Israël', 'en' => 'Israel'], 'code' => 'IL', 'phone_code' => '+972', 'flag' => '🇮🇱'],
            ['name' => ['fr' => 'Italie', 'en' => 'Italy'], 'code' => 'IT', 'phone_code' => '+39', 'flag' => '🇮🇹'],
            ['name' => ['fr' => 'Kenya', 'en' => 'Kenya'], 'code' => 'KE', 'phone_code' => '+254', 'flag' => '🇰🇪'],
            ['name' => ['fr' => 'Liberia', 'en' => 'Liberia'], 'code' => 'LR', 'phone_code' => '+231', 'flag' => '🇱🇷'],
            ['name' => ['fr' => 'Lituanie', 'en' => 'Lithuania'], 'code' => 'LT', 'phone_code' => '+370', 'flag' => '🇱🇹'],
            ['name' => ['fr' => 'Luxembourg', 'en' => 'Luxembourg'], 'code' => 'LU', 'phone_code' => '+352', 'flag' => '🇱🇺'],
            ['name' => ['fr' => 'Madagascar', 'en' => 'Madagascar'], 'code' => 'MG', 'phone_code' => '+261', 'flag' => '🇲🇬'],
            ['name' => ['fr' => 'Malaisie', 'en' => 'Malaysia'], 'code' => 'MY', 'phone_code' => '+60', 'flag' => '🇲🇾'],
            ['name' => ['fr' => 'Malawi', 'en' => 'Malawi'], 'code' => 'MW', 'phone_code' => '+265', 'flag' => '🇲🇼'],
            ['name' => ['fr' => 'Mali', 'en' => 'Mali'], 'code' => 'ML', 'phone_code' => '+223', 'flag' => '🇲🇱'],
            ['name' => ['fr' => 'Malte', 'en' => 'Malta'], 'code' => 'MT', 'phone_code' => '+356', 'flag' => '🇲🇹'],
            ['name' => ['fr' => 'Maroc', 'en' => 'Morocco'], 'code' => 'MA', 'phone_code' => '+212', 'flag' => '🇲🇦'],
            ['name' => ['fr' => 'Mozambique', 'en' => 'Mozambique'], 'code' => 'MZ', 'phone_code' => '+258', 'flag' => '🇲🇿'],
            ['name' => ['fr' => 'Namibie', 'en' => 'Namibia'], 'code' => 'NA', 'phone_code' => '+264', 'flag' => '🇳🇦'],

            ['name' => ['fr' => 'Niger', 'en' => 'Niger'], 'code' => 'NE', 'phone_code' => '+227', 'flag' => '🇳🇪'],
            ['name' => ['fr' => 'Nigeria', 'en' => 'Nigeria'], 'code' => 'NG', 'phone_code' => '+234', 'flag' => '🇳🇬'],
            ['name' => ['fr' => 'Norvège', 'en' => 'Norway'], 'code' => 'NO', 'phone_code' => '+47', 'flag' => '🇳🇴'],
            ['name' => ['fr' => 'Nouvelle-Zélande', 'en' => 'New Zealand'], 'code' => 'NZ', 'phone_code' => '+64', 'flag' => '🇳🇿'],
            ['name' => ['fr' => 'Pays-Bas', 'en' => 'Netherlands'], 'code' => 'NL', 'phone_code' => '+31', 'flag' => '🇳🇱'],
            ['name' => ['fr' => 'Pologne', 'en' => 'Poland'], 'code' => 'PL', 'phone_code' => '+48', 'flag' => '🇵🇱'],
            ['name' => ['fr' => 'Portugal', 'en' => 'Portugal'], 'code' => 'PT', 'phone_code' => '+351', 'flag' => '🇵🇹'],
            ['name' => ['fr' => 'Qatar', 'en' => 'Qatar'], 'code' => 'QA', 'phone_code' => '+974', 'flag' => '🇶🇦'],
            ['name' => ['fr' => 'Royaume-Uni', 'en' => 'United Kingdom'], 'code' => 'GB', 'phone_code' => '+44', 'flag' => '🇬🇧'],
            ['name' => ['fr' => 'Russie', 'en' => 'Russia'], 'code' => 'RU', 'phone_code' => '+7', 'flag' => '🇷🇺'],
            ['name' => ['fr' => 'Rwanda', 'en' => 'Rwanda'], 'code' => 'RW', 'phone_code' => '+250', 'flag' => '🇷🇼'],
            ['name' => ['fr' => 'Sao Tomé-et-Principe', 'en' => 'São Tomé and Príncipe'], 'code' => 'ST', 'phone_code' => '+239', 'flag' => '🇸🇹'],
            ['name' => ['fr' => 'Sénégal', 'en' => 'Senegal'], 'code' => 'SN', 'phone_code' => '+221', 'flag' => '🇸🇳'],
            ['name' => ['fr' => 'Serbie', 'en' => 'Serbia'], 'code' => 'RS', 'phone_code' => '+381', 'flag' => '🇷🇸'],
            ['name' => ['fr' => 'Suède', 'en' => 'Sweden'], 'code' => 'SE', 'phone_code' => '+46', 'flag' => '🇸🇪'],
            ['name' => ['fr' => 'Suisse', 'en' => 'Switzerland'], 'code' => 'CH', 'phone_code' => '+41', 'flag' => '🇨🇭'],
            ['name' => ['fr' => 'Tchad', 'en' => 'Chad'], 'code' => 'TD', 'phone_code' => '+235', 'flag' => '🇹🇩'],
            ['name' => ['fr' => 'Togo', 'en' => 'Togo'], 'code' => 'TG', 'phone_code' => '+228', 'flag' => '🇹🇬'],
            ['name' => ['fr' => 'Tunisie', 'en' => 'Tunisia'], 'code' => 'TN', 'phone_code' => '+216', 'flag' => '🇹🇳'],
            ['name' => ['fr' => 'Turquie', 'en' => 'Turkey'], 'code' => 'TR', 'phone_code' => '+90', 'flag' => '🇹🇷'],
            ['name' => ['fr' => 'Zambie', 'en' => 'Zambia'], 'code' => 'ZM', 'phone_code' => '+260', 'flag' => '🇿🇲'],
            ['name' => ['fr' => 'Zimbabwe', 'en' => 'Zimbabwe'], 'code' => 'ZW', 'phone_code' => '+263', 'flag' => '🇿🇼'],
        ];

        foreach ($countries as $country) {
            $model = Country::query()->firstOrNew(['code' => $country['code']]);
            $model->phone_code = $country['phone_code'] ?? null;
            $model->flag = $country['flag'] ?? null;
            $model->is_active = true;
            $model->setTranslations('name', $country['name']);
            $model->save();
        }
    }
}
