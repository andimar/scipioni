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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gender', 30)->nullable();
            $table->string('age_range', 20)->nullable();
            $table->string('origin_city')->nullable();
            $table->string('rome_area')->nullable()->index();
            $table->json('food_preferences')->nullable();
            $table->json('event_preferences')->nullable();
            $table->string('source_channel')->nullable();
            $table->boolean('privacy_consent')->default(false);
            $table->timestamp('privacy_consented_at')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('marketing_consented_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
