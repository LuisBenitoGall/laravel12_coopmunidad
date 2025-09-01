<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('company_communities', function (Blueprint $table){
            //FK
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            //FK
            $table->foreignId('community_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['company_id', 'community_id']); // evita duplicados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::dropIfExists('company_communities');
    }
};
