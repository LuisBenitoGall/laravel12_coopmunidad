<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void{
        Schema::create('categorizables', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->morphs('categorizable'); // categorizable_id (bigint) + categorizable_type (string)
            $table->timestamps();

            $table->primary(['category_id', 'categorizable_id', 'categorizable_type'], 'categorizables_pk');

            $table->foreign('category_id')
                  ->references('id')->on('categories')
                  ->cascadeOnDelete();

            $table->index(['categorizable_type', 'categorizable_id'], 'categorizables_target_idx');
        });
    }

    public function down(): void{
        Schema::dropIfExists('categorizables');
    }
};
