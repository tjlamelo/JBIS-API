<?php

namespace Illuminate\Http;

interface Request
{
    /**
     * @return \App\Core\Domain\Identity\Models\User|null
     */
    public function user($guard = null);
}