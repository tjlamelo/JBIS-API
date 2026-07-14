<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Import;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class AdminUserImportTemplateGenerator
{
    public function generate(string $destinationPath): string
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $this->addInstructionsSheet($spreadsheet);
        $this->addUsersSheet($spreadsheet);
        $this->addEnumsSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(1);

        $dir = dirname($destinationPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        (new Xlsx($spreadsheet))->save($destinationPath);

        return $destinationPath;
    }

    public function defaultPath(): string
    {
        return storage_path('app/templates/admin-users-import-template.xlsx');
    }

    private function addInstructionsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(0);
        $sheet->setTitle('Instructions');

        $defaultPassword = (string) config('identity.default_user_password');

        $lines = [
            ['Règle', 'Détail'],
            ['Feuille à remplir', 'Users (les autres feuilles sont des aides)'],
            ['Champs obligatoires', 'email, first_name, last_name'],
            ['Mot de passe', "Laissez « password » vide pour utiliser le mot de passe par défaut configuré côté serveur ({$defaultPassword})."],
            ['Emails de bienvenue', 'À l’import confirmé, chaque compte avec email réel reçoit un e-mail (identifiants + lien) via la file mail, par paquets.'],
            ['phone_number1', 'Normalisé automatiquement (ex. 6XXXXXXXX → +2376XXXXXXXX). Unicité sur le numéro normalisé.'],
            ['Valeurs vides', 'Cellules vides = non renseigné. Si first_name/last_name vides, le champ name est découpé.'],
            ['Doublons', 'Email et téléphone doivent être uniques en base et dans le fichier.'],
            ['roles', 'Liste séparée par des virgules. Défaut : candidate. Ex. candidate ou staff,candidate'],
            ['gender', 'M / F (ou homme / femme)'],
            ['civility', 'mr / mrs / miss (voir feuille _Enums)'],
            ['date_of_birth', 'AAAA-MM-JJ — âge minimum 18 ans'],
            ['nationality_country_code', 'Code ISO pays (ex. CM, FR, DE)'],
            ['active', 'oui / non (défaut : oui)'],
            ['Lignes de commentaire', 'Les lignes dont la première cellule commence par # sont ignorées'],
        ];

        $sheet->fromArray($lines);
        $this->styleHeaderRow($sheet, 1, 2);
        $sheet->getColumnDimension('A')->setWidth(36);
        $sheet->getColumnDimension('B')->setWidth(100);
    }

    private function addUsersSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(1);
        $sheet->setTitle('Users');
        $sheet->fromArray([
            [
                'email',
                'first_name',
                'last_name',
                'name',
                'phone_number1',
                'gender',
                'civility',
                'date_of_birth',
                'place_of_birth',
                'nationality_country_code',
                'residence_city',
                'career_intent',
                'profile_type',
                'marital_status',
                'number_of_children',
                'roles',
                'active',
                'password',
            ],
            [
                'jean.dupont@example.com',
                'Jean',
                'Dupont',
                '',
                '+237600000000',
                'M',
                'mr',
                '1995-06-15',
                'Yaoundé',
                'CM',
                'Douala',
                'work_abroad',
                'job_seeker',
                'SINGLE',
                '0',
                'candidate',
                'oui',
                '',
            ],
        ]);
        $this->styleHeaderRow($sheet, 1, 18);
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setWidth(18);
        }
    }

    private function addEnumsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet(2);
        $sheet->setTitle('_Enums');
        $sheet->fromArray([
            ['field', 'value', 'label'],
            ['gender', 'M', 'Homme'],
            ['gender', 'F', 'Femme'],
            ['civility', 'mr', 'Monsieur'],
            ['civility', 'mrs', 'Madame'],
            ['civility', 'miss', 'Mademoiselle'],
            ['marital_status', 'SINGLE', 'Célibataire'],
            ['marital_status', 'MARRIED', 'Marié(e)'],
            ['marital_status', 'DIVORCED', 'Divorcé(e)'],
            ['marital_status', 'WIDOWED', 'Veuf/Veuve'],
            ['career_intent', 'work_abroad', 'Travailler à l’étranger'],
            ['career_intent', 'work_local', 'Travailler localement'],
            ['career_intent', 'visa_support', 'Accompagnement visa'],
            ['career_intent', 'explore', 'Explorer'],
            ['profile_type', 'student', 'Étudiant'],
            ['profile_type', 'recent_graduate', 'Jeune diplômé'],
            ['profile_type', 'active_worker', 'Actif'],
            ['profile_type', 'job_seeker', 'Demandeur d’emploi'],
            ['profile_type', 'exploring', 'En exploration'],
            ['roles', 'candidate', 'Candidat'],
            ['roles', 'staff', 'Staff'],
            ['roles', 'admin', 'Admin'],
            ['roles', 'partner', 'Partenaire'],
            ['roles', 'recruiter', 'Recruteur'],
        ]);
        $this->styleHeaderRow($sheet, 1, 3);
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(36);
    }

    private function styleHeaderRow(Worksheet $sheet, int $row, int $columnCount): void
    {
        $range = 'A'.$row.':'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount).$row;
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8E8E8');
    }
}
