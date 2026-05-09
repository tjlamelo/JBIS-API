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

        Schema::create('payments', function (Blueprint $table) {
            $table->id();


            $table->foreignId('application_id')->constrained()->onDelete('cascade');

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Informations de paiement
            $table->decimal('amount', 15, 2);       // Montant payé
            $table->string('currency', 10)->default('USD'); // Devise
            $table->enum('payment_type', ['FULL', 'PARTIAL', 'REFUND'])->default('FULL'); // Type de paiement
            $table->enum('payment_method', ['CARD', 'BANK_TRANSFER', 'CASH', 'OTHER'])->default('CARD'); // Moyen de paiement
            $table->dateTime('payment_date')->nullable(); // Date de paiement
            $table->dateTime('due_date')->nullable();     // Date limite de paiement

            // Suivi & référence
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'REFUNDED'])->default('PENDING');
            $table->string('transaction_id')->nullable(); // ID de transaction externe
            $table->string('reference')->nullable();      // Référence interne
            $table->text('description')->nullable();      // Commentaires ou détails

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
