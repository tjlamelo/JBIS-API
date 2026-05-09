<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Le candidat ou l'initiateur

            // LIEN CRUCIAL : Vers quelle tranche ce paiement est-il dirigé ?
            $table->foreignId('payment_installment_id')->nullable()->constrained()->nullOnDelete();

            // Informations financières
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('XAF'); // Par défaut pour le Cameroun

            // Précision du type pour la comptabilité
            $table->enum('payment_type', ['DEPOSIT', 'INSTALLMENT', 'FINAL', 'REFUND'])->default('INSTALLMENT');
            $table->enum('payment_method', ['CASH', 'BANK_TRANSFER', 'MOBILE_MONEY', 'CARD', 'CHEQUE'])->default('CASH');

            // Dates
            $table->dateTime('payment_date')->useCurrent(); // Date effective de l'encaissement
            // Note: due_date n'est plus nécessaire ici car elle est gérée par l'installment

            // Suivi & Preuves
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'REVERSED'])->default('COMPLETED');

            $table->string('transaction_id')->nullable()->index(); // ID externe (OM/MoMo/Banque)
            $table->string('reference')->unique()->nullable();    // Ton numéro de reçu interne (ex: RCP-2026-001)

            $table->text('internal_note')->nullable(); // Note pour l'admin JBIS
            $table->string('receipt_path')->nullable(); // Lien vers le scan du reçu papier si besoin

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_installments');
    }
};
