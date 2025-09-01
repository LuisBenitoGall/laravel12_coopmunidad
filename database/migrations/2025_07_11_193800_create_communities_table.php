<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('communities', function (Blueprint $table){
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique()->index();
            $table->string('address')->nullable();
            $table->string('cp', 10)->nullable();
            $table->foreignId('town_id')->constrained()->onDelete('restrict');
            $table->string('nif', 20)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('status')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::dropIfExists('communities');
    }
};
