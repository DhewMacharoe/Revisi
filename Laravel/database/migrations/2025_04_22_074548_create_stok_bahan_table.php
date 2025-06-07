<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('stok_bahan', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->foreign('id_admin')->references('id')->on('admin')->onDelete('set null');
            $table->string('nama_bahan',20);
            $table->integer('jumlah');
            $table->enum('satuan', ['kg', 'liter', 'pcs', 'tandan', 'dus']);
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('stok_bahan');
    }
};