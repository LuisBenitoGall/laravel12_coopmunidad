<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); 
            $table->string('slug')->nullable()->unique(); 
            $table->string('code')->nullable()->index(); 
            $table->char('alfa2', 2)->nullable()->index(); 
            $table->char('alfa3', 3)->nullable()->index(); 
            $table->string('flag')->nullable(); 
            $table->boolean('status')->default(1)->index(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::dropIfExists('countries');
    }
};



