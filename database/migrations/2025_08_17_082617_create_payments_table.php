<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('XAF');
            $table->enum('payment_type', ['FULL', 'PARTIAL', 'REFUND'])->default('FULL');
            $table->enum('payment_method', ['CARD', 'BANK_TRANSFER', 'CASH', 'OTHER'])->default('BANK_TRANSFER');
            $table->dateTime('payment_date')->nullable();
            $table->dateTime('due_date')->nullable();

            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'REFUNDED'])->default('PENDING')->index();
            $table->string('transaction_id')->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['application_step_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
