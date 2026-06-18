<?php

namespace Database\Seeders;

use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        // Liste exhaustive des pays du monde (ONU + observateurs)
        $countries = [
            // A
            ['name' => ['fr' => 'Afghanistan', 'en' => 'Afghanistan'], 'code' => 'AF', 'phone_code' => '+93', 'flag' => '🇦🇫'],
            ['name' => ['fr' => 'Afrique du Sud', 'en' => 'South Africa'], 'code' => 'ZA', 'phone_code' => '+27', 'flag' => '🇿🇦'],
            ['name' => ['fr' => 'Albanie', 'en' => 'Albania'], 'code' => 'AL', 'phone_code' => '+355', 'flag' => '🇦🇱'],
            ['name' => ['fr' => 'Algérie', 'en' => 'Algeria'], 'code' => 'DZ', 'phone_code' => '+213', 'flag' => '🇩🇿'],
            ['name' => ['fr' => 'Allemagne', 'en' => 'Germany'], 'code' => 'DE', 'phone_code' => '+49', 'flag' => '🇩🇪'],
            ['name' => ['fr' => 'Andorre', 'en' => 'Andorra'], 'code' => 'AD', 'phone_code' => '+376', 'flag' => '🇦🇩'],
            ['name' => ['fr' => 'Angola', 'en' => 'Angola'], 'code' => 'AO', 'phone_code' => '+244', 'flag' => '🇦🇴'],
            ['name' => ['fr' => 'Antigua-et-Barbuda', 'en' => 'Antigua and Barbuda'], 'code' => 'AG', 'phone_code' => '+1268', 'flag' => '🇦🇬'],
            ['name' => ['fr' => 'Arabie saoudite', 'en' => 'Saudi Arabia'], 'code' => 'SA', 'phone_code' => '+966', 'flag' => '🇸🇦'],
            ['name' => ['fr' => 'Argentine', 'en' => 'Argentina'], 'code' => 'AR', 'phone_code' => '+54', 'flag' => '🇦🇷'],
            ['name' => ['fr' => 'Arménie', 'en' => 'Armenia'], 'code' => 'AM', 'phone_code' => '+374', 'flag' => '🇦🇲'],
            ['name' => ['fr' => 'Australie', 'en' => 'Australia'], 'code' => 'AU', 'phone_code' => '+61', 'flag' => '🇦🇺'],
            ['name' => ['fr' => 'Autriche', 'en' => 'Austria'], 'code' => 'AT', 'phone_code' => '+43', 'flag' => '🇦🇹'],
            ['name' => ['fr' => 'Azerbaïdjan', 'en' => 'Azerbaijan'], 'code' => 'AZ', 'phone_code' => '+994', 'flag' => '🇦🇿'],
            // B
            ['name' => ['fr' => 'Bahamas', 'en' => 'Bahamas'], 'code' => 'BS', 'phone_code' => '+1242', 'flag' => '🇧🇸'],
            ['name' => ['fr' => 'Bahreïn', 'en' => 'Bahrain'], 'code' => 'BH', 'phone_code' => '+973', 'flag' => '🇧🇭'],
            ['name' => ['fr' => 'Bangladesh', 'en' => 'Bangladesh'], 'code' => 'BD', 'phone_code' => '+880', 'flag' => '🇧🇩'],
            ['name' => ['fr' => 'Barbade', 'en' => 'Barbados'], 'code' => 'BB', 'phone_code' => '+1246', 'flag' => '🇧🇧'],
            ['name' => ['fr' => 'Bélarus', 'en' => 'Belarus'], 'code' => 'BY', 'phone_code' => '+375', 'flag' => '🇧🇾'],
            ['name' => ['fr' => 'Belgique', 'en' => 'Belgium'], 'code' => 'BE', 'phone_code' => '+32', 'flag' => '🇧🇪'],
            ['name' => ['fr' => 'Belize', 'en' => 'Belize'], 'code' => 'BZ', 'phone_code' => '+501', 'flag' => '🇧🇿'],
            ['name' => ['fr' => 'Bénin', 'en' => 'Benin'], 'code' => 'BJ', 'phone_code' => '+229', 'flag' => '🇧🇯'],
            ['name' => ['fr' => 'Bhoutan', 'en' => 'Bhutan'], 'code' => 'BT', 'phone_code' => '+975', 'flag' => '🇧🇹'],
            ['name' => ['fr' => 'Bolivie', 'en' => 'Bolivia'], 'code' => 'BO', 'phone_code' => '+591', 'flag' => '🇧🇴'],
            ['name' => ['fr' => 'Bosnie-Herzégovine', 'en' => 'Bosnia and Herzegovina'], 'code' => 'BA', 'phone_code' => '+387', 'flag' => '🇧🇦'],
            ['name' => ['fr' => 'Botswana', 'en' => 'Botswana'], 'code' => 'BW', 'phone_code' => '+267', 'flag' => '🇧🇼'],
            ['name' => ['fr' => 'Brésil', 'en' => 'Brazil'], 'code' => 'BR', 'phone_code' => '+55', 'flag' => '🇧🇷'],
            ['name' => ['fr' => 'Brunei', 'en' => 'Brunei'], 'code' => 'BN', 'phone_code' => '+673', 'flag' => '🇧🇳'],
            ['name' => ['fr' => 'Bulgarie', 'en' => 'Bulgaria'], 'code' => 'BG', 'phone_code' => '+359', 'flag' => '🇧🇬'],
            ['name' => ['fr' => 'Burkina Faso', 'en' => 'Burkina Faso'], 'code' => 'BF', 'phone_code' => '+226', 'flag' => '🇧🇫'],
            ['name' => ['fr' => 'Burundi', 'en' => 'Burundi'], 'code' => 'BI', 'phone_code' => '+257', 'flag' => '🇧🇮'],
            // C
            ['name' => ['fr' => 'Cambodge', 'en' => 'Cambodia'], 'code' => 'KH', 'phone_code' => '+855', 'flag' => '🇰🇭'],
            ['name' => ['fr' => 'Cameroun', 'en' => 'Cameroon'], 'code' => 'CM', 'phone_code' => '+237', 'flag' => '🇨🇲'],
            ['name' => ['fr' => 'Canada', 'en' => 'Canada'], 'code' => 'CA', 'phone_code' => '+1', 'flag' => '🇨🇦'],
            ['name' => ['fr' => 'Cap-Vert', 'en' => 'Cape Verde'], 'code' => 'CV', 'phone_code' => '+238', 'flag' => '🇨🇻'],
            ['name' => ['fr' => 'République centrafricaine', 'en' => 'Central African Republic'], 'code' => 'CF', 'phone_code' => '+236', 'flag' => '🇨🇫'],
            ['name' => ['fr' => 'Chili', 'en' => 'Chile'], 'code' => 'CL', 'phone_code' => '+56', 'flag' => '🇨🇱'],
            ['name' => ['fr' => 'Chine', 'en' => 'China'], 'code' => 'CN', 'phone_code' => '+86', 'flag' => '🇨🇳'],
            ['name' => ['fr' => 'Colombie', 'en' => 'Colombia'], 'code' => 'CO', 'phone_code' => '+57', 'flag' => '🇨🇴'],
            ['name' => ['fr' => 'Comores', 'en' => 'Comoros'], 'code' => 'KM', 'phone_code' => '+269', 'flag' => '🇰🇲'],
            ['name' => ['fr' => 'République du Congo', 'en' => 'Republic of the Congo'], 'code' => 'CG', 'phone_code' => '+242', 'flag' => '🇨🇬'],
            ['name' => ['fr' => 'République démocratique du Congo', 'en' => 'DR Congo'], 'code' => 'CD', 'phone_code' => '+243', 'flag' => '🇨🇩'],
            ['name' => ['fr' => 'Corée du Nord', 'en' => 'North Korea'], 'code' => 'KP', 'phone_code' => '+850', 'flag' => '🇰🇵'],
            ['name' => ['fr' => 'Corée du Sud', 'en' => 'South Korea'], 'code' => 'KR', 'phone_code' => '+82', 'flag' => '🇰🇷'],
            ['name' => ['fr' => 'Costa Rica', 'en' => 'Costa Rica'], 'code' => 'CR', 'phone_code' => '+506', 'flag' => '🇨🇷'],
            ['name' => ['fr' => 'Côte d’Ivoire', 'en' => 'Ivory Coast'], 'code' => 'CI', 'phone_code' => '+225', 'flag' => '🇨🇮'],
            ['name' => ['fr' => 'Croatie', 'en' => 'Croatia'], 'code' => 'HR', 'phone_code' => '+385', 'flag' => '🇭🇷'],
            ['name' => ['fr' => 'Cuba', 'en' => 'Cuba'], 'code' => 'CU', 'phone_code' => '+53', 'flag' => '🇨🇺'],
            ['name' => ['fr' => 'Chypre', 'en' => 'Cyprus'], 'code' => 'CY', 'phone_code' => '+357', 'flag' => '🇨🇾'],
            ['name' => ['fr' => 'Tchéquie', 'en' => 'Czechia'], 'code' => 'CZ', 'phone_code' => '+420', 'flag' => '🇨🇿'],
            // D
            ['name' => ['fr' => 'Danemark', 'en' => 'Denmark'], 'code' => 'DK', 'phone_code' => '+45', 'flag' => '🇩🇰'],
            ['name' => ['fr' => 'Djibouti', 'en' => 'Djibouti'], 'code' => 'DJ', 'phone_code' => '+253', 'flag' => '🇩🇯'],
            ['name' => ['fr' => 'Dominique', 'en' => 'Dominica'], 'code' => 'DM', 'phone_code' => '+1767', 'flag' => '🇩🇲'],
            ['name' => ['fr' => 'République dominicaine', 'en' => 'Dominican Republic'], 'code' => 'DO', 'phone_code' => '+1849', 'flag' => '🇩🇴'],
            // E
            ['name' => ['fr' => 'Égypte', 'en' => 'Egypt'], 'code' => 'EG', 'phone_code' => '+20', 'flag' => '🇪🇬'],
            ['name' => ['fr' => 'Émirats arabes unis', 'en' => 'United Arab Emirates'], 'code' => 'AE', 'phone_code' => '+971', 'flag' => '🇦🇪'],
            ['name' => ['fr' => 'Équateur', 'en' => 'Ecuador'], 'code' => 'EC', 'phone_code' => '+593', 'flag' => '🇪🇨'],
            ['name' => ['fr' => 'Érythrée', 'en' => 'Eritrea'], 'code' => 'ER', 'phone_code' => '+291', 'flag' => '🇪🇷'],
            ['name' => ['fr' => 'Espagne', 'en' => 'Spain'], 'code' => 'ES', 'phone_code' => '+34', 'flag' => '🇪🇸'],
            ['name' => ['fr' => 'Estonie', 'en' => 'Estonia'], 'code' => 'EE', 'phone_code' => '+372', 'flag' => '🇪🇪'],
            ['name' => ['fr' => 'Eswatini', 'en' => 'Eswatini'], 'code' => 'SZ', 'phone_code' => '+268', 'flag' => '🇸🇿'],
            ['name' => ['fr' => 'Éthiopie', 'en' => 'Ethiopia'], 'code' => 'ET', 'phone_code' => '+251', 'flag' => '🇪🇹'],
            // F
            ['name' => ['fr' => 'Fidji', 'en' => 'Fiji'], 'code' => 'FJ', 'phone_code' => '+679', 'flag' => '🇫🇯'],
            ['name' => ['fr' => 'Finlande', 'en' => 'Finland'], 'code' => 'FI', 'phone_code' => '+358', 'flag' => '🇫🇮'],
            ['name' => ['fr' => 'France', 'en' => 'France'], 'code' => 'FR', 'phone_code' => '+33', 'flag' => '🇫🇷'],
            // G
            ['name' => ['fr' => 'Gabon', 'en' => 'Gabon'], 'code' => 'GA', 'phone_code' => '+241', 'flag' => '🇬🇦'],
            ['name' => ['fr' => 'Gambie', 'en' => 'Gambia'], 'code' => 'GM', 'phone_code' => '+220', 'flag' => '🇬🇲'],
            ['name' => ['fr' => 'Géorgie', 'en' => 'Georgia'], 'code' => 'GE', 'phone_code' => '+995', 'flag' => '🇬🇪'],
            ['name' => ['fr' => 'Ghana', 'en' => 'Ghana'], 'code' => 'GH', 'phone_code' => '+233', 'flag' => '🇬🇭'],
            ['name' => ['fr' => 'Grèce', 'en' => 'Greece'], 'code' => 'GR', 'phone_code' => '+30', 'flag' => '🇬🇷'],
            ['name' => ['fr' => 'Grenade', 'en' => 'Grenada'], 'code' => 'GD', 'phone_code' => '+1473', 'flag' => '🇬🇩'],
            ['name' => ['fr' => 'Guatemala', 'en' => 'Guatemala'], 'code' => 'GT', 'phone_code' => '+502', 'flag' => '🇬🇹'],
            ['name' => ['fr' => 'Guinée', 'en' => 'Guinea'], 'code' => 'GN', 'phone_code' => '+224', 'flag' => '🇬🇳'],
            ['name' => ['fr' => 'Guinée-Bissau', 'en' => 'Guinea-Bissau'], 'code' => 'GW', 'phone_code' => '+245', 'flag' => '🇬🇼'],
            ['name' => ['fr' => 'Guinée équatoriale', 'en' => 'Equatorial Guinea'], 'code' => 'GQ', 'phone_code' => '+240', 'flag' => '🇬🇶'],
            ['name' => ['fr' => 'Guyana', 'en' => 'Guyana'], 'code' => 'GY', 'phone_code' => '+592', 'flag' => '🇬🇾'],
            // H
            ['name' => ['fr' => 'Haïti', 'en' => 'Haiti'], 'code' => 'HT', 'phone_code' => '+509', 'flag' => '🇭🇹'],
            ['name' => ['fr' => 'Honduras', 'en' => 'Honduras'], 'code' => 'HN', 'phone_code' => '+504', 'flag' => '🇭🇳'],
            ['name' => ['fr' => 'Hongrie', 'en' => 'Hungary'], 'code' => 'HU', 'phone_code' => '+36', 'flag' => '🇭🇺'],
            // I
            ['name' => ['fr' => 'Inde', 'en' => 'India'], 'code' => 'IN', 'phone_code' => '+91', 'flag' => '🇮🇳'],
            ['name' => ['fr' => 'Indonésie', 'en' => 'Indonesia'], 'code' => 'ID', 'phone_code' => '+62', 'flag' => '🇮🇩'],
            ['name' => ['fr' => 'Iran', 'en' => 'Iran'], 'code' => 'IR', 'phone_code' => '+98', 'flag' => '🇮🇷'],
            ['name' => ['fr' => 'Iraq', 'en' => 'Iraq'], 'code' => 'IQ', 'phone_code' => '+964', 'flag' => '🇮🇶'],
            ['name' => ['fr' => 'Irlande', 'en' => 'Ireland'], 'code' => 'IE', 'phone_code' => '+353', 'flag' => '🇮🇪'],
            ['name' => ['fr' => 'Islande', 'en' => 'Iceland'], 'code' => 'IS', 'phone_code' => '+354', 'flag' => '🇮🇸'],
            ['name' => ['fr' => 'Israël', 'en' => 'Israel'], 'code' => 'IL', 'phone_code' => '+972', 'flag' => '🇮🇱'],
            ['name' => ['fr' => 'Italie', 'en' => 'Italy'], 'code' => 'IT', 'phone_code' => '+39', 'flag' => '🇮🇹'],
            // J
            ['name' => ['fr' => 'Jamaïque', 'en' => 'Jamaica'], 'code' => 'JM', 'phone_code' => '+1876', 'flag' => '🇯🇲'],
            ['name' => ['fr' => 'Japon', 'en' => 'Japan'], 'code' => 'JP', 'phone_code' => '+81', 'flag' => '🇯🇵'],
            ['name' => ['fr' => 'Jordanie', 'en' => 'Jordan'], 'code' => 'JO', 'phone_code' => '+962', 'flag' => '🇯🇴'],
            // K
            ['name' => ['fr' => 'Kazakhstan', 'en' => 'Kazakhstan'], 'code' => 'KZ', 'phone_code' => '+7', 'flag' => '🇰🇿'],
            ['name' => ['fr' => 'Kenya', 'en' => 'Kenya'], 'code' => 'KE', 'phone_code' => '+254', 'flag' => '🇰🇪'],
            ['name' => ['fr' => 'Kirghizistan', 'en' => 'Kyrgyzstan'], 'code' => 'KG', 'phone_code' => '+996', 'flag' => '🇰🇬'],
            ['name' => ['fr' => 'Kiribati', 'en' => 'Kiribati'], 'code' => 'KI', 'phone_code' => '+686', 'flag' => '🇰🇮'],
            ['name' => ['fr' => 'Koweït', 'en' => 'Kuwait'], 'code' => 'KW', 'phone_code' => '+965', 'flag' => '🇰🇼'],
            // L
            ['name' => ['fr' => 'Laos', 'en' => 'Laos'], 'code' => 'LA', 'phone_code' => '+856', 'flag' => '🇱🇦'],
            ['name' => ['fr' => 'Lesotho', 'en' => 'Lesotho'], 'code' => 'LS', 'phone_code' => '+266', 'flag' => '🇱🇸'],
            ['name' => ['fr' => 'Lettonie', 'en' => 'Latvia'], 'code' => 'LV', 'phone_code' => '+371', 'flag' => '🇱🇻'],
            ['name' => ['fr' => 'Liban', 'en' => 'Lebanon'], 'code' => 'LB', 'phone_code' => '+961', 'flag' => '🇱🇧'],
            ['name' => ['fr' => 'Liberia', 'en' => 'Liberia'], 'code' => 'LR', 'phone_code' => '+231', 'flag' => '🇱🇷'],
            ['name' => ['fr' => 'Libye', 'en' => 'Libya'], 'code' => 'LY', 'phone_code' => '+218', 'flag' => '🇱🇾'],
            ['name' => ['fr' => 'Liechtenstein', 'en' => 'Liechtenstein'], 'code' => 'LI', 'phone_code' => '+423', 'flag' => '🇱🇮'],
            ['name' => ['fr' => 'Lituanie', 'en' => 'Lithuania'], 'code' => 'LT', 'phone_code' => '+370', 'flag' => '🇱🇹'],
            ['name' => ['fr' => 'Luxembourg', 'en' => 'Luxembourg'], 'code' => 'LU', 'phone_code' => '+352', 'flag' => '🇱🇺'],
            // M
            ['name' => ['fr' => 'Macédoine du Nord', 'en' => 'North Macedonia'], 'code' => 'MK', 'phone_code' => '+389', 'flag' => '🇲🇰'],
            ['name' => ['fr' => 'Madagascar', 'en' => 'Madagascar'], 'code' => 'MG', 'phone_code' => '+261', 'flag' => '🇲🇬'],
            ['name' => ['fr' => 'Malaisie', 'en' => 'Malaysia'], 'code' => 'MY', 'phone_code' => '+60', 'flag' => '🇲🇾'],
            ['name' => ['fr' => 'Malawi', 'en' => 'Malawi'], 'code' => 'MW', 'phone_code' => '+265', 'flag' => '🇲🇼'],
            ['name' => ['fr' => 'Maldives', 'en' => 'Maldives'], 'code' => 'MV', 'phone_code' => '+960', 'flag' => '🇲🇻'],
            ['name' => ['fr' => 'Mali', 'en' => 'Mali'], 'code' => 'ML', 'phone_code' => '+223', 'flag' => '🇲🇱'],
            ['name' => ['fr' => 'Malte', 'en' => 'Malta'], 'code' => 'MT', 'phone_code' => '+356', 'flag' => '🇲🇹'],
            ['name' => ['fr' => 'Maroc', 'en' => 'Morocco'], 'code' => 'MA', 'phone_code' => '+212', 'flag' => '🇲🇦'],
            ['name' => ['fr' => 'Îles Marshall', 'en' => 'Marshall Islands'], 'code' => 'MH', 'phone_code' => '+692', 'flag' => '🇲🇭'],
            ['name' => ['fr' => 'Maurice', 'en' => 'Mauritius'], 'code' => 'MU', 'phone_code' => '+230', 'flag' => '🇲🇺'],
            ['name' => ['fr' => 'Mauritanie', 'en' => 'Mauritania'], 'code' => 'MR', 'phone_code' => '+222', 'flag' => '🇲🇷'],
            ['name' => ['fr' => 'Mexique', 'en' => 'Mexico'], 'code' => 'MX', 'phone_code' => '+52', 'flag' => '🇲🇽'],
            ['name' => ['fr' => 'Micronésie', 'en' => 'Micronesia'], 'code' => 'FM', 'phone_code' => '+691', 'flag' => '🇫🇲'],
            ['name' => ['fr' => 'Moldavie', 'en' => 'Moldova'], 'code' => 'MD', 'phone_code' => '+373', 'flag' => '🇲🇩'],
            ['name' => ['fr' => 'Monaco', 'en' => 'Monaco'], 'code' => 'MC', 'phone_code' => '+377', 'flag' => '🇲🇨'],
            ['name' => ['fr' => 'Mongolie', 'en' => 'Mongolia'], 'code' => 'MN', 'phone_code' => '+976', 'flag' => '🇲🇳'],
            ['name' => ['fr' => 'Monténégro', 'en' => 'Montenegro'], 'code' => 'ME', 'phone_code' => '+382', 'flag' => '🇲🇪'],
            ['name' => ['fr' => 'Mozambique', 'en' => 'Mozambique'], 'code' => 'MZ', 'phone_code' => '+258', 'flag' => '🇲🇿'],
            ['name' => ['fr' => 'Myanmar', 'en' => 'Myanmar'], 'code' => 'MM', 'phone_code' => '+95', 'flag' => '🇲🇲'],
            // N
            ['name' => ['fr' => 'Namibie', 'en' => 'Namibia'], 'code' => 'NA', 'phone_code' => '+264', 'flag' => '🇳🇦'],
            ['name' => ['fr' => 'Nauru', 'en' => 'Nauru'], 'code' => 'NR', 'phone_code' => '+674', 'flag' => '🇳🇷'],
            ['name' => ['fr' => 'Népal', 'en' => 'Nepal'], 'code' => 'NP', 'phone_code' => '+977', 'flag' => '🇳🇵'],
            ['name' => ['fr' => 'Nicaragua', 'en' => 'Nicaragua'], 'code' => 'NI', 'phone_code' => '+505', 'flag' => '🇳🇮'],
            ['name' => ['fr' => 'Niger', 'en' => 'Niger'], 'code' => 'NE', 'phone_code' => '+227', 'flag' => '🇳🇪'],
            ['name' => ['fr' => 'Nigeria', 'en' => 'Nigeria'], 'code' => 'NG', 'phone_code' => '+234', 'flag' => '🇳🇬'],
            ['name' => ['fr' => 'Norvège', 'en' => 'Norway'], 'code' => 'NO', 'phone_code' => '+47', 'flag' => '🇳🇴'],
            ['name' => ['fr' => 'Nouvelle-Zélande', 'en' => 'New Zealand'], 'code' => 'NZ', 'phone_code' => '+64', 'flag' => '🇳🇿'],
            // O
            ['name' => ['fr' => 'Oman', 'en' => 'Oman'], 'code' => 'OM', 'phone_code' => '+968', 'flag' => '🇴🇲'],
            ['name' => ['fr' => 'Ouganda', 'en' => 'Uganda'], 'code' => 'UG', 'phone_code' => '+256', 'flag' => '🇺🇬'],
            ['name' => ['fr' => 'Ouzbékistan', 'en' => 'Uzbekistan'], 'code' => 'UZ', 'phone_code' => '+998', 'flag' => '🇺🇿'],
            // P
            ['name' => ['fr' => 'Pakistan', 'en' => 'Pakistan'], 'code' => 'PK', 'phone_code' => '+92', 'flag' => '🇵🇰'],
            ['name' => ['fr' => 'Palaos', 'en' => 'Palau'], 'code' => 'PW', 'phone_code' => '+680', 'flag' => '🇵🇼'],
            ['name' => ['fr' => 'Palestine', 'en' => 'Palestine'], 'code' => 'PS', 'phone_code' => '+970', 'flag' => '🇵🇸'],
            ['name' => ['fr' => 'Panama', 'en' => 'Panama'], 'code' => 'PA', 'phone_code' => '+507', 'flag' => '🇵🇦'],
            ['name' => ['fr' => 'Papouasie-Nouvelle-Guinée', 'en' => 'Papua New Guinea'], 'code' => 'PG', 'phone_code' => '+675', 'flag' => '🇵🇬'],
            ['name' => ['fr' => 'Paraguay', 'en' => 'Paraguay'], 'code' => 'PY', 'phone_code' => '+595', 'flag' => '🇵🇾'],
            ['name' => ['fr' => 'Pays-Bas', 'en' => 'Netherlands'], 'code' => 'NL', 'phone_code' => '+31', 'flag' => '🇳🇱'],
            ['name' => ['fr' => 'Pérou', 'en' => 'Peru'], 'code' => 'PE', 'phone_code' => '+51', 'flag' => '🇵🇪'],
            ['name' => ['fr' => 'Philippines', 'en' => 'Philippines'], 'code' => 'PH', 'phone_code' => '+63', 'flag' => '🇵🇭'],
            ['name' => ['fr' => 'Pologne', 'en' => 'Poland'], 'code' => 'PL', 'phone_code' => '+48', 'flag' => '🇵🇱'],
            ['name' => ['fr' => 'Portugal', 'en' => 'Portugal'], 'code' => 'PT', 'phone_code' => '+351', 'flag' => '🇵🇹'],
            // Q
            ['name' => ['fr' => 'Qatar', 'en' => 'Qatar'], 'code' => 'QA', 'phone_code' => '+974', 'flag' => '🇶🇦'],
            // R
            ['name' => ['fr' => 'Roumanie', 'en' => 'Romania'], 'code' => 'RO', 'phone_code' => '+40', 'flag' => '🇷🇴'],
            ['name' => ['fr' => 'Royaume-Uni', 'en' => 'United Kingdom'], 'code' => 'GB', 'phone_code' => '+44', 'flag' => '🇬🇧'],
            ['name' => ['fr' => 'Russie', 'en' => 'Russia'], 'code' => 'RU', 'phone_code' => '+7', 'flag' => '🇷🇺'],
            ['name' => ['fr' => 'Rwanda', 'en' => 'Rwanda'], 'code' => 'RW', 'phone_code' => '+250', 'flag' => '🇷🇼'],
            // S
            ['name' => ['fr' => 'Saint-Christophe-et-Niévès', 'en' => 'Saint Kitts and Nevis'], 'code' => 'KN', 'phone_code' => '+1869', 'flag' => '🇰🇳'],
            ['name' => ['fr' => 'Sainte-Lucie', 'en' => 'Saint Lucia'], 'code' => 'LC', 'phone_code' => '+1758', 'flag' => '🇱🇨'],
            ['name' => ['fr' => 'Saint-Marin', 'en' => 'San Marino'], 'code' => 'SM', 'phone_code' => '+378', 'flag' => '🇸🇲'],
            ['name' => ['fr' => 'Saint-Vincent-et-les-Grenadines', 'en' => 'Saint Vincent and the Grenadines'], 'code' => 'VC', 'phone_code' => '+1784', 'flag' => '🇻🇨'],
            ['name' => ['fr' => 'Salomon', 'en' => 'Solomon Islands'], 'code' => 'SB', 'phone_code' => '+677', 'flag' => '🇸🇧'],
            ['name' => ['fr' => 'Salvador', 'en' => 'El Salvador'], 'code' => 'SV', 'phone_code' => '+503', 'flag' => '🇸🇻'],
            ['name' => ['fr' => 'Samoa', 'en' => 'Samoa'], 'code' => 'WS', 'phone_code' => '+685', 'flag' => '🇼🇸'],
            ['name' => ['fr' => 'Sao Tomé-et-Principe', 'en' => 'São Tomé and Príncipe'], 'code' => 'ST', 'phone_code' => '+239', 'flag' => '🇸🇹'],
            ['name' => ['fr' => 'Sénégal', 'en' => 'Senegal'], 'code' => 'SN', 'phone_code' => '+221', 'flag' => '🇸🇳'],
            ['name' => ['fr' => 'Serbie', 'en' => 'Serbia'], 'code' => 'RS', 'phone_code' => '+381', 'flag' => '🇷🇸'],
            ['name' => ['fr' => 'Seychelles', 'en' => 'Seychelles'], 'code' => 'SC', 'phone_code' => '+248', 'flag' => '🇸🇨'],
            ['name' => ['fr' => 'Sierra Leone', 'en' => 'Sierra Leone'], 'code' => 'SL', 'phone_code' => '+232', 'flag' => '🇸🇱'],
            ['name' => ['fr' => 'Singapour', 'en' => 'Singapore'], 'code' => 'SG', 'phone_code' => '+65', 'flag' => '🇸🇬'],
            ['name' => ['fr' => 'Slovaquie', 'en' => 'Slovakia'], 'code' => 'SK', 'phone_code' => '+421', 'flag' => '🇸🇰'],
            ['name' => ['fr' => 'Slovénie', 'en' => 'Slovenia'], 'code' => 'SI', 'phone_code' => '+386', 'flag' => '🇸🇮'],
            ['name' => ['fr' => 'Somalie', 'en' => 'Somalia'], 'code' => 'SO', 'phone_code' => '+252', 'flag' => '🇸🇴'],
            ['name' => ['fr' => 'Soudan', 'en' => 'Sudan'], 'code' => 'SD', 'phone_code' => '+249', 'flag' => '🇸🇩'],
            ['name' => ['fr' => 'Soudan du Sud', 'en' => 'South Sudan'], 'code' => 'SS', 'phone_code' => '+211', 'flag' => '🇸🇸'],
            ['name' => ['fr' => 'Sri Lanka', 'en' => 'Sri Lanka'], 'code' => 'LK', 'phone_code' => '+94', 'flag' => '🇱🇰'],
            ['name' => ['fr' => 'Suède', 'en' => 'Sweden'], 'code' => 'SE', 'phone_code' => '+46', 'flag' => '🇸🇪'],
            ['name' => ['fr' => 'Suisse', 'en' => 'Switzerland'], 'code' => 'CH', 'phone_code' => '+41', 'flag' => '🇨🇭'],
            ['name' => ['fr' => 'Suriname', 'en' => 'Suriname'], 'code' => 'SR', 'phone_code' => '+597', 'flag' => '🇸🇷'],
            ['name' => ['fr' => 'Syrie', 'en' => 'Syria'], 'code' => 'SY', 'phone_code' => '+963', 'flag' => '🇸🇾'],
            // T
            ['name' => ['fr' => 'Tadjikistan', 'en' => 'Tajikistan'], 'code' => 'TJ', 'phone_code' => '+992', 'flag' => '🇹🇯'],
            ['name' => ['fr' => 'Tanzanie', 'en' => 'Tanzania'], 'code' => 'TZ', 'phone_code' => '+255', 'flag' => '🇹🇿'],
            ['name' => ['fr' => 'Tchad', 'en' => 'Chad'], 'code' => 'TD', 'phone_code' => '+235', 'flag' => '🇹🇩'],
            ['name' => ['fr' => 'Thaïlande', 'en' => 'Thailand'], 'code' => 'TH', 'phone_code' => '+66', 'flag' => '🇹🇭'],
            ['name' => ['fr' => 'Timor oriental', 'en' => 'East Timor'], 'code' => 'TL', 'phone_code' => '+670', 'flag' => '🇹🇱'],
            ['name' => ['fr' => 'Togo', 'en' => 'Togo'], 'code' => 'TG', 'phone_code' => '+228', 'flag' => '🇹🇬'],
            ['name' => ['fr' => 'Tonga', 'en' => 'Tonga'], 'code' => 'TO', 'phone_code' => '+676', 'flag' => '🇹🇴'],
            ['name' => ['fr' => 'Trinité-et-Tobago', 'en' => 'Trinidad and Tobago'], 'code' => 'TT', 'phone_code' => '+1868', 'flag' => '🇹🇹'],
            ['name' => ['fr' => 'Tunisie', 'en' => 'Tunisia'], 'code' => 'TN', 'phone_code' => '+216', 'flag' => '🇹🇳'],
            ['name' => ['fr' => 'Turkménistan', 'en' => 'Turkmenistan'], 'code' => 'TM', 'phone_code' => '+993', 'flag' => '🇹🇲'],
            ['name' => ['fr' => 'Turquie', 'en' => 'Turkey'], 'code' => 'TR', 'phone_code' => '+90', 'flag' => '🇹🇷'],
            ['name' => ['fr' => 'Tuvalu', 'en' => 'Tuvalu'], 'code' => 'TV', 'phone_code' => '+688', 'flag' => '🇹🇻'],
            // U
            ['name' => ['fr' => 'Ukraine', 'en' => 'Ukraine'], 'code' => 'UA', 'phone_code' => '+380', 'flag' => '🇺🇦'],
            ['name' => ['fr' => 'Uruguay', 'en' => 'Uruguay'], 'code' => 'UY', 'phone_code' => '+598', 'flag' => '🇺🇾'],
            // V
            ['name' => ['fr' => 'Vanuatu', 'en' => 'Vanuatu'], 'code' => 'VU', 'phone_code' => '+678', 'flag' => '🇻🇺'],
            ['name' => ['fr' => 'Vatican', 'en' => 'Vatican City'], 'code' => 'VA', 'phone_code' => '+379', 'flag' => '🇻🇦'],
            ['name' => ['fr' => 'Venezuela', 'en' => 'Venezuela'], 'code' => 'VE', 'phone_code' => '+58', 'flag' => '🇻🇪'],
            ['name' => ['fr' => 'Viêt Nam', 'en' => 'Vietnam'], 'code' => 'VN', 'phone_code' => '+84', 'flag' => '🇻🇳'],
            // Y
            ['name' => ['fr' => 'Yémen', 'en' => 'Yemen'], 'code' => 'YE', 'phone_code' => '+967', 'flag' => '🇾🇪'],
            // Z
            ['name' => ['fr' => 'Zambie', 'en' => 'Zambia'], 'code' => 'ZM', 'phone_code' => '+260', 'flag' => '🇿🇲'],
            ['name' => ['fr' => 'Zimbabwe', 'en' => 'Zimbabwe'], 'code' => 'ZW', 'phone_code' => '+263', 'flag' => '🇿🇼'],
        ];

        foreach ($countries as $country) {
            $model = Country::query()->firstOrNew(['code' => $country['code']]);
            $model->phone_code = $country['phone_code'] ?? null;
            $model->is_active = true;
            $model->setTranslations('name', $country['name']);
            $model->save();
        }
    }
}