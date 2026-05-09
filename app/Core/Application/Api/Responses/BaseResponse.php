<?php

declare(strict_types=1);

namespace App\Core\Application\Api\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Enveloppe JSON standard de l'API : code HTTP, message lisible, charge utile.
 */
final readonly class BaseResponse
{
    /**
     * @param  array<string, mixed>|list<mixed>|object|null  $data
     */
    public function __construct(
        public int $code,
        public string $message,
        public mixed $data = null,
    ) {}

    public static function make(
        HttpResponseCode $status,
        mixed $data = null,
        ?string $message = null,
    ): self {
        return new self(
            $status->value,
            $message ?? $status->defaultMessage(),
            $data,
        );
    }

    public static function ok(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::Ok, $data, $message);
    }

    public static function created(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::Created, $data, $message);
    }

    public static function accepted(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::Accepted, $data, $message);
    }

    public static function noContent(?string $message = null): self
    {
        return self::make(HttpResponseCode::NoContent, null, $message);
    }

    public static function badRequest(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::BadRequest, $data, $message);
    }

    public static function unauthorized(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::Unauthorized, $data, $message);
    }

    public static function forbidden(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::Forbidden, $data, $message);
    }

    public static function notFound(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::NotFound, $data, $message);
    }

    public static function conflict(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::Conflict, $data, $message);
    }

    public static function unprocessableEntity(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::UnprocessableEntity, $data, $message);
    }

    public static function tooManyRequests(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::TooManyRequests, $data, $message);
    }

    public static function internalServerError(mixed $data = null, ?string $message = null): self
    {
        return self::make(HttpResponseCode::InternalServerError, $data, $message);
    }

    /**
     * @return array{code: int, message: string, data: mixed}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }

    /**
     * @param  int|null  $httpStatus  Statut HTTP de la réponse ; par défaut égal à {@see $code}.
     */
    public function toJsonResponse(?int $httpStatus = null): JsonResponse
    {
        return response()->json($this->toArray(), $httpStatus ?? $this->code);
    }
}
