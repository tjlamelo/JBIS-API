<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{
    /**
     * @return \App\Core\Domain\Identity\Models\User|null
     */
    public function user();
}