<?php

namespace Database\Factories;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDocument>
 */
class UserDocumentFactory extends Factory
{
    protected $model = UserDocument::class;

    public function definition(): array
    {
        $documentTypeId = DocumentType::query()->inRandomOrder()->value('id')
            ?? DocumentType::query()->where('code', 'OTHER')->value('id');

        return [
            'user_id' => User::factory(),
            'uploaded_by' => null,
            'document_type_id' => $documentTypeId,
            'document_number' => $this->faker->optional()->numerify('########'),
            'issuing_country_id' => null,
            'file_path' => 'Document/users/1/'.now()->format('Y/m').'/sample.pdf',
            'original_filename' => 'sample.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'issue_date' => $this->faker->optional()->date(),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('+1 year', '+5 years')?->format('Y-m-d'),
            'status' => UserDocumentStatus::Pending->value,
            'rejection_reason' => null,
            'validated_at' => null,
            'validated_by' => null,
            'notes' => null,
            'is_verified_copy' => false,
            'is_sensitive' => false,
        ];
    }
}
