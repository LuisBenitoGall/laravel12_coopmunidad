<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('community_settings', function (Blueprint $table) {
            $table->id();
            //FK:
            $table->foreignId('community_id')->constrained()->onDelete('cascade');
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::dropIfExists('community_settings');
    }
};
