<?php

declare(strict_types=1);

/**
 * Fêtes et journées nationales / civiles du Cameroun.
 *
 * - `fixed` : mois-jour (mm-dd)
 * - `easter_offset` : jours relatifs au dimanche de Pâques grégorien
 * - `islamic` : dates lunaires (approximations civiles pour 2026–2032)
 */
return [
    'timezone' => 'Africa/Douala',

    'fixed' => [
        '01-01' => [
            'code' => 'new_year',
            'title' => [
                'fr' => 'Bonne année !',
                'en' => 'Happy New Year!',
            ],
            'body' => [
                'fr' => 'Toute l’équipe MyJob Best  vous souhaite une excellente année au Cameroun et à l’international.',
                'en' => 'The entire MyJob Best team wishes you an excellent year in Cameroon and abroad.',
            ],
        ],
        '02-11' => [
            'code' => 'youth_day',
            'title' => [
                'fr' => 'Joyeuse fête de la Jeunesse',
                'en' => 'Happy Youth Day',
            ],
            'body' => [
                'fr' => 'Le 11 février, le Cameroun célèbre sa jeunesse. Belle journée à vous !',
                'en' => 'On 11 February, Cameroon celebrates its youth. Have a wonderful day!',
            ],
        ],
        '05-01' => [
            'code' => 'labour_day',
            'title' => [
                'fr' => 'Bonne fête du Travail',
                'en' => 'Happy Labour Day',
            ],
            'body' => [
                'fr' => 'Le 1er mai, journée internationale des travailleurs. Reposez-vous bien.',
                'en' => '1 May is International Workers’ Day. Enjoy a well-deserved rest.',
            ],
        ],
        '05-20' => [
            'code' => 'national_day',
            'title' => [
                'fr' => 'Joyeuse fête nationale',
                'en' => 'Happy National Day',
            ],
            'body' => [
                'fr' => 'Le 20 mai, fête de l’Unité nationale (réunification). Vive le Cameroun !',
                'en' => '20 May is National Unity Day (reunification). Long live Cameroon!',
            ],
        ],
        '08-15' => [
            'code' => 'assumption',
            'title' => [
                'fr' => 'Bonne fête de l’Assomption',
                'en' => 'Happy Assumption Day',
            ],
            'body' => [
                'fr' => 'Jour férié au Cameroun — nous vous souhaitons une belle journée.',
                'en' => 'A public holiday in Cameroon — we wish you a lovely day.',
            ],
        ],
        '10-01' => [
            'code' => 'reunification_day',
            'title' => [
                'fr' => 'Journée de la Réunification',
                'en' => 'Reunification Day',
            ],
            'body' => [
                'fr' => 'Le 1er octobre marque la réunification du Cameroun. Belle journée de mémoire et d’unité.',
                'en' => '1 October marks Cameroon’s reunification. A day of remembrance and unity.',
            ],
        ],
        '12-25' => [
            'code' => 'christmas',
            'title' => [
                'fr' => 'Joyeux Noël',
                'en' => 'Merry Christmas',
            ],
            'body' => [
                'fr' => 'Toute l’équipe MyJob Best vous souhaite un joyeux Noël.',
                'en' => 'The entire MyJob Best team wishes you a Merry Christmas.',
            ],
        ],
    ],

    /**
     * Journées de mémoire / identité souvent mises en avant (hors liste stricte des jours fériés).
     */
    'observances' => [
        '01-01' => [
            'code' => 'independence_memory',
            'title' => [
                'fr' => 'Mémoire de l’Indépendance',
                'en' => 'Independence Remembrance',
            ],
            'body' => [
                'fr' => 'Le 1er janvier 1960, le Cameroun accède à l’indépendance. Nous honorons cette date fondatrice.',
                'en' => 'On 1 January 1960, Cameroon gained independence. We honour this founding date.',
            ],
        ],
        '02-18' => [
            'code' => 'liberation_day',
            'title' => [
                'fr' => 'Journée de la Libération',
                'en' => 'Liberation Day',
            ],
            'body' => [
                'fr' => 'Nous célébrons la mémoire des luttes pour la liberté et la souveraineté du Cameroun.',
                'en' => 'We commemorate the struggles for Cameroon’s freedom and sovereignty.',
            ],
        ],
        '05-20' => [
            'code' => 'national_unity_reunion',
            'title' => [
                'fr' => 'Journée de la Réunion nationale',
                'en' => 'National Reunion Day',
            ],
            'body' => [
                'fr' => 'Fête de l’Unité : un seul Cameroun, une même ambition. Belle fête nationale !',
                'en' => 'Unity Day: one Cameroon, one shared ambition. Happy National Day!',
            ],
        ],
        '11-06' => [
            'code' => 'renaissance_day',
            'title' => [
                'fr' => 'Journée de la Renaissance',
                'en' => 'Renaissance Day',
            ],
            'body' => [
                'fr' => 'Journée dédiée à la renaissance et au renouveau — belle journée inspirante sur MyMyJob Best.',
                'en' => 'A day dedicated to renaissance and renewal — wishing you an inspiring day on MyMyJob Best.',
            ],
        ],
    ],

    'easter_offset' => [
        -2 => [
            'code' => 'good_friday',
            'title' => [
                'fr' => 'Vendredi saint',
                'en' => 'Good Friday',
            ],
            'body' => [
                'fr' => 'Jour férié au Cameroun. Nous vous souhaitons une journée sereine.',
                'en' => 'A public holiday in Cameroon. We wish you a peaceful day.',
            ],
        ],
        1 => [
            'code' => 'easter_monday',
            'title' => [
                'fr' => 'Lundi de Pâques',
                'en' => 'Easter Monday',
            ],
            'body' => [
                'fr' => 'Joyeuses fêtes de Pâques ! Profitez de cette journée fériée.',
                'en' => 'Happy Easter! Enjoy this public holiday.',
            ],
        ],
        39 => [
            'code' => 'ascension',
            'title' => [
                'fr' => 'Ascension',
                'en' => 'Ascension Day',
            ],
            'body' => [
                'fr' => 'Jour de l’Ascension, férié au Cameroun. Belle journée à vous.',
                'en' => 'Ascension Day, a public holiday in Cameroon. Have a lovely day.',
            ],
        ],
    ],

    /**
     * Approximations civiles des fêtes musulmanes (Cameroun).
     * À ajuster chaque année si besoin via config / admin.
     *
     * @var array<int, list<array{date:string,code:string,title:array{fr:string,en:string},body:array{fr:string,en:string}}>>
     */
    'islamic_by_year' => [
        2026 => [
            [
                'date' => '2026-03-20',
                'code' => 'eid_fitr',
                'title' => [
                    'fr' => 'Aïd el-Fitr',
                    'en' => 'Eid al-Fitr',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête de la rupture du jeûne à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a joyful end of the fast.',
                ],
            ],
            [
                'date' => '2026-05-27',
                'code' => 'eid_adha',
                'title' => [
                    'fr' => 'Aïd el-Adha',
                    'en' => 'Eid al-Adha',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête du sacrifice à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a blessed Feast of Sacrifice.',
                ],
            ],
        ],
        2027 => [
            [
                'date' => '2027-03-09',
                'code' => 'eid_fitr',
                'title' => [
                    'fr' => 'Aïd el-Fitr',
                    'en' => 'Eid al-Fitr',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête de la rupture du jeûne à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a joyful end of the fast.',
                ],
            ],
            [
                'date' => '2027-05-16',
                'code' => 'eid_adha',
                'title' => [
                    'fr' => 'Aïd el-Adha',
                    'en' => 'Eid al-Adha',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête du sacrifice à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a blessed Feast of Sacrifice.',
                ],
            ],
        ],
        2028 => [
            [
                'date' => '2028-02-26',
                'code' => 'eid_fitr',
                'title' => [
                    'fr' => 'Aïd el-Fitr',
                    'en' => 'Eid al-Fitr',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête de la rupture du jeûne à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a joyful end of the fast.',
                ],
            ],
            [
                'date' => '2028-05-05',
                'code' => 'eid_adha',
                'title' => [
                    'fr' => 'Aïd el-Adha',
                    'en' => 'Eid al-Adha',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête du sacrifice à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a blessed Feast of Sacrifice.',
                ],
            ],
        ],
        2029 => [
            [
                'date' => '2029-02-14',
                'code' => 'eid_fitr',
                'title' => [
                    'fr' => 'Aïd el-Fitr',
                    'en' => 'Eid al-Fitr',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête de la rupture du jeûne à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a joyful end of the fast.',
                ],
            ],
            [
                'date' => '2029-04-24',
                'code' => 'eid_adha',
                'title' => [
                    'fr' => 'Aïd el-Adha',
                    'en' => 'Eid al-Adha',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête du sacrifice à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a blessed Feast of Sacrifice.',
                ],
            ],
        ],
        2030 => [
            [
                'date' => '2030-02-04',
                'code' => 'eid_fitr',
                'title' => [
                    'fr' => 'Aïd el-Fitr',
                    'en' => 'Eid al-Fitr',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête de la rupture du jeûne à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a joyful end of the fast.',
                ],
            ],
            [
                'date' => '2030-04-13',
                'code' => 'eid_adha',
                'title' => [
                    'fr' => 'Aïd el-Adha',
                    'en' => 'Eid al-Adha',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête du sacrifice à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a blessed Feast of Sacrifice.',
                ],
            ],
        ],
        2031 => [
            [
                'date' => '2031-01-24',
                'code' => 'eid_fitr',
                'title' => [
                    'fr' => 'Aïd el-Fitr',
                    'en' => 'Eid al-Fitr',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête de la rupture du jeûne à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a joyful end of the fast.',
                ],
            ],
            [
                'date' => '2031-04-03',
                'code' => 'eid_adha',
                'title' => [
                    'fr' => 'Aïd el-Adha',
                    'en' => 'Eid al-Adha',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête du sacrifice à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a blessed Feast of Sacrifice.',
                ],
            ],
        ],
        2032 => [
            [
                'date' => '2032-01-14',
                'code' => 'eid_fitr',
                'title' => [
                    'fr' => 'Aïd el-Fitr',
                    'en' => 'Eid al-Fitr',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête de la rupture du jeûne à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a joyful end of the fast.',
                ],
            ],
            [
                'date' => '2032-03-22',
                'code' => 'eid_adha',
                'title' => [
                    'fr' => 'Aïd el-Adha',
                    'en' => 'Eid al-Adha',
                ],
                'body' => [
                    'fr' => 'Eid Mubarak ! Belle fête du sacrifice à vous et vos proches.',
                    'en' => 'Eid Mubarak! Wishing you and your loved ones a blessed Feast of Sacrifice.',
                ],
            ],
        ],
    ],
];
