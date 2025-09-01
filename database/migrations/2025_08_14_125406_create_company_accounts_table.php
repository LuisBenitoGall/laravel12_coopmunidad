<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('company_accounts', function (Blueprint $table) {
            //FK
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            //FK
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price', 9, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->integer('status')->default(1);      //Leyenda: 0 = inactivo; 1 = activo; 2 = caducado.
            $table->softDeletes();
            $table->timestamps();

            $table->primary(['company_id', 'account_id']); // evita duplicados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void{
        Schema::dropIfExists('company_accounts');
    }
};
