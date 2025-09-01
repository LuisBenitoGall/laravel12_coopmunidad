<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('workplaces', function (Blueprint $table){
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->index();
            //FK
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->boolean('featured')->default(false);
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('cp')->nullable();
            //FK
            $table->foreignId('town_id')->nullable()->constrained('towns')->onDelete('set null');
            $table->string('nif')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->integer('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::dropIfExists('workplaces');
    }
};
