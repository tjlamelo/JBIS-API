<?php
 
namespace App\Core\Domain\Shared\Media\DTOs;

class UploadedMediaDto
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $localOptimizedPath,
        public readonly string $localRawPath,
        public readonly ?string $cloudinaryId,
        public readonly string $publicUrl,
        public readonly bool $isPrimary = false
    ) {}

    public function toArray(): array
    {
        return [
            'file_name' => $this->fileName,
            'local_optimized_path' => $this->localOptimizedPath,
            'local_raw_path' => $this->localRawPath,
            'cloudinary_id' => $this->cloudinaryId,
            'public_url' => $this->publicUrl,
            'is_primary' => $this->isPrimary,
            'uploaded_at' => now()->toDateTimeString(),
        ];
    }
}