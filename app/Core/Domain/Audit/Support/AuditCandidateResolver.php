<?php

declare(strict_types=1);

namespace App\Core\Domain\Audit\Support;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\Interview;
use App\Core\Domain\Identity\Models\User;
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
            ->select(['id', 'first_name', 'last_name', 'email'])
            ->find($userId);

        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'first_name' => (string) $user->first_name,
            'last_name' => (string) $user->last_name,
            'email' => (string) $user->email,
        ];
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
            \App\Core\Domain\Identity\Models\Experience::class => 'Expérience',
            \App\Core\Domain\Identity\Models\Education::class => 'Formation',
            \App\Core\Domain\Identity\Models\Certification::class => 'Certification',
            \App\Core\Domain\Identity\Models\Language::class => 'Langue',
            \App\Core\Domain\Identity\Models\InterestAndHobby::class => 'Centre d\'intérêt',
            \App\Core\Domain\Identity\Models\UserSkill::class => 'Compétence',
            \App\Core\Domain\Identity\Models\UserTraining::class => 'Formation catalogue',
            \App\Core\Domain\Identity\Models\UserInternship::class => 'Stage',
            \App\Core\Domain\Identity\Models\UserVisaHistory::class => 'Visa',
            \App\Core\Domain\Identity\Models\UserPreferredCountry::class => 'Pays préféré',
            \App\Core\Domain\Identity\Models\UserNote::class => 'Note interne',
            \App\Core\Domain\Identity\Models\UserDocument::class => 'Document',
            \App\Core\Domain\Identity\Models\Archive::class => 'Archive',
            \App\Core\Domain\Identity\Models\UserProfile::class => 'Profil',
        ];

        return $map[$model::class] ?? class_basename($model);
    }
}
