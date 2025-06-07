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
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique(); // Contoh: 'registration_pin'
                $table->text('value')->nullable(); // Nilai dari pengaturan, bisa null
                $table->foreignId('updated_by_admin_id')->nullable()->constrained('admin')->onDelete('set null'); // Ganti 'admin' dengan nama tabel admin Anda jika berbeda
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('settings');
        }
    };
    