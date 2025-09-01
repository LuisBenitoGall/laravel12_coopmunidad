<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('contents', function (Blueprint $table){
            $table->id();
            $table->string('name')->nullable()->index();                 //Nombre del contenido
            $table->string('code')->unique()->nullable()->index();       //Código único del contenido
            $table->integer('type');                            //1=info; 2=contenido.
            $table->longText('title')->nullable();
            $table->text('slug')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->text('tags')->nullable();
            $table->text('links')->nullable();
            $table->longText('video')->nullable();
            $table->text('classes')->nullable();
            $table->integer('status')->default(1);
            $table->text('observations')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->dateTime('published_end')->nullable();
            //FK
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            //FK
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::dropIfExists('contents');
    }
};
