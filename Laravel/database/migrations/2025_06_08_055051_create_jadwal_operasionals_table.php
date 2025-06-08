<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalOperasionalsTable extends Migration
{
    public function up()
    {
        Schema::create('jadwal_operasional', function (Blueprint $table) {
            $table->id();
            $table->string('hari');
            $table->time('jam_buka')->default('08:00');
            $table->time('jam_tutup')->default('22:00');
            $table->boolean('is_tutup')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_operasional');
    }
}
