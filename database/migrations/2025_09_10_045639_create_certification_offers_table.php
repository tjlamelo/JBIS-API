<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certification_offers', function (Blueprint $table) {
            $table->id();

            $table->string('domain')->index();
            $table->string('title');
            $table->string('duration_label', 64)->nullable();
            $table->string('organization');
            $table->text('description')->nullable();

            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('first_installment', 15, 2)->nullable();
            $table->decimal('second_installment', 15, 2)->nullable();
            $table->decimal('registration_fee', 15, 2)->default(25000);
            $table->string('currency', 10)->default('XAF');
            $table->enum('exam_mode', ['ONLINE', 'ONSITE', 'PROCTORED'])->default('ONSITE');

            $table->integer('validity_years')->nullable();
            $table->string('level')->nullable();

            $table->foreignId('process_flow_id')->nullable()->constrained()->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_offers');
    }
};
