<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support\Document;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Services\Document\DocumentTypeResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

final class UserDocumentTypeRules
{
    /**
     * @return array<int, string>
     */
    public static function typeFieldRules(bool $required = true): array
    {
        $rules = ['string', 'exists:document_types,code'];

        return $required ? ['required', ...$rules] : ['sometimes', ...$rules];
    }

    /**
     * @return array<string, mixed>
     */
    public static function baseFileRules(bool $required = true): array
    {
        $fileRule = File::types(UserDocumentFilePolicy::ALLOWED_EXTENSIONS)
            ->max(UserDocumentFilePolicy::MAX_SIZE_KB_DEFAULT);

        return $required ? ['required', 'file', $fileRule] : ['sometimes', 'file', $fileRule];
    }

    public static function applyTypeSpecificRules(
        Validator $validator,
        bool $isUpdate = false,
        ?int $ignoreDocumentId = null,
        ?int $userId = null,
        ?DocumentType $defaultType = null,
    ): void {
        $resolver = app(DocumentTypeResolver::class);

        $validator->after(function (Validator $validator) use ($isUpdate, $ignoreDocumentId, $userId, $defaultType, $resolver): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $typeInput = $validator->getData()['type'] ?? null;
            $type = null;

            if ($typeInput !== null && $typeInput !== '') {
                try {
                    $type = $resolver->resolve((string) $typeInput);
                } catch (\Throwable) {
                    $validator->errors()->add('type', __('Type de document invalide.'));

                    return;
                }
            } elseif ($isUpdate && $defaultType !== null) {
                $type = $defaultType;
            } elseif (! $isUpdate) {
                return;
            } else {
                return;
            }

            $file = request()->file('file');
            if ($file instanceof UploadedFile) {
                $error = UserDocumentFilePolicy::validate($file, $type);
                if ($error !== null) {
                    $validator->errors()->add('file', $error);
                }
            } elseif (! $isUpdate) {
                $validator->errors()->add('file', __('Le fichier est obligatoire.'));
            }

            $data = $validator->getData();
            $typeChanged = ! $isUpdate || ($typeInput !== null && $typeInput !== '');

            if ($type->requiresDocumentNumber() && empty($data['document_number'])) {
                $validator->errors()->add('document_number', __('Le numéro de document est obligatoire pour ce type.'));
            }

            if ($type->requiresExpiryDate() && empty($data['expiry_date'])) {
                $validator->errors()->add('expiry_date', __('La date d\'expiration est obligatoire pour ce type.'));
            }

            $resolvedUserId = $userId ?? (isset($data['user_id']) ? (int) $data['user_id'] : 0);

            if (
                $typeChanged
                && $resolvedUserId > 0
                && $type->isUniquePerUser()
                && ! self::shouldBypassUniqueness()
            ) {
                self::assertUniqueTypeForUser($validator, $resolvedUserId, $type, $ignoreDocumentId);
            }
        });
    }

    private static function shouldBypassUniqueness(): bool
    {
        $user = request()->user();

        return $user !== null && $user->can('userdocument.create');
    }

    private static function assertUniqueTypeForUser(
        Validator $validator,
        int $userId,
        DocumentType $type,
        ?int $ignoreDocumentId,
    ): void {
        $exists = UserDocument::query()
            ->where('user_id', $userId)
            ->where('document_type_id', $type->id)
            ->when($ignoreDocumentId !== null, fn ($q) => $q->where('id', '!=', $ignoreDocumentId))
            ->exists();

        if ($exists) {
            $validator->errors()->add(
                'type',
                __('Un document de type :type existe déjà pour cet utilisateur.', ['type' => $type->label]),
            );
        }
    }
}
