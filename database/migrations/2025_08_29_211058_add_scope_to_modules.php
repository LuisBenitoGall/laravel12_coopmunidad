<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::table('modules', function (Blueprint $table) {
            $table->string('scope', 30)->default('both')->after('icon'); // 'company' | 'community' | 'both'
            $table->index('scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::table('modules', function (Blueprint $table) {
            $table->dropIndex(['scope']);
            $table->dropColumn('scope');
        });
    }
};
