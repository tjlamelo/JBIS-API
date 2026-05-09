<?php
declare(strict_types=1);


namespace App\Core\Domain\Shared\Interfaces;

use Illuminate\Http\Request;

interface IDto
{

    /**
     * Summary of fromRequest
     * @param \Illuminate\Http\Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self;
    /**
     * Summary of fromArray
     * @param array $data
     * @return void
     */
    public static function fromArray(array $data): self;
    /**
     * Summary of toArray
     * @return void
     */
    public function toArray(): array;
}