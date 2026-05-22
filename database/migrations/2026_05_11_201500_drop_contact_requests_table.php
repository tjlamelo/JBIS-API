<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('contact_requests');
    }

    public function down(): void
    {
        // Intentionally left blank.
        // If you need to restore this table, revert to the migration that created it.
    }
};
