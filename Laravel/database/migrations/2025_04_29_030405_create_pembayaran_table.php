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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pemesanan');
            $table->string('order_id')->unique();
            $table->decimal('gross_amount', 10, 2);
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_status');
            $table->dateTime('transaction_time')->nullable();
            $table->text('snap_token')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('payment_code')->nullable();
            $table->string('bank')->nullable();
            $table->string('va_number')->nullable();
            $table->string('qr_code_url')->nullable();
            $table->timestamps();

            $table->foreign('id_pemesanan')->references('id')->on('pemesanan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};