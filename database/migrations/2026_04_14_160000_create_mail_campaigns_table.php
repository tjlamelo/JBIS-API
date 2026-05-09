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
        Schema::create('mail_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150)->nullable();
            $table->string('subject', 255);
            $table->longText('body');
            $table->json('content')->nullable();
            $table->json('targeting')->nullable();
            $table->string('send_mode', 20)->default('queue');
            $table->string('from_name', 120)->nullable();
            $table->string('reply_to', 255)->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('sent_at')->nullable();
            

          
  
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_campaigns');
    }
};
