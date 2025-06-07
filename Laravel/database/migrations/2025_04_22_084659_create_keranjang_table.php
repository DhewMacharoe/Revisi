<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeranjangTable extends Migration
{
    public function up()
    {
        Schema::create('keranjang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pelanggan');
            $table->unsignedBigInteger('id_menu');
            $table->string('nama_menu',30);
            $table->enum('kategori', ['makanan', 'minuman'])->nullable();
            $table->integer('jumlah')->default(1);
            $table->decimal('harga', 10, 2);
            $table->string('catatan',50)->nullable(); // Tambahkan ini
            $table->string('suhu',20)->nullable(); // Tambahkan ini juga untuk konsistensi
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('id_pelanggan')->references('id')->on('pelanggan')->onDelete('cascade');
            $table->foreign('id_menu')->references('id')->on('menu')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('keranjang');
    }
}
