<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\States;

enum ProgramStatus: string
{
    case Draft = 'DRAFT';         // En cours de rédaction
    case Published = 'PUBLISHED'; // Visible par les candidats
    case Archived = 'ARCHIVED';   // Masqué mais conservé en historique
    case Closed = 'CLOSED';       // Programme terminé (inscriptions closes)
}