<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_menu');
            $table->enum('kategori', ['makanan', 'minuman']);
            $table->enum('suhu', ['panas', 'dingin'])->nullable();
            $table->decimal('harga', 10, 2);
            $table->integer('stok')->default(0);
            $table->string('gambar')->nullable();
            // $table->integer('stok_terjual')->default(0);
            $table->string('deskripsi')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->foreign('id_admin')->references('id')->on('admin')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
