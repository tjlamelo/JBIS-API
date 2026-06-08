<?php

declare(strict_types=1);

namespace App\Core\Domain\Audit\Support;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\Interview;
use App\Core\Domain\Identity\Models\Archive;
use App\Core\Domain\Identity\Models\Certification;
use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Identity\Models\InterestAndHobby;
use App\Core\Domain\Identity\Models\Language;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\UserInternship;
use App\Core\Domain\Identity\Models\UserNote;
use App\Core\Domain\Identity\Models\UserPreferredCountry;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Models\UserSkill;
use App\Core\Domain\Identity\Models\UserTraining;
use App\Core\Domain\Identity\Models\UserVisaHistory;
use App\Core\Domain\Identity\Support\UserPersonName;
use Illuminate\Database\Eloquent\Model;

final class AuditCandidateResolver
{
    /**
     * @return array{id: int, first_name: string, last_name: string, email: string}|null
     */
    public static function fromAuditable(?Model $model): ?array
    {
        if ($model === null) {
            return null;
        }

        $userId = self::resolveUserId($model);
        if ($userId === null) {
            return null;
        }

        $user = User::query()
            ->select(['id', 'name', 'email'])
            ->with('profile:'.UserPersonName::PROFILE_COLUMNS)
            ->find($userId);

        if ($user === null) {
            return null;
        }

        return UserPersonName::toContactArray($user);
    }

    public static function resolveUserId(?Model $model): ?int
    {
        if ($model === null) {
            return null;
        }

        if ($model instanceof Application) {
            return (int) $model->user_id;
        }

        if ($model instanceof Interview) {
            return Application::query()
                ->where('id', $model->application_id)
                ->value('user_id');
        }

        if (isset($model->user_id)) {
            return (int) $model->user_id;
        }

        return null;
    }

    public static function resourceLabel(?Model $model): string
    {
        if ($model === null) {
            return 'Ressource';
        }

        $map = [
            Application::class => 'Candidature',
            Interview::class => 'Entretien',
            Experience::class => 'Expérience',
            Education::class => 'Formation',
            Certification::class => 'Certification',
            Language::class => 'Langue',
            InterestAndHobby::class => 'Centre d\'intérêt',
            UserSkill::class => 'Compétence',
            UserTraining::class => 'Formation catalogue',
            UserInternship::class => 'Stage',
            UserVisaHistory::class => 'Visa',
            UserPreferredCountry::class => 'Pays préféré',
            UserNote::class => 'Note interne',
            UserDocument::class => 'Document',
            Archive::class => 'Archive',
            UserProfile::class => 'Profil',
        ];

        return $map[$model::class] ?? class_basename($model);
    }
}
