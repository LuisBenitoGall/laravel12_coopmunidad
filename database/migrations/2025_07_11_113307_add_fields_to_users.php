<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::table('users', function (Blueprint $table){
            $table->string('surname')->nullable()->after('name');
            $table->string('nickname')->nullable()->after('surname');
            $table->date('birthday')->nullable()->after('password');
            $table->string('nif')->nullable()->after('birthday');
            $table->string('signature')->nullable()->after('nif');
            $table->boolean('isAdmin')->default(false)->after('signature');
            $table->integer('status')->default(0)->after('isAdmin');
            $table->softDeletes()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::table('users', function (Blueprint $table){
            $table->dropColumn(['surname', 'nickname', 'birthday', 'nif', 'signature', 'isAdmin', 'status', 'deleted_at']);
        });
    }
};
