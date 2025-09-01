<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void{
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // Nombre visible
            $table->string('slug')->unique();             // Slug único global
            $table->string('color', 20)->nullable();      // Ej: #4F46E5 o 'indigo-600'
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // (opcional) autor
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void{
        Schema::dropIfExists('categories');
    }
};
