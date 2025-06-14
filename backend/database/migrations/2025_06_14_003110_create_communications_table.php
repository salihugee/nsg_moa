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
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->bigInteger('recipient_id');
            $table->text('message');
            $table->string('status', 50);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Index for quick lookups
            $table->index(['recipient_id', 'type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
