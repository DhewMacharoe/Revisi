<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::unprepared('
            CREATE TRIGGER kurangi_stok_setelah_diproses
            AFTER UPDATE ON pemesanan
            FOR EACH ROW
            BEGIN
                DECLARE menu_id_val INT;
                DECLARE jumlah_val INT;
                DECLARE done INT DEFAULT FALSE;
                DECLARE cur CURSOR FOR 
                    SELECT id_menu, jumlah FROM detail_pemesanan WHERE id_pemesanan = NEW.id;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                IF NEW.status = \'diproses\' AND OLD.status != \'diproses\' THEN
                    OPEN cur;

                    read_loop: LOOP
                        FETCH cur INTO menu_id_val, jumlah_val;

                        IF done THEN
                            LEAVE read_loop;
                        END IF;

                        UPDATE menu
                        SET stok = stok - jumlah_val
                        WHERE id = menu_id_val;
                    END LOOP;

                    CLOSE cur;
                END IF;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS kurangi_stok_setelah_diproses');
    }
};
