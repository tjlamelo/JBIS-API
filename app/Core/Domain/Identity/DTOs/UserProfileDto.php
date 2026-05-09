<?php
declare(strict_types=1);

namespace App\Core\Domain\Identity\DTOs;

use App\Core\Domain\Shared\Interfaces\IDto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class UserProfileDto implements IDto
{
    /**
     * Utilisation de la promotion de propriété pour éviter la double déclaration
     * et le constructeur verbeux.
     */
    public function __construct(
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $date_of_birth = null,
        public ?string $place_of_birth = null,
        public ?string $address = null,
        public ?string $phone_number2 = null,
        public ?string $phone_number3 = null,
        public ?string $gender = null,
        public ?UploadedFile $profile_picture = null,
        public ?string $bio = null,
        public ?string $marital_status = null,
        public ?int $number_of_children = null,
        public ?int $agencies_id = null,
        public ?string $matricule = null,
        public ?string $email_institutional = null,
        public ?bool $is_approved = null,
        public ?int $approved_by = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        // On utilise $request->validated() si tu utilises des FormRequests (recommandé)
        // Sinon, $request->all() ou une sélection précise.
        return new self(
            ...$request->only([
                'first_name', 'last_name', 'date_of_birth', 'place_of_birth', 
                'address', 'phone_number2', 'phone_number3', 
                'gender', 'bio', 'marital_status', 'matricule', 'email_institutional'
            ]),
            profile_picture: $request->file('profile_picture'),
            number_of_children: $request->integer('number_of_children') ?: null,
            agencies_id: $request->integer('agencies_id') ?: null,
            is_approved: $request->boolean('is_approved'),
            approved_by: $request->integer('approved_by') ?: null,
        );
    }

    public static function fromArray(array $data): self
    {
        // Utilisation des arguments nommés pour plus de clarté et éviter les erreurs d'index
        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            date_of_birth: $data['date_of_birth'] ?? null,
            place_of_birth: $data['place_of_birth'] ?? null,
            address: $data['address'] ?? null,
            phone_number2: $data['phone_number2'] ?? null,
            phone_number3: $data['phone_number3'] ?? null,
            gender: $data['gender'] ?? null,
            profile_picture: $data['profile_picture'] ?? null,
            bio: $data['bio'] ?? null,
            marital_status: $data['marital_status'] ?? null,
            number_of_children: isset($data['number_of_children']) ? (int) $data['number_of_children'] : null,
            agencies_id: isset($data['agencies_id']) ? (int) $data['agencies_id'] : null,
            matricule: $data['matricule'] ?? null,
            email_institutional: $data['email_institutional'] ?? null,
            is_approved: isset($data['is_approved']) ? (bool) $data['is_approved'] : null,
            approved_by: isset($data['approved_by']) ? (int) $data['approved_by'] : null,
        );
    }

    public function toArray(): array
    {
        // get_object_vars($this) récupère automatiquement toutes les propriétés publiques
        return array_filter(get_object_vars($this), fn($value) => !is_null($value));
    }
}