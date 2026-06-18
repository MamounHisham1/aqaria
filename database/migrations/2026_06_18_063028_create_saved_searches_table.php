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
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            // The query-string filters captured from the listings search.
            $table->json('filters');
            // Whether the user wants email notifications for new matches.
            $table->boolean('notify')->default(false);
            // The last time matching listings were emailed (for dedup).
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'notify']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
