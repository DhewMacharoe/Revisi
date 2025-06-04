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
                Schema::create('pemesanan', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('id_pelanggan');
                    $table->unsignedBigInteger('admin_id')->nullable();
                    $table->decimal('total_harga', 10, 2);
                    $table->string('metode_pembayaran')->default('tunai');
                    $table->string('bukti_pembayaran')->nullable();
                    $table->enum('status', ['menunggu', 'pembayaran', 'dibayar', 'diproses', 'selesai', 'dibatalkan'])->default('menunggu');
                    $table->dateTime('waktu_pemesanan')->nullable();
                    $table->dateTime('waktu_pengambilan')->nullable();
                    $table->timestamps();

                    $table->foreign('id_pelanggan')->references('id')->on('pelanggan');
                    $table->foreign('admin_id')->references('id')->on('admin')->onDelete('set null');
                });
            }

            /**
             * Reverse the migrations.
             */
            public function down(): void
            {
                Schema::dropIfExists('pemesanan');
            }
        };
