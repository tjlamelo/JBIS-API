<?php

namespace Database\Factories;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Domain\Identity\Models\UserDocument>
 */

class UserDocumentFactory extends Factory
{
    protected $model = UserDocument::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_type' => $this->faker->word,
            'document_number' => $this->faker->uuid,
            'issue_date' => $this->faker->date(),
            'expiry_date' => $this->faker->date(),
            'issuing_authority' => $this->faker->company,
            'document_description' => $this->faker->sentence,
            'document_status' => 'pending',
            'document_front' => null,
            'document_back' => null,
        ];
    }
}
